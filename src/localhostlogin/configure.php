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

      <?php
      $lhlDefined = defined('US_LOCALHOST_LOGIN') && US_LOCALHOST_LOGIN === true;
      $lhlLocal = isLocalhost();
      ?>
      <div class="card mb-4">
        <div class="card-header">
          <h5>Status on this request</h5>
        </div>
        <div class="card-body">
          <ul class="mb-3">
            <li><code>US_LOCALHOST_LOGIN</code> in <code>users/init.php</code>: <strong><?= $lhlDefined ? 'defined' : 'not defined' ?></strong></li>
            <li>This request counts as local: <strong><?= $lhlLocal ? 'yes' : 'no' ?></strong></li>
            <li>Localhost Login is <strong><?= ($lhlDefined && $lhlLocal) ? 'available' : 'off' ?></strong></li>
          </ul>
          <?php if (!$lhlDefined) { ?>
            <p class="mb-1">To turn it on for this machine only, add this line to <code>users/init.php</code> on your development copy. Don't add it on a server other people can reach.</p>
            <pre class="mb-0"><code>define('US_LOCALHOST_LOGIN', true);</code></pre>
          <?php } ?>
        </div>
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
            <li><strong>Two locks:</strong> the login page and link only work when <code>define('US_LOCALHOST_LOGIN', true);</code> is in <code>users/init.php</code> <em>and</em> the request is genuinely local. Plugin settings travel with your code when you deploy; <code>users/init.php</code> is per server, so the plugin stays off wherever you haven't added the line.</li>
            <li><strong>CSRF protection:</strong> All form submissions are protected with CSRF tokens</li>
            <li><strong>Audit logging:</strong> Every localhost login attempt is recorded in the UserSpice logs</li>
            <li><strong>Access denial logging:</strong> Any attempt to access from a non-localhost IP is logged and denied</li>
          </ul>
          <p class="text-muted"><small>"Genuinely local" uses <code>isLocalhost()</code>. On UserSpice 6.1.8 and later that means a connection from 127.0.0.1 or ::1 with no proxy or tunnel in front and a localhost host name, so a same-server proxy or Cloudflare Tunnel doesn't count. To use another host name for your dev site, list it in <code>define('US_LOCAL_HOSTS', ['mysite.test']);</code> in <code>users/init.php</code>. Older UserSpice versions only check the 127.0.0.1 / ::1 address, which is another reason for the init.php line.</small></p>
        </div>
      </div>

      <div class="card mb-4">
        <div class="card-header">
          <h5>How to Use</h5>
        </div>
        <div class="card-body">
          <ol>
            <li>Add <code>define('US_LOCALHOST_LOGIN', true);</code> to <code>users/init.php</code> on your development copy</li>
            <li>Make sure you're accessing your UserSpice installation from <strong>localhost</strong> (e.g., <code>http://localhost/your-site/</code>), not through a proxy or tunnel</li>
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
