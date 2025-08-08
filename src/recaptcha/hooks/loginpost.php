<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted ?>

<?php
global $validation;
global $settings;
if ($settings->recaptcha == 1) {
    if(!verifyCaptcha()) {
        $str = lang("ERR_CAP");
        $validation->addError(["reCAPTCHA $str","g-recaptcha-response"]);
    }
}
?>