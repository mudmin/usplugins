<?php
//rename this file to override.php to override the existing email function
function email($to, $subject, $body, $opts = [], $attachment = null)
{
    $brevo_opts = [];
    if (isset($opts['replyTo']))    { $brevo_opts['reply']      = $opts['replyTo']; }
    if (isset($opts['email']))      { $brevo_opts['from']       = $opts['email']; }
    if (isset($opts['name']))       { $brevo_opts['from_name']  = $opts['name']; }
    if (isset($opts['reply_name'])) { $brevo_opts['reply_name'] = $opts['reply_name']; }

    // If the attachment can't be read, it is dropped silently and the
    // email still sends. This intentionally differs from core email():
    // core fails the whole send on a bad attachment path. For an email
    // path (password resets, etc.) delivering the message matters more
    // than failing over a stale file path.
    if ($attachment !== null) {
        $content = @file_get_contents($attachment);
        if ($content !== false) {
            $brevo_opts['attachments'] = [
                ['content' => base64_encode($content), 'name' => basename($attachment)]
            ];
        }
    }

    return sendinblue($to, $subject, $body, '', $brevo_opts);
}
