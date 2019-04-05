<?php
//Security and UserSpice Includes
$authorized = 0;
require_once '../../../../users/init.php';
$db = DB::getInstance();
if(!in_array($user->data()->id,$master_account)){
  logger($user->data()->id,"Errors","Attempted to access db manager");
  Redirect::to($us_url_root.'users/admin.php?err=Permission+denied');} //only allow master accounts to manage plugins!
  include "../plugin_info.php";
  if(pluginActive($plugin_name)){
    $authorized = 1;
  }

?>
<?php
if($authorized === 1){
  include('index.php');
}
