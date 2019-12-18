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

if(!isset($_SESSION['orderno']) || !is_numeric($_SESSION['orderno'])){
	$string = uniqid(15);
	$db->insert('store_orders',['code'=>$string]);
	$_SESSION['orderno'] = $db->lastId();
}
$order = $_SESSION['orderno'];

$itemsQ = $db->query("SELECT * FROM store_order_items WHERE orderno = ?",array($order));
$itemsC = $itemsQ->count();
$qty = 0;
$tot = 0;
if($itemsC > 0){
	$items = $itemsQ->results();

	foreach($items as $i){
		$qty = $qty + $i->qty;
		$tot = $tot + $i->price_tot;
	}
}
$it = Input::get('item');
$itemQ = $db->query("SELECT * FROM store_inventory WHERE id = ?",[$it]);
$itemC = $itemQ->count();
if($itemC < 1){Redirect::to('store.php?err=Item+not+found');}
$item = $itemQ->first();
if($item->disabled == 1){Redirect::to('store.php?err=This+item+is+no+longer+available');}
?>


<div id="page-wrapper">
	<div class="container-fluid">
		<!-- Page Heading -->
<?php if($order > 0 && $itemsC > 0){ //items found?>
		<div class="row">
			<div class="col-12">
				<h1 class="text-center">Checkout</h1>
			</div>
		</div>
			<div class="col-12 text-center">
				<h3>Current Order:
				<?php
				echo money($tot) ." total and ";
				if($qty == 1){
					echo "1 item. ";
				}else{
					echo $qty . " items. ";
				}
			}
				?>
			</h3>
				<h4 align="center"><a href="store.php"><i class="fa fa-fw fa-heart"></i> Order More Items</a></h4>
				<br>
				<h2 align="center"><?=$item->item?> <?php if($item->qoh < 1 && $item->digital == 0 && $settings->ignore_inventory == 0){echo "(Sold Out)";}?></h2>
				<p align="center"><?=$item->description?></p>
			</div> <!-- /.col -->
		</div> <!-- /.row -->

		<div class="row">
			<?php
			$photosQ = $db->query("SELECT * FROM store_inventory_photos WHERE item = ? AND disabled = 0",[$it]);
			$photosC = $photosQ->count();
			if($photosC > 0){
				$photos = $photosQ->results();
				foreach($photos as $p){?>
					<div class="col-6 col-sm-4 col-md-3">
						<a href="<?=$us_url_root?>usersc/plugins/store/img/<?=$p->photo?>"><img src="<?=$us_url_root?>usersc/plugins/store/img/<?=$p->photo?>" alt="" height="150"></a>
					</div>
				<?php }
			} ?>
		</div>
	</div> <!-- /.container -->
</div> <!-- /.wrapper -->


<?php //require_once $abs_us_root.$us_url_root.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

<?php require_once $abs_us_root.$us_url_root.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
