# UserSpice Plugin File Structure

## Root Directory: usersc/plugins/plugin_name/

The following is the standard file and folder structure for a UserSpice plugin. This structure follows the convention shown in the UserSpice demo plugin.
usersc/plugins/plugin_name/
│
├── activate.php            # Special scripts that run only on plugin activation
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
    └── menu_hook.php       # Example dynamic menu link
## Key Files Explained

* **info.xml**: Contains metadata about the plugin including name, author, version, description, and the name of the configuration page (if any) between the `<button>` tags.
* **install.php**: Sets up database tables, registers hooks, and performs other installation tasks. This is where you define hook locations around line 32, for example:
    ```php
    $hooks['account.php']['bottom'] = 'hooks/accountbottom.php';
    ```
* **activate.php**: Most of the time this file can just be copied verbatim
* **functions.php**: Contains custom functions for the plugin. This file is included on every page when the plugin is active.
* **header.php**: Code to be included in the header of every page when the plugin is active.
* **footer.php**: Code to be included in the footer of every page when the plugin is active.
* **migrate.php**: Contains numbered migrations to update the plugin over time. This file is executed during plugin installation and update and can be used to modify the database schema or perform other updates. If you add a db column on install and want to drop it later, create a new migration wrapped in  ```php 
  $update = '00001';
  if(!in_array($update,$existing)){
  logger($user->data()->id,"Migrations","$update migration triggered for $plugin_name");

  $existing[] = $update; //add the update you just did to the existing update array
  $count++;
  }
  ```

Do NOT use a variable named $check in there as it will break core functionality of the update.
* **override.rename.php**: A file that can be renamed by the user to override UserSpice's core functionality. This is ideal for things like email plugins where you would want the user to be able to override UserSpice's built in email function. Since that function is wrapped in if(!function_exists('email')){ ... } you can safely override it by renaming this file to override.php. This will allow you to use the same function name without conflict and every place where the built in function would have been called in the core, your new function will be used in its place.
* **configure.php**: The control panel for the plugin. Generally also includes plugin documentation. 

## Folder Purposes

* **assets**
    Contains files used during normal plugin operation like JavaScript, CSS, or supplementary PHP files.
* **files**
    Stores files that need to be copied to other locations during installation (e.g., adding a new view to the dashboard).
* **hooks**
    Contains PHP files used by the plugin hooks feature, allowing code injection into common pages without modifying those pages directly.
* **menu_hooks**
    Contains PHP files used in UltraMenu to add dynamic menu links.

## Best Practices

1. New tables should be prefixed with plg_{plugin_name} or a short version of it to group them together.
2. Do not repurpose the $settings variable for plugin settings, use a different variable like $plgSettings.
3. Do not repurpose the $user variable. Especially in loops like foreach($users as $user); use $u instead.
4. You do not need to call DB::getInstance(); use global $db in functions. 
5. All forms should have a csrf token with
    ```php
    <?=tokenHere();?>
    ```
    and
    ```php
    if (!empty($_POST)) {
  if (!Token::check(Input::get('csrf'))) {
    include($abs_us_root . $us_url_root . 'usersc/scripts/token_error.php');
  }
  //continue post
    ```
6. Ajax parsers should be located in the assets/parsers folder.
7. All input should be sanitized with Input::get whether get or post.
8. DB inserts do not return the id.  You must call 
    ```php
    $id = $db->lastId();
    ```
    after the insert.

## Important Notes

1.  All plugin files and folders should use lowercase names.
2.  Plugin hooks are automatically unregistered during uninstallation.
3.  The logo.png file should be a 200x200 clear PNG image placed in the root folder.
4.  Configure.php and other supplementary files should be protected from direct access.
5.  Files in functions.php, header.php, and footer.php are included on every page load, so code in them should be optimized and secure.


This structure provides a standardized way to extend UserSpice functionality while keeping your custom code separate from the core system, making updates easier.

Demo Plugin [https://github.com/mudmin/usplugins/tree/master/src/demo](https://github.com/mudmin/usplugins/tree/master/src/demo)
Other Plugin examples can be found at [https://github.com/mudmin/usplugins/tree/master/src](https://github.com/mudmin/usplugins/tree/master/src)