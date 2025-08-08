  <?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

<?php
include "plugin_info.php";
pluginActive($plugin_name);
$action = Input::get('action');
if($action == ''){$action = "manager";}
?>
<div class="content mt-3">

<div class="row">
  <div class="col-sm-12">
      <a href="<?=$us_url_root?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
<?php
if($action == 'manager'){ include $abs_us_root.$us_url_root.'usersc/plugins/forum/assets/forum_manager.php';}
if($action == 'edit_category'){ include $abs_us_root.$us_url_root.'usersc/plugins/forum/assets/edit_category.php';}
if($action == 'edit_board'){ include $abs_us_root.$us_url_root.'usersc/plugins/forum/assets/edit_board.php';}
 ?>
</div>
</div>
