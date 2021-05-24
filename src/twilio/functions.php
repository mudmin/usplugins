<?php
require __DIR__ . '/vendor/autoload.php';
use Twilio\Rest\Client;

if(!function_exists("twilsms")){
  function twilsms($message,$to,$from = "",$log = false){
    $db = DB::getInstance();
    global $user;
    $response = [];
    $twil = $db->query("SELECT * from plg_twilio_settings")->first();
    if($from == ""){
      $from = $twil->primary;
    }

    $client = new Client($twil->sid, $twil->token);
    try {
      $client->messages->create(
          $to,
          array(
              'from' => $from,
              'body' => $message,
          )
      );
      $response['success'] = true;
      $status = "Success";
      $response['message'] = $status;
    } catch (\Exception $e) {
      $status = "Failed:" . $e->getMessage();
      $response['success'] = false;
      $response['message'] = $status;
    }

    if($log == true){
      if(isset($user) && $user->isLoggedIn()){
        $uid = $user->data()->id;
      }else{
        $uid = 1;
      }
      logger($uid,"twilio","$status $message : sent from $from to $to");
    }
    return $response;
  }
}

if(!function_exists("twilio")){
  function twilio($data){
    $data = preg_replace('/[^0-9]/', "", $data);
    $data = "+".$data;
    return $data;
  }
}
