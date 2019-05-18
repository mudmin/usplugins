<?php
require_once '../users/init.php';
global $settings, $user, $db, $authUrl, $us_url_root, $abS_us_root;
if($settings->fblogin==1 && !$user->isLoggedIn()){
    require_once $abs_us_root.$us_url_root.'usersc/plugins/facebook_login/assets/facebook_oauth.php';
  }
?>
