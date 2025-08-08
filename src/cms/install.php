<?php
require_once("init.php");
//For security purposes, it is MANDATORY that this page be wrapped in the following
//if statement. This prevents remote execution of this code.
if (in_array($user->data()->id, $master_account)){


$db = DB::getInstance();
include "plugin_info.php";

$db->query("CREATE TABLE plg_cms_content (
  id int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
	author int(11),
  title varchar(255) NOT NULL,
	content LONGTEXT,
	category int(11),
	status tinyint(1) DEFAULT 0,
  layout int(11) DEFAULT 0,
	date_published DATE,
	last_modified TIMESTAMP
)");

$db->query("CREATE TABLE plg_cms_layouts (
  id int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  title varchar(255) NOT NULL,
	layout TEXT,
	def tinyint(1) DEFAULT 0
)");

$db->query("CREATE TABLE plg_cms_tags (
  id int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  article int(11) UNSIGNED NOT NULL,
  tag varchar(255) NOT NULL
)");

$db->query("CREATE TABLE plg_cms_categories (
  id int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  category varchar(255) NOT NULL,
	perms varchar(255),
	subcat_of int(11) DEFAULT 1
)");

$db->query("CREATE TABLE plg_cms_widgets (
  id int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  widget_type tinyint(1) DEFAULT 0,
	title varchar(255),
  file varchar(255),
	content text
)");

$db->query("CREATE TABLE plg_cms_notices (
  id int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  dismissed tinyint(1) DEFAULT 0
)");



$db->query("CREATE TABLE plg_cms_settings (
  id int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  parser varchar(255) NOT NULL
)");

$check = $db->query("SELECT * FROM plg_cms_settings")->count();
if($check < 1){
	$db->insert('plg_cms_settings',['parser'=>'usersc/plugins/cms/content.php']);
}

$check = $db->query("SELECT * FROM plg_cms_layouts")->count();
if($check < 1){
  $db->query("
  INSERT INTO `plg_cms_layouts` (`title`, `layout`, `def`) VALUES
  ('Default', '<div class=\"row\"><div class=\"col-12\"><!>con<!></div></div>', 1),
  ('Blog', '<div class=\"row\">\r\n  <div class=\"col-md-12\">\r\n    <h2 align=\"center\"><!>nam<!></h2>\r\n  </div>\r\n  <div class=\"col-md-12 text-center\">\r\n    <!>cat<!>\r\n  </div>\r\n</div>\r\n<div class=\"row\">\r\n  <div class=\"col-md-6\">Author:\r\n    <strong><!>aut<!></strong>\r\n  </div>\r\n  <div class=\"col-md-6\">Updated:\r\n    <strong><!>mod<!></strong>\r\n  </div>\r\n</div>\r\n<div class=\"row\">\r\n  <div class=\"col-md-12\">\r\n    <!>con<!>\r\n  </div>\r\n</div>', 0);
  ");
}

$db->query("ALTER TABLE plg_cms_content ADD COLUMN slug varchar(255)");

$check = $db->query("SELECT id FROM plg_cms_categories")->count();
if($check < 1){
  $db->insert('plg_cms_categories',['category'=>'Default','perms'=>2,'subcat_of'=>0]);
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
