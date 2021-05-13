<?php

if(!function_exists('verifyCaptcha')) {
    function verifyCaptcha($array = false) {
        require_once dirname(__FILE__) . '/assets/recaptcha/src/autoload.php';
        $db = DB::getInstance();
        $gRecaptchaResponse = $_POST['g-recaptcha-response'];
        $recap = $db->query("SELECT recap_private from settings")->first();
        $secret = $recap->recap_private;
        $recaptcha = new \ReCaptcha\ReCaptcha($secret);
        $resp = $recaptcha->verify($gRecaptchaResponse, $_SERVER['REMOTE_ADDR']);
        return $array ? $resp->toArray() : $resp->isSuccess();
    }
}



if(!function_exists('addCaptcha')) {
    function addCaptcha($formName) {
        $db = DB::getInstance();
        $recaptcha = $db->query("SELECT recap_version, recap_public, recap_type from settings")->first();
        $version = $recaptcha->recap_version;
        $siteKey = $recaptcha->recap_public;
        $type = $recaptcha->recap_type;
        if ($version == 2 && $type == 1) {
            ?>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script>
$('#<?=$formName?>').find('[type="submit"]').before(
    '<div class="g-recaptcha" data-sitekey="<?=$siteKey?>" style="padding-bottom: 10px;"></div>'
);
</script>
<?php
}
elseif ($version == 3 || ($version == 2 && $type == 2)) {
?>
<script src="https://www.google.com/recaptcha/api.js"></script>
<script>
$('#<?=$formName?>').find('[type="submit"]').before(
    '<div class="g-recaptcha" data-sitekey="<?=$siteKey?>" data-size="invisible" data-callback="recaptchaCompleted"></div>'
);
$('#<?=$formName?>').submit(function(event) {
    if (!grecaptcha.getResponse()) {
        event.preventDefault();
        grecaptcha.execute();
    }
});
recaptchaCompleted = function() {
    $('#<?=$formName?>').submit();
}
</script>
<?php
        }
        else {
            echo 'NO VALID OPTIONS';
        }
    }
}