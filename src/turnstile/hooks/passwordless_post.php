<?php
if (!isset($abs_us_root) || !function_exists('plgTurnstileEnabled')) { return; }
if (Server::get('REQUEST_METHOD') !== 'POST' || !plgTurnstileEnabled('passwordless')) { return; }
// The post hook runs after CSRF, but BEFORE the page initializes $method.
// An omitted/empty method defaults to enter_email in the core page.
$plgTurnstileMethod = Input::get('method');
if (($plgTurnstileMethod === '' || $plgTurnstileMethod === 'enter_email')
    && !checkTurnstile(null, 'passwordless')) {
    usError(turnstileError());
    $plgTurnstileFolder = Server::get('PHP_SELF') === $us_url_root . 'usersc/passwordless.php' ? 'usersc/' : 'users/';
    Redirect::to($us_url_root . $plgTurnstileFolder . 'passwordless.php');
    exit;
}
