<?php /* UserSpice AI Prompt — protected from HTTP access. Markdown content below. */ __halt_compiler(); ?>

# Custom AI Prompts (override layer)

This folder is yours. Drop prompt files here and they show up alongside the shipped
ones in the AI Prompts plugin admin page. Files in this folder **survive plugin updates**.

## File naming

Files must be named `{name}.md.php` — for example, `my_team_conventions.md.php`.

- A file with the same name as a shipped prompt **overrides** the shipped one
  (e.g. `00_start_here.md.php` here wins over `prompts/00_start_here.md.php`).
- A file with a new name shows up as an additional prompt.

The `name` portion (whatever comes before `.md.php`) must be alphanumeric, underscore,
or hyphen only — that's what the `aiPromptPath()` helper allows.

## Required file format

Every prompt file MUST start with this PHP wrapper line:

```
<?php /* UserSpice AI Prompt — protected from HTTP access. Markdown content below. */ __halt_compiler(); ?>
```

The `__halt_compiler();` call halts the PHP lexer permanently — nothing after it
is parsed or executed. So when someone tries to read the file by URL, PHP runs the
empty comment, halts, and returns nothing. The wrapper is stripped automatically
when `aiPromptRead()` returns the content, so the AI sees clean markdown.

(`exit;` would also stop runtime execution, but it lets PHP keep *parsing* — and
your markdown often contains `<?php` examples in code blocks that PHP would then
choke on at parse time. `__halt_compiler();` avoids that entirely.)

After the wrapper, leave a blank line, then write normal markdown.

## Example

```
<?php /* UserSpice AI Prompt — protected from HTTP access. Markdown content below. */ __halt_compiler(); ?>

# My Team's UserSpice Conventions

We do things slightly differently here. Specifically:

- Every page must include `our_telemetry.php` after `init.php`.
- Custom helpers go in `lib/` (project root), not `usersc/includes/`.
- Run `make audit` before declaring any task done.

(...etc...)
```

## Defense in depth

This folder is also protected by `.htaccess` (Apache deny) and an `index.php`
that returns 403. The PHP wrapper is the universal protection — `.htaccess` is
the belt-and-suspenders for Apache.

If you're on **nginx**, add this to your server block to be extra safe:

```
location ~ /usersc/plugins/ai_prompts/(prompts|custom_prompts)/ {
    deny all;
}
```

(The PHP wrapper still protects you without this, but `deny all` is faster — the
request never reaches PHP.)
