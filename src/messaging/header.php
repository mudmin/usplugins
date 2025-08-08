<?php
$msgSettings = $db->query("SELECT * FROM plg_msg_settings")->first();

      if (isset($user) && $user->isLoggedIn()) {
        $notifCount = fetchPLGMessageCount();
        $plgMessages = fetchPLGMessages(500);
      }
      ?>
