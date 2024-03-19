<?php
require_once("init.php");
//For security purposes, it is MANDATORY that this page be wrapped in the following
//if statement. This prevents remote execution of this code.
if (in_array($user->data()->id, $master_account)){


global $db;
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

$db->query("CREATE TABLE `plg_sendinblue` (
  `id` int(11) NOT NULL,
  `from` varchar(255),
  `reply` varchar(255),
  `key` varchar(255),
	`override` tinyint(1) default 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
$db->query("ALTER TABLE `plg_sendinblue`	ADD PRIMARY KEY (`id`)");
$db->query("ALTER TABLE `plg_sendinblue` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT");

$db->query("ALTER TABLE plg_sendinblue ADD COLUMN from_name varchar(255)");
$check = $db->query("SELECT * FROM plg_sendinblue")->count();
if($check < 1){
	$db->query("TRUNCATE TABLE plg_sendinblue");
	$db->insert("plg_sendinblue",['id'=>1]);
}
//do you want to inject your plugin in the middle of core UserSpice pages?
$hooks = [];

//The format is $hooks['userspicepage.php']['position'] = path to filename to include
//Note you can include the same filename on multiple pages if that makes sense;
//postion options are post,body,form,bottom
//See documentation for more information
// $hooks['login.php']['body'] = 'hooks/loginbody.php';
// $hooks['login.php']['form'] = 'hooks/loginform.php';
// $hooks['login.php']['bottom'] = 'hooks/loginbottom.php';
// $hooks['login.php']['post'] = 'hooks/loginpost.php';
registerHooks($hooks,$plugin_name);

} //do not perform actions outside of this statement
