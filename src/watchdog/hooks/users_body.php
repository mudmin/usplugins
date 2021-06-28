<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
$wdSettings = $db->query("SELECT * FROM plg_watchdog_settings")->first();
if($wdSettings->tracking == 1){
  echo "<th  class='text-center'>Online</th>";
}
?>
