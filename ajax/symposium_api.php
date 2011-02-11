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

// Add post
// eg: symposium_api.php?action=post&subject_uid=309&author_uid=499&text=This%20is%20to%20Simon's%20wall
if ($_GET['action'] == 'post') {

	$text = $_GET['text'];

	if ($text != '') {

		$subject_uid = $_GET['subject_uid'];
		$author_uid = $_GET['author_uid'];

		$wpdb->query( $wpdb->prepare( "
			INSERT INTO ".$wpdb->prefix."symposium_comments
			( 	subject_uid, 
				author_uid,
				comment_parent,
				comment_timestamp,
				comment
			)
			VALUES ( %d, %d, %d, %s, %s )", 
	        array(
	        	$subject_uid, 
	        	$author_uid, 
	        	0,
	        	date("Y-m-d H:i:s"),
	        	$text
	        	) 
	        ) );

		// New Post ID
		$new_id = $wpdb->insert_id;

	    // Subject's name for use below
		$subject_name = $wpdb->get_var($wpdb->prepare("SELECT display_name FROM ".$wpdb->prefix."users WHERE ID = %d", $subject_uid));
	        
		// Email all friends who want to know about it
		$sql = "SELECT u.ID, f.friend_to, u.user_email, m.notify_new_wall 
		 FROM ".$wpdb->prefix."symposium_friends f 
		 LEFT JOIN ".$wpdb->prefix."symposium_usermeta m ON m.uid = f.friend_to 
		 LEFT JOIN ".$wpdb->prefix."users u ON f.friend_to = u.ID 
		WHERE f.friend_from = ".$current_user->ID;
		$recipients = $wpdb->get_results($sql);	
				
		if ($recipients) {
			if ($subject_uid == $author_uid) {
				$body = "<p>".$current_user->display_name." ".__('has added a new status to their wall', 'wp-symposium').":</p>";
			} else {
				$body = "<p>".$current_user->display_name." ".__( sprintf("has added a new status to %s's wall", $subject_name), 'wp-symposium').":</p>";
			}
			$body .= "<p>".stripslashes($text)."</p>";
			$body .= "<p><a href='".symposium_get_url('profile')."?uid=".$subject_uid."&post=".$new_id."'>".__('Go to their wall', 'wp-symposium')."...</a></p>";
			foreach ($recipients as $recipient) {
				if ( ($recipient->ID != $current_user->ID) && ($recipient->notify_new_wall == 'on') ) {
					symposium_sendmail($recipient->user_email, __('New Wall Post', 'wp-symposium'), $body);
				}
			}
		}
					
	}

}

// Add reply (where X = parent post ID)
// eg: symposium_api.php?action=reply&parent=434&subject_uid=499&author_uid=309&text=This%20is%20a%20reply%20to%20Simon's%20wall%20post
if ($_GET['action'] == 'reply') {

	$author_uid = $_GET['author_uid'];
	$subject_uid = $_GET['subject_uid'];
	$text = $_GET['text'];
	$parent = $_GET['parent'];
	
	if ( $text != '') {

		$wpdb->query( $wpdb->prepare( "
			INSERT INTO ".$wpdb->prefix."symposium_comments
			( 	subject_uid, 
				author_uid,
				comment_parent,
				comment_timestamp,
				comment
			)
			VALUES ( %d, %d, %d, %s, %s )", 
	        array(
	        	$subject_uid, 
	        	$author_uid, 
	        	$parent,
	        	date("Y-m-d H:i:s"),
	        	$text
	        	) 
	        ) );
	        
		// New Post ID
		$new_id = $wpdb->insert_id;
	        		        
	    // Subject's name for use below
		$subject_name = $wpdb->get_var($wpdb->prepare("SELECT display_name FROM ".$wpdb->prefix."users WHERE ID = %d", $uid));
	
		// Email all friends who want to know about it
		$sql = "SELECT u.ID, f.friend_to, u.user_email, m.notify_new_wall 
		 FROM ".$wpdb->prefix."symposium_friends f 
		 LEFT JOIN ".$wpdb->prefix."symposium_usermeta m ON m.uid = f.friend_to 
		 LEFT JOIN ".$wpdb->prefix."users u ON f.friend_to = u.ID 
		WHERE f.friend_from = ".$current_user->ID;
		$recipients = $wpdb->get_results($sql);			
		if ($recipients) {
			if ($parent == 0) {
				$email_subject = __('New Wall Post', 'wp-symposium');
				if ($current_user->ID == $uid) {
					$body = "<p>".$current_user->display_name." ".__('has added a new status to their wall', 'wp-symposium').":</p>";
				} else {
					$body = "<p>".$current_user->display_name." ".sprintf(__("has added a new post to %s's wall", "wp-symposium"), $subject_name).":</p>";
				}
			} else {
				$email_subject = __('New Wall Reply', 'wp-symposium');
				if ($current_user->ID == $uid) {
					$body = "<p>".$current_user->display_name." has replied to their post:</p>";
				} else {
					$body = "<p>".$current_user->display_name." has replied to ".$subject_name."'s post:</p>";
				}
			}
			$body .= "<p>".stripslashes($text)."</p>";
			$body .= "<p><a href='".symposium_get_url('profile')."?uid=".$uid."&post=".$parent."'>".__('Go to their wall', 'wp-symposium')."...</a></p>";
			foreach ($recipients as $recipient) {
				if ( ($recipient->ID != $current_user->ID) && ($recipient->notify_new_wall == 'on') ) {
					symposium_sendmail($recipient->user_email, $email_subject, $body);
				}
			}
		}
							
	}

}

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
		$sql = "SELECT c.*, u.display_name, u2.display_name AS subject_name FROM ".$wpdb->prefix."symposium_comments c LEFT JOIN ".$wpdb->prefix."users u ON c.author_uid = u.ID LEFT JOIN ".$wpdb->prefix."users u2 ON c.subject_uid = u2.ID WHERE c.comment_parent = 0 ORDER BY c.comment_timestamp DESC LIMIT 0,20";							
	}

	if ($version == "friends") {
		$sql = "SELECT c.*, u.display_name, u2.display_name AS subject_name FROM ".$wpdb->prefix."symposium_comments c LEFT JOIN ".$wpdb->prefix."users u ON c.author_uid = u.ID LEFT JOIN ".$wpdb->prefix."users u2 ON c.subject_uid = u2.ID WHERE ( (c.subject_uid = ".$uid1.") OR (c.author_uid = ".$uid1.") OR ( c.author_uid IN (SELECT friend_to FROM ".$wpdb->prefix."symposium_friends WHERE friend_from = ".$uid1.")) OR ( c.subject_uid IN (SELECT friend_to FROM ".$wpdb->prefix."symposium_friends WHERE friend_from = ".$uid1.")) ) AND c.comment_parent = 0 ORDER BY c.comment_timestamp DESC LIMIT 0,20";							
	}

	if ($version == "my") {
		$sql = "SELECT c.*, u.display_name, u2.display_name AS subject_name FROM ".$wpdb->prefix."symposium_comments c LEFT JOIN ".$wpdb->prefix."users u ON c.author_uid = u.ID LEFT JOIN ".$wpdb->prefix."users u2 ON c.subject_uid = u2.ID WHERE (c.subject_uid = ".$uid1.") AND c.comment_parent = 0 ORDER BY c.comment_timestamp DESC LIMIT 0,20";							
	}

	$list = $wpdb->get_results($sql);	
	
	if ($list) {
		foreach ($list as $item) {
						
			$reply_count = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."symposium_comments WHERE comment_parent = ".$item->cid);	
			$avatar = get_user_avatar($item->author_uid, 32);
			preg_match('/<img\s.*src=["\'](.*?)["\']/i', $avatar, $matches); 
			$avatar = $matches[1];  
			
			$row_array['author_name'] = $item->display_name;
			$row_array['subject_name'] = $item->subject_name;
			$row_array['cid'] = $item->cid;
			$row_array['subject_uid'] = $item->subject_uid;
			$row_array['author_uid'] = $item->author_uid;
			$row_array['author_avatar'] = $avatar;
			$row_array['comment_parent'] = $item->comment_parent;
			$row_array['comment_timestamp'] = $item->comment_timestamp;
			$row_array['comment'] = $item->comment;
			$row_array['reply_count'] = $reply_count;
			
	        array_push($return_arr,$row_array);

			// Get replies
			$sql2 = "SELECT c.*, u.display_name, u2.display_name AS subject_name FROM ".$wpdb->prefix."symposium_comments c LEFT JOIN ".$wpdb->prefix."users u ON c.author_uid = u.ID LEFT JOIN ".$wpdb->prefix."users u2 ON c.subject_uid = u2.ID WHERE c.comment_parent = ".$item->cid;

			$replies = $wpdb->get_results($sql2);	

			if ($replies) {
				foreach ($replies as $reply) {
								
					$avatar = get_user_avatar($reply->author_uid, 32);
					preg_match('/<img\s.*src=["\'](.*?)["\']/i', $avatar, $matches); 
					$avatar = $matches[1];  
					
					$row_array['author_name'] = $reply->display_name;
					$row_array['subject_name'] = $reply->subject_name;
					$row_array['cid'] = $reply->cid;
					$row_array['subject_uid'] = $reply->subject_uid;
					$row_array['author_uid'] = $reply->author_uid;
					$row_array['author_avatar'] = $avatar;
					$row_array['comment_parent'] = $reply->comment_parent;
					$row_array['comment_timestamp'] = $reply->comment_timestamp;
					$row_array['comment'] = $reply->comment;
					$row_array['reply_count'] = "0";
					
			        array_push($return_arr,$row_array);
		
				}
			}
					
		}
	}
	

	echo json_encode($return_arr);

}
						


?>