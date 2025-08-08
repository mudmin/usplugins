<?php
if(count(get_included_files()) ==1){
  $ip = $_SERVER['REMOTE_ADDR'];
  require_once("../banned.php");
  if(in_array($ip,$banned)){
    die();
  }
  require_once "../../../../users/init.php";
}

require_once $abs_us_root.$us_url_root."usersc/plugins/game_show/assets/functions.php";
//games

//1 - lockout

//2 - Buzz Order

//3 - Buzzer Test

$json = file_get_contents('php://input');
$data = json_decode($json, "true");

if(isset($data['buzz'])){
  $_POST = $data;
}



$buzz = Input::get("buzz");

$msg = [];

$msg['success'] = 0;
$diag = true;
  $owner = Input::get('owner');
  $q = $db->query("SELECT * FROM gameshow_settings WHERE owner = ?",[$owner]);
  $c = $q->count();
  if($c < 1){
    gameAPIFail(ipCheck());
    $msg['msg'] = "Owner not found";
    echo json_encode($msg);die;
  }else{
    $owner = $q->first();
  }
  $key = Input::get('key');

  $q = $db->query("SELECT * FROM gameshow_buzzers WHERE id = ? AND owner = ? AND buzzer_key = ?",[$buzz,$owner->owner,$key]);

  $c = $q->count();
  if($c < 1){
    gameAPIFail(ipCheck());
    $msg['msg'] = "Buzzer not found";
    echo json_encode($msg);die;

  }else{
    $b = $q->first();

    if($b->disabled == 1){
      $msg['msg'] = "Buzzer is disabled";
      echo json_encode($msg);die;
    }

    if($b->can_buzz == 1 && $b->buzzed == 0){
      //valid buzz
    $diff = parseBuzzET($owner->begin_time);

    //lockout logic
    if($owner->game == 1){
      $db->query("UPDATE gameshow_buzzers SET can_buzz = 0 WHERE owner = ?",[$owner->owner]);
     }

     //for games 1 and 2 we want to record this buzzer's action
     if($owner->game == 1 || $owner->game == 2){
       $db->update("gameshow_buzzers",$b->id,['can_buzz'=>0,'buzzed'=>1,'elapsed'=>$diff,'to_play'=>$b->sound]);
     }

    //for test mode, we don't want to lock the buzzer, so we're just going to record the elapsed so it updates
     if($owner->game == 3){
       $db->update("gameshow_buzzers",$b->id,['can_buzz'=>1,'buzzed'=>0,'elapsed'=>$diff,'to_play'=>$b->sound]);
     }

      //play sound
      $msg['msg'] = "buzzed";
      $msg['success'] = 1;
     // shell_exec('cmdmp3.exe mp3/'.$b->sound);
      echo json_encode($msg);die;

    }elseif($b->can_buzz == 0 || $b->buzzed == 1){
      $msg['msg'] = "off";
      echo json_encode($msg);die;
    }else{
      //who knows?
    }

  }
