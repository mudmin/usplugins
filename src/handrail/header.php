<?php
if (count(get_included_files()) === 1) { exit('Direct access not permitted.'); }
// Runs at the top of <body> on every page. Applies a visitor's saved adjustments before
// anything paints; visitors with nothing saved get a few hundred bytes and no requests.
require_once $abs_us_root . $us_url_root . 'usersc/plugins/handrail/assets/includes/frontend.php';
if (handrailActive()) {
    echo '<script id="handrail-boot" nonce="' . safeReturn($userspice_nonce ?? '') . '">window.HandrailBoot=' . handrailJson(handrailBootData()) . ";\n"
        . file_get_contents($abs_us_root . $us_url_root . 'usersc/plugins/handrail/core/js/boot.js') . "</script>\n";
}
