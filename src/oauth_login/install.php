<?php
require_once("init.php");
//For security purposes, it is MANDATORY that this page be wrapped in the following
//if statement. This prevents remote execution of this code.
if (in_array($user->data()->id, $master_account)){
include "plugin_info.php";

//all actions should be performed here.
$pluginCheck = $db->query("SELECT * FROM us_plugins WHERE plugin = ?",array($plugin_name))->count();
if($pluginCheck > 0){
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
//visit https://userspice.com/plugin-hooks/ to get a better understanding of hooks
$hooks = [];

//The format is $hooks['userspicepage.php']['position'] = path to filename to include
//Note you can include the same filename on multiple pages if that makes sense;
//postion options are post,body,form,bottom
//See documentation for more information
// $hooks['login.php']['body'] = 'hooks/loginbody.php';

$db->query("CREATE TABLE plg_oauth_login (
    id INT AUTO_INCREMENT PRIMARY KEY,
	oauth tinyint(1) DEFAULT 0,
	client_name VARCHAR(255) default 'UserSpice Login',
    client_icon varchar(255) default 'oauth.png',
    client_id VARCHAR(80) UNIQUE,
    client_secret VARCHAR(80) UNIQUE,
    redirect_uri VARCHAR(200) 
);");
$db->query("ALTER TABLE settings ADD COLUMN oauth tinyint(1) default 0");
$db->query("ALTER TABLE plg_oauth_login ADD COLUMN server_url varchar(255)");
$db->query("ALTER TABLE plg_oauth_login ADD COLUMN login_title VARCHAR(255) default 'UserSpice'");
$db->query("ALTER TABLE plg_oauth_login ADD COLUMN login_script VARCHAR(255) default 'default_script.php'");
$tableCheck = $db->query("SELECT * FROM plg_oauth_login")->count();
if($tableCheck < 1){
	$db->query("TRUNCATE TABLE plg_oauth_login");
	$db->insert("plg_oauth_login",array(
		'id'=>1,
	));
}

$db->query("CREATE TABLE plg_oauth_client_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    access_token VARCHAR(255) NOT NULL,
    refresh_token VARCHAR(255),
    expires_at DATETIME NOT NULL,
    scope VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);");

$db->query("CREATE TABLE plg_oauth_client_logins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
	new_user tinyint(1) DEFAULT 0,
    ts DATETIME DEFAULT CURRENT_TIMESTAMP
);");

$ocheck = $db->query("SELECT * FROM plg_social_logins WHERE plugin = ?",array("oauth_login"))->count();



if($ocheck < 1){
	$fields = [
		'plugin'=>'oauth_login',
		'provider'=>'UserSpice',
		'enabledsetting'=>'oauth',
		'image'=>'assets/oauth.png',
		'link'=>'assets/oauth.php',
	];
	$db->insert('plg_social_logins',$fields);

}




registerHooks($hooks,$plugin_name);

} //do not perform actions outside of this statement
