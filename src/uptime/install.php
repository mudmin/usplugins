<?php
require_once("init.php");
//For security purposes, it is MANDATORY that this page be wrapped in the following
//if statement. This prevents remote execution of this code.
if (in_array($user->data()->id, $master_account)){


$db = DB::getInstance();
include "plugin_info.php";



//all actions should be performed here.
$db->query("CREATE TABLE plg_uptime (
  id int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
	site varchar(255),
  url text,
	last_check varchar(30),
	ustarget tinyint(1) default 1,
	usver varchar(30),
	phpver varchar(30),
	disabled tinyint(1) default 0,
  first_down varchar(30),
	notified_down varchar(30),
	created datetime
)");

$db->query("ALTER TABLE plg_uptime add column first_down datetime default ''");

$db->query("CREATE TABLE plg_uptime_downtime (
  id int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  site int(11),
	downtime decimal(11,2),
  ts timestamp
)");

$db->query("CREATE TABLE plg_uptime_settings (
  id int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
	check_every int(11) default 5,
	notify_every int(11) default 120
)");
if($check < 1){
	$db->insert('plg_uptime_settings',['id'=>1]);
}

$db->query("CREATE TABLE plg_uptime_notifications (
  id int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
	method varchar(255),
	target varchar(255),
	disabled tinyint(1) default 0
)");
$check = $db->query("SELECT * FROM plg_uptime_settings")->count();


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

//do you want to inject your plugin in the middle of core UserSpice pages?
$hooks = [];

//The format is $hooks['userspicepage.php']['position'] = path to filename to include
//Note you can include the same filename on multiple pages if that makes sense;
//postion options are post,body,form,bottom
//See documentation for more information
// $hooks['login.php']['body'] = 'hooks/loginbody.php';
// $hooks['login.php']['form'] = 'hooks/loginform.php';
// $hooks['login.php']['bottom'] = 'hooks/loginbottom.php';
// $hooks['login.php']['post'] = 'hooks/loginpost.php';
registerHooks($hooks,$plugin_name);

} //do not perform actions outside of this statement
