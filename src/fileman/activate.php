<?php
require_once("init.php");

//For security purposes, it is MANDATORY that this page be wrapped in the following
//if statement. This prevents remote execution of this code.
if (in_array($user->data()->id, $master_account)){
$db = DB::getInstance();
$plugin_name = "fileman"; //change this for your plugin!
$plugin_name = strtolower($plugin_name);//you're welcome


//all actions should be performed here.
$checkQ = $db->query("SELECT * FROM us_plugins WHERE plugin = ?",array($plugin_name));
$checkC = $checkQ->count();
if($checkC > 0){
	$check = $checkQ->first();
	$fields = array(
	 'status'=>'active',
 );



 $db->update('us_plugins',$check->id,$fields);
 if(!$db->error()) {
	 err($plugin_name.' activated');
	 logger($user->data()->id,"USPlugins", $plugin_name. " activated");
 } else {
	 err($plugin_name.' was not activated');
	 logger($user->data()->id,"USPlugins-fileman","Failed to reactivate Plugin, Error: ".$db->errorString());
 }
}else{
	err($plugin_name.' is not found! Has it been installed?');
	logger($user->data()->id,"USPlugins", $plugin_name. " activation error - possibly not installed.");

}
//you will probably do more actions than just the db


} //do not perform actions outside of this statement
