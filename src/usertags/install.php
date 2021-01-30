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

$db->query("
CREATE TABLE `plg_tags` (
 `id` int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
 `tag` varchar(255)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
");

$db->query("
CREATE TABLE `plg_tags_matches` (
 `id` int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
 `tag_id` int(11) UNSIGNED NOT NULL,
 `tag_name` varchar(255),
 `user_id` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
");

//do you want to inject your plugin in the middle of core UserSpice pages?
$hooks = [];
$hooks['admin.php?view=user']['form'] = 'hooks/admin_user_form.php';
$hooks['admin.php?view=user']['post'] = 'hooks/admin_user_post.php';
registerHooks($hooks,$plugin_name);

} //do not perform actions outside of this statement
