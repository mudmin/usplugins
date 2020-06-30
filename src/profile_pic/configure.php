  <?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

<?php
include "plugin_info.php";
pluginActive($plugin_name);
 if(!empty($_POST['plugin_profile pic'])){
   $token = $_POST['csrf'];
if(!Token::check($token)){
  include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
}
   // Redirect::to('admin.php?err=I+agree!!!');
 }
 $token = Token::generate();
 $pics = $db->query("SELECT id,profile_pic FROM users WHERE profile_pic != ? ORDER BY RAND() LIMIT 12",[''])->results();

 ?>
<div class="content mt-3">
 		<div class="row">
 			<div class="col-sm-12">
          <a href="<?=$us_url_root?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
 					<h1>Profile Pic Changer</h1>
          <p>There's nothing to configure, so here are (up to) 12 random profile pictures from your collection!</p>
 			</div> <!-- /.col -->
 		</div> <!-- /.row -->
    <div class="row">
      <?php foreach($pics as $p){?>
      <div class="col-6 col-3">
      <a href="<?=$us_url_root?>users/admin.php?view=user&id=<?=$p->id?>">
      <img src="<?=$us_url_root?>usersc/plugins/profile_pic/files/<?=$p->profile_pic?>" alt="">
      </a>
      </div>
      <?php } ?>
    </div>
If you appreciate this plugin and would like to make a donation to the author, you can do so at <a href="https://UserSpice.com/donate">https://UserSpice.com/donate</a>. Either way, thanks for using UserSpice!
