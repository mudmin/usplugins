<?php
//you can rename this file to "custom_notification_rules.php" and setup your own rules to override the default rules
$allowed_to_send_notif = false;

  if(pluginActive("usertags",true)){
      if(hasPerm(2) || hasTag("Manager",$user->data()->id)){
          $allowed_to_send_notif = true;
      }
  }else{
      if(hasPerm(2)){
          $allowed_to_send_notif = true;
      }
  }

  $notification_sound =  $us_url_root . "usersc/plugins/messaging/assets/ding.mp3";

 