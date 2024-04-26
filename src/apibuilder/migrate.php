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
  logger($user->data()->id,"Migrations","$update migration triggered for $plugin_name");
  $db->query("ALTER TABLE plg_api_settings ADD COLUMN spice_api tinyint(1) default '1'");
  $existing[] = $update; //add the update you just did to the existing update array
  $count++;
  }

  $update = '00003';
  if(!in_array($update,$existing)){
  logger($user->data()->id,"Migrations","$update migration triggered for $plugin_name");
  $db->query("ALTER TABLE plg_api_settings ADD COLUMN spice_user_api tinyint(1) default '1'");
  $existing[] = $update; //add the update you just did to the existing update array
  $count++;
  }

  $update = '00004';
  if(!in_array($update,$existing)){
  logger($user->data()->id,"Migrations","$update migration triggered for $plugin_name");
  $db->query("ALTER TABLE plg_api_settings ADD COLUMN dev_msg tinyint(1) default '0'");
  $existing[] = $update; //add the update you just did to the existing update array
  $count++;
  }

  //unzip on every update
  $zip = new ZipArchive;
  if ($zip->open($abs_us_root.$us_url_root."/usersc/plugins/apibuilder/files/api.zip") === TRUE) {

    // Unzip Path
    $zip->extractTo($abs_us_root.$us_url_root);
    $zip->close();

} else {
    logger($user->data()->id,"APIBuilder","Failed to unzip file");
}



$update = '00006';
if(!in_array($update,$existing)){
  $hooks = [];
  $hooks['join.php']['post'] = 'hooks/joinpost.php';
  $hooks['account.php']['bottom'] = 'hooks/accountbottom.php';
  registerHooks($hooks,$plugin_name);
$existing[] = $update; //add the update you just did to the existing update array
$count++;
}


$update = '00007';
if(!in_array($update,$existing)){
  $db->query("ALTER TABLE plg_api_settings ADD COLUMN key_on_acct tinyint(1) default '01'");
$existing[] = $update; //add the update you just did to the existing update array
$count++;
}

$update = '00008';
if(!in_array($update,$existing)){
  $db->query("ALTER TABLE plg_api_settings ADD COLUMN new_user_key tinyint(1) default '0'");
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
