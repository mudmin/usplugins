<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
if(!pluginActive('forum',true)){die;}
if(!forumAccess($board,"read",$uid)){
  Redirect::to($currentPage."?err=Board+not+available");
}

$b = $db->query("SELECT * FROM forum_boards WHERE id = ? AND disabled = 0",[$board])->first();
$threadsQ = $db->query("SELECT * FROM forum_threads WHERE board = ? AND deleted = 0 ORDER BY last DESC",[$board]);
$threadsC = $threadsQ->count();
$threads = $threadsQ->results();
?>

<div class="row my-3">
  <div class="col-6 text-start">
    <a href="<?=$currentPage?>" class="btn btn-outline-primary">Return to Categories</a>
  </div>
  <?php
  if($write){
    $link = $currentPage."?board=".$board."&view=new";
    ?>
    <div class="col-6 text-right text-end">
    <a href="<?=$link?>" class="btn btn-outline-primary">Post New Topic</a>
    </div>
  <?php } ?>
</div>

<h3 class="text-center"><?=$b->board?></h3>
<div class="table-responsive">
<table class="table table-striped table-hover table-borderless">
    <thead class="thead-dark">
      <th>Subject</th><th>Started By</th><th>Posts</th><th>Last Post</th>
    </thead>
    <tbody>
    <?php foreach($threads as $t){?>
      <tr>
        <td><a href="<?=$currentPage?>?board=<?=$board?>&thread=<?=$t->id?>"><?=$t->title?></a></td>
        <td><?php echouser($t->created_by);?></td>
        <td><?=$db->query("SELECT id FROM forum_messages WHERE thread = ? AND disabled = 0",[$t->id])->count();?></td>
        <td><?=$t->last?></td>
      </tr>
      <?php } ?>
    </tbody>
</table>
</div>
