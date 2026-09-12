<?php
if (!isset($abs_us_root) || !function_exists('plgTurnstileEnabled')) { return; }
global $method;
// The form hook also runs on the code-entry view. Protect only email requests.
if (($method ?? '') === 'enter_email' && plgTurnstileEnabled('passwordless')) {
    turnstileHere(['action' => 'passwordless']);
}
