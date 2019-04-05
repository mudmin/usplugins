<?php
require_once("init.php");
//For security purposes, it is MANDATORY that this page be wrapped in the following
//if statement. This prevents remote execution of this code.
if (in_array($user->data()->id, $master_account)){


$db = DB::getInstance();
$plugin_name = "comments"; //change this for your plugin!
$plugin_name = strtolower($plugin_name);//you're welcome

$db->query("CREATE TABLE `us_comments_plugin` (
	`id` int(11) NOT NULL,
	`user` int(11),
	`page` int(11),
	`comment` text,
	`deleted` tinyint(1) DEFAULT '0',
	`approved` tinyint(1) DEFAULT '0',
	`timestamp` timestamp
) ENGINE=InnoDB DEFAULT CHARSET=latin1");

$db->query("ALTER TABLE `us_comments_plugin`
	ADD PRIMARY KEY (`id`)");

$db->query("ALTER TABLE `us_comments_plugin`
		MODIFY `id` int(11) NOT NULL AUTO_INCREMENT");

$db->query("ALTER TABLE `settings` ADD COLUMN cmntapprvd tinyint(1) DEFAULT '1'");

$db->query("ALTER TABLE `users` ADD COLUMN commentmod tinyint(1) DEFAULT '0'");

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



} //do not perform actions outside of this statement
