<?php
require_once $abs_us_root.$us_url_root."usersc/plugins/game_show/assets/functions.php";
if (!empty($_POST)) {
  if(!Token::check(Input::get('csrf'))){
    include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
  }

$action = Input::get('action');
if($action == "unlockAll"){
  $db->query("UPDATE gameshow_buzzers SET buzzed = 0, can_buzz = 1, elapsed = 0, to_play = '' WHERE owner = ?",[$user->data()->id]);
 // dnd($db->errorString());
  $now = DateTime::createFromFormat('U.u', microtime(true));
  $db->query("UPDATE gameshow_settings SET begin_time = ? WHERE owner = ?",[$now->format("Y-m-d H:i:s.u"),$user->data()->id]);
}elseif($action == "lockAll"){
  $db->query("UPDATE gameshow_buzzers SET can_buzz = 0, to_play = '' WHERE owner = ?",[$user->data()->id]);
}elseif($action == "unlockUnbuzzed"){
  $db->query("UPDATE gameshow_buzzers SET elapsed = 0, to_play = '' WHERE owner = ?",[$user->data()->id]);
  $db->query("UPDATE gameshow_buzzers SET can_buzz = 1 WHERE owner = ? AND buzzed = 0",[$user->data()->id]);

  $now = DateTime::createFromFormat('U.u', microtime(true));
  $db->query("UPDATE gameshow_settings SET begin_time = ? WHERE owner = ?",[$now->format("Y-m-d H:i:s.u"),$user->data()->id]);
}
Redirect::to(currentPage());
}

//show only active buzzers
$buzzers = fetchBuzzers(false);
$bc = count($buzzers);
if($bc < 3){
  $class = "col-6";
}elseif($bc >= 3 && $bc <= 8){
  $class = "col-3";
}elseif($bc >= 9 && $bc <= 12){
  $class = "col-2";
}elseif($bc >= 13 ){
  $class = "col-1";
}
$sounds = [];
?>
<link rel="stylesheet" href="<?=$us_url_root?>usersc/plugins/game_show/assets/css/bootstrap.min.css">
<script src="<?=$us_url_root?>usersc/plugins/game_show/assets/js/jquery.min.js" type="text/javascript"></script>
<script src="<?=$us_url_root?>usersc/plugins/game_show/assets/js/bootstrap.bundle.min.js" type="text/javascript"></script>
<?php require_once $abs_us_root.$us_url_root."usersc/plugins/game_show/assets/css/game_style.php";?>
<div class="row buttons justify-content-center">
  <div class="col-6 col-sm-4 justify-content-center">
    <form class="" action="" method="post">
      <?=tokenHere();?>
      <h1 class="text-center">
        <input type="submit" name="submit" value="Unlock All Buzzers" class="btn btn-outline-success btn-lg">
      </h1>

    <input type="hidden" name="action" value="unlockAll">
    </form>
  </div>

  <div class="col-6 col-sm-4 justify-content-center">
    <form class="" action="" method="post">
      <?=tokenHere();?>
      <h1 class="text-center">
        <input type="submit" name="submit" value="Unlock Unbuzzed Buzzers" class="btn btn-outline-warning btn-lg">
      </h1>

    <input type="hidden" name="action" value="unlockUnbuzzed">
    </form>
  </div>

  <div class="col-6 col-sm-4 justify-content-center">
    <form class="" action="" method="post">
      <?=tokenHere();?>
      <h1 class="text-center">
        <input type="submit" name="submit" value="Lock All Buzzers" class="btn btn-outline-danger btn-lg">
      </h1>

    <input type="hidden" name="action" value="lockAll">
    </form>
  </div>

</div>

<div class="row">
  <?php foreach($buzzers as $b){
    //this makes the text and icons in the contrast color for the background
    $c = "style='color:".$b->font_color."'";
    if($b->can_buzz == 0){
      $lock = "lock.png";
    }else{
      $lock = "unlock.png";
    }
    if(!in_array($b->sound,$sounds)){
      $sounds[] = $b->sound;
    }
     ?>
    <div class="<?=$class?> buzzer" style="background-color:<?=$b->screen_color?>;" id="buzzer<?=$b->id?>">
      <div class="row mt-3 iconleft">
        <div class="col-6">
          <img src="<?=$us_url_root?>usersc/plugins/game_show/assets/images/<?=$lock?>" alt="" class="lockicon" id="lockicon<?=$b->id?>">
        </div>
      </div>
      <h1 class="text-center" <?=$c?>><?=$b->buzzer_name?></h1>
      <h1 class="text-center time" id="time<?=$b->id?>" <?=$c?>><?=gameTime($b->elapsed);?></h1>
      <h1 class="text-center counter" id="counter<?=$b->id?>"></h1>
      <span style="display:none" data-sound="false" id="sound<?=$b->id?>"></span>
    </div>

  <?php } ?>
</div>
<div class="row buttons">
  <div class="col-4">
    <a href="?view=game_settings" class="btn btn-outline-secondary">Game Settings</a>
  </div>
  <div class="col-4">
    <h3 class="text-center"><?=$game_modes[$gsettings->game]?></h3>
  </div>
  <div class="col-4">
    <a href="?view=game_buzzers" class="btn btn-outline-secondary" style="float: right;">Buzzer Settings</a>
  </div>
</div>

<?php require_once $abs_us_root.$us_url_root."usersc/plugins/game_show/assets/js/game_js.php"; ?>
