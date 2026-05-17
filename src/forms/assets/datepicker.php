<?php
// Date picker - now uses HTML5 native date input
// The input type="date" is set in formField() in functions.php
// This file is kept for backwards compatibility with any custom implementations
// Reuse core's nonce if present; otherwise self-provide one (older UserSpice).
if (!isset($GLOBALS['userspice_nonce'])) {
    $GLOBALS['userspice_nonce'] = base64_encode(random_bytes(16));
}
?>
<script nonce="<?= htmlspecialchars($GLOBALS['userspice_nonce'] ?? '') ?>">
(function() {
    // HTML5 date inputs handle date format natively (YYYY-MM-DD)
    // This script ensures proper fallback behavior if needed
    var dateInput = document.getElementById('<?=$o->col?>');
    if (dateInput && dateInput.type !== 'date') {
        // Fallback for browsers that don't support date input
        dateInput.placeholder = 'YYYY-MM-DD';
    }
})();
</script>
