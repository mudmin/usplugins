<?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

<?php
include "plugin_info.php";
pluginActive($plugin_name);
 if(!empty($_POST)){
   if(!Token::check(Input::get('csrf'))){
     include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
   }
 }
?>

<div class="content mt-3">
 		<div class="row">
 			<div class="col-12">
          <a href="<?=$us_url_root?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
 					<h1>Configure the Demo Plugin!</h1>
          <p>
            This page is designed for you to configure and give basic information about your plugin. 
          </p>
          <p>
            The demo plugin is designed to give you a basic framework for creating your own plugins.  To create your own plugin:
              <ul>
                <li>Copy this plugin to a new folder in the usersc/plugins directory</li>
                <li>Do a case sensitive search for both "demo" and "Demo" and replace those with your new plugin folder name ("demo") and the plugin name "Demo."</li>
                <li>Update the info.xml file to give your plugin a description and add your author/version info.</li>
                <li>Edit the files for your own purposes</li>
                <li>Update the install.php and migrate.php files to create any new database tables, etc.</li>
                <li>Check out the hooks folder to learn how to hook into existing UserSpice files.  See <a href="https://userspice.com/plugin-hooks/" style="color:blue">this page</a> for more info.</li>
                <li>Check out the menu_hooks folder to learn how to make a custom menu snippet available.</li>
              </ul>
          </p>

 			</div>
 		</div>


    <!-- Do not close the content mt-3 div in this file -->
