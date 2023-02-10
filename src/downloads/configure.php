  <?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

<?php
include "plugin_info.php";
pluginActive($plugin_name);
 if(!empty($_POST['plugin_downloads'])){
   $token = $_POST['csrf'];
if(!Token::check($token)){
  include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
}
   // Redirect::to('admin.php?err=I+agree!!!');
 }
 $token = Token::generate();
 $view = Input::get('v');
 if($view == ""){
   $view = "home";
 }

$plgSet = $db->query("SELECT * FROM plg_download_settings")->first();
$downloadModes = dlPluginModes();
if(!file_exists($abs_us_root.$us_url_root.$plgSet->parser."index.php")){
  bold("WARNING: Parser file not found. Please copy usersc/plugins/downloads/assets/dl/index.php to /".$plgSet->parser."index.php");
}
 // $skip = [".","..",".htaccess","readme.txt"];
 // $files = scandir($abs_us_root.$us_url_root."usersc/plugins/downloads/files");
 // $dirs = glob($abs_us_root.$us_url_root."usersc/plugins/downloads/files/*" , GLOB_ONLYDIR);
 ?>
<div class="content mt-3">
 		<div class="row">
 			<div class="col-sm-12">
          <a href="<?=$us_url_root?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
 					<h1>Protected Downloads Plugin</h1>
          <?php include($abs_us_root.$us_url_root."usersc/plugins/downloads/assets/menu.php"); ?>
      </div> <!-- /.col -->
 		</div> <!-- /.row -->
<?php
    if(file_exists($abs_us_root.$us_url_root."usersc/plugins/downloads/assets/".$view.".php")){
    include($abs_us_root.$us_url_root."usersc/plugins/downloads/assets/".$view.".php");
   }
if(!isset($chartsLoaded) || $chartsLoaded != true){
?>
<script type="text/javascript" src="<?=$us_url_root?>users/js/pagination/datatables.min.js"></script>
<script>
$(document).ready(function () {
   $('.paginate').DataTable({"pageLength": 25,"stateSave": true,"aLengthMenu": [[25, 50, 100, -1], [25, 50, 100, 250, 500]], "aaSorting": []});

});
<?php
}  //end duplicate loading protection
?>
function copyStringToClipboard (textToCopy) {
  console.log(textToCopy);
  navigator.clipboard.writeText(textToCopy);
}
</script>
