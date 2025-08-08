<?php
//Please don't load functions system-wide if you don't need them system-wide.
// To make your plugin more efficient on resources, consider only loading resources that need to be loaded when they need to be loaded.
// For instance, you can do
// $currentPage = currentPage();
// if($currentPage == 'admin.php'){ //The administrative dashboard
//   bold("<br>See! I am only loading this when I need it!");
// }
// // Also, please wrap your functions in if(!function_exists())
// if(!function_exists('pointsFunction')) {
//   function pointsFunction(){ }
// }
if(!function_exists('pointsName')) {
  function pointsName(){
    global $db;
    $pntSettings = $db->query("SELECT * FROM plg_points_settings")->first();
    echo $pntSettings->term;
  }
}

if(!function_exists('pointsNameReturn')) {
  function pointsNameReturn(){
    global $db;
    $pntSettings = $db->query("SELECT * FROM plg_points_settings")->first();
    return $pntSettings->term;
  }
}

if(!function_exists('pointsUnitReturn')) {
  function pointsUnitReturn($num){
    global $db;
    $pntSettings = $db->query("SELECT * FROM plg_points_settings")->first();
    if($num == 1){
      return "1 ".$pntSettings->term_sing;
    }else{
      return $num." ".$pntSettings->term;
    }
  }
}

if(!function_exists('validatePointsUser')) {
  function validatePointsUser($username){
    global $db;
    $checkQ = $db->query("SELECT id,username,plg_points FROM users WHERE username = ?",[$username]);
    $checkC = $checkQ->count();
    if($checkC < 1){
      $checkQ = $db->query("SELECT id,username,plg_points FROM users WHERE id = ?",[$username]);
      $checkC = $checkQ->count();
      if($checkC < 1){
        return false;
      }else{
        $check = $checkQ->first();
      }
    }else{
      $check = $checkQ->first();
    }
    return $check;
  }
}

if(!function_exists('logPoints')) {
  function logPoints($from,$to,$reason,$points){
    global $db;
    $fields = array(
      'trans_from'=>$from,
      'trans_to'=>$to,
      'reason'=>$reason,
      'points'=>$points,
      'ts'=>date('Y-m-d H:i:s')
    );
    $db->insert('plg_points_trans',$fields);
    return false;
  }
}

if(!function_exists('alterPoints')) {
  function alterPoints($username,$points,$type,$reason){
    global $db, $user;
    $name = pointsNameReturn();
    $fail = false;
    $msg = [];
    if(!is_numeric($points)){
      $fail = true;
      $msg['reason'] = $name." not provided";
    }

    if($points < .00000001){
      $fail = true;
      $msg['reason'] = $name." cannot be 0 or negative";
    }

    if($type == ""){
      $fail = true;
      $msg['reason'] = "Transaction type not provided";
    }

    if($reason == ""){
      $fail = true;
      $msg['reason'] = "Reason not provided";
    }

    $check = validatePointsUser($username);
    if($check == false){
      $msg['reason'] = "$username does not exist";
      $fail = true;
    }

    if(!$fail){
      if($type == 'give'){
        $db->update('users',$check->id,['plg_points'=>$check->plg_points+$points]);
        logPoints($user->data()->id,$check->id,$reason,$points);
        $msg['success'] = true;
        $unit = pointsUnitReturn($points);
        $msg['reason'] = "$unit given to $check->username";
      }


      if($type == 'take'){
        $checkBal = false;
        if(($check->plg_points-$points) >= 0){
          $checkBal = true;
        }
        if($checkBal){
          $db->update('users',$check->id,['plg_points'=>$check->plg_points-$points]);
          logPoints($user->data()->id,$check->id,$reason,$points*-1);
          $msg['success'] = true;
          $unit = pointsUnitReturn($points);
          $msg['reason'] = "$unit taken from $check->username";
        }else{
          $msg['success'] = false;
          $msg['reason'] = "$check->username had an insufficient balance. They only had $check->plg_points.";
        }
      }

    }else{
      $msg['success'] = false;
    }
    return $msg;
  }
}

if(!function_exists('transferPoints')) {
  function transferPoints($from,$to,$points,$reason){
    global $db, $user;
    $name = pointsNameReturn();
    $fail = false;
    $msg = [];

    if(!is_numeric($points)){
      $fail = true;
      $msg['reason'] = $name." not provided";
    }

    if($points < .00000001){
      $fail = true;
      $msg['reason'] = $name." cannot be 0 or negative";
    }


    if($from == "" || $to == ""){
      $fail = true;
      $msg['reason'] = "Username/ID not provided";
    }

    if($reason == ""){
      $fail = true;
      $msg['reason'] = "Reason not provided";
    }

    $checkFrom = validatePointsUser($from);
    if($checkFrom == false){
      $msg['reason'] = "$from does not exist";
      $fail = true;
    }

    $checkTo = validatePointsUser($to);
    if($checkTo == false){
      $msg['reason'] = "$to does not exist";
      $fail = true;
    }

    if($checkTo->id == $checkFrom->id){
      $fail = true;
      $msg['reason'] = "You cannot transfer to/from the same user";
    }

    if(!$fail){

      $checkBal = false;
      if(($checkFrom->plg_points-$points) >= 0){
        $checkBal = true;
      }
      if($checkBal){
        $db->update('users',$checkFrom->id,['plg_points'=>$checkFrom->plg_points-$points]);
        $db->update('users',$checkTo->id,['plg_points'=>$checkTo->plg_points+$points]);
        logPoints($checkFrom->id,$checkTo->id,$reason,$points);
        $msg['success'] = true;
        $unit = pointsUnitReturn($points);
        $msg['reason'] = "$unit taken from $checkFrom->username and given to $checkTo->username.";
      }else{
        $msg['success'] = false;
        $msg['reason'] = "$checkFrom->username had an insufficient balance. They only had $checkFrom->plg_points.";
      }
    }else{
      $msg['success'] = false;
    }
    return $msg;
  }
}
