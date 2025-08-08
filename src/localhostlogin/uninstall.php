<?php
require_once("init.php");
if (in_array($user->data()->id, $master_account)){
  $db = DB::getInstance();
  include "plugin_info.php";

  $db->query("DELETE FROM us_plugins WHERE plugin = ?",array($plugin_name));
  deRegisterHooks($plugin_name);
  if(!$db->error()) {
    err($plugin_name.' uninstalled');
    logger($user->data()->id,"USPlugins", $plugin_name. " uninstalled");
  } else {
    err($plugin_name.' was not uninstalled');
    logger($user->data()->id,"USPlugins","Failed to uninstall Plugin, Error: ".$db->errorString());
  }
}
