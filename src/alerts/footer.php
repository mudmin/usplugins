<?php
if(!isset($settings)){
  $settings = $db->query("SELECT * FROM settings")->first();
}

$alertType = isset($settings->alerts) ? preg_replace('/[^a-zA-Z0-9_-]/', '', basename($settings->alerts)) : 'default';
$alertPath = $abs_us_root.$us_url_root."usersc/plugins/alerts/assets/".$alertType."/alerts.php";

if($alertType && file_exists($alertPath)){
  include $alertPath;
}else{
  include $abs_us_root.$us_url_root."usersc/plugins/alerts/assets/default/alerts.php";
}
?>
