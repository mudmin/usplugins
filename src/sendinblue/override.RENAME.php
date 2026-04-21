<?php
//rename this file to override.php to override the existing email function
function email($to, $subject, $body, $opts = [], $attachment = null)
{
    $brevo_opts = [];
    if (isset($opts['replyTo'])) { $brevo_opts['reply']     = $opts['replyTo']; }
    if (isset($opts['email']))   { $brevo_opts['from']      = $opts['email']; }
    if (isset($opts['name']))    { $brevo_opts['from_name'] = $opts['name']; }
    return sendinblue($to, $subject, $body, '', $brevo_opts);
}
