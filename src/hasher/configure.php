<?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

  <?php
  include "plugin_info.php";
  pluginActive($plugin_name);

  $files = scandir($abs_us_root.$us_url_root.'/usersc/plugins/hasher');
  if(!empty($_POST)){
    $token = Input::get('csrf');
    if(!Token::check($token)){
      include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
    }

    if(!empty($_POST['purgeZips'])){
      foreach ($files as $file) {
        $extension = pathinfo($file, PATHINFO_EXTENSION);
        if ($extension == 'zip') {
          if(file_exists($abs_us_root.$us_url_root."usersc/plugins/hasher/".$file)){
            unlink($abs_us_root.$us_url_root."usersc/plugins/hasher/".$file);
          }
        }
      }
      Redirect::to($us_url_root."users/admin.php?view=plugins_config&plugin=hasher&msg=Zip Files Purged");
    }
  }

  ?>
  <div class="content mt-3">
    <div class="row">
      <div class="col-sm-12">
        <h3 align="center">Hasher</h3>
        <br>
        <form class="" action="" method="post" onsubmit="return confirm('Do you really want to do this? It cannot be undone.');">
          <input type="hidden" name="csrf" value="<?=Token::generate();?>">
          <div class="text-center">
            <input type="submit" name="purgeZips" value="Purge Zip Files" class="btn btn-danger">
          </div>
        </form>
        <br>
        <?php
        foreach ($files as $file) {
          $extension = pathinfo($file, PATHINFO_EXTENSION);
          if ($extension == 'zip') {
            $zip = new ZipArchive;
            if($zip->open($abs_us_root.$us_url_root.'/usersc/plugins/hasher/'.$file) != "true")
            {
              echo "Error :- Unable to open the Zip File";
            }else{
              $newCrc = base64_encode(hash_file("sha256",$zip->filename));
              ?>
              <h4 align="center"><?=$file?></strong>

                <button type="button" class="btn btn-primary" onclick="copyStringToClipboard('<?=$newCrc?>');">Copy</button>
                <br>
                <p class="text-center"><?=$newCrc?></p>
                <?php
              }
              ?>
              <hr>
              <?php
            }
          }

?>
<script type="text/javascript">
function copyStringToClipboard (textToCopy) {
  navigator.clipboard.writeText(textToCopy)
}
</script>
