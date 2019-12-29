<?php
$responseAr = array();

require_once('../../../../users/init.php');

$action = "";
$responseAr['success'] = true;

if(isset($_REQUEST['action'])){
    $action = $_REQUEST['action'];
    $responseAr['error'] = false;
    $db = DB::getInstance();
}else{
    returnError('No API action specified.');
}

if(isset($user) && $user->isLoggedIn()){
    $currentUser = $user->data();
    $loggedIn = true;
}else{
    $loggedIn = false;
}

if($loggedIn===true) {
  switch($action){
      case "checkSessionStatus":
        if(!storeUser(TRUE)) {
          logger($currentUser->id,"User Tracker","Logged User out due to expired session");
          returnError('Logout');
        }
      break;
      default:
          returnError('Invalid API action specified.');
          break;
  }
}
else returnError('User Not Logged In');

echo json_encode($responseAr);
