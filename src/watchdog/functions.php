<?php

if(!function_exists('watchdogHere')) {
  function watchdogHere(){
    $db = DB::getInstance();
    global $abs_us_root,$us_url_root;
    $directory = $abs_us_root.$us_url_root."usersc/plugins/watchdog/assets/";
    $funcFiles = glob($directory . "*.php");
    $availableFuncs = [];
    foreach($funcFiles as $f){
      include($f);
    }
    $wd = $db->query("SELECT * FROM plg_watchdog_settings")->first();
    $time = $wd->wd_time * 1000;
    $cp = currentPage();
    ?>
    <script type="text/javascript">
    $(document).ready(function() {
    function watchdog(){
      console.log("requesting");
      var formData = {
        'currentPage' 	: "<?=$cp?>"
      };

      $.ajax({
        type 		: 'POST',
        url 		: '<?=$us_url_root?>usersc/plugins/watchdog/parser.php',
        data 		: formData,
        dataType 	: 'json',
      })

      .done(function(data) {
        console.log(data);
        console.log(data.func);
        if(data.func != ""){
        window.focus();  
        window[data.func](data.args);
        }
      })
    }

    watchdog();
    setInterval(watchdog,<?=$time?>);
    });
    </script>
    <?php
  }
}
