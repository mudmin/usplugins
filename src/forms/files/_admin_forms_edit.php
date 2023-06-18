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
    //is the thing you're trying to delete a valid validation type to begin with?
    //we need this check since Input::get sanitizes < symbols, etc
    $edit = Input::get('edit');
    $field = Input::get('field');
    $formName = getFormName($edit,['name'=>1]);
    $getValQ = $db->query("SELECT id,validation FROM $formName WHERE id = ?",array($field));
    $getValC = $getValQ->count();
    if($getValC > 0){
      $getVal = $getValQ->first();
      $current = json_decode($getVal->validation, true);
      unset($current[$delete]);
      $new = json_encode($current);
      $db->update($formName,$field,['validation'=>$new]);
    }
  }
}



$field = Input::get('field');
$edit = Input::get('edit');
$checkQ = $db->query("SELECT * FROM us_forms WHERE id = ?",array($edit));
$checkC = $checkQ->count();
if($checkC < 1 && is_numeric($edit)){
  Redirect::to($us_url_root.'users/admin.php?view=plugins_config&plugin=forms&newFormView=_admin_forms_edit&err=Form+not+found');
}elseif(is_numeric($edit)){
  $check = $checkQ->first();
  $name = formatName($check->form);
  $formQ = $db->query("SELECT * FROM $name");
  $formC = $formQ->count();
  if($formC > 0){
    $form = $formQ->results();
  }
}

if(!empty($_GET['switchto'])){
if(Input::get('switchto') == "manually"){
  $fields = ["select_opts"=>"{\"\":\"\"}"];
  $db->update($name,$field,$fields);
}elseif(Input::get('switchto') == "database"){
  $options = [];
  $options["usformquery"] = "";
  $options["key"] = "id";
  $options["values"] = [];
  $options = json_encode($options);
  $fields = ["select_opts"=>$options];
  $db->update($name,$field,$fields);
}
  Redirect::to("admin.php?view=plugins_config&newFormView=_admin_forms_edit&edit=".$edit."&field=".$field."&plugin=forms&editOpts=true");
}

$lastOrder = Input::get('lastOrder');
if(!is_numeric($lastOrder)){
  $lastQ = $db->query("SELECT ord FROM $name ORDER BY ord DESC");
  $lastC = $lastQ->count();
  if($lastC < 1){
    $lastOrder = 10;
  }else{
    $last = $lastQ->first();
    $lastOrder = $last->ord+10;
  }
}else{
  $lastOrder = $lastOrder + 10;
}
if(!empty($_POST['edit_field'])){
  Redirect::to($us_url_root.'users/admin.php?view=plugins_config&plugin=forms&newFormView=_admin_forms_edit&edit='.$edit.'&field='.$field);
}

if(!empty($_POST['create_field'])){
  //need to check to make sure the column names are not protected sql keywords

  $field_type = Input::get('field_type');
  $editing = Input::get('editing');
  $col = Input::get('col');
  if(!isSqlProtected($col)){
    //check to see if column name is a sql protected keyword
    if($field_type == 'timestamp'){
      //this gives all ts columns the name timestamp and prevents double timestamps in db
      $col = 'timestamp';
    }

    $col = preg_replace("/[^A-Za-z0-9]/", "", $col); //no spaces/chars
    $fields = [];
    foreach($_POST as $k=>$v){
      if(($k != 'create_field') && ($k != 'key') && ($k != 'val')){
        $fields[$k] = Input::get($k);
      }
    }

    $mainTable = substr($name, 0, -5); //name without _form
    $check = $db->query("SELECT * FROM $name WHERE col = ?",array($col))->count();
    if($check < 1){
      if(isset($fields['optStyle'])){unset($fields['optStyle']);}
      $step1 = $db->insert($name,$fields);

      $id = $db->lastId();

      if(Input::get('optStyle') == "database"){
        $opts = [];
        $opts["usformquery"] = "";
        $opts["key"] = "id";
        $opts["values"] = [];
        $opts = json_encode($opts);
      }else{
        $opts = "{\"\":\"\"}";
      }
      $fields = array(
        'select_opts'=>$opts,
        'col'=>$col,
      );

      $db->update($name,$id,$fields);

      if($field_type == "timestamp"){
        $db->query("ALTER TABLE $mainTable ADD timestamp $field_type");
        $fields = array(
          'length'=>0,
          'required'=>0,
          'field_type'=>'timestamp',
          'col_type'=>'timestamp'
        );
        $db->update($name,$id,$fields);
      }elseif($field_type == "number" || $field_type == "tinyint"){
        $field_type = 'int';
        if($field_type == "tinyint"){
          $length = 1;
        }else{
          $length = 11;
        }
        $db->query("ALTER TABLE $mainTable ADD $col $field_type($length)");
        $db->update($name,$id,['col_type'=>'int']);
      }elseif($field_type == "date" || $field_type == "datetime"){
        $db->query("ALTER TABLE $mainTable ADD $col $field_type");
        $fields = array(
          'col_type'=>$field_type,
          'col'=>$col,
        );
        $db->update($name,$id,$fields);
      }elseif($field_type == "money"){
        $db->query("ALTER TABLE $mainTable ADD `$col` decimal(11,2)");
        $fields = array(
          'col_type'=>$field_type,
          'col'=>$col,
        );
        $db->update($name,$id,$fields);
      }elseif($field_type == "textarea" || $field_type == "checkbox"){
        $field_type = 'text';
        $db->query("ALTER TABLE $mainTable ADD $col $field_type");
        $fields = array(
          'col_type'=>$field_type,
          'col'=>$col,
        );
        $db->update($name,$id,$fields);
      }else{
        $db->query("ALTER TABLE $mainTable ADD $col varchar(255)");
        $fields = array(
          'col_type'=>'varchar',
          'col'=>$col,
        );
        $db->update($name,$id,$fields);
      }

    }else{
      bold("<br>A column already exists with that name");
      exit;
    }

    if($field_type == "dropdown" || $field_type == "radio" || $field_type == "checkbox"){
          Redirect::to($us_url_root."users/admin.php?view=plugins_config&plugin=forms&newFormView=_admin_forms_edit&edit=".$edit."&field=".$id."&editOpts=".$id);
    }else{
          Redirect::to($us_url_root."users/admin.php?view=plugins_config&plugin=forms&newFormView=_admin_forms_edit&edit=".$edit."&lastOrder=".$lastOrder);
    }

  }//end of SQL protected checking
  bold("<br>".$col." is a SQL protected keyword, so you can't use it");
}


if(!empty($_POST['delete_field'])){
  $delete = Input::get('delete');
  $q = $db->query("DELETE FROM $name WHERE id = ?",array($delete));
  Redirect::to($us_url_root."users/admin.php?view=plugins_config&plugin=forms&newFormView=_admin_forms_edit&err=Field+deleted&edit=".$edit);
}

if(!empty($_POST['edit_this_field'])){
  $field = Input::get('editing');

  $fields = array(
    'form_descrip'=>Input::get('form_descrip'),
    'table_descrip'=>Input::get('table_descrip'),
    'required'=>Input::get('required'),
    'field_class'=>Input::get('field_class'),
    'input_html'=>Input::get('input_html'),
    'ord'=>Input::get('ord'),
  );
  $db->update($name,$field,$fields);
  // dnd($db->errorInfo());
  // $id = $db->lastId();
  // $keys = Input::get('key');
  // $vals = Input::get('val');
  // $opts = array_combine($keys, $vals);
  // $opts = json_encode($opts);
  //
  //
  // $fields = array(
  //   'select_opts'=>$opts,
  // );
  // $db->update($name,$field,$fields);
  Redirect::to($us_url_root."users/admin.php?view=plugins_config&plugin=forms&newFormView=_admin_forms_edit&edit=".$edit);
}

if(!empty($_POST['edit_this_field_options'])){
  $field = Input::get('editing');

  $keys = Input::get('key');
  $vals = Input::get('val');

  $opts = array_combine($keys, $vals);

  if(isset($keys[0]) && $keys[0] == "usformquery"){
    $sk = Input::get('schemakey');
    $sv = Input::get('schemaval');
    $opts['values'] = [];
    // dump($sk);
    // dump($sv);

    $counter = 0;
    foreach($sk as $k=>$v){

      $opts['values'][$counter] = [$v=>$sv[$k]];
      $counter++;
    }
  }
  $opts = json_encode($opts);


  $fields = array(
    'select_opts'=>$opts,
  );
  $db->update($name,$field,$fields);
  Redirect::to($us_url_root."users/admin.php?view=plugins_config&plugin=forms&newFormView=_admin_forms_edit&edit=".$edit);
}

?>

<div class="content mt-3">
  <?php require_once($abs_us_root.$us_url_root.'usersc/plugins/forms/files/_form_manager_menu.php');?>
  <?php if(is_numeric($autogen)){ ?>
    <div class="row">
      <div class="col-sm-12">
        <strong><font color="red">Please note:</font> This is a form that was automatically created from a database table.  Your form labels were created from the database column names, so you will probably want to edit those. If you want more complicated fields like dropdowns, checkboxes, simply go into the database and change the field_type.
        </div>
      </div>
    <?php } ?>



    <?php if(is_numeric($edit)){?>
      <h3>Managing the "<font color="blue"><?=$check->form?></font>" form</h3>
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
        if($formC < 1){
          echo "No fields found. Add one!";
        }else{
          displayForm($check->form,['nosubmit'=>1]);
        }
        ?>
      </div>
      <div class="col-sm-6"><?php require_once($abs_us_root.$us_url_root.'usersc/plugins/forms/files/_form_edit_delete_reorder.php');?></div>
    <?php } //end editing section
    ?>

  </div>
</div>
