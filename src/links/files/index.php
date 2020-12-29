<?php
/*
UserSpice 5
An Open Source PHP User Management System
by the UserSpice Team at http://UserSpice.com

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
require_once '../users/init.php';
require_once $abs_us_root.$us_url_root.'users/includes/template/prep.php';
if(!pluginActive("links",true)){
	die("disabled");
}
$link = "";
if(is_array($_GET)){
	foreach($_GET as $k=>$v){
		$link = $k;
		break;
	}
}
$link = strtolower(Input::sanitize($link));
$checkQ = $db->query("SELECT * FROM plg_links WHERE link_name = ?",[$link]);
$checkC = $checkQ->count();

?>

		<div class="row">
			<div class="col-sm-12">
				<?php if($link == "" || $checkC < 1){
					echo "<h3 align='center'>We are sorry, but that link does not exist.</h3>";
				}else{
					$check = $checkQ->first();

					if($check->logged_in == 1 && (!isset($user) || !$user->isLoggedIn())){
						$db->insert("plg_links_clicks",['link'=>$check->id,'user'=>0,'ip'=>ipCheck()]);
						Redirect::to($us_url_root."users/login.php?err=You must be logged in to use this link");
					}else{
						$db->insert("plg_links_clicks",['link'=>$check->id,'user'=>$user->data()->id,'ip'=>ipCheck()]);
						$db->update("plg_links",$check->id,['clicks'=>$check->clicks+1]);
						Redirect::to($check->link);
					}
				}
				?>
			</div>
		</div>



<?php require_once $abs_us_root . $us_url_root . 'users/includes/html_footer.php'; ?>
