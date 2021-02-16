<?php
require_once '../../../users/init.php';
$db = DB::getInstance();
include "plugin_info.php";
if(!pluginActive($plugin_name,true)){die("inactive");}
require_once $abs_us_root.$us_url_root.'usersc/plugins/cronpro/vendor/autoload.php';
$settings = $db->query("SELECT * FROM settings")->first();
$ip = ipCheck();
if($ip != $settings->cron_ip){
  logger(1,"CronPro","Unauthorized target.php attempt from $ip");
  die;
}else{
  if(Input::get("diag") == true){
    logger(1,"CronPro","Cron hit successfully in ?diag=true mode");
  }
}

$dt = date("Y-m-d H:i:s");
//look for one time cron jobs
$q = $db->query("SELECT * FROM plg_cronpro_single WHERE go_time <= ? AND complete = 0",[$dt]);
$c = $q->count();
if($c > 0){
  $singles = $q->results();

  foreach($singles as $s){
    if($s->calltype == "db"){
      $db->query($s->calldata);
      logger(1,"CronPro Single","DB Job ".$s->id." - ".$db->errorString());
    }elseif($s->calltype == "file"){
      if(file_exists($abs_us_root.$us_url_root."usersc/plugins/cronpro/assets/".$s->calldata)){
        include($abs_us_root.$us_url_root."usersc/plugins/cronpro/assets/".$s->calldata);
        logger(1,"CronPro Single","File Job ".$s->id." - ".$s->calldata." executed");
      }else{
        logger(1,"CronPro Single","File Job ".$s->id." - ".$s->calldata." NOT FOUND");
      }
    }
    $db->update("plg_cronpro_single",$s->id,['complete'=>1,'hit_time'=>date("Y-m-d H:i:s")]);


    if(is_numeric($s->recurring) && $s->recurring > 0){
      $q = $db->query("SELECT * FROM plg_cronpro_recurring WHERE id = ?",[$s->recurring]);
      $c = $q->count();
      if($c > 0){
        $r = $q->first();
        $cron = new Cron\CronExpression($r->schedule);
        $next = $cron->getNextRunDate()->format('Y-m-d H:i:s');
        $fields = [
          'cron_name'=>$s->cron_name,
          'recurring'=>$s->recurring,
          'go_time'=>$next,
          'calltype'=>$s->calltype,
          'calldata'=>$s->calldata
        ];
        $db->insert("plg_cronpro_single",$fields);
        logger(1,"CronPro","Attempting recreate recurring ".$db->errorString());
      }else{
        logger(1,"CronPro","Attempting recreate recurring but recur not found.");
      }
    }
  }
}
