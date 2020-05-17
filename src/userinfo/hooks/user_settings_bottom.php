<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
$e = $db->query("SELECT * FROM plg_userinfo")->first();
global $user;

if($e->fname == 1){ ?>
<script type="text/javascript">
  $("#fname-group").hide();
</script>
<?php
}

if($e->lname == 1){?>
<script type="text/javascript">
  $("#lname-group").hide();
</script>
<?php
}

if($e->uname == 1){?>
<script type="text/javascript">
  $("#email-group").hide();
  $("#confemail-group").hide();
</script>
<?php
}

if($e->uname == 2){?>
<script type="text/javascript">
  $("#username-group").hide();
</script>
<?php
}
