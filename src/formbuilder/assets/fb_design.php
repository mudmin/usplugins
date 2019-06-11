<?php

class fb_design {
    
    public 
        // IN
        $options        = [],
        $database,
        // OUT        
        $html_code      = [];
    
    private
        $_errors        = [],
        $_db            = NULL,
        $_results       = [],
        $_div2          = 1,
        $_div_value,
        $_time          = NULL,
        $_javascript    = [];
    
    public function __construct()  {
        $this->_db = DB::getInstance();
    }
    
    public function start(){
        $data = $this->query();
        if(empty($this->_errors)){
            $this->foreach($data);
        }
        if(!empty($this->_errors)){
            $this->display_errors();
        }
    }
    
    private function foreach($datas){
        $this->html_code[] = '<form action="'.$_SERVER['REQUEST_URI'].'" method="post">';
        
        foreach($datas AS $data){
            $name = $data->name;
            $field_type = $data->field_type;
            $field_html = json_decode($data->field_html);
            
            
            if(!empty($data->databasevalue)){
                $results = json_decode($data->databasevalue);
            } elseif(!empty($data->database_name)){
                if ( $field_type != 'hidden'){
                $results = $this->database_query($data);
                } else {
                    $results = '';
                }
            } else {
                $results = '';
            } 
            
            if(!empty($this->_errors)){
                return;
            } 

            $this->form($name, $field_type, $field_html, $results, $data);
            
        }
        $this->div_class1(1);
        $this->options('token');
        $this->options('submit');
        if(!empty($this->_javascript)){
            foreach ($this->_javascript as $java_name){
                $count = $this->_db->query("SELECT * FROM fb_javascript WHERE fb_java_name = $java_name")->count();
                if($count > 0){
                    $this->JavaScript($this->_db->results()); 
                }
            }
        }
    }

    private function form($name,$field_type = [],$field_html = [], $results = [], $rdata = []){
        if ($field_type == 'hidden'){
            if(isset($this->_results->$name)){
                if(!empty($this->_results->$name)){
                    $value = $this->_results->$name;
                } else {
                    $value = "";
                }
            }else {
                $value = "";
            }
            if(isset($rdata->database_name)){
                if(!empty($rdata->database_name)){
                    $this->hidden_query($rdata, $value);
                }
            }
        }elseif ($field_type == 'hidden_timestamp'){
            // Time Stamp is auto on server.
        }elseif ($field_type == 'javascript'){
            $this->_javascript[] = $rdata->database_value;
        } else {
            $this->design($name, $field_type, $field_html, $results);
        }
    }
    
    private function options($item){
        if($item == 'token'){
            if(!isset($this->options['token'])){
                $this->html_code[] = '<input type="hidden" name="csrf" value="'.Token::generate().'" />';
            } else {
                $this->html_code[] = '<input type="hidden" name="csrf" value="'.$this->options['token'].'" />';
            }
        } elseif($item == 'submit'){
            if(!empty($this->options['noclose'])){
                // No Submit Button
            } elseif(isset($this->options['submit_name']) && isset($this->options['submit_value']) && isset($this->options['submit_class'])){
                $this->html_code[] = '<input type="submit" name="'.$this->options['submit_name'].'" value="'.$this->options['submit_value'].'" class="'.$this->options['submit_class'].'" />';
                $this->html_code[] = '</form>';
            } elseif(isset($this->options['nosubmit'])){
                $this->html_code[] = '</form>';
            } else {
                $this->html_code[] = '<input type="submit" name="fb_formbuilder" value="submit" class="btn btn-primary" />';
                $this->html_code[] = '</form>';
            }
        }
    }

    private function query(){
        $database_fb_fields = $this->database . "_fb_fields";
        $count = $this->_db->query('SELECT * FROM '.$database_fb_fields.' ORDER BY fb_order')->count();
        if($count > 0){
            $data = $this->_db->results();
            if(isset($_GET['id'])){
                require_once 'fb_validate.php';
                $fb_validate = new fb_validate;
                $get = Input::get('id');
                $fb_validate->check($_GET, array('id' => array('display' => 'ID', 'required' => true, 'is_numeric' => true)));
                if($fb_validate->passed()){
                    $count = $this->_db->query('SELECT * FROM '.$this->database.' WHERE id = ?',[$get])->count();
                    if($count > 0){
                        $this->_results = $this->_db->first();
                    } 
                }
            }
            return $data;
        } else {
            $this->_errors += ['Error: '.$this->database.' does not exist.'];
        }
    }
    
    private function database_query($data){
        if(!empty($data->database_where)){
            $database_value = html_entity_decode($data->database_value, ENT_QUOTES);
            $database_where = html_entity_decode($data->database_where, ENT_QUOTES);
            $count = $this->_db->query("SELECT id, $database_value AS value FROM $data->database_name WHERE $database_where")->count();
        } else {
            $database_value = html_entity_decode($data->database_value, ENT_QUOTES);
            $count = $this->_db->query("SELECT id, $database_value AS value FROM $data->database_name")->count();
        }
        if($count > 0){          
            return $this->_db->results();
        }elseif (!empty($this->_db->_error)) {
            $this->_errors += ['Error: Error building form!'];
        } else {
            
        }   
    }
    
    private function hidden_query($data, $value){
        $database_name = html_entity_decode($data->database_name, ENT_QUOTES).'_fb_fields';
        if(!empty($value)){
            $count = $this->_db->query("SELECT * FROM $database_name WHERE id = $value ORDER BY fb_order")->count();
        } else {
            $count = $this->_db->query("SELECT * FROM $database_name ORDER BY fb_order")->count();
        }
        if(empty($this->_db->error())){          
            $hidden_datas = $this->_db->results();
            
            foreach ($hidden_datas as $hidden_data){
                $name = $hidden_data->name;
                $field_type = $hidden_data->field_type;
                $field_html = json_decode($hidden_data->field_html);

                if(!empty($hidden_data->databasevalue)){
                    $results = json_decode($data->databasevalue);
                } elseif(!empty($hidden_data->database_name)){
                    if ( $field_type != 'hidden'){
                    $results = $this->database_query($hidden_data);
                    } else {
                        $results = '';
                    }
                } else {
                    $results = '';
                }   
                if(!empty($this->_errors)){
                    return;
                } 

                $this->form($name, $field_type, $field_html, $results, $hidden_data);
            }
        } else {
            $this->_errors += ['Error: Error building form!'];
        }
    }

    
    private function design($name,$field_type = [],$field_html = [], $results = []){
        if($field_type == 'time'){
            $clockpicker = 'clockpicker ';
            $clockpicker2=' data-placement="left" data-align="top" data-autoclose="true"';
            if(empty($this->_time)){
                $this->html_code[] = "<script type='text/javascript'>";
                $this->html_code[] = "$('.clockpicker').clockpicker();";
                $this->html_code[] = "</script>";
                $this->_time = true;
            }
        }else{
            $clockpicker='';
            $clockpicker2='';
        };
        if($field_type == 'blank_line'){
            $this->html_code[] = '<div class="col-sm-12, col-md-12, col-lg-12"><br /></div>';
        }elseif($field_type == 'empty_div'){
            $this->div_class1(0, $field_type, $field_html);
            $this->html_code[] = '<div name="'.$name.'" id="'.$name.'" class='.$field_html->div_class2.'>';
            $this->html_code[] = '</div>';
        }else{
            $this->div_class1(0, $field_type, $field_html);
            $this->html_code[] = '<div class="'.$clockpicker.$field_html->div_class2.'"'.$clockpicker2.'>';
            $this->html_code[] = '<label class="'.$field_html->label_class.'" for="'.$name.'">'.$field_html->label.'</label>';
            if($field_type != 'label'){
                $this->field_type($name,$field_type,$field_html,$results, $this->_results);
            }
            $this->html_code[] = '</div>';
        }
    }
    
    private function div_class1($div_close, $field_type = [],$field_html = []){
        
        if($div_close == 1 && $this->_div2 == 2){
            $this->html_code[] = '</div>';
            $this->_div2 = 1;
        }
        if($this->_div2 == 2){
            if(!empty($field_html->div_class1)){
                if($this->_div_value != $field_html->div_class1){
                    $this->html_code[] = '</div>';
                    $this->html_code[] = '<div class="'.$field_html->div_class1.'">';
                    $this->_div2 = 2;
                }
            }else{
                $this->html_code[] = '</div>';
                $this->_div2 = 1;
            }
            
        }elseif($this->_div2 == 1){
            if(!empty($field_html->div_class1)){
                
                $this->_div_value = $field_html->div_class1;
                $this->html_code[] = '<div class="'.$field_html->div_class1.'">';
                $this->_div2 = 2;
            }
        }
    }

        private function field_type($name,$field_type = [],$field_html = [], $results = [],$get_results = []){
        if(!isset($_POST)){
            $value = Input::get($name);
        }elseif(isset($get_results->$name)){
            if(!empty($get_results->$name)){
                $value = $get_results->$name;
            } else {
                $value = "";
            }
        }else {
            $value = "";
        }
        
        if(!empty($field_html->input_step)){
            $input_step = $field_html->input_step;
        } else {
            $input_step = "";
        }
        if(!empty($field_html->input_html)){
            $input_html = " ".html_entity_decode($field_html->input_html);
        } else {
            $input_html = "";
        }
        if(!empty($field_html->required)){
            $required = " required";
        } else {
            $required = "";
        }
        
        // Field Type Search
        
        if($field_type == 'text'){
            $this->html_code[] = '<input type="'.$field_type.'" name="'.$name.'" id="'.$name.'" class="'.$field_html->input_class.'" value="'.$value.'"'.$input_html.$required.' />';
        } elseif($field_type == 'password' || $field_type == 'e_password'){
            if($field_type == 'e_password'){
                $field_type = 'password';
            }
            $this->html_code[] = '<input type="'.$field_type.'" name="'.$name.'" id="'.$name.'" class="'.$field_html->input_class.'" value="'.$value.'"'.$input_html.$required.' />';
        }elseif($field_type == 'number'){    
            $this->html_code[] = '<input type="'.$field_type.'" name="'.$name.'" id="'.$name.'" class="'.$field_html->input_class.'" value="'.$value.'" step="'.$input_step.'"'.$input_html.$required.' />';
        }elseif($field_type == 'time'){ 
            $this->html_code[] = '<input type="text" name="'.$name.'" id="'.$name.'" class="'.$field_html->input_class.'" value="'.$value.'"'.$input_html.$required.' />';
            
        }elseif($field_type == 'date'){    
            $this->html_code[] = '<input type="text" name="'.$name.'" id="'.$name.'" class="'.$field_html->input_class.'" value="'.$value.'"'.$input_html.$required.' />';
            $this->html_code[] = "<script>";
            $this->html_code[] = "$('#".$name."').datetimepicker({";
            $this->html_code[] = "dateFormat: 'yy-mm-dd',";
            $this->html_code[] = "showTimepicker:0";
            $this->html_code[] = "});";
            $this->html_code[] = "</script>";
        }elseif($field_type == 'datetime'){    
            $this->html_code[] = '<input type="text" name="'.$name.'" id="'.$name.'" class="'.$field_html->input_class.'" value="'.$value.'"'.$input_html.$required.' />';
            $this->html_code[] = "<script>";
            $this->html_code[] = "$('#".$name."').datetimepicker({";
            $this->html_code[] = "dateFormat: 'yy-mm-dd',";
            $this->html_code[] = "stepMinute: 1,";
            $this->html_code[] = "showSecond: 0,";
            $this->html_code[] = "});";
            $this->html_code[] = "</script>";
        }elseif($field_type == 'tel'){
            $this->html_code[] = '<input type="'.$field_type.'" name="'.$name.'" id="'.$name.'" class="'.$field_html->input_class.'" value="'.$value.'" pattern="[0-9]{3}-[0-0]{3}-[0-9]{4}"'.$input_html.$required.' />';
            $this->html_code[] = 'Format: 123-123-1234';
        }elseif($field_type == 'textarea'){
            $this->html_code[] = '<textarea name="'.$name.'" id="'.$name.'" class="'.$field_html->input_class.'"'.$input_html.$required.'>'.$value.'</textarea>';
        }elseif($field_type == 'dropdown'){
            $this->html_code[] = '<select name="'.$name.'" id="'.$name.'" class="'.$field_html->input_class.'"'.$input_html.$required.'>';
            $this->html_code[] = '<option disabled selected>--Select One--</option>';
            
            if(isset($results)){
                foreach ($results AS $result){
                    if(!empty($value)){
                       if($result->id == $value){
                            $selected = " selected";
                        } else {
                            $selected = "";
                        } 
                    } else {
                        $selected = "";
                    }                

                    $database_id = $result->id;
                    $database_value = $result->value;

                    $this->html_code[] ='<option value="'.$database_id.'"'.$selected.'>'.$database_value.'</option>';
                }
            }
            $this->html_code[] = '</select>';
        }elseif($field_type == 'checkbox'){
            foreach ($results AS $result){
                if($result->id = $value){
                    $checked = " checked";
                } else {
                    $checked = "";
                }
                $this->html_code[] = '<label class="'.$field_html->input_class.'"><input type="'.$field_type.'" name="'.$name.'" id="'.$name.'" value="'.$result->id.'"'.$input_html.$required.$checked.'>'.$result->value.'</label>';
            }
        }elseif($field_type == 'radio'){    
            foreach ($results AS $result){
                if($result->id = $value){
                    $checked = " checked";
                } else {
                    $checked = "";
                }
                $this->html_code[] = '<label class="'.$field_html->input_class.'"><input type="'.$field_type.'" name="'.$name.'" id="'.$name.'" value="'.$result->id.'"'.$input_html.$required.$checked.'>'.$result->value.'</label>';
            }
        }elseif($field_type == 'file'){
            $this->html_code[] = '<input type="file" name="'.$name.'" id="'.$name.'" class="'.$field_html->input_class.'"'.$input_html.$required.'>'; 
        }        
    }
    
    private function JavaScript($results){
        foreach($results as $result){
            $this->html_code[] = "<script ".html_entity_decode($result->fb_java_html).">";
            if(!empty($result->fb_java_code)){
                $this->html_code[] = html_entity_decode($result->fb_java_code);
                $this->html_code[] = "</script>";
            }
        }
    }

    public function display_errors() {
        unset($this->html_code); // Kill HTML Code!	
        $this->html_code[] = "<UL CLASS='bg-danger'>";
        foreach($this->_errors as $error) {
            $this->html_code[] = "<LI>{$error}</LI>";
        }
        $this->html_code[] = "</UL>";
    }
    
}
