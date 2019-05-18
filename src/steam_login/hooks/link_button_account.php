<?php
global $user;
if($user->data()->steam_id == ''){ ?>
  <button type="button" onclick="window.location.href = '<?=$us_url_root?>usersc/plugins/steam_login/link_account.php';" name="button" class="btn btn-primary">Link Steam Account</button>
<?php }else{ ?>
  <button type="button" onclick="window.location.href = '#';" name="button" class="btn btn-default">Steam Account Linked</button>
<?php } ?>
