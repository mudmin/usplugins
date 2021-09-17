  <?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

<?php
include "plugin_info.php";
include "assets/local_functions.php";
$upset = $db->query("SELECT * FROM plg_uptime_settings")->first();
$methods = [];
$email = $db->query("SELECT * FROM email")->first();
if($email->email_login != "" && $email->email_login != "yourEmail@gmail.com"){
  $methods[] = "email";
}

if(pluginActive('pushover',true)){
  if($db->query("SELECT * FROM plg_uptime_notifications WHERE method = ? AND disabled = 0",['pushover'])->count() < 1){
      $methods[] = "pushover";
  }
}


if(pluginActive('twilio',true)){
      $methods[] = "twilio";
}

pluginActive($plugin_name);
 if(!empty($_POST)){
   $token = $_POST['csrf'];
if(!Token::check($token)){
  include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
}
  if(!empty($_POST['clearDowntime'])){
    $db->query("TRUNCATE table plg_uptime_downtime");
    Redirect::to("admin.php?view=plugins_config&plugin=uptime&msg=Downtime data cleared");
  }
  if(!empty($_POST['addTarget'])){
    $check = $db->query("SELECT * FROM plg_uptime WHERE site = ?"[Input::get('site')])->count();
    if($check > 0){
      Redirect::to("admin.php?view=plugins_config&plugin=uptime&msg=Error. A site with that name already exists");
    }
    $fields = [
      'site' => Input::get('site'),
      'url'  => Input::get('url'),
      'ustarget' =>Input::get('ustarget'),
      'created' =>date("Y-m-d H:i:s"),
    ];
    $db->insert("plg_uptime",$fields);
    Redirect::to("admin.php?view=plugins_config&plugin=uptime&err=Target Added!");
  }

  if(!empty($_POST['addNotif'])){

    if(Input::get('method') == "email"){
      if(Input::get('target') != ""){
        $fields = [
          'method' => Input::get('method'),
          'target' => Input::get('target')
        ];
        $db->insert("plg_uptime_notifications",$fields);
        Redirect::to("admin.php?view=plugins_config&plugin=uptime&err=Notification Added!");
      }else{
        Redirect::to("admin.php?view=plugins_config&plugin=uptime&err=Email cannot be blank");
      }
    }
    if(Input::get('method') == "twilio"){
      if(Input::get('twilio') != ""){
        $fields = [
          'method' => Input::get('method'),
          'target' => Input::get('twilio')
        ];
        $db->insert("plg_uptime_notifications",$fields);
        Redirect::to("admin.php?view=plugins_config&plugin=uptime&err=Notification Added!");
      }else{
        Redirect::to("admin.php?view=plugins_config&plugin=uptime&err=Phone number cannot be blank");
      }
    }
    if(Input::get('method') == "pushover"){

        $fields = [
          'method' => Input::get('method')
        ];
        $db->insert("plg_uptime_notifications",$fields);
        Redirect::to("admin.php?view=plugins_config&plugin=uptime&err=Notification Added!");
      }
    }



  if(!empty($_POST['saveSettings'])){
    $n = Input::get('notify_every');
    $d = Input::get('debug');
    if(is_numeric($d)){
      $db->update('plg_uptime_settings',1,['debug'=>$d]);
    }
    if(is_numeric($n) && $n >= 1){
      $db->update('plg_uptime_settings',1,['notify_every'=>$n]);
      Redirect::to("admin.php?view=plugins_config&plugin=uptime&err=Saved!");
    }
  }
 }

 if(!empty($_GET['deleteNot'])){

   $db->query("DELETE FROM plg_uptime_notifications WHERE id = ?",[Input::get('delme')]);

   Redirect::to("admin.php?view=plugins_config&plugin=uptime&err=Deleted!");
 }

 if(!empty($_GET['delTarg'])){
   $db->query("DELETE FROM plg_uptime WHERE id = ?",[Input::get('delme')]);
   Redirect::to("admin.php?view=plugins_config&plugin=uptime&err=Deleted!");
 }


 $token = Token::generate();
 ?>
 <style media="screen">
   .blue {
     color:blue;
   }

 </style>
<div class="content mt-3">
  <a href="<?=$us_url_root?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
  <div class="row">
    <div class="col-12">

      <h3>Uptime Plugin</h3>
      <form class="" action="" method="post">
        <div class="col-12 col-sm-6">
          <input type="hidden" name="csrf" value="<?=Token::generate();?>">
          Re-send notification if site stays offline for
              <input type="number" name="notify_every" value="<?=$upset->notify_every?>" step="1" min="1" style="width: 4em" required> minutes.   Debug Mode(0/1): <input type="number" name="debug" value="<?=$upset->debug?>" min="0" max="1" style="width: 4em" required>
          <input type="submit" name="saveSettings" value="Save Settings" class="btn btn-primary">
          </form>
        </div>
        <div class="col-12 col-sm-2">
          <form class="" action="" method="post" onsubmit="return confirm('Do you really want to do this? It cannot be undone.');">
            <input type="hidden" name="csrf" value="<?=Token::generate();?>">
            <input type="submit" name="clearDowntime" value="Clear Downtime Data" class="btn btn-danger">
          </form>
        </div>
        <div class="col-12 col-sm-2">
          <a class="btn btn-secondary" target="_blank" href="<?=$us_url_root?>usersc/plugins/uptime/documentation.php">Read Documentation</a>

        </div>
    </div>
  </div>
  <br>
 		<div class="row">
 			<div class="col-sm-4">
          <h3>New Target</h3>
          <form class="" action="" method="post">
            <input type="hidden" name="csrf" value="<?=Token::generate();?>">
            <div class="form-group">
              <label for="">Site Name</label><br>
              <input type="text" name="site" value="" class="form-control" required>
            </div>

            <div class="form-group">
              <label for="">URL to target file (can be any php/html file if not UserSpice)</label>
              <b>Example:</b> <em>https://yourdomain.com/index.php</em>
              <input type="text" name="url" value="" class="form-control" required>
            </div>

            <div class="form-group">
              <label for="">Is this a UserSpice or Wordpress site?<br>
                We provide you with some extra data if it is.</label>
              <select class="form-control" name="ustarget" required>
                <option value="" disabled selected="selected">--Choose--</option>
                <option value="1">Yes</option>
                <option value="0">No</option>
              </select>
            </div>
            <input type="submit" name="addTarget" value="Add Server" class="btn btn-primary">
          </form>

 			</div> <!-- /.col -->

      <div class="col-sm-8">
          <h3>New Notification</h3>
          <br>
          <?php if($methods == []){ ?>
            <br>
            <h4>No valid NEW notification methods have been set!</h4>
            <p>You either need to properly configure your <a class="blue" href="admin.php?view=email">Email Settings</a> or install and configure a push notifications plugin like <a class="blue"  href="admin.php?view=spice&search=pushover">Pushover Plugin</a> in order to receive notifications of offline sites. Note that you can only setup one pushover notification.</p>
          <?php }else{  ?>
          <form class="" action="" method="post">
            <input type="hidden" name="csrf" value="<?=Token::generate();?>">
            <label for="">Notification Type</label>
            <select class="" name="method" required id="method">
              <option value="" disabled selected="selected">--Choose--</option>
              <?php foreach($methods as $m) { ?>
                <option value="<?=$m?>"><?=ucfirst($m);?></option>
              <?php } ?>
            </select>
            <input type="text" name="target" id="target" value="" placeholder="email@domain.com" style="display:none;">
            <input type="text" name="twilio" id="twilio" value="" placeholder="+14448675309" style="display:none;">
            <input type="submit" name="addNotif" value="Add Notification" class="btn btn-primary">
          </form>
        <?php } ?>
        <br>
        <h3>Current Notifications</h3>
        <table class="table table-striped table-hover paginate">
          <thead>
            <tr>
              <th>Method</th>
              <th>Target</th>
              <th>Delete</th>
            </tr>
          </thead>
          <tbody>
            <?php $n = $db->query("SELECT * FROM plg_uptime_notifications WHERE disabled = 0")->results();

            foreach($n as $e){ ?>
              <tr>
                <td><?=ucfirst($e->method);?></td>
                <td>
                  <?php if($e->method != "pushover"){
                    echo $e->target;
                  }else{
                    echo "<a href='admin.php?view=plugins_config&plugin=pushover' class='blue'>See Plugin</a>";
                  } ?>
                </td>
                <td>
                  <form class="" action="admin.php?view=plugins_config&plugin=uptime" method="get"
                    onsubmit="return confirm('Do you really want to do this? It cannot be undone');">
                    <input type="hidden" name="view" value="plugins_config">
                    <input type="hidden" name="plugin" value="uptime">
                    <input type="hidden" name="delme" value="<?=$e->id?>">
                    <input type="submit" name="deleteNot" value="Delete" class="btn btn-danger">
                  </form>
                </td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
 			</div> <!-- /.col -->
 		</div> <!-- /.row -->
    <br>
<div class="row">
  <div class="col-12">
    <h3>Existing Sites</h3>
    <table class="table table-hover table-striped paginate">
      <thead>
        <tr>
          <th>Site</th><th>URL</th><th>Last Checked</th><th>US/WP</th><th>SW Ver</th><th>PHP Ver</th><th>Down Since</th><th>Outages</th><th>Total Downtime</th><th>Delete</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $sites = $db->query("SELECT * FROM plg_uptime ORDER BY disabled DESC, first_down DESC, site ASC")->results();
        foreach($sites as $s){
          $dt = $db->query("SELECT SUM(downtime) as dt FROM plg_uptime_downtime WHERE site = ?",[$s->id])->first();
          $dtC = $db->query("SELECT id FROM plg_uptime_downtime WHERE site = ?",[$s->id])->count();
        ?>
          <tr>
            <td><?=$s->site?></td>
            <td><?=$s->url?></td>
            <td><?=$s->last_check?></td>
            <td><?=bin($s->ustarget)?></td>
            <td><?=$s->usver?></td>
            <td><?=$s->phpver?></td>
            <td>
              <?php if($s->first_down != ""){
                echo "<font color='red'>".$s->first_down."</font>";
              }else{
                echo "-";
              }
              ?>

            </td>
            <td><?=$dtC?></td>
            <td><?php if($dt->dt != ""){ echo $dt->dt . " mins";}?></td>
            <td>
              <form class="" action="" method="get"
              onsubmit="return confirm('Do you really want to do this? It cannot be undone');"
                >
                <input type="hidden" name="view" value="plugins_config">
                <input type="hidden" name="plugin" value="uptime">
                <input type="hidden" name="delme" value="<?=$s->id?>">
                <input type="submit" name="delTarg" value="Delete" class="btn btn-danger">
              </form>
            </td>
          </tr>
        <?php } ?>
      </tbody>
    </table>


  </div>
</div>

<?php
$q = $db->query("SELECT * FROM logs WHERE logtype = ? ORDER BY id DESC LIMIT 10",['uptimeIP']);
$c = $q->count();

if($c > 0){
?>
<div class="row">
  <div class="col-12">
    <h3>Invalid Attempts</h3>
    <p>You may need to check your IP whitelist because your uptime.php has been hit by non-whitelisted IPs.</p>
    <?php
      tableFromQuery($q->results());
    ?>
  </div>
</div>
<?php } ?>

<script type="text/javascript">
$("#method").change(function () {
   var method = $(this).val();
   if(method == "email"){
     $("#target").show();
     $("#twilio").hide();
   }else if(method == "twilio"){
     $("#target").hide();
     $("#twilio").show();
   }else{
     $("#target").hide();
     $("#twilio").hide();
   }
});
</script>
<script type="text/javascript" src="<?=$us_url_root?>users/js/pagination/datatables.min.js"></script>
<script>
$(document).ready(function () {
  $('.paginate').DataTable({"pageLength": 25,"aLengthMenu": [[25, 50, 100, -1], [25, 50, 100, 250, 500]], "aaSorting": []});
});
</script>
