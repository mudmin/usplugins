<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
if(!pluginActive('forum',true)){die;}
if(!$read){
  Redirect::to($currentPage."?err=Board+not+available");
}



$b = $db->query("SELECT * FROM forum_boards WHERE id = ? AND disabled = 0",[$board])->first();
$t = $db->query("SELECT * FROM forum_threads WHERE id = ? AND deleted = 0",[$thread])->first();
$msgQ = $db->query("SELECT * FROM forum_messages WHERE board = ? AND thread = ? AND disabled = 0 AND replying_to = 0",[$board,$thread]);
$msgC = $msgQ->count();
$msg = $msgQ->results();
$images = []; //store this to lessen the number of queries
$counter = 1;
// $fp = $db->query("SELECT * FROM forum")

if($is_mod && !empty($_POST['modHook'])){
  $token = $_POST['csrf'];
  if(!Token::check($token)){
    include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
  }

  $msg = Input::get('msg');

  $checkQ = $db->query("SELECT * FROM forum_messages WHERE id = ?",[$msg]);
  $checkC = $checkQ->count();
  if($checkC < 1){
    Redirect::to('forum.php?board='.$board.'&thread='.$thread.'&err=Cannot+find+message');
  }

  if(isset($_POST['deletePost'])){
    $check = $checkQ->first();
    $db->update('forum_messages',$msg,['message'=>"{{{Deleted}}}",'disabled'=>1]);
    logger($user->data()->id,"Forum Moderation", "Deleted message $msg by user $check->user_id");
    Redirect::to('forum.php?board='.$board.'&thread='.$thread.'&err=Message+deleted');
  }
  if(isset($_POST['deleteThread'])){
    $check = $checkQ->first();
    $db->update('forum_threads',$check->thread,['title'=>"{{{Deleted}}}",'deleted'=>1]);
    $msgs = $db->query("SELECT * FROM forum_messages WHERE thread = ?",[$check->thread])->results();
    foreach($msgs as $m){
      $db->update('forum_messages',$m->id,['message'=>"{{{Deleted}}}",'disabled'=>1]);
    }
    logger($user->data()->id,"Forum Moderation", "Deleted thread $check->thread by user $check->user_id");
    Redirect::to('forum.php?board='.$board.'&err=Thread+deleted');
  }
  if($can_ban){
    $bh = Input::get('banhammer');
    if(hasPerm([2],$bh)){
      logger($user->data()->id,"Forum Moderation", "Tried to banhammer admin $bh");
      Redirect::to('forum.php?board='.$board.'&thread='.$thread.'&err=You+cannot+ban+an+admin!');
    }
    if(isset($_POST['banUser'])){
      $check = $checkQ->first();
      $db->update('users',$bh,['permissions'=>0]);

      logger($user->data()->id,"Forum Moderation", "Banned $bh");
      Redirect::to('forum.php?board='.$board.'&thread='.$thread.'&err=Member Banned');
    }
    if(isset($_POST['purgeUser'])){
      $check = $checkQ->first();
      $db->update('users',$bh,['permissions'=>0]);
      $msgs = $db->query("SELECT * FROM forum_messages WHERE user_id = ?",[$bh])->results();
      foreach($msgs as $m){
        $db->update('forum_messages',$m->id,['message'=>"{{{Deleted}}}",'disabled'=>1]);
      }
      logger($user->data()->id,"Forum Moderation", "Purged $bh");
      Redirect::to('forum.php?board='.$board.'&thread='.$thread.'&err=Member Purged');
    }
  }


}

if(!empty($_POST) && $write){
  $token = $_POST['csrf'];
  if(!Token::check($token)){
    include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
  }
  if(!empty($_POST['submitReply'])){
  $message=trim(Input::get('message'));
  $replyTo = Input::get("replyTo");
  if($replyTo > 0){
    $check = $db->query("SELECT id FROM forum_messages WHERE id = ? AND board = ? AND thread = ?",[$replyTo,$board,$thread])->count();
    if($check < 1){ $replyTo = 0; }
  }
    $fields = [
      'message'=>$message,
      'thread'=>$thread,
      'user_id'=>$user->data()->id,
      'created_on'=>date("Y-m-d H:i:s"),
      'board'=>$board,
      'replying_to'=>$replyTo,
      'ip'=>ipCheck(),
    ];
    $db->insert("forum_messages",$fields);
    $msgid = $db->lastId();
    $db->update("forum_threads",$thread,['last'=>date("Y-m-d H:i:s")]);
    $db->update("forum_boards",$board,['last'=>date("Y-m-d H:i:s")]);
    Redirect::to($currentPage."?board=".$board."&thread=".$thread);
  }
 }
?>
<div class="row">
  <div class="col-6 text-left">
      <button type="button" onclick="window.location.href = '<?=$currentPage?>?board=<?=$board?>';" name="button" class="btn btn-primary">Return to Topics</button>
  </div>
  <?php
  if($write){
    $link = $currentPage."?board=".$board."&view=new";
    ?>
    <div class="col-6 text-right">
      <button type="button" onclick="window.location.href = '<?=$link?>';" name="button" class="btn btn-primary">Post New Topic</button>
    </div>
  <?php } ?>
</div>

<h3 align="center"><?=$b->board?> - <?=$t->title?></h3>
<div class="">
  <table class="table">
    <thead>
      <th style="width:20%"></th>
      <th style="width:80%"></th>
    </thead>
    <tbody>
      <?php foreach($msg as $m){
        $parentid = $m->id;
        ?>
        <tr>
          <!-- left column -->
          <td class="text-center">
            <?php if(pluginActive("profile_pic",true)){

              if(isset($images[$m->user_id])){
                $img = $images[$m->user_id];
              }else{
                $uQ = $db->query("SELECT profile_pic FROM users WHERE id = ?",[$m->user_id]);
                $uC = $uQ->count();
                $u = $uQ->results();
                if($uC < 1 || $u[0]->profile_pic == ""){
                  if(file_exists($abs_us_root.$us_url_root."usersc/plugins/forum/custom/av.jpg")){

                    $img = $us_url_root."usersc/plugins/forum/custom/av.jpg";
                  }else{
                    $img = $us_url_root."usersc/plugins/forum/assets/av.jpg";
                  }
                }else{

                  $img = $us_url_root."usersc/plugins/profile_pic/files/".$u[0]->profile_pic;

                }
                $images[$m->user_id] = $img;
              }

              ?>

              <img src="<?=$img?>" alt="" class="img-fluid" style="max-width:20%;">
            <?php } ?>
            <div class="text-primary">
              <?=echouser($m->user_id);?><br>
            </div>
            <?php $count = $db->query("SELECT COUNT(*) AS c FROM forum_messages WHERE user_id = ? AND disabled = 0 ",[$m->user_id])->first();
            echo $count->c;
            if($count->c == 1){echo " post";}else{echo " posts";}
            ?>
          </td>
          <td>
            <!-- right column -->
            <div class="row">
              <div class="col-12">
                <?="#".$counter?> -
                <font color="text-primary">Re: <?=$t->title?></font><br>
                <?php
                echo $m->created_on;
                $counter++;

                if($is_mod){ ?>
                  <form class="" action="" method="post">
                    <input type="hidden" name="csrf" value="<?=Token::generate();?>">
                    <input type="hidden" name="modHook" value="1">
                    <input type="hidden" name="msg" value="<?=$m->id?>">
                    <input type="submit" name="deletePost" value="Delete Post">
                    <input type="submit" name="deleteThread" value="Delete Thread">
                    <?php if($can_ban && !hasPerm([2],$m->user_id)){?>
                    <input type="hidden" name="banhammer" value="<?=$m->user_id?>">
                    <input type="submit" name="banUser" value="Ban <?php echouser($m->user_id);?>">
                    <input type="submit" name="purgeUser" value="Ban & Purge <?php echouser($m->user_id);?>">
                  <?php } ?>
                  </form>
                <?php } ?>

              </div>
            </div>
            <hr>
            <?=htmlspecialchars_decode(stripslashes($m->message)); ?>

            <?php
            $repliesQ = $db->query("SELECT * FROM forum_messages WHERE board = ? AND thread = ? AND disabled = 0 AND replying_to = ?",[$board,$thread,$m->id]);
            $repliesC = $repliesQ->count();
            if($repliesC > 0){
              $replies = $repliesQ->results();
              ?>
              <strong class="text-primary">Replies</strong><br>
                  <?php foreach($replies as $m){?>
                    <div class="row">
                      <div class="col-3 offset-1">
                    <?php if(pluginActive("profile_pic",true)){

                      if(isset($images[$m->user_id])){
                        $img = $images[$m->user_id];
                      }else{
                        $uQ = $db->query("SELECT profile_pic FROM users WHERE id = ?",[$m->user_id]);
                        $uC = $uQ->count();
                        $u = $uQ->results();
                        if($uC < 1 || $u[0]->profile_pic == ""){
                          if(file_exists($abs_us_root.$us_url_root."usersc/plugins/forum/custom/av.jpg")){

                            $img = $us_url_root."usersc/plugins/forum/custom/av.jpg";
                          }else{
                            $img = $us_url_root."usersc/plugins/forum/assets/av.jpg";
                          }
                        }else{

                          $img = $us_url_root."usersc/plugins/profile_pic/files/".$u[0]->profile_pic;

                        }
                        $images[$m->user_id] = $img;
                      }

                      ?>

                      <img src="<?=$img?>" alt="" class="img-fluid" style="max-width:20%;">
                    <?php } ?>
                    <div class="text-primary">
                      <?=echouser($m->user_id);?><br>
                    </div>
                    <?php $count = $db->query("SELECT COUNT(*) AS c FROM forum_messages WHERE user_id = ? AND disabled = 0",[$m->user_id])->first();
                    echo $count->c;
                    if($count->c == 1){echo " post";}else{echo " posts";}
                    ?>
                  </div>
                  <div class="col-8">
                    <?="#".$counter?> -
                    <font color="text-primary">Re: <?=$t->title?></font><br>
                    <?php
                    echo $m->created_on;
                    $counter++;
                    ?>
                  </div>
                <hr>
              </div>
              <br>
              <div class="row">
                  <div class="col-8 offset-4">
                    <?=htmlspecialchars_decode(stripslashes($m->message)); ?>
                  </div>
              </div>
                  <?php } //end foreach replies ?>
            <?php } ?>
            <?php
            if($write){ ?>
            <div class="text-right">
            <button type="button" class="btn btn-primary replyButton" data-toggle="modal" data-target="#replyModal" data-reply="<?=$parentid?>">
              Reply To This Message
            </button>
          </div>
          <?php } ?>
          </td>
        </tr>
      <?php } ?>
      <tr>
        <td></td>
        <td>
        <?php if($write){?>
          <div class="row">
            <div class="col-12 card bg-light" style="padding: 1em;">
              <form class="" action="" method="post">
                <h3>Leave a General Reply</h3>
                <input type="hidden" name="csrf" value="<?=Token::generate();?>">
                <textarea name="message" rows="8" class="tiny"></textarea>
                <input type="submit" name="submitReply" value="Post" class="btn btn-primary btn-block">
              </form>
            </div>
          </div>
          <script src='https://cdn.tinymce.com/4/tinymce.min.js'></script>
          <script>
          $(document).ready(function(){
            tinymce.init({
              selector: '.tiny'
            });
          $(".mce-branding").hide();
          });
        </script>
      <?php } ?>
      </td>
    </tr>
  </tbody>
</table>
</div>

<?php if($write){?>
<!-- The Modal -->
<div class="modal" id="replyModal">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title">Reply</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>

      <!-- Modal body -->
      <div class="modal-body">
        <form class="" action="" method="post">
          <span id="replyToModal"></span>
          <input type="hidden" name="csrf" value="<?=Token::generate();?>">
          <input type="hidden" name="replyTo" value="0" id="replyTo">
          <textarea name="message" rows="8" class="tiny"></textarea>
          <input type="submit" name="submitReply" value="Post" class="btn btn-primary btn-block">
        </form>
      </div>

      <!-- Modal footer -->
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>

<script type="text/javascript">

$(".replyButton").on('click', function () {
  var reply = $(this).attr("data-reply");
  console.log(reply);
  $("#replyTo").val(reply);
  // $("#replyToModal").html(reply);
});


</script>
<?php } ?>
