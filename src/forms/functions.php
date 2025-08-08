<?php
function formField($o, $v = []){
  global $abs_us_root, $us_url_root;
  $db = DB::getInstance();
  $v = (object) $v;
  $u = 0;
  $value = "";
  if(isset($v->update)){
    $u = 1;
    $value = get_object_vars($v);
    if(isset($value[$o->col])){
      $value = $value[$o->col];
    }else{
      $value = '';
    }
  }
  //note that formField expects an entire object, not an id

    ?>
    <div class="form-group">
      <?php if($o->field_type != 'timestamp' && $o->field_type != "hidden"){ ?>
        <label class="<?=$o->label_class?>" for="<?=$o->col?>">
          <?php
          if(str_starts_with($o->form_descrip,'(LANG)')) {
            echo lang(substr($o->form_descrip, 6));
            } else {
                echo $o->form_descrip;
            }

        if($o->required == 1){echo "*";}?>
        </label>
      <?php }

      $standard_types = [
        'text','password','passwordE','color','hidden'
      ];
      if(in_array($o->field_type,$standard_types)){

        $type = $o->field_type;
        if($o->field_type == 'passwordE'){$type = "password";}
        ?>
        <input type='<?=$type?>'
                name='<?=$o->col?>'
                id='<?=$o->col?>'
                class='<?=$o->field_class?>'
                value="<?php
                  if($u == 1){
                    echo $value;
                  }elseif(!empty($_POST)){
                    if(isset($_POST[$o->col])){
                      echo $_POST[$o->col];
                      }
                      }?>"
        <?php if($o->required == 1){echo "required";}?>
        <?=html_entity_decode($o->input_html)?>
        >
        <?php
      } //end if text

      if($o->field_type == "number" || $o->field_type == "tinyint"){
        ?>
        <input type="number" step="1" <?php if($o->field_type == "tinyint"){ echo "min='0' max='9'";}?> name='<?=$o->col?>' id='<?=$o->col?>' class='<?=$o->field_class?>'
        value="<?php if($u == 1){echo $value;}elseif(!empty($_POST)){echo $_POST[$o->col];}?>"
        <?php if($o->required == 1){echo "required";}?>
        <?=html_entity_decode($o->input_html)?>
        >
      <?php } //end if int
      if($o->field_type == "money"){
        ?>
        <input type="number" step=".01" name='<?=$o->col?>' id='<?=$o->col?>' class='<?=$o->field_class?>'
        value="<?php if($u == 1){echo $value;}elseif(!empty($_POST)){echo $_POST[$o->col];}?>"
        <?php if($o->required == 1){echo "required";}?>
        <?=html_entity_decode($o->input_html)?>
        >
      <?php } //end if int

      if($o->field_type == "time" ){
        ?>
        <input type="time" name='<?=$o->col?>' pattern="^([0-1]?[0-9]|2[0-4]):([0-5][0-9])(:[0-5][0-9])?$" id='<?=$o->col?>' placeholder="23:59 / 23:59:59" class='<?=$o->field_class?>'
        value="<?php if($u == 1){echo $value;}elseif(!empty($_POST)){echo $_POST[$o->col];}?>"
        <?php if($o->required == 1){echo "required";}?>
        <?=html_entity_decode($o->input_html)?>
        ><?php

      } //end if int

      if($o->field_type == "textarea") { ?>
        <textarea name='<?=$o->col?>' id='<?=$o->col?>' class='<?=$o->field_class?>'
          <?php if($o->required == 1){echo "required";}?>   <?=html_entity_decode($o->input_html)?>><?php if($u == 1){echo $value;}elseif(!empty($_POST)){echo $_POST[$o->col];}?></textarea>
        <?php } //end if textarea?>


        <?php if($o->field_type == "dropdown") {
          $options = parseFormPluginInputOptions($o->select_opts);

          ?>
          <select <?=html_entity_decode($o->input_html)?> name='<?=$o->col?>' id='<?=$o->col?>' class='<?=$o->field_class?>'
            <?php if($o->required == 1){echo "required";}?>>
            <?php


            if($u == 1){
              if($value == ''){ ?>
                <option disabled selected value>--Select One--</option>
              <?php }else{
                if (!is_object($options)) {
                    $option = $options;
                } else {
                    $option = get_object_vars($options);
                }

              }
              }
              foreach($options as $k=>$v){
                if($k == $value && $value != ''){ ?>
                  <option selected='selected' value="<?=$value?>"><?=$option[$value]?></option>
                <?php }else{  ?>
                  <option value="<?=$k?>">
                    <?php
                    if(str_starts_with($v,'(LANG)')) {
                      echo lang(substr($v, 6));
                    } else {
                      echo $v;
                    }
                    ?>
                  </option>
                <?php }
              }
              ?>
            </select>

          <?php  } //end if dropdown

          if($o->field_type == "date"){?>
            <input type="date" class="form-control" name="<?=$o->col?>" id="<?=$o->col?>" value="<?php if($u == 1){echo $value;}elseif(!empty($_POST)){echo $_POST[$o->col];}?>">
            <?php

          }
          if($o->field_type == "datetime"){?>
            <input type="datetime-local" class="form-control" name="<?=$o->col?>" id="<?=$o->col?>"
            value="<?php if($u == 1){echo date("Y-m-d\TH:i:s", strtotime($value));;}elseif(!empty($_POST)){echo
              date("Y-m-d\TH:i:s", strtotime($_POST[$o->col]));}?>">
            <?php
          }

          if($o->field_type == "checkbox"){
            $options = parseFormPluginInputOptions($o->select_opts);
            if($u == 1){
              $option = json_decode($value);
              if($option == ""){$option = [];}
            }
            foreach($options as $k=>$v){
              ?>
              <label class="<?=$o->field_class?>"><input type='checkbox'  <?php if($u == 1){
                if(in_array($k,$option)){ echo "checked='checked'";}} ?> name='<?=$o->col?>[]' value='<?=$k?>'
                <?php if($o->required == 1){echo "required";}?>
                <?=html_entity_decode($o->input_html)?>
                >
                <?php
                if(str_starts_with($v,'(LANG)')) {
                  echo lang(substr($v, 6));
                } else {
                  echo $v;
                }
                  ?>
              </label>
              <?php }
            } //end if checkbox

            if($o->field_type == "radio") {
              $options = parseFormPluginInputOptions($o->select_opts);
              foreach($options as $k=>$v){
                ?>
                <div class="radio">
                  <label><input type="radio" value="<?=$k?>" <?php if($u == 1){if($value == $k){echo "checked='checked'";}} ?> <?php echo $o->input_html;?> name='<?=$o->col?>'>
                    <?php
                    if(str_starts_with($v,'(LANG)')) {
                      echo lang(substr($v, 6));
                    } else {
                      echo $v;
                    }
                    ?>
                  </label>
                </div>
              <?php } //end radio
            }

            if($o->field_type == "timestamp") {
              //do nothing.
            }
            ?>

            <!-- final div -->
          </div>
          <?php

      } //end of function

      function displayForm($name, $opts = []){
        global $abs_us_root,$us_url_root;
        $db = DB::getInstance();
        $formatted = formatName($name);
        $u = 0;
        if(isset($opts['update'])){
          $id = $opts['update'];
          $q = $db->query("SELECT * FROM $name WHERE id = ?",array($id));
          $c = $q->count();
          if($c > 0){
            $u = 1;
            $v = $q->first();
          }else{
            die("Form record not found. Check your id");
          }
        }

        $o = $db->query("SELECT * FROM $formatted ORDER BY ord")->results();
        if(file_exists($abs_us_root.$us_url_root."usersc/plugins/forms/hooks/above_form/".$name.".php")){
          include $abs_us_root.$us_url_root."usersc/plugins/forms/hooks/above_form/".$name.".php";
        }
        ?>
        <form action="" method="post">
          <?php
          if(file_exists($abs_us_root.$us_url_root."usersc/plugins/forms/hooks/before_first_input/".$name.".php")){
            include $abs_us_root.$us_url_root."usersc/plugins/forms/hooks/before_first_input/".$name.".php";
          }
          if(!isset($opts['token'])){ ?>
            <input type="hidden" name="csrf" value="<?=Token::generate();?>" />
          <?php }else{ ?>
            <input type="hidden" name="csrf" value="<?=$opts['token'];?>" />
          <?php }
          foreach ($o as $f){
            if(isset($opts['skip'])){
              if(in_array($f->col,$opts['skip'])){
                continue;
              }
            }
            // dnd($f);
            if($u != 1){
              //note that formField expects an entire object, not an id
              formField($f);
            }else{
              $v->update = $id;

              formField($f,$v);
            }
          }
          ?>
          <input type="hidden" name="form_name" value="<?=$name?>">
          <?php
          if(file_exists($abs_us_root.$us_url_root."usersc/plugins/forms/hooks/after_last_input/".$name.".php")){
            include $abs_us_root.$us_url_root."usersc/plugins/forms/hooks/after_last_input/".$name.".php";
          }
          include('form_submit_button.php');
          if(!isset($opts['noclose'])){
            echo "</form>";
          }
          if(file_exists($abs_us_root.$us_url_root."usersc/plugins/forms/hooks/below_form/".$name.".php")){
            include $abs_us_root.$us_url_root."usersc/plugins/forms/hooks/below_form/".$name.".php";
          }

        }


        function displayView($view, $opts = []){
          global $abs_us_root,$us_url_root;
          $db = DB::getInstance();
          $getViewQ = $db->query("SELECT * FROM us_form_views WHERE id = ?",array($view));
          $getViewC = $getViewQ->count();
          if($getViewC < 1){
            bold("<br>View not found");
            exit;
          }else{
            $getView = $getViewQ->first();
          }

          $form = $getView->form_name.'_form';
          $fields = json_decode($getView->fields);
          $u = 0;
          if(isset($opts['update'])){
            $id = $opts['update'];
            $q = $db->query("SELECT * FROM $getView->form_name WHERE id = ?",array($id));
            $c = $q->count();
            if($c > 0){
              $u = 1;
              $v = $q->first();
            }else{
              die("Form record not found. Check your id");
            }
          }
          if(file_exists($abs_us_root.$us_url_root."usersc/plugins/forms/hooks/above_form/".$getView->form_name.".php")){
            include $abs_us_root.$us_url_root."usersc/plugins/forms/hooks/above_form/".$getView->form_name.".php";
          }
          ?>
          <form action="" method="post">
            <?php
            if(file_exists($abs_us_root.$us_url_root."usersc/plugins/forms/hooks/before_first_input/".$getView->form_name.".php")){
              include $abs_us_root.$us_url_root."usersc/plugins/forms/hooks/before_first_input/".$getView->form_name.".php";
            }
            if(!isset($opts['token'])){ ?>
              <input type="hidden" name="csrf" value="<?=Token::generate();?>" />
            <?php }else{ ?>
              <input type="hidden" name="csrf" value="<?=$opts['token'];?>" />
            <?php }
            foreach ($fields as $f){

              $fldQ = $db->query("SELECT * FROM $form WHERE id = ?",array($f));
              $fldC = $fldQ->count();
              if($fldC > 0){
                $fld = $fldQ->first();
                if($u != 1){
                  //note that formField expects an entire object, not an id
                  formField($fld);
                }else{
                  $v->update = $id;
                  formField($fld,$v);
                }
              }else{
                continue;
              }
            }
            ?>
            <input type="hidden" name="form_view" value="<?=$view?>">
            <input type="hidden" name="form_name" value="<?=$getView->form_name?>">
            <?php
            if(file_exists($abs_us_root.$us_url_root."usersc/plugins/forms/hooks/after_last_input/".$getView->form_name.".php")){
              include $abs_us_root.$us_url_root."usersc/plugins/forms/hooks/after_last_input/".$getView->form_name.".php";
            }
            include('form_submit_button.php'); ?>
          </form>

          <?php
          if(file_exists($abs_us_root.$us_url_root."usersc/plugins/forms/hooks/below_form/".$getView->form_name.".php")){
            include $abs_us_root.$us_url_root."usersc/plugins/forms/hooks/below_form/".$getView->form_name.".php";
          }
        }

        function displayTable($name,$opts = []){
          $db = DB::getInstance();
          //Pass id as 1 to show the id column

          if(!isset($opts['class'])){
            $opts['class'] = 'table table-striped';
          }

          if(!isset($opts['id'])){
            $opts['id'] = 0;
          }
          $form = $name.'_form';
          $s = $db->query("SELECT * FROM $form ORDER BY ord")->results();
          $order=[];
          if($opts['id'] == 1){
            $order['id'] = "id";
          }
          $newOrder = [];
          foreach($s as $key=>$value){
            $order[$value->col] = $value->table_descrip;
          }
          if(isset($opts['where'])){
            $table = $db->get($name,$opts['where']);
          }elseif(isset($opts['raw'])){
            $table = $db->query($opts['raw']);
          }else{
            $table = $db->query("SELECT * FROM $name");
          }

          $count = $table->count();
          ?>
          <!-- optional table class? -->
          <table class='<?=$opts['class']?>'>
            <thead>
              <?php
              foreach($order as $key=>$value){?>
                <th><?=$value?></th>
              <?php } ?>
            </thead>
            <tbody>
              <?php
              if($count > 0){

                $t = $table->results(true);
                foreach($t as $r){
                  // All of this is to get the table in the same order as your form
                  $r = array_intersect_key($r,$order);
                  $r = array_merge($order,$r);
                  ?>
                  <tr>
                    <?php foreach($r as $k=>$v){
                      if($k == 'id' && $opts['id'] != 1){
                        continue;
                      }elseif(isJSON($v)){
                        $v = json_decode($v);
                        $v = rtrim(implode(',', $v), ','); ?>
                        <td><?=$v?></td>
                        <?php
                      }elseif(isset($opts['html'])){
                        ?>
                        <td><?=htmlspecialchars_decode(stripslashes($v));?></td>
                        <?php
                      }else{?>
                        <td><?=$v?></td>
                      <?php }
                    }
                    ?>
                  </tr>
                  <?php
                }
              }
              ?>
            </tbody>
          </table>
          <?php
        }

        function displayTableRow($name,$row,$opts = []){
          $db = DB::getInstance();
          //Pass id as 1 to show the id column
          if(!isset($opts['class'])){
            $opts['class'] = 'table table-striped';
          }
          if(!isset($opts['id'])){
            $opts['id'] = 0;
          }
          $form = $name.'_form';
          $s = $db->query("SELECT * FROM $form WHERE id = ? ORDER BY ord LIMIT 1",array($row))->results();

          $order=[];
          $newOrder = [];
          foreach($s as $key=>$value){
            $order[$value->col] = $value->table_descrip;
          }
          $table = $db->query("SELECT * FROM $name");
          $count = $table->count();
          if($count > 0){
            $t = $table->results(true);
            // dnd($t);
          }
        }

        function preProcessForm($opts = []){
          global $abs_us_root;
          global $us_url_root;
          $response = array(
            'form_valid'=>false,
            'validation'=>false,
            'token'=>false,
          );
          $valData = [];
          //v2.1.1 & later. We don't want to send sanitized data to the validator because special characters etc throw off field lengths and other validations.
          //this feature will probably not work for API forms since they're pre-sanitized.
          //$formData is for the db
          //$valData is to validate -- do not ever store in the db

          if(isset($opts['apiEndpoint']) && isset($opts['apiData'])){
            $formData = $valData = $opts['apiData'];
            $formData['csrf'] = "";
          }else{
            $formData = $valData = $_POST;
          }


          $token = $formData['csrf'];
          if(!Token::check($token) && !isset($opts['apiEndpoint']) ){
            require_once $abs_us_root.$us_url_root.'usersc/scripts/token_error.php';
          }else{
            $response['token'] = true;
          }
          $validation = new Validate();
          $db = DB::getInstance();
          if(isset($formData['form_name'])){
            $name = Input::sanitize($formData['form_name']);
          }else{
            $name = false;
          }
          
          $fetchFormQ = $db->query("SELECT * FROM us_forms WHERE form = ?",[$name]);
          $fetchFormC = $fetchFormQ->count();
          if($fetchFormC < 1 || $name == false){
            $response['errors'] = "The requested form does not exist";
            return $response; die;
          }
          $form = $name.'_form';
          $fields = [];


          if(file_exists($abs_us_root.$us_url_root."usersc/plugins/forms/hooks/before_form_process/".$name.".php")){
            include $abs_us_root.$us_url_root."usersc/plugins/forms/hooks/before_form_process/".$name.".php";
          }
          $errors = [];
          $successes = [];
          $errorArray = [];
          $formView = Input::get('form_view');

          if(is_numeric($formView)){

            $checkQ = $db->query("SELECT * FROM us_form_views WHERE id = ? AND form_name = ?",[$formView,$name]);
            $checkC = $checkQ->count();
            if($checkC < 1){
              if($response['validation'] == true && $response['token'] == true){
                $response['form_valid'] = true;
              }
              $response['form_valid'] = true;
              $response['errors'][] = "This form view is invalid";
              return $response;
            }else{
              $check = $checkQ->first();
              $viewFields = json_decode($check->fields);
            }
          }
          $s = $db->query("SELECT * FROM $form")->results(true);
          foreach($s as $r){

            if(isset($viewFields) && !in_array($r['id'],$viewFields)){
              continue;
            }
            if($r['required'] == 1 && !isset($formData[$r['col']])){
              $errorArray[] = $r['table_descrip']." ".lang("GEN_REQ");
            }
          }
          //only deal with the fields that were actually posted
          $submitted = [];
          foreach($formData as $k=>$v){
            foreach($s as $t){
              if(array_search($k,$t)){
                $submitted[]= $t;
              }
            }
          }
          //check for posted arrays
          foreach($formData as $k=>$v){
            foreach($submitted as $t)
            if(is_array($k)){
            }
          }

          foreach($submitted as $c){

            $col = $c['col'];
            $val = [];
            if($c['field_type'] == "checkbox"){
              if(! isset($formData[$c['col']])){
                $data = [];
              }else{
                $data = filter_var_array($formData[$c['col']],FILTER_SANITIZE_ENCODED);
              }
              $data = json_encode($data);
              $fields[$c['col']] = $data;
            }elseif($c['field_type'] == "passwordE"){
              $fields[$c['col']] = password_hash(Input::sanitize($formData[$col]), PASSWORD_BCRYPT, array('cost' => 12));
            }elseif($c['field_type'] == "timestamp"){
              continue;
            }else{
              $fields[$c['col']] = Input::sanitize($formData[$col]);

              if($c['validation'] != "" && $c['validation'] != '[]'){

                $val = json_decode($c['validation']);
                $process = [];
                $process['display'] = $c['table_descrip'];
                foreach($val as $key => $value){
                  $process[$key] = $value;
                }

                $validation->check($valData,array(
                  $c['col'] => $process
                ));
                if($validation->passed()) {

                }else{
                  foreach($validation->errors() as $ve){
                    $errorArray[] = $ve;
                    //requires 5.3.8 or later
                    if(method_exists($validation,"rulesBroken")){
                      $response['rules_broken'] = $validation->rulesBroken();
                    }
                  }
                  if($opts != '' && isset($opts['debug'])){
                    dump($validation);
                  }
                }
              }
            }
          }

          if(!$errorArray==[]) {
            if(file_exists($abs_us_root.$us_url_root."usersc/plugins/forms/hooks/form_validation_fail/".$name.".php")){
              include $abs_us_root.$us_url_root."usersc/plugins/forms/hooks/form_validation_fail/".$name.".php";
            }
            display_errors($errorArray);
          }else{

            $response['validation']=true;
            if($opts != '' && isset($opts['debug'])){
              dnd($db->errorString());
            }
            if(file_exists($abs_us_root.$us_url_root."usersc/plugins/forms/hooks/form_validation_success/".$name.".php")){
              include $abs_us_root.$us_url_root."usersc/plugins/forms/hooks/form_validation_success/".$name.".php";
            }
          }
          $response['fields'] = $fields;
          $response['name'] = $name;
          if($response['validation'] == true && $response['token'] == true){
            $response['form_valid'] = true;
          }
          $response['errors'] = $errorArray;
          return $response;
        }

        function postProcessForm($response,$opts = []){
          global $usFormUpdate;
          global $abs_us_root;
          global $us_url_root;
          $db = DB::getInstance();
          if(isset($usFormUpdate)){
            $db->update($response['name'],$usFormUpdate,$response['fields']);

          }else{
            $db->insert($response['name'],$response['fields']);
          }
          $response['errors'] = $db->errorString();

          if(file_exists($abs_us_root.$us_url_root."usersc/plugins/forms/hooks/after_form_process/".$response['name'].".php")){
            include $abs_us_root.$us_url_root."usersc/plugins/forms/hooks/after_form_process/".$response['name'].".php";
          }

          return $response;
        }

        function processForm($opts = []){
          //form name is auto detected so we might want to prevent column names that match the form name
          global $usFormUpdate;
          $db = DB::getInstance();
          $response = preProcessForm();
          if($response['form_valid'] == true){
            //we are sending the info from the preprocess to the postprocess
            $response = postProcessForm($response);
          }
          return $response;
        }


        function createForm($name,$opts = []){
          global $us_url_root;
          $db = DB::getInstance();
          $form = $name.'_form';
          $check = checkFormName($name,$opts);

          if($check['success']==true){
            // echo 'Good to go';
            $columns = "id INT( 11 ) AUTO_INCREMENT PRIMARY KEY";
            $columns2 = "`id` INT( 11 ) AUTO_INCREMENT PRIMARY KEY,
            `ord` int(11) NOT NULL,
            `col` varchar(255) NOT NULL,
            `form_descrip` varchar(255) NOT NULL,
            `table_descrip` varchar(255) NOT NULL,
            `col_type` varchar(255) NOT NULL,
            `field_type` varchar(100) NOT NULL,
            `length` int(11) NOT NULL,
            `required` tinyint(1) NOT NULL,
            `validation` text NOT NULL,
            `label_class` varchar(255) NOT NULL,
            `field_class` varchar(255) NOT NULL,
            `input_html` text NOT NULL,
            `select_opts` text NOT NULL";
            $db->query("CREATE TABLE IF NOT EXISTS $name ( $columns )");
            $db->query("CREATE TABLE IF NOT EXISTS $form ( $columns2 )");
            $db->insert('us_forms',['form'=>$name]);
            $id = $db->lastId();
            usSuccess("Form created!");
            Redirect::to($us_url_root.'users/admin.php?view=plugins_config&plugin=forms&newFormView=_admin_forms_edit&edit='.$id);
          }else{ //failed name check
            usError($check['msg']);
            Redirect::to($us_url_root.'users/admin.php?view=plugins_config&plugin=forms&newFormView=_admin_forms');
            exit;
          }
        }



        function buildFormFromTable($name){
          $db = DB::getInstance();
          global $us_url_root;

          $pattern = '/[^a-zA-Z0-9-_]/';
          $name = preg_replace($pattern, '', $name);
 
          $order = 10;
          $form = $name.'_form';
          $check = checkFormName($name,['existing']);
         
          if($check['success']==true){
            $test = $db->query("SHOW TABLES LIKE ?",[$name])->count();

            //we want to make sure the requested table is really there
            if ($test < 1){
              usError("Sorry! The table you're requesting does not exist!");

              exit;
            }else{
              $count = $db->query("SELECT form FROM us_forms WHERE form = ?",array($name))->count();
         
              if($count < 1){
                $db->insert('us_forms',['form'=>$name]);
                $id = $db->lastId();
                $columns2 = "`id` INT( 11 ) AUTO_INCREMENT PRIMARY KEY,
                `ord` int(11) NOT NULL,
                `col` varchar(255) NOT NULL,
                `form_descrip` varchar(255) NOT NULL,
                `table_descrip` varchar(255) NOT NULL,
                `col_type` varchar(255) NOT NULL,
                `field_type` varchar(100) NOT NULL,
                `required` tinyint(1) NOT NULL,
                `validation` text NOT NULL,
                `label_class` varchar(255) NOT NULL,
                `field_class` varchar(255) NOT NULL,
                `input_html` text NOT NULL,
                `select_opts` text NOT NULL";
                $db->query("CREATE TABLE IF NOT EXISTS $form ( $columns2 )");
            
                $schema = $db->query("SHOW COLUMNS FROM $name")->results(true);
             
                foreach($schema as $s){

                  $type = '';
                  $field = '';
                  $t = $s['Type'];
                  if($s['Field'] == 'id'){
                    continue;
                  }else{
                    if(substr($t,0,3) == 'int'){
                      $type = "int";
                      $field = "number";
                    }elseif(substr($t,0,3) == 'var'){
                      $type = "varchar";
                      $field = "text";
                    }elseif(substr($t,0,3) == 'dat'){
                      if(substr($t,0,5) == 'datet'){
                        $type = "datetime";
                        $field = "datetime";
                      }else{
                        $type = "date";
                        $field = "date";
                      }
                    }elseif(substr($t,0,3) == 'tex'){
                      $type = "text";
                      $field = "textarea";
                    }elseif(substr($t,0,9) == 'timestamp'){
                      continue;
                    }
                  }
                  $fields = array(
                    'ord'=>$order,
                    'col'=>$s['Field'],
                    'form_descrip'=>ucfirst($s['Field']),
                    'table_descrip'=>ucfirst($s['Field']),
                    'col_type'=>$type,
                    'field_type'=>$field,
                    'field_class'=>'form-control',
                  );
                  $order = $order + 10;
                
                  $db->insert($form,$fields);
                }

              }else{
                bold("<br>Your us_forms table already has a form called ".$name);
                exit;
              }
            }
            Redirect::to($us_url_root.'users/admin.php?view=plugins_config&plugin=forms&newFormView=_admin_forms_edit&autogen=1&edit='.$id);
          }else{ //name check failed
            usError($check['msg']);
            Redirect::to($us_url_root.'users/admin.php?view=plugins_config&plugin=forms&newFormView=_admin_forms');
            exit;
          }


        }


        function formatName($name){
          $post = "_form";
          $formatted = $name."_form";
          return $formatted;
        }

        function getFormName($id,$opt=[]){
          $db = DB::getInstance();
          $q = $db->query("SELECT form FROM us_forms WHERE id = ?",array($id));
          $c = $q->count();
          if($c > 0){
            $f = $q->first();
            $name = $f->form;
            // dnd($opt);
            if($opt != [] && $opt['name'] == 1){
              $name = $f->form."_form";
            }
            return $name;
          }else{
            $msg = "not found";
            return $msg;
          }
        }

        function isSqlProtected($col){
          $protected = ['accessible','add','all','alter','analyze','and','as','asc','asensitive','before','between','bigint','binary','blob','both','by','call','cascade','case','change','char','character','check','collate','column','condition','constraint','continue','convert','create','cross','current_date','current_time','current_timestamp','current_user','cursor','database','databases','day_hour','day_microsecond','day_minute','day_second','dec','decimal','declare','default','delayed','delete','desc','describe','deterministic','distinct','distinctrow','div','double','drop','dual','each','else','elseif','enclosed','escaped','exists','exit','explain','false','fetch','float','float4','float8','for','force','foreign','from','fulltext','general','grant','group','having','high_priority','hour_microsecond','hour_minute','hour_second','if','ignore','ignore_server_ids','in','index','infile','inner','inout','insensitive','insert','int','int1','int2','int3','int4','int8','integer','interval','into','is','iterate','join','key','keys','kill','leading','leave','left','like','limit','linear','lines','load','localtime','localtimestamp','lock','long','longblob','longtext','loop','low_priority','master_heartbeat_period','master_ssl_verify_server_cert','match','maxvalue','mediumblob','mediumint','mediumtext','middleint','minute_microsecond','minute_second','mod','modifies','natural','not','no_write_to_binlog','null','numeric','on','optimize','option','optionally','or','order','out','outer','outfile','partition','precision','primary','procedure','purge','range','read','reads','read_write','real','recursive','references','regexp','release','rename','repeat','replace','require','resignal','restrict','return','revoke','right','rlike','rows','schema','schemas','second_microsecond','select','sensitive','separator','set','show','signal','slow','smallint','spatial','specific','sql','sqlexception','sqlstate','sqlwarning','sql_big_result','sql_calc_found_rows','sql_small_result','ssl','starting','straight_join','table','terminated','then','tinyblob','tinyint','tinytext','to','trailing','trigger','true','undo','union','unique','unlock','unsigned','update','usage','use','using','utc_date','utc_time','utc_timestamp','values','varbinary','varchar','varcharacter','varying','when','where','while','window','with','write','xor','year_month','zerofill'];
          $col = strtolower($col);
          if(in_array($col,$protected)){
            return true;
          }else{
            return false;
          }
        }

        function isValidValidation($opt){
          //since we cannot sanitize < symbols etc, we need to make sure that the posted values
          //are in the db table to prevent injections
          $db = DB::getInstance();
          $c = $db->query("SELECT value FROM us_form_validation WHERE value = ?",array($opt))->count();
          if($c > 0){
            return true;
          }else{
            return false;
          }
        }

        function formDataExport($form){
          $db = DB::getInstance();
          $name = $form.'_form';
          $s = $db->query("SELECT col,table_descrip FROM $name")->results();
          $order=['id'];
          foreach($s as $key=>$value){
            $order[$value->col] = $value->table_descrip;
          }

          // output headers so that the file is downloaded rather than displayed
          // header('Content-Type: text/csv; charset=utf-8');
          // header('Content-Disposition: attachment; filename='.$form.'.csv');
          $output = fopen($form.'.csv', 'w');

          // output the column headings
          fputcsv($output, $order);

          $rows = $db->query("SELECT * FROM $form")->results(true);
          // loop over the rows, outputting them
          foreach($rows as $row){
            fputcsv($output, $row);
          }?>
          <a href="<?=$form?>.csv">Download CSV</a>
          <?php
        }

        function duplicateForm($new,$old){
          $db = DB::getInstance();
          global $us_url_root;
          $check = checkFormName($new);
          if($check['success'] == true){
            $db->insert('us_forms',['form'=>$new]);
            $id = $db->lastId();
            $query = $db->query("CREATE TABLE $new LIKE $old");
            $new = $new."_form";
            $old = $old."_form";

            $query = $db->query("CREATE TABLE $new LIKE $old");
            $copy = $db->query("SELECT * FROM $old")->results(true);
            foreach($copy as $c){
              $db->insert($new,$c);
            }
            usSuccess("Form duplicated!");
            Redirect::to($us_url_root.'users/admin.php?view=plugins_config&plugin=forms&newFormView=_admin_forms_edit&edit='.$id);
          }else{//name check failed
            usError($check['msg']);
            Redirect::to($us_url_root.'users/admin.php?view=plugins_config&plugin=forms&newFormView=_admin_forms');
            exit;
          }
        }

        function getValidTables(){
          //get a list of tables that don't end in _form
          $db = DB::getInstance();
          $query = $db->query("SHOW TABLES")->results();
          $tables = [];
          foreach($query as $t){
            foreach($t as $q){
              $tables[] = $q;
            }
          }
          foreach($tables as $k=>$v){
            if(substr($v,-5)=='_form'){
              unset($tables[$k]);
            }
          }
          //check if there's already a form.
          //if yes, unset it
          $query = $db->query("SELECT form FROM us_forms")->results();
          foreach($query as $k=>$v){
            foreach($tables as $key=>$value){
              if($v->form == $value){
                unset($tables[$key]);
              }
            }
          }
          return $tables;
        }

        function checkFormName($name,$opts = []){
          //run this check before creating a new form. Checks for conflicts in the db.
          //if you are building from an existing db table, pass in the word
          //['existing'] in opts
          $db = DB::getInstance();
          $msg = [];
          $msg['success'] = false;
          $check = $db->query("SELECT id FROM us_forms WHERE form = ?",[$name])->count();
          if($check > 0){
            $msg['msg'] = "Sorry. A form with that name already exists";
            return $msg;
            exit;
          }

          if (!preg_match("#^[a-z0-9_]+$#", $name)) {
            $msg['msg'] = "Sorry! You can only use lowercase letters and numbers in your form name!";
            return $msg;
            exit;
          }

          //if you are building a form from an existing db table, you want to skip this
          //check because you NEED an existing table here.

          if(!in_array('existing',$opts)){
            $test = $db->query("SELECT * FROM $name")->first();

            $e = $db->error();
            if ($e == false){
              $msg['msg'] = "Sorry! A table with that name exists in your database!";
              return $msg;
              exit;
            }
          }//end existing skip
          $name = $name."_form";
          $test = $db->query("SELECT * FROM $name")->first();
          $e = $db->errorString();
          if (strpos($e, 'ERROR #0 ') !== false && strpos($e, 'ERROR #0') !== false){
            $msg['msg'] = "Sorry! It looks like you used to have a form by that name that was never fully deleted!";
            return $msg;
            exit;
          }
          $msg['success']=true;
          return $msg;
        }


        function isJSON($string){
          return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
        }

        function displaySingleItem($row,$col,$form,$opts = []){
          if(!isset($opts['skip'])){$opts['skip'] = ['id'];}
          $db = DB::getInstance();
          $name = $form."_form";
          if(isset($opts['long'])){
            $selector = "form_descrip";
          }else{
            $selector = "table_descrip";
          }
          if($col != 'id' && !in_array($col,$opts['skip'])){
            $f = $db->query("SELECT $selector FROM $name WHERE col = ?",array($col))->first();
            $v = $db->query("SELECT $col FROM $form WHERE id = ?",array($row))->first();
            echo "<strong>".$f->$selector.": </strong><font color='blue'>".$v->$col."<br></font>";
          }
        }

        function deleteForm($name,$opts = []){
          $db = DB::getInstance();
          $db->query("DELETE FROM us_forms WHERE form = ?",(array($name)));
          $formatted = formatName($name);
          $db->query("DROP TABLE IF EXISTS `$formatted`");
          if($opts['deleteTable'] == "YES"){
            $db->query("DROP TABLE IF EXISTS `$name`");
          }
        }

        function parseFormPluginInputOptions($options){
          global $db, $user;

          $options = json_decode($options);

          //if this is set, we're grabbing opts from a db query
          if(isset($options->usformquery) && $options->usformquery != ""){
            $dbOptions = new stdClass();
            //run the raw query and loop it
            $options->usformquery = str_replace("{{{user_id}}}", $user->data()->id, $options->usformquery);

            $q = $db->query($options->usformquery)->results();

            foreach($q as $v){
              //since the key is stored as a string, we need to get the actual key which
              //will be the 'value' on the form input. We need the data, not the string.
              //This should be refactored, but it's functional.
              $key = $options->key;
                      $key = $v->$key;


              //create a blank string to be the form input visible value
              $value = "";

              foreach($options->values as $primary){

                //visible values can be either strings or db columns.  Build the string accordingly
                foreach($primary as $vkey=>$vvalue)

                if($vkey == "col" || $vkey == "DB Column"){
                  if($value == ""){
                    $value .= $v->$vvalue;
                  }else{
                    $value .= " ".$v->$vvalue;
                  }

                }elseif($vkey == "str"){
                  if($value == ""){
                    $value .= $vvalue;
                  }else{
                    $value .= " ".$vvalue;
                  }
                }

                //assign this key value pair to the temporary $dbOptions variable
                $dbOptions->$key = $value;
              }
            }
            //replace the original $options variable with the db generated one
            $options = $dbOptions;
          }

          if($options == ""){
            return $options = new stdClass();
          }
          return $options;
        }

        if(!function_exists("permInput")){
          function permInput($input,$required=true){
            if(!is_array($input)){
              return false;
            }
            //if required, there needs to be at least 1 perm
            if(count($input) < 1 && $required){
              return false;
            }
            foreach($input as $i){
              if(!is_numeric($i)){
                //if anything other than a number is passed, fail the whole dang thing
                return false;
              }
            }

            if(count($input) < 1){
              return "";
            }else{
              return implode(',', $input);
            }
          }
        }

if(!function_exists("str_starts_with")){
  function str_starts_with ( $haystack, $needle ) {
      return strpos( $haystack , $needle ) === 0;
  }
}
