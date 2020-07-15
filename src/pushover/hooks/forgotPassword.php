<?php
$settings = $db->query("SELECT * FROM settings")->first();
$email = Input::get('email');
$ip = ipCheck();
$message = "Someone is trying to reset $email from $ip";
pushoverNotification($settings->plg_po_key,$message);
?>
