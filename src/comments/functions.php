<?php
function commentsHere($opt = []){
  global $db, $settings,$user;
  if(!isset($opt['id'])){
    $id = getPageForComments();
  }else{
    $id = $opt['id'];
  }
  if($id == 0){
    echo "This page id is not in the database";
    exit();
  }
  if(!empty($_POST['submitComment'])){
    $token = $_POST['csrf'];
    if(!Token::check($token)){
      Redirect::to($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
    }
    $fields = array(
      'page'=>$id,
      'user'=>$user->data()->id,
      'comment'=>Input::get('comment'),
      'approved'=>$settings->cmntapprvd
    );
    $db->insert('us_comments_plugin',$fields);
    // dnd($db->errorInfo());
  }
  $token = Token::generate();
  getPageForComments();
?>
<style type="text/css">
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
.comment-box .media-heading:before {
    content: "";
    width: 12px;
    height: 12px;
    border-width: 1px 0 0 1px;
    -webkit-transform: rotate(-45deg);
    transform: rotate(-45deg);
    position: absolute;
    top: 10px;
    left: -6px;
}
</style>

<form class="" action="" method="post">
  <input type="hidden" value="<?=$token;?>" name="csrf">
  <textarea name="comment" rows="4" class="form-control" placeholder="Leave a Comment"></textarea><br>
  <input type="submit" class='btn btn-success btn-sm' name="submitComment" value="Post Comment">
</form>
<?php
$commentsQ = $db->query("SELECT * FROM us_comments_plugin WHERE page = ? AND approved = 1 AND deleted = 0 ORDER BY id DESC LIMIT 100",array($id));
$commentsC = $commentsQ->count();
$comments = $commentsQ->results();
if($commentsC < 1){
  echo "There are no comments. Leave one!";
}else{
  ?>
  <h3 align="center">Comments</h3>

      <?php foreach($comments as $c){
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
              <div class='media-heading bg-default'>
                <a href="#"><strong><?php echouser($c->user);?></strong></a> at
                <?=$c->timestamp?>
              </div>
              <div class='media-content'>
                <?=$c->comment?>
              </div>

                	            <!-- <a class="float-right btn btn-outline-primary ml-2"> <i class="fa fa-reply"></i> Reply</a>
                	            <a class="float-right btn text-white btn-danger"> <i class="fa fa-heart"></i> Like</a> -->

            </div>
        </div>
      <?php } ?>

  <?php
}
}

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
