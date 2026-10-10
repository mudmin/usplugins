<?php
require_once '../../../../../users/init.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');

$reply = static function ($state, $message, $http = 200) {
  http_response_code($http);
  echo json_encode(['state' => $state, 'message' => $message]);
  exit;
};
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Token::check(Input::get('csrf'))) {
  $reply('unavailable', 'Delivery status is unavailable. Reload this page and try again.', 403);
}
if (!pluginActive('sendinblue', true)) {
  $reply('unavailable', 'Delivery status is unavailable.', 404);
}
$mailSettingsRow = $db->query('SELECT public_delivery_status FROM plg_sendinblue WHERE id = 1')->first();
if (empty($mailSettingsRow->public_delivery_status)) {
  $reply('unavailable', 'Delivery status is turned off.', 404);
}
$tracking = $_SESSION['sendinblue_passwordless'] ?? null;
if (!is_array($tracking) || empty($tracking['message_id']) ||
    empty($tracking['recipient_email']) ||
    ($tracking['accepted_at'] ?? 0) < time() - 3600) {
  $reply('unavailable', 'No delivery result is available for this sign-in request.');
}
$row = $db->query(
  'SELECT id FROM plg_sendinblue_messages WHERE message_id = ? AND recipient_email = ? AND kind = ? LIMIT 1',
  [$tracking['message_id'], $tracking['recipient_email'], 'passwordless']
)->first();
if (!$row) {
  $reply('unavailable', 'No delivery result is available for this sign-in request.');
}
$event = sendinblueDeliveryStatus($tracking['message_id']);
if (!$event) {
  $reply('unavailable', 'Delivery status is temporarily unavailable.');
}
$status = strtolower((string)$event->status);
if ($status === 'spam') {
  $reply('problem', 'The Mail Server recorded a spam complaint for this address. Please contact support.');
}
if (in_array($status, ['bounces', 'hardbounces', 'softbounces', 'blocked', 'invalid', 'error'], true)) {
  $reply('problem', 'The Mail Server reports a delivery problem. Give your IT team the message ID shown in the help page.');
}
if ($status === 'deferred') {
  $reply('waiting', "The recipient's mail server asked for a retry. Check again shortly.");
}
if (!empty($event->delivered_at)) {
  $reply('delivered', 'The Mail Server reports that your mail system accepted the message. Check Junk, Other, quarantine, and your mail rules.');
}
$reply('waiting', 'The Mail Server accepted the send request. Waiting for a delivery result.');
