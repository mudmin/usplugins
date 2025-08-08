<?php
global $user;
if($user->data()->steam_id == ''){ ?>
  <p><button type="button" onclick="window.location.href = '<?=$us_url_root?>usersc/plugins/steam_login/link_account.php';" name="button" class="btn btn-primary">Link Steam Account</button></p>
<?php }else{ ?>
  <p><button type="button" onclick="window.location.href = '#';" name="button" class="btn btn-default">Steam Account Linked</button></p>
<?php } ?>
