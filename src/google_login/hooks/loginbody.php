<?php
require_once '../users/init.php';
global $settings, $user, $db, $authUrl, $us_url_root, $abS_us_root;
if($settings->glogin==1 && !$user->isLoggedIn()){
    require_once $abs_us_root.$us_url_root.'usersc/plugins/google_login/assets/google_oauth_login.php';
  }
?>
