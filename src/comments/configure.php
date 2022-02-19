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
   $app2 = Input::get('cmntpub');
   $db->update('settings',1,['cmntpub'=>$app2]);
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
 if(!empty($_POST['approveComment'])){
   $com_id = Input::get('approveComment');
   $check = $db->query("SELECT id FROM us_comments_plugin WHERE id = ?",array($com_id))->count();
   if($check > 0){
     $db->update('us_comments_plugin',$com_id,['approved'=>1]);
     Redirect::to($us_url_root.'users/admin.php?view=plugins_config&plugin=comments&err=Comment+Approved');
   }else{
     Redirect::to($us_url_root.'users/admin.php?view=plugins_config&plugin=comments&err=Comment+Not+Found');
   }
 }
 if(!empty($_POST['deleteComment'])){
   $com_id = Input::get('deleteComment');
   $check = $db->query("SELECT id FROM us_comments_plugin WHERE id = ?",array($com_id))->count();
   if($check > 0){
     $db->update('us_comments_plugin',$com_id,['deleted'=>1]);
     Redirect::to($us_url_root.'users/admin.php?view=plugins_config&plugin=comments&err=Comment+Deleted');
   }else{
     Redirect::to($us_url_root.'users/admin.php?view=plugins_config&plugin=comments&err=Comment+Not+Found');
   }
 }
 $token = Token::generate();

 ?>

<!-- Plugin Configure Body -->
<style>
.comment {
	white-space: pre-line;
	display: block;
	unicode-bidi: embed;
}
</style>
<div class="content mt-3">
  <a href="<?=$us_url_root?>users/admin.php?view=plugins">Return to the Plugin Manager</a>

 		<div class="row">
 			<div class="col-sm-6">
        <div class="card no-padding">
          <div class="card-header">
            <h3>Settings</h3>
          </div>
          <div class="card-body">
    				<form class="" action="" method="post">
              <input type="hidden" value="<?=$token;?>" name="csrf">

              <!-- Moderation Settings -->
              <div class="form-group">
                <label for="site_offline">Require Moderation? <a href="#!" tabindex="-1" title="Note" data-trigger="focus" class="nounderline" data-toggle="popover" data-content="Yes Comments are auto-approved. No Comments require approval."><i class="fa fa-question-circle"></i></a></label>
                <span style="float:right;">
                  <select class="" name="cmntapprvd">
                    <option <?php if($settings->cmntapprvd == 0){echo "selected";}?> value="0">No</option>
                    <option <?php if($settings->cmntapprvd == 1){echo "selected";}?>  value="1">Yes</option>
                  </select>
                </span>
              </div>

              <!-- Moderation Settings -->
              <div class="form-group">
                <label for="site_offline">Allow Public? <a href="#!" tabindex="-1" title="Note" data-trigger="focus" class="nounderline" data-toggle="popover" data-content="Yes Allows Public Users to Post Comments. No Requires Users to be Logged In to Post Comments."><i class="fa fa-question-circle"></i></a></label>
                <span style="float:right;">
                  <select class="" name="cmntpub">
                    <option <?php if($settings->cmntpub == 0){echo "selected";}?> value="0">No</option>
                    <option <?php if($settings->cmntpub == 1){echo "selected";}?>  value="1">Yes</option>
                  </select>
                </span>
              </div>

              <input type="submit" name="plugin_comments" class="btn btn-success" value="Update Settings">
            </form>
          </div>
        </div>

   	  </div> <!-- /.col -->
      <div class="col-sm-6">
        <div class="card no-padding">
          <div class="card-header">
            <h3>Comment Moderators</h3>
          </div>
          <div class="card-body">
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
        </div>
      </div>

      <div class="col-12">
        <div class="card no-padding">
          <div class="card-header">
            <h3>Comments Needing Approval</h3>
          </div>
          <div class="card-body">
            <?php
            $commentsQ = $db->query("SELECT * FROM us_comments_plugin WHERE approved = 0 AND deleted = 0 ORDER BY id DESC LIMIT 50");
            $commentsC = $commentsQ->count();
            $comments = $commentsQ->results();
            if($comments > 0){?>

            <table class="table table-striped">
              <thead>
                <tr>
                  <th>Page ID</th><th>User</th><th>Comment Content</th><th>Approve</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach($comments as $c){ ?>
                  <tr>
                    <td>
                      <?php
                        if($c->location != '0'){
                          echo $c->location." - ".$c->location_id;
                        }else{
                          echo $c->page;
                        }
                      ?>
                    </td>
                    <td><?php echouser($c->user);?></td>
                    <td><div class="comment"><?=$c->comment?></div></td>
                    <td>
                      <form class="" action="" method="post" style="display:inline">
                        <input type="hidden" value="<?=$token;?>" name="csrf">
                        <input type="hidden" name="approveComment" value="<?=$c->id?>">
                        <input type="submit" name="approve" value="Approve" class="btn btn-success">
                      </form>
                      <form class="" action="" method="post" style="display:inline">
                        <input type="hidden" value="<?=$token;?>" name="csrf">
                        <input type="hidden" name="deleteComment" value="<?=$c->id?>">
                        <input type="submit" name="delete" value="Delete" class="btn btn-danger">
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
        </div>
      </div>
      <div class="row">
        <div class="col-12">
          <div class="card no-padding">
            <div class="card-header">
              <h3>Instructions</h3>
            </div>
            <div class="card-body">
              <p>
                On any page that you want comments, simply put the tag <font color="red">commentsHere();</font>
                Note that this should be a protected page in the database because it relies on the page id.
                If it is not in the database, you can supply your own id, but putting
                <font color="red">commentsHere(['id'=>3]);</font> Where 3 is the id you want to use.
                Just make sure that id will not be used in the pages table in the database.
              </p>

          <p>
            If using on a user's profile or anything where IDs can be used, use the following:
            <font color="red">commentsHere(['location'=>'profile', 'location_id'=>$userid]);</font>
            Change "profile" to the name of the page, and change $userid to the unique id related
            to where comments show.
          </p>

              <p>
                To be completely dynamic for things like complicated GET variables or clean urls, you could do something like
                <code>
                if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'){<br>
                  $link = "https://";<br>
                }else{<br>
                  $link = "http://";<br>
                };<br>
                $link.= $_SERVER['HTTP_HOST'];   // Append the requested resource location to the URL <br>
                $link.= $_SERVER['REQUEST_URI'];<br>
                $parts = explode('/', $link);<br>
                $location = end($parts);<br>
                $location_id       = $db->cell("hp_products.id", ["product_name_tag","=","$location"]);<br>
                commentsHere(['location'=>$location, 'location_id'=>$location_id]);<br>
                </code>
              </p>
              The comment manager is at <a href="<?=$us_url_root?>usersc/plugins/comments/files/index.php">usersc/plugins/comments/files/index.php</a>
            </div>
          </div>
        </div>
      </div>
 		</div> <!-- /.row -->
<!-- </div>  -->
