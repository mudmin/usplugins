<?php
$settings = $db->query("SELECT * FROM settings")->first();
$ip = ipCheck();
$un = Input::get('username');
$message = "A failed login attempt happened for $un from $ip";
pushoverNotification($settings->plg_po_key,$message);
?>
