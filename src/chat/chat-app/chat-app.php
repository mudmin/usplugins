<?php
global $user, $cb;
if(isset($user) && $user->isLoggedIn()){
?>
<style media="screen">
  .hide{
    display:none;
  }
</style>
<!-- load additional resources  -->
<script src="https://kit.fontawesome.com/03bdbd7088.js" crossorigin="anonymous"></script>
<script>
  if(!document.getElementById('chat-styles-link')){
    var styles = document.createElement('link');
    styles.id = 'chat-styles-link';
    styles.rel = 'stylesheet';
    styles.href = '<?=$us_url_root?>/usersc/plugins/chat/chat-app/styles/chat-app.css?cb=<?=$cb?>';
    document.head.appendChild(styles);
  }

  if(!document.getElementById('chat-app-js-src')) {
    var script = document.createElement('script');
    script.id = 'chat-app-js-src';
    script.src = '<?=$us_url_root?>/usersc/plugins/chat/chat-app/chat-app.js?cb=<?=$cb?>';
    // script.defer = true;
    document.head.appendChild(script);
  }



  var script = document.createElement('script');
  script.id = 'emoji-button-src'
  script.src = '<?=$us_url_root?>/usersc/plugins/chat/chat-app/emoji-button.min.js?cb=<?=$cb?>';
  document.head.appendChild(script);

</script>

<section id="chatWindow" class="hide">
  <div class="control-hidden">
    <header>
      <div>Chat as <?= $user->data()->fname?> <?= $user->data()->lname?> <?= $user->data()->id?></div>
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
        <h3>Participants</h3>
        <ul id="chat-app-participants-list" >

        </ul>
      </aside>
    </div>
  </div>

</section>
<?php } ?>
