  <?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

<?php
include "plugin_info.php";
pluginActive($plugin_name);
 if(!empty($_POST)){
   if(!Token::check(Input::get('csrf'))){
     include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
   }
   // Redirect::to('admin.php?err=I+agree!!!');
 }

 $mode = Input::get('mode');
 if($mode == ""){
  $mode = "settings";
 }


 require_once $abs_us_root.$us_url_root.'usersc/plugins/messaging/assets/views/config_menu.php';
?>

<div class="content mt-3">
 		<div class="row">
 			<div class="col-12">
          <a href="<?=$us_url_root?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
          <div class="alert alert-light border py-2 px-3 small text-muted mt-3" role="note">
            <i class="fa fa-info-circle mr-1"></i>
            <strong>CSP note:</strong> the message composer loads the Select2 and Summernote libraries from <code>https://cdnjs.cloudflare.com</code>. If your site sends a <em>Content-Security-Policy</em> header, add that origin to <code>script-src</code> (and <code>style-src</code>) or those controls will not load.
          </div>
 					<?php if(file_exists($abs_us_root . $us_url_root . "usersc/plugins/messaging/assets/views/_".$mode.".php")){

            include($abs_us_root . $us_url_root . "usersc/plugins/messaging/assets/views/_".$mode.".php"); 
            }else{
             echo "No file found for this mode."; 
             } ?>

 			</div>
 		</div>


    <!-- Do not close the content mt-3 div in this file -->
