<?php
if (count(get_included_files()) === 1) { exit('Direct access not permitted.'); }
// Barrier reports: cleaning, rate limits, storage, notifications and retention.

require_once __DIR__ . '/settings.php';

if (!defined('HANDRAIL_RATE_MAX')) {
    define('HANDRAIL_RATE_MAX', 5);
    define('HANDRAIL_RATE_WINDOW', 600);
    define('HANDRAIL_MIN_SECONDS', 3);
    // Above this many report emails in an hour, the rest wait for the Reports screen (one notice is sent).
    define('HANDRAIL_MAIL_CAP', 20);
}

// ------------------------------------------------------------------ limits

if (!function_exists('handrailLimitHits')) {
    /** Current count for a key, or 0 once it has expired. */
    function handrailLimitHits($key)
    {
        global $db;
        $q = $db->query('SELECT hits FROM plg_handrail_limits WHERE k = ? AND expires > ?', [sha1($key), time()]);
        return $q->count() ? (int) $q->first()->hits : 0;
    }

    /** Add one to a key. A new or expired key starts a fresh window. */
    function handrailLimitBump($key, $window)
    {
        global $db;
        $now = time();
        $db->query(
            'INSERT INTO plg_handrail_limits (k, hits, expires) VALUES (?, 1, ?)
             ON DUPLICATE KEY UPDATE hits = IF(expires > ?, hits + 1, 1), expires = IF(expires > ?, expires, VALUES(expires))',
            [sha1($key), $now + $window, $now, $now]
        );
    }

    function handrailLimitPurge()
    {
        global $db;
        $db->query('DELETE FROM plg_handrail_limits WHERE expires <= ?', [time()]);
    }
}

if (!function_exists('handrailIpHash')) {
    /**
     * The visitor's address for rate limiting only, never stored as is. UserSpice's ipCheck()
     * already applies the site's proxy settings. IPv6 addresses are grouped by /64, since one
     * visitor usually controls the whole block.
     */
    function handrailIpHash()
    {
        $ip = function_exists('ipCheck') ? (string) ipCheck() : (string) ($_SERVER['REMOTE_ADDR'] ?? '');
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $ip = implode(':', array_slice(explode(':', inet_ntop(inet_pton($ip)) . '::::'), 0, 4)) . '::/64';
        }
        return hash_hmac('sha256', $ip, handrailSalt());
    }

    function handrailSalt()
    {
        $s = handrailSettings();
        if ($s['salt'] === '') {
            $s['salt'] = bin2hex(random_bytes(32));
            handrailSaveSettings($s);
        }
        return $s['salt'];
    }
}

// ------------------------------------------------------------------ saving

if (!function_exists('handrailCleanReport')) {
    /** The payload as the fields we store, or an error message. */
    function handrailCleanReport($p)
    {
        foreach (['page_url', 'page_title', 'comments', 'contact', 'viewport', 'lang'] as $k) {
            if (isset($p[$k]) && !is_string($p[$k])) {
                $p[$k] = '';
            }
        }
        $url = trim((string) ($p['page_url'] ?? ''));
        $parts = parse_url($url);
        $host = strtolower((string) parse_url(handrailSiteUrl(), PHP_URL_HOST));
        if ($url === '' || strlen($url) > 2000 || !is_array($parts) || !in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)
            || isset($parts['user']) || isset($parts['pass']) || strtolower((string) ($parts['host'] ?? '')) !== $host) {
            return 'Reports can only be about pages on this site.';
        }

        $allowed = array_keys(handrailReportCategories());
        $categories = [];
        foreach ((array) ($p['categories'] ?? []) as $c) {
            if (is_string($c) && in_array($c, $allowed, true) && !in_array($c, $categories, true)) {
                $categories[] = $c;
            }
        }
        $comments = mb_substr(trim(str_replace("\0", '', (string) ($p['comments'] ?? ''))), 0, 2000);
        if (!$categories && $comments === '') {
            return 'Choose at least one problem or tell us what happened.';
        }

        $contact = '';
        if (!empty($p['contact']) && handrailSettings()['report_contact']) {
            $contact = trim((string) $p['contact']);
            if (strlen($contact) > 190 || !filter_var($contact, FILTER_VALIDATE_EMAIL)) {
                return 'That email address does not look right. Fix it or leave it blank.';
            }
        }

        $tools = handrailTools();
        $adjustments = [];
        foreach ((array) ($p['adjustments'] ?? []) as $tool => $step) {
            if (is_string($tool) && is_scalar($step) && isset($tools[$tool]) && (int) $step > 0 && (int) $step <= count($tools[$tool]['steps'])) {
                $adjustments[$tool] = (int) $step;
            }
        }

        $scan = null;
        if (!empty($p['scan']) && is_array($p['scan']) && handrailCanCheck()) {
            $v = [];
            foreach ((array) ($p['scan']['violations'] ?? []) as $rule => $n) {
                if (preg_match('/^[a-z0-9-]{1,60}$/', (string) $rule) && count($v) < 80) {
                    $v[$rule] = max(0, (int) $n);
                }
            }
            $scan = [
                'axe' => substr(preg_replace('/[^0-9.]/', '', (string) ($p['scan']['axe'] ?? '')), 0, 20),
                'violations' => $v,
                'incomplete' => max(0, (int) ($p['scan']['incomplete'] ?? 0)),
            ];
        }

        return [
            'page_url' => $url,
            'page_title' => mb_substr(trim(strip_tags((string) ($p['page_title'] ?? ''))), 0, 200),
            'categories' => $categories,
            'comments' => $comments,
            'contact' => $contact,
            'adjustments' => $adjustments,
            'viewport' => preg_match('/^\d{2,5}x\d{2,5}$/', (string) ($p['viewport'] ?? '')) ? (string) $p['viewport'] : '',
            'zoom' => min(1000, max(0, (int) ($p['zoom'] ?? 0))),
            'lang' => substr(preg_replace('/[^A-Za-z0-9-]/', '', (string) ($p['lang'] ?? '')), 0, 20),
            'ua' => mb_substr(strip_tags((string) ($_SERVER['HTTP_USER_AGENT'] ?? '')), 0, 300),
            'scan' => $scan,
        ];
    }
}

if (!function_exists('handrailSaveReport')) {
    /**
     * Rate limit, dedupe and save, one report at a time so parallel requests can't slip past
     * the limit. Returns ['id' => n], ['ok' => true] for a quiet duplicate, or an error array.
     */
    function handrailSaveReport($data, $userId)
    {
        global $db;
        $lock = $db->query('SELECT GET_LOCK(?, 5) AS got', ['handrail_report_' . Config::get('mysql/db')]);
        if (!$lock->count() || (int) $lock->first()->got !== 1) {
            return ['error' => 'The site is busy. Please try again in a moment.', 'status' => 503];
        }
        try {
            $who = $userId ? 'u' . $userId : handrailIpHash();
            if (handrailLimitHits('rate|' . $who) >= HANDRAIL_RATE_MAX) {
                return ['error' => 'Too many reports from here. Please wait a few minutes and try again.', 'status' => 429];
            }
            // Same person, same page, same words within the window: already have it.
            $dedupe = 'dd|' . $who . '|' . $data['page_url'] . '|' . $data['comments'] . '|' . implode(',', $data['categories']);
            if (handrailLimitHits($dedupe)) {
                return ['ok' => true];
            }
            handrailLimitBump('rate|' . $who, HANDRAIL_RATE_WINDOW);
            handrailLimitBump($dedupe, HANDRAIL_RATE_WINDOW);

            $db->insert('plg_handrail_reports', [
                'status' => 'open',
                'created' => date('Y-m-d H:i:s'),
                'user_id' => (int) $userId,
                'page_url' => $data['page_url'],
                'page_title' => $data['page_title'],
                'categories' => implode(',', $data['categories']),
                'comments' => $data['comments'],
                'contact' => $data['contact'],
                'adjustments' => json_encode((object) $data['adjustments']),
                'viewport' => $data['viewport'],
                'zoom' => $data['zoom'],
                'lang' => $data['lang'],
                'ua' => $data['ua'],
                'scan' => $data['scan'] ? json_encode($data['scan']) : null,
                'notes' => '',
            ]);
            if ($db->error()) {
                return ['error' => 'Sorry, the report could not be saved.', 'status' => 500];
            }
            return ['id' => (int) $db->lastId()];
        } finally {
            $db->query('SELECT RELEASE_LOCK(?)', ['handrail_report_' . Config::get('mysql/db')]);
        }
    }
}

// ------------------------------------------------------------------ reading

if (!function_exists('handrailGetReport')) {
    /** One report, with categories, adjustments and scan decoded. */
    function handrailGetReport($id)
    {
        global $db;
        $q = $db->query('SELECT * FROM plg_handrail_reports WHERE id = ?', [(int) $id]);
        if (!$q->count()) {
            return null;
        }
        $r = (array) $q->first();
        $r['categories'] = $r['categories'] === '' ? [] : explode(',', $r['categories']);
        $r['adjustments'] = (array) json_decode((string) $r['adjustments'], true);
        $r['scan'] = $r['scan'] ? json_decode((string) $r['scan'], true) : null;
        return $r;
    }

    function handrailOpenCount()
    {
        global $db;
        try {
            $q = $db->query("SELECT COUNT(*) AS c FROM plg_handrail_reports WHERE status = 'open'");
            return $db->error() ? 0 : (int) $q->first()->c;
        } catch (Throwable $e) {
            return 0;
        }
    }

    function handrailSetStatus($id, $resolved, $notes = null)
    {
        global $db, $user;
        $fields = $resolved
            ? ['status' => 'resolved', 'resolved_by' => (int) $user->data()->id, 'resolved_on' => date('Y-m-d H:i:s')]
            : ['status' => 'open', 'resolved_by' => 0, 'resolved_on' => null];
        if ($notes !== null) {
            $fields['notes'] = mb_substr((string) $notes, 0, 5000);
        }
        $db->update('plg_handrail_reports', (int) $id, $fields);
    }
}

// ------------------------------------------------------------------ email

if (!function_exists('handrailNotifyAddress')) {
    /** The configured address, or the first master account's email. */
    function handrailNotifyAddress()
    {
        global $db, $master_account;
        $to = trim((string) handrailSettings()['report_email']);
        if (filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return $to;
        }
        $ids = array_values(array_filter(array_map('intval', (array) ($master_account ?? [1]))));
        $q = $db->query('SELECT email FROM users WHERE id = ?', [$ids ? $ids[0] : 1]);
        return $q->count() ? (string) $q->first()->email : '';
    }

    function handrailReportsUrl($id = 0)
    {
        global $us_url_root;
        return handrailSiteUrl() . $us_url_root . 'users/admin.php?view=plugins_config&plugin=handrail&tab=reports' . ($id ? '&report=' . (int) $id : '');
    }

    /** Email one new report, at most HANDRAIL_MAIL_CAP an hour. */
    function handrailNotify($id)
    {
        global $settings;
        if (handrailSettings()['notify'] !== 'each') {
            return;
        }
        $to = handrailNotifyAddress();
        if ($to === '' || !function_exists('email')) {
            return;
        }
        $site = (string) ($settings->site_name ?? 'Your site');
        $sent = handrailLimitHits('mail-hour');
        handrailLimitBump('mail-hour', 3600);
        if ($sent > HANDRAIL_MAIL_CAP) {
            return;
        }
        if ($sent === HANDRAIL_MAIL_CAP) {
            email($to, '[' . $site . '] Many accessibility reports',
                '<p>More than ' . HANDRAIL_MAIL_CAP . ' accessibility reports arrived in the last hour, so Handrail stopped emailing each one. '
                . 'They are all under <a href="' . safeReturn(handrailReportsUrl()) . '">Handrail &gt; Reports</a>. '
                . 'If they look like spam, turn on a captcha under Handrail &gt; Reports.</p>');
            return;
        }
        $r = handrailGetReport($id);
        if (!$r) {
            return;
        }
        $cats = handrailReportCategories();
        $names = array_map(function ($c) use ($cats) {
            return $cats[$c] ?? $c;
        }, $r['categories']);
        $rows = [['Page', '<a href="' . safeReturn($r['page_url']) . '">' . safeReturn($r['page_url']) . '</a>']];
        if ($names) {
            $rows[] = ['Problems', safeReturn(implode('; ', $names))];
        }
        if ($r['comments'] !== '') {
            $rows[] = ['Comments', nl2br(safeReturn($r['comments']))];
        }
        if ($r['contact'] !== '') {
            $rows[] = ['Reply to', safeReturn($r['contact'])];
        }
        $body = '<p>Someone reported an accessibility problem on your site. Treat it like a support request: fix what you can and reply if they left an email.</p><table>';
        foreach ($rows as $row) {
            $body .= '<tr><th align="left" valign="top" style="padding-right:12px">' . $row[0] . '</th><td>' . $row[1] . '</td></tr>';
        }
        $body .= '</table><p><a href="' . safeReturn(handrailReportsUrl($id)) . '">Open the report</a></p>';
        $opts = [];
        if ($r['contact'] !== '') {
            $opts['replyTo'] = $r['contact'];
        }
        email($to, '[' . $site . '] New accessibility report', $body, $opts);
    }
}

// ------------------------------------------------------------------ retention

if (!function_exists('handrailMaybePurge')) {
    /** At most once a day: delete resolved reports past the retention period and expired limits. */
    function handrailMaybePurge()
    {
        global $db;
        $s = handrailSettings();
        if ((int) $s['last_purge'] > time() - 86400) {
            return;
        }
        $s['last_purge'] = time();
        handrailSaveSettings($s);
        handrailLimitPurge();
        $days = (int) $s['retention_days'];
        if ($days > 0) {
            $db->query("DELETE FROM plg_handrail_reports WHERE status = 'resolved' AND created < ?", [date('Y-m-d H:i:s', time() - $days * 86400)]);
        }
    }
}
