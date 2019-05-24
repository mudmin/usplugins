<?php
    require '../../../users/init.php';
    require_once $abs_us_root.$us_url_root.'users/includes/template/prep.php';
    include "plugin_info.php";
    pluginActive($plugin_name);
    if(isset($user) && $user->isLoggedIn()){
    require ('assets/steamauthlink.php');

?>
    <style>
        .table {
            table-layout: fixed;
            word-wrap: break-word;
        }
    </style>
  </head>

  <body style="background-color: #EEE;">
    <div class="container" style="margin-top: 30px; margin-bottom: 30px; padding-bottom: 10px; background-color: #FFF;">
		<h1 align="center">Link Your LDAP Account</h1>
		<hr>
		<?php
if(!isset($_SESSION['steamid'])) {
    echo "<div style='margin: 30px auto; text-align: center;'>Once you link your account, you will be able to login with LDAP!<br>";
    loginbutton();
	echo "</div>";
	}  else {
    include ('assets/userInfo.php');
    $check = $db->query("SELECT id FROM users WHERE steam_id = ?",[$steamprofile['steamid']])->count();
    if($check < 1){
    $fields = array(
      'steam_id'=>$steamprofile['steamid'],
      'steam_avatar'=>$steamprofile['avatarfull'],
      'steam_un'=>$steamprofile['personaname'],
    );
    $db->update('users',$user->data()->id,$fields);

    Redirect::to($us_url_root.'users/account.php');
  }else{
    Redirect::to($us_url_root.'users/account.php?err=This+LDAP+id+is+already+linked+to+an+account');
  }
  }
	?>
<?php }else{
  Redirect::to($us_url_root."users/account.php");
} //end of security checks?>
