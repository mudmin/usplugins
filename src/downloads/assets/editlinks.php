<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted

$l = Input::get('link');
$folder = Input::get('folder');
$q = $db->query("SELECT * FROM plg_download_links WHERE id = ?",[$l]);
$c = $q->count();
if($c < 1){
  Redirect::to("admin.php?view=plugins_config&plugin=downloads&v=links&folder=".$folder."&err=Link not found");
}else{
  $link = $q->first();
}

if(!empty($_POST['deleteMe'])){
  $db->query("DELETE FROM plg_download_links WHERE id = ?",[$l]);
    Redirect::to("admin.php?view=plugins_config&plugin=downloads&v=links&folder=".$folder."&err=Link deleted");
}

if(!empty($_POST['disableMe'])){
  $db->update('plg_download_links',$l,['disabled'=>1]);
    Redirect::to("admin.php?view=plugins_config&plugin=downloads&v=editlinks&link=".$l."&folder=".$folder."&err=Link disabled");
}

if(!empty($_POST['enableMe'])){
  $db->update('plg_download_links',$l,['disabled'=>0]);
    Redirect::to("admin.php?view=plugins_config&plugin=downloads&v=editlinks&link=".$l."&folder=".$folder."&err=Link enabled");
}

if(!empty($_POST['updatelink'])){
  $fields = array(
    'file'=>$link->id,
    'folder'=>$link->folder,
    'user'=>Input::get('user'),
    'perms'=>Input::get('perms'),
    'max'=>Input::get('max'),
    'expires'=>Input::get('expires'),
    'dlcode'=>Input::get('dlcode'),
  );

  $db->update("plg_download_links",$l,$fields);
  Redirect::to("admin.php?view=plugins_config&plugin=downloads&v=links&folder=".$folder."&err=Link Updated");
}
?>

<div class="row">
  <div class="col-sm-12">
    <br>
    <h3>Edit Custom Link</h3>
    This form allows you to create one off links for your files with very granular permissions. It's up to you to not put dumb values in here :)
    <div class="row">
      <div class="col-6 col-sm-4 col-md-2">
        <form class="" action="" method="post" onsubmit="return confirm('Do you really want to delete this link?');">
          <input type="hidden" name="csrf" value="<?=$token?>">
          <input type="hidden" name="folder" value="<?=$folder?>">
          <input type="hidden" name="link" value="<?=$l?>">
          <input type="submit" name="deleteMe" value="Delete Link" class="btn btn-danger">
        </form>
      </div>
      <div class="col-6 col-sm-4 col-md-2">
        <?php
        if($link->disabled == 0){ ?>
          <form class="" action="" method="post" >
            <input type="hidden" name="csrf" value="<?=$token?>">
            <input type="hidden" name="folder" value="<?=$folder?>">
            <input type="hidden" name="link" value="<?=$l?>">
            <input type="submit" name="disableMe" value="Disable Link" class="btn btn-warning">
          </form>
        <?php }else{ ?>
            <form class="" action="" method="post" >
              <input type="hidden" name="csrf" value="<?=$token?>">
              <input type="hidden" name="folder" value="<?=$folder?>">
              <input type="hidden" name="link" value="<?=$l?>">
              <input type="submit" name="enableMe" value="Enable Link" class="btn btn-success">
            </form>
        <?php } ?>
      </div>
    </div>



    <form class="" action="" method="post">
      <input type="hidden" name="csrf" value="<?=$token?>">
      <div class="form-group">
        <label for="">Enter up to ONE user ID</label><br>
        <input type="number" name="user" value="<?=$link->user?>" min="0" step="1" required>
      </div>

      <div class="form-group">
        <label for="">Number of times this link can be used (0 for unlimited)</label><br>
        <input type="number" name="max" value="<?=$link->max?>" min="0" step="1" required>
      </div>

      <div class="form-group">
        <label for="">Expiration Date/Time of this link.  (0000-00-00 00:00:00 for never expires)</label><br>
        <input type="datetime" name="expires" value="<?=$link->expires?>">
      </div>

      <div class="form-group">
        <label for="">Comma separated list of permissions who can use this link (blank for unlimited)</label><br>
        <input type="text" name="perms" value="<?=$link->perms?>">
      </div>

      <div class="form-group">
        <label for="">Download Code (Feel free to change it)</label><br>
        <input type="text" name="dlcode" value="<?=$link->dlcode?>">
      </div>
      <div class="form-group">
        <input type="submit" name="updatelink" value="Update Link" class="btn btn-primary">
      </div>
    </form>

  </div>
</div>
