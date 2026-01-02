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
$q = $db->query("SELECT * FROM plg_cronpro_single WHERE go_time <= ? AND complete = 0",[$dt]);
$c = $q->count();

if($c > 0){
  $singles = $q->results();

  foreach($singles as $s){
    if($s->calltype == "db"){
      // HARDENING: Prevent non-SELECT/UPDATE/INSERT/DELETE queries to mitigate major injection risks
      $trimmedQuery = trim($s->calldata);
      $allowedPatterns = '/^\s*(SELECT|UPDATE|INSERT|DELETE)\s/i';
      
      if (preg_match($allowedPatterns, $trimmedQuery)) {
          $db->query($s->calldata);
          logger(1,"CronPro Single","DB Job ".$s->id." - ".$db->errorString());
      } else {
          logger(1,"CronPro Single","DB Job ".$s->id." - BLOCKED: Unauthorized Query Type");
      }

    }elseif($s->calltype == "file"){
      // HARDENING: Use basename() and strict whitelist validation for file inclusion
      $assetPath = $abs_us_root.$us_url_root."usersc/plugins/cronpro/assets/";
      $requestedFile = basename($s->calldata);
      
      // Whitelist existing .php files in the assets folder
      $allowedFiles = glob($assetPath . "*.php");
      $whiteList = array_map('basename', $allowedFiles);

      if(!empty($requestedFile) && in_array($requestedFile, $whiteList)){
        include($assetPath . $requestedFile);
        logger(1,"CronPro Single","File Job ".$s->id." - ".$requestedFile." executed");
      }else{
        logger(1,"CronPro Single","File Job ".$s->id." - ".$s->calldata." NOT FOUND or UNAUTHORIZED");
      }
    }
    
    $db->update("plg_cronpro_single",$s->id,['complete'=>1,'hit_time'=>date("Y-m-d H:i:s")]);

    if(is_numeric($s->recurring) && $s->recurring > 0){
      $q = $db->query("SELECT * FROM plg_cronpro_recurring WHERE id = ?",[(int)$s->recurring]);
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