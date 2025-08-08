<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
global $user;
$pset = $db->query("SELECT * FROM plg_spicebin_settings")->first();

if($pset->account == 1){
?>
<p>
  <a href="<?=$us_url_root.$pset->your_page?>" class="btn btn-primary btn-block"><?=$pset->product_button?></a>
</p>
<?php } ?>
