<?php

class fb_create_database {

    public
        $_db        = null;

    public function __construct()  {
        $this->_db = DB::getInstance();
    }

    public function create($database){
        $count = $this->_db->query("SELECT form FROM fb_formbuilder WHERE form = ?",[$database])->count();
        if($count == 0){
            $fields = array('form'=>$database,);
            $this->_db->insert('fb_formbuilder',$fields);
            $this->create_db($database);
            $this->create_db_fb_fields($database);
        } else {
            $error_message = $database." already exists!";
        }
    }

    private function create_db_fb_fields($database) {
        $database_fb_fields = $database."_fb_fields";
        $columns = "(
            `id` INT(11) NOT NULL AUTO_INCREMENT ,
            `fb_order` INT(11) NOT NULL ,
            `name` VARCHAR(255) NOT NULL ,
            `field_type` TEXT NOT NULL ,
            `field_html` TEXT NOT NULL ,
            `requirements` TEXT NOT NULL ,
            `databasevalue` VARCHAR(255) NULL DEFAULT NULL ,
            `database_name` VARCHAR(255) NULL DEFAULT NULL ,
            `database_value` VARCHAR(255) NULL DEFAULT NULL ,
            `database_where` VARCHAR(255) NULL DEFAULT NULL ,
            PRIMARY KEY (`id`)) ENGINE = InnoDB;
            ";
        $this->_db->query("CREATE TABLE IF NOT EXISTS `$database_fb_fields` $columns");
    }

    private function create_db($database) {
        $columns = "(
            `id` INT(11) NOT NULL AUTO_INCREMENT ,
            PRIMARY KEY (`id`)) ENGINE = InnoDB;
            ";

        $this->_db->query("CREATE TABLE IF NOT EXISTS `$database` $columns");
    }

}

if (isset($_POST['database_submit'])){
    $token = $_POST['csrf'];
    if(!Token::check($token)){
        include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
    }

    $createdatabase = new fb_create_database();
    if(!preg_match('/[^a-z0-9_]/',Input::get('database'))){
        $createdatabase->create(Input::get('database'));
        Redirect::to('FormBuilder.php?database='.Input::get('database'));
    } else {
        $error_message = "Only Lower Case, Numbers and Underscore only";
    }
}elseif(isset($_POST['form_design_submit'])){
    require_once '../../../../users/init.php';
    if(!Token::check(Input::get('csrf'))){
       require_once $abs_us_root.$us_url_root.'usersc/scripts/token_error.php';
    }
    require_once 'fb_validate.php';
    $validation = new fb_validate;
    $db = DB::getInstance();
    $form = Input::get('form');
    $count = $db->query("SELECT NULL FROM fb_formbuilder WHERE form = ?",[$form])->count();
    if($count>0){
        $database = $form."_fb_fields";
        $count = $db->query("SELECT id, name FROM $database")->count();
        if($count>0){
            $val = [];
            $datas = $db->results();
            foreach($datas AS $data){
                $val[$data->id] = array(
                    'display' => $data->name,
                    'is_numeric' => true,
                    'greaterthan' => 0
                );
            }
            $validation->check($_POST, $val);
            if ($validation->passed()) {
                foreach($datas AS $data){
                    $db->update($database,$data->id,['fb_order' => Input::get($data->id)]);
                }
            }else{
                $error_message = $validation->errors();
            }
            Redirect::to($us_url_root.'usersc/plugins/formbuilder/index.php');
        }
    } else {
        $error_message = "$form not found!";
    }
}elseif(isset($_GET['form_design'])){
    require_once '../../../../users/init.php';
    $database = Input::get('form_design')."_fb_fields";
    $db = DB::getInstance();
    $count = $db->query("SELECT * FROM $database ORDER by fb_order")->count();
    if($count > 0){
        $results = $db->results();
        ?>
<div className="table-responsive">
    <form action="<?=$_SERVER['REQUEST_URI']?>" method="post">
        <table class="table table-responsive" id="job-table">
            <thead>
                <tr>
                    <th scope="col"><a class="btn btn-light" href="<?=$us_url_root?>usersc/plugins/formbuilder/FormBuilder.php?database=<?=Input::get('form_design')?>" role="button">Add</a></th>
                    <th scope="col-2">Order</th>
                    <th scope="col-2">Name</th>
                    <th scope="col">Field Type</th>
                    <th scope="col-2">Label</th>
                    <th scope="col">Input Steps</th>
                    <th scope="col">Required</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($results AS $result){
                    $field_html = json_decode($result->field_html,true);
                    ?>
                <tr>
                    <th scope="row"><a class="btn btn-light" href="<?=$us_url_root?>usersc/plugins/formbuilder/FormBuilder.php?database=<?=Input::get('form_design')?>&id=<?=$result->id?>" role="button">Edit</a></th>
                <?php
                
                
                    $id = $result->id ?: '(empty)';
                    $order = $result->fb_order ?: '(empty)';
                    $name = $result->name ?: '(empty)';
                    $field_type = $result->field_type ?: '(empty)';
                    $label = $field_html['label'] ?: '(empty)';
                    $input_step = $field_html['input_step'] ?: '(empty)';
                    $required = $field_html['required'] ?: 'No';

                    if ($required == 1){ $required = 'Yes';}
                    
                    echo '<td><input type="number" name="'.$id.'" id="'.$id.'" class="form-control" value="'.$order.'" step="1" /></td>';
                    echo '<td>'.$name.'</td>';
                    echo '<td>'.$field_type.'</td>';
                    echo '<td>'.$label.'</td>';
                    echo '<td>'.$input_step.'</td>';
                    echo '<td>'.$required.'</td>';
                ?>
                </tr>
                    <?php
                } ?>
            </tbody>
        </table>
        <input type="hidden" name="form" value="<?=Input::get('form_design');?>" />
        <input type="hidden" name="csrf" value="<?=Input::get('token');?>" />
        <input type="submit" name="form_design_submit" value="submit" class="btn btn-primary" />
    </form>
</div>
        <?php
    }
}elseif(isset($_GET['form_preview'])){
    require_once '../../../../users/init.php';
    $database = Input::get('form_preview');
    require_once 'fb_displayform.php';
    $options = array(
        'token'     => Input::get('token'),
        'nosubmit'  => true
    );
    fb_displayform($database,$options);
}
