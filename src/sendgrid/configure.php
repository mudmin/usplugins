<?php if (!in_array($user->data()->id, $master_account)) {
  Redirect::to($us_url_root . 'users/admin.php');
} //only allow master accounts to manage plugins! 
?>

<?php
include "plugin_info.php";
pluginActive($plugin_name);
$send = $db->query("SELECT * FROM plg_sendgrid")->first();
if (!empty($_POST)) {
  $token = $_POST['csrf'];
  if (!Token::check($token)) {
    include($abs_us_root . $us_url_root . 'usersc/scripts/token_error.php');
  }

  $fields = [
    'from' => Input::get('from'),
    'from_name' => Input::get('from_name'),
    'reply' => Input::get('reply'),
    'key' => Input::get('key'),
  ];
  $db->update("plg_sendgrid", 1, $fields);
  Redirect::to('admin.php?view=plugins_config&plugin=sendgrid&msg=Settings saved');
}
$token = Token::generate();
?>
<div class="content mt-3">
  <a href="<?= $us_url_root ?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
  <h1>Configure the SendGrid Plugin!</h1>
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

  <div class="row" style="padding-top:2em;">
    <div class="col-12">
      <h2>Documentation</h2>
      <p class="mb-2">
        SendGrid offers various pricing plans, including a free tier that allows you to send up to 100 emails per day. This is suitable for most UserSpice projects, especially for handling password resets and similar functionalities. To get started, visit <a href="https://sendgrid.com/">https://sendgrid.com/</a> and create an account.
      </p>

      <p class="mb-2">
        After creating your account and verifying your email, navigate to the API Keys section in your SendGrid dashboard. Create a new API key and copy it. Paste this key into the settings above, along with the other required information.
      </p>

      <p class="mb-2">
        This plugin provides a function called <strong>sendgrid($to, $subject, $body, $to_name = "", $options = [])</strong>
      </p>

      <p class="mb-2">
        The plugin automatically logs errors, but you can also capture the return value like this: <strong>$send = sendgrid($to, $subject, $body);</strong> to have those messages returned to you immediately.
      </p>

      <p class="mb-2">
        Simply call the function just like the built-in UserSpice email function, and you're good to go. If you would like to override the built-in UserSpice email function and use SendGrid instead, you can rename the file override.RENAME.php to override.php in usersc/plugins/sendgrid.
      </p>

      <h5>Basic Usage</h5>
      <pre style="background-color: #f4f4f4; border: 1px solid #ddd; padding: 0px 15px;">
<code>
$options = [
  'template_id' => 'd-xxxxxxxxxxxxxxxxxxxxxxxx',
  'dynamic_template_data' => [
    'fname' => $user->data()->fname,
    'lname' => $user->data()->lname,
    'items' => [
      ['name' => 'Chair', 'price' => '12.99'],
      ['name' => 'Table', 'price' => '24.99'],
    ],
  ],
];
$send = sendgrid("to@gmail.com", "SendGrid Test", "This is the message", "Joe User", $options);
</code>
</pre>
<!-- still working on template testing -->
<!-- <h5>Using Templates</h5>
      <p>SendGrid supports dynamic templates. To use a template, you need to create it in your SendGrid account and use its ID when sending an email. Here's how you can use templates with this plugin:</p>

      <pre style="background-color: #f4f4f4; border: 1px solid #ddd; padding: 0px 15px;">
<code>
$options = [
  'template_id' => 'd-xxxxxxxxxxxxxxxxxxxxxxxx',
  'dynamic_template_data' => [
    'first_name' => $user->data()->fname,
    'last_name' => $user->data()->lname,
    'items' => [
      ['name' => 'Chair', 'price' => '12.99'],
      ['name' => 'Table', 'price' => '24.99'],
    ],
  ],
];
$send = sendgrid("to@gmail.com", "SendGrid Test", "", "Joe User", $options);
</code>
</pre>

      <p>In your SendGrid template, you can use the dynamic data like this:</p>

      <pre style="background-color: #f4f4f4; border: 1px solid #ddd; padding: 0px 15px;">
<code>
Hello {{first_name}} {{last_name}},

Your order includes:
{{#each items}}
  - {{name}}: ${{price}}
{{/each}}
</code>
</pre>

      <p>Note that when using a template, the 'body' parameter in the sendgrid function call can be left empty, as the content will be defined by the template.</p> -->



      <p>If you appreciate this plugin and would like to make a donation to the author, you can do so at <strong><a href="https://UserSpice.com/donate">https://UserSpice.com/donate</a></strong>. Either way, thanks for using UserSpice!</p>
    </div>
  </div>
</div> <!-- /.row -->