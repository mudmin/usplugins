<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
$e = $db->query("SELECT * FROM plg_userinfo")->first();
global $user;

if($e->fname == 1){ ?>
<script type="text/javascript">
  $("#fname").hide();
</script>
<?php
}

if($e->lname == 1){?>
<script type="text/javascript">
  $("#lname").hide();
</script>
<?php
}

if($e->lname == 1 && $e->fname == 1){?>
<script type="text/javascript">
  $("#slash").hide();
</script>
<?php
}

if($e->uname == 2){?>
<script type="text/javascript">
  $("#username").html("<?=$user->data()->email?>");
</script>
<?php
}
