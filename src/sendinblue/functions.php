<?php
function sendinblue($to,$subject,$body,$to_name = ""){
  global $user,$us_url_root,$abs_us_root;

  if($to == "" || $subject == "" || $body == ""){
    logger($user->data()->id,"sendinblue","FAILED: Attempted to send without all required fields");
    return "All fields are required";
  }

  $db = DB::getInstance();
  $send = $db->query("SELECT * FROM plg_sendinblue")->first();
  if($to_name == ""){ $to_name = $to; }

  require_once($abs_us_root.$us_url_root.'usersc/plugins/sendinblue/vendor/autoload.php');

  $credentials = SendinBlue\Client\Configuration::getDefaultConfiguration()->setApiKey('api-key', $send->key);
  $apiInstance = new SendinBlue\Client\Api\TransactionalEmailsApi(new GuzzleHttp\Client(),$credentials);

  $sendSmtpEmail = new \SendinBlue\Client\Model\SendSmtpEmail([
       'subject' => $subject,
       'sender' => ['name' => $send->from_name, 'email' => $send->from],
       'replyTo' => ['name' => $send->from_name, 'email' => $send->reply],
       'to' => [[ 'name' => $to_name, 'email' => $to]],
       'htmlContent' => $body
  ]);

  try {
      $result = $apiInstance->sendTransacEmail($sendSmtpEmail);
      return $result;
  } catch (Exception $e) {
      $msg = $e->getMessage();;
      logger($user->data()->id,"sendinblue","ERROR $msg");
      return $e->getMessage();
  }
}
