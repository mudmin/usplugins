<?php
//NOTE: This also serves as the reference file for how to do One Click Edit with UserSpice. See comments below.
  require_once '../../../../users/init.php';
  $db = DB::getInstance();
  if(!isset($user) || !$user->isLoggedIn()){
  die("You do not have permission to be here.");
  }
if(!empty($_POST)){
$msg = [];
$msg['response'] = "bad";
$check = $db->query("SELECT * FROM plg_links WHERE link_name = ?",[strtolower(Input::get('link_name'))])->count();
if($check < 1){
  $msg['response'] = "good";
}
echo json_encode($msg);
}
