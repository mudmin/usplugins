<?php
if(isset($user) && $user->isLoggedIn()){
  Redirect::to($us_url_root.'users/account.php');
}else{
  Redirect::to($us_url_root.'users/login.php');
}
