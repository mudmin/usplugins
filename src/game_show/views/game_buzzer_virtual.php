<?php
require_once $abs_us_root.$us_url_root."usersc/plugins/game_show/assets/functions.php";
$vbuzz = Input::get('vbuzz');
$key = Input::get('key');
$owner = Input::get('owner');
$buzzerQ = $db->query("SELECT * FROM gameshow_buzzers WHERE id = ? AND buzzer_key = ? AND owner = ?",[$vbuzz,$key,$owner]);
$buzzerC = $buzzerQ->count();
if($buzzerC < 1){
  die("This virtual buzzer cannot be found");
}else{
  $buzzer = $buzzerQ->first();
}

$owner = $db->query("SELECT * FROM gameshow_settings WHERE owner = ?",[$owner])->first();
if($owner->live_url == ""){
  die("The live url must be set on the Game Settings Page");
}
if (!empty($_POST)) {
  if(!Token::check(Input::get('csrf'))){
    include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
  }

  if(Input::get('virtualbuzz') != ""){
    try {
      $data = array("buzz" => $buzzer->id, "key"=>$buzzer->buzzer_key, "owner" =>$buzzer->owner);
      $ch = curl_init();
      if ($ch === false) {
        throw new Exception('failed to initialize');
      }

      curl_setopt($ch, CURLOPT_URL, $owner->live_url."usersc/plugins/game_show/parsers/api.php");
      // curl_setopt($ch, CURLOPT_URL, "http://localhost/gameshow-dev/usersc/plugins/game_show/parsers/api.php");
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
      curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));


      $content = curl_exec($ch);

      // Check the return value of curl_exec(), too
      if ($content === false) {
        throw new Exception(curl_error($ch), curl_errno($ch));
      }else{
        $content = json_decode($content);

      }
      // Check HTTP return code, too; might be something else than 200
      $httpReturnCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      /* Process $content here */

    } catch(Exception $e) {

      trigger_error(sprintf(
        'Curl failed with error #%d: %s',
        $e->getCode(), $e->getMessage()),
        E_USER_ERROR);
    } finally {
        // Close curl handle unless it failed to initialize
        if (is_resource($ch)) {
          curl_close($ch);
        }
      }


    }
  }
  ?>
  <link rel="stylesheet" href="<?=$us_url_root?>usersc/plugins/game_show/assets/css/bootstrap.min.css">
  <script src="<?=$us_url_root?>usersc/plugins/game_show/assets/js/jquery.min.js" type="text/javascript"></script>
  <script src="<?=$us_url_root?>usersc/plugins/game_show/assets/js/bootstrap.bundle.min.js" type="text/javascript"></script>
  <?php require_once $abs_us_root.$us_url_root."usersc/plugins/game_show/assets/css/game_style.php";?>


  <style media="screen">
  .buzz{
    color:<?=$buzzer->font_color?>;
    background-color:<?=$buzzer->screen_color?>;
    font-size: 7rem;

    margin-top:25vh;
    height: 50vh;
    width: 50vw;
    border-radius:5%;
    border: 5px solid black;
  }

  #response{
    font-size:3rem;
  }
</style>
<form action="" method="post">
  <?=tokenHere();?>
  <div class="row justify-content-center">
    <button name="virtualbuzz" id="buzz" type="submit" value="submit" class="buzz">
      <?=$buzzer->buzzer_name?>
      <br>
      <span id="response">
        <?php if(isset($content->msg)){ echo ucfirst($content->msg); }else { echo "<br>";} ?>
      </span>
    </button>
  </div>

</form>

<script type="text/javascript">
$( document ).ready(function() {
  setTimeout(
    function() {
      $("#response").html("<br>");
    }, 3000);

  });
</script>
