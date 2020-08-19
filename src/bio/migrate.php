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
    $publicPages = [
    	'edit_profile.php',
    	'profile.php',
    	'view_all_users.php',
    ];
    foreach($publicPages as $a){
      unlink($abs_us_root.$us_url_root."users/".$a);
    	copy($abs_us_root.$us_url_root."usersc/plugins/bio/files/".$a, $abs_us_root.$us_url_root."users/".$a);
    	$check = $db->query("SELECT * FROM pages WHERE page = ?",['users/'.$a])->count();

    		 if($check < 1){
    			 $db->insert('pages',['page'=>'users/'.$a,'private'=>1]);
    			 $newId = $db->lastId();
    			 $db->insert('permission_page_matches',['permission_id'=>1,'page_id'=>$newId]);
    			 $db->insert('permission_page_matches',['permission_id'=>2,'page_id'=>$newId]);
    		 }
    	}
  logger($user->data()->id,"Migrations","$update migration triggered for $plugin_name");
  $existing[] = $update; //add the update you just did to the existing update array
  $count++;
  }










  //after all updates are done. Keep this at the bottom.
  $new = json_encode($existing);
  $db->update('us_plugins',$check->id,['updates'=>$new,'last_check'=>date("Y-m-d H:i:s")]);
  if(!$db->error()) {
    logger($user->data()->id,"Migrations","$count migration(s) susccessfully triggered for $plugin_name");
  } else {
   	logger($user->data()->id,"USPlugins","Failed to save updates, Error: ".$db->errorString());
  }
}//do not perform actions outside of this statement
}
