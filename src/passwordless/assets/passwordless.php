<?php
//Note: This file can be copied to usersc/plugins/passwordless/assets/custom.php and your file will be loaded instead of ours
global $db;
$emset = $db->query("SELECT * FROM email")->first();
$ps = $db->query("SELECT * FROM plg_passwordless_settings")->first();

 ?>
 <a href="<?=$emset->verify_url.$ps->link?>">
   <img class='img-responsive' src="<?=$us_url_root?>usersc/plugins/passwordless/assets/passwordless.png" alt=""/>
 </a>
