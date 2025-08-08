  <?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

<?php
include "plugin_info.php";
pluginActive($plugin_name);
 if(!empty($_POST['plugin_autologout'])){
   $token = $_POST['csrf'];
if(!Token::check($token)){
  include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
}
  $db->update('settings',1,['plg_al'=>Input::get('plg_al'),'plg_al_time'=>Input::get('plg_al_time')]);
  Redirect::to("admin.php?view=plugins_config&plugin=autologout&err=Updated");
 }
 $token = Token::generate();
 ?>
<div class="content mt-3">
 		<div class="row">
 			<div class="col-sm-12">
          <a href="<?=$us_url_root?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
 					<h1>Configure the Auto Logout Plugin!</h1>
          <form class="" action="" method="post">
            <input type="hidden" name="csrf" value="<?=$token?>" />
            <label for="">Mode</label>
            <select class="form-control" name="plg_al">
              <option value="0" <?php if($settings->plg_al == 0){echo "selected";} ?>>No Auto Logout</option>
              <option value="1" <?php if($settings->plg_al == 1){echo "selected";} ?>>Admins Only</option>
              <option value="2" <?php if($settings->plg_al == 2){echo "selected";} ?>>Everyone Except Admins</option>
              <option value="3" <?php if($settings->plg_al == 3){echo "selected";} ?>>All Users</option>
            </select>
            <label for="">Timeout in Minutes</label>
            <input class="form-control" type="number" step="1" min="1"  name="plg_al_time" value="<?=$settings->plg_al_time?>">
            <input type="submit" name="plugin_autologout" value="Update" class="btn btn-primary">
          </form>
 			</div> <!-- /.col -->
 		</div> <!-- /.row -->
