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


$db->query("CREATE TABLE plg_db_explainer_databases (
	id int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
	db_name varchar(255) NOT NULL,
	db_description varchar(255) NOT NULL,
	imported_on datetime,
	last_updated datetime
  )");

//'Table Name', 'Column Name', 'Column Type', 'Column Length', 'Description', 'Key of Table', 'Key of Column'
$db->query("CREATE TABLE plg_db_explainer_tables (
	id int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
	db_id int(11) UNSIGNED NOT NULL,
	table_name varchar(255) NOT NULL,
	table_description varchar(255) NOT NULL,
	imported_on datetime
  )");

$db->query("CREATE TABLE plg_db_explainer_columns (
	id int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
	db_id int(11) UNSIGNED NOT NULL,
	table_id int(11) UNSIGNED NOT NULL,
	column_name varchar(255) NOT NULL,
	column_type varchar(255) NOT NULL,
	column_length varchar(255) NOT NULL,
	column_description varchar(255) NOT NULL,
	related_to_table varchar(255) NOT NULL,
	related_to_column varchar(255) NOT NULL,
	imported_on datetime,
	last_updated datetime
  )");
  
  

//The format is $hooks['userspicepage.php']['position'] = path to filename to include
//Note you can include the same filename on multiple pages if that makes sense;
//postion options are post,body,form,bottom
//See documentation for more information
// $hooks['login.php']['body'] = 'hooks/loginbody.php';

registerHooks($hooks,$plugin_name);

} //do not perform actions outside of this statement
