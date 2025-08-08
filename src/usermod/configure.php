  <?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

<?php
include "plugin_info.php";
pluginActive($plugin_name);
 if(!empty($_POST['remove'])){
   $token = $_POST['csrf'];
if(!Token::check($token)){
  include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
}
   $email = Input::get('email');
   $fname = Input::get('fname');
   $lname = Input::get('lname');
   if(($email != 1 && $email != 2 && $email != 3) || ($fname != 0 && $fname !=1) || ($lname != 0 && $lname !=1)){
    Redirect::to('admin.php?view=plugins_config&plugin=usermod&err=Invalid Values Provided');
   }
   $set = [];
   if($email == 2){
     $set[]="username";
   }elseif($email == 3){
     $set[]="email";
   }
   if($fname == 0){
     $set[] = "fname";
   }
   if($lname == 0){
     $set[] = "lname";
   }
   $db->update('settings',1,['usermod'=>json_encode($set)]);
   Redirect::to('admin.php?view=plugins_config&plugin=usermod&err=Saved');
 }
 $token = Token::generate();
 ?>
<div class="content mt-3">
 		<div class="row">
 			<div class="col-sm-12">
          <a href="<?=$us_url_root?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
 					<h1>Remove Built In UserSpice Fields</h1>
          <p><strong>Please Note:</strong> This will not remove any data from the DB.  It will merely hide fields
          for existing users. It is recommended that you also change the settings for the echouser function on the existing dashboard.</p>
          <p>To maintain compatibility, user-provided information will be entered as dummy info in the DB. (ie if only email is enabled, the email will be stored as the username and email in the db).</p>
          <p><strong>Currently Disabled:</strong>
            <?php oxfordList(json_decode($settings->usermod));?>
          </p>

          <form class="" action="" method="post">
            <input type="hidden" name="csrf" value="<?=$token?>" />
            <div class="form-group">
              <label for="">Username/Email</label>
              <select class="form-control" name="email" required>
                <option value="" selected disabled>--Choose One--</option>
                <option value="1">Both Enabled</option>
                <option value="2">Username Disabled</option>
                <option value="3">Email Disabled (No Password Recovery)</option>
              </select>
            </div>
            <div class="form-group">
              <label for="">First Name</label>
              <select class="form-control" name="fname" required>
                <option value="" selected disabled>--Choose One--</option>
                <option value="0">Disabled</option>
                <option value="1">Enabled</option>
              </select>
            </div>
            <div class="form-group">
              <label for="">Last Name</label>
              <select class="form-control" name="lname" required>
                <option value="" selected disabled>--Choose One--</option>
                <option value="0">Disabled</option>
                <option value="1">Enabled</option>
              </select>
            </div>
            <input type="submit" name="remove" value="Set Removed Items" class="btn btn-primary">
          </form>
 			</div> <!-- /.col -->
 		</div> <!-- /.row -->
