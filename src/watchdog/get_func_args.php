<?php
//NOTE: This also serves as the reference file for how to do One Click Edit with UserSpice. See comments below.
  require_once '../../../users/init.php';
  $db = DB::getInstance();
  if(!hasPerm([2],$user->data()->id)){
  die("You do not have permission to be here.");
  }
include "plugin_info.php";
if(pluginActive($plugin_name,true)){

$msg = "";
$directory = $abs_us_root.$us_url_root."usersc/plugins/watchdog/assets/";
$funcFiles = glob($directory . "*.php");
$availableFuncs = [];
foreach($funcFiles as $f){
  include($f);
  if(isset($availableWatchdogs)){
    foreach($availableWatchdogs as $k=>$a){
      $availableFuncs[$k] = $a;
    }
    unset($availableWatchdogs);
  }
}
$value = Input::get("value");
if($value != "" && isset($availableFuncs[$value]["args"])){
  $msg = $availableFuncs[$value]["desc"]."<br>".$availableFuncs[$value]["args"];
}
echo $msg;
} //end plugin active
