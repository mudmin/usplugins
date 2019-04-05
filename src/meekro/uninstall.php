<?php
require_once("init.php");

//For security purposes, it is MANDATORY that this page be wrapped in the following
//if statement. This prevents remote execution of this code.
if (in_array($user->data()->id, $master_account)){
$db = DB::getInstance();
$plugin_name = "meekro"; //change this for your plugin!
$plugin_name = strtolower($plugin_name);//you're welcome
//all actions should be performed here.
//you will probably be doing more than removing the item from the db
$db->query("ALTER TABLE settings DROP COLUMN meekro");
$db->query("DELETE FROM us_plugins WHERE plugin = ?",array($plugin_name));
if(!$db->error()) {
    err($plugin_name.' uninstalled');
    logger($user->data()->id,"USPlugins", $plugin_name. " uninstalled");
} else {
    err($plugin_name.' was not uninstalled');
    logger($user->data()->id,"USPlugins","Failed to uninstall Plugin, Error: ".$db->errorString());
}
} //do not perform actions outside of this statement
