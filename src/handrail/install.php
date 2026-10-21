<?php
if (!isset($user) || !$user->isLoggedIn() || !in_array($user->data()->id, $master_account)) { return; }
require $abs_us_root . $us_url_root . 'usersc/plugins/handrail/plugin_info.php';
$hrQuery = $db->query('SELECT id FROM us_plugins WHERE plugin = ?', [$plugin_name]);
if (!$hrQuery->count()) {
    $db->insert('us_plugins', ['plugin' => $plugin_name, 'status' => 'installed']);
    if ($db->error()) { throw new RuntimeException('Unable to install Handrail.'); }
    logger($user->data()->id, 'USPlugins', 'Handrail installed');
}
// Handrail needs no hooks: header.php and footer.php run on every page by themselves.
require $abs_us_root . $us_url_root . 'usersc/plugins/handrail/migrate.php';
