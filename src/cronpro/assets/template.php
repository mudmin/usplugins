<?php
if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
global $ip,$settings,$db,$abs_us_root,$us_url_root;
if($ip != $settings->cron_ip){
  logger(1,"CronPro","Direct access attempted");
  die;
}

//put your script here. Don't touch anything above this line
//Create any php script below
logger(1,"template.php","This is just a test");
