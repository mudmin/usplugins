<?php
if (!isset($abs_us_root) || !function_exists('plgTurnstileEnabled')) { return; }
global $currentPage;
$plgTurnstileForm = $currentPage === 'login.php' ? 'login' : ($currentPage === 'join.php' ? 'join' : '');
if (plgTurnstileEnabled($plgTurnstileForm)) {
    turnstileHere(['action' => $plgTurnstileForm]);
}
