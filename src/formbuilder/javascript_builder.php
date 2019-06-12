<?php
require_once '../../../users/init.php';
$db = DB::getInstance();
if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>
<?php
include "plugin_info.php";
pluginActive($plugin_name);
require_once $abs_us_root.$us_url_root.'usersc/plugins/formbuilder/assets/fb_displayform.php';
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
        <?php require_once $abs_us_root.$us_url_root.'usersc/plugins/formbuilder/assets/fb_nav_bar.php';?>
        <div id="page-wrapper">
            <div class="container">
                <?php
                fb_displayform('fb_javascript');
                ?>
            </div>
        </div>
    </body>
</html>

