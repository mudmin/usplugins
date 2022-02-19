<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
global $user,$db,$settings,$abs_us_root,$us_url_root,$master_account;
$v = Input::get('v');
if(saasMgr()){ //wrap everythng in this section!
  $managers = $db->query("SELECT * FROM us_saas_mgrs WHERE org = ?",[$user->data()->account_owner])->results();
  $planInfo = saasPlanInfo($user->data()->account_owner);
  if($planInfo->used >= $planInfo->users){
    $usersLeft = false;
  }else{
    $usersLeft = true;
  }

  $users = $db->query("SELECT * FROM users WHERE account_owner = ? AND id != ?",[$user->data()->account_owner,$user->data()->id])->results();
  $mgrs = [];
  $mg = $db->query("SELECT * FROM us_saas_mgrs WHERE org = ?",[$user->data()->account_owner])->results();
  foreach($mg as $m){$mgrs[]=$m->user;}
  $errors = $successes = [];
  $e = $db->query("SELECT * FROM email")->first();
  $act = $e->email_act;
  $form_valid=TRUE;

  // dnd($permOps);
  $validation = new Validate();
  $planInfo = saasPlanInfo($user->data()->account_owner);?>

  <h5>You have used <font color='blue'><?=number_format($planInfo->used);?></font> of your <font color='blue'><?=number_format($planInfo->users);?></font> available users. </h5>


  <?php
  if(count($mgrs) > 0){
    echo '<div class="card">';
    echo  '<div class="card-header">Your Managers</div>';
    echo  '<div class="card-body">';
    foreach($mgrs as $m){
      echouser($m)."  ";
    }
    echo "</div></div>";
  }

  if($v == ''){
    include $abs_us_root.$us_url_root.'usersc/plugins/saas/assets/uman.php';
  }
  if($v == 'manage'){
    include $abs_us_root.$us_url_root.'usersc/plugins/saas/assets/uedit.php';
  }


} // Nothing below this!!!
