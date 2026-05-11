<?php /* UserSpice AI Prompt — protected from HTTP access. Markdown content below. */ __halt_compiler(); ?>

# UserSpice — Start Here (read first)

You are working in a **UserSpice** project. UserSpice is a mature PHP user-management framework with its own ORM-lite, auth, permissions, sessions, hooks, and template system. It has strong, opinionated patterns. Use them.

This file is the index. Load the deeper prompts only when the task matches.

> **About this file format:** every prompt in this folder is a `.md.php` file with a one-line `<?php exit; ?>` wrapper at the top. That's an HTTP-protection mechanism — you can ignore it and read the markdown that follows. The shipped prompts live in `prompts/`; same-name files in `custom_prompts/` override them.

---

## The five non-negotiables

1. **Never edit anything in `users/`.** That folder is core. The next update will overwrite your changes. Every customization happens in `usersc/`.
2. **`usersc/` is for specific override patterns, not a dumping ground.** Regular pages live in the root (or any folder enabled in Manage → Pages), not in `usersc/`. See `customizing_core.md.php` for what *does* belong in `usersc/`.
3. **Use the framework's helpers, not raw PHP.** Every common security concern already has a helper. Reaching into `$_POST`, `$_SERVER`, or building SQL with concatenation is wrong by default. See `secure_page_pattern.md.php`.
4. **`$db` is already wired up.** On any page that includes `users/init.php`, `$db` is live. Inside a function, write `global $db;` first. Never call `DB::getInstance()` in this codebase — it's not the convention.
5. **Every mutating endpoint validates a CSRF token AND re-checks auth.** That includes AJAX parsers, which are *separate requests* and inherit nothing from the calling page.

---

## Where to go next

| If you're doing this... | Load this prompt |
|---|---|
| Bootstrapping a fresh page or feature | `new_project.md.php` |
| Overriding a core helper, page, or behavior | `customizing_core.md.php` |
| Writing the actual page file (security-correct) | `secure_page_pattern.md.php` |
| Anything involving access control, groups, or "who can see X" | `permissions.md.php` |
| Something doesn't work and you don't know why | `debugging.md.php` |
| "Where does X live?" / file map | `where_to_look.md.php` |

Each task-specific prompt links back to the canonical site guides for depth. The four canonical guides are:

- `userspice-best-practices/index.php` — the security checklist (Input::get, Token, Validate, safeReturn, etc.)
- `using-the-database/index.php` — the full DB class reference
- `getting-started/index.php` — first-run / installer walkthrough
- `understanding-the-usersc-folder/index.php` — every override point in `usersc/`

When you need the canonical wording, read the guide. The prompts here are summaries, not replacements.

---

## Available skills (invoke when matching)

If installed in this environment:

- **`/userspice-helper-lookup`** — given a helper name (`safeReturn`), class method (`Token::check`), or task ("escape html for js"), returns the canonical signature with file:line. **Use this any time you're unsure of a helper signature** — it reads from the live install, so it never goes stale.
- **`/userspice-page-scaffold`** — generates a security-correct page file (guarded page, form-with-handler, or AJAX parser). Use this instead of writing a page from memory; the output is designed to pass `/userspice-audit` cleanly.
- **`/userspice-audit`** — audits custom code (everything outside `users/`) against the official best-practices guide. Run before declaring work done.

These are optional Claude Code companion skills — the prompts in this plugin work standalone without them. If they're not installed and you'd find them useful, the install instructions and source live at **<https://github.com/UserSpice-AI/userspice-claude-skills>**. Drop each folder into `~/.claude/skills/` on the workstation running Claude Code.

---

## Things that bite people new to UserSpice

- **`Input::get('foo')` returns `""` (empty string) when the key is missing**, not `false` or `null`. Test presence by comparing the return value to `''` (or use `empty()` / `=== ''`).
- **`Input::exists()` does NOT take a field name.** The argument is a *type* — `'post'` or `'get'` — not a field. `Input::exists('action')` always returns `false` because `'action'` isn't a recognized type and the default branch returns `false`. The whole if-block is silently dead. To check whether a specific field was submitted, just use `Input::get('action') === 'save'` (since missing fields return `''`). `Input::exists()` alone (no args, defaults to `'post'`) is the right way to ask "is this a POST request?" See [secure_page_pattern.md.php](secure_page_pattern.md.php) for the full rundown.
- **Use `$abs_us_root . $us_url_root . 'path/to/file.php'` for `require_once`, never `__DIR__ . '/../path'`.** Both work at runtime, but the framework convention is the absolute-root pattern — it's filesystem-vs-webroot-aware, survives subpath installs, and matches every shipped page. `__DIR__` relative paths are brittle if the file moves and look wrong in a UserSpice codebase. The *only* exception is the bootstrap line itself (`require_once 'users/init.php'`), which uses a relative path because `$abs_us_root`/`$us_url_root` aren't defined yet.
- **Use `<?= $us_url_root ?>path/to/file.css` for internal links, hrefs, src, and form actions** (or just omit the form action and self-post). Don't hardcode `/includes/...` or `./assets/...` — the site may be installed at any subpath, and `$us_url_root` is the right way to get from "wherever the doc root is" to "wherever this UserSpice install is."
- **`$config` is a reference to `$GLOBALS['config']`** at global scope. Assigning `$config = [...]` in a page script wipes the framework's entire configuration. Use any other variable name.
- **AJAX endpoints MUST live in a `parsers/` subfolder.** UserSpice's URL rewriter strips `.php` extensions, but exempts paths containing `parsers/`. Anywhere else and your AJAX call hits a 404 or worse.
- **Helpers use `if (!function_exists(...))` guards.** That's the override mechanism — defining a function in `usersc/includes/custom_functions.php` (which loads first) silently shadows the core helper. See [users/helpers/helpers.php:24](users/helpers/helpers.php#L24).
- **Shipped templates/plugins/widgets in `usersc/` *do* get updated.** Always fork (`usersc/templates/standard/` → `usersc/templates/your-name/`) before customizing.
- **`tokenHere()` outputs `name="csrf"`, not `name="csrf_token"`.** The matching handler is `Token::check(Input::get('csrf'))`. Mixing up the field name is the #1 cause of "every form submission silently fails."
- **`hasPerm()` is a global function, not a method on `$user`.** There is no `$user->hasPermission()` — that call will fatal. See `permissions.md.php`.
- **`$db->insert()` and `$db->update()` return `bool`, not the new ID or affected row count.** `$id = $db->insert(...)` gives you `true`, not the auto-increment ID. Get the ID via `$db->lastId()` after the insert; get the affected row count via `$db->count()` after the update. The Laravel/Eloquent reflex of "assign the result and use it" silently corrupts foreign keys.
- **A literal `?>` inside a `//` PHP comment exits PHP mode.** Everything after dumps to the browser as text and any function definitions below never get defined. See `debugging.md.php` for the full trap.

---

## How these prompts are organized on disk

These prompts live in a UserSpice plugin called `ai_prompts`:

```
usersc/plugins/ai_prompts/
├── prompts/                  ← shipped (this file lives here; updates may replace it)
│   ├── 00_start_here.md.php
│   ├── where_to_look.md.php
│   ├── customizing_core.md.php
│   ├── secure_page_pattern.md.php
│   ├── permissions.md.php
│   ├── debugging.md.php
│   └── new_project.md.php
└── custom_prompts/           ← project-local additions and overrides (survives updates)
    └── (your prompts here)
```

If a file with the same name exists in `custom_prompts/`, it wins. If you find guidance here that doesn't match this project's conventions, check `custom_prompts/` for an override before assuming the shipped guidance is wrong.
