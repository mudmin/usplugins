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
 					<?php if(file_exists($abs_us_root . $us_url_root . "usersc/plugins/messaging/assets/views/_".$mode.".php")){ 

            include($abs_us_root . $us_url_root . "usersc/plugins/messaging/assets/views/_".$mode.".php"); 
            }else{
             echo "No file found for this mode."; 
             } ?>

 			</div>
 		</div>


    <!-- Do not close the content mt-3 div in this file -->
