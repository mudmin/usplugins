<?php
if (!isset($abs_us_root) || !function_exists('plgTurnstileEnabled')) { return; }
// includeHook() runs in function scope; explicitly import the page's validation state.
global $currentPage, $validation, $validated, $hookData;
$plgTurnstileForms = ['login.php' => 'login', 'join.php' => 'join', 'forgot_password.php' => 'forgot_password'];
$plgTurnstileForm = $plgTurnstileForms[$currentPage] ?? '';
if (Server::get('REQUEST_METHOD') === 'POST' && plgTurnstileEnabled($plgTurnstileForm)
    && !checkTurnstile(null, $plgTurnstileForm)) {
    if (!isset($validation) || !is_object($validation) || !method_exists($validation, 'addError')) {
        // A customized page with incompatible validation must not continue unprotected.
        http_response_code(403);
        exit(safeReturn(turnstileError()));
    }
    $validation->addError([turnstileError(), 'cf-turnstile-response']);
    if ($plgTurnstileForm === 'login') {
        // login.php captures passed() before invoking its post hook.
        $validated = false;
    } elseif ($plgTurnstileForm === 'forgot_password') {
        $hookData['validation'] = $validation;
    }
}
