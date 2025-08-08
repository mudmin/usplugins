<?php
global $user, $cb, $us_url_root;
if(isset($user) && $user->isLoggedIn() && pluginActive("chat",true)){
require_once $abs_us_root.$us_url_root."usersc/plugins/chat/chat-app/chat-app-js.php";
require_once $abs_us_root.$us_url_root."usersc/plugins/chat/chat-app/styles/chat-app-style.php";
?>
<style media="screen">
  .hide{
    display:none;
  }
  #chatWindow{
    z-index:9999 !important;
  }
</style>
<!-- load additional resources  -->
<script src="https://kit.fontawesome.com/03bdbd7088.js" crossorigin="anonymous"></script>


<section id="chatWindow" class="hide">
  <div class="control-hidden">
    <header>
      <div>Chat as <?= $user->data()->fname?> <?= $user->data()->lname?></div>
      <div>
        <a href="#" id="chat-window-drag-handle" class="grip"><i class="fas fa-grip-vertical"></i></a>
        <a href="#" onclick="closeChat();" class="close"><i class="fas fa-times"></i></a>
      </div>

    </header>

    <div class="content-wrapper">

      <div class="msg-container">
        <div id="chat-app-msg-body" class="msg-body">
        </div>

        <div class="msg-form">
          <div style="display:flex;justify-content:space-between;align-items:center;padding-left:8px;position:relative;">
            <a href="#" id="emoji-trigger"><i class="fas fa-smile"></i></a>
            <a href="#" id="addMsgBtn"><i class="fas fa-paper-plane"></i></a>
          </div>
          <textarea name="chat-msg-value" id="chat-msg-value" rows="2" placeholder="Message" data-emoji-picker="true"></textarea>
        </div>
      </div>



      <aside class="participants">

        <h3>
          <?php if(isset($custom_chat_title)){
                echo $custom_chat_title;
              }
             ?>
             Participants
          </h3>
        <ul id="chat-app-participants-list" >

        </ul>
      </aside>
    </div>
  </div>

</section>
<?php } ?>
