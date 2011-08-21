<?php
	include_once('../../../../wp-config.php');
	global $wpdb;
	
	$sql = "SELECT img_db, img_path FROM ".$wpdb->prefix."symposium_config";
	$img_db = $wpdb->get_var($sql);	
	
	$sql = "SELECT img_upload FROM ".$wpdb->prefix."symposium_config";
	$avatar = $wpdb->get_var($sql);	

	header("Content-type: image/jpeg");
	echo stripslashes($avatar);
		
	exit;
	
?>