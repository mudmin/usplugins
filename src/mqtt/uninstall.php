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

  }

} //do not perform actions outside of this statement
