<?php
function echoCat($id, $showMain = true, $showInv = false)
{
	$db = DB::getInstance();
	$catQ = $db->query("SELECT * FROM store_categories WHERE id = ?", array($id));
	$catC = $catQ->count();
	if ($catC > 0) {
		$cat = $catQ->first();
		if ($cat->is_subcat > 0) {
			$q = $db->query("SELECT cat FROM store_categories WHERE id = ?", [$cat->subcat_of]);
			$c = $q->count();
			if ($c > 0 && $showMain == true) {
				$f = $q->first();
				echo $f->cat . "-" . $cat->cat;
			} else {
				echo $cat->cat;
			}
		} else {
			echo $cat->cat;
		}
		if ($showInv == true) {
			$inv = $db->query("SELECT id FROM store_inventory WHERE category = ? AND disabled = 0 AND qoh > 0", [$cat->id])->count();
			echo "($inv)";
		}
	} else {
		echo "unknown";
	}
}

function getTopCat($id)
{
	global $db;
	$catQ = $db->query("SELECT * FROM store_categories WHERE id = ?", array($id));
	$catC = $catQ->count();
	if ($catC > 0) {
		$cat = $catQ->first();
		if ($cat->is_subcat > 0) {
			$q = $db->query("SELECT id FROM store_categories WHERE id = ?", [$cat->subcat_of]);
			$c = $q->count();
			if ($c > 0) {
				$f = $q->first();
				return $f->id;
			} else {
				return $id;
			}
		} else {
			return $id;
		}
	} else {
		return $id;
	}
}

function fetchStoreSubCats($id)
{
	global $db;
	$check = $db->query("SELECT id FROM store_categories WHERE disabled = 0 AND is_subcat > 0")->count();
	if ($check < 1) {
		return false;
	}
	$catsQ = $db->query("SELECT * FROM store_categories WHERE subcat_of = ? ORDER BY cat", [$id]);
	$catsC = $catsQ->count();
	if ($catsC < 1) {
		echo "<br>";
	} else {
		$cats = $catsQ->results();
?>
		<p align="center">
			<?php
			$i = 1;
			foreach ($cats as $c) { ?>
				<?php
				if ($c->id != $id) {
				?>
					<a href="store.php?category=<?= $c->id ?>">
						<?php
						echoCat($c->id, $showMain = false, $showInv = true);
						?>
					</a>
		<?php
					if ($i < count($cats)) {
						echo "   -   ";
					}
					$i++;
				}
			}
		} ?>
		</p>
	<?php
}

function fetchStoreCats()
{
	global $db;
	$cats = new stdClass();
	$counter = 0;
	$primary = $db->query("SELECT * FROM store_categories WHERE is_subcat = 0 ORDER BY cat")->results();
	foreach ($primary as $p) {
		$counter++;
		$cats->$counter = $p;
		$subs = $db->query("SELECT * FROM store_categories WHERE subcat_of = ? ORDER BY cat", [$p->id])->results();
		foreach ($subs as $s) {
			$counter++;
			$s->cat = $p->cat . " - " . $s->cat;
			$cats->$counter = $s;
		}
	}
	return $cats;
}

function echoItem($id)
{
	$db = DB::getInstance();
	$catQ = $db->query("SELECT item FROM store_inventory WHERE id = ?", array($id));
	$catC = $catQ->count();
	if ($catC > 0) {
		$cat = $catQ->first();
		echo $cat->item;
	} else {
		echo "unknown";
	}
}

function parsePickup($id)
{
	$db = DB::getInstance();
	$dateQ = $db->query("SELECT * FROM store_pickup_options WHERE id = ?", array($id));
	$dateC = $dateQ->count();
	if ($dateC > 0) {
		$date = $dateQ->first();
		$format = date("D, M d", strtotime($date->date));
		echo $format;
		echo " (" . $date->time . ")";
	}
}

function itemPrice($id)
{
	$db = DB::getInstance();
	$itemQ = $db->query("SELECT price FROM store_inventory WHERE id = ?", array($id));
	$itemC = $itemQ->count();
	if ($itemC > 0) {
		$item = $itemQ->first();
		$price = $item->price;
	} else {
		$price = "x";
	}
	return $price;
}

function validDate($date, $format = 'Y-m-d')
{
	$d = DateTime::createFromFormat($format, $date);
	// The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.
	return $d && $d->format($format) === $date;
}

function checkInventory($order)
{
	global $db, $settings;
	$data = [
		'success' => true,
		'fails'  => [],
	];
	if ($settings->ignore_inventory == 1) {
		return $data;
	}
	$items = $db->query("SELECT * FROM store_order_items WHERE orderno = ?", [$order])->results();
	foreach ($items as $i) {
		$checkQ = $db->query("SELECT * FROM store_inventory WHERE id = ?", [$i->item]);
		$checkC = $checkQ->count();
		if ($checkC > 0) {
			$check = $checkQ->first();
			if ($check->digital == 1) {
				continue;
			}
			if ($check->qoh < $i->qty) {
				$data['success'] = false;
				$data['fails'][$i->id] = "We only have $check->qoh $check->item in stock.";
			}
			if ($check->disabled == 1) {
				$data['success'] = false;
				$data['fails'][$i->id] = "$check->item is unavailable at this time.";
			}
		} else {
			$data['success'] = false;
			$data['fails'][$i->id] = "$i->item not found. Please remove that item number from your cart.";
		}
	}
	return $data;
}

function processInventory($order)
{
	global $db, $settings;

	if ($settings->ignore_inventory == 1) {
		return true;
	}
	$items = $db->query("SELECT * FROM store_order_items WHERE orderno = ?", [$order])->results();
	foreach ($items as $i) {
		$check = $db->query("SELECT * FROM store_inventory WHERE id = ?", [$i->item])->first();
		if ($check->digital != 1) {
			$db->update('store_inventory', $check->id, ['qoh' => $check->qoh - $i->qty]);
		}
	}
	return true;
}
