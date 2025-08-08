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
        <p class="pb-2">This plugin allows chatting between your users. It's up to you how you include the files.  For instance, you can wrap the includes in hasPerm or hasTag and the chat will only show for certain people. I also built in the concept of "event_ids" where this could <i>potentially</i> be used with each chat group having a unique chat id, but that is just as stub at the moment. It's hard coded to 1 now.</p>

        <p class="pb-2">
          To use the chat app on any page (or include) requires 2 things.
          <li>A button to toggle the chat window</li>
          <li>The chat app</li>
        </p>
        <p class="pb-2">
          There is now a menu snippet for adding the chat toggle button in your UltraMenu using a class. As of <b>1.0.3</b> you can use a class or id to toggle the chat window.  A class is preferred, but an id is available for legacy support.  
          The legacy way of doing it was...<br>
      
          
          It could be in your header or footer and look like this... <br>
          <img src="<?=$us_url_root?>usersc/plugins/chat/assets/button.png" alt="">
          (id or class)
          <br>
          The chat app looks like this (and should probably be in your footer)...
          <br>
          <code>
            require_once $abs_us_root.$us_url_root."usersc/plugins/chat/chat-app/chat-app.php";
          </code>
        </p>

        <p class="pb-2">
          <b>New to version 1.0.3</b>.  Support has been added for multiple rooms.  The plugin itself doesn't care how you create the rooms.  You can create a page and your own header or whatever you want.  The key is that you need to have a variable called <code>$custom_chat_room</code> set. You can also put a room name in the chat box by setting a <code>$custom_chat_title</code> variable. Both variables need to be set somewhere before you instantiate the chat app (think init or something else loaded by the header).  For example, you could store the chat room variable in the users table and do <code>if(isset($user) && $user->isLoggedIn() ){ $custom_chat_room = $user->data()->chat_room; } </code> in usersc/security_headers.php. The room name can also be a string if you want to allow your people to just spin up chat rooms by $_GET variables in the URL.  I don't care.

        </p>

        <p>
          I do care about your appreciation of the plugin! If you appreciate this plugin and would like to make a donation to the author, you can do so at <a href="https://UserSpice.com/donate">https://UserSpice.com/donate</a>. Either way, thanks for using UserSpice!
        </p>
      </div>
    </div>



    <!-- Do not close the content mt-3 div in this file -->
