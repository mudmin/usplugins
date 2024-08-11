<?php
require_once("init.php");
//For security purposes, it is MANDATORY that this page be wrapped in the following
//if statement. This prevents remote execution of this code.
if (in_array($user->data()->id, $master_account)){
include "plugin_info.php";
//get all login forms from the login_forms directory


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

$db->query("CREATE TABLE plg_oauth_server_clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_name VARCHAR(80) NOT NULL,
    client_description VARCHAR(200),
    client_enabled TINYINT(1) DEFAULT 1,
    client_id VARCHAR(80) UNIQUE NOT NULL,
    client_secret VARCHAR(80) UNIQUE NOT NULL,
    redirect_uri VARCHAR(200) NOT NULL,
    ip_restrict VARCHAR(200)
)");

$db->query("CREATE TABLE plg_oauth_server_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT(11),
    user_id INT(11),
    auth_code VARCHAR(80) NOT NULL,
    expires_at DATETIME NOT NULL,
    INDEX (auth_code)
)");

$db->query("CREATE TABLE plg_oauth_server_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT(11),
    user_id INT(11),
    access_token VARCHAR(80) NOT NULL,
    refresh_token VARCHAR(80),
    expires_at DATETIME NOT NULL,
    INDEX (access_token)
)");

$db->query("CREATE TABLE plg_oauth_server_settings (
    id INT AUTO_INCREMENT PRIMARY KEY
)");
$osetQ = $db->query("SELECT * FROM plg_oauth_server_settings");
if($osetQ->count() == 0){
	$db->query("TRUNCATE TABLE plg_oauth_server_settings");
	$db->insert('plg_oauth_server_settings', ['id'=>1]);
	$oset = $db->query("SELECT * FROM plg_oauth_server_settings")->first();
}else{
	$oset = $osetQ->first();
}
if(!isset($oset->other_columns)){
	$db->query("ALTER TABLE plg_oauth_server_settings ADD COLUMN other_columns TEXT");
	$db->query("ALTER TABLE plg_oauth_server_settings ADD COLUMN include_tags tinyint(1) DEFAULT 1");
	$db->update('plg_oauth_server_settings', 1, ['other_columns'=>'language,created']);
}

$db->query("ALTER TABLE plg_oauth_server_codes ADD COLUMN used tinyint(1) DEFAULT 0");
$db->query("ALTER TABLE plg_oauth_server_clients ADD COLUMN login_title VARCHAR(255) default 'Login with UserSpice'");
$db->query("ALTER TABLE plg_oauth_server_clients ADD COLUMN login_form VARCHAR(255) default 'default_login.php'");
$db->query("ALTER TABLE plg_oauth_server_clients ADD COLUMN login_script VARCHAR(255) default 'default_script.php'");
$db->query("ALTER TABLE plg_oauth_server_codes ADD COLUMN redirect_uri tinyint(1) DEFAULT 0");





//The format is $hooks['userspicepage.php']['position'] = path to filename to include
//Note you can include the same filename on multiple pages if that makes sense;
//postion options are post,body,form,bottom
//See documentation for more information
// $hooks['login.php']['body'] = 'hooks/loginbody.php';

registerHooks($hooks,$plugin_name);

} //do not perform actions outside of this statement
