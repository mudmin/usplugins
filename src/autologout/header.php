<?php
if(isset($user) && $user->isLoggedIn() && $settings->plg_al > 0){
$plg_time = $settings->plg_al_time * 60000;
?>


<?php

if(
  ($settings->plg_al == 1 && hasPerm([2],$user->data()->id)) ||
  ($settings->plg_al == 2 && !hasPerm([2],$user->data()->id)) ||
  ($settings->plg_al == 3)
  ){ ?>
  <script type="text/javascript">
  $(document).ready(function() {
    var timeoutInMilliseconds = "<?=$plg_time?>";
    var timeoutId;
    function resetTimer() {
       window.clearTimeout(timeoutId);
        startTimer();
      }
    function startTimer() {
      // window.setTimeout returns an Id that can be used to start and stop a timer
      timeoutId = window.setTimeout(LogOut, timeoutInMilliseconds)
     }
     function setupTimers () {
       document.addEventListener("mousemove", resetTimer, false);
       document.addEventListener("mousedown", resetTimer, false);
       document.addEventListener("keypress", resetTimer, false);
       document.addEventListener("touchmove", resetTimer, false);
       startTimer();
     }
     setupTimers(); // start auto logout timers
     function LogOut (){
       location.replace("<?php echo $us_url_root?>users/logout.php");
     }
   })
  </script>
<?php
}
}
