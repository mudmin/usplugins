<?php
global $user;
if($user->data()->steam_id == ''){ ?>
  <p><a href="<?=safeReturn($us_url_root.'usersc/plugins/steam_login/link_account.php')?>" class="btn btn-primary" role="button">Link Steam Account</a></p>
<?php }else{ ?>
  <p><button type="button" name="button" class="btn btn-default" disabled>Steam Account Linked</button></p>
<?php } ?>
