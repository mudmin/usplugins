<?php
$email = Input::get('email');
pushoverSecurityAlert('join', [
  'user' => $email,
  'extra' => 'New user registration',
]);
?>
