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
  //repeating this because 00001 was originally broken.
  logger($user->data()->id,"Migrations","$update migration triggered for $plugin_name");
  $existing[] = $update; //add the update you just did to the existing update array
  $count++;
  }


  $update = '00003';
  if(!in_array($update,$existing)){
  $db->query("ALTER TABLE us_forms ADD COLUMN api_insert tinyint(1) DEFAULT 0");
  $db->query("ALTER TABLE us_forms ADD COLUMN api_update tinyint(1) DEFAULT 0");
  $db->query("ALTER TABLE us_forms ADD COLUMN api_perms_insert varchar(255) DEFAULT '2'");
  $db->query("ALTER TABLE us_forms ADD COLUMN api_perms_update varchar(255) DEFAULT '2'");
  logger($user->data()->id,"Migrations","$update migration triggered for $plugin_name");

  $existing[] = $update; //add the update you just did to the existing update array
  $count++;
  }

  $update = '00004';
  if(!in_array($update,$existing)){
  $db->query("ALTER TABLE us_forms ADD COLUMN api_user_col varchar(255) DEFAULT ''");
  $db->query("ALTER TABLE us_forms ADD COLUMN api_force_user_col tinyint(1) DEFAULT '1'");

  logger($user->data()->id,"Migrations","$update migration triggered for $plugin_name");

  $existing[] = $update; //add the update you just did to the existing update array
  $count++;
  }


  $update = '00005';
  if(!in_array($update,$existing)){
  $db->query("ALTER TABLE us_forms ADD COLUMN api_user_col varchar(255) DEFAULT ''");
  $db->query("ALTER TABLE us_forms ADD COLUMN api_force_user_col tinyint(1) DEFAULT '1'");

  logger($user->data()->id,"Migrations","$update migration triggered for $plugin_name");

  $existing[] = $update; //add the update you just did to the existing update array
  $count++;
  }

  $update = '00006';
  if(!in_array($update,$existing)){
    $db->query("ALTER TABLE us_forms CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
    $db->query("ALTER TABLE us_form_validation CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
    $db->query("ALTER TABLE us_form_views CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");

    $forms = $db->query("SELECT * FROM us_forms")->results();
    foreach($forms as $f){
      $db->query("ALTER TABLE $f->form CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
      $n = $f->form."_form";
      $db->query("ALTER TABLE $n CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
    }

  logger($user->data()->id,"Migrations","$update migration triggered for $plugin_name");

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
