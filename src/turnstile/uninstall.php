<?php
if (!isset($user) || !$user->isLoggedIn() || !in_array($user->data()->id, $master_account)) { return; }
// UserSpice calls this on deactivation. Preserve settings and migration history.
deRegisterHooks('turnstile');
$db->query('UPDATE us_plugins SET status = ? WHERE plugin = ?', ['uninstalled', 'turnstile']);
logger($user->data()->id, 'USPlugins', 'Turnstile deactivated; settings retained');
