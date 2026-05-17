<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
$e = $db->query("SELECT * FROM plg_userinfo")->first();
if (!isset($GLOBALS['userspice_nonce'])) {
    $GLOBALS['userspice_nonce'] = base64_encode(random_bytes(16));
}

$string = randomstring(10);

if($e->fname == 1){ ?>
<script type="text/javascript" nonce="<?= htmlspecialchars($GLOBALS['userspice_nonce'] ?? '') ?>">
  $("#fname").hide();
  $("#fname-label").hide();
  $("#fname").val("<?=$string?>");
</script>
<?php
}

if($e->lname == 1){?>
<script type="text/javascript" nonce="<?= htmlspecialchars($GLOBALS['userspice_nonce'] ?? '') ?>">
  $("#lname").hide();
  $("#lname-label").hide();
  $("#lname").val("<?=$string?>");
</script>
<?php
}

if($e->uname == 1){?>
<script type="text/javascript" nonce="<?= htmlspecialchars($GLOBALS['userspice_nonce'] ?? '') ?>">
  $("#email").hide();
  $("#email-label").hide();
  $("#email").val("<?=$string?>@<?=$e->domain?>");
</script>
<?php
}

if($e->uname == 2){?>
<script type="text/javascript" nonce="<?= htmlspecialchars($GLOBALS['userspice_nonce'] ?? '') ?>">
  $("#username").hide();
  $("#username-label").hide();
  $("#username").val("<?=$string?>");
</script>
<?php
}
