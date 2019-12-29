<?php if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted
$change = Input::get('change');
global $user;
?>
<style media="screen">
  .img-thumbnail{
    max-width:240px !important;
    max-height:310px !important;
  }
</style>
<div class="form-group">
<button type="button" onclick="window.location.href = 'account.php?change=pic';" name="button" class="btn btn-primary btn-block">Update Photo</button>
</div>
<?php if($user->data()->profile_pic != ''){ ?>
<script type="text/javascript">
  $(".img-thumbnail, .profile-replacer").attr("src", "<?=$us_url_root?>usersc/plugins/profile_pic/files/<?=$user->data()->profile_pic?>");
</script>
<?php } ?>
