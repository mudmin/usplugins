<?php
require_once("init.php");

//For security purposes, it is MANDATORY that this page be wrapped in the following
//if statement. This prevents remote execution of this code.
if (in_array($user->data()->id, $master_account)){
$db = DB::getInstance();
$plugin_name = "forms"; //change this for your plugin!
$plugin_name = strtolower($plugin_name);//you're welcome
//all actions should be performed here.
//you will probably be doing more than removing the item from the db
// unlink($abs_us_root.$us_url_root.'users/message.php');

$files = [
  "_admin_forms_edit.php",
  "_admin_forms_preview.php",
  "_admin_forms_views.php",
  "_admin_forms.php",
  "_form_create_field.php",
  "_form_validation_options.php",
  "_form_edit_delete_reorder.php",
  "_form_edit_field.php",
  "_form_existing_forms.php",
  "_form_existing_views.php",
  "_form_manager_menu.php",
];
foreach($files as $file){
unlink($abs_us_root.$us_url_root.'users/views/'.$file);
}

$file = "combobox.js";
unlink($abs_us_root.$us_url_root.'users/js/'.$file);

$file = "form_validation.php";
unlink($abs_us_root.$us_url_root.'users/parsers/'.$file);

$db->update("settings",1,['forms'=>0]);
$db->query("DELETE FROM us_plugins WHERE plugin = ?",array($plugin_name));
if(!$db->error()) {
    err($plugin_name.' uninstalled');
    logger($user->data()->id,"USPlugins", $plugin_name. " uninstalled");
} else {
    err($plugin_name.' was not uninstalled');
    logger($user->data()->id,"USPlugins","Failed to uninstall Plugin, Error: ".$db->errorString());
}

} //do not perform actions outside of this statement
