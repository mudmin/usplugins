<?php
global $verify;
$user = new User();
$_SESSION[Config::get('session/session_name')] = $verify->data()->id;
Redirect::to($us_url_root);
