<style>
  .openMessageButton:hover {
    cursor: pointer;
  }

  .plg-messaging-item:hover {
    background-color: transparent !important;
    cursor: pointer;
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

  if ($notifSettings->ajax == 1 && $notifSettings->ajax_time > 0) {
  ?>
    $(document).ready(function() {
      let plg_message_previewer = false;
      <?php if (isset($plg_message_previewer) && $plg_message_previewer == true) { ?>
        plg_message_previewer = true;
      <?php } ?>

      function playAudioWithInteraction(audio) {
        var played = audio.play();
        if (played !== undefined) {
          played.then(_ => {
            // Audio playback started
          }).catch(error => {
            // Audio playback failed - add a one-time event listener
            var playAttemptAfterInteraction = function() {
              audio.play().then(_ => {
                // Remove event listener after successful playback
                document.removeEventListener('click', playAttemptAfterInteraction);
                document.removeEventListener('touchstart', playAttemptAfterInteraction);
                document.removeEventListener('keydown', playAttemptAfterInteraction);
              }).catch(_ => {
                // Playback failed again, likely due to another reason
              });
            };
            // Add event listeners to retry playback after user interaction
            document.addEventListener('click', playAttemptAfterInteraction, {
              once: true
            });
            document.addEventListener('touchstart', playAttemptAfterInteraction, {
              once: true
            });
            document.addEventListener('keydown', playAttemptAfterInteraction, {
              once: true
            });
          });
        }
      }
      var ajaxTime = <?= $notifSettings->ajax_time ?> * 1000;
      var formData = {
        'preview': plg_message_previewer,
      };
      var ajaxTimer = setInterval(function() {
        $.ajax({
          url: "<?= $us_url_root ?>usersc/plugins/messaging/parsers/check_messages.php",
          data: formData,
          type: "POST",
          dataType: "json",
          success: function(data) {
            console.log(data);

            if (data.success) {
              // Map type numbers to data properties
              var typeMappings = {
                3: 'message_count',
                2: 'notification_count',
                1: 'alert_count'
              };

              // Update HTML and conditionally remove 'd-none' class
              $.each(typeMappings, function(type, countType) {
                $(".type-" + type).html(data[countType]);
                if (data[countType] > 0) {
                  $(".openMessageButton[data-initial-category='" + type + "']").removeClass("d-none");
                }
              });

              // Existing logic for message preview and sound alerts
              if (plg_message_previewer == true && typeof data.preview !== "undefined") {
                $(".plg-messaging-preview").html(data.preview);
              }
              if (data.alertsSound != "") {
                var audio = new Audio("<?= $us_url_root ?>usersc/plugins/messaging/assets/sounds/" + data.alertsSound);
                playAudioWithInteraction(audio);
                fetchMessages();
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