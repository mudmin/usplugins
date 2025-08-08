<?php
require_once "../../../../users/init.php";
$settings = $db->query("SELECT * FROM settings")->first();
$ip = ipCheck();
if($ip != $settings->cron_ip){
  logger(0,"Security","Tried to access spicebin auto delete cron target from an invalid ip");
  die("Permission denied");
}
$pset = $db->query("SELECT * FROM plg_spicebin_settings")->first();
if($pset->del_mode == 0 && is_numeric($pset->delete_days) && $pset->delete_days > 0){
$delete = $db->query("DELETE FROM plg_spicebin WHERE no_auto = 0 AND DATE(delete_on) < ?",[date("Y-m-d H:i:s")]);
logger(0,"SpiceBin",$pset->product_plural." deleted via cron job");
}else{
  logger(0,"SpiceBin","Tried to access spicebin auto delete cron with mode set to delete on admin login");
  die("Permission denied. Wrong mode.");
}
