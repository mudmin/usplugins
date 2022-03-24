<?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

  <?php
  include "plugin_info.php";
  pluginActive($plugin_name);
  if(!empty($_POST)){
    if(!Token::check(Input::get('csrf'))){
      include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
    }
    // Redirect::to('admin.php?err=I+agree!!!');
  }
  ?>

  <div class="content mt-3">
    <div class="row">
      <div class="col-12">
        <a href="<?=$us_url_root?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
        <h1>Configure the Chat Plugin!</h1>
        <p>This plugin allows chatting between your users. It's up to you how you include the files.  For instance, you can wrap the includes in hasPerm or hasTag and the chat will only show for certain people. I also built in the concept of "event_ids" where this could <i>potentially</i> be used with each chat group having a unique chat id, but that is just as stub at the moment. It's hard coded to 1 now.</p>

        <p>
          To use the chat app on any page (or include) requires 2 things.
          <li>A button to toggle the chat window</li>
          <li>The chat app</li>
        </p>
        <p>
          For instance the button with an id of "toggleChatWindowBtn". It could be in your header or footer and look like this...
          <img src="<?=$us_url_root?>usersc/plugins/chat/assets/button.png" alt="">
          <br>
          The chat app looks like this...
          <code>
            require_once $abs_us_root.$us_url_root."usersc/plugins/chat/chat-app/chat-app.php";
          </code>
        </p>

        <p>
          If you appreciate this plugin and would like to make a donation to the author, you can do so at <a href="https://UserSpice.com/donate">https://UserSpice.com/donate</a>. Either way, thanks for using UserSpice!
        </p>
      </div>
    </div>


    <!-- Do not close the content mt-3 div in this file -->
