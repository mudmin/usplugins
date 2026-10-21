<?php
if (!isset($user) || !$user->isLoggedIn() || !in_array($user->data()->id, $master_account)) { return; }
// UserSpice calls this on deactivation. Settings, reports and migration history stay;
// deleting the plugin (delete.php) removes them.
deRegisterHooks('handrail');
$db->query('UPDATE us_plugins SET status = ? WHERE plugin = ?', ['uninstalled', 'handrail']);
logger($user->data()->id, 'USPlugins', 'Handrail deactivated; settings and reports retained');
