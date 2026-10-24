<?php
if (count(get_included_files()) === 1) { exit('Direct access not permitted.'); }
// Handrail for UserSpice: settings, the tool and preset catalogs, and who sees what.
// Loaded on every page by functions.php, so nothing here queries until it's asked for.

if (!defined('HANDRAIL_US_VERSION')) {
    define('HANDRAIL_US_VERSION', '1.1.3');
    define('HANDRAIL_HOME', 'https://makewpcompliant.com/handrail/');
    define('HANDRAIL_DONATE', 'https://makewpcompliant.com/donate/?for=handrail-userspice');
    define('HANDRAIL_MAX_PRESETS', 6);
}

if (!function_exists('handrailDefaults')) {
    function handrailDefaults()
    {
        return [
            // Panel style (see handrailStyles()).
            'style' => 'sheet',
            // Placement.
            'floating' => 1,
            'corner' => 'br',
            'offset_x' => 16,
            'offset_y' => 16,
            'size' => 'medium',
            'label' => 'icon',
            'icon' => 'person',
            'hide_on_mobile' => 0,
            'on_dashboard' => 0,
            // Tools.
            'tools' => array_keys(handrailTools(false)),
            'presets' => ['low-vision', 'dyslexia', 'seizure', 'keyboard', 'focus'],
            'custom_presets' => [],
            // Branding.
            'brand_accent' => '',
            'brand_title' => '',
            'brand_button' => '',
            // Reports.
            'reports' => 'off',
            'report_email' => '',
            'notify' => 'each',
            'report_contact' => 1,
            'retention_days' => 365,
            'captcha_provider' => 'none',
            'captcha_source' => 'manual',
            'captcha_sitekey' => '',
            'captcha_secret' => '',
            'captcha_v3_score' => 0.5,
            // Checker. Permission levels that see the Check tab; 2 is Administrator.
            'check' => 1,
            'check_perms' => [2],
            'check_wcag22' => 1,
            // Statement.
            'public_path' => '',
            'org_name' => '',
            'contact_email' => '',
            'contact_phone' => '',
            'standard' => 'wcag21aa',
            // Nothing is claimed until the site owner chooses: a statement is a public claim.
            'conformance' => 'unknown',
            'limitations' => '',
            'response_days' => 5,
            'reviewed' => '',
            // The "Accessibility tools by Handrail" link. On by default; supporters turn it off.
            'credit' => 1,
            // Internal.
            'salt' => '',
            'last_purge' => 0,
        ];
    }
}

if (!function_exists('handrailSettings')) {
    function handrailSettings($refresh = false)
    {
        global $db;
        static $cache = null;
        if ($cache === null || $refresh) {
            $saved = [];
            try {
                $q = $db->query('SELECT data FROM plg_handrail_settings WHERE id = 1');
                if (!$db->error() && $q->count()) {
                    $saved = json_decode((string) $q->first()->data, true);
                }
            } catch (Throwable $e) {
                // A missing table (plugin files present but never installed) just means defaults.
            }
            $cache = array_merge(handrailDefaults(), is_array($saved) ? $saved : []);
        }
        return $cache;
    }
}

if (!function_exists('handrailSaveSettings')) {
    function handrailSaveSettings($s)
    {
        global $db;
        $db->query('INSERT INTO plg_handrail_settings (id, data) VALUES (1, ?) ON DUPLICATE KEY UPDATE data = VALUES(data)', [json_encode($s)]);
        $ok = !$db->error();
        handrailSettings(true);
        return $ok;
    }
}

if (!function_exists('handrailTools')) {
    /**
     * The tool catalog. Each tool: label, desc, steps (one label per step), group,
     * js (needs the prefs runtime before first paint), excludes.
     * On UserSpice every tool is free, including the ones that are Pro on WordPress.
     */
    function handrailTools($withCustom = true)
    {
        return [
            'text-size' => ['label' => 'Text size', 'desc' => 'Bigger text everywhere', 'steps' => ['115%', '130%', '150%', '175%', '200%'], 'group' => 'text', 'js' => true],
            'font' => ['label' => 'Readable font', 'desc' => 'A plainer, clearer typeface', 'steps' => ['On'], 'group' => 'text'],
            'dyslexia-font' => ['label' => 'Dyslexia font', 'desc' => 'Letters with heavier bottoms', 'steps' => ['On'], 'group' => 'text'],
            'line-height' => ['label' => 'Line height', 'desc' => 'More space between lines', 'steps' => ['1.5', '1.8', '2.1'], 'group' => 'text'],
            'spacing' => ['label' => 'Text spacing', 'desc' => 'More room between letters and words', 'steps' => ['Wider', 'Widest'], 'group' => 'text'],
            'text-align' => ['label' => 'Align text left', 'desc' => 'No centered or justified text', 'steps' => ['On'], 'group' => 'text'],
            'contrast' => ['label' => 'Contrast', 'desc' => 'Stronger text and backgrounds', 'steps' => ['Dark', 'High contrast'], 'group' => 'color'],
            'color-filter' => ['label' => 'Color filter', 'desc' => 'Change how colors look', 'steps' => ['Grayscale', 'Soft colors', 'Strong colors', 'Inverted'], 'group' => 'color'],
            'hide-images' => ['label' => 'Hide images', 'desc' => 'Only text and controls', 'steps' => ['On'], 'group' => 'color'],
            'read-aloud' => ['label' => 'Read aloud', 'desc' => 'Click any text to hear it', 'steps' => ['On'], 'group' => 'reading', 'js' => true],
            'guide' => ['label' => 'Reading guide', 'desc' => 'A line that follows your pointer', 'steps' => ['On'], 'group' => 'reading', 'js' => true, 'excludes' => ['mask']],
            'mask' => ['label' => 'Reading mask', 'desc' => 'Dims everything but a band of text', 'steps' => ['Narrow', 'Wide'], 'group' => 'reading', 'js' => true, 'excludes' => ['guide']],
            'links' => ['label' => 'Highlight links', 'desc' => 'Underline and outline every link', 'steps' => ['On'], 'group' => 'moving'],
            'focus' => ['label' => 'Focus outline', 'desc' => 'A thick outline shows where you are', 'steps' => ['On'], 'group' => 'moving'],
            'motion' => ['label' => 'Stop animations', 'desc' => 'No moving or autoplaying content', 'steps' => ['On'], 'group' => 'moving', 'js' => true],
            'cursor' => ['label' => 'Large cursor', 'desc' => 'A bigger mouse pointer', 'steps' => ['Dark', 'Light'], 'group' => 'moving'],
        ];
    }
}

if (!function_exists('handrailStyles')) {
    /** Icons for the button, the panel heading and the Accessibility link. The markup is in core/icons.json. */
    function handrailIcons()
    {
        return [
            'person' => 'Person',
            'person-circle' => 'Person in a circle',
            'handrail' => 'Handrail',
            'sliders' => 'Sliders',
            'eye' => 'Eye',
        ];
    }

    /** The icon to use: the saved one if it still exists. */
    function handrailIcon()
    {
        $icon = handrailSettings()['icon'];
        return isset(handrailIcons()[$icon]) ? $icon : 'person';
    }

    /** An icon as inline SVG (decorative: screen readers skip it). */
    function handrailIconSvg($size = 24, $icon = null)
    {
        static $glyphs = null;
        if ($glyphs === null) {
            $glyphs = json_decode((string) @file_get_contents(dirname(__DIR__, 2) . '/core/icons.json'), true);
            $glyphs = is_array($glyphs) ? $glyphs : [];
        }
        $icon = $icon ?? handrailIcon();
        if (empty($glyphs[$icon])) {
            return '';
        }
        return '<svg width="' . (int) $size . '" height="' . (int) $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $glyphs[$icon] . '</svg>';
    }

    function handrailStyles()
    {
        return [
            'sheet' => ['label' => 'Settings sheet', 'desc' => 'A panel from the side. Each tool is one row with a switch or a short list of choices.'],
            'tiles' => ['label' => 'Tile grid', 'desc' => 'A panel from the side with a grid of tiles. Each click steps a tile to its next setting.'],
            'card' => ['label' => 'Floating card', 'desc' => 'A rounded card next to the button. Visitors start from the quick settings, then fine-tune one group at a time with − and + buttons.'],
            'dock' => ['label' => 'Reading dock', 'desc' => 'A bar along the bottom of the screen with every tool in view, so the page stays visible while visitors adjust it.'],
        ];
    }
}

if (!function_exists('handrailGroups')) {
    function handrailGroups()
    {
        return ['text' => 'Text', 'color' => 'Color', 'reading' => 'Reading help', 'moving' => 'Finding your way'];
    }
}

if (!function_exists('handrailPresets')) {
    /** Quick settings: built-in ones plus the site's own. Each maps tool id => step. */
    function handrailPresets()
    {
        $presets = [
            'low-vision' => ['label' => 'Low vision', 'desc' => 'Bigger text, clearer links and focus', 'tools' => ['text-size' => 2, 'links' => 1, 'focus' => 1, 'cursor' => 1]],
            // OpenDyslexic is already widely spaced, so no extra letter spacing.
            'dyslexia' => ['label' => 'Dyslexia friendly', 'desc' => 'Dyslexia font, left-aligned, taller lines', 'tools' => ['dyslexia-font' => 1, 'line-height' => 1, 'text-align' => 1]],
            // Not "seizure safe": stopping CSS animation can't promise anything about flashing
            // video or GIFs, so the name says only what it does.
            'seizure' => ['label' => 'Reduce motion', 'desc' => 'Stops animations and softens colors', 'tools' => ['motion' => 1, 'color-filter' => 2]],
            'keyboard' => ['label' => 'Keyboard', 'desc' => 'Always see where you are', 'tools' => ['focus' => 1, 'links' => 1]],
            'focus' => ['label' => 'Fewer distractions', 'desc' => 'Reading mask and no animations', 'tools' => ['mask' => 1, 'motion' => 1]],
        ];
        foreach ((array) handrailSettings()['custom_presets'] as $i => $p) {
            if (!empty($p['label']) && !empty($p['tools'])) {
                $presets['custom-' . $i] = ['label' => $p['label'], 'desc' => $p['desc'] ?? '', 'tools' => $p['tools']];
            }
        }
        return $presets;
    }
}

if (!function_exists('handrailEnabledTools')) {
    /** Tools the admin turned on, in catalog order. */
    function handrailEnabledTools()
    {
        $on = (array) handrailSettings()['tools'];
        return array_filter(handrailTools(), function ($id) use ($on) {
            return in_array($id, $on, true);
        }, ARRAY_FILTER_USE_KEY);
    }
}

if (!function_exists('handrailReportCategories')) {
    function handrailReportCategories()
    {
        return [
            'read' => 'Hard to read (color, size or font)',
            'keyboard' => "Can't use it with a keyboard",
            'reader' => 'Problem with a screen reader',
            'media' => 'Image, video or audio has no description or captions',
            'form' => "A form doesn't work for me",
            'document' => "A document (PDF, Word) isn't accessible",
            'other' => 'Something else',
        ];
    }
}

if (!function_exists('handrailCanReport')) {
    function handrailCanReport()
    {
        global $user;
        $mode = handrailSettings()['reports'];
        if ($mode === 'everyone') {
            return true;
        }
        return $mode === 'logged_in' && isset($user) && $user->isLoggedIn();
    }
}

if (!function_exists('handrailCanCheck')) {
    function handrailCanCheck()
    {
        global $user;
        $s = handrailSettings();
        if (empty($s['check']) || !isset($user) || !$user->isLoggedIn()) {
            return false;
        }
        $perms = array_values(array_filter(array_map('intval', (array) $s['check_perms'])));
        return $perms && hasPerm($perms);
    }
}

if (!function_exists('handrailTier')) {
    /** public, editor or admin. Admins also get selectors, contrast numbers and the Markdown export. */
    function handrailTier()
    {
        if (!handrailCanCheck()) {
            return 'public';
        }
        return hasPerm([2]) ? 'admin' : 'editor';
    }
}

if (!function_exists('handrailUrl')) {
    /** A URL inside the plugin folder. */
    function handrailUrl($path = '')
    {
        global $us_url_root;
        return $us_url_root . 'usersc/plugins/handrail/' . $path;
    }
}

if (!function_exists('handrailSiteUrl')) {
    /** Scheme and host of this site, for absolute links in emails. */
    function handrailSiteUrl()
    {
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443;
        $host = preg_replace('/[^A-Za-z0-9.\-:\[\]]/', '', (string) ($_SERVER['HTTP_HOST'] ?? 'localhost'));
        return ($https ? 'https' : 'http') . '://' . $host;
    }
}

// ------------------------------------------------------------------ statement address

if (!function_exists('handrailValidPublicPath')) {
    /** Lowercase folder names, up to three levels, and never one of UserSpice's own folders. */
    function handrailValidPublicPath($p)
    {
        if (!preg_match('#^[a-z0-9][a-z0-9_-]{0,40}(/[a-z0-9][a-z0-9_-]{0,40}){0,2}$#', (string) $p)) {
            return false;
        }
        return !in_array(explode('/', $p)[0], ['users', 'usersc', 'api', 'vendor', 'node_modules'], true);
    }
}

if (!function_exists('handrailPublicPath')) {
    /** The statement's own folder, if one is set and its index.php is in place. */
    function handrailPublicPath()
    {
        global $abs_us_root, $us_url_root;
        $p = trim((string) handrailSettings()['public_path'], '/');
        if ($p === '' || !handrailValidPublicPath($p)) {
            return null;
        }
        return is_file($abs_us_root . $us_url_root . $p . '/index.php') ? $p : null;
    }
}

if (!function_exists('handrailPublicWrapper')) {
    /** The whole of the statement folder's index.php: it just includes the plugin's page. */
    function handrailPublicWrapper($p)
    {
        $up = str_repeat('../', substr_count($p, '/') + 1);
        return "<?php\n//Shows the accessibility statement at this address. Created by the Handrail plugin (Statement > Address).\n"
            . "require_once '" . $up . "users/init.php';\n"
            . "require \$abs_us_root . \$us_url_root . 'usersc/plugins/handrail/statement.php';\n";
    }
}

if (!function_exists('handrailOwnsFile')) {
    /** Only ever touch an index.php this plugin wrote. */
    function handrailOwnsFile($file)
    {
        return is_file($file) && strpos((string) file_get_contents($file), 'Created by the Handrail plugin') !== false;
    }
}

if (!function_exists('handrailRemovePublicPage')) {
    /** Remove the statement folder's index.php (and any empty folders above it) if Handrail made it. */
    function handrailRemovePublicPage($p = null)
    {
        global $abs_us_root, $us_url_root;
        $p = trim((string) ($p === null ? handrailSettings()['public_path'] : $p), '/');
        $root = $abs_us_root . $us_url_root;
        if ($p === '' || !handrailValidPublicPath($p) || !handrailOwnsFile($root . $p . '/index.php')) {
            return;
        }
        @unlink($root . $p . '/index.php');
        $parts = explode('/', $p);
        while ($parts && @rmdir($root . implode('/', $parts))) {
            array_pop($parts);
        }
    }
}

if (!function_exists('handrailStatementUrl')) {
    function handrailStatementUrl()
    {
        global $us_url_root;
        $p = handrailPublicPath();
        return $p !== null ? $us_url_root . $p . '/' : handrailUrl('statement.php');
    }
}

if (!function_exists('handrailTriggerUrl')) {
    /** Where #handrail links point: the statement page, which also works without JavaScript. */
    function handrailTriggerUrl($tab = '')
    {
        return handrailStatementUrl() . '#handrail' . ($tab ? '-' . $tab : '');
    }
}

// ------------------------------------------------------------------ colors

if (!function_exists('handrailHex')) {
    function handrailHex($value)
    {
        $value = trim((string) $value);
        if (preg_match('/^#([0-9a-f]{3})$/i', $value, $m)) {
            $value = '#' . $m[1][0] . $m[1][0] . $m[1][1] . $m[1][1] . $m[1][2] . $m[1][2];
        }
        return preg_match('/^#[0-9a-f]{6}$/i', $value) ? strtolower($value) : '';
    }

    function handrailLuminance($hex)
    {
        $c = array_map(function ($v) {
            $v = hexdec($v) / 255;
            return $v <= 0.03928 ? $v / 12.92 : pow(($v + 0.055) / 1.055, 2.4);
        }, str_split(substr($hex, 1), 2));
        return 0.2126 * $c[0] + 0.7152 * $c[1] + 0.0722 * $c[2];
    }

    function handrailRatio($a, $b)
    {
        $x = handrailLuminance($a);
        $y = handrailLuminance($b);
        return (max($x, $y) + 0.05) / (min($x, $y) + 0.05);
    }

    /** Black or white, whichever reads better on the color. */
    function handrailTextOn($hex)
    {
        return handrailRatio($hex, '#ffffff') >= handrailRatio($hex, '#000000') ? '#ffffff' : '#000000';
    }
}
