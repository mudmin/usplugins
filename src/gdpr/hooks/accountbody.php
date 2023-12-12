<?php
$last = $db->query("SELECT `delete`, `btn_delete` FROM us_gdpr ORDER BY id DESC LIMIT 1")->first();

if($last->delete == 1){ ?>
<p>
    <a href="<?=$us_url_root?>usersc/plugins/gdpr/files/confirm_delete.php" class="account-delete-button btn btn-outline-danger btn-block mt-3"><?=$last->btn_delete?></a>
</p>
<?php }
// Redirect::to($us_url_root.'usersc/plugins/gdpr/files/confirm_delete.php');