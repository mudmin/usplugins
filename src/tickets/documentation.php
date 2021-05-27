<?php
require_once "../../../users/init.php";
require_once $abs_us_root.$us_url_root.'users/includes/template/prep.php';
if(!pluginActive("tickets",true)){ die("Tickets plugin not active");}
if(!hasPerm([2],$user->data()->id)){
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
    <h1>Support Tickets Plugin Documentation</h1>
    <p>If you appreciate this plugin and would like to make a donation to the author, you can do so at <a href="https://UserSpice.com/donate">https://UserSpice.com/donate</a>.
        Either way, thanks for using UserSpice!</p>

    <h3>Overview</h3>
    <p>
      The thing that has prevented me from writing this plugin for a long time is the fact that I know everyone wants to customize their ticket system.  While this plugin may not handle every single option you could ever want, it serves as a good starting point.  Many things in the plugin are customizable directly and things like the ticket chart, individual ticket view, and ticket creator are all able to be scrapped in favor of your own designs.
    </p>
    <p>
      <b>This plugin requires the Official UserSpice Form Builder Plugin.</b>  This allows you to customize your form without any coding. Any additional fields you add will even automatically be added to the single ticket view.
    </p>

    <h3>Editing the Plugin</h3>
    <p>
      In a word, don't.  If you want to fork the plugin, copy it to a new folder and edit away.  If you want to edit create_ticket.php, ticket.php, or tickets.php, create new files and update the settings on the Configuration page to show the new paths.
    </p>

    <h3>Basic Configuration</h3>
    <p>
      Most of the config on the Configuration page is pretty self explanatory.  You choose what permission level can work on tickets and which one can do things like assign tickets to other agents.  If you don't have a permission that works for this, create one.  There are a few terms that are customizable. If you would rather use the term Department instead of Category or the term SuperHero instead of Agent, this page lets you do that.  You can also create unlimited status' and categories.
    </p>

    <h3>Creating Tickets</h3>
    <p>
      You can (and may want to) create your own "Create a Ticket" page.  The one we have, however is setup to be used in one of two ways.  You can access it directly in usersc/plugins/tickets/create_ticket.php to get a really basic, ugly page.  Or, you can do <br>
      include $abs_us_root.$us_url_root."usersc/plugins/tickets/create_ticket.php"; <br>
      on an existing php page (The user must be logged in!) and that view will just pop up in the middle of your page. This is the preferred solution because you can give it a proper title and styling.
    </p>

    <h3>Managing Tickets</h3>
    <p>
      There's not really a whole lot to say here. Agents get an extra panel at the top that allows them to change the category or status of a ticket. Note that open and closed are separate from the other dropdowns. This means that a ticket can be any status and open or closed.  Closed just means you're done with it.
    </p>

    <h3>Forms</h3>
    <p>
      As stated, this plugin uses the Form Builder, and you can add additional fields to your form using the form builder.  Just go in there and edit the form called plg_tickets.
    </p>

    <h3>Why can't I edit / delete this or that?</h3>
    <p>
      This plugin took a lot of time to develop and the more CRUD (Create, Read, Update, Delete) things I add, just take more time and add bulk to the plugin.  All of the data for this plugin is stored in tables beginning with "plg_tickets" so you can use the Quick CRUD plugin to make super easy (as in only a few lines of code) CRUD pages for all the built in features.
    </p>

    <h3>Emails</h3>
    <p>
      Email is annoying and often hard to diagnose.  It's recommended that you keep these features off until you have time to test them.  Currently if you have emails in the "people to email when there is a new ticket" box, they will be sent an email when a new ticket is created. Sending email takes time and will slow down the ticket creation process, so you may want to consider sending to an email alias if you have a lot of people to notify.  If you enable the users/agents emails, new emails go out when someone else comments on the ticket. So if you want the end user to get a notification, leave a comment.
    </p>
    <p>On the initial build of this plugin, I'm minimizing the number of emails that are sent.  More emails and logging will come over time as the bugs are worked out.</p>

    <h3>Custom Development</h3>
    <p>If you need a custom developed ticket system or any other plugin, consider reaching out to me <a href="https://userspice.com/custom-userspice-development/" class="blue">at the UserSpice website.</a> </p>
  </div>
</div>
