<?php

global $user;

if($user->data()->id != 1 && !isset($_SESSION['ldaplogin'])) {
    $user->logout();
    Redirect::to($us_url_root.'users/login.php?err=LDAP+account+is+required');
}