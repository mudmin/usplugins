<?php 

if (!hasPerm(2)) {
    usError("This page is admin only.");
    Redirect::to("index.php");
}

$messages = $db->query("
    SELECT m.*, 
           n.msg_id, 
           SUM(CASE WHEN n.msg_read = 1 THEN 1 ELSE 0 END) AS msg_read_count
    FROM plg_msg_messages AS m
    LEFT JOIN plg_msg AS n ON m.id = n.msg_id
    WHERE m.user_from = 0
    GROUP BY m.id
    ORDER BY m.id DESC
    LIMIT 500
")->results();
?>


<div class="row">
    <div class="col-12 col-md-4">
        <?php require_once $abs_us_root . $us_url_root . 'usersc/plugins/messaging/assets/views/_message_send.php'; ?>
    </div>
    <div class="col-12 col-md-8">
        <?php require_once $abs_us_root . $us_url_root . 'usersc/plugins/messaging/assets/views/_previous_messages.php'; ?>
    </div>

</div>