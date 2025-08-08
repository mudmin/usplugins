<?php
global $user;
if($user->data()->steam_id != ''){ ?>
  <p>Steam <?=lang("MENU_ACCOUNT")?>: <?=$user->data()->steam_un?></p>
<?php }?>
