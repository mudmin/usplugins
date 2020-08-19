<?php
require_once("init.php");
//For security purposes, it is MANDATORY that this page be wrapped in the following
//if statement. This prevents remote execution of this code.
if (in_array($user->data()->id, $master_account)){


$db = DB::getInstance();
include "plugin_info.php";

$users = $db->query("SELECT id FROM users")->results();
foreach($users as $u){
$check = $db->query("SELECT id FROM profiles WHERE user_id = ?",[$u->id])->count();
if($check < 1){
  $db->insert('profiles',['user_id'=>$u->id,'bio'=>"This is your bio"]);
}
}

//all actions should be performed here.
$check = $db->query("SELECT * FROM us_plugins WHERE plugin = ?",array($plugin_name))->count();
if($check > 0){
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

//do you want to inject your plugin in the middle of core UserSpice pages?
$hooks = [];
$hooks['account.php']['body'] = 'hooks/accountbody.php';
registerHooks($hooks,$plugin_name);

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


} //do not perform actions outside of this statement
