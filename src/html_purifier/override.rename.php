<?php
// For security purposes, it is MANDATORY that this page be wrapped in the following
// if statement. This prevents remote execution of this code.

//This OPTIONAL file should be used only if absolutely necessary.
//It allows you to override core UserSpice functions with your plugin.
//A perfect example would be if you want to override the built in userspice email function
//to use something like MailChimp. You would rename this file to override.php
//and create your email function below.
//note that usersc/includes/custom_functions includes before this so that file can
//declare a function before the plugin.

include "plugin_info.php";
if (in_array($user->data()->id, $master_account) && pluginActive($plugin_name,true)){
//all actions should be performed here.


}//do not perform actions outside of this statement
}
