<?php
require_once("init.php");
//For security purposes, it is MANDATORY that this page be wrapped in the following
//if statement. This prevents remote execution of this code.
if (in_array($user->data()->id, $master_account)){


$db = DB::getInstance();
$plugin_name = "notifications"; //change this for your plugin!
$plugin_name = strtolower($plugin_name);//you're welcome

$cpyfail = 0;

$file = "_admin_notifications.php";
if (!copy($abs_us_root.$us_url_root."usersc/plugins/notifications/files/".$file, $abs_us_root.$us_url_root."users/views/".$file)) {
    echo "failed to copy $file...\n";
		$cpyfail=1;
}

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
if($cpyfail == 1){
	echo "The plugin installed but did not have permission to copy files.<br>";
	echo "Please copy message.php and notifications.php to the users/ directory.<br>";
	echo "Please copy _admin_notifications.php to the users/views/ directory.<br>";
	echo "You will find this file in usersc/plugins/notifications/files.<br>";
	echo "Please note that you will most likely have to remove these files manually if you uninstall the plugin.<br>";
  die();
}

} //do not perform actions outside of this statement
