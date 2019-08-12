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
<form class="" action="" method="post">
  <input type="hidden" value="<?=$token;?>" name="csrf">
  Post your comment<br>
  <textarea name="comment" rows="4" cols="120"></textarea><br>
  <input type="submit" name="submitComment" value="Post Comment">
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
        $thatUserQ = $db->query("SELECT email FROM users WHERE id = ?",array($c->user));
        $thatUserC = $thatUserQ->count();
        if($thatUserC > 0){
          $thatUser = $thatUserQ->first();

        $grav = get_gravatar(strtolower(trim($thatUser->email)));
      	$useravatar = '<img src="'.$grav.'" class="img img-thumbnail img-fluid">';
        }
        ?>
        <div class="row">
              	    <div class="col-md-2">
                      <?php
                      if($thatUserC > 0){
                      echo $useravatar;
                    } ?>
              	    </div>
              	    <div class="col-md-10">
              	        <p>
              	            <a class="float-left" href="#"><strong><?php echouser($c->user);?></strong></a> at
                            <?=$c->timestamp?>
              	       </p>
              	       <div class="clearfix"></div>
              	        <p><?=$c->comment?></p>
              	        <p>
              	            <!-- <a class="float-right btn btn-outline-primary ml-2"> <i class="fa fa-reply"></i> Reply</a>
              	            <a class="float-right btn text-white btn-danger"> <i class="fa fa-heart"></i> Like</a> -->
              	       </p>
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
