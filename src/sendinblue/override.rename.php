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

//all actions should be performed here.
function email($to,$body,$subject){
  $result = sendinblue($to,$body,$subject);
  return $result;
}
