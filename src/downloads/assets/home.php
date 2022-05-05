<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
if(!empty($_POST['saveDlSettings'])){
  $fields = [
    'dlmode'=>Input::get('dlmode'),
    'baseurl'=>Input::get('baseurl'),
    'parser'=>Input::get('parser'),
    'perms'=>Input::get('perms'),
  ];
  $db->update('plg_download_settings',1,$fields);
  Redirect::to('admin.php?view=plugins_config&plugin=downloads&v=home&err=Saved');
}

if(!empty($_POST['resetDatabase'])){
  $db->query("TRUNCATE TABLE plg_download_logs");
  $db->query("TRUNCATE TABLE plg_download_files");
  $db->query("TRUNCATE TABLE plg_download_links");
  Redirect::to('admin.php?view=plugins_config&plugin=downloads&v=home&err=Your database has been reset');
}
?>
<div class="row">
  <div class="col-sm-12">
    <br>
    <h3>Global Plugin Settings</h3>

    <form class="" action="" method="post">
      <input type="hidden" name="csrf" value="<?=$token?>">
      <div class="form-group">

        <strong>Very Important:</strong>  The settings you set here overrides all other settings.  So if you open all your files to "all registered users" here, no other permissions matter.<br>
        <label for="">Download Mode</label>

      <select class="form-control" name="dlmode" required>
        <?php foreach($downloadModes as $k=>$v){ ?>
          <option <?php if($k == $plgSet->dlmode){echo "selected='selected'";} ?> value="<?=$k?>"><?=$v?></option>
        <?php } ?>
      </select>
      </div>

      <div class="form-group">
        <label for="">Permissions IDs allowed to download for Mode 3 (Comma Separated List of IDs)</label>
        <input type="text" name="perms" value="<?=$plgSet->perms?>" class="form-control">
      </div>

      <div class="form-group">
        <label for="">Base URL of your site with final "/" such as https://example.com/ </label>
        <input type="text" name="baseurl" value="<?=$plgSet->baseurl?>" class="form-control" required>
      </div>

      <div class="form-group">
        <label for="">Download parser location. Must be a subfolder with the final "/".  By default this should be dl/ but you can move or rename the folder if you like.</label>
        <input type="text" name="parser" value="<?=$plgSet->parser?>" class="form-control" required>
      </div>

      <div class="form-group">
        <br>
        <input type="submit" name="saveDlSettings" value="Save" class="btn btn-primary">
      </div>
    </form>

    <h3>Reset Database</h3>
    <form class="" action="" method="post" onsubmit="return confirm('Do you really want to clear your links and logs? THIS CANNOT BE UNDONE and all links that have been shared will no longer work.');">
      <input type="hidden" name="csrf" value="<?=$token?>">
      <p>If you have been messing around creating links and want to reset your database, click the button below.  Please note, this will delete the info in your database referring
      to this plugin, but it will NOT delete your plugin settings or your files that you have in your /files folder.  This will truncate...</p>
      <p>plg_download_logs</p>
      <p>plg_download_files</p>
      <p>plg_download_links</p>
      <input type="submit" name="resetDatabase" value="Reset the Database" class="btn btn-danger">
    </form>
</div> <!-- /.row -->
