<?php
require_once("init.php");
//For security purposes, it is MANDATORY that this page be wrapped in the following
//if statement. This prevents remote execution of this code.
if (in_array($user->data()->id, $master_account)){


$db = DB::getInstance();
include "plugin_info.php";

$p = "v4api";
$min = "4.4.0";
$max = "4.4.14";

if (version_compare($user_spice_ver, $min) >= 0 && version_compare($user_spice_ver, $max) <= 0){
$files = [
	"users/views/_admin_dashboard.php",
	"users/views/_admin_menu.php",
	"users/views/_admin_plugins.php",
	"users/views/_admin_templates.php",
	"/usersc/plugins/spice_shaker/assets/downloader.php",
	"/usersc/plugins/spice_shaker/configure.php",
];

foreach($files as $file){
if (!copy($abs_us_root.$us_url_root."usersc/plugins/".$p."/files/".$file, $abs_us_root.$us_url_root.$file)) {
    logger("1","API Upgrade","failed to copy ".$file);

}else{
		logger("1","API Upgrade","Successfully copied ".$file);
}
}
}else{
	logger("1","API Upgrade","Updates skipped because you are on $user_spice_ver and the min is $min and the max is $max");
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
// $hooks['login.php']['body'] = 'hooks/loginbody.php';
// $hooks['login.php']['form'] = 'hooks/loginform.php';
// $hooks['login.php']['bottom'] = 'hooks/loginbottom.php';
// $hooks['login.php']['post'] = 'hooks/loginpost.php';
registerHooks($hooks,$plugin_name);

} //do not perform actions outside of this statement
