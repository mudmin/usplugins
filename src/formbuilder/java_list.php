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
        <title>Form Builder - Javascript Code</title>
        <?php
        require_once $abs_us_root.$us_url_root.'usersc/plugins/formbuilder/assets/bootstrap4.php'; 
        // require_once $abs_us_root.$us_url_root.'users/includes/template/prep.php';
        ?>
        <script type="text/JavaScript" src="<?=$us_url_root?>usersc/plugins/formbuilder/assist/formbuilder.js"></script>
    </head>
    <body>
        <?php require_once $abs_us_root.$us_url_root.'usersc/plugins/formbuilder/assets/fb_nav_bar.php';
        $count = $db->query("SELECT id, fb_java_name FROM fb_javascript ORDER by fb_java_name")->count();
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
                        <h3 class="text-center"><a data-toggle="collapse" href="#Forms">JavaScript</a></h3>
                        <p class="text-center">This is a list of JavaScript code.</p>
                        <div id="Forms" class="collapse <?php if(!isset($_GET['form_preview'])){ echo "show"; } ?> row justify-content-center">
                            <div className="table-responsive">
                                <table class="table table-responsive" id="job-table">
                                    <thead>
                                        <tr>
                                            <th scope="col"><a class="btn btn-light" href="<?=$us_url_root?>usersc/plugins/formbuilder/javascript_builder.php" role="button">Add</a></th>
                                            <th scope="col">JavaScript Name</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if(!empty($results)){
                                        foreach($results AS $result){ ?>
                                        <tr>
                                            <th scope="row"><a class="btn btn-light" href="<?=$form_url?>?form_preview=fb_javascript&id=<?=$result->id?>" role="button">Display JavaScript Code</a></th>
                                            <td scope="row"><?=$result->fb_java_name?></td>
                                        </tr>
                                        <?php }} ?>
                                    </tbody>
                                </table>

                            </div>
                        </div>
                    </div>
                    <?php if(isset($_GET['form_preview'])){ ?>
                    
                <div class="col-sm-12, col-md-12, col-lg-12">
                    <h3 class="text-center"><a data-toggle="collapse" href="#form_preview">Java Script Code</a></h3>
                </div>
                <div class="collapse show" id="form_preview">
                    <?php
                    $database = Input::get('form_preview');
                    require_once 'assets/fb_displayform.php';
                    fb_displayform($database);
                    ?>
                </div>
                        <?php } ?>
                <div class="col-sm-12, col-md-12, col-lg-12">
                    <br />
                    <br />
                </div>
            </div>
        </div>
    </body>
</html>
