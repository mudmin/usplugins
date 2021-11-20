  <?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>
<style media="screen">
  .blue{color:blue;}
</style>
<?php
include "plugin_info.php";
pluginActive($plugin_name);
 if(!empty($_POST['plugin_rememberme'])){
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
 					<h1>There is nothing to configure!</h1>
          <br>
          <h4 class="blue">If you appreciate this plugin and would like to make a donation to the author, you can do so at <a href="https://UserSpice.com/donate">https://UserSpice.com/donate</a>.
            <br>Either way, thanks for using UserSpice!</h4>
 			</div> <!-- /.col -->
 		</div> <!-- /.row -->
