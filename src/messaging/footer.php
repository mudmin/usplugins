<style>
  .openMessageButton:hover{
    cursor:pointer;
  }

  .plg-messaging-item:hover{
    background-color: transparent !important;
    cursor:pointer;
  }
  .plg-messaging-badge {
    position: relative;
    display: inline-block;
    border-radius: 0px;
  }

  .plg-messaging-badge-icon {
    position: relative;
    display: inline-block;
    font-size: 1rem;

  }



</style>
<script>

<?php
 $notifSettings = $db->query("SELECT * FROM plg_msg_settings")->first();

  if($notifSettings->ajax == 1 && $notifSettings->ajax_time > 0){
    ?>
      $(document).ready(function() {
        let plg_message_previewer = false;
      <?php if(isset($plg_message_previewer) && $plg_message_previewer == true){ ?>
        plg_message_previewer = true;
      <?php } ?>

      var ajaxTime = <?= $notifSettings->ajax_time ?> * 1000;
      var formData = {
        'preview' 		  : plg_message_previewer,
      };

      // === NOTIFICATION SOUND & TOAST SYSTEM ===
      <?php if($notifSettings->ding == 1){ ?>
      var plgMsgSoundsEnabled = true;
      <?php } else { ?>
      var plgMsgSoundsEnabled = false;
      <?php } ?>

      <?php if(!isset($notifSettings->show_toasts) || $notifSettings->show_toasts == 1){ ?>
      var plgMsgToastsEnabled = true;
      <?php } else { ?>
      var plgMsgToastsEnabled = false;
      <?php } ?>

      var plgMsgSoundsUnlocked = false;
      var plgMsgSoundPath = "<?= $us_url_root ?>usersc/plugins/messaging/assets/sounds/";
      var plgMsgFirstPoll = true; // Don't notify on first poll (page load)

      // Preload audio objects for each type
      var plgMsgSounds = {
        alert: new Audio(plgMsgSoundPath + "<?= $notifSettings->alerts_sound ?>"),
        notification: new Audio(plgMsgSoundPath + "<?= $notifSettings->notifications_sound ?>"),
        message: new Audio(plgMsgSoundPath + "<?= $notifSettings->messages_sound ?>")
      };

      // Preload all sounds
      Object.values(plgMsgSounds).forEach(function(audio) {
        audio.preload = "auto";
        audio.volume = 0.7;
      });

      // Track previous counts - initialize from server data, not DOM
      var plgMsgPrevCounts = {
        alert: <?= isset($notifCount->alert_count) ? (int)$notifCount->alert_count : 0 ?>,
        notification: <?= isset($notifCount->notification_count) ? (int)$notifCount->notification_count : 0 ?>,
        message: <?= isset($notifCount->message_count) ? (int)$notifCount->message_count : 0 ?>
      };

      // Unlock audio on first user interaction (browser autoplay policy)
      function unlockPlgMsgAudio() {
        if (plgMsgSoundsUnlocked) return;

        // Play silent/very short to unlock audio context
        Object.values(plgMsgSounds).forEach(function(audio) {
          audio.play().then(function() {
            audio.pause();
            audio.currentTime = 0;
          }).catch(function(e) {
            // Ignore errors during unlock attempt
          });
        });
        plgMsgSoundsUnlocked = true;
        console.log("PLG Messaging: Audio unlocked");
      }

      // Listen for user interaction to unlock audio
      document.addEventListener('click', unlockPlgMsgAudio, { once: true });
      document.addEventListener('keydown', unlockPlgMsgAudio, { once: true });
      document.addEventListener('touchstart', unlockPlgMsgAudio, { once: true });

      // Play notification sound
      function playPlgMsgSound(type) {
        if (!plgMsgSoundsEnabled) return;

        var audio = plgMsgSounds[type];
        if (audio) {
          audio.currentTime = 0;
          audio.play().catch(function(e) {
            console.log("PLG Messaging: Could not play sound - " + e.message);
          });
        }
      }

      // Show toast notification using UserSpice built-in toasts
      function showPlgMsgToast(type, count) {
        // Check if toasts are enabled
        if (!plgMsgToastsEnabled) {
          console.log("PLG Messaging: Toasts disabled, skipping toast for " + type);
          return;
        }

        var text;
        var diff = count;

        switch(type) {
          case 'alert':
            text = diff > 1 ? '<strong>New Alerts!</strong> You have ' + diff + ' new alerts' : '<strong>New Alert!</strong> You have a new alert';
            if (typeof usError === 'function') {
              usError(text);
            }
            break;
          case 'notification':
            text = diff > 1 ? '<strong>New Notifications!</strong> You have ' + diff + ' new notifications' : '<strong>New Notification!</strong> You have a new notification';
            if (typeof usPrimary === 'function') {
              usPrimary(text);
            }
            break;
          case 'message':
            text = diff > 1 ? '<strong>New Messages!</strong> You have ' + diff + ' new messages' : '<strong>New Message!</strong> You have a new message';
            if (typeof usInfo === 'function') {
              usInfo(text);
            }
            break;
        }

        console.log("PLG Messaging Toast: " + type + " - " + text);
      }

      // Check for new messages, play sounds, show toasts
      function checkAndPlaySounds(data) {
        var newAlerts = parseInt(data.alert_count) || 0;
        var newNotifications = parseInt(data.notification_count) || 0;
        var newMessages = parseInt(data.message_count) || 0;

        // Skip notifications on first poll (initial page load)
        if (plgMsgFirstPoll) {
          plgMsgFirstPoll = false;
          plgMsgPrevCounts.alert = newAlerts;
          plgMsgPrevCounts.notification = newNotifications;
          plgMsgPrevCounts.message = newMessages;
          return;
        }

        var hasNewMessages = false;

        // Check alerts
        if (newAlerts > plgMsgPrevCounts.alert) {
          var diff = newAlerts - plgMsgPrevCounts.alert;
          console.log("PLG Messaging: " + diff + " new alert(s) detected");
          playPlgMsgSound('alert');
          showPlgMsgToast('alert', diff);
          hasNewMessages = true;
        }

        // Check notifications
        if (newNotifications > plgMsgPrevCounts.notification) {
          var diff = newNotifications - plgMsgPrevCounts.notification;
          console.log("PLG Messaging: " + diff + " new notification(s) detected");
          if (!hasNewMessages) playPlgMsgSound('notification'); // Don't overlap sounds
          showPlgMsgToast('notification', diff);
          hasNewMessages = true;
        }

        // Check messages
        if (newMessages > plgMsgPrevCounts.message) {
          var diff = newMessages - plgMsgPrevCounts.message;
          console.log("PLG Messaging: " + diff + " new message(s) detected");
          if (!hasNewMessages) playPlgMsgSound('message');
          showPlgMsgToast('message', diff);
          hasNewMessages = true;
        }

        // Update previous counts
        plgMsgPrevCounts.alert = newAlerts;
        plgMsgPrevCounts.notification = newNotifications;
        plgMsgPrevCounts.message = newMessages;

        // Trigger event so modal can refresh if open
        if (hasNewMessages) {
          $(document).trigger('plgMessaging:newMessages', [data]);
        }
      }
      // === END NOTIFICATION SOUND & TOAST SYSTEM ===

      var ajaxTimer = setInterval(function() {
        $.ajax({
          url: "<?= $us_url_root ?>usersc/plugins/messaging/parsers/check_messages.php",
          data: formData,
          type: "POST",
          dataType: "json",
          success: function(data) {
            console.log(data);

            if (data.success) {
              // Check for new messages and play sounds BEFORE updating display
              checkAndPlaySounds(data);

              $(".type-3").html(data.message_count);
              $(".type-2").html(data.notification_count);
              $(".type-1").html(data.alert_count);
              if(plg_message_previewer == true && typeof data.preview !== "undefined"){
                $(".plg-messaging-preview").html(data.preview);
              }

            }
          }
        });
      }, ajaxTime);
    });


    <?php
  }

  ?>
</script>
<?php


  require_once $abs_us_root . $us_url_root . "usersc/plugins/messaging/assets/modals/messages.php";
  ?>