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

    $cpyfail = 0;

    $file = "message.php";
    unlink($abs_us_root.$us_url_root."users/".$file);
    if (!copy($abs_us_root.$us_url_root."usersc/plugins/messages/files/".$file, $abs_us_root.$us_url_root."users/".$file)) {
        echo "failed to copy $file...\n";
    		$cpyfail=1;
    }

    $file = "messages.php";
    unlink($abs_us_root.$us_url_root."users/".$file);
    if (!copy($abs_us_root.$us_url_root."usersc/plugins/messages/files/".$file, $abs_us_root.$us_url_root."users/".$file)) {
        echo "failed to copy $file...\n";
    		$cpyfail=1;
    }

    $files = ["_messages.php","msg1.php","msg2.php","msg3.php","msg4.php"];
    foreach($files as $file){
    unlink($abs_us_root.$us_url_root."users/views/".$file);
    if (!copy($abs_us_root.$us_url_root."usersc/plugins/messages/files/".$file, $abs_us_root.$us_url_root."users/views/".$file)) {
        echo "failed to copy $file...\n";
    		$cpyfail=1;
    }
    }

  $existing[] = $update; //add the update you just did to the existing update array
  $count++;
  }

  $update = '00003';
  function deleteDirectory($dirPath) {
    if (is_dir($dirPath)) {
        $objects = scandir($dirPath);
        foreach ($objects as $object) {
            if ($object != "." && $object !="..") {
                if (filetype($dirPath . DIRECTORY_SEPARATOR . $object) == "dir") {
                    deleteDirectory($dirPath . DIRECTORY_SEPARATOR . $object);
                } else {
                    unlink($dirPath . DIRECTORY_SEPARATOR . $object);
                }
            }
        }
    reset($objects);
    rmdir($dirPath);
    }
}
deleteDirectory($abs_us_root.$us_url_root."usersc/plugins/messages/assets");
  if(!in_array($update,$existing)){

    $cpyfail = 0;

    $file = "message.php";
    unlink($abs_us_root.$us_url_root."users/".$file);
    if (!copy($abs_us_root.$us_url_root."usersc/plugins/messages/files/".$file, $abs_us_root.$us_url_root."users/".$file)) {
        echo "failed to copy $file...\n";
        $cpyfail=1;
    }

    $file = "messages.php";
    unlink($abs_us_root.$us_url_root."users/".$file);
    if (!copy($abs_us_root.$us_url_root."usersc/plugins/messages/files/".$file, $abs_us_root.$us_url_root."users/".$file)) {
        echo "failed to copy $file...\n";
        $cpyfail=1;
    }

    $files = ["_messages.php","msg1.php","msg2.php","msg3.php","msg4.php"];
    foreach($files as $file){
    unlink($abs_us_root.$us_url_root."users/views/".$file);
    if (!copy($abs_us_root.$us_url_root."usersc/plugins/messages/files/".$file, $abs_us_root.$us_url_root."users/views/".$file)) {
        echo "failed to copy $file...\n";
        $cpyfail=1;
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
