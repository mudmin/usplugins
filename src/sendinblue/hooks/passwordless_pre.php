<?php
// A new attempt must not inherit the previous attempt's tracking reference.
if (($_POST['method'] ?? '') === 'enter_email') {
  unset($_SESSION['sendinblue_passwordless']);
}
