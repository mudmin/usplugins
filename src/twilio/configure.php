<?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

  <?php
  include "plugin_info.php";
  pluginActive($plugin_name);
  $twil = $db->query("SELECT * from plg_twilio_settings")->first();
  if(!empty($_POST)){
    $token = $_POST['csrf'];
    if(!Token::check($token)){
      include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
    }
    if(!empty($_POST['saveSettings'])){
      $fields = [
        'sid'=>Input::get('sid'),
        'token'=>Input::get('token'),
        'primary'=>Input::get('primary'),

      ];
      $db->update("plg_twilio_settings",1,$fields);
      Redirect::to("admin.php?view=plugins_config&plugin=twilio&err=Saved!");
    }
  }
  $token = Token::generate();
  ?>
  <style media="screen">
  .blue {
    color:blue;
  }
  p {
    color: black;
  }
</style>
<div class="content mt-3">
  <div class="row">
    <div class="col-sm-12">
      <a href="<?=$us_url_root?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
      <h1>Configure the Twilio Plugin!</h1>

      <form class="" action="" method="post">
        <input type="hidden" name="csrf" value="<?=Token::generate();?>">
        <div class="row">

          <div class="col-12 col-sm-3">
            Twilio SID
            <input type="password" name="sid" value="<?=$twil->sid?>" class="form-control">
          </div>

          <div class="col-12 col-sm-3">
            Twilio Token
            <input type="password" name="token" value="<?=$twil->token?>" class="form-control">
          </div>
          <div class="col-12 col-sm-4">
            Primary Twilio Phone (begins with +countryCode)
            <input type="text" name="primary" value="<?=$twil->primary?>" class="form-control">
          </div>
          <div class="col-12 col-sm-1">
            <br>
            <input type="submit" name="saveSettings" value="Save" class="btn btn-primary">
          </div>
        </form>
      </div>
    </div> <!-- /.col -->
  </div> <!-- /.row -->
  <br>
  <h4>Documentation</h4>
  <p>
    Twilio is super easy to setup. No credit card required. Free trial and you get enough free credit to buy a phone number and send a bunch of messages.
    <a href="https://www.twilio.com/referral/pnDYsm" class="blue" target="_blank">If you use my link, we each get an extra $10. </a>
  </p>

  <h5>Configuration</h5>
  <p>
    When you sign up for Twilio, using the link above, they will give you at least $10 in credit. The first thing you need to do is use some of that credit to "purchase" a phone number.  They're about $1 per month.  Most people only need one, and it's handy for you to set one as your "primary" phone number in the settings above.  Then grab your "Account SID" and "Auth Token" off the dashboard and enter them above and you're ready to rock.
  </p>

  <h5>Formatting Numbers</h5>
  <p>
    Twilio expects you to format phone numbers like this <br>
    <span style="color:blue">+</span><span style="color:red">countryCode</span><span style="color:green">phoneDigits</span><br>
    So, in the USA and Canada, that would look like<br>
    <span style="color:blue">+</span><span style="color:red">1</span><span style="color:green">4075318008</span><br>
    We do provide the function "twilio" to help clean up phone numbers. More on that below.
  </p>

  <h5>Using this Plugin</h5>
  <p>
    <span style="color:red">function twilsms($message,$to,$from = "", log=false)</span>
    <br>
    Simply provide a text only message, a properly formatted number to send to and from and you're good to go.  If you do not provide a from number, it will send from your "primary" number that you specified above. Note that with SMS, you are charged in 160 character segments (about $.03 each), so you want to keep the messages short. Also note, that in free trial mode, they prepend a "sent from twilio trial," so that takes some space too.

    You can provide an optional 4th argument of true to log this text message.
  </p>

  <p>
    <span style="color:red">function twilio($data)</span>
    <br>
    Everyone has a different way of writing phone numbers, from dots to spaces to parentheses. This function strips those out and adds the plus to the beginning of the phone number.  You can call <br>
    <span style="color:green">twilsms("test message",twilio("(407)531-8008"));</span><br>
    And it will clean up that number formatting. Note that it does no validation.  Just clean up. It's your job to make sure you have a valid number.
  </p>
  <h5>Error Handling</h5>
  <p>
    The twilsms function has a try catch statement that will catch any errors and provide validation that the message was sent.  You can either grab the messages in php with <br>
    <span style="color:green">$sms = twilsms("test message","+14075318008");</span><br>
    and then checking the response in the $sms variable or you can log the errors with <br>
    <span style="color:green">twilsms("test message","+14075318008","",true);</span><br>
  </p>
  <h5>This is Just the Beginning</h5>
  <p>
    Well, if all you need to do is send a few SMS messages for notifications, I guess it's the end.  But really, this plugin gives you access to all of Twilio's features.  You can create your own functions for all of Twilio's <a href="https://www.twilio.com/code-exchange?q=&f=php">Code Exchange</a> library where you can see what other people are doing with it. You can receive SMS and even make browser based phone calls.
  </p>

  <h5>Contribute</h5>
  <p>
    If you expand this plugin, we'd love to see what you do! If you appreciate this plugin and would like to make a donation to the author, you can do so at <a href="https://UserSpice.com/donate">https://UserSpice.com/donate</a>. Either way, thanks for using UserSpice!
  </p>
