<?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

  <?php
  include "plugin_info.php";
  pluginActive($plugin_name);
  $send = $db->query("SELECT * FROM plg_sendinblue")->first();
  if(!empty($_POST)){
    $token = $_POST['csrf'];
    if(!Token::check($token)){
      include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
    }

    $fields = [
      'from'=>Input::get('from'),
      'from_name'=>Input::get('from_name'),
      'reply'=>Input::get('reply'),
      'override'=>Input::get('override'),
      'key'=>Input::get('key'),
    ];
    $db->update("plg_sendinblue",1,$fields);
    Redirect::to('admin.php?view=plugins_config&plugin=sendinblue&msg=Settings saved');
  }
  $token = Token::generate();
  ?>
  <div class="content mt-3">

    <a href="<?=$us_url_root?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
    <h1>Configure the Sendinblue Plugin!</h1>
    <form class="" action="" method="post">
      <input type="hidden" name="csrf" value="<?=Token::generate()?>">

      <div class="row">
        <div class="col-12 col-sm-4">
          <label for="">From Email</label>
          <input type="text" name="from" value="<?=$send->from?>" required class="form-control">
        </div>
        <div class="col-12 col-sm-4">
          <label for="">Reply to Email (Usually the same)</label>
          <input type="text" name="reply" value="<?=$send->reply?>" required class="form-control">
        </div>

        <div class="col-12 col-sm-4">
          <label for="">Email "From" Name</label>
          <input type="text" name="from_name" value="<?=$send->from_name?>" required class="form-control">
        </div>

      </div>

      <div class="row">
        <div class="col-12 col-sm-12">
          <label for="">API Key</label>
            <div class="input-group">
            <input type="password" name="key" value="<?=$send->key?>" required class="form-control">
            <input type="submit" name="save" value="Save" class="btn btn-primary">
          </div>
        </div>
      </div>
      </form>


<div class="row" style="padding-top:2em;">
  <div class="col-12">
    <h2>Documentation</h2>
    <p>Sendinblue lets you send 300 emails per day, free of charge (no credit card required), which is perfect for password resets etc for most UserSpice projects. Get started by visiting <a href="https://www.sendinblue.com/">https://www.sendinblue.com</a> and creating an account.  Don't worry about filling out most of the information other than the basic contact/business info. You can even use a Gmail account to sign up and proxy your emails through their server. This avoids a ton of annoying email setup and configuration.  </p>
    <p>
      Once you've created your account and verified your email, go in the upper right hand corner and click on your email address and the menu will drop down. Select SMTP & API. Click the button to create a new API key and copy that key. Paste it in the settings above.  Fill out the other obvious information above and you're good to go.
    </p>
    <p>
      By default, this plugin gives you a function called <strong>sendinblue($to,$subject,$body,$to_name = "")</strong>
    </p>
    <p>
      The plugin automatically logs errors, but you can also do something like <strong>$send = sendinblue($to,$subject,$body);</strong> to have those messages returned to you immediately.
    </p>
    <p>Simply call the function just like the built in UserSpice email function and you are good to go.  If you would like to override the built in UserSpice email function and use Sendinblue instead, simply rename the file called override.rename.php to override.php</p>

    <p>If you appreciate this plugin and would like to make a donation to the author, you can do so at <strong><a href="https://UserSpice.com/donate">https://UserSpice.com/donate</a></strong>. Either way, thanks for using UserSpice!</p>
  </div>
</div>
  </div> <!-- /.row -->
