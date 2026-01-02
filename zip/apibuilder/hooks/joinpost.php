<?php
if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
global $theNewId;
$apiset = $db->query("SELECT * FROM plg_api_setting")->first();

if($apiset->new_user_key == 1){ 
$code = strtoupper(randomstring(12) . uniqid());
$code = substr(chunk_split($code, 5, '-'), 0, -1);
$db->update('users',$theNewId,['apibld_key'=>$code]);
}