<?php
// Only notify for admin (permission level 2) logins
global $user;
if ($user && $user->isLoggedIn() && pushoverIsAdmin($user->data()->id)) {
  pushoverSecurityAlert('loginSuccessAdmin', [
    'user' => $user->data()->username,
    'extra' => '*** ADMIN LOGIN ***',
  ]);
}
?>
