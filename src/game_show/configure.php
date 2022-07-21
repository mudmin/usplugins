  <?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

<?php
include "plugin_info.php";
pluginActive($plugin_name);
?>

<div class="content mt-3">
 		<div class="row">
 			<div class="col-12">
          <a href="<?=$us_url_root?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
 					<?php
          if(file_exists($abs_us_root.$us_url_root."game.php")){
            Redirect::to($us_url_root."game.php?view=game_settings");
          }else{ ?>
          <h3>Your game.php file is missing from the root of your project.  copy it from usersc/plugins/game_show/files/ and try again<h3>
         <?php  }  ?>

 			</div>
 		</div>


    <!-- Do not close the content mt-3 div in this file -->
