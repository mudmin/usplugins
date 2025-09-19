<?php
//Include reCAPTCHA v3 for site-wide scoring or form-only usage
if ($settings->recap_version == 3) {
    $v3_mode = isset($settings->recap_v3_mode) ? $settings->recap_v3_mode : 'form';
    $hide_badge = isset($settings->recap_hide_badge) && $settings->recap_hide_badge == 1;

    if ($v3_mode == 'sitewide') {
        // Site-wide mode: Load script and execute on every page for background scoring
        echo '<script src="https://www.google.com/recaptcha/api.js?render=' . $settings->recap_public . '"></script>';
        ?>
        <script>
        grecaptcha.ready(function() {
            // Execute reCAPTCHA for general page scoring
            grecaptcha.execute('<?php echo $settings->recap_public; ?>', {action: 'page_view'}).then(function(token) {
                // Store token for potential form use later
                window.recaptchaToken = token;
                window.recaptchaTimestamp = Date.now();
            });
        });
        </script>
        <?php

        // Hide badge if requested
        if ($hide_badge) {
            echo '<style>.grecaptcha-badge { visibility: hidden; }</style>';
        }
    } else {
        // Form-only mode: Just load the script, don't execute
        echo '<script src="https://www.google.com/recaptcha/api.js?render=' . $settings->recap_public . '"></script>';

        // Hide badge if requested (for invisible forms)
        if ($hide_badge) {
            echo '<style>.grecaptcha-badge { visibility: hidden; }</style>';
        }
    }
}