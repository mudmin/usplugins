<?php /* UserSpice AI Prompt — protected from HTTP access. Markdown content below. */ __halt_compiler(); ?>

# UserSpice — Debugging & Common Traps

Load this when something doesn't work and you don't know why. The amateur traps in UserSpice are mostly framework-specific behaviors that are perfectly logical *once you know about them* and completely baffling *the first time you hit them*. This prompt collects them.

---

## Diagnostic order: what to check first

When a UserSpice page does the wrong thing, walk this list top-to-bottom. The early items rule out 80% of issues.

1. **Is PHP even running?** View source on the page. If you see literal `<?php ... ?>` in the HTML, PHP isn't being executed (file extension, server config, or a `?>` trap — see below).
2. **Are there fatal errors hidden by `display_errors=Off`?** Check your PHP error log. (`/var/log/php-fpm/error.log`, `/var/log/apache2/error.log`, or whatever your stack uses. Your `php.ini` `error_log` directive points at the file.)
3. **Is the plugin active?** Admin → Plugins. If it shows "Install" instead of "Configure" or "Disable", `functions.php` isn't loaded and any plugin helpers are undefined.
4. **Is `securePage()` redirecting you?** That's the single biggest source of "where did the page go" confusion. See "Redirect surprises" below.
5. **Did the CSRF check fail silently?** If a form submit "did nothing," the most likely cause is a CSRF mismatch (field name, expired token).
6. **Is the SQL actually running?** Wrap suspect queries with `if ($db->error()) { error_log($db->errorString()); }` or turn on database logging (see below).

---

## Trap #1: `<?php` or `?>` inside a `//` PHP comment

> **Symptom:** the page renders the file's source code below a certain line, OR you get "Call to undefined function X" because helpers in the file never got defined.

**The trap:** a single-line `//` comment in PHP is terminated by *either* end-of-line *or* a `?>`. So this:

```php
// The wrapper looks like <?php exit; ?> followed by markdown.
function aiPromptList() { ... }
```

…is parsed as:
1. `// The wrapper looks like <?php exit; ` — comment runs until `?>`
2. `?>` — exits PHP mode
3. ` followed by markdown.\n function aiPromptList() { ... }` — all HTML output to the browser

The function never gets defined. The code dumps to the page as text. PHP linter (`php -l`) doesn't always catch it because after `?>` the rest of the file is "HTML mode" and just gets sent to output without parse errors.

**The fix:** never put a literal `<?php` or `?>` inside a PHP comment. Reword:

```php
// Each prompt file starts with a one-line PHP wrapper, followed by markdown.
```

Or, if you must show the literal characters, use a multi-line `/* */` block (also terminated by `?>`, but it's more obvious) — better, just split the string at runtime:

```php
$closeTag = '?' . '>';   // safe — no literal ?> in source
```

**How to find it across a project:**

```bash
grep -rnE '//.*\?>|#.*\?>' path/to/files
```

This is exactly the trap that broke the AI Prompts plugin's first build of `functions.php`. It's not theoretical.

---

## Trap #2: PHP-wrapped data files (`.md.php`) — use `__halt_compiler()`, not `exit;`

If you embed non-PHP content in a `.php` file (markdown, JSON, YAML), wrap with `__halt_compiler();`, not `exit;`.

`exit;` only stops *runtime* execution. PHP keeps *parsing*. If your data section contains `<?php` or `?>` in a code block (e.g. inline PHP examples in markdown), PHP re-enters code mode and chokes on the first thing that isn't valid PHP (backticks, hashes, etc.) — the file fails to parse, and at runtime nothing runs at all.

`__halt_compiler();` halts the lexer permanently. Everything after is unparsed bytes.

```php
<?php /* ... */ __halt_compiler(); ?>
# Title

Markdown here, including <?php $literal_tags = "fine" ?> in code blocks.
```

---

## Trap #3: The `../users/init.php` path math

Every UserSpice page starts with `require_once '../users/init.php';` — but the relative path depends on where the page lives:

| Page location | Correct require |
|---|---|
| `account.php` (project root) | `require_once 'users/init.php';` |
| `admin/dashboard.php` | `require_once '../users/init.php';` |
| `admin/parsers/delete.php` | `require_once '../../users/init.php';` |
| `usersc/foo.php` (replacing a core page) | `require_once '../users/init.php';` |

Get this wrong and you get "Failed opening required '../users/init.php'" — fatal error, blank page if `display_errors` is off.

**The fix:** count the directory levels between your file and the project root, and use that many `../`. Don't try to use `__DIR__` magic to sidestep it — UserSpice's bootstrap expects this exact include shape and downstream code depends on `$abs_us_root` being computed from it.

---

## Trap #4: The CSRF field-name mismatch

`tokenHere()` outputs `<input type="hidden" name="csrf" value="...">`. So the matching handler is `Token::check(Input::get('csrf'))` — **not** `Input::get('csrf_token')`.

If you see "every form submission silently fails / redirects without error / triggers the token_error.php hook," it's almost certainly this.

```php
// Form
<?= tokenHere(); ?>            // emits name="csrf"

// Handler
if (!Token::check(Input::get('csrf'))) {     // ← match the field name
    usError('Session expired.');
    Redirect::to('account.php');
}
```

If you write the hidden input by hand instead of using `tokenHere()`, name it whatever you like, but match the `Input::get()` key on the handler side.

---

## Trap #5: `$db->insert()` / `$db->update()` return `bool`, not the ID or row count

> **Symptom:** foreign keys point at the wrong row. Children get attached to whoever is row 1 in the parent table. "Why is everything attached to my admin user?"

`insert()` returns `true` on success and `false` on error. **It does not return the new auto-increment ID.** If you write:

```php
$id = $db->insert('orders', $fields);                    // $id is bool true, NOT the order's id
$db->insert('order_items', ['order_id' => $id, ...]);    // PHP coerces true → 1; you just attached the items to order #1
```

…all your child rows reference whatever happens to be row 1 in `orders`. Often the test order you made on day one. The bug is silent — no exception, no warning, just bad data.

The fix:

```php
$db->insert('orders', $fields);
if (!$db->error()) {
    $orderId = $db->lastId();
    $db->insert('order_items', ['order_id' => $orderId, ...]);
}
```

`update()` has the same shape (`bool` return). To know how many rows were touched, call `$db->count()` after a successful update. Both `lastId()` and `count()` reflect the *most recent query* on the connection — check `$db->error()` first, then read them, before issuing another query.

---

## Trap #6: `Input::get()` returns `""`, not `false` or `null`

```php
if (Input::get('foo') === false) { ... }   // never fires
if (Input::get('foo') === null)  { ... }   // never fires
if (Input::get('foo') === '')    { ... }   // ✓
if (empty(Input::get('foo')))    { ... }   // ✓
if (!Input::exists('foo'))       { ... }   // ✓ best — tests for presence
```

The most insidious version: `if (Input::get('overwrite') === '1')`. That works, but `if (Input::get('overwrite'))` will be falsy for `'0'` (the string zero is falsy in PHP). Be explicit.

---

## Redirect surprises (`securePage()` is doing something)

When a page "doesn't load" but doesn't show an error, you've almost always hit `securePage()`. Match the symptom to the cause:

| Symptom | What `securePage()` did | Why |
|---|---|---|
| Hard `die()` with "You must go into the Admin Panel and click Manage Pages" | Page not in `pages` table; current user is not admin | First admin visit to a page auto-registers it. Non-admins can't trigger that, so they get a stop-sign. |
| Redirected to `users/admin.php?view=page&new=yes&id=X` | Page not registered, current user IS admin | Pick the permissions for the new page; you'll be sent back to the original URL after save. |
| Redirected to `usersc/scripts/banned.php` | User has permission level 0 (banned) | Even if the user could otherwise see the page. Banned is global. |
| Redirected to `users/login.php` | Page is private (`pages.private = 1`) and user isn't logged in | Logs to `audit` table on the way out. |
| Redirected to homepage (`Config::get('homepage')` or project root) | User logged in but no matching permission in `permission_page_matches` | Logs to `audit` table on the way out. Runs `usersc/scripts/did_not_have_permission.php` first. |
| Page returns blank | Most likely a fatal *before* `securePage()` ran. Check error log. | Less likely: `securePage()` returned false but you didn't `die()` after. Always: `if (!securePage(...)) { die(); }`. |

For the deep mechanics, see `permissions.md.php`. The `audit` table is your friend — `SELECT user, page, ip, time FROM audit ORDER BY id DESC LIMIT 20` shows exactly who got denied what, recently.

---

## Redirect loops

Symptom: browser says "too many redirects" or page keeps reloading.

Common causes:

- **`Config::get('homepage')` itself requires a permission the user doesn't have.** `securePage()` denies → redirects to homepage → homepage's `securePage()` denies → redirects to homepage… Set the homepage to a *public* page (`pages.private = 0`) or one that the lowest-permission user (logged in or not) can access.
- **Custom `usersc/scripts/did_not_have_permission.php` calls `Redirect::to()` to a protected page.** Same loop, just one hop bigger.
- **`Redirect::to()` after a successful POST that ends up triggering the same POST handler.** Always change the URL on redirect (don't redirect to `Server::get('REQUEST_URI')` after a submit).

Find the loop by opening browser dev tools → Network tab → look at the redirect chain. The destination that keeps appearing is the one with a misconfigured permission or the bad redirect target.

---

## "Call to undefined function X"

When PHP fatals on `Call to undefined function`:

1. **The function lives in a file that didn't load.** For UserSpice helpers, that means `users/init.php` wasn't included before the call.
2. **The function lives in a plugin that isn't active.** `functions.php` only loads when the plugin's row in `us_plugins` has `status = 'active'`. Activate it from Admin → Plugins.
3. **The function file silently failed to parse.** See Trap #1 (`?>` in `//` comment). The file "loaded" — `require_once` returned successfully — but PHP exited PHP mode partway through, so the function definitions below never executed.
4. **The function exists but you're calling it from inside another function without the right scope.** Most UserSpice helpers are global, so this is rare — but if it's a method (`Foo::bar()`), check the class name.

Fastest debug: `var_dump(function_exists('the_function_name'));` right before the call. If `false`, you have a load-order problem.

---

## "I see PHP source code in the page"

Either:

- **PHP isn't running on this host.** Test with a one-line `<?php phpinfo(); ?>` file. If you see source, the server isn't routing `.php` to PHP. Fix the web-server config.
- **Trap #1 fired.** A `?>` inside a `//` comment exited PHP mode and dumped the rest of the file as text. See Trap #1.

---

## Database debugging

### When a query "doesn't work":

```php
$result = $db->query("SELECT ...", [$arg]);
if ($db->error()) {
    error_log("DB error: " . $db->errorString());
    error_log(print_r($db->errorInfo(), true));   // raw PDO error array
}
```

### Tracking down rogue writes ("who's INSERTing into the users table?"):

At the top of any page, or in `users/init.php` for a global trace:

```php
$database_logging              = true;
$database_logging_tables_only  = ['users'];   // optional whitelist
```

Every INSERT/UPDATE on the listed tables (or all tables, if no whitelist) gets logged to the `logs` table with a full stack trace in the metadata. Don't leave this on in production — it writes a row per write.

### Check what the framework has been logging:

```sql
-- Recent activity / DB writes
SELECT id, user_id, logtype, info, time FROM logs ORDER BY id DESC LIMIT 50;

-- Recent denied access attempts
SELECT id, user, page, ip, time FROM audit ORDER BY id DESC LIMIT 50;
```

---

## Plugin debugging

### "My plugin's helpers are undefined site-wide."

`functions.php` loads on every page when the plugin is active. If it's not loading:

- Check the plugin is active: `SELECT * FROM us_plugins WHERE plugin = 'your_plugin'` — `status` should be `active`.
- Check `functions.php` itself parses cleanly: `php -l usersc/plugins/your_plugin/functions.php`.
- Run Trap #1's grep against the file.

### "configure.php throws a fatal."

The plugin manager loads `configure.php` via `users/modules/views.php` (the `case 'plugins_config':` branch around line 125). Globals like `$user`, `$master_account`, `$db`, `$abs_us_root`, `$us_url_root` are in scope when it runs — directly accessing `$_SERVER` or assuming you're in CLI mode will surprise you.

### "The launch button on the plugin manager goes nowhere / 404s."

The `<button>` tag in `info.xml` is the label. The actual URL is `users/admin.php?view=plugins_config&plugin=your_plugin`. Make sure your plugin's folder name matches exactly (lowercase, the same as in `plugin_info.php`).

---

## The Security Dashboard is a quick health check

Admin → Settings → Security. It's a one-page audit:

- PHP version (EOL warnings)
- Update track
- Rate-limit health
- Passkey RP ID validity
- TOTP encryption status
- Security headers actually being sent
- SSRF hardening, email config, direct `$_SERVER` usage in `init.php`

If you're not sure why something feels "off," start there before instrumenting code.

---

## Reference

- `users/helpers/permissions.php` — securePage, hasPerm, etc.
- `users/classes/DB.php` — error()/errorString()/errorInfo() and $database_logging
- `audit` and `logs` tables — what the framework has been recording for you
- `userspice-best-practices/index.php` — the canonical security guide
- For the permission redirect logic in depth: see `permissions.md.php`.
