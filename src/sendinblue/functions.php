<?php
//options takes an array where you can specify a template id and as many optional params as you want
// $options = [
// 	'template'=>1,
// 	'params' => [
// 		'fname' => $user->data()->fname,
//    'lname' => $user->data()->lname,
// 	],
// ];

//attachments is an array of arrays with the content and name of the attachment

//// Define the path to the PDF file
// $pdfFilePath = $abs_us_root . $us_url_root . "sample.pdf";

// // Read the content of the PDF file
// $pdfContent = file_get_contents($pdfFilePath);

// // Encode the content in base64
// $base64PdfContent = base64_encode($pdfContent);

// // Prepare the attachment for the sendinblue function
// $attachments = [
//     [
//         'content' => $base64PdfContent,
//         'name' => 'sample.pdf' // The name of the file as it will appear in the email
//     ]
// ];

// // Prepare other email details
// $to = "recipient@example.com";
// $subject = "Subject of the email";
// $body = "<p>This is the body of the email</p>";

// // Optional parameters, including the attachments
// $options = [
//     'attachments' => $attachments
// ];

// // Call the sendinblue function with the attachments
// $result = sendinblue($to, $subject, $body, "", $options);

// // Check the result of the email sending
// if ($result === true) {
//     echo "Email sent successfully with attachment.";
// } else {
//     echo "Failed to send email. Error: " . $result;
// }


function sendinblue($to, $subject, $body, $to_name = "", $options = []){
  global $us_url_root, $abs_us_root;
  $GLOBALS['sendinblue_last_message_id'] = null;
  $GLOBALS['sendinblue_last_error'] = null;

  if($to == "" || $subject == "" || $body == ""){
    return sendinblueFail("FAILED: Attempted to send without all required fields");
  }
  // Core email() accepts an array of recipients (UserSpice 5.6+); so does this.
  $recipients = [];
  foreach ((array)$to as $address) {
    if (is_string($address) && trim($address) !== '') $recipients[] = rawurldecode(trim($address));
  }
  if (!$recipients) {
    return sendinblueFail("FAILED: No usable recipient address");
  }
  $to = implode(', ', $recipients);
  global $db;
  $send = $db->query("SELECT * FROM plg_sendinblue")->first();
  if (!$send || empty($send->key)) {
    return sendinblueFail("FAILED: The Brevo API key is not configured");
  }

  require_once($abs_us_root.$us_url_root.'usersc/plugins/sendinblue/vendor/autoload.php');

  $credentials = Brevo\Client\Configuration::getDefaultConfiguration()->setApiKey('api-key', $send->key);
  // Guzzle waits forever by default. Core's PHPMailer path gives up after 10 seconds.
  $apiInstance = new Brevo\Client\Api\TransactionalEmailsApi(new GuzzleHttp\Client(['connect_timeout' => 5, 'timeout' => 10]), $credentials);
  if(isset($options['from'])){
    $send->from = $options['from'];
  }
  if(isset($options['from_name'])){
    $send->from_name = $options['from_name'];
  }
  if(isset($options['reply'])){
    $send->reply = $options['reply'];
  }
  // Like core email(), the Reply-To has no display name unless one is given.
  $replyTo = (isset($options['reply_name']) && $options['reply_name'] !== '')
    ? ['name' => $options['reply_name'], 'email' => $send->reply]
    : ['email' => $send->reply];

  $sendSmtpEmail = new Brevo\Client\Model\SendSmtpEmail([
    'subject' => $subject,
    'sender' => ['name' => $send->from_name, 'email' => $send->from],
    'replyTo' => $replyTo,
    'to' => array_map(function ($address) use ($to_name, $recipients) {
      // A single $to_name only makes sense for a single recipient.
      return ['name' => ($to_name !== '' && count($recipients) === 1) ? $to_name : $address, 'email' => $address];
    }, $recipients),
    'htmlContent' => $body
  ]);
  if (isset($options['textContent'])) {
    $sendSmtpEmail['textContent'] = $options['textContent'];
  }

  if(isset($options['template'])){
    $sendSmtpEmail['templateId'] = $options['template'];
  }

  if(isset($options['params'])){
    $sendSmtpEmail['params'] = $options['params'];
  }

  // Explicit tags always win. Otherwise the "Tag untagged mail" setting applies.
  // The backtrace is read here, not in a helper, so frame [1] is email() when
  // the override called us and frame [0] is a direct sendinblue() caller.
  $tags = sendinblueNormalizeTags($options['tags'] ?? []);
  if (!$tags) {
    $tagMode = $send->tag_mode ?? 'off';
    if ($tagMode === 'fixed') {
      $tags = sendinblueNormalizeTags(sendinblueCleanTag($send->tag_fixed ?? ''));
    } elseif ($tagMode === 'caller' && function_exists('debug_backtrace')) {
      $frames = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
      $frame = (isset($frames[1]) && ($frames[1]['function'] ?? '') === 'email' && !isset($frames[1]['class']))
        ? $frames[1] : $frames[0];
      $tags = sendinblueNormalizeTags(sendinblueTagFromFile($frame['file'] ?? ''));
    }
  }
  if ($tags) {
    $sendSmtpEmail['tags'] = $tags;
  }

  // Handling attachments
  if(isset($options['attachments']) && is_array($options['attachments'])){
    $attachments = array_map(function($attachment){
      return ['content' => $attachment['content'], 'name' => $attachment['name']];
    }, $options['attachments']);

    $sendSmtpEmail['attachment'] = $attachments;
  }

  try {
    $result = $apiInstance->sendTransacEmail($sendSmtpEmail);
    // Keep the Boolean return value for existing callers. The API response is
    // acceptance by Brevo; delivery is a later event for this message ID.
    $messageId = $result->getMessageId();
    $GLOBALS['sendinblue_last_message_id'] = $messageId;
    if (is_string($messageId) && $messageId !== '') {
      $kind = basename($_SERVER['SCRIPT_NAME'] ?? '') === 'passwordless.php'
        ? 'passwordless' : 'transactional';
      $db->insert('plg_sendinblue_messages', [
        'message_id' => $messageId,
        'recipient_email' => mb_substr($to, 0, 255),
        'sender_email' => $send->from,
        'kind' => $kind,
        'status' => 'accepted',
        'accepted_at' => date('Y-m-d H:i:s'),
      ] + (property_exists($send, 'tag_mode') ? [
        // tag_mode and the messages.tags column arrive in the same migration (00008).
        // Commas are folded so the column stays a FIND_IN_SET list.
        'tags' => sendinblueStoredTags($tags),
      ] : []));
      if ($kind === 'passwordless') {
        $_SESSION['sendinblue_passwordless'] = [
          'message_id' => $messageId,
          'recipient_email' => $to,
          'sender_email' => $send->from,
          'accepted_at' => time(),
        ];
      }
    }
    return true;
  } catch (Exception $e) {
    return sendinblueFail("ERROR " . $e->getMessage(), $e->getMessage());
  }
}

// Logs a failed send and keeps the message for sendinblueLastError(). An empty
// user id lets logger() use the signed-in user, or 0 for a guest/cron send.
function sendinblueFail($logNote, $error = null){
  $GLOBALS['sendinblue_last_error'] = $error ?? $logNote;
  logger("", "sendinblue", $logNote);
  return false;
}

// Why the last sendinblue() call returned false, or null after a success.
function sendinblueLastError(){
  return $GLOBALS['sendinblue_last_error'] ?? null;
}

// Accepts a string or an array of strings (ints allowed). Returns unique, non-empty
// strings in the order given, otherwise as the caller wrote them. Anything else is
// ignored: casting an object to an array would expose its private properties.
function sendinblueNormalizeTags($tags){
  $clean = [];
  if (is_string($tags)) $tags = [$tags];
  if (!is_array($tags)) return $clean;
  foreach ($tags as $tag) {
    if (!is_string($tag) && !is_int($tag)) continue;
    $tag = trim(preg_replace('/[\x00-\x1F\x7F]/u', '', (string)$tag) ?? '');
    if ($tag !== '' && !in_array($tag, $clean, true)) $clean[] = $tag;
  }
  return $clean;
}

// Tags as stored in plg_sendinblue_messages.tags: a FIND_IN_SET list, so commas
// inside a tag become spaces. Whole tags only, up to the column's 255 characters.
function sendinblueStoredTags($tags){
  $stored = '';
  foreach ($tags as $tag) {
    $tag = trim(str_replace(',', ' ', $tag));
    if ($tag === '') continue;
    $next = $stored === '' ? $tag : $stored . ',' . $tag;
    if (mb_strlen($next) > 255) break;
    $stored = $next;
  }
  return $stored === '' ? null : $stored;
}

// Reduces admin-entered text to a short, safe tag: letters, digits, _ . -
// Returns '' when nothing usable is left.
function sendinblueCleanTag($value){
  return substr(preg_replace('/[^A-Za-z0-9_.-]/', '', (string)$value), 0, 64);
}

// Turns the file that called email() into a tag, e.g. forgot_password. Code run
// by eval() reports "file.php(12) : eval()'d code", so that suffix goes first.
function sendinblueTagFromFile($file){
  $file = preg_replace('/\(\d+\) : .*$/s', '', (string)$file);
  return sendinblueCleanTag(preg_replace('/\.php$/i', '', basename(str_replace('\\', '/', $file))));
}

// The response remains available to integrations without changing sendinblue()'s
// long-standing true/false contract. Never use this ID as proof of delivery.
function sendinblueLastMessageId(){
  return $GLOBALS['sendinblue_last_message_id'] ?? null;
}

// Refresh one stored message from Brevo. Calls are cached for 15 seconds so a
// passwordless page can poll without making one Brevo request per page view.
function sendinblueDeliveryStatus($messageId){
  global $db, $abs_us_root, $us_url_root;
  $row = $db->query('SELECT * FROM plg_sendinblue_messages WHERE message_id = ? LIMIT 1', [$messageId])->first();
  if (!$row) return null;
  if ($row->last_checked_at && strtotime($row->last_checked_at) > time() - 15) return $row;

  $brevoSettingsRow = $db->query('SELECT `key` FROM plg_sendinblue WHERE id = 1')->first();
  if (empty($brevoSettingsRow->key)) return $row;
  require_once $abs_us_root.$us_url_root.'usersc/plugins/sendinblue/vendor/autoload.php';
  $credentials = Brevo\Client\Configuration::getDefaultConfiguration()->setApiKey('api-key', $brevoSettingsRow->key);
  $api = new Brevo\Client\Api\TransactionalEmailsApi(new GuzzleHttp\Client(['connect_timeout' => 5, 'timeout' => 10]), $credentials);
  try {
    $report = $api->getEmailEventReport(100, 0, null, null, 90, null, null, null, $messageId, null, 'desc');
    $events = $report->getEvents() ?: [];
    $fields = ['last_checked_at' => date('Y-m-d H:i:s')];
    if ($events) {
      $latest = $events[0];
      $fields['status'] = (string)$latest->getEvent();
      $latestAt = strtotime((string)$latest->getDate());
      if ($latestAt !== false) $fields['last_event_at'] = date('Y-m-d H:i:s', $latestAt);
      $fields['last_reason'] = substr((string)$latest->getReason(), 0, 255);
      foreach ($events as $event) {
        if ($event->getEvent() === 'delivered') {
          $deliveredAt = strtotime((string)$event->getDate());
          if ($deliveredAt !== false) $fields['delivered_at'] = date('Y-m-d H:i:s', $deliveredAt);
          break;
        }
      }
    }
    $db->update('plg_sendinblue_messages', $row->id, $fields);
    return $db->query('SELECT * FROM plg_sendinblue_messages WHERE id = ?', [$row->id])->first();
  } catch (Exception $e) {
    // The UI keeps the last known event; an API outage is not a bounce. Stamping
    // last_checked_at reuses the 15-second cache so an outage is not retried on
    // every poll.
    $db->update('plg_sendinblue_messages', $row->id, ['last_checked_at' => date('Y-m-d H:i:s')]);
    return $row;
  }
}

