<?php
//rename this file to override.php to override the existing email function
function email($to, $subject, $body, $opts = [], $attachment = null)
{
    $brevo_opts = [];
    if (isset($opts['replyTo']))    { $brevo_opts['reply']      = $opts['replyTo']; }
    if (isset($opts['email']))      { $brevo_opts['from']       = $opts['email']; }
    if (isset($opts['name']))       { $brevo_opts['from_name']  = $opts['name']; }
    if (isset($opts['reply_name'])) { $brevo_opts['reply_name'] = $opts['reply_name']; }
    if (isset($opts['altBody']))    { $brevo_opts['textContent'] = $opts['altBody']; }
    // Brevo tags: a string or an array of strings. Untagged mail follows the
    // plugin's "Tag untagged mail" setting.
    if (isset($opts['tags']))       { $brevo_opts['tags']       = $opts['tags']; }

    // If the attachment can't be read, it is dropped silently and the
    // email still sends. This intentionally differs from core email():
    // core fails the whole send on a bad attachment path. For an email
    // path (password resets, etc.) delivering the message matters more
    // than failing over a stale file path.
    // Core treats any falsy $attachment (null, false, '') as "no attachment".
    if (is_string($attachment) && $attachment !== '') {
        $content = @file_get_contents($attachment);
        if ($content !== false) {
            $brevo_opts['attachments'] = [
                ['content' => base64_encode($content), 'name' => basename($attachment)]
            ];
        }
    }

    return sendinblue($to, $subject, $body, '', $brevo_opts);
}
