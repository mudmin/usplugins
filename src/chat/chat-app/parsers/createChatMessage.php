<?php
require_once ('../../../../../users/init.php');
require_once ($abs_us_root.$us_url_root."usersc/plugins/chat/chat-app/helpers.php");
$event_override = $event_id = 1; //compatibility

$resp = ['success' => false];

$msg = Input::get('msg');
$tz = date_default_timezone_get();
$eastern = new DateTimeZone($tz);
$date = new DateTime('NOW');
$utcDate = $date->setTimeZone(new DateTimeZone('UTC'));
$formattedDate = $utcDate->format('Y-m-d H:i:s');
if(!empty($msg)) {
  $data = [
    'created_at' => $formattedDate,
    'user_id' => $user->data()->id,
    'event_id' => $event_override,
    'msg' => $msg,
    'type' => 0
  ];
  $db = DB::getInstance();
  $result = $db->insert('plg_chat_messages',$data);
  $resp['success'] = $result;
}

jsonResponse($resp);
