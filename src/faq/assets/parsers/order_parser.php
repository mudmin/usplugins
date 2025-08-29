<?php
require_once __DIR__ . '/../../../../../users/init.php';
if(!isset($user) || !$user->isLoggedIn() || !hasPerm([2],$user->data()->id)) {
    die(json_encode(['success' => false, 'message' => 'Access denied.']));
}
if (!Token::check(Input::get('csrf'))) {
    die(json_encode(['success' => false, 'message' => 'CSRF token mismatch.']));
}

$order = Input::get('order');
$type = Input::get('type');
$table = '';

if ($type === 'categories') {
    $table = 'plg_faq_categories';
} elseif ($type === 'faqs') {
    $table = 'plg_faqs';
} else {
    die(json_encode(['success' => false, 'message' => 'Invalid type.']));
}

if (is_array($order)) {
    foreach ($order as $index => $id) {
        $id = (int)$id;
        $db->update($table, $id, ['display_order' => $index]);
    }
}

echo json_encode(['success' => true]);
