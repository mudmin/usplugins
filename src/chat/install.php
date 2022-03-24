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

// $dbName = Config::get('mysql/db');
// $db->query("ALTER DATABASE `{$dbName}` CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;");

$db->query("CREATE TABLE IF NOT EXISTS `plg_chat_messages` (
  `id` int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `created_at` datetime NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `event_id` int(11) UNSIGNED NOT NULL,
  `msg` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");



$db->query("ALTER TABLE `plg_chat_messages`
  ADD KEY IF NOT EXISTS `event_id` (`event_id`) USING BTREE");

$db->query("CREATE TABLE IF NOT EXISTS `plg_chat_sessions` (
  `id` int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NOT NULL,
  `event_id` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

$db->query("ALTER TABLE `plg_chat_sessions`
  ADD KEY IF NOT EXISTS `event_id` (`event_id`) USING BTREE");

$db->query("ALTER TABLE `plg_chat_sessions`
ADD KEY IF NOT EXISTS `user_id` (`user_id`) USING BTREE");

$db->query("ALTER TABLE `plg_chat_messages` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$db->query("ALTER TABLE `plg_chat_messages` MODIFY `msg` TEXT CHARSET utf8mb4");
$db->query("ALTER TABLE plg_chat_messages ADD COLUMN IF NOT EXISTS `type` tinyint(1) DEFAULT 0");
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
