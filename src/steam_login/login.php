<?php

    if(!isset($user) || !$user->isLoggedIn()){
    require ('assets/steamauthlogin.php');
?>
		<?php
if(!isset($_SESSION['steamid'])) {
    loginbutton();
	}  else {
    $lookupQ = $db->query("SELECT id,logins FROM users WHERE steam_id = ?",[$_SESSION['steamid']]);
    $lookupC = $lookupQ->count();
    if($lookupC > 0){
      $lookup = $lookupQ->first();
      $_SESSION['user'] = $lookup->id;
      $db->update('users',$lookup->id, ['logins'=>$lookup->logins+1]);
      if(file_exists($abs_us_root.$us_url_root.'usersc/scripts/custom_login_script_no_redir')){
        include($abs_us_root.$us_url_root.'usersc/scripts/custom_login_script_no_redir');
      }
      if(file_exists($abs_us_root.$us_url_root.'usersc/scripts/custom_login_script')){
        include($abs_us_root.$us_url_root.'usersc/scripts/custom_login_script');
      }
      Redirect::to('account.php');
    }else {
      Redirect::to($us_url_root.'users/login.php?err=This+Steam+account+is+not+linked+to+a+user+on+this+site');
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
  //   Redirect::to($us_url_root.'users/account.php?err=This+Steam+id+is+already+linked+to+an+account');
  // }
  }
	?>
<?php }else{
  Redirect::to($us_url_root."users/account.php");
} //end of security checks?>
