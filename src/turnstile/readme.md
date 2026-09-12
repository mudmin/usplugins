# Cloudflare Turnstile for UserSpice

Install/activate **Cloudflare Turnstile** in the UserSpice Plugin Manager, open **Configure Turnstile**, save your keys, and use **Verify test challenge**. Then select the account forms to protect. All account-form switches start off.

Requires PHP cURL and HTTPS access to Cloudflare. Integration tests use UserSpice 6.1.5. For older releases or customized pages, confirm the hooks below exist and run at the same points before enabling protection.

## Any form

The plugin's functions load automatically with `users/init.php` while active. Keep your page's normal authentication, authorization, CSRF, validation, and rate limits.

Inside the form:

```php
<?php tokenHere(); ?>
<?php
if (function_exists('turnstileHere')) {
    turnstileHere();
}
?>
<button type="submit">Send</button>
```

Guard helper calls so deactivating the plugin does not cause an undefined-function error. For rendering, `if (pluginActive('turnstile', true)) { turnstileHere(); }` is also valid; the `true` checks status without redirecting. `function_exists()` checks whether the helper is loaded.

In its receiving handler, **before** email, database writes, or other protected work (this example rejects submissions if the plugin is unavailable):

```php
if (Server::get('REQUEST_METHOD') === 'POST') {
    if (!Token::check(Input::get('csrf'))) {
        http_response_code(403);
        exit('Invalid security token.');
    }
    if (!function_exists('checkTurnstile') || !checkTurnstile()) {
        http_response_code(422);
        exit('Please complete the security check and try again.');
    }
    // Validate the remaining fields and process the submission.
}
```

`turnstileHere(array $options = [])` echoes the widget and loads its JavaScript once per page. `checkTurnstile($token = null, $expectedAction = null, $expectedHostname = null)` returns a boolean. With a null token it reads only the POST `cf-turnstile-response` field. It rejects missing keys, malformed tokens, verification failures, network failures, and mismatched restrictions. `turnstileError()` returns a safe user-facing error message.

Use a distinct action to bind a challenge to its intended form:

```php
if (function_exists('turnstileHere')) {
    turnstileHere(['action' => 'contact']);
}
// In the receiving handler:
$verified = function_exists('checkTurnstile') && checkTurnstile(null, 'contact');
```

Optional widget settings:

```php
if (function_exists('turnstileHere')) {
    turnstileHere([
        'action' => 'contact',        // Letters, digits, underscores, hyphens; max 32.
        'theme' => 'auto',            // auto, light, dark; defaults to saved setting.
        'size' => 'normal',           // normal, compact, flexible; defaults to saved setting.
        'language' => 'auto',         // Cloudflare language code.
        'response_field' => 'contact-token',
    ]);
}
// Custom response fields require an explicit token:
$verified = function_exists('checkTurnstile') && checkTurnstile(Input::get('contact-token'), 'contact');
```

Use one widget per form. Multiple separate forms on a page are supported. After adding form markup dynamically, call `window.userspiceTurnstile.render(formElement)` once the plugin script is loaded. After an AJAX submission, call `window.userspiceTurnstile.reset(formElement)` to refresh its challenge while keeping the other fields. `formElement.reset()` resets both the challenge and other fields.

AJAX handlers should live in a `parsers/` directory. Send `new FormData(formElement)` so both CSRF and Turnstile fields reach PHP; call `checkTurnstile()` in the receiving handler. Do not create a separate public “verify and trust later” endpoint.

Call verification once per submission. Tokens are single-use and expire after five minutes. A rejected form needs a fresh challenge; ordinary PHP rerenders get one automatically. [Cloudflare server verification](https://developers.cloudflare.com/turnstile/get-started/server-side-validation/).

## Built-in integrations

| Selection | Widget hook | Verification hook |
| --- | --- | --- |
| Login | `login.php` → `form` | `login.php` → `post` |
| Join | `join.php` → `form` | `joinAttempt` → `body` |
| Forgot password | `forgot_password.php` → `bottom` | `forgot_password.php` → `post` |
| Password reset | `forgotPasswordResponse` → `body` | Same event, before password processing |
| Passwordless email requests | `passwordless.php` → `form` (email-request view only) | `passwordless.php` → `post` (email requests only) |

UserSpice resolves page hooks by basename, so these registrations cover `users/` and `usersc/` copies. They do not create or overwrite page/view files. A customized copy must retain the listed hooks, the standard validation variables, and the original ordering. Join's `post` hook is too late for verification: the user has already been created. Login's cached `$validated` boolean must also be invalidated on failure. See [UserSpice plugin hooks](https://userspice.com/plugin-hooks/).

The recovery views have no form hook. The plugin moves their widgets into the forms using submit controls named `forgotten_password` and `resetPassword`. The reset event runs before the view is rendered, so insertion waits for DOM readiness. Missing/renamed controls require custom `turnstileHere()` placement; do not add a second widget to a form already covered by an automatic widget hook.

Login, join, and forgot-password errors use the page's normal validation display. The reset event has no validation object at its insertion point, so a failed check stops processing with HTTP 403 and a **Try again** link that preserves the reset context. It does not change the password or consume the reset link.

The **Protect passwordless email requests** switch challenges the initial request and subsequent requests for another login email. It covers `method=enter_email` and the core's omitted/empty-method default. Its post hook reads `Input::get('method')` because the page initializes `$method` after that hook. Failed verification stops processing and redirects back to the email-request form with an error, before any login email or token is generated. The widget is omitted from the code-entry view. Code verification and magic-link redemption retain their existing flow and rate limits.

Login protection applies to password sign-in. TOTP follow-up, passkeys, social login, API registration, and admin-created users retain their existing flows. This plugin does not replace core authentication or rate limits. Existing reCAPTCHA plugins operate independently.

## Settings and lifecycle

Migration `00001` creates the singleton `plg_turnstile_settings` table. Migration `00002` adds the passwordless-email switch, defaulting it off and preserving existing settings. The table stores site/secret keys, appearance, optional exact hostname restrictions, and five account-form switches. The secret is stored server-side in the database; it is never placed in HTML or plugin logs. The password field stays blank on the configuration page; blank saves preserve the current secret.

For an existing installation, saving configuration with a valid CSRF token applies the passwordless migration before saving selections. Plugin activation also runs migrations; no version-number change is required.

Configuration is limited to master accounts and validates CSRF. Saving settings synchronizes only this plugin's hooks. Repeated saves/reactivation reuse existing hook rows. Deactivation preserves settings and disables hooks; activation restores selections. Explicit deletion removes settings and hook rows.

The optional hostname list adds a server-side exact-match restriction, including for manual forms. An explicit `$expectedHostname` adds a further restriction. Hostnames are never inferred from an untrusted HTTP Host header. With the list blank, configure hostname restrictions in Cloudflare's dashboard.

The loader carries the UserSpice nonce to Cloudflare's script. Custom CSP policies must permit the required scripts/frames; see [Cloudflare CSP configuration](https://developers.cloudflare.com/turnstile/reference/content-security-policy/).

## Tests

The CLI integration suite uses the installed UserSpice DB, Input, Token, Validate, and hook helpers against a disposable MariaDB instance. It never loads the site's `init.php` or credentials. Start MariaDB with networking disabled and a temporary socket matching `/tmp/turnstile-test.XXXXXX/mysql.sock`, then run:

```sh
php usersc/plugins/turnstile/tests/run.php /tmp/turnstile-test.XXXXXX/mysql.sock
php usersc/plugins/turnstile/tests/run.php /tmp/turnstile-test.XXXXXX/mysql.sock --reset-reject
php usersc/plugins/turnstile/tests/run.php /tmp/turnstile-test.XXXXXX/mysql.sock --save
php usersc/plugins/turnstile/tests/run.php /tmp/turnstile-test.XXXXXX/mysql.sock --passwordless-reject omitted usersc
php usersc/plugins/turnstile/tests/run.php /tmp/turnstile-test.XXXXXX/mysql.sock --live
```

`--passwordless-reject` accepts a case (`missing`, `omitted`, `empty`, `query-token`, `rejected`, or `wrong-action`) and a route folder (`users` or `usersc`). It verifies rejection stops before email generation and returns to the matching form. `--save` also covers upgrading an existing installation through configuration.

`--live` calls Cloudflare using its published dummy keys to test success, failure, and spent-token responses. It never saves keys to the development site's database. Open `tests/browser.html` in a browser for deterministic widget placement, submission, expiry, error, reset, and multiple-form tests. This fixture stubs Cloudflare while running the real plugin JavaScript.

Cloudflare provides [development test keys](https://developers.cloudflare.com/turnstile/troubleshooting/testing/). Use your actual widget's keys and hostnames when checking the installed plugin's complete account flows.
