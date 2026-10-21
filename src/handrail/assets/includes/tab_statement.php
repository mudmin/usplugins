<?php
if (count(get_included_files()) === 1) { exit('Direct access not permitted.'); }
// Handrail > Statement: the accessibility statement's settings and its address.
$hrPublic = handrailPublicPath();
$hrPathValue = trim((string) $hrS['public_path'], '/');
?>
<div class="card mb-3"><div class="card-body">
  <h2 class="h4">Your statement page</h2>
  <p>Handrail builds the statement from the settings below every time it's shown, so saving a change updates the page. The Accessibility button's links also point here, so it works for visitors without JavaScript.</p>
  <p class="mb-0"><a href="<?= safeReturn(handrailStatementUrl()) ?>">View the statement page</a></p>
</div></div>

<form method="post" action="<?= safeReturn($hrSelf . '&tab=statement') ?>">
  <?php tokenHere(); ?>
  <input type="hidden" name="hr_action" value="save_statement">
  <div class="card mb-3"><div class="card-body">
    <h2 class="h4">What it says</h2>
    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label" for="hr-org">Organization name</label>
        <input type="text" class="form-control" id="hr-org" name="org_name" value="<?= safeReturn($hrS['org_name']) ?>" placeholder="<?= safeReturn((string) ($settings->site_name ?? '')) ?>" maxlength="120" aria-describedby="hr-org-d">
        <div class="form-text" id="hr-org-d">Leave blank to use the site name.</div>
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label" for="hr-standard">Target standard</label>
        <select class="form-select" id="hr-standard" name="standard" aria-describedby="hr-standard-d">
          <?php foreach (handrailStandards() as $hrK => $hrL) { ?>
            <option value="<?= safeReturn($hrK) ?>"<?= $hrS['standard'] === $hrK ? ' selected' : '' ?>><?= safeReturn($hrL) ?></option>
          <?php } ?>
        </select>
        <div class="form-text" id="hr-standard-d">WCAG 2.1 AA is what the ADA Title II rule for US public bodies requires.</div>
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label" for="hr-conf">How well the site meets it</label>
        <select class="form-select" id="hr-conf" name="conformance" aria-describedby="hr-conf-d">
          <?php foreach (handrailConformanceOptions() as $hrK => $hrL) { ?>
            <option value="<?= safeReturn($hrK) ?>"<?= $hrS['conformance'] === $hrK ? ' selected' : '' ?>><?= safeReturn($hrL) ?></option>
          <?php } ?>
        </select>
        <div class="form-text" id="hr-conf-d">The statement is a public claim. Only choose "Fully conforms" after a real review, not just an automated check.</div>
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label" for="hr-days">Reply within (business days)</label>
        <input type="number" class="form-control" id="hr-days" name="response_days" min="0" max="60" value="<?= (int) $hrS['response_days'] ?>" aria-describedby="hr-days-d">
        <div class="form-text" id="hr-days-d">0 leaves the promise out.</div>
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label" for="hr-cemail">Contact email</label>
        <input type="email" class="form-control" id="hr-cemail" name="contact_email" value="<?= safeReturn($hrS['contact_email']) ?>" autocomplete="email">
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label" for="hr-phone">Contact phone</label>
        <input type="tel" class="form-control" id="hr-phone" name="contact_phone" value="<?= safeReturn($hrS['contact_phone']) ?>" maxlength="40" autocomplete="tel">
      </div>
      <div class="col-12 mb-3">
        <label class="form-label" for="hr-limits">Known problems</label>
        <textarea class="form-control" id="hr-limits" name="limitations" rows="5" maxlength="5000" aria-describedby="hr-limits-d"><?= safeReturn($hrS['limitations']) ?></textarea>
        <div class="form-text" id="hr-limits-d">Plain text. Leave a blank line between paragraphs. For example: "Some older PDFs are not tagged. Ask us for an accessible copy."</div>
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label" for="hr-reviewed">Last reviewed</label>
        <input type="date" class="form-control" id="hr-reviewed" name="reviewed" value="<?= safeReturn($hrS['reviewed']) ?>" aria-describedby="hr-reviewed-d">
        <div class="form-text" id="hr-reviewed-d">Set this when someone actually reviews the site. Handrail reminds you after a year.</div>
      </div>
    </div>
  </div></div>
  <button type="submit" class="btn btn-primary mb-4">Save statement</button>
</form>

<div class="card mb-3"><div class="card-body">
  <h2 class="h4">Address</h2>
  <p>The statement is at <code><?= safeReturn(handrailStatementUrl()) ?></code>. You can give it a shorter address instead, such as <code><?= safeReturn($us_url_root) ?>accessibility/</code>. Handrail writes a two-line <code>index.php</code> in that folder.</p>
  <form method="post" action="<?= safeReturn($hrSelf . '&tab=statement') ?>" aria-label="Statement address">
    <?php tokenHere(); ?>
    <input type="hidden" name="hr_action" value="public_save">
    <label class="form-label" for="hr-public">Folder</label>
    <div class="input-group mb-2 w-auto">
      <span class="input-group-text"><?= safeReturn($us_url_root) ?></span>
      <input type="text" class="form-control" id="hr-public" name="public_path" value="<?= safeReturn($hrPathValue !== '' ? $hrPathValue : 'accessibility') ?>" maxlength="100" aria-describedby="hr-public-d">
      <button class="btn btn-primary" type="submit">Use this address</button>
    </div>
    <div class="form-text mb-2" id="hr-public-d">Lowercase letters, numbers, dashes and underscores.</div>
  </form>
  <?php if ($hrPublic !== null) { ?>
    <form method="post" action="<?= safeReturn($hrSelf . '&tab=statement') ?>" aria-label="Remove the statement address">
      <?php tokenHere(); ?>
      <input type="hidden" name="hr_action" value="public_remove">
      <button class="btn btn-link px-0" type="submit">Go back to the plugin's own address</button>
    </form>
  <?php } elseif ($hrPathValue !== '') { ?>
    <p class="mb-1">If the web server can't write there, create <code><?= safeReturn($hrPathValue) ?>/index.php</code> yourself with:</p>
    <pre class="bg-body-tertiary p-2"><code><?= safeReturn(handrailPublicWrapper($hrPathValue)) ?></code></pre>
  <?php } ?>
</div></div>

<div class="card mb-3"><div class="card-body">
  <h2 class="h4">Show it on your own page</h2>
  <p class="mb-1">Print the statement inside any page of your own:</p>
  <pre class="bg-body-tertiary p-2 mb-0"><code>&lt;?php if (function_exists('handrailStatement')) { echo handrailStatement(); } ?&gt;</code></pre>
</div></div>
