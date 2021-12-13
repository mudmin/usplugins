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

$db->query("CREATE TABLE `plg_points_settings` (
  `id` int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `term` varchar(255),
  `show_acct_bal` tinyint(1) DEFAULT 1,
	`allow_arb_trans` tinyint(1) DEFAULT 1,
	`show_trans_acct` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$db->query("CREATE TABLE `plg_points_trans` (
  `id` int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `trans_from` int(11),
  `trans_to` int(11),
	`ts` datetime,
	`reason` varchar(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$db->query("ALTER TABLE plg_points_trans ADD column points varchar(255)");
$db->query("ALTER TABLE plg_points_settings ADD column term_sing varchar(255)");
$db->query("ALTER TABLE users ADD column plg_points varchar(255) DEFAULT 0");

$c = $db->query("SELECT * FROM plg_points_settings")->count();
if($c < 1){
	$fields = array(
		'term'=>"points",
		'term_sing'=>"point",
		'show_acct_bal'=>1,
		'allow_arb_trans'=>1,
		'show_trans_acct'=>1,
	);
	$db->insert('plg_points_settings',$fields);
}
//do you want to inject your plugin in the middle of core UserSpice pages?
$hooks = [];

//The format is $hooks['userspicepage.php']['position'] = path to filename to include
//Note you can include the same filename on multiple pages if that makes sense;
//postion options are post,body,form,bottom
//See documentation for more information
$hooks['account.php']['pre'] = 'hooks/accountpre.php';
$hooks['account.php']['body'] = 'hooks/accountbody.php';
$hooks['account.php']['bottom'] = 'hooks/accountbottom.php';
$hooks['admin.php?view=users']['body'] = 'hooks/uman_thead.php';
$hooks['admin.php?view=users']['bottom'] = 'hooks/uman_tbody.php';
registerHooks($hooks,$plugin_name);

} //do not perform actions outside of this statement
