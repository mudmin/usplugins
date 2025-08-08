<?php
require_once("init.php");
//For security purposes, it is MANDATORY that this page be wrapped in the following
//if statement. This prevents remote execution of this code.
if (in_array($user->data()->id, $master_account)){


$db = DB::getInstance();
include "plugin_info.php";

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
 $db->query("ALTER TABLE settings ADD stripe_private varchar(255)");
 $db->query("ALTER TABLE settings ADD stripe_public varchar(255)");
 $db->query("ALTER TABLE settings ADD stripe_private_test varchar(255)");
 $db->query("ALTER TABLE settings ADD stripe_public_test varchar(255)");
 $db->query("ALTER TABLE settings ADD stripe_live int(1)");
 $db->query("ALTER TABLE settings ADD stripe_url varchar(255)");
  $db->query("ALTER TABLE settings ADD stripe_currency varchar(3) default 'usd'");
 $db->query("CREATE TABLE `stripe_transactions` (
	 `id` int(11) NOT NULL,
	 `user` int(11) NOT NULL,
	 `timestamp`  timestamp,
	 `notes` text,
	 `fname` varchar(255) NOT NULL,
	 `lname` varchar(255) NOT NULL,
	 `email` varchar(255) NOT NULL,
	 `amount` varchar(30) NOT NULL,
	 `card_type` varchar(30) NOT NULL,
	 `charge_id` varchar(255) NOT NULL
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

 $db->query("ALTER TABLE `stripe_transactions`
	 ADD PRIMARY KEY (`id`)");

	$db->query("ALTER TABLE `stripe_transactions`
		 MODIFY `id` int(11) NOT NULL AUTO_INCREMENT");
  $db->update('settings',1,['stripe_live'=>1]);

 if(!$db->error()) {
	 	err($plugin_name.' installed');
		logger($user->data()->id,"USPlugins", $plugin_name. " installed.");
 } else {
	 	err($plugin_name.' was not installed');
		logger($user->data()->id,"USPlugins","Failed to to install plugin, Error: ".$db->errorString());
 }
}



} //do not perform actions outside of this statement
