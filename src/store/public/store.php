<?php
/*
UserSpice 4
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
?>
<?php
require '../../../../users/init.php';
require_once $abs_us_root.$us_url_root.'users/includes/template/prep.php';
//require_once $abs_us_root.$us_url_root.'users/includes/navigation.php';
if (!securePage($_SERVER['PHP_SELF'])){die();} $db=DB::getInstance(); if(!pluginActive("store")){die();}
if(!hasPerm([2])){
	if($settings->open == 0){
		Redirect::to('store_closed.php');
	}
}
$category = Input::get('category');
$inv = $db->query("SELECT * FROM store_store_inventory ORDER BY category, item")->results();

if(!isset($_SESSION['orderno']) || !is_numeric($_SESSION['orderno'])){
	$string = uniqid('', true);
	$db->insert('store_orders',['code'=>$string]);
	$_SESSION['orderno'] = $db->lastId();
}
$orderQ = $db->query("SELECT * FROM store_orders WHERE id = ?",array($_SESSION['orderno']));
$orderC = $orderQ->count();
if($orderC < 1){
	$string = uniqid('', true);
	$db->insert('store_orders',['code'=>$string]);
	$_SESSION['orderno'] = $db->lastId();
	Redirect::to('store.php');
}else{
	$order = $orderQ->first();
	$items = $db->query("SELECT * FROM store_order_items WHERE orderno = ?",array($_SESSION['orderno']))->results();
	$qty = 0;
	$tot = 0;
	foreach($items as $i){
		$qty = $qty + $i->qty;
		$tot = $tot + $i->price_tot;
	}
}

$cats = $db->query("SELECT * FROM store_categories WHERE is_subcat = 0 ORDER BY cat")->results();
// if(!empty($_POST)){
// 	$q = $_POST['qty'];
//
// 	//loop through existing items in order and update quantities as necessary
// 	$existingQ = $db->query("SELECT * FROM store_order_items WHERE orderno = ?",array($_SESSION['orderno']));
// 	$existingC = $existingQ->count();
//
// 	if($existingC > 0){ //items found
// 		$existing = $existingQ->results();
// 		foreach($existing as $e){ //loop through items in order
// 			foreach($q as $k=>$v){
// 				if($e->item == $k){ //posted item found in order
// 					$tot = $e->price_each * $v;
// 					$fields = array(
// 						'qty'=>$v,
// 						'price_tot'=>$tot,
// 					);
// 					$db->update('store_order_items',$e->id,$fields);
// 					unset($q[$k]); //remove from array
// 				}
// 			}
// 		}
// 	} //end of processing existing items
//
// 	foreach($q as $k=>$v){ //process what's left
// 		if($v < 1){continue;}//ignore 0s
// 		$price = itemPrice($k);
// 		if($price == "x"){continue;}
// 		$tot = $price * $v;
// 		$fields = array(
// 			'price_each'=>$price,
// 			'orderno'=>$_SESSION['orderno'],
// 			'item'=>$k,
// 			'qty'=>$v,
// 			'price_tot'=>$tot,
// 		);
// 		$db->insert('store_order_items',$fields);
// 	}
// 	Redirect::to('store.php?category='.$category.'&Cart+Updated!');
// }
?>
<style media="screen">
.container-fluid {
	padding-right:0;
	padding-left:0;
	margin-right:auto;
	margin-left:auto
}
</style>


<div id="page-wrapper">
	<div class="container-fluid">
		<!-- Page Heading -->
		<div class="row">
			<p align="center"><strong><?=$settings->header_msg?></strong></p>
			<div class="col-12">
				<h1 class="text-center">
					Order Items Online
				</h1>
			</div> <!-- /.col -->
			<div class="col-12 text-center">
				<h3>Current Order:<span id="cartTotal">
					<?php
					echo money($tot) ."</span> total and <span id='numItems'>";
					if($qty == 1){
						echo "1 item. ";
					}else{
						echo $qty . " items.";
					}
					?></span>
				</h3>
				<h3><strong><a href="cart.php"><i class="fa fa-fw fa-shopping-cart"></i> View Cart & Checkout</a></strong></h3>
				<br>
			</div> <!-- /.col -->
		</div> <!-- /.row -->
		<div class="row">
			<?php
			if(!is_numeric($category)){
				foreach($cats as $c){
					$count = $db->query("SELECT id FROM store_inventory WHERE disabled = 0 AND qoh > 0 AND (category = ? OR topcat = ?)",[$c->id,$c->id])->count();
					?>
					<div class="col-lg-3 col-sm-6">
						<a href="store.php?category=<?=$c->id?>" style="text-decoration: none;">
							<h3 align="center"><?=$c->cat?>(<?=$count?>)</h3>
							<?php fetchStoreSubCats($c->id);	?>
							<p align="center"><img width="250px" src="<?=$us_url_root.'usersc/plugins/store/img/'.$c->photo?>" alt=""></p>
						</a>
					</div>
					<?php
				}
			}else{
				$itemsQ = $db->query("SELECT * FROM store_inventory WHERE disabled = 0 AND (category = ? OR topcat = ?) ORDER BY item",array($category,$category));
				$itemsC = $itemsQ->count();
				if($itemsC < 1){
					Redirect::to('store.php?err=Sorry.+That+category+does+not+have+any+items.');
				}else{
					$items = $itemsQ->results();
					?>
				</div>
				<div class="row">
					<div class="col-md-12">
						<h2 align="center"><?php echoCat($category);?></h2>
						<p align="center">
							<?php
							$i = 1;
							foreach($cats as $c){ ?>

									<a href="store.php?category=<?=$c->id?>">
										<?php
										echoCat($c->id);
										?>
									</a>
									<?php
									if($i < count($cats)){
										echo "   -   ";
									}
									$i++;
							}?>
						</p>
					</div>
				</div>
		<!-- <form class="" action="store.php?category=<?php //echo $category?>" method="post"> -->
				<div class="row">
					<div class="col-12">
						<!-- <input type="submit" class="submit btn btn-primary pull-right" name="add" value="Update Cart" > -->
							<table class="table table-hover" id="paginate">
								<thead>
									<th>Item</th>
									<th>Category</th>
									<th>Price</th>
									<th>Quantity</th>
								</thead>
								<tbody>
									<?php foreach($items as $i){
										$so = false;
										if($settings->ignore_inventory == 0 && $i->digital == 0 && $i->qoh < 1){
											$so = true;
										}
										$oiq = 0;
										$orderItemQ = $db->query("SELECT qty FROM store_order_items WHERE orderno = ? AND item = ?",array($_SESSION['orderno'],$i->id));

										$orderItemC = $orderItemQ->count();
										if($orderItemC > 0){
											$orderItem = $orderItemQ->first();
											$oiq = $orderItem->qty;
										}
										?>
										<tr>
											<td><a href="item.php?item=<?=$i->id?>"><?=$i->item?></a></td>
											<td><?php echoCat($i->category);?></td>
											<td><?php echo money($i->price);?></td>
											<td>
												<?php if(!$so){?>
												<div id="<?=$i->id?>">
												<!-- <button type="button" id="sub" class="sub">-</button> -->
												<input type="number" class="qty" name="qty[<?=$i->id?>]" id="<?=$i->id?>" value="<?=$oiq?>" min="0" max="" />
												<!-- <button type="button" id="add" class="add">+</button> -->
											</div>
										<?php }else{
											echo "<strong>(Sold Out)</strong>";
										} ?>
											</td>
										</tr>
									<?php } ?>
								<!-- </form> -->
							</tbody>
						</table>
						<!-- <input type="submit" class="submit btn btn-primary pull-right" name="add" value="Update Cart" > -->
						<?php
					}
				}
				?>

			</div>
		</div>
	</div> <!-- /.container -->
</div> <!-- /.wrapper -->


<?php //require_once $abs_us_root.$us_url_root.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>
<script type="text/javascript">
$(document).ready(function() {
$('[data-toggle="popover"]').popover();

  function messages(data) {
    console.log("messages found");
    $('#messages').removeClass();
    $('#message').text("");
    $('#messages').show();
    if(data.success == "true"){
      $('#messages').addClass("sufee-alert alert with-close alert-success alert-dismissible fade show");
    }else{
      $('#messages').addClass("sufee-alert alert with-close alert-success alert-dismissible fade show");
    }
    $('#message').text(data.msg);
    $('#messages').delay(3000).fadeOut('slow');

  }

  $( ".qty" ).change(function() { //use event delegation

    var qty = $(this).val();
    var id = $(this).attr("id"); //the id in the input tells which field to update
    var desc = $(this).attr("data-desc"); //For messages
    var formData = {
      'qty' 				: qty,
      'id'					: id,
    };

    $.ajax({
      type 		: 'POST',
      url 		: "<?=$us_url_root?>usersc/plugins/store/parsers/cartParser.php",
      data 		: formData,
      dataType 	: 'json',
    })

    .done(function(data) {
      messages(data);
			console.log(data);
			if(data.success == "true"){
					$("#cartTotal").html(data.cartTotal);
					$("#numItems").html(data.numItems);
			}
    })
  });
});
</script>

<script>
$(document).ready(function() {

// 	$(document).on('touchend click', '.add', function(e) {
// 	e.stopImmediatePropagation();
// 	e.preventDefault();
// 	var handled = false;
// 	if(e.type == 'touchend' && handled == false){
// 		if ($(this).prev().val() < 100) {
// 			$(this).prev().val(+$(this).prev().val() + 1);
// 		}
// 		return false;
// 	}else if(e.type == 'click' && handled == false){
// 		if ($(this).prev().val() < 16) {
// 			$(this).prev().val(+$(this).prev().val() + 1);
// 		}
// 		return false;
// 	}
// });
//
//
// $(document).on('touchend click', '.sub', function(e) {
// e.stopImmediatePropagation();
// e.preventDefault();
// var handled = false;
// if(e.type == 'touchend' && handled == false){
// 	if ($(this).next().val() > 0) {
// 		if ($(this).next().val() > 0) $(this).next().val(+$(this).next().val() - 1);
// 	}
// 	return false;
// }else if(e.type == 'click' && handled == false){
// 	if ($(this).next().val() > 0) {
// 		if ($(this).next().val() > 0) $(this).next().val(+$(this).next().val() - 1);
// 	}
// 	return false;
// }
// });
});
</script>
<!-- <script type="text/javascript" src="users/js/pagination/datatables.min.js"></script> -->

<?php require_once $abs_us_root.$us_url_root.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
