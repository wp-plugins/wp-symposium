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
	

global $wpdb;

$wpdb->show_errors();


// Authenticate
// eg: WPROOT_URL/wp-content/plugins/wp-symposium/ajax/symposium_api.php?action=authenticate&username=joe&password=xyz123
if ($_GET['action'] == 'authenticate') {

	global $wpdb,$wp_error;

	$username = $_GET['username'];
	$password = $_GET['password'];
	
	$user = wp_authenticate($username, $password);
    if(is_wp_error($user)) {
        echo "FAIL";
    } else {
		echo "SUCCESS:".$user->ID;   	
    }

}

// Get my profile information

// Get wall
// eg: WPROOT_URL/wp-content/plugins/wp-symposium/ajax/symposium_api.php?action=wall&uid=2
if ($_GET['action'] == 'wall') {
	
	$version = $_GET['version'];
	if ($version == '') { $version = 'my'; }
	
	$uid1 = $_GET['uid'];
	if ($uid1 == '') { $uid = 2; }

	$return_arr = array();	
	
	if ($version == "all") {
		$sql = "SELECT c.*, u.display_name, u2.display_name AS subject_name FROM ".$wpdb->prefix."symposium_comments c LEFT JOIN ".$wpdb->prefix."users u ON c.author_uid = u.ID LEFT JOIN ".$wpdb->prefix."users u2 ON c.subject_uid = u2.ID WHERE c.comment_parent = 0 ORDER BY c.comment_timestamp DESC LIMIT 0,100";							
	}

	if ($version == "friends") {
		$sql = "SELECT c.*, u.display_name, u2.display_name AS subject_name FROM ".$wpdb->prefix."symposium_comments c LEFT JOIN ".$wpdb->prefix."users u ON c.author_uid = u.ID LEFT JOIN ".$wpdb->prefix."users u2 ON c.subject_uid = u2.ID WHERE ( (c.subject_uid = ".$uid1.") OR (c.author_uid = ".$uid1.") OR ( c.author_uid IN (SELECT friend_to FROM ".$wpdb->prefix."symposium_friends WHERE friend_from = ".$uid1.")) OR ( c.subject_uid IN (SELECT friend_to FROM ".$wpdb->prefix."symposium_friends WHERE friend_from = ".$uid1.")) ) AND c.comment_parent = 0 ORDER BY c.comment_timestamp DESC LIMIT 0,100";							
	}

	if ($version == "my") {
		$sql = "SELECT c.*, u.display_name, u2.display_name AS subject_name FROM ".$wpdb->prefix."symposium_comments c LEFT JOIN ".$wpdb->prefix."users u ON c.author_uid = u.ID LEFT JOIN ".$wpdb->prefix."users u2 ON c.subject_uid = u2.ID WHERE (c.subject_uid = ".$uid1.") AND c.comment_parent = 0 ORDER BY c.comment_timestamp DESC LIMIT 0,100";							
	}

	$list = $wpdb->get_results($sql);	
	
	if ($list) {
		foreach ($list as $item) {
			
			$row_array['author_name'] = $item->author_name;
			$row_array['subject_name'] = $item->subject_name;
			$row_array['cid'] = $item->cid;
			$row_array['subject_uid'] = $item->subject_uid;
			$row_array['author_uid'] = $item->author_uid;
			$row_array['comment_parent'] = $item->comment_parent;
			$row_array['comment_timestamp'] = $item->comment_timestamp;
			$row_array['comment'] = $item->comment;
			
	        array_push($return_arr,$row_array);
		}
	}

	echo json_encode($return_arr);

}
						


?>