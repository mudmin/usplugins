<?php
if (!isset($abs_us_root) || !function_exists('plgTurnstileEnabled')) { return; }
if (plgTurnstileEnabled('reset_password')) {
    // forgotPasswordResponse executes before password validation/update, on GET and POST.
    if (Server::get('REQUEST_METHOD') === 'POST' && !checkTurnstile(null, 'reset_password')) {
        plgTurnstileReject();
    }
    turnstileHere(['action' => 'reset_password', 'target' => '[name="resetPassword"]']);
}
