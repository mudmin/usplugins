<?php

    if((!isset($user) || !$user->isLoggedIn()) && $settings->registration==1){
    require ('assets/steamauthjoin.php');
?>
		<?php
if(!isset($_SESSION['steamid'])) {
    loginbutton();
	}  else {
    include ('assets/userInfoJoin.php');
    $lookupQ = $db->query("SELECT id,logins FROM users WHERE steam_id = ?",[$_SESSION['steamid']]);
    $lookupC = $lookupQ->count();
    if($lookupC > 0){
      $lookup = $lookupQ->first();
      $_SESSION[Config::get('session/session_name')] = $lookup->id;
      $db->update('users',$lookup->id, ['logins'=>$lookup->logins+1]);
      if(file_exists($abs_us_root.$us_url_root.'usersc/scripts/custom_login_script_no_redir')){
        include($abs_us_root.$us_url_root.'usersc/scripts/custom_login_script_no_redir');
      }
      if(file_exists($abs_us_root.$us_url_root.'usersc/scripts/custom_login_script')){
        include($abs_us_root.$us_url_root.'usersc/scripts/custom_login_script');
      }
      Redirect::to('account.php');
    }else {
      $checkUn = $db->query("SELECT id FROM users WHERE username = ?",[$steamprofile['personaname']])->count();
      if($checkUn < 1){
        $username = $steamprofile['personaname'];
      }else{
        $username = $steamprofile['personaname'].randomstring(6); //close enough
      }
      $fields = array(
        'username'=>$username,
        'steam_id'=>$steamprofile['steamid'],
        'steam_avatar'=>$steamprofile['avatarfull'],
        'steam_un'=>$steamprofile['personaname'],
        'username' => $username,
        'fname' => $username,
        'lname' => $username,
        'email' => $username."@".$settings->steam_domain,
        'password' => password_hash(randomstring(20), PASSWORD_BCRYPT, array('cost' => 12)),
        'permissions' => 1,
        'account_owner' => 1,
        'join_date' => date("Y-m-d H:i:s"),
        'email_verified' => 1,
        'active' => 1,
        'vericode' => randomstring(12),
        'vericode_expiry' => "2016-01-01 00:00:00"
      );
      $db->insert('users',$fields);
      $theNewId = $db->lastId();
      $fields = array(
        'user_id'=>$theNewId,
        'permission_id'=>1,
      );
      $db->insert('user_permission_matches',$fields);
      include($abs_us_root.$us_url_root.'usersc/scripts/during_user_creation.php');
      Redirect::to($us_url_root.'users/joinThankYou.php');
    }


  //   $check = $db->query("SELECT id FROM users WHERE steam_id = ?",[$steamprofile['steamid']])->count();
  //   if($check < 1){
  //   $fields = array(
  //     'steam_id'=>$steamprofile['steamid'],
  //     'steam_avatar'=>$steamprofile['avatarfull'],
  //     'steam_un'=>$steamprofile['personaname'],
  //   );
  //   $db->update('users',$user->data()->id,$fields);
  //
  //   Redirect::to($us_url_root.'users/account.php');
  // }else{
  //   Redirect::to($us_url_root.'users/account.php?err=This+LDAP+id+is+already+linked+to+an+account');
  // }
  }
	?>
<?php }else{
  Redirect::to($us_url_root."users/account.php");
} //end of security checks?>
