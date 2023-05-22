<?php
//Please don't load functions system-wide if you don't need them system-wide.
// To make your plugin more efficient on resources, consider only loading resources that need to be loaded when they need to be loaded.
// For instance, you can do
// $currentPage = currentPage();
// if($currentPage == 'admin.php'){ //The administrative dashboard
//   bold("<br>See! I am only loading this when I need it!");
// }
// // Also, please wrap your functions in if(!function_exists())
// if(!function_exists('performancecheckerFunction')) {
//   function performancecheckerFunction(){ }
// }

if(!function_exists("startPageTimer")){
function startPageTimer()
{   
    if(isset($_SESSION['pageTimer'])){
      unset($_SESSION['pageTimer']);
    }
    return microtime(true);
}
}

//pass a string to identify the call
if(!function_exists("checkPageTimer")){
function checkPageTimer($id = "", $user_id = 0){    
    global $user;
    if($user_id > 0){
        if(!isset($user->data()->id) || $user->data()->id != $user_id) {
          return;
        }
     
    }


    $currentTime = microtime(true);

    if (!isset($_SESSION['pageTimer'])) {
        $_SESSION['pageTimer'] = [
            'startTime' => $currentTime,
            'lastTime' => $currentTime,

        ];
    }

    $pageTimer = $_SESSION['pageTimer'];

    $executionTime = round(($currentTime - $pageTimer['startTime']) * 1000, 2); // Time since page started loading
    $timeSinceLastCall = round(($currentTime - $pageTimer['lastTime']) * 1000, 2); // Time since last call to stopTimer

    $_SESSION['pageTimer']['lastTime'] = $currentTime;

    echo "<h5>Time since last call: <span class='text-danger'>{$timeSinceLastCall} ms</span> - <span class='text-success'>{$executionTime} ms </span> since page load";


    if($id != ""){
      echo " - Call id:  $id ";
    }
    echo "</h5>";
}
}