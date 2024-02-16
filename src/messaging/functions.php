<?php
// type 1 = alert
// type 2 = notification
// type 3 = message

function sendPlgMessage($user_to, $title, $message, $user_from = 0, $type = 1, $expires = "", $send_method = "standard")
{
    global $db, $user;


    if (is_array($user_to)) {
        $to_count = count($user_to);
    } else {
        $to_count = 1;
    }
    $send_method = ucwords(str_replace("_", " ", $send_method));
    $fields = [
        'user_from' => $user_from,
        'title' => $title,
        'msg' => $message,
        'msg_type' => $type,
        'recipients' => $to_count,
        'msg_sent_on' => date("Y-m-d H:i:s"),
        'send_method' => $send_method,
    ];
    if ($user_from == 0) {
        if (isset($user) && $user->IsLoggedIn()) {
            $fields['sent_by'] = $user->data()->id;
        }
    }
    if ($expires != "") {
        $fields['msg_expires_on'] = $expires;
        $fields['expires'] = 1;
    }
    $db->insert("plg_msg_messages", $fields);
    $msg_id = $db->lastId();
    if (is_array($user_to)) {
        foreach ($user_to as $to) {
            $fields = [
                'user_to' => $to,
                'msg_id' => $msg_id,
            ];
            $db->insert("plg_msg", $fields);
        }
    } else {
        $fields = [
            'user_to' => $user_to,
            'msg_id' => $msg_id,
        ];
        $db->insert("plg_msg", $fields);
    }
}

function fetchPlgMessageCount()
{
    global $db, $user;
    $msgCount = $db->query("SELECT 
        COALESCE(SUM(CASE WHEN m.msg_type = 1 THEN IF(n.msg_read = 0, 1, 0) ELSE 0 END), 0) AS alert_count,
        COALESCE(SUM(CASE WHEN m.msg_type = 2 THEN IF(n.msg_read = 0, 1, 0) ELSE 0 END), 0) AS notification_count,
        COALESCE(SUM(CASE WHEN m.msg_type = 3 THEN IF(n.msg_read = 0, 1, 0) ELSE 0 END), 0) AS message_count,
        COALESCE(COUNT(n.id), 0) AS total_count,
        MAX(CASE WHEN m.msg_type = 1 THEN n.id ELSE NULL END) AS max_alert_id, -- Highest ID for type 1
        MAX(CASE WHEN m.msg_type = 2 THEN n.id ELSE NULL END) AS max_notification_id, -- Highest ID for type 2
        MAX(CASE WHEN m.msg_type = 3 THEN n.id ELSE NULL END) AS max_message_id -- Highest ID for type 3
        FROM plg_msg n
        LEFT OUTER JOIN plg_msg_messages m ON n.msg_id = m.id
        WHERE n.user_to = ? AND n.deleted = 0 AND m.expired = 0", [$user->data()->id])->first();

    return $msgCount;
}


function fetchPLGMessages($limit)
{
    global $db, $user;
    $msgs = $db->query("SELECT
    n.*,
    m.user_from,
    m.title,
    m.expired,
    m.msg AS `message`,
    m.msg_class AS `class`,
    DATE_FORMAT(m.msg_sent_on, '%a %b %d, %Y - %h:%i %p') AS `date`,
    DATE_FORMAT(m.msg_sent_on, '%d/%m/%Y') AS `date_only`,
    DATE_FORMAT(m.msg_sent_on, '%h:%i %p') AS `time_only`,
    m.msg_type AS `msg_type`,
    u.fname,
    u.lname
FROM plg_msg n
LEFT OUTER JOIN plg_msg_messages m ON n.msg_id = m.id
LEFT OUTER JOIN users u ON m.user_from = u.id
WHERE n.user_to = ? AND n.deleted = 0 AND m.expired = 0
GROUP BY n.id
ORDER BY n.msg_read ASC, n.id DESC
LIMIT $limit", [$user->data()->id])->results();
    return $msgs;
}

function fetchPLGMessage($id)
{

    global $db, $user;
    $msgQ = $db->query("SELECT
    n.*,
    m.user_from,
    m.title,
    m.msg AS `message`,
    m.msg_class AS `class`,
    m.msg_sent_on AS `date`,
    m.msg_type AS `msg_type`,
    CONCAT(u.fname, ' ', u.lname) AS `from_name`
FROM plg_msg n
LEFT OUTER JOIN plg_msg_messages m ON n.msg_id = m.id
LEFT OUTER JOIN users u ON m.user_from = u.id
WHERE n.user_to = ? AND n.id = ?", [$user->data()->id, $id]);


    $msgC = $msgQ->count();

    if ($msgC == 0) {
        return false;
    }
    $msg = $msgQ->first();
    $msg->message = hed($msg->message);
    if ($msg->msg_read == 0) {
        $fields = [
            'msg_read' => 1,
            'msg_read_on' => date("Y-m-d H:i:s"),
        ];
        $db->update("plg_msg", $id, $fields);
    }
    if($msg->from_name == null){
        $msg->from_name = "System Message";
    }
    $msg->date = formatMsgDate($msg->date);
    return $msg;
}

function formatMsgDate($datetime,$hours = true)
{
  if ($datetime == null || $datetime == "") {
    return "";
  }
  if(!$hours){
    return date("D M d, Y", strtotime($datetime));
  }
  return date("D M d, Y - g:i a", strtotime($datetime));
}

if (!function_exists("hed")) {
    function hed($string) 
    {
      return htmlspecialchars_decode(html_entity_decode($string ?? "", ENT_QUOTES, "UTF-8"));
    }
  }

function processPlgMessagesTheme($p){
    //establish fallbacks
    $t = [
      'openMessageButtonBG' => '#dbdbdb',
      'openMessageButton' => 'ms-2 me-2',
      'messageCountColor' => '#000000',
      'alertIcon'=>'fas pe-2 fa-lg fa-exclamation-circle text-danger',
      'notifIcon'=>'fas pe-2 fa-lg fa-bell text-warning',
      'messageIcon'=>'fas pe-2 fa-lg fa-envelope text-primary', 
      'iconSizeSize'=>'font-size:1.1rem;',
      'counterFontSize'=>'font-size:1.1rem;  line-height: 1.5;',
      
    ];
    // overwrite fallbacks with any passed vars that match
    foreach($p as $k=>$v){
        if(array_key_exists($k,$t)){
            $t[$k] = $v;
        }
    }
    return (object)$t;
    }    




//minified for compatibility with menu_hooks
function displayNotificationsBadges($from_menu = false, $theme = "lightbg", $header = false){
global $user, $abs_us_root, $us_url_root, $allowed_to_send_notif, $item, $plgMessagesTheme, $msgSettings;

if(isset($user) && $user->isLoggedIn()){
$notifCount= fetchPlgMessageCount();
if(isset($item->menu)){
    $data_menu = "data-menu=\"{$item->menu}\"";
}else{
    $data_menu = "";
}

$messagesHide = "";
$notificationsHide = "";
$alertsHide = "";

if($from_menu){
    $begin = "<li class=\"plg-messaging-item pe-2\" {$data_menu}>";
    $end = "</li>";
    if(isset($msgSettings->messages_if_none) && $msgSettings->messages_if_none == 0 && $notifCount->message_count == 0){
        $messagesHide = "d-none";
    }

    if(isset($msgSettings->notifications_if_none) && $msgSettings->notifications_if_none == 0 && $notifCount->notification_count == 0){
        $notificationsHide = "d-none";
    }

    if(isset($msgSettings->alerts_if_none) && $msgSettings->alerts_if_none == 0 && $notifCount->alert_count == 0){
        $alertsHide = "d-none";
    }

}else{
    $begin = "";
    $end = "";
}


if(file_exists($abs_us_root . $us_url_root . 'usersc/plugins/messaging/themes/'.$theme.'.php')){
    include $abs_us_root . $us_url_root . 'usersc/plugins/messaging/themes/'.$theme.'.php';
}

$t = processPlgMessagesTheme($plgMessagesTheme);

?>
<?=$begin?>
<?php if(isset($msgSettings) && $msgSettings->alerts == 1){ ?>
<span class="badge openMessageButton <?=$t->openMessageButton?> <?=$alertsHide?>" data-initial-category="1" style="background-color:<?=$t->openMessageButtonBG?>">
    <i class="<?=$t->alertIcon?>" style="<?=$t->iconSizeSize?>"></i>
    <span class="notifCount type-1" style="color: <?=$t->messageCountColor?> !important; <?=$t->counterFontSize?>">
        <?= $notifCount->alert_count ?>
    </span>
</span>
<?php } 
if(isset($msgSettings) && $msgSettings->notifications == 1){
?>
<span class="badge openMessageButton <?=$t->openMessageButton?> <?=$notificationsHide?> " data-initial-category="2" style="background-color:<?=$t->openMessageButtonBG?>">
    <i class="<?=$t->notifIcon?>" style="<?=$t->iconSizeSize?>"></i>
    <span class="notifCount type-2" style="color: <?=$t->messageCountColor?> !important; <?=$t->counterFontSize?>">
        <?= $notifCount->notification_count ?>
    </span>
</span>
<?php }
if(isset($msgSettings) && $msgSettings->messages == 1){ 
?>
<span class="badge openMessageButton <?=$t->openMessageButton?> <?=$messagesHide?> " data-initial-category="3" style="background-color:<?=$t->openMessageButtonBG?>">
    <i class="<?=$t->messageIcon?>" style="<?=$t->iconSizeSize?>"></i>
    <span class="notifCount type-3" style="color: <?=$t->messageCountColor?> !important; <?=$t->counterFontSize?>">
        <?= $notifCount->message_count ?>
    </span>
</span>
<?php } ?>
<?=$end?>
<?php } 
  }
  