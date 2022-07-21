<?php
if(!in_array($user->data()->id,$master_account)){die();}
if (!securePage($_SERVER['PHP_SELF'])){die();}

//Errors Successes
$errors = [];
$successes = [];

$field = Input::get('field');
$edit = Input::get('edit');
if(!pluginActive("apibuilder",true)){
  Redirect::to($us_url_root.'users/admin.php?view=plugins_config&plugin=forms&newFormView=_admin_forms_edit&err=API Builder Plugin is disabled');
}
$checkQ = $db->query("SELECT * FROM us_forms WHERE id = ?",array($edit));
$checkC = $checkQ->count();
if($checkC < 1 && is_numeric($edit)){
  Redirect::to($us_url_root.'users/admin.php?view=plugins_config&plugin=forms&newFormView=_admin_forms_edit&err=Form+not+found');
}elseif(is_numeric($edit)){
  $check = $checkQ->first();
  $name = formatName($check->form);
  $formQ = $db->query("SELECT * FROM $name ORDER BY `ord`");
  $formC = $formQ->count();
  if($formC > 0){
    $form = $formQ->results();
  }
}
if($check->api_update == 0 && $check->api_insert == 0){
  $status = "<font color='red'>Disabled</font>";
}else{
  $status = "<font color='green'>Enabled</font>";
}
$perms = $db->query("SELECT * FROM permissions")->results();
if(!empty($_POST)){
  if(!Token::check(Input::get('csrf'))){
    include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
  }

  $ins = permInput(Input::get('api_perms_insert'));

  $upd = permInput(Input::get('api_perms_update'));

  if(!$ins || !$upd){
    Redirect::to("admin.php?view=plugins_config&plugin=forms&newFormView=_admin_forms_api_settings&edit=$edit&err=You must select at least one permission");
  }


  $fields = [
    'api_insert'=>Input::get('api_insert'),
    'api_update'=>Input::get('api_update'),
    'api_user_col'=>Input::get('api_user_col'),
    'api_force_user_col'=>Input::get('api_force_user_col'),
    'api_perms_insert'=>$ins,
    'api_perms_update'=>$upd
  ];

  $db->update("us_forms",$edit,$fields);
  Redirect::to("admin.php?view=plugins_config&plugin=forms&newFormView=_admin_forms_api_settings&edit=$edit");
}

?>

<div class="content mt-3">
  <?php require_once($abs_us_root.$us_url_root.'usersc/plugins/forms/files/_form_manager_menu.php');?>
  <div class="row">
    <div class="col-12 col-sm-4 text-center">
      <h3>API Status for this form</h3>
      <h4><?=$status?></h4>
    </div>
    <div class="col-12 col-sm-4 text-center">
      <h3>API "insert" allowed</h3>
      <h4><?=bin($check->api_insert);?></h4>
    </div>
    <div class="col-12 col-sm-4 text-center">
      <h3>API "update" allowed </h3>
      <h4><?=bin($check->api_update);?></h4>
    </div>
  </div>
  <br>
  <div class="row">
    <div class="col-12 col-sm-9">

      <h3>Managing the "<font color="blue"><?=$check->form?></font>" API</h3>
      <p>These settings allow you to use the UserSpice API Builder and its general API to turn one of your existing forms into an API endpoint. This means that you can sumbit and update forms over the API.  It's a fantastic feature, but there are some things you will need to think through.  This page will guide you through that process.</p>

      <h3>Settings</h3><br>
      <form class="" action="" method="post">
        <input type="hidden" name="csrf" value="<?=Token::generate();?>">
        <h5>Security</h5>
        <p>Because we are processing forms via an API, you may want to consider linking the data of your form insert/update to a user id in the table itself. This allows you prevent any user-specified data from being inserted into a row not "owned" by that user. This is because we are using the user's authentication data to determine this and not any user-supplied data.  Accomplishing this is a two step process.  You must tell us which column in the table(form) has been set aside for the user id (should be int/whole number). Then you must tell us to enforce this rule (on by default).</p>

        <p>When this feature is enabled and configured the authenticated user id will automatically be entered in that column and that same id will be required for that row to be updated by the API.  Note that you will still have to specify the row that you are requesting to update. </p>

        <select class="" name="api_user_col">
          <option value=" ">--No Column Selected--</option>
          <?php foreach($form as $f){ ?>
            <option value="<?=$f->col?>" <?php if($check->api_user_col == $f->col){ echo "selected = 'selected'";}?>><?=$f->col?></option>
          <?php } ?>
        </select>
        <hr>
        <p>Select "Yes" to enable this feature, otherwise "No" to disable it. With this feature enabled, the logged in user id will be auto inserted into all inserts and updates will be prevented if the selected column does not match the user id.  If you select "Yes" without specifying a column, the form will fail.</p>
        <select class="" name="api_force_user_col">
          <option value="0" <?php if ($check->api_force_user_col == 0){echo "selected='selected'";} ?> >No</option>
          <option value="1" <?php if ($check->api_force_user_col == 1){echo "selected='selected'";} ?> >Yes</option>
        </select>
        <hr>
        <h5>API Insert</h5>
        <p>What permissions are required to perform an insert? Note that if inserting is disabled, this does not override that setting. You must specifiy at least one permission. </p>
        <div class="row">
          <?php foreach($perms as $p) {
            $per = explode(",",$check->api_perms_insert);
            ?>
            <div class="col-3">
              <input type="checkbox" name="api_perms_insert[]" value="<?=$p->id?>" <?php if(in_array($p->id,$per)){echo "checked"; }?> > <?=$p->name?>
            </div>
          <?php } ?>
        </div>
        <hr>
        <p>Enable API Insert on this form. By setting this option to yes, you will be able to use this form as an API endpoint to insert new rows into your <b><?=$check->form?></b> table.</p>
        <select class="" name="api_insert">
          <option value="0" <?php if ($check->api_insert == 0){echo "selected='selected'";} ?> >No</option>
          <option value="1" <?php if ($check->api_insert == 1){echo "selected='selected'";} ?> >Yes</option>
        </select>
        <hr>
        <h5>API Update</h5>
        <p><b>Warning: </b>Updating existing data in your database requires special consideration, namely deciding exactly which row the user will be updating. The options below will help you make that determination.</p>

        <p>What permissions are required to perform an insert? Note that if inserting is disabled, this does not override that setting.You must specifiy at least one permission. </p>
        <div class="row">
          <?php foreach($perms as $p) {
            $per = explode(",",$check->api_perms_update);
            ?>
            <div class="col-3">
              <input type="checkbox" name="api_perms_update[]" value="<?=$p->id?>" <?php if(in_array($p->id,$per)){echo "checked"; }?>> <?=$p->name?>
            </div>
          <?php } ?>
        </div>
        <hr>

        <p>Enable API Update on this form. By setting this option to yes, you will be able to use this form as an API endpoint to update rows on your <b><?=$check->form?></b> table.</p>

        <select class="" name="api_update">
          <option value="0" <?php if ($check->api_update == 0){echo "selected='selected'";} ?> >No</option>
          <option value="1" <?php if ($check->api_update == 1){echo "selected='selected'";} ?> >Yes</option>
        </select>
        <p>
          <br>
          <input type="submit" name="save" value="Save Settings" class="btn btn-outline-primary">
        </p>
      </form>
    </div>
    <div class="col-12 col-sm-3">
      <h3>Columns for API Calls</h3>

      <table class="table table-striped">
        <thead>
          <tr>
            <th>Column Name</th><th>Description</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($form as $f){ ?>
            <tr>
              <td><?=$f->col;?></td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
      Include these columns in your apiData.  You must specifiy apiData[form_name] for the whole form and apiData[columnName] for each column you are updating/inserting.
    </div>
  </div>
</div>
