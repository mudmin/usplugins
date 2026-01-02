<?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>
<?php
include "plugin_info.php";
pluginActive($plugin_name);
?>
<div class="content mt-3">
  <div class="row">
    <div class="col-sm-12">
      <a href="<?=$us_url_root?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
      <h1>Localhost Login</h1>

      <div class="alert alert-warning" role="alert">
        <h5><i class="fas fa-exclamation-triangle"></i> Development Tool Only</h5>
        <p class="mb-0">This plugin is intended for <strong>local development environments only</strong>. It allows passwordless authentication and bypasses all security measures including 2FA. Never use this on a production server.</p>
      </div>

      <div class="card mb-4">
        <div class="card-header">
          <h5>What This Plugin Does</h5>
        </div>
        <div class="card-body">
          <p>The Localhost Login plugin provides a convenient way to quickly switch between user accounts during local development without needing to remember passwords or go through the normal authentication flow.</p>

          <h6>Features:</h6>
          <ul>
            <li>Adds a <strong>[Localhost Login]</strong> link to the bottom of the login page</li>
            <li>Provides a dropdown menu of all users in the system</li>
            <li>Allows one-click login as any user</li>
            <li>Bypasses password requirements and 2FA</li>
            <li>All login attempts are logged for auditing</li>
          </ul>
        </div>
      </div>

      <div class="card mb-4">
        <div class="card-header">
          <h5>Security</h5>
        </div>
        <div class="card-body">
          <p>This plugin includes multiple security measures to prevent misuse:</p>
          <ul>
            <li><strong>Localhost-only access:</strong> The login page and link are only accessible when accessing from localhost (127.0.0.1 or ::1)</li>
            <li><strong>CSRF protection:</strong> All form submissions are protected with CSRF tokens</li>
            <li><strong>Audit logging:</strong> Every localhost login attempt is recorded in the UserSpice logs</li>
            <li><strong>Access denial logging:</strong> Any attempt to access from a non-localhost IP is logged and denied</li>
          </ul>
          <p class="text-muted"><small>The plugin checks the server's IP address using the <code>isLocalhost()</code> function which verifies the request originates from 127.0.0.1, ::1, or localhost.</small></p>
        </div>
      </div>

      <div class="card mb-4">
        <div class="card-header">
          <h5>How to Use</h5>
        </div>
        <div class="card-body">
          <ol>
            <li>Make sure you're accessing your UserSpice installation from <strong>localhost</strong> (e.g., <code>http://localhost/your-site/</code> or <code>http://127.0.0.1/your-site/</code>)</li>
            <li>Navigate to the login page</li>
            <li>Click the <strong>[Localhost Login]</strong> link at the bottom of the form</li>
            <li>Select a user from the dropdown</li>
            <li>Click Sign In - you'll be logged in immediately as that user</li>
          </ol>
        </div>
      </div>

      <p>If you appreciate this plugin and would like to make a donation to the author, you can do so at <a href="https://UserSpice.com/donate">https://UserSpice.com/donate</a>.
        <br>Either way, thanks for using UserSpice!</p>
    </div> <!-- /.col -->
  </div> <!-- /.row -->
</div>
