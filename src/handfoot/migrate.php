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

  // Add creator_ip column to games table
  $update = '00001';
  if(!in_array($update,$existing)){
    logger($user->data()->id,"Migrations","$update migration triggered for $plugin_name");

    // Check if column exists before adding
    $columnExists = $db->query("SHOW COLUMNS FROM plg_handfoot_games LIKE 'creator_ip'")->count();
    if($columnExists == 0) {
      $db->query("ALTER TABLE plg_handfoot_games ADD COLUMN creator_ip VARCHAR(45) NOT NULL DEFAULT '0.0.0.0' AFTER num_players");
      $db->query("ALTER TABLE plg_handfoot_games ADD KEY creator_ip (creator_ip)");
    }

    $existing[] = $update; //add the update you just did to the existing update array
    $count++;
  }

  // Create settings table for handfoot plugin
  $update = '00002';
  if(!in_array($update,$existing)){
    logger($user->data()->id,"Migrations","$update migration triggered for $plugin_name");

    $db->query("CREATE TABLE IF NOT EXISTS plg_handfoot_settings (
      id INT(11) NOT NULL AUTO_INCREMENT,
      require_login TINYINT(1) DEFAULT 0,
      allow_user_creation TINYINT(1) DEFAULT 0,
      PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Insert default settings row
    $settingsCheck = $db->query("SELECT id FROM plg_handfoot_settings")->count();
    if($settingsCheck == 0) {
      $db->insert('plg_handfoot_settings', ['id' => 1, 'require_login' => 0, 'allow_user_creation' => 0]);
    }

    $existing[] = $update;
    $count++;
  }

  // Add user_id column to players table (nullable for backwards compatibility)
  $update = '00003';
  if(!in_array($update,$existing)){
    logger($user->data()->id,"Migrations","$update migration triggered for $plugin_name");

    $columnExists = $db->query("SHOW COLUMNS FROM plg_handfoot_players LIKE 'user_id'")->count();
    if($columnExists == 0) {
      $db->query("ALTER TABLE plg_handfoot_players ADD COLUMN user_id INT(11) NULL AFTER game_id");
      $db->query("ALTER TABLE plg_handfoot_players ADD KEY user_id (user_id)");
    }

    $existing[] = $update;
    $count++;
  }

  // Add creator_user_id column to games table (nullable for backwards compatibility)
  $update = '00004';
  if(!in_array($update,$existing)){
    logger($user->data()->id,"Migrations","$update migration triggered for $plugin_name");

    $columnExists = $db->query("SHOW COLUMNS FROM plg_handfoot_games LIKE 'creator_user_id'")->count();
    if($columnExists == 0) {
      $db->query("ALTER TABLE plg_handfoot_games ADD COLUMN creator_user_id INT(11) NULL AFTER creator_ip");
      $db->query("ALTER TABLE plg_handfoot_games ADD KEY creator_user_id (creator_user_id)");
    }

    $existing[] = $update;
    $count++;
  }

  // Add notes column to games table
  $update = '00005';
  if(!in_array($update,$existing)){
    logger($user->data()->id,"Migrations","$update migration triggered for $plugin_name");

    $columnExists = $db->query("SHOW COLUMNS FROM plg_handfoot_games LIKE 'notes'")->count();
    if($columnExists == 0) {
      $db->query("ALTER TABLE plg_handfoot_games ADD COLUMN notes TEXT NULL AFTER creator_user_id");
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
