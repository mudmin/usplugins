<?php
require_once("init.php");
//This file is called when "deleting" a plugin in v5.3.6 or greater
//It's a great place to delete any tables your plugin created because it is only run if someone has chosen
//to actively delete the plugin (not just deactivate it temporarily)


//For security purposes, it is MANDATORY that this page be wrapped in the following
//if statement. This prevents remote execution of this code.
if (in_array($user->data()->id, $master_account)){
$db = DB::getInstance();
include "plugin_info.php";

//all actions should be performed here.
// Example:
// $db->query("DELETE TABLE plg_rememberme_content");


} //do not perform actions outside of this statement
