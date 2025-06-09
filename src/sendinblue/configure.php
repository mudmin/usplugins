<?php if (!in_array($user->data()->id, $master_account)) {
  Redirect::to($us_url_root . 'users/admin.php');
} //only allow master accounts to manage plugins! 
?>

<?php
include "plugin_info.php";
pluginActive($plugin_name);
$send = $db->query("SELECT * FROM plg_sendinblue")->first();

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

// Handle form submissions
if (!empty($_POST)) {
  $token = $_POST['csrf'];
  if (!Token::check($token)) {
    include($abs_us_root . $us_url_root . 'usersc/scripts/token_error.php');
  }

  // Handle settings save
  if (isset($_POST['save'])) {
    $fields = [
      'from' => Input::get('from'),
      'from_name' => Input::get('from_name'),
      'reply' => Input::get('reply'),
      'override' => Input::get('override'),
      'key' => Input::get('key'),
    ];
    $db->update("plg_sendinblue", 1, $fields);
    Redirect::to('admin.php?view=plugins_config&plugin=sendinblue&msg=Settings saved');
  }

  // Handle override file activation
  if (isset($_POST['activate_override'])) {
    if ($overrideExists) {
      unlink($overridePath);
    }
    if (rename($overrideRenamePath, $overridePath)) {
      Redirect::to('admin.php?view=plugins_config&plugin=sendinblue&msg=Override activated successfully');
    } else {
      Redirect::to('admin.php?view=plugins_config&plugin=sendinblue&err=Failed to activate override');
    }
  }

  // Handle override file deactivation
  if (isset($_POST['deactivate_override'])) {
    if (rename($overridePath, $overrideRenamePath)) {
      Redirect::to('admin.php?view=plugins_config&plugin=sendinblue&msg=Override deactivated successfully');
    } else {
      Redirect::to('admin.php?view=plugins_config&plugin=sendinblue&err=Failed to deactivate override');
    }
  }

  // Handle test email
  if (isset($_POST['send_test'])) {
    $testEmail = Input::get('test_email');
    $testSubject = Input::get('test_subject') ?: 'Brevo Test Email';
    $testBody = Input::get('test_body') ?: 'This is a test email from your SendinBlue plugin configuration.';
    
    if (filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {

      $result = sendinblue($testEmail, $testSubject, $testBody, 'Test User');

      if ($result === true) {
        Redirect::to('admin.php?view=plugins_config&plugin=sendinblue&msg=Test email sent successfully');
      } else {
        Redirect::to('admin.php?view=plugins_config&plugin=sendinblue&err=Test email failed: ' . $result);
      }
    } else {
      Redirect::to('admin.php?view=plugins_config&plugin=sendinblue&err=Invalid email address');
    }
  }
}
$token = Token::generate();
?>
<div class="content mt-3">

  <a href="<?= $us_url_root ?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
  <h1>Configure the Brevo (Formerly SendinBlue) Plugin!</h1>

  <?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <?= htmlspecialchars($_GET['msg']) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <?php if (isset($_GET['err'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?= htmlspecialchars($_GET['err']) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <!-- Override Status Banner -->
  <?php if ($overrideRenameExists && !$overrideExists): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
      <h5><i class="fas fa-info-circle"></i> Plugin Running in Standalone Mode</h5>
      <p class="mb-3">This means that UserSpice's built in email calls (ie passwordless logins and password resets) will still use the built in email($to function.  You can still use the sendinblue($to email function to send emails with Brevo.  To force UserSpice to use Brevo for all emails, activate the override below.   </p>
      <form method="post" class="d-inline">
        <input type="hidden" name="csrf" value="<?= $token ?>">
        <button type="submit" name="activate_override" class="btn btn-success btn-sm">
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
        <button type="submit" name="deactivate_override" class="btn btn-warning btn-sm">
          <i class="fas fa-toggle-off"></i> Deactivate Override (Having email problems?)
        </button>
      </form>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <!-- Email Settings Warning -->
  <?php if ($showEmailWarning): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <h5><i class="fas fa-exclamation-circle"></i> Forgot Password Link Hidden</h5>
      <p class="mb-3">The forgot password link will not be shown on the login form because your email settings are set to default values. Please configure your email settings properly.</p>
      <a href="<?= $us_url_root ?>users/admin.php?view=email" class="btn btn-danger btn-sm">
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
        <input type="text" name="from" value="<?= $send->from ?>" required class="form-control">
      </div>
      <div class="col-12 col-sm-4">
        <label for="">Reply to Email (Usually the same)</label>
        <input type="text" name="reply" value="<?= $send->reply ?>" required class="form-control">
      </div>

      <div class="col-12 col-sm-4">
        <label for="">Email "From" Name</label>
        <input type="text" name="from_name" value="<?= $send->from_name ?>" required class="form-control">
      </div>

    </div>

    <div class="row">
      <div class="col-12 col-sm-12">
        <label for="">API Key</label>
        <div class="input-group">
          <input type="password" name="key" value="<?= $send->key ?>" required class="form-control">
          <input type="submit" name="save" value="Save" class="btn btn-primary">
        </div>
      </div>
    </div>
  </form>

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
                <button type="submit" name="send_test" class="btn btn-success">
                  <i class="fas fa-paper-plane"></i> Send Test Email
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <div class="row" style="padding-top:2em;">
    <div class="col-12">
      <h2>Documentation</h2>
      <p class="mb-2">
        Sendinblue lets you send 300 emails per day, free of charge (no credit card required), which is perfect for password resets etc for most UserSpice projects. Get started by visiting <a href="https://www.brevo.com/">https://www.brevo.com/</a> and creating an account. Don't worry about filling out most of the information other than the basic contact/business info. You can even use a Gmail account to sign up and proxy your emails through their server. This avoids a ton of annoying email setup and configuration. </p>

      <p class="mb-2">
        Once you've created your account and verified your email, go in the upper right hand corner and click on your email address and the menu will drop down. Select SMTP & API. Click the button to create a new API key and copy that key. Paste it in the settings above. Fill out the other obvious information above and you're good to go.
      </p>
      <p class="mb-2">
        By default, this plugin gives you a function called <strong>sendinblue($to,$subject,$body,$to_name = "")</strong>
      </p>
            <p class="mb-2">
       Brevo has an IP whitelist that is on by default, so if your emails do not appear to be sending, please check <a href="<?= $us_url_root ?>users/admin.php?view=logs" class="text-primary">
        <i class="fas fa-cog"></i> the UserSpice Logs
      </a> </strong> or visit <a href="https://developers.brevo.com/docs/ip-security">Brevo IP Security</a> for more information.
      </p>
      <p class="mb-2">
        The plugin automatically logs errors, but you can also do something like <strong>$send = sendinblue($to,$subject,$body);</strong> to have those messages returned to you immediately.
      </p>
      <p class="mb-2">
        Simply call the function just like the built in UserSpice email function and you are good to go. If you would like to override the built in UserSpice email function and use Sendinblue instead, simply rename the file called override.RENAME.php to override.php</p>
      <p class="mb-2">
        As of October, 2022, the plugin also supports templates, dynamic sender, and dynamic data inside your template. You can also do a foreach loop in your sendinblue templates. On the UserSpice side, use it like:
      </p>
      <h5>Basic Usage</h5>
      <p class="mb-2">
      <pre style="background-color: #f4f4f4; border: 1px solid #ddd; padding: 0px 15px;">
<code>
$options = [
  'from' => 'bob@aol.com',
  'from_name' => 'Bob Smith',
  'template' => 1,
  'params' => [
    'fname' => $user->data()->fname,
    'lname' => $user->data()->lname,
    'items' => [
      ['name' => 'Chair', 'price' => '12.99'],
      ['name' => 'Table', 'price' => '24.99'],
    ],
  ],
];
$send = sendinblue("to@gmail.com", "Sendinblue Test", "This is the message", "Joe User", $options);
</code>
</pre>

      </p>
      <h5>Using Templates</h5>
      <p>In sendinblue, use <strong>{{params.fname}}</strong> to pass in your fname variable. You can loop through the items array on your template with something tlike this : <br>

      <pre style="background-color: #f4f4f4; border: 1px solid #ddd; padding: 0px 15px;">
          <code>
            {% for item in params.items %}
            {{ item.name }} - {{ item.price }}
            {% endfor %}
          </code>
        </pre>
      </p>

      <h5>Sending Attachments</h5>
      <p>As of version 1.0.9, you can now send attachments. Attachments are arrays of arrays as described below. <br>

      <pre style="background-color: #f4f4f4; border: 1px solid #ddd; padding: 0px 15px;">
          <code>
// Define the path to the Attachment
$pdfFilePath = $abs_us_root . $us_url_root . "sample.pdf";

// Read the content of the Attachment
$pdfContent = file_get_contents($pdfFilePath);

// Encode the content in base64
$base64PdfContent = base64_encode($pdfContent);

// Prepare the attachment for the sendinblue function
$attachments = [
    [
        'content' => $base64PdfContent,
        'name' => 'sample.pdf' // The name of the file as it will appear in the email
    ]
];

// Prepare other email details
$to = "recipient@example.com";
$subject = "Subject of the email";
$body = "<p>This is the body of the email</p>";

// Optional parameters, including the attachments
$options = [
    'attachments' => $attachments
];

// Call the sendinblue function with the attachments
$result = sendinblue($to, $subject, $body, "", $options);

// Check the result of the email sending
if ($result === true) {
    echo "Email sent successfully with attachment.";
} else {
    echo "Failed to send email. Error: " . $result;
}
          </code>
        </pre>
      </p>
      <p>If you appreciate this plugin and would like to make a donation to the author, you can do so at <strong><a href="https://UserSpice.com/donate">https://UserSpice.com/donate</a></strong>. Either way, thanks for using UserSpice!</p>
    </div>
  </div>
</div> <!-- /.row -->