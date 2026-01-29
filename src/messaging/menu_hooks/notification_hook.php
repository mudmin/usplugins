<?php
global $notificationTheme;
if(file_exists($abs_us_root . $us_url_root . "usersc/plugins/messaging/files/custom_notification_rules.php")){
  require_once $abs_us_root . $us_url_root . "usersc/plugins/messaging/files/custom_notification_rules.php";
}else{

  require_once $abs_us_root . $us_url_root . "usersc/plugins/messaging/files/standard_notification_rules.php";

}

if(!isset($notificationTheme)){
   $notificationTheme = "darkbg";
}
if(function_exists("displayNotificationsBadges")){
displayNotificationsBadges($from_menu = true, $theme = $notificationTheme);
}