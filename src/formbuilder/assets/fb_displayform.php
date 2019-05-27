<?php

class fb_options {
    
    // PLEASE TEST TO MAKE SURE IT WORKS FOR YOU.
    
    var $token;
    var $noclose;       
    var $nosubmit;      
    var $nogetid; // This will remove the 'id' from $_GET!
    
    // All 3 Required
    var $submit_name;   
    var $submit_value;  
    var $submit_class;  
    
    // What Page to Navigate to:
    // Default: Relaod current page.
    var $navigation;   
    
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
    
    if (!isset($options['nosubmit'])){
        if (isset($_POST['csrf'])){
            require_once 'fb_processing.php';
            
            $processing = new fb_processing;
            $processing->form_name = $database;
            $processing->preProcessForm($options);
            $response = $processing->response;
            if($response['form_valid'] == true){
                $processing->postProcessForm();
            }
            $response = $processing->response;
            
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
        echo $html_code;
    }
}





