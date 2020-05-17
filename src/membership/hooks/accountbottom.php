<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
if(pluginActive('payments',true)){
$status = "";
global $user,$settings;
$memset = $db->query("SELECT * FROM plg_mem_settings")->first();
$status = memberPlanStatus();
$extend = Input::get('extend');
$memSettings = $db->query("SELECT * FROM plg_mem_settings")->first();
$plans = $db->query("SELECT * FROM plg_mem_plans ORDER BY ordering")->results();
$ps = false;
$pl = Input::get('ps');
if(is_numeric($pl)){$ps = true;}
$sel = false;
$membershipChange = Input::get('change');
$opt = Input::get('opt');
$pass = false;
$formInfo = [
    'processed' =>false,
  	'success'		=>false,
  ];

if(!empty($_POST && $membershipChange == "membership" && $opt == "checkout")){
  $po = Input::get('paymentOption');
  if(haltPayment($po)){Redirect::to('account.php?change=membership&err=Invalid+payment+method');}
  $plan = Input::get('plan');
    if($status == "Active" && $plan != $user->data()->plg_mem_level){
      Redirect::to('account.php?change=membership&err=Only+an+admin+can+change+your+plan');
    }
  $cost = Input::get('plan_cst');
  $check1 = $db->query("SELECT * FROM plg_mem_plans WHERE disabled = 0 AND id = ?",[$plan])->count();
  $check2Q = $db->query("SELECT * FROM plg_mem_cost WHERE id = ? AND plan = ? AND disabled = 0",[$cost,$plan]);
  $check2C = $check2Q->count();
  if($check1 < 1 || $check2C < 1){
    Redirect::to('account.php?change=membership&err=Invalid+plan+selected');
  }
  $check2 = $check2Q->first();
  $formInfo = [
  	'method'		=>$po,
  	'action'		=>'', 												//can be blank
  	'total'			=>$check2->cost,
  	'email'			=>Input::get('email'),   //not required, but ideal
  	'reason'		=>'Membership at '.$settings->site_name,						//not required, but ideal
  	'notes' 		=>'From the account page',					//Stored in plg_payments table
  	'processed' =>false, 											//do not change this
  	'success'		=>false, 											//do not change this
  	'id'				=>null,												//do not change this
  	'msg'				=>null,												//do not change this
  	'callback'  =>null,												//for future use, a php file for the payment processor to callback
  	'redir'			=>null,												//for future use, where to go after success
  	'submit'    =>"<button class='btn btn-success' type='submit'>Submit Payment</button>"
  	//optional submit button only works for the displayPayment function, not payment1, payment2 etc
  ];
  $pass = true;
}
?>

Membership Status: <?=$status?><br>
<?php if($status == "Active"){ ?>
Membership Plan:  <?=echoPlanName($user->data()->plg_mem_level);?> <br>
<form class="" action="account.php?change=membership&opt=checkout" method="post">
Membership Expires: <?=$user->data()->plg_mem_exp; ?><br>
<?php if($memSettings->payments == 1 && $membershipChange == "membership" && !$pass){?>
  <form class="" action="" method="get">
    <input type="hidden" name="plan" value="<?=$user->data()->plg_mem_level?>">
    Extend your expiration date with the following plan. If you would like to change your membership level, please contact an administrator. <br>
    <select class="" name="plan_cst">
      <option value="" disabled selected="selected">---Please Choose a Plan---</option>
      <?php
      $costs = $db->query("SELECT * FROM plg_mem_cost WHERE plan = ? AND disabled = 0 ORDER BY days",[$user->data()->plg_mem_level])->results();
      foreach($costs as $p){ ?>
        <option value="<?=$p->id?>"><?=$p->descrip?> - <?=$p->days?> days - <?=$memset->sym?><?=$p->cost?></option>
      <?php 	} ?>
    </select>
    <div class="form-group">
          <?php showPaymentOptions();?>
    </div>

    <input type="submit" name="cc" value="Select Plan">
  </form><br>
<?php }
}elseif($membershipChange == "membership" && !$pass){ ?>
  <br>
  <form class="" action="account.php?change=membership&opt=checkout" method="post">
    <div class="form-group">
      <label for="">Please choose a membership level</label><br>
        <select class="" name="plan" id="plan">
        <option value="0" disabled selected="selected">---Please Choose a Level---</option>
        <?php foreach($plans as $p){ ?>
          <option value="<?=$p->id?>"><?=$p->plan_name?></option>
        <?php 	} ?>
      </select>
    </div>

    <div class="form-group">
      <label for="">Please choose a membership plan </label><br>
      <select class="" name="plan_cst" required id="plan_cst">
        <option value="" disabled selected="selected">---Please Choose an Option---</option>
        <?php
        $costs = $db->query("SELECT * FROM plg_mem_cost WHERE disabled = 0 ORDER BY days")->results();
        foreach($costs as $p){ ?>
          <option class="planOption" data-plan="<?=$p->plan?>" value="<?=$p->id?>"><?=$p->descrip?> - <?=$p->days?> days - <?=$memset->sym?><?=$p->cost?></option>
        <?php 	} ?>
      </select>
    </div>
    <?php showPaymentOptions();?>
    <input type="submit" name="newPlan" value="Select Plan" id="subBtn" class="btn btn-primary">
  </form>
<?php }
if($pass){ //checkout
$formInfo = payment1($formInfo);
if($formInfo['success'] == true){
  if($status == "Active"){
    $newdate = new DateTime($user->data()->plg_mem_exp);
    $newdate->add(new DateInterval('P'.$check2->days.'D'));
    $db->update('users',$user->data()->id,['plg_mem_exp'=>$newdate->format('Y-m-d')]);
    logger($user->data()->id,"Membership","Extended Membership through ".$newdate->format('Y-m-d')." for ".$formInfo['total']);
  }else{
    $newdate = new DateTime(date("Y-m-d"));
    $newdate->add(new DateInterval('P'.$check2->days.'D'));
    $db->update('users',$user->data()->id,['plg_mem_exp'=>$newdate->format('Y-m-d'),'plg_mem_level'=>$plan]);
    changeOfPlans($user->data()->plg_mem_level,$plan,$user->data()->id);
    logger($user->data()->id,"Membership","Paid ".$formInfo['total']." via ".$formInfo['method']." for level $plan at cost $cost.");
  }
  Redirect::to('account.php?err=Payment+was+successful');
}
$formInfo = payment2($formInfo);?>
<input type="hidden" name="plan_cst" value="<?=$cost?>">
<input type="hidden" name="plan" value="<?=$plan?>">
<input type="hidden" name="paymentOption" value="<?=$po?>">
<button class='btn btn-primary payment-form' type='submit'>Submit Payment</button><br>
<?php
$formInfo = payment3($formInfo);
}
if($formInfo['processed'] == true && $formInfo['processed'] == false && $membershipChange == "membership" && $pass){
  Redirect::to('account.php?change=membership&err=Something+went+wrong+with+your+payment');
}
}
?>

<script type="text/javascript">
$( document ).ready(function() {
  $('.planOption').each(function() {
    $(this).hide();
  });
});

  $("#plan").change(function(event) {
	var val = $("#plan").val();
  $("#plan_cst")[0].selectedIndex = 0;
  $('.planOption').each(function() {

    if(val == $(this).attr('data-plan')){
      $(this).show();
    }else{
      $(this).hide();
    }
	});
  });



</script>
