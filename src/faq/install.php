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
		$db->query("CREATE TABLE `plg_faq_categories` (`id` int(11) NOT NULL AUTO_INCREMENT, `name` varchar(255) NOT NULL, `menu_text` varchar(255) NOT NULL, `display_order` int(11) NOT NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
		$db->query("CREATE TABLE `plg_faqs` (`id` int(11) NOT NULL AUTO_INCREMENT, `category_id` int(11) NOT NULL, `question` text NOT NULL, `answer` text NOT NULL, `display_order` int(11) NOT NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
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
	//visit https://userspice.com/plugin-hooks/ to get a better understanding of hooks
	$hooks = [];

	//The format is $hooks['userspicepage.php']['position'] = path to filename to include
	//Note you can include the same filename on multiple pages if that makes sense;
	//postion options are post,body,form,bottom
	//See documentation for more information
	// $hooks['login.php']['body'] = 'hooks/loginbody.php';

	registerHooks($hooks,$plugin_name);

} //do not perform actions outside of this statement