<?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

  <?php
  include "plugin_info.php";
  pluginActive($plugin_name);
  if(!empty($_POST['stripe_credit'])){
    $token = $_POST['csrf'];
    if(!Token::check($token)){
      include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
    }
    $fields = array(
      'stripe_public'=>trim(Input::get('stripe_public')),
      'stripe_private'=>trim(Input::get('stripe_private')),
    );
    $db->update('settings',1,$fields);
    err('Stripe+settings+updated!');
  }
  $tranQ = $db->query("SELECT * FROM stripe_transactions ORDER BY id DESC LIMIT 25");
  $tranC = $tranQ->count();
  if($tranC > 0){
    $tran = $tranQ->results();
  }
  $token = Token::generate();
  ?>
  <div class="content mt-3">
    <a href="<?=$us_url_root?>users/admin.php?view=plugins">Return to the Plugin Manager</a>
    <h1>Configure the Stripe Plugin</h1>
    <h3><a href="<?=$us_url_root?>usersc/plugins/stripe/files/example.php"><font color="blue">View the Example Form</font></a></h3>
    Note: To must be on a live server (not localhost) and have properly configured https. You will also need an account at stripe.com and your API keys.
    <form class="" action="" method="post">
      <label for="">Your Stripe Secret Key</label>
      <input type="password" name="stripe_private" value="<?=$settings->stripe_private?>"><br>
      <label for="">Your Stripe Publishable Key</label>
      <input type="text" name="stripe_public" value="<?=$settings->stripe_public?>"><br>

      <label for="">Your Test Stripe Secret Key</label>
      <input type="password" name="stripe_private_test" value="<?=$settings->stripe_private_test?>"><br>
      <label for="">Your Test Stripe Publishable Key</label>
      <input type="text" name="stripe_public_test" value="<?=$settings->stripe_public_test?>"><br>
      <input type="hidden" name="csrf" value="<?=$token?>">
      Please note that live mode will force https, so you will want to have that configured first.<br>
      <label for="">Stripe Mode</label>
      <select class="" name="stripe_live">
        <option <?php if($settings->stripe_live == 0){echo "selected";} ?> value="0">Test Mode</option>
        <option <?php if($settings->stripe_live == 1){echo "selected";} ?> value="1">Live Mode</option>
      </select><br>
      <input type="submit" name="stripe_credit" value="Update" class="btn btn-primary">
    </p>
  </form>
  <div class="row">
    <div class="col-sm-12">
      <h3>Transaction History</h3><br>
      <h4>
        <?php if($tranC > 0){
          if($tranC == 1){
            echo "Your last transaction";
          }elseif($tranC > 25){
            echo "Your last 25 transactions";
          }else{
            echo "Your last ".$tranC." transactions";
          }
          ?>
        </h4>
        <table class="table table-striped table-responsive">
          <thead>
            <tr>
              <th>User</th><th>Timestamp</th><th>Type</th><th>Name</th><th>Amount</th><th>Email</th><th>ID</th><th>Live?</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($tran as $t){ ?>
              <tr>
                <td><?php echoUser($t->user);?></td>
                <td><?=$t->timestamp?></td>
                <td><?=$t->trans_type?></td>
                <td><?=$t->fname?> <?=$t->lname?></td>
                <td><?=$t->email?></td>
                <td><?php echo money($t->amount);?></td>
                <td><?=$t->charge_id?></td>
                <td><?php echo bin($t->live);?></td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      <?php }else{ ?>
        <h4>You do not have any transactions</h4>
      <?php } ?>
    </div>
  </div>
