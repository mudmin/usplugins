<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
$e = $db->query("SELECT * FROM plg_userinfo")->first();
global $user;
if (!isset($GLOBALS['userspice_nonce'])) {
    $GLOBALS['userspice_nonce'] = base64_encode(random_bytes(16));
}
$usFormUpdate = $user->data()->id;

$e = $db->query("SELECT * FROM plg_userinfo")->first();
if(pluginActive("forms",true)){
$options['update'] = $usFormUpdate;
$plgform = displayForm('users',['nosubmit'=>true,'noclose'=>1,'update'=>$usFormUpdate]);
}

if($e->fname == 1){ ?>
<script type="text/javascript" nonce="<?= htmlspecialchars($GLOBALS['userspice_nonce'] ?? '') ?>">
  $("#fname-group").hide();
</script>
<?php
}

if($e->lname == 1){?>
<script type="text/javascript" nonce="<?= htmlspecialchars($GLOBALS['userspice_nonce'] ?? '') ?>">
  $("#lname-group").hide();
</script>
<?php
}

if($e->uname == 1){?>
<script type="text/javascript" nonce="<?= htmlspecialchars($GLOBALS['userspice_nonce'] ?? '') ?>">
  $("#email-group").hide();
  $("#confemail-group").hide();
</script>
<?php
}

if($e->uname == 2){?>
<script type="text/javascript" nonce="<?= htmlspecialchars($GLOBALS['userspice_nonce'] ?? '') ?>">
  $("#username-group").hide();
</script>
<?php
}
