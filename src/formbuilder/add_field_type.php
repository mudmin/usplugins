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
        <div id="page-wrapper">
            <div class="container">
                <div class="row justify-content-md-center">
                    <h3>Add Item to Field Type</h3>
                    <br />
                    <ol>
                        <li>Page: FormBuilder.php</li>
                        <br />
                        <ul>
                            <li>Line: 41 - 54</li>
                            <li>Copy full line,</li>
                            <li>Paste in position.</li>
                            <li>Update the answer on If statement and value.</li>
                            <li>Give it a displayed name.</li>                          
                        </ul> 
                        <br />
                        <li>Page: assets/fb_createform.php</li>
                        <br />
                        <ul>
                            <li>line: 180</li>
                            <li>Is this a hidden item?</li>                        
                        </ul>
                        <br />
                        <ul>
                            <li>line: 306-335</li>
                            <li>Copy the elseif statement.</li>
                            <li>Paste in group,</li>
                            <li>update the field type</li>
                            <li>Also make sure correct MYSQL statement for item.</li>                          
                        </ul>
                        <br />
                        <ul>
                            <li>line: 556-630</li>
                            <li>Select correct option's for that is required.</li>      
                        </ul>
                        <br />
                        <li>Page: assets/fb_design.php/li>
                        <br />
                        <br />
                        <ul>
                            <li>line: 49-54</li>
                            <li>If its hidden option, then add it here.</li>      
                        </ul>
                        <br />
                        <ul>
                            <li>line: 158-245</li>
                            <li>Add the item here</li> 
                            <li>(this is what is displayed in form.)</li> 
                        </ul>
                    </ol>
                </div>
            </div>
        </div>
        <div class="col-sm-12, col-md-12, col-lg-12">
            <br />
            <br />
        </div>
    </body>
</html>

