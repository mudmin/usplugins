<?php 
require_once "../../../../../users/init.php";
global $user;

//fetching a message marks it read, so it only accepts a POST with a valid token
if($_SERVER['REQUEST_METHOD'] !== 'POST' || !Token::check(Input::get('csrf'))){
  echo json_encode(["success"=>false,"msg"=>"Invalid token"]);die;
}

if(!isset($user) ||  !$user->isLoggedIn()){
  $response = ["success"=>false,"msg"=>"You are not logged in", "reload"=>true];
  echo json_encode($response);die;
}


$id = Input::get('id');

$fetch = fetchPLGMessage($id);

if(!$fetch){
    $msg = ['success'=>false, 'msg'=>''];
}else{

    $msg = ['success'=>true, 'msg'=>$fetch];
}

echo json_encode($msg); die;