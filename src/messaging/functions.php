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
            // Invalidate cache for each recipient
            invalidatePlgMessageCache($to);
        }
    } else {
        $fields = [
            'user_to' => $user_to,
            'msg_id' => $msg_id,
        ];
        $db->insert("plg_msg", $fields);
        // Invalidate cache for the recipient
        invalidatePlgMessageCache($user_to);
    }
}

function fetchPlgMessageCount($useCache = true, $cacheMaxAge = 30)
{
    global $db, $user;
    $userId = $user->data()->id;

    // Try to get from cache first (if enabled and cache is fresh)
    if ($useCache) {
        $cache = $db->query("SELECT * FROM plg_msg_cache WHERE user_id = ? AND cached_at > DATE_SUB(NOW(), INTERVAL ? SECOND)", [$userId, $cacheMaxAge]);
        if ($cache->count() > 0) {
            return $cache->first();
        }
    }

    // Cache miss or disabled - fetch fresh counts
    $msgCount = $db->query("SELECT
        COALESCE(SUM(CASE WHEN m.msg_type = 1 THEN IF(n.msg_read = 0, 1, 0) ELSE 0 END), 0) AS alert_count,
        COALESCE(SUM(CASE WHEN m.msg_type = 2 THEN IF(n.msg_read = 0, 1, 0) ELSE 0 END), 0) AS notification_count,
        COALESCE(SUM(CASE WHEN m.msg_type = 3 THEN IF(n.msg_read = 0, 1, 0) ELSE 0 END), 0) AS message_count,
        COALESCE(COUNT(n.id), 0) AS total_count
        FROM plg_msg n
        LEFT OUTER JOIN plg_msg_messages m ON n.msg_id = m.id
        WHERE n.user_to = ? AND deleted = 0", [$userId])->first();

    // Update cache
    if ($useCache) {
        updatePlgMessageCache($userId, $msgCount);
    }

    return $msgCount;
}

function updatePlgMessageCache($userId, $counts = null)
{
    global $db;

    // If no counts provided, fetch them
    if ($counts === null) {
        $counts = $db->query("SELECT
            COALESCE(SUM(CASE WHEN m.msg_type = 1 THEN IF(n.msg_read = 0, 1, 0) ELSE 0 END), 0) AS alert_count,
            COALESCE(SUM(CASE WHEN m.msg_type = 2 THEN IF(n.msg_read = 0, 1, 0) ELSE 0 END), 0) AS notification_count,
            COALESCE(SUM(CASE WHEN m.msg_type = 3 THEN IF(n.msg_read = 0, 1, 0) ELSE 0 END), 0) AS message_count,
            COALESCE(COUNT(n.id), 0) AS total_count
            FROM plg_msg n
            LEFT OUTER JOIN plg_msg_messages m ON n.msg_id = m.id
            WHERE n.user_to = ? AND deleted = 0", [$userId])->first();
    }

    // Insert or update cache
    $existing = $db->query("SELECT user_id FROM plg_msg_cache WHERE user_id = ?", [$userId])->count();
    if ($existing > 0) {
        $db->query("UPDATE plg_msg_cache SET alert_count = ?, notification_count = ?, message_count = ?, total_count = ?, cached_at = NOW() WHERE user_id = ?",
            [$counts->alert_count, $counts->notification_count, $counts->message_count, $counts->total_count, $userId]);
    } else {
        $db->query("INSERT INTO plg_msg_cache (user_id, alert_count, notification_count, message_count, total_count, cached_at) VALUES (?, ?, ?, ?, ?, NOW())",
            [$userId, $counts->alert_count, $counts->notification_count, $counts->message_count, $counts->total_count]);
    }
}

function invalidatePlgMessageCache($userId)
{
    global $db;
    $db->query("DELETE FROM plg_msg_cache WHERE user_id = ?", [$userId]);
}

function fetchPLGMessages($limit, $page = 1, $search = '', $dateFrom = '', $dateTo = '', $msgType = null)
{
    global $db, $user;

    $offset = ($page - 1) * $limit;
    $params = [$user->data()->id];
    $whereConditions = ["n.user_to = ?", "n.deleted = 0"];

    // Search filter (title, message content, or sender name)
    if (!empty($search)) {
        $searchTerm = "%" . $search . "%";
        $whereConditions[] = "(m.title LIKE ? OR m.msg LIKE ? OR CONCAT(u.fname, ' ', u.lname) LIKE ?)";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    // Date range filter
    if (!empty($dateFrom)) {
        $whereConditions[] = "DATE(m.msg_sent_on) >= ?";
        $params[] = $dateFrom;
    }
    if (!empty($dateTo)) {
        $whereConditions[] = "DATE(m.msg_sent_on) <= ?";
        $params[] = $dateTo;
    }

    // Message type filter
    if ($msgType !== null && $msgType !== '' && $msgType !== 'all') {
        $whereConditions[] = "m.msg_type = ?";
        $params[] = (int)$msgType;
    }

    $whereClause = implode(" AND ", $whereConditions);

    $msgs = $db->query("SELECT
    n.*,
    m.user_from,
    m.title,
    m.msg AS `message`,
    m.msg_class AS `class`,
    m.msg_sent_on AS `date_raw`,
    m.msg_type AS `msg_type`,
    u.fname,
    u.lname
FROM plg_msg n
LEFT OUTER JOIN plg_msg_messages m ON n.msg_id = m.id
LEFT OUTER JOIN users u ON m.user_from = u.id
WHERE {$whereClause}
GROUP BY n.id
ORDER BY n.msg_read ASC, n.id DESC
LIMIT {$limit} OFFSET {$offset}", $params)->results();

    // Format dates with timezone support
    foreach ($msgs as $msg) {
        $msg->date = formatMsgDate($msg->date_raw, true);
        $msg->date_only = formatMsgDateShort($msg->date_raw);
        $msg->time_only = formatMsgTime($msg->date_raw);
    }

    return $msgs;
}

function fetchPLGMessagesCount($search = '', $dateFrom = '', $dateTo = '', $msgType = null)
{
    global $db, $user;

    $params = [$user->data()->id];
    $whereConditions = ["n.user_to = ?", "n.deleted = 0"];

    // Search filter
    if (!empty($search)) {
        $searchTerm = "%" . $search . "%";
        $whereConditions[] = "(m.title LIKE ? OR m.msg LIKE ? OR CONCAT(u.fname, ' ', u.lname) LIKE ?)";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    // Date range filter
    if (!empty($dateFrom)) {
        $whereConditions[] = "DATE(m.msg_sent_on) >= ?";
        $params[] = $dateFrom;
    }
    if (!empty($dateTo)) {
        $whereConditions[] = "DATE(m.msg_sent_on) <= ?";
        $params[] = $dateTo;
    }

    // Message type filter
    if ($msgType !== null && $msgType !== '' && $msgType !== 'all') {
        $whereConditions[] = "m.msg_type = ?";
        $params[] = (int)$msgType;
    }

    $whereClause = implode(" AND ", $whereConditions);

    $count = $db->query("SELECT COUNT(DISTINCT n.id) as total
FROM plg_msg n
LEFT OUTER JOIN plg_msg_messages m ON n.msg_id = m.id
LEFT OUTER JOIN users u ON m.user_from = u.id
WHERE {$whereClause}", $params)->first();

    return (int)$count->total;
}

function markAllPlgMessagesRead($msgType = null)
{
    global $db, $user;
    $userId = $user->data()->id;

    if ($msgType !== null && $msgType !== '' && $msgType !== 'all') {
        // Mark only messages of a specific type as read
        $db->query("UPDATE plg_msg n
            INNER JOIN plg_msg_messages m ON n.msg_id = m.id
            SET n.msg_read = 1, n.msg_read_on = NOW()
            WHERE n.user_to = ? AND n.msg_read = 0 AND n.deleted = 0 AND m.msg_type = ?",
            [$userId, (int)$msgType]);
    } else {
        // Mark all messages as read
        $db->query("UPDATE plg_msg SET msg_read = 1, msg_read_on = NOW() WHERE user_to = ? AND msg_read = 0 AND deleted = 0", [$userId]);
    }

    // Invalidate cache after marking as read
    invalidatePlgMessageCache($userId);

    return true;
}

function fetchPLGMessage($id)
{

    global $db, $user;
    $userId = $user->data()->id;
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
WHERE n.user_to = ? AND n.id = ?", [$userId, $id]);


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
        // Invalidate cache when message is marked as read
        invalidatePlgMessageCache($userId);
    }
    if($msg->from_name == null){
        $msg->from_name = "System Message";
    }
    $msg->date = formatMsgDate($msg->date);
    return $msg;
}

function formatMsgDate($datetime, $hours = true)
{
    global $user, $timezone_string;

    if ($datetime == null || $datetime == "") {
        return "";
    }

    // Create DateTime from UTC
    $dt = new DateTime($datetime, new DateTimeZone('UTC'));

    // Determine target timezone: user preference > site default
    $tz = null;
    if (isset($user) && $user->isLoggedIn() && isset($user->data()->tz) && $user->data()->tz != '') {
        $tz = $user->data()->tz;
    } elseif (isset($timezone_string) && $timezone_string != '') {
        $tz = $timezone_string;
    }

    if ($tz !== null) {
        $dt->setTimezone(new DateTimeZone($tz));
    }

    if (!$hours) {
        return $dt->format("D M d, Y");
    }
    return $dt->format("D M d, Y - g:i a");
}

function formatMsgDateShort($datetime)
{
    global $user, $timezone_string;

    if ($datetime == null || $datetime == "") {
        return "";
    }

    $dt = new DateTime($datetime, new DateTimeZone('UTC'));

    $tz = null;
    if (isset($user) && $user->isLoggedIn() && isset($user->data()->tz) && $user->data()->tz != '') {
        $tz = $user->data()->tz;
    } elseif (isset($timezone_string) && $timezone_string != '') {
        $tz = $timezone_string;
    }

    if ($tz !== null) {
        $dt->setTimezone(new DateTimeZone($tz));
    }

    return $dt->format("m/d/Y");
}

function formatMsgTime($datetime)
{
    global $user, $timezone_string;

    if ($datetime == null || $datetime == "") {
        return "";
    }

    $dt = new DateTime($datetime, new DateTimeZone('UTC'));

    $tz = null;
    if (isset($user) && $user->isLoggedIn() && isset($user->data()->tz) && $user->data()->tz != '') {
        $tz = $user->data()->tz;
    } elseif (isset($timezone_string) && $timezone_string != '') {
        $tz = $timezone_string;
    }

    if ($tz !== null) {
        $dt->setTimezone(new DateTimeZone($tz));
    }

    return $dt->format("g:i a");
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
  