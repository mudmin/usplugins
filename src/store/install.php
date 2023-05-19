<?php
require_once("init.php");
//For security purposes, it is MANDATORY that this page be wrapped in the following
//if statement. This prevents remote execution of this code.
if (in_array($user->data()->id, $master_account)) {


  $db = DB::getInstance();
  include "plugin_info.php";

  $db->query("
 CREATE TABLE `store_categories` (
   `id` int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
   `cat` varchar(255) ,
   `disabled` int(1)  DEFAULT '0',
   `subcats` int(1)  DEFAULT '0',
   `photo` varchar(255)
 ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
 ");

  $db->query("
 CREATE TABLE `store_inventory` (
  `id` int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
   `category` int(11) ,
   `topcat` int(11) ,
   `item` varchar(255) ,
   `description` text ,
   `photo` varchar(255) ,
   `price` decimal(11,2) DEFAULT NULL,
   `cost` varchar(255) ,
   `inv_cont` int(1)  DEFAULT '0',
   `stock` int(11) ,
   `disabled` int(1)  DEFAULT '0'
 ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
 ");


  $db->query("
  CREATE TABLE `store_inventory_vars` (
   `id` int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `item` int(11) ,
    `description` text,
    `photo` varchar(255),
    `price` decimal(11,2) DEFAULT NULL,
    `cost` varchar(255) ,
    `inv_cont` int(1)  DEFAULT '0',
    `stock` int(11) ,
    `disabled` int(1)  DEFAULT '0'
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
  ");

  $db->query("
  CREATE TABLE `store_inventory_photos` (
   `id` int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `item` int(11) ,
    `var` int(11) ,
    `description` text ,
    `photo` varchar(255) ,
    `disabled` int(1)  DEFAULT '0'
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
  ");



  $db->query("
 CREATE TABLE `store_orders` (
   `id` int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
   `fullname` varchar(255) DEFAULT NULL,
   `code` varchar(20) ,
   `submitted` datetime ,
   `last_update` timestamp  DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
   `phone` varchar(255) ,
   `email` varchar(255) ,
	 `add1` varchar(255) ,
	 `add2` varchar(255) ,
	 `city` varchar(255) ,
	 `state` varchar(255) ,
	 `postal` varchar(255) ,
	 `status` varchar(255) ,
   `amt_paid` varchar(25) ,
   `paid` int(1)  DEFAULT '0',
   `reference` varchar(255) ,
   `pickup_date` varchar(255) ,
   `notes` text ,
   `archived` int(1)  DEFAULT '0',
   `order_type` varchar(255) ,
   `taken_by` varchar(255) ,
   `payment_method` varchar(255) ,
   `charge_id` varchar(255)
 ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
 ");

  $db->query("
 CREATE TABLE `store_order_items` (
  `id` int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
   `orderno` int(11) ,
   `item` int(11) ,
   `price_each` varchar(255) ,
   `price_tot` varchar(255) ,
   `qty` int(11)
 ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
 ");

  $db->query("
 CREATE TABLE `store_order_status` (
  `id` int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
   `status` varchar(255) ,
   `disabled` tinyint(1) default 0
 ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
 ");
  $check = $db->query("SELECT * FROM store_order_status")->count();
  if ($check < 1) {
    $opts = ['Order Placed', 'Order Shipped', 'Order Cancelled', 'Backordered', 'Disputed'];
    foreach ($opts as $o) {
      $db->insert('store_order_status', ['status' => $o]);
    }
  }

  $db->query("
 CREATE TABLE `store_payment_options` (
  `id` int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
   `opt` varchar(255) ,
   `def` tinyint(1) default 0 ,
   `disabled` tinyint(1) default 0
 ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
 ");
  $db->query("ALTER TABLE store_payment_options ADD COLUMN common varchar(255)");
  $check = $db->query("SELECT * FROM store_payment_options")->count();
  if ($check < 1) {
    $db->insert('store_payment_options', ['opt' => 'check', 'def' => 1, 'common' => 'Check']);
  }

  $adminPages = [
    'abandoned.php',
    'categories.php',
    'documentation.php',
    'edit_order.php',
    'inventory.php',
    'manage_inventory.php',
    'search_orders.php',
    'settings.php',
    'store_cart.php',
    'store_closed_msg.php',
    'store_order.php',
    'system_messages.php',
    'view_orders.php',
  ];
  foreach ($adminPages as $a) {
    $check = $db->query("SELECT * FROM pages WHERE page = ?", ['usersc/plugins/store/admin/' . $a])->count();

    if ($check < 1) {
      $db->insert('pages', ['page' => 'usersc/plugins/store/admin/' . $a, 'private' => 1]);
      $db->insert('permission_page_matches', ['permission_id' => 2, 'page_id' => $db->lastId()]);
    }
  }

  $publicPages = [
    'store_closed.php',
    'cart.php',
    'item.php',
    'store.php',
    'view_order.php',
  ];

  foreach ($publicPages as $a) {
    $check = $db->query("SELECT * FROM pages WHERE page = ?", ['usersc/plugins/store/public/' . $a])->count();

    if ($check < 1) {
      $db->insert('pages', ['page' => 'usersc/plugins/store/public/' . $a, 'private' => 1]);
      $newId = $db->lastId();
      $db->insert('permission_page_matches', ['permission_id' => 1, 'page_id' => $newId]);
      $db->insert('permission_page_matches', ['permission_id' => 2, 'page_id' => $newId]);
    }
  }

  $db->query("ALTER TABLE store_inventory ADD COLUMN qoh int(11) default 999999");
  $db->query("ALTER TABLE store_inventory ADD COLUMN digital tinyint(1) default 0");
  $db->query("ALTER TABLE store_categories ADD COLUMN is_subcat tinyint(1) default 0");
  $db->query("ALTER TABLE store_categories ADD COLUMN subcat_of int(11) default 0");

  $db->query("ALTER TABLE settings ADD COLUMN order_link varchar(255)");
  $db->query("ALTER TABLE settings ADD COLUMN open tinyint(1) default 0");
  $db->query("ALTER TABLE settings ADD COLUMN closed_msg text");
  $db->query("ALTER TABLE settings ADD COLUMN ignore_inventory tinyint(1) default 0");
  $db->query("ALTER TABLE settings ADD COLUMN email_msg text");
  $db->query("ALTER TABLE settings ADD COLUMN checkout_msg text");
  $db->query("ALTER TABLE settings ADD COLUMN header_msg text");
  $db->query("ALTER TABLE settings ADD COLUMN auto_close datetime");
  $db->update("settings", 1, ['auto_close' => '2030-12-31 00:00:00']);
  //all actions should be performed here.
  $check = $db->query("SELECT * FROM us_plugins WHERE plugin = ?", array($plugin_name))->count();
  if ($check > 0) {
    err($plugin_name . ' has already been installed!');
  } else {
    $fields = array(
      'plugin' => $plugin_name,
      'status' => 'installed',
    );
    $db->insert('us_plugins', $fields);
    if (!$db->error()) {
      err($plugin_name . ' installed');
      logger($user->data()->id, "USPlugins", $plugin_name . " installed");
    } else {
      err($plugin_name . ' was not installed');
      logger($user->data()->id, "USPlugins", "Failed to to install plugin, Error: " . $db->errorString());
    }
  }

  //do you want to inject your plugin in the middle of core UserSpice pages?
  $hooks = [];

  //The format is $hooks['userspicepage.php']['position'] = path to filename to include
  //Note you can include the same filename on multiple pages if that makes sense;
  //postion options are post,body,form,bottom
  //See documentation for more information
  // $hooks['login.php']['body'] = 'hooks/loginbody.php';
  // $hooks['login.php']['form'] = 'hooks/loginform.php';
  // $hooks['login.php']['bottom'] = 'hooks/loginbottom.php';
  // $hooks['login.php']['post'] = 'hooks/loginpost.php';
  registerHooks($hooks, $plugin_name);
} //do not perform actions outside of this statement
