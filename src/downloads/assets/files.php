<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
$folder = Input::get('folder');
$procount = 0;
if($folder == ""){
$files = scandir($abs_us_root.$us_url_root."usersc/plugins/downloads/files");
$f = "/";
$pre = "";
}else{
$files = scandir($abs_us_root.$us_url_root."usersc/plugins/downloads/files/".$folder);
$f = "/".$folder."/";
$pre = $folder."/";
}

$folders = scandir($abs_us_root.$us_url_root."usersc/plugins/downloads/files");
foreach($folders as $k=>$v){
  if(is_file($abs_us_root.$us_url_root."usersc/plugins/downloads/files/".$v) || $v == "." || $v == ".."){
    unset($folders[$k]);
  }
}


foreach($files as $k=>$v){
  if(!is_file($abs_us_root.$us_url_root."usersc/plugins/downloads/files".$f.$v)){
    unset($files[$k]);
  }
  if($v == "readme.txt" || $v == ".htaccess"){
    unset($files[$k]);
  }
}

if(!empty($_POST['toflip'])){
  $db->update('plg_download_files',Input::get('toflip'),['disabled'=>Input::get('to')]);
  Redirect::to("admin.php?view=plugins_config&plugin=downloads&err=Done&v=files&folder=".$folder);
}

if(!empty($_POST['savetodb'])){

  $add = Input::get('addtodb');
  $fn = Input::get('fn');
  $fld = Input::get('fld');
  foreach($add as $k=>$v){
    $q = $db->query("SELECT * FROM plg_download_files WHERE location = ?",[$pre.$k]);
    $c = $q->count();
    if($c > 0){ //updating
      $existing = $q->first();
      $db->update("plg_download_files",$existing->id,['disabled'=>0,'filename'=>$fn[$k]]);
    }else{ //inserting
      $fields = array(
        'location'=>$pre.$k,
        'disabled'=>0,
        'filename'=>$fn[$k],
        'folder'=>$fld[$k],
        'dlcode'=>uniqid().randomstring(7),
      );
      $db->insert("plg_download_files",$fields);
    }
  }
  Redirect::to("admin.php?view=plugins_config&plugin=downloads&err=Processed&v=files&folder=".$folder);
}
?>

<div class="row">
  <div class="col-sm-12">
    <br>
    <h3>Manage Files</h3>
    <p>Currently Managing:  <font color="blue"><?=$us_url_root?>usersc/plugins/downloads/files/<?=$folder?></font></p>
      <div class="row">
        <div class="col-3">
        <p>Manage Another Folder: </p>
        </div>
      <div class="col-2">
        <form class="" action="" method="get">

          <input type="hidden" name="view" value="plugins_config">
          <input type="hidden" name="plugin" value="downloads">
          <input type="hidden" name="v" value="files">
          <select class="" name="folder">
            <option value="">/</option>
            <?php foreach($folders as $f){ ?>
              <option <?php if($folder == $f){echo "selected='selected'";} ?> value="<?=$f?>"><?=$f?></option>
            <?php } ?>
          </select>
          <input type="submit" name="submit" value="Go" class="btn-primary">
        </form>
      </div>
    </div>
</div>
</div>

<div class="row" id="toprocess">
  <div class="col-12" style="background-color:#ebebeb;">
    <form class="" action="" method="post">
      <input type="hidden" name="csrf" value="<?=$token?>">
    <br>
    <div class="row">
      <div class="col-6">
        <h4>Files to be Processed</h4>
      </div>
      <div class="col-3">

      </div>
      <div class="col-3">

      </div>
    </div>

    <table class="table table-striped paginate">
      <thead>
        <tr>
          <th>Location</th>
          <th>Filename (when downloading)</th>
          <th>Add to DB
            <input type="checkbox" id="addall" value="">   <input type="submit" name="savetodb" value="Save" class="btn btn-primary">
          </th>
        </tr>
      </thead>
      <tbody>

        <?php foreach($files as $f){
          $check = $db->query("SELECT * FROM plg_download_files WHERE location = ?",[$pre.$f])->count();
          if($check > 0){ continue; }
          $procount++;
            ?>
            <tr>
              <td><?=$pre.$f?></td>
              <td>
                <input type="text" name="fn[<?=$f?>]" value="<?=$f?>">
                <input type="hidden" name="fld[<?=$f?>]" value="<?=$pre?>">
              </td>
              <td>
                <input type="checkbox" class="addtodb" name="addtodb[<?=$f?>]" value="1">
              </td>
            </tr>

            <?php
          }

          ?>
        </form>
      </tbody>
    </table>
  </div>
</div>

<div class="row">
  <div class="col-12">
    <br>
    <h4>Files Currently in the DB</h4>
    <table class="table table-striped paginate">
      <thead>
        <tr>
          <th>Location</th>
          <th>Filename</th>
          <th>Downloads</th>
          <th>Direct Link (if enabled)</th>
          <th></th>
          <th>Disabled?</th>
          <th>Disable/Enable</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($files as $f){
          $checkQ = $db->query("SELECT * FROM plg_download_files WHERE location = ?",[$pre.$f]);
          $checkC = $checkQ->count();
          if($checkC < 1){
            continue;
          }else{
            $check = $checkQ->first();
            ?>

            <tr id="row<?=$check->id?>">
              <td><?=$check->location?></td>
              <td><?=$check->filename?></td>
              <td><?=$check->downloads?></td>
              <td>
                <a href="<?=$plgSet->baseurl?><?=$plgSet->parser?>?id=<?=$check->id?>&mode=1&code=<?=$check->dlcode?>"><?=$plgSet->baseurl?><?=$plgSet->parser?>?id=<?=$check->id?>&mode=1&code=<?=$check->dlcode?></a>
              </td>
              <td>
                  <button type="button" class=" btn btn-primary" onclick="copyStringToClipboard('<?=$plgSet->baseurl?><?=$plgSet->parser?>?id=<?=$check->id?>&mode=1&code=<?=$check->dlcode?>');">Copy</button>
              </td>
              <td><?=bin($check->disabled);?></td>
              <td>
                <form class="" action="" method="post">
                  <input type="hidden" name="csrf" value="<?=$token?>">
                  <input type="hidden" name="toflip" value="<?=$check->id?>">
                  <?php if($check->disabled == 0){ ?>
                    <input type="submit" name="dis" value="Disable" class="btn btn-warning">
                    <input type="hidden" name="to" value="1">
                  <?php }else{ ?>
                    <input type="submit" name="en" value="Enable" class="btn btn-success">
                    <input type="hidden" name="to" value="0">
                  <?php } ?>
                </form>
              </td>
            </tr>

            <?php
          }
        }
          ?>
      </tbody>
    </table>
  </div>
</div>

<script type="text/javascript">
$( document ).ready(function() {
  $("#addall").change(function(){
   if($(this).is(':checked')){
     $(".addtodb").each(function() {
       $(this).prop("checked",true);
     });

   }else{
     $(".addtodb").each(function() {
       $(this).prop("checked",false);
     });
   }
 });

if("<?=$procount?>" == 0){
  $("#toprocess").hide();
}

});
</script>
