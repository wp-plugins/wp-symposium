<?php

include_once('../../../../wp-config.php');
include_once('../../../../wp-includes/wp-db.php');
include_once('../symposium_functions.php');

// AJAX function to send new password
if ($_POST['action'] == 'deletePost') {

	global $wpdb, $current_user;
	wp_get_current_user();

	$cid = $_POST['cid'];
	$uid = $_POST['uid'];

	if (is_user_logged_in()) {
	
		if ( symposium_safe_param($cid) && symposium_safe_param($uid) ) {
			
			if ( symposium_get_current_userlevel($uid) == 5 ) {
				$sql = "DELETE FROM ".$wpdb->prefix."symposium_comments WHERE cid = ".$cid;
			} else {
				$sql = "DELETE FROM ".$wpdb->prefix."symposium_comments WHERE cid = ".$cid." AND (subject_uid = ".$uid." OR author_uid = ".$uid.")";
			}
			$rows_affected = $wpdb->query( $wpdb->prepare($sql) );
			if ( $rows_affected > 0 ) {
	
				// Delete any replies
				$sql = "DELETE FROM ".$wpdb->prefix."symposium_comments WHERE comment_parent = ".$cid.")";
				$rows_affected = $wpdb->query( $wpdb->prepare($sql) );
	
				echo "#".$cid;
			} else {
				echo "FAILED TO DELETE ".$wpdb->last_query;
			}
			
		} else {
			echo "FAIL, INVALID PARAMETERS (".$uid.":".$cid.")";
		}
	} else {
		echo "FAIL, NOT LOGGED IN";
	}

	exit;
}

// AJAX function to add comment
if ($_POST['action'] == 'addComment') {

	global $wpdb, $current_user;
	wp_get_current_user();

	$uid = $_POST['uid'];
	$text = $_POST['text'];
	$parent = $_POST['parent'];

	if (is_user_logged_in()) {

		if ( ($text != __(addslashes("Write a comment..."), "wp-symposium")) && ($text != '') ) {
	
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
		        	$uid, 
		        	$current_user->ID, 
		        	$parent,
		        	date("Y-m-d H:i:s"),
		        	$text
		        	) 
		        ) );
		        
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
				$body .= "<p><a href='".symposium_get_url('profile')."?uid=".$current_user->ID."'>".__('Go to their wall', 'wp-symposium')."...</a></p>";
				foreach ($recipients as $recipient) {
					if ( ($recipient->ID != $current_user->ID) && ($recipient->notify_new_wall == 'on') ) {
						symposium_sendmail($recipient->user_email, $email_subject, $body);
					}
				}
			}
		
			// Build HTML to prepend to Comment
			$styles = $wpdb->get_row($wpdb->prepare("SELECT bg_color_2 FROM ".$wpdb->prefix . 'symposium_config'));
		
			$html = "<div style='background-color: ".$styles->bg_color_2."; padding:4px; padding-bottom:0px; clear: both; overflow: auto; margin-top:10px;'>";
				$html .= "<div style='float: left; overflow:auto; width:100%;padding:0px;'>";
					$html .= "<div style='margin-left: 45px;overflow:auto;'>";
						$html .= '<a href="'.symposium_get_url('profile').'?uid='.$current_user->ID.'">'.stripslashes($current_user->display_name).'</a>.<br />';
						$html .= symposium_make_url(stripslashes($text));
					$html .= "</div>";
				$html .= "</div>";
				
				$html .= "<div style='float:left;width:45px;margin-left:-100%;'>";
					$html .= get_avatar($current_user->ID, 40);
				$html .= "</div>";
													
			$html .= "</div>";
						
			echo $html;
			exit;

		} else {

			echo '';
			exit;
			
		}
			
			
	} else {
		echo "FAIL, NOT LOGGED IN";
	}
}

// AJAX function to add status
if ($_POST['action'] == 'addStatus') {

	global $wpdb, $current_user;
	wp_get_current_user();

	$subject_uid = $_POST['subject_uid'];
	$author_uid = $_POST['author_uid'];
	$text = $_POST['text'];

	if (is_user_logged_in()) {
		
		if ( ($text != __(addslashes("What's on your mind?"), "wp-symposium")) && ($text != '') ) {
	
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
		        
			// Email all friends who want to know about it
			$sql = "SELECT u.ID, f.friend_to, u.user_email, m.notify_new_wall 
			 FROM ".$wpdb->prefix."symposium_friends f 
			 LEFT JOIN ".$wpdb->prefix."symposium_usermeta m ON m.uid = f.friend_to 
			 LEFT JOIN ".$wpdb->prefix."users u ON f.friend_to = u.ID 
			WHERE f.friend_from = ".$current_user->ID;
			$recipients = $wpdb->get_results($sql);	
					
			if ($recipients) {
				$body = "<p>".$current_user->display_name." ".__('has added a new status to their wall', 'wp-symposium').":</p>";
				$body .= "<p>".stripslashes($text)."</p>";
				$body .= "<p><a href='".symposium_get_url('profile')."?uid=".$current_user->ID."'>".__('Go to their wall', 'wp-symposium')."...</a></p>";
				foreach ($recipients as $recipient) {
					if ( ($recipient->ID != $current_user->ID) && ($recipient->notify_new_wall == 'on') ) {
						symposium_sendmail($recipient->user_email, __('New Wall Post', 'wp-symposium'), $body);
					}
				}
			}
		
		
			// Build HTML to prepend to Wall
			$styles = $wpdb->get_row($wpdb->prepare("SELECT row_border_size, row_border_style, text_color_2 FROM ".$wpdb->prefix . 'symposium_config'));
		
			$html = "<div style='overflow: auto; padding-top: 10px;margin-right: 15px;margin-bottom:15px;border-top: ".$styles->row_border_size."px ".$styles->row_border_style." ".$styles->text_color_2.";'>";
				$html .= "<div style='float: left; overflow:auto; width:100%;padding:0px;'>";
					$html .= "<div style='margin-left: 74px;overflow:auto;'>";
						$html .= '<a href="'.symposium_get_url('profile').'?uid='.$current_user->ID.'">'.stripslashes($current_user->display_name).'</a><br />';
						$html .= symposium_make_url(stripslashes($text));
					$html .= "</div>";
				$html .= "</div>";
				$html .= "<div style='float:left;width:74px;margin-left:-100%;'>";
					$html .= get_avatar($current_user->ID, 64);
				$html .= "</div>";
			$html .= "</div>";
						
			echo $html;
			exit;
			
		} else {

			echo '';
			exit;
			
		}

	} else {
		echo "FAIL, NOT LOGGED IN";
	}
		
}


?>

	