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
 			<div class="col-sm-12">
      <h3 align="center">Hasher</h3>
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
                  <h4 align="center"><?=$file?></strong><br>
                  <p class="text-center"><?=$newCrc?></p>
                  <?php
                  }
                  ?>
                  <hr>
                  <?php
               }
            }
