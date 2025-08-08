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
<div class="row my-2">
  <div class="col-md-6 text-start">
    <a href="<?=$currentPage?>?board=<?=$board?>" class="btn btn-outline-primary">Return to Topics</a>
  </div>
  <?php if($write): 
    $link = $currentPage."?board=".$board."&view=new";
    ?>
    <div class="col-md-6 text-end">
    <a href="<?=$link?>" class="btn btn-outline-primary">Post New Topic</a>
    </div>
  <?php endif; ?>
</div>

<h3 class="text-center"><?=$b->board?> - <?=$t->title?></h3>
<div class="table-responsive">
  <table class="table table-hover">
    <thead>
      <th style="width:20%"></th>
      <th style="width:80%"></th>
    </thead>
    <tbody>
  <?php foreach($msg as $m) {
    $parentid = $m->id;
    ?>
    <tr>
      <!-- Left column for user info and profile pic -->
      <td class="align-middle text-center">
        <?php if(pluginActive("profile_pic", true)) {
          if(isset($images[$m->user_id])) {
            $img = $images[$m->user_id];
          } else {
            $uQ = $db->query("SELECT profile_pic FROM users WHERE id = ?", [$m->user_id]);
            $uC = $uQ->count();
            $u = $uQ->results();
            if($uC < 1 || $u[0]->profile_pic == "") {
              if(file_exists($abs_us_root.$us_url_root."usersc/plugins/forum/custom/av.jpg")) {
                $img = $us_url_root."usersc/plugins/forum/custom/av.jpg";
              } else {
                $img = $us_url_root."usersc/plugins/forum/assets/av.jpg";
              }
            } else {
              $img = $us_url_root."usersc/plugins/profile_pic/files/".$u[0]->profile_pic;
            }
            $images[$m->user_id] = $img;
          }
        ?>
          <img src="<?=$img?>" alt="" class="img-fluid rounded-circle" style="max-width: 60px;">
        <?php } ?>
        <div class="mt-2">
          <span class="fw-bold text-primary"><?= echouser($m->user_id); ?></span><br>
          <?php 
          $count = $db->query("SELECT COUNT(*) AS c FROM forum_messages WHERE user_id = ? AND disabled = 0", [$m->user_id])->first();
          echo $count->c; 
          echo ($count->c == 1) ? " post" : " posts";
          ?>
        </div>
      </td>

      <!-- Right column for message and reply -->
      <td>
        <div class="d-flex flex-column">
          <div>
            <span class="badge bg-primary">#<?=$counter?></span>
            <span class="ms-2 text-muted">Re: <?=$t->title?></span>
          </div>
          <small class="text-muted"><?= $m->created_on ?></small>

          <?php if($is_mod) { ?>
            <div class="mt-2">
              <form class="d-inline" action="" method="post">
                <input type="hidden" name="csrf" value="<?=Token::generate();?>">
                <input type="hidden" name="modHook" value="1">
                <input type="hidden" name="msg" value="<?=$m->id?>">
                <button type="submit" name="deletePost" class="btn btn-outline-danger btn-sm">Delete Post</button>
                <button type="submit" name="deleteThread" class="btn btn-danger btn-sm">Delete Thread</button>
                <?php if($can_ban && !hasPerm([2], $m->user_id)) { ?>
                  <input type="hidden" name="banhammer" value="<?=$m->user_id?>">
                  <input type="submit" name="banUser" class="btn btn-warning btn-sm" value="Ban <?= echouser($m->user_id); ?>">
                  <input type="submit" name="purgeUser" class="btn btn-warning btn-sm" value="Ban & Purge <?= echouser($m->user_id); ?>">
                <?php } ?>
              </form>
            </div>
          <?php } ?>

          <hr>
          <p><?= $m->message; ?></p>
          <?php
          if($write){ ?>
            <div class="text-right text-end">
            <button type="button" class="btn btn-primary replyButton" data-toggle="modal" data-bs-toggle="modal" data-target="#replyModal" data-bs-target="#replyModal" data-reply="<?=$parentid?>">
              Reply To This Message
            </button>
          </div>
          <?php } ?>

          <?php
          // Replies logic
          $repliesQ = $db->query("SELECT * FROM forum_messages WHERE board = ? AND thread = ? AND disabled = 0 AND replying_to = ?", [$board, $thread, $m->id]);
          $repliesC = $repliesQ->count();
          if($repliesC > 0) {
            $replies = $repliesQ->results();
            echo "<strong class='text-primary'>Replies</strong><br>";
            foreach($replies as $r) { ?>
              <div class="row mb-2">
                <!-- Left column for user info and profile pic -->
                <div class="col-md-3 offset-md-1">
                  <?php 
                  if(pluginActive("profile_pic", true)) {
                    if(isset($images[$r->user_id])) {
                      $img = $images[$r->user_id];
                    } else {
                      $uQ = $db->query("SELECT profile_pic FROM users WHERE id = ?", [$r->user_id]);
                      $uC = $uQ->count();
                      if($uC < 1 || empty($uQ->results()[0]->profile_pic)) {
                        $img = file_exists($abs_us_root.$us_url_root."usersc/plugins/forum/custom/av.jpg") ? 
                               $us_url_root."usersc/plugins/forum/custom/av.jpg" :
                               $us_url_root."usersc/plugins/forum/assets/av.jpg";
                      } else {
                        $img = $us_url_root."usersc/plugins/profile_pic/files/".$uQ->results()[0]->profile_pic;
                      }
                      $images[$r->user_id] = $img;
                    }
                  ?>
                    <img src="<?=$img?>" alt="" class="img-fluid rounded-circle" style="max-width: 60px;">
                  <?php } ?>
                  <div class="mt-2">
                    <span class="fw-bold text-primary"><?= echouser($r->user_id); ?></span><br>
                    <?php 
                    $count = $db->query("SELECT COUNT(*) AS c FROM forum_messages WHERE user_id = ? AND disabled = 0", [$r->user_id])->first();
                    echo $count->c . " " . ($count->c == 1 ? "post" : "posts");
                    ?>
                  </div>
                </div>
            
                <!-- Right column for reply content -->
                <div class="col-md-8">
                  <div>
                    <span class="badge bg-secondary">#<?=$counter?></span>
                    <span class="ms-2 text-muted">Re: <?=$t->title?></span>
                  </div>
                  <small class="text-muted"><?=$r->created_on?></small>
            
                  <hr>
                  <p><?=$r->message?></p>
                </div>
              </div>
            <?php $counter++; } ?>
            
            <?php } ?>

          </td>
        </tr>
      <?php } ?>
    <?php $counter++;  ?>

  <!-- General Reply form row -->
  <tr>
    <td></td>
    <td>
      <?php if($write) { ?>
        <div class="card bg-light mt-3">
          <div class="card-body">
            <h5 class="card-title">Leave a General Reply</h5>
            <form action="" method="post">
              <input type="hidden" name="csrf" value="<?=Token::generate();?>">
              <textarea name="message" rows="4" class="form-control"></textarea>
              <button type="submit" name="submitReply" class="btn btn-primary mt-2">Post</button>
            </form>
          </div>
        </div>
      <?php } ?>
    </td>
  </tr>
</tbody>
</table>
</div>

<?php if($write){?>
<!-- Reply Modal -->
<div class="modal fade" id="replyModal" tabindex="-1" aria-labelledby="replyModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="replyModalLabel">Reply</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form class="" action="" method="post">
          <span id="replyToModal"></span>
          <input type="hidden" name="csrf" value="<?=Token::generate();?>">
          <input type="hidden" name="replyTo" value="0" id="replyTo">
          <textarea name="message" id="replyToMessage" rows="8" class="form-control"></textarea>
          <!--  -->
      
      </div>

      <!-- Modal footer -->
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <input type="submit" name="submitReply" value="Post Reply" class="btn btn-primary">
  
      </div>
      </form>
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

$(document).ready(function() {
    $('#replyModal').on('shown.bs.modal', function() {
        $('#replyToMessage').focus();
    });
});


</script>
<?php } ?>
