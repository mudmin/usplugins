<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
global $user;
?>
<div class="form-group">
<button type="button" onclick="window.location.href = 'profile.php?id=<?=$user->data()->id?>';" name="button" class="btn btn-primary">Your Bio</button>
</div>
<div class="form-group">
<button type="button" onclick="window.location.href = 'view_all_users.php';" name="button" class="btn btn-primary">All Users</button>
</div>
