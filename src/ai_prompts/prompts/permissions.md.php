<?php /* UserSpice AI Prompt — protected from HTTP access. Markdown content below. */ __halt_compiler(); ?>

# UserSpice — Permissions

Load this when the task involves access control: who can see a page, who can do an action, what the "you must go to the Admin Panel" message means, why a page suddenly redirects you to a permission-setup screen, or how to gate a feature on whether a user belongs to some group.

UserSpice's permission system is **role-based with explicit page-to-role mapping**. There are no scopes, capabilities, or wildcards — just integers (permission level IDs) attached to users and to pages.

---

## The four tables you need to know

| Table | What it stores |
|---|---|
| `groups` | The permission levels themselves: `id`, `name`, `description`. A "permission" and a "group" are the same thing. |
| `user_permission_matches` | Which users belong to which groups. Many-to-many: one row per (user_id, permission_id) pair. |
| `pages` | Every page the framework knows about: `id`, `page` (relative path), `private` (0 = public, 1 = private), plus `title`, `re_auth`, etc. |
| `permission_page_matches` | Which permission levels can access which private pages. Many-to-many: one row per (page_id, permission_id) pair. |

The shipped permission levels (you can add more in the admin):

| ID | Name | Meaning |
|---|---|---|
| 0 | Banned | Special — `securePage()` redirects these users to `usersc/scripts/banned.php` regardless of which page they're hitting |
| 1 | Logged In Users | Default for any registered user |
| 2 | Administrators | The "admin" tier. Almost all admin pages require this. |

You can add your own (e.g. `Editor`, `Tenant Admin`, `Beta Tester`) in **Manage → Groups**. Pick a high-numbered ID so you don't conflict with future framework defaults.

---

## What `securePage()` actually does (read this once, refer back)

`securePage($_SERVER['PHP_SELF'])` is the gatekeeper at the top of every protected page. The flow:

1. **Banned check.** If the current user has permission level 0, redirect to `usersc/scripts/banned.php` and `die()`. Doesn't matter which page they were trying to access.
2. **Page-registration check.** Look up the requested path in the `pages` table.
   - **Not registered, current user is admin (perm 2):** Auto-insert a row, then redirect to `users/admin.php?view=page&new=yes&id=X&dest=...` — the "set up permissions for this new page" screen. **This is the surprise that confuses everyone the first time.** Just pick the permission levels that should access the page and click save; you'll be sent back to the original URL.
     - Special case: if the new page is under `usersc/` and a same-named page exists under `users/`, the new row's permissions are auto-mirrored from the `users/` version (so overriding a core page keeps its access rules). No setup screen.
   - **Not registered, current user is NOT admin:** Print the message `"You must go into the Admin Panel and click the Manage Pages button to add this page to the database. Doing so will make this error go away."` and `die()`. Yes, this is jarring. The fix is "log in as admin and visit the page once" — see step above.
3. **Public-page check.** If the page row has `private = 0`, return `true` (allow access). Done.
4. **Logged-in check.** If the user isn't logged in:
   - Log to the `audit` table (user=0, page=this).
   - Include `usersc/scripts/not_logged_in.php` (the customization hook).
   - Capture the destination URL so login can come back here.
   - Redirect to `users/login.php`.
   - Return `false`.
5. **Permission match check.** Look up `permission_page_matches` for this page.
   - If the list is empty, default to `[2]` (admin only). Sensible secure default.
   - If the user has any of the listed permissions, return `true`.
   - If the user is in `$master_account` (the super-admin override defined in `init.php`), return `true` regardless.
6. **Permission denied path.** Otherwise:
   - Log to the `audit` table.
   - Include `usersc/scripts/did_not_have_permission.php`.
   - Run any `noAccess` event hooks.
   - Redirect to `Config::get('homepage')` (or the project root if no homepage is configured).
   - Return `false`.

The function lives at `users/helpers/permissions.php` (around line 308 in current versions). It's worth opening once to see this logic in your install.

---

## Checking permissions in code: `hasPerm()`

`hasPerm()` is the global helper for "does this user have any of these permissions?" There is **no `$user->hasPermission()` method** — the function lives in `users/helpers/permissions.php`.

```php
hasPerm($permissions, $id = null, $masterCheck = true)
```

- `$permissions` — single permission ID or array of IDs. The check is OR-based: passes if the user has *any* of them.
- `$id` — user ID to check. Defaults to the currently logged-in user. Pass an integer to check a specific user.
- `$masterCheck` — defaults `true`. If true, master accounts always return true. Set to `false` if you want a strict permission check that ignores the master backdoor.

Patterns you'll write:

```php
// Admin-only feature
if (hasPerm(2)) {
    echo '<button>Delete user</button>';
}

// Either of two permissions
if (hasPerm([2, 5])) {
    // user is an admin OR a moderator
}

// Check a different user (e.g., "can this user see other-user's profile?")
if (hasPerm(2, $otherUserId)) {
    // $otherUserId is an admin
}

// Strict check — ignore the master_account override
if (hasPerm(2, null, false)) {
    // ONLY users with explicit perm 2; master accounts fail this check
}
```

`hasPerm()` returns `false` if `$user` isn't set or there's a database error — it fails closed.

---

## The `master_account` array (the backdoor — by design)

`init.php` defines a global `$master_account` array of user IDs (often just `[1]` for the original installer admin). Users in this array bypass:

- All permission checks in `hasPerm()` (unless `$masterCheck` is explicitly false)
- The "permission denied" path in `securePage()`

This exists so you can never lock yourself out of your own site by removing your own admin permission. It is also the standard guard for plugin admin files — every page in a plugin's root that mutates state should start with:

```php
if (!in_array($user->data()->id, $master_account)) {
    Redirect::to($us_url_root . 'users/admin.php');
}
```

You'll see this pattern at the top of `install.php`, `activate.php`, `uninstall.php`, `delete.php`, `migrate.php`, and `configure.php` in every shipped plugin.

---

## Gating UI vs gating endpoints

> **A permission check on a page does not protect the data — it just hides the UI.**

If you only check `hasPerm(2)` to decide whether to *show* a Delete button, but the parser at `parsers/delete_item.php` doesn't check, anyone can call the parser directly with the right `id` and delete things.

Rule: **every endpoint re-checks**. Parsers, AJAX handlers, form-target pages, exports — all of them. The button being hidden is a UX nicety, not a security boundary.

```php
// In the page (UX): hide the button for non-admins
<?php if (hasPerm(2)): ?>
  <button data-action="delete" data-id="<?= safeReturn($post->id) ?>">Delete</button>
<?php endif; ?>

// In the parser (security): re-verify
// admin/parsers/delete_post.php
require_once '../../users/init.php';
header('Content-Type: application/json');

if (!Token::check(Input::get('csrf'))) { http_response_code(403); die('CSRF'); }
if (!securePage($_SERVER['PHP_SELF'])) { exit; }
if (!hasPerm(2)) { http_response_code(403); die('Forbidden'); }

$id = (int)Input::get('id');
$db->query("DELETE FROM posts WHERE id = ?", [$id]);
echo json_encode(['ok' => true]);
```

`securePage()` already re-runs the page's permission check — but it only knows about the page-level rule (`permission_page_matches`). If you have a page that several roles can access but only *some* of them can perform a specific action, you need an *additional* `hasPerm()` check around the action itself.

---

## Setting up permissions for a new page (the manual route)

If you don't want the auto-redirect to the setup screen (e.g. you're scripting a plugin install that creates pages), you can wire `pages` and `permission_page_matches` directly:

```php
// 1. Register the page
$db->insert('pages', [
    'page'    => 'reports/sales.php',  // path relative to project root
    'private' => 1,                     // 0 = public, 1 = requires login + permission
    'title'   => 'Sales Reports',
]);
$pageId = $db->lastId();

// 2. Allow specific permission levels
foreach ([2, 5] as $permId) {  // admin (2) and "Sales Manager" (5, if you've created it)
    $db->insert('permission_page_matches', [
        'page_id'       => $pageId,
        'permission_id' => $permId,
    ]);
}
```

This is what `securePage()` does internally on the first admin visit, just with a UI in between. For plugin installers, doing it inline is cleaner.

---

## Adding/removing user permissions in code

```php
// Grant
$db->insert('user_permission_matches', [
    'user_id'       => $userId,
    'permission_id' => 5,
]);

// Revoke
$db->delete('user_permission_matches', [
    'and',
    ['user_id', '=', $userId],
    ['permission_id', '=', 5],
]);
```

Most code shouldn't be doing this directly — there are admin UIs in the dashboard for it. But for plugin installers, automated provisioning, or bulk imports, this is the pattern.

---

## Common gotchas

- **"Why am I being redirected to the homepage?"** — `securePage()` hit the permission-denied path (step 6 above). The user is logged in but doesn't have a matching permission. Check `permission_page_matches` for that page, and check what the user's groups are in `user_permission_matches`.
- **"Why am I being redirected to a Set Up Permissions screen?"** — First admin visit to an unregistered page (step 2 above). It's a feature; just save and go.
- **"Why does a non-admin see 'You must go into the Admin Panel'?"** — The page isn't registered yet. Have an admin visit it once.
- **"My new page bypasses my permission rules."** — Did you call `securePage($_SERVER['PHP_SELF'])`? If you skipped it, the page is wide open. Did you register it under a path that doesn't match `$_SERVER['PHP_SELF']`? Compare exactly.
- **"My master account bypasses my new permission level — that's a security issue!"** — It's intentional. If you genuinely need a check the master can't bypass, pass `false` as the third argument to `hasPerm()`. Use sparingly; locking yourself out is easy.
- **"I deleted permission_page_matches rows for a page and now it's admin-only."** — `securePage()` defaults to `[2]` (admin) when the list is empty. Add explicit rows to broaden access.
- **"My plugin's configure.php is wide open!"** — It probably isn't, if you used the demo skeleton. The `if (!in_array($user->data()->id, $master_account))` guard at line 1 redirects non-master users away. If you wrote your own configure.php from scratch and skipped that line, fix it now.

---

## Reference

- `users/helpers/permissions.php` — `securePage`, `hasPerm`, `fetchPagePermissions`, `checkMenu`, `permission_exists`, etc.
- `users/classes/User.php` — `data()`, `isLoggedIn()`, `find()`, `login()`, etc. (no `hasPermission` method.)
- For the AJAX-endpoint pattern: see `secure_page_pattern.md.php`.
- For plugin-specific permission patterns: see the master_account guard in any shipped plugin's `install.php`.
