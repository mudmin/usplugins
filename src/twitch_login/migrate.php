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
$checkQ = $db->query("SELECT * FROM us_plugins WHERE plugin = ?",array($plugin_name));
$checkC = $checkQ->count();
if($checkC > 0){

  $check = $checkQ->first();
  if($check->updates == ''){
  $existing = []; //deal with not finding any updates
  }else{
  $existing = json_decode($check->updates);
  }


  $update = '00001';
  if(!in_array($update,$existing)){
  // The plugin zip is unpacked OVER the existing install, so files that were
  // removed from the plugin still linger on disk. This migration sweeps them.
  //
  // Two groups:
  //  1. assets/OAuth - a dead, unreferenced duplicate of league/oauth2-client 2.4.1.
  //     Nothing ever loaded it (twitch.php uses assets/vendor/autoload.php), but it
  //     was the only composer manifest in the plugin, so it was the thing security
  //     scanners flagged.
  //  2. vendor packages/files dropped by the guzzle 6.3.3 -> 7.15.2 and
  //     psr7 1.5.2 -> 2.13.0 upgrades. paragonie/random_compat is gone entirely
  //     (a PHP 5 polyfill), and psr7/promises removed their functions.php helpers.
  $pluginRoot = $abs_us_root.$us_url_root.'usersc/plugins/twitch_login';
  $baseDir    = realpath($pluginRoot);
  $removed    = 0;

  // Recursively delete a directory, but only if it really sits inside this plugin.
  $nukeDir = function($relPath) use ($baseDir, &$removed){
    if(!$baseDir) return;
    $target = realpath($baseDir.'/'.$relPath);
    // containment guard - never delete outside the plugin folder
    if(!$target || strpos($target, $baseDir.DIRECTORY_SEPARATOR) !== 0) return;
    if(!is_dir($target)) return;
    $it    = new RecursiveDirectoryIterator($target, RecursiveDirectoryIterator::SKIP_DOTS);
    $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
    foreach($files as $file){
      if($file->isDir()){
        rmdir($file->getRealPath());
      } else {
        unlink($file->getRealPath());
        $removed++;
      }
    }
    rmdir($target);
  };

  // 1. the dead vendored copy of league/oauth2-client
  $nukeDir('assets/OAuth');

  // 2. paragonie/random_compat - package removed outright
  $nukeDir('assets/vendor/paragonie');

  // 3. individual files dropped by the guzzle / psr7 / oauth2-client upgrades
  $staleFiles = [
    'assets/vendor/guzzlehttp/guzzle/src/Exception/SeekException.php',
    'assets/vendor/guzzlehttp/guzzle/src/UriTemplate.php',
    'assets/vendor/guzzlehttp/promises/Makefile',
    'assets/vendor/guzzlehttp/promises/src/functions.php',
    'assets/vendor/guzzlehttp/promises/src/functions_include.php',
    'assets/vendor/guzzlehttp/psr7/.editorconfig',
    'assets/vendor/guzzlehttp/psr7/src/functions.php',
    'assets/vendor/guzzlehttp/psr7/src/functions_include.php',
    'assets/vendor/league/oauth2-client/CHANGELOG.md',
    'assets/vendor/league/oauth2-client/CONTRIBUTING.md',
    'assets/vendor/league/oauth2-client/CREDITS.md',
    'assets/vendor/league/oauth2-client/README.PROVIDER-GUIDE.md',
    'assets/vendor/ralouphie/getallheaders/.gitignore',
    'assets/vendor/ralouphie/getallheaders/.travis.yml',
    'assets/vendor/ralouphie/getallheaders/phpunit.xml',
    'assets/vendor/ralouphie/getallheaders/tests/GetAllHeadersTest.php',
  ];
  foreach($staleFiles as $f){
    $full = $pluginRoot.'/'.$f;
    if(file_exists($full) && is_file($full)){
      unlink($full);
      $removed++;
    }
  }

  // directories left empty by the sweep (rmdir is a no-op unless empty)
  foreach(['assets/vendor/ralouphie/getallheaders/tests'] as $d){
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
