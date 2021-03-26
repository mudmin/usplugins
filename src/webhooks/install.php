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

//do you want to inject your plugin in the middle of core UserSpice pages?
$hooks = [];
$db->query("CREATE TABLE `plg_webhook_activity_logs` (
	`id` int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
	`hook` int(11) UNSIGNED NOT NULL,
	`ip`	varchar(50),
	`ts` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	`subject`	varchar(50),
	`log` varchar(255)
) ENGINE=InnoDB DEFAULT CHARSET=latin1");

$db->query("CREATE TABLE `plg_webhook_data_logs` (
	`id` int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
	`hook` int(11) UNSIGNED NOT NULL,
	`ip`	varchar(50),
	`ts` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	`method`	varchar(20),
	`log` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1");

$db->query("CREATE TABLE `plg_webhooks` (
	`id` int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
	`hook` varchar(255),
	`auth`	varchar(50),
	`action_type` varchar(50),
	`action` varchar(255),
	`twofa_key` varchar(255),
	`twofa_value` varchar(255),
	`log` tinyint(1) default 0,
	`disabled` tinyint(1) default 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1");

registerHooks($hooks,$plugin_name);

} //do not perform actions outside of this statement
