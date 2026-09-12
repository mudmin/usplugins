<?php
if (!isset($user) || !$user->isLoggedIn() || !in_array($user->data()->id, $master_account)) { return; }
require $abs_us_root . $us_url_root . 'usersc/plugins/turnstile/plugin_info.php';
if (!pluginActive($plugin_name)) { return; }
require_once $abs_us_root . $us_url_root . 'usersc/plugins/turnstile/functions.php';
require_once $abs_us_root . $us_url_root . 'usersc/plugins/turnstile/assets/admin.php';
$plgTurnstileSettings = plgTurnstileSettings();
if (!$plgTurnstileSettings) { echo '<p>Turnstile settings could not be loaded. Please reactivate the plugin.</p>'; return; }
$plgTurnstileErrors = [];
$plgTurnstileNotice = '';
$plgTurnstileForms = [
    'login' => ['Login', 'login.php', 'form + post'],
    'join' => ['Join / registration', 'join.php', 'form + joinAttempt event'],
    'forgot_password' => ['Forgot password', 'forgot_password.php', 'bottom + post'],
    'reset_password' => ['Password reset', 'forgot_password_reset.php', 'forgotPasswordResponse event'],
    'passwordless' => ['Protect passwordless email requests', 'passwordless.php', 'form + post (email-request step only)'],
];
if (Server::get('REQUEST_METHOD') === 'POST') {
    if (!Token::check(Input::get('csrf'))) {
        $plgTurnstileErrors[] = 'Your security token expired. Reload this page before trying again.';
    } elseif (Input::get('turnstile_action') === 'test') {
        if (checkTurnstile(null, 'configuration_test')) {
            $plgTurnstileNotice = 'Success: Cloudflare verified this widget using your saved keys and hostname settings.';
        } else {
            $plgTurnstileErrors[] = 'Verification failed. Check your saved keys, allowed hostnames, and outbound HTTPS access, then try a fresh challenge.';
        }
    } elseif (Input::get('turnstile_action') === 'save') {
        // Existing installations can apply the new migration on a CSRF-checked save.
        if (!property_exists($plgTurnstileSettings, 'protect_passwordless')) {
            require $abs_us_root . $us_url_root . 'usersc/plugins/turnstile/migrate.php';
            $plgTurnstileSettings = plgTurnstileSettings(true);
        }
        $plgTurnstileFields = [];
        foreach (['site_key', 'secret_key', 'theme', 'size', 'hostnames'] as $plgField) {
            $plgValue = Input::get($plgField);
            if (!is_string($plgValue)) {
                $plgTurnstileErrors[] = 'Invalid settings value.';
                $plgValue = '';
            }
            $plgTurnstileFields[$plgField] = $plgValue;
        }
        if ($plgTurnstileFields['secret_key'] === '') {
            $plgTurnstileFields['secret_key'] = $plgTurnstileSettings->secret_key;
        }
        if (Input::get('clear_keys') === '1') {
            $plgTurnstileFields['site_key'] = '';
            $plgTurnstileFields['secret_key'] = '';
        }
        foreach (['site_key', 'secret_key'] as $plgField) {
            if ($plgTurnstileFields[$plgField] !== '' && !preg_match('/^[a-zA-Z0-9_-]{1,255}$/D', $plgTurnstileFields[$plgField])) {
                $plgTurnstileErrors[] = 'Keys may contain only letters, numbers, underscores, and hyphens (up to 255 characters).';
            }
        }
        if (!in_array($plgTurnstileFields['theme'], ['auto', 'light', 'dark'], true)
            || !in_array($plgTurnstileFields['size'], ['normal', 'compact', 'flexible'], true)) {
            $plgTurnstileErrors[] = 'Choose a supported theme and widget size.';
        }
        $plgTurnstileHosts = preg_split('/[\s,]+/', strtolower($plgTurnstileFields['hostnames']), -1, PREG_SPLIT_NO_EMPTY);
        if (strlen($plgTurnstileFields['hostnames']) > 4000) {
            $plgTurnstileErrors[] = 'The hostname list must be 4,000 characters or fewer.';
        }
        foreach ($plgTurnstileHosts as &$plgHost) {
            $plgHost = rtrim($plgHost, '.');
            if (strlen($plgHost) > 253 || !filter_var($plgHost, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
                $plgTurnstileErrors[] = 'Enter hostnames without schemes, paths, ports, or wildcards.';
                break;
            }
        }
        unset($plgHost);
        $plgTurnstileFields['hostnames'] = implode("\n", array_unique($plgTurnstileHosts));
        foreach ($plgTurnstileForms as $plgForm => $plgDetails) {
            $plgTurnstileFields['protect_' . $plgForm] = Input::get('protect_' . $plgForm) === '1' ? 1 : 0;
        }
        $plgTurnstileProtect = array_sum(array_intersect_key($plgTurnstileFields, array_flip([
            'protect_login', 'protect_join', 'protect_forgot_password', 'protect_reset_password', 'protect_passwordless',
        ]))) > 0;
        if ($plgTurnstileProtect && ($plgTurnstileFields['site_key'] === '' || $plgTurnstileFields['secret_key'] === '' || !function_exists('curl_init'))) {
            $plgTurnstileErrors[] = 'Save both keys and enable PHP cURL before protecting account forms.';
        }
        if (!$plgTurnstileErrors) {
            try {
                $db->beginTransaction();
                $db->update('plg_turnstile_settings', 1, $plgTurnstileFields);
                if ($db->error()) { throw new RuntimeException('Settings save failed.'); }
                plgTurnstileSyncHooks((object) $plgTurnstileFields);
                $db->commit();
                plgTurnstileSettings(true);
                logger($user->data()->id, 'USPlugins', 'Turnstile settings and hook selections updated');
                usSuccess('Turnstile settings saved.');
                Redirect::to($us_url_root . 'users/admin.php?view=plugins_config&plugin=turnstile');
                return;
            } catch (Throwable $e) {
                if ($db->inTransaction()) { $db->rollBack(); }
                $plgTurnstileErrors[] = 'Settings could not be saved. No changes were applied. Please try again.';
            }
        }
    } else {
        $plgTurnstileErrors[] = 'Unknown Turnstile action.';
    }
}
$plgTurnstileHooks = $db->query('SELECT page, position, hook FROM us_plugin_hooks WHERE folder = ? AND disabled = 0 ORDER BY page, position', ['turnstile'])->results();
?>
<div class="container-fluid py-3">
    <a href="<?= safeReturn($us_url_root . 'users/admin.php?view=plugins') ?>">Return to the Plugin Manager</a>
    <h1 class="mt-3">Cloudflare Turnstile</h1>
    <p>Add a security check to your forms. Create a widget in the <a href="https://dash.cloudflare.com/?to=/:account/turnstile">Cloudflare dashboard</a>, add your site's hostnames there, then save the site key and secret key below.</p>
    <?php foreach (array_unique($plgTurnstileErrors) as $plgError) { ?>
        <div class="alert alert-danger" role="alert"><?= safeReturn($plgError) ?></div>
    <?php } ?>
    <?php if ($plgTurnstileNotice !== '') { ?>
        <div class="alert alert-success" role="status"><?= safeReturn($plgTurnstileNotice) ?></div>
    <?php } ?>
    <?php if (!function_exists('curl_init')) { ?>
        <div class="alert alert-warning">PHP cURL is required for server verification. Enable it before using Turnstile.</div>
    <?php } ?>
    <form method="post" action="">
        <?php tokenHere(); ?>
        <input type="hidden" name="turnstile_action" value="save">
        <div class="card mb-3"><div class="card-body">
            <h2 class="h4">Keys and appearance</h2>
            <div class="mb-3">
                <label for="ts-site-key" class="form-label">Site key</label>
                <input id="ts-site-key" class="form-control" type="text" name="site_key" maxlength="255" value="<?= safeReturn($plgTurnstileSettings->site_key) ?>" autocomplete="off" spellcheck="false">
            </div>
            <div class="mb-3">
                <label for="ts-secret-key" class="form-label">Secret key</label>
                <input id="ts-secret-key" class="form-control" type="password" name="secret_key" maxlength="255" value="" autocomplete="new-password">
                <p class="form-text"><?= $plgTurnstileSettings->secret_key !== '' ? 'A secret key is saved. Leave this blank to keep it; enter a new key to replace it.' : 'No secret key has been saved yet.' ?></p>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3"><label for="ts-theme" class="form-label">Theme</label>
                    <select class="form-control" id="ts-theme" name="theme">
                        <?php foreach (['auto' => 'Automatic', 'light' => 'Light', 'dark' => 'Dark'] as $plgValue => $plgLabel) { ?>
                            <option value="<?= safeReturn($plgValue) ?>" <?= $plgTurnstileSettings->theme === $plgValue ? 'selected' : '' ?>><?= safeReturn($plgLabel) ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3"><label for="ts-size" class="form-label">Size</label>
                    <select class="form-control" id="ts-size" name="size">
                        <?php foreach (['normal' => 'Normal', 'compact' => 'Compact', 'flexible' => 'Flexible width'] as $plgValue => $plgLabel) { ?>
                            <option value="<?= safeReturn($plgValue) ?>" <?= $plgTurnstileSettings->size === $plgValue ? 'selected' : '' ?>><?= safeReturn($plgLabel) ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <div class="mb-3"><label for="ts-hostnames" class="form-label">Verify response hostnames (optional)</label>
                <textarea class="form-control" id="ts-hostnames" name="hostnames" rows="3" maxlength="4000" placeholder="example.com&#10;www.example.com"><?= safeReturn($plgTurnstileSettings->hostnames) ?></textarea>
                <p class="form-text">One exact hostname per line, or separated by commas. When set, every verification must match this list. Leave blank to rely on the widget's hostname restrictions in Cloudflare.</p>
            </div>
            <label><input type="checkbox" name="clear_keys" value="1"> Clear both saved keys (uncheck all account forms first)</label>
        </div></div>
        <div class="card mb-3"><div class="card-body">
            <h2 class="h4">Protect UserSpice forms</h2>
            <p>Save and test your keys before enabling these options. Each selection covers both <code>users/</code> and <code>usersc/</code> copies that retain the required hooks. Custom forms work independently of these switches.</p>
            <?php foreach ($plgTurnstileForms as $plgForm => $plgDetails) { ?>
                <div class="mb-3">
                    <label><input type="checkbox" name="<?= safeReturn('protect_' . $plgForm) ?>" value="1" <?= (int) ($plgTurnstileSettings->{'protect_' . $plgForm} ?? 0) === 1 ? 'checked' : '' ?>> <strong><?= safeReturn($plgDetails[0]) ?></strong></label>
                    <div class="small text-muted"><code><?= safeReturn($plgDetails[1]) ?></code> — <?= safeReturn($plgDetails[2]) ?></div>
                </div>
            <?php } ?>
            <p class="small">Password recovery forms use JavaScript to insert the widget before their submit control. Customized copies must retain <code>name="forgotten_password"</code> / <code>name="resetPassword"</code> and the listed hooks. Passwordless protection covers the email-request step, including requests for a new email; code entry and magic-link redemption keep their existing flow. Login protects password sign-in; TOTP follow-up, passkeys, and social sign-in retain their own authentication flows.</p>
            <p class="small">An enabled check rejects missing or invalid tokens and connection failures. Existing reCAPTCHA plugins remain independent; disable their overlapping form protection if you want only Turnstile.</p>
            <button type="submit" class="btn btn-primary">Save settings and form selections</button>
        </div></div>
    </form>
    <div class="card mb-3"><div class="card-body">
        <h2 class="h4">Test saved keys</h2>
        <?php if (plgTurnstileConfigured()) { ?>
            <form method="post" action="">
                <?php tokenHere(); ?>
                <input type="hidden" name="turnstile_action" value="test">
                <?php turnstileHere(['action' => 'configuration_test']); ?>
                <button class="btn btn-outline-primary" type="submit">Verify test challenge</button>
            </form>
        <?php } else { ?><p>Save both keys to display a test challenge here.</p><?php } ?>
    </div></div>
    <div class="card mb-3"><div class="card-body">
        <h2 class="h4">Add Turnstile to any form</h2>
        <p>Place this inside your form, before its submit button:</p>
        <pre><code>&lt;?php
if (function_exists('turnstileHere')) {
    turnstileHere();
}
?&gt;</code></pre>
        <p>Guard helper calls so the page still loads if the plugin is deactivated. You can also wrap the widget in <code>if (pluginActive('turnstile', true)) { turnstileHere(); }</code>. The <code>true</code> makes it a status check without a redirect.</p>
        <p>In the receiving PHP handler, validate CSRF, then verify Turnstile before sending email, saving data, or performing the protected action:</p>
        <pre><code>if (!Token::check(Input::get('csrf'))) {
    http_response_code(403);
    exit('Invalid security token.');
}
if (!function_exists('checkTurnstile') || !checkTurnstile()) {
    http_response_code(422);
    exit('Please complete the security check and try again.');
}
// Process the verified submission.</code></pre>
        <p>The handler above rejects submissions when the plugin is unavailable. For separate forms, use a matching action inside the same guards: <code>turnstileHere(['action' =&gt; 'contact'])</code> and <code>checkTurnstile(null, 'contact')</code>. Call verification once per submission. The README includes AJAX and multiple-form examples.</p>
        <p>The plugin uses your UserSpice CSP nonce. If you manage a custom Content Security Policy, allow Cloudflare's scripts and frames as described in <a href="https://developers.cloudflare.com/turnstile/reference/content-security-policy/">Cloudflare's CSP guide</a>.</p>
    </div></div>
    <details class="mb-3"><summary>Registered hooks (<?= count($plgTurnstileHooks) ?> active)</summary>
        <div class="table-responsive"><table class="table"><thead><tr><th>Page / event</th><th>Position</th><th>File</th></tr></thead><tbody>
            <?php foreach ($plgTurnstileHooks as $plgHook) { ?>
                <tr><td><?= safeReturn($plgHook->page) ?></td><td><?= safeReturn($plgHook->position) ?></td><td><?= safeReturn($plgHook->hook) ?></td></tr>
            <?php } ?>
        </tbody></table></div>
        <p><a href="https://userspice.com/plugin-hooks/">UserSpice hook documentation</a>. Saving the selections restores this plugin's selected hooks. Deactivation disables them and retains settings; deleting the plugin removes its settings and hooks.</p>
    </details>
</div>
