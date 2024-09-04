<?php
//rename this file to override.php to override the existing email function
function email($to, $subject, $body, $to_name = "", $options = []){
    return sendgrid($to, $subject, $body, $to_name, $options);
}