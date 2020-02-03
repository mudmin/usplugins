<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted ?>
<?php
global $settings;
if($settings->session_manager==1) {?><p><a class="btn btn-primary btn-block" href="../users/manage_sessions.php" role="button"><?=lang("ACCT_SESS")?></a></p><?php } ?>
