<?php
// DateTime picker - now uses HTML5 native datetime-local input
// The input type="datetime-local" is set in formField() in functions.php
// This file is kept for backwards compatibility with any custom implementations
// Reuse core's nonce if present; otherwise self-provide one (older UserSpice).
if (!isset($GLOBALS['userspice_nonce'])) {
    $GLOBALS['userspice_nonce'] = base64_encode(random_bytes(16));
}
?>
<script nonce="<?= htmlspecialchars($GLOBALS['userspice_nonce'] ?? '') ?>">
(function() {
    // HTML5 datetime-local inputs handle datetime format natively
    // This script ensures proper fallback behavior if needed
    var datetimeInput = document.getElementById('<?=$o->col?>');
    if (datetimeInput && datetimeInput.type !== 'datetime-local') {
        // Fallback for browsers that don't support datetime-local input
        datetimeInput.placeholder = 'YYYY-MM-DDTHH:MM';
    }
})();
</script>
