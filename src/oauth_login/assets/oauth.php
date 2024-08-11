<?php
if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
global $user;

if($settings->oauth==1 && !$user->isLoggedIn()){

	$link = $us_url_root . "usersc/plugins/oauth_login/assets/oauth_request.php";

}
?>