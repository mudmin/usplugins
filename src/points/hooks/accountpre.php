<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
$uc = ucfirst(pointsNameReturn());
global $user;
$pntSettings = $db->query("SELECT * FROM plg_points_settings")->first();
if($pntSettings->allow_arb_trans == 1){
if(!empty($_POST['transferPoints'])){
  $attempt = transferPoints($user->data()->id,Input::get('to'),Input::get('points'),Input::get('reason'));
  Redirect::to($us_url_root.'users/account.php?err='.$attempt['reason']);}
} ?>
