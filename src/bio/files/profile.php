<?php
// This is a user-facing page

require_once '../users/init.php';
if (!securePage($_SERVER['PHP_SELF'])){die();}
require_once $abs_us_root.$us_url_root.'users/includes/template/prep.php';
$hooks =  getMyHooks();
includeHook($hooks,'pre');

if($user->isLoggedIn()) { $thisUserID = $user->data()->id;} else { $thisUserID = 0; }
if(!isset($_GET['id'])){
	$userID = $user->data()->id;
}else{
	$userID = Input::get('id');
}

if(isset($userID))
	{
	$userQ = $db->query("SELECT * FROM profiles LEFT JOIN users ON user_id = users.id WHERE user_id = ?",array($userID));
	$thatUser = $userQ->first();

	if($thisUserID == $userID)
		{
		$editbio = ' <small><a href="edit_profile.php">Edit Bio</a></small>';
		}
	else
		{
		$editbio = '';
		}

	$ususername = ucfirst($thatUser->username)."'s Profile";
	$usbio = html_entity_decode($thatUser->bio);
	}
else
	{
	$ususername = '404';
	$usbio = 'User not found';
	$useravatar = '';
	$editbio = ' <small><a href="/">Go to the homepage</a></small>';
	}
?>
   <div id="page-wrapper">

		 <div class="container">
				<!-- Main jumbotron for a primary marketing message or call to action -->
				<div class="well">
					<div class="row">
						<div class="col-xs-12 col-md-2">
							<p>
							<?php if(pluginActive('profile_pic',true) && $thatUser->profile_pic != ''){ ?>
								<img src="<?=$us_url_root?>usersc/plugins/profile_pic/files/<?=$thatUser->profile_pic?>" class="img-thumbnail">
							<?php }else{
								$grav = get_gravatar(strtolower(trim($thatUser->email)));
								$useravatar = '<img src="'.$grav.'" class="img-thumbnail" alt="'.$ususername.'">';
								echo $useravatar;
							} ?>
							</p>
						</div>
						<div class="col-xs-12 col-md-10">
						<h1><?php echo $ususername;?></h1>
							<h2><?php echo $usbio.$editbio;?></h2>

					</div>
					</div>
				</div>

										<a class="btn btn-success" href="view_all_users.php" role="button">All Users</a>


    </div> <!-- /container -->

</div> <!-- /#page-wrapper -->

<?php require_once $abs_us_root.$us_url_root.'usersc/templates/'.$settings->template.'/container_close.php'; //custom template container ?>

<!-- footers -->
<?php require_once $abs_us_root.$us_url_root.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

<!-- Place any per-page javascript here -->

<?php   if($settings->recaptcha == 1){ ?>
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
  <script>
  function submitForm() {
	document.getElementById("login-form").submit();
  }
  </script>
<?php } ?>
<?php require_once $abs_us_root.$us_url_root.'usersc/templates/'.$settings->template.'/footer.php'; //custom template footer?>
