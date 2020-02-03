<?php
//Please don't load functions system-wide if you don't need them system-wide.

function pluginSuperLogger(){
  global $user,$settings,$db,$currentPage;
  $ip = ipCheck();
  if(!$user->isLoggedIn()){
    if($settings->plg_sl_guest == 1){
      $user_id = 0;
    }else{
      return false;
    }
  }else{
    if($settings->plg_sl_opt_out == 1 && $user->data()->plg_sl_opt_out == 1){
      return false;
    }else{
        $user_id = $user->data()->id;
    }
}
  $getdata = [];
  foreach($_GET as $k=>$v){
    $getdata[$k] = Input::sanitize($v);
  }
  $getdata = json_encode($getdata);

  $postdata = [];
  foreach($_POST as $k=>$v){
    if($k != 'password' && $k != 'password_confirm' && $k != 'confirm'){
    $postdata[$k] = Input::sanitize($v);
    }
  }
    $postdata = json_encode($postdata);
$fields = array(
  'user_id'=>$user_id,
  'page'=>$currentPage,
  'get_data'=>$getdata,
  'post_data'=>$postdata,
  'ip'=>$ip,
  'ts'=>date("Y-m-d H:i:s"),
);
$db->insert('plg_sl_logs',$fields);

}
