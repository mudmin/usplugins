<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
$e = $db->query("SELECT * FROM plg_userinfo")->first();
global $user;
if (!isset($GLOBALS['userspice_nonce'])) {
    $GLOBALS['userspice_nonce'] = base64_encode(random_bytes(16));
}

if($e->fname == 1){ ?>
<script type="text/javascript" nonce="<?= htmlspecialchars($GLOBALS['userspice_nonce'] ?? '') ?>">
  $("#fname").hide();
</script>
<?php
}

if($e->lname == 1){?>
<script type="text/javascript" nonce="<?= htmlspecialchars($GLOBALS['userspice_nonce'] ?? '') ?>">
  $("#lname").hide();
</script>
<?php
}

if($e->lname == 1 && $e->fname == 1){?>
<script type="text/javascript" nonce="<?= htmlspecialchars($GLOBALS['userspice_nonce'] ?? '') ?>">
  $("#slash").hide();
</script>
<?php
}

if($e->uname == 2){?>
<script type="text/javascript" nonce="<?= htmlspecialchars($GLOBALS['userspice_nonce'] ?? '') ?>">
  $("#username").html("<?=$user->data()->email?>");
</script>
<?php
}
