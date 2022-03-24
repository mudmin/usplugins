<?php
require_once ('../../../../../users/init.php');
require_once ($abs_us_root.$us_url_root."usersc/plugins/chat/chat-app/helpers.php");
$event_override = $event_id = 1; //compatibility

$db = DB::getInstance();

$sql = "
  SELECT msg.*, users.id as user_id, users.fname as user_fname, users.lname as user_lname, users.picture as user_picture
  FROM plg_chat_messages as msg
  JOIN users ON msg.user_id = users.id
  WHERE event_id = ?
  ORDER BY msg.id DESC
  LIMIT 500
";

$msgs = $db->query($sql,[$event_override])->results();
$lastId = !empty($msgs)? $msgs[0]->id : '';
$decodedMsgs = array_walk($msgs, function($msg){
  $msg->msg = html_entity_decode($msg->msg, ENT_QUOTES);
  return $msg;
});

$user_ids = [];

// GET ALL MESSAGE USERIDS
$msgUsers = $db->query("SELECT user_id from plg_chat_messages WHERE event_id = ? GROUP BY user_id",[$event_override])->results();
foreach($msgUsers as $msg){
  $user_ids[] = $msg->user_id;
}
// GET ALL SESSION USRIDS
$sessionUsers = $db->query("SELECT user_id from plg_chat_sessions WHERE event_id = ? GROUP BY user_id",[$event_override])->results();
foreach($sessionUsers as $session){
  if(!in_array($session->user_id, $user_ids)){
    $user_ids[] = $session->user_id;
  }
}
$participants = [];
if(!empty($user_ids)){
  $userIdString = "'" . implode("', '", $user_ids) . "'";


  $sql = "
    SELECT users.fname, users.lname, users.id, sessions.id as session_id, IF(sessions.id IS NOT NULL, 1, 0) as session_active
    FROM users
    LEFT JOIN plg_chat_sessions as sessions ON sessions.user_id = users.id
    WHERE users.id IN({$userIdString})
    GROUP BY users.id
    ORDER BY session_active DESC, users.lname, users.fname
  ";
  $participants = $db->query($sql)->results();
}

$currentActiveChatters = 0;
foreach($participants as $participant){
  if($participant->session_active == 1) $currentActiveChatters += 1;
}

jsonResponse(['success' => true, 'msgs' => $msgs, 'lastId' => $lastId, "active" => $currentActiveChatters, "participants" => $participants]);
