  <?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

<?php
include "plugin_info.php";
pluginActive($plugin_name);
if(!empty($_POST)){
  $token = $_POST['csrf'];
if(!Token::check($token)){
  include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
}
}
if(!empty($_POST['plugin_comments'])){
  $app = Input::get('cmntapprvd');
  $db->update('settings',1,['cmntapprvd'=>$app]);
   Redirect::to($us_url_root.'users/admin.php?view=plugins_config&plugin=comments&err=Settings+updated');
 }
 if(!empty($_POST['addMod'])){
   $uid = Input::get('addMod');
   $check = $db->query("SELECT id FROM users WHERE id = ?",array($uid))->count();
   if($check > 0){
     $db->update('users',$uid,['commentmod'=>1]);
     Redirect::to($us_url_root.'users/admin.php?view=plugins_config&plugin=comments&err=User+added');
   }else{
     Redirect::to($us_url_root.'users/admin.php?view=plugins_config&plugin=comments&err=User+not+found');
   }
 }

 if(!empty($_POST['removeMod'])){
   $uid = Input::get('removeMod');
   $check = $db->query("SELECT id FROM users WHERE id = ?",array($uid))->count();
   if($check > 0){
     $db->update('users',$uid,['commentmod'=>0]);
     Redirect::to($us_url_root.'users/admin.php?view=plugins_config&plugin=comments&err=User+removed');
   }else{
     Redirect::to($us_url_root.'users/admin.php?view=plugins_config&plugin=comments&err=User+not+found');
   }
 }
 $token = Token::generate();

 ?>
<div class="content mt-3">
 		<div class="row">
 			<div class="col-sm-6">
          <a href="<?=$us_url_root?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
          <h3>Settings</h3>
 					<form class="" action="" method="post">
            <input type="hidden" value="<?=$token;?>" name="csrf">
            Comments are auto-approved ("No" requires moderation)<br>
            <select class="" name="cmntapprvd">
              <option <?php if($settings->cmntapprvd == 0){echo "selected";}?> value="0">No</option>
              <option <?php if($settings->cmntapprvd == 1){echo "selected";}?>  value="1">Yes</option>
            </select>
            <input type="submit" name="plugin_comments" value="Update Settings">
          </form>
          <h3>Instructions</h3>
          On any page that you want comments, simply put the tag <font color="red">commentsHere();</font> Note that this should be a protected page in the database because it relies on the page id.  If it is not in the database, you can supply your own id, but putting <font color="red">commentsHere(['id'=>3]);</font> Where 3 is the id you want to use. Just make sure that id will not be used in the pages table in the database.
<br><br>
     The comment manager is at <a href="<?=$us_url_root?>usersc/plugins/comments/files/index.php">usersc/plugins/comments/files/index.php</a>
   	</div> <!-- /.col -->
      <div class="col-sm-6">
        <h3>Comment Moderators</h3>
        Adding a user here allows you to give them comment moderator permission without making them an admin.<br><br>
        <h5>Add Moderator</h5>
        <form class="" action="" method="post">
          <input type="hidden" value="<?=$token;?>" name="csrf">
          Enter the User ID of the User you want to add.<br>
          <input type="number" name="addMod" value="">
          <input type="submit" name="add" value="Add" class="btn btn-success">
        </form>
        <br>
        <h5>Existing Moderators</h5>
        <?php
        $specQ = $db->query("SELECT id FROM users WHERE commentmod = 1");
        $specC = $specQ->count();
        $spec = $specQ->results();
        if($specC > 0){?>

        <table class="table table-striped">
          <thead>
            <tr>
              <th>ID</th><th>User</th><th>Remove</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($spec as $s){ ?>
              <tr>
                <td><?=$s->id?></td>
                <td><?php echouser($s->id);?></td>
                <td>
                  <form class="" action="" method="post">
                    <input type="hidden" value="<?=$token;?>" name="csrf">
                    <input type="hidden" name="removeMod" value="<?=$s->id?>">
                    <input type="submit" name="remove" value="Remove" class="btn btn-danger">
                  </form>
                </td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      <?php }else{
        echo "none";
      } ?>


      </div>
 		</div> <!-- /.row -->
