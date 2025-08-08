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

$db->query("ALTER TABLE users ADD COLUMN plg_mem_level int(11) DEFAULT 0");
$db->query("ALTER TABLE users ADD COLUMN plg_mem_cost int(11) DEFAULT 0");
$db->query("ALTER TABLE users ADD COLUMN plg_mem_cred dec(11,2) DEFAULT 0");
$db->query("ALTER TABLE users ADD COLUMN plg_mem_exp date");
$db->query("ALTER TABLE users ADD COLUMN plg_mem_expired tinyint(1) default 1");

$db->query("CREATE TABLE `plg_mem_plans` (
  `id` int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `plan_name` varchar(255),
  `plan_desc` text,
  `perms_added` varchar(255),
	`icon` varchar(255),
	`ordering` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1");

$db->query("CREATE TABLE `plg_mem_cost` (
  `id` int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `plan` varchar(255),
  `days` int(11),
  `cost` DEC(11,2),
	`icon` varchar(255)
) ENGINE=InnoDB DEFAULT CHARSET=latin1");

$db->query("CREATE TABLE `plg_mem_settings` (
  `id` int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `sym` varchar(1) DEFAULT '$' ,
  `cur` varchar(3) DEFAULT 'usd'
) ENGINE=InnoDB DEFAULT CHARSET=latin1");
$check = $db->query("SELECT * FROM plg_mem_settings")->count();
if($check < 1){
	$fields = array('sym'=>'$','cur'=>'usd');
	$db->insert('plg_mem_settings',$fields);
}
$db->query("ALTER TABLE plg_mem_settings ADD COLUMN payments tinyint(1) default 0");
$db->query("ALTER TABLE plg_mem_cost ADD COLUMN disabled tinyint(1) default 0");
$db->query("ALTER TABLE plg_mem_plans ADD COLUMN disabled tinyint(1) default 0");
$db->query("ALTER TABLE plg_mem_plans ADD COLUMN script_add varchar(255)");
$db->query("ALTER TABLE plg_mem_plans ADD COLUMN script_remove varchar(255)");
$db->query("ALTER TABLE plg_mem_cost ADD COLUMN descrip varchar(255)");

//do you want to inject your plugin in the middle of core UserSpice pages?
$hooks = [];
$hooks['account.php']['bottom'] = 'hooks/accountbottom.php';
$hooks['account.php']['body'] = 'hooks/accountbody.php';
registerHooks($hooks,$plugin_name);

} //do not perform actions outside of this statement
