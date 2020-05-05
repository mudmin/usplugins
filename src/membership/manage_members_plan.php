<?php
/*
UserSpice 5
An Open Source PHP User Management System
by the UserSpice Team at http://UserSpice.com

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
require_once '../../../users/init.php';
require_once $abs_us_root.$us_url_root.'users/includes/template/prep.php';
include "plugin_info.php";
pluginActive($plugin_name);
if (!securePage($_SERVER['PHP_SELF'])){die();}
$memset = $db->query("SELECT * FROM plg_mem_settings")->first();
$date = date("Y-m-d");
$id = Input::get('userid');
$method = Input::get('method');
$uiQ = $db->query("SELECT * FROM users WHERE id = ?",[$id]);

$uiC = $uiQ->count();
if($uiC < 1){
	Redirect::to('manage_members.php?err=User+not=found');
}
$u = $uiQ->first();
$plans = $db->query("SELECT * FROM plg_mem_plans WHERE disabled = 0 ORDER BY ordering")->results();

$ps = false;
$pl = Input::get('ps');
if(is_numeric($pl)){$ps = true;}
$sel = false;
$costSel = false;
if($ps){
	$costs = $db->query("SELECT * FROM plg_mem_cost WHERE plan = ? AND disabled = 0 ORDER BY days",[$pl])->results();

	$selected = $db->query("SELECT * FROM plg_mem_plans WHERE id = ?",[$pl])->first();

	$sel = true;
	$cst = Input::get('cst');
	if(is_numeric($cst)){
		$costSel = true;
		$costPlan = $db->query("SELECT * FROM plg_mem_cost WHERE id = ?",[$cst])->first();
		if($costPlan->plan != $pl){
			Redirect::to("manage_members_plans.php?userid=$id&method=$method&err=Something+is+wrong");
		}
	}

}

?>

<div id="page-wrapper">
	<div class="container">
		<div class="row">
			<div class="col-sm-12">
				<h3>Manage <?=echouser($u->id)?></h3>

				<strong>Current Plan:</strong> <?=echoPlanName($u->plg_mem_level);?><br>
				<strong>Exp. Date:</strong> <?php if($u->plg_mem_exp < $date){
					$e = true; ?>
					<font color="red">
					<?php }else{
						$e = false;
					}
					echo $u->plg_mem_exp;
					?>
					<font color="black"><br>
						<?php if($sel && $costSel){
							$billed = 0;
							$creditAccount = 0;
							$whatHappened = "";
							?>
							<form class="" action="manage_members_plan.php" method="get">
								Great! This is what we have:<br>
								<input type="hidden" name="userid" value="<?=$id?>">
								<input type="hidden" name="method" value="<?=$method?>">
								<input type="hidden" name="ps" value="<?=$pl?>">
								<input type="hidden" name="cst" value="<?=$cst?>">
								<?php if($u->plg_mem_exp < $date){
									$whatHappened = "New Plan";
									$billed = $costPlan->cost;
									?>
									Because there is no active current plan, there is no pro-rated cost.<br>
								<?php }elseif($u->plg_mem_level == $pl){
									$whatHappened = "Plan extended";
									$billed = $costPlan->cost;
									?>
									Because  you are not changing membership levels, there is no pro-rated cost. <br>
									These days will simply extend the user's expiration date.<br>
								<?php }else{
									$whatHappened = "Plan changed";
									if($u->plg_mem_level > 0 && $u->plg_mem_cost > 0){
									$oldPlan = $db->query("SELECT * FROM plg_mem_cost WHERE id = ?",[$u->plg_mem_cost])->first();
									$oldcpd = number_format($oldPlan->cost/$oldPlan->days,2,".","");
								}else{
									$oldcpd = 0;
								}
									$newcpd = number_format($costPlan->cost/$costPlan->days,2,".","");
									// dump($oldcpd);dump($newcpd);
									$today = date_create($date);
									$ends = date_create($u->plg_mem_exp);
									$interval = date_diff($today, $ends);
									$interval = $interval->format('%a');
									$credit = number_format($interval * $oldcpd,2,".","");
									echo "<p>Since the existing plan has not expired, you have a credit of <font color='red'>$memset->sym $credit</font> for your $interval unused day(s)<br>at the cost of $oldcpd per day.</p>";
									if($credit > $costPlan->cost){
										echo "<p>There is no charge to make this change.</p>";
										echo "<p><strong>Please note:</strong> Although the credit will be used for this plan change, any balance will NOT automatically be refunded.  Please see the site administrator for any refunds necessary.</p>";
									}else{
										echo "Since the new plan is more expensive than the old plan, there is a cost to update the existing billing cycle.<br>";
										echo "The billable amount for the current period is: <font color='red'>$memset->sym ";

										$billed = $costPlan->cost - $credit;
										echo number_format($billed,2,".","");
										echo "</font><br>";
										$billed = $billed + $costPlan->cost;
										echo "In other words, the plan will be <strong>upgraded</strong> for the strong current period and <strong>extended</strong> to the new billing date.<br>";
										$credit = 0.00;
									}

								} ?>

								<strong>New Plan:</strong> <?=echoPlanName($pl);?><br>
								<strong>New Expiration Date:</strong>
								<?php
								if($e){
									$newdate = new DateTime($date);
								}else{
									$newdate = new DateTime($u->plg_mem_exp);
								}
								$newdate->add(new DateInterval('P'.$costPlan->days.'D'));
								?>
								<input type="date" name="expiration" value="<?=$newdate->format('Y-m-d')?>">
								<br>
								<strong>To Be Collected:<font color="red"></strong> <?=$memset->sym?>
									<input type="number" name="billed" min="0" step=".01" value="<?=number_format($billed,2,".","");?>">
									</font><br>
								<strong>The user should get a credit of <font color="red"><?=$memset->sym?></font><input type="number" min="0" step=".01" name="cred" value="<?=number_format($credit,2,".","");?>"> back to their original payment method.
									(This is for you to deal with outside of this system).</strong> <br>
								<br>
								<strong>This is your system and you can do whatever you want<strong><br>
									If you change the numbers above, your values will be used instead of the ones we calculated.<br>
								<input type="submit" name="submitFinal" value="Finalize Changes" class="btn btn-primary">
							</form>
							<?php
							if(!empty($_GET['submitFinal'])){
								$nd = Input::get('expiration');
								$billed = Input::get('billed');
								$cred = Input::get('cred');
								$fields = array(
									'plg_mem_expired'=>0,
									'plg_mem_exp'=> $nd,
									'plg_mem_level'=>$pl,
									'plg_mem_cred'=>$cred,
									'plg_mem_cost'=>$costPlan->id,
								);
								$db->update('users',$id,$fields);
								if($cred > 0){
									logger($user->data()->id,"Membership","$u->username($u->id) credited $cred for plan change");
									// $creditAccount = $creditAccount + $u->plg_mem_cred;
								}
								logger($user->data()->id,"Membership","$whatHappened for $u->username($u->id) in back office. Billed $memset->sym $billed");
								if(file_exists($abs_us_root.$us_url_root.'usersc/plugins/membership/scripts/'.$selected->script)){
									include $abs_us_root.$us_url_root.'usersc/plugins/membership/scripts/'.$selected->script;
								}
								changeOfPlans($u->plg_mem_level,$selected->id,$u->id);
								Redirect::to('manage_members.php?err='.$whatHappened);
							} ?>
						<?php } ?>
						<?php if(!$ps && !$sel){?>
							<?php if($e){ ?>
								This user does not have an active plan. Please choose a new one.<br>
							<?php }else{ ?>
								This user has an active plan. If you choose to upgrade/downgrade their plan, the balance for the rest of this period will be pro-rated.<br>
							<?php } ?>
							<form class="" action="manage_members_plan.php" method="get">
								<input type="hidden" name="userid" value="<?=$id?>">
								<input type="hidden" name="method" value="<?=$method?>">
								<select class="" name="ps">
									<option value="" disabled selected="selected">---Please Choose a Level---</option>
									<?php foreach($plans as $p){ ?>
										<option value="<?=$p->id?>"><?=$p->plan_name?></option>
									<?php 	} ?>
								</select>
								<input type="submit" name="cp" value="Select Level">
							</form>
						<?php } ?>
						<?php if($sel && !$costSel){?>
							<form class="" action="manage_members_plan.php" method="get">
								Membership level chosen. Now choose a plan.<br>
								<input type="hidden" name="userid" value="<?=$id?>">
								<input type="hidden" name="method" value="<?=$method?>">
								<input type="hidden" name="ps" value="<?=$pl?>">
								<select class="" name="cst">
									<option value="" disabled selected="selected">---Please Choose a Plan---</option>
									<?php foreach($costs as $p){ ?>
										<option value="<?=$p->id?>"><?=$p->descrip?> - <?=$p->days?> days - <?=$memset->sym?><?=$p->cost?></option>
									<?php 	} ?>
								</select>
								<input type="submit" name="cc" value="Select Plan">
							</form>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>


		<?php require_once $abs_us_root . $us_url_root . 'users/includes/html_footer.php'; ?>
