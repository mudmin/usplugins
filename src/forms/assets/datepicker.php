<?php
// Date picker - now uses HTML5 native date input
// The input type="date" is set in formField() in functions.php
// This file is kept for backwards compatibility with any custom implementations
?>
<script>
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
