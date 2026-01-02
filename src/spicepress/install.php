<?php
require_once("init.php");
//For security purposes, it is MANDATORY that this page be wrapped in the following
//if statement. This prevents remote execution of this code.
if (in_array($user->data()->id, $master_account)){


$db = DB::getInstance();
include "plugin_info.php";



//all actions should be performed here.
$check = $db->query("SELECT * FROM us_plugins WHERE plugin = ?",array($plugin_name))->count();
if($check > 0){
	err($plugin_name.' has already been installed!');
}else{
 $fields = array(
	 'plugin'=>$plugin_name,
	 'status'=>'installed',
 );
 $db->insert('us_plugins',$fields);
 if(!$db->error()) {
	 	err($plugin_name.' installed');
		logger($user->data()->id,"USPlugins",$plugin_name." installed");
 } else {
	 	err($plugin_name.' was not installed');
		logger($user->data()->id,"USPlugins","Failed to to install plugin, Error: ".$db->errorString());
 }
}

$db->query("CREATE TABLE plg_spicepress_sessions (
  id int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  user_id int(11) UNSIGNED,
  session varchar(255),
  expires datetime
)");

$db->query("CREATE TABLE plg_spicepress_settings (
  id int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  expires_hours int(11) default 48
)");

$db->query("CREATE TABLE plg_spicepress_authorized_urls (
  id int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  url text
)");

$check = $db->query("SELECT * FROM plg_spicepress_settings")->count();
if($check < 1){
	$fields = [
		"id"=>1,
	];
	$db->query("TRUNCATE TABLE plg_spicepress_settings");
	$db->insert("plg_spicepress_settings",$fields);

}

//do you want to inject your plugin in the middle of core UserSpice pages?
//visit https://userspice.com/plugin-hooks/ to get a better understanding of hooks
$hooks = [];

//The format is $hooks['userspicepage.php']['position'] = path to filename to include
//Note you can include the same filename on multiple pages if that makes sense;
//postion options are post,body,form,bottom
//See documentation for more information
$hooks['login.php']['pre'] = 'hooks/login_pre.php';
$hooks['login.php']['form'] = 'hooks/login_form.php';
$hooks['login.php']['post'] = 'hooks/login_post.php';
$hooks['loginSuccess']['body'] = 'hooks/login_success.php';
registerHooks($hooks,$plugin_name);

} //do not perform actions outside of this statement
