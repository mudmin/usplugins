<?php
if(count(get_included_files()) ==1) die();
?>
<br>
<div class="row">
  <div class="col-12">
    <h1>Webhook Plugin Documentation</h1>

    <h4>Basic Concepts</h4>
    <p>
      A webhook is similar to an API in that it's made to be "hit" from another application. The major difference (in the context of this plugin) is that a webhook is primarily designed to be one way communication from that other application to your server.  Some common examples of this include...
    </p>
    <p>Let's say you have a PayPal transaction that starts on your website and moves over to PayPal’s site.  That transaction may get held up for any number of reasons.  So, instead of assuming that the person will be redirected right back with all the proper information, you can setup for PayPal to hit your webhook and POST all of the relevant transaction information once the transaction is done.  This is great because the user may not ever actually return to your site, so PayPal makes sure you get all of the info and you can "release" the product or do whatever else you need to do.</p>
    <p>In another scenario, let's say you have a WordPress website and when a user does something (like makes a purchase) over on WordPress, you want to give them special access to something on your UserSpice site. You may want to create an account, add their email address to a special list or literally anything. WordPress can fire off a request to your webhook and that action can be automated.  The possibilities are endless.</p>

    <br>
    <h4>Webhook IDs</h4>
    <p>This plugin is a "webhook receiver." In other words, it provides a URL for you to direct all of your webhook requests to.  By default, that webhook will be at <br><b>https://yourdomain.com/usersc/plugins/webhooks/webhook.php?webhook_id=YOUR_ID</b>.<br></p>

    <p>That YOUR_ID is important because this one file can handle thousands of different webhook requests.  When you go back to the <b><em><a href="admin.php?view=plugins_config&plugin=webhooks">Home</a></b></em> section of this plugin, you can create as many new webhooks as you wish and each one will be given a unique id.  It's important that whoever is requesting it, requests the proper one. The easiest way to do that is to append it to the URL itself with ?webhook_id=1, for example.</p>

    <br>
    <h4>Configuring the Webhook</h4>
    <p>For your convenience, most of the options for configuring a webhook are defined to the right of the webhook creation form.  You decide who can hit your webhook, whether hitting the webhook will fire off a database query, run a PHP script or execute code on your server.  You decide how much information you want to be logged and if you want a second factor of authentication. </p>

    <br>
    <h4>Action Types</h4>
    <p>This plugin comes with three different "Action Types" that should handle most of your needs.</p>

    <p>The <b>Raw Database Query</b> action type allows you to run simple database queries when the webhook is hit.  The most common uses for this is if you want to run some simple database cleanup operation, insert some sort of log, or change a setting on the site. If you need more complicated queries and logic, you probably want to use the PHP option instead. </p>

    <p>The <b>Execute PHP Script</b> action type is the most common.  You put a script in usersc/plugins/webhooks/assets/ and that script will be called when the webhook is hit.  There is a sample testscript.php file that you should use as an example. The first few lines prevent the script from being called from anything other than the server itself.</p>

    <p>The <b>Execute Bash Script or System Command (exec)</b> action type is the most powerful and requires the most care.  This option gives you access to anything that is accessible to the underlying operating system from the web server. The two most common uses for this are executing bash scripts and issuing system commands.  It is 100% your responsibility to make sure that you understand the power of this and that you do it safely.  It's a powerful tool that can allow you to run git commands or even shutdown the server. </p>

    <br>
    <h4>GET POST and JSON</h4>
    <p>Webhooks are generally sent GET requests (in the URL), POSTed Key Value Pairs (like form data) or POSTed JSON blobs and this plugin not only handles all three, but turns them into one data set.</p>

    <p>Any GET, POST, or JSON will be turned into a $data array in that order. This means if you have the same "key" in GET it will be overwritten if that key is used in JSON. </p>

    <p>It's not obvious, but this entire $data variable will be available to you in any PHP scripts you run.  This is a powerful tool to allow you to process all the incoming data to your webhook. </p>

    <br>
    <h4>IP Filtering</h4>
    <p>The first line of security defense in this plugin is IP filtering. You can choose if a particular webhook can be hit by ANY IP (*), A single IP, or only IPs that are in your UserSpice IP Whitelist.</p>

    <br>
    <h4>Second Factor</h4>
    <p>Although webhooks do not use API keys, you can create your own special access Key Value Pair.  This can be passed to the webhook in GET, POST, or JSON, but it must be passed or the webhook transaction will fail.</p>

    <br>
    <h4>Logging</h4>
    <p>This plugin does two types of logging.  The first is Activity Logging.  As long as the IP making the request is not on the UserSpice IP Blacklist, the attempt to make a webhook connection will be logged in the <b><em><a href="admin.php?view=plugins_config&plugin=webhooks&method=activity">Activity Log</a></b></em>.</p>
    <p>The second type of logging is optional and it is a webhook data log. On the webhook create/edit screen, if you set log to "yes", all of that GET/POST/JSON data will be stored in the database as a big JSON string. You can view these logs under the "Existing Webhooks" chart at the bottom of the <b><em><a href="admin.php?view=plugins_config&plugin=webhooks">Home</a></b></em> section of this plugin</p>

    <p>You may find this handy for diagnostic purposes, but you may also find it useful because each of those logs is tied to the webhook_id.  This means you don't have to create a special table if you want to do something with that data later.  You can just query all the data from plg_webhook_data_logs WHERE hook = the id of your hook.</p>
  </div>

</div>
</div>
