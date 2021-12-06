<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
global $user;

if(hasPerm([2],$user->data()->id)){

  $pset = $db->query("SELECT * FROM plg_spicebin_settings")->first();
  if($pset->del_mode == 1 && is_numeric($pset->delete_days) && $pset->delete_days > 0){
    $delete = $db->query("DELETE FROM plg_spicebin WHERE no_auto = 0 AND DATE(delete_on) < ?",[date("Y-m-d H:i:s")]);
    logger($user->data()->id,"SpiceBin","Login triggered deleting old ".$pset->product_plural);

  }
}
