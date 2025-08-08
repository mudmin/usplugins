<?php
// For security purposes, it is MANDATORY that this page be wrapped in the following
// if statement. This prevents remote execution of this code.


//Please jump donw to line 27 to see the example code.
include "plugin_info.php";
if (in_array($user->data()->id, $master_account) && pluginActive($plugin_name,true)){
//all actions should be performed here.

//check which updates have been installed
$count = 0;
$db = DB::getInstance();

//Make sure the plugin is installed and get the existing updates
$checkQ = $db->query("SELECT * FROM us_plugins WHERE plugin = ?",array($plugin_name));
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


  $update = '00003';
  if(!in_array($update,$existing)){

    $db->query("CREATE TABLE `plg_msg_settings` (
      id int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
      ding tinyint(1) default 0,
      ajax tinyint(1) default 0,
      ajax_time int(11) default 60,
      alerts tinyint(1) default 1,
      messages tinyint(1) default 1,
      notifications tinyint(1) default 1
    )");

    $ck = $db->query("SELECT id from plg_msg_settings")->count();
    if($ck < 1){
      $db->insert("plg_msg_settings",['id'=>1]);
    }

$menusSearch = $db->query("SELECT * FROM us_menus")->results();
$link = "usersc/plugins/messaging/menu_hooks/notification_hook.php";
$fields = [
  "type"=>"snippet",
  "label"=>"Messages",
  "link"=>$link,
  "link_target"=>"_self",
  "parent"=>0,
  "display_order"=>0,
  "disabled"=>0,
  "permissions"=>'["1"]',
];
foreach($menusSearch as $m){
  $fields['menu'] = $m->id;
  $ck= $db->query("SELECT id FROM us_menu_items WHERE menu = ? AND link = ?",[$m->id,$link])->count();
  if($ck < 1){
    $db->insert("us_menu_items",$fields);
  }
}

  logger($user->data()->id,"Migrations","$update migration triggered for $plugin_name");

  $existing[] = $update; //add the update you just did to the existing update array
  $count++;
  }

  $update = '00005';
  if(!in_array($update,$existing)){
  logger($user->data()->id,"Migrations","$update migration triggered for $plugin_name");
  $db->query("ALTER TABLE plg_msg_settings ADD COLUMN notifications_sound varchar(255) default 'ding.mp3' AFTER notifications");
  $db->query("ALTER TABLE plg_msg_settings ADD COLUMN alerts_sound varchar(255) default 'ding.mp3' AFTER alerts");
  $db->query("ALTER TABLE plg_msg_settings ADD COLUMN messages_sound varchar(255) default 'ding.mp3' AFTER messages");


  $existing[] = $update; //add the update you just did to the existing update array
  $count++;
  }

  $update = '00006';
  if(!in_array($update,$existing)){
  logger($user->data()->id,"Migrations","$update migration triggered for $plugin_name");
  $db->query("ALTER TABLE plg_msg_settings ADD COLUMN multiple tinyint(1) default 1");

  $existing[] = $update; //add the update you just did to the existing update array
  $count++;
  }

  $update = '00007';
  if(!in_array($update,$existing)){
  logger($user->data()->id,"Migrations","$update migration triggered for $plugin_name");
  $db->query("ALTER TABLE plg_msg_settings ADD COLUMN notifications_if_none tinyint(1) default 1 AFTER notifications");
  $db->query("ALTER TABLE plg_msg_settings ADD COLUMN alerts_if_none tinyint(1) default 1 AFTER alerts");
  $db->query("ALTER TABLE plg_msg_settings ADD COLUMN messages_if_none tinyint(1) default 1 AFTER messages");


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
