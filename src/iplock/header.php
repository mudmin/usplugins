<?php
global $user;
$ip = ipCheck();
if($ip != "::1" && $ip != "127.0.0.1"){
if($settings->plg_iplock == 1 && $user->isLoggedIn() && hasPerm([2],$user->data()->id)){
  $check = $db->query("SELECT ip FROM us_ip_whitelist WHERE ip = ?",[$ip])->count();
  if($check < 1){
    Redirect::to($us_url_root.'users/logout.php');
  }
}

if($settings->plg_iplock == 2 && $user->isLoggedIn()){
  $check = $db->query("SELECT ip FROM us_ip_whitelist WHERE ip = ?",[$ip])->count();
  if($check < 1){
    Redirect::to($us_url_root.'users/logout.php');
  }
}

if($settings->plg_iplock == 3){
  $check = $db->query("SELECT ip FROM us_ip_whitelist WHERE ip = ?",[$ip])->count();
  if($check < 1){
    die;
  }
}
}
