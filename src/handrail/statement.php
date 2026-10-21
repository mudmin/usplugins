<?php
// The accessibility statement page. A site can also show it at its own address
// (Handrail > Statement > Address): that folder's index.php includes this file, which is
// why init.php may already be loaded here.
if (!isset($abs_us_root)) {
    // Walk up from the requested path (not __DIR__, which resolves symlinks) to the site root.
    $hrDir = dirname((string) $_SERVER['SCRIPT_FILENAME']);
    while (!is_file($hrDir . '/users/init.php') && dirname($hrDir) !== $hrDir) {
        $hrDir = dirname($hrDir);
    }
    require_once $hrDir . '/users/init.php';
}
if (!pluginActive('handrail', true)) {
    Redirect::to($us_url_root . 'index.php');
}
require_once $abs_us_root . $us_url_root . 'usersc/plugins/handrail/assets/includes/statement.php';
$pageTitle = 'Accessibility statement';
require_once $abs_us_root . $us_url_root . 'users/includes/template/prep.php';
?>
<div class="container py-4">
  <div class="row">
    <div class="col-12 col-lg-9">
      <h1>Accessibility statement</h1>
      <?= handrailStatementHtml() ?>
    </div>
  </div>
</div>
<?php require_once $abs_us_root . $us_url_root . 'users/includes/html_footer.php'; ?>
