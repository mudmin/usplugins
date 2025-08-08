<?php
function authenticatePasswordlessEmail($pwl){
  $db = DB::getInstance();
  $uid = hexdec(strtok($pwl, '!'));
  if(!is_numeric($uid)){
    logger(0,"Passwordless","An non-numeric or blank user id $uid attempted a pwl login");
    return false;
  }
  $key = substr($pwl, strpos($pwl, "!") + 1);
  $q = $db->query("SELECT * FROM users WHERE id = ?",[$uid]);
  $c = $q->count();
  if($c < 1){
    logger(0,"Passwordless","An invalid user id $uid attempted a pwl login");
    return false;
  }else{
    $u = $q->first();
    if($u->pwl_to < date("Y-m-d H:i:s")){
      logger($uid,"Passwordless","Link expired");
      return false;
    }
    if(password_verify($key,$u->pwl)){
      logger($uid,"Passwordless","Logged in");
      //invalidate link
      $db->update("users",$uid,['pwl_to'=>"2018-02-04 20:52:52"]);
      Session::put(Config::get('session/session_name'), $uid);
      $hash = Hash::unique();
      $hashCheck = $db->get('users_session', ['user_id', '=', $uid]);
      $db->insert('users_session', [
              'user_id' => $uid,
              'hash' => $hash,
              'uagent' => Session::uagent_no_version(),
          ]);

      Cookie::put(Config::get('remember/cookie_name'), $hash, Config::get('remember/cookie_expiry'));

      $date = date('Y-m-d H:i:s');
      $db->query('UPDATE users SET last_login = ?, logins = logins + 1 WHERE id = ?', [$date, $uid]);
      $_SESSION['last_confirm'] = date('Y-m-d H:i:s');
      $db->insert('logs', ['logdate' => $date, 'user_id' => $uid, 'logtype' => 'Login', 'lognote' => 'User logged in.', 'ip' => $_SERVER['REMOTE_ADDR']]);
      $ip = ipCheck();
      $q = $db->query('SELECT id FROM us_ip_list WHERE ip = ?', [$ip]);
      $c = $q->count();
      if ($c < 1) {
          $db->insert('us_ip_list', [
              'user_id' => $uid,
              'ip' => $ip,
          ]);
      } else {
          $f = $q->first();
          $db->update('us_ip_list', $f->id, [
              'user_id' => $uid,
              'ip' => $ip,
          ]);
      }
      return true;
    }else{
      logger($uid,"Passwordless","Invalid login detected. Bad password");
      return false;
    }
  }
}




function sendPasswordlessEmail($email){
  $db = DB::getInstance();
  $emset = $db->query("SELECT * FROM email")->first();
  $q = $db->query("SELECT * FROM users WHERE email = ?",[$email]);
  $c = $q->count();
  if($c < 1){
    logger(0,"Passwordless",$email." - not found");
    sleep(3);
    return false;
  }else{
    $f = $q->first();
    $ps = $db->query("SELECT * FROM plg_passwordless_settings")->first();
    $link = randomstring(15).uniqid();
    $fields = [
      'pwl'=>password_hash($link,PASSWORD_BCRYPT,array('cost' => 12)),
      'pwl_to'=>date("Y-m-d H:i:s",strtotime("+ ".$ps->timeout." minutes",strtotime(date("Y-m-d H:i:s") ) ) ),
    ];
    $db->update("users",$f->id,$fields);
    $body = $ps->top."<br>";
    $uid = dechex((int)$f->id);
    $pwl = $uid."!".$link;

    $body .= "<a href='".$emset->verify_url.$ps->link."?pwl=$pwl' target='_'>".$emset->verify_url.$ps->link."?pwl=$pwl</a><br>";
    $body .= $ps->bottom;
    email($email,$ps->subject,$body);
    return true;
  }
}
