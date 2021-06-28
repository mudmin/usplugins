  <?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

<?php
include "plugin_info.php";
pluginActive($plugin_name);
$wdSettingsQ = $db->query("SELECT * FROM plg_watchdog_settings");
$wdSettingsC = $wdSettingsQ->count();
if($wdSettingsC < 1){
  die("Watchdog settings not found in the database. Please reinstall");
}else{
  $wdSettings = $wdSettingsQ->first();
}
$directory = $abs_us_root.$us_url_root."usersc/plugins/watchdog/assets/";
$funcFiles = glob($directory . "*.php");
$availableFuncs = [];
foreach($funcFiles as $f){
  include($f);
  if(isset($availableWatchdogs)){
    foreach($availableWatchdogs as $k=>$a){
      $availableFuncs[$k] = $a;
    }
    unset($availableWatchdogs);
  }
}
ksort($availableFuncs);
if(!empty($_POST)){
$token = $_POST['csrf'];
if(!Token::check($token)){
  include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
}
if(!empty($_POST['expireNow'])){
  $db->update("plg_watchdogs",Input::get('expireThis'),['wd_timeout'=>date("Y-m-d H:i:s",strtotime("-2 seconds",strtotime(date("Y-m-d H:i:s"))))]);
  logger($user->data()->id,"Watchdog", "Expired watchdog early ". Input::get('expireThis'));
  Redirect::to("admin.php?view=plugins_config&plugin=watchdog&err=Watchdog expired");
}
if(!empty($_POST['createWatchdog'])){
  $wd_func = Input::get("wd_func");
  if(!isset($availableFuncs[$wd_func])){
    Redirect::to("admin.php?view=plugins_config&plugin=watchdog&err=".$wd_func." not found in the available functions");
  }else{
    $seconds = $wdSettings->wd_time + 60;
    $timeout = date("Y-m-d H:i:s",strtotime("+$seconds seconds",strtotime(date("Y-m-d H:i:s"))));
    $fields = [
      'wd_created_by'=>$user->data()->id,
      'wd_created_on'=>date("Y-m-d H:i:s"),
      'wd_target_type'=>Input::get("wd_target_type"),
      'wd_targets'=>preg_replace("/\s+/", "", Input::get("wd_targets")),
      'wd_func'=>$wd_func,
      'wd_args'=>$_POST['wd_args'],
      'wd_timeout'=>$timeout,
      'wd_notes'=>Input::get('wd_notes')
    ];
    $db->insert("plg_watchdogs",$fields);
    logger($user->data()->id,"Watchdog Created",$db->lastId());
    if($timeout > $wdSettings->last_wd){
      $db->update("plg_watchdog_settings",$wdSettings->id,['last_wd'=>$timeout]);
    }
    Redirect::to("admin.php?view=plugins_config&plugin=watchdog&err=Watchdog created");
  }
}
if(!empty($_POST['updateSettings'])){
  $wdtime = Input::get('wdtime');

  if(is_numeric($wdtime) && $wdtime > 9){
    $db->update("plg_watchdog_settings",$wdSettings->id,["wd_time"=>$wdtime,"every_page"=>Input::get('every_page'),"tracking"=>Input::get('tracking')]);
    if($wdSettings->tracking == 0 && Input::get('tracking') == 1){
      $db->query("ALTER TABLE pages ADD COLUMN dwells bigint default 0");
      $db->query("ALTER TABLE users ADD COLUMN last_watchdog datetime");
      $db->query("ALTER TABLE users ADD COLUMN last_page varchar(255)");
    }
      Redirect::to("admin.php?view=plugins_config&plugin=watchdog&err=Watchdog settings updated");
  }
}
}
 $token = Token::generate();
 $wd = $db->query("SELECT * FROM plg_watchdogs ORDER BY wd_timeout DESC LIMIT 20")->results();
 ?>
<div class="content mt-3">
 		<div class="row">
 			<div class="col-sm-12">
          <a href="<?=$us_url_root?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
 					<h1>Watchdog Plugin</h1>
          <a href="<?=$us_url_root?>usersc/plugins/watchdog/sample.php" class="btn btn-primary">View User Tracking Samples</a>

 			</div> <!-- /.col -->
 		</div> <!-- /.row -->
    <br>
<div class="row">
<div class="col-12">
<div class="row">
  <div class="col-6">

    <form class="" action="" method="post">
      <input type="hidden" name="csrf" value="<?=$token?>">
      <div class="form-group">
        <label for="">How often should the watchdog fire (in seconds)?</label>
        <input type="number" class="form-control" name="wdtime" min="10" value="<?=$wdSettings->wd_time?>">
      </div>
      <div class="form-group">
        <label for="">Automatically include the watchdog timer in the UserSpice footer?</label>
        <select class="form-control" name="every_page">
          <option value="0" <?php if($wdSettings->every_page == 0){echo "selected = 'selected'";}?>>No</option>

          <option value="1" <?php if($wdSettings->every_page == 1){echo "selected = 'selected'";}?>>Yes</option>
        </select>
      </div>

      <div class="form-group">
        <label for="">Enable User Tracking Features (Current Page and Online Status)</label>
        <p>Note: These put a marginal load on the server, so the more active your site gets, it is suggested that you make your watchdog timer fire less often. Typical ranges are between 10 and 60 seconds.</p>
        <select class="form-control" name="tracking">
          <option value="0" <?php if($wdSettings->tracking == 0){echo "selected = 'selected'";}?>>No</option>

          <option value="1" <?php if($wdSettings->tracking == 1){echo "selected = 'selected'";}?>>Yes</option>
        </select>
      </div>
      <input type="submit" name="updateSettings" value="Update Settings" class="btn btn-primary">
    </form>
  </div>
</div>


  <h3>Create a new watchdog</h3>
  <br>
  <form class="" action="" method="post">
    <input type="hidden" name="csrf" value="<?=$token?>">
    <div class="form-group">
      <label for="">Select Watchdog Type</label><br>
      <select class="form-control" name="wd_func" id="wd_func" required>
        <option value="" disabled selected="selected">-- Choose a Function -- </option>
        <?php foreach($availableFuncs as $k=>$v){ ?>
          <option value="<?=$k?>"><?=$k?></option>
        <?php } ?>
      </select>
    </div>
    <div class="form-group">
      <label for="">Pass your arguments</label><br>
      <div id="instructions"> <br> </div>
      <textarea name="wd_args" id="wd_args" rows="3" class="form-control"></textarea>
    </div>
    <div class="form-group">
      <label for="">Select Your Target</label><br>
      <select class="form-control" name="wd_target_type" id="wd_target_type" required>
        <option value="" disabled selected="selected">-- Choose a Target -- </option>
        <option value="all">All Users</option>
        <option value="logged_in">Logged In Users</option>
        <option value="logged_out">Logged Out Users (Guests)</option>
        <option value="with_perm">Users with a Permission (comma separated)(experimental)</option>
        <option value="without_perm">Users without a Permission (comma separated)(experimental)</option>
        <option value="page">On a Specific Page (comma separated)</option>
      </select>
    </div>
    <div class="form-group" style="display:none;" id="wd_targets_group">
      <label for="">Enter your target permissions, users, or pages if necessary</label><br>
      <textarea name="wd_targets" id="wd_targets" rows="3" class="form-control"></textarea>
    </div>
    <div class="form-group">
      <label for="">Feel free to add notes for why you triggered this watchdog</label><br>
      <textarea name="wd_notes" rows="3" class="form-control"></textarea>
    </div>
    <div class="form-group">
      <input type="submit" name="createWatchdog" value="Create Watchdog" class="btn btn-primary">
    </div>
  </form>
</div>
</div>

<div class="row">
  <div class="col-12">
    <h3>Latest Watchdogs</h3>
    <table class="table table-striped table-hover">
      <thead>
        <tr>
          <th>Created By</th>
          <th>Created On</th>
          <th>Target Type</th>
          <th>Target</th>
          <th>Function</th>
          <th>Args</th>
          <th>Expires</th>
          <th>Notes</th>
          <th>Triggered</th>
          <th>Expire Now</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($wd as $w){ ?>
          <tr>
            <td><?=echouser($w->wd_created_by);?></td>
            <td><?=$w->wd_created_on?></td>
            <td><?=$w->wd_target_type?></td>
            <td><?=$w->wd_targets?></td>
            <td><?=$w->wd_func?></td>
            <td><?=$w->wd_args?></td>
            <td <?php if($w->wd_timeout < date("Y-m-d H:i:s")){?> style="color:red;" <?php } ?>>
              <?=$w->wd_timeout?></td>
            <td><?=$w->wd_notes?></td>
            <td><?=$w->wd_times_triggered?></td>
            <td>
              <form class="" action="" method="post">
                <input type="hidden" name="csrf" value="<?=$token?>">
                <input type="hidden" name="expireThis" value="<?=$w->id?>">
                <input type="submit" name="expireNow" value="Expire Now" class="btn btn-danger">
              </form>
            </td>
          </tr>

        <?php } ?>
      </tbody>
    </table>
  </div>
</div>
<div class="row">
  <div class="col-12">
    <h3>Documentation</h3>
        <p>If you appreciate this plugin and would like to make a donation to the author, you can do so at <a href="https://UserSpice.com/donate">https://UserSpice.com/donate</a>. Either way, thanks for using UserSpice!</p>
        <h4>One Plugin, Two Uses</h4>
        <p>This plugin is a bit of a Swiss Army Knife. Besides its original purpose of forcing actions to happen to logged in users, we've taken that same overhead and also added (optional) user tracking features.  See the bottom of this documentation for more information on those features.</p>

        <h4>Original Purpose</h4>
        <p>The purpose of this plugin is to force things to happen on a page even if the user does not refresh the page. This is most useful in SPAs (Single Page Applications) but could also be used if you want all your users to logout or refresh or even if you have some sort of "event" starting and on your site and you want all your users to go to that place.</p>
        <p>It works on a watchdog timer where the user checks in to see if you have anything for them to do every x number of seconds. The default is 120 but can be as low as 10.  Obviously choose something that balances the load on your server and your need for immediate action. Note that the watchdog parser will fire on page load, so that could prove helpful if you redirect from one of your pages to another and need multiple watchdogs to fire off.</p>

        <h4>Adding your own functions</h4>
        <p>The watchdog plugin is designed to fire off JavaScript functions and requires jQuery to work.  The JavaScript is important because these functions are called without refreshing the page (client side). Functions will automatically be loaded from the assets folder of the plugin.  You can have as many .php files as you would like. Many people may want a "core" set of functions that are available on every project and additional php files for project-specific js functions.</p>
        <p>Look at default.php for guidance but you will see that adding a new function is a 1 or 2 step process. At the top of the file you will "define" your new function and explain how to pass it arguments. You will see that I've given examples that pass nothing, a simple string and a JSON string. If your function is a built in JavaScript function such as alert, you don't need to do anything else. If it is not, you need to write your function below. Note that your "function" can simple be something that parses your arguments into a real function that you've already declared. You don't necessarily need to store all your functions in this file. Just parsers to fire them off.</p>

        <h4>Creating a Watchdog</h4>
        <p>You must understand that watchdogs are designed for people who are already on your site and they have an intentionally short lifespan. They're not made to hang out until someone visits your site next time. They only last one round of your watchdog time (that 120 seconds by default) + 60 seconds to deal with slow page loads etc.  If your user qualifies to be "hit" by multiple watchdogs, only the first one will hit per cycle.</p>
        <p>To create a watchdog, go through the form on the control panel and decide what function you want to fire, what arguments you need to pass it if any and who should be targeted by the watchdog.  That's it!</p>

        <h4>Including the Watchdog Timer</h4>
        <p>There is a setting to optionally include the watchdog timer on every UserSpice page.  However, to reduce server load, you may decide to only include it on certain pages.  You may also need to do this for pages that do not include the UserSpice footer.  To include the watchdog add the line watchdogHere(); in php tags near the bottom of your page</p>
        <p>You can take a look at these watchdogs in action in your Chrome Inspector in the console and network tabs. </p>

        <h4>New User Tracking Features</h4>
        <p>A new user tracking feature allows you to see who is online and what page they are on (if that page is in the database).  If you enable the user tracking, it includes a hook that allows you to see this info in <a href="<?=$us_url_root?>users/admin.php?view=users">the user manager</a>. </p>

      </div>

</div>
<script type="text/javascript">
$(document).ready(function() {
$( "#wd_func" ).change(function() { //use event delegation
  var value = $(this).val();
  console.log(value);
  var formData = {
    'value' 				: value
  };

  $.ajax({
    type 		: 'POST',
    url 		: '<?=$us_url_root?>usersc/plugins/watchdog/get_func_args.php',
    data 		: formData,
  })

  .done(function(data) {
      $("#instructions").html(data);
      $("#wd_args").val("");
  })
});

$( "#wd_target_type" ).change(function() {
    var value = $(this).val();
      $("#wd_targets").val("");
    if(value == "with_perm" || value == "without_perm" || value == "page"){
      $("#wd_targets_group").show();
    }else{
      $("#wd_targets_group").hide();
    }
});

});
</script>
