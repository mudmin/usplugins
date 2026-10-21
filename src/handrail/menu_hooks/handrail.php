<?php
if (count(get_included_files()) === 1) { exit('Direct access not permitted.'); }
// UltraMenu snippet: an "Accessibility" item that opens the Handrail panel. When editing a menu
// item, set its type to Snippet and pick this file. It prints one line, as UltraMenu requires.
global $abs_us_root, $us_url_root;
if (!pluginActive('handrail', true)) {
    return;
}
if (!function_exists('handrailTriggerUrl')) {
    require_once $abs_us_root . $us_url_root . 'usersc/plugins/handrail/assets/includes/settings.php';
}
echo '<li class="nav-item"><a class="nav-link" href="' . safeReturn(handrailTriggerUrl()) . '"><i class="fa fa-universal-access" aria-hidden="true"></i> Accessibility</a></li>';
