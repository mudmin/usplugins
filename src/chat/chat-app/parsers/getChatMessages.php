<?php
require_once ('../../../../../users/init.php');
require_once ($abs_us_root.$us_url_root."usersc/plugins/chat/chat-app/helpers.php");
if(isset($custom_chat_room) && is_numeric($custom_chat_room)){
  $event_override = $event_id = $custom_chat_room;
}else{
  $event_override = $event_id = 1;
}

$sql = "SELECT 
msg.*, 
users.id as user_id, 
users.fname as user_fname, 
CASE 
  WHEN upm.permission_id = 2 THEN CONCAT(users.lname, ' (Admin)')
  ELSE users.lname 
END as user_lname, 
users.picture as user_picture
FROM 
plg_chat_messages as msg
JOIN 
users ON msg.user_id = users.id
LEFT JOIN 
user_permission_matches as upm ON users.id = upm.user_id
WHERE 
msg.event_id = ?
ORDER BY 
msg.id DESC
LIMIT 
500
";

$msgs = $db->query($sql,[$event_override])->results();
$lastId = !empty($msgs)? $msgs[0]->id : '';
$decodedMsgs = array_walk($msgs, function($msg){
  $msg->msg = html_entity_decode($msg->msg, ENT_QUOTES);
  return $msg;
});

$user_ids = [];

$msgUsers = $db->query("SELECT user_id from plg_chat_messages WHERE event_id = ? GROUP BY user_id",[$event_override])->results();
foreach($msgUsers as $msg){
  $user_ids[] = $msg->user_id;
}

$sessionUsers = $db->query("SELECT user_id from plg_chat_sessions WHERE event_id = ? GROUP BY user_id",[$event_override])->results();
foreach($sessionUsers as $session){
  if(!in_array($session->user_id, $user_ids)){
    $user_ids[] = $session->user_id;
  }
}

$participants = [];
if(!empty($user_ids)){
  // HARDENING: Generate a string of placeholders (?,?,?) based on the number of user IDs
  $placeholders = implode(',', array_fill(0, count($user_ids), '?'));

  $sql = "SELECT 
    users.fname, 
    CASE 
      WHEN MAX(upm.permission_id) = 2 THEN CONCAT(users.lname, ' (Admin)')
      ELSE users.lname 
    END as lname, 
    users.id, 
    sessions.id as session_id, 
    IF(MAX(sessions.id) IS NOT NULL, 1, 0) as session_active
  FROM 
    users
  LEFT JOIN 
    plg_chat_sessions as sessions ON sessions.user_id = users.id
  LEFT JOIN 
    user_permission_matches as upm ON users.id = upm.user_id
  WHERE 
    users.id IN($placeholders)
  GROUP BY 
    users.id, users.fname, users.lname
  ORDER BY 
    session_active DESC, lname, users.fname
";

  // Pass the $user_ids array as the second parameter to bind them safely
  $participants = $db->query($sql, $user_ids)->results();
}

$currentActiveChatters = 0;
foreach($participants as $participant){
  if($participant->session_active == 1) $currentActiveChatters += 1;
}

jsonResponse(['success' => true, 'msgs' => $msgs, 'lastId' => $lastId, "active" => $currentActiveChatters, "participants" => $participants]);