<?php
require_once '../../../users/init.php';
require_once $abs_us_root.$us_url_root.'users/includes/template/prep.php';
if(!pluginActive("spicebin",true)){ die ("SpiceBin is disabled");}
?>
<style media="screen">
p{font-size:1.25em;}
</style>
<h2>Documentation</h2>
<p>Thank you for using the SpiceBin plugin. I hope you find it useful. While most of the settings are explained on the
  <a href="admin.php?view=plugins_config&plugin=spicebin" style="color:blue;">the configuration page</a>, there are some customization settings that need some further explanation.
</p>

<h3>The Default Pages</h3>
<p>In order to give you the best initial experience, this plugin works with essentially no configuration.  This means that I have designed some pages to help you create, view, and manage your pastes.  For conveninece, these pages are located in the usersc/plugins/spicebin/files folder and they're called create.php, view.php, and user.php.  If you're fine with that, you can leave things alone.  However, if you don't like the pages buried that deep, you can make your own pages. It's not as hard as it sounds.</p>

<p>To create your own page, only requires minimal code changes.  Let's say that you want to put your own create "create.php" but instead, you want to call it "create_blurb.php" and you want to put it in the root of your project.  Fantastic. Copy the <b>usersc/plugins/spicebin/files/user.php</b> file to <b>/create_blurb.php</b> and open the file in your favorite editor.  The only line you need to change is that <b>require_once '../../../../users/init.php';</b> line to
  <b>require_once 'users/init.php';</b> since init.php is now one folder "forward" of the php file as opposed to 4 folders back.
</p>
<p>Now, the only other thing you have to do update the config page to let it know that your create file is now in "create_blurb.php" instead of "usersc/plugins/spicebin/files/create.php" and you are good to go.</p>

<h3>Styling and Functionality Changes</h3>
<p>If you want to change the styling and functionality of our pages, simply copy them and rename them inside the usersc/plugins/spicebin/files folder.  For instance, if you want to copy _create_paste.php to _create_custom.php you can do that and put your file in as the include instead of ours.
</p>

<h3>Auto Deleting</h3>
<p>
  The auto delete feature is designed to be more for maintenance than a hard delete. To avoid overloading the database, something needs to trigger the actual deletion. This can be done in one of two ways.  It can be done automatically when an administrator logs in or it can be done via cron job.
</p>
<p>
  You may need the help of your hosting provider, but you will need to setup a Cron job to at an interval. If you want to run your delete script to run every morning at 1am, then your job may look something like this:

  0 1 * * * curl https://yourdomain.com/usersc/plugins/spicebin/files/cron_target.php

  ...but it may look a little different on your hosting provider.
</p>
<p>One other important note about the Cron job is that you MUST specify the IP address that is allowed to trigger this job. This prevents any outsider from triggering that script in their browser. This IP address is normally the IP of the server itself, but could be something else depending on your hosting provider. The best way to test is to set the job to run more often and then check your
  <a href="<?=$us_url_root?>users/admin.php?view=logs" style="color:blue">System Logs</a>. You will see either a success or fail message.  If the Cron fails, you can update the address that is allowed to run crons on the <a href="<?=$us_url_root?>users/admin.php?view=general" style="color:blue">General Settings</a> page.
</p>

<p>
  Setting the auto delete days to 0 will prevent any pastes from being auto deleted.
</p>
<p>
  It's important to note that the date a paste will be deleted happens whenever a paste is created.  So if the limit is 120 days when a user creates a paste and you drop it down to 30, they will be "grandfathered in" temporarily to the old 120 day limit.
</p>
<p>
  The reason I say grandfathere'd in is because the delete date is <em>from the last date a paste was viewed</em>.  This is because viewing a paste refreshes its date.  This allows you to keep well used pastes in your database, while clearing out things that are not used.
</p>
<?php
require_once $abs_us_root . $us_url_root . 'users/includes/html_footer.php';
