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
$db->query("ALTER TABLE users ADD COLUMN ldap varchar(255)");
$db->query("ALTER TABLE settings ADD COLUMN ldap_server varchar(255)");
$db->query("ALTER TABLE settings ADD COLUMN ldap_admin varchar(255)");
$db->query("ALTER TABLE settings ADD COLUMN ldap_admin_pw varchar(255)");
$db->query("ALTER TABLE settings ADD COLUMN ldap_tree varchar(255)");
$db->query("ALTER TABLE settings ADD COLUMN ldap_port varchar(255)");
$db->query("ALTER TABLE settings ADD COLUMN ldap_version varchar(255)");
$check = $db->query("SELECT ldap_server FROM settings")->first();

if($check->ldap_server == ''){ //nothing in the settings so put demo data
$fields = array(
	'ldap_server'=>'www.zflexldap.com',
	'ldap_admin'=>'cn=ro_admin,ou=sysadmins,dc=zflexsoftware,dc=com',
	'ldap_admin_pw'=>'zflexpass',
	'ldap_tree'=>'dc=zflexsoftware,dc=com',
	'ldap_port'=>'389',
	'ldap_version'=>'3'
);
$db->update('settings',1,$fields);

}
//do you want to inject your plugin in the middle of core UserSpice pages?
$hooks = [];

//The format is $hooks['userspicepage.php']['position'] = path to filename to include
//Note you can include the same filename on multiple pages if that makes sense;
//postion options are post,body,form,bottom
//See documentation for more information
$hooks['login.php']['body'] = 'hooks/redir.php';
$hooks['join.php']['body'] = 'hooks/redir.php';
$hooks['user_settings.php']['body'] = 'hooks/user_settings.php';

registerHooks($hooks,$plugin_name);

} //do not perform actions outside of this statement
