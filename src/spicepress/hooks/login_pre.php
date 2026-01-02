<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
//in the event you get a request to authenticate and you're already logged in, we don't want to miss that, so we will handle it here.
//there would normally be a redirect to the standard landing page, but we're going to catch the attempt first
// dnd("pre");
global $user;
if(isset($user) && $user->isLoggedIn()){
  require_once $abs_us_root.$us_url_root."usersc/plugins/spicepress/hooks/login_success.php";
}

?>
