<?php
require_once '../../../../users/init.php';
require_once $abs_us_root.$us_url_root.'users/includes/template/prep.php';
if(!isset($last)){
	$last = $db->query("SELECT * FROM us_gdpr ORDER BY id DESC LIMIT 1")->first();
}
?>
<div id="page-wrapper">
	<div class="container">
			<h1 align="center"><?php echo $settings->site_name;?></h1>
    <?php
    echo html_entity_decode($last->detail);
?>
	</div>
</div>

<!-- Place any per-page javascript here -->


<?php require_once $abs_us_root . $us_url_root . 'users/includes/html_footer.php'; ?>
