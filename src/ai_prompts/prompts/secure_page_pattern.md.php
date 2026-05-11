<?php /* UserSpice AI Prompt — protected from HTTP access. Markdown content below. */ __halt_compiler(); ?>

# UserSpice — The Secure Page Pattern

Load this when you're writing the actual code for a page, form handler, or AJAX endpoint. This is the canonical recipe — every shipped UserSpice page follows it, and `/userspice-audit` will flag pages that don't.

For a fresh page, prefer **`/userspice-page-scaffold`** — it generates this pattern correctly the first time, including the variations for form-with-handler and AJAX parser. The patterns below explain *what the scaffold does* and *how to read or audit existing code*.

---

## The skeleton (a guarded page with no form)

Every UserSpice page starts with these three lines, in this order:

```php
<?php
require_once '../users/init.php';
require_once $abs_us_root . $us_url_root . 'users/includes/template/prep.php';
if (!securePage($_SERVER['PHP_SELF'])) { die(); }
?>
```

What each line does:

1. **`users/init.php`** — bootstraps the framework. After this line, `$db`, `$user`, `$settings`, `$config`, `$abs_us_root`, `$us_url_root`, and all framework classes/helpers exist.
2. **`prep.php`** — wires the active template (header/footer, nav, system messages). Pages that opt out of the template (raw JSON endpoints, downloads) skip this line.
3. **`securePage()`** — checks that the visitor is allowed to view this page given the registered permissions in the `permission_page_matches` table. Returns false → `die()`. If you don't call this, the page is wide open.

The relative path `'../users/init.php'` assumes the page lives one directory below the project root. From the root itself it's `'users/init.php'`. From two levels deep, `'../../users/init.php'`. Don't try to be clever — the framework expects this exact shape.

---

## Page with a form (the full recipe)

```php
<?php
require_once '../users/init.php';
require_once $abs_us_root . $us_url_root . 'users/includes/template/prep.php';
if (!securePage($_SERVER['PHP_SELF'])) { die(); }

// 1. Handle the POST if there is one
if (Input::exists()) {
    // 2. CSRF first, before anything else
    if (!Token::check(Input::get('csrf'))) {
        usError(lang('TOKEN_ERROR'));
        Redirect::to($us_url_root . 'account.php');
    }

    // 3. Validate
    $validate = new Validate();
    $validate->check($_POST, [
        'name'  => ['display' => 'Name',  'required' => true, 'min' => 2, 'max' => 80],
        'email' => ['display' => 'Email', 'required' => true, 'valid_email' => true],
    ]);

    if ($validate->passed()) {
        // 4. Bound parameters — never interpolate user input
        global $db; // (only needed inside functions; here we're at global scope, but harmless)
        $db->insert('contacts', [
            'name'  => Input::get('name'),
            'email' => Input::get('email'),
        ]);
        usSuccess('Saved.');
        Redirect::to($us_url_root . 'contacts.php');
    } else {
        // Errors get rendered below
    }
}
?>

<!-- 5. Render the form. tokenHere() emits the CSRF hidden input. -->
<form method="post">
    <?= tokenHere(); ?>
    <label>Name <input type="text" name="name" value="<?= safeReturn(Input::get('name')) ?>"></label>
    <label>Email <input type="email" name="email" value="<?= safeReturn(Input::get('email')) ?>"></label>
    <button type="submit">Save</button>
</form>

<?php
if (!empty($validate) && !$validate->passed()) {
    echo $validate->display_errors();
}
?>
```

The order matters: **CSRF → validate → write → redirect**. Each step gates the next. If the CSRF check fails, never proceed to validation. If validation fails, never write. After a successful write, redirect (so refresh doesn't double-submit).

---

## AJAX endpoint (the `parsers/` folder)

UserSpice's URL rewriter strips `.php` from page URLs — but **exempts** anything under a `parsers/` folder. AJAX endpoints MUST live there or they'll either 404 or get routed somewhere unexpected.

A parser is a **separate request**. It inherits nothing from the page that called it — not session-derived auth, not page-level permission checks. **Every parser re-does the full auth dance.**

```php
<?php
// admin/parsers/delete_item.php
require_once '../../users/init.php';
header('Content-Type: application/json');

// 1. CSRF first — fail loud, fail early
if (!Token::check(Input::get('csrf_token'))) {
    http_response_code(403);
    die(json_encode(['success' => false, 'message' => 'Invalid CSRF token.']));
}

// 2. Same page-access check as the parent page
if (!securePage($_SERVER['PHP_SELF'])) { exit; }

// 3. Same permission check as the parent page (hasPerm() is a global helper, not a User method)
if (!hasPerm(2)) {
    http_response_code(403);
    die(json_encode(['success' => false, 'message' => 'Forbidden.']));
}

// 4. Cast IDs to int, validate ranges
$id = (int)Input::get('id');
if ($id <= 0) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'Bad id.']));
}

// 5. Bound query
$db->query("DELETE FROM posts WHERE id = ?", [$id]);

echo json_encode(['success' => true]);
```

Calling from the page:

```javascript
$.ajax({
    url: 'parsers/delete_item.php',
    method: 'POST',
    data: { csrf: $('input[name="csrf"]').val(), id: itemId },
    dataType: 'json',
});
```

---

## The non-negotiable helpers

If you write any of these patterns the "raw PHP" way, your code is wrong:

| Don't | Do | Why |
|---|---|---|
| `$_POST['x']` / `$_GET['x']` | `Input::get('x')` | Trims + escapes, recursively sanitizes arrays |
| `$_SERVER['HTTP_HOST']` | `Server::get('HTTP_HOST')` | Type-checked, sanitized, works in CLI |
| `$_SERVER['REMOTE_ADDR']` | `Server::getClientIp($trustedProxies)` | Proxy-aware, only honors forwarded headers from trusted CIDRs |
| `htmlspecialchars($v)` | `safeReturn($v)` | Same escaping + understood by the Psalm taint analyzer |
| In JS: `<?= json_encode($v) ?>` | `<?= safeJsonEncodeForJs($v) ?>` | Escapes `<`, `>`, `&`, `'`, `"` so it can't break out of the script tag |
| `header('Location: ' . $_GET['next'])` | `Redirect::sanitized(Input::get('next'), null, 302, ['same_origin' => true])` | Strips CRLF, blocks `//evil.com` and `javascript:` |
| `header('Location: account.php')` | `Redirect::to('account.php')` | Same protections, exits cleanly, has CSP-nonced fallback if headers already sent |
| `md5(uniqid())` / `mt_rand()` for tokens | `Hash::unique()` | CSPRNG — `uniqid()` is clock-derived and guessable |
| String concatenation in SQL | `$db->query("... WHERE x = ?", [$x])` | Prepared statements with bound values |
| Plaintext API keys in DB | `spiceEncrypt($v)` / `spiceDecrypt($blob, '', '')` | AES-256-GCM, authenticated |
| Ad-hoc `if (!empty($x) && ...)` chains | `new Validate(); $v->check($_POST, $rules)` | Collects all errors, integrates with the flash banner system |

When you don't know the exact signature of one of these helpers, run **`/userspice-helper-lookup <name>`**.

---

## SQL identifiers and `LIMIT`/`OFFSET` — the one place placeholders don't help

PDO placeholders only bind *values*. Table names, column names, sort directions, and `LIMIT`/`OFFSET` cannot be bound. The driver also quotes bound values as strings, so MySQL rejects `LIMIT '10'` as a syntax error.

**Never interpolate user input into those positions.** Two safe patterns:

```php
// Whitelist identifiers
$allowedCols = ['id', 'title', 'created_at'];
$allowedDirs = ['ASC', 'DESC'];

$col = in_array(Input::get('sort'), $allowedCols, true)
    ? Input::get('sort') : 'id';
$dir = in_array(strtoupper(Input::get('dir')), $allowedDirs, true)
    ? strtoupper(Input::get('dir')) : 'DESC';

// Cast LIMIT/OFFSET to int in named variables FIRST, then interpolate
$page    = max(1, (int)Input::get('page', 1));
$perPage = (int)Input::get('per_page', 25);
$perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 25;
$offset  = ($page - 1) * $perPage;

$rows = $db->query(
    "SELECT * FROM posts ORDER BY $col $dir LIMIT $offset, $perPage"
)->results();
```

The cast is what makes `LIMIT $offset, $perPage` safe. A string like `"10; DROP TABLE users"` casts to `10`. Non-numeric input becomes `0`. Always assign to a named variable so the cast is obvious at the call site — don't write `LIMIT (int)Input::get(...)` inline.

---

## `$db->insert()` and `$db->update()` return `bool`, not the ID or row count

This is the single most common API surprise for developers coming from Laravel, Eloquent, ActiveRecord, or any other ORM where the create/save method returns the model with the new primary key on it. **UserSpice's DB class doesn't.** Both `insert()` and `update()` return `true` on success and `false` on error. Nothing else.

To get the new auto-increment ID after an insert, call `$db->lastId()`. To get the affected row count after an update or delete, call `$db->count()`.

```php
// WRONG — $newId is bool true, not the new contact's ID. Foreign key now points at row 1.
$newId = $db->insert('contacts', ['name' => $name, 'email' => $email]);
$db->insert('contact_tags', ['contact_id' => $newId, 'tag' => 'lead']);

// RIGHT — separate calls
$db->insert('contacts', ['name' => $name, 'email' => $email]);
if (!$db->error()) {
    $newId = $db->lastId();
    $db->insert('contact_tags', ['contact_id' => $newId, 'tag' => 'lead']);
}

// Update returns bool too. Affected row count is on $db->count() afterward.
$db->update('users', $userId, ['email' => $newEmail]);
if (!$db->error()) {
    $rowsChanged = $db->count();   // 0 if email was already $newEmail; 1 if it actually changed
}
```

The `if (!$db->error())` guard matters — `$db->lastId()` and `$db->count()` reflect the *most recent query on this connection*. If the insert errored and you then ran another query before checking, `lastId()` would refer to whatever ran most recently. Check the error first, then read.

---

## Flash messages: `usError` / `usSuccess`

The framework has a banner system rendered by the template. Push a message, then redirect, and the user sees it on the next page:

```php
usError('Could not save your changes.');
Redirect::to($us_url_root . 'account.php');

// or
usSuccess('Profile updated.');
Redirect::to($us_url_root . 'account.php');
```

Don't `echo` errors directly into the page — they won't follow the redirect and they don't get the standard styling.

---

## `Input::get()` gotcha (worth re-reading)

`Input::get('foo')` returns `""` (empty string) when the key is missing — **not** `false`, **not** `null`. So:

```php
if (Input::get('foo') === false) { ... }   // never fires
if (Input::get('foo') === null) { ... }    // never fires
if (Input::get('foo') === '')   { ... }    // ✓ correct
if (empty(Input::get('foo')))   { ... }    // ✓ correct
if (!Input::exists('foo'))      { ... }    // ✓ best — tests for presence
```

If you need a non-empty default, pass it as the second arg: `Input::get('page', 1)`.

---

## Validate — the full rule reference

`Validate::check($source, $rules, $sanitize = false)` runs a declarative rule set against any source array. It collects *all* errors so the user sees them at once, and `display_errors()` returns Bootstrap-friendly markup that highlights the offending fields via jQuery.

The rules you'll actually use:

| Rule | What it checks | Example |
|---|---|---|
| `display` | Human label used in error messages (not a check) | `'display' => 'Email Address'` |
| `required` | Value present and non-empty | `'required' => true` |
| `min` / `max` | String length | `'min' => 8, 'max' => 128` |
| `matches` | Equal to another field's value | `'matches' => 'password'` (confirm-password) |
| `unique` | Doesn't exist in `{table}.{column}` (column = field name) | `'unique' => 'users'` |
| `unique_update` | Like `unique`, but ignores the row with this id | `'unique_update' => 'users.42'` |
| `valid_email` | Looks like an email | `'valid_email' => true` |
| `is_not_email` | Specifically *isn't* an email (anti-email field) | `'is_not_email' => true` |
| `is_numeric` / `is_num` | Numeric value | `'is_numeric' => true` |
| `is_integer` / `is_int` | Integer specifically | `'is_int' => true` |
| `<` / `>` / `<=` / `>=` / `!=` / `==` | Numeric comparison to a value | `'>' => 0, '<=' => 100` |
| `in` | Value is in a fixed set | `'in' => ['yes', 'no', 'maybe']` |
| `is_in_array` | Value is in a runtime-supplied array | `'is_in_array' => $allowedRoles` |
| `is_in_database` | Value exists in `{table}.{column}` | `'is_in_database' => 'categories.slug'` |
| `is_datetime` | Parseable as datetime | `'is_datetime' => true` |
| `is_timezone` | A valid PHP timezone string | `'is_timezone' => true` |
| `is_valid_north_american_phone` | Looks like a NA phone number | `'is_valid_north_american_phone' => true` |

Realistic example with several rules per field:

```php
$validate = new Validate();
$validate->check($_POST, [
    'username' => [
        'display'  => 'Username',
        'required' => true,
        'min'      => 3,
        'max'      => 30,
        'unique'   => 'users',
    ],
    'email'    => [
        'display'     => 'Email',
        'required'    => true,
        'valid_email' => true,
        'unique'      => 'users',
    ],
    'password' => [
        'display'  => 'Password',
        'required' => true,
        'min'      => 12,
        'max'      => 200,
    ],
    'confirm'  => [
        'display' => 'Confirm Password',
        'matches' => 'password',
    ],
    'role'     => [
        'display' => 'Role',
        'in'      => ['member', 'editor', 'admin'],
    ],
    'age'      => [
        'display' => 'Age',
        'is_int'  => true,
        '>'       => 12,
        '<'       => 130,
    ],
]);

if ($validate->passed()) {
    // safe to insert
} else {
    echo $validate->display_errors();
}
```

`display_errors()` outputs ready-styled markup — call it once at the bottom of the page (or near the form) and the user sees every problem at once.

---

## Embedding PHP values in JavaScript: `safeJsonEncodeForJs`

`json_encode()` alone is **not** safe for embedding inside a `<script>` block: a `</script>` substring inside a string literal can prematurely close the script tag, and `<!--` can start an HTML comment that swallows your code.

`safeJsonEncodeForJs($value)` wraps `json_encode()` with `JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP`, so `<`, `>`, `&`, `'`, and `"` come out as `\uXXXX` escapes that cannot terminate the surrounding script tag or break out of the string literal.

```php
<?php
$user = ['name' => $row->name, 'tags' => json_decode($row->tags_json, true)];
?>
<script>
  // Use safeJsonEncodeForJs for ANY value going into a <script> tag
  const currentUser = <?= safeJsonEncodeForJs($user) ?>;

  // Works for strings, arrays, objects, nested structures — anything json_encode handles
  const dangerousString = <?= safeJsonEncodeForJs("</script><img src=x>") ?>;
  // → "<\/script><img src=x>"  — harmless inside the script tag
</script>
```

Don't HTML-escape JSON via `safeReturn()` and embed it — that mangles the JSON. Don't roll your own with `addslashes()` either. Just use `safeJsonEncodeForJs()`.

---

## Rate-limit auth-adjacent endpoints

Every login, registration, password-reset, 2FA-verify, and "expensive write" endpoint should sit behind `RateLimit`:

```php
$rl = new RateLimit();
if (!$rl->check('login', ['ip' => Server::getClientIp(), 'email' => $email])) {
    http_response_code(429);
    die('Too many attempts. Try again later.');
}
// ...attempt the auth...
$rl->record('login', ['ip' => Server::getClientIp(), 'email' => $email], $success);
```

Limits are configured per-action in `usersc/includes/rate_limits.php`. The class sanitizes identifiers internally — don't pre-clean them.

The default action names that ship with limits configured: `login`, `register`, `forgot_password`, `verify_resend`, `totp_verify`, `passkey_login`. Add your own by editing `rate_limits.php`. The `record()` call's `$success` flag matters — failed attempts and total attempts have separate caps, so a successful login resets the failed-attempt counter for that identifier.

---

## Server::getClientIp and trusted proxies

`$_SERVER['REMOTE_ADDR']` is the IP of whatever immediately connected to the web server. Behind a load balancer, CDN, or reverse proxy (Cloudflare, Nginx, Traefik, ELB), that's *the proxy*, not the actual user. The real client IP is in a forwarded header like `X-Forwarded-For` — but you can only trust that header if it came from a proxy *you control*. Otherwise any visitor can spoof their IP by sending the header themselves.

`Server::getClientIp($trustedProxyCidrs)` does the right thing:

```php
// No proxies at all — direct-to-PHP server
$ip = Server::getClientIp();   // returns REMOTE_ADDR

// You sit behind Cloudflare. Pass Cloudflare's CIDRs as trusted.
$cloudflare = ['173.245.48.0/20', '103.21.244.0/22', '...etc']; // from cloudflare.com/ips
$ip = Server::getClientIp($cloudflare);
// If REMOTE_ADDR matches one of those CIDRs, the function honors X-Forwarded-For;
// otherwise it falls back to REMOTE_ADDR. Spoofed forwarded headers from random
// internet IPs are ignored.
```

Same `$trustedProxyCidrs` argument works for `Server::getScheme()` (returns `'http'`/`'https'`, honors `X-Forwarded-Proto` from trusted proxies), `Server::getHost()`, and `Server::getOrigin()`. Use these instead of `$_SERVER['HTTPS']` and `$_SERVER['HTTP_HOST']` — they're proxy-aware and CLI-safe (cron jobs work).

If you don't pass any CIDRs, the helpers fall back to the raw `$_SERVER` values without honoring any forwarded headers. That's the safe default.

---

## Audit before declaring done

Run **`/userspice-audit`** after writing or modifying a page. It walks every custom file (outside `users/`) and flags violations with file:line references. It does not modify code — it just tells you what to fix.

---

## Canonical reference

This prompt summarizes `userspice-best-practices/index.php`. Read that guide when you need the longer rationale (proxy CIDR handling, the double-encoding case for `safeReturn(..., true)`, the AES-256-GCM details, etc.).
