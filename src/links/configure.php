  <?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

<?php
include "plugin_info.php";
pluginActive($plugin_name);
if(!empty($_POST)){
  if(!Token::check(Input::get('csrf'))){
    include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
  }
}

if(!empty($_POST['delLink'])){
  $d = Input::get('delMe');
  $db->query("DELETE FROM plg_links WHERE id = ?",[$d]);
  Redirect::to('admin.php?view=plugins_config&plugin=links&err=Deleted');
}

if(!empty($_POST['save_settings'])){
   $token = $_POST['csrf'];

$ai = Input::get('all_internal');
  if(!is_numeric($ai)){
    $ai = 1;
  }

  $fields = array(
    'perms'=>Input::get('perms'),
    'all_internal'=>$ai,
    'non_admins_see_all'=>Input::get('non_admins_see_all'),
    'parser_location'=>Input::get('parser_location'),
    'allow_login_choice'=>Input::get('allow_login_choice'),
    'base_url'=>Input::get('base_url'),
  );
  $db->update('plg_links_settings',1,$fields);
  Redirect::to('admin.php?view=plugins_config&plugin=links&err=Saved');
}
$lsettings = $db->query("SELECT * FROM plg_links_settings WHERE id = 1")->first();
 $token = Token::generate();
 ?>
<div class="content mt-3">
 		<div class="row">
 			<div class="col-sm-12">
          <a href="<?=$us_url_root?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
 					<h1>Links</h1>
          <div class="row">
            <div class="col-12 col-sm-6">
              <form class="" action="" method="post">
              <input type="hidden" name="csrf" value="<?=Token::generate();?>">
              Please give a comma separated list of permission levels that can create links<br>
              <input type="text" name="perms" value="<?=$lsettings->perms?>" class="form-control"  required><br>
              <!-- Will all links be translated to internal links? (https://yourdomain.com/l/link_name)<br>
              Please note: Setting this to yes will give you the best analytics and the cleanst links.<br>
              <select class="form-control" name="all_internal"  required>
                <option value="0" <?php //if($lsettings->all_internal == 0){echo "selected='selected'";}?>>No</option>
                <option value="1" <?php //if($lsettings->all_internal == 1){echo "selected='selected'";}?>>Yes</option>
              </select>
              <br> -->
              Do non-admins see only their own links or all links<br>
              <select class="form-control" name="non_admins_see_all"  required>
                <option value="0" <?php if($lsettings->non_admins_see_all == 0){echo "selected='selected'";}?>>They see only their own</option>
                <option value="1" <?php if($lsettings->non_admins_see_all == 1){echo "selected='selected'";}?>>They see all links</option>
              </select>
              <br>
              Location of your url shortener parser relative to root - usually "l/index.php"<br>
              <input type="text" name="parser_location" value="<?=$lsettings->parser_location?>" class="form-control" required>
              <br>
              Please enter your full base website url (https://mydomain.com) (applies to all links)<br>
              <input type="text" name="base_url" value="<?=$lsettings->base_url?>" class="form-control" required>
              <br>
              Require user to be logged in to visit a link (applies to new links)<br>
              <select class="form-control" name="allow_login_choice"  required>
                <option value="0" <?php if($lsettings->allow_login_choice == 0){echo "selected='selected'";}?>>Choose when creating the link</option>
                <option value="1" <?php if($lsettings->allow_login_choice == 1){echo "selected='selected'";}?>>Must always be logged in</option>
                <option value="2" <?php if($lsettings->allow_login_choice == 2){echo "selected='selected'";}?>>Never require login</option>
              </select>
              <br>
              <input type="submit" name="save_settings" value="Save Settings" class="btn btn-primary">

            </form>
            </div>
            <div class="col-12 col-sm-6">
              <strong>Notes:</strong>
              <p><strong>Very Important:</strong> this plugin requries a little bit of configuration.
              The full base url needs to be set to your site's home url.  Ideally the link parser will remain as
               l/index.php (for a full url format of something like https://yourdomain.com/l/?linkname), but this
               can be customized.</p>
              <p>There are some includes in usersc/plugins/links/assets that may be of use to you.
              <strong>usersc/plugins/links/assets/link_management.php</strong> is the user facing link
              management system and is fully self contained. It can be included on any other page.
              </p>
              <p><strong>usersc/plugins/links/assets/mini_table.php</strong> is a quick table to access
              these links and can be included anywhere.</p>


            </div>
          </div>

 			</div> <!-- /.col -->
 		</div> <!-- /.row -->
    <br>
    <div class="row">
      <div class="col-12">
        <h3>All Links</h3>
        <?php $links = $db->query("SELECT * FROM plg_links ORDER BY clicks DESC")->results(); ?>
        <table class="table table-striped paginate">
          <thead>
            <tr class="text-left">
              <th></th>
              <th>Link Name</th>
              <th>Link</th>
              <th>Created By</th>
              <th>Must be logged in?<br>(Only for internal)</th>
              <th>Clicks<br>(Only for internal)</th>
              <th>Delete</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($links as $l){?>
              <tr>
                <td>
                  <button type="button" class=" btn btn-primary" onclick="copyStringToClipboard('<?=generatePluginLink($l->id)?>');">Copy</button>
                </td>
                <td><?=$l->link_name?></td>
                <td><?=$l->link?></td>
                <td><?php echouser($l->user);?></td>
                <td><?php bin($l->logged_in);?></td>
                <td><?=$l->clicks?></td>
                <td>
                  <form class="" action="" method="post">
                      <input type="hidden" name="csrf" value="<?=Token::generate();?>">
                      <input type="hidden" name="delMe" value="<?=$l->id?>">
                      <input type="submit" name="delLink" value="Delete" class="btn btn-danger">
                  </form>
                </td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
    <br>
    <div class="row">
      <div class="col-12">
        <h3>Link Clicks (Only for internal links)</h3>
        <?php $clicks = $db->query("SELECT * FROM plg_links_clicks ORDER BY id DESC")->results(); ?>
        <table class="table table-striped paginate">
          <thead>
            <tr class="text-left">
              <th>Link Name</th>
              <th>User Who Clicked</th>
              <th>IP</th>
              <th>Timestamp</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($clicks as $l){?>
              <tr>
                <td><?=linkNameFromId($l->link)?>
                  <a href="<?=generatePluginLink($l->link)?>">(visit)</a>
                </td>
                <td><?php echouser($l->user);?></td>
                <td><?=$l->ip?></td>
                <td><?=$l->ts?></td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php
    if (!isset($chartsLoaded) || $chartsLoaded !== true && $chartsLoaded !== "true") { ?>
      <script type="text/javascript" src="<?= $us_url_root ?>users/js/pagination/datatables.min.js"></script>


      <script>
        $(document).ready(function() {
          $('.paginate').DataTable({
            "pageLength": 25,
            "aLengthMenu": [
              [25, 50, 100, -1],
              [25, 50, 100, 250, 500]
            ],
            "aaSorting": []
          });
        });
      </script>
    <?php } ?>
    <script type="text/javascript">
    function copyStringToClipboard (textToCopy) {
      navigator.clipboard.writeText(textToCopy)
    }
    </script>
