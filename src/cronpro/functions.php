<?php
if(!function_exists("parseSingleCron")){
  function parseSingleCron($cron){
    $try = parseCron($cron->calltype,$cron->calldata);
    logger(1,"CronPro Job",$try['response']);
  }
}

if(!function_exists("parseCron")){
  function parseCron($calltype,$calldata){
    $msg = [];
    if($calltype == "db" && $calldata != ""){
      $db->query($calldata);
      if(!$db->error()) {
        $msg['success'] = true;
        $msg['response'] = "";
      }else{
        $msg['success'] = false;
        $msg['response'] = $db->errorString();
      }
    }
    return $msg;
  }
}
