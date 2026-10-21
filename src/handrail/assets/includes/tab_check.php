<?php
if (count(get_included_files()) === 1) { exit('Direct access not permitted.'); }
// Handrail > Checker: who sees the Check tab.
$hrPerms = $db->query('SELECT id, name FROM permissions ORDER BY id')->results();
$hrChosen = array_map('intval', (array) $hrS['check_perms']);
?>
<form method="post" action="<?= safeReturn($hrSelf . '&tab=check') ?>">
  <?php tokenHere(); ?>
  <input type="hidden" name="hr_action" value="save_check">
  <div class="card mb-3"><div class="card-body">
    <h2 class="h4">Check this page</h2>
    <p>The Check tab runs <a href="https://github.com/dequelabs/axe-core">axe-core</a> on the page you're looking at and groups each problem by who can fix it: this page's own code, your template, a plugin, your custom code in <code>usersc/</code>, or UserSpice itself. Visitors never see it, and its scripts load only when someone opens it.</p>
    <div class="form-check mb-3">
      <input class="form-check-input" type="checkbox" name="check" id="hr-check" value="1"<?= $hrS['check'] ? ' checked' : '' ?>>
      <label class="form-check-label" for="hr-check">Show the Check tab</label>
    </div>
    <fieldset class="mb-3">
      <legend class="fs-6 fw-semibold">Who sees it</legend>
      <?php foreach ($hrPerms as $hrP) { ?>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="check_perms[]" id="hr-perm-<?= (int) $hrP->id ?>" value="<?= (int) $hrP->id ?>"<?= in_array((int) $hrP->id, $hrChosen, true) ? ' checked' : '' ?>>
          <label class="form-check-label" for="hr-perm-<?= (int) $hrP->id ?>"><?= safeReturn($hrP->name) ?></label>
        </div>
      <?php } ?>
      <p class="form-text">Administrators also see the technical details: selectors, contrast numbers, links to each rule, and a Markdown export.</p>
    </fieldset>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" name="check_wcag22" id="hr-wcag22" value="1"<?= $hrS['check_wcag22'] ? ' checked' : '' ?>>
      <label class="form-check-label" for="hr-wcag22">Include the WCAG 2.2 checks as well as WCAG 2.1 AA</label>
    </div>
  </div></div>
  <button type="submit" class="btn btn-primary mb-4">Save checker settings</button>
</form>
<div class="card mb-3"><div class="card-body">
  <h2 class="h4">Open it from anywhere</h2>
  <p class="mb-1">Any link to this address opens the panel straight to the Check tab (for people who can see it):</p>
  <input type="text" readonly class="form-control font-monospace" value="<?= safeReturn(handrailTriggerUrl('check')) ?>" aria-label="Link address that opens the Check tab">
</div></div>
