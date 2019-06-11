<?php

class fb_process_form {
    
    public 
        $options = [];

    private 
        $_db                = null,
        $_processing        = null,
        $_processing_name   = [],
        $_name              = [],
        $_db_name           = [],
        $_response          = [];


    public function __construct()  {
        require_once 'fb_processing.php';
        $this->_db = DB::getInstance();
        $this->_processing = new fb_processing;
    }
    
    public function start($database){
        $response = $this->options('submit');
        if($response == true){
            if (isset($_POST['csrf'])){
                $token = $_POST['csrf'];
                if(!Token::check($token)){
                    global $abs_us_root;
                    global $us_url_root;
                    require_once $abs_us_root.$us_url_root.'usersc/scripts/token_error.php';
                } else {
                    $this->options = 'csrf_pass';
                }
                $this->pre_process($database);
                $this->_processing->form_name = $database;
                $this->_processing->preProcessForm($this->options);
                $this->_response[] = "main";
                $this->_response["main"] = $this->_processing->response;
                if(isset($this->_db_name["main"])){
                    foreach ($this->_db_name["main"] as $result){
                        if(isset($result->database_name)){
                            $_response["main"]['fields'][$result->name] = $this->post_process_database($result->name);
                        }elseif(!empty($this->options[$result->name])){
                            $_response["main"]['fields'][$result->name] = $this->options[$result->name];
                        }else{
                            $_response["main"]['fields'][$result->name] = "";
                        }
                    }
                    $this->_processing->response = $this->_response["main"];
                    return $this->_processing->postProcessForm();
                }
            }
        }
    }
    
    private function options($item){
        if($item == 'submit'){
            if (!isset($this->options['nosubmit'])){
                return true;
            }
        }
    }
    
    private function pre_process($database = null){
        if(!empty($database)){
            $database_fb_fields = $database."_fb_fields";
            $count = $this->_db->query("SELECT name, database_name FROM $database_fb_fields WHERE field_type = 'hidden'")->count();
            if($count > 0){
                $result = $this->hidden_pre_process($db->results());
            }elseif(!isset($this->_db_name['main'])){
                $this->_db_name[] = "main";
                $this->_db_name["main"] = $this->_db->results();
            }
        }
        if(isset($result)){
            if(!isset($result->database_name)){
                if(!isset($this->_response[$result->name])){
                $this->_processing_name[$result->name]->form_name = $result->database_name;
                $this->_processing_name[$result->name]->preProcessForm($this->options);
                $this->_response[] = $result->name;
                $this->_response[$result->name] = $this->_processing_name[$result->name]->response;
                }
            }
        }
    }
    
    private function hidden_pre_process($results){
        foreach($results as $result){
            $this->_processing_name[] = $this->_name = $result->name;
            $this->_processing_name[$result->name] = new fb_processing;;
            if(!empty($database_query->database_name)){
                $this->pre_process($database_query->database_name);
            }
            if(isset($this->_db_name['main']) && !isset($this->_db_name[$this->_name])){
                $this->_db_name[] = $this->_name;
                $this->_db_name[$this->_name] = $results;
            } elseif(!isset($this->_db_name['main'])){
                $this->_db_name[] = "main";
                $this->_db_name["main"] = $results;
            }
            return $result;
        }
    }
    
    private function post_process_database($name = null){
        foreach ($this->_db_name[$name] as $result){
            if(isset($result->database_name)){
                $_response[$result->name]['fields'][$result->name] = $this->post_process_database($result->name);
            }elseif(isset($this->options[$result->name])){
                $_response[$result->name]['fields'][$result->name] = $this->options[$result->name];
            }else{
                $_response[$result->name]['fields'][$result->name] = "";
            }
        }
        $this->_processing_name[$name]->response = $this->_response[$name];
        return $this->_processing_name[$name]->postProcessForm();
    }
    
}

function fb_displayform($database,$options = []){
    if (isset($options['nogetid'])){
        if(isset($_GET['id'])){
            $basename = basename($_SERVER['REQUEST_URI']);
            $parts = parse_url($basename);
            $queryParams = array();
            parse_str($parts['query'], $queryParams);
            unset($queryParams['id']);
            $queryString = http_build_query($queryParams);
            $url = $parts['path'] . '?' . $queryString;
            redirect::to($url);
        }
    }
    
    if(!empty($_POST)){
        if (!isset($options['nosubmit'])){
            require_once 'fb_processing.php';

            $fb_process_form = new fb_process_form;
            $fb_process_form->options = $options;
            $results = $fb_process_form->start($database);

            if(isset($options['navigation'])){
                Redirect::to($options['navigation']);
            }
        }
    }
    
    require_once 'fb_design.php';
     $form = new fb_design;
    $form->database = $database;
    $tokenName = Config::get('session/token_name');
    if (Session::exists($tokenName)) {
        $options['token'] = Session::get($tokenName);
    }
    $form->options = $options;
    $form->start();
    foreach ($form->html_code as $html_code) {
        echo $html_code."\n";
    }
}
