<?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

<?php
include "plugin_info.php";
pluginActive($plugin_name);
Redirect::to("admin.php?view=messages");
if(!empty($_POST)){
  $token = $_POST['csrf'];
if(!Token::check($token)){
  include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
}
}

 $token = Token::generate();

 ?>
<div class="content mt-3">
 		<div class="row">
 			<div class="col-sm-6">
      </div>
    </div>
 		</div> <!-- /.row -->
