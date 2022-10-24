<?php
//options takes an array where you can specify a template id and as many optional params as you want
// $options = [
// 	'template'=>1,
// 	'params' => [
// 		'fname' => $user->data()->fname,
//    'lname' => $user->data()->lname,
// 	],
// ];

// $send = sendinblue("mudmin@gmail.com","Sendinblue Test","This is the message","",$options);
function sendinblue($to,$subject,$body,$to_name = "", $options = []){
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
  if(isset($options['from'])){
    $send->from = $options['from'];
  }
  if(isset($options['from_name'])){
    $send->from_name = $options['from_name'];
  }
  
  $sendSmtpEmail = new \SendinBlue\Client\Model\SendSmtpEmail([
       'subject' => $subject,
       'sender' => ['name' => $send->from_name, 'email' => $send->from],
       'replyTo' => ['name' => $send->from_name, 'email' => $send->reply],
       'to' => [[ 'name' => $to_name, 'email' => $to]],
       'htmlContent' => $body
  ]);
  if(isset($options['template'])){
      $sendSmtpEmail['templateId'] = $options['template'];
  }

  if(isset($options['params'])){
      $sendSmtpEmail['params'] = $options['params'];
  }


  try {
      $result = $apiInstance->sendTransacEmail($sendSmtpEmail);
      return $result;
  } catch (Exception $e) {
      $msg = $e->getMessage();;
      logger($user->data()->id,"sendinblue","ERROR $msg");
      return $e->getMessage();
  }
}
