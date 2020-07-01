<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
$view      = Input::get('view');
$thread    = Input::get('thread');
$board     = Input::get('board');

$hColor = "white";
$bColor = "dark";
$sColor  = "primary";
if(isset($user) && $user->isLoggedIn()){
  $uid = $user->data()->id;
}else{
  $uid = 0;
}
if(forumAccess($board,"write",$uid)){
$write = true;
}else{
$write = false;
}

if(forumAccess($board,"read",$uid)){
$read = true;
}else{
$read = false;
}

$is_mod = false;
$can_ban = false;
if(hasPerm([2],$user->data()->id)){
  $is_mod = true;
  $can_ban = true;
}elseif($settings->forum_mod_perms != ""){
  $mods = explode(",",$settings->forum_mod_perms);
  foreach($mods as $m){
    if($m == "" || $m < 3){continue;}
    if(hasPerm([$m],$user->data()->id)){
      $is_mod = true;
    }
  }
}
if($is_mod && $settings->forum_mod_boot == 1){
  $can_ban = true;
}

$aPath = $abs_us_root.$us_url_root.'usersc/plugins/forum/assets/';
$cPath = $abs_us_root.$us_url_root.'usersc/plugins/forum/custom/';

if($board == ""){
  $filename = "board_browser.php";
  if(file_exists($cPath.$filename)){
    require_once($cPath.$filename);
  }else{
    require_once($aPath.$filename);
  }

}elseif($view=="new"){
  $filename = "new_post.php";
  if(file_exists($cPath.$filename)){
    require_once($cPath.$filename);
  }else{
    require_once($aPath.$filename);
  }
}elseif(is_numeric($board) && ($thread == "" || !is_numeric($thread))){
  $filename = "thread_browser.php";
  if(file_exists($cPath.$filename)){
    require_once($cPath.$filename);
  }else{
    require_once($aPath.$filename);
  }
}elseif(is_numeric($board) && is_numeric($thread)){
  $filename = "thread_view.php";
  if(file_exists($cPath.$filename)){
    require_once($cPath.$filename);
  }else{
    require_once($aPath.$filename);
  }
}else{

}


?>
