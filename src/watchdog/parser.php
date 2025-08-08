<?php
//NOTE: This also serves as the reference file for how to do One Click Edit with UserSpice. See comments below.
require_once '../../../users/init.php';
global $user;
$db = DB::getInstance();

if(!isset($_SESSION[Config::get('session/session_name')."-watchdogs"])){
  $_SESSION[Config::get('session/session_name')."-watchdogs"] = [];
}

if(isset($user) && $user->isLoggedIn()){
  $li = true;
}else{
  $li = false;
}

$found = false;
$cp = Input::get('currentPage');
$cwd = Input::get('currentPath');
$wd = $db->query("SELECT * FROM plg_watchdog_settings")->first();
$dt = date("Y-m-d H:i:s");
if($wd->tracking){
  if($cwd == ""){
    $page = $cp;
  }else{
    $page = $cwd."/".$cp;
  }
  $q = $db->query("UPDATE pages SET dwells = dwells+1 WHERE page = ?",[ltrim($page,'/')]);
}

if($wd->tracking == 1 && $li){
  $db->update('users',$user->data()->id,['last_watchdog'=>$dt,'last_page'=>ltrim($page,'/')]);
}

$msg = [];
$msg['func'] = "";
$msg['args'] = [];
// if($wd->last_wd > $dt){
  $dogs = $db->query("SELECT * FROM plg_watchdogs WHERE wd_timeout >= ?",[$dt])->results();
  foreach($dogs as $d){
    if(in_array($d->id,$_SESSION[Config::get('session/session_name')."-watchdogs"])){
      continue; //don't retrigger
    }
    if($d->wd_target_type == "all"){
      $found = true;
    }elseif($d->wd_target_type == "logged_in" && $li){
      $found = true;
    }elseif($d->wd_target_type == "logged_out" && !$li){
      $found = true;
    }elseif($d->wd_target_type == "with_perm" && $li && hasPerm([$d->wd_targets],$user->data()->id)){
      $found = true;
    }elseif($d->wd_target_type == "without_perm" && ($li && !hasPerm([$d->wd_targets],$user->data()->id)) ){
      $found = true;
    }elseif($d->wd_target_type == "page" && in_array($cp,explode(",",$d->wd_targets))){
      $found = true;
    }
    if($found){
      $msg['func'] = $d->wd_func;
      $msg['args'] = $d->wd_args;
      $_SESSION[Config::get('session/session_name')."-watchdogs"][] = $d->id;
      $db->query("UPDATE plg_watchdogs SET wd_times_triggered = wd_times_triggered + 1 WHERE id = ?",[$d->id]);
      break;
    }
  }
// }
echo json_encode($msg);
