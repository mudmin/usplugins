<?php
require_once '../../../../users/init.php';
$db = DB::getInstance();
$settings = $db->query("SELECT * FROM settings")->first();

$id = Input::get('notif');
if(is_numeric($id)){
$check = $db->query("SELECT id FROM notifications WHERE id = ? AND user_id = ?",[$id,$user->data()->id])->count();
if($check > 0){
  $db->update('notifications',$id,['is_archived'=>1]);
}
}
