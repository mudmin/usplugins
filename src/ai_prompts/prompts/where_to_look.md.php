<?php /* UserSpice AI Prompt — protected from HTTP access. Markdown content below. */ __halt_compiler(); ?>

# UserSpice — Where to Look

The map. When you need to find or change something, this tells you which folder/file to open *first*. Most "where does X live" questions are answered here without grepping.

---

## Top-level folder layout

```
projectroot/
├── users/                   # CORE — never edit anything in here
│   ├── init.php             # bootstrap: DB, session, $user, helpers, classes
│   ├── classes/             # framework classes (DB, Token, Input, Server, User, ...)
│   ├── helpers/             # framework helpers (safeReturn, hed, Hash, etc.)
│   ├── includes/            # framework includes (template/prep.php, etc.)
│   ├── views/               # framework view fragments
│   ├── parsers/             # framework AJAX endpoints
│   ├── _blank_pages/        # starter pages you can copy into root
│   └── *.php                # account.php, login.php, join.php, etc.
├── usersc/                  # YOUR CUSTOMIZATIONS — survives updates
│   ├── includes/            # always-on overrides (head_tags, footer, custom_functions, ...)
│   ├── scripts/             # event hooks (login, logout, registration, ...)
│   ├── views/               # form-view replacements (_join.php, _admin_sidebar.php, ...)
│   ├── modules/             # admin module overrides
│   ├── lang/                # additive language overrides
│   ├── templates/           # front-end templates (FORK before editing the shipped ones)
│   ├── plugins/             # plugins (FORK before editing the shipped ones)
│   ├── widgets/             # dashboard widgets (FORK before editing the shipped ones)
│   └── {anyPage}.php        # top-level: replaces the same-named core page
└── *.php                    # YOUR APP PAGES — these go here, not in usersc/
```

---

## "I want to do X" → look here

| Goal | Open / use |
|---|---|
| Make a new page | Copy `users/_blank_pages/project_root.php` into root, rename. Or use `/userspice-page-scaffold`. |
| Add a function callable from any page | `usersc/includes/custom_functions.php` |
| Override a helper from `users/helpers/` | Define a function with the same name in `usersc/includes/custom_functions.php` (it loads first; helpers are guarded with `function_exists()`) |
| Add `<head>` tags / favicon / extra CSS | `usersc/includes/head_tags.php` |
| Add footer markup / scripts | `usersc/includes/footer.php` |
| Add analytics snippet | `usersc/includes/analytics.php` |
| Set CSP / HSTS / security headers | `usersc/includes/security_headers.php` |
| Run code on every request after bootstrap | `usersc/includes/loader.php` |
| Adjust rate-limit thresholds | `usersc/includes/rate_limits.php` |
| Hook into a successful login | `usersc/scripts/custom_login_script.php` |
| Hook into a successful registration | `usersc/scripts/during_user_creation.php` |
| Add custom join-form fields | `usersc/scripts/additional_join_form_fields.php` |
| Customize the "permission denied" page | `usersc/scripts/did_not_have_permission.php` |
| Customize the CSRF-fail page | `usersc/scripts/token_error.php` |
| Replace the registration form | `usersc/views/_join.php` |
| Replace the admin sidebar | `usersc/views/_admin_sidebar.php` |
| Replace the whole `account.php` page | `usersc/account.php` |
| Override a few language strings | `usersc/lang/{locale}.php` (use `array_merge`, additive) |
| Bring in a Composer package | `usersc/vendor/autoload.php` |
| Add an AJAX endpoint | A `parsers/` subfolder of the calling page (the rewriter exempts `parsers/`) |

---

## Core classes you'll reach for constantly

All live in `users/classes/` and are autoloaded.

| Concern | Class / methods |
|---|---|
| Database | `$db` (global) → `query`, `results`, `first`, `count`, `lastId`, `findAll`, `findById`, `get`, `cell`, `insert`, `update`, `delete`, `deleteById`, `tableExists`, `columnExists`, `addColumn`, `addIndex`, `beginTransaction`, `commit`, `rollBack` |
| User input | `Input::get`, `Input::exists`, `Input::sanitize` |
| Server vars | `Server::get`, `Server::getClientIp`, `Server::getHost`, `Server::getOrigin` |
| CSRF | `Token::generate`, `Token::check` |
| Sessions | `Session::put`, `Session::get`, `Session::flash`, `Session::delete`, `Session::exists` |
| Cookies | `Cookie::get`, `Cookie::put`, `Cookie::delete`, `Cookie::exists` |
| Hashing | `Hash::make` (deterministic), `Hash::unique` (CSPRNG token) |
| Validation | `new Validate(); $v->check($source, $rules); $v->passed(); $v->display_errors()` |
| Rate limiting | `new RateLimit(); $rl->check($action, $ids); $rl->record($action, $ids, $success)` |
| Redirects | `Redirect::to`, `Redirect::sanitized` |
| Current user | `$user` (global) → `data()`, `isLoggedIn()`. Permission check is the *global helper* `hasPerm($id)` or `hasPerm([$id1, $id2])` — there is no `$user->hasPermission()` method. |

---

## Core helpers (functions, not classes)

Live in `users/helpers/`. Highlights:

| Function | Purpose |
|---|---|
| `safeReturn($v)` / `safeReturn($v, true)` | HTML-escape for output. Pass `true` for already-encoded values. |
| `safeJsonEncodeForJs($v)` | JSON-encode for embedding in `<script>` tags. |
| `hed($v)` | Decode HTML entities. |
| `securePage($_SERVER['PHP_SELF'])` | Page guard — checks login + permission for the current page. |
| `tokenHere()` | Echoes the CSRF hidden input. |
| `usError($msg)` / `usSuccess($msg)` | Flash error / success banner for the next request. |
| `spiceEncrypt($plain)` / `spiceDecrypt($blob, '', '')` | AES-256-GCM at-rest encryption. |
| `email($to, $subject, $body, ...)` | Send mail through the configured transport. |

When unsure of a helper's exact signature, run **`/userspice-helper-lookup <name>`** — it reads live from the install.

---

## Globals available after `users/init.php`

| Global | Holds |
|---|---|
| `$db` | DB instance (use `global $db;` inside functions) |
| `$user` | Current user object |
| `$settings` | Row from the `settings` table |
| `$config` | The full config tree (DB creds, session name, mail). **Never assign to this name.** |
| `$abs_us_root` | Filesystem path to project root, with trailing slash |
| `$us_url_root` | URL path to project root (often empty string) |
| `$T` / `$lang` | Active language array |

---

## Database tables you'll see most

| Table | Purpose |
|---|---|
| `users` | User accounts |
| `groups` | Permission groups (referenced by name as "permissions") |
| `permission_page_matches` | Which permissions can access which pages |
| `pages` | Registered pages and their access flags |
| `settings` | Site settings (loaded into `$settings`) |
| `logs` | Activity log + DB-write trace (when `$database_logging = true`) |
| `audit` | Access-denied / forbidden hits |
| `sessions` | DB-backed PHP sessions (when enabled) |

---

## Bulletproof discovery: grep `usersc`

If this page doesn't list the override point you need, find it the same way the core does:

```bash
grep -rn "usersc" users/
```

Every `file_exists(... 'usersc/...')` check in core is an override point you can fill in. New ones get added every release; this is the way to find them without waiting for documentation to catch up.
