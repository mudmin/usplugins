  <?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

<?php
include "plugin_info.php";
pluginActive($plugin_name);
 if(!empty($_POST['plugin_cleanurls'])){
   $token = $_POST['csrf'];
if(!Token::check($token)){
  include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
}
   // Redirect::to('admin.php?err=I+agree!!!');
 }
 $token = Token::generate();
 ?>
 <style media="screen">
   p {color:black;}
 </style>
<div class="content mt-3">
 		<div class="row">
 			<div class="col-sm-12">
          <a href="<?=$us_url_root?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
 					<h3>Instructions</h3>
          <p>
            <b>Notes:</b><br>
              These snippets should work all the way back to UserSpice 4 (later versions) without breaking anything, but we need your help testing.
          </p>

          <p>
            <b>Step 1:</b><br>
              Copy the following code snippet to the bottom of usersc/includes/analytics.php (after the closing php tag)
              <xmp>
                <script type="text/javascript">
                    $( document ).ready(function() {
                    	$("form").each(function() {
                    		var method = $(this).attr('method');
                        if(typeof method !== "undefined"){ //make sure the form actually has a method attribute
                      		if(method.toUpperCase() === 'POST'){ //case insensitive
                      			$(this).attr("action","");
                      		}
                        }
                    	});
                    });
                    </script>
              </xmp>
              This script is necessary because if a POST form explicitly lists an action with a .php extension, the submitted POST data will be lost on the redirect.  The action field is not necessary in any of our UserSpice forms because we always POST to the same page.  I know the location is a bit weird, but it loads on every page (backend and frontend), after jQuery is loaded, so it's a nice place to put it for testing purposes.
          </p>

          <p>
            <b>Step 2:</b><br>
              Create a file called <b><em>.htaccess</b></em> in your UserSpice root and paste the following into it. This assumes your server has the mod_rewrite module on.
              <xmp>
                <IfModule mod_rewrite.c>
                 RewriteEngine On
                 Rewritecond %{REQUEST_FILENAME} !/parsers
                 RewriteCond %{REQUEST_FILENAME} !-d
                 RewriteCond %{THE_REQUEST} /([^.]+)\.php [NC]
                 RewriteRule ^ /%1 [NC,L,R]
                 RewriteCond %{REQUEST_FILENAME}\.php -f
                 RewriteRule ^(.*)$ $1.php [NC,L]
                </IfModule>
              </xmp>
              This script is necessary because if a POST form explicitly lists an action with a .php extension, the submitted POST data will be lost on the redirect.  The action field is not necessary in any of our UserSpice forms because we always POST to the same page.
          </p>

          <p>
            <b>Step 3:</b><br>
              Test your site for any problems (especially redirects and forms) and report any problems over on Discord.
          </p>
          <h3>Known Issues</h3>
          <p>
            1. If you have a folder with the same name as the directory, the folder will currently take precidence.  In other words, if you add a folder called users/admin, that will load instead of users/admin.php
          </p>
          <p>
            2. Although GET variables work, we're not doing anything to make them pretty.
          </p>

          <h3>Support</h3>
          <p>
            This is one of the most requested features in the last 5 years, so if appreciate this work and would like to make a donation to the author, you can do so at <a href="https://UserSpice.com/donate">https://UserSpice.com/donate</a>. Either way, thanks for using UserSpice!
          </p>
 			</div> <!-- /.col -->
 		</div> <!-- /.row -->
