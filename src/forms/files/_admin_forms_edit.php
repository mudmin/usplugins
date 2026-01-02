<?php
if(!in_array($user->data()->id,$master_account)){die();}
if (!securePage($_SERVER['PHP_SELF'])){die();}

//Errors Successes
$errors = [];
$successes = [];
$formsQ = $db->query('SELECT * FROM us_forms ORDER BY form');
$formsC = $formsQ->count();
if($formsC > 0){
  $forms = $formsQ->results();
}
$autogen = Input::get('autogen');

if(!empty($_POST['deleteValidation'])){
  $delete = Input::get('toDelete');
  if(isValidValidation($delete)){
    $edit = Input::get('edit');
    $fieldId = (int)Input::get('field');
    
    if($fieldId <= 0){
      die("Invalid field ID");
    }

    $formName = getFormName($edit,['name'=>1]);
    if($formName === "not found" || !preg_match('/^[a-zA-Z0-9_]+$/', $formName)){
      die("Invalid form name");
    }

    $getValQ = $db->query("SELECT id,validation FROM `$formName` WHERE id = ?", [$fieldId]);
    if($getValQ->count() > 0){
      $getVal = $getValQ->first();
      $current = json_decode($getVal->validation, true);
      unset($current[$delete]);
      $new = json_encode($current);
      $db->update($formName, $fieldId, ['validation'=>$new]);
    }
  }
}

$field = Input::get('field');
$edit = Input::get('edit');
$checkQ = $db->query("SELECT * FROM us_forms WHERE id = ?", array($edit));
$checkC = $checkQ->count();

if($checkC < 1 && is_numeric($edit)){
  Redirect::to($us_url_root.'users/admin.php?view=plugins_config&plugin=forms&newFormView=_admin_forms_edit&err=Form+not+found');
}elseif(is_numeric($edit)){
  $check = $checkQ->first();

  if(!preg_match('/^[a-zA-Z0-9_]+$/', $check->form)){
    die("Invalid form name in database");
  }
  $name = formatName($check->form);

  $formQ = $db->query("SELECT * FROM `$name` ");
  if($formQ->count() > 0){
    $form = $formQ->results();
  }
}

if(!empty($_GET['switchto'])){
  $fieldInt = (int)$field;
  if($fieldInt <= 0){ die("Invalid field ID"); }
  
  if(Input::get('switchto') == "manually"){
    $db->update($name, $fieldInt, ["select_opts"=>"{\"\":\"\"}"]);
  }elseif(Input::get('switchto') == "database"){
    $options = json_encode(["usformquery" => "", "key" => "id", "values" => []]);
    $db->update($name, $fieldInt, ["select_opts"=>$options]);
  }
  Redirect::to("admin.php?view=plugins_config&newFormView=_admin_forms_edit&edit=".$edit."&field=".$fieldInt."&plugin=forms&editOpts=true");
}

$lastOrder = Input::get('lastOrder');
if(!is_numeric($lastOrder)){
  $lastQ = $db->query("SELECT ord FROM `$name` ORDER BY ord DESC");
  if($lastQ->count() < 1){
    $lastOrder = 10;
  }else{
    $lastOrder = $lastQ->first()->ord + 10;
  }
}else{
  $lastOrder = $lastOrder + 10;
}

if(!empty($_POST['create_field'])){
  $field_type = Input::get('field_type');
  $col = Input::get('col');
  
  if(!isSqlProtected($col)){
    if($field_type == 'timestamp'){ $col = 'timestamp'; }
    $col = preg_replace("/[^A-Za-z0-9_]/", "", $col);
    
    $fields = [];
    foreach($_POST as $k=>$v){
      if(!in_array($k, ['create_field', 'key', 'val'])){
        $fields[$k] = Input::get($k);
      }
    }

    $mainTable = substr($name, 0, -5);
    if(!preg_match('/^[a-zA-Z0-9_]+$/', $mainTable)){ die("Invalid target table"); }

    $check = $db->query("SELECT id FROM `$name` WHERE col = ?", [$col])->count();
    if($check < 1){
      if(isset($fields['optStyle'])){unset($fields['optStyle']);}
      $db->insert($name, $fields);
      $id = $db->lastId();

      $opts = (Input::get('optStyle') == "database") ? json_encode(["usformquery" => "", "key" => "id", "values" => []]) : "{\"\":\"\"}";
      $db->update($name, $id, ['select_opts' => $opts, 'col' => $col]);

      // Hardened ALTER TABLE statements - whitelisted by regex validation on lines 111 & 116
      if($field_type == "timestamp"){
        $db->query("ALTER TABLE `$mainTable` ADD `timestamp` $field_type");
        $db->update($name,$id,['length'=>0,'required'=>0,'field_type'=>'timestamp','col_type'=>'timestamp']);
      }elseif($field_type == "number" || $field_type == "tinyint"){
        $length = ($field_type == "tinyint") ? 1 : 11;
        $db->query("ALTER TABLE `$mainTable` ADD `$col` int($length)");
        $db->update($name,$id,['col_type'=>'int']);
      }elseif($field_type == "date" || $field_type == "datetime" || $field_type == "money"){
        $type = ($field_type == "money") ? "decimal(11,2)" : $field_type;
        $db->query("ALTER TABLE `$mainTable` ADD `$col` $type");
        $db->update($name,$id,['col_type'=>$field_type, 'col'=>$col]);
      }elseif($field_type == "textarea" || $field_type == "checkbox"){
        $db->query("ALTER TABLE `$mainTable` ADD `$col` text");
        $db->update($name,$id,['col_type'=>'text', 'col'=>$col]);
      }else{
        $db->query("ALTER TABLE `$mainTable` ADD `$col` varchar(255)");
        $db->update($name,$id,['col_type'=>'varchar', 'col'=>$col]);
      }
    }else{
      bold("<br>A column already exists with that name"); exit;
    }

    $redirectUrl = $us_url_root."users/admin.php?view=plugins_config&plugin=forms&newFormView=_admin_forms_edit&edit=".$edit;
    if(in_array($field_type, ["dropdown", "radio", "checkbox"])){
      Redirect::to($redirectUrl."&field=".$id."&editOpts=".$id);
    }else{
      Redirect::to($redirectUrl."&lastOrder=".$lastOrder);
    }
  }
  bold("<br>".$col." is a SQL protected keyword or invalid");
}

if(!empty($_POST['delete_field'])){
  $delete = Input::get('delete');
  if(is_numeric($delete) && preg_match('/^[a-zA-Z0-9_]+$/', $name)){
     $db->query("DELETE FROM `$name` WHERE id = ?", [$delete]);
     Redirect::to($us_url_root."users/admin.php?view=plugins_config&plugin=forms&newFormView=_admin_forms_edit&err=Field+deleted&edit=".$edit);
  }
}

if(!empty($_POST['edit_this_field'])){
  $field = (int)Input::get('editing');
  if($field <= 0){ die("Invalid field ID"); }
  
  $fields = [
    'form_descrip'=>Input::get('form_descrip'),
    'table_descrip'=>Input::get('table_descrip'),
    'required'=>Input::get('required'),
    'field_class'=>Input::get('field_class'),
    'input_html'=>Input::get('input_html'),
    'ord'=>Input::get('ord'),
  ];
  $db->update($name, $field, $fields);
  Redirect::to($us_url_root."users/admin.php?view=plugins_config&plugin=forms&newFormView=_admin_forms_edit&edit=".$edit);
}

if(!empty($_POST['edit_this_field_options'])){
  $field = (int)Input::get('editing');
  if($field <= 0){ die("Invalid field ID"); }

  $keys = Input::get('key');
  $vals = Input::get('val');
  $opts = array_combine($keys, $vals);

  if(isset($keys[0]) && $keys[0] == "usformquery"){
    $sk = Input::get('schemakey');
    $sv = Input::get('schemaval');
    $opts['values'] = [];
    foreach($sk as $k=>$v){
      $opts['values'][] = [$v=>$sv[$k]];
    }
  }
  $db->update($name, $field, ['select_opts'=>json_encode($opts)]);
  Redirect::to($us_url_root."users/admin.php?view=plugins_config&plugin=forms&newFormView=_admin_forms_edit&edit=".$edit);
}
?>

<div class="content mt-3">
  <?php require_once($abs_us_root.$us_url_root.'usersc/plugins/forms/files/_form_manager_menu.php');?>
  <?php if(is_numeric($autogen)){ ?>
    <div class="row">
      <div class="col-sm-12">
        <strong><font color="red">Please note:</font></strong> This is a form that was automatically created from a database table.
      </div>
    </div>
  <?php } ?>

  <?php if(is_numeric($edit)){?>
    <h3>Managing the "<font color="blue"><?=htmlspecialchars($check->form)?></font>" form</h3>
    <?php
    if(!is_numeric($field)){
      require_once($abs_us_root.$us_url_root.'usersc/plugins/forms/files/_form_create_field.php');
    }elseif(Input::get('editOpts') != ""){
      require_once($abs_us_root.$us_url_root.'usersc/plugins/forms/files/_form_edit_options.php');
    }else{
      require_once($abs_us_root.$us_url_root.'usersc/plugins/forms/files/_form_edit_field.php');
    }
    ?>
    <div class="col-sm-6" >
      <h2>Form Preview</h2>
      <input type="button" value="Refresh Page" onClick="window.location.reload()">
      <?php
      if($formQ->count() < 1){
        echo "No fields found. Add one!";
      }else{
        displayForm($check->form,['nosubmit'=>1]);
      }
      ?>
    </div>
    <div class="col-sm-6"><?php require_once($abs_us_root.$us_url_root.'usersc/plugins/forms/files/_form_edit_delete_reorder.php');?></div>
  <?php } ?>
</div>