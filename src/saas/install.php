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

$check = $db->query("SELECT id FROM us_saas_levels")->count();
if($check < 1){
	$db->insert('us_saas_levels',['level'=>'bronze','users'=>10,'details'=>'Bronze Package']);
	$db->insert('us_saas_levels',['level'=>'silver','users'=>100,'details'=>'Silver Package']);
	$db->insert('us_saas_levels',['level'=>'gold','users'=>1000,'details'=>'Gold Package']);
	$db->insert('us_saas_levels',['level'=>'platinum','users'=>10000,'details'=>'Platinum Package']);
	$db->insert('us_saas_levels',['level'=>'diamond','users'=>100000,'details'=>'Diamond Package']);
}

$db->query("CREATE TABLE `us_saas_mgrs` (
	`id` int(11) NOT NULL,
	`org` int(11),
	`user` int(11)) ENGINE=InnoDB DEFAULT CHARSET=latin1");
$db->query("ALTER TABLE `us_saas_mgrs`
	ADD PRIMARY KEY (`id`)");
$db->query("ALTER TABLE `us_saas_mgrs`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT");

$db->query("ALTER TABLE us_saas_levels ADD COLUMN perms varchar(255)");


$check = $db->query("SELECT id FROM us_saas_orgs")->count();
if($check < 1){
	$db->insert('us_saas_orgs',['id'=>1,'org'=>'Reserved','owner'=>1,'active'=>1]);
}
//do you want to inject your plugin in the middle of core UserSpice pages?
$hooks = [];

//The format is $hooks['userspicepage.php']['position'] = path to filename to include
//Note you can include the same filename on multiple pages if that makes sense;
//postion options are post,body,form,bottom
//See documentation for more information
// $hooks['login.php']['body'] = 'hooks/loginbody.php';
$hooks['account.php']['bottom'] = 'hooks/accountbottom.php';
$hooks['join.php']['pre'] = 'hooks/joinpre.php';
$hooks['join.php']['form'] = 'hooks/joinform.php';
// $hooks['login.php']['bottom'] = 'hooks/loginbottom.php';
// $hooks['login.php']['post'] = 'hooks/loginpost.php';
registerHooks($hooks,$plugin_name);

} //do not perform actions outside of this statement
