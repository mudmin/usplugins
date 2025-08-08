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

//The format is $hooks['userspicepage.php']['position'] = path to filename to include
//Note you can include the same filename on multiple pages if that makes sense;
//postion options are post,body,form,bottom
//See documentation for more information
// $hooks['login.php']['body'] = 'hooks/loginbody.php';
// $hooks['login.php']['form'] = 'hooks/loginform.php';
// $hooks['login.php']['bottom'] = 'hooks/loginbottom.php';
// $hooks['login.php']['post'] = 'hooks/loginpost.php';
registerHooks($hooks,$plugin_name);
$db->query("CREATE TABLE `forum_boards` (
  `id` int(11) UNSIGNED NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `board` varchar(255) NOT NULL,
	`cat` int(11),
	`to_read` varchar(255) DEFAULT 1,
	`to_write` varchar(255) DEFAULT 1,
  `disabled` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1");

$db->query("CREATE TABLE `forum_categories` (
  `id` int(11) UNSIGNED NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `category` varchar(255) NOT NULL,
  `deleted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1");

$db->query("CREATE TABLE `forum_threads` (
  `id` int(11) UNSIGNED NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `board` int(11) NOT NULL,
	`created_by` int(11) DEFAULT 1,
	`created_on` TIMESTAMP,
	`post` int(11),
  `deleted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1");

$db->query("CREATE TABLE `forum_messages` (
  `id` int(11) UNSIGNED NOT NULL  PRIMARY KEY AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
	`message` text,
	`thread` int(11) DEFAULT 1,
	`replying_to` int(11) DEFAULT 1,
	`user_id` int(11),
	`created_on` TIMESTAMP,
  `disabled` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1");

$check = $db->query("SELECT * FROM forum_categories")->count();
if($check < 1){
	$db->insert("forum_categories",['category'=>"Main"]);
}
$db->query("ALTER TABLE forum_boards ADD COLUMN descrip varchar(255)");
$db->query("ALTER TABLE forum_messages ADD COLUMN board int(11)");
$db->query("ALTER TABLE forum_boards ADD COLUMN last DATETIME");
$db->query("ALTER TABLE forum_threads ADD COLUMN last DATETIME");
$db->query("ALTER TABLE forum_threads ADD COLUMN title varchar(255)");
$db->query("ALTER TABLE forum_messages DROP COLUMN title");
$db->query("ALTER TABLE forum_messages DROP COLUMN last");
$db->query("ALTER TABLE forum_messages ADD COLUMN pinned tinyint(1) DEFAULT 0");
$db->query("ALTER TABLE forum_messages ADD COLUMN ip varchar(255)");
$db->query("ALTER TABLE settings ADD COLUMN forum_mod_perms varchar(255)");
$db->query("ALTER TABLE settings ADD COLUMN forum_mod_boot tinyint(1) DEFAULT 0");


} //do not perform actions outside of this statement
