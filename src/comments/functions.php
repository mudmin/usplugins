<?php
/**
* Main Comments Display Function
* This function can be called anywhere on the site that you want
* comments to display.
**/

function commentsHere($opt = []){
  global $db,$settings,$user;

  /** Get Comment Page info **/
  if(isset($opt['location'])){
    $id = 0;
    $com_location = $opt['location'];
    $com_location_id = $opt['location_id'];
  }else{
    if(!isset($opt['id'])){
        $id = getPageForComments();
    }else{
      $id = $opt['id'];
    }
    if($id == 0){
      echo "This page id is not in the database";
      exit();
    }
    $com_location = 0;
    $com_location_id = 0;
  }

  /** Get Current Page for Redirect **/
  $cur_page = currentPage();

  /** Get user permissions **/
  if(isset($user) && $user->isLoggedIn()){ $user_loggedin = true; }else{ $user_loggedin = false; }

  /** Check if Public Comments are Alllowed **/
  if($settings->cmntpub == 1){ $allow_comments = true; }else{ $allow_comments = false; }

  /** Check if Comments require Moderation **/
  if($settings->cmntapprvd == 1){
    $require_mod = true;
    $com_approved = 0;
  }else{
    $require_mod = false;
    $com_approved = 1;
  }

  /** Check to see if Users Must be logged in to post comments **/
  if($allow_comments == false){
    /** Make sure user is logged in **/
    if($user_loggedin == true){
      $ok_post = true;
    }else{
      $ok_post = false;
    }
  }else{
    /** Public Comments allowed **/
    $ok_post = true;
  }

  /** Check to see if user is submitting a comment **/
  if(!empty($_POST['submitComment']) && $ok_post == true){
    global $user;
    if(!$user || !$user->isLoggedIn()){
      $commentsUserId = 0;
    }else{
      $commentsUserId = $user->data()->id;
    }

    $com_content = Input::get('comment');
    if(empty($com_content)){
      $errors[]='Comment is Blank';
    }else{
      $fields = array(
        'page'=>$id,
        'location'=>$com_location,
        'location_id'=>$com_location_id,
        'user'=>$commentsUserId,
        'comment'=>$com_content,
        'approved'=>$com_approved
      );
      $db->insert('us_comments_plugin',$fields);
      if($require_mod == true){
        $successes[]='Comment Submitted and Waiting for Moderator Approval';
      }else{
        $successes[]='Comment Posted';
      }
    }
  }

  /** Check to see if user or mod is deleting a comment **/
  if(!empty($_POST['deleteComment']) && $ok_post == true){
    $token = $_POST['csrf'];
    if(!Token::check($token)){
      Redirect::to($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
    }
    $com_id = Input::get('com_id');
    $db->update('us_comments_plugin',$com_id,['deleted'=>1]);
    $successes[]='Comment Deleted';
  }

  /** Check to see if user is a Moderator **/
  if(isset($user) && $user->isLoggedIn()){
  $specQ = $db->query("SELECT id FROM users WHERE commentmod = 1 AND id = ? ", array($user->data()->id));
  $specC = $specQ->count();
  $user_mod_check = $specQ->results();
}else{
  $specC = 0;
}
  if(!empty($user_mod_check)){ $user_is_mod = true; }else{ $user_is_mod = false; }

  /** Check to see if mod is approving a comment **/
  if(!empty($_POST['approveComment']) && $ok_post == true && $user_is_mod == true){
    $token = $_POST['csrf'];
    if(!Token::check($token)){
      Redirect::to($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
    }
    $com_id = Input::get('com_id');
    $db->update('us_comments_plugin',$com_id,['approved'=>1]);
    $successes[]='Comment Approved';
  }

  /** Get csrf token for security **/
  $token = Token::generate();
  getPageForComments();
?>
<!-- Comments Display Body -->
<style type="text/css">
.comment {
	white-space: pre-line;
	display: block;
	unicode-bidi: embed;
}
.comment-box {
  margin-top: 10px !important;
}
.comment-box img {
  width: 50px;
  height: 50px;
}
.comment-box .media-left {
  padding-right: 4px;
  width: 65px;
}
.comment-box .media-body p {
  padding: 2px;
}
.comment-box .media-body .media p {
  margin-bottom: 0;
}
.comment-box .media-heading {
  padding: 7px 10px;
  position: relative;
  margin-bottom: -1px;
}
.comment-box .media-body p {
  border: 1px solid;
  padding: 2px;
}
.comment-box .media-content {
  border: 1px solid;
  padding: 2px;
}
.comment-box .media-heading {
  border: 1px solid;
}
.media-content .comment {
  padding: 4px;
}

</style>
<?php if(isset($errors) && !$errors=='') {?><div class="alert alert-danger"><?=display_errors($errors);?></div><?php } ?>
<?php if(isset($successes) && !$successes=='') {?><div class="alert alert-success"><?=display_successes($successes);?></div><?php } ?>
<?php
/** Check to see if user is logged in, and if public comments are allowed **/
if($ok_post == true){
?>
<form class="" action="" method="post">
  <input type="hidden" value="<?=$token;?>" name="csrf">
  <textarea name="comment" rows="4" class="form-control" placeholder="Leave a Comment"></textarea><br>
  <input type="submit" class='btn btn-success btn-sm' name="submitComment" value="Post Comment">
</form>
<?php
}else{
?>
<a href="../users/login.php">Login</a> to post comments.
<?php
}
/** Get Comments **/
/** Display all comments if user is mod **/
if($user_is_mod == true){
  /** Get all comments **/
  if($com_location != '0'){
    $commentsQ = $db->query("SELECT * FROM us_comments_plugin WHERE location = ? AND location_id = ? AND deleted = 0 ORDER BY id DESC LIMIT 100",array($com_location, $com_location_id));
    $commentsC = $commentsQ->count();
    $comments = $commentsQ->results();
  }else{
    $commentsQ = $db->query("SELECT * FROM us_comments_plugin WHERE page = ? AND deleted = 0 ORDER BY id DESC LIMIT 100",array($id));
    $commentsC = $commentsQ->count();
    $comments = $commentsQ->results();
  }
}else{
  if($com_location != 0){
    $commentsQ = $db->query("SELECT * FROM us_comments_plugin WHERE location = ? AND location_id = ? AND approved = 1 AND deleted = 0 ORDER BY id DESC LIMIT 100",array($com_location, $com_location_id));
    $commentsC = $commentsQ->count();
    $comments = $commentsQ->results();
  }else{
    /** Get approved Comments **/
    $commentsQ = $db->query("SELECT * FROM us_comments_plugin WHERE page = ? AND approved = 1 AND deleted = 0 ORDER BY id DESC LIMIT 100",array($id));
    $commentsC = $commentsQ->count();
    $comments = $commentsQ->results();
  }
}

/** Display Comments **/
if($commentsC < 1){
  echo "There are no comments. Leave one!";
}else{
  ?>
  <h3 align="center">Comments</h3>

      <?php foreach($comments as $c){
        /** Check to see if comment is approved - if not then if Mod display approval/delete buttons **/
        if($c->approved == 0){
          $com_not_approved = true; $com_mod_style = 'bg-danger';
        }else{
          $com_not_approved = false; $com_mod_style = '';
        }
        ?>
        <div class='media comment-box'>
          <?php
          $thatUserQ = $db->query("SELECT email FROM users WHERE id = ?",array($c->user));
          $thatUserC = $thatUserQ->count();
          if($thatUserC > 0){
            $thatUser = $thatUserQ->first();
          $grav = get_gravatar(strtolower(trim($thatUser->email)));
        	$useravatar = '<img src="'.$grav.'" class="img img-thumbnail img-fluid rounded">';
          }
          ?>
            <div class='media-left'>
              <?php
                if($thatUserC > 0){
                echo $useravatar;
                }
              ?>
            </div>
            <div class='media-body text-break'>
              <div class='media-heading bg-dark'>
                <a href="#"><strong><?php echouser($c->user);?></strong></a>
                <font class='text-muted' size='1'><?php echo time2str($c->timestamp); ?></font>
              </div>
              <div class='media-content <?=$com_mod_style?>'>
                <div class='comment'><?=$c->comment?></div>
                <?php
                  /** Display Approve Button if Mod and not approved **/
                  if($user_is_mod == true && $com_not_approved == true){
                    echo "
                      <strong><font color='red'>Comment Waiting for Approval!</font></strong><Br>
                      <form class='' action='' method='post' style='display:inline'>
                        <input type='hidden' value='$token' name='csrf'>
                        <input type='hidden' value='$c->id' name='com_id'>
                        <input type='submit' class='btn btn-sm btn-link' name='approveComment' value='Approve'>
                      </form>
                    ";
                  }
                  /** Display Delete Button if Mod or Owner **/
                  if($user_loggedin == true && ($user_is_mod == true || $c->user == $user->data()->id)){
                    echo "
                      <form class='' action='' method='post' style='display:inline'>
                        <input type='hidden' value='$token' name='csrf'>
                        <input type='hidden' value='$c->id' name='com_id'>
                        <input type='submit' class='btn btn-sm btn-link' name='deleteComment' value='Delete'>
                      </form>
                    ";
                  }
                ?>
              </div>
            </div>
        </div>
      <?php } ?>

  <?php
}
}

/** Get's current page id for Comments display **/
function getPageForComments(){
  global $db;
  $page = currentPage();
  $idQ = $db->query("SELECT * FROM pages WHERE page = ?",array($page));
  $idC = $idQ->count();
  if($idC < 1){
    $id = 0;
  }else{
    $idF = $idQ->first();
    $id = $idF->id;
  }
  return $id;
}
