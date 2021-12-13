<?php
require_once("init.php");
//For security purposes, it is MANDATORY that this page be wrapped in the following
//if statement. This prevents remote execution of this code.
if (in_array($user->data()->id, $master_account)){


$db = DB::getInstance();
include "plugin_info.php";



//all actions should be performed here.
$db->query("
CREATE TABLE `plg_download_files` (
	`id` int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
	`disabled` int(1)  DEFAULT '0',
	`location` text,
	`meta` text,
	`downloads` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

$db->query("
CREATE TABLE `plg_download_links` (
	`id` int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
	`file` int(11) UNSIGNED NOT NULL,
	`disabled` int(1)  DEFAULT '0',
	`no_restrictions` tinyint(1) DEFAULT '0',
	`user` int(11),
	`perms` varchar(255),
	`max` int(11) UNSIGNED,
	`used` int(11) UNSIGNED DEFAULT '0',
	`expires` DATETIME,
	`dlcode` varchar(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

$db->query("
CREATE TABLE `plg_download_logs` (
	`id` int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
	`link` int(11) UNSIGNED NOT NULL,
	`linkmode` int(11) UNSIGNED NOT NULL,
	`dlcode` varchar(255),
	`success` varchar(255),
	`message` varchar(255),
	`user` int(11) UNSIGNED NOT NULL,
	`ts` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`ip` VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

$db->query("
CREATE TABLE `plg_download_settings` (
	`id` int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
	`dlmode` int(11) UNSIGNED NOT NULL,
	`baseurl` varchar(255),
	`parser` varchar(255),
	`perms` varchar(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

$check = $db->query("SELECT * FROM plg_download_logs")->count();
if($check < 1){
	$db->insert("plg_download_settings",['dlmode'=>1,'parser'=>"dl/"]);
}
$plgSet = $db->query("SELECT * FROM plg_download_settings")->first();
mkdir($abs_us_root.$us_url_root.$plgSet->parser);
unlink($abs_us_root.$us_url_root.$plgSet->parser."index.php");
copy($abs_us_root.$us_url_root."usersc/plugins/downloads/assets/dl/index.php", $abs_us_root.$us_url_root.$plgSet->parser."index.php");
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

} //do not perform actions outside of this statement
