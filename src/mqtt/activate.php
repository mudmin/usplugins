<?php
require_once("init.php");

//For security purposes, it is MANDATORY that this page be wrapped in the following
//if statement. This prevents remote execution of this code.
if (in_array($user->data()->id, $master_account)){
$db = DB::getInstance();
include "plugin_info.php";

//all actions should be performed here.
$checkQ = $db->query("SELECT * FROM us_plugins WHERE plugin = ?",array($plugin_name));
$checkC = $checkQ->count();
if($checkC > 0){
	$check = $checkQ->first();
	$fields = array(
	 'status'=>'active',
 );
$check = $checkQ->first();
$db->update('us_plugins',$check->id,$fields);

//These queries  can be run again because they won't really break anything if the plugin has been uninstalled and reinstalled.

 		//Create the MQTT table if it doesn't exist
 		$db->query("CREATE TABLE `mqtt` (
 			`id` int(11) NOT NULL,
 			`server` varchar(255),
 			`port` int(11),
 			`username` varchar(255),
 			`password` varchar(255),
 			`nickname` varchar(255)
 		) ENGINE=InnoDB DEFAULT CHARSET=latin1");

 		$db->query("ALTER TABLE `mqtt`
 			ADD PRIMARY KEY (`id`)");

 			$db->query("ALTER TABLE `mqtt`
 				MODIFY `id` int(11) NOT NULL AUTO_INCREMENT");
 		$check = $db->query("SELECT id FROM mqtt")->count(); //check for existing rows
 		if($check < 1){ //no example in the db, so add one
 			$fields = array(
 				'server'=>'192.168.1.222',
 				'port'=>'1883',
 				'nickname'=>'Raspberry Pi 2'
 			);
 			$db->insert('mqtt',$fields);
 		}

 $db->update('us_plugins',$check->id,$fields);
 if(!$db->error()) {
	 err($plugin_name.' activated');
	 logger($user->data()->id,"USPlugins", $plugin_name. " activated");
 } else {
	 err($plugin_name.' was not activated');
	 logger($user->data()->id,"USPlugins-MQTT","Failed to reactivate Plugin, Error: ".$db->errorString());
 }
}else{
	err($plugin_name.' is not found! Has it been installed?');
	logger($user->data()->id,"USPlugins", $plugin_name. " activation error - possibly not installed.");

}
//you will probably do more actions than just the db


} //do not perform actions outside of this statement
