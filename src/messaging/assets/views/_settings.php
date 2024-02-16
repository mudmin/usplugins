<?php
$msgSettings = $db->query("SELECT * FROM plg_msg_settings")->first();
//grab all mp3s from assets/sounds folder
$sounds = glob($abs_us_root . $us_url_root . 'usersc/plugins/messaging/assets/sounds/*.mp3');
foreach ($sounds as $k => $v) {
  $sounds[$k] = str_replace($abs_us_root . $us_url_root . 'usersc/plugins/messaging/assets/sounds/', '', $v);
}

$qs = http_build_query($_GET);


if (!empty($_POST['update_messages_settings_hook'])) {
  $fields = [
    'ding' => Input::get('ding'),
    'ajax' => Input::get('ajax'),
    'ajax_time' => Input::get('ajax_time'),
    'alerts' => Input::get('alerts'),
    'alerts_sound' => Input::get('alerts_sound'),
    'messages' => Input::get('messages'),
    'messages_sound' => Input::get('messages_sound'),
    'notifications' => Input::get('notifications'),
    'notifications_sound' => Input::get('notifications_sound'),
    'notifications_if_none' => Input::get('notifications_if_none'),
    'alerts_if_none' => Input::get('alerts_if_none'),
    'messages_if_none' => Input::get('messages_if_none'),


  ];
  $db->update("plg_msg_settings", 1, $fields);

  usSuccess("Settings saved");
  Redirect::to(currentPage() . "?" . $qs);
}
?>
<form action="" method="post">
  <h3 class="mt-2">Settings

    <button type="submit" class="btn btn-outline-primary btn-sm float-right">Save Settings</button>
  </h3>
  <div class="row">
    <div class="col-12 col-md-6">
      <div class="card">
        <div class="card-header">
          <h5>General Settings</h5>
        </div>
        <div class="card-body">
          <div class="form-group mb-3">
            <label for="">Enable Alerts</label>
            <select class="form-select" name="alerts">
              <option value="1" <?php if ($msgSettings->alerts == "1") {
                                  echo "selected";
                                } ?>>Yes</option>
              <option value="0" <?php if ($msgSettings->alerts == "0") {
                                  echo "selected";
                                } ?>>No</option>
            </select>
          </div>
          <div class="form-group mb-3">
            <label for="">Enable Messages</label>
            <select class="form-select" name="messages">
              <option value="1" <?php if ($msgSettings->messages == "1") {
                                  echo "selected";
                                } ?>>Yes</option>
              <option value="0" <?php if ($msgSettings->messages == "0") {
                                  echo "selected";
                                } ?>>No</option>
            </select>
          </div>

          <div class="form-group mb-3">
            <label for="">Enable Notifications</label>
            <select class="form-select" name="notifications">
              <option value="1" <?php if ($msgSettings->notifications == "1") {
                                  echo "selected";
                                } ?>>Yes</option>
              <option value="0" <?php if ($msgSettings->notifications == "0") {
                                  echo "selected";
                                } ?>>No</option>
            </select>
          </div>
        </div>
      </div>

      <div class="card mt-3">
        <div class="card-header">
          <h5>Menu Hook</h5>
        </div>
        <div class="card-body">
          <div class="form-group">
            <label for="">Show Alerts in Header if None</label>
            <select class="form-select" name="alerts_if_none">
              <option value="1" <?php if ($msgSettings->alerts_if_none == "1") {
                                  echo "selected";
                                } ?>>Yes</option>
              <option value="0" <?php if ($msgSettings->alerts_if_none == "0") {
                                  echo "selected";
                                } ?>>No</option>
            </select>
          </div>
          <div class="form-group mb-3">
          <label for="">Show Messages in Header if None</label>
            <select class="form-select" name="messages_if_none">
              <option value="1" <?php if ($msgSettings->messages_if_none == "1") {
                                  echo "selected";
                                } ?>>Yes</option>
              <option value="0" <?php if ($msgSettings->messages_if_none == "0") {
                                  echo "selected";
                                } ?>>No</option>
            </select>
          </div>

          <div class="form-group mb-3">
          <label for="">Show Notifications in Header if None</label>
            <select class="form-select" name="notifications_if_none">
              <option value="1" <?php if ($msgSettings->notifications_if_none == "1") {
                                  echo "selected";
                                } ?>>Yes</option>
              <option value="0" <?php if ($msgSettings->notifications_if_none == "0") {
                                  echo "selected";
                                } ?>>No</option>
            </select>
          </div>
        </div>
      </div>

    </div>

    <div class="col-12 col-md-6">
    <div class="card mb-3">
        <div class="card-header">
          <h5>Ajax Polling</h5>
        </div>
        <div class="card-body">
          <div class="form-group mb-3">
            <div class="form-group mb-3">
              <label for="">Use Ajax to Poll for new notifications</label>
              <select class="form-select" name="ajax">
                <option value="1" <?php if ($msgSettings->ajax == "1") {
                                    echo "selected";
                                  } ?>>Yes</option>
                <option value="0" <?php if ($msgSettings->ajax == "0") {
                                    echo "selected";
                                  } ?>>No</option>
              </select>
            </div>

            <div class="form-group mb-3">
              <label for="">Check for new notifications every x seconds</label>
              <input type="number" name="ajax_time" class="form-control" value="<?= $msgSettings->ajax_time ?>" min="5" step="1">
              <small class="form-text text-muted">This is in seconds. The default is 60 seconds.</small>
            </div>
          </div>
        </div>
      </div>

    <div class="card">
        <div class="card-header">
          <h5>Sounds</h5>
        </div>
        <div class="card-body">
          <div class="form-group mb-3">
            <?= tokenHere(); ?>
            <div class="form-group">
              <input type="hidden" name="update_messages_settings_hook" value="1">
              <label for="">Play sounds for new notification</label>
              <select class="form-select" name="ding">
                <option value="1" <?php if ($msgSettings->ding == "1") {
                                    echo "selected";
                                  } ?>>Yes</option>
                <option value="0" <?php if ($msgSettings->ding == "0") {
                                    echo "selected";
                                  } ?>>No</option>
              </select>
        
            </div>


            <div class="form-group mb-3">
              <label for="">Alerts Sound</label>
              <select class="form-select" name="alerts_sound" readonly disabled>
                <?php foreach ($sounds as $k => $v) { ?>
                  <option value="<?= $v ?>" <?php if ($msgSettings->alerts_sound == $v) {
                                            echo "selected";
                                          } ?>><?= $v ?></option>
                <?php } ?>
              </select>
            </div>



            <div class="form-group mb-3">
              <label for="">Messages Sound</label>
              <select class="form-select" name="messages_sound" readonly disabled>
                <?php foreach ($sounds as $k => $v) { ?>
                  <option value="<?= $v ?>" <?php if ($msgSettings->messages_sound == $v) {
                                            echo "selected";
                                          } ?>><?= $v ?></option>
                <?php } ?>
              </select>
            </div>



            <div class="form-group mb-3">
              <label for="">Notifications Sound</label>
              <select class="form-select" name="notifications_sound" readonly disabled>
                <?php foreach ($sounds as $k => $v) { ?>
                  <option value="<?= $v ?>" <?php if ($msgSettings->notifications_sound == $v) {
                                            echo "selected";
                                          } ?>><?= $v ?></option>
                <?php } ?>
              </select>
            </div>

            <small class="form-text text-muted">Add your own small mp3 files to usersc/plugins/messaging/assets/sounds for additional notificaiton sounds.</small>
          </div>
        </div>
      </div>
    </div>
    <p class="pt-2">If you appreciate this plugin and would like to make a donation to the author, you can do so at <a style="color:blue;" href="https://UserSpice.com/donate">https://UserSpice.com/donate</a>. Either way, thanks for using UserSpice!</p>