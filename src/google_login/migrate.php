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

  $update = '00100';
  if(!in_array($update,$existing)){
  //move db info from settings to custom table.
  if(!isset($settings->glogin)){
    $db->query("ALTER TABLE settings ADD glogin tinyint(1) default 0");
  }
  $googleSettings = $db->query("SELECT * FROM settings")->first();
  $db->query("CREATE TABLE `plg_google_login` (
    `id` int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `gid` varchar(255),
    `gsecret` varchar(255),
    `ghome` varchar(255),
    `gredirect` varchar(255)

      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    $c = $db->query("SELECT * FROM plg_google_login WHERE id = 1")->count();
    if($c < 1){
      $db->query("TRUNCATE TABLE plg_google_login");
      $db->insert("plg_google_login", ["id"=>1]);
      $cols = ['gid','gsecret','ghome','gredirect'];
      foreach($cols as $col){
        if(isset($googleSettings->$col)){
          $db->query("UPDATE plg_google_login SET $col = ?",[$googleSettings->$col]);
          $db->query("ALTER TABLE settings DROP $col");
        }
       
      }
    }
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
