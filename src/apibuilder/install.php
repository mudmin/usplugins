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
		$db->query("ALTER TABLE users ADD COLUMN apibld_key varchar(255)");
		$db->query("ALTER TABLE users ADD COLUMN apibld_ip varchar(255)");
		// $db->query("ALTER TABLE users ADD COLUMN apibld_type tinyint(1)");
		$db->query("ALTER TABLE users ADD COLUMN apibld_blocked tinyint(1) DEFAULT 0");

		$db->query("CREATE TABLE `plg_api_settings` (
			`id` int(11) NOT NULL,
			`api_auth_type` tinyint(1) DEFAULT 1,
			`api_fails` int(11) DEFAULT 10,
			`force_ssl` tinyint(1) DEFAULT 0,
			`disabled` tinyint(1) DEFAULT 0
		) ENGINE=InnoDB DEFAULT CHARSET=latin1");

		$db->query("ALTER TABLE `plg_api_settings`	ADD PRIMARY KEY (`id`)");
		$db->query("ALTER TABLE `plg_api_settings` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT");
		$db->insert('plg_api_settings',['id'=>1]);

		$db->query("CREATE TABLE `plg_api_pool` (
			`id` int(11) NOT NULL,
			`api_key` varchar(255),
			`ip` varchar(255),
			`user_id` int(11) DEFAULT 0,
			`descrip` varchar(255),
			`blocked` tinyint(1) DEFAULT 0,
			`updated` TIMESTAMP
		) ENGINE=InnoDB DEFAULT CHARSET=latin1");
		$db->query("ALTER TABLE `plg_api_pool`	ADD PRIMARY KEY (`id`)");
		$db->query("ALTER TABLE `plg_api_pool` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT");

		$db->query("CREATE TABLE `plg_api_fails` (
			`id` int(11) NOT NULL,
			`ip` varchar(255),
			`attempts` int(11),
			`blocked` tinyint(1) DEFAULT 0,
			`updated` TIMESTAMP
		) ENGINE=InnoDB DEFAULT CHARSET=latin1");
		$db->query("ALTER TABLE `plg_api_fails`	ADD PRIMARY KEY (`id`)");
		$db->query("ALTER TABLE `plg_api_fails` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT");



 } else {
	 	err($plugin_name.' was not installed');
		logger($user->data()->id,"USPlugins","Failed to to install plugin, Error: ".$db->errorString());
 }
}

//do you want to inject your plugin in the middle of core UserSpice pages?
$hooks = [];
$hooks['join.php']['post'] = 'hooks/joinpost.php';
$hooks['account.php']['bottom'] = 'hooks/accountbottom.php';
registerHooks($hooks,$plugin_name);

} //do not perform actions outside of this statement
