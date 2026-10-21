<?php
if (count(get_included_files()) === 1) { exit('Direct access not permitted.'); }
// Handrail > Credit and support.
?>
<div class="card mb-3"><div class="card-body">
  <h2 class="h4">Handrail is free</h2>
  <p>Every Handrail feature is free on UserSpice, including the ones that are paid on WordPress: read aloud, the reading guide and mask, the dyslexia font, color filters, large cursors, your own quick settings, branding and all four panel styles.</p>
  <p>In return, the panel shows a small "Accessibility tools by Handrail" link at the bottom. If Handrail is useful to you, please consider a donation. Once you have donated, you're welcome to turn the link off. It works on trust: there's no key to enter.</p>
  <p><a class="btn btn-success" href="<?= safeReturn(HANDRAIL_DONATE) ?>">Donate to Handrail</a></p>
</div></div>
<form method="post" action="<?= safeReturn($hrSelf . '&tab=support') ?>">
  <?php tokenHere(); ?>
  <input type="hidden" name="hr_action" value="save_support">
  <div class="card mb-3"><div class="card-body">
    <h2 class="h4">Credit link</h2>
    <div class="form-check mb-3">
      <input class="form-check-input" type="checkbox" name="credit" id="hr-credit" value="1"<?= $hrS['credit'] ? ' checked' : '' ?>>
      <label class="form-check-label" for="hr-credit">Show "Accessibility tools by Handrail" at the bottom of the panel</label>
    </div>
    <button type="submit" class="btn btn-primary">Save</button>
  </div></div>
</form>
