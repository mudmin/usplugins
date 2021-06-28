<?php
require_once '../../../users/init.php';
global $user;
$db = DB::getInstance();

//BE SURE to set the permissions of the parser file to the same ones you want being able to access the page with this data.

if(!hasPerm([2],$user->data()->id)){
  die("insufficient permissions");
}
if(!pluginActive("watchdog",true)){
  die("plugin is not active");
}

$request = Input::get('request');

if($request == "usersWithOnlineStatus"){
  //in this example, we're going to query the usernames and determine whether they're online or not.
  $cutoff = Input::get('offline_after');
  if(is_numeric($cutoff)){
    $date = date("Y-m-d H:i:s",strtotime("-$cutoff seconds",strtotime(date("Y-m-d H:i:s"))));
    $users = $db->query("SELECT id,username,last_watchdog FROM users ORDER BY username")->results();
    $data = "";
    foreach($users as $u){

      if($u->last_watchdog <= $date){
        $img = $us_url_root."usersc/plugins/watchdog/images/offline.png";
        $alt = "offline";
      }else{
        $img = $us_url_root."usersc/plugins/watchdog/images/online.png";
        $alt = "online";
      }

      $data .= "<div class='col-2'><img height='15px' src='";
      $data .= $img."' alt='".$alt."'> ".$u->username."</div>";

    }
  }
$msg = [];
$msg['data'] = $data;
echo json_encode($msg);
}
