<?php
//Please don't load code on the header of every page if you don't need it on the header of every page.
// bold("<br>Demo Header.php Loaded");
if(($settings->notifications == 1) && ($user->isLoggedIn())){ ?>
  <div id="messages" class="sufee-alert alert with-close alert-primary alert-dismissible fade show d-none">
    <span id="message"></span>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">&times;</span>
    </button>
  </div>
<?php
}
