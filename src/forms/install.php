<?php
require_once("init.php");
//For security purposes, it is MANDATORY that this page be wrapped in the following
//if statement. This prevents remote execution of this code.
if (in_array($user->data()->id, $master_account)){


$db = DB::getInstance();
$plugin_name = "forms"; //change this for your plugin!
$plugin_name = strtolower($plugin_name);//you're welcome
$cpyfail = 0;
$check = $db->query("SHOW TABLES LIKE '%us_form_views%'")->count();
if($check < 1){
$db->query("CREATE TABLE `us_forms` (
  `id` int(11) NOT NULL,
  `form` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$db->query("CREATE TABLE `us_form_validation` (
  `id` int(11) NOT NULL,
  `value` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `params` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$db->query("INSERT INTO `us_form_validation` (`id`, `value`, `description`, `params`) VALUES
(1, 'min', 'Minimum # of Characters', 'number'),
(2, 'max', 'Maximum # of Characters', 'number'),
(3, 'is_numeric', 'Must be a number', 'true'),
(4, 'valid_email', 'Must be a valid email address', 'true'),
(5, '<', 'Must be a number less than', 'number'),
(6, '>', 'Must be a number greater than', 'number'),
(7, '<=', 'Must be a number less than or equal to', 'number'),
(8, '>=', 'Must be a number greater than or equal to', 'number'),
(9, '!=', 'Must not be equal to', 'text'),
(10, '==', 'Must be equal to', 'text'),
(11, 'is_integer', 'Must be an integer', 'true'),
(12, 'is_timezone', 'Must be a valid timezone name', 'true'),
(13, 'is_datetime', 'Must be a valid DateTime', 'true');");

$db->query("CREATE TABLE `us_form_views` (
  `id` int(11) NOT NULL,
  `form_name` varchar(255) NOT NULL,
  `view_name` varchar(255) NOT NULL,
  `fields` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$db->query("ALTER TABLE `us_forms`
  ADD PRIMARY KEY (`id`);");

$db->query("ALTER TABLE `us_form_validation`
  ADD PRIMARY KEY (`id`);");

$db->query("ALTER TABLE `us_form_views`
  ADD PRIMARY KEY (`id`);");

$db->query("ALTER TABLE `us_forms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;");
$db->query("ALTER TABLE `us_form_validation`

  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;");
$db->query("ALTER TABLE `us_form_views`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;");
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
if($cpyfail == 1){
	echo "The plugin installed but did not have permission to copy files.<br>";
	echo "Please copy the files in the 'files' folder to the users/views directory.<br>";
	echo "Please note that you will most likely have to remove these files manually if you uninstall the plugin.<br>";

}

} //do not perform actions outside of this statement
