  <?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

<?php
include "plugin_info.php";
pluginActive($plugin_name);
 if(!empty($_POST['plugin_saas'])){
   $token = $_POST['csrf'];
if(!Token::check($token)){
  include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
}
   // Redirect::to($us_url_root . 'users/admin.php?err=I+agree!!!');
 }
 $token = Token::generate();
 $v = Input::get('v');
 ?>
<div class="content mt-3">
 		<div class="row">
 			<div class="col-sm-12">
          <a href="<?=$us_url_root?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
 					<h1>Software-as-a-Service Plugin!</h1>
      <?php
      include($abs_us_root.$us_url_root.'usersc/plugins/saas/navbar.php');
      if(file_exists($abs_us_root.$us_url_root.'usersc/plugins/saas/assets/'.$v.'.php')){
          include($abs_us_root.$us_url_root.'usersc/plugins/saas/assets/'.$v.'.php');
        }else{
          include($abs_us_root.$us_url_root.'usersc/plugins/saas/assets/main.php');
        }
        ?>

 			</div> <!-- /.col -->
 		</div> <!-- /.row -->
