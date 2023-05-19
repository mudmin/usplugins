<?php
require '../../../../users/init.php';
require_once $abs_us_root . $us_url_root . 'users/includes/template/prep.php';
if (!securePage($_SERVER['PHP_SELF'])) {
	die();
}
if (!pluginActive("store", true)) {
	die();
}
?>
<div id="page-wrapper">
	<div class="container-fluid">
		<!-- Page Heading -->
		<div class="row">
			<div class="col-sm-12">
				<h1 class="page-header">
					Documentation
				</h1>
				<strong>Intro</strong>
				<p>I hope you enjoy this store plugin. <strong>The most important thing</strong> to realize is that this plugin is
					not meant to be your "dream store" right out of the box. Although the store is functional, it is made to be a jumping off point
					to design your own store. I've come to the realization that no matter what I design, you will want something different and I am okay with that.</p>
				<strong>Important Concepts</strong>
				<p>• You will need to install the 'Payments' plugin if you want people to be able to pay at checkout. This plugin is separate because it
					is useful well beyond the store and updates will benefit everyone. You can however play with the back end of the store without installing this plugin. The checkout page will not work without it.</p>
				<p>• You should configure the few settings that are in the Important Settings section of the control panel.</p>
				<p>• Files in this plugin are different than in most plugins. While the control panel itself is locked to the master account, all of the
					individual settings pages can be assigned to any permission level (just like any "normal" UserSpice page). They are located in the "admin" folder
					of the plugin. If you would like to modify these, it is best to make a copy of this file and edit your new file.</p>
				<p>• The public files are located in the "public" folder of the plugin. I fully expect that you will move these files to a better location
					than that deep nested folder inside the plugin. When you do, simply update the path to init.php and update your settings in the pages panel
					just like any other UserSpice page. You can leave them where they're at for testing purposes though. <strong>Important:</strong> It is ideal that
					all the files in the public folder remain in the same folder as each other when you move them around. Don't split up the family! They rely on each other.
					Also, when you move these files, make sure you update your important settings.</p>
				<p>• Because it is expected that you will move these files around, I didn't create menus for the admin (other than the cpanel) or the front
					store. Create whatever menus you want in the code itself or the Settings->Navigation in the UserSpice Dashboard.</p>
				<p>• You need to create inventory categories before you can add items to your inventory.</p>
				<p>• You must create an item or inventory category before you add photo(s).</p>
				<p>• While I can (and will) add features to the store itself as I clean up the code and user experience, I can't offer a ton of support or
					promise to build out major features. This is a big undertaking that I did for free as a convenience to my friends at UserSpice and my time
					is going to be limited on further development after 1.0 beyond bug fixes.</p>
				<p>• The database contains places to put additional information that you can use however you want. There are still some features that are
					not fully built out. I'm doing my best!</p>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-12">

			</div>
		</div>
	</div> <!-- /.container -->
</div> <!-- /.wrapper -->

<?php require_once $abs_us_root . $us_url_root . 'users/includes/html_footer.php'; // currently just the closing /body and /html 
?>