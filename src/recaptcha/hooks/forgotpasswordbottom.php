<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted ?>

<?php
global $settings;
if ($settings->recaptcha == 1) {
    addCaptcha('pwReset');
}
?>