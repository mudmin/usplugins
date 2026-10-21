<?php
if (count(get_included_files()) === 1) { exit('Direct access not permitted.'); }
// Loaded on every page while Handrail is active. It only defines functions; nothing queries
// until header.php or footer.php asks for the settings.
require_once $abs_us_root . $us_url_root . 'usersc/plugins/handrail/assets/includes/settings.php';

if (!function_exists('handrailLink')) {
    /**
     * Print a link that opens the panel, for a template footer or menu. Without JavaScript it
     * goes to the accessibility statement. $tab can be 'report' or 'check' to open that tab.
     */
    function handrailLink($text = 'Accessibility', $class = '', $tab = '')
    {
        $tab = in_array($tab, ['adjust', 'report', 'check'], true) ? $tab : '';
        echo '<a href="' . safeReturn(handrailTriggerUrl($tab)) . '"' . ($class !== '' ? ' class="' . safeReturn($class) . '"' : '') . '>' . safeReturn($text) . '</a>';
    }
}

if (!function_exists('handrailStatement')) {
    /** The accessibility statement as HTML, built from Handrail > Statement. */
    function handrailStatement()
    {
        global $abs_us_root, $us_url_root;
        require_once $abs_us_root . $us_url_root . 'usersc/plugins/handrail/assets/includes/statement.php';
        return handrailStatementHtml();
    }
}
