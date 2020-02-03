<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
global $settings, $user, $db;

if($settings->plg_sl_opt_out == 1 && $user->data()->plg_sl_opt_out == 0){
  if(!empty($_POST['optOutLoggingData'])){
    $db->update('users',$user->data()->id,['plg_sl_opt_out'=>1]);
    Redirect::to('account.php?err=You have opted out of deep logging');
  }
    ?>
  <form class="" action="" method="post">
    <input type="submit" name="optOutLoggingData" value="Opt Out of Logging" class="btn btn-primary btn-block">
  </form><br>
<?php }

$loggingData = $db->query("SELECT id FROM plg_sl_logs WHERE user_id = ?",[$user->data()->id])->count();
if($settings->plg_sl_del_data == 1 && $loggingData > 0){
if(!empty($_POST['deleteMyLoggingData'])){
  $db->query("DELETE FROM plg_sl_logs WHERE user_id = ?",[$user->data()->id]);
  Redirect::to('account.php?err=Data+deleted');
}
  ?>
<form class="" action="" method="post">
  <input type="submit" name="deleteMyLoggingData" value="Delete My Logging Data" class="btn btn-danger btn-block">
</form>
<?php } ?>
