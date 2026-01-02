<?php
if(!in_array($user->data()->id,$master_account)){die();}
if (!securePage($_SERVER['PHP_SELF'])){die();}

$errors = [];
$successes = [];

$field = Input::get('field');
$edit = Input::get('edit');
if(!pluginActive("apibuilder",true)){
  Redirect::to($us_url_root.'users/admin.php?view=plugins_config&plugin=forms&newFormView=_admin_forms_edit&err=API+Builder+Plugin+is+disabled');
}
$checkQ = $db->query("SELECT * FROM us_forms WHERE id = ?",array($edit));
$checkC = $checkQ->count();
if($checkC < 1 && is_numeric($edit)){
  Redirect::to($us_url_root.'users/admin.php?view=plugins_config&plugin=forms&newFormView=_admin_forms_edit&err=Form+not+found');
}elseif(is_numeric($edit)){
  $check = $checkQ->first();
  if(!preg_match('/^[a-zA-Z0-9_]+$/', $check->form)){
    die("Invalid form name");
  }
  $name = formatName($check->form);
  $formQ = $db->query("SELECT * FROM `$name` ORDER BY `ord` ");
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
    Redirect::to("admin.php?view=plugins_config&plugin=forms&newFormView=_admin_forms_api_settings&edit=$edit&err=You+must+select+at+least+one+permission");
  }

  if(!is_numeric($edit) || (int)$edit <= 0){
    die("Invalid form ID");
  }
  $edit = (int)$edit;

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
      <h3>Managing the "<font color="blue"><?=htmlspecialchars($check->form)?></font>" API</h3>
      <p>These settings allow you to use the UserSpice API Builder and its general API to turn one of your existing forms into an API endpoint.</p>
      <form class="" action="" method="post">
        <input type="hidden" name="csrf" value="<?=Token::generate();?>">
        <h5>Security</h5>
        <select class="" name="api_user_col">
          <option value=" ">--No Column Selected--</option>
          <?php foreach($form as $f){ ?>
            <option value="<?=$f->col?>" <?php if($check->api_user_col == $f->col){ echo "selected = 'selected'";}?>><?=$f->col?></option>
          <?php } ?>
        </select>
        <hr>
        <select class="" name="api_force_user_col">
          <option value="0" <?php if ($check->api_force_user_col == 0){echo "selected='selected'";} ?> >No</option>
          <option value="1" <?php if ($check->api_force_user_col == 1){echo "selected='selected'";} ?> >Yes</option>
        </select>
        <hr>
        <h5>API Insert</h5>
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
        <select class="" name="api_insert">
          <option value="0" <?php if ($check->api_insert == 0){echo "selected='selected'";} ?> >No</option>
          <option value="1" <?php if ($check->api_insert == 1){echo "selected='selected'";} ?> >Yes</option>
        </select>
        <hr>
        <h5>API Update</h5>
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
          <tr><th>Column Name</th></tr>
        </thead>
        <tbody>
          <?php foreach($form as $f){ ?>
            <tr><td><?=htmlspecialchars($f->col);?></td></tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
</div>