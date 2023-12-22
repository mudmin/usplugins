<?php
if(count(get_included_files()) == 1) die(); //Direct Access Not Permitted
if(!pluginActive("spicebin",true)){ die ("SpiceBin is disabled");}
$pset = $db->query("SELECT * FROM plg_spicebin_settings")->first();
$paste = canIViewPaste();
if(!$paste){
  die("You are not authorized to view this ".$pset->product_single);
}
$deleted = false;
include $abs_us_root.$us_url_root."usersc/plugins/spicebin/files/_last_ten_logic.php";
$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$code = Input::get('code');
if($code == ""){
  $actual_link .= "&code=".$paste->code;
}

if(file_exists($abs_us_root.$us_url_root."usersc/plugins/spicebin/files/_custom_includes.php")){
  include $abs_us_root.$us_url_root."usersc/plugins/spicebin/files/_custom_includes.php";
}else{
  include $abs_us_root.$us_url_root."usersc/plugins/spicebin/files/_includes.php";
}
if(isset($user) && $user->isLoggedIn() && $paste->user == $user->data()->id || hasPerm([2])) {
  if(!empty($_POST)){
    $token = $_POST['csrf'];
    if(!Token::check($token)){
      include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
    }
    if(!empty($_POST['deleteThis'])){
      $db->query("DELETE FROM plg_spicebin WHERE id = ?",[$paste->id]);
      $deleted = true;
    }
    if(hasPerm([2]) && isset($_POST['autodelete'])){
      $db->update("plg_spicebin",$paste->id,["no_auto"=>Input::get('no_auto')]);
      sessionValMessages([],$pset->product_plural." auto delete updated");
      $paste->no_auto = Input::get('no_auto');
    }
  }
  ?>

  <div class="row">
    <div class="col-12 col-sm-6">
      <form class="" action="" method="post">
        <h4>
          Manage this <?=$pset->product_single?>
          <button type="button" class="btn btn-primary" onclick="copyStringToClipboard('<?=$actual_link?>');">Copy Link</button>
          <span style="display:none; color:blue;" id="copyLink">Link Copied</span>

          <input type="hidden" name="csrf" value="<?=Token::generate();?>">
          <input type="submit" name="deleteThis" value="Delete" class="btn btn-danger">
        </h4>
      </form>
      <?php if(hasPerm([2]) && $pset->delete_days > 0){ ?>
      </div>
      <div class="col-12 col-sm-6">

        <form class="" action="" method="post">
          <div class="input-group">

            <input type="hidden" name="csrf" value="<?=Token::generate();?>">
            <select class="" name="no_auto">
              <option value="0" <?php if($paste->no_auto == 0){echo "selected = 'selected'";}?>>Auto delete this <?=$pset->product_single?></option>
              <option value="1" <?php if($paste->no_auto == 1){echo "selected = 'selected'";}?>>Exclude this <?=$pset->product_single?> from auto delete</option>
            </select>
            <input type="submit" name="autodelete" value="Save" class="btn btn-primary">
          </form>
        </div>

      </div>

    <?php } ?>
  </div>
  Public Link (For Sharing): <a href="<?=$actual_link?>"><?=$actual_link?></a>
</div>
<br>
<?php }
if(!$deleted){
  ?>

  <div class="row">
    <div class="<?=$main?>">
      <h3><?=$paste->title?></h3>
      <table class="table">
        <thead>
          <tr class="text-center">
            <th><i class="fa fa-user fa-lg"></i> <?php echouser($paste->user);?></th>
            <th><i class="fa fa-calendar fa-lg"></i> <?php echo $paste->created_on;?></th>
            <th><i class="fa fa-eye fa-lg"></i> <?php echo $paste->views;?></th>
            <th>
              <?php if($paste->private > 0) { ?>
                <i class="fa fa-lock fa-lg"></i> Private
              <?php }else{ ?>
                <i class="fa fa-globe fa-lg"></i> Public
              <?php } ?>
            </th>
            <th>
              <i class="fa fa-code fa-lg"></i>
              <?php if($paste->lang == ""){
                echo "Just a " . $pset->product_single;
              }else{
                echo strtoupper($paste->lang);
              }
              ?>
            </th>
          </tr>
        </thead>
      </table>
      <textarea class="code" style="width:100%;"><?php echo htmlspecialchars_decode(Input::sanitize($paste->paste)); ?></textarea>
    </div>
    <?php
  }else{
    ?>
    <h3 class='text-center'>This <?=$pset->product_single?> has been deleted.</h3>
    <div class="text-center">
      <a href="<?=$us_url_root.$pset->your_page?>" class="btn btn-primary"><?=$pset->product_button?></a>
      <?php if(hasPerm([2])){ ?>
        <a href="<?=$us_url_root?>usersc/plugins/spicebin/management.php" class="btn btn-primary"><?=$pset->product_single?> Management</a>
      <?php } ?>
    </div>

    <?php
  }
  if($pset->$col > 0) { ?>
    <div class="col-2 d-none d-lg-block" style="padding-top:6em;">
      <?php include $abs_us_root.$us_url_root."usersc/plugins/spicebin/files/_last_ten.php"; ?>
    </div>
  <?php } ?>
</div>

<script type="text/javascript">
function qsa(sel) {
  return Array.apply(null, document.querySelectorAll(sel));
}
qsa(".code").forEach(function (editorEl) {
  CodeMirror.fromTextArea(editorEl, {
    lineNumbers: true,
    lineWrapping: true,
    matchBrackets: true,
    theme: "<?=$pset->theme?>",
    mode: "application/x-httpd-php",
    indentUnit: 4,
    indentWithTabs: true,
    readOnly: true
  });
});

function copyStringToClipboard (textToCopy) {
  navigator.clipboard.writeText(textToCopy);
  $("#copyLink").fadeIn();
  $("#copyLink").fadeOut(3500);
}
</script>
