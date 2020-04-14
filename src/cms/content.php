<?php
//This is the file that renders your CMS/Blog content and can be copied anywhere on your and
//even renamed to whatever you want.

//if you duplicate this file, you will have to update the path to the init file below
// for instance, if you put it in root it's 'users/init.php'

//It can accept content by id or slug
require_once "../../../users/init.php";
require_once $abs_us_root.$us_url_root.'users/includes/template/prep.php';
$c = Input::get('c');
if($c != ""){
  $contentQ = $db->query("SELECT * FROM plg_cms_content WHERE id = ? OR slug = ?",[$c,$c]);
  $contentC = $contentQ->count();
  if($contentC > 0){
    $content = $contentQ->first();
  }
}
?>
<div id="page-wrapper">
  <div class="container">
    <?php
    if($c != "" && $contentC > 0){
      // dump($content);
      displayLayout($content);
    }else{
      echo "<h3 align='center'>Not found</h3>";
    }
    ?>

  </div>
</div>
