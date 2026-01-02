<?php
// Time picker - now uses HTML5 native time input
// The input type="time" is set in formField() in functions.php
// This file is kept for backwards compatibility with any custom implementations
?>
<script>
(function() {
    // HTML5 time inputs handle time format natively (HH:MM or HH:MM:SS)
    // This script ensures proper fallback behavior if needed
    var timeInput = document.getElementById('<?=$o->col?>');
    if (timeInput && timeInput.type !== 'time') {
        // Fallback for browsers that don't support time input
        timeInput.placeholder = 'HH:MM';
    }
})();
</script>
