<?php
//{"lang":"","auth_pass":"d41d8cd98f00b204e9800998ecf8427e","error_reporting":1}
$authorized = 0;
require_once '../../../../users/init.php';
$db = DB::getInstance();
if(!in_array($user->data()->id,$master_account)){
  logger($user->data()->id,"Errors","Attempted to access file manager");
  Redirect::to($us_url_root.'users/admin.php?err=Permission+denied');} //only allow master accounts to manage plugins!
  include "../plugin_info.php";
  if(pluginActive($plugin_name)){
    $authorized = 1;
  }

if($authorized === 1){
  include('fileman.php');
}
