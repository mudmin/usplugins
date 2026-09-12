<?php
// Loaded by UserSpice when the plugin is active. No queries until a helper is used.
if (!function_exists('plgTurnstileSettings')) {
    function plgTurnstileSettings($refresh = false)
    {
        global $db;
        static $loaded = false, $plgSettings = null;
        if (!$loaded || $refresh) {
            $loaded = true;
            $plgSettings = null;
            try {
                $plgQuery = $db->query('SELECT * FROM plg_turnstile_settings WHERE id = 1');
                if (!$db->error() && $plgQuery->count()) {
                    $plgSettings = $plgQuery->first();
                }
            } catch (Throwable $e) {
                // A missing settings table must never make verification succeed.
            }
        }
        return $plgSettings;
    }
}

if (!function_exists('plgTurnstileConfigured')) {
    function plgTurnstileConfigured()
    {
        $plgSettings = plgTurnstileSettings();
        return $plgSettings && $plgSettings->site_key !== '' && $plgSettings->secret_key !== '';
    }
}

if (!function_exists('plgTurnstileEnabled')) {
    function plgTurnstileEnabled($form)
    {
        $plgSettings = plgTurnstileSettings();
        return $plgSettings && in_array($form, ['login', 'join', 'forgot_password', 'reset_password', 'passwordless'], true)
            && (int) ($plgSettings->{'protect_' . $form} ?? 0) === 1;
    }
}

if (!function_exists('turnstileError')) {
    /** Safe public message; does not disclose keys, tokens, or Cloudflare response data. */
    function turnstileError()
    {
        return 'Please complete the security check and try again. If it does not load, refresh the page.';
    }
}

if (!function_exists('plgTurnstileSiteverify')) {
    /** Fixed HTTPS endpoint, verified TLS, bounded timeouts, no redirects or token logging. */
    function plgTurnstileSiteverify($secret, $token)
    {
        if (!function_exists('curl_init')) {
            return null;
        }
        $plgCurl = curl_init('https://challenges.cloudflare.com/turnstile/v0/siteverify');
        if ($plgCurl === false) {
            return null;
        }
        curl_setopt_array($plgCurl, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query(['secret' => $secret, 'response' => $token]),
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded', 'Accept: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);
        $plgBody = curl_exec($plgCurl);
        $plgStatus = curl_getinfo($plgCurl, CURLINFO_HTTP_CODE);
        // PHP releases the handle when it leaves scope (curl_close is deprecated in PHP 8.5).
        if (!is_string($plgBody) || $plgStatus !== 200) {
            return null;
        }
        $plgResponse = json_decode($plgBody, true);
        return is_array($plgResponse) ? $plgResponse : null;
    }
}

if (!function_exists('checkTurnstile')) {
    /**
     * Verify once, before processing a form. Null token reads the POST field only.
     * Set the same action in turnstileHere() and here to bind tokens to a form.
     * An explicit hostname adds a restriction to the configured hostname allowlist.
     */
    function checkTurnstile($token = null, $expectedAction = null, $expectedHostname = null)
    {
        if ($token === null) {
            // Test presence in POST; Input::get alone also falls back to query-string tokens.
            $token = array_key_exists('cf-turnstile-response', $_POST) ? Input::get('cf-turnstile-response') : '';
        }
        if (!is_string($token) || trim($token) === '' || strlen($token) > 2048 || !plgTurnstileConfigured()) {
            return false;
        }
        if ($expectedAction !== null && (!is_string($expectedAction) || !preg_match('/^[a-zA-Z0-9_-]{1,32}$/D', $expectedAction))) {
            return false;
        }
        if ($expectedHostname !== null && (!is_string($expectedHostname) || $expectedHostname === '')) {
            return false;
        }
        $plgSettings = plgTurnstileSettings();
        try {
            $plgResponse = plgTurnstileSiteverify($plgSettings->secret_key, $token);
        } catch (Throwable $e) {
            return false;
        }
        if (!is_array($plgResponse) || ($plgResponse['success'] ?? null) !== true) {
            return false;
        }
        if ($expectedAction !== null && ($plgResponse['action'] ?? null) !== $expectedAction) {
            return false;
        }
        $plgHostname = $plgResponse['hostname'] ?? '';
        if (!is_string($plgHostname)) {
            return false;
        }
        $plgHostname = strtolower(rtrim($plgHostname, '.'));
        if ($expectedHostname !== null && $plgHostname !== strtolower(rtrim($expectedHostname, '.'))) {
            return false;
        }
        $plgHosts = preg_split('/[\s,]+/', strtolower(trim($plgSettings->hostnames)), -1, PREG_SPLIT_NO_EMPTY);
        if ($plgHosts && !in_array($plgHostname, $plgHosts, true)) {
            return false;
        }
        return true;
    }
}

if (!function_exists('turnstileHere')) {
    /** Echo a widget inside a form. Supports action, theme, size, language and response_field. */
    function turnstileHere($options = [])
    {
        global $us_url_root;
        static $plgScriptLoaded = false;
        if (!is_array($options)) {
            throw new InvalidArgumentException('Turnstile options must be an array.');
        }
        if (!plgTurnstileConfigured()) {
            echo '<p class="text-danger" role="alert">Security verification is unavailable. Please contact the site administrator.</p>';
            return;
        }
        $plgSettings = plgTurnstileSettings();
        $plgOptions = array_merge([
            'action' => '', 'theme' => $plgSettings->theme, 'size' => $plgSettings->size,
            'language' => 'auto', 'response_field' => 'cf-turnstile-response', 'target' => '',
        ], $options);
        if (!in_array($plgOptions['theme'], ['auto', 'light', 'dark'], true)
            || !in_array($plgOptions['size'], ['normal', 'compact', 'flexible'], true)
            || !is_string($plgOptions['action']) || !preg_match('/^[a-zA-Z0-9_-]{0,32}$/D', $plgOptions['action'])
            || !is_string($plgOptions['language']) || !preg_match('/^[a-zA-Z-]{2,12}$/D', $plgOptions['language'])
            || !is_string($plgOptions['response_field']) || !preg_match('/^[a-zA-Z0-9_-]{1,64}$/D', $plgOptions['response_field'])
            || !is_string($plgOptions['target'])) {
            throw new InvalidArgumentException('Invalid Turnstile widget options.');
        }
        echo '<div class="us-turnstile mb-3" data-sitekey="' . safeReturn($plgSettings->site_key) . '"';
        foreach ($plgOptions as $plgKey => $plgValue) {
            if (in_array($plgKey, ['action', 'theme', 'size', 'language', 'response_field', 'target'], true)) {
                echo ' data-' . str_replace('_', '-', $plgKey) . '="' . safeReturn($plgValue) . '"';
            }
        }
        echo ($plgOptions['target'] !== '' ? ' hidden' : '') . '><div class="us-turnstile-widget"></div>';
        echo '<p class="us-turnstile-status small" role="status">Loading security check…</p></div>';
        echo '<noscript><p class="text-danger">Please enable JavaScript to complete the security check.</p></noscript>';
        if (!$plgScriptLoaded) {
            $plgScriptLoaded = true;
            echo '<script defer nonce="' . safeReturn($GLOBALS['userspice_nonce'] ?? '') . '" src="'
                . safeReturn($us_url_root . 'usersc/plugins/turnstile/assets/turnstile.js') . '"></script>';
        }
    }
}

if (!function_exists('plgTurnstileReject')) {
    /** Reset has no pre-validation object hook; stop before the password can be changed. */
    function plgTurnstileReject()
    {
        global $us_url_root;
        $plgPath = Server::get('PHP_SELF');
        $plgFolder = $plgPath === $us_url_root . 'usersc/forgot_password_reset.php' ? 'usersc/' : 'users/';
        $plgParams = ['reset' => 1];
        foreach (['email', 'vericode', 'user_id'] as $plgField) {
            $plgValue = Input::get($plgField);
            if (is_string($plgValue) && $plgValue !== '' && strlen($plgValue) <= 512) {
                $plgParams[$plgField] = html_entity_decode($plgValue, ENT_QUOTES, 'UTF-8');
            }
        }
        http_response_code(403);
        $plgRetry = $us_url_root . $plgFolder . 'forgot_password_reset.php?' . http_build_query($plgParams);
        echo '<div class="alert alert-danger" role="alert">' . safeReturn(turnstileError()) . '</div>';
        echo '<p><a href="' . safeReturn($plgRetry) . '">Try again</a></p>';
        exit;
    }
}
