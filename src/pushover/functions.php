<?php
function pushoverNotification($to, $message, $priority = 0, $html = 0) {
  $db = DB::getInstance();
  $settings = $db->query("SELECT * FROM settings")->first();
  $postFields = [
    "token" => $settings->plg_po_token,
    "user" => $to,
    "message" => $message,
    "priority" => $priority,
  ];
  if ($html) {
    $postFields["html"] = 1;
  }
  curl_setopt_array($ch = curl_init(), [
    CURLOPT_URL => "https://api.pushover.net/1/messages.json",
    CURLOPT_POSTFIELDS => $postFields,
    CURLOPT_SAFE_UPLOAD => true,
    CURLOPT_RETURNTRANSFER => true,
  ]);
  curl_exec($ch);
  curl_close($ch);
}

function pushoverGetIpInfo() {
  $db = DB::getInstance();
  $ip = ipCheck();
  $isWhitelisted = $db->query('SELECT id FROM us_ip_whitelist WHERE ip = ?', [$ip])->count() > 0;
  return [
    'ip' => $ip,
    'whitelisted' => $isWhitelisted,
    'status' => $isWhitelisted ? 'WHITELISTED' : 'Not Whitelisted',
  ];
}

function pushoverIsAdmin($userId) {
  return hasPerm([2], $userId);
}

function pushoverSecurityAlert($event, $details = []) {
  $db = DB::getInstance();
  $settings = $db->query("SELECT * FROM settings")->first();
  $ipInfo = pushoverGetIpInfo();

  $priorities = [
    'hitBanned' => 1,
    'loginFail' => 0,
    'noAccess' => 0,
    'forgotPassword' => 0,
    'forgotPasswordAdmin' => 1,
    'join' => -1,
    'loginSuccess' => -1,
    'loginSuccessAdmin' => 0,
    'passwordResetSuccess' => 0,
    'passwordResetSuccessAdmin' => 1,
  ];

  $priority = $priorities[$event] ?? 0;

  $user = $details['user'] ?? 'Unknown';
  $extra = $details['extra'] ?? '';

  $message = "<b>{$event}</b>\n";
  if (!empty($user) && $user !== 'Unknown') {
    $message .= "User: {$user}\n";
  }
  $message .= "IP: {$ipInfo['ip']} ({$ipInfo['status']})";
  if (!empty($extra)) {
    $message .= "\n{$extra}";
  }

  pushoverNotification($settings->plg_po_key, $message, $priority, 1);
}
