<?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

<?php
include "plugin_info.php";
pluginActive($plugin_name);
 if(!empty($_POST['plugin_performancechecker'])){
   $token = $_POST['csrf'];
if(!Token::check($token)){
  include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
}
   // Redirect::to('admin.php?err=I+agree!!!');
 }
 $token = Token::generate();
 ?>

<div class="content mt-3">
 		<div class="row">
 			<div class="col-sm-12">
          <a href="<?=$us_url_root?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
 					<h1>Welcome to the Performance Checker Plugin!</h1><br>
          <p class="mb-3">This plugin checks your page load times and displays the overall page load time on the page.  There are 2 additional functions. <code>startPageTimer()</code> and <code>checkPageTimer($id = "", $user_id = 0)</code>
        
          </p>
          <p class="mb-3">These functions are great for checking the running page load time throguhout the page. Simply call startPageTimer() at the top of your page and call <code>checkPageTimer("uniqueIDHere")</code> at any point in the page where you want to see the elapsed time since the page started loading and since your last call.  By passing your own unique id, it will make it really easy to figure out between which two calls of checkPageTimer your performance issue is happening. </p>
          
          <p class="mb-3">By passing an optional user id as the second parameter, only that user will see the debugging info, which is great if you're debugging on a live site.</p>
      
          <p class="mb-3">
          If you appreciate this plugin and would like to make a donation to the author, you can do so at <a href="https://UserSpice.com/donate">https://UserSpice.com/donate</a>. Either way, thanks for using UserSpice!
          </p>

 			</div> <!-- /.col -->
 		</div> <!-- /.row -->
