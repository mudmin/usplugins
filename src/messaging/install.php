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

$db->query("CREATE TABLE `plg_msg` (
	`id` int(11) UNSIGNED NOT NULL,
	`user_to` int(11) NOT NULL,
	`msg_id` int(11) NOT NULL,
	`msg_read` tinyint(1) DEFAULT 0,
	`msg_read_on` datetime DEFAULT NULL,
	`deleted` tinyint(1) DEFAULT 0
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
  
  ALTER TABLE `plg_msg`
	ADD PRIMARY KEY (`id`);

  ALTER TABLE `plg_msg`
	MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;
");

$db->query("CREATE TABLE `plg_msg_messages` (
	`id` int(11) UNSIGNED NOT NULL,
	`user_from` int(11) NOT NULL,
	`title` varchar(255) DEFAULT NULL,
	`msg` text DEFAULT NULL,
	`msg_type` int(11) DEFAULT NULL,
	`msg_class` varchar(255) DEFAULT NULL,
	`recipients` int(11) DEFAULT NULL,
	`msg_sent_on` datetime DEFAULT NULL,
	`msg_expires_on` datetime DEFAULT NULL,
	`expires` tinyint(1) DEFAULT 0,
	`expired` tinyint(1) DEFAULT 0,
	`send_method` varchar(50) DEFAULT NULL,
	`sent_by` int(11) DEFAULT 0
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
 
  ALTER TABLE `plg_msg_messages`
	ADD PRIMARY KEY (`id`);

  ALTER TABLE `plg_msg_messages`
	MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;
");


//do you want to inject your plugin in the middle of core UserSpice pages?
//visit https://userspice.com/plugin-hooks/ to get a better understanding of hooks
$hooks = [];

//The format is $hooks['userspicepage.php']['position'] = path to filename to include
//Note you can include the same filename on multiple pages if that makes sense;
//postion options are post,body,form,bottom
//See documentation for more information
// $hooks['login.php']['body'] = 'hooks/loginbody.php';

registerHooks($hooks,$plugin_name);

} //do not perform actions outside of this statement
