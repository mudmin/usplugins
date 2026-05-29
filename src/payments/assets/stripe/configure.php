<?php if(count(get_included_files()) ==1) die();?>
<h3>Stripe Payment Option</h3>
<?php
$keysQ = $db->query('SELECT * FROM `keys`');
$keysC = $keysQ->count();
if($keysC < 1){
  $db->query("TRUNCATE TABLE `keys`");
  // `keys` is a reserved word; raw query() works on both old and new DB classes
  $db->query("INSERT INTO `keys` (`currency`) VALUES (?)",['usd']);
  // Re-query fresh: $keysQ is the stale (empty) result from before the insert
  $keys = $db->query('SELECT * FROM `keys`')->first();
}else{
  $keys = $keysQ->first();
  if($keys->currency == ''){
    $db->query("UPDATE `keys` SET `currency` = ? WHERE id = ?",['usd',$keys->id]);
    $keys->currency = 'usd';
  }
}
if(!empty($_POST['updateStripe'])){
  $db->query("UPDATE `keys` SET `stripe_lp` = ? , `stripe_ls` = ? WHERE id = ?",[Input::get('stripe_lp'),Input::get('stripe_ls'),$keys->id]);
  // Session flash (not an ?err= param) so the message shows once, not also as an alerts toast
  usSuccess('Stripe settings saved');
  Redirect::to('admin.php?view=plugins_config&plugin=payments');
}
?>
<form class="" action="" method="post" autocomplete="off" data-lpignore="true" data-form-type="other">
    <div class="form-group">
      <label for="">Publishable (Public) Stripe Key</label>
      <input type="text" name="stripe_lp" value="<?=$keys->stripe_lp ?? ''?>" class="form-control" required autocomplete="off" data-lpignore="true" data-1p-ignore data-bwignore="true" data-form-type="other">
    </div>

    <div class="form-group">
      <label for="">Private (Secret) Stripe Key</label>
      <input type="password" name="stripe_ls" value="<?=$keys->stripe_ls ?? ''?>" class="form-control" required autocomplete="off" data-lpignore="true" data-1p-ignore data-bwignore="true" data-form-type="other">
    </div>
  <input type="hidden" name="csrf" value="<?=$token?>" />
  <input type="submit" name="updateStripe" value="Update Stripe Settings" class="btn btn-primary">
</form>
