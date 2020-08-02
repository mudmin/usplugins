<?php
if($settings->session_manager == 1 && !isset($_SESSION['fingerprint'])) {?>
  <script src="<?=$us_url_root?>usersc/plugins/session_manager/assets/tomfoolery.js"></script>
  <script>
  if (window.requestIdleCallback) {
    requestIdleCallback(function () {
      Fingerprint2.get(function (components) {
        var values = components.map(function (component) { return component.value })
        var murmur = Fingerprint2.x64hash128(values.join(''), 31)
        var fingerprint = murmur;
        $.ajax({
          type: "POST",
          url: '<?=$us_url_root?>usersc/plugins/session_manager/assets/fingerprint_post.php',
          data: ({fingerprint:fingerprint}),
        });
              })
            })
          } else {
            setTimeout(function () {
              Fingerprint2.get(function (components) {
                var values = components.map(function (component) { return component.value })
                var murmur = Fingerprint2.x64hash128(values.join(''), 31)
                var fingerprint = murmur;
                $.ajax({
                  type: "POST",
                  url: '<?=$us_url_root?>usersc/plugins/session_manager/assets/fingerprint_post.php',
                  data: ({fingerprint:fingerprint}),
                });
              })
            }, 500)
          }
        </script>
      <?php }
      if($settings->session_manager==1) storeUser();
      if($settings->one_sess == 1 && $settings->session_manager == 1 && $user->isLoggedIn()){
        $sessions = fetchUserSessions();
        foreach($sessions as $session) {
          if($session->kUserSessionID!=$_SESSION['kUserSessionID'] && $session->UserSessionEnded!=1) {
            killSessions([$session->kUserSessionID]);
          }
        }
      }
       ?>
