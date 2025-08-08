<?php
$wdSettings = $db->query("SELECT * FROM plg_watchdog_settings")->first();
if($wdSettings->every_page == 1){
  watchdogHere();
}
