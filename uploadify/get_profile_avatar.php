<?php
	include_once('../../../../wp-config.php');
	global $wpdb;
	$uid = $_REQUEST['uid'];
	$sql = "SELECT profile_avatar FROM ".$wpdb->base_prefix."symposium_usermeta WHERE uid = ".$uid;
	$avatar = $wpdb->get_var($sql);	
	header("Content-type: image/jpeg");
	echo stripslashes($avatar);
?>