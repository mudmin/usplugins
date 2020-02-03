<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted ?>
<?php
$refSettings = $db->query("SELECT * FROM plg_refer_settings")->first();
if($refSettings->show_acct == 1){
global $user;
if($user->data()->plg_ref == ''){
  if(!empty($_POST['genNewRef'])){
    $db->update("users",$user->data()->id,['plg_ref'=>uniqid()]);
    Redirect::to("account.php");
  }
  ?>

  <form class="" action="" method="post">
    <input type="submit" name="genNewRef" value="Generate Referral Link">
  </form>
<?php }else{
  $ref_link = 'http://'.$_SERVER['HTTP_HOST'].$us_url_root.'users/join.php?ref='.$user->data()->plg_ref;
  echo "Your Referral Link: <strong>".$ref_link."</strong>";
}
}
?>
