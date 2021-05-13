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
	$db->query("ALTER TABLE `settings` ADD `recaptcha` TINYINT(1) NOT NULL DEFAULT 0;");
	$db->query("ALTER TABLE `settings` ADD `recap_public` VARCHAR(100) NOT NULL DEFAULT 'Your-reCAPTCHA-Public-Key';");
	$db->query("ALTER TABLE `settings` ADD `recap_private` VARCHAR(100) NOT NULL DEFAULT 'Your-reCAPTCHA-Private-Key';");
	$db->query("ALTER TABLE `settings` ADD `recap_type` TINYINT(1) NOT NULL DEFAULT 2;");
	$db->query("ALTER TABLE `settings` ADD `recap_version` TINYINT(1) NOT NULL DEFAULT 3;");
	$db->query("UPDATE `settings` SET recaptcha = '0';"); //Disable reCAPTCHA on public pages to prevent lockout if keys are not valid


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
$hooks['login.php']['post'] = 'hooks/loginpost.php';
$hooks['login.php']['bottom'] = 'hooks/loginbottom.php';
$hooks['joinAttempt']['body'] = 'hooks/joinattemptbody.php';
$hooks['join.php']['bottom'] = 'hooks/joinbottom.php';
$hooks['forgot_password.php']['post'] = 'hooks/forgotpasswordpost.php';
$hooks['forgot_password.php']['bottom'] = 'hooks/forgotpasswordbottom.php';

registerHooks($hooks,$plugin_name);

} //do not perform actions outside of this statement