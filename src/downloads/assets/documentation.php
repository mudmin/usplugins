<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted ?>

<div class="row">
  <div class="col-sm-12">
    <br>
    <h3>Documentation</h3>
    <p>
      <b>Quick Start</b><br>
      1. Setup the basic plugin settings <a href="admin.php?view=plugins_config&plugin=downloads&v=home">here</a>. <br>
      2. Copy your files to usersc/plugins/downloads/files <br>
      3. Add your files to the database <a href="admin.php?view=plugins_config&plugin=downloads&v=files">here</a>.
    </p>
    
    <p><b>Please Note: </b> This plugin is designed to be flexible and provide protection for your downloads.  It is NOT designed to be a public facing UI for a full download system.
    In other words, it will give you the tools you need to generate secure links and decide who can download what, but how you share those links with your end users is up to you.  </p>

    <p>Store your uploads in usersc/plugins/downloads/files.  You can add more subfolders <b>but be sure to put the .htaccess file</b> from the files folder in there to prevent accessing from outside the server.</p>

    <h4>Important Terms</h4>

    <p><b>Location</b><br>
      Whenever location is mentioned in the tables, it is referring to the location of the actual physical file that will be served, from the usersc/plugins/downloads/files folder.
    </p>

    <p><b>Filename</b><br>
      Filename is the name that the file will automatically be given when it is downloaded.  This does not have to match the true filename on the server, but it will by default.
    </p>

    <p><b>Code</b><br>
      The download link is essentially a combination of the file id and a time-based random string.  This random string is the code that is passed to the download parser.  We may make an option for pretty links, although there is already a pretty url plugin.
    </p>

    <p><b>Mode</b><br>
      In order to make this plugin as flexible as possible, it can be put in several "modes" to determine who can download what.  To maintain maximum flexibility, you may be able to adjust settings on a file or link that may be overridden by the mode.  In other words, if you adjust a link to only allow users with a certain permission level to download that file and then put the plugin in "Registered users can download all files (Mode 2)", those settings will be ignored.  It's important to note that the settings themselves will be maintained so you do not have to re-set them when you change to a mode that uses them.
    </p>

    <p><b>Folders</b><br>
      You can put as many folders you want in usersc/plugins/downloads/files to help you organize your downloads.  It's important to note that you MUST copy the .htaccess file from the /files to /files/yourfolder to prevent outside access to your downloads.  It should also be noted that you can only go one folder deep beyond files/.  We do not support nested folders and do not plan to do so.  Putting your downloads in folders will also give you the option to bulk create links from those folders.
    </p>

    <p><b>Direct Links</b><br>
      Direct Links are tied to the file itself in the database. They are simpler to setup but have less flexibility. They do not have per file permissions, download limits, or expiration dates.  If you want simple and use a mode such as 2 or 3, you will be using direct links.  You can see the direct links to your files <a href="admin.php?view=plugins_config&plugin=downloads&v=files">here</a>.
    </p>

    <p><b>Custom Links</b><br>
      Custom links allow you to add cascading rules and create custom download links.  It's important to note that EVERY rule that is anything other than "blank" will count. So if a user does not meet one of the rules, the download will not be allowed.  You can see your custom links <a href="admin.php?view=plugins_config&plugin=downloads&v=links">here</a>.
    </p>

    <p><b>Generating Custom Links</b><br>
      There is a built in function for generating custom links that take advantage of all the security features of the download plugin.  As stated above, you can pass multiple rules to one download link. At minimum you must pass the id from plg_download_files to the function. <br>
      <b>generatePluginDownloadLink($fileid,$uid = "",$perms = "", $max = "", $expires = "")</b>
      <b>fileid</b> is the id from the files table.<br>
      <b>uid</b> is optionally the single user id who is allowed to download this file.<br>
      <b>perms</b> is an comma separated list of user permissions allowed to download this file.<br>
      <b>max</b> is the number of times this link can be used.<br>
      <b>expires</b> is the last datetime this link can be used such as 2021-12-31 23:59:59<br>
    </p>

    </div> <!-- /.col -->
</div> <!-- /.row -->
