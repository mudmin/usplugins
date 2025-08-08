<?php
function pushoverNotification($to,$message){
  $db = DB::getInstance();
  $settings = $db->query("SELECT * FROM settings")->first();
  curl_setopt_array($ch = curl_init(), array(
  CURLOPT_URL => "https://api.pushover.net/1/messages.json",
  CURLOPT_POSTFIELDS => array(
    "token" => $settings->plg_po_token,
    "user" => $to,
    "message" => $message,
  ),
  CURLOPT_SAFE_UPLOAD => true,
  CURLOPT_RETURNTRANSFER => true,
));
curl_exec($ch);
curl_close($ch);
}
