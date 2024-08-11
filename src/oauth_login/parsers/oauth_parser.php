<?php
require_once '../../../../users/init.php';
$resp = ["success" => false, "msg"=> "Invalid permission"];
if(!hasPerm(2)){
    echo json_encode($resp);die;
}

$find = $db->query("SELECT * FROM plg_social_logins WHERE plugin = ?", ['oauth_login'])->first();
if(!$find){
    $resp['msg'] = "Plugin not installed";
    echo json_encode($resp);die;
}
$icon = Input::get('icon');
$title = Input::get('title');
$action = Input::get('action');

if($action == "icon"){
//make sure icon ends in png
if(substr($icon, -3) != 'png'){
    $resp['msg'] = "Invalid icon";
    echo json_encode($resp);die;
}

if($find){
    $db->update('plg_social_logins', $find->id, ['image' => "assets/". $icon]);
    $resp['success'] = true;
    $resp['msg'] = "Icon updated";
    echo json_encode($resp);die;
}
}

if($action == "title"){
if($find){
    $db->update('plg_social_logins', $find->id, ['provider' => $title]);
    $resp['success'] = true;
    $resp['msg'] = "Title updated";
    echo json_encode($resp);die;
}
}