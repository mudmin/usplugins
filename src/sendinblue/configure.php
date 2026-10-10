<?php if (!in_array($user->data()->id, $master_account)) {
  Redirect::to($us_url_root . 'users/admin.php');
} //only allow master accounts to manage plugins! 
?>

<?php
include "plugin_info.php";
pluginActive($plugin_name);
$send = $db->query("SELECT * FROM plg_sendinblue")->first();
if (!$send) {
  // install.php seeds row 1; recreate it if it was lost so saves have a target.
  $db->insert("plg_sendinblue", ['id' => 1]);
  $send = $db->query("SELECT * FROM plg_sendinblue")->first();
}

// Check for override file status
$overrideRenamePath = $abs_us_root . $us_url_root . 'usersc/plugins/sendinblue/override.RENAME.php';
$overridePath = $abs_us_root . $us_url_root . 'usersc/plugins/sendinblue/override.php';
$overrideRenameExists = file_exists($overrideRenamePath);
$overrideExists = file_exists($overridePath);

// Check email settings for forgot password warning
$emailSet = $db->query("SELECT * FROM email")->first();
$settings = $db->query("SELECT * FROM settings")->first();
if (!isset($settings->no_passwords)) {
  $settings->no_passwords = 0;
}
$showEmailWarning = false;
if ($overrideExists && ($emailSet->email_login == "yourEmail@gmail.com" || $emailSet->email_login == "" || $emailSet->email_pass == "1234")) {
  $showEmailWarning = true;
}

// Check if API key is configured
$apiKeyConfigured = !empty($send->key);

// Handle form submissions
// Actions and fields are read from the POST body only. Input::get() falls back to
// the query string, and these forms post back to the current URL, so a crafted
// link could otherwise add "save=1&key=..." to an admin's legitimate POST.
// Values are stored as plain text and escaped on output; Input::get() would
// HTML-encode them and Brevo would show "Bob &amp; Co" to recipients.
$posted = static function ($field) {
  $value = $_POST[$field] ?? '';
  return is_string($value) ? trim($value) : '';
};
if (Input::exists()) {
  if (!Token::check($posted('csrf'))) {
    include($abs_us_root . $us_url_root . 'usersc/scripts/token_error.php');
    exit; // token_error.php is site-editable; never fall through to the actions
  }

  // Handle settings save
  if ($posted('save') !== '') {
    $fields = [
      'from' => $posted('from'),
      'from_name' => $posted('from_name'),
      'reply' => $posted('reply'),
      'public_delivery_status' => $posted('public_delivery_status') === '1' ? 1 : 0,
      'public_delivery_status_seeded' => 1,
      'key' => $posted('key'),
    ];
    // Until migration 00008 runs, the tag columns do not exist yet.
    if (property_exists($send, 'tag_mode')) {
      $tagMode = $posted('tag_mode');
      $fields['tag_mode'] = in_array($tagMode, ['off', 'caller', 'fixed'], true) ? $tagMode : 'off';
      $fields['tag_fixed'] = sendinblueCleanTag($posted('tag_fixed'));
    }
    $db->update("plg_sendinblue", $send->id, $fields);
    if ($db->error()) {
      usError('Settings were not saved: ' . $db->errorString());
    } else {
      usSuccess('Settings saved');
    }
    Redirect::to('admin.php?view=plugins_config&plugin=sendinblue');
  }

  // The bundled override.RENAME.php always stays in place. Activating copies it to
  // override.php (which UserSpice loads); deactivating deletes that copy. Updating
  // replaces an older active copy, since plugin updates never touch override.php.
  if ($posted('activate_override') === '1' || $posted('update_override') === '1') {
    if ($overrideRenameExists && copy($overrideRenamePath, $overridePath)) {
      usSuccess($posted('update_override') === '1' ? 'Override updated to the bundled version' : 'Override activated successfully');
    } else {
      usError('Failed to activate override: override.RENAME.php is missing or the folder is not writable');
    }
    Redirect::to('admin.php?view=plugins_config&plugin=sendinblue');
  }

  if ($posted('deactivate_override') === '1') {
    if (!$overrideExists) {
      usSuccess('Override deactivated successfully');
    } elseif (!$overrideRenameExists) {
      // Sites activated before 1.8.0 renamed the bundled file away. Put it back
      // rather than delete the only copy.
      rename($overridePath, $overrideRenamePath)
        ? usSuccess('Override deactivated successfully')
        : usError('Failed to deactivate override');
    } else {
      unlink($overridePath)
        ? usSuccess('Override deactivated successfully')
        : usError('Failed to deactivate override');
    }
    Redirect::to('admin.php?view=plugins_config&plugin=sendinblue');
  }

  // Handle test email
  if ($posted('send_test') === '1') {
    $testEmail = $posted('test_email');
    $testSubject = $posted('test_subject') ?: 'Brevo Test Email';
    $testBody = $posted('test_body') ?: 'This is a test email from your SendinBlue plugin configuration.';

    if (filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
      $result = sendinblue($testEmail, $testSubject, $testBody, 'Test User');

      if ($result === true) {
        $_SESSION['sendinblue_test_result'] = ['success' => true, 'message' => 'Test email sent successfully'];
      } else {
        // Sanitize the error message from remote server
        $_SESSION['sendinblue_test_result'] = ['success' => false, 'message' => htmlspecialchars(sendinblueLastError() ?? 'Unknown error', ENT_QUOTES, 'UTF-8')];
      }
    } else {
      $_SESSION['sendinblue_test_result'] = ['success' => false, 'message' => 'Invalid email address'];
    }
    Redirect::to('admin.php?view=plugins_config&plugin=sendinblue');
  }
}
$token = Token::generate();
?>
<div class="content mt-3">

  <a href="<?= $us_url_root ?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
  <h1>Configure the Brevo (Formerly SendinBlue) Plugin!</h1>
  <p><a href="<?= htmlspecialchars($us_url_root . 'usersc/plugins/sendinblue/delivery_logs.php', ENT_QUOTES, 'UTF-8') ?>">View Brevo delivery evidence</a></p>

  <?php if ($overrideExists && $overrideRenameExists && sha1_file($overridePath) !== sha1_file($overrideRenamePath)): ?>
    <div class="alert alert-info" role="alert">
      <h5><i class="fas fa-code-branch"></i> Override Update Available</h5>
      <p class="mb-2">Your active <code>override.php</code> differs from the bundled <code>override.RENAME.php</code> shipped with this plugin version. The bundled file may contain bug fixes or improvements that aren't in your active override. You may want to review the differences and consider rolling the update in.</p>
      <p class="mb-0">
        <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#overrideDiff" aria-expanded="false">
          <i class="fas fa-eye"></i> Show file contents
        </button>
      </p>
      <div class="collapse mt-3" id="overrideDiff">
        <div class="row">
          <div class="col-md-6">
            <strong>Your active override.php:</strong>
            <pre style="background-color:#f4f4f4;border:1px solid #ddd;padding:10px;max-height:400px;overflow:auto;"><?= htmlspecialchars(file_get_contents($overridePath), ENT_QUOTES, 'UTF-8') ?></pre>
          </div>
          <div class="col-md-6">
            <strong>Bundled override.RENAME.php:</strong>
            <pre style="background-color:#f4f4f4;border:1px solid #ddd;padding:10px;max-height:400px;overflow:auto;"><?= htmlspecialchars(file_get_contents($overrideRenamePath), ENT_QUOTES, 'UTF-8') ?></pre>
          </div>
        </div>
        <form method="post" class="mt-2">
          <input type="hidden" name="csrf" value="<?= $token ?>">
          <button type="submit" name="update_override" value="1" class="btn btn-sm btn-primary" onclick="return confirm('Replace your active override.php with the bundled version? Any edits you made to it will be lost.');">
            Replace my override.php with the bundled version
          </button>
        </form>
      </div>
    </div>
  <?php endif; ?>

  <?php if (isset($_SESSION['sendinblue_test_result'])): ?>
    <?php $testResult = $_SESSION['sendinblue_test_result']; unset($_SESSION['sendinblue_test_result']); ?>
    <div class="alert alert-<?= $testResult['success'] ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
      <strong>Test Email:</strong> <?= $testResult['message'] ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <?php if (!$apiKeyConfigured): ?>
    <div class="alert alert-danger" role="alert">
      <h5><i class="fas fa-key"></i> API Key Required</h5>
      <p class="mb-0">Please fill in your Brevo API key and other information below and click Save to configure the plugin.</p>
    </div>
  <?php endif; ?>

  <?php if ($apiKeyConfigured): ?>
    <!-- Override Status Banner -->
    <?php if ($overrideRenameExists && !$overrideExists): ?>
      <div class="alert alert-info alert-dismissible fade show" role="alert">
        <h5><i class="fas fa-info-circle"></i> Plugin Running in Standalone Mode</h5>
        <p class="mb-3">This means that UserSpice's built in email calls (ie passwordless logins and password resets) will still use the built in email function.  You can still use the sendinblue email function to send emails with Brevo.  To force UserSpice to use Brevo for all emails, activate the override below.   </p>
        <form method="post" class="d-inline">
          <input type="hidden" name="csrf" value="<?= $token ?>">
          <button type="submit" name="activate_override" value="1" class="btn btn-success btn-sm">
            <i class="fas fa-toggle-on"></i> Activate Override (Use SendinBlue for all emails)
          </button>
        </form>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <!-- Override Active Warning -->
    <?php if ($overrideExists): ?>
      <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <h5><i class="fas fa-exclamation-triangle"></i> Override Active</h5>
        <p class="mb-3">SendinBlue is currently overriding the UserSpice email system. All emails are being sent through SendinBlue.</p>
        <form method="post" class="d-inline">
          <input type="hidden" name="csrf" value="<?= $token ?>">
          <button type="submit" name="deactivate_override" value="1" class="btn btn-warning btn-sm"<?= ($overrideRenameExists && sha1_file($overridePath) !== sha1_file($overrideRenamePath)) ? ' onclick="return confirm(\'Your override.php has been edited or is from an older version. Deactivating deletes it. Continue?\');"' : '' ?>>
            <i class="fas fa-toggle-off"></i> Deactivate Override (Having email problems?)
          </button>
        </form>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>
  <?php endif; ?>

  <!-- Email Settings Warning -->
  <?php if ($showEmailWarning): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <h5><i class="fas fa-exclamation-circle"></i> Forgot Password Link Hidden</h5>
      <p class="mb-3">The forgot password link will not be shown on the login form because your email settings are set to default values. Please configure your email settings properly. You simply need to <b><u>change one character</u></b> on the Email Login/Username to fix this.</p>
      <a target="_blank" href="<?= $us_url_root ?>users/admin.php?view=email" class="btn btn-danger btn-sm">
        <i class="fas fa-cog"></i> Edit Email Settings
      </a>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <!-- Configuration Form -->
  <form class="" action="" method="post">
    <input type="hidden" name="csrf" value="<?= Token::generate() ?>">

    <div class="row">
      <div class="col-12 col-sm-4">
        <label for="">From Email</label>
        <input type="text" name="from" value="<?= htmlspecialchars((string)$send->from, ENT_QUOTES, 'UTF-8') ?>" required class="form-control">
      </div>
      <div class="col-12 col-sm-4">
        <label for="">Reply to Email (Usually the same)</label>
        <input type="text" name="reply" value="<?= htmlspecialchars((string)$send->reply, ENT_QUOTES, 'UTF-8') ?>" required class="form-control">
      </div>

      <div class="col-12 col-sm-4">
        <label for="">Email "From" Name</label>
        <input type="text" name="from_name" value="<?= htmlspecialchars((string)$send->from_name, ENT_QUOTES, 'UTF-8') ?>" required class="form-control">
      </div>

    </div>

    <div class="row">
      <div class="col-12 col-sm-12">
        <label for="">API Key</label>
        <div class="input-group">
          <input type="password" name="key" value="<?= htmlspecialchars((string)$send->key, ENT_QUOTES, 'UTF-8') ?>" required class="form-control">
          <input type="submit" name="save" value="Save" class="btn btn-primary">
        </div>
      </div>
    </div>
    <div class="form-check mt-3">
      <input class="form-check-input" type="checkbox" name="public_delivery_status" id="public_delivery_status" value="1" <?= !empty($send->public_delivery_status) ? 'checked' : '' ?>>
      <label class="form-check-label" for="public_delivery_status">Show live delivery status on the passwordless sign-in page</label>
      <div class="form-text">This can reveal whether an email address has an account. Leave off on sites that require account-existence privacy.</div>
    </div>
    <?php if (property_exists($send, 'tag_mode')): ?>
      <div class="row mt-3">
        <div class="col-12 col-sm-6">
          <label for="tag_mode">Tag untagged mail</label>
          <select name="tag_mode" id="tag_mode" class="form-select">
            <option value="off" <?= $send->tag_mode === 'off' ? 'selected' : '' ?>>Off (no tag)</option>
            <option value="caller" <?= $send->tag_mode === 'caller' ? 'selected' : '' ?>>Calling file name (e.g. forgot_password, join)</option>
            <option value="fixed" <?= $send->tag_mode === 'fixed' ? 'selected' : '' ?>>Fixed tag</option>
          </select>
        </div>
        <div class="col-12 col-sm-6">
          <label for="tag_fixed">Fixed tag</label>
          <input type="text" name="tag_fixed" id="tag_fixed" maxlength="64" value="<?= htmlspecialchars($send->tag_fixed, ENT_QUOTES, 'UTF-8') ?>" class="form-control">
          <div class="form-text">Letters, numbers, <code>_</code> <code>.</code> <code>-</code> only. Used when "Fixed tag" is selected.</div>
        </div>
        <div class="col-12">
          <div class="form-text">Brevo groups statistics, logs and webhook events by tag. Mail sent with a <code>tags</code> option always keeps its own tags.</div>
        </div>
      </div>
    <?php endif; ?>
  </form>

  <?php if ($apiKeyConfigured): ?>
    <!-- Test Email Form -->
    <div class="row mt-4">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h5><i class="fas fa-envelope-open-text"></i> Send Test Email</h5>
          </div>
          <div class="card-body">
            <form method="post">
              <input type="hidden" name="csrf" value="<?= $token ?>">
              <div class="row">
                <div class="col-12 col-md-6">
                  <label for="test_email">Test Email Address</label>
                  <input type="email" name="test_email" id="test_email" class="form-control" required placeholder="recipient@example.com">
                </div>
                <div class="col-12 col-md-6">
                  <label for="test_subject">Subject (Optional)</label>
                  <input type="text" name="test_subject" id="test_subject" class="form-control" placeholder="SendinBlue Test Email">
                </div>
              </div>
              <div class="row mt-3">
                <div class="col-12">
                  <label for="test_body">Message (Optional)</label>
                  <textarea name="test_body" id="test_body" class="form-control" rows="3" placeholder="This is a test email from your SendinBlue plugin configuration."></textarea>
                </div>
              </div>
              <div class="row mt-3">
                <div class="col-12">
                  <button type="submit" name="send_test" value="1" class="btn btn-success">
                    <i class="fas fa-paper-plane"></i> Send Test Email
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <div class="row" style="padding-top:2em;">
    <div class="col-12">
      <h2>Documentation</h2>
      <?php $codeStyle = 'background-color:#f4f4f4;border:1px solid #ddd;padding:10px 15px;white-space:pre-wrap;'; ?>

      <h4 class="mt-4">1. Getting started</h4>
      <p class="mb-2">Brevo (formerly Sendinblue) sends email through an API instead of SMTP. The free plan sends 300 emails per day, which covers password resets and sign-ins for most UserSpice sites.</p>
      <ol>
        <li>Create an account at <a href="https://www.brevo.com/">brevo.com</a> and verify your email.</li>
        <li>In Brevo, open your account menu (upper right), choose <strong>SMTP &amp; API</strong>, and create an API key.</li>
        <li>Paste the key above, fill in the From and Reply-To addresses and the From name, and click Save. The From address must be a sender or domain you have verified in Brevo.</li>
        <li>Send yourself a test email with the form above. If it fails, the error from Brevo is shown and also written to <a href="<?= $us_url_root ?>users/admin.php?view=logs">the UserSpice logs</a>.</li>
      </ol>
      <p class="mb-2">Brevo blocks API calls from unknown IP addresses by default. If your first send fails with an IP error, add your server's IP in Brevo or see <a href="https://developers.brevo.com/docs/ip-security">Brevo IP Security</a>.</p>

      <h4 class="mt-4">2. Standalone mode and override mode</h4>
      <p class="mb-2">The plugin always gives you the <strong>sendinblue()</strong> function. What happens to UserSpice's own mail depends on the override:</p>
      <ul>
        <li><strong>Standalone (override off):</strong> UserSpice's built-in mail (password resets, join verification, passwordless sign-in, admin messages) still uses the core <strong>email()</strong> function and your SMTP settings. Only your own code that calls <strong>sendinblue()</strong> uses Brevo.</li>
        <li><strong>Override (on):</strong> the plugin replaces <strong>email()</strong>, so all of UserSpice's mail, and any of your code that calls <strong>email()</strong>, goes through Brevo.</li>
      </ul>
      <p class="mb-2">How the buttons work:</p>
      <ul>
        <li><strong>Activate Override</strong> copies the bundled <code>override.RENAME.php</code> to <code>override.php</code>, which UserSpice loads. The bundled file is never moved or changed.</li>
        <li><strong>Deactivate Override</strong> deletes <code>override.php</code>. If you edited that file, your edits are lost; you are asked to confirm when it differs from the bundled one.</li>
        <li>Plugin updates replace <code>override.RENAME.php</code> but never touch your active <code>override.php</code>. When the two differ, an <strong>Override Update Available</strong> notice appears at the top of this page, with a button to replace your copy with the bundled one.</li>
      </ul>

      <h4 class="mt-4">3. Sending with sendinblue()</h4>
      <pre style="<?= $codeStyle ?>"><code>sendinblue($to, $subject, $body, $to_name = "", $options = []);</code></pre>
      <ul>
        <li><strong>$to</strong>: one address, or an array of addresses. With an array, every recipient sees the others, as with core <strong>email()</strong>.</li>
        <li><strong>$body</strong>: HTML. Ignored by Brevo when you send a template.</li>
        <li><strong>$to_name</strong>: the recipient's display name. Used only when there is a single recipient; otherwise each address is its own name.</li>
      </ul>
      <div class="table-responsive">
        <table class="table table-sm table-bordered">
          <thead><tr><th>Option</th><th>What it does</th></tr></thead>
          <tbody>
            <tr><td><code>from</code>, <code>from_name</code></td><td>Send from a different verified sender than the one saved above.</td></tr>
            <tr><td><code>reply</code>, <code>reply_name</code></td><td>Set the Reply-To address and display name for this message. Without <code>reply_name</code>, the Reply-To has no display name.</td></tr>
            <tr><td><code>template</code></td><td>A Brevo template ID. See section 6.</td></tr>
            <tr><td><code>params</code></td><td>Values for the template, available as <code>{{ params.name }}</code>.</td></tr>
            <tr><td><code>attachments</code></td><td>An array of <code>['content' =&gt; base64 data, 'name' =&gt; 'file.pdf']</code>. See section 7.</td></tr>
            <tr><td><code>textContent</code></td><td>A plain-text version of the message.</td></tr>
            <tr><td><code>tags</code></td><td>A string or an array of strings. See section 5.</td></tr>
          </tbody>
        </table>
      </div>
      <pre style="<?= $codeStyle ?>"><code>$options = [
  'reply' =&gt; 'support@example.com',
  'reply_name' =&gt; 'Support Team',
  'tags' =&gt; 'invoice',
];
if (sendinblue("to@example.com", "Your invoice", "&lt;p&gt;Thanks for your order.&lt;/p&gt;", "Joe User", $options)) {
  echo "Sent";
} else {
  echo "Failed: " . sendinblueLastError();
}</code></pre>
      <p class="mb-2"><strong>Return value:</strong> <strong>sendinblue()</strong> returns <code>true</code> when Brevo accepts the message and <code>false</code> otherwise. After a <code>false</code>, <strong>sendinblueLastError()</strong> returns the reason (Brevo's error text, a missing API key, a missing recipient). Every failure is also written to the UserSpice logs. After a success, <strong>sendinblueLastMessageId()</strong> returns Brevo's message ID. Accepted is not the same as delivered; see section 8.</p>
      <p class="mb-2">If Brevo does not answer within 10 seconds, the send fails instead of holding up the page.</p>

      <h4 class="mt-4">4. Sending with email() when the override is active</h4>
      <p class="mb-2">With the override on, <strong>email($to, $subject, $body, $opts = [], $attachment = null)</strong> keeps the core signature, so existing code works unchanged. It maps the core options to Brevo:</p>
      <div class="table-responsive">
        <table class="table table-sm table-bordered">
          <thead><tr><th>email() option</th><th>Becomes</th></tr></thead>
          <tbody>
            <tr><td><code>email</code>, <code>name</code></td><td>The From address and From name.</td></tr>
            <tr><td><code>replyTo</code>, <code>reply_name</code></td><td>The Reply-To address and display name.</td></tr>
            <tr><td><code>altBody</code></td><td>The plain-text version.</td></tr>
            <tr><td><code>tags</code></td><td>Brevo tags (needs the 1.8.0 or later override).</td></tr>
            <tr><td><code>$attachment</code></td><td>A file path. If the file cannot be read, the email is still sent without it; core <strong>email()</strong> would fail instead.</td></tr>
            <tr><td><code>cc</code>, <code>bcc</code></td><td>Not supported; they are ignored.</td></tr>
          </tbody>
        </table>
      </div>

      <h4 class="mt-4">5. Tags</h4>
      <p class="mb-2">Brevo groups mail by tag in its statistics, its transactional logs and its webhook events. Tags let you see, for example, that password resets are landing in spam while join emails are not, or act on a bounce only for verification emails.</p>
      <ul>
        <li><strong>Tags you pass always win.</strong> Pass <code>'tags'</code> as a string or an array of strings to <strong>sendinblue()</strong> or <strong>email()</strong>.</li>
        <li><strong>Mail sent without tags</strong> follows the <strong>Tag untagged mail</strong> setting above:
          <ul>
            <li><strong>Off</strong>: no tag. This is the default and matches earlier versions.</li>
            <li><strong>Fixed tag</strong>: every untagged message gets the tag you enter.</li>
            <li><strong>Calling file name</strong>: the tag is the name of the PHP file that called <strong>email()</strong> or <strong>sendinblue()</strong>, without <code>.php</code>. UserSpice's own mail is tagged by page with no core changes: <code>forgot_password</code>, <code>join</code>, <code>verify_resend</code>, <code>reauth</code>, <code>passwordless</code>, <code>user_settings</code>, <code>_admin_user</code>, <code>_admin_users</code>, <code>_messages</code>. Test emails from this page are tagged <code>configure</code>. Only the file name is sent, never its path.</li>
          </ul>
        </li>
        <li><strong>Mail sent through a helper function</strong> is tagged with the helper's file, not the page that called the helper. A plugin whose helper lives in <code>functions.php</code> produces the tag <code>functions</code>. Pass explicit tags from helpers to keep their mail apart.</li>
        <li>Tags are also saved with each message on the <a href="<?= htmlspecialchars($us_url_root . 'usersc/plugins/sendinblue/delivery_logs.php', ENT_QUOTES, 'UTF-8') ?>">delivery evidence page</a>, which can filter by tag.</li>
      </ul>
      <pre style="<?= $codeStyle ?>"><code>sendinblue("to@example.com", "Subject", "Body", "Joe User", ['tags' =&gt; ['billing', 'invoice']]);
email("to@example.com", "Subject", "Body", ['tags' =&gt; 'contact-form']); // override active</code></pre>

      <h4 class="mt-4">6. Templates</h4>
      <p class="mb-2">Build the email in Brevo, then send its template ID with <code>params</code>. In the template, <code>{{ params.fname }}</code> prints a value, and you can loop over arrays:</p>
      <pre style="<?= $codeStyle ?>"><code>$options = [
  'template' =&gt; 1,
  'params' =&gt; [
    'fname' =&gt; $user-&gt;data()-&gt;fname,
    'items' =&gt; [
      ['name' =&gt; 'Chair', 'price' =&gt; '12.99'],
      ['name' =&gt; 'Table', 'price' =&gt; '24.99'],
    ],
  ],
];
sendinblue("to@example.com", "Your order", "Your order", "Joe User", $options);

{% for item in params.items %}
  {{ item.name }} - {{ item.price }}
{% endfor %}</code></pre>

      <h4 class="mt-4">7. Attachments</h4>
      <pre style="<?= $codeStyle ?>"><code>$pdf = $abs_us_root . $us_url_root . "sample.pdf";
$options = [
  'attachments' =&gt; [
    ['content' =&gt; base64_encode(file_get_contents($pdf)), 'name' =&gt; 'sample.pdf'],
  ],
];
sendinblue("to@example.com", "Your file", "&lt;p&gt;Attached.&lt;/p&gt;", "", $options);</code></pre>
      <p class="mb-2">With the override active, <strong>email()</strong> takes a file path as its fifth argument instead and encodes it for you.</p>

      <h4 class="mt-4">8. Delivery evidence</h4>
      <ul>
        <li>Every message Brevo accepts is recorded with its recipient, sender, type, tags and Brevo message ID on the <a href="<?= htmlspecialchars($us_url_root . 'usersc/plugins/sendinblue/delivery_logs.php', ENT_QUOTES, 'UTF-8') ?>">delivery evidence page</a> (master accounts only). <strong>Refresh</strong> asks Brevo for the latest event. <em>Delivered</em> means the recipient's mail server accepted the message; it does not prove the message reached the inbox. Brevo keeps events for 90 days. Subjects and bodies are never stored, because passwordless subjects can contain the sign-in code.</li>
      </ul>

      <h4 class="mt-4">9. Deactivating and deleting the plugin</h4>
      <ul>
        <li><strong>Deactivating</strong> the plugin in the Plugin Manager stops it, including the override, but keeps your settings, API key and delivery records.</li>
        <li><strong>Deleting</strong> the plugin removes its files and its data: the settings (including the API key), the delivery records and its hook registrations.</li>
      </ul>

      <p class="mt-4">If you appreciate this plugin and would like to make a donation to the author, you can do so at <strong><a href="https://UserSpice.com/donate">https://UserSpice.com/donate</a></strong>. Either way, thanks for using UserSpice!</p>
    </div>
  </div>
</div> <!-- /.row -->
