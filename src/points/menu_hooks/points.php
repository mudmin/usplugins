<?php
global $user, $db;
if(isset($user) && $user->isLoggedIn()){
  $pn = $db->query("SELECT * FROM plg_points_settings")->first()->term;
  $points = $user->data()->plg_points ." ". $pn;
  echo "<a class='noHover'><span class='labelText'>".$points ."</span></a>"; 
}
?>
<style>
  .noHover{
    pointer-events: none;
    cursor: default;
    text-decoration:none;
  }
  .noHover:hover {
    color: inherit; 
    text-decoration: inherit;
    background-color: inherit; 
 
}
</style>