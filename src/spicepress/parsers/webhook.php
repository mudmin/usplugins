<?php
require_once "../../../../users/init.php";
$json = file_get_contents('php://input');

logger(0,"1","webhook hit");

$data = json_decode($json, "true");
$response = [];
$response['success'] = false;

if($data['auth_code'] != ""){
  $authenticate = checkSpicePressSession(Input::sanitize($data['auth_code']));
  if($authenticate == true){
    logger(0,"SpicePress","Successful webhook authentication");
    $response['success'] = true;
  }else{
    logger(0,"SpicePress","Failed webhook authentication");
  }
  echo json_encode($response);die;
}
