<?php
if (count(get_included_files()) === 1) { exit('Direct access not permitted.'); }
// Handrail > Reports: the inbox, one report, and the report settings.
$hrCats = handrailReportCategories();
$hrReportId = (int) Input::get('report');
$hrReport = $hrReportId ? handrailGetReport($hrReportId) : null;
$hrUserName = function ($id) use ($db) {
    if (!$id) {
        return 'A visitor';
    }
    $q = $db->query('SELECT username FROM users WHERE id = ?', [(int) $id]);
    return $q->count() ? $q->first()->username : 'User #' . (int) $id;
};

if ($hrReport) {
    $hrTools = handrailTools();
    ?>
  <p><a href="<?= safeReturn($hrSelf . '&tab=reports') ?>">&larr; All reports</a></p>
  <div class="card mb-3"><div class="card-body">
    <h2 class="h4"><?= safeReturn($hrReport['page_title'] !== '' ? $hrReport['page_title'] : $hrReport['page_url']) ?></h2>
    <p class="mb-2">
      <?php if ($hrReport['status'] === 'resolved') { ?>
        <span class="badge text-bg-success">Resolved</span>
        <span class="small">by <?= safeReturn($hrUserName($hrReport['resolved_by'])) ?> on <?= safeReturn($hrReport['resolved_on']) ?></span>
      <?php } else { ?>
        <span class="badge text-bg-warning">Open</span>
      <?php } ?>
    </p>
    <table class="table table-sm">
      <tbody>
        <tr><th scope="row" class="w-25">Page</th><td><a href="<?= safeReturn($hrReport['page_url']) ?>"><?= safeReturn($hrReport['page_url']) ?></a></td></tr>
        <tr><th scope="row">Sent</th><td><?= safeReturn($hrReport['created']) ?> by <?= safeReturn($hrUserName($hrReport['user_id'])) ?></td></tr>
        <?php if ($hrReport['categories']) { ?>
        <tr><th scope="row">Problems</th><td><ul class="mb-0 ps-3"><?php foreach ($hrReport['categories'] as $hrC) { ?><li><?= safeReturn($hrCats[$hrC] ?? $hrC) ?></li><?php } ?></ul></td></tr>
        <?php } ?>
        <tr><th scope="row">Comments</th><td><?= $hrReport['comments'] !== '' ? nl2br(safeReturn($hrReport['comments'])) : '<span class="text-body-secondary">None</span>' ?></td></tr>
        <tr><th scope="row">Reply to</th><td><?= $hrReport['contact'] !== '' ? '<a href="mailto:' . safeReturn($hrReport['contact']) . '">' . safeReturn($hrReport['contact']) . '</a>' : '<span class="text-body-secondary">No email given</span>' ?></td></tr>
        <tr><th scope="row">Adjustments on</th><td>
          <?php if ($hrReport['adjustments']) { ?>
            <?= safeReturn(implode(', ', array_map(function ($id, $step) use ($hrTools) {
                return isset($hrTools[$id]) ? $hrTools[$id]['label'] . ': ' . ($hrTools[$id]['steps'][$step - 1] ?? $step) : $id;
            }, array_keys($hrReport['adjustments']), $hrReport['adjustments']))) ?>
          <?php } else { ?><span class="text-body-secondary">None</span><?php } ?>
        </td></tr>
        <tr><th scope="row">Screen</th><td><?= safeReturn(trim($hrReport['viewport'] . ($hrReport['zoom'] ? ', ' . $hrReport['zoom'] . '% pixel ratio' : '') . ($hrReport['lang'] !== '' ? ', page language ' . $hrReport['lang'] : ''), ', ')) ?></td></tr>
        <tr><th scope="row">Browser</th><td class="small"><?= safeReturn($hrReport['ua']) ?></td></tr>
        <?php if ($hrReport['scan']) { ?>
        <tr><th scope="row">Check results sent</th><td class="small"><?= safeReturn(implode(', ', array_map(function ($rule, $n) {
            return $rule . ' (' . (int) $n . ')';
        }, array_keys((array) $hrReport['scan']['violations']), (array) $hrReport['scan']['violations']))) ?: 'No problems' ?>; <?= (int) $hrReport['scan']['incomplete'] ?> need a person</td></tr>
        <?php } ?>
      </tbody>
    </table>
    <form method="post" action="<?= safeReturn($hrSelf . '&tab=reports&report=' . $hrReportId) ?>">
      <?php tokenHere(); ?>
      <input type="hidden" name="hr_action" value="report_update">
      <input type="hidden" name="report" value="<?= (int) $hrReportId ?>">
      <div class="mb-3">
        <label class="form-label" for="hr-notes">Notes (only admins see these)</label>
        <textarea class="form-control" id="hr-notes" name="notes" rows="4" maxlength="5000"><?= safeReturn($hrReport['notes']) ?></textarea>
      </div>
      <?php if ($hrReport['status'] === 'resolved') { ?>
        <button class="btn btn-primary" type="submit" name="status" value="resolved">Save notes</button>
        <button class="btn btn-link" type="submit" name="status" value="open">Reopen</button>
      <?php } else { ?>
        <button class="btn btn-primary" type="submit" name="status" value="resolved">Mark resolved</button>
        <button class="btn btn-link" type="submit" name="status" value="open">Save notes</button>
      <?php } ?>
    </form>
    <form method="post" action="<?= safeReturn($hrSelf . '&tab=reports') ?>" class="mt-3" data-hr-confirm="Delete this report for good?">
      <?php tokenHere(); ?>
      <input type="hidden" name="hr_action" value="report_delete">
      <input type="hidden" name="report" value="<?= (int) $hrReportId ?>">
      <button class="btn btn-outline-danger btn-sm" type="submit">Delete report</button>
    </form>
  </div></div>
<script nonce="<?= safeReturn($userspice_nonce ?? '') ?>">
document.querySelectorAll('form[data-hr-confirm]').forEach(function (f) {
  f.addEventListener('submit', function (e) { if (!confirm(f.getAttribute('data-hr-confirm'))) e.preventDefault(); });
});
</script>
    <?php
    return;
}

$hrFilter = in_array(Input::get('status'), ['open', 'resolved', 'all'], true) ? Input::get('status') : 'open';
$hrSql = 'SELECT id, status, created, user_id, page_url, page_title, categories, comments, contact FROM plg_handrail_reports';
$hrArgs = [];
if ($hrFilter !== 'all') {
    $hrSql .= ' WHERE status = ?';
    $hrArgs[] = $hrFilter;
}
$hrList = $db->query($hrSql . ' ORDER BY created DESC, id DESC LIMIT 200', $hrArgs)->results();
$hrDetected = handrailCaptchaDetect();
$hrProvider = $hrS['captcha_provider'];
?>
<?php if ($hrS['reports'] === 'off') { ?>
  <div class="alert alert-info">Reports are off, so visitors don't see the Report tab. Turn them on under Report settings below.</div>
<?php } ?>
<?php if (handrailCaptchaMissing()) { ?>
  <div class="alert alert-warning">The captcha keys you picked are no longer available (was a plugin turned off?). Reports still work with the built-in spam checks. Pick new keys below.</div>
<?php } ?>
<?php if (handrailStatementStale()) { ?>
  <div class="alert alert-warning">Your accessibility statement was last reviewed more than a year ago. Review it under <a href="<?= safeReturn($hrSelf . '&tab=statement') ?>">Statement</a>.</div>
<?php } ?>

<div class="card mb-3"><div class="card-body">
  <h2 class="h4">Reports</h2>
  <nav aria-label="Filter reports" class="mb-2">
    <ul class="nav nav-pills">
    <?php foreach (['open' => 'Open', 'resolved' => 'Resolved', 'all' => 'All'] as $hrK => $hrL) { ?>
      <li class="nav-item"><a class="nav-link py-1<?= $hrFilter === $hrK ? ' active' : '' ?>"<?= $hrFilter === $hrK ? ' aria-current="page"' : '' ?> href="<?= safeReturn($hrSelf . '&tab=reports&status=' . $hrK) ?>"><?= safeReturn($hrL) ?></a></li>
    <?php } ?>
    </ul>
  </nav>
  <?php if (!$hrList) { ?>
    <p class="mb-0">No <?= $hrFilter === 'all' ? '' : safeReturn($hrFilter) . ' ' ?>reports.</p>
  <?php } else { ?>
    <div class="table-responsive">
      <table class="table table-striped align-middle">
        <thead><tr><th scope="col">Page</th><th scope="col">Problems</th><th scope="col">Comments</th><th scope="col">From</th><th scope="col">Sent</th></tr></thead>
        <tbody>
        <?php foreach ($hrList as $hrR) {
            $hrRcats = $hrR->categories === '' ? [] : explode(',', $hrR->categories); ?>
          <tr>
            <td><a href="<?= safeReturn($hrSelf . '&tab=reports&report=' . (int) $hrR->id) ?>"><?= safeReturn($hrR->page_title !== '' ? $hrR->page_title : $hrR->page_url) ?></a>
              <?php if ($hrR->status === 'resolved') { ?><span class="badge text-bg-success">Resolved</span><?php } ?></td>
            <td class="small"><?= safeReturn(implode('; ', array_map(function ($c) use ($hrCats) { return $hrCats[$c] ?? $c; }, $hrRcats))) ?></td>
            <td class="small"><?= safeReturn(mb_strimwidth($hrR->comments, 0, 160, '…')) ?></td>
            <td class="small"><?= safeReturn($hrR->contact !== '' ? $hrR->contact : $hrUserName($hrR->user_id)) ?></td>
            <td class="small text-nowrap"><?= safeReturn(date('M j, Y', strtotime($hrR->created))) ?></td>
          </tr>
        <?php } ?>
        </tbody>
      </table>
    </div>
  <?php } ?>
</div></div>

<form method="post" action="<?= safeReturn($hrSelf . '&tab=reports') ?>">
  <?php tokenHere(); ?>
  <input type="hidden" name="hr_action" value="save_reports">
  <div class="card mb-3"><div class="card-body">
    <h2 class="h4">Report settings</h2>
    <fieldset class="mb-3">
      <legend class="fs-6 fw-semibold">Who can send a report</legend>
      <?php foreach (['off' => 'Nobody (reports are off)', 'logged_in' => 'Logged-in users', 'everyone' => 'Everyone'] as $hrK => $hrL) { ?>
        <div class="form-check">
          <input class="form-check-input" type="radio" name="reports" id="hr-reports-<?= $hrK ?>" value="<?= $hrK ?>"<?= $hrS['reports'] === $hrK ? ' checked' : '' ?>>
          <label class="form-check-label" for="hr-reports-<?= $hrK ?>"><?= safeReturn($hrL) ?></label>
        </div>
      <?php } ?>
    </fieldset>
    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label" for="hr-email">Send new reports to</label>
        <input type="email" class="form-control" id="hr-email" name="report_email" value="<?= safeReturn($hrS['report_email']) ?>" placeholder="<?= safeReturn(handrailNotifyAddress()) ?>" aria-describedby="hr-email-d" autocomplete="email">
        <div class="form-text" id="hr-email-d">Leave blank for the first master account's email.</div>
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label" for="hr-notify">Email</label>
        <select class="form-select" id="hr-notify" name="notify">
          <option value="each"<?= $hrS['notify'] === 'each' ? ' selected' : '' ?>>Each new report (up to 20 an hour)</option>
          <option value="none"<?= $hrS['notify'] === 'none' ? ' selected' : '' ?>>No email; I'll check this page</option>
        </select>
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label" for="hr-retention">Delete resolved reports after (days)</label>
        <input type="number" class="form-control" id="hr-retention" name="retention_days" min="0" max="3650" value="<?= (int) $hrS['retention_days'] ?>" aria-describedby="hr-retention-d">
        <div class="form-text" id="hr-retention-d">0 keeps them forever. Open reports are never deleted.</div>
      </div>
    </div>
    <div class="form-check mb-2">
      <input class="form-check-input" type="checkbox" name="report_contact" id="hr-contact" value="1"<?= $hrS['report_contact'] ? ' checked' : '' ?>>
      <label class="form-check-label" for="hr-contact">Ask for an optional email address so you can reply</label>
    </div>
  </div></div>

  <div class="card mb-3"><div class="card-body">
    <h2 class="h4">Spam protection</h2>
    <p>Every report already passes a hidden honeypot field, a minimum time on the form, a limit of <?= (int) HANDRAIL_RATE_MAX ?> reports per 10 minutes from one address, and a duplicate check. Most sites need nothing more. A captcha is only shown to visitors who are not logged in, and its script loads only when someone opens the report form.</p>
    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label" for="hr-provider">Captcha</label>
        <select class="form-select" id="hr-provider" name="captcha_provider" aria-describedby="hr-provider-d">
          <?php foreach (handrailCaptchaProviders() as $hrK => $hrL) { ?>
            <option value="<?= safeReturn($hrK) ?>"<?= $hrProvider === $hrK ? ' selected' : '' ?>><?= safeReturn($hrL) ?></option>
          <?php } ?>
        </select>
        <div class="form-text" id="hr-provider-d">Turnstile is the easiest for people with disabilities. reCAPTCHA v2 can turn into image puzzles, which some visitors can't solve.</div>
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label" for="hr-source">Keys</label>
        <select class="form-select" id="hr-source" name="captcha_source" aria-describedby="hr-source-d">
          <option value="manual"<?= $hrS['captcha_source'] === 'manual' ? ' selected' : '' ?>>The keys I enter below</option>
          <?php foreach ($hrDetected as $hrRow) { ?>
            <option value="<?= safeReturn($hrRow['source']) ?>"<?= $hrS['captcha_source'] === $hrRow['source'] ? ' selected' : '' ?>><?= safeReturn($hrRow['label'] . ': its ' . ['turnstile' => 'Turnstile', 'recaptcha_v3' => 'reCAPTCHA v3', 'recaptcha_v2' => 'reCAPTCHA v2', 'hcaptcha' => 'hCaptcha'][$hrRow['provider']] . ' keys') ?></option>
          <?php } ?>
        </select>
        <div class="form-text" id="hr-source-d"><?= $hrDetected ? 'Borrowed keys stay in the other plugin, so changing them there changes them here. Pick the matching captcha type.' : 'No captcha keys were found in the Turnstile or reCAPTCHA plugins on this site.' ?></div>
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label" for="hr-sitekey">Site key</label>
        <input type="text" class="form-control font-monospace" id="hr-sitekey" name="captcha_sitekey" value="<?= safeReturn($hrS['captcha_sitekey']) ?>" autocomplete="off" spellcheck="false" maxlength="255">
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label" for="hr-secret">Secret key</label>
        <input type="password" class="form-control font-monospace" id="hr-secret" name="captcha_secret" value="" autocomplete="new-password" maxlength="255" aria-describedby="hr-secret-d">
        <div class="form-text" id="hr-secret-d"><?= $hrS['captcha_secret'] !== '' ? 'A secret key is saved. Leave this blank to keep it.' : 'No secret key is saved yet.' ?></div>
        <?php if ($hrS['captcha_secret'] !== '') { ?>
          <div class="form-check"><input class="form-check-input" type="checkbox" name="clear_secret" id="hr-clear" value="1"><label class="form-check-label" for="hr-clear">Clear the saved secret key</label></div>
        <?php } ?>
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label" for="hr-score">reCAPTCHA v3 minimum score</label>
        <input type="number" class="form-control" id="hr-score" name="captcha_v3_score" min="0.1" max="0.9" step="0.1" value="<?= safeReturn((string) $hrS['captcha_v3_score']) ?>" aria-describedby="hr-score-d">
        <div class="form-text" id="hr-score-d">Only used with reCAPTCHA v3. 0.5 is Google's suggestion.</div>
      </div>
    </div>
  </div></div>
  <button type="submit" class="btn btn-primary mb-4">Save report settings</button>
</form>
