<?php
require_once("init.php");
if (in_array($user->data()->id, $master_account)){


	$db = DB::getInstance();
	include "plugin_info.php";

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

	$hooks = [];
	$hooks['login.php']['bottom'] = 'hooks/loginbottom.php';
	registerHooks($hooks,$plugin_name);

}
