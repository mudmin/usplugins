  <?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

<?php
include "plugin_info.php";
pluginActive($plugin_name);
$f = '';
 if(!empty($_POST['plugin_hasher'])){
   $f = Input::get('file');
 }
$files = scandir($abs_us_root.$us_url_root.'/usersc/plugins/hasher');
 ?>
<div class="content mt-3">
 		<div class="row">
 			<div class="col-sm-8 offset-2">
      <h3 align="center">Hasher</h3>
        <form class="" action="" method="post">
          <label for="type"></label>
          <div class="d-flex">
          <select class="form-control" name="file">
            <option value="" disabled selected="selected">--Select File--</option>
            <?php
            foreach ($files as $file) {
              $extension = pathinfo($file, PATHINFO_EXTENSION);
              if ($extension == 'zip') { ?>
                <option value="<?=$file?>"><?=$file?></option>
              <?php }
            }
            ?>
          </select>
           <input type="submit" name="plugin_hasher" value="Go">
        </div>
        </form>
        <?php
if($f != ''){
$zip = new ZipArchive;
	if($zip->open($abs_us_root.$us_url_root.'/usersc/plugins/hasher/'.$f) != "true")
	{
	 echo "Error :- Unable to open the Zip File";
 }else{
	$newCrc = base64_encode(hash_file("sha256",$zip->filename));
	?>
  <br><br>
  <h4 align="center">The hash of <strong><?=$f?></strong> is...</h4><br>
  <p class="text-center"><?=$newCrc?></p>
  <?php
  }
}
?>
