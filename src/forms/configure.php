<?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>
  <div class="content mt-3">
    <div class="row">
      <div class="col-sm-12">
<?php
if(!empty($_POST['delete_view'])){
  $delete = Input::get("delete_view");
  $q = $db->query("SELECT id FROM us_form_views WHERE id = ?",array($delete));
  $c = $q->count();
  if($c > 0){
    $db->query("DELETE FROM us_form_views WHERE id = ?",array($delete));
    Redirect::to($us_url_root.'users/admin.php?view=plugins_config&plugin=forms&err=View+deleted');
  }
}
include "plugin_info.php";
pluginActive($plugin_name);
$newFormView = Input::get('newFormView');

if($newFormView  == ""){
  include "files/_admin_forms.php";
}else{
  include "files/".$newFormView.".php";
}
