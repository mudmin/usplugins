<?php
require_once("init.php");
//This file is called when "deactivating" a plugin (ie uninstall pre v5.3.6)
//normally it should be left alone, although if you want to perform some actions here, you can.

//This file is a good place to delete any files you moved into other folders during the install process

//as of 5.3.6, if you would like your plugin to delete the data it created, you can do that in the delete.php file.


//For security purposes, it is MANDATORY that this page be wrapped in the following
//if statement. This prevents remote execution of this code.
if (in_array($user->data()->id, $master_account)){
$db = DB::getInstance();
include "plugin_info.php";

//all actions should be performed here.


$db->query("DELETE FROM us_plugins WHERE plugin = ?",array($plugin_name));
deRegisterHooks($plugin_name);
if(!$db->error()) {
    err($plugin_name.' uninstalled');
    logger($user->data()->id,"USPlugins", $plugin_name. " uninstalled");
} else {
    err($plugin_name.' was not uninstalled');
    logger($user->data()->id,"USPlugins","Failed to uninstall Plugin, Error: ".$db->errorString());
}
} //do not perform actions outside of this statement
