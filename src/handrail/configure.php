<?php
if (count(get_included_files()) === 1) { exit('Direct access not permitted.'); }
if (!isset($user) || !$user->isLoggedIn() || !in_array($user->data()->id, $master_account)) {
    Redirect::to($us_url_root . 'users/admin.php');
}
require $abs_us_root . $us_url_root . 'usersc/plugins/handrail/plugin_info.php';
if (!pluginActive($plugin_name)) { return; }
$hrInc = $abs_us_root . $us_url_root . 'usersc/plugins/handrail/assets/includes/';
require_once $hrInc . 'settings.php';
require_once $hrInc . 'statement.php';
require_once $hrInc . 'captcha.php';
require_once $hrInc . 'reports.php';

// Settings that predate a table (an older install) are created on the next visit.
try {
    $db->query('SELECT 1 FROM plg_handrail_reports LIMIT 1');
    $hrNeedsMigrate = (bool) $db->error();
} catch (Throwable $e) {
    $hrNeedsMigrate = true;
}
if ($hrNeedsMigrate) {
    require $abs_us_root . $us_url_root . 'usersc/plugins/handrail/migrate.php';
    handrailSettings(true);
}

$hrTabs = [
    'look' => 'Look and placement',
    'tools' => 'Tools',
    'reports' => 'Reports',
    'check' => 'Checker',
    'statement' => 'Statement',
    'support' => 'Credit and support',
];
$hrTab = (string) Input::get('tab');
if (!isset($hrTabs[$hrTab])) {
    $hrTab = 'look';
}
$hrSelf = $us_url_root . 'users/admin.php?view=plugins_config&plugin=handrail';

if (Server::get('REQUEST_METHOD') === 'POST') {
    if (!Token::check(Input::get('csrf'))) {
        include $abs_us_root . $us_url_root . 'usersc/scripts/token_error.php';
        return;
    }
    require $hrInc . 'admin_post.php';
}

handrailMaybePurge();
$hrS = handrailSettings();
$hrOpen = handrailOpenCount();
?>
<style nonce="<?= safeReturn($userspice_nonce ?? '') ?>">
/* users/css/dashboard/style.css sets body and p to black, which can't be read in dark mode. */
.handrail-admin { color: var(--bs-body-color); }
.handrail-admin p { color: inherit; }
</style>
<div class="container-fluid py-3 handrail-admin">
  <a href="<?= safeReturn($us_url_root . 'users/admin.php?view=plugins') ?>">Return to the Plugin Manager</a>
  <h1 class="mt-3">Handrail</h1>
  <p class="lead mb-2">An accessibility panel for your visitors: they adjust how the site looks and reads, report a barrier, and admins check the page and see who can fix each problem.</p>
  <p class="text-body-secondary small">These tools make your site easier to use. They don't change how it is built, and they are not a certification or guarantee of accessibility. Every feature is free on UserSpice.</p>

  <nav aria-label="Handrail settings">
    <ul class="nav nav-tabs mb-3 flex-wrap">
      <?php foreach ($hrTabs as $hrId => $hrLabel) { ?>
        <li class="nav-item">
          <a class="nav-link<?= $hrId === $hrTab ? ' active' : '' ?>"<?= $hrId === $hrTab ? ' aria-current="page"' : '' ?> href="<?= safeReturn($hrSelf . '&tab=' . $hrId) ?>"><?= safeReturn($hrLabel) ?><?php if ($hrId === 'reports' && $hrOpen) { ?> <span class="badge text-bg-danger"><?= (int) $hrOpen ?><span class="visually-hidden"> open</span></span><?php } ?></a>
        </li>
      <?php } ?>
    </ul>
  </nav>

  <?php require $hrInc . 'tab_' . $hrTab . '.php'; ?>
</div>
