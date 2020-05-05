<?php
if(isset($user) && $user->isLoggedIn() && $settings->plg_al > 0){
$plg_time = $settings->plg_al_time * 60000;

if($settings->plg_al == 1 && hasPerm([2],$user->data()->id)){ ?>
  <script type="text/javascript">
  setTimeout(function(){location.href="<?php echo $us_url_root?>users/logout.php"} , <?=$plg_time?>);
  </script>
<?php
}

if($settings->plg_al == 2 && !hasPerm([2],$user->data()->id)){ ?>
  <script type="text/javascript">
  setTimeout(function(){location.href="<?php echo $us_url_root?>users/logout.php"} , <?=$plg_time?>);
  </script>
<?php
}

if($settings->plg_al == 3){ ?>
  <script type="text/javascript">
  setTimeout(function(){location.href="<?php echo $us_url_root?>users/logout.php"} , <?=$plg_time?>);
  </script>
<?php
}
?>

<?php } ?>
