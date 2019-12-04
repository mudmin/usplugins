<?php
require_once '../../../../users/init.php';
$fingerprint=Input::get('fingerprint');
if(!isset($_SESSION['fingerprint']) && $fingerprint!='') {
	$_SESSION['fingerprint']=$fingerprint;
}
if(!isset($_SESSION['fingerprint']) && $fingerprint=='') {
	$_SESSION['fingerprint']=NULL;
}
?>
