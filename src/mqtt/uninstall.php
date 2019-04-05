<?php
require_once("init.php");

//For security purposes, it is MANDATORY that this page be wrapped in the following
//if statement. This prevents remote execution of this code.
if (in_array($user->data()->id, $master_account)){
$db = DB::getInstance();
include "plugin_info.php";

//all actions should be performed here.
//you will probably be doing more than removing the item from the db

$db->query("DELETE FROM us_plugins WHERE plugin = ?",array($plugin_name));
if(!$db->error()) {
  err($plugin_name.' uninstalled');
  //delete files
  $files = ['mqtt_settings.php','subscribe.php','classes/phpMQTT.php'];
  foreach($files as $file){
  if(!unlink($abs_us_root.$us_url_root.'users/'.$file)){
  echo "failed to delete ".$file."<br>";
  }
  }

  //in general, it's probably a bad idea to delete tables from the users' database
  //but that depends on your use case.  Since this is very specialized, we're going to do it.
  $db->query("DROP TABLE mqtt");
  logger($user->data()->id,"USPlugins", $plugin_name. " uninstalled");
} else {
	logger($user->data()->id,"USPlugins-MQTT","Failed to remove plugin from us_plugins, Error: ".$db->errorString());
}

} //do not perform actions outside of this statement
