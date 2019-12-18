<?php if(count(get_included_files()) ==1) die();?>
<h3>Stripe Payment Option</h3>
<?php
$keysQ = $db->query('SELECT * FROM `keys`');
$keysC = $keysQ->count();
if($keysC < 1){
  $db->query("TRUNCATE TABLE `keys`");
  $db->insert("`keys`",['currency'=>'usd']);
  $keys = $keysQ->first();
}else{
  $keys = $keysQ->first();
  if($keys->currency == ''){
    $db->update('`keys`',$keys->id,['currency'=>'usd']);
    $keys->currency = 'usd';
  }
}
if(!empty($_POST['updateStripe'])){
  $db->update('`keys`',$keys->id,['stripe_lp'=>Input::get('stripe_lp'),'stripe_ls'=>Input::get('stripe_ls')]);
  Redirect::to('admin.php?view=plugins_config&plugin=payments&err=Stripe+settings+saved');
}
?>
<form class="" action="" method="post">
    <div class="form-group">
      <label for="">Publishable (Public) Stripe Key</label>
      <input type="text" name="stripe_lp" value="<?=$keys->stripe_lp?>" class="form-control" required>
    </div>

    <div class="form-group">
      <label for="">Private (Secret) Stripe Key</label>
      <input type="password" name="stripe_ls" value="<?=$keys->stripe_ls?>" class="form-control" required>
    </div>
  <input type="hidden" name="csrf" value="<?=$token?>" />
  <input type="submit" name="updateStripe" value="Update Stripe Settings" class="btn btn-primary">
</form>
