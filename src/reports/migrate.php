<?php
// For security purposes, it is MANDATORY that this page be wrapped in the following
// if statement. This prevents remote execution of this code.
include "plugin_info.php";
if (in_array($user->data()->id, $master_account) && pluginActive($plugin_name,true)){
//all actions should be performed here.

//check which updates have been installed
$count = 0;
$db = DB::getInstance();

//Make sure the plugin is installed and get the existing updates
$checkQ = $db->query("SELECT id,updates FROM us_plugins WHERE plugin = ?",array($plugin_name));
$checkC = $checkQ->count();
if($checkC > 0){
  $check = $checkQ->first();
  if($check->updates == ''){
  $existing = []; //deal with not finding any updates
  }else{
  $existing = json_decode($check->updates);
  }





  //list your updates here from oldest at the top to newest at the bottom.
  //Give your update a unique update number/code.

  //here is an example
  $update = '00001';
  if(!in_array($update,$existing)){
  logger($user->data()->id,"Migrations","$update migration triggered for $plugin_name");

  $existing[] = $update; //add the update you just did to the existing update array
  $count++;
  }

  $update = '00002';
  if(!in_array($update,$existing)){
  // The plugin zip unpacks OVER the existing install, so files dropped from the
  // package still linger. zipstream-php moved 3.2.2 -> 3.1.1 (3.2.2 requires
  // php-64bit ^8.3, which broke installs on the supported 8.1 floor), and these
  // three files only ever existed in 3.2.2.
  $pluginRoot = $abs_us_root.$us_url_root.'usersc/plugins/reports';
  $removed = 0;
  $staleFiles = [
    'vendor/maennchen/zipstream-php/src/Stream/CallbackStreamWrapper.php',
    'vendor/maennchen/zipstream-php/guides/LargeFilesToS3.rst',
    'vendor/maennchen/zipstream-php/test/CallbackOutputTest.php',
  ];
  foreach($staleFiles as $f){
    $full = $pluginRoot.'/'.$f;
    if(file_exists($full) && is_file($full)){
      unlink($full);
      $removed++;
    }
  }
  // directories left empty by the sweep (rmdir is a no-op unless empty)
  foreach(['vendor/maennchen/zipstream-php/test','vendor/maennchen/zipstream-php/guides','vendor/maennchen/zipstream-php/src/Stream'] as $d){
    $dp = $pluginRoot.'/'.$d;
    if(is_dir($dp)){
      @rmdir($dp);
    }
  }
  logger($user->data()->id,"Migrations","$update migration triggered for $plugin_name - removed $removed stale file(s)");

  $existing[] = $update; //add the update you just did to the existing update array
  $count++;
  }





  //after all updates are done. Keep this at the bottom.
  $new = json_encode($existing);
  $db->update('us_plugins',$check->id,['updates'=>$new,'last_check'=>date("Y-m-d H:i:s")]);
  if(!$db->error()) {
    logger($user->data()->id,"Migrations","$count migration(s) successfully triggered for $plugin_name");
  } else {
   	logger($user->data()->id,"USPlugins","Failed to save updates, Error: ".$db->errorString());
  }
}//do not perform actions outside of this statement
}
