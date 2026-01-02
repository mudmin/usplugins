<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
$folder = Input::get("folder");
$f = Input::get('file');
$fileQ = $db->query("SELECT * FROM plg_download_files WHERE id = ? AND disabled = 0",[$f]);
$fileC = $fileQ->count();
if($fileC < 1){
  Redirect::to("admin.php?view=plugins_config&plugin=downloads&v=links&folder=".$folder."&err=File not found");
}else{
  $file = $fileQ->first();
}


$rando = uniqid().randomString(7);
if(!empty($_POST['createlink'])){
  $fields = array(
    'file'=>$file->id,
    'folder'=>$file->folder,
    'user'=>Input::get('user'),
    'perms'=>Input::get('perms'),
    'max'=>Input::get('max'),
    'expires'=>Input::get('expires'),
    'dlcode'=>Input::get('dlcode'),
  );

  $db->insert("plg_download_links",$fields);
  Redirect::to("admin.php?view=plugins_config&plugin=downloads&v=links&folder=".$folder."&err=Link Created");
}
?>

<div class="row">
  <div class="col-sm-12">
    <br>
    <h3>Create Link</h3>

    This form allows you to create one off links for your files with very granular permissions. It's up to you to not put dumb values in here :)

    <h4>File: <font color="blue"><?php echo getFileLocationFromDLLink($file->id);?></font></h4>
    <form class="" action="" method="post">
      <input type="hidden" name="csrf" value="<?=$token?>">
      <div class="form-group">
        <label for="">Enter up to ONE user ID</label><br>
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
        <label for="">Comma separated list of permissions who can use this link (blank for unlimited)</label><br>
        <input type="text" name="perms" value="">
      </div>

      <div class="form-group">
        <label for="">Download Code (Feel free to change it)</label><br>
        <input type="text" name="dlcode" value="<?=$rando?>">
      </div>
      <div class="form-group">
        <input type="submit" name="createlink" value="Create Link" class="btn btn-primary">
      </div>
    </form>
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
