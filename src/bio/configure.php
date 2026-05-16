  <?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

<?php
include "plugin_info.php";
pluginActive($plugin_name);
 if(!empty($_POST['plugin_bio'])){
   $token = $_POST['csrf'];
if(!Token::check($token)){
  include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
}
   // Redirect::to('admin.php?err=I+agree!!!');
 }
 $token = Token::generate();
 ?>
<div class="content mt-3">
 		<div class="row">
 			<div class="col-sm-12">
          <a href="<?=$us_url_root?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
          <div class="alert alert-light border py-2 px-3 small text-muted mt-3" role="note">
            <i class="fa fa-info-circle mr-1"></i>
            <strong>CSP note:</strong> the profile editor loads the Summernote editor from <code>https://cdnjs.cloudflare.com</code>, and the profile page loads Google reCAPTCHA from <code>https://www.google.com</code>. If your site sends a <em>Content-Security-Policy</em> header, add those origins to <code>script-src</code> (and <code>style-src</code>) or those features will not load.
          </div>
 					<h1>There's nothing to configure!</h1>
          <br><br>
          If you appreciate this plugin and would like to make a donation to the author, you can do so at <a href="https://UserSpice.com/donate">https://UserSpice.com/donate</a>. Either way, thanks for using UserSpice!
          <br><br>

 			</div> <!-- /.col -->
 		</div> <!-- /.row -->
