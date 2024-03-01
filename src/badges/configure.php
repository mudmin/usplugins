<?php if (!in_array($user->data()->id, $master_account)) {
  Redirect::to($us_url_root . 'users/admin.php');
} //only allow master accounts to manage plugins! 

include "plugin_info.php";
pluginActive($plugin_name);
$plgset = $db->query("SELECT * FROM plg_badges_settings")->first();
$uwab = Input::get('uwab');
$search = Input::get('search');
$cats = $db->query("SELECT * FROM plg_badges_cats")->results();

//find all badges beginning with perm_ in folder
$perm_badges = glob($abs_us_root . $us_url_root . $plgset->badge_location . "perm_*.png");
$tag_badges = glob($abs_us_root . $us_url_root . $plgset->badge_location . "tag_*.png");



if (!empty($_POST)) {
  $token = $_POST['csrf'];
  if (!Token::check($token)) {
    include($abs_us_root . $us_url_root . 'usersc/scripts/token_error.php');
  }

  if (!empty($_POST['createCat'])) {
    $cat = Input::get('cat');
    $c = $db->query("SELECT * FROM plg_badges_cats WHERE cat = ?", [$cat])->count();
    if ($c < 1) {
      $db->insert("plg_badges_cats", ['cat' => $cat]);
      usSuccess("Category Created");
      Redirect::to($us_url_root . "users/admin.php?view=plugins_config&plugin=badges");
    } else {
      usError("Category already exists");
      Redirect::to($us_url_root . "users/admin.php?view=plugins_config&plugin=badges");
    }
  }

  if (!empty($_POST['changeBadgeLocation'])) {
    $location = Input::get('badge_location');
    if (substr($location, -1) != "/" && substr($location, -1) != "\\") {
      $location .= "/";
    }

    $db->update("plg_badges_settings", 1, ['badge_location' => $location]);
    // check if exists
    if (!is_dir($abs_us_root . $us_url_root . $location)) {
      usError("WARNING: The directory you specified did not exist. We attempted to create it, but you should check to make sure it is correct.");
      mkdir($abs_us_root . $us_url_root . $location, 0755, true);
    }
    usSuccess("Path Changed");
    Redirect::to($us_url_root . "users/admin.php?view=plugins_config&plugin=badges");
  }

  if (!empty($_POST['newBadge'])) {
    $b = Input::get('badge');
    $c = $db->query("SELECT * FROM plg_badges WHERE badge = ?", [$b])->count();
    if ($c > 0) {
      usError("This name is already in use");
      Redirect::to($us_url_root . "users/admin.php?view=plugins_config&plugin=badges");
    } else {

      $db->insert("plg_badges", ['badge' => $b, 'cat_id' => Input::get('cat')]);
      usSuccess("Badge Created");
      Redirect::to($us_url_root . "users/admin.php?view=plugins_config&plugin=badges");
    }
  }

  if (!empty($_POST['changeBadges'])) {
    $names = Input::get('badge');
    $failed = false;
    foreach ($names as $k => $v) {
      $c = $db->query("SELECT * FROM plg_badges WHERE badge = ? AND id != ?", [$v, $k])->count();
      if ($c < 1 && $v != "") {
        $db->update("plg_badges", $k, ['badge' => $v]);
      } else {
        $failed = true;
      }
    }
    if (!$failed) {
      usSuccess("Changes Saved");
      Redirect::to($us_url_root . "users/admin.php?view=plugins_config&plugin=badges");
    } else {
      usError("Names must be unique");
      Redirect::to($us_url_root . "users/admin.php?view=plugins_config&plugin=badges");
    }
  }

  if (!empty($_POST['delBadge'])) {
    $b = Input::get('badge');
    if (is_numeric($b)) {
      $db->query("DELETE FROM plg_badges WHERE id = ?", [$b]);
      $db->query("DELETE FROM plg_badges_match WHERE badge_id = ?", [$b]);
    }
    usSuccess("Badge Deleted");
    Redirect::to($us_url_root . "users/admin.php?view=plugins_config&plugin=badges");
  }

  if (!empty($_POST['badgeAction'])) {
    $attempt = manageBadge(Input::get('uid'), Input::get('badge'), Input::get('method'));
    // dump($attempt);

    Redirect::to($us_url_root . "users/admin.php?view=plugins_config&plugin=badges&err=" . $attempt['msg'] . "&search=" . $search);
  }
}
$token = Token::generate();
$badges = $db->query("SELECT 
b.*,
c.cat
FROM plg_badges b 
LEFT OUTER JOIN plg_badges_cats c on b.cat_id = c.id 

")->results();

?>
<div class="content mt-3">
  <div class="row">
    <div class="col-sm-12">
      <a href="<?= $us_url_root ?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
      <h1>Configure the Badges Plugin!</h1>

      <div class="row">
       
        <div class="col-12 col-sm-3">
          <div class="card mb-3">
            <div class="card-header">
              <h5>Manage Users</h5>
            </div>
            <div class="card-body">
              <form class="mb-3" action="" method="post">
                Show users with a particular badge <small>(categories excluded)</small><br>
                <input type="hidden" name="csrf" value="<?= Token::generate(); ?>">
                <div class="input-group">
                  <select class="form-select" name="uwab">
                    <option value="" selected="selected" disabled>--Choose Badge--</option>
                    <?php foreach ($badges as $b) {
                      if ($b->cat_id != 1) {
                        continue;
                      }
                    ?>
                      <option <?php if (is_numeric($uwab) && $uwab == $b->id) {
                                echo "selected='selected'";
                              } ?> value="<?= $b->id ?>"><?= $b->badge ?></option>
                    <?php } ?>
                  </select>
                  <input type="submit" name="submit" value="Go" class="btn btn-primary">
                </div>

              </form>

              <form class="mb-3" action="" method="post">
                Search for a user to add or remove a badge<br>
                <input type="hidden" name="csrf" value="<?= Token::generate(); ?>">
                <div class="input-group">
                  <input type="text" name="search" value="<?= $search ?>" class="form-control">
                  <input type="submit" name="submit" value="Go" class="btn btn-primary">
                </div>

              </form>
            </div>
          </div>
          <div class="card mb-3">
            <div class="card-header">
              <h5>Create a New Badge</h5>
            </div>
            <div class="card-body">
              <form class="" action="" method="post">
                <label for="">Badge Name</label>
                <input type="hidden" name="csrf" value="<?= Token::generate(); ?>">
                <input type="text" name="badge" value="" required class="form-control">
                <select name="cat" class="form-select mt-3" required>
                  <option value="" disabled selected>-- Choose Category --</option>
                  <?php foreach ($cats as $c) { ?>
                    <option value="<?= $c->id ?>"><?= $c->cat ?></option>
                  <?php } ?>
                </select>
                <input type="submit" name="newBadge" value="Create Badge" class="mt-3 btn btn-primary">
              </form>
            </div>
          </div>

          <div class="card mb-3">
            <div class="card-header">
              <h5>Delete a Badge</h5>
            </div>
            <div class="card-body">
              <form class="" action="" method="post" onsubmit="return confirm('Do you really want to do this? It cannot be undone.');">

                <input type="hidden" name="csrf" value="<?= Token::generate(); ?>">
                <select class="form-control" name="badge">
                  <option value="" selected="selected" disabled>--Choose Badge--</option>
                  <?php foreach ($badges as $b) { ?>
                    <option value="<?= $b->id ?>"><?= $b->badge ?> (<?= $b->id ?>)</option>
                  <?php } ?>
                </select>
                <input type="submit" name="delBadge" value="Delete Badge" class="mt-3 btn btn-danger">
                <br>
               <small>This will remove it from any user or entity which has it. Does not delete the actual picture file so you can reuse it in the future.</small>
              </form>
            </div>
          </div>

          <div class="card mb-3">
            <div class="card-header">
              <h5>Create a Category</h5>
            </div>
            <div class="card-body">
              <form class="" action="" method="post">

                <input type="hidden" name="csrf" value="<?= Token::generate(); ?>">
                <input type="text" name="cat" value="" required class="form-control" placeholder="Category Name">
                <input type="submit" name="createCat" value="Create Category" class="mt-3 btn btn-primary">
                <br>

              </form>
            </div>
          </div>
        </div>
        <div class="col-12 col-md-9">
          
        <?php if (is_numeric($uwab)) {
          $matches = $db->query("SELECT * FROM plg_badges_match WHERE badge_id = ?", [$uwab])->results();

        ?>

        
            <h4>Users with a Badge</h4>
            <table class="table table-striped pagninate mb-4">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>First Name</th>
                  <th>Last Name</th>
                  <th>Email</th>
                  <th>Badges</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($matches as $u) {
                  $q = $db->query("SELECT id, fname,lname,email FROM users WHERE id = ?", [$u->user_id]);
                  $c = $q->count();
                  if ($c < 1) {
                    $db->query("DELETE FROM plg_badges_match WHERE user_id = ?", [$u->user_id]);
                    continue;
                  } else {
                    $f = $q->first();
                ?>
                    <tr>
                      <td><?= $f->id ?></td>
                      <td><?= $f->fname ?></td>
                      <td><?= $f->lname ?></td>
                      <td><?= $f->email ?></td>
                      <td><?php displayBadges($f->id); ?></td>
                    </tr>
                  <?php } ?>

                <?php } ?>
              </tbody>
            </table>
          <hr>
        <?php } //end uwab
        if ($search != "") {

          $searched = $db->query("SELECT id, email, fname, lname, username FROM users WHERE id LIKE ? OR email LIKE ? OR fname LIKE ? OR lname LIKE ? OR username LIKE ?", ["%" . $search . "%", "%" . $search . "%", "%" . $search . "%", "%" . $search . "%", "%" . $search . "%"])->results();
        ?>

   
            <h4>Search Results
              <small>These results only show badges belonging to the "User," not tag, permission, or category badges.
            </h4>
            <table class="table table-striped pagninate mb-4">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Name</th>
                  <th>Username</th>
                  <th>Email / Badges</th>
                  <th>Badges</th>
                  <th>Give/Take</th>
                  <th>Submit</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($searched as $f) { ?>
                  <tr>
                    <td><?= $f->id ?></td>
                    <td><?= $f->fname ?> <?= $f->lname ?></td>
                    <td><?= $f->username ?></td>
                    <td>
                      <?= $f->email ?><br>
                      <?php displayBadges($f->id); ?>
                    </td>
                    <form class="" action="" method="post">
                      <td>

                        <input type="hidden" name="csrf" value="<?= Token::generate(); ?>">
                        <input type="hidden" name="search" value="<?= $search ?>">
                        <input type="hidden" name="uid" value="<?= $f->id ?>">
                        <select class="form-control" name="badge">
                          <option value="" selected="selected" disabled>--Choose Badge--</option>
                          <?php foreach ($badges as $b) { 
                            if($b->cat_id != 1){
                              continue;
                            }
                            ?>
                            <option value="<?= $b->id ?>"><?= $b->badge ?> (<?= $b->id ?>)</option>
                          <?php } ?>
                        </select>
                      </td>
                      <td>
                        <select class="form-control" name="method">
                          <option value="" selected="selected" disabled>--Choose Action--</option>
                          <option value="give">Give</option>
                          <option value="take">Take</option>
                        </select>
                      </td>
                      <td>
                        <input type="submit" name="badgeAction" value="Go" class="btn btn-primary">
                      </td>
                    </form>
                  </tr>
                <?php } ?>
              </tbody>
            </table>
          <hr>
        <?php }  ?>



          <form class="" action="" method="post">
            <h4>Your Badges <input type="submit" name="changeBadges" value="Save Changes" class="btn btn-primary"> </h4>
            <?= tokenHere(); ?>
            <table class="table table-striped paginate">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Name <small>(visible on hover)</th>
                  <th>Category</th>
                  <th>Badge</th>
                </tr>
              </thead>
              <tbody>

                <input type="hidden" name="csrf" value="<?= Token::generate(); ?>">
                <?php foreach ($badges as $b) { ?>
                  <tr>
                    <td><?= $b->id ?></td>
                    <td>
                      <input type="text" name="badge[<?= $b->id ?>]" value="<?= $b->badge ?>" class="form-control">
                    </td>
                    <td><?= $b->cat ?></td>
                    <td>
                      <?php if (file_exists($abs_us_root . $us_url_root . $plgset->badge_location . $b->id . ".png")) { ?>
                        <img src="<?= $us_url_root . $plgset->badge_location . $b->id . ".png" ?>" alt="" height="35px">
                      <?php } else { ?>
                        File Missing
                      <?php } ?>
                    </td>
                  </tr>
                <?php } 
                foreach($perm_badges as $b){
                  $id = explode("_",basename($b))[1];
                  $id = strtolower($id);
                  $id = str_replace(".png","",$id);
                  $nameQ = $db->query("SELECT name FROM permissions WHERE id = ?",[$id]);
                  $nameC = $nameQ->count();
                  if($nameC < 1){
                    continue;
                  }
                  $name = $nameQ->first()->name;
                  ?>
                  <tr>
                    <td>Permission <?= $id ?></td>
                    <td>
                      <input type="text" value="<?= $name ?>" class="form-control" readonly disabled>
                    </td>
                    <td>Permission</td>
                    <td>
                      <img src="<?= $us_url_root . $plgset->badge_location . basename($b) ?>" alt="" height="35px">
                    </td>
                  </tr>
                  <?php
                }
                foreach($tag_badges as $b){
                  $id = explode("_",basename($b))[1];
                  $id = strtolower($id);
                  $id = str_replace(".png","",$id);
                  $nameQ = $db->query("SELECT tag FROM plg_tags WHERE id = ?",[$id]);
                  $nameC = $nameQ->count();
      
                  if($nameC < 1){
                    continue;
                  }
                  $name = $nameQ->first();
                  $name = $name->tag;
                  ?>
                  <tr>
                    <td>Tag <?= $id ?></td>
                    <td>
                      <input type="text" value="<?= $name ?>" class="form-control" readonly disabled>
                    </td>
                    <td>Tag</td>
                    <td>
                      <img src="<?= $us_url_root . $plgset->badge_location . basename($b) ?>" alt="" height="35px">
                    </td>
                  </tr>
                  <?php
                }


                ?>

                
          </form>
          </tbody>
          </table>



        </div>


      </div>


      <hr class="mt-4">
      <h3 class="">Instructions</h3>
      <p>If you appreciate this plugin and would like to make a donation to the author, you can do so at <a style="color:blue;" href="https://UserSpice.com/donate">https://UserSpice.com/donate</a>. Either way, thanks for using UserSpice!</p>
      <p>This plugin is pretty simple. You create a badge by giving it a name. That inserts it into the database and spits back an id number. Your badge should be a png (ideally with a clear background) with the name being id#.png.
        <br>So ID 1, should be 1.png located in
      <form action="" method="post">
        <?= tokenHere(); ?>
        <div class="input-group">
          <input type="text" name="badge_location" value="<?= $plgset->badge_location ?>" required class="form-control">
          <input type="submit" name="changeBadgeLocation" value="Change Path" class="btn btn-primary">
        </div>
      </form>
      </p>
      <p>There are two key functions in this plugin that you can use outside the config page. <br>
        <b>displayBadges($user_id,$size="25px")</b><br>
        Simply specify the user id whose badges you want to display with the displayBadges function. It only requires a user id (Which is usually $user->data()->id) and you can optionally choose a size. I used pixels but you could use other measurements. This determines the height of the badge.
      </p>

      <p>
        <b>manageBadge($user_id,$badge,$action="give", $category = 1)</b><br>
        Give it a user id, a badge (either the unique name or its id) and by default it will give that person the badge. You can optionally add "take" to that third parameter to take the badge. There is no reason to add the final $category variable if you're not working with categories.
      </p>

      <h5><b>New in v1.0.5 (March 1, 2024): Automatic Badges and Badge Categories</b></h5>
      <p>
        After this update, nothing will change with your existing function calls. However, you can now create categories for your badges. Category 1, "User Badges" has been created by default. This is is the default behavior. You call <code>displayBadges($user_id,$size="25px")</code> and it will display all User Badges for that user. However, there are 2 new functions and some new options for managing your tags.
      </p>

      <p>
      <h6><b>Automatic Badges</b></h6>
      You can implement automatic badges so you do not have to actually add and remove them from the users. You can call the new functions <code>displayPermBadges($user_id, $size="25px")</code> or <code>displayTagBadges($user_id, $size="25px")</code>. To use these badges simply create images in your <?= $plgset->badge_location ?> directory with the name "perm_#".png or "tag_#".png. The # is the id of the permission level or tag from the <b>permissions</b> or <b>plg_tags</b> tables. <span class="bg-warning ps-2 pe-2">Warning. Your badge, permission, and tag names are shown on hover, so be careful what you name them.</span>
      </p>

      <h6><b>Category Badges</b></h6>
      <p>
        Category badges are a way to add some extra flexibility to the badges plugin. Let's say your site has the concept of "teams" made up of many users. You can create a category for "Team Badges" and then call <code></code>displayBadges($team_id,$size="25px", $category = 2)</code> to display all badges in category 2 for that team. It's up to you to pass whatever id (int) for the team and to know that cateogry 2 is your team badges. But if you do that, right, the system will display the badges.
      </p>
      <p>
        The thought is that you will be using the manageBadge function to give and take badges from teams as well. You can also call <code>manageBadge($team_id,$badge,$action="give",2)</code> to give or take a badge from a team in category 2.
      </p>
      <p>
        A new function <code>displayAllBadges($user_id, $size="25px");</code> wraps up all three functions (displayBadges, displayPermBadges, and displayTagBadges) into one function. 
    </p>
    </div> <!-- /.col -->
  </div> <!-- /.row -->

  <style>
    p {
      margin-bottom: 1rem;
    }
  </style>