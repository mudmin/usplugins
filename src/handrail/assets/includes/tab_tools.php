<?php
if (count(get_included_files()) === 1) { exit('Direct access not permitted.'); }
// Handrail > Tools: which tools and quick settings visitors see, and the site's own quick settings.
$hrAll = handrailTools();
$hrOn = (array) $hrS['tools'];
$hrRows = array_values((array) $hrS['custom_presets']);
$hrBlanks = max(1, min(2, HANDRAIL_MAX_PRESETS - count($hrRows)));
for ($hrI = 0; $hrI < $hrBlanks; $hrI++) {
    $hrRows[] = ['label' => '', 'desc' => '', 'tools' => []];
}
$hrRows = array_slice($hrRows, 0, HANDRAIL_MAX_PRESETS);
?>
<form method="post" action="<?= safeReturn($hrSelf . '&tab=tools') ?>">
  <?php tokenHere(); ?>
  <input type="hidden" name="hr_action" value="save_tools">

  <div class="card mb-3"><div class="card-body">
    <h2 class="h4">Tools</h2>
    <p>Turn off anything your visitors don't need. Fewer tools make a shorter panel.</p>
    <div class="row">
      <?php foreach (handrailGroups() as $hrGid => $hrGlabel) { ?>
        <fieldset class="col-md-6 col-xl-3 mb-3">
          <legend class="fs-6 fw-semibold"><?= safeReturn($hrGlabel) ?></legend>
          <?php foreach ($hrAll as $hrId => $hrT) { if ($hrT['group'] !== $hrGid) { continue; } ?>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="tools[]" id="hr-tool-<?= safeReturn($hrId) ?>" value="<?= safeReturn($hrId) ?>" aria-describedby="hr-tool-<?= safeReturn($hrId) ?>-d"<?= in_array($hrId, $hrOn, true) ? ' checked' : '' ?>>
              <label class="form-check-label" for="hr-tool-<?= safeReturn($hrId) ?>"><?= safeReturn($hrT['label']) ?></label>
              <div class="form-text mt-0" id="hr-tool-<?= safeReturn($hrId) ?>-d"><?= safeReturn($hrT['desc']) ?></div>
            </div>
          <?php } ?>
        </fieldset>
      <?php } ?>
    </div>
  </div></div>

  <div class="card mb-3"><div class="card-body">
    <h2 class="h4">Quick settings</h2>
    <p>One click turns on a few tools together. A quick setting only uses the tools you left on.</p>
    <fieldset>
      <legend class="visually-hidden">Quick settings to show</legend>
      <?php foreach (handrailPresets() as $hrId => $hrP) { $hrId = (string) $hrId; ?>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="presets[]" id="hr-preset-<?= safeReturn($hrId) ?>" value="<?= safeReturn($hrId) ?>" aria-describedby="hr-preset-<?= safeReturn($hrId) ?>-d"<?= in_array($hrId, (array) $hrS['presets'], true) ? ' checked' : '' ?>>
          <label class="form-check-label" for="hr-preset-<?= safeReturn($hrId) ?>"><?= safeReturn($hrP['label']) ?></label>
          <div class="form-text mt-0" id="hr-preset-<?= safeReturn($hrId) ?>-d"><?= safeReturn($hrP['desc']) ?></div>
        </div>
      <?php } ?>
    </fieldset>
  </div></div>

  <div class="card mb-3"><div class="card-body">
    <h2 class="h4">Your own quick settings</h2>
    <p>Make quick settings for the people who use your site, for example "Large print" for a senior center or "Classroom" for a school. Up to <?= (int) HANDRAIL_MAX_PRESETS ?>. Leave the name blank to remove one.</p>
    <?php foreach ($hrRows as $hrI => $hrP) { ?>
      <fieldset class="border rounded p-3 mb-3">
        <legend class="float-none w-auto px-2 fs-6 fw-semibold mb-0">Quick setting <?= (int) $hrI + 1 ?></legend>
        <div class="row">
          <div class="col-md-4 mb-2">
            <label class="form-label" for="hr-cp-<?= (int) $hrI ?>-label">Name</label>
            <input type="text" class="form-control" id="hr-cp-<?= (int) $hrI ?>-label" name="custom[<?= (int) $hrI ?>][label]" value="<?= safeReturn($hrP['label']) ?>" maxlength="40">
          </div>
          <div class="col-md-8 mb-2">
            <label class="form-label" for="hr-cp-<?= (int) $hrI ?>-desc">Short description</label>
            <input type="text" class="form-control" id="hr-cp-<?= (int) $hrI ?>-desc" name="custom[<?= (int) $hrI ?>][desc]" value="<?= safeReturn($hrP['desc']) ?>" maxlength="80">
          </div>
        </div>
        <div class="row">
          <?php foreach ($hrAll as $hrId => $hrT) { if (!in_array($hrId, $hrOn, true)) { continue; } $hrFid = 'hr-cp-' . (int) $hrI . '-' . $hrId; ?>
            <div class="col-6 col-md-4 col-xl-3 mb-2">
              <label class="form-label small mb-1" for="<?= safeReturn($hrFid) ?>"><?= safeReturn($hrT['label']) ?></label>
              <select class="form-select form-select-sm" id="<?= safeReturn($hrFid) ?>" name="custom[<?= (int) $hrI ?>][tools][<?= safeReturn($hrId) ?>]">
                <option value="0">Leave as is</option>
                <?php foreach ($hrT['steps'] as $hrN => $hrStep) { ?>
                  <option value="<?= (int) $hrN + 1 ?>"<?= (int) ($hrP['tools'][$hrId] ?? 0) === $hrN + 1 ? ' selected' : '' ?>><?= safeReturn($hrStep) ?></option>
                <?php } ?>
              </select>
            </div>
          <?php } ?>
        </div>
      </fieldset>
    <?php } ?>
  </div></div>

  <button type="submit" class="btn btn-primary mb-4">Save tools and quick settings</button>
</form>
