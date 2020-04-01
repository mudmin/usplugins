<?php
//checks to see if the person is the owner of their own saas org
function saasOwner(){
global $abs_us_root,$us_url_root,$db,$user,$master_account;
if(in_array($user->data()->id,$master_account)){return true; }
  $q = $db->query("SELECT * FROM us_saas_orgs WHERE id = ? AND active = 1 AND owner = ?",[$user->data()->account_owner,$user->data()->id])->count();
  if($q < 1){
    return false;
  }else{
    if(file_exists($abs_us_root.$us_url_root.'usersc/plugins/saas/assets/saasOwener.php')){
      include($abs_us_root.$us_url_root.'usersc/plugins/saas/assets/saasOwener.php');
    }
    return true;
  }
}

function saasMgr(){
global $abs_us_root,$us_url_root,$db,$user;
  if(saasOwner()){return true;}
  $q = $db->query("SELECT * FROM us_saas_mgrs WHERE org = ? AND user = ?",[$user->data()->account_owner,$user->data()->id])->count();
  if($q < 1){
    return false;
  }else{
    if(file_exists($abs_us_root.$us_url_root.'usersc/plugins/saas/assets/saasOwener.php')){
      include($abs_us_root.$us_url_root.'usersc/plugins/saas/assets/saasOwener.php');
    }
    return true;
  }
}

function saasPlanInfo($account_owner){
  global $db;
    $m = $db->query("SELECT id FROM users WHERE account_owner = ?",[$account_owner])->count();
  if($account_owner == 1){
    $p = $db->query("SELECT * FROM us_saas_levels ORDER BY users DESC")->first();
    $p->used = $m;
    return $p;
  }
  $q = $db->query("SELECT level FROM us_saas_orgs WHERE id = ?",[$account_owner]);
  $c = $q->count();
  if($c < 1){return false;}
  $p = $q->first();
  $planQ = $db->query("SELECT * from us_saas_levels WHERE id = ?",[$p->level]);
  $planC = $planQ->count();
  if($planC < 1){return false;}
  $p->used = $m;
  return $planQ->first();
}

if(!function_exists('echoPerm')){
  function echoPerm($id){
    global $db;
    $q = $db->query("SELECT name FROM permissions WHERE id = ?",[$id]);
    $c = $q->count();
    if($c < 1){
      echo "unknown";
    }else{
      $f = $q->first();
      echo $f->name;
    }
  }
}
