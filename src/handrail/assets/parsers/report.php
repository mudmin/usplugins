<?php
// POST a barrier report (JSON) from the panel. Answers in JSON.
// Walk up from the requested path (not __DIR__, which resolves symlinks) to the site root.
$hrDir = dirname((string) $_SERVER['SCRIPT_FILENAME']);
while (!is_file($hrDir . '/users/init.php') && dirname($hrDir) !== $hrDir) {
    $hrDir = dirname($hrDir);
}
require_once $hrDir . '/users/init.php';
require_once $abs_us_root . $us_url_root . 'usersc/plugins/handrail/assets/includes/reports.php';
require_once $abs_us_root . $us_url_root . 'usersc/plugins/handrail/assets/includes/captcha.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function handrailRespond($status, $body)
{
    http_response_code($status);
    echo json_encode($body);
    exit;
}

if (Server::get('REQUEST_METHOD') !== 'POST' || !pluginActive('handrail', true)) {
    handrailRespond(404, ['message' => 'Not found.']);
}
if (!handrailCanReport()) {
    handrailRespond(403, ['message' => 'Reports are not open on this site.']);
}
// Every page that shows the form carries this session's token (UserSpice has no page cache).
if (!Token::check((string) ($_SERVER['HTTP_X_HANDRAIL_TOKEN'] ?? ''))) {
    handrailRespond(403, ['message' => 'This page has been open a long time. Reload it and send your report again.']);
}
$hrRaw = file_get_contents('php://input', false, null, 0, 65536);
$hrPost = json_decode((string) $hrRaw, true);
if (!is_array($hrPost)) {
    handrailRespond(400, ['message' => 'The report could not be read.']);
}

// Bots: filled the hidden field, or submitted faster than a person can (or didn't say).
// The panel waits out the minimum itself, so a real visitor never lands here.
if (!empty($hrPost['website']) || !isset($hrPost['elapsed']) || !is_numeric($hrPost['elapsed']) || (int) $hrPost['elapsed'] < HANDRAIL_MIN_SECONDS) {
    handrailRespond(200, ['ok' => true]);
}

$hrUserId = $user->isLoggedIn() ? (int) $user->data()->id : 0;
if (!$hrUserId) {
    $hrKeys = handrailCaptchaActive();
    if ($hrKeys) {
        $hrOk = handrailCaptchaVerify($hrKeys, isset($hrPost['captcha_token']) && is_string($hrPost['captcha_token']) ? $hrPost['captcha_token'] : '');
        if ($hrOk !== true) {
            handrailRespond(400, ['message' => $hrOk]);
        }
    }
}

$hrData = handrailCleanReport($hrPost);
if (is_string($hrData)) {
    handrailRespond(400, ['message' => $hrData]);
}
$hrResult = handrailSaveReport($hrData, $hrUserId);
if (isset($hrResult['error'])) {
    handrailRespond($hrResult['status'], ['message' => $hrResult['error']]);
}
if (isset($hrResult['id'])) {
    // Email outside the lock: a slow mail server mustn't make the next visitor's report wait.
    handrailNotify($hrResult['id']);
    handrailMaybePurge();
    handrailRespond(201, ['ok' => true]);
}
handrailRespond(200, ['ok' => true]);
