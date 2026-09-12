<?php
if (!isset($user) || !$user->isLoggedIn() || !in_array($user->data()->id, $master_account)) { return; }

if (!function_exists('plgTurnstileHookMap')) {
    function plgTurnstileHookMap($plgSettings)
    {
        $plgHooks = [];
        if ((int) $plgSettings->protect_login === 1) {
            $plgHooks['login.php']['form'] = 'hooks/form.php';
            $plgHooks['login.php']['post'] = 'hooks/validate.php';
        }
        if ((int) $plgSettings->protect_join === 1) {
            $plgHooks['join.php']['form'] = 'hooks/form.php';
            // join.php's post hook runs AFTER the user has been created.
            $plgHooks['joinAttempt']['body'] = 'hooks/validate.php';
        }
        if ((int) $plgSettings->protect_forgot_password === 1) {
            $plgHooks['forgot_password.php']['bottom'] = 'hooks/forgot.php';
            $plgHooks['forgot_password.php']['post'] = 'hooks/validate.php';
        }
        if ((int) $plgSettings->protect_reset_password === 1) {
            $plgHooks['forgotPasswordResponse']['body'] = 'hooks/reset.php';
        }
        if ((int) ($plgSettings->protect_passwordless ?? 0) === 1) {
            $plgHooks['passwordless.php']['form'] = 'hooks/passwordless_form.php';
            $plgHooks['passwordless.php']['post'] = 'hooks/passwordless_post.php';
        }
        return $plgHooks;
    }
}

if (!function_exists('plgTurnstileSyncHooks')) {
    function plgTurnstileSyncHooks($plgSettings)
    {
        global $db;
        // Only touch this plugin's hooks. registerHooks reuses existing rows.
        deRegisterHooks('turnstile');
        if ($db->error()) { throw new RuntimeException('Unable to disable Turnstile hooks.'); }
        $plgHooks = plgTurnstileHookMap($plgSettings);
        foreach ($plgHooks as $plgPage => $plgPositions) {
            foreach ($plgPositions as $plgPosition => $plgFile) {
                registerHooks([$plgPage => [$plgPosition => $plgFile]], 'turnstile');
                if ($db->error()) { throw new RuntimeException('Unable to register Turnstile hooks.'); }
            }
        }
    }
}
