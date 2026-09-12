<?php
// CLI-only integration tests. Requires a disposable MariaDB socket; never loads site credentials/init.php.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$testSocket = $argv[1] ?? '';
if (!preg_match('#^/tmp/turnstile-test\.[a-zA-Z0-9]+/mysql\.sock$#D', $testSocket) || !file_exists($testSocket)) {
    exit("Usage: php tests/run.php /tmp/turnstile-test.XXXXXX/mysql.sock [--live|--reset-reject|--save|--passwordless-reject]\n");
}
$testRoot = dirname(__DIR__, 4);
$testMode = $argv[2] ?? '';
$testDatabase = 'turnstile_test_' . bin2hex(random_bytes(6));
$testPdo = new PDO('mysql:unix_socket=' . $testSocket, 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$testPdo->exec('CREATE DATABASE `' . $testDatabase . '`');
$testBeforeCleanup = null;
register_shutdown_function(function () use ($testPdo, $testDatabase, &$testBeforeCleanup) {
    try { if ($testBeforeCleanup) $testBeforeCleanup(); }
    finally { $testPdo->exec('DROP DATABASE `' . $testDatabase . '`'); }
});
// Isolated framework bootstrap: all globals below belong only to this CLI process.
$GLOBALS['config'] = ['mysql' => ['host' => 'localhost;unix_socket=' . $testSocket, 'db' => $testDatabase, 'username' => 'root', 'password' => ''], 'session' => ['token_name' => 'csrf']];
foreach (['Config', 'DB', 'Input', 'Server', 'Session', 'Token', 'Validate', 'Redirect'] as $testClass) {
    if ($testClass === 'Redirect' && $testMode === '--passwordless-reject') { continue; }
    require $testRoot . '/users/classes/' . $testClass . '.php';
}
if ($testMode === '--passwordless-reject') {
    // Capture the redirect destination while retaining its terminating behavior.
    class Redirect {
        public static function to($location = null, $args = '') {
            $GLOBALS['testRedirect'] = $location . $args;
            exit;
        }
    }
}
// Exercise web request semantics; Server otherwise derives CLI paths from the test script.
(new ReflectionProperty(Server::class, 'isCli'))->setValue(null, false);
function logger($user_id = '', $logtype = '', $lognote = '', $metadata = null) {}
function usSuccess($message) {}
require $testRoot . '/users/helpers/us_helpers.php';
$db = DB::getInstance();
$abs_us_root = $testRoot;
$us_url_root = '/';
$usplugins = ['turnstile' => 1];
$master_account = [1];
$user = new class {
    public function isLoggedIn() { return true; }
    public function data() { return (object) ['id' => 1]; }
};
$_SESSION = [];
$userspice_nonce = 'test-nonce';
$currentPage = 'admin.php';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['PHP_SELF'] = '/users/admin.php';
$testResponse = ['success' => true, 'action' => 'contact', 'hostname' => 'example.com'];
$testCalls = 0;
if ($testMode !== '--live') {
    function plgTurnstileSiteverify($secret, $token) {
        global $testResponse, $testCalls;
        ++$testCalls;
        if ($testResponse instanceof Throwable) { throw $testResponse; }
        return $testResponse;
    }
}
$testAssertions = 0;
function testExpect($condition, $message) {
    global $testAssertions;
    ++$testAssertions;
    if (!$condition) { throw new RuntimeException('FAIL: ' . $message); }
}
$db->query("CREATE TABLE us_plugins (id int NOT NULL AUTO_INCREMENT PRIMARY KEY, plugin varchar(100), status varchar(30), updates text, last_check datetime NULL)");
$db->query("CREATE TABLE us_plugin_hooks (id int NOT NULL AUTO_INCREMENT PRIMARY KEY, page varchar(100), folder varchar(100), position varchar(30), hook varchar(255), disabled tinyint NOT NULL DEFAULT 0)");
$testPlugin = $testRoot . '/usersc/plugins/turnstile/';
require $testPlugin . 'install.php';
require $testPlugin . 'activate.php';
require $testPlugin . 'migrate.php';
testExpect($db->query('SELECT * FROM plg_turnstile_settings')->count() === 1, 'migration is idempotent');
testExpect($db->query('SELECT * FROM us_plugin_hooks')->count() === 0, 'fresh install opts out of built-in forms');
testExpect(!plgTurnstileConfigured() && !checkTurnstile('token'), 'missing keys reject verification');
$db->update('plg_turnstile_settings', 1, ['site_key' => '1x00000000000000000000AA', 'secret_key' => '1x0000000000000000000000000000000AA']);
plgTurnstileSettings(true);

if ($testMode === '--live') {
    foreach (['1' => true, '2' => false, '3' => false] as $testPrefix => $testExpected) {
        $db->update('plg_turnstile_settings', 1, ['secret_key' => $testPrefix . 'x0000000000000000000000000000000AA']);
        plgTurnstileSettings(true);
        testExpect(checkTurnstile('XXXX.DUMMY.TOKEN.XXXX') === $testExpected, 'Cloudflare test secret ' . $testPrefix);
    }
    echo "PASS: live Cloudflare success, failure, and spent-token checks.\n";
    exit;
}

// Simulate an existing installation at 00001 with saved keys and selections.
$db->update('plg_turnstile_settings', 1, ['protect_login' => 1]);
$db->query('ALTER TABLE plg_turnstile_settings DROP COLUMN protect_passwordless');
$db->query('UPDATE us_plugins SET updates = ? WHERE plugin = ?', ['["00001"]', 'turnstile']);
plgTurnstileSettings(true);
testExpect(!plgTurnstileEnabled('passwordless'), 'unmigrated installations default passwordless protection off');
if ($testMode !== '--save') {
    require $testPlugin . 'migrate.php';
    require $testPlugin . 'migrate.php';
    testExpect((int) plgTurnstileSettings()->protect_passwordless === 0, 'upgrade defaults passwordless protection off');
    testExpect(plgTurnstileSettings()->secret_key === '1x0000000000000000000000000000000AA' && plgTurnstileEnabled('login'), 'upgrade preserves keys and selections');
    testExpect($db->query("SHOW COLUMNS FROM plg_turnstile_settings LIKE 'protect_passwordless'")->count() === 1, 'upgrade is idempotent');
}

$testEnabled = ['protect_login' => 1, 'protect_join' => 1, 'protect_forgot_password' => 1, 'protect_reset_password' => 1];
if ($testMode !== '--save') { $testEnabled['protect_passwordless'] = 1; }
$db->update('plg_turnstile_settings', 1, $testEnabled);
plgTurnstileSyncHooks(plgTurnstileSettings(true));
plgTurnstileSyncHooks(plgTurnstileSettings());
testExpect($db->query('SELECT * FROM us_plugin_hooks WHERE disabled = 0')->count() === ($testMode === '--save' ? 7 : 9), 'hook registration is idempotent');

if ($testMode === '--passwordless-reject') {
    $testCase = $argv[3] ?? 'missing';
    $testFolder = $argv[4] ?? 'usersc';
    testExpect(in_array($testFolder, ['users', 'usersc'], true), 'known route folder');
    $currentPage = 'passwordless.php';
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['PHP_SELF'] = '/' . $testFolder . '/passwordless.php';
    $_POST = ['method' => 'enter_email', 'email' => 'test@example.invalid'];
    $_GET = [];
    if ($testCase === 'omitted') { unset($_POST['method']); }
    if ($testCase === 'empty') { $_POST['method'] = ''; }
    if ($testCase === 'query-token') { $_GET['cf-turnstile-response'] = 'token'; }
    if (in_array($testCase, ['rejected', 'wrong-action'], true)) {
        $_POST['cf-turnstile-response'] = 'token';
        $testResponse = ['success' => $testCase === 'wrong-action', 'action' => 'contact', 'hostname' => 'example.com'];
    }
    $testContinued = false;
    $testBeforeCleanup = function () use (&$testContinued, $testFolder, $testCase) {
        testExpect(!$testContinued, 'rejected passwordless request never reaches email generation');
        testExpect(($GLOBALS['testRedirect'] ?? '') === '/' . $testFolder . '/passwordless.php', 'passwordless retry preserves route folder');
        testExpect(strpos(json_encode($_SESSION), 'Please complete the security check') !== false, 'passwordless failure sets a visible error');
        echo 'PASS: passwordless rejection (' . $testCase . ', ' . $testFolder . ").\n";
    };
    includeHook(getMyHooks(), 'post');
    $testContinued = true;
    exit;
}

if ($testMode === '--reset-reject') {
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['PHP_SELF'] = '/usersc/forgot_password_reset.php';
    $_POST = ['resetPassword' => 'Reset', 'email' => 'a+b@example.com', 'vericode' => 'abc<def', 'user_id' => '7'];
    $testContinued = false;
    ob_start();
    $testBeforeCleanup = function () use (&$testContinued) {
        $testHtml = ob_get_clean();
        testExpect(!$testContinued, 'rejected reset never continues to password update');
        testExpect(http_response_code() === 403, 'reset rejects with 403');
        testExpect(strpos($testHtml, '/usersc/forgot_password_reset.php?reset=1') !== false, 'reset preserves usersc route');
        testExpect(strpos($testHtml, 'a%2Bb%40example.com') !== false && strpos($testHtml, 'abc%3Cdef') !== false, 'retry link encodes context');
        echo "PASS: reset rejection stops processing and preserves safe retry context.\n";
    };
    includeHook(getMyHooks(['page' => 'forgotPasswordResponse']), 'body');
    $testContinued = true;
    exit;
}
if ($testMode === '--save') {
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_POST = ['csrf' => 'invalid', 'turnstile_action' => 'save'];
    ob_start();
    require $testPlugin . 'configure.php';
    ob_end_clean();
    testExpect($db->query("SHOW COLUMNS FROM plg_turnstile_settings LIKE 'protect_passwordless'")->count() === 0, 'invalid CSRF cannot run pending migration');
    $_POST = ['csrf' => Token::generate(), 'turnstile_action' => 'save', 'site_key' => 'new-site-key', 'secret_key' => '', 'theme' => 'dark', 'size' => 'compact', 'hostnames' => 'Example.COM, www.example.com.', 'protect_login' => '1', 'protect_passwordless' => '1'];
    ob_start();
    $testBeforeCleanup = function () use ($db) {
        ob_end_clean();
        $testSaved = $db->query('SELECT * FROM plg_turnstile_settings WHERE id = 1')->first();
        testExpect($testSaved->site_key === 'new-site-key' && $testSaved->secret_key === '1x0000000000000000000000000000000AA', 'blank secret preserves saved key');
        testExpect($testSaved->hostnames === "example.com\nwww.example.com", 'hostname normalization');
        testExpect((int) $testSaved->protect_passwordless === 1, 'save migrates and enables passwordless selection');
        testExpect($db->query('SELECT * FROM us_plugin_hooks WHERE disabled = 0')->count() === 4, 'save synchronizes selected hooks');
        echo "PASS: configuration saves keys and selected hooks.\n";
    };
    require $testPlugin . 'configure.php';
    exit;
}

foreach ([null, false, [], ['success' => false], ['success' => 'true'], new RuntimeException('network failure')] as $testBadResponse) {
    $testResponse = $testBadResponse;
    testExpect(!checkTurnstile('token'), 'malformed, failed or unavailable responses reject');
}
$testResponse = ['success' => true, 'action' => 'contact', 'hostname' => 'example.com'];
foreach (['', ' ', [], str_repeat('a', 2049)] as $testBadToken) {
    $testBefore = $testCalls;
    testExpect(!checkTurnstile($testBadToken), 'malformed tokens reject');
    testExpect($testCalls === $testBefore, 'malformed tokens never make a network request');
}
$_GET = ['cf-turnstile-response' => 'query-token'];
$_POST = [];
testExpect(!checkTurnstile(), 'query-only tokens are rejected');
$_GET = [];
$_POST = ['cf-turnstile-response' => 'post-token'];
testExpect(checkTurnstile(null, 'contact', 'EXAMPLE.COM'), 'valid POST token/action/hostname passes');
testExpect(!checkTurnstile(null, 'login') && !checkTurnstile(null, 'contact', 'evil.example'), 'action and hostname mismatch reject');
$db->update('plg_turnstile_settings', 1, ['hostnames' => "www.example.com\nexample.com"]);
plgTurnstileSettings(true);
testExpect(checkTurnstile('token', 'contact'), 'configured hostnames permit exact match');
$testResponse['hostname'] = 'evil.example.com';
testExpect(!checkTurnstile('token', 'contact'), 'configured hostnames reject unlisted subdomain');
$testResponse['hostname'] = 'example.com';

foreach (['login.php' => 'post', 'join.php' => 'body', 'forgot_password.php' => 'post'] as $testPage => $testPosition) {
    foreach (['/users/', '/usersc/'] as $testFolder) {
        $currentPage = $testPage;
        $_SERVER['PHP_SELF'] = $testFolder . $testPage;
        $_SERVER['REQUEST_METHOD'] = 'POST';
        (new ReflectionProperty(Server::class, 'cache'))->setValue(null, []);
        $validation = new Validate();
        $validated = true;
        $hookData = [];
        $_POST = [];
        $testHooks = getMyHooks($testPage === 'join.php' ? ['page' => 'joinAttempt'] : []);
        includeHook($testHooks, $testPosition);
        testExpect(!$validation->passed(), $testFolder . $testPage . ' invalid challenge blocks validation');
        if ($testPage === 'login.php') testExpect($validated === false, 'login cached result is invalidated');
        if ($testPage === 'forgot_password.php') testExpect($hookData['validation'] === $validation, 'forgot password passes validation back to caller');
        $validation = new Validate();
        $validated = true;
        $_POST = ['cf-turnstile-response' => 'token'];
        $testResponse['action'] = str_replace('.php', '', $testPage);
        includeHook($testHooks, $testPosition);
        testExpect($validation->passed() && $validated, $testFolder . $testPage . ' verified challenge preserves validation');
    }
}
$testResponse['action'] = 'reset_password';
ob_start();
includeHook(getMyHooks(['page' => 'forgotPasswordResponse']), 'body');
$testResetMarkup = ob_get_clean();
testExpect(strpos($testResetMarkup, 'data-target="[name=&quot;resetPassword&quot;]"') !== false, 'successful reset hook emits targeted widget');
ob_start();
turnstileHere(['action' => 'contact']);
turnstileHere(['action' => 'newsletter', 'response_field' => 'newsletter-token']);
$testMarkup = ob_get_clean();
testExpect(substr_count($testMarkup, 'class="us-turnstile mb-3"') === 2, 'multiple widget markup');
testExpect(strpos($testMarkup, 'newsletter-token') !== false, 'custom response field');
testExpect(strpos($testMarkup . $testResetMarkup, '1x0000000000000000000000000000000AA') === false, 'secret is never rendered');
testExpect(substr_count($testMarkup . $testResetMarkup, 'assets/turnstile.js') === 1, 'script emitted once per request');
try { turnstileHere(['action' => '"><script>']); testExpect(false, 'unsafe action must throw'); }
catch (InvalidArgumentException $e) { testExpect(true, 'unsafe action rejected'); }

$currentPage = 'passwordless.php';
$testPasswordlessHooks = getMyHooks();
foreach (['enter_email' => true, 'check_email' => false, 'verify_code' => false] as $method => $testRender) {
    ob_start();
    includeHook($testPasswordlessHooks, 'form');
    $testPasswordlessMarkup = ob_get_clean();
    testExpect((strpos($testPasswordlessMarkup, 'data-action="passwordless"') !== false) === $testRender, 'passwordless widget visibility for ' . $method);
}
foreach (['/users/', '/usersc/'] as $testFolder) {
    $_SERVER['PHP_SELF'] = $testFolder . 'passwordless.php';
    $_SERVER['REQUEST_METHOD'] = 'POST';
    (new ReflectionProperty(Server::class, 'cache'))->setValue(null, []);
    foreach (['enter_email', ''] as $testMethod) {
        $_POST = ['method' => $testMethod, 'email' => 'test@example.invalid', 'cf-turnstile-response' => 'token'];
        $testResponse['action'] = 'passwordless';
        $testBefore = $testCalls;
        includeHook($testPasswordlessHooks, 'post');
        testExpect($testCalls === $testBefore + 1, 'email request verifies once before continuing');
    }
    foreach (['verify_code', 'check_email'] as $testMethod) {
        $_POST = ['method' => $testMethod, 'code' => '123456'];
        $testBefore = $testCalls;
        includeHook($testPasswordlessHooks, 'post');
        testExpect($testCalls === $testBefore, 'code entry and check-email view skip verification');
    }
}
$_SERVER['REQUEST_METHOD'] = 'GET';
(new ReflectionProperty(Server::class, 'cache'))->setValue(null, []);
$_POST = [];
$testBefore = $testCalls;
includeHook($testPasswordlessHooks, 'post');
testExpect($testCalls === $testBefore, 'GET requests skip passwordless verification');
$db->update('plg_turnstile_settings', 1, ['protect_passwordless' => 0]);
plgTurnstileSettings(true);
$_SERVER['REQUEST_METHOD'] = 'POST';
(new ReflectionProperty(Server::class, 'cache'))->setValue(null, []);
$_POST = ['method' => 'enter_email'];
includeHook($testPasswordlessHooks, 'post');
testExpect($testCalls === $testBefore, 'disabled passwordless protection skips verification');
$method = 'enter_email';
ob_start();
includeHook($testPasswordlessHooks, 'form');
testExpect(ob_get_clean() === '', 'disabled passwordless protection hides widget');
$db->update('plg_turnstile_settings', 1, ['protect_passwordless' => 1]);
plgTurnstileSettings(true);

$currentPage = 'admin.php';
$_POST = ['csrf' => 'wrong', 'turnstile_action' => 'save', 'site_key' => 'attacker'];
$testOriginal = clone plgTurnstileSettings();
ob_start();
require $testPlugin . 'configure.php';
$testConfigHtml = ob_get_clean();
testExpect(plgTurnstileSettings(true) == $testOriginal, 'invalid CSRF cannot change settings');
testExpect(strpos($testConfigHtml, $testOriginal->secret_key) === false, 'configuration never reflects the saved secret');
testExpect(strpos($testConfigHtml, 'security token expired') !== false, 'CSRF error is visible');

require $testPlugin . 'uninstall.php';
testExpect($db->query('SELECT * FROM us_plugin_hooks WHERE disabled = 0')->count() === 0, 'deactivation disables all hooks');
testExpect(plgTurnstileSettings(true)->site_key === $testOriginal->site_key, 'deactivation preserves keys');
require $testPlugin . 'install.php';
require $testPlugin . 'activate.php';
testExpect($db->query('SELECT * FROM us_plugin_hooks WHERE disabled = 0')->count() === 9, 'reactivation restores selections');
testExpect($db->query('SELECT * FROM us_plugin_hooks')->count() === 9, 'reactivation creates no duplicate hooks');
require $testPlugin . 'delete.php';
testExpect($db->query('SELECT * FROM us_plugin_hooks')->count() === 0, 'delete removes hooks');
testExpect($db->query("SHOW TABLES LIKE 'plg_turnstile_settings'")->count() === 0, 'delete removes settings table');
$db->insert('us_plugins', ['plugin' => 'turnstile', 'status' => 'active']);
plgTurnstileSettings(true);
ob_start();
require $testPlugin . 'configure.php';
$testRecoveryHtml = ob_get_clean();
testExpect(strpos($testRecoveryHtml, 'reactivate') !== false, 'missing settings directs administrator to activation');
testExpect($db->query("SHOW TABLES LIKE 'plg_turnstile_settings'")->count() === 0, 'configuration never runs a migration before CSRF');
echo 'PASS: ' . $testAssertions . " assertions using installed UserSpice classes/hooks and isolated MariaDB.\n";
