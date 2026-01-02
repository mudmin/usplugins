<?php
global $user;
$currentPage = currentPage();
$name = $GLOBALS['config']['session']['session_name'];

if (is_numeric($_SESSION[$name])) {
  $q = $db->query("SELECT username FROM users WHERE id = ?", [$_SESSION[$name]]);
  $c = $q->count();
  if ($c < 1) {
    $un = "Unknown user";
  } else {
    $u = $q->first();
    $un = $u->username;
  }
} else {
  $un = "Unknown user";
}

pushoverSecurityAlert('noAccess', [
  'user' => $un,
  'extra' => "Page: {$currentPage}",
]);
?>
