<?php
if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
global $theNewId;
global $username;
$refSettings = $db->query("SELECT * FROM plg_refer_settings")->first();
$refReq = $refSettings->only_refer == 1 ? true : false;
$refCode = Input::get('ref');
$refProc = false;

$successful_refer = false;
$check_referQ = $db->query("SELECT id,username FROM users WHERE plg_ref = ?",[$refCode]);
$check_referC = $check_referQ->count();
if($check_referC > 0){
  $check_refer = $check_referQ->first();
  $successful_refer = true;
  $db->update('users',$theNewId,['plg_ref_by'=>$check_refer->id]);
  logger($theNewId,"good_refer", $check_refer->id);
}elseif($refSettings->allow_un == 1){
  $check_referQ = $db->query("SELECT id,username FROM users WHERE username = ?",[$refCode]);
  $check_referC = $check_referQ->count();
  if($check_referC > 0){
    $check_refer = $check_referQ->first();
    $successful_refer = true;
    $db->update('users',$theNewId,['plg_ref_by'=>$check_refer->id]);
    logger($theNewId,"good_refer", $check_refer->id);
  }
  if($refSettings->only_refer == 0){
    logger(1,"bad_refer", "$username registered, but had a bad referral code of $refCode");
    //mark them as their own referral
    $db->update('users',$theNewId,['plg_ref_by'=>$theNewId]);
  }else{
    $db->query("DELETE FROM users WHERE id = ?",[$theNewId]);
    $db->query("DELETE FROM user_permission_matches WHERE user_id = ?",[$theNewId]);
    logger(1,"bad_refer", "$username attempted refer with code of $refCode");
    Redirect::to('join.php?ref='.$refCode.'&err=Invalid+referral+code');
  }
}elseif($refSettings->only_refer == 0){
  logger(1,"bad_refer", "$username registered, but had a bad referral code of $refCode");
  //mark them as their own referral
  $db->update('users',$theNewId,['plg_ref_by'=>$theNewId]);
}else{
  $db->query("DELETE FROM users WHERE id = ?",[$theNewId]);
  $db->query("DELETE FROM user_permission_matches WHERE user_id = ?",[$theNewId]);
  logger(1,"bad_refer", "$username attempted refer with code of $refCode");
  Redirect::to('join.php?ref='.$refCode.'&err=Invalid+referral+code');
}

if($successful_refer){
  if(file_exists($abs_us_root.$us_url_root.'usersc/plugins/refer/success_script.php')){
    include $abs_us_root.$us_url_root.'usersc/plugins/refer/success_script.php';
  }
}
