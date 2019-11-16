  <?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

<?php
include "plugin_info.php";

pluginActive($plugin_name);
 if(!empty($_POST['plugin_superlogger'])){
   $token = $_POST['csrf'];
if(!Token::check($token)){
  include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
}
  $fields = array(
    'plg_sl_guest'=>Input::get('plg_sl_guest'),
    'plg_sl_forms'=>Input::get('plg_sl_forms'),
    'plg_sl_opt_out'=>Input::get('plg_sl_opt_out'),
    'plg_sl_del_data'=>Input::get('plg_sl_del_data'),
    'plg_sl_join_warn'=>Input::get('plg_sl_join_warn'),
  );

  $db->update('settings',1,$fields);
   Redirect::to('admin.php?view=plugins_config&plugin=superlogger&err=Settings+updated');
 }

 // $db->query("ALTER TABLE settings ADD COLUMN plg_sl_guest tinyint(1) DEFAULT 0");
 // $db->query("ALTER TABLE settings ADD COLUMN plg_sl_forms tinyint(1) DEFAULT 0");
 // $db->query("ALTER TABLE settings ADD COLUMN plg_sl_opt_out tinyint(1) DEFAULT 0");
 // $db->query("ALTER TABLE settings ADD COLUMN plg_sl_del_data tinyint(1) DEFAULT 0");
 // $db->query("ALTER TABLE settings ADD COLUMN plg_sl_join_warn tinyint(1) DEFAULT 0");
 $token = Token::generate();
 ?>
<div class="content mt-3">
 		<div class="row">
      <?php ?>
 			<div class="col-sm-6 offset-sm-3">
          <a href="<?=$us_url_root?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
 					<h1>Configure the Super Logger Plugin!</h1>
          <form class="" action="" method="post">
            <input type="hidden" name="csrf" value="<?=$token?>" />

            <div class="form-group">
              <label for="">Save Form Data? (GET and POST)?</label>
              <select class="form-control" name="plg_sl_forms">
                <option value="0" <?php if($settings->plg_sl_forms == 0){echo "selected='selected'";}?>>No</option>
                <option value="1" <?php if($settings->plg_sl_forms == 1){echo "selected='selected'";}?>>Yes</option>
              </select>
            </div>

            <div class="form-group">
              <label for="">Track guests?</label>
              <select class="form-control" name="plg_sl_guest">
                <option value="0" <?php if($settings->plg_sl_guest == 0){echo "selected='selected'";}?>>No</option>
                <option value="1" <?php if($settings->plg_sl_guest == 1){echo "selected='selected'";}?>>Yes</option>
              </select>
            </div>

            <div class="form-group">
              <label for="">Allow users to opt out?</label>
              <select class="form-control" name="plg_sl_opt_out">
                <option value="0" <?php if($settings->plg_sl_opt_out == 0){echo "selected='selected'";}?>>No</option>
                <option value="1" <?php if($settings->plg_sl_opt_out == 1){echo "selected='selected'";}?>>Yes</option>
              </select>
            </div>

            <div class="form-group">
              <label for="">Allow users to delete their tracking data?</label>
              <select class="form-control" name="plg_sl_del_data">
                <option value="0" <?php if($settings->plg_sl_del_data == 0){echo "selected='selected'";}?>>No</option>
                <option value="1" <?php if($settings->plg_sl_del_data == 1){echo "selected='selected'";}?>>Yes</option>
              </select>
            </div>

            <div class="form-group">
              <label for="">Warn new users of tracking?</label>
              <select class="form-control" name="plg_sl_join_warn">
                <option value="0" <?php if($settings->plg_sl_join_warn == 0){echo "selected='selected'";}?>>No</option>
                <option value="1" <?php if($settings->plg_sl_join_warn == 1){echo "selected='selected'";}?>>Yes</option>
              </select>
            </div>

          <input type="submit" name="plugin_superlogger" value="Save Settings" class="btn btn-primary">
          </form>
 			</div> <!-- /.col -->
 		</div> <!-- /.row -->
    <div class="row">
      <div class="col-12">
        <?php
        $users = $db->query("SELECT id FROM users")->results();
        $pages = $db->query("SELECT DISTINCT page FROM plg_sl_logs ORDER BY page")->results();
        ?>
        <h4>Filter Data</h4>
    </div>
  </div>
  <div class="row">
    <div class="col-3">
        <form class="" action="" method="get">
          <input type="hidden" name="view" value="plugins_config">
          <input type="hidden" name="plugin" value="superlogger">
          <select class="" name="users">
            <option value="0">Guests</option>
            <?php foreach($users as $u){ ?>
              <option value="<?=$u->id?>"><?php echouser($u->id);?></option>
            <?php } ?>
          </select>
          <input type="submit" name="submitUser" value="Go">
        </form>
      </div>
        <div class="col-3">
        <form class="" action="" method="get">
          <input type="hidden" name="view" value="plugins_config">
          <input type="hidden" name="plugin" value="superlogger">
          <select class="" name="pages">
            <?php foreach($pages as $u){ ?>
              <option value="<?=$u->page?>"><?=$u->page?></option>
            <?php } ?>
          </select>
          <input type="submit" name="submitPage" value="Go">
        </form>
      </div>
        <div class="col-3">
        <form class="" action="" method="get">
          <input type="hidden" name="view" value="plugins_config">
          <input type="hidden" name="plugin" value="superlogger">
          <input type="submit" name="submitClear" value="Clear Filters">
        </form>
      </div>
    </div>
      <div class="row">
        <div class="col-12">
        <?php
        $users = Input::get('users');
        $pages = Input::get('pages');
        if($users != '' && is_numeric($users)){
          $data = $db->query("SELECT * FROM plg_sl_logs WHERE user_id = ? ORDER BY id DESC LIMIT 5000",[$users])->results();
        }elseif($pages != ''){
          $data = $db->query("SELECT * FROM plg_sl_logs WHERE page = ? ORDER BY id DESC LIMIT 5000",[$pages])->results();
        }else{
          $data = $db->query("SELECT * FROM plg_sl_logs ORDER BY id DESC LIMIT 5000")->results();
        }
        ?>
        <h3>Logging Data</h3>
        <table class="table table-striped" id="paginate">
          <thead>
            <tr>
              <th>User</th><th>Page</th><th>IP</th><th>GET</th><th>POST</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($data as $d){ ?>
              <tr>
                <td><?php echouser($d->user_id);?></td>
                <td><?=$d->page?></td>
                <td><?=$d->ip?></td>
                <td><?=$d->get_data?></td>
                <td><?=$d->post_data?></td>
              </tr>
            <?php } ?>
          </tbody>
        </table>

      </div>
    </div>
    <script type="text/javascript" src="js/pagination/datatables.min.js"></script>
    <script>
    $(document).ready(function() {
      $('#paginate').DataTable({"pageLength": 25,"aLengthMenu": [[25, 50, 100, -1], [25, 50, 100, "All"]], "aaSorting": []});
    } );
  </script>
