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

$db->query("CREATE TABLE `plg_links` (
	`id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
	`link_name` varchar(255) DEFAULT 2,
	`link` text,
	`user` int(11),
	`logged_in` tinyint(1),
	`clicks` int(11)
) ENGINE=InnoDB DEFAULT CHARSET=utf8");

$db->query("CREATE TABLE `plg_links_clicks` (
	`id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
	`link` int(11),
	`user` int(11),
	`ip`	varchar(255),
	`ts` timestamp
) ENGINE=InnoDB DEFAULT CHARSET=utf8");

$db->query("CREATE TABLE `plg_links_settings` (
	`id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
	`perms` varchar(255) DEFAULT 2,
	`all_internal` tinyint(1) DEFAULT 1,
	`display_style` tinyint(1) DEFAULT 1,
	`non_admins_see_all` tinyint(1) DEFAULT 0,
	`parser_location` varchar(255) DEFAULT 'l/index.php'
) ENGINE=InnoDB DEFAULT CHARSET=utf8");

$check = $db->query("SELECT * FROM plg_links_settings")->count();
if($check < 1){
	$db->query("TRUNCATE TABLE plg_links_settings");
	$db->insert("plg_links_settings",['id'=>1]);
}

$db->query("ALTER TABLE `plg_links_settings` ADD COLUMN allow_login_choice tinyint(1) DEFAULT 0");
$db->query("ALTER TABLE `plg_links_settings` ADD COLUMN base_url varchar(255)");
mkdir($abs_us_root.$us_url_root."l", 0755, true);
copy($abs_us_root.$us_url_root."usersc/plugins/links/files/index.php", $abs_us_root.$us_url_root."l/index.php");

//do you want to inject your plugin in the middle of core UserSpice pages?
$hooks = [];

//The format is $hooks['userspicepage.php']['position'] = path to filename to include
//Note you can include the same filename on multiple pages if that makes sense;
//postion options are post,body,form,bottom
//See documentation for more information
$hooks['account.php']['bottom'] = 'hooks/account.php';

registerHooks($hooks,$plugin_name);

} //do not perform actions outside of this statement
