<?php
if (count(get_included_files()) === 1) { exit('Direct access not permitted.'); }
// Handrail > Look and placement.
$hrSel = function ($name, $choices, $value, $id) {
    echo '<select class="form-select" id="' . safeReturn($id) . '" name="' . safeReturn($name) . '">';
    foreach ($choices as $k => $label) {
        echo '<option value="' . safeReturn($k) . '"' . ((string) $value === (string) $k ? ' selected' : '') . '>' . safeReturn($label) . '</option>';
    }
    echo '</select>';
};
$hrAccent = $hrS['brand_accent'] !== '' ? $hrS['brand_accent'] : '#0b6e69';
?>
<form method="post" action="<?= safeReturn($hrSelf . '&tab=look') ?>">
  <?php tokenHere(); ?>
  <input type="hidden" name="hr_action" value="save_look">

  <div class="card mb-3"><div class="card-body">
    <h2 class="h4">Panel style</h2>
    <fieldset>
      <legend class="visually-hidden">Panel style</legend>
      <?php foreach (handrailStyles() as $hrId => $hrSt) { ?>
        <div class="form-check mb-2">
          <input class="form-check-input" type="radio" name="style" id="hr-style-<?= safeReturn($hrId) ?>" value="<?= safeReturn($hrId) ?>" aria-describedby="hr-style-<?= safeReturn($hrId) ?>-d"<?= $hrS['style'] === $hrId ? ' checked' : '' ?>>
          <label class="form-check-label fw-semibold" for="hr-style-<?= safeReturn($hrId) ?>"><?= safeReturn($hrSt['label']) ?></label>
          <div class="form-text mt-0" id="hr-style-<?= safeReturn($hrId) ?>-d"><?= safeReturn($hrSt['desc']) ?></div>
        </div>
      <?php } ?>
    </fieldset>
  </div></div>

  <style nonce="<?= safeReturn($userspice_nonce ?? '') ?>">
  .hr-icon-swatch { display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 50%; background: <?= safeReturn($hrAccent) ?>; color: <?= safeReturn(handrailTextOn($hrAccent)) ?>; }
  </style>
  <div class="card mb-3"><div class="card-body">
    <h2 class="h4">Icon</h2>
    <fieldset aria-describedby="hr-icon-d">
      <legend class="visually-hidden">Icon</legend>
      <div class="d-flex flex-wrap gap-4">
        <?php foreach (handrailIcons() as $hrId => $hrLabel) { ?>
          <div class="form-check d-flex align-items-center gap-2">
            <input class="form-check-input mt-0" type="radio" name="icon" id="hr-icon-<?= safeReturn($hrId) ?>" value="<?= safeReturn($hrId) ?>"<?= handrailIcon() === $hrId ? ' checked' : '' ?>>
            <label class="form-check-label d-inline-flex align-items-center gap-2" for="hr-icon-<?= safeReturn($hrId) ?>">
              <span class="hr-icon-swatch"><?= handrailIconSvg(22, $hrId) ?></span>
              <?= safeReturn($hrLabel) ?>
            </label>
          </div>
        <?php } ?>
      </div>
    </fieldset>
    <div class="form-text" id="hr-icon-d">On the floating button, at the top of the panel, and on the UltraMenu snippet.</div>
  </div></div>

  <div class="card mb-3"><div class="card-body">
    <h2 class="h4">Floating button</h2>
    <div class="form-check mb-3">
      <input class="form-check-input" type="checkbox" name="floating" id="hr-floating" value="1"<?= $hrS['floating'] ? ' checked' : '' ?>>
      <label class="form-check-label" for="hr-floating">Show a floating Accessibility button on every page</label>
    </div>
    <div class="row">
      <div class="col-md-4 mb-3">
        <label class="form-label" for="hr-label">Looks like</label>
        <?php $hrSel('label', ['icon' => 'Round button with the icon', 'text' => 'Pill with the icon and the word', 'tab' => 'Tab on the side edge of the screen', 'handle' => 'Tab at the bottom of the screen'], $hrS['label'], 'hr-label'); ?>
      </div>
      <div class="col-md-4 mb-3">
        <label class="form-label" for="hr-corner">Corner</label>
        <?php $hrSel('corner', ['br' => 'Bottom right', 'bl' => 'Bottom left', 'tr' => 'Top right', 'tl' => 'Top left'], $hrS['corner'], 'hr-corner'); ?>
      </div>
      <div class="col-md-4 mb-3">
        <label class="form-label" for="hr-size">Size</label>
        <?php $hrSel('size', ['small' => 'Small (44px)', 'medium' => 'Medium (52px)', 'large' => 'Large (60px)'], $hrS['size'], 'hr-size'); ?>
      </div>
    </div>
    <p class="form-text mt-0">The side tab uses the side of the corner you chose. The bottom tab sits in the middle and ignores the corner and distances.</p>
    <fieldset class="mb-3">
      <legend class="form-label fs-6">Distance from the edges, in pixels</legend>
      <div class="row">
        <div class="col-6 col-md-3">
          <label class="form-label" for="hr-ox">Side</label>
          <input class="form-control" type="number" min="0" max="400" id="hr-ox" name="offset_x" value="<?= (int) $hrS['offset_x'] ?>">
        </div>
        <div class="col-6 col-md-3">
          <label class="form-label" for="hr-oy">Top or bottom</label>
          <input class="form-control" type="number" min="0" max="400" id="hr-oy" name="offset_y" value="<?= (int) $hrS['offset_y'] ?>">
        </div>
      </div>
      <p class="form-text">Move it if it covers a chat widget or cookie banner.</p>
    </fieldset>
    <div class="form-check mb-2">
      <input class="form-check-input" type="checkbox" name="hide_on_mobile" id="hr-mobile" value="1"<?= $hrS['hide_on_mobile'] ? ' checked' : '' ?>>
      <label class="form-check-label" for="hr-mobile">Hide the floating button on phones (use a menu or footer link there instead)</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" name="on_dashboard" id="hr-dash" value="1"<?= $hrS['on_dashboard'] ? ' checked' : '' ?>>
      <label class="form-check-label" for="hr-dash">Also show it on the admin dashboard (users/admin.php)</label>
    </div>
  </div></div>

  <div class="card mb-3"><div class="card-body">
    <h2 class="h4">Branding</h2>
    <p>Match the button and panel to your site. Handrail picks black or white text for your accent color so it always reads clearly.</p>
    <div class="row">
      <div class="col-md-4 mb-3">
        <label class="form-label" for="hr-accent">Accent color</label>
        <div class="input-group">
          <input type="color" class="form-control form-control-color" id="hr-accent-picker" value="<?= safeReturn($hrAccent) ?>" aria-label="Pick the accent color">
          <input type="text" class="form-control font-monospace" id="hr-accent" name="brand_accent" value="<?= safeReturn($hrS['brand_accent']) ?>" placeholder="#0b6e69" maxlength="7" aria-describedby="hr-accent-d">
        </div>
        <div class="form-text" id="hr-accent-d">Leave blank for the default teal. It must reach 4.5:1 against a light gray.</div>
      </div>
      <div class="col-md-4 mb-3">
        <label class="form-label" for="hr-title">Panel title</label>
        <input type="text" class="form-control" id="hr-title" name="brand_title" value="<?= safeReturn($hrS['brand_title']) ?>" placeholder="Accessibility" maxlength="60">
      </div>
      <div class="col-md-4 mb-3">
        <label class="form-label" for="hr-button">Button text</label>
        <input type="text" class="form-control" id="hr-button" name="brand_button" value="<?= safeReturn($hrS['brand_button']) ?>" placeholder="Accessibility" maxlength="40" aria-describedby="hr-button-d">
        <div class="form-text" id="hr-button-d">Read by screen readers, and shown when the button shows text.</div>
      </div>
    </div>
  </div></div>

  <button type="submit" class="btn btn-primary mb-4">Save look and placement</button>
</form>

<div class="card mb-3"><div class="card-body">
  <h2 class="h4">Other places to put it</h2>
  <p>Use any of these as well as, or instead of, the floating button. They are ordinary links, so without JavaScript they still go to your accessibility statement.</p>
  <ul>
    <li class="mb-2"><strong>Menu:</strong> in UltraMenu, add a menu item, set its type to Snippet and pick <code>usersc/plugins/handrail/menu_hooks/handrail.php</code>.</li>
    <li class="mb-2"><strong>Template footer or any page:</strong>
      <pre class="bg-body-tertiary p-2 mt-1 mb-0"><code>&lt;?php if (function_exists('handrailLink')) { handrailLink('Accessibility'); } ?&gt;</code></pre>
      <span class="small">Add a CSS class as the second argument, or <code>'report'</code> as the third to open the report form.</span></li>
    <li><strong>Anywhere else:</strong> any link to this address opens the panel:
      <input type="text" readonly class="form-control font-monospace mt-1" value="<?= safeReturn(handrailTriggerUrl()) ?>" aria-label="Link address that opens the panel"></li>
  </ul>
</div></div>
<script nonce="<?= safeReturn($userspice_nonce ?? '') ?>">
(function () {
  var pick = document.getElementById('hr-accent-picker'), text = document.getElementById('hr-accent');
  if (!pick || !text) return;
  pick.addEventListener('input', function () { text.value = pick.value; });
  text.addEventListener('input', function () { if (/^#[0-9a-f]{6}$/i.test(text.value)) pick.value = text.value; });
})();
</script>
