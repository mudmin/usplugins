<?php
if (count(get_included_files()) === 1) { exit('Direct access not permitted.'); }
// Handrail for UserSpice: the pre-paint boot, the config object for the shared browser code,
// and the owner map behind "who can fix it". Loaded by header.php and footer.php.

require_once __DIR__ . '/settings.php';

if (!function_exists('handrailActive')) {
    /** Should the panel load on this page? Not on the admin dashboard unless the admin wants it. */
    function handrailActive()
    {
        static $active = null;
        if ($active === null) {
            $self = str_replace('\\', '/', (string) ($_SERVER['PHP_SELF'] ?? ''));
            $dashboard = substr($self, -strlen('users/admin.php')) === 'users/admin.php';
            $active = !$dashboard || !empty(handrailSettings()['on_dashboard']);
        }
        return $active;
    }
}

if (!function_exists('handrailCoreUrl')) {
    function handrailCoreUrl($path = '')
    {
        return handrailUrl('core/' . $path);
    }
}

if (!function_exists('handrailBootData')) {
    /** The small object the boot script reads before first paint. */
    function handrailBootData()
    {
        global $userspice_nonce;
        $steps = [];
        $jsTools = [];
        foreach (handrailEnabledTools() as $id => $t) {
            $steps[$id] = count($t['steps']);
            if (!empty($t['js'])) {
                $jsTools[] = $id;
            }
        }
        $v = '?ver=' . HANDRAIL_US_VERSION;
        $data = [
            'tools' => (object) $steps,
            'jsTools' => $jsTools,
            'css' => [handrailCoreUrl('css/prefs.css' . $v), handrailUrl('pro/css/prefs-pro.css' . $v)],
            'js' => [handrailCoreUrl('js/prefs.js' . $v), handrailUrl('pro/js/pro-runtime.js' . $v)],
        ];
        if (!empty($userspice_nonce)) {
            $data['nonce'] = $userspice_nonce;
        }
        return $data;
    }
}

if (!function_exists('handrailConfigData')) {
    /** Everything the panel needs, printed just before button.js. */
    function handrailConfigData()
    {
        global $userspice_nonce;
        $s = handrailSettings();
        $v = '?ver=' . HANDRAIL_US_VERSION;

        $tools = [];
        foreach (handrailEnabledTools() as $id => $t) {
            $tools[] = [
                'id' => $id,
                'label' => $t['label'],
                'desc' => $t['desc'] ?? '',
                'steps' => array_values($t['steps']),
                'group' => $t['group'],
                'excludes' => array_values((array) ($t['excludes'] ?? [])),
            ];
        }
        $enabled = array_column($tools, 'id');

        $presets = [];
        foreach (handrailPresets() as $id => $p) {
            if (!in_array((string) $id, (array) $s['presets'], true)) {
                continue;
            }
            $map = [];
            foreach ($p['tools'] as $tool => $step) {
                if (in_array($tool, $enabled, true)) {
                    $map[$tool] = (int) $step;
                }
            }
            if ($map) {
                $presets[] = ['id' => (string) $id, 'label' => $p['label'], 'desc' => $p['desc'] ?? '', 'tools' => $map];
            }
        }

        $groups = [];
        foreach (handrailGroups() as $id => $label) {
            $groups[] = ['id' => $id, 'label' => $label];
        }

        $canReport = handrailCanReport();
        $canCheck = handrailCanCheck();
        $styles = handrailStyles();

        $strings = [
            'open' => $s['brand_button'] !== '' ? $s['brand_button'] : 'Accessibility',
            'credit' => 'Accessibility tools by Handrail',
            'readAloud' => 'Read aloud',
            'readAloudHint' => 'Click any text to hear it.',
            'readPage' => 'Read this page',
            'readStop' => 'Stop reading',
            'readNoVoices' => "Read aloud isn't available in this browser.",
        ];

        $config = [
            'version' => HANDRAIL_US_VERSION,
            'base' => handrailCoreUrl(),
            'style' => isset($styles[$s['style']]) ? $s['style'] : 'sheet',
            'icon' => handrailIcon(),
            'placement' => [
                'floating' => $s['floating'] ? [
                    'corner' => $s['corner'],
                    'offsetX' => (int) $s['offset_x'],
                    'offsetY' => (int) $s['offset_y'],
                    'size' => $s['size'],
                    'label' => $s['label'],
                    'hideOnMobile' => (bool) $s['hide_on_mobile'],
                ] : false,
            ],
            'mode' => ['report' => $canReport, 'check' => $canCheck],
            'tier' => handrailTier(),
            'tools' => $tools,
            'groups' => $groups,
            'presets' => $presets,
            'statementUrl' => handrailStatementUrl(),
            'credit' => $s['credit'] ? HANDRAIL_HOME : false,
            'strings' => $strings,
            'panelScripts' => [handrailUrl('pro/js/pro-runtime.js' . $v), handrailUrl('pro/js/pro-icons.js' . $v)],
        ];
        if (!empty($userspice_nonce)) {
            $config['nonce'] = $userspice_nonce;
        }

        $accent = handrailHex($s['brand_accent']);
        if ($accent) {
            $config['brand'] = ['accent' => $accent, 'accentText' => handrailTextOn($accent)];
        }
        if ($s['brand_title'] !== '') {
            $config['brand']['title'] = $s['brand_title'];
        }

        if ($canReport) {
            require_once __DIR__ . '/captcha.php';
            $report = [
                'url' => handrailUrl('assets/parsers/report.php'),
                'contact' => (bool) $s['report_contact'],
                'categories' => [],
                'captcha' => null,
                // UserSpice has no page caching, so every visitor can carry a CSRF token.
                'headers' => ['X-Handrail-Token' => Token::generate()],
            ];
            foreach (handrailReportCategories() as $id => $label) {
                $report['categories'][] = ['id' => $id, 'label' => $label];
            }
            global $user;
            if (!isset($user) || !$user->isLoggedIn()) {
                $keys = handrailCaptchaActive();
                if ($keys) {
                    $report['captcha'] = ['provider' => $keys['provider'], 'sitekey' => $keys['sitekey']];
                }
            }
            $config['report'] = $report;
        }

        if ($canCheck) {
            $tags = ['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa'];
            if ($s['check_wcag22']) {
                $tags[] = 'wcag22aa';
            }
            $config['check'] = [
                'tags' => $tags,
                'skip' => [],
                'owners' => handrailOwners(),
                'plain' => true,
                'strings' => [
                    'checkIntro' => 'Run an automated accessibility check of this page. Only people you allow under Handrail > Checker see this tab.',
                    'owner_content' => "This page's own file and what it prints. Fix these in the page.",
                    'owner_settings' => 'Site-wide pieces: your custom CSS, menus or the site logo.',
                    'owner_theme' => 'Comes from your template. Change it in the template settings, or override it from usersc/templates/.',
                    'owner_plugin' => 'Comes from this plugin. Check its settings or ask its developer.',
                    'owner_core' => 'Comes from UserSpice itself. Override it from usersc/ rather than editing users/, and tell the UserSpice team.',
                ],
            ];
        }

        return $config;
    }
}

if (!function_exists('handrailPluginNames')) {
    /** Folder => display name (from info.xml) for every active plugin. Only built for people who can check. */
    function handrailPluginNames()
    {
        global $abs_us_root, $us_url_root, $usplugins;
        $out = [];
        foreach ((array) ($usplugins ?? []) as $folder => $on) {
            if ((int) $on !== 1 || $folder === 'handrail' || !preg_match('/^[A-Za-z0-9_-]+$/', (string) $folder)) {
                continue;
            }
            $name = $folder;
            $xml = $abs_us_root . $us_url_root . 'usersc/plugins/' . $folder . '/info.xml';
            if (is_file($xml) && preg_match('#<name>\s*([^<]{1,80}?)\s*</name>#', (string) file_get_contents($xml), $m)) {
                $name = html_entity_decode($m[1], ENT_QUOTES | ENT_XML1, 'UTF-8');
            }
            $out[$folder] = $name;
        }
        return $out;
    }
}

if (!function_exists('handrailOwners')) {
    /**
     * The owner map: which stylesheet or markup belongs to the template, a plugin, UserSpice core,
     * the site's own custom code or the page itself. Paths are matched in order, most specific first.
     */
    function handrailOwners()
    {
        global $settings, $us_url_root;
        $tpl = isset($settings->template) ? (string) $settings->template : '';
        $themeLabel = $tpl !== '' ? 'Your template (' . $tpl . ')' : 'Your template';
        $paths = [];
        if ($tpl !== '') {
            // usersc/templates/{name}.css overrides everything the template does.
            $paths[] = ['p' => $us_url_root . 'usersc/templates/' . $tpl . '.css', 'type' => 'settings', 'label' => 'Your template override CSS (usersc/templates/' . $tpl . '.css)'];
            $paths[] = ['p' => $us_url_root . 'usersc/templates/' . $tpl . '/', 'type' => 'theme', 'label' => $themeLabel];
        }
        foreach (handrailPluginNames() as $folder => $name) {
            $paths[] = ['p' => $us_url_root . 'usersc/plugins/' . $folder . '/', 'type' => 'plugin', 'label' => 'Plugin: ' . $name];
        }
        $paths[] = ['p' => $us_url_root . 'usersc/', 'type' => 'settings', 'label' => 'Your custom code (usersc/)'];
        $paths[] = ['p' => $us_url_root . 'users/', 'type' => 'core', 'label' => 'UserSpice core'];

        // Templates open each page's own output with container_open.php, which almost always uses
        // the container class from the settings. Only containers right under <body> count, so a
        // container inside the footer or menu doesn't.
        $content = ['main'];
        foreach (array_unique(['container-fluid', 'container', strtok(trim((string) ($settings->container_open_class ?? '')), ' ')]) as $cls) {
            if (is_string($cls) && preg_match('/^[A-Za-z0-9_-]+$/', $cls)) {
                $content[] = 'body > .' . $cls;
                $content[] = 'body > div:not([class]) > .' . $cls;
            }
        }

        return [
            'handles' => new stdClass(),
            'ids' => new stdClass(),
            'paths' => $paths,
            'selectors' => [
                ['s' => '.us_menu, .navbar', 'type' => 'settings', 'label' => 'Menus (UltraMenu)'],
                ['s' => 'footer, #footer', 'type' => 'theme', 'label' => $themeLabel],
            ],
            'content' => $content,
            'contentLabel' => 'This page',
            'fallback' => ['type' => 'content', 'label' => 'This page'],
        ];
    }
}

if (!function_exists('handrailJson')) {
    /** JSON that is safe inside a <script> element. */
    function handrailJson($value)
    {
        return json_encode($value, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);
    }
}
