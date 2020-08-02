  <?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

<?php
include "plugin_info.php";
pluginActive($plugin_name);
 if(!empty($_POST['plugin_session_manager'])){
   $token = $_POST['csrf'];
if(!Token::check($token)){
  include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
}
 // Redirect::to('admin.php?err=I+agree!!!');
}

$showAllSessions = Input::get('showAllSessions');
$errors=[];
$successes=[];
if (!empty($_POST)) {
  $token = $_POST['csrf'];
  if(!Token::check($token)){
    include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
  }
  if(!empty($_POST['save'])){
    $sm = Input::get('session_manager');
    if($sm == 0 || $sm == 1){
      $db->update('settings',1,['session_manager'=>$sm]);

    }
    $sess = Input::get('one_sess');
    if($sess == 0 || $sess == 1){
      $db->update('settings',1,['one_sess'=>$sess]);
    }
      Redirect::to('admin.php?view=plugins_config&plugin=session_manager&err=Saved');
  }

  if(!empty($_POST['sessionChange'])) {

    if(isset($_POST['deleteAllSessions'])) {
      $db->query("TRUNCATE TABLE us_user_sessions");
      if(!$db->error()) {
        logger($user->data()->id,"User Tracker","Deleted all Session Records and reset the table.");
        $successes[] = "Deleted all sessions";
      } else {
        $error=$db->errorString();
        logger($user->data()->id,"User Tracker","Failure deleting all session records, Error: ".$error);
        $errors[] = "Failure deleting all sessions, Error: ".$error;
      }
    }

    if(isset($_POST['killAllSessions']) && in_array($user->data()->id,$master_account)) {
      $db->query("UPDATE us_user_sessions SET UserSessionEnded=1,UserSessionEnded_Time=NOW() WHERE UserSessionEnded=0 AND kUserSessionID <> ?",[$_SESSION['kUserSessionID']]);
      if(!$db->error()) {
        logger($user->data()->id,"User Tracker","Killed all Sessions.");
        $successes[] = "Killed all Sessions";
      } else {
        $error=$db->errorString();
        logger($user->data()->id,"User Tracker","Failure killing all sessions, Error: ".$error);
        $errors[] = "Failure killing all sessions, Error: ".$error;
      }
    }

    if(isset($_POST['killSession'])) {
      $sessions = Input::get('killSession');
      $kill = killSessions($sessions,true);
      if($kill) {
        if($kill==1) $successes[] = "Killed 1 Session";
        else $successes[] = "Killed $kill Sessions";
      }
    }
  }
}
 $token = Token::generate();
 ?>
<div class="content mt-3">
 		<div class="row">
 			<div class="col-sm-12">
          <a href="<?=$us_url_root?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
<div class="content mt-3">
  <h2>Session Administrator</h2>
  <hr>
  <?=resultBlock($errors,$successes);?>
  <div class="row">
    <div class="col-12 col-sm-6">
      <form class="" action="admin.php?view=plugins_config&plugin=session_manager" method="POST">
        <div class="form-group">
          <label for="session_manager">Enable/Disable the session manager feature (not the plugin itself)</label>
          <select class="" name="session_manager">
            <option value="0" <?php if($settings->session_manager == 0){echo "selected='selected'";}?>>Disabled</option>
            <option value="1" <?php if($settings->session_manager == 1){echo "selected='selected'";}?>>Enabled</option>
          </select><br>
          <label for="one_sess">Force each user to be logged in only one place at a time</label>
          <select class="" name="one_sess">
            <option value="0" <?php if($settings->one_sess == 0){echo "selected='selected'";}?>>No</option>
            <option value="1" <?php if($settings->one_sess == 1){echo "selected='selected'";}?>>Yes</option>
          </select><br>
          <input class='btn-primary' type='submit' name='save' value='Save Settings' />
          <input type="hidden" value="<?=$token?>" name="csrf">
        </div>
      </form>
    </div>
    <div class="col-12 col-sm-6">
        <strong>Important Note:</strong> Disabling the plugin does NOT fully disable session management features.  There are two ways to do that.<br>
        1. Using the toggle above.<br>
        2. Clicking both the deactivate and uninstall buttons in the plugin manager.  <br>
        It is not recommended to leave this plugin in the disabled state with session management itself enabled.
    </div>
  </div>

  <form autocomplete="off" class="verify-admin" action="admin.php?view=plugins_config&plugin=session_manager" method="POST">
    <h4>Active Sessions</h4>
    <table class="table table-bordered">
      <?php
      if($showAllSessions!=1) $sessions = fetchAdminSessions();
      else $sessions = fetchAdminSessions(true);
      if($sessions) { ?>
        <tr>
          <th width="10%">User</th>
          <th width="30%">Information</th>
          <th width="15%">Recorded</th>
          <th width="35%">Last Action</th>
          <th width="10%">Kill</th>
        </tr>
        <?php foreach($sessions as $session) { ?>
          <tr>
            <td><?=echousername($session->fkUserID)?></td>
            <td>
              <?=$session->UserSessionBrowser?> on <?=$session->UserSessionOS?> <?php if($session->kUserSessionID==$_SESSION['kUserSessionID']) {?><sup>Current Session</sup><?php } ?><br>
              <?php if($session->UserSessionIP!='::1') {
                $geo = json_decode(file_get_contents("http://extreme-ip-lookup.com/json/$session->UserSessionIP"));
                $country = $geo->country;
                $city = $geo->city;
                $ipType = $geo->ipType;
                $businessName = $geo->businessName;
                $businessWebsite = $geo->businessWebsite;

                echo "Location of $session->UserSessionIP: $city, $country\n";
              } ?>
            </td>
            <td><span class="show-tooltip" title="<?=date("D, M j, Y g:i:sa",strtotime($session->UserSessionStarted))?>"><?=time2str($session->UserSessionStarted)?></span></td>
            <td><?=$session->UserSessionLastPage?> <span class="show-tooltip" title="<?=date("D, M j, Y g:i:sa",strtotime($session->UserSessionLastUsed))?>"><?=time2str($session->UserSessionLastUsed)?></span></td>
            <td>
              <?php if($session->kUserSessionID!=$_SESSION['kUserSessionID'] && $session->UserSessionEnded!=1) {?>
                <span class="button-checkbox">
                  <button type="button" class="btn" data-color="warning" style="border-radius: 8px;"></button>
                  <input type="checkbox" class="hidden" name="killSession[]" value="<?=$session->kUserSessionID?>" style="display: none;" />
                </span>
              <?php } ?>
            </td>
          </tr>
        <?php } ?>
        <tr>
          <td colspan='4'>
            <div class="col col-md-6">
              <label><input type="checkbox" name="killAllSessions" id="killAllSessions" class="killAllSessions" value="1" <?php if(!in_array($user->data()->id,$master_account)) {?>disabled<?php } ?>/> Kill All Sessions</label>
              <br><font color='red'><strong>Urgent / Attention</strong></font> This is an <strong>extremely</strong> powerful function and will almost instantly log every user on your site out. Any data entered and not saved will be instantly lost.
            </div>

            <div class="col col-md-6">
              <label><input type="checkbox" name="deleteAllSessions" value="1" /> Delete All Sessions</label>
              <br>This is less powerful version of the latter. No sessions will be logged out, however Session Data will be re-entered and the table will begin at ID #1 again.
            </div>
          </td>
          <td>
            <input class='btn btn-primary pull-right' type='submit' name='sessionChange' value='Submit' />
            <input type="hidden" value="<?=$token?>" name="csrf">
          </td>
        </tr>
      <?php } else { ?>
        <tr><td><center>No Fingerprints Found</center></td></tr><?php } ?>
      </table>
      <?php if($showAllSessions!=1) {?><a href="?view=sessions&showAllSessions=1" class="btn btn-primary nounderline pull-right">Show All Recorded Sessions</a><?php } ?>
      <?php if($showAllSessions==1) {?><a href="?view=sessions" class="btn btn-primary nounderline pull-right">Show Active Sessions Only</a><?php } ?>
    </div>
  </form>
  <br><br>
    If you appreciate this plugin and would like to make a donation to the author, you can do so at <a href="https://UserSpice.com/donate">https://UserSpice.com/donate</a>. Either way, thanks for using UserSpice!
<br><br>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js" integrity="sha256-4F7e4JsAJyLUdpP7Q8Sah866jCOhv72zU5E8lIRER4w=" crossorigin="anonymous"></script>
  <script>
  $(function () {
    $('.button-checkbox').each(function () {

      // Settings
      var $widget = $(this),
      $button = $widget.find('button'),
      $checkbox = $widget.find('input:checkbox'),
      color = $button.data('color'),
      settings = {
        on: {
          icon: 'fa fa-square-o'
        },
        off: {
          icon: 'fa fa-check-square-o'
        }
      };

      // Event Handlers
      $button.on('click', function () {
        $checkbox.prop('checked', !$checkbox.is(':checked'));
        $checkbox.triggerHandler('change');
        updateDisplay();
      });
      $checkbox.on('change', function () {
        updateDisplay();
      });

      // Actions
      function updateDisplay() {
        var isChecked = $checkbox.is(':checked');

        // Set the button's state
        $button.data('state', (isChecked) ? "on" : "off");

        // Set the button's icon
        $button.find('.state-icon')
        .removeClass()
        .addClass('state-icon ' + settings[$button.data('state')].icon);

        // Update the button's color
        if (isChecked) {
          $button
          .removeClass('btn-default')
          .addClass('btn-' + color + ' active');
        }
        else {
          $button
          .removeClass('btn-' + color + ' active')
          .addClass('btn-default');
        }
      }

      // Initialization
      function init() {

        updateDisplay();

        // Inject the icon if applicable
        if ($button.find('.state-icon').length == 0) {
          $button.prepend('<i class="state-icon ' + settings[$button.data('state')].icon + '"></i>');
        }
      }
      init();
    });
  });

  $(document).on("click", ".killAllSessions", function(e) {
    if($(".killAllSessions").is(':checked')) {
      bootbox.confirm({
        size: "medium",
        message: "Are you sure you want to kill all sessions?",
        callback: function(result){
          if(result) {
            $('.killAllSessions').prop('checked', true);
          } else {
            $('.killAllSessions').prop('checked', false);
          }
        }
      })
    }
  });
  </script>

 			</div> <!-- /.col -->
 		</div> <!-- /.row -->
