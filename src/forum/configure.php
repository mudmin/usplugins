  <?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

<?php
include "plugin_info.php";
pluginActive($plugin_name);
 if(!empty($_POST)){
   $token = $_POST['csrf'];
if(!Token::check($token)){
  include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
}
if(!empty($_POST['createBoard'])){
  $read = Input::get('read');
  $write = Input::get('write');
  $to_read = "";
  $to_write = "";
  foreach($read as $r){
    if($r == -1){$r = 0;}
    $to_read .= $r.",";
  }
  foreach($write as $r){
    if($r == -1){$r = 0;}
    $to_write .= $r.",";
  }
  $fields = array(
    'board'=>Input::get('board'),
    'descrip'=>Input::get('descrip'),
    'cat'=>Input::get('cat'),
    'to_read'=>$to_read,
    'to_write'=>$to_write,
  );
  $db->insert("forum_boards",$fields);
  Redirect::to("admin.php?view=plugins_config&plugin=forum&err=Board+created");
}

if(!empty($_POST['createCategory'])){
  $fields = array(
    'category'=>Input::get('category'),
  );
    $db->insert("forum_categories",$fields);
  Redirect::to("admin.php?view=plugins_config&plugin=forum&err=Category+created");
}
}
 $token = Token::generate();
 $boards = $db->query("SELECT * FROM forum_boards")->results();
 $cats = $db->query("SELECT * FROM forum_categories ORDER BY category")->results();
 $permissions = $db->query("SELECT * FROM permissions")->results();
 ?>
 <style media="screen">
   p {color:black;}
 </style>
<div class="content mt-3">
 		<div class="row">
 			<div class="col-sm-12">
          <a href="<?=$us_url_root?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
          <div class="row">
            <div class="col-12 col-sm-6">
              <h4>Create a New Category</h4><br>
              <form class="" action="" method="post">
                <input type="hidden" name="csrf" value="<?=$token?>" />
                  <label for="">Category Name</label><br>
                <input type="text" name="category" value="" class="form-control" required>
                <br>
                <input type="submit" name="createCategory" value="Create Category" class="btn btn-primary">
              </form>
            </div>



            <div class="col-12 col-sm-6">
              <h4>Create a New Board</h4><br>
              <form class="" action="" method="post">
                <input type="hidden" name="csrf" value="<?=$token?>" />
                <label for="">Category</label><br>
                <select class="form-control" name="cat" required>
                  <option value="" disabled selected="selected">--Choose a Category--</option>
                    <?php foreach($cats as $p){
                      if($p->deleted == 1){continue;}
                      ?>
                        <option value="<?=$p->id?>"><?=$p->category?></option>
                    <?php } ?>
                </select>
                <label for="">Board Name</label><br>
                <input type="text" name="board" value="" class="form-control">
                <label for="">Board Description</label><br>
                <input type="text" name="descrip" value="" class="form-control">
                <label for="">Permission levels allowed to read this board</label><br>
                <input type="checkbox" name="read[]" value="-1"> Public
                <?php foreach($permissions as $p){ ?>
                  <input type="checkbox" name="read[]" value="<?=$p->id?>"> <?=$p->name?>(<?=$p->id?>)
                <?php } ?>
                <br><br>
                <label for="">Permission levels allowed to post to board</label><br>
                <?php foreach($permissions as $p){ ?>
                  <input type="checkbox" name="write[]" value="<?=$p->id?>"> <?=$p->name?>(<?=$p->id?>)
                <?php } ?>
                <br>
                <input type="submit" name="createBoard" value="Create Board" class="btn btn-primary">
              </form>
            </div>
          </div>

          <div class="row">
            <div class="col-12 col-sm-6">
              <h4>Manage Categories</h4>
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>Category</th>
                    <th>Deleted?</th>
                    <th>Edit</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach($cats as $c){?>
                    <tr>
                      <td><?=$c->category?></td>
                      <td><?=bin($c->deleted);?></td>
                      <td>Future</td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
          </div>

          <div class="col-12 col-sm-6">
            <h4>Manage Boards</h4>
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>Board</th>
                  <th>Deleted?</th>
                  <th>Edit</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach($boards as $b){?>
                  <tr>
                    <td><?=$b->board?></td>
                    <td><?=bin($b->disabled);?></td>
                    <td>Future</td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>
        </div>
        </div>
        <div class="row">
          <div class="col-12">
            <h3>The Basics</h3>
            <p>
              <strong>Categories</strong> contain boards.  <strong>Boards</strong> are general topics. <strong>Threads</strong> are conversations inside a board.  <strong>Messages</strong> are individual posts inside a thread.
            </p>

            <p>
              Create at least one category above and then at least one board.  When you create a board you get to decide which of your UserSpice permissions can write to it and who can read it.
            </p>

            <h3>Making a page for the forums</h3>
            <p>
              It is recommended that you take the blank page in <strong>users/_blank_pages/project_root.php</strong> and copy it to the root of your project in the same folder as z_us_root.php.  <br>
              Rename it whatever you want.<br>
              Delete all the divs in that file <br>
              add the following line inside the //php goes here section<br>
              require_once $abs_us_root.$us_url_root.'usersc/plugins/forum/forum.php';<br>
              The first time you visit that page when logged in as admin, you will be redirected to the page manager. If you want your forum to be publicly viewable, make sure NOT to mark the page as private. If you want
              only logged in users to be able to access the forum, mark it private and choose which users can use the forum itself. Side note. This plugin also works with the profile_pic plugin if you have that enabled.<br>
            </p>
            <p>
              <strong>Please note:</strong> this page permission overrides the permissions you set in the plugin. In other words, no matter who you say can view a forum or write to it, they cannot do it if they're blocked
              by the permission check.  So there are GENERALLY 2 ways you could configure this page. Either not private, or private with permission level 1(user).
            </p>
            <h3>Customizing</h3>
            <p>There are 3 ways to customize this plugin to your liking.<br>
              1. Fork It - Just copy usersc/plugins/forum to usersc/plugins/yourforum.  You will want to open the plugin in your code editor and do a find/replace for plugins/forum with plugins/yourforum but everything should work fine after that.<br><br>
              2. Contribute to it - I haven't spent a ton of time on design and if you want to customize this and make it better, reach out to me.<br><br>
              3. Use the special files - If you look in usersc/plugins/forum/assets there are all the files that perform all the views for the forum. If you copy one of those to usersc/plugins/forum/custom folder yours will be loaded instead of ours. Go wild.
            </p>
            <h3>Updates</h3>
            <p>Writing free plugins that I don't have a personal use for is a lot of work.  I love doing it, but your feedback and support helps.  You can support by testing the plugin.  You can support with bug reports and fixes.

            You can support by donating at <a href="https://userspice.com/donate/">Donate</a>.  Regardless, I'm thrilled that you found this plugin and your feedback/support help ensure this plugin gets updated.</p>

            <h3>Warranty</h3>
            <p>Letting people post content to your database that is visible to others always carries risk. Both the words they use and the code they upload.  Be careful. If this plugin gets some love, I will develop more moderation tools.</p>
          </div>
        </div>
 			</div> <!-- /.col -->
 		</div> <!-- /.row -->
