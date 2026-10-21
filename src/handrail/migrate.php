<?php
if (!isset($user) || !$user->isLoggedIn() || !in_array($user->data()->id, $master_account)) { return; }
// Keep all migration variables plugin-specific: the core updater uses $check itself.
$hrQuery = $db->query('SELECT id, updates FROM us_plugins WHERE plugin = ?', ['handrail']);
if ($db->error() || !$hrQuery->count()) { return; }
$hrRecord = $hrQuery->first();
$hrUpdates = json_decode($hrRecord->updates ?? '', true);
if (!is_array($hrUpdates)) { $hrUpdates = []; }

if (!in_array('00001', $hrUpdates, true)) {
    // All settings live in one JSON row, like the WordPress plugin's single option.
    $db->query("CREATE TABLE IF NOT EXISTS plg_handrail_settings (
        id int unsigned NOT NULL PRIMARY KEY,
        data mediumtext NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    if ($db->error()) { throw new RuntimeException('Unable to create Handrail settings.'); }
    // The salt for hashing visitor addresses (rate limiting only) is made once, here.
    $db->query("INSERT INTO plg_handrail_settings (id, data) VALUES (1, ?) ON DUPLICATE KEY UPDATE id = id",
        [json_encode(['salt' => bin2hex(random_bytes(32))])]);
    if ($db->error()) { throw new RuntimeException('Unable to initialize Handrail settings.'); }

    $db->query("CREATE TABLE IF NOT EXISTS plg_handrail_reports (
        id int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
        status varchar(10) NOT NULL DEFAULT 'open',
        created datetime NOT NULL,
        user_id int unsigned NOT NULL DEFAULT 0,
        page_url varchar(2000) NOT NULL,
        page_title varchar(255) NOT NULL DEFAULT '',
        categories varchar(255) NOT NULL DEFAULT '',
        comments text NOT NULL,
        contact varchar(190) NOT NULL DEFAULT '',
        adjustments text NOT NULL,
        viewport varchar(20) NOT NULL DEFAULT '',
        zoom smallint unsigned NOT NULL DEFAULT 0,
        lang varchar(20) NOT NULL DEFAULT '',
        ua varchar(300) NOT NULL DEFAULT '',
        scan text NULL,
        notes text NOT NULL,
        resolved_by int unsigned NOT NULL DEFAULT 0,
        resolved_on datetime NULL,
        KEY status (status, created),
        KEY contact (contact)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    if ($db->error()) { throw new RuntimeException('Unable to create Handrail reports.'); }

    // Rate limits, duplicate checks and the hourly email count. Rows expire on their own.
    $db->query("CREATE TABLE IF NOT EXISTS plg_handrail_limits (
        k char(40) NOT NULL PRIMARY KEY,
        hits int unsigned NOT NULL DEFAULT 0,
        expires int unsigned NOT NULL,
        KEY expires (expires)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    if ($db->error()) { throw new RuntimeException('Unable to create Handrail limits.'); }

    $hrUpdates[] = '00001';
    $db->update('us_plugins', $hrRecord->id, ['updates' => json_encode($hrUpdates)]);
    if ($db->error()) { throw new RuntimeException('Unable to record Handrail migration.'); }
    logger($user->data()->id, 'Migrations', '00001 migration completed for handrail');
}
