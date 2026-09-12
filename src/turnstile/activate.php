<?php
if (!isset($user) || !$user->isLoggedIn() || !in_array($user->data()->id, $master_account)) { return; }
require $abs_us_root . $us_url_root . 'usersc/plugins/turnstile/migrate.php';
$db->query('UPDATE us_plugins SET status = ? WHERE plugin = ?', ['active', 'turnstile']);
if ($db->error()) { throw new RuntimeException('Unable to activate Turnstile.'); }
logger($user->data()->id, 'USPlugins', 'Turnstile activated');
