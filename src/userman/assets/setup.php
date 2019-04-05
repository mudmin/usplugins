<?php
//repetitive queries
$opts = array(
'0'=>"Disabled",
'1'=>"Master Account Only",
'2'=>"Master and Admin",
'3'=>"Master, Admin, and Specified Users"
);

$existing = $db->query("SELECT * FROM userman_settings WHERE id = 1 LIMIT 1")->results(true);
$userman = [];
$highest = 0; //the highest tells us whether the userman should be loaded at all.
foreach($existing[0] as $k=>$v){
  if($k != 'id'){
    $userman[$k] = $v;
    if($v > $highest){
      $highest = $v;
    }
  }
}

function usermanSecurity($level){
  if($level == 0){
    return false;
  }

  if($level == 1){
    if(in_array($user->data()->id, $master_account)){
      return true;
    }else{
      return false;
    }
  }

  if($level == 2){
    if((in_array($user->data()->id, $master_account)) || hasPerm([2],$user->data()->id)){
      return true;
    }else{
      return false;
    }
  }

  if($level == 3){
    if((in_array($user->data()->id, $master_account)) || hasPerm([2],$user->data()->id)){
      return true;
    }else{
      $check = $db->query("SELECT id FROM users WHERE userman = 1 AND id = ?",array($user->data()->id))->count();
      if($check == 1){
        return true;
      }else{
        return false;
      }
    }
  }
}
