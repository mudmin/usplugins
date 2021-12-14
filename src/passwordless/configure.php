  <?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

<?php
include "plugin_info.php";
pluginActive($plugin_name);
$email = $db->query("SELECT * FROM email")->first();
$pwlset = $db->query("SELECT * FROM plg_passwordless_settings")->first();
 if(!empty($_POST)){
   if(!Token::check(Input::get('csrf'))){
     include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
   }
   $fields = [
     "timeout"=>(int)Input::get('timeout'),
     "link"=>Input::get('link'),
     "subject"=>Input::get('subject'),
     "bottom"=>Input::get('bottom'),
     "top"=>Input::get('top'),
   ];
   $db->update("plg_passwordless_settings",$pwlset->id,$fields);
   sessionValMessages("","Settings updated");
   Redirect::to("admin?view=plugins_config&plugin=passwordless");
 }

?>

<div class="content mt-3">
 		<div class="row">
 			<div class="col-12">
          <a href="<?=$us_url_root?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
 					<h1>Configure the Passwordless Plugin!</h1>
                <p>If you appreciate this plugin and would like to make a donation to the author, you can do so at <a style="color:blue;" href="https://UserSpice.com/donate">https://UserSpice.com/donate</a>. Either way, thanks for using UserSpice!</p>
          <p>This plugin requires some basic setup and has a few options you might want to consider.</p>
          <p>It is <b>very important</b> to this plugin that your
            <a style="color:blue;" href="<?=$us_url_root?>users/admin?view=email">Email Settings</a> are properly configured and tested.  <br>Beyond just being able to send an email, it is very important that the Site URL setting on that page is correct as we will be using it.
          </p>
          <form class="" action="" method="post">
            <input type="submit" name="save" value="Save Settings" class="btn btn-primary">
            <input type="hidden" name="csrf" value="<?=Token::generate();?>">
            <div class="row">
              <div class="col-12">
                <div class="form-group">
                  <h4>How many minutes before the link expires?</h4>
                  <input type="number" name="timeout" value="<?=$pwlset->timeout?>" class="form-control" required min="1" step="1">
                </div>
                <div class="form-group">
                  <h4>Login Page</h4>
                  <p>This plugin contains a pwl.php file in usersc/plugins/passwordless/files that will handle both the generating and the parsing of email links.  Of course, that may not be where you want the file to be located or what you want it to be called.  You can copy/rename this file, but you must give us this path.  No slash at the beginning.</p>
                  <p><b>Pro Tip:</b>  If you want to ONLY allow passwordless logins, you can copy this file to usersc and rename it to login.php</p>
                  <p>In this case, you would enter <br><b>usersc/login.php<b><br>In the box below. Don't forget to update the init.php line in that file with the proper path. It is also expected that you will edit the copied version of this file to put in your own language and styling.</p>
                  <input type="text" name="link" value="<?=$pwlset->link?>" class="form-control" required>
                  <p>Note: After saving, you can click the link below to confirm that your link is configured properly.<br>
                    <a style="color:blue;" href="<?=$email->verify_url?><?=$pwlset->link?>?verifylink=true" target="_blank">Click here to test your link</a>
                  </p>
                </div>
                <div class="form-group">
                  <h4>Subject line of your passwordless login email</h4>
                  <input type="text" name="subject" value="<?=$pwlset->subject?>" class="form-control" required>
                </div>

                <div class="form-group">
                  <h4>Your message BEFORE the passwordless login link in the body of the email (HTML okay)</h4>
                  <textarea name="top" class="form-control" required><?=$pwlset->top?></textarea>
                </div>

                <div class="form-group">
                  <h4>Your message AFTER the passwordless login link in the body of the email (HTML okay)</h4>
                  <textarea name="bottom" class="form-control" required><?=$pwlset->bottom?></textarea>
                </div>


              </div>
            </div>
            <input type="submit" name="save" value="Save Settings" class="btn btn-primary">
          </form>

 			</div>
 		</div>


    <!-- Do not close the content mt-3 div in this file -->
