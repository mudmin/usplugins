<?php if(count(get_included_files()) ==1) die();
if (!isset($GLOBALS['userspice_nonce'])) {
    $GLOBALS['userspice_nonce'] = base64_encode(random_bytes(16));
}
global $user;
if(isset($user) && $user->isLoggedIn() && $user->data()->profile_pic != ''){ ?>
<script type="text/javascript" nonce="<?= htmlspecialchars($GLOBALS['userspice_nonce'] ?? '') ?>">
  $(".img-thumbnail, .profile-replacer").attr("src", "<?=$us_url_root?>usersc/plugins/profile_pic/files/<?=$user->data()->profile_pic?>");
</script>
<?php } ?>
