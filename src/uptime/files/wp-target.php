<?php
//this tartget MUST be in the root or you need to adjust the paths
$ip = $_SERVER['REMOTE_ADDR'];

// //once you are setup, it is recommended that you comment in these lines to
// //only allow certain IPs to perform this check.
// if(
//   $ip != "xxx.xxx.xxx.xxx"  //primary ip
//   && $ip != "xxx.xxx.xxx.xxx" //optional secondary ip
// ){
//   die("Permission denied-".$ip);
// }
$return = [];
if(file_exists("wp-includes/version.php")){
  include "wp-includes/version.php";
  $return['usver'] = $wp_version;
}

$return['alive'] = true;
$return['phpver'] = phpversion();
echo json_encode($return);
if(file_exists("wp-load.php")){
  include "wp-load.php";
}
