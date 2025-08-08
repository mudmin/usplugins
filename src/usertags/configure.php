  <?php if (!in_array($user->data()->id, $master_account)) {
    Redirect::to($us_url_root . 'users/admin.php');
  } //only allow master accounts to manage plugins! 

  include "plugin_info.php";
  pluginActive($plugin_name);
  if (!empty($_POST)) {
    $token = $_POST['csrf'];
    if (!Token::check($token)) {
      include($abs_us_root . $us_url_root . 'usersc/scripts/token_error.php');
    }
    if (!empty($_POST['create'])) {
      $tag = Input::get('tag');
      $check = $db->query("SELECT * FROM plg_tags WHERE tag = ?", [$tag])->count();
      if ($check > 0) {
        Redirect::to("admin.php?view=plugins_config&plugin=usertags&err=A tag with that name already exists");
      } elseif (is_numeric($tag) || $tag == "") {
        Redirect::to("admin.php?view=plugins_config&plugin=usertags&err=Tag cannot be numeric or blank");
      } else {
        $db->insert("plg_tags", ['tag' => $tag]);
        Redirect::to("admin.php?view=plugins_config&plugin=usertags&err=$tag created!");
      }
    }

    if (!empty($_POST['delete'])) {
      $tag = Input::get('tag');
      $db->query("DELETE FROM plg_tags WHERE id = ?", [$tag]);
      $db->query("DELETE FROM plg_tags_matches WHERE tag_id = ?", [$tag]);
      Redirect::to("admin.php?view=plugins_config&plugin=usertags&err=$tag has been purged!");
    }
  }

  $mt = Input::get("manageTag");
  if (is_numeric($mt)) {
    $usersWith = $db->query("SELECT * FROM plg_tags_matches WHERE tag_id = ?", [$mt])->results();
  }

  $token = Token::generate();
  $existingTags = $db->query("SELECT * FROM plg_tags")->results();
  ?>
  <div class="content mt-3">
    <div class="row">
      <div class="col-12">
        <h1>Manage Tags</h1>
        <a href="<?= $us_url_root ?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
      </div>
    </div>

    <div class="row">
      <div class="col-sm-6">
        <h3>Create Tag</h3>
        <form class="" action="" method="post">
          <input type="hidden" name="csrf" value="<?= $token ?>">
          <div class="input-group">
            <input type="text" name="tag" value="" class="form-control" placeholder="Tag Name">
            <input type="submit" name="create" value="Create Tag" class="btn btn-primary">
          </div>
        </form>
      </div>

      <div class="col-sm-6">
        <h3>Delete Tag</h3>
        <form class="" action="" method="post" onsubmit="return confirm('Are you sure? This will delete the tag and untag all users who had it! It cannot be undone!');">
          <input type="hidden" name="csrf" value="<?= $token ?>">
          <div class="input-group">
            <select class="form-control" name="tag">
              <option value="" disabled selected="selected">---Select Tag to Delete---</option>
              <?php foreach ($existingTags as $et) { ?>
                <option value="<?= $et->id ?>"><?= $et->tag ?></option>
              <?php } ?>
            </select>
            <input type="submit" name="delete" value="Delete Tag" class="btn btn-primary">
          </div>
        </form>
      </div>
    </div> <!-- /.row -->
    <br>
    <div class="row">
      <div class="col-12 col-sm-6">

        <h3>See Users with a Tag</h3>
        <form class="" action="" method="get">
          <input type="hidden" name="view" value="plugins_config">
          <input type="hidden" name="plugin" value="usertags">
          <div class="input-group">

            <select class="form-control" name="manageTag">
              <option value="" disabled selected="selected">---Select Tag to Manage---</option>
              <?php foreach ($existingTags as $et) { ?>
                <option <?php if ($mt == $et->id) {
                          echo "selected = 'selected'";
                        } ?> value="<?= $et->id ?>"><?= $et->tag ?></option>
              <?php } ?>
            </select>
            <input type="submit" name="submit" value="Manage" class="btn btn-primary">

          </div>
        </form>
        <?php if (is_numeric($mt)) { ?>
          <table class="table table-striped table-hover paginate">
            <thead>
              <th>User</th>
              <th>View</th>
            </thead>
            <tbody>
              <?php foreach ($usersWith as $u) { ?>
                <tr>
                  <td><?php echouser($u->user_id); ?></td>
                  <td>
                    <a href="admin.php?view=user&id=<?= $u->user_id ?>" class="btn btn-primary">View</a>

                  </td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        <?php } ?>
      </div>
      <div class="col-12 col-sm-6">
        <h3>Documentation</h3>
        This plugin adds an extra section on the individual user manager that lets you add and remove tags for that user. Tags are just another way of grouping your users together, except they don't have any implicit permissions. There are a few functions that will help with using these tags.
        <br><br>
        <b>usersWithTag($tag)</b>: Returns an array of users with a tag. You can pass the id of the tag such as <b><em>usersWithTag(1)</em></b> or the case-sensitive name of the tag <b><em>usersWithTag("Manager")</em></b>.
        <br><br>
        <b>hasTag($tag,$user_id)</b>: Returns true or false depending on whether or not the specified user has that tag. Tag can be an id or the case-sensitive tag name.

        <br><br>
        <b>hasOneTag($tags, $user_id = "")</b>: Returns true if the user has one tag from an array of tags. If you specify the user id as the second parameter, it will use that id, otherwise, it will use the id of the loggded in user.

        <br><br>
        <b>hasAllTags($tags, $user_id = "")</b>: Returns true if the user has all tags in an array of tags. If you specify the user id as the second parameter, it will use that id, otherwise, it will use the id of the loggded in user.

        <br><br>
        <b>usersWithTag($tag)</b>: Returns an array of user ids with a given tag.  The tag specified can either be the tag id or the tag name.
 
      </div>
    </div>
    <?php if (!isset($chartsLoaded)) { ?>
      <script type="text/javascript" src="<?= $us_url_root ?>users/js/pagination/datatables.min.js"></script>

      <script>
        $(document).ready(function() {
          $('.paginate').DataTable({
            "pageLength": 25,
            "stateSave": true,
            "aLengthMenu": [
              [25, 50, 100, -1],
              [25, 50, 100, 250, 500]
            ]
          });
        });
      </script>
    <?php } ?>