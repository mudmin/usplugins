<?php if(count(get_included_files()) ==1) die();
global $user;
if(isset($user) && $user->isLoggedIn() && $user->data()->profile_pic != ''){ ?>
<script type="text/javascript">
  $(".img-thumbnail, .profile-replacer").attr("src", "<?=$us_url_root?>usersc/plugins/profile_pic/files/<?=$user->data()->profile_pic?>");
</script>
<?php } ?>
