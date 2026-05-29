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

$keysQ = $db->query('SELECT * FROM `keys`');
$keysC = $keysQ->count();
if($keysC < 1){
  $db->query("TRUNCATE TABLE `keys`");
  // `keys` is a MySQL reserved word. Use raw query() (unsanitized on both old
  // and new DB classes) instead of insert(), which rejects backticked names on
  // new versions and requires them on old ones.
  $db->query("INSERT INTO `keys` (`currency`) VALUES (?)",['usd']);
  // Re-query fresh: $keysQ is the stale (empty) result from before the insert
  $keys = $db->query('SELECT * FROM `keys`')->first();
}else{
  $keys = $keysQ->first();
  if($keys->currency == ''){
    $db->query("UPDATE `keys` SET `currency` = ? WHERE id = ?",['usd',$keys->id]);
    $keys->currency = 'usd';
  }
}

$db->query("
CREATE TABLE `plg_payments` (
 `id` int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
 `user` int(11) ,
 `amt_paid` dec(11,2),
 `dt` datetime ,
 `charge_id` varchar(255) ,
 `method` varchar(255) ,
 `notes` text ,
 `failed` int(1) default 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
");
$db->query("
CREATE TABLE `plg_payments_options` (
 `id` int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
 `option` varchar(255) ,
 `enabled` tinyint(11) default 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
");

$dirs = glob($abs_us_root . $us_url_root . 'usersc/plugins/payments/assets/*', GLOB_ONLYDIR);
foreach($dirs as $d){
	$asset = str_replace($abs_us_root . $us_url_root . 'usersc/plugins/payments/assets/','',$d);
	// `option` is also a reserved word; backtick it in raw SQL and avoid insert()
	$check = $db->query("SELECT * FROM plg_payments_options WHERE `option` = ?",[$asset])->count();
	if($check < 1){
		$db->query("INSERT INTO plg_payments_options (`option`) VALUES (?)",[$asset]);
	}
}
$db->query("ALTER TABLE `keys` ADD COLUMN currency varchar(3) default 'usd'");
$db->query("ALTER TABLE `keys` ADD COLUMN paypal_email varchar(255)");
$db->query("ALTER TABLE `keys` ADD COLUMN paypal_sandbox varchar(5) default 'TRUE'");
$db->query("ALTER TABLE `keys` ADD COLUMN paypal_callback varchar(255)");
//do you want to inject your plugin in the middle of core UserSpice pages?
$hooks = [];

//The format is $hooks['userspicepage.php']['position'] = path to filename to include
//Note you can include the same filename on multiple pages if that makes sense;
//postion options are post,body,form,bottom
//See documentation for more information
// $hooks['login.php']['body'] = 'hooks/loginbody.php';
// $hooks['login.php']['form'] = 'hooks/loginform.php';
// $hooks['login.php']['bottom'] = 'hooks/loginbottom.php';
// $hooks['login.php']['post'] = 'hooks/loginpost.php';
registerHooks($hooks,$plugin_name);

} //do not perform actions outside of this statement
