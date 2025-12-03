<?php
require_once("init.php");
//For security purposes, it is MANDATORY that this page be wrapped in the following
//if statement. This prevents remote execution of this code.
if (in_array($user->data()->id, $master_account)){
include "plugin_info.php";

//all actions should be performed here.
$pluginCheck = $db->query("SELECT * FROM us_plugins WHERE plugin = ?",array($plugin_name))->count();
if($pluginCheck > 0){
	err($plugin_name.' has already been installed!');
}else{
 $fields = array(
	 'plugin'=>$plugin_name,
	 'status'=>'installed',
 );
 $db->insert('us_plugins',$fields);
 if(!$db->error()) {
	 	err($plugin_name.' installed');
		logger($user->data()->id,"USPlugins",$plugin_name." installed");
 } else {
	 	err($plugin_name.' was not installed');
		logger($user->data()->id,"USPlugins","Failed to to install plugin, Error: ".$db->errorString());
 }
}

// Create database tables for Hand and Foot scoring
$db->query("CREATE TABLE IF NOT EXISTS plg_handfoot_rulesets (
  id INT(11) NOT NULL AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  going_out_round_1 INT(11) DEFAULT 50,
  going_out_round_2 INT(11) DEFAULT 100,
  going_out_round_3 INT(11) DEFAULT 150,
  going_out_round_4 INT(11) DEFAULT 200,
  black_book_points INT(11) DEFAULT 300,
  red_book_points INT(11) DEFAULT 500,
  is_default TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$db->query("CREATE TABLE IF NOT EXISTS plg_handfoot_games (
  id INT(11) NOT NULL AUTO_INCREMENT,
  ruleset_id INT(11) NOT NULL,
  num_players INT(11) NOT NULL,
  creator_ip VARCHAR(45) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY ruleset_id (ruleset_id),
  KEY creator_ip (creator_ip)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$db->query("CREATE TABLE IF NOT EXISTS plg_handfoot_players (
  id INT(11) NOT NULL AUTO_INCREMENT,
  game_id INT(11) NOT NULL,
  player_name VARCHAR(100) NOT NULL,
  player_order INT(11) NOT NULL,
  PRIMARY KEY (id),
  KEY game_id (game_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$db->query("CREATE TABLE IF NOT EXISTS plg_handfoot_scores (
  id INT(11) NOT NULL AUTO_INCREMENT,
  game_id INT(11) NOT NULL,
  player_id INT(11) NOT NULL,
  round_number INT(11) NOT NULL,
  went_out TINYINT(1) DEFAULT 0,
  black_books INT(11) DEFAULT 0,
  red_books INT(11) DEFAULT 0,
  card_points INT(11) DEFAULT 0,
  total_points INT(11) DEFAULT 0,
  PRIMARY KEY (id),
  KEY game_id (game_id),
  KEY player_id (player_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Insert default ruleset
$defaultCheck = $db->query("SELECT id FROM plg_handfoot_rulesets WHERE is_default = 1")->count();
if($defaultCheck == 0) {
  $fields = array(
    'name' => 'Standard Rules',
    'going_out_round_1' => 50,
    'going_out_round_2' => 100,
    'going_out_round_3' => 150,
    'going_out_round_4' => 200,
    'black_book_points' => 300,
    'red_book_points' => 500,
    'is_default' => 1
  );
  $db->insert('plg_handfoot_rulesets', $fields);
}

//do you want to inject your plugin in the middle of core UserSpice pages?
//visit https://userspice.com/plugin-hooks/ to get a better understanding of hooks
$hooks = [];

//The format is $hooks['userspicepage.php']['position'] = path to filename to include
//Note you can include the same filename on multiple pages if that makes sense;
//postion options are post,body,form,bottom
//See documentation for more information
// $hooks['login.php']['body'] = 'hooks/loginbody.php';

registerHooks($hooks,$plugin_name);

} //do not perform actions outside of this statement
