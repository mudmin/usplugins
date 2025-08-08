<?php
$settings = $db->query("SELECT * FROM settings")->first();
$ip = ipCheck();
$email = Input::get('email');
$message = "A new email $email registered from $ip";
pushoverNotification($settings->plg_po_key,$message);
?>
