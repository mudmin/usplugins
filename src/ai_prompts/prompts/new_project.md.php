<?php /* UserSpice AI Prompt — protected from HTTP access. Markdown content below. */ __halt_compiler(); ?>

# UserSpice — Starting a New Project (or a New Feature)

Load this when you're spinning up a fresh UserSpice install or starting a new feature on an existing one. The "I want to add a thing" workflow.

If the project already exists and you're just modifying behavior of something the framework does, you want **`customizing_core.md.php`** instead.

---

## Step 0: Confirm the install is healthy

Before writing any code on a UserSpice site, the user should verify three things in the admin dashboard. If they haven't, gently surface this — broken installs produce confusing bugs.

1. **Settings → Security Dashboard** — the live posture score. Aim for 100%; everything red has a one-click fix.
2. **Settings → General + Settings → Registration** — every option has a hover tooltip. Worth flipping through once before building on top.
3. **Free API key** at bugs.userspice.com pasted into Spice Shaker — unlocks themes, plugins, widgets, language packs, and in-dashboard updates.

Reference: `getting-started/index.php`.

---

## Step 1: Where does my page go?

UserSpice scans these directories for pages by default:

- `/` (project root) ← **default for app pages**
- `/users/` ← framework pages, don't touch
- `/usersc/` ← reserved for the override patterns (see `customizing_core.md.php`)

Additional folders can be added under **Manage → Pages → Change** in the dashboard. Subfolders work — many sites organize as `/admin/`, `/account/`, `/api/`, etc., and add each one to the allowed list.

> **Default to project root.** Don't put new app pages in `usersc/` just because you don't know where else. `usersc/` is for override hooks, not application code. See `customizing_core.md.php`.

---

## Step 2: Create the page

Two options:

### Option A: Use the scaffold skill (recommended)

```
/userspice-page-scaffold
```

The skill prompts for path, page kind (simple guarded / form-with-handler / AJAX parser), and writes a single file with the canonical security-correct boilerplate. Output passes `/userspice-audit` cleanly.

### Option B: Copy the blank page template

UserSpice ships a starter at `users/_blank_pages/project_root.php`. Copy it to wherever the page should live and rename:

```bash
cp users/_blank_pages/project_root.php contacts.php
```

The first time you visit the page as admin, UserSpice redirects you to the permission-setup screen for that page. Pick which permission groups can access it; that wires up `permission_page_matches` in the database.

### Option C: Start from scratch

The minimum viable UserSpice page is three lines of bootstrap plus the `securePage` guard:

```php
<?php
require_once '../users/init.php';
require_once $abs_us_root . $us_url_root . 'users/includes/template/prep.php';
if (!securePage($_SERVER['PHP_SELF'])) { die(); }
?>

<h1>My Page</h1>
```

The relative path `'../users/init.php'` assumes the page lives one level under the project root. From the root itself, drop one `../`.

For the full canonical pattern (forms, AJAX, validation, escaping), see **`secure_page_pattern.md.php`**.

---

## Step 3: Need a database table?

UserSpice's `DB` class has idempotent schema helpers — perfect for a one-off install script or a plugin installer:

```php
require_once 'users/init.php';

$db->query("
    CREATE TABLE IF NOT EXISTS contacts (
        id          INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        name        VARCHAR(80) NOT NULL,
        email       VARCHAR(120) NOT NULL,
        created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB CHARSET=utf8mb4
");

// Adding columns later? These skip silently if already present.
$db->addColumn('contacts', 'phone', "VARCHAR(40) NULL");
$db->addIndex('contacts', 'idx_email', 'email');
```

Use **InnoDB** (the framework default) so transactions work. UTF8MB4 so emoji and 4-byte characters work.

For querying, use `$db` — already wired up on every page. Inside a function, `global $db;` first.

The full DB reference is `using-the-database/index.php` and the cheat-sheet table in `where_to_look.md.php`. Highlights:

```php
$db->query("SELECT * FROM contacts WHERE email = ?", [$email]);
foreach ($db->results() as $row) {
    echo safeReturn($row->name);
}

$db->insert('contacts', ['name' => $name, 'email' => $email]);
// insert() returns bool — get the new ID from $db->lastId() on the next line, NOT the insert's return value
$newId = $db->lastId();

$db->update('contacts', $newId, ['name' => $newName]);
$db->deleteById('contacts', $newId);

// WHERE-condition array
$db->get('contacts', ['email', '=', $email]);
$db->get('contacts', ['created_at', '>', $cutoff]);
```

---

## Step 4: Need a helper function used across pages?

Put it in `usersc/includes/custom_functions.php`. That file is loaded by [users/helpers/helpers.php:24](users/helpers/helpers.php#L24) **before** any core helper, and core helpers use `function_exists()` guards. So:

- A new helper there is available everywhere.
- A function with the same name as a core helper *replaces* the core version.

```php
// usersc/includes/custom_functions.php

function contactCount() {
    global $db;
    return $db->query("SELECT COUNT(*) AS c FROM contacts")->first()->c;
}
```

See `customizing_core.md.php` for the override mechanics.

---

## Step 5: Need an AJAX endpoint?

It MUST live in a `parsers/` subfolder of the page that calls it. UserSpice's URL rewriter strips `.php` from every other path — `parsers/` is the explicit exemption.

A parser is a separate request with NO inherited auth. Re-do CSRF + page check + permission check at the top of every parser. Full pattern in `secure_page_pattern.md.php`.

---

## Step 6: Need to react to a system event?

Don't intercept it; use the appropriate `usersc/scripts/*.php` hook. After login? `custom_login_script.php`. During registration? `during_user_creation.php`. Permission denied? `did_not_have_permission.php`. Full table in `customizing_core.md.php`.

---

## Step 7: Audit before shipping

```
/userspice-audit
```

Walks every file outside `users/` and reports best-practices violations (raw `$_POST`, missing CSRF, unescaped output, etc.) with file:line references. Run this every time before declaring work done. Then read `userspice-best-practices/index.php` for the full security checklist if anything trips.

---

## A complete, minimal feature: contact-form page

Putting it together. This single file (`contact.php` in the project root) is a real, complete, secure feature:

```php
<?php
require_once 'users/init.php';
require_once $abs_us_root . $us_url_root . 'users/includes/template/prep.php';
if (!securePage($_SERVER['PHP_SELF'])) { die(); }

if (Input::exists()) {
    if (!Token::check(Input::get('csrf'))) {
        usError('Session expired. Please try again.');
        Redirect::to('contact.php');
    }

    $validate = new Validate();
    $validate->check($_POST, [
        'name'    => ['display' => 'Name',    'required' => true, 'min' => 2, 'max' => 80],
        'email'   => ['display' => 'Email',   'required' => true, 'valid_email' => true],
        'message' => ['display' => 'Message', 'required' => true, 'min' => 10, 'max' => 5000],
    ]);

    if ($validate->passed()) {
        $db->insert('contacts', [
            'name'    => Input::get('name'),
            'email'   => Input::get('email'),
            'message' => Input::get('message'),
        ]);
        usSuccess('Thanks — we got your message.');
        Redirect::to('contact.php');
    }
}
?>

<h1>Contact us</h1>

<form method="post">
    <?= tokenHere(); ?>
    <label>Name <input type="text" name="name" value="<?= safeReturn(Input::get('name')) ?>"></label>
    <label>Email <input type="email" name="email" value="<?= safeReturn(Input::get('email')) ?>"></label>
    <label>Message <textarea name="message"><?= safeReturn(Input::get('message')) ?></textarea></label>
    <button type="submit">Send</button>
</form>

<?php if (!empty($validate) && !$validate->passed()) echo $validate->display_errors(); ?>
```

One file. CSRF, validation, escaping, prepared statement, redirect-after-post, flash messages — all the canonical patterns. This is what a new feature should look like.

---

## Reference

- `getting-started/index.php` — the canonical first-run walkthrough
- `secure_page_pattern.md.php` — the security-correct page recipe in depth
- `customizing_core.md.php` — when "starting a feature" is really "modifying something the framework does"
- `where_to_look.md.php` — file map and cheat-sheet
