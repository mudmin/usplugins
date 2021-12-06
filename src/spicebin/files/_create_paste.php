<?php
if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
if(!pluginActive("spicebin",true)){ die ("SpiceBin is disabled");}
$pset = $db->query("SELECT * FROM plg_spicebin_settings")->first();
if(!canIPaste()){
  die("You are not allowed to ".$pset->product_single);
}

if(!empty($_POST['paste'])){
  if(isset($user) && $user->isLoggedIn()){
    $uid = $user->data()->id;
  }else{
    $uid = 0;
  }

  $date = date("Y-m-d H:i:s");
  $link = uniqid();
  $code = randomstring(22);
  if($pset->delete_days == 0){
    $pset->delete_days = 120; //compatibility fix
  }
  $delete = date("Y-m-d H:i:s" ,strtotime("+ ".$pset->delete_days." days",strtotime(date("Y-m-d H:i:s"))));
  $fields = [
    'user'=>$uid,
    'title'=>Input::get('title'),
    'lang'=>Input::get('lang'),
    'paste'=>Input::get('paste'),
    'created_on'=>date("Y-m-d H:i:s"),
    'last_visit'=>date("Y-m-d H:i:s"),
    'private'=>Input::get('private'),
    'delete_on'=>$delete,
    'code'=>$code,
    'link'=>$link,
    'ip'=>ipCheck(),
  ];
  $db->insert("plg_spicebin",$fields);
  sessionValMessages([],$pset->product_single." created");
  Redirect::to($us_url_root.$pset->view_page."?".$link);
}
include $abs_us_root.$us_url_root."usersc/plugins/spicebin/files/_last_ten_logic.php";
if(file_exists($abs_us_root.$us_url_root."usersc/plugins/spicebin/files/_custom_includes.php")){
  include $abs_us_root.$us_url_root."usersc/plugins/spicebin/files/_custom_includes.php";
}else{
  include $abs_us_root.$us_url_root."usersc/plugins/spicebin/files/_includes.php";
}
?>

<form class="" action="" method="post">
  <br>
  <div class="row">
    <div class="col-12 col-sm-6">
      <div class="form-group">
        <input type="hidden" name="csrf" value="<?=Token::generate();?>">
        <label for="">Give your <?=lcfirst($pset->product_single);?> a title *</label>
        <input type="text" class="form-control" name="title" value="" required>
      </div>
    </div>
    <div class="col-12 col-sm-3">
      <div class="form-group">
        <label for="">Make this <?=lcfirst($pset->product_single);?> private?*</label>
        <select class="form-control" name="private" required>
          <option value="0">No</option>
          <option value="1">Yes</option>
        </select>
      </div>
    </div>

    <div class="col-12 col-sm-3">
      <div class="form-group">
        <label for="">Select your language *</label>
        <?php binLangDropdown($pset); ?>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="<?=$main?>">
      <div class="form-group">
        <label for="">Enter your <?=lcfirst($pset->product_single);?>* </label>
        <textarea name="paste" rows="20" class="form-control code"><?="\n\n\n";?></textarea>
      </div>

      <input type="submit" name="submit" value="Submit <?=$pset->product_single;?>" class="btn btn-primary">
    </form>
  </div>
  <?php if($pset->$col > 0) { ?>
    <div class="col-2 d-none d-lg-block">
      <?php include $abs_us_root.$us_url_root."usersc/plugins/spicebin/files/_last_ten.php"; ?>
    </div>
  <?php }?>
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
    readOnly: false
  });
});

</script>
