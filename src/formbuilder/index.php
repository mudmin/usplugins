<?php
require_once '../../../users/init.php';
$db = DB::getInstance();
if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>
<?php
include "plugin_info.php";
pluginActive($plugin_name);
require_once $abs_us_root.$us_url_root.'usersc/plugins/formbuilder/assets/fb_createdatabase.php'; ?>
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
        if(isset($error_message)){
            echo "<UL CLASS='bg-danger'>";
            foreach($error_message as $error){
            echo '<h4 class="text-center">'.$error.'</h4>';
            }
            echo "</UL>";
        }
        $count = $db->query("SELECT form FROM fb_formbuilder")->count();
        if($count > 0){
            $results = $db->results();
        } else {
            $results = "";
        }
        ?>
        <div id="page-wrapper">
            <div class="container">
                <div class="row justify-content-md-center">
                    <div class="col-sm-10, col-md-10, col-lg-10">
                        <h3 class="text-center">Forms</h3>
                        <p class="text-center">Use the PHP code below for the form.</p>
                        <?php if(!empty($results)){ ?>
                        <div class="row justify-content-center">
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
                                        foreach($results AS $result){
                                            ?>
                                        <tr>
                                            <td scope="row"><?=$result->form?></td>
                                            <td>fb_displayform(<?=$result->form?>)</td>
                                            <td><a class="btn btn-light" role="button" onclick="js_form_design('<?=$result->form?>','<?=$token?>'),js_form_preview('<?=$result->form?>','<?=$token?>')">Preview</a></td>
                                        </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>

                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12, col-md-12, col-lg-12">
                        <h3 class="text-center">Form Design</h4>
                        <div class="row justify-content-center" id="js_form_design"></div>
                    </div>
                    <div class="col-sm-12, col-md-12, col-lg-12">
                        <br />
                        <br />
                    </div>
                    
                </div>
                <div class="col-sm-12, col-md-12, col-lg-12">
                    <h3 class="text-center">Form Preview</h3>
                </div>
                <div id="js_form_preview"></div>
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
