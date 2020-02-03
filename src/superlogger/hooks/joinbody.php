<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted ?>
<?php
global $settings;
if($settings->plg_sl_join_warn == 1 && $settings->plg_sl_opt_out == 0){ ?>
  <p>This site records your actions. If you do not like that, please do not join!</p>
<?php }
if($settings->plg_sl_join_warn == 1 && $settings->plg_sl_opt_out == 1){ ?>
  <p>This site records your actions. You can opt out on your account page.</p>
<?php }
if($settings->plg_sl_del_data == 1){ ?>
  <p>You can also delete your data on your account page.</p>
<?php } ?>
