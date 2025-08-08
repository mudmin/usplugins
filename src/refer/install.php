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

$db->query("CREATE TABLE `plg_refer_settings` (
  `id` int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `only_refer` tinyint(1) DEFAULT 0,
  `show_acct` tinyint(1) DEFAULT 1,
	`allow_un` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
			

$db->query("ALTER TABLE users ADD column plg_ref varchar(255)");
$db->query("ALTER TABLE users ADD column plg_ref_by int(11)");

$users = $db->query("SELECT id FROM users")->results();
foreach($users as $u){
	$db->update('users',$u->id,['plg_ref_by'=>1]);
}
$c = $db->query("SELECT * FROM plg_refer_settings")->count();
if($c < 1){
	$fields = array(
		'only_refer'=>0,
		'show_acct'=>0,
		'allow_un'=>0
	);
	$db->insert('plg_refer_settings',$fields);
}

//do you want to inject your plugin in the middle of core UserSpice pages?
$hooks = [];

//The format is $hooks['userspicepage.php']['position'] = path to filename to include
//Note you can include the same filename on multiple pages if that makes sense;
//postion options are post,body,form,bottom
//See documentation for more information

$hooks['join.php']['form'] = 'hooks/joinform.php';
// $hooks['login.php']['bottom'] = 'hooks/loginbottom.php';
$hooks['join.php']['post'] = 'hooks/joinpost.php';
$hooks['join.php']['pre'] = 'hooks/joinpre.php';
$hooks['account.php']['bottom'] = 'hooks/accountbottom.php';
registerHooks($hooks,$plugin_name);

} //do not perform actions outside of this statement
