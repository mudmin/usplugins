<?php

class fb_processing {
    
    public 
        $form_name,
        $response;
    
    private 
        $_db            = NULL,
        $_validation    = NULL;
    
    public function __construct()  {
        require_once 'fb_validate.php';
        $this->_db = DB::getInstance();
        $this->_validation = new fb_validate;   
    }
        
    function preProcessForm($opts = []){
        global $abs_us_root;
        global $us_url_root;
        $this->response = array(
            'form_valid'=>false,
            'validation'=>false,
            'token'=>false,
        );
        if(isset($opts["csrf_pass"])){
            $token = $_POST['csrf'];
            if(!Token::check($token)){
                require_once $abs_us_root.$us_url_root.'usersc/scripts/token_error.php';
            }else{
                $this->response['token'] = true;
            }
        }else{
            $this->response['token'] = true;
        }    
        $name = $this->form_name;
        $form = $name.'_fb_fields';
        $fields = [];

        $s = $this->_db->query("SELECT * FROM $form")->results(true);
        //only deal with the fields that were actually posted
        $submitted = [];
        foreach($_POST as $k=>$v){
            foreach($s as $t){
                if(array_search($k,$t)){
                    $submitted[]= $t;
                }
            }
        }

        $errors = [];
        $successes = [];
        //check for posted arrays
        foreach($_POST as $k=>$v){
            foreach($submitted as $t)
            if(is_array($k)){
            }
        }

        foreach($submitted as $c){
            $val = [];
            if($c['field_type'] == "checkbox"){
                if(! isset($_POST[$c['name']])){
                    $data = [];
                }else{
                    $data = filter_var_array($_POST[$c['name']],FILTER_SANITIZE_ENCODED);
                }
                $data = json_encode($data);
                $fields[$c['name']] = $data;
            }elseif($c['field_type'] == "passwordE"){
                $fields[$c['name']] = password_hash(Input::get($c['name']), PASSWORD_BCRYPT, array('cost' => 12));
            }elseif($c['field_type'] == "timestamp"){
                continue;
            }elseif($c['field_type'] == "dropdown"){
                if(!is_null($c['databasevalue'])){
                    if(!in_array(Input::get($c['name']), json_decode($c['databasevalue']))){
                        $count = 1;
                    }else{
                        $count = 0;
                    }
                }elseif(!empty($c['database_where'])){
                    $database_name = $c['database_name'];
                    $database_value = $c['database_value'];
                    $database_where = html_entity_decode($c['database_where'], ENT_QUOTES);
                    $count = $this->_db->query("SELECT NULL FROM $database_name WHERE $database_value = ? AND $database_where",[Input::get($c['name'])])->count();
                }else{
                    $database_name = $c['database_name'];
                    $database_value = $c['database_value'];
                    $count = $this->_db->query("SELECT NULL FROM $database_name WHERE $database_value = ?",[Input::get($c['name'])])->count();
                }
                if($count = 0){
                    $this->_validation->addError("Error: Processing ".$c['name']."!");
                }else{
                    $fields[$c['name']] = Input::get($c['name']);
                }
            }else{
                $fields[$c['name']] = Input::get($c['name']);
                //dnd($c);
                //dnd($_POST);
                if($c['requirements'] != "" && $c['requirements'] != '[]'){

                  $val = json_decode($c['requirements']);
                  $process = [];
                  $label_name = json_decode($c['field_html'])->label;
                  $process['display'] = $label_name;
                  foreach($val as $key => $value){
                    $process[$key] = $value;
                  }
                    $this->_validation->check($_POST,array(
                        $c['name'] => $process,
                    ));
                    if($this->_validation->passed()) {
                          // die("Passed");
                    }else{
                        if($opts != '' && isset($opts['debug'])){
                            dump($this->_validation);
                        }
                    }
                }
            }
        }

        if(!$this->_validation->errors()=='') {
            ?>
        <div class="alert alert-danger">
            <?=display_errors($this->_validation->errors());?>
        </div><?php }
        if($this->_validation->passed()) {
            $this->response['validation']=true;
            if($opts != '' && isset($opts['debug'])){
              dnd($db->errorInfo());
            }
        }
        $this->response['fields'] = $fields;
        $this->response['name'] = $name;
        if($this->response['validation'] == true && $this->response['token'] == true){
            $this->response['form_valid'] = true;
        }
    }
    
    function postProcessForm(){
        if(isset($_GET['id'])){
            $id = Input::get('id');
            if(is_numeric($id)){
                $this->_db->update($this->response['name'],$id,$this->response['fields']);
            }
        }else{
            $this->_db->insert($this->response['name'],$this->response['fields']);
            return $this->_db->lastId();
        }
        $this->response['errors'] = $this->_db->errorInfo();
    }
    
}
