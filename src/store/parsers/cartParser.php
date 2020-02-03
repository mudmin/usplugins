<?php
//NOTE: This also serves as the reference file for how to do One Click Edit with UserSpice. See comments below.
  require_once '../../../../users/init.php';
  $db = DB::getInstance();

if(!isset($_SESSION['orderno']) || $_SESSION['orderno']==''){
  die("Invalid order");
}
$orderQ = $db->query("SELECT * FROM store_orders WHERE id = ?",[$_SESSION['orderno']]);
$orderC = $orderQ->count();
if($orderC < 1){
  die("Invalid Order");
}else{
  $order = $orderQ->first();
  if($order->paid > 0){
    die("This order is already complete");
  }
}

$msg = [];
$id = Input::get('id');
$qty = Input::get('qty');
$numItems = 0;
$cartTotal = 0;

$itemQ = $db->query("SELECT * FROM store_order_items WHERE orderno = ? AND item = ?",[$_SESSION['orderno'],$id]);
$itemC = $itemQ->count();
if($itemC < 1){
  $lookupQ = $db->query("SELECT * FROM store_inventory WHERE id = ?",[$id]);

  $lookupC = $lookupQ->count();
  if($lookupC < 1){
      $msg['success'] = "false";
      $msg['msg'] = "Item not found!";
      echo json_encode($msg);
      die();
  }else{
    $lookup = $lookupQ->first();
  }
  		$tot = $lookup->price * $qty;
  		$fields = array(
  			'price_each'=>$lookup->price,
  			'orderno'=>$_SESSION['orderno'],
  			'item'=>$id,
  			'qty'=>$qty,
  			'price_tot'=>$tot,
  		);
  		$db->insert('store_order_items',$fields);
        $msg['success'] = "true";
        $msg['msg'] = "Added successfully!";

}else{
  $item = $itemQ->first();
  $tot = $item->price_each * $qty;
					$fields = array(
						'qty'=>$qty,
						'price_tot'=>$tot,
					);
					$db->update('store_order_items',$item->id,$fields);
          $msg['success'] = "true";
          $msg['msg'] = "Added successfully!";
}
$cart = $db->query("SELECT * FROM store_order_items WHERE qty > 0 AND orderno = ?",[$_SESSION['orderno']])->results();

foreach($cart as $c){
  $numItems = $numItems + $c->qty;
  $cartTotal = $cartTotal + $c->price_tot;
}

if($numItems == 1){
  $numItems = "1 item.";
}else{
  $numItems = $numItems." items.";
}
$msg['numItems'] = $numItems;
$msg['cartTotal'] = money($cartTotal);

echo json_encode($msg);
