<?php
//Include V3 invisible recaptcha on all pages for score accuracy
//If you would like to hide this badge see here: https://developers.google.com/recaptcha/docs/faq#id-like-to-hide-the-recaptcha-badge.-what-is-allowed
if ($settings->recap_version == 3 && $settings->recap_global == 1) {
    echo '<script src="https://www.google.com/recaptcha/api.js" async defer></script>';

    // Hide the reCAPTCHA badge if option is enabled
    if ($settings->recap_hide_badge == 1) {
        echo '<style>.grecaptcha-badge { visibility: hidden; }</style>';
    }
}