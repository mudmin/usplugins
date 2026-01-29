<?php
define('USERSPICE_DO_NOT_LOG', true);
require_once "../../../../../users/init.php";
global $user;

if (!Token::check(Input::get('csrf'))) {
    echo json_encode(["success" => false, "msg" => "Invalid token"]);
    die;
}

if (!isset($user) || !$user->isLoggedIn()) {
    echo json_encode(["success" => false, "msg" => "You are not logged in", "messages" => null]);
    die;
}

// Pagination parameters
$page = (int)Input::get('page') ?: 1;
$limit = (int)Input::get('limit') ?: 50;

// Ensure reasonable limits
if ($limit < 1) $limit = 50;
if ($limit > 200) $limit = 200;
if ($page < 1) $page = 1;

// Search and filter parameters
$search = Input::get('search') ?: '';
$dateFrom = Input::get('date_from') ?: '';
$dateTo = Input::get('date_to') ?: '';
$msgType = Input::get('msg_type');

// Fetch messages with pagination and filters
$messages = fetchPLGMessages($limit, $page, $search, $dateFrom, $dateTo, $msgType);
$totalCount = fetchPLGMessagesCount($search, $dateFrom, $dateTo, $msgType);
$totalPages = ceil($totalCount / $limit);

// Get unread counts for badges
$counts = fetchPlgMessageCount();

$response = [
    'success' => true,
    'msg' => 'Messages fetched',
    'messages' => $messages,
    'pagination' => [
        'current_page' => $page,
        'per_page' => $limit,
        'total_count' => $totalCount,
        'total_pages' => $totalPages,
        'has_more' => $page < $totalPages
    ],
    'counts' => [
        'alert_count' => $counts->alert_count,
        'notification_count' => $counts->notification_count,
        'message_count' => $counts->message_count,
        'total_count' => $counts->total_count
    ]
];

echo json_encode($response);
die;