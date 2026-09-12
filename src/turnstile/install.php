<?php
if (!isset($user) || !$user->isLoggedIn() || !in_array($user->data()->id, $master_account)) { return; }
require $abs_us_root . $us_url_root . 'usersc/plugins/turnstile/plugin_info.php';
$plgTurnstileQuery = $db->query('SELECT id FROM us_plugins WHERE plugin = ?', [$plugin_name]);
if (!$plgTurnstileQuery->count()) {
    $db->insert('us_plugins', ['plugin' => $plugin_name, 'status' => 'installed']);
    if ($db->error()) { throw new RuntimeException('Unable to install Turnstile.'); }
    logger($user->data()->id, 'USPlugins', 'Turnstile installed');
}
require $abs_us_root . $us_url_root . 'usersc/plugins/turnstile/migrate.php';
