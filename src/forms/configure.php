<?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>
  <div class="content mt-3">
    <div class="row">
      <div class="col-sm-12">
<?php
include "plugin_info.php";
pluginActive($plugin_name);
$newFormView = Input::get('newFormView');

if($newFormView  == ""){
  include "files/_admin_forms.php";
}else{
  include "files/".$newFormView.".php";
}
