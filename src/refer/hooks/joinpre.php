<?php
if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
if(!empty($_POST)){
global $theNewId;
global $username;
$refSettings = $db->query("SELECT * FROM plg_refer_settings")->first();
$refReq = $refSettings->only_refer == 1 ? true : false;
$refCode = Input::get('ref');
$check_referQ = $db->query("SELECT id,username FROM users WHERE plg_ref = ?",[$refCode]);
$check_referC = $check_referQ->count();
  if($refSettings->only_refer == 1 && $check_referC < 1){
    logger(1,"bad_refer", "Attempted refer with code of $refCode");
    Redirect::to('join.php?ref='.$refCode.'&err=Invalid+referral+code');
  }
}
