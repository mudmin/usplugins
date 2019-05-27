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
        <title>Form Builder Settings</title>
        <?php require_once $abs_us_root.$us_url_root.'usersc/plugins/formbuilder/assets/bootstrap4.php'; ?>
        <script type="text/JavaScript" src="assist/formbuilder.js"></script>
    </head>
    <body>
<div class="row bg-light">
    <br />    
</div>
<div class="row bg-light justify-content-center">
    <h2>Form Builder</h2>
</div>
<nav class="navbar navbar-expand-sm bg-light justify-content-center">

  <!-- Links -->
  <ul class="navbar-nav">
    <li class="nav-item">
      <a class="nav-link" href="index.php">Home</a>
    </li>
    </li>
	<li class="nav-item">
        <a class="nav-link" href="<?=$us_url_root?>users/admin.php?view=plugins">Exit FormBuilder</a>
    </li>
  </ul>

</nav>
        <?php
        if(isset($error_message)){
            echo "<UL CLASS='bg-danger'>";
            echo '<h4 class="text-center">'.$error_message.'</h4>';
            echo "</UL>";
        }
        ?>
        <div id="page-wrapper">
            <div class="container">
                <div class="row justify-content-md-center">
                    <?php
                    fb_displayform('fb_settings');
                    ?>
                </div>
            </div>
        </div>
    </body>
</html>

