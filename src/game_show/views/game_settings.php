<?php
require_once $abs_us_root.$us_url_root.'users/includes/template/prep.php';
require_once $abs_us_root.$us_url_root."usersc/plugins/game_show/assets/functions.php";
writeGameBannedFile();
if (!empty($_POST)) {
  if(!Token::check(Input::get('csrf'))){
    include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
  }
  $game = Input::get('game');
  if($game != $gsettings->game && array_key_exists($game,$game_modes)){
    //reset buzzers
    $db->query("UPDATE gameshow_buzzers SET buzzed = 0, can_buzz = 1, elapsed = 0, to_play = '' WHERE owner = ?",[$user->data()->id]);

    $db->update('gameshow_settings',$gsettings->id,['game'=>$game]);
    usSuccess("Game mode changed and buzzers have been reset");
  }
  $sounds = Input::get('play_sounds');
  if($sounds == 0 || $sounds == 1){
    $db->update('gameshow_settings',$gsettings->id,['play_sounds'=>$sounds]);
  }

  if(Input::get('live_url') != $gsettings->live_url){
    $db->update('gameshow_settings',$gsettings->id,['live_url'=>Input::get('live_url')]);
  }
  usSuccess("Saved");
  Redirect::to("?view=game_settings");
}

?>
<div class="row buttons">
  <div class="col-2">
    <a href="?view=game" class="btn btn-secondary">Play Game</a>
  </div>
  <div class="col-8">
    <h1 class="text-center">Gameshow Settings</h1>
    <form class="" action="" method="post">
      <h1 class="text-center">
        <input type="submit" name="save" value="Save" class="btn btn-primary btn-lg">
      </h1>
    </div>
    <div class="col-2">
      <a href="?view=game_buzzers" class="btn btn-secondary" style="float: right;">Buzzer Settings</a>
    </div>
  </div>
  <div class="row">
    <div class="col-12 col-sm-6 offset-sm-3">

      <?=tokenHere();?>
      <div class="form-group">
        <label for="game">Game Mode</label>
        <select class="form-control" name="game">
          <?php foreach($game_modes as $k=>$v) { ?>
            <option value="<?=$k?>" <?php if($gsettings->game == $k ){ echo "selected='selected'";}?>><?=$v?></option>
          <?php } ?>
        </select>
      </div>

      <div class="form-group">
        <label for="play">Play Sounds</label>
        <select class="form-control" name="play_sounds">
          <option value="0" <?php if($gsettings->play_sounds == 0){ echo "selected='selected'";}?>>No</option>
          <option value="1" <?php if($gsettings->play_sounds == 1){ echo "selected='selected'";}?>>Yes</option>
        </select>
      </div>

      <div class="form-group">
        <label for="play">Live URL with final / (http://yourdomain.com/ or http://192.168.1.100/)</label>
        <input type="text" class="form-control" name="live_url" value="<?=$gsettings->live_url?>">
      </div>
    </form>
  </div>
  <div class="row">
    <div class="col-12" style="margin:2em;">
      <h1 class="text-center">Documentation</h1>
      <h5>Thank You</h5>
      <p>
        I wanted to thank you for checking out my Ultimate Gameshow Platform.  If you appreciate this plugin and would like to make a donation to the author, you can do so at <a href="https://UserSpice.com/donate">https://UserSpice.com/donate</a>. Either way, thanks for using UserSpice!
      </p>

      <h5>Buzzer Building Instructions</h5>
      <p>For the latest information on building buzzers that are compatible with this system, please check out the code and videos that can be found <a href="https://github.com/mudmin/AnotherMaker/tree/master/gameshow_ultimate" target="_blank">here</a>. </p>

      <h5>Game Modes</h5>
      <p>In order to offer some flexibility, the system offers several modes.  It offers your traditional "Lockout" mode.  This means that when one person, buzzes in, the other people are immediately locked out.  In the event the person guesses wrong, you can choose to either reset the lockout or only allow people who haven't buzzed in yet get a chance.</p>


      <h5>Buzzer Config</h5>
      <p>
        The buzzer config screen allows you to choose several settings for the buttons.  These options include setting the background/font-colors for the UI.  You can remame the buzzer. You can also set the sound that can be played when someone buzzes in.  Finally you can set a random string to act as an API key to add an additional layer of security to your buttons.
      </p>

      <h5>Virtual Buzzers</h5>
      <p>
        We provide "Virtual Buzzers" as a way to test the system, fill in for a broken buzzer, or as an alternative to building phyiscal buzzers. These buzzers require you to set the url. If you're running on xampp or something similar, that would look like "http://localhost/gameshow/".  On a live server it might look something like "http://yourdomain.com/";  Don't forget that final /.  I was being  lazy, so I didn't force it in there. For the time being, you will need to log any remote computers in using your username and password.  However, since this was built on an API, I may offer app and standalone versions that remove this requirement.
      </p>

      <h5>Sounds</h5>
      <p>It is important to note that sounds have been given a low priority to maintain the accuracy of the lockout.  This means that while the time may not appear on the screen or the sound may not immeditely play, the actual button sensing is accurate to the millisecond level. When adding your own custom sounds (usersc/plugins/game_show/assets/mp3/), it is recommended that you choose mp3 files smaller than 50 kilobytes and the smaller the better.</p>
    </div>

    <h5>Further Support</h5>
    <p>Feel free to reach out on Discord at <a href="https://discord.gg/6XZ7mEWnzZ">https://discord.gg/6XZ7mEWnzZ</a> where we provide the best tech support in the industry. No joke. There is support in the #sserspice-5-support channel for the core application.  This plugin is currently supported in #official-plugin-support (Although I may make a standalone channel for this app).  And anything relating to the physical costruction of the buzzers/race timers is covered under #arduino-and-maker-stuff.
    </p>
  </div>

  <?php require_once $abs_us_root.$us_url_root.'users/includes/html_footer.php'; ?>
