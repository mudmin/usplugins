<?php
require_once("init.php");
if (in_array($user->data()->id, $master_account)){

	$count = 0;
	$db = DB::getInstance();
	include "plugin_info.php";
	pluginActive($plugin_name);

	$checkQ = $db->query("SELECT id,updates FROM us_plugins WHERE plugin = ?",array($plugin_name));
	$checkC = $checkQ->count();
	if($checkC < 1){
		err($plugin_name." is not installed!");
		die();
	}
	$check = $checkQ->first();
	if($check->updates == ''){
		$existing = [];
	}else{
		$existing = json_decode($check->updates);
	}

	// //here is an example
	// $update = '00001';
	// if(!in_array($update,$existing)){
	// 	//do something
	// $existing[] = $update;
	// $count++;
	// }

	$new = json_encode($existing);
	$db->update('us_plugins',$check->id,['updates'=>$new]);
	if(!$db->error()) {
		if($count == 1){

		}else{
			err($count.' updates applied!');
		}
	} else {
		err('Failed to save updates');
		logger($user->data()->id,"USPlugins","Failed to save updates, Error: ".$db->errorString());
	}

}
