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

 //Create the MQTT table if it doesn't exist
 $db->query("CREATE TABLE `userman_settings` (
	 `id` int(11) NOT NULL,
	 `create` int(11) DEFAULT '0',
	 `delete` int(11) DEFAULT '0',
	 `perms` int(11) DEFAULT '0',
	 `passwords` int(11) DEFAULT '0',
	 `info` int(11) DEFAULT '0'
 ) ENGINE=InnoDB DEFAULT CHARSET=latin1");

 $db->query("ALTER TABLE `userman_settings`
	 ADD PRIMARY KEY (`id`)");

 $db->query("ALTER TABLE `userman_settings`
		 MODIFY `id` int(11) NOT NULL AUTO_INCREMENT");

 $count = $db->query("SELECT id FROM userman_settings")->count();
 if($count < 1){
	 $db->insert('userman_settings',['id'=>1]);
 }

 $db->query("ALTER TABLE `users`
	ADD COLUMN userman tinyint(1) DEFAULT 0");

 if(!$db->error()) {
	 	err($plugin_name.' installed');
		logger($user->data()->id,"USPlugins",$plugin_name." installed");
 } else {
	 	err($plugin_name.' was not installed');
		logger($user->data()->id,"USPlugins","Failed to to install plugin, Error: ".$db->errorString());
 }
}



} //do not perform actions outside of this statement
