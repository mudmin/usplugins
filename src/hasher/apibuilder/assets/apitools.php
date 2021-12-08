<?php
if(!function_exists("apibuilderAuth")){
  function apibuilderAuth($key){
    $lang = "en-US";
    $db = DB::getInstance();
    $settings = $db->query("SELECT * FROM plg_api_settings")->first();
    if($key == ''){
      return false;
    }

    if($settings->disabled == 1){
      return false;
    }

    $ip = ipCheckApi();
    $passed = 0;
    $ipcheck = $db->query("SELECT id FROM us_ip_blacklist WHERE ip = ?",[$ip])->count();
    if($ipcheck > 0){
      return false;
    }

    if ($settings->force_ssl==1 && $ip != "127.0.0.1"){
      if (!isset($_SERVER['HTTPS']) || !$_SERVER['HTTPS']) {
        return false;
      }
    }

    if($settings->api_auth_type == 2){ //pool of API keys
      $keycheckQ = $db->query("SELECT * FROM plg_api_pool WHERE api_key = ? AND blocked = 0",[$key]);
      $keycheckC = $keycheckQ->count();
      if($keycheckC > 0){
        $keycheck = $keycheckQ->first();
        $user_id = $keycheck->user_id;
        $descrip = $keycheck->descrip;
        $lang = "";
        $verified = "";
        $passed = 1;
        $valtype = "keypool";
      }else{
        apibuilderBan($ip);
      }
    }elseif($settings->api_auth_type == 3){ //pool of API keys with ip validation
      $keycheckQ = $db->query("SELECT * FROM plg_api_pool WHERE api_key = ? AND blocked = 0",[$key]);
      $keycheckC = $keycheckQ->count();
      if($keycheckC > 0){
        $keycheck = $keycheckQ->first();
        if($ip != '' && $keycheck->ip != '' && ($ip == $keycheck->ip || $ip == gethostbyname($keycheck->ip))){
          $user_id = $keycheck->user_id;
          $descrip = $keycheck->descrip;
          $lang = "";
          $verified = "";
          $passed = 1;
          $valtype = "keypoolwithip";
        }else{
          apibuilderBan($ip);
        }
      }else{
        apibuilderBan($ip);
      }
    }elseif($settings->api_auth_type == 4){ //keys linked to users
      $keycheckQ = $db->query("SELECT * FROM users WHERE apibld_key = ? AND apibld_blocked = 0 AND permissions = 1",[$key]);
      $keycheckC = $keycheckQ->count();
      if($keycheckC > 0){
        $usercheck = $keycheckQ->first();
        $user_id = $usercheck->id;
        $lang = $usercheck->language;
        $descrip = $usercheck->username;
        $verified = $usercheck->email_verified;
        $passed = 1;
        $valtype = "userkey";
      }else{
        apibuilderBan($ip);
      }
    }elseif($settings->api_auth_type == 5){ //keys linked to users with ip check
      $keycheckQ = $db->query("SELECT * FROM users WHERE apibld_key = ? AND apibld_blocked = 0 AND permissions = 1",[$key]);
      $keycheckC = $keycheckQ->count();
      if($keycheckC > 0){
        $keycheck = new stdClass();
        $usercheck = $keycheckQ->first();

        if($ip != '' && $usercheck->apibld_ip != '' && ($ip == $usercheck->apibld_ip || $ip == gethostbyname($usercheck->apibld_ip))){
          $user_id = $usercheck->id;
          $descrip = $usercheck->username;
          $lang = $usercheck->lang;
          $verified = $usercheck->email_verified;
          $passed = 1;
          $valtype = "userwithip";
        }else{
          apibuilderBan($ip);
        }
      }else{
        apibuilderBan($ip);
      }
    }else{

      return false;
    }
    if($passed == 1){
      $data = [
        'lang'=>$lang,
        'success'=>true,
        'valtype'=>$valtype,
        'user_id'=>$user_id,
        'verified'=>$verified,
        'descrip'=>$descrip,
        'apimode'=>$settings->api_auth_type,
      ];
      //clear any bad login attempts
      $q = $db->query("SELECT id FROM plg_api_fails WHERE ip = ?",[$ip]);
      $c = $q->count();
      if($c > 0){
        $f = $q->first();
        $db->query("DELETE FROM plg_api_fails WHERE ip = ?",[$ip]);
      }

      return $data;
    }else{
      return false;
    }
  }
}

if(!function_exists("apibuilderLogin")){
  function apibuilderLogin($username,$password){
    $db = DB::getInstance();
    $settings = $db->query("SELECT * FROM plg_api_settings")->first();

    if($settings->disabled == 1){
      return false;
    }

    if($settings->api_auth_type != 4 && $settings->api_auth_type != 5){
      return false;
    }

    $ip = ipCheckApi();
    $passed = 0;
    $ipcheck = $db->query("SELECT id FROM us_ip_blacklist WHERE ip = ?",[$ip])->count();
    if($ipcheck > 0){
      return false;
    }

    if ($settings->force_ssl==1 && $ip != "127.0.0.1"){
      if (!isset($_SERVER['HTTPS']) || !$_SERVER['HTTPS']) {
        return false;
      }
    }

    $q = $db->query("SELECT * FROM users WHERE username = ? AND permissions = ?",[$username,1]);
    $c = $q->count();
    if($c < 1){
      apibuilderBan($ip);
      return false;
    }else{
      $u = $q->first();
      if(!password_verify($password, $u->password)){
        apibuilderBan($ip);
        return false;
      }else{
        $success = false;
        //check if a static ip is required
        if($settings->api_auth_type == 5){
          if( $ip != '' && $u->apibld_ip != '' && ($ip == $u->apibld_ip || $ip == gethostbyname($u->apibld_ip) ) ){
            //success
            $success = true;
          }else{
            //fail
            apibuilderBan($ip);
            return false;
          }
        }else{ //no ip linking required
          $success = true;
        }
        if($success == true){
          $data = [
            'success'=>true,
            'valtype'=>"login",
            'user_id'=>$u->id,
            'descrip'=>$u->username,
            'verified'=>$u->email_verified,
            'lang'=>$u->language,
            'apimode'=>$settings->api_auth_type,
            'apikey'=>$u->apibld_key,
          ];
          $db->query("UPDATE users SET logins = logins+1 WHERE id = ?",[$u->id]);
          logger($u->id,"login","Logged in via API");
          //clear any bad login attempts
          $q = $db->query("SELECT id FROM plg_api_fails WHERE ip = ?",[$ip]);
          $c = $q->count();
          if($c > 0){
            $f = $q->first();
            $db->query("DELETE FROM plg_api_fails WHERE ip = ?",[$ip]);
          }

          return $data;
        }
      }
    }


    if($settings->api_auth_type == 4){ //keys linked to users
      $keycheckQ = $db->query("SELECT * FROM users WHERE apibld_key = ? AND apibld_blocked = 0 AND permissions = 1",[$key]);
      $keycheckC = $keycheckQ->count();
      if($keycheckC > 0){
        $usercheck = $keycheckQ->first();
        $user_id = $usercheck->id;
        $descrip = $usercheck->username;
        $passed = 1;
        $valtype = "userkey";
      }else{
        apibuilderBan($ip);
      }
    }elseif($settings->api_auth_type == 5){ //keys linked to users with ip check
      $keycheckQ = $db->query("SELECT * FROM users WHERE apibld_key = ? AND apibld_blocked = 0 AND permissions = 1",[$key]);
      $keycheckC = $keycheckQ->count();
      if($keycheckC > 0){
        $keycheck = new stdClass();
        $usercheck = $keycheckQ->first();

        if($ip != '' && $usercheck->apibld_ip != '' && ($ip == $usercheck->apibld_ip || $ip == gethostbyname($usercheck->apibld_ip))){
          $user_id = $usercheck->id;
          $descrip = $usercheck->username;
          $passed = 1;
          $valtype = "userwithip";
        }else{
          apibuilderBan($ip);
        }
      }else{
        apibuilderBan($ip);
      }
    }else{

      return false;
    }

  }
}



if(!function_exists("apibuilderBan")){
  function apibuilderBan($ip){
    $db = DB::getInstance();
    $settings = $db->query("SELECT * FROM settings")->first();
    if($ip != "127.0.0.1"){
      $q = $db->query("SELECT * FROM plg_api_fails WHERE ip = ?",[$ip]);
      $c = $q->count();
      if($c < 1){
        $fields = array(
          'ip'=>$ip,
          'blocked'=>0,
          'attempts'=>1,
        );
        $db->insert("plg_api_fails",$fields);
      }else{
        $f = $q->first();
        if($f->attempts >= $settings->api_fails-1){
          $fields = array(
            'ip'=>$ip,
            'reason'=>9999,
          );
          $db->insert("us_ip_blacklist",$fields);
          $db->update("plg_api_fails",$f->id,['blocked'=>1]);
        }else{
          $db->update("plg_api_fails",$f->id,['attempts'=>$f->attempts+1]);
        }
      }
    }
  }
}

if(!function_exists("ipCheckApi")){
  function ipCheckApi() {
    $ip = $_SERVER['REMOTE_ADDR'];
    if($ip == "::1"){
      $ip = "127.0.0.1";
    }
    return $ip;
  }
}

if(!function_exists("apiResponse")){
  function apiResponse($response){
    global $sendDevMessage;
    if(!$sendDevMessage){
      if(isset($response['devMessage'])){
        unset($response['devMessage']);
      }
    }
    echo json_encode($response);
    die();
  }
}

if(!function_exists("apiVersioning")){
  function apiVersioning($file){
    global $abs_us_root,$us_url_root,$version;
    //allow override in usersc/v1/filename etc
    if(file_exists($abs_us_root.$us_url_root."usersc/api/" . $version . "/" .$file)){
      include $abs_us_root.$us_url_root."usersc/api/" . $version . "/" .$file;

      //use standard api/v1/filename
    }elseif(file_exists($abs_us_root.$us_url_root."api/" . $version . "/" .$file)){
      include  "./" . $version . "/" .$file;
    }else{
      die("API File $file is missing");
    }
  }
}
