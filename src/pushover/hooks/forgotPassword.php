<?php
$email = Input::get('email');

// Check if this is an admin account
$adminCheck = $db->query("SELECT id FROM users WHERE email = ?", [$email])->first();
$isAdmin = $adminCheck && pushoverIsAdmin($adminCheck->id);
$event = $isAdmin ? 'forgotPasswordAdmin' : 'forgotPassword';

pushoverSecurityAlert($event, [
  'user' => $email,
  'extra' => $isAdmin ? '*** ADMIN ACCOUNT ***' : '',
]);
?>
