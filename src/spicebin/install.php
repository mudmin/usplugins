<?php
require_once("init.php");
//For security purposes, it is MANDATORY that this page be wrapped in the following
//if statement. This prevents remote execution of this code.
if (in_array($user->data()->id, $master_account)){


$db = DB::getInstance();
include "plugin_info.php";



//all actions should be performed here.
$db->query("CREATE TABLE plg_spicebin (
  id int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  paste LONGTEXT,
	private tinyint(1) default 0,
	delete_on DATETIME default '2099-12-31 23:59:59',
	last_visit datetime,
	created_on DATETIME,
	user int(11),
	title varchar(255),
	ip varchar(255),
	code varchar(255),
	link varchar(255),
	lang varchar(255),
  views int(11) default 0
)");
logger("0","Spicebin","Installed - ".$db->errorString());

$db->query("CREATE TABLE plg_spicebin_settings(
  id int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
	view_privacy tinyint(1) default 1,
	delete_days int(11) default 120,
	create_privacy tinyint(11) default 1,
	perm int(11) default 2,
	tag varchar(255),
  create_page varchar(255) default 'usersc/plugins/spicebin/files/create.php',
  view_page varchar(255) default 'usersc/plugins/spicebin/files/view.php',
  account tinyint(1) default 1
)");
logger("0","Spicebin","Installed - ".$db->errorString());
$check = $db->query("SELECT * FROM plg_spicebin_settings")->count();
if($check < 1){
	$db->query("TRUNCATE TABLE plg_spicebin_settings");
	$db->insert("plg_spicebin_settings",['id'=>1]);
}
$db->query("ALTER TABLE plg_spicebin_settings ADD COLUMN mng_tag varchar(255)");
$db->query("ALTER TABLE plg_spicebin_settings ADD COLUMN mng_perm int(11) default 2");
$db->query("ALTER TABLE plg_spicebin_settings ADD COLUMN lten_view tinyint(1) default 1");
$db->query("ALTER TABLE plg_spicebin_settings ADD COLUMN del_mode tinyint(1) default 1");
$db->query("ALTER TABLE plg_spicebin_settings ADD COLUMN lten_create tinyint(1) default 1");
$db->query("ALTER TABLE plg_spicebin_settings ADD COLUMN lten_your tinyint(1) default 1");
$db->query("ALTER TABLE plg_spicebin_settings ADD COLUMN product_name varchar(255) default 'SpiceBin'");
$db->query("ALTER TABLE plg_spicebin_settings ADD COLUMN product_single varchar(255) default 'Paste'");
$db->query("ALTER TABLE plg_spicebin_settings ADD COLUMN product_plural varchar(255) default 'Pastes'");
$db->query("ALTER TABLE plg_spicebin_settings ADD COLUMN product_button varchar(255) default 'Your Pastes'");
$db->query("ALTER TABLE plg_spicebin_settings ADD COLUMN your_page varchar(255) default 'usersc/plugins/spicebin/files/user.php'");
$db->query("ALTER TABLE plg_spicebin_settings ADD COLUMN theme varchar(255) default 'elegant'");

$db->query("CREATE TABLE plg_spicebin_lang(
  id int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
	lang varchar(255),
	common tinyint(1) default 0
)");

$db->query("ALTER TABLE plg_spicebin ADD COLUMN no_auto tinyint(1) default 0");

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

$hooks['account.php']['body'] = 'hooks/account_body.php';
$hooks['loginSuccess']['body'] = 'hooks/login_auto_delete.php';

registerHooks($hooks,$plugin_name);

} //do not perform actions outside of this statement
