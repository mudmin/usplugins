<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
global $user;
?>
<p>
  Your Badges: <?php displayBadges($user->data()->id);?>
</p>
