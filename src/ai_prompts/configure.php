<?php if (!in_array($user->data()->id, $master_account)) {
  Redirect::to($us_url_root . 'users/admin.php');
} //only allow master accounts to manage plugins!
?>

<?php
include "plugin_info.php";
pluginActive($plugin_name);

if (!empty($_POST)) {
  if (!Token::check(Input::get('csrf'))) {
    include($abs_us_root . $us_url_root . 'usersc/scripts/token_error.php');
  }
}

$prompts        = aiPromptList();
$selectedName   = Input::get('prompt');
$selectedPrompt = isset($prompts[$selectedName]) ? $prompts[$selectedName] : null;
$selectedBody   = $selectedPrompt ? aiPromptRead($selectedName) : null;

$shippedDir = aiPromptsDir();
$customDir  = aiPromptsCustomDir();

// CLAUDE.md target lives in the project root (the only file this plugin writes outside its own folder).
$claudeMdPath   = $abs_us_root . $us_url_root . 'CLAUDE.md';
$claudeMdExists = is_file($claudeMdPath);
$claudeMdMtime  = $claudeMdExists ? date('Y-m-d H:i:s', filemtime($claudeMdPath)) : null;

// Pre-written content. Heredoc keeps the markdown readable; the {$var} interpolations
// pull in the absolute prompt-folder paths so the agent can read the files directly.
$claudeMdContent = <<<MD
## UserSpice AI Prompts

This is a UserSpice site. Before doing substantive work, read:

  {$shippedDir}00_start_here.md.php

That file indexes the available task-specific prompts — load the one that
matches what you're doing.

Local overrides may live alongside in `{$customDir}` —
files there with the same name win over the shipped versions.

Each prompt file is wrapped in `<?php /* ... */ __halt_compiler(); ?>` for
protection from direct HTTP access; skip the first line and read the markdown
that follows.
MD;

// Handle the "write CLAUDE.md" form submission. CSRF was already checked above.
$writeMessage = null;
$writeStatus  = null; // 'success' | 'error'
if (Input::get('action') === 'write_claudemd') {
  if ($claudeMdExists && Input::get('overwrite') !== '1') {
    $writeStatus  = 'error';
    $writeMessage = 'CLAUDE.md already exists. Tick "Overwrite existing file" if you want to replace it.';
  } else {
    $bytes = @file_put_contents($claudeMdPath, $claudeMdContent);
    if ($bytes === false) {
      $writeStatus  = 'error';
      $writeMessage = 'Failed to write CLAUDE.md — check that the project root is writable by the web user.';
    } else {
      $writeStatus    = 'success';
      $writeMessage   = ($claudeMdExists ? 'Overwrote ' : 'Wrote ') . $claudeMdPath . ' (' . $bytes . ' bytes).';
      $claudeMdExists = true;
      $claudeMdMtime  = date('Y-m-d H:i:s', filemtime($claudeMdPath));
    }
  }
}
?>

<!-- CDN: syntax highlighting (atom-one-dark) + markdown rendering -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.10.0/styles/atom-one-dark.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.10.0/highlight.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/marked/12.0.2/marked.min.js"></script>

<style>
  /* All styles scoped to .aip so we don't bleed into the rest of the admin dashboard. */
  .aip {
    --aip-dark-bg:    #282c34;
    --aip-dark-text:  #abb2bf;
    --aip-accent:     #007bff;  /* Bootstrap primary */
    --aip-accent-dk:  #0062cc;  /* Bootstrap primary, darkened */
    --aip-border:     #e5e7eb;
    --aip-muted:      #6b7280;
    --aip-soft:       #f9fafb;
    --aip-shadow:     0 1px 2px rgba(0,0,0,.04), 0 1px 3px rgba(0,0,0,.06);
    color: #1f2937;
  }

  /* Hero */
  .aip-hero {
    margin: 0 0 28px;
    padding: 0 0 20px;
    border-bottom: 1px solid var(--aip-border);
  }
  .aip-hero a.aip-back {
    font-size: 13px;
    color: var(--aip-muted);
    text-decoration: none;
  }
  .aip-hero a.aip-back:hover { color: var(--aip-accent); }
  .aip-hero h1 {
    font-size: 30px;
    font-weight: 700;
    letter-spacing: -.02em;
    margin: 10px 0 6px;
    display: flex;
    align-items: center;
    gap: 14px;
  }
  .aip-hero h1 .aip-icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    background: var(--aip-accent);
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    box-shadow: 0 2px 6px rgba(0,123,255,.30);
  }
  .aip-hero p.lead {
    font-size: 15.5px;
    color: var(--aip-muted);
    max-width: 720px;
    margin: 0;
    line-height: 1.55;
  }

  /* Cards */
  .aip .card {
    border: 1px solid var(--aip-border);
    border-radius: 10px;
    box-shadow: var(--aip-shadow);
    margin-bottom: 22px;
    overflow: hidden;
  }
  .aip .card-header {
    background: var(--aip-soft);
    border-bottom: 1px solid var(--aip-border);
    padding: 12px 18px;
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    letter-spacing: .01em;
  }
  .aip .card-header.aip-header-accent {
    background: var(--aip-accent);
    color: #fff;
    border-bottom: none;
  }
  .aip .card-body { padding: 18px 20px; }
  .aip .card-body > p:last-child { margin-bottom: 0; }

  /* Dark code blocks (for both highlighted and plain pre) */
  .aip pre {
    background: var(--aip-dark-bg);
    color: var(--aip-dark-text);
    padding: 14px 16px;
    border-radius: 8px;
    font-size: 13px;
    line-height: 1.6;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, "Cascadia Code", Consolas, monospace;
    white-space: pre-wrap;
    word-wrap: break-word;
    margin: 0 0 14px;
    overflow: auto;
    max-height: 480px;
    border: 1px solid #1f2228;
  }
  .aip pre code {
    background: transparent;
    color: inherit;
    padding: 0;
    font-size: inherit;
    border-radius: 0;
  }
  /* Make sure hljs's own theme styles don't add a second background */
  .aip pre code.hljs { background: transparent; padding: 0; }

  /* Inline code */
  .aip code {
    background: #f3f4f6;
    color: #db2777;
    padding: 1px 6px;
    border-radius: 4px;
    font-size: 13px;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, monospace;
  }

  /* Prompt list table */
  .aip .table { margin-bottom: 0; }
  .aip .table th {
    background: var(--aip-soft);
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: var(--aip-muted);
    font-weight: 600;
    border-top: none;
    padding: 10px 14px;
  }
  .aip .table td {
    padding: 12px 14px;
    vertical-align: middle;
    border-top: 1px solid var(--aip-border);
  }
  .aip .table tbody tr:hover { background: #fafbff; }

  /* Badges */
  .aip .badge {
    font-size: 11px;
    padding: 4px 9px;
    font-weight: 600;
    border-radius: 999px;
    letter-spacing: .02em;
  }
  .aip .badge-secondary { background: #e5e7eb; color: #4b5563; }
  .aip .badge-success   { background: #d1fae5; color: #065f46; }
  .aip .badge-warning   { background: #fef3c7; color: #92400e; }

  /* Rendered prompt markdown viewer */
  .aip .aip-rendered {
    font-size: 15px;
    line-height: 1.7;
    color: #1f2937;
  }
  .aip .aip-rendered h1,
  .aip .aip-rendered h2,
  .aip .aip-rendered h3 {
    margin: 26px 0 10px;
    font-weight: 700;
    letter-spacing: -.015em;
  }
  .aip .aip-rendered h1:first-child,
  .aip .aip-rendered h2:first-child { margin-top: 0; }
  .aip .aip-rendered h1 { font-size: 24px; }
  .aip .aip-rendered h2 { font-size: 19px; padding-bottom: 6px; border-bottom: 1px solid var(--aip-border); }
  .aip .aip-rendered h3 { font-size: 16px; }
  .aip .aip-rendered p { margin: 0 0 12px; }
  .aip .aip-rendered ul,
  .aip .aip-rendered ol { margin: 0 0 14px; padding-left: 24px; }
  .aip .aip-rendered li { margin: 4px 0; }
  .aip .aip-rendered a { color: var(--aip-accent); text-decoration: none; }
  .aip .aip-rendered a:hover { text-decoration: underline; }
  .aip .aip-rendered table {
    width: 100%;
    border-collapse: collapse;
    margin: 14px 0;
    font-size: 14px;
  }
  .aip .aip-rendered th,
  .aip .aip-rendered td {
    border: 1px solid var(--aip-border);
    padding: 8px 12px;
    text-align: left;
    vertical-align: top;
  }
  .aip .aip-rendered th { background: var(--aip-soft); font-weight: 600; }
  .aip .aip-rendered blockquote {
    border-left: 3px solid var(--aip-accent);
    padding: 6px 14px;
    margin: 14px 0;
    background: #e7f3ff;
    color: #004085;
    border-radius: 0 6px 6px 0;
  }
  .aip .aip-rendered hr {
    border: 0;
    border-top: 1px solid var(--aip-border);
    margin: 22px 0;
  }
  .aip .aip-rendered pre { max-height: 600px; }

  /* Path / status row */
  .aip .aip-meta-list { font-size: 13.5px; color: #4b5563; margin: 0; padding-left: 18px; }
  .aip .aip-meta-list li { margin: 4px 0; }

  /* Buttons — let Bootstrap's defaults handle .btn-primary; just tighten the small variant. */
  .aip .btn-sm { font-size: 12px; padding: 4px 10px; }

  /* Alerts */
  .aip .alert { border-radius: 8px; font-size: 14px; padding: 12px 14px; }
</style>

<div class="content mt-3">
  <div class="aip">

    <!-- Hero -->
    <div class="aip-hero">
      <a class="aip-back" href="<?= $us_url_root ?>users/admin.php?view=plugins"><i class="fa fa-arrow-left"></i> Back to Plugin Manager</a>
      <h1><span class="aip-icon"><i class="fa fa-robot"></i></span> AI Prompts</h1>
      <p class="lead">
        A library of agent prompts that orient AI coding assistants (Claude, GPT, etc.) to UserSpice
        conventions. Point your AI at one of these files when starting a task and it skips the usual
        rediscovery dance.
      </p>
    </div>

    <!-- CSP note -->
    <div class="alert alert-light border py-2 px-3 small text-muted" role="note">
      <i class="fa fa-info-circle mr-1"></i>
      <strong>CSP note:</strong> this page loads syntax-highlighting and markdown libraries from <code>https://cdnjs.cloudflare.com</code>. If your site sends a <em>Content-Security-Policy</em> header, add that origin to <code>script-src</code> (and <code>style-src</code>) or the prompt viewer will not render.
    </div>

    <!-- Prompt list -->
    <div class="card">
      <div class="card-header">Available prompts</div>
      <div class="card-body p-0">
        <?php if (empty($prompts)): ?>
          <p class="p-3 mb-0 text-muted">No prompts found. Did the install copy the prompts/ folder?</p>
        <?php else: ?>
        <table class="table table-sm">
          <thead>
            <tr>
              <th>Name</th>
              <th>Source</th>
              <th>Filesystem path</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($prompts as $p): ?>
            <tr>
              <td><code><?= safeReturn($p['name']) ?></code></td>
              <td>
                <?php if ($p['source'] === 'shipped'): ?>
                  <span class="badge badge-secondary">shipped</span>
                <?php elseif ($p['source'] === 'custom'): ?>
                  <span class="badge badge-success">custom</span>
                <?php else: ?>
                  <span class="badge badge-warning" title="A custom file with this name overrides the shipped version">overridden</span>
                <?php endif; ?>
              </td>
              <td><small><code><?= safeReturn($p['path']) ?></code></small></td>
              <td class="text-right">
                <a class="btn btn-sm btn-primary"
                   href="<?= $us_url_root ?>users/admin.php?view=plugins_config&amp;plugin=ai_prompts&amp;prompt=<?= safeReturn($p['name']) ?>">View</a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>
      </div>
    </div>

    <!-- Selected prompt viewer -->
    <?php if ($selectedPrompt): ?>
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span>Viewing: <code><?= safeReturn($selectedPrompt['name']) ?></code></span>
        <small class="text-muted">
          <?= safeReturn($selectedPrompt['source']) ?> &middot; <?= safeReturn($selectedPrompt['path']) ?>
        </small>
      </div>
      <div class="card-body">
        <!-- marked.js renders into this div on page load. JS at the bottom of the file. -->
        <div id="aip-rendered" class="aip-rendered">
          <p class="text-muted">Loading prompt…</p>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- Usage docs -->
    <div class="card">
      <div class="card-header">How to use these with Claude</div>
      <div class="card-body">
        <p>The prompts are plain markdown files on disk. Hand the absolute path to your AI assistant
          and tell it to read the file. For Claude Code or any other agent with filesystem access:</p>
        <pre><code>Please read <?= safeReturn($shippedDir) ?>00_start_here.md.php
and follow its guidance for this UserSpice project.</code></pre>
        <p class="mb-0">
          <code>00_start_here.md.php</code> is the entry point — it indexes the others and routes the
          agent to the right prompt for the task. The <strong>CLAUDE.md</strong> card below can write a
          file in your project root that makes Claude auto-load it.
        </p>
      </div>
    </div>

    <!-- File format & override -->
    <div class="card">
      <div class="card-header">Adding your own prompts (and overriding shipped ones)</div>
      <div class="card-body">
        <p>Drop a file into the <code>custom_prompts/</code> folder:</p>
        <pre><code><?= safeReturn($customDir) ?>{your_prompt_name}.md.php</code></pre>
        <ul>
          <li>If the name matches a shipped prompt, your version wins (the row above shows
            <span class="badge badge-warning">overridden</span>).</li>
          <li>If the name is new, it shows up as a brand-new prompt (<span class="badge badge-success">custom</span>).</li>
          <li>The shipped <code>prompts/</code> folder gets updated when this plugin updates;
            <code>custom_prompts/</code> never does.</li>
        </ul>

        <h5 class="mt-4">Required file format</h5>
        <p>Every prompt file <strong>must</strong> start with a PHP wrapper. This is what stops
          visitors from reading the file by URL — without it, your prompts would be served as plain
          text by the web server.</p>
<pre><code class="language-php">&lt;?php /* UserSpice AI Prompt — content below this PHP wrapper. */ __halt_compiler(); ?&gt;
# Title of your prompt

Your markdown content here. The PHP wrapper above gets stripped automatically
when the prompt is read by `aiPromptRead()`, so the AI sees clean markdown.</code></pre>
        <p class="mb-0">The folder is also protected by <code>.htaccess</code> and an <code>index.php</code>
          403 page as defense in depth, but the PHP wrapper is the protection that works on every web
          server (Apache, nginx, IIS).</p>
      </div>
    </div>

    <!-- CLAUDE.md writer -->
    <div class="card">
      <div class="card-header aip-header-accent">CLAUDE.md for your project root</div>
      <div class="card-body">

        <?php if ($writeMessage): ?>
          <div class="alert alert-<?= $writeStatus === 'success' ? 'success' : 'danger' ?>">
            <?= safeReturn($writeMessage) ?>
          </div>
        <?php endif; ?>

        <p>Claude Code auto-loads <code>CLAUDE.md</code> at the start of every conversation in a
          project. Drop one at your project root so future sessions know to consult these prompts
          without you having to point at them manually.</p>

        <p>
          <strong>Target:</strong> <code><?= safeReturn($claudeMdPath) ?></code><br>
          <strong>Status:</strong>
          <?php if ($claudeMdExists): ?>
            <span class="badge badge-warning">file exists</span>
            <small class="text-muted">last modified <?= safeReturn($claudeMdMtime) ?></small>
          <?php else: ?>
            <span class="badge badge-secondary">no file yet</span>
          <?php endif; ?>
        </p>

        <p class="mb-1"><strong>Content that will be written:</strong></p>
<pre><code class="language-markdown"><?= safeReturn($claudeMdContent) ?></code></pre>

        <form method="post" class="mt-3">
          <input type="hidden" name="csrf" value="<?= Token::generate(); ?>">
          <input type="hidden" name="action" value="write_claudemd">
          <?php if ($claudeMdExists): ?>
            <div class="form-check mb-2">
              <input class="form-check-input" type="checkbox" name="overwrite" value="1" id="overwrite_claudemd">
              <label class="form-check-label" for="overwrite_claudemd">
                Overwrite existing file
              </label>
            </div>
          <?php endif; ?>
          <button type="submit" class="btn btn-primary">
            <?= $claudeMdExists ? 'Replace CLAUDE.md' : 'Write CLAUDE.md to project root' ?>
          </button>
        </form>

        <p class="mt-3 mb-0 text-muted small">
          This is the only file the plugin writes outside its own folder. If you have an existing
          CLAUDE.md you want to keep, copy the content above into your existing file by hand instead
          of using the overwrite button.
        </p>
      </div>
    </div>

    <!-- Plugin meta -->
    <div class="card">
      <div class="card-header">Plugin info</div>
      <div class="card-body">
        <ul class="aip-meta-list">
          <li>Plugin folder: <code><?= safeReturn($abs_us_root . $us_url_root . 'usersc/plugins/ai_prompts/') ?></code></li>
          <li>Shipped prompts: <code><?= safeReturn($shippedDir) ?></code> &mdash; updated by this plugin</li>
          <li>Custom prompts: <code><?= safeReturn($customDir) ?></code> &mdash; survives plugin updates</li>
          <li>Helper functions (loaded site-wide when active): <code>aiPromptList()</code>, <code>aiPromptPath($name)</code>, <code>aiPromptRead($name)</code></li>
        </ul>
      </div>
    </div>

  </div>

  <script>
    (function () {
      // Render the selected prompt's markdown body, if any.
      <?php if ($selectedBody !== null && $selectedBody !== false): ?>
      var promptMarkdown = <?= safeJsonEncodeForJs($selectedBody) ?>;
      var target = document.getElementById('aip-rendered');
      if (target && window.marked) {
        target.innerHTML = window.marked.parse(promptMarkdown);
      }
      <?php endif; ?>

      // Apply syntax highlighting to every <pre><code> on the page (static AND just-rendered).
      if (window.hljs) {
        document.querySelectorAll('.aip pre code').forEach(function (el) {
          window.hljs.highlightElement(el);
        });
      }
    })();
  </script>

  <!-- Do not close the content mt-3 div in this file -->
