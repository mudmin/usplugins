<?php
global $db, $us_url_root;
//do not edit this file. Instead see custom_notification_rules.example.php
$allowed_to_send_notif = false;
if(isset($user) && $user->isLoggedIn()){
  if(pluginActive("usertags",true)){

    if(hasPerm(2) || hasTag("Manager",$user->data()->id)){
          $allowed_to_send_notif = true;
      }
  }else{
      if(hasPerm(2)){
          $allowed_to_send_notif = true;
      }
  }
}

 
   

  $notification_sound =  $us_url_root . "usersc/plugins/messaging/assets/ding.mp3";
