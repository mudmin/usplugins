<?php
require_once("init.php");
//For security purposes, it is MANDATORY that this page be wrapped in the following
//if statement. This prevents remote execution of this code.
if (in_array($user->data()->id, $master_account)){


$db = DB::getInstance();
include "plugin_info.php";

//all actions should be performed here.

$db->query("CREATE TABLE `plg_tickets` (
  `id` int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `subject` varchar(255) DEFAULT NULL,
  `issue` text DEFAULT NULL,
  `user` int(11) default 0,
  `agent` int(11) default 0,
  `category` varchar(255),
  `status` varchar(255),
  `closed` tinyint(1) default 0,
  `created` datetime,
  `last_updated` datetime
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
)");

$db->query("CREATE TABLE `plg_tickets_form` (
  `id` int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `ord` int(11) NOT NULL,
  `col` varchar(255) NOT NULL,
  `form_descrip` varchar(255) NOT NULL,
  `table_descrip` varchar(255) NOT NULL,
  `col_type` varchar(255) NOT NULL,
  `field_type` varchar(100) NOT NULL,
  `length` int(11) NOT NULL,
  `required` tinyint(1) NOT NULL,
  `validation` text NOT NULL,
  `label_class` varchar(255) NOT NULL,
  `field_class` varchar(255) NOT NULL,
  `input_html` text NOT NULL,
  `select_opts` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$check = $db->query("SELECT * FROM plg_tickets_form")->count();
if($check < 1){
	$db->query("
	INSERT INTO `plg_tickets_form` (`id`, `ord`, `col`, `form_descrip`, `table_descrip`, `col_type`, `field_type`, `length`, `required`, `validation`, `label_class`, `field_class`, `input_html`, `select_opts`) VALUES
	(1, 10, 'subject', 'Subject', 'Subject', 'varchar', 'text', 0, 1, '', '', 'form-control', '', '{\"\":\"\"}'),
	(2, 20, 'issue', 'Please give a detailed description of the issue', 'Description', 'text', 'textarea', 0, 1, '', '', 'form-control', '', '{\"\":\"\"}')
	");
}

$db->query("CREATE TABLE `plg_tickets_settings` (
  `id` int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `perm` int(11) default 2,
	`perm_to_assign` int(11) default 2,
  `agent_term` varchar(255) default 'agent',
	`cat_term` varchar(255) default 'department',
	`cat_enabled` tinyint(1) default 0,
  `agents_act` tinyint(1) default 1,
  `users_act` tinyint(1) default 1,
	`must_login` tinyint(1) default 1,
	`email_agent` tinyint(1) default 0,
	`email_user` tinyint(1) default 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$check = $db->query("SELECT * FROM plg_tickets_settings")->count();
$db->query("ALTER TABLE plg_tickets_settings ADD column email_new text");
$db->query("ALTER TABLE plg_tickets_settings ADD column ticket_view varchar(255) default 'usersc/plugins/tickets/tickets.php'");
$db->query("ALTER TABLE plg_tickets_settings ADD column single_view varchar(255) default 'usersc/plugins/tickets/ticket.php'");
if($check < 1){
	$db->insert("plg_tickets_settings",['id'=>1]);
}

$db->query("CREATE TABLE `plg_tickets_cats` (
  `id` int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `cat` varchar(255) default 'Uncategorized'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$check = $db->query("SELECT * FROM plg_tickets_cats")->count();
if($check < 1){
  $db->insert("plg_tickets_cats",['id'=>1]);
}

$db->query("CREATE TABLE `plg_tickets_status` (
  `id` int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `status` varchar(255) default 'New'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$check = $db->query("SELECT * FROM plg_tickets_status")->count();
if($check < 1){
  $db->insert("plg_tickets_status",['id'=>1]);
}

$db->query("CREATE TABLE `plg_tickets_notes` (
  `id` int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `ticket` int(11),
	`note` text,
  `user` int(11),
  `ts` timestamp
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$db->query("
CREATE TABLE `plg_tickets_cats_form` (
  `id` int(11)  UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `ord` int(11) NOT NULL,
  `col` varchar(255) NOT NULL,
  `form_descrip` varchar(255) NOT NULL,
  `table_descrip` varchar(255) NOT NULL,
  `col_type` varchar(255) NOT NULL,
  `field_type` varchar(100) NOT NULL,
  `required` tinyint(1) NOT NULL,
  `validation` text NOT NULL,
  `label_class` varchar(255) NOT NULL,
  `field_class` varchar(255) NOT NULL,
  `input_html` text NOT NULL,
  `select_opts` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

$check = $db->query("SELECT * FROM plg_tickets_cats_form")->count();
if($check < 1){
$db->query("INSERT INTO `plg_tickets_cats_form` (`id`, `ord`, `col`, `form_descrip`, `table_descrip`, `col_type`, `field_type`, `required`, `validation`, `label_class`, `field_class`, `input_html`, `select_opts`) VALUES
(1, 10, 'cat', 'Category', 'Category', 'varchar', 'text', 0, '', '', 'form-control', '', '');");
}

$db->query("
CREATE TABLE `plg_tickets_status_form` (
  `id` int(11)  UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `ord` int(11) NOT NULL,
  `col` varchar(255) NOT NULL,
  `form_descrip` varchar(255) NOT NULL,
  `table_descrip` varchar(255) NOT NULL,
  `col_type` varchar(255) NOT NULL,
  `field_type` varchar(100) NOT NULL,
  `required` tinyint(1) NOT NULL,
  `validation` text NOT NULL,
  `label_class` varchar(255) NOT NULL,
  `field_class` varchar(255) NOT NULL,
  `input_html` text NOT NULL,
  `select_opts` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

$check = $db->query("SELECT * FROM plg_tickets_status_form")->count();
if($check < 1){
$db->query("INSERT INTO `plg_tickets_status_form` (`id`, `ord`, `col`, `form_descrip`, `table_descrip`, `col_type`, `field_type`, `required`, `validation`, `label_class`, `field_class`, `input_html`, `select_opts`) VALUES
(1, 10, 'status', 'Status', 'Status', 'varchar', 'text', 0, '', '', 'form-control', '', '');");
}

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
$hooks['account.php']['bottom'] = 'hooks/account.php';
// $hooks['login.php']['form'] = 'hooks/loginform.php';
// $hooks['login.php']['bottom'] = 'hooks/loginbottom.php';
// $hooks['login.php']['post'] = 'hooks/loginpost.php';
registerHooks($hooks,$plugin_name);

} //do not perform actions outside of this statement
