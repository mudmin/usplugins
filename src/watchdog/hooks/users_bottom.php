<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
global $v1;
$wdSettings = $db->query("SELECT * FROM plg_watchdog_settings")->first();

if($wdSettings->tracking == 1){
  $cutoff = 4 * $wdSettings->wd_time;
  $date = date("Y-m-d H:i:s",strtotime("-$cutoff seconds",strtotime(date("Y-m-d H:i:s"))));
  if($v1->last_watchdog >= $date){ ?>
    <td class="text-center"><img src="<?=$us_url_root?>usersc/plugins/watchdog/images/online.png" alt="" height="15px"></td>
    <?php
  }else{ ?>
    <td class="text-center"><img src="<?=$us_url_root?>usersc/plugins/watchdog/images/offline.png" alt="" height="15px"></td>
  <?php
}
?>
<td><?=$v1->last_page?></td>
<?php
}
?>
