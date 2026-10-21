<?php
if (!isset($user) || !$user->isLoggedIn() || !in_array($user->data()->id, $master_account)) { return; }
// Called only when the administrator explicitly deletes the plugin.
require_once $abs_us_root . $us_url_root . 'usersc/plugins/handrail/assets/includes/settings.php';
handrailRemovePublicPage();
$db->query('DROP TABLE IF EXISTS plg_handrail_settings');
$db->query('DROP TABLE IF EXISTS plg_handrail_reports');
$db->query('DROP TABLE IF EXISTS plg_handrail_limits');
$db->query('DELETE FROM us_plugin_hooks WHERE folder = ?', ['handrail']);
$db->query('DELETE FROM us_plugins WHERE plugin = ?', ['handrail']);
logger($user->data()->id, 'USPlugins', 'Handrail deleted');
