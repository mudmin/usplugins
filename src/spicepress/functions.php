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
//a match needs the same scheme, host and port, and the path has to sit at or under the authorized path
function verifyReturnUrl($url){
  global $db;
  $req = spicePressParseUrl($url);
  if($req === false){
    return false;
  }
  $list = $db->query("SELECT * FROM plg_spicepress_authorized_urls")->results();
  foreach($list as $l){
    $auth = spicePressParseUrl(trim((string)$l->url));
    if($auth === false){
      continue;
    }
    if($req['scheme'] !== $auth['scheme'] || $req['host'] !== $auth['host'] || $req['port'] !== $auth['port']){
      continue;
    }
    $authPath = rtrim($auth['path'], '/');
    if($authPath === '' || $req['path'] === $authPath || strpos($req['path'], $authPath.'/') === 0){
      return true;
    }
  }
  //finished searching all urls without a match, so return false
  return false;
}

//splits an absolute url into lowercase scheme and host, port (default filled in) and path
//returns false for relative urls, urls with userinfo, or urls with backslashes, whitespace or control characters
function spicePressParseUrl($url){
  if(!is_string($url) || $url === '' || preg_match('/[\\\\\s\x00-\x1f\x7f]/', $url)){
    return false;
  }
  $parts = parse_url($url);
  if($parts === false || empty($parts['scheme']) || empty($parts['host'])){
    return false;
  }
  if(isset($parts['user']) || isset($parts['pass'])){
    return false;
  }
  $scheme = strtolower($parts['scheme']);
  $defaults = ['http'=>80, 'https'=>443];
  if(isset($parts['port'])){
    $port = (int)$parts['port'];
  }elseif(isset($defaults[$scheme])){
    $port = $defaults[$scheme];
  }else{
    $port = null;
  }
  return [
    'scheme' => $scheme,
    'host'   => strtolower($parts['host']),
    'port'   => $port,
    'path'   => isset($parts['path']) && $parts['path'] !== '' ? $parts['path'] : '/',
  ];
}
