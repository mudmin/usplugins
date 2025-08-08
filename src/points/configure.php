  <?php if (!in_array($user->data()->id, $master_account)) {
    Redirect::to($us_url_root . 'users/admin.php');
  } //only allow master accounts to manage plugins! 
  ?>

  <?php
  include "plugin_info.php";
  pluginActive($plugin_name);
  if (!empty($_POST['updateSettings'])) {
    $fields = array(
      'term' => Input::get('term'),
      'term_sing' => Input::get('term_sing'),
      'show_acct_bal' => Input::get('show_acct_bal'),
      'allow_arb_trans' => Input::get('allow_arb_trans'),
      'show_trans_acct' => Input::get('show_trans_acct'),
    );
    $db->update('plg_points_settings', 1, $fields);
    Redirect::to('admin.php?view=plugins_config&plugin=points&err=Settings+saved');
  }

  if (!empty($_POST['givePoints'])) {
    $attempt = alterPoints(Input::get('user'), Input::get('points'), Input::get('type'), Input::get('reason'));
    Redirect::to('admin.php?view=plugins_config&plugin=points&err=' . $attempt['reason']);
  }

  if (!empty($_POST['transferPoints'])) {
    $attempt = transferPoints(Input::get('from'), Input::get('to'), Input::get('points'), Input::get('reason'));
    Redirect::to('admin.php?view=plugins_config&plugin=points&err=' . $attempt['reason']);
  }

  $token = Token::generate();
  $pntSettings = $db->query("SELECT * FROM plg_points_settings")->first();
  ?>
  <div class="content mt-3">
    <div class="row">
      <div class="col-12">
        <a href="<?= $us_url_root ?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
        <h1>Configure the Points Plugin!</h1>
        <strong>Please note:</strong> This plugin comes with a lot of functions for your use that are documented at the bottom of this page.<br><br>
        </h3>
      </div>
    </div>
    <div class="row" style="margin-top: 1.1em;">
      <div class="col-12 col-sm-4">
        <h4>General Settings</h4>

        <form class="" action="" method="post">
          <input type="hidden" name="csrf" value="<?= $token ?>" />
          <div class="form-group">
            <label for="">What term do you want to use for your site currency?</label>
            <input class="form-control" type="text" name="term" value="<?= $pntSettings->term ?>" required>
          </div>
          <div class="form-group">
            <label for="">What term do you want to use for a single unit of your site currency?</label>
            <input class="form-control" type="text" name="term_sing" value="<?= $pntSettings->term_sing ?>" required>
          </div>
          <div class="form-group">
            <label for="">Show <?= pointsName(); ?> on account page?</label>
            <select class="form-control" name="show_acct_bal">
              <option <?php if ($pntSettings->show_acct_bal == 0) {
                        echo "selected='selected'";
                      } ?> value="0">No</option>
              <option <?php if ($pntSettings->show_acct_bal == 1) {
                        echo "selected='selected'";
                      } ?> value="1">Yes</option>
            </select>
          </div>

          <div class="form-group">
            <label for="">Allow direct transfer of <?= pointsName(); ?> between users (based on username)?</label>
            <select class="form-control" name="allow_arb_trans">
              <option <?php if ($pntSettings->allow_arb_trans == 0) {
                        echo "selected='selected'";
                      } ?> value="0">No</option>
              <option <?php if ($pntSettings->allow_arb_trans == 1) {
                        echo "selected='selected'";
                      } ?> value="1">Yes</option>
            </select>
          </div>

          <div class="form-group">
            <label for="">Show <?= pointsName(); ?> transactions on account page?</label>
            <select class="form-control" name="show_trans_acct">
              <option <?php if ($pntSettings->show_trans_acct == 0) {
                        echo "selected='selected'";
                      } ?> value="0">No</option>
              <option <?php if ($pntSettings->show_trans_acct == 1) {
                        echo "selected='selected'";
                      } ?> value="1">Yes</option>
            </select>
          </div>
          <div class="form-group">
            <input type="submit" name="updateSettings" value="Update Settings" class="btn btn-primary">
          </div>
        </form>
      </div> <!-- /.col -->
      <div class="col-12 col-sm-4">
        <h4>Give/Take <?= pointsName(); ?></h4>
        <form class="" action="" method="post">
          <div class="form-group">
            <label for="">How many <?= pointsName(); ?>?*</label>
            <input class="form-control" type="number" name="points" value="" required>
          </div>
          <div class="form-group">
            <label for="">Username or ID*</label>
            <input class="form-control" type="text" name="user" value="" required>
          </div>
          <div class="form-group">
            <label for="">Reason*</label>
            <input class="form-control" type="text" name="reason" value="" required>
          </div>
          <div class="form-group">
            <label for="">Are you giving or taking <?= pointsName(); ?>?*</label>
            <select class="form-control" name="type" required>
              <option value="" disabled selected="selected">--Choose Option--</option>
              <option value="give">Give <?= pointsName(); ?></option>
              <option value="take">Take <?= pointsName(); ?></option>
            </select>
          </div>
          <strong>Please note:</strong> Giving/taking with this form does NOT affect your personal
          balance.<br><br>
          <div class="form-group">
            <input type="submit" name="givePoints" value="Give/Take" class="btn btn-primary">
          </div>

        </form>
      </div>

      <div class="col-12 col-sm-4">
        <h4>Transfer <?= pointsName(); ?></h4>
        <form class="" action="" method="post">
          <div class="form-group">
            <label for="">How many <?= pointsName(); ?>?*</label>
            <input class="form-control" type="number" name="points" value="" required>
          </div>
          <div class="form-group">
            <label for="">Take <?= pointsName(); ?> From (Username or ID)*</label>
            <input class="form-control" type="text" name="from" value="" required>
          </div>
          <div class="form-group">
            <label for="">Give <?= pointsName(); ?> To (Username or ID)*</label>
            <input class="form-control" type="text" name="to" value="" required>
          </div>

          <div class="form-group">
            <label for="">Reason*</label>
            <input class="form-control" type="text" name="reason" value="" required>
          </div>
          <strong>Please note:</strong> This form WILL affect your personal balance if you transfer to/from your account.<br><br>
          <div class="form-group">
            <input type="submit" name="transferPoints" value="Transfer" class="btn btn-primary">
          </div>

        </form>
      </div>
    </div> <!-- /.row -->
    <div class="row">
      <div class="col-12">
        <?php $uc = ucfirst(pointsNameReturn()); ?>
        <h4><?= $uc ?> Transactions</h4>
        <?php $trans = $db->query("SELECT * FROM plg_points_trans ORDER BY id DESC")->results();
        ?>
        <table class="table table-striped" id="paginate">
          <thead>
            <tr>
              <th>Date</th>
              <th><?= $uc ?></th>
              <th>From</th>
              <th>To</th>
              <th>Reason</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($trans as $b) { ?>
              <tr>
                <td><?= $b->ts ?></td>
                <td><?= $b->points ?></td>
                <td><?php echouser($b->trans_from); ?></td>
                <td><?php echouser($b->trans_to); ?></td>
                <td><?= $b->reason ?></td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
    <div class="row">
      <div class="col-12">
        <h2>Documentation</h2>
        <p>If you appreciate this plugin and would like to make a donation to the author, you can do so at <a style="color:blue;" href="https://UserSpice.com/donate">https://UserSpice.com/donate</a>. Either way, thanks for using UserSpice!</p>
        <br>
        <strong>UltraMenu Hook</strong><br>
        There is a hook now that you can add as a "snippet" in UltraMenu to show the current user's point total. You can copy the code and use it as the basis for your own snippet by copying the file from /usersc/plugins/points/menu_hooks/points.php to your /usersc/hooks/menu folder and then adding it as a snippet in UltraMenu.
        <br> <br>
        <strong>Arbitrary Transfers</strong><br>
        Technically allowing arbitrary transfers between users from the account page COULD make it easier for someone to hack your site. In order
        for someone to "brute force" login your site (try every combination) they need to know a username and a password.
        By seeing if a transfer cannot go through because a user does not exist, they could figure out if a username is valid on your site and then they
        only need to worry about the password. Due to UserSpice's password strength and intentionally slow password
        decryption, UserSpice is VERY resistant to this type of attack, but you should understand the risks.

        <br><br><strong>pointsName() function</strong><br>
        Calling pointsName() will echo whatever you call points on your system.

        <br><br><strong>pointsNameReturn() function</strong><br>
        Calling pointsName() will return whatever you call points on your system.

        <br><br><strong>pointsUnitReturn($num) function</strong><br>
        Let's say you use the terms gem and gems for points on your system.<br>
        pointsUnitReturn(1) will return "1 gem"<br>
        pointsUnitReturn(5) will return "5 gems"

        <br><br><strong>validatePointsUser($username) function</strong><br>
        You can pass either a user id or a username to this function. It will return false if the user does not exist
        and will return the entire row of that user from the users table if they do.

        <br><br><strong>logPoints($from,$to,$reason,$points) function</strong><br>
        Creates a log in the plg_points_trans table.

        <br><br><strong>alterPoints($username,$points,$type,$reason) function</strong><br>
        Adds or removes points to/from a user WITHOUT taking them from someone else.<br>
        $username can be a username or user id<br>
        $type is either 'give' or 'take'<br>
        If you were doing this in the usersc/scripts/during_user_creation.php script, it would look like<br>
        alterPoints($theNewId,500,'give','500 point signup bonus!');<br>
        This transaction is automatically logged;

        <br><br><strong>transferPoints($from,$to,$points,$reason) function</strong><br>
        Transfers points from one user to another.<br>
        both $to and $from can be either a username or user id.<br>
        This transaction is automatically logged;
      </div>

    </div>
    <script type="text/javascript" src="js/pagination/datatables.min.js"></script>
    <script>
      $(document).ready(function() {
        $('#paginate').DataTable({
          "pageLength": 25,
          "stateSave": true,
          "aLengthMenu": [
            [25, 50, 100, -1],
            [25, 50, 100, "All"]
          ],
          "aaSorting": []
        });
      });
    </script>