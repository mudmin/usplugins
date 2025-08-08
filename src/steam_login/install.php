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
$db->query("ALTER TABLE users ADD COLUMN steam_id varchar(255)");
$db->query("ALTER TABLE users ADD COLUMN steam_un varchar(255)");
$db->query("ALTER TABLE users ADD COLUMN steam_avatar varchar(255)");
$db->query("ALTER TABLE settings ADD COLUMN steam_api varchar(255)");
$db->query("ALTER TABLE settings ADD COLUMN steam_domain varchar(255)");

//do you want to inject your plugin in the middle of core UserSpice pages?
$hooks = [];

//The format is $hooks['userspicepage.php']['position'] = path to filename to include
//Note you can include the same filename on multiple pages if that makes sense;
//postion options are post,body,form,bottom
//See documentation for more information
$hooks['login.php']['body'] = 'hooks/loginbody.php';
//$hooks['join.php']['body'] = 'hooks/joinbody.php';
$hooks['account.php']['body'] = 'hooks/link_button_account.php';
$hooks['account.php']['bottom'] = 'hooks/accountbottom.php';
// $hooks['login.php']['form'] = 'hooks/loginform.php';
// $hooks['login.php']['bottom'] = 'hooks/loginbottom.php';
// $hooks['login.php']['post'] = 'hooks/loginpost.php';
registerHooks($hooks,$plugin_name);

} //do not perform actions outside of this statement
