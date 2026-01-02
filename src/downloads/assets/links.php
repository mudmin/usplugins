<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted

$folder = Input::get('folder');
if($folder != ""){
  $fld = $folder."/";
}else{
  $fld = "";
}
$procount = 0;

$links = $db->query("SELECT * FROM plg_download_links WHERE folder = ? ORDER BY used DESC",[$fld])->results();
$files = $db->query("SELECT * FROM plg_download_files WHERE folder = ? AND disabled = 0",[$fld])->results();

$folders = scandir($abs_us_root.$us_url_root."usersc/plugins/downloads/files");
foreach($folders as $k=>$v){
  if(is_file($abs_us_root.$us_url_root."usersc/plugins/downloads/files/".$v) || $v == "." || $v == ".."){
    unset($folders[$k]);
  }
}
if(!empty($_POST['delTheseLinks'])){
  $delMe = Input::get('delMe');
  if(is_array($delMe)){
    foreach($delMe as $d){
      $db->query("DELETE FROM plg_download_links WHERE id = ?",[$d]);
    }
  }else{
    $db->query("DELETE FROM plg_download_links WHERE id = ?",[$delMe]);
  }
  Redirect::to("admin.php?view=plugins_config&plugin=downloads&v=links&folder=".$folder."&submit=Go&err=Deleted");
}


if(!empty($_POST['createlinks'])){
  foreach($files as $file){
    $fields = array(
      'file'=>$file->id,
      'folder'=>$file->folder,
      'user'=>Input::get('user'),
      'perms'=>Input::get('perms'),
      'max'=>Input::get('max'),
      'expires'=>Input::get('expires'),
      'dlcode'=>uniqid().randomString(7),
    );
    $db->insert("plg_download_links",$fields);
  }
  Redirect::to("admin.php?view=plugins_config&plugin=downloads&v=links&folder=".$folder."&err=Links Created");
}
// ?>

<div class="row">
  <div class="col-sm-12">
    <br>
    <h3>Manage Links</h3>
    <p>Currently Managing:  <font color="blue"><?=$us_url_root?>usersc/plugins/downloads/files/<?=$folder?></font></p>
    <div class="row">
      <div class="col-3">
      <p>Manage Another Folder: </p>
      </div>
      <div class="col-2">
        <form class="" action="" method="get">

          <input type="hidden" name="view" value="plugins_config">
          <input type="hidden" name="plugin" value="downloads">
          <input type="hidden" name="v" value="links">
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
<br>
<div class="row">
  <div class="col-12" style="background-color:#ebebeb;">
    <form class="" action="" method="post" onsubmit="return confirm('Do you really want to delete these links? This cannot be undone.');">
    <div class="row">
      <div class="col-6">
        <h4>Existing Links</h4>
      </div>
      <div class="col-3">

      </div>
      <div class="col-3">
         <input type="submit" name="delTheseLinks" value="Delete Selected Links" class="btn btn-primary">
      </div>
    </div>

    <table class="table table-striped paginate">
      <thead>
        <tr>
          <th>Location</th>
          <th>User</th>
          <th>Permissions</th>
          <th>Max Downloads</th>
          <th>Downloads</th>
          <th>Expires</th>
          <th>Link</th>
          <th></th>
          <th>Disabled</th>
          <th>Delete Link
            <input type="checkbox" id="delAll">
          </th>
          <th>Edit</th>
        </tr>
      </thead>
      <tbody>

          <input type="hidden" name="csrf" value="<?=$token?>">
          <?php foreach($links as $l){ ?>
          <tr>
            <td><?php echo getFileLocationFromDLLink($l->file);?></td>
            <td>
              <?php if(is_numeric($l->user) && $l->user > 0){
                echouser($l->user);
                echo "($l->user)";
              }else{
                echo "-";
              }
              ?>
            </td>

              <td><?php if($l->perms != ""){echo $l->perms;}else{ echo "-";}?></td>
              <td><?php if($l->max > 0){echo $l->max;}else{ echo "-";}?></td>
              <td><?=$l->used?></td>
              <td><?php if($l->expires != "0000-00-00 00:00:00"){echo $l->expires;}else{ echo "-";}?></td>
              <td>
                <a href="<?=$plgSet->baseurl?><?=$plgSet->parser?>?id=<?=$l->id?>&mode=2&code=<?=$l->dlcode?>"><?=$plgSet->baseurl?><?=$plgSet->parser?>?id=<?=$l->id?>&mode=2&code=<?=$l->dlcode?></a>
              </td>
              <td>
                  <button type="button" class=" btn btn-primary" onclick="copyStringToClipboard('<?=$plgSet->baseurl?><?=$plgSet->parser?>?id=<?=$l->id?>&mode=2&code=<?=$l->dlcode?>');">Copy</button>
              </td>
              <td><?=bin($l->disabled);?></td>
              <td>
                <input type="checkbox" class="delMe" name="delMe" value="<?=$l->id?>">
              </td>
              <td>
                <a name="button" href="admin.php?view=plugins_config&plugin=downloads&v=editlinks&folder=<?=$folder?>&submit=Go&link=<?=$l->id?>" class="btn btn-primary">Edit</button>
              </td>
          </tr>

        <?php } ?>
      </tbody>
    </table>
    </form>
  </div>
</div>

<div class="row">
  <div class="col-12">
    <h4>Create a set of links for every file in this folder</h4>
     It's up to you to not put dumb values in here :)
    <form class="" action="" method="post">
      <input type="hidden" name="csrf" value="<?=$token?>">
      <div class="form-group">
        <label for="">Enter up to ONE user ID (Users are listed at the bottom of this page)</label><br>
        <input type="number" name="user" value="0" min="0" step="1">
      </div>

      <div class="form-group">
        <label for="">Number of times this link can be used (0 for unlimited)</label><br>
        <input type="number" name="max" value="0" min="0" step="1">
      </div>

      <div class="form-group">
        <label for="">Expiration Date/Time of this link.  (0000-00-00 00:00:00 for never expires)</label><br>
        <input type="datetime" name="expires" value="0000-00-00 00:00:00">
      </div>

      <div class="form-group">
        <label for="">Comma separated list of permissions who can use this link (blank for unlimited) (Perms are at the bottom of this page)</label><br>
        <input type="text" name="perms" value="">
      </div>

      <div class="form-group">
        <input type="submit" name="createlinks" value="Create Links for Every File in the Folder" class="btn btn-primary">
      </div>
    </form>
  </div>
</div>
<div class="row">
  <div class="col-12" style="background-color:#ebebeb;">
    <h4>Create Individual Links from Files</h4>
    <table class="table table-striped paginate">
      <thead>
        <tr>
          <th>Location</th>
          <th>Create Link Manually</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($files as $f){ ?>
          <tr>
            <td><?=$f->location?></td>
            <td>
              <a href="admin.php?view=plugins_config&plugin=downloads&v=createlink&folder=<?=$folder?>&file=<?=$f->id?>" class="btn btn-danger">Create Link</a>
            </td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</div>
<br><br>
<h1 class="text-center">For Your Reference</h1>
<div class="row">
  <div class="col-6">
    <h4>Permission Levels</h4>
    <?php tableFromQuery($db->query("SELECT * FROM permissions")->results());?>
  </div>
  <div class="col-6">
    <h4>Users and IDs</h4>
    <?php tableFromQuery($db->query("SELECT id, fname,lname,email FROM users")->results());?>
  </div>
</div>
<script type="text/javascript">
$( document ).ready(function() {
  $("#delAll").change(function(){
   if($(this).is(':checked')){
     $(".delMe").each(function() {
       $(this).prop("checked",true);
     });

   }else{
     $(".delMe").each(function() {
       $(this).prop("checked",false);
     });
   }
 });

});
</script>
