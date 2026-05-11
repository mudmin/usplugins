<?php /* UserSpice AI Prompt — protected from HTTP access. Markdown content below. */ __halt_compiler(); ?>

# UserSpice — Customizing the Core

Load this when the task involves changing the behavior of something that already exists in UserSpice: a helper function, a system page (login/account/join), a system message, an auth flow, a navigation item, or "what should happen after a user does X."

The single rule that makes everything else fall into place:

> **Never edit `users/`. Always work through `usersc/`.**

Updates wipe `users/` clean. `usersc/` survives. There is an override point for almost everything; the rest of this prompt is "which override point, in which situation."

---

## The four override patterns

Every customization in UserSpice is one of these four. Identifying the pattern first tells you exactly where the code goes.

| Pattern | What it means | Lives in |
|---|---|---|
| **Replace** | Your file is loaded *instead of* the core file | `usersc/{page}.php`, `usersc/views/_*.php` |
| **Append** | Core runs first, then yours runs after | `usersc/includes/head_tags.php`, `footer.php`, etc. |
| **Event hook** | Your file fires at one specific moment | `usersc/scripts/*.php` |
| **Drop-in module** | Auto-discovered by core | `usersc/plugins/`, `usersc/widgets/`, `usersc/templates/`, `usersc/modules/` |

Most overrides work through a simple `file_exists()` check in core. If your file is there, it runs. If not, core's default behavior runs.

---

## Pattern 1: Override a helper function (the most common case)

`usersc/includes/custom_functions.php` is loaded by [users/helpers/helpers.php:24](users/helpers/helpers.php#L24) **before** any of the core helper files. Every core helper is wrapped in `if (!function_exists(...))`. That means:

> **Define a function with the same name as a core helper in `custom_functions.php`, and your version wins.** The core version is silently skipped.

This is the canonical way to change the behavior of `safeReturn`, `email`, `tokenHere`, `usError`, etc.

```php
// usersc/includes/custom_functions.php

// Override the shipped email() function to route through a transactional API
function email($to, $subject, $body, $opts = []) {
    // your implementation
    return true; // match the core signature
}
```

Caveats:
- **Match the signature exactly.** If the core helper takes `($v, $second = false)`, your override has to accept the same arguments or callers will break.
- **You are now responsible for the function's behavior across updates.** When UserSpice changes the helper's default behavior, your override won't pick that up. Treat overrides like load-bearing infrastructure — review them when you upgrade.
- **Loading order:** `custom_functions.php` runs first, then *plugin* `override.php` files, then the core helpers. So plugins can also shadow helpers, and your `custom_functions.php` wins over both.

If the override is project-specific (just your helper for your app, not a replacement of a core function), still put it here. `custom_functions.php` is the right home for "a function I want available on every page in my project."

---

## Pattern 2: Replace a whole page

Drop a same-named file into the `usersc/` top level. UserSpice's loader checks for it on every request and routes there instead of the core file.

```
users/account.php      # core (don't edit)
usersc/account.php     # your version — used instead
```

When this is the right choice:
- You want a totally different account dashboard layout / fields.
- The shipped page doesn't have a hook for what you want to add.

When this is **wrong** (and a hook would be better):
- You only want to add a section to the existing page — use the page's hook file or a `pre_footer.php` instead.
- You want to react to a specific event (login, logout, registration) — use `usersc/scripts/*.php`.

Replacing a page means **you're maintaining the full page forever**. Anything the upstream page gains (security fixes, new features) won't reach your replacement.

The loader sanitizes the query string before forwarding to your replacement — it drops `redirect`, `url`, `next`, etc. to prevent open-redirect bugs introduced through this mechanism.

---

## Pattern 3: Replace a form view

Several core flows render a "view" file as their body. Drop the matching file into `usersc/views/`:

| View file | What it replaces |
|---|---|
| `usersc/views/_join.php` | The registration form |
| `usersc/views/_joinDisabled.php` | Shown when registration is off |
| `usersc/views/_joinThankYou.php` | Post-registration confirmation |
| `usersc/views/_forgot_password.php` / `_forgot_password_sent.php` | The forgot-password flow |
| `usersc/views/_verify_success.php` / `_verify_error.php` | Email-verification result pages |
| `usersc/views/_social_logins.php` | The social-login button strip |
| `usersc/views/_admin_sidebar.php` / `_admin_menu.php` | Admin dashboard chrome |

Same trade-off as full page replacement: you own it forever. If the shipped view picks up a security fix, your fork won't.

---

## Pattern 4: Event hooks (`usersc/scripts/`)

These are the lightest-weight customization. Each file fires at one specific moment in a user flow. Open the file, add your code, save. That's it.

| Hook file | Fires when |
|---|---|
| `custom_login_script.php` | After a successful login (best place for per-user landing redirects) |
| `custom_login_script_no_redir.php` | Same hook, no-redirect variant (AJAX flows) |
| `just_before_logout.php` | Right before the session is torn down |
| `just_after_logout.php` | Right after logout completes |
| `additional_join_form_fields.php` | Extra fields rendered on join form + admin Add User |
| `during_user_creation.php` | While a new user is being created (process custom fields here) |
| `after_user_deletion.php` | After a user is deleted (clean up related rows) |
| `not_logged_in.php` | A guest hit a page requiring login |
| `did_not_have_permission.php` | A logged-in user hit a page they aren't allowed to see |
| `banned.php` | A banned user tried to use the site |
| `token_error.php` | CSRF token check failed |
| `spice_update_begins.php` / `_success.php` / `_fail.php` | UserSpice self-update lifecycle |

**This is almost always the right answer.** If a hook exists for what you want, use it. Don't replace a whole page just to add a side-effect after login.

---

## `usersc/includes/` — the always-loaded includes

These files come with UserSpice already wired into core. Edit them in place; UserSpice will not overwrite them on update.

### Output / chrome
- `head_tags.php` — extra tags appended to `<head>` (meta, favicon, custom CSS)
- `footer.php` — appended to every page's footer
- `analytics.php` — your tracking snippet
- `security_headers.php` — CSP / HSTS / X-Frame-Options (always loaded; no `file_exists` check needed)

### Behavior / runtime
- `loader.php` — runs at the end of every request's bootstrap. The "run on every page" entry point.
- `custom_functions.php` — see Pattern 1 above.
- `rate_limits.php` — override per-action limits
- `oauth_success_redirect.php` — runs after OAuth login
- `dashboard_overrides.php` — set `$template_override` for the admin dashboard

### UI
- `database_navigation_hooks.php` / `_dropdown.php` — replace `{{placeholders}}` in nav items
- `user_manager_columns.php` — which columns appear on Manage Users
- `user_agreement.php` — your ToS copy

### Optional files you can create
These aren't shipped — create them when you need them; core will pick them up:
- `pre_footer.php` — rendered just before the page footer
- `system_messages_header.php` / `_footer.php` — extend the success/error banner system
- `password_meter.php` — replace the shipped strength meter
- `totp_requirements.php` — override per-page TOTP enforcement
- `admin_panel_buttons.php` / `admin_panels.php` / `admin_panel_custom_settings.php` — extend the admin dashboard

---

## What does NOT belong in `usersc/`

`usersc/` is for **override points the framework recognizes**, not a junk drawer. Don't put generic application code here just because you don't know where else to put it.

- **Regular app pages** belong in the project root (or any folder enabled in Manage → Pages). Not `usersc/`.
- **Models / app-specific classes** belong wherever your app organizes them (often a `lib/` or `app/` folder in the project root). Not `usersc/`.
- **One-off scripts** belong in a project-specific folder. Not `usersc/`.
- **Composer dependencies** go through `usersc/vendor/autoload.php`, which *is* a usersc convention — that one is correct.

If `usersc/` ends up holding hundreds of unrelated files, you've drifted from the pattern. The folder should be small and obviously categorical: hooks, view overrides, custom_functions.php, plus the shipped templates/plugins/widgets folders.

---

## The "fork before customizing" rule

Even though they live in `usersc/`, the **shipped** templates, plugins, and widgets *do* get updated automatically when UserSpice pushes a security patch. Edit one of them in place and your changes will be wiped out.

Always fork:

```
# Wrong — your edits will be overwritten by the next push
usersc/templates/standard/header.php  ← edited

# Right — your fork is yours forever
cp -r usersc/templates/standard usersc/templates/yourname
# now edit usersc/templates/yourname/header.php
```

Same pattern for plugins (`usersc/plugins/foo/` → `usersc/plugins/foo-yours/`) and widgets.

---

## Language overrides are different (additive, not replacing)

Language is the one exception to "drop a file in `usersc/` to replace it." Translations are **merged** on top of the shipped pack. To change a handful of keys in `users/lang/it-IT.php`, create `usersc/lang/it-IT.php` and only define the keys you want to change:

```php
<?php
$lang = array_merge($lang, array(
    "DAT_SEARCH" => "Your translation here",
    "DAT_FIRST"  => "Your translation here",
));
```

Untouched keys keep their shipped value. Don't fork the whole language file.

---

## Finding override points yourself

New override points get added in every release. The bulletproof way to find them:

```bash
grep -rn "usersc" users/
```

Every `file_exists(... 'usersc/...')` check in core is a hook waiting to be filled. If you're trying to change behavior in a specific core file, open it and search for `usersc` — there's a good chance the hook is already there.

---

## Decision tree

When the user wants to change something the framework already does:

1. **Is there a `usersc/scripts/` event hook for it?** → Use that. (Logins, logouts, registration, deletion, errors, update lifecycle.)
2. **Is there a `usersc/includes/` file for it?** → Edit that. (Head tags, footer, analytics, security headers, loader, custom_functions.)
3. **Is the target a helper function?** → Override it in `custom_functions.php` with the same name.
4. **Is the target a form view (`_join`, `_forgot_password`, etc.)?** → Replace it in `usersc/views/`.
5. **Is the target a whole top-level page?** → Replace it via `usersc/{page}.php` — but only after confirming none of the above work. This is the heaviest option and you own it forever.
6. **None of the above?** → `grep -rn "usersc" users/` to find a hook you didn't know about. If genuinely none exists, that's a feature request — file it at bugs.userspice.com rather than editing `users/`.

---

## Canonical reference

This prompt summarizes `understanding-the-usersc-folder/index.php`. Read that guide when you need exact wording or the rationale behind a pattern.
