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

copy($abs_us_root.$us_url_root."usersc/plugins/session_manager/assets/manage_sessions.php", $abs_us_root.$us_url_root."users/manage_sessions.php");
$checkQ = $db->query("SELECT * FROM pages WHERE page = ?",['users/manage_sessions.php']);
$checkC = $checkQ->count();
if($check < 1){
	$fields = array(
		'page'=>'users/manage_sessions.php',
		'title'=>'Session Management',
		'private'=>1,
	);
	$db->insert('pages',$fields);
	$lastId = $db->lastId();
	$db->insert('permission_page_matches',['permission_id'=>1,'page_id'=>$lastId]);
}
$db->query("ALTER TABLE settings ADD COLUMN one_sess tinyint(1)");
$settings = $db->query("SELECT * FROM settings")->first();
if($settings->one_sess == ''){
	$db->update('settings',1,['one_sess'=>0]);
}
//do you want to inject your plugin in the middle of core UserSpice pages?
$hooks = [];

//The format is $hooks['userspicepage.php']['position'] = path to filename to include
//Note you can include the same filename on multiple pages if that makes sense;
//postion options are post,body,form,bottom
//See documentation for more information
$hooks['account.php']['body'] = 'hooks/account_body.php';
// $hooks['login.php']['form'] = 'hooks/loginform.php';
// $hooks['login.php']['bottom'] = 'hooks/loginbottom.php';
// $hooks['login.php']['post'] = 'hooks/loginpost.php';
registerHooks($hooks,$plugin_name);

} //do not perform actions outside of this statement
