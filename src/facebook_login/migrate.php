<?php
// For security purposes, it is MANDATORY that this page be wrapped in the following
// if statement. This prevents remote execution of this code.


//Please jump donw to line 27 to see the example code.
include "plugin_info.php";
if (in_array($user->data()->id, $master_account) && pluginActive($plugin_name, true)) {
  //all actions should be performed here.

  //check which updates have been installed
  $count = 0;
  $db = DB::getInstance();

  //Make sure the plugin is installed and get the existing updates
  $checkQ = $db->query("SELECT id,updates FROM us_plugins WHERE plugin = ?", array($plugin_name));
  $checkC = $checkQ->count();
  if ($checkC > 0) {
    $check = $checkQ->first();
    if ($check->updates == '') {
      $existing = []; //deal with not finding any updates
    } else {
      $existing = json_decode($check->updates);
    }


    //list your updates here from oldest at the top to newest at the bottom.
    //Give your update a unique update number/code.

    //here is an example
    $update = '00001';
    if (!in_array($update, $existing)) {
      logger($user->data()->id, "Migrations", "$update migration triggered for $plugin_name");

      $existing[] = $update; //add the update you just did to the existing update array
      $count++;
    }

    $update = '00101';
    if (!in_array($update, $existing)) {
      //move db info from settings to custom table.
      if (!isset($settings->fblogin)) {
        $db->query("ALTER TABLE settings ADD fblogin tinyint(1) default 0");
      }

      $db->query("CREATE TABLE `plg_facebook_login` (
    `id` int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `fbid` varchar(255),
    `fbsecret` varchar(255),
    `fbcallback` varchar(255),
    `graph_ver` varchar(255)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
      $c = $db->query("SELECT * FROM plg_facebook_login WHERE id = 1")->count();
      if ($c == 0) {
        $db->query("TRUNCATE TABLE plg_facebook_login");
        $db->query("INSERT INTO plg_facebook_login (id) VALUES (1)");
      }
      $full_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
      $path = explode("users/", $full_url);
      $url_path = $path[0] . "usersc/plugins/$plugin_name/assets/oauth_success.php";
      $db->query("UPDATE plg_facebook_login SET fbcallback = ? WHERE fbcallback IS NULL OR fbcallback = '';", [$url_path]);

      $db->query("UPDATE plg_facebook_login SET graph_ver = ? WHERE graph_ver IS NULL OR graph_ver = '';", ["v19.0"]);
		// $db->query("ALTER TABLE settings ADD fbid varchar(255)");
		// $db->query("ALTER TABLE settings ADD fbsecret varchar(255)");
		// $db->query("ALTER TABLE settings ADD fbcallback varchar(255)");
		// $db->query("ALTER TABLE settings ADD graph_ver varchar(255)");
      $cols = ['fbid','fbsecret'];
      foreach ($cols as $col) {
        if (isset($settings->$col)) {
          $db->query("UPDATE plg_facebook_login SET $col = ?", [$settings->$col]);
          $db->query("ALTER TABLE settings DROP $col");
        }
      }
      $cols = ['fbcallback', 'graph_ver'];
      foreach ($cols as $col) {
        if (isset($settings->$col)) {
          $db->query("ALTER TABLE settings DROP $col");
        }
      }

      $existing[] = $update; //add the update you just did to the existing update array
      $count++;
    }


    //after all updates are done. Keep this at the bottom.
    $new = json_encode($existing);
    $db->update('us_plugins', $check->id, ['updates' => $new, 'last_check' => date("Y-m-d H:i:s")]);
    if (!$db->error()) {
      logger($user->data()->id, "Migrations", "$count migration(s) successfully triggered for $plugin_name");
    } else {
      logger($user->data()->id, "USPlugins", "Failed to save updates, Error: " . $db->errorString());
    }
  } //do not perform actions outside of this statement
}
