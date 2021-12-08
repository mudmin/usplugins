<?php
  require_once '../../../../users/init.php';
  $db = DB::getInstance();
  if(!in_array($user->data()->id,$master_account)){
  die("You do not have permission to be here.");
  }
$msg = [];
$settings = $db->query("SELECT * FROM plg_api_settings")->first();
$type = Input::get('type');
$field = Input::get('field');
$value = Input::get('value');
$desc = Input::get('desc');
$token = Input::get('token');



if($type == 'toggle'){
  //check for tomfoolery and make sure the old option was numeric
  if(is_numeric($settings->$field)){
    if($value == 'true'){
      $value = 1;
    }else{
      $value = 0;
    }
    $db->update('plg_api_settings',1,[$field=>$value]);
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
    $db->update('plg_api_settings',1,[$field=>$value]);
    $msg['success'] = "true";
    $msg['msg'] = $desc." Updated!";
  }else{
    $msg['success'] = "false";
    $msg['msg'] = $desc." Not Updated!";
  }
}

if($type == 'apimode'){
  if(is_numeric($value) && $value > 0 && $value < 6){
    $db->update('plg_api_settings',1,[$field=>$value]);
    $msg['success'] = "true";
    $msg['msg'] = $desc." Updated!";
    $msg['mode'] = $value;
  }else{
    $msg['success'] = "false";
    $msg['msg'] = $desc." Not Updated! Invalid Mode";
  }
}

if($type == 'txt'){
    $db->update('plg_api_settings',1,[$field=>$value]);
    $msg['success'] = "true";
    $msg['msg'] = $desc." Updated!";
  }

echo json_encode($msg);
