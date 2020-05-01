<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
$e = $db->query("SELECT * FROM plg_userinfo")->first();

$string = randomstring(10);

if($e->fname == 1){ ?>
<script type="text/javascript">
  $("#fname").hide();
  $("#fname-label").hide();
  $("#fname").val("<?=$string?>");
</script>
<?php
}

if($e->lname == 1){?>
<script type="text/javascript">
  $("#lname").hide();
  $("#lname-label").hide();
  $("#lname").val("<?=$string?>");
</script>
<?php
}

if($e->uname == 1){?>
<script type="text/javascript">
  $("#email").hide();
  $("#email-label").hide();
  $("#email").val("<?=$string?>@<?=$e->domain?>");
</script>
<?php
}

if($e->uname == 2){?>
<script type="text/javascript">
  $("#username").hide();
  $("#username-label").hide();
  $("#username").val("<?=$string?>");
</script>
<?php
}
