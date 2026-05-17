<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
global $user;
$check = $db->query("SELECT id FROM profiles WHERE user_id = ?",[$user->data()->id])->count();
if($check < 1){
  $db->insert('profiles',['user_id'=>$user->data()->id,'bio'=>"This is your bio"]);
}
?>
<div class="form-group mb-3">
<a href="profile.php?id=<?=safeReturn($user->data()->id)?>" role="button" class="btn btn-primary btn-block">Your Bio</a>
</div>
<div class="form-group mb-3">
<a href="view_all_users.php" role="button" class="btn btn-primary btn-block">All Users</a>
</div>
