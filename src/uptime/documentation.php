<?php
require_once '../../../users/init.php';
require_once $abs_us_root.$us_url_root.'users/includes/template/prep.php';
if(!pluginActive("uptime",true) || !hasPerm([2],$user->data()->id)){
  die("nope");
}
?>
<style media="screen">
  .blue {
    color:blue;
  }
</style>
<div class="row">
  <div class="col-12">
    <h1>Uptime Plugin Documentation</h1>
    <p>If you appreciate this plugin and would like to make a donation to the author, you can do so at <a href="https://UserSpice.com/donate">https://UserSpice.com/donate</a>.
        Either way, thanks for using UserSpice!</p>

    <h3>Overview</h3>
    <p>
      The Uptime plugin is designed to serve as a dashboard for all your websites to make sure they are all online. If a site goes down you get a notification.  You get one when it comes back online.  You can also choose to get a notification every X minutes that your site remains offline.  You can add unlimited sites and notify unlimited emails (based on server limits) and it is 100% free.
    </p>

    <h3>Basic Concepts</h3>
    <p>
      This plugin has 3 important parts:  the Dashboard, the Cron Script, and the target.  You have already seen the dashboard.  It is where you configure your general settings.  You can also add servers to monitor (aka "Targets"), and check out logs of past server checks.
    </p>

    <p>
      The Cron Script is where the real magic happens.  Just like everything in UserSpice you're welcome to copy <b>usersc/plugins/uptime/uptime.php<b> and call it <b>usersc/plugins/uptime/whatever-u-want.php<b> and create your own custom rules and procedures, but the one we've given you works pretty great out of the box. This script needs to be hit by a Cron job and we'll talk about that below.
    </p>

    <p>
      The Target is any file on a website that you are "targeting" to see if the website is online.  It can be an image, but ideally it would be a php or html file.  This file <b>does not have to be</b> anything special. It can be an existing file on the website, but it MUST be accessable from anyone who is not logged in to the site.  So you can point to the existing index.php page for a website, or you can drop in one of our special target files. More on that below.
    </p>

    <h3>Configuring Notifications</h3>
    <p>
      At launch, there were two ways to send out notifications about site outages.  You could use the built in UserSpice email system to send an email notification. You could also install the free Pushover plugin from Spice Shaker. This is the author's preferred method. The service is free, but mobile apps have a one time fee of $4.99.
    </p>
    <p>
      To use email, you must properly configure your email on the <a class="blue" href="admin.php?view=email" target="_blank">Email Settings</a> page of the UserSpice Dashboard. Make sure you run the tests and that your emails are not going to spam.  Although you can send as many emails as you want, please check with your hosting provider to make sure you don't get shut down for "spamming."   Once everything is configured, you simply add any email address that you want to be notified on the dashboard.
    </p>
    <p>
      The <a class="blue"  href="admin.php?view=spice&search=pushover" target="_blank">Pushover Plugin</a> currently only allows you to send notifications to one Pushover account.  This is not a limitation of the service itself, so this could be extended in the future.  Pushover is great because it keeps these notifications out of the clutter of your email inbox. They're important and you want to see them
    </p>

    <h3>Your Targets</h3>
    <p>
      As stated above, any file on your webserver could be a target, but it's not a bad idea to upload a dedicated file to each server for this application to hit.  If your site is a UserSpice site, we provide a special target file called target.php located in <b>usersc/plugins/uptime/files</b>.  Please copy this to the root of your target UserSpice project.
    </p>
    <p>
      It is <b>strongly recommended</b> that you comment in the lines about ipCheck() in on your target file and add the ip address of this system (the one that is hitting all the targets) to the built in UserSpice IP whitelist.  This keeps the information this file discloses private to anyone other than the Uptime monitor.
    </p>
    <p>
      If your site is not a UserSpice site, you are free to copy the file called uptime_target.php from <b>usersc/plugins/uptime/files</b> to all of your non UserSpice servers and no other configuration is required on that server.
    </p>

    <h3>Configuring the Targets</h3>
    <p>
      Adding a target is super easy. In the plugin manager, simply give it a unique name and enter the FULL url (including the target filename).  If the target is a UserSpice site and has the UserSpice target.php file, select that option and you're good to go.  The reason for this last option is that not only lets you keep track of whether or not your sites are online, but what versions of UserSpice and PHP you are running on each server. Nifty.
    </p>

    <h3>Configuring the Cron Job</h3>
    <p>
      You may need the help of your hosting provider, but you will need to setup a Cron job to run every X number of minutes.  5 minutes seems like a good number, but you could go more or less often depending on your server and how much you care.  If you are using our default Cron Script, and you want to run it every 5 minutes, then your job will look something like this:
    </p>
    <p>
      <b>
      */5 * * * * curl https://yourdomain.com/usersc/plugins/uptime/uptime.php
      </b>
    </p>
    <p>...but it may look a little different on your hosting provider. </p>

    <h3>One Last Important Step</h3>
    <p>For security purposes, this uptime.php file can ONLY be triggered by IP addresses that are in your <a href="admin.php?view=ip" class="blue" target="_blank">UserSpice IP Whitelist</a>. This address may not be obvious, so after you setup your Cron job, check the bottom of the plugin config page and look for any logs that an invalid IP has hit your uptime.php. Add that IP to your whitelist and you will be good to go.</p>
  </div>
</div>
