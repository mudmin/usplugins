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

  $update = '00005';
  if(!in_array($update,$existing)){
  // The plugin zip unpacks OVER the existing install, so files dropped from the
  // package still linger. These were left behind by the guzzle 7.10.0 -> 7.15.2
  // and promises 2.3.0 -> 2.5.1 upgrades. promises' functions.php helpers are no
  // longer wired into autoload_files.php, and package-lock.json is the same class
  // of unused dev artifact that migration 00004 cleaned off brevo-php.
  $pluginRoot = $abs_us_root.$us_url_root.'usersc/plugins/sendinblue';
  $removed = 0;
  $staleFiles = [
    'vendor/guzzlehttp/guzzle/package-lock.json',
    'vendor/guzzlehttp/promises/Makefile',
    'vendor/guzzlehttp/promises/src/functions.php',
    'vendor/guzzlehttp/promises/src/functions_include.php',
    'vendor/symfony/deprecation-contracts/.gitignore',
  ];
  foreach($staleFiles as $f){
    $full = $pluginRoot.'/'.$f;
    if(file_exists($full) && is_file($full)){
      unlink($full);
      $removed++;
    }
  }
  logger($user->data()->id,"Migrations","$update migration triggered for $plugin_name - removed $removed stale file(s)");

  $existing[] = $update;
  $count++;
  }

  $update = '00006';
  if(!in_array($update,$existing)){
    // Keep only delivery evidence. Never store passwordless subjects or bodies:
    // some UserSpice passwordless modes put the one-time code in the subject.
    $db->query("CREATE TABLE IF NOT EXISTS plg_sendinblue_messages (
      id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
      message_id VARCHAR(191) NOT NULL,
      recipient_email VARCHAR(255) NOT NULL,
      sender_email VARCHAR(255) NOT NULL,
      kind VARCHAR(32) NOT NULL,
      status VARCHAR(32) NOT NULL,
      accepted_at DATETIME NOT NULL,
      delivered_at DATETIME NULL,
      last_event_at DATETIME NULL,
      last_checked_at DATETIME NULL,
      last_reason VARCHAR(255) NULL,
      UNIQUE KEY message_id (message_id),
      KEY recipient_accepted (recipient_email(128), accepted_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    if(!$db->error()){
      $existing[] = $update;
      $count++;
      logger($user->data()->id,"Migrations","$update migration triggered for $plugin_name");
    } else {
      logger($user->data()->id,"Migrations","$update migration failed for $plugin_name: ".$db->errorString());
    }
  }
  $update = '00007';
  if(!in_array($update,$existing)){
    $migrationOk = true;
    if(!$db->columnExists('plg_sendinblue_messages', 'delivered_at')){
      $db->query('ALTER TABLE plg_sendinblue_messages ADD COLUMN delivered_at DATETIME NULL AFTER accepted_at');
      $migrationOk = $migrationOk && !$db->error();
    }
    if(!$db->columnExists('plg_sendinblue', 'public_delivery_status')){
      $db->query('ALTER TABLE plg_sendinblue ADD COLUMN public_delivery_status TINYINT(1) NOT NULL DEFAULT 0');
      $migrationOk = $migrationOk && !$db->error();
    }
    if(!$db->columnExists('plg_sendinblue', 'public_delivery_status_seeded')){
      $db->query('ALTER TABLE plg_sendinblue ADD COLUMN public_delivery_status_seeded TINYINT(1) NOT NULL DEFAULT 0');
      $migrationOk = $migrationOk && !$db->error();
    }
    if($migrationOk){
      $existing[] = $update;
      $count++;
      logger($user->data()->id,"Migrations","$update migration triggered for $plugin_name");
    } else {
      logger($user->data()->id,"Migrations","$update migration failed for $plugin_name: ".$db->errorString());
    }
  }
  $update = '00008';
  if(!in_array($update,$existing)){
    // Brevo tags. functions.php treats plg_sendinblue.tag_mode as the marker that
    // plg_sendinblue_messages.tags exists too, so add the messages column first.
    $migrationOk = true;
    if(!$db->columnExists('plg_sendinblue_messages', 'tags')){
      $db->query('ALTER TABLE plg_sendinblue_messages ADD COLUMN tags VARCHAR(255) NULL AFTER kind');
      $migrationOk = $migrationOk && !$db->error();
    }
    if($migrationOk && !$db->columnExists('plg_sendinblue', 'tag_fixed')){
      $db->query("ALTER TABLE plg_sendinblue ADD COLUMN tag_fixed VARCHAR(64) NOT NULL DEFAULT ''");
      $migrationOk = $migrationOk && !$db->error();
    }
    if($migrationOk && !$db->columnExists('plg_sendinblue', 'tag_mode')){
      $db->query("ALTER TABLE plg_sendinblue ADD COLUMN tag_mode VARCHAR(10) NOT NULL DEFAULT 'off'");
      $migrationOk = $migrationOk && !$db->error();
    }
    if($migrationOk){
      $existing[] = $update;
      $count++;
      logger($user->data()->id,"Migrations","$update migration triggered for $plugin_name");
    } else {
      logger($user->data()->id,"Migrations","$update migration failed for $plugin_name: ".$db->errorString());
    }
  }
  $update = '00009';
  if(!in_array($update,$existing)){
    // configure.php used to save settings through Input::get(), which HTML-encodes,
    // so "Bob & Co" was stored and sent to recipients as "Bob &amp; Co". Settings
    // are now stored as plain text; decode what earlier versions saved.
    $row = $db->query('SELECT id, `from`, from_name, reply FROM plg_sendinblue')->first();
    if($row){
      $db->update('plg_sendinblue', $row->id, [
        'from' => html_entity_decode((string)$row->from, ENT_QUOTES, 'UTF-8'),
        'from_name' => html_entity_decode((string)$row->from_name, ENT_QUOTES, 'UTF-8'),
        'reply' => html_entity_decode((string)$row->reply, ENT_QUOTES, 'UTF-8'),
      ]);
    }
    if(!$db->error()){
      $existing[] = $update;
      $count++;
      logger($user->data()->id,"Migrations","$update migration triggered for $plugin_name");
    } else {
      logger($user->data()->id,"Migrations","$update migration failed for $plugin_name: ".$db->errorString());
    }
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
