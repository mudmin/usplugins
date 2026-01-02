<?php
function createSpicePressSession($uid = false, $id = 1){
  global $db,$user;
  $resp = [];
  $resp['success'] = false;
  $resp['msg'] = "";
  $resp['code'] = false;
  $resp['expires'] = date("Y-m-d H:i:s");
  if($uid == false && !$user->isLoggedIn()){
    $resp['msg'] = "User not logged in and not specified";
    return $resp;
  }else{
    $uid = $user->data()->id;
  }

  $check = $db->query("SELECT id FROM users WHERE id = ?",[$uid])->count();
  if($check < 1){
    $resp['msg'] = "User could not be found";
    return $resp;
  }

  $setQ = $db->query("SELECT * FROM plg_spicepress_settings WHERE id = ?",[$id]);
  $setC = $setQ->count();
  if($setC < 1){
    $resp['msg'] = "Settings row could not be found";
    return $resp;
  }
  $set = $setQ->first();
  $resp['code'] = randomstring(36);
  $resp['expires'] = date("Y-m-d H:i:s",strtotime("+ ".$set->expires_hours." hours",strtotime(date("Y-m-d H:i:s"))));
  $fields = [
    "user_id"=>$uid,
    "session"=>$resp['code'],
    "expires"=>$resp['expires']
  ];
  $db->insert("plg_spicepress_sessions",$fields);
  $resp['msg'] = "Session created";
  $resp['success'] = true;
  return $resp;
}

function checkSpicePressSession($code){
  global $db;
  if($code == ""){
    return false;
  }
  $code = Input::sanitize($code);
  $code = substr($code,0,255);
  $q = $db->query("SELECT * FROM plg_spicepress_sessions WHERE session = ?",[$code]);
  $c = $q->count();
  if($c < 1){
    return false;
  }else{
    $f = $q->first();
    if($f->expires > date("Y-m-d H:i:s")){
      return true;
    }else{
      //expired
      return false;
    }
  }
}

//we're going through the urls in the db and looking for a valid one
function verifyReturnUrl($url){
  global $db;
  $list = $db->query("SELECT * FROM plg_spicepress_authorized_urls")->results();
  foreach($list as $l){
      if (strpos($url, $l->url) !== false) {
        return true;
      }
  }
  //finished searching all urls without a match, so return false
  return false;
}
