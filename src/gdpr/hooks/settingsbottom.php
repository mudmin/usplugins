<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
$last = $db->query("SELECT * FROM us_gdpr ORDER BY id DESC LIMIT 1")->first();

if($last->delete==1){
?>

<form class="" action="" method="post">
  <input type="hidden" name="csrf" value="<?=Token::generate();?>" />
  <input type="hidden" name="gdprhook" value="1">
  <?php if($last->delete == 1){ ?>
    <input type="submit" name="gdprDelete" value="<?=$last->btn_delete?>" class="btn btn-danger">
  <?php } ?>
<?php } ?>
