<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
global $user;
if(countBadges($user->data()->id)>0) { ?>
<p>
  Your Badges: <?php displayBadges($user->data()->id);?>
</p>
<?php } ?>
