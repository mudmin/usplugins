<?php

if(!function_exists('verifyCaptcha')) {
    function verifyCaptcha($array = false) {
        require_once dirname(__FILE__) . '/assets/recaptcha/src/autoload.php';
        $db = DB::getInstance();
        $gRecaptchaResponse = Input::get('g-recaptcha-response');
        $recap = $db->query("SELECT recap_private, recap_version, recap_v3_threshold from settings")->first();
        $secret = $recap->recap_private;
        $version = $recap->recap_version;
        $threshold = isset($recap->recap_v3_threshold) ? (float)$recap->recap_v3_threshold : 0.5;

        $recaptcha = new \ReCaptcha\ReCaptcha($secret);
        $resp = $recaptcha->verify($gRecaptchaResponse, $_SERVER['REMOTE_ADDR']);

        if ($array) {
            return $resp->toArray();
        }

        // For v3, check score against threshold
        if ($version == 3) {
            $response_array = $resp->toArray();
            if ($resp->isSuccess() && isset($response_array['score'])) {
                return $response_array['score'] >= $threshold;
            }
            return false;
        }

        // For v2, just return success status
        return $resp->isSuccess();
    }
}



if(!function_exists('addCaptcha')) {
    function addCaptcha($formName, $action = 'form_submit') {
        $db = DB::getInstance();
        $recaptcha = $db->query("SELECT recap_version, recap_public, recap_type, recap_v3_mode from settings")->first();
        $version = $recaptcha->recap_version;
        $siteKey = $recaptcha->recap_public;
        $type = $recaptcha->recap_type;
        $v3_mode = isset($recaptcha->recap_v3_mode) ? $recaptcha->recap_v3_mode : 'form';

        if ($version == 2 && $type == 1) {
            // v2 Checkbox
            ?>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script>
$('#<?=$formName?>').find('[type="submit"]').before(
    '<div class="g-recaptcha" data-sitekey="<?=$siteKey?>" style="padding-bottom: 10px;"></div>'
);
</script>
<?php
        }
        elseif ($version == 2 && $type == 2) {
            // v2 Invisible
            ?>
<script src="https://www.google.com/recaptcha/api.js"></script>
<script>
$('#<?=$formName?>').find('[type="submit"]').before(
    '<div class="g-recaptcha" data-sitekey="<?=$siteKey?>" data-size="invisible" data-callback="recaptchaCompleted_<?=$formName?>"></div>'
);
$('#<?=$formName?>').submit(function(event) {
    if (!grecaptcha.getResponse()) {
        event.preventDefault();
        grecaptcha.execute();
    }
});
window.recaptchaCompleted_<?=$formName?> = function() {
    var submitName = $('#<?=$formName?>').find('[type="submit"]').attr('name');
    var submitValue = $('#<?=$formName?>').find('[type="submit"]').attr('value');
    if (submitName !== undefined) {
        $('#<?=$formName?>').find('[type="submit"]').before(`<input type="hidden" name="${submitName}" value="${submitValue}" />`);
    }
    $('#<?=$formName?>').submit();
}
</script>
<?php
        }
        elseif ($version == 3) {
            // v3 handling
            if ($v3_mode == 'sitewide') {
                // Site-wide mode: Use existing token or get new one
                ?>
<script>
$('#<?=$formName?>').submit(function(event) {
    var form = this;

    // Check if we have a recent token (less than 2 minutes old)
    if (window.recaptchaToken && window.recaptchaTimestamp &&
        (Date.now() - window.recaptchaTimestamp) < 120000) {
        // Use existing token
        if (!$('#<?=$formName?>').find('input[name="g-recaptcha-response"]').length) {
            $('#<?=$formName?>').find('[type="submit"]').before(
                '<input type="hidden" name="g-recaptcha-response" value="' + window.recaptchaToken + '">'
            );
        }
        return true;
    } else {
        // Get new token
        event.preventDefault();
        grecaptcha.ready(function() {
            grecaptcha.execute('<?=$siteKey?>', {action: '<?=$action?>'}).then(function(token) {
                $('#<?=$formName?>').find('input[name="g-recaptcha-response"]').remove();
                $('#<?=$formName?>').find('[type="submit"]').before(
                    '<input type="hidden" name="g-recaptcha-response" value="' + token + '">'
                );
                form.submit();
            });
        });
        return false;
    }
});
</script>
<?php
            } else {
                // Form-only mode: Generate token on form submission
                ?>
<script>
$('#<?=$formName?>').submit(function(event) {
    var form = this;
    if (!$('#<?=$formName?>').find('input[name="g-recaptcha-response"]').val()) {
        event.preventDefault();
        grecaptcha.ready(function() {
            grecaptcha.execute('<?=$siteKey?>', {action: '<?=$action?>'}).then(function(token) {
                $('#<?=$formName?>').find('input[name="g-recaptcha-response"]').remove();
                $('#<?=$formName?>').find('[type="submit"]').before(
                    '<input type="hidden" name="g-recaptcha-response" value="' + token + '">'
                );
                form.submit();
            });
        });
    }
});
</script>
<?php
            }
        }
    }
}