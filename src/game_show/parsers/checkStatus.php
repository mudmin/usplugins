<?php
require_once "../../../../users/init.php";
require_once $abs_us_root.$us_url_root."usersc/plugins/game_show/assets/functions.php";
$msg = [];

if(!isset($user) || !$user->isLoggedIn()){
  $msg['msg'] = "NLI";
  echo json_encode($msg);die;
}

$buzzers = $db->query("SELECT *
FROM gameshow_buzzers
WHERE owner = ?
ORDER BY
  elapsed = 0.00,
  elapsed
",[$user->data()->id]
)->results();

$response = [];
$counter = 0;
foreach($buzzers as $b){
  $row = [];
  $row['id'] = $b->id;
  if($b->elapsed != 0){
    $counter++;
    $row['counter'] = "#".$counter;
    $row['time'] = gameTime($b->elapsed);
    $row['sound'] = $b->to_play;
  }else{
    $row['counter'] = "";
    $row['time'] = "";
    $row['sound'] = "";
  }

  if($b->can_buzz == 1){
    $row['lock'] = "unlock.png";
  }else{
    $row['lock'] = "lock.png";
  }
  $response[] = $row;


}
echo json_encode($response);
