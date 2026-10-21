<?php
if (count(get_included_files()) === 1) { exit('Direct access not permitted.'); }
// The config object and the trigger script (the floating button and #handrail links).
// The panel itself loads only when someone opens it.
require_once $abs_us_root . $us_url_root . 'usersc/plugins/handrail/assets/includes/frontend.php';
if (handrailActive()) {
    $hrNonce = safeReturn($userspice_nonce ?? '');
    echo '<script nonce="' . $hrNonce . '">window.HandrailConfig=' . handrailJson(handrailConfigData()) . ";</script>\n";
    echo '<script nonce="' . $hrNonce . '" defer src="' . safeReturn(handrailCoreUrl('js/button.js?ver=' . HANDRAIL_US_VERSION)) . "\"></script>\n";
}
