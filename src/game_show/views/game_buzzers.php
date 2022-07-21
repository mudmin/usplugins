<?php
require_once $abs_us_root.$us_url_root.'users/includes/template/prep.php';
require_once $abs_us_root.$us_url_root."usersc/plugins/game_show/assets/functions.php";
writeGameBannedFile();
//show all buzzers
$buzzers = fetchBuzzers(true);
$colors = fetchColors();
$sounds = fetchSounds();

if(count($buzzers) == 1){
  $term = "buzzer";
}else{
  $term = "buzzers";
}

if (!empty($_POST)) {
  if(!Token::check(Input::get('csrf'))){
    include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
  }

  if(!empty($_POST['deleteMe'])){
    $id = Input::get('deleteMe');
    $db->query("DELETE FROM gameshow_buzzers WHERE id = ? AND owner = ?",[$id,$user->data()->id]);
    usSuccess("Done!");
    Redirect::to("?view=game_buzzers");
  }

  $action = Input::get('action');

  if($action == "update"){
  $rows = Input::get('row');
  $buzzer_name = Input::get('buzzer_name');
  $buzzer_key = Input::get('buzzer_key');
  $screen_color = Input::get('screen_color');
  $font_color = Input::get('font_color');
  $light_color = Input::get('light_color');
  $sound = Input::get('sound');
  $disabled = Input::get('disabled');

  foreach($rows as $r){
    $r = (int)$r;
    $check = $db->query("SELECT id FROM gameshow_buzzers WHERE id = ? AND owner = ?",[$r,$user->data()->id])->count();
    if($check < 1){
      usError("Buzzer id $r is not your buzzer. Skipping");
      continue;
    }
    $fields = [];
    $fields['buzzer_name'] = $buzzer_name[$r];
    $fields['buzzer_key'] = $buzzer_key[$r];
    $fields['screen_color'] = $screen_color[$r];
    $fields['font_color'] = $font_color[$r];
    if($disabled[$r] != 0 && $disabled[$r] != 1){
      $disabled[$r] = 0;
    }
    $fields['disabled'] = $disabled[$r];

    if(!in_array($light_color[$r],$colors)){
      $light_color[$r] = $color[0];
    }
    $fields['light_color'] = $light_color[$r];

    if(!in_array($sound[$r],$sounds)){
      $sound[$r] = $sounds[0];
    }
    $fields['sound'] = $sound[$r];
    $db->update("gameshow_buzzers",$r,$fields);

  }
  usSuccess("Done!");
  Redirect::to("?view=game_buzzers");
  }
  if($action == "create"){
    $count = count($buzzers) + 1;
    if($count >= 25){
      usError("There is a hard limit of 24 buzzers");
      Redirect::to("?view=game_buzzers");
    }
    $fields = [
      'owner'=>$user->data()->id,
      'buzzer_name'=>"Player ".$count,
      'buzzer_key'=>randomstring(6),
      'light_color'=>"blue",
      'sound'=>"beep",
      'screen_color'=>"#04e748",
      'font_color'=>"#000000",
    ];
    $db->insert("gameshow_buzzers",$fields);
    usSuccess("New buzzer created");
    Redirect::to("?view=game_buzzers");
  }
}
?>


  <div class="row">
    <div class="col-2">
      <a href="?view=game" class="btn btn-secondary">Play Game</a>
    </div>
    <div class="col-8">
      <h1 class="text-center">Manage Your <?=count($buzzers)?> <?=ucfirst($term)?></h1>
    </div>
    <div class="col-2">
      <a href="?view=game_settings" class="btn btn-secondary"  style="float:right;">Game Settings</a>

    </div>
  </div>

  <div class="row">
    <div class="col-6">
      <?php if(count($buzzers) < 24){ ?>
      <form class="" action="" method="post">
        <?=tokenHere();?>
        <input type="hidden" name="action" value="create">
        <input type="submit" name="submit" value="Create New" class="btn btn-primary btn-lg">
      </form>
    <?php } ?>
    </div>
    <div class="col-6">
      <form class="" action="" method="post">
        <?=tokenHere();?>
        <input type="hidden" name="action" value="update">
        <h2 class="text-right">
            <input type="submit" name="submit" value="Save Settings" class="btn btn-primary btn-lg">
        </h2>

    </div>
  </div>
  <div class="row">

    <table class="table">
      <thead>
        <th>Buzzer Name</th>
        <th>Buzzer Key</th>
        <th>Screen Color</th>
        <th>Font Color</th>
        <th>Light Color</th>
        <th>Sound</th>
        <th>Disabled</th>
        <th>QueryString</th>
        <th>Virtual Buzzer</th>
      </thead>
      <tbody>

        <?php foreach($buzzers as $b){ ?>
          <tr>
            <td>
              <input type="hidden" name="row[<?=$b->id?>]" value="<?=$b->id?>">
              <input type="text" name="buzzer_name[<?=$b->id?>]" value="<?=$b->buzzer_name?>" class="form-control" required>
            </td>

            <td>
              <input type="text" name="buzzer_key[<?=$b->id?>]" value="<?=$b->buzzer_key?>" class="form-control" required>
            </td>

            <td>
              <input type="color" name="screen_color[<?=$b->id?>]" value="<?=$b->screen_color?>" class="form-control" required>
            </td>

            <td>
              <input type="color" name="font_color[<?=$b->id?>]" value="<?=$b->font_color?>" class="form-control" required>
            </td>

            <td>
              <select class="form-control" name="light_color[<?=$b->id?>]" required>
                <?php foreach($colors as $c){ ?>
                  <option value="<?=$c?>"
                    <?php if($b->light_color == $c){ echo "selected = 'selected'";} ?>
                    ><?=$c?></option>
                  <?php } ?>
                </select>
              </td>

              <td>
                <select class="form-control" name="sound[<?=$b->id?>]" required>
                  <?php foreach($sounds as $c){ ?>
                    <option value="<?=$c?>"
                      <?php if($b->sound == $c){ echo "selected = 'selected'";} ?>
                      ><?=$c?></option>
                    <?php } ?>
                  </select>
                </td>

                <td>
                  <select class="form-control" name="disabled[<?=$b->id?>]" required>
                    <option value="0" <?php if($b->disabled == 0){echo "selected='selected'";}?>>No</option>
                    <option value="1" <?php if($b->disabled == 1){echo "selected='selected'";}?>>Yes</option>
                  </select>
                </td>
                <td>
                  ?buzz=<?=$b->id?>&owner=<?=$user->data()->id?>&key=<?=$b->buzzer_key?>
                </td>
                <td>
                  <a href="?vbuzz=<?=$b->id?>&owner=<?=$user->data()->id?>&key=<?=$b->buzzer_key?>" target="_blank" class="btn btn-primary">View</a>
                </td>
              </tr>
            <?php } ?>
          </tbody>
        </table>

      </div>
    </div>
  </form>
  <form class="" action="" method="post" class="mt-5" style="margin-left:5em;"  onsubmit="return confirm('Do you really want to do this? It cannot be undone.');">
    <?=tokenHere(); ?>
    <label for="">Delete Buzzer</label>
    <div class="input-group">

      <select class="" name="deleteMe" required>
        <option value="" disabled selected="selected">-- Choose Buzzer to Delete --</option>
        <?php foreach($buzzers as $b){ ?>
          <option value="<?=$b->id?>"><?=$b->buzzer_name?></option>
        <?php } ?>
      </select>
      <input type="submit" name="delete" value="Delete Buzzer" class="btn btn-danger">
    </div>

  </form>
  <?php require_once $abs_us_root.$us_url_root.'users/includes/html_footer.php'; ?>
