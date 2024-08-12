<?php if (!in_array($user->data()->id, $master_account)) {
  Redirect::to($us_url_root . 'users/admin.php');
} //only allow master accounts to manage plugins! 
?>

<?php
include "plugin_info.php";
$oauthSettings = $db->query("SELECT * FROM plg_oauth_login")->first();

// $db->query("CREATE TABLE plg_oauth_client_logins (
//   id INT AUTO_INCREMENT PRIMARY KEY,
//   user_id INT,
// new_user tinyint(1) DEFAULT 0,
//   ts DATETIME DEFAULT CURRENT_TIMESTAMP
// );");
$scripts = scandir($abs_us_root . $us_url_root . 'usersc/plugins/oauth_login/login_scripts');

$scripts = array_filter($scripts, function ($file) {
  return strpos($file, '.php') !== false;
});
$icons = scandir($abs_us_root . $us_url_root . 'usersc/plugins/oauth_login/assets');

$icons = array_filter($icons, function ($file) {
  return strpos($file, '.png') !== false;
});


pluginActive($plugin_name);
if (!empty($_POST)) {
  if (!Token::check(Input::get('csrf'))) {
    include($abs_us_root . $us_url_root . 'usersc/scripts/token_error.php');
  }
}
?>

<div class="content mt-1">
  <div class="row">
    <div class="col-12">
      <a href="<?= $us_url_root ?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
      <h1>Configure the OAuth Client Plugin!</h1>
      <div class="row">
        <div class="col-12 col-md-5">
          <div class="card">
            <div class="card-header">
              <h3>OAuth Client Settings</h3>
            </div>
            <div class="card-body">
              <form action="" method="post">
                <input type="hidden" name="csrf" value="<?= Token::generate() ?>">
                <div class="form-group">
                  <label for="oauth">Enable OAuth Login</label>
                  <span id="oauth-status"><?php echo ($settings->oauth == 1) ? "(Currently Enabled)" : "(Currently Disabled)"; ?></span>
                  <span style="float:right;" class="form-check form-switch">
                    <label class="switch switch-text switch-success">
                      <input id="oauth" type="checkbox" class="switch-input form-check-input toggle" data-desc="OAuth Login" <?php if ($settings->oauth == 1) echo 'checked="true"'; ?>>
                      <span data-on="Yes" data-off="No" class="switch-label"></span>
                      <span class="switch-handle"></span>
                    </label>
                  </span>

                </div>


                <div class="form-group
                <label for=" server_url">Server URL</label>
                  <br>
                  <small>Enter the full url including the final slash such as <span style="color:red;">https://server_domain.com/</span></small>
                  <input type="text" class="form-control ajxtxt" data-table="plg_oauth_login" data-desc="Server URL" name="server_url" id="server_url" value="<?= $oauthSettings->server_url ?>">
                </div>
                <div class="form-group mt-1">
                  <label for="client_name">Client Name</label><br>
                  <small>This may be used if we allow one plugin to connect to multiple servers. </small>
                  <input type="text" class="form-control ajxtxt" data-table="plg_oauth_login" data-desc="Client Name" name="client_name" id="client_name" value="<?= $oauthSettings->client_name ?>">
                </div>

                <div class="form-group mt-1">
                  <label for="client_id">Client ID</label>
                  <br><small>Obtain this from the OAuth server</small>
                  <input type="password" class="form-control ajxtxt showOnHoverOnly" data-table="plg_oauth_login" data-desc="Client ID" name="client_id" id="client_id" value="<?= $oauthSettings->client_id ?>">


                </div>

                <div class="form-group mt-1">
                  <label for="client_secret">Client Secret</label>
                  <br><small>Obtain this from the OAuth server</small>
                  <input type="password" class="form-control ajxtxt showOnHoverOnly" data-table="plg_oauth_login" data-desc="Client Secret" name="client_secret" id="client_secret" value="<?= $oauthSettings->client_secret ?>">
                </div>

                <?php
                $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";

                $exampleRedirectUri = $baseUrl . $us_url_root . "usersc/plugins/oauth_login/parsers/oauth_response.php";
                ?>

                <div class="form-group mt-1">
                  <label for="redirect_uri">Redirect URI</label>
                  <br>
                  Example: <?= htmlspecialchars($exampleRedirectUri ?? "") ?>
                  <input type="text" class="form-control ajxtxt" data-table="plg_oauth_login" data-desc="Redirect URI" name="redirect_uri" id="redirect_uri" value="<?= htmlspecialchars($oauthSettings->redirect_uri ?? "") ?>">
                </div>

                <div class="form-group mt-1">
                  <label for="login_title">Login Title</label>
                  <br><small>This will be displayed in the UserSpice Login Form. You can customize it for your site.</small>
                  <input type="text" class="form-control ajxtxt" data-table="plg_oauth_login" data-desc="Login Title" name="login_title" id="login_title" value="<?= $oauthSettings->login_title ?>">
                </div>

                <div class="form-group mt-1">
                  <label for="client_icon">Login Icon</label>
                  <br><small>This is the icon will be displayed in the UserSpice Login Form.</small>
                  <select class="form-select ajxtxt" data-table="plg_oauth_login" data-desc="Login Icon" name="client_icon" id="client_icon">
                    <?php
                    foreach ($icons as $icon) {
                      $selected = ($icon == $oauthSettings->client_icon) ? 'selected' : '';
                      echo "<option value='$icon' $selected>$icon</option>";
                    }
                    ?>
                  </select>

                </div>
                <div class="form-group mt-1">
                  <label for="login_script">Login Script</label>
                  <br><small>This is the optional script that will be executed when the user logs in.</small>
                  <select class="form-select ajxtxt" data-table="plg_oauth_login" data-desc="Login Script" name="login_script" id="login_script">
                    <option value="">--No Login Script--</option>
                    <?php
                    foreach ($scripts as $script) {
                      $selected = ($script == $oauthSettings->login_script) ? 'selected' : '';
                      echo "<option value='$script' $selected>$script</option>";
                    }
                    ?>
                  </select>
                </div>
            </div>
          </div>
        </div>
        <div class="col-12 col-md-7">
          <div class="card">
            <div class="card-header">
              <h3>Recent OAuth Logins</h3>
            </div>
            <div class="card-body">
              <table class="table table-striped table-hover paginate">
                <thead>
                  <tr>
                    <th>User ID</th>
                    <th>Username</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>New</th>
                    <th>Timestamp</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $logins = $db->query("SELECT 
                  l.*,
                  u.username,
                  u.fname,
                  u.lname,
                  u.email
                  FROM plg_oauth_client_logins l
                  LEFT JOIN users u
                  ON l.user_id = u.id
                  ORDER BY l.id DESC LIMIT 250")->results();
                  foreach ($logins as $login) {
                    $new = ($login->new_user == 1) ? 'Yes' : 'No';
                  ?><tr>
                      <td><?= $login->user_id ?></td>
                      <td><?= $login->username ?></td>
                      <td><?= $login->fname . ' ' . $login->lname ?></td>
                      <td><?= $login->email ?></td>
                      <td><?= $new ?></td>
                      <td><?= $login->ts ?></td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="row mt-2">
    <div class="col-12">
      <h3>Documentation</h3>
      <p>If you appreciate this plugin and would like to make a donation to the author, you can do so at <a href="https://UserSpice.com/donate"><span style="color:blue;">https://UserSpice.com/donate</span></a>. Either way, thanks for using UserSpice!</p>

      <h5 class="mt-2">Setting Up the Server</h5>
      <p>If you have not already done so, please make sure that you have the UserSpice OAuth Server plugin installed and configured on another server.  Once you have done so, please generate a new "Client" for this instance, following the instructions over there.</p>

      <h5 class="mt-2">Configuring the Client</h5>
      <p>Once you have generated a new client on the server, you will need to enter the following information from the server into the form above:</p>
      <ul>
        <li>Server URL: The full URL of the server, including the final slash. For example, <span style="color:red;">https://yourdomain.com/</span></li>
        <li>Client ID: This is the ID that you generated on the server.</li>
        <li>Client Secret: This is the secret that you generated on the server.</li>
        <li>Redirect URI: This is the URL that the server will redirect to after the user logs in. It should be <span style="color:red;">https://yourdomain.com/usersc/plugins/oauth_login/parsers/oauth_response.php</span>.  You can customize this by copying this file to a new location, but it will be your responsibility to maintain it.</li>
      </ul>

      <p>Then you can customize your experience on this particular server by entering the following options.</p>
      <ul>
        <li>Login Title: This is the title that will be displayed on the login form.</li>
        <li>Login Icon: This is the icon that will be displayed on the login form.</li>
        <li>Login Script: This is an optional script that will be executed when the user logs in.</li>
      </ul>

      <h5 class="mt-2">Customization</h5>
      <p>On the client side, you can choose how this login will appear by changing your icon or the title.  Icons should be .png files placed in <span style="color:red">usersc/plugins/oauth_login/assets</span></p>
      <p>You can also customize the login form visitors will see on the server side by using a custom login form on the server. More information can be found on the server plugin.</p>

      <h5 class="mt-2">Scripts</h5>
      <p>This plugin makes use of two built in userspice scripts, <span style="color:red;">usersc/scripts/custom_login_script.php</span> and <span style="color:red;">usersc/scripts/during_user_creation.php</span></p>
      <p>Additionally, there are 2 scripts that are specific to this plugin. <span style="color:red;">usersc/plugins/oauth_login/login_scripts/</span> folder allows you to store a custom script that can be called when a user logs in via oauth. Note that in this script, you have access to $log['new_user'] which will be 1 if the user is new and 0 if they are existing.  Additionally, if you don't want new users to be able to be created from OAuth, you can rename the example script to <span style="color:red;">usersc/plugins/oauth_login/assets/before_user_creation.php</span> and follow the instructions in there. </p>
 

      <h5 class="mt-2">Alternatives</h5>
      <p>I have also created a Wordpress plugin, as well as examples for go, nodejs, vanilla php, python, react, rust, and more over at 
      <a target="_blank" style="color:blue;" href="https://github.com/mudmin/userspice-oauth-examples" title="UserSpice OAuth Examples">UserSpice OAuth Examples</a>
      </p>
    </div>
    <a href="https://www.flaticon.com/free-icons/authentication" title="authentication icons" class="mt-4">Authentication icons created by Freepik - Flaticon</a>
  </div>
  <script>

    $("#client_icon").change(function() {
      var icon = $(this).val();

      $.ajax({
        url: "<?=$us_url_root?>usersc/plugins/oauth_login/parsers/oauth_parser.php",
        type: "POST",
        data: {
          action: "icon",
          icon: icon
        },
        success: function(data) {
         console.log(data);
        }
      });
    });

  //login_title
  $("#login_title").change(function() {
      var title = $(this).val();

      $.ajax({
        url: "<?=$us_url_root?>usersc/plugins/oauth_login/parsers/oauth_parser.php",
        type: "POST",
        data: {
          action: "title",
          title: title
        },
        success: function(data) {
         console.log(data);
        }
      });
    });
    
    //change $("#oauth-status"). when switch flips
    $("#oauth").change(function() {
      var status = $(this).prop('checked');
      var statusText = (status) ? "(Currently Enabled)" : "(Currently Disabled)";
      $("#oauth-status").text(statusText);
    });
    

  </script>
  <!-- Do not close the content mt-1 div in this file -->