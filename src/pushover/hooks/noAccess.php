<?php
global $user;
$settings = $db->query("SELECT * FROM settings")->first();
$currentPage = currentPage();
$ip = ipCheck();
$name = $GLOBALS['config']['session']['session_name'];


if(is_numeric($_SESSION[$name])){
  $q = $db->query("SELECT username FROM users WHERE id = ?",[$_SESSION[$name]]);
  $c = $q->count();
  if($c < 1){
    $un = "Unknown user";
  }else{
    $u = $q->first();
    $un = $u->username;
  }
}else{
  $un = "Unknown user";
}

$message = "$un attempted to visit $currentPage without permission from $ip";
pushoverNotification($settings->plg_po_key,$message);
?>
