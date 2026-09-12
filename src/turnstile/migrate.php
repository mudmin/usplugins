<?php
if (!isset($user) || !$user->isLoggedIn() || !in_array($user->data()->id, $master_account)) { return; }
require_once $abs_us_root . $us_url_root . 'usersc/plugins/turnstile/functions.php';
require_once $abs_us_root . $us_url_root . 'usersc/plugins/turnstile/assets/admin.php';
// Keep all migration variables plugin-specific: the core updater uses $check itself.
$plgTurnstileQuery = $db->query('SELECT id, updates FROM us_plugins WHERE plugin = ?', ['turnstile']);
if ($db->error() || !$plgTurnstileQuery->count()) { return; }
$plgTurnstileRecord = $plgTurnstileQuery->first();
$plgTurnstileUpdates = json_decode($plgTurnstileRecord->updates ?? '', true);
if (!is_array($plgTurnstileUpdates)) { $plgTurnstileUpdates = []; }
if (!in_array('00001', $plgTurnstileUpdates, true)) {
    $db->query("CREATE TABLE IF NOT EXISTS plg_turnstile_settings (
        id int unsigned NOT NULL PRIMARY KEY,
        site_key varchar(255) NOT NULL DEFAULT '',
        secret_key varchar(255) NOT NULL DEFAULT '',
        theme varchar(10) NOT NULL DEFAULT 'auto',
        size varchar(10) NOT NULL DEFAULT 'normal',
        hostnames text NOT NULL,
        protect_login tinyint unsigned NOT NULL DEFAULT 0,
        protect_join tinyint unsigned NOT NULL DEFAULT 0,
        protect_forgot_password tinyint unsigned NOT NULL DEFAULT 0,
        protect_reset_password tinyint unsigned NOT NULL DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    if ($db->error()) { throw new RuntimeException('Unable to create Turnstile settings.'); }
    $db->query("INSERT INTO plg_turnstile_settings (id, hostnames) VALUES (1, '') ON DUPLICATE KEY UPDATE id = id");
    if ($db->error()) { throw new RuntimeException('Unable to initialize Turnstile settings.'); }
    $plgTurnstileUpdates[] = '00001';
    $db->update('us_plugins', $plgTurnstileRecord->id, ['updates' => json_encode($plgTurnstileUpdates)]);
    if ($db->error()) { throw new RuntimeException('Unable to record Turnstile migration.'); }
    logger($user->data()->id, 'Migrations', '00001 migration completed for turnstile');
}
if (!in_array('00002', $plgTurnstileUpdates, true)) {
    // Check the column too, so retrying after an interrupted migration is safe.
    $plgTurnstileColumn = $db->query("SHOW COLUMNS FROM plg_turnstile_settings LIKE 'protect_passwordless'");
    if ($db->error()) { throw new RuntimeException('Unable to inspect Turnstile settings.'); }
    if (!$plgTurnstileColumn->count()) {
        $db->query('ALTER TABLE plg_turnstile_settings ADD protect_passwordless tinyint unsigned NOT NULL DEFAULT 0');
        if ($db->error()) { throw new RuntimeException('Unable to add passwordless protection setting.'); }
    }
    $plgTurnstileUpdates[] = '00002';
    $db->update('us_plugins', $plgTurnstileRecord->id, ['updates' => json_encode($plgTurnstileUpdates)]);
    if ($db->error()) { throw new RuntimeException('Unable to record Turnstile migration.'); }
    logger($user->data()->id, 'Migrations', '00002 migration completed for turnstile');
}
$plgTurnstileSettings = plgTurnstileSettings(true);
if ($plgTurnstileSettings) { plgTurnstileSyncHooks($plgTurnstileSettings); }
