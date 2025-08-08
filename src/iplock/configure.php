  <?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

<?php
include "plugin_info.php";
pluginActive($plugin_name);
 if(!empty($_POST)){
   $token = $_POST['csrf'];
if(!Token::check($token)){
  include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
}
$set = Input::get('plugin-iplock');
if(is_numeric($set) && $set < 4){
  $db->update('settings',1,['plg_iplock'=>$set]);
  Redirect::to('admin.php?view=plugins_config&plugin=iplock');
}
 }
 $token = Token::generate();
 ?>
<div class="content mt-3">
 		<div class="row">
 			<div class="col-sm-12">
          <a href="<?=$us_url_root?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
 					<h1>Configure the IP Lock Plugin!</h1>
          This plugin works in conjuection with the <strong><a href="admin.php?view=ip">UserSpice IP Whitelist</a></strong>
          and forces either all users or just admins to be on that list in order to use the site.  Admins who login from
          a non-whitelisted IP will be logged back out. Other users will be sent to the maintenance.php page.
          127.0.0.1 and ::1 are automatically whitelisted.
          <form class="" action="" method="post">
            <input type="hidden" name="csrf" value="<?=$token?>" />
            <select class="form-control" name="plugin-iplock" required>
              <option value="0"<?php if($settings->plg_iplock == 0){echo "selected";}?>>No IP Locking</option>
              <option value="1"<?php if($settings->plg_iplock == 1){echo "selected";}?>>Admins Must be on IP Whitelist</option>
              <option value="2"<?php if($settings->plg_iplock == 2){echo "selected";}?>>All Logged in Users Must be on IP Whitelist</option>
              <option value="3"<?php if($settings->plg_iplock == 3){echo "selected";}?>>Kill Entire Site For Non-Whitelisted IPs</option>
            </select>
            <input type="submit" name="submit" value="Save" class="btn btn-danger">
          </form>
 			</div> <!-- /.col -->
 		</div> <!-- /.row -->
