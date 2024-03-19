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
  global $user, $us_url_root, $abs_us_root;

  if($to == "" || $subject == "" || $body == ""){
    logger($user->data()->id,"sendinblue","FAILED: Attempted to send without all required fields");
    return "All fields are required";
  }
  $to = rawurldecode($to);
  global $db;
  $send = $db->query("SELECT * FROM plg_sendinblue")->first();
  if($to_name == ""){ $to_name = $to; }

  require_once($abs_us_root.$us_url_root.'usersc/plugins/sendinblue/vendor/autoload.php');

  $credentials = Brevo\Client\Configuration::getDefaultConfiguration()->setApiKey('api-key', $send->key);
  $apiInstance = new Brevo\Client\Api\TransactionalEmailsApi(new GuzzleHttp\Client(), $credentials);
  if(isset($options['from'])){
    $send->from = $options['from'];
  }
  if(isset($options['from_name'])){
    $send->from_name = $options['from_name'];
  }
  
  $sendSmtpEmail = new Brevo\Client\Model\SendSmtpEmail([
    'subject' => $subject,
    'sender' => ['name' => $send->from_name, 'email' => $send->from],
    'replyTo' => ['name' => $send->from_name, 'email' => $send->reply],
    'to' => [['name' => $to_name, 'email' => $to]],
    'htmlContent' => $body
  ]);

  if(isset($options['template'])){
    $sendSmtpEmail['templateId'] = $options['template'];
  }

  if(isset($options['params'])){
    $sendSmtpEmail['params'] = $options['params'];
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
    return true;
  } catch (Exception $e) {
    $msg = $e->getMessage();
    logger($user->data()->id,"sendinblue","ERROR $msg");
    return $e->getMessage();
  }
}

