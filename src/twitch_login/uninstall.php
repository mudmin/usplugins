<?php
require_once("init.php");

//For security purposes, it is MANDATORY that this page be wrapped in the following
//if statement. This prevents remote execution of this code.
if (in_array($user->data()->id, $master_account)){
$db = DB::getInstance();
include "plugin_info.php";

//all actions should be performed here.
//you will probably be doing more than removing the item from the db
$db->update('settings',1,["twlogin"=>0]);

$db->query("DELETE FROM us_plugins WHERE plugin = ?",array($plugin_name));
deRegisterHooks($plugin_name);
if(!$db->error()) {

    $db->query("DELETE FROM plg_social_logins WHERE plugin = ?", [$plugin_name]);
    $db->query("UPDATE settings SET twlogin = 0;");
    
    err($plugin_name.' uninstalled');
    logger($user->data()->id,"USPlugins", $plugin_name. " uninstalled");
} else {
    err($plugin_name.' was not uninstalled');
    logger($user->data()->id,"USPlugins","Failed to uninstall Plugin, Error: ".$db->errorString());
}
} //do not perform actions outside of this statement
