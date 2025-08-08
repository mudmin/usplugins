<?php
//NOTE: This also serves as the reference file for how to do One Click Edit with UserSpice. See comments below.
  require_once '../init.php';
  $db = DB::getInstance();
  $settings = $db->query("SELECT * FROM settings")->first();
  if(!hasPerm([2],$user->data()->id)){
  die("You do not have permission to be here.");
  }
$msg = [];

$type = Input::get('type');
$field = Input::get('field');
$value = Input::get('value');
$desc = Input::get('desc');
$token = Input::get('token');


if($type == 'resetPW'){
  $db->query("UPDATE users SET force_pr = 1");
  $msg['success'] = "true";
  $msg['msg'] = "Force password reset initiated!";
}
if($type == 'toggle'){
  //check for tomfoolery and make sure the old option was numeric
  if(is_numeric($settings->$field)){
    if($value == 'true'){
      $value = 1;
    }else{
      $value = 0;
    }
    $db->update('settings',1,[$field=>$value]);
    $msg['success'] = "true";
    $msg['msg'] = $desc." Updated!";
  }else{
    $msg['success'] = "false";
    $msg['msg'] = $desc." Not Updated!";
  }
}

if($type == 'num'){
  //check for tomfoolery and make sure the old option was numeric
  if(is_numeric($settings->$field)){
    $db->update('settings',1,[$field=>$value]);
    $msg['success'] = "true";
    $msg['msg'] = $desc." Updated!";
  }else{
    $msg['success'] = "false";
    $msg['msg'] = $desc." Not Updated!";
  }
}

if($type == 'txt'){
    $db->update('settings',1,[$field=>$value]);
    $msg['success'] = "true";
    $msg['msg'] = $desc." Updated!";
  }

echo json_encode($msg);
