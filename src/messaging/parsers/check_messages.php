<?php 
$resp['success'] = false;
$resp["alert_count"]= 0;
$resp["notification_count"]= 0;
$resp["message_count"]= 0;
$resp["total_count"]= 0;
$resp['preview'] = "";
require_once "../../../../users/init.php";
if(!isset($user) || !$user->isLoggedIn()){
    echo json_encode($resp);die;
}

if(isset($config['session']['session_name'])){
  $sn = $config['session']['session_name'];
}else{
  $sn = "";
}

$notifCount = fetchPlgMessageCount();
$allowDing = true;
$alertsSound = "";
if(!isset($_SESSION[$sn."msgSettings"])){
    // $allowDing = false;
    $plg_settings = $db->query("SELECT * FROM plg_msg_settings")->first();
    $_SESSION[$sn."msgSettings"] = $plg_settings;
    $_SESSION[$sn."max_alert_id"] = $notifCount->max_alert_id;
    $_SESSION[$sn."max_notification_id"] = $notifCount->max_notification_id;
    $_SESSION[$sn."max_message_id"] = $notifCount->max_message_id;
}else{
    $plg_settings = $_SESSION[$sn."msgSettings"];
}

foreach($notifCount as $key=>$value){
    $resp[$key] = $value;
}
$resp['success'] = true;

if($allowDing && $notifCount->max_alert_id > $_SESSION[$sn."max_alert_id"] && $plg_settings->alerts_sound != ""){
    $alertsSound = $plg_settings->alerts_sound;
    $_SESSION[$sn."max_alert_id"] = $notifCount->max_alert_id;
}

if($allowDing && $notifCount->max_notification_id > $_SESSION[$sn."max_notification_id"] && $plg_settings->notifications_sound != ""){
    $alertsSound = $plg_settings->notifications_sound;
    $_SESSION[$sn."max_notification_id"] = $notifCount->max_notification_id;
}


if($allowDing && $notifCount->max_message_id > $_SESSION[$sn."max_message_id"] && $plg_settings->messages_sound != ""){
  
    $alertsSound = $plg_settings->messages_sound;

    $_SESSION[$sn."max_message_id"] = $notifCount->max_message_id;
}

$resp['alertsSound'] = $alertsSound;


$preview = Input::get('preview');

if($preview == "true"){
    $notifications = fetchPLGMessages(500);
 
    ob_start();
    if ($notifCount->total_count > 0) { ?>
        <div class="row">
          <div class="col-6 ps-3" style="color:black; font-weight:600;">
            <small>Item / From</small>
          </div>
          <div class="col-6 pe-3 text-end" style="color:black; font-weight:600;">
            <small>Sent Date / Time</small>
          </div>
        </div>
        <table class="table omt-table-nolines">

          <tbody>
            <?php
            $counter = 0;
            foreach ($notifications as $n) {
              $counter++;
              if ($counter > 5) {
                break;
              }
              if ($n->msg_read == 0) {
                $class = " unread ";
              } else {
                $class = "";
              }

            ?>
              <tr class="msg<?= $n->id ?>">

                <td>
                  <?php if ($n->msg_type == 1) { ?>
                    <i class="fas fa-1x fa-exclamation-circle text-danger" style="font-size:1rem;"></i>
                  <?php } elseif ($n->msg_type == 2) { ?>
                    <i class="fas fa-bell text-warning" style="font-size:1rem;"></i>
                  <?php } elseif ($n->msg_type == 3) { ?>
                    <i class="fas fa-envelope text-primary" style="font-size:1rem;"></i>
                  <?php } ?>
                  <a class="notifTitle openMessageButton <?= $class ?>" data-initial-category="all" data-message-id="<?= $n->id ?>"><?= $n->title ?>
                  </a><br>
                  <?php if ($n->user_from == 0) { ?>
                    <span class="notifFrom">System Message</span>
                  <?php } else { ?>
                    <span class="notifFrom"><?= $n->fname ?> <?= $n->lname ?></span>
                  <?php } ?>
                </td>
                <td class="text-end text-right pull-right">
                  <span class="notifDT">
                    <?= $n->date_only; ?><br>
                  </span>
                  <span class="notifDT">
                    <?= $n->time_only; ?>
                  </span>
                </td>
              </tr>
            <?php } ?>

          </tbody>
        </table>
        <?php
        if ($notifCount->total_count > 5) {
          $more = $notifCount->total_count - 5;
        ?>
          <div class="mb-2">
            <a class="ps-3 openMessageButton" href="#"><?= $more ?> more...</a>
          </div>

        <?php
        }
        
        ?>
        <div class="row">
          <div class="col-12 px-4 pb-3">
            <button class="btn btn-primary btn-sm col-12 openMessageButton">View All (<?=$notifCount->total_count?>)</button>
          </div>
        </div>

        <?php } else { 
        }      
        $resp['preview'] = ob_get_clean();
}


echo json_encode($resp);die;