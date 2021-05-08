<?php
if(!isset($settings)){
  $settings = $db->query("SELECT * FROM settings")->first();
}

if(file_exists($abs_us_root.$us_url_root."usersc/plugins/alerts/assets/".$settings->alerts."/alerts.php")){
  include $abs_us_root.$us_url_root."usersc/plugins/alerts/assets/".$settings->alerts."/alerts.php";
}else{
  include $abs_us_root.$us_url_root."usersc/plugins/alerts/assets/default/alerts.php";
}
?>
