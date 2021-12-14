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


$db->query("CREATE TABLE `plg_passwordless_settings` (
  `id` int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `subject` varchar(255),
  `top` text,
  `bottom` text,
	`hidepw` tinyint(1) default 0,
	`link` varchar(255),
	timeout int(11) default 60
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$db->query("ALTER TABLE plg_passwordless_settings ADD column timeout int(11) default 60");
$db->query("ALTER TABLE users ADD COLUMN pwl varchar(255)");
$db->query("ALTER TABLE users ADD COLUMN pwl_to datetime");


$c = $db->query("SELECT * FROM plg_passwordless_settings")->count();
if($c < 1){
	$db->query("TRUNCATE table plg_passwordless_settings");
	$fields = [
		'subject'=>"The login link you requested",
		'top'=>"Please click the link to login",
		'bottom'=>"-The Team",
		'link'=>"usersc/plugins/passwordless/files/pwl.php",
	];
	$db->insert("plg_passwordless_settings",$fields);
}

//do you want to inject your plugin in the middle of core UserSpice pages?
//visit https://userspice.com/plugin-hooks/ to get a better understanding of hooks
$hooks = [];

//The format is $hooks['userspicepage.php']['position'] = path to filename to include
//Note you can include the same filename on multiple pages if that makes sense;
//postion options are post,body,form,bottom
//See documentation for more information
$hooks['login.php']['body'] = 'hooks/loginbody.php';

registerHooks($hooks,$plugin_name);

} //do not perform actions outside of this statement
