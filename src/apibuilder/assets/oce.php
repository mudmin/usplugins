<?php
require_once '../../../../users/init.php';
$db = DB::getInstance();
if(!in_array($user->data()->id,$master_account)){
die("You do not have permission to be here.");
}

  $id = Input::get('id');
  $field = Input::get('field');
  $value = Input::get('value');

if($field == 'user_id' && ($value < 0 || !is_numeric($value))){
  $msg = "User ID must be a whole number!";
    echo json_encode($msg);
    exit;
}
  $db->update('plg_api_pool',$id,[$field=>$value]);
  $desc = ucfirst($field);
  $msg = $desc." Updated!";

  echo $msg;
  exit;
?>
