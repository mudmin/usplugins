<?php
require_once("init.php");
//For security purposes, it is MANDATORY that this page be wrapped in the following
//if statement. This prevents remote execution of this code.
if (in_array($user->data()->id, $master_account)){


$db = DB::getInstance();
include "plugin_info.php";

if(version_compare($user_spice_ver, '5.6.9', '<') && !pluginActive($social_logins, true)) {
    $db->update('us_plugins', ['plugin', '=', $plugin_name], ['status' => 'uninstalled']);
    $usplugins[$plugin_name] = 2;
    write_php_ini($usplugins, $abs_us_root . $us_url_root . 'usersc/plugins/plugins.ini.php');
    usError("Social Logins plugin or UserSpice 5.6.9+ required to activate.");
    Redirect::to('admin.php?view=plugins');
    die();
}

//all actions should be performed here.
$check = $db->query("SELECT * FROM us_plugins WHERE plugin = ?",array($plugin_name))->count();
if($check > 0){
	err($plugin_name.' has already been installed!');
}else{
	$db->query("ALTER TABLE settings ADD glogin BOOLEAN");
	// $db->query("ALTER TABLE settings ADD gid varchar(255)");
	// $db->query("ALTER TABLE settings ADD gsecret varchar(255)");
	// $db->query("ALTER TABLE settings ADD ghome varchar(255)");
	// $db->query("ALTER TABLE settings ADD gredirect varchar(255)");

	$db->query("ALTER TABLE users ADD oauth_provider varchar(255)");
	$db->query("ALTER TABLE users ADD oauth_uid varchar(255)");

	$db->query("DELETE FROM plg_social_logins WHERE plugin = 'google_login';");
	$db->insert("plg_social_logins", ["plugin"=>$plugin_name, "provider"=>"Google", "enabledsetting"=>"glogin", "image"=>"assets/google.png", "link"=>"assets/google_oauth.php"]);
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
//$hooks['login.php']['body'] = 'hooks/loginbody.php';
//$hooks['join.php']['body'] = 'hooks/loginbody.php';

registerHooks($hooks,$plugin_name);

} //do not perform actions outside of this statement
