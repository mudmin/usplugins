<?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

<div class="content mt-3">
 		<div class="row">
 			<div class="col-sm-12">
           <?php  ?>
          <a href="<?=$us_url_root?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
          <a href="<?=$us_url_root?>usersc/plugins/dbman/files/index.php">
<?php
//we need to check to explicitly make sure that the plugin is active
include "plugin_info.php";
if(pluginActive($plugin_name)){
  Redirect::to($us_url_root.'usersc/plugins/dbman/files/index.php');
}
?>
                </div>
            </div>
          </div>
