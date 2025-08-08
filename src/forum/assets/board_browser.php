<?php if(count(get_included_files()) == 1) die(); // Direct Access Not Permitted
if(!pluginActive('forum', true)) { die; }
$cats = $db->query("SELECT * FROM forum_categories WHERE deleted = 0")->results();

foreach($cats as $c) { ?>
  <div class="row my-3">
    <div class="col-12">
      <div class="card bg-<?=$bColor?> shadow-sm">
        <div class="card-body text-<?=$hColor?>">
          <h2 class="card-title"><?=$c->category?></h2>
        </div>
      </div>
    </div>
  </div>

  <?php
  $boards = $db->query("SELECT * FROM forum_boards WHERE cat = ? AND disabled = 0", [$c->id])->results();
  foreach($boards as $b) {
    if(!forumAccess($b->id, 'read', $uid)) { continue; }
    $lp = forumLastPost($b->id, "boards");
    ?>
    <div class="row mb-2">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <div class="row">
              <div class="col-md-8">
                <h4 class="card-title"><a href="<?=$currentPage?>?view=board&board=<?=$b->id?>"><?=$b->board?></a></h4>
                <p class="card-text"><?=$b->descrip?></p>
                <p class="card-text">
                  Last Post: 
                  <?php if($lp['id'] != 0) { ?>
                    <a href="<?=$currentPage?>?board=<?=$b->id?>&thread=<?=$lp['thread']?>"><?=$lp['title']?></a> on <?=$lp['date']?>.
                  <?php } else {
                    echo "Never";
                  } ?>
                </p>
              </div>
              <div class="col-md-2 text-center">
                <p class="card-text"><strong><?= forumCount($b->id, "messages") ?></strong> Posts</p>
              </div>
              <div class="col-md-2 text-center">
                <p class="card-text"><strong><?= forumCount($b->id, "threads") ?></strong> Topics</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  <?php } ?>
<?php } ?>
