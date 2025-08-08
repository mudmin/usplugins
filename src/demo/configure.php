<?php if (!in_array($user->data()->id, $master_account)) {
  Redirect::to($us_url_root . 'users/admin.php');
} //only allow master accounts to manage plugins! 
?>

<?php
include "plugin_info.php";
pluginActive($plugin_name);
if (!empty($_POST)) {
  if (!Token::check(Input::get('csrf'))) {
    include($abs_us_root . $us_url_root . 'usersc/scripts/token_error.php');
  }
}
?>

<div class="content mt-3">
  <div class="row">
    <div class="col-12">
      <a href="<?= $us_url_root ?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
      <h1>Configure the Demo Plugin!</h1>
      <p>
        This page is designed for you to configure and give basic information about your plugin.
      </p>
      <p>
        The demo plugin is designed to give you a basic framework for creating your own plugins. To create your own plugin:
      <ul>
        <li>Copy this plugin to a new folder in the usersc/plugins directory</li>
        <li>Do a case sensitive search for both "demo" and "Demo" and replace those with your new plugin folder name ("demo") and the plugin name "Demo."</li>
        <li>Update the info.xml file to give your plugin a description and add your author/version info.</li>
        <li>Edit the files for your own purposes</li>
        <li>Update the install.php and migrate.php files to create any new database tables, etc.</li>
        <li>Check out the hooks folder to learn how to hook into existing UserSpice files. See <a href="https://userspice.com/plugin-hooks/" style="color:blue">this page</a> for more info.</li>
        <li>Check out the menu_hooks folder to learn how to make a custom menu snippet available.</li>
      </ul>
      </p>

      <div class="row">
        <div class="col-12">
          <h2 class="mb-3">UserSpice Plugin File Structure</h2>

          <div class="card mb-4">
            <div class="card-header">
              <h3 class="h5 mb-0">Root Directory: usersc/plugins/plugin_name/</h3>
            </div>
            <div class="card-body">
              <p>The following is the standard file and folder structure for a UserSpice plugin. This structure follows the convention shown in the UserSpice demo plugin.</p>

              <pre class="bg-light p-3 border rounded"><code>usersc/plugins/plugin_name/
              The following is the standard file and folder structure for a UserSpice plugin. This structure follows the convention shown in the UserSpice demo plugin.
usersc/plugins/plugin_name/
│

├── configure.php           # Control panel/config page (protected from direct access)
├── footer.php              # Included in every page footer when active
├── functions.php           # Custom functions, included on every page load
├── header.php              # Included in every page header when active
├── info.xml                # Plugin metadata (name, author, version, config button)
├── install.php             # Installation script (tables, hooks, migrations)
├── logo.png                # 200×200 transparent PNG icon
├── migrate.php             # Numbered migrations for updates
├── override.rename.php     # Rename to override core functions safely
├── plugin_info.php         # Stores the folder name of the plugin so it can be used everywhere
│
├── assets/                 # Runtime assets
│   ├── css/                # Stylesheets
│   ├── js/                 # JavaScript files
│   ├── images/             # Image files
│   ├── includes/           # PHP includes
│   └── parsers/            # AJAX Parser Files
│
├── files/                  # Files to copy during install (e.g., dashboard views)
│   └── …
│
├── hooks/                  # Hook scripts to inject into core pages
│   ├── accountbottom.php   # e.g., bottom of account.php
│   └── …
│
└── menu_hooks/             # UltraMenu integration snippets
    └── menu_hook.php       # Example dynamic menu link</code></pre>
            </div>
          </div>

          <div class="card mb-4">
            <div class="card-header">
              <h3 class="h5 mb-0">Key Files Explained</h3>
            </div>
            <div class="card-body">
              <ul class="list-group list-group-flush">
                <li class="list-group-item">
                  <strong>info.xml</strong>: Contains metadata about the plugin including name, author, version, description, and the name of the configuration page (if any) between the <code>&lt;button&gt;</code> tags.
                </li>
                <li class="list-group-item">
                  <strong>install.php</strong>: Sets up database tables, registers hooks, and performs other installation tasks. This is where you define hook locations around line 32, for example:
                  <pre class="bg-light p-2 mt-2"><code>$hooks['account.php']['bottom'] = 'hooks/accountbottom.php';</code></pre>
                </li>

                <li class="list-group-item">
                  <strong>functions.php</strong>: Contains custom functions for the plugin. This file is included on every page when the plugin is active.
                </li>
                <li class="list-group-item">
                  <strong>header.php</strong>: Code to be included in the header of every page when the plugin is active.
                </li>
                <li class="list-group-item">
                  <strong>footer.php</strong>: Code to be included in the footer of every page when the plugin is active.
                </li>
                <li class="list-group-item">
                  <strong>migrate.php</strong>: Contains numbered migrations to update the plugin over time. This file is executed during plugin installation and update and can be used to modify the database schema or perform other updates. Do NOT use a variable named $check in there as it will break core functionality of the update.
                </li>
                <li class="list-group-item">
                  <strong>override.rename.php</strong>: A file that can be renamed by the user to override UserSpice's core functionality. This is ideal for things like email plugins where you would want the user to be able to override UserSpice's built in email function. Since that function is wrapped in if(!function_exists('email')){ ... } you can safely override it by renaming this file to override.php. This will allow you to use the same function name without conflict and every place where the built in function would have been called in the core, your new function will be used in its place.

                <li class="list-group-item">
                  <strong>configure.php</strong>: The control panel for the plugin. Often also includes documentation.
                </li>
              </ul>
            </div>
          </div>

          <div class="card mb-4">
            <div class="card-header">
              <h3 class="h5 mb-0">Folder Purposes</h3>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-4 mb-3">
                  <div class="card h-100">
                    <div class="card-header bg-light">
                      <strong>assets</strong>
                    </div>
                    <div class="card-body">
                      Contains files used during normal plugin operation like JavaScript, CSS, or supplementary PHP files.
                    </div>
                  </div>
                </div>
                <div class="col-md-4 mb-3">
                  <div class="card h-100">
                    <div class="card-header bg-light">
                      <strong>files</strong>
                    </div>
                    <div class="card-body">
                      Stores files that need to be copied to other locations during installation (e.g., adding a new view to the dashboard).
                    </div>
                  </div>
                </div>
                <div class="col-md-4 mb-3">
                  <div class="card h-100">
                    <div class="card-header bg-light">
                      <strong>hooks</strong>
                    </div>
                    <div class="card-body">
                      Contains PHP files used by the plugin hooks feature, allowing code injection into common pages without modifying those pages directly.
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="card mb-4">
            <div class="card-header">
              <h3 class="h5 mb-0">Best Practices</h3>
            </div>
            <div class="card-body">
              <ol>
                <li class="mb-2">New tables should be prefixed with plg_{plugin_name} or a short version of it to group them together.</li>
                <li class="mb-2">Do not repurpose the $settings variable for plugin settings, use a different variable like $plgSettings.</li>
                <li class="mb-2">Do not repurpose the $user variable. Especially in loops like foreach($users as $user); use $u instead.</li>
                <li class="mb-2">You do not need to call DB::getInstance(); use global $db in functions.</li>
              </ol>

            </div>
          </div>

          <div class="card mb-4">
            <div class="card-header">
              <h3 class="h5 mb-0">Important Notes</h3>
            </div>
            <div class="card-body">
              <ol>
                <li class="mb-2">All plugin files and folders should use lowercase names.</li>
                <li class="mb-2">Plugin hooks are automatically unregistered during uninstallation.</li>
                <li class="mb-2">The logo.png file should be a 200x200 clear PNG image placed in the root folder.</li>
                <li class="mb-2">Configure.php and other supplementary files should be protected from direct access.</li>
                <li class="mb-2">Files in functions.php, header.php, and footer.php are included on every page load, so code in them should be optimized and secure.</li>
              </ol>


              <p class="mb-0">This structure provides a standardized way to extend UserSpice functionality while keeping your custom code separate from the core system, making updates easier.</p>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>


  <!-- Do not close the content mt-3 div in this file -->