<?php
$settings = $db->query("SELECT * FROM settings")->first();
$ip = ipCheck();
$message = "The IP $ip is banned but is trying to access this site.";
pushoverNotification($settings->plg_po_key,$message);
?>
