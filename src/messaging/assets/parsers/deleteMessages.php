<?php 
require_once "../../../../../users/init.php";
global $user, $db;

if(!Token::check(Input::get('csrf'))){
  echo json_encode(["success"=>false,"msg"=>"Invalid token"]);die;
}

if(!isset($user) ||  !$user->isLoggedIn()){
  $response = ["success"=>false,"msg"=>"You are not logged in", "reload"=>true];
  echo json_encode($response);die;
}

$checked = Input::get('checked');
if(is_array($checked)){
    foreach($checked as $id){
        // Scope the delete to the current user so a user cannot delete others' messages.
        $db->query("UPDATE plg_msg SET deleted = 1 WHERE id = ? AND user_to = ?", [(int)$id, $user->data()->id]);
    }
    $msg = ['success'=>true, 'msg'=>'Deleted'];
}else{
    $msg = ['success'=>false, 'msg'=>'No messages selected'];
}

echo json_encode($msg); die;