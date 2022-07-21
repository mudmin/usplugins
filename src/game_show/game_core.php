<?php
if(!isset($user) || !$user->isLoggedIn()){
  Redirect::to($us_url_root.'users/login.php');
}

$gsettingsQ = $db->query("SELECT * FROM gameshow_settings WHERE owner = ?",[$user->data()->id]);
$gsettingsC = $gsettingsQ->count();
if($gsettingsC < 1){
  $fields = [
      "owner"=>$user->data()->id,
      "require_key"=>1,
      "play_sounds"=>0,
      "game"=>3
  ];
  $db->insert("gameshow_settings",$fields);
  $gsettingsQ = $db->query("SELECT * FROM gameshow_settings WHERE owner = ?",[$user->data()->id]);
  $gsettings = $gsettingsQ->first();
  // die("Settings could not be found. Please reinstall");
}else{
  $gsettings = $gsettingsQ->first();
}

$view = Input::get('view');
$vbuzz = Input::get('vbuzz');
if(is_numeric($vbuzz)){
 include $abs_us_root.$us_url_root."usersc/plugins/game_show/views/game_buzzer_virtual.php";
}elseif($view != "" && file_exists($abs_us_root.$us_url_root."usersc/plugins/game_show/views/".$view.".php")){
  include $abs_us_root.$us_url_root."usersc/plugins/game_show/views/".$view.".php";
}else{
  include $abs_us_root.$us_url_root."usersc/plugins/game_show/views/game.php";
}

 ?>
