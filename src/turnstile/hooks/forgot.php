<?php
if (!isset($abs_us_root) || !function_exists('plgTurnstileEnabled')) { return; }
if (plgTurnstileEnabled('forgot_password')) {
    turnstileHere(['action' => 'forgot_password', 'target' => '[name="forgotten_password"]']);
}
