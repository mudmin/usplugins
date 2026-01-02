<?php
// $ruser is available from forgot_password_reset.php context
if (isset($ruser) && $ruser->data() && pushoverIsAdmin($ruser->data()->id)) {
  pushoverSecurityAlert('passwordResetSuccessAdmin', [
    'user' => $ruser->data()->username,
    'extra' => '*** ADMIN PASSWORD RESET COMPLETED ***',
  ]);
}
?>
