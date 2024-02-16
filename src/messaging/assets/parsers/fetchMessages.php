<?php 
require_once "../../../../../users/init.php";
global $user;

if(!isset($user) ||  !$user->isLoggedIn()){
  $response = ["success"=>false,"msg"=>"You are not logged in", "messages"=>null];
  echo json_encode($response);die;
}


$id = Input::get('id');
$response['success'] = true;
$response['msg'] = "Message fetched";
$fetch = fetchPLGMessages(500);
$response['messages'] = $fetch;
echo json_encode($response); die;