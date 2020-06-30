<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
$cats = $db->query("SELECT * FROM forum_categories WHERE deleted = 0")->results();

foreach($cats as $c){ ?>
  <div class="row">
    <div class="col-12 card bg-<?=$bColor?> text-middle">
      <h2 style=""><font color="<?=$hColor?>"><?=$c->category?></font></h2>
    </div>
  </div>

  <?php
  $boards = $db->query("SELECT * FROM forum_boards WHERE cat = ? AND disabled = 0",[$c->id])->results();
  foreach($boards as $b){
    if(!forumAccess($b->id,'read',$uid)){continue;}
    $lp = forumLastPost($b->id,"boards");
    ?>
    <div class="row">
      <div class="col-12 card">
        <div class="row">
          <div class="col-7 offset-1">
            <h4><a href="<?=$currentPage?>?view=board&board=<?=$b->id?>"><?=$b->board?></a></h4>
            <h5><?=$b->descrip?><h5>
            Last Post:
            <?php if($lp['id'] != 0){ ?>
              <a href="<?=$currentPage?>?thread=<?=$lp['thread']?>&msg=<?=$lp['id']?>"><?=$lp['title']?></a> on <?=$lp['date']?>.
            <?php
            }else{
            echo "Never";
            }
            ?>
          </div>
        <div class="col-2"><br>
          <?php echo forumCount($b->id,"messages"); ?> Posts<br>
        </div>
        <div class="col-2"><br>
          <?php echo forumCount($b->id,"threads"); ?> Topics
        </div>
      </div>
      </div>
    </div>
  <?php } ?>
<?php } ?>
