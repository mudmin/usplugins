<?php
if(!function_exists('fetchOnlineUsers')){
  function fetchOnlineUsers($multiple = 4){
  $db = DB::getInstance();
  $wd = $db->query("SELECT * FROM plg_watchdog_settings")->first();
  $cutoff = $multiple * $wd->wd_time;
  $date = date("Y-m-d H:i:s",strtotime("-$cutoff seconds",strtotime(date("Y-m-d H:i:s"))));
  $users = $db->query("SELECT id,fname,lname,email,username,last_watchdog,last_page FROM users WHERE last_watchdog >= ? ORDER BY last_watchdog DESC",[$date])->results();
  return $users;
  }
}

if(!function_exists('fetchOnlineUserLocations')){
  function fetchOnlineUserLocations($multiple = 4){
  $db = DB::getInstance();
  $wd = $db->query("SELECT * FROM plg_watchdog_settings")->first();
  $cutoff = $multiple * $wd->wd_time;
  $date = date("Y-m-d H:i:s",strtotime("-$cutoff seconds",strtotime(date("Y-m-d H:i:s"))));
  $pages = $db->query("SELECT DISTINCT last_page FROM users WHERE last_watchdog >= ? ORDER BY last_page",[$date])->results();

  foreach($pages as $k=>$p){
    $pages[$k]->page = $p->last_page;
    $pages[$k]->users = [];
    $users = $db->query("SELECT id FROM users WHERE last_watchdog >= ? AND last_page = ?",[$date,$p->last_page])->results();
    foreach($users as $u){
      $pages[$k]->users[] = $u->id;
    }
    unset($pages[$k]->last_page);
  }
  return $pages;
  }
}

if(!function_exists('isUserOnline')){
  function isUserOnline($id,$multiple = 4){
  $db = DB::getInstance();
  $wd = $db->query("SELECT * FROM plg_watchdog_settings")->first();
  $cutoff = $multiple * $wd->wd_time;
  $date = date("Y-m-d H:i:s",strtotime("-$cutoff seconds",strtotime(date("Y-m-d H:i:s"))));
  $count = $db->query("SELECT id FROM users WHERE last_watchdog >= ? AND id = ?",[$date,$id])->count();
  if($count > 0){
    return true;
  }else{
    return false;
  }

  }
}

if(!function_exists('fetchPopularPages')){
  function fetchPopularPages($count = 20){
  if(is_numeric($count)){
  $db = DB::getInstance();
  $pages = $db->query("SELECT * FROM pages WHERE dwells > 0 ORDER BY dwells DESC LIMIT $count")->results();
  return $pages;
  }
  }
}



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
    $cwd = str_replace(substr($abs_us_root.$us_url_root,0,-1),"",str_replace("\\","/",getcwd()));


    ?>
    <script type="text/javascript">
    $(document).ready(function() {
    function watchdog(){
      console.log("requesting");
      var formData = {
        'currentPage' 	: "<?=$cp?>",
        'currentPath'   : "<?=$cwd?>"
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
