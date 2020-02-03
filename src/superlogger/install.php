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

//settings table
$db->query("ALTER TABLE settings ADD COLUMN plg_sl_guest tinyint(1) DEFAULT 0");
$db->query("ALTER TABLE settings ADD COLUMN plg_sl_forms tinyint(1) DEFAULT 0");
$db->query("ALTER TABLE settings ADD COLUMN plg_sl_opt_out tinyint(1) DEFAULT 0");
$db->query("ALTER TABLE settings ADD COLUMN plg_sl_del_data tinyint(1) DEFAULT 0");
$db->query("ALTER TABLE settings ADD COLUMN plg_sl_join_warn tinyint(1) DEFAULT 0");

//users table
$db->query("ALTER TABLE users ADD COLUMN plg_sl_opt_out tinyint(1) DEFAULT 0");

//plg_sl_logs
$db->query("CREATE TABLE `plg_sl_logs` (
  `id` int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `page` varchar(255) NOT NULL,
  `get_data` text,
	`post_data` text,
	`ts` timestamp NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1");

$db->query("ALTER TABLE plg_sl_logs ADD COLUMN ip varchar(100)");

//do you want to inject your plugin in the middle of core UserSpice pages?
$hooks = [];

//The format is $hooks['userspicepage.php']['position'] = path to filename to include
//Note you can include the same filename on multiple pages if that makes sense;
//postion options are post,body,form,bottom
//See documentation for more information
$hooks['account.php']['body'] = 'hooks/accountbody.php';
$hooks['join.php']['body'] = 'hooks/joinbody.php';
// $hooks['login.php']['bottom'] = 'hooks/loginbottom.php';
// $hooks['login.php']['post'] = 'hooks/loginpost.php';
registerHooks($hooks,$plugin_name);

} //do not perform actions outside of this statement
