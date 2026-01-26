<?php
// For security purposes, it is MANDATORY that this page be wrapped in the following
// if statement. This prevents remote execution of this code.
include "plugin_info.php";
if (in_array($user->data()->id, $master_account) && pluginActive($plugin_name,true)){
//all actions should be performed here.

//check which updates have been installed
$count = 0;
global $db;

//Make sure the plugin is installed and get the existing updates
$checkQ = $db->query("SELECT id,updates FROM us_plugins WHERE plugin = ?",array($plugin_name));
$checkC = $checkQ->count();
if($checkC < 1){
  $fields = array(
    'plugin'=>$plugin_name,
    'status'=>'installed',
  );
  $db->insert('us_plugins',$fields);
  $checkQ = $db->query("SELECT id,updates FROM us_plugins WHERE plugin = ?",array($plugin_name));
  $checkC = $checkQ->count();
}
if($checkC > 0){
  $check = $checkQ->first();
  if($check->updates == ''){
  $existing = []; //deal with not finding any updates
  }else{
  $existing = json_decode($check->updates);
  }

  $update = '00001';
  if(!in_array($update,$existing)){
  logger($user->data()->id,"Migrations","$update migration triggered for $plugin_name");

  $existing[] = $update; //add the update you just did to the existing update array
  $count++;
  }

  $update = '00002';
  if(!in_array($update,$existing)){
  $db->query("ALTER TABLE plg_sendinblue CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
  logger($user->data()->id,"Migrations","$update migration triggered for $plugin_name");
  $existing[] = $update; //add the update you just did to the existing update array
  $count++;
  }

  $update = '00003';
  if(!in_array($update,$existing)){
  // Remove orphaned old sendinblue SDK with vulnerable guzzle dependencies
  $oldSdkPath = $abs_us_root.$us_url_root.'usersc/plugins/sendinblue/vendor/sendinblue';
  if(is_dir($oldSdkPath)){
    // Recursively delete the directory
    $it = new RecursiveDirectoryIterator($oldSdkPath, RecursiveDirectoryIterator::SKIP_DOTS);
    $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
    foreach($files as $file){
      if($file->isDir()){
        rmdir($file->getRealPath());
      } else {
        unlink($file->getRealPath());
      }
    }
    rmdir($oldSdkPath);
    logger($user->data()->id,"Migrations","$update migration triggered for $plugin_name - removed orphaned sendinblue SDK");
  } else {
    logger($user->data()->id,"Migrations","$update migration triggered for $plugin_name - no cleanup needed");
  }
  $existing[] = $update;
  $count++;
  }

  $update = '00004';
  if(!in_array($update,$existing)){
  // Remove composer.lock from brevo-php package (unused dev artifact that triggers security scanners)
  $brevoLockFile = $abs_us_root.$us_url_root.'usersc/plugins/sendinblue/vendor/getbrevo/brevo-php/composer.lock';
  if(file_exists($brevoLockFile)){
    unlink($brevoLockFile);
    logger($user->data()->id,"Migrations","$update migration triggered for $plugin_name - removed brevo-php composer.lock");
  } else {
    logger($user->data()->id,"Migrations","$update migration triggered for $plugin_name - no cleanup needed");
  }
  $existing[] = $update;
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
