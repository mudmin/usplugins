<?php
if (!isset($user) || !$user->isLoggedIn() || !in_array($user->data()->id, $master_account)) { return; }
// Called only when the administrator explicitly deletes the plugin.
$db->query('DROP TABLE IF EXISTS plg_turnstile_settings');
$db->query('DELETE FROM us_plugin_hooks WHERE folder = ?', ['turnstile']);
$db->query('DELETE FROM us_plugins WHERE plugin = ?', ['turnstile']);
logger($user->data()->id, 'USPlugins', 'Turnstile deleted');
