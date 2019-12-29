<?php
// This is a user-facing page

require_once '../users/init.php';
if (!securePage($_SERVER['PHP_SELF'])){die();}
require_once $abs_us_root.$us_url_root.'users/includes/template/prep.php';
$hooks =  getMyHooks();
includeHook($hooks,'pre');
?>
<?php
$userQ = $db->query("SELECT * FROM users LEFT JOIN profiles ON users.id = user_id ");
// group active, inactive, on naughty step
$users = $userQ->results();
?>
<div id="page-wrapper">

	<div class="container">

		<!-- Page Heading -->
		<div class="row">

			<div class="col-xs-12 col-md-6">
				<h1 >View All Users</h1>
			</div>
		</div>

		<div class="row">
			<div class="col-md-12">
				<div class="allutable table-responsive">
					<table class='table table-hover table-list-search'>
						<thead>
							<tr>
								<th><div class="alluinfo">&nbsp;</div></th>
								<th>Username</th>
							</tr>
						</thead>
						<tbody>
							<?php
							//Cycle through users
							foreach ($users as $v1) {

								$ususername = ucfirst($v1->username);
								$ususerbio = ucfirst($v1->bio);

								?>

								<tr>
									<td>
										<?php
										if(pluginActive('profile_pic') && $v1->profile_pic != ''){ ?>
										<a href="<?=$us_url_root?>users/profile.php?id=<?=$v1->id?>"><img src="<?=$us_url_root?>usersc/plugins/profile_pic/files/<?=$v1->profile_pic?>" class="img-responsive img-thumbnail"></a>
										<?php }else{
										$grav = get_gravatar(strtolower(trim($v1->email)));
										$useravatar = '<img src="'.$grav.'" class="img-responsive img-thumbnail" alt="'.$ususername.'">'; ?>
										<a href="<?=$us_url_root?>users/profile.php?id=<?=$v1->id?>"><?php echo $useravatar;?></a>
										<?php } ?>

									</td>

									<td>
										<h4><a href="<?=$us_url_root?>users/profile.php?id=<?=$v1->id?>"><?=$ususername?>  </a></h4>
										<p><?php //echo $ususerbio ?></p>
									</td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>

			</div>
		</div>

		<!-- /.row -->
	</div>
</div>


<!-- footers -->
<?php require_once $abs_us_root.$us_url_root.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>
