<?php
require_once '../../../users/init.php';
$db = DB::getInstance();
if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>
<?php
include "plugin_info.php";
pluginActive($plugin_name);
require_once $abs_us_root.$us_url_root.'usersc/plugins/formbuilder/assets/fb_createdatabase.php'; 
$basename = basename($_SERVER['REQUEST_URI']);
$parts = parse_url($basename);
$form_url = $parts['path'];
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Form Builder</title>
        <?php
        require_once $abs_us_root.$us_url_root.'usersc/plugins/formbuilder/assets/bootstrap4.php'; 
        // require_once $abs_us_root.$us_url_root.'users/includes/template/prep.php';
        ?>
        <script type="text/JavaScript" src="<?=$us_url_root?>usersc/plugins/formbuilder/assist/formbuilder.js"></script>
    </head>
    <body>
        <?php require_once $abs_us_root.$us_url_root.'usersc/plugins/formbuilder/assets/fb_nav_bar.php';
        $count = $db->query("SELECT form FROM fb_formbuilder WHERE form != 'fb_settings' AND form != 'fb_javascript' ORDER by form")->count();
        if($count > 0){
            $results = $db->results();
            $removed_item = false;
            foreach($results as $result){
                $form = $result->form;
                $form2 = $result->form."_fb_fields";
                $count = $db->query("SELECT id FROM $form")->count();
                $count2 = $db->query("SELECT id FROM $form2")->count();
                if($count == 0 && $count2 == 0){
                    $db->delete('fb_formbuilder',array('form','=',$form));
                    $db->query("DROP TABLE $form, $form2;");
                    $removed_item = true;
                }
            }
            if($removed_item == true){
                $count = $db->query("SELECT form FROM fb_formbuilder WHERE form != 'fb_settings' AND form != 'fb_javascript' ORDER by form")->count();
                if($count > 0){

                }else {
                    $results = "";
                }
            }
        } else {
            $results = "";
        }
        ?>
        <div id="page-wrapper">
            <div class="container">
                <div class="row justify-content-md-center">
                    <div class="col-sm-10, col-md-10, col-lg-10">
                        <h3 class="text-center"><a data-toggle="collapse" href="#Forms">Forms</a></h3>
                        <p class="text-center">Use the PHP code below for the form.</p>
                        <?php if(!empty($results)){ ?>
                        <div id="Forms" class="collapse <?php if(!isset($_GET['form_preview'])){ echo "show"; } ?> row justify-content-center">
                            <div className="table-responsive">
                                <table class="table table-responsive" id="job-table">
                                    <thead>
                                        <tr>
                                            <th scope="col">Form Name</th>
                                            <th scope="col">PHP Code</th>
                                            <th scope="col"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach($results AS $result){ ?>
                                        <tr>
                                            <td scope="row"><?=$result->form?></td>
                                            <td>fb_displayform(<?=$result->form?>)</td>
                                            <td><a class="btn btn-light" href="<?=$form_url?>?form_preview=<?=$result->form?>" role="button">Preview</a></td>
                                        </tr>
                                            <?php } ?>
                                    </tbody>
                                </table>

                            </div>
                        </div>
                    </div>
                    <?php if(isset($_GET['form_preview'])){ ?>
                    <div class="col-sm-12, col-md-12, col-lg-12">
                        <h3 class="text-center"><a data-toggle="collapse" href="#form_design">Form Design</a></h4>
                        <div class="collapse row justify-content-center" id="form_design">
                        <?php
                        $database = Input::get('form_preview')."_fb_fields";
                        $count = $db->query("SELECT * FROM $database ORDER by fb_order")->count();
                        if($count > 0){
                            $results = $db->results();
                            ?>
                            <div className="table-responsive">
                                <form action="<?=$_SERVER['REQUEST_URI']?>" method="post">
                                    <table class="table table-responsive" id="job-table">
                                        <thead>
                                            <tr>
                                                <th scope="col"><a class="btn btn-light" href="<?=$us_url_root?>usersc/plugins/formbuilder/FormBuilder.php?database=<?=Input::get('form_preview')?>" role="button">Add</a></th>
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
                                                <th scope="row"><a class="btn btn-light" href="<?=$us_url_root?>usersc/plugins/formbuilder/FormBuilder.php?database=<?=Input::get('form_preview')?>&id=<?=$result->id?>" role="button">Edit</a></th>
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
                                    <input type="hidden" name="form" value="<?=Input::get('form_preview');?>" />
                                    <?php
                                    $tokenName = Config::get('session/token_name');
                                    if (Session::exists($tokenName)) {
                                        $token = Session::get($tokenName);
                                    } else {
                                        $token = Token::generate();
                                    }
                                    ?>
                                    <input type="hidden" name="csrf" value="<?=$token;?>" />
                                    <input type="submit" name="form_design_submit" value="submit" class="btn btn-primary" />
                                </form>
                            </div>  
                        <?php } ?>
                        </div>
                    </div>
                    <div class="col-sm-12, col-md-12, col-lg-12">
                        <br />
                        <br />
                    </div>
                    
                </div>
                <div class="col-sm-12, col-md-12, col-lg-12">
                    <h3 class="text-center"><a data-toggle="collapse" href="#form_preview">Form Preview</a></h3>
                </div>
                <div class="collapse show" id="form_preview">
                    <?php
                    $database = Input::get('form_preview');
                    require_once 'assets/fb_displayform.php';
                    $options = array(
                        'token'     => Input::get('token'),
                        'nosubmit'  => true
                    );
                    fb_displayform($database,$options);
                    ?>
                </div>
                        <?php } ?>
                <div class="col-sm-12, col-md-12, col-lg-12">
                    <br />
                    <br />
                </div>

                        <?php } else { ?>
                        <h4 class="text-center">No Forms Created</h4>
                    </div>
                </div>    
                        <?php } ?>
            </div>
        </div>
    </body>
</html>
