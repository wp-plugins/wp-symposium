<?php
/*  Copyright 2010,2011  Simon Goodchild  (info@wpsymposium.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


include_once('../../../../wp-config.php');
include_once('../../../../wp-includes/wp-db.php');
include_once('../symposium_functions.php');
	

$action = $_POST['action'].$_GET['action'];

if ($action == "symposium_test_ajax") {

	$value = $_POST['postID'];	
	echo $value*100;
	exit;
}

if ($action == "symposium_motd") {

	global $wpdb;

	// Update motd flag
	$sql = "UPDATE ".$wpdb->prefix."symposium_config SET motd = 'on'";
	$wpdb->query($sql); 
	
	// Send activation update to WPS
	$url = get_bloginfo('url');

	$localhost = false;
	if (strpos($url, '127.0.0.1') != FALSE) { $localhost = true; }
	if (strpos($url, 'localhost') != FALSE) { $localhost = true; }

	if (!$localhost) {
	
		$admin_email = get_bloginfo('admin_email');
		if ($_POST['optin'] == 'false') { $admin_email = ''; }
		$version = get_option("symposium_version");
		
		$goto = "http://www.wpsymposium.com/wp-content/plugins/wp-symposium/ajax/symposium_ajax_functions.php";
		
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		$headers .= 'From: noreply@wpsymposium.com';
	
		$report = $goto."?action=symposium_activationlog&url=".$url."&admin_email=".$admin_email."&version=".$version;
		
		header('Location:'.$report);
		
	}
	
	exit;	
}

if ($action == "symposium_activationlog") {

	global $wpdb;

	$url = $_GET['url'];

	$admin_email = $_GET['admin_email'];
	$version = $_GET['version'];
	
	$sql = "SELECT id from ".$wpdb->prefix."symposium_activations WHERE url = '".$url."'";
	$id = $wpdb->get_var($sql);
	
	if ($id) { 

		$wpdb->update($wpdb->prefix.'symposium_activations',
			array(	'url'=>$url, 
					'datetime'=>date("Y-m-d H:i:s"), 
					'email'=>$admin_email,
					'version'=>$version
				 ),
			array('id'=>$id)
			);
			
	} else {
		
		$wpdb->query( $wpdb->prepare( "
			INSERT INTO ".$wpdb->prefix."symposium_activations
			( 	url,
				datetime, 
				version,
				email
			)
			VALUES ( %s, %s, %s, %s )", 
	        array(
	        	$url, 
				date("Y-m-d H:i:s"), 
				$version,
				$admin_email
	        	) 
	        ) );

	}
	
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	$headers .= 'From: noreply@wpsymposium.com';
	mail('simon.goodchild@mac.com', 'WPS Activation', $wpdb->last_query, $headers);
	
	exit;
}


?>
