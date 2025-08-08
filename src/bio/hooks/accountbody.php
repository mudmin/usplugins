<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
global $user;
$check = $db->query("SELECT id FROM profiles WHERE user_id = ?",[$user->data()->id])->count();
if($check < 1){
  $db->insert('profiles',['user_id'=>$user->data()->id,'bio'=>"This is your bio"]);
}
?>
<div class="form-group">
<button type="button" onclick="window.location.href = 'profile.php?id=<?=$user->data()->id?>';" name="button" class="btn btn-primary btn-block">Your Bio</button>
</div>
<div class="form-group">
<button type="button" onclick="window.location.href = 'view_all_users.php';" name="button" class="btn btn-primary btn-block">All Users</button>
</div>
