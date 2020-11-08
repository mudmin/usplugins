<?php
require_once("init.php");
//For security purposes, it is MANDATORY that this page be wrapped in the following
//if statement. This prevents remote execution of this code.
if (in_array($user->data()->id, $master_account)){


$db = DB::getInstance();
include "plugin_info.php";

$check = $db->query("SELECT * FROM us_forms WHERE form = ?",['users'])->count();
if($check < 1){
	$db->insert('us_forms',['form'=>'users']);
	$db->query("CREATE TABLE `users_form` (
	  `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
	  `ord` int(11) NOT NULL,
	  `col` varchar(255) NOT NULL,
	  `form_descrip` varchar(255) NOT NULL,
	  `table_descrip` varchar(255) NOT NULL,
	  `col_type` varchar(255) NOT NULL,
	  `field_type` varchar(100) NOT NULL,
	  `length` int(11) NOT NULL,
	  `required` tinyint(1) NOT NULL,
	  `validation` text NOT NULL,
	  `label_class` varchar(255) NOT NULL,
	  `field_class` varchar(255) NOT NULL,
	  `input_html` text NOT NULL,
	  `select_opts` text NOT NULL
	) ENGINE=InnoDB DEFAULT CHARSET=latin1;
	");
}

$db->query("CREATE TABLE `plg_userinfo` (
	`id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
	`fname` int(1) NOT NULL DEFAULT 0,
	`lname` int(1) NOT NULL DEFAULT 0,
	`uname` int(1) NOT NULL DEFAULT 0,
	`domain` varchar(255)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
");
$check = $db->query("SELECT * FROM plg_userinfo")->count();
if($check < 1){
	$db->query("TRUNCATE TABLE plg_userinfo");
	$db->insert("plg_userinfo",['domain'=>'userspice.com']);
}

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

//do you want to inject your plugin in the middle of core UserSpice pages?
$hooks = [];

//The format is $hooks['userspicepage.php']['position'] = path to filename to include
//Note you can include the same filename on multiple pages if that makes sense;
//postion options are post,body,form,bottom
//See documentation for more information
$hooks['join.php']['bottom'] = 'hooks/joinbottom.php';
$hooks['join.php']['pre'] = 'hooks/joinpre.php';
$hooks['join.php']['form'] = 'hooks/joinform.php';
$hooks['join.php']['post'] = 'hooks/joinpost.php';
// $hooks['admin.php?view=users']['form'] = 'hooks/joinform.php';
// $hooks['admin.php?view=users']['post'] = 'hooks/joinpre.php';
$hooks['account.php']['bottom'] = 'hooks/accountbottom.php';
$hooks['user_settings.php']['bottom'] = 'hooks/user_settings_bottom.php';
$hooks['user_settings.php']['form'] = 'hooks/joinform.php';
$hooks['user_settings.php']['post'] = 'hooks/user_settings_post.php';
$hooks['admin.php?view=user']['post'] = 'hooks/user_settings_post.php';
$hooks['admin.php?view=user']['form'] = 'hooks/singleuser_bottom.php';
$hooks['admin.php?view=user']['bottom'] = 'hooks/accountbottom.php';
// $hooks['login.php']['form'] = 'hooks/loginform.php';
// $hooks['login.php']['bottom'] = 'hooks/loginbottom.php';
// $hooks['login.php']['post'] = 'hooks/loginpost.php';
registerHooks($hooks,$plugin_name);

} //do not perform actions outside of this statement
