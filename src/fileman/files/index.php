<?php
//{"lang":"","auth_pass":"d41d8cd98f00b204e9800998ecf8427e","error_reporting":1}
$authorized = 0;
require_once '../../../../users/init.php';
$db = DB::getInstance();
if(!in_array($user->data()->id,$master_account)){
  logger($user->data()->id,"Errors","Attempted to access file manager");
  Redirect::to($us_url_root.'users/admin.php?err=Permission+denied');} //only allow master accounts to manage plugins!
  $check = $db->query("SELECT id FROM us_plugins WHERE plugin = ? and status = ?",array("fileman","active"))->count();
  if($check != 1) {
    logger($user->data()->id,"Errors","Attempted to access disabled file manager");
    Redirect::to($us_url_root.'users/admin.php?err=Plugin+is+disabled');
  }else{
    $authorized = 1;
  }

?>
<?php
if($authorized === 1){
  include('fileman.php');
}
