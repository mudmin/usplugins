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


$db->query("CREATE TABLE `plg_watchdog_settings` (
	`id` int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
	`last_wd` DATETIME,
	`wd_time` int(11) DEFAULT 120,
	`every_page` tinyint(1) default '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1");

$db->query("CREATE TABLE `plg_watchdogs` (
	`id` int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
	`wd_created_by` int(11),
	`wd_created_on` datetime,
	`wd_target_type` varchar(25),
	`wd_targets` varchar(255),
	`wd_func` varchar(255),
	`wd_args` text,
	`wd_timeout` datetime,
	`wd_notes` text,
	`wd_times_triggered` int(11) UNSIGNED NOT NULL default '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1");

$check = $db->query("SELECT * FROM plg_watchdog_settings")->count();
if($check < 1){
	$db->insert("plg_watchdog_settings",["wd_time"=>120,"last_wd"=>"2021-01-01 00:00:00"]);
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
