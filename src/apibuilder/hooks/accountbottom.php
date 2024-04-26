<?php
global $user;
global $ip;
$apisettings = $db->query("SELECT * FROM plg_api_settings")->first();
if (($apisettings->api_auth_type == 4 || $apisettings->api_auth_type == 5) && $apisettings->key_on_acct == 1) {
  if (!empty($_POST['genNewkey'])) {
    $code = strtoupper(randomstring(12) . uniqid());
    $code = substr(chunk_split($code, 5, '-'), 0, -1);
    $db->update('users', $user->data()->id, ['apibld_key' => $code]);
    Redirect::to('account.php?err=Key+Generated');
  }

  if (!empty($_POST['updateKeyIP'])) {
    $db->update('users', $user->data()->id, ['apibld_ip' => Input::get('ip')]);
    Redirect::to('account.php?err=IP+Updated');
  }

  //  
?>

  <form class="" action="" method="post">
    Your API Key: <?= $user->data()->apibld_key ?>
    <input type="submit" name="genNewkey" value="Generate New Key">
  </form>
  <?php if ($apisettings->api_auth_type == 5) { //  
  ?>
    <form class="" action="" method="post">
      Your Key Is Locked To: <input type="text" name="ip" value="<?= $user->data()->apibld_ip ?>">
      <input type="submit" name="updateKeyIP" value="Update IP Hostname"><br>
      In order to use the API, we need to know what IP address your requests are coming from. If you have a Static IP, you can store that above. If you have an IP address that changes, we recommend you use a free service like duckdns.org and put your url in the box above in the format of whatever.duckdns.org.
    </form>
<?php
  }
}
?>