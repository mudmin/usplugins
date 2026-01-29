<?php
define('USERSPICE_DO_NOT_LOG', true);
require_once "../../../../../users/init.php";
global $user;

if (!Token::check(Input::get('csrf'))) {
    echo json_encode(["success" => false, "msg" => "Invalid token"]);
    die;
}

if (!isset($user) || !$user->isLoggedIn()) {
    echo json_encode(["success" => false, "msg" => "You are not logged in"]);
    die;
}

$msgType = Input::get('msg_type');

// Mark all messages as read (optionally filtered by type)
$result = markAllPlgMessagesRead($msgType);

if ($result) {
    // Get fresh counts after marking as read
    $counts = fetchPlgMessageCount(false); // Skip cache to get fresh data

    echo json_encode([
        "success" => true,
        "msg" => "All messages marked as read",
        "alert_count" => $counts->alert_count,
        "notification_count" => $counts->notification_count,
        "message_count" => $counts->message_count,
        "total_count" => $counts->total_count
    ]);
} else {
    echo json_encode(["success" => false, "msg" => "Failed to mark messages as read"]);
}
die;
