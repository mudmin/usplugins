<?php
require_once 'users/init.php';

//you can whitelist this to only respond to your uptime checking server with
// if(ipCheck() != "8.8.8.8"){ //insert your server's ip here
//   die;
// }

$return = [];
$return['alive'] = true;
if(file_exists($abs_us_root.$us_url_root."users/includes/user_spice_ver")){
  include $abs_us_root.$us_url_root."users/includes/user_spice_ver";
}

if(isset($user_spice_ver)){
  $return['usver'] = $user_spice_ver;
}else{
  $return['usver'] = "0.0.0";
}

$return['phpver'] = phpversion();

echo json_encode($return);
