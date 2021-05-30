<?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

  <?php
  include "plugin_info.php";
  pluginActive($plugin_name);
  $uwab = Input::get('uwab');
  $search = Input::get('search');

  if(!empty($_POST)){
    $token = $_POST['csrf'];
    if(!Token::check($token)){
      include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
    }

    if(!empty($_POST['newBadge'])){
      $b = Input::get('badge');
      $c = $db->query("SELECT * FROM plg_badges WHERE badge = ?",[$b])->count();
      if($c > 0){
        Redirect::to($us_url_root."users/admin.php?view=plugins_config&plugin=badges&err=This name is already in use");
      }else{
        $db->insert("plg_badges",['badge'=>$b]);
        Redirect::to($us_url_root."users/admin.php?view=plugins_config&plugin=badges&err=Badge Created");
      }
    }

    if(!empty($_POST['changeBadges'])){
      $names = Input::get('badge');
      $failed = false;
      foreach($names as $k=>$v){
        $c = $db->query("SELECT * FROM plg_badges WHERE badge = ? AND id != ?",[$v,$k])->count();
        if($c < 1 && $v != ""){
          $db->update("plg_badges",$k,['badge'=>$v]);
        }else{
          $failed = true;
        }
      }
      if(!$failed){
        Redirect::to($us_url_root."users/admin.php?view=plugins_config&plugin=badges&err=Changes Saved");
      }else{
        Redirect::to($us_url_root."users/admin.php?view=plugins_config&plugin=badges&err=Names must be unique");
      }
    }

    if(!empty($_POST['delBadge'])){
      $b = Input::get('badge');
      if(is_numeric($b)){
        $db->query("DELETE FROM plg_badges WHERE id = ?",[$b]);
        $db->query("DELETE FROM plg_badges_match WHERE badge_id = ?",[$b]);
      }
      Redirect::to($us_url_root."users/admin.php?view=plugins_config&plugin=badges&err=Deleted");

    }

    if(!empty($_POST['badgeAction'])){
      $attempt = manageBadge(Input::get('uid'),Input::get('badge'),Input::get('method'));
      // dump($attempt);
      // dnd($_POST);
      Redirect::to($us_url_root."users/admin.php?view=plugins_config&plugin=badges&err=".$attempt['msg']."&search=".$search);
    }

  }
  $token = Token::generate();
  $badges = $db->query("SELECT * FROM plg_badges")->results();

  ?>
  <div class="content mt-3">
    <div class="row">
      <div class="col-sm-12">
        <a href="<?=$us_url_root?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
        <h1>Configure the Badges Plugin!</h1>
        <h4>Instructions</h4>
        <p>This plugin is pretty simple. You create a badge by giving it a name. That inserts it into the database and spits back an id number.  Your badge should be a png (ideally with a clear background) with the name being id#.png.  So ID 1, should be 1.png located in usersc/plugins/badges/files/</p>
        <p>There are two key functions in this plugin that you can use outside the config page. <br>
          <b>displayBadges($user_id,$size="25px")</b><br>
          Simply specify the user id whose badges you want to display with the displayBadges function. It only requires a user id (Which is usually $user->data()->id) and you can optionally choose a size. I used pixels but you could use other measurements. This determines the height of the badge.
        </p>

        <p>
          <b>manageBadge($user_id,$badge,$action="give")</b><br>
          Give it a user id, a badge (either the unique name or its id) and by default it will give that person the badge. You can optionally add "take" to that third parameter to take the badge.
        </p>
      </div> <!-- /.col -->
    </div> <!-- /.row -->
    <div class="row">
      <div class="col-4">
        <h4>Create a Badge</h4>
        <form class="" action="" method="post">
          <label for="">Badge Name</label>
          <input type="hidden" name="csrf" value="<?=Token::generate();?>">
          <input type="text" name="badge" value="" required class="form-control">
          <input type="submit" name="newBadge" value="Create Badge" class="btn btn-primary">
        </form>
      </div>
      <div class="col-4">
        <h4>Delete a Badge</h4>

        <form class="" action="" method="post" onsubmit="return confirm('Do you really want to do this? It cannot be undone.');">
          <label for="">Badge to Delete</label>
          <input type="hidden" name="csrf" value="<?=Token::generate();?>">
          <select class="form-control" name="badge">
            <option value="" selected="selected" disabled>--Choose Badge--</option>
            <?php foreach($badges as $b){ ?>
              <option value="<?=$b->id?>"><?=$b->badge?> (<?=$b->id?>)</option>
            <?php } ?>
          </select>
          <input type="submit" name="delBadge" value="Delete Badge" class="btn btn-danger">
          <p>This will remove it from anyone who has it. Does not delete the actual picture file.</p>
        </form>
      </div>
      <div class="col-4">
        <form class="" action="" method="post">
          <h4>Your Badges <input type="submit" name="changeBadges" value="Save Changes" class="btn btn-primary"> </h4>
          <table class="table table-striped">
            <thead>
              <tr>
                <th>ID</th><th>Name</th><th>Photo</th>
              </tr>
            </thead>
            <tbody>

              <input type="hidden" name="csrf" value="<?=Token::generate();?>">
              <?php foreach($badges as $b){ ?>
                <tr>
                  <td><?=$b->id?></td>
                  <td>
                    <input type="text" name="badge[<?=$b->id?>]" value="<?=$b->badge?>" class="form-control">
                  </td>
                  <td>
                    <?php if(file_exists($abs_us_root.$us_url_root."usersc/plugins/badges/files/".$b->id.".png")){ ?>
                      <img src="<?=$us_url_root."usersc/plugins/badges/files/".$b->id.".png"?>" alt="" height="35px" >
                    <?php }else{ ?>
                      File Missing
                    <?php } ?>
                  </td>
                </tr>
              <?php } ?>
            </form>
          </tbody>
        </table>
      </div>
    </div>


    <div class="row">
      <div class="col-12 col-sm-3">
        <h4>Manage Users</h4>
        <form class="" action="" method="post">
          Show users with this badge<br>
          <input type="hidden" name="csrf" value="<?=Token::generate();?>">
          <select class="form-control" name="uwab">
            <option value="" selected="selected" disabled>--Choose Badge--</option>
            <?php foreach($badges as $b){ ?>
              <option <?php if(is_numeric($uwab) && $uwab == $b->id){echo "selected='selected'";}?> value="<?=$b->id?>"><?=$b->badge?></option>
            <?php } ?>
          </select>
          <input type="submit" name="submit" value="Go" class="btn btn-primary">
        </form>
        <form class="" action="" method="post">
          Search for a user to add or remove a badge<br>
          <input type="hidden" name="csrf" value="<?=Token::generate();?>">
          <input type="text" name="search" value="<?=$search?>" class="form-control">
          <input type="submit" name="submit" value="Go" class="btn btn-primary">
        </form>
      </div>
      <?php if(is_numeric($uwab)){
        $matches = $db->query("SELECT * FROM plg_badges_match WHERE badge_id = ?",[$uwab])->results();

        ?>

        <div class="col-12 col-sm-9">
          <h4>Users with a Badge</h4>
          <table class="table table-striped pagninate">
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
              <?php foreach($matches as $u){
                $q = $db->query("SELECT id, fname,lname,email FROM users WHERE id = ?",[$u->user_id]);
                $c = $q->count();
                if($c < 1){
                  $db->query("DELETE FROM plg_badges_match WHERE user_id = ?",[$u->user_id]);
                  continue;
                }else{
                  $f = $q->first();
                  ?>
                  <tr>
                    <td><?=$f->id?></td>
                    <td><?=$f->fname?></td>
                    <td><?=$f->lname?></td>
                    <td><?=$f->email?></td>
                    <td><?php displayBadges($f->id);?></td>
                  </tr>
                <?php } ?>

              <?php } ?>
            </tbody>
          </table>
        </div>
      <?php } //end uwab
      if($search != ""){

        $searched = $db->query("SELECT id, email, fname, lname, username FROM users WHERE id LIKE ? OR email LIKE ? OR fname LIKE ? OR lname LIKE ? OR username LIKE ?",["%".$search."%","%".$search."%","%".$search."%","%".$search."%","%".$search."%"])->results();
        ?>

        <div class="col-12 col-sm-9">
          <h4>Search Results</h4>
          <table class="table table-striped pagninate">
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
              <?php foreach($searched as $f) { ?>
                <tr>
                  <td><?=$f->id?></td>
                  <td><?=$f->fname?> <?=$f->lname?></td>
                  <td><?=$f->username?></td>
                  <td>
                    <?=$f->email?><br>
                    <?php displayBadges($f->id);?>
                  </td>
                  <form class="" action="" method="post">
                    <td>

                      <input type="hidden" name="csrf" value="<?=Token::generate();?>">
                      <input type="hidden" name="search" value="<?=$search?>">
                      <input type="hidden" name="uid" value="<?=$f->id?>">
                      <select class="form-control" name="badge">
                        <option value="" selected="selected" disabled>--Choose Badge--</option>
                        <?php foreach($badges as $b){ ?>
                          <option value="<?=$b->id?>"><?=$b->badge?> (<?=$b->id?>)</option>
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
        </div>
      <?php }  ?>


    </div>
