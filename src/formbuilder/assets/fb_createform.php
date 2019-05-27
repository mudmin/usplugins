<?php

class fb_createform {

    public
    $database,
    $databasevalue = NULL,
    $_error = [];

    private
    $_db    = null;

    public function __construct()  {
        $this->_db = DB::getInstance();
    }

    public function create(){
        require_once 'fb_validate.php';
        $fb_validate = NEW fb_validate;
        if(preg_match('/[^a-z0-9_]/',Input::get('name'))){
            $fb_validate->addError(Input::get('name')." Error: Only Lower Case, Numbers, and Underscore");
        }
        $name_check = $this->_db->query('SELECT NULL FROM '.$this->database.'_fb_fields WHERE name = ?',[Input::get('name')])->count();
        if($name_check > 0 && !isset($_GET['id'])){
            $fb_validate->addError(Input::get('name')." already exist!");
        }
        if(isset($_POST['database_name'])){
            if(preg_match('/[^a-z0-9_]/',Input::get('database_name'))){
                $fb_validate->addError("Database Name: Only Lower Case, Numbers and Underscore only");
            }
        }
        if(isset($_post['database_type'])){
            if ($_post['database_type'] == 'manual'){
                $post = array(
                    'fb_order' => Input::get('fb_order'),
                    'name' => Input::get('name'),
                    'field_type' => Input::get('field_type'),
                    'databaseid' => Input::get('databaseid'),
                    'databasevalue' => Input::get('databasevalue'),
                );
                $fb_validate->check($_POST, array(
                    'fb_order' => array(
                        'display'           => 'fb_order',
                        'min'               => '1',
                        'is_numeric'        => true,
                    ),
                    'name' => array(
                        'display'           => 'Name',
                        'required'          => true,
                        'min'               => '1',
                        'max'               => '50'
                    ),
                    'field_type' => array(
                        'display'           => 'Field Type',
                        'required'          => true,
                    ),
                    'databaseid' => array(
                        'display'           => 'Database ID',
                        'required'          => true,
                    ),
                    'databasevalue' => array(
                        'display'           => 'Database Value',
                        'required'          => true,
                    ),
                ));
            }
        } elseif (isset($_post['database_type'])){
            if ($_post['database_type'] == 'database'){
                $post = array(
                    'fb_order' => Input::get('fb_order'),
                    'name' => Input::get('name'),
                    'field_type' => Input::get('field_type'),
                    'database_name' => Input::get('database_name'),
                    'database_value' => Input::get('database_value'),
                );
                $fb_validate->check($_POST, array(
                    'fb_order' => array(
                        'display'           => 'fb_order',
                        'min'               => '1',
                        'is_numeric'        => true,
                    ),
                    'name' => array(
                        'display'           => 'Name',
                        'required'          => true,
                        'min'               => '1',
                        'max'               => '50'
                    ),
                    'field_type' => array(
                        'display'           => 'Field Type',
                        'required'          => true,
                    ),
                    'database_name' => array(
                        'display'           => 'Database Name',
                        'required'          => true,
                    ),
                    'database_value' => array(
                        'display'           => 'Database Value',
                        'required'          => true,
                    ),

                ));
            }
        } else {
            $fb_validate->check($_POST, array(
                'fb_order' => array(
                    'display'           => 'fb_order',
                    'min'               => '1',
                    'is_numeric'        => true,
                ),
                'name' => array(
                    'display'           => 'Name',
                    'required'          => true,
                    'min'               => '1',
                    'max'               => '50'
                ),
                'field_type' => array(
                    'display'           => 'Field Type',
                    'required'          => true,
                )
            ));
        }



        if($fb_validate->passed()){
            $this->design_sql();
            if (empty($this->_error)){
                if(isset($_GET['id'])){
                    $id = Input::get('id');
                    if(is_numeric($id)){
                        $this->_db->update($this->database."_fb_fields",$id,$this->insert_query());
                    }
                } else {
                    $this->_db->insert($this->database."_fb_fields",$this->insert_query());
                }
                return "Created";
            }
        } else {
            $this->_error += $fb_validate->errors();
        }
    }

    private function insert_query(){
        $insert_query = array(
            'fb_order'        => Input::get('fb_order'),
            'name'            => Input::get('name'),
            'field_type'      => Input::get('field_type'),
            'field_html'      => json_encode($this->field_design()),
            'requirements'    => json_encode($this->requirement_insert()),
            );
        if (isset($_POST['database_type'])){
            if ($_POST['database_type'] == 'manual'){
                $insert_query += [
                        'databasevalue'     => json_encode($this->database_design()),
                        'database_name'     => '',
                        'database_value'    => '',
                        'database_where'    => ''
                    ];
            } elseif ($_POST['database_type'] == 'database'){
                $insert_query += [
                        'databasevalue'     => '',
                        'database_name'     => Input::get('database_name'),
                        'database_value'    => Input::get('database_value'),
                        'database_where'    => Input::get('database_where'),
                    ];
            }
        } else {
            $insert_query += [
                'databasevalue'     => '',
                'database_name'     => '',
                'database_value'    => '',
                'database_where'    => ''
            ];
        }

        return $insert_query;
    }

    private function field_design(){
        if(isset($_POST['div_class1'])){
            $div_class1 = Input::get('div_class1');
        }else{
            $div_class1 = "";
        }
        if($_POST['field_type'] == 'hidden' || $_POST['field_type'] == 'hidden_timestamp'){
            $insert_query = array(
                'div_class1'      => '',
                'div_class2'      => '',
                'label'           => '',
                'label_class'     => '',
                'input_class'     => '',
                'input_html'      => '',
                'required'        => '',
            );
        } else {
            $insert_query = array(
                'div_class1'      => $div_class1,
                'div_class2'      => Input::get('div_class2'),
                'label'           => Input::get('label'),
                'label_class'     => Input::get('label_class'),
                'input_class'     => Input::get('input_class'),
                'input_html'      => Input::get('input_html'),
                'required'        => Input::get('required'),
            );
        }
        if ($_POST['field_type'] == 'number'){
            $insert_query += ['input_step' => Input::get('input_step')];
        } else {
            $insert_query += ['input_step' => ''];
        }

        return $insert_query;
    }

    private function database_design(){
        $insert_query = array();
        $database_ids = Input::get('databaseid');
        $database_name = Input::get('databasevalue');

        foreach ($database_ids as $row => $database_id) {
            $insert_query[] = array('id' => $database_id, 'value' => $database_name[$row]);
        }
        $this->databasevalue = $insert_query;
        return $insert_query;
    }

    private function requirement_insert(){
        $insert_query = array();

        $insert_query += ['display' => Input::get('label')];

        $required_min_check = Input::get('required_min_check');
        if ($required_min_check == "ON"){
            $insert_query += ['min' => Input::get('required_min_value')];
        }

        $required_max_check = Input::get('required_max_check');
        if ($required_max_check == "ON"){
            $insert_query += ['max' => Input::get('required_max_value')];
        }

        $required_is_numeric_check = Input::get('required_is_numeric_check');
        if ($required_is_numeric_check == "ON"){
            $insert_query += ['is_numeric' => true];
        }

        $required_valid_email_check = Input::get('required_valid_email_check');
        if ($required_valid_email_check == "ON"){
            $insert_query += ['valid_email' => true];
        }

        $required_greaterthan_check = Input::get('required_greaterthan_check');
        if ($required_greaterthan_check == "ON"){
            $insert_query += ['greaterthan' => Input::get('required_greaterthan_value')];
        }

        $required_lessthan_check = Input::get('required_lessthan_check');
        if ($required_lessthan_check == "ON"){
            $insert_query += ['lessthan' => Input::get('required_lessthan_value')];
        }

        $required_greaterthanequal_check = Input::get('required_greaterthanequal_check');
        if ($required_greaterthanequal_check == "ON"){
            $insert_query += ['greaterthanequal' => Input::get('required_greaterthanequal_value')];
        }

        $required_lessthanequal_check = Input::get('required_lessthanequal_check');
        if ($required_lessthanequal_check == "ON"){
            $insert_query += ['lessthanequal' => Input::get('required_lessthanequal_value')];
        }

        $required_notequal_check = Input::get('required_notequal_check');
        if ($required_notequal_check == "ON"){
            $insert_query += ['notequal' => Input::get('required_notequal_value')];
        }

        $required_equal_check = Input::get('required_equal_check');
        if ($required_equal_check == "ON"){
            $insert_query += ['equal' => Input::get('required_equal_value')];
        }

        $required_is_integer_check = Input::get('required_is_integer_check');
        if ($required_is_integer_check == "ON"){
            $insert_query += ['is_integer' => true];
        }

        $required_is_timezone_check = Input::get('required_is_timezone_check');
        if ($required_is_timezone_check == "ON"){
            $insert_query += ['is_timezone' => true];
        }

        $required_is_datetime_check = Input::get('required_is_datetime_check');
        if ($required_is_datetime_check == "ON"){
            $insert_query += ['is_datetime' => true];
        }
        
        $is_valid_north_american_phone_check = Input::get('is_valid_north_american_phone_check');
        if ($is_valid_north_american_phone_check == "ON"){
            $insert_query += ['is_valid_north_american_phone' => true];
        }

        $required = Input::get('required');
        if ($required == "1"){
            $insert_query += ['required' => true];
        }

        return $insert_query;
    }

    private function design_sql(){
    $field_type = Input::get('field_type');

    if($field_type == "text"){
        $columns = Input::get('name')." varchar(255) NULL";
    }elseif($field_type == "password" || $field_type == "e_passworde"){
        $columns = Input::get('name')." varchar(255) NULL";
    }elseif($field_type == "number"){
        $n = strlen(substr(strrchr(Input::get('input_step'), "."), 1));
        $columns = Input::get('name')." DECIMAL(50,".$n.") NULL";
    }elseif($field_type == "time"){
        $columns = Input::get('name')." TIME NULL";
    }elseif($field_type == "date"){
        $columns = Input::get('name')." DATE NULL";
    }elseif($field_type == "datetime"){
        $columns = Input::get('name')." DATETIME NULL";
    }elseif($field_type == "textarea"){
        $columns = Input::get('name')." TEXT NULL";
    }elseif($field_type == "tel"){
        $columns = Input::get('name')." varchar(12) NULL";
    }elseif($field_type == "dropdown"){
        $columns = Input::get('name')." varchar(255) NULL";
    }elseif($field_type == "checkbox"){
        $columns = Input::get('name')." varchar(255) NULL";
    }elseif($field_type == "radio"){
        $columns = Input::get('name')." varchar(255) NULL";
    }elseif($field_type == "file"){
        $columns = Input::get('name')." varchar(255) NULL";
    }elseif($field_type == "hidden"){
        $columns = Input::get('name')." varchar(255) NULL";
    }elseif($field_type == "hidden_timestamp"){
        $columns = Input::get('name')." TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP";
    }else{
        $columns = Input::get('name')." varchar(255) NULL";
    }
    $table_schema = $GLOBALS['config']['mysql']['db'];
    $table_name = $this->database;
    $database_name = $this->database."_fb_fields";
    $database_column = Input::get('name');
    $check = $this->_db->query("SELECT NULL FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = ? AND table_schema = ? AND column_name = ?",[$table_name,$table_schema,$database_column])->count();
    $check2 = $this->_db->query("SELECT NULL FROM $database_name WHERE name = ?",[$database_column])->count();
    if($check == 0 && $check2 == 0){
        $this->_db->query("ALTER TABLE $this->database ADD $columns");
    }else{
        if(!isset($_GET['id'])){
            return $this->_error +="Error: ".Input::get('name')." already exist!";
        }
    }
    }

}

if (isset($_POST['database_submit'])){
    require_once 'fb_createdatabase.php';
} elseif (isset($_POST['csrf'])){
    $token = $_POST['csrf'];
    if(!Token::check($token)){
        include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
    }

    $createform = new fb_createform();
    $createform->database = Input::get('database');
    $createform->create();
    
    $db = DB::getInstance();
    $count = $db->findById(1,'fb_settings')->count();
    if($count == 0){
        $submit_new = $db->submit_new;
        if($submit_new == 1){
            redirect::to($us_url_root.'usersc/plugins/formbuilder/index.php');
        } else {
            if(isset($_GET['id'])){
                $basename = basename($_SERVER['REQUEST_URI']);
                $parts = parse_url($basename);
                $queryParams = array();
                parse_str($parts['query'], $queryParams);
                unset($queryParams['id']);
                $queryString = http_build_query($queryParams);
                $url = $parts['path'] . '?' . $queryString;
                redirect::to($url);
            } else {
                redirect::to($_SERVER['REQUEST_URI']);
            }
        }
    }

}

if (isset($_POST['name']) && isset($_GET['database'])){

    $database = Input::get('database');

    $fb_order = Input::get('fb_order');
    $name = Input::get('name');
    $field_type = Input::get('field_type');

    if(isset($_POST['div_class1'])){
        $div_class1 = Input::get('div_class1');
        $div_number = 2;
    } else {
        $div_class1 = "";
    }
    
    $div_class2 = Input::get('div_class2');
    $label = Input::get('label');
    $label_class = Input::get('label_class');
    $input_class = Input::get('input_class');
    $input_html = Input::get('input_html');
    $required = Input::get('required');
    $input_step = Input::get('input_step');

    if(isset($_POST['database_type'])){
    $database_type = Input::get('database_type');
        if($database_type == "manual"){
            $databasetype = "manual";
            $databasevalue_Content = json_decode($databasevalue,true);
            $databaseids = $databasevalue_Content->databaseids;
            $databasevalue = $databasevalue_Content->databasevalue;
        }elseif($database_type == 'database'){
            if(!empty($_POST['database_where'])){
                $databasetype = "database";
                $database_name = Input::get('database_name');
                $database_value = Input::get('database_value');
                $database_where = Input::get('database_where');
            } else {
                $databasetype = "database";
                $database_name = Input::get('database_name');
                $database_value = Input::get('database_value');
            }
        }
    }
    
    $databasevalue = $createform->databasevalue;
    if(!empty($databasevalue)){

    }elseif($databasevalue == "manual"){
        $databasevalue = "manual";
        $databasevalue_Content = json_decode($databasevalue,true);
        $databaseids = $databasevalue_Content->databaseids;
        $databasevalue = $databasevalue_Content->databasevalue;
    }elseif(isset($field_html->database_name) && isset($field_html->database_value) && isset($field_html->database_where)) {
        $databasevalue = "database";
        $database_name = Input::get('database_name');
        $database_value = Input::get('database_value');
        $database_where = Input::get('database_where');
    }elseif(isset($field_html->database_name) && isset($field_html->database_value)) {
        $databasevalue = "database";
        $database_name = Input::get('database_name');
        $database_value = Input::get('database_value');
    }

    $required_min_check = Input::get('required_min_check');
    $required_min_value = Input::get('required_min_value');

    $required_max_check = Input::get('required_max_check');
    $required_max_value = Input::get('required_max_value');

    $required_is_numeric_check = Input::get('required_is_numeric_check');

    $required_valid_email_check = Input::get('required_valid_email_check');

    $required_greaterthan_check = Input::get('required_greaterthan_check');
    $required_greaterthan_value = Input::get('required_greaterthan_value');

    $required_lessthan_check = Input::get('required_lessthan_check');
    $required_lessthan_value = Input::get('required_lessthan_value');

    $required_greaterthanequal_check = Input::get('required_greaterthanequal_check');
    $required_greaterthanequal_value = Input::get('required_greaterthanequal_value');

    $required_lessthanequal_check = Input::get('required_lessthanequal_check');
    $required_lessthanequal_value = Input::get('required_lessthanequal_value');

    $required_notequal_check = Input::get('required_notequal_check');
    $required_notequal_value = Input::get('required_notequal_value');

    $required_equal_check = Input::get('required_equal_check');
    $required_equal_value = Input::get('required_equal_value');

    $required_is_integer_check = Input::get('required_is_integer_check');

    $required_is_timezone_check = Input::get('required_is_timezone_check');

    $required_is_datetime_check = Input::get('required_is_datetime_check');
    
    $is_valid_north_american_phone_check = Input::get('is_valid_north_american_phone_check');

} elseif (isset($_GET['database'])){
    $database = Input::get('database');
    $db = DB::getInstance();
    $count = $db->query("SELECT form FROM fb_formbuilder WHERE form = ?",[$database])->count();
    if($count > 0){
        if(isset($_GET['id'])){
            $database_fb_fields = Input::get('database').'_fb_fields';
            $db = $db->query("SELECT * FROM $database_fb_fields WHERE id = ?",[Input::get('id')]);
            $count = $db->count();
            if($count > 0){
                $result = $db->first();
                $fb_order = $result->fb_order;
                $name = $result->name;
                $field_type = $result->field_type;
                $field_html = json_decode($result->field_html);

                if(isset($field_html->div_class1)){
                    if(!empty($field_html->div_class1)){
                        $div_class1 = $field_html->div_class1;
                        $div_number = 2;
                    }else{
                        $div_class1 = "";
                        $div_number = 1;
                    }
                }else{
                    $div_class1 = "";
                    $div_number = 1;
                }
                
                $div_class2 = $field_html->div_class2;
                $label = $field_html->label;
                $label_class = $field_html->label_class;
                $input_class = $field_html->input_class;
                $input_html = $field_html->input_html;
                $required = $field_html->required;
                $input_step = $field_html->input_step;

                if($result->field_type == 'dropdown'){
                    if(!empty($result->databasevalue)){
                        $databasetype = "manual";
                        $database_design = json_decode($result->databasevalue);
                    }elseif(!empty($result->database_where)){
                        $databasetype = "database";
                        $database_name = $result->database_name;
                        $database_value = $result->database_value;
                        $database_where = html_entity_decode($result->database_where, ENT_QUOTES);
                    }elseif(!empty($result->database_name) && !empty($result->database_value)){
                        $databasetype = "database";
                        $database_name = $result->database_name;
                        $database_value = $result->database_value;
                    }
                }

                $requirements = json_decode($result->requirements);

                if(isset($requirements->min)){
                    $required_min_check = 'ON';
                    $required_min_value = $requirements->min;
                }

                if(isset($requirements->max)){
                    $required_max_check = 'ON';
                    $required_max_value = $requirements->max;
                }

                if(isset($requirements->is_numeric)){
                    $required_is_numeric_check = 'ON';
                }

                if(isset($requirements->valid_email)){
                    $required_valid_email_check = 'ON';
                }

                if(isset($requirements->greaterthan)){
                    $required_greaterthan_check = 'ON';
                    $required_greaterthan_value = $requirements->greaterthan;
                }

                if(isset($requirements->lessthan)){
                    $required_lessthan_check = 'ON';
                    $required_lessthan_value = $requirements->lessthan;
                }

                if(isset($requirements->greaterthanequal)){
                    $required_greaterthanequal_check = 'ON';
                    $required_greaterthanequal_value = $requirements->greaterthanequal;
                }

                if(isset($requirements->lessthanequal)){
                    $required_lessthanequal_check = 'ON';
                    $required_lessthanequal_value = $requirements->lessthanequal;
                }

                if(isset($requirements->notequal)){
                    $required_notequal_check = 'ON';
                    $required_notequal_value = $requirements->notequal;
                }

                if(isset($requirements->equal)){
                    $required_equal_check = 'ON';
                    $required_equal_value = $requirements->equal;
                }

                if(isset($requirements->is_integer)){
                    $required_is_integer_check = 'ON';
                }

                if(isset($requirements->is_timezone)){
                    $required_is_timezone_check = 'ON';
                }
                
                if(isset($requirements->is_valid_north_american_phone)){
                    $is_valid_north_american_phone_check = 'ON';
                }
            }
        }
    } else {
        echo $database." does not exist";
        die();
    }
} elseif(isset($_GET['div_number']) && isset($_GET['div_value'])) {
    require_once '../../../../users/init.php';
    $div_number = Input::get('div_number');
    $div_value = Input::get('div_value');
    if(is_numeric($div_number)){
        if($div_number == "2"){ ?>
        <div class="form-group">
            <label for="label">div Class 1:</label>
            <input type="text" class="form-control" name="div_class1" id="div_class1" value="form-row" required />
        </div>
        <div class="form-group">
            <label for="label">div Class 2:</label>
            <input type="text" class="form-control" name="div_class2" id="div_class2" <?php if(isset($div_value)){echo 'value="'.$div_value.'"';}else{'value="form-group"';}?> required />
        </div>
        <?php } else { ?>
        <div class="form-group">
            <label for="label">div Class:</label>
            <input type="text" class="form-control" name="div_class2" id="div_class2" <?php if(isset($div_value)){echo 'value="'.$div_value.'"';}else{'value="form-group"';}?> required />
        </div>
        <?php }        
    }
} elseif(isset($_GET['type'])) {
    require_once '../../../../users/init.php';
    $db = DB::getInstance();
    $check = $db->findById(1,"fb_settings")->count();
    if($check = 1){
        $result = $db->first();
    }

    $type = $_GET["type"];
    if($type == 'hidden' || $type == 'hidden_timestamp'){
    }else{
        ?>
        <div class="form-group">
            <label for="label">Number of Div's?</label>
            <select name="div_number" class="form-control" onchange="js_div_number(this.value)">
                <option value="1">1</option>
                <option value="2">2</option>
            </select>
        </div>
        <div id="div_number">
            <div class="form-group">
                <label for="label">div Class:</label>
                <input type="text" class="form-control" name="div_class2" id="div_class2" value="form-group" required />
            </div>
        </div>
        <div class="form-group">
            <label for="label">Label:</label>
            <input type="text" class="form-control" name="label" id="label" required />
        </div>
        <div class="form-group">
            <label for="label">Label Class:</label>
            <input type="text" class="form-control" name="label_class" id="label_class" value="<?php if(isset($result->label_class)){echo $result->label_class;}else{ echo 'form-group';}?>" required />
        </div>
        <div class="form-group">
            <label for="label">Input Class:</label>
            <input type="text" class="form-control" name="input_class" id="input_class" value="<?php if(isset($result->input_class)){echo $result->input_class;}else{ echo 'form-control';}?>" required />
        </div>
        <?php
        if($type == 'text' || $type == 'password' || $type == 'e_password' || $type == 'time' || $type == 'date' || $type == 'datetime' || $type == 'tel' || $type == 'textarea'){
            $type;
            ?>
            <div class="form-group">
                <label for="label">Type HTML:</label>
                <input type="text" class="form-control" name="input_html" id="input_html" value="<?php if(isset($result->type_html)){echo $result->type_html;}?>" />
            </div>
            <div class="form-group">
                <label for="label">Type Required:</label>
                <select name="required" class="form-control" onchange="form(this.value)">
                    <option value="" selected>No</option>
                    <option value="1">Yes</option>
                </select>
            </div>
            <?php
        } elseif ($type == 'number') {
            ?>
            <div class="form-group">
                <label for="label">Type Steps:</label>
                <input type="number" class="form-control" name="input_step" id="input_step" value="" step="0.000001" />
            </div>
            <div class="form-group">
                <label for="label">Type HTML:</label>
                <input type="text" class="form-control" name="input_html" id="input_html" value="<?php if(isset($result->type_html)){echo $result->type_html;}?>" />
            </div>
            <div class="form-group">
                <label for="label">Type Required:</label>
                <select name="required" class="form-control" onchange="form(this.value)">
                    <option value="" selected>No</option>
                    <option value="1">Yes</option>
                </select>
            </div>
            <?php
        } elseif ($type == 'dropdown' || $type == 'checkbox' || $type == 'radio') {
            ?>
            <div class="form-group">
                <label for="label">Type HTML:</label>
                <input type="text" class="form-control" name="input_html" id="input_html" value="<?php if(isset($result->type_html)){echo $result->type_html;}?>" />
            </div>
            <div class="form-group">
                <label for="label">Type Required:</label>
                <select name="required" class="form-control" >
                    <option value="" selected>No</option>
                    <option value="1">Yes</option>
                </select>
            </div>
            <div class="form-group">
                <label for="label">Style:</label>
                <select name="database_type" class="form-control" onchange="js_input_style(this.value)" required>
                    <option selected disabled>--Select One--</option>
                    <option value="manual">Manual</option>
                    <option value="database">Database</option>
                </select>
            </div>
            <div id="js_input_style">
            </div>
            <?php
        }
    }
    ?>
    <input type="submit" class="btn btn-primary" value="Submit" />
    <?php
} elseif(isset($_GET["style"])){
    $type = $_GET["style"];
    if($type == 'manual'){
        ?>
    <table id="table_database">
        <thead>
            <tr>
                <td class="text-center">ID</td>
                <td class="text-center">Value</td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><input type="text" class="form-control" name="databaseid[]" id="databaseid" /></td>
                <td><input type="text" class="form-control" name="databasevalue[]" id="databasevalue" /></td>
                <td><input type="button" class="btn btn-danger" value="Delete" onclick="deleteRow(this)"></td>
            </tr>
            <tr>
                <td><input type="text" class="form-control" name="databaseid[]" id="databaseid" /></td>
                <td><input type="text" class="form-control" name="databasevalue[]" id="databasevalue" /></td>
                <td><input type="button" class="btn btn-danger" value="Delete" onclick="deleteRow(this)"></td>
            </tr>
        </tbody>
    </table>
    <br>
    <input type="button" value="Add Row" class="btn btn-primary" onclick="database_addrow()">
    <br><br>

        <?php
    }
    if($type == 'database'){
        ?>
        <div class="form-group">
            <label for="label">Database Name:</label>
            <input type="text" class="form-control" name="database_name" id="database_name" required />
        </div>
        <div class="form-group">
            <label for="label">Value:</label>
            <input type="text" class="form-control" name="database_value" id="database_value" required />
        </div>
        <div class="form-group">
            <label for="label">WHERE:</label>
            <input type="text" class="form-control" name="database_where" id="database_where" />
        </div>
        <?php
    }
}else {
    echo "Database not selected";
    die();
}
