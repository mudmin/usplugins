<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
if(!pluginActive('forum',true)){die;}
if(!$write){
  Redirect::to($currentPage."?board=".$board."&err=Board+locked");
}


$b = $db->query("SELECT * FROM forum_boards WHERE id = ?",[$board])->first();
if(!empty($_POST)){
  $token = $_POST['csrf'];
  if(!Token::check($token)){
    include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
  }
  $message=trim(Input::get('message'));
  if($message != ""){
    $fields = array(
      'board'=>$board,
      'created_by'=>$uid,
      'created_on'=>date("Y-m-d H:i:s"),
      'title'=>Input::get('title'),
      'last'=>date("Y-m-d H:i:s"),
    );
    $db->insert("forum_threads",$fields);
    $thread = $db->lastId();
    $fields = [
      'message'=>$message,
      'thread'=>$thread,
      'user_id'=>$uid,
      'created_on'=>date("Y-m-d H:i:s"),
      'board'=>$board,
      'replying_to'=>0,
      'ip'=>ipCheck(),
    ];
    $db->insert("forum_messages",$fields);
    $msgid = $db->lastId();
    $db->update("forum_threads",$thread,['post'=>$msgid]);
    $db->update("forum_boards",$board,['last'=>date("Y-m-d H:i:s")]);
    Redirect::to($currentPage."?board=".$board."&thread=".$thread);
  }

}
?>

<div class="row">
  <div class="col-12 card bg-light" style="padding: 1em; padding-left:3em">
      <form class="" action="" method="post">
        <div class="row">
          <div class="col-12 col-sm-9">
          <h3>Start New Topic in <?=$b->board?></h3>
          </div>
          <div class="col-12 col-sm-3 text-end">
            <a href="<?=$currentPage?>?board=<?=$board?>" class="btn btn-primary">Back</a>
          </div>
        </div>
        
      </div>
    </div>
    <div class="row">
      <div class="col-12 card" style="padding: 3em; ">
          <input type="hidden" name="csrf" value="<?=Token::generate();?>">
          <label for="">Subject </label>
          <input class="form-control" type="text" name="title" value="" maxlength="100" required>
          <br>
          <label for="">Message </label>
          <textarea name="message" rows="8" class="tiny"></textarea>
          <input type="submit" name="submitPost" value="Post" class="btn btn-primary mt-3">
      </form>

  </div>
</div>
