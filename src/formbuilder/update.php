<?php
//For security purposes, it is MANDATORY that this page be wrapped in the following
//if statement. This prevents remote execution of this code.
require_once '../../../users/init.php';
	if (in_array($user->data()->id, $master_account)){
//all actions should be performed here.

//check which updates have been installed
$count = 0;
$db = DB::getInstance();
include "plugin_info.php";
pluginActive($plugin_name);


//Make sure the plugin is installed and get the existing updates
$checkQ = $db->query("SELECT * FROM us_plugins WHERE plugin = ?",array($plugin_name));
$checkC = $checkQ->count();
if($checkC < 1){
	err($plugin_name." is not installed!");
	die();
}

$check = $checkQ->first();
if($check->updates == ''){
$existing = []; //deal with not finding any updates
}else{
$existing = json_decode($check->updates);
}
$active = $check->status;
//list your updates here from oldest at the top to newest at the bottom.
//Give your update a unique update number/code.

$count = $db->query("SELECT fb_version FROM fb_settings WHERE id=1")->count();
if($count > 0){
    $fb_version = $db->first()->fb_version;
}else{
    $fb_version = 0;
}

switch ($fb_version) {
    case 0:
        // May 23, 2019        
        // Install
        
        $fb_formbuilder = 'fb_formbuilder';

        $columns = "
            (
            `id` INT NOT NULL AUTO_INCREMENT ,
            `form` VARCHAR(255) NOT NULL ,
            PRIMARY KEY (`id`)
            ) ENGINE = InnoDB
            ";
        $db->query("CREATE TABLE IF NOT EXISTS `$fb_formbuilder` $columns");

        $check = $db->query("SELECT NULL FROM fb_formbuilder WHERE form = ?",['fb_settings'])->count();
        if($check < 1){
        $insert_settings = array(
            'form' => 'fb_settings',
        );
        $db->insert($fb_formbuilder,$insert_settings);
        }

        $fb_settings = "fb_settings";
        $columns2 = "(
            `id` INT(11) NOT NULL AUTO_INCREMENT ,
            `div_class` VARCHAR(255) NULL ,
            `label_class` VARCHAR(255) NULL ,
            `input_class` VARCHAR(255) NULL ,
            `type_html` VARCHAR(255) NULL ,
            `install_check` INT(11) NULL ,
            PRIMARY KEY (`id`)) ENGINE = InnoDB;
            ";
        $db->query("CREATE TABLE IF NOT EXISTS `$fb_settings` $columns2");

        $check = $db->query("SELECT NULL FROM fb_settings WHERE install_check = ?",[1])->count();
        if($check < 1){
        $insert = array(
            'div_class' => 'form-group',
            'label_class' => 'form-group',
            'input_class' => 'form-control',
            'install_check' => 1
        );
        $db->insert($fb_settings,$insert);
        }

        $database_fb_fields = $fb_settings.'_fb_fields';
        $columns3 = "(
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
        $db->query("CREATE TABLE IF NOT EXISTS `$database_fb_fields` $columns3");

        $check = $db->query("SELECT NULL FROM fb_settings_fb_fields WHERE fb_order = 1")->count();
        if($check < 1){
        $insert1 = array(
            'fb_order' => '1',
            'name' => 'div_class',
            'field_type' => 'text',
            'field_html' => '{"div_class":"form-group","label":"div Class: (DEFAULT)","label_class":"form-group","input_class":"form-control","input_html":"","required":"No","input_step":""}'
        );
        $insert2 = array(
            'fb_order' => '2',
            'name' => 'label_class',
            'field_type' => 'text',
            'field_html' => '{"div_class":"form-group","label":"Label Class: (DEFAULT)","label_class":"form-group","input_class":"form-control","input_html":"","required":"No","input_step":""}'
        );
        $insert3 = array(
            'fb_order' => '3',
            'name' => 'input_class',
            'field_type' => 'text',
            'field_html' => '{"div_class":"form-group","label":"Input Class: (DEFAULT)","label_class":"form-group","input_class":"form-control","input_html":"","required":"No","input_step":""}'
        );
        $insert4 = array(
            'fb_order' => '4',
            'name' => 'type_html',
            'field_type' => 'text',
            'field_html' => '{"div_class":"form-group","label":"Type HTML: (DEFAULT)","label_class":"form-group","input_class":"form-control","input_html":"","required":"No","input_step":""}'
        );
        $db->insert($database_fb_fields,$insert1);
        $db->insert($database_fb_fields,$insert2);
        $db->insert($database_fb_fields,$insert3);
        $db->insert($database_fb_fields,$insert4);
        }
        
        $fb_version ++;
        
        echo '<p class="text-center">Installing - Complete</p>';
        echo '<br />';
        
    case 1:
        // May 25, 2019

        // Updating 1
    
        // Added / Adjust / Fixed Code
        // Add: Setting for On-Submit of Form: Exit?
        // Add: add_field_type.php
        // Adjust index.php
        // Adjust fb_nav_bar.php
        // Adjust FormBuilder.php
        // Add / Fixed Code: fb_createform.php
        // Add / Fixed Code: fb_design.php
        // Add / Fixed Code: fb_displayform.php
        // Add / Fixed Code: fb_processing.php
        
        $table_name = "fb_settings";
        $database_name = $table_name."_fb_fields";
        $database_column = 'submit_new';
        $check = $db->query("SHOW COLUMNS FROM $table_name LIKE $database_column")->count();
        $check2 = $db->query("SELECT NULL FROM $database_name WHERE name = ?",[$database_column])->count();
        if($check == 0 && $check2 == 0){
            $insert5 = array(
                'fb_order' => '1',
                'name' => 'submit_new',
                'field_type' => 'text',
                'field_html' => '{"div_class1":"","div_class2":"form-group","label":"When Creating Form, On Submit: Exit?","label_class":"form-group","input_class":"form-control","input_html":"","required":"1","input_step":""}'
            ); 
            $db->insert($database_name,$insert5);
            $db->query("ALTER TABLE $table_name ADD $database_column VARCHAR(255) NULL AFTER type_html");    
        }
        
        $fb_version ++;
    case 2:
        // May 26, 2019
        // Updating 2
        
        // Adding 2 Div's
        // Adjusting: index.php
        // Adjusting: FormBuilder.php
        // Adjusting: fb_createform.php
        // Adjusting: fb_design.php
        // Adjusting: fb_displayform.php
        // Adjusting: fb_processing.php
        // Adjust MYSQL for new Div format.
        
        $check = $db->query("SELECT * FROM fb_formbuilder")->count();
        if($check > 0){
            $forms = $db->results();
            foreach($forms as $form){
                $database_fb_fields = $form->form."_fb_fields";
                $db->query("SELECT id, field_html FROM $database_fb_fields")->count();
                if($check > 0){
                    $querys = $db->results();
                    foreach ($querys as $query){
                        $field_html = json_decode($query->field_html);
                        if(isset($field_html->div_class)){
                            $insert_query = json_encode(array(
                                'div_class1'      => '',
                                'div_class2'      => $field_html->div_class,
                                'label'           => $field_html->label,
                                'label_class'     => $field_html->label_class,
                                'input_class'     => $field_html->input_class,
                                'input_html'      => $field_html->input_html,
                                'required'        => $field_html->required,
                                'input_step'      => $field_html->input_step,
                            ));
                            $update = array('field_html'=>$insert_query);
                            $db->update($database_fb_fields,$query->id,$update);
                        }
                    }
                }
            }    
        }
        $table_name = "fb_settings";
        $database_name = $table_name."_fb_fields";
        $database_column = 'fb_version';
        $check = $db->query("SHOW COLUMNS FROM $table_name LIKE $database_column")->count();
        if($check == 0){
            $db->query("ALTER TABLE $table_name ADD $database_column INT(11) NULL");
        }
        
        $fb_version ++;
    case 3:
        
        // 6/1/2019
        // Join 2 form's together onto 1 form.
        // Update the Index.php
        // Update All files.
        
        // Applying Fix for Update.
        // Removing & Readding FB_Settings database's.
        
        // Still testing the Link with Hidden Fields.
        // Hidden Fields dont show up on webpage any longer.
        
        // Hide FB_SETTING form.
        
        $db->delete('fb_formbuilder',array('form','=','fb_settings'));
        $db->query("DROP TABLE `fb_settings`, `fb_settings_fb_fields`;");

        $check = $db->query("SELECT NULL FROM fb_formbuilder WHERE form = ?",['fb_settings'])->count();
        if($check < 1){
        $insert_settings = array(
                'form' => 'fb_settings',
        );
        $db->insert($fb_formbuilder,$insert_settings);
        }

        $fb_settings = "fb_settings";
        $columns2 = "(
                `id` INT(11) NOT NULL AUTO_INCREMENT ,
                `div_class1` VARCHAR(255) NULL ,
                `div_class2` VARCHAR(255) NULL ,
                `label_class` VARCHAR(255) NULL ,
                `input_class` VARCHAR(255) NULL ,
                `type_html` VARCHAR(255) NULL ,
                `submit_new` VARCHAR(255) NULL ,
                `install_check` INT(11) NULL ,
                `fb_version` INT(11) NULL ,
                PRIMARY KEY (`id`)) ENGINE = InnoDB;
                ";
        $db->query("CREATE TABLE IF NOT EXISTS `$fb_settings` $columns2");
        $check = $db->query("SELECT NULL FROM fb_settings WHERE install_check = ?",[1])->count();
        if($check < 1){
        $insert = array(
                'div_class1' => 'form-row',
                'div_class2' => 'form-group',
                'label_class' => 'form-group',
                'input_class' => 'form-control',
                'install_check' => 1,
                'submit_new'	=> 'false'
        );
        $db->insert($fb_settings,$insert);
        }

        $database_fb_fields = $fb_settings.'_fb_fields';
        $columns3 = "(
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
        $db->query("CREATE TABLE IF NOT EXISTS `$database_fb_fields` $columns3");

        $check = $db->query("SELECT NULL FROM fb_settings_fb_fields WHERE fb_order = 1")->count();
        if($check < 1){
        $insert0 = array(
                'fb_order' => '1',
                'name' => 'div_class1',
                'field_type' => 'text',
                'field_html' => '{"div_class2":"form-group","label":"div Class1: (DEFAULT)","label_class":"form-group","input_class":"form-control","input_html":"","input_step":""}'
        );
        $insert1 = array(
                'fb_order' => '2',
                'name' => 'div_class2',
                'field_type' => 'text',
                'field_html' => '{"div_class2":"form-group","label":"div Class2: (DEFAULT)","label_class":"form-group","input_class":"form-control","input_html":"","input_step":""}'
        );
        $insert2 = array(
                'fb_order' => '3',
                'name' => 'label_class',
                'field_type' => 'text',
                'field_html' => '{"div_class2":"form-group","label":"Label Class: (DEFAULT)","label_class":"form-group","input_class":"form-control","input_html":"","input_step":""}'
        );
        $insert3 = array(
                'fb_order' => '4',
                'name' => 'input_class',
                'field_type' => 'text',
                'field_html' => '{"div_class2":"form-group","label":"Input Class: (DEFAULT)","label_class":"form-group","input_class":"form-control","input_html":"","input_step":""}'
        );
        $insert4 = array(
                'fb_order' => '5',
                'name' => 'type_html',
                'field_type' => 'text',
                'field_html' => '{"div_class2":"form-group","label":"Type HTML: (DEFAULT)","label_class":"form-group","input_class":"form-control","input_html":"","input_step":""}'
        );
        $insert5 = array(
                'fb_order' => '6',
                'name' => 'submit_new',
                'field_type' => 'dropdown',
                'field_html' => '{"div_class1":"","div_class2":"form-group","label":"When Creating Form, On Submit: Exit?","label_class":"form-group","input_class":"form-control","input_html":"","required":"1","input_step":""}',
                'databasevalue' => '[{"id":"true","value":"Yes"},{"id":"false","value":"No"}]'
        );
        $db->insert($database_fb_fields,$insert0); 
        $db->insert($database_fb_fields,$insert1);
        $db->insert($database_fb_fields,$insert2);
        $db->insert($database_fb_fields,$insert3);
        $db->insert($database_fb_fields,$insert4);
        $db->insert($database_fb_fields,$insert5);
        }
        
        $fb_version ++;
    //case 4:
        
        // 6/4/2019
        // Add Blank Div used with Javascript
        // Add Blank Line in form
        // Adding Javascript Feature
        
        $fb_javascript = "fb_javascript";
        $columns2 = "(
            `id` INT(11) NOT NULL AUTO_INCREMENT ,
            `div_class1` VARCHAR(255) NULL ,
            `div_class2` VARCHAR(255) NULL ,
            `label_class` VARCHAR(255) NULL ,
            `input_class` VARCHAR(255) NULL ,
            `type_html` VARCHAR(255) NULL ,
            `submit_new` VARCHAR(255) NULL ,
            `install_check` INT(11) NULL ,
            `fb_version` INT(11) NULL ,
            PRIMARY KEY (`id`)) ENGINE = InnoDB;
            ";
        $db->query("CREATE TABLE IF NOT EXISTS `$fb_javascript` $columns2");

        $fb_javascript_fb_fields = 'fb_javascript_fb_fields';
        $columns3 = "(
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
        $db->query("CREATE TABLE IF NOT EXISTS `$fb_javascript_fb_fields` $columns3");

        $check = $db->query("SELECT NULL FROM fb_settings_fb_fields WHERE fb_order = 1")->count();
        if($check < 1){
        $insert0 = array(
            'fb_order' => '1',
            'name' => 'fb_java_name',
            'field_type' => 'text',
            'field_html' => '{"div_class1":"form-row","div_class2":"form-group col-md-6","label":"Name","label_class":"form-group","input_class":"form-control","input_html":"","required":"1","input_step":""}',
            'requirements' => '{"display":"Name","required":true}'
        );
        $insert1 = array(
            'fb_order' => '2',
            'name' => 'fb_java_html',
            'field_type' => 'text',
            'field_html' => '{"div_class1":"form-row","div_class2":"form-group col-md-6","label":"JavaScript HTML","label_class":"form-group","input_class":"form-control","input_html":"","required":"","input_step":""}',
            'requirements' => '{"display":"JavaScript HTML"}'
        );
        $insert2 = array(
            'fb_order' => '3',
            'name' => 'fb_java_code',
            'field_type' => 'textarea',
            'field_html' => '{"div_class1":"","div_class2":"form-group","label":"JavaScript Code","label_class":"form-group","input_class":"form-control","input_html":"rows=&quot;10&quot;","required":"1","input_step":""}',
            'requirements' => '{"display":"JavaScript Code"}'
        );
        $insert3 = array(
            'fb_order' => '4',
            'name' => 'fb_java_information',
            'field_type' => 'textarea',
            'field_html' => '{"div_class1":"","div_class2":"form-group","label":"Information","label_class":"form-group","input_class":"form-control","input_html":"rows=&quot;5&quot;","required":"1","input_step":""}',
            'requirements' => '{"display":"Information"}'
        );

        $db->insert($fb_javascript_fb_fields,$insert0); 
        $db->insert($fb_javascript_fb_fields,$insert1);
        $db->insert($fb_javascript_fb_fields,$insert2);
        $db->insert($fb_javascript_fb_fields,$insert3);
        }
        
        $fb_version ++;
    //case 5:
        
    case 'END':
        $db->update('fb_settings',1,['fb_version'=>$fb_version]);
        break;
}

//after all updates are done. Keep this at the bottom.
$update = $fb_version;
if(!in_array($update,$existing)){
$existing[] = $update; //add the update you just did to the existing update array
$count++;
}
$new = json_encode($existing);
$db->update('us_plugins',$check->id,['updates'=>$new]);
if(!$db->error()) {
	if($count == 1){
            if($active == 'active'){
                Redirect::to($us_url_root."usersc/plugins/formbuilder/index.php");
            }
	}else{
            err($count.' updates applied!');
            if($active == 'active'){
                Redirect::to($us_url_root."usersc/plugins/formbuilder/index.php");
            }
	}
} else {
	err('Failed to save updates');
	logger($user->data()->id,"USPlugins","Failed to save updates, Error: ".$db->errorString());
}

} //do not perform actions outside of this statement
