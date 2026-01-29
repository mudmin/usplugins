<?php 
require_once "../../../../../users/init.php";
global $user;

if(!isset($user) ||  !$user->isLoggedIn()){
  $response = ["success"=>false,"msg"=>"You are not logged in", "reload"=>true];
  echo json_encode($response);die;
}

$checked = Input::get('checked');
if(is_array($checked)){
    foreach($checked as $id){
        $db->update("plg_msg", $id, ["deleted"=>1]);
    }
    $msg = ['success'=>true, 'msg'=>'Deleted'];
}else{
    $msg = ['success'=>false, 'msg'=>'No messages selected'];
}

echo json_encode($msg); die;