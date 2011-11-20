<?php
	include_once('../../../../wp-config.php');
	global $wpdb;
		
	$avatar = WPS_IMG_UPLOAD;

	header("Content-type: image/jpeg");
	echo stripslashes($avatar);
		
	exit;
	
?>