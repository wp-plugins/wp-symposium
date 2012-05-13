<?php

include_once('../../../../wp-config.php');

// Show people following people
if ($_POST['action'] == 'menu_plus' || $_POST['action'] == 'menu_plus_me') {

	global $wpdb;
	
	$id = $_POST['uid1'];
	$limit_count = 100;
	$limit_from = isset($_POST['limit_from']) ? $_POST['limit_from'] : 0;

	$mailpage = symposium_get_url('mail');
	if ($mailpage[strlen($mailpage)-1] != '/') { $mailpage .= '/'; }
	$q = symposium_string_query($mailpage);		

	$html = "";	
	
		// Following
		if ($_POST['action'] == 'menu_plus') {
			$sql = "SELECT f.uid, f.following
				FROM ".$wpdb->base_prefix."symposium_following f 
				WHERE f.uid = %d";
		} else {
			$sql = "SELECT f.uid, f.following
				FROM ".$wpdb->base_prefix."symposium_following f 
				WHERE f.following = %d";
		}
		$friends_list = $wpdb->get_results($wpdb->prepare($sql, $id));
		
		$friends_array = array();
		foreach ($friends_list as $friend) {

			$add = array (	
				'following' => $friend->following,
				'uid' => $friend->uid,				
				'last_activity' => get_symposium_meta($friend->uid, 'last_activity')
			);
		
			array_push($friends_array, $add);
		}
		$friends = sub_val_sort($friends_array, 'last_activity', false);	
			
		if ($friends) {
		
			$count = 0;
		
			$inactive = get_option('symposium_online');
			$offline = get_option('symposium_offline');
			
			foreach ($friends as $friend) {
				
				$count++;
				
				$time_now = time();
				$last_active_minutes = strtotime($friend['last_activity']);
				$last_active_minutes = floor(($time_now-$last_active_minutes)/60);

				if ($_POST['action'] == 'menu_plus') {										
					$id = $friend['following'];
				} else {
					$id = $friend['uid'];
				}
												
				$html .= "<div id='friend_".$id."' class='friend_div row_odd corners' style='clear:right; margin-top:8px; overflow: auto; margin-bottom: 15px; padding:6px; width:95%;'>";
				
					$html .= "<div style='width:64px; margin-right: 15px'>";
						$html .= get_avatar($id, 64);
					$html .= "</div>";
										
					$html .= "<div style='padding-left:74px;'>";
						$html .= symposium_profile_link($id);
						$html .= "<br />";
						if ($last_active_minutes >= $offline) {
							$html .= __('Logged out', 'wp-symposium').'. '.__('Last active', 'wp-symposium').' '.symposium_time_ago($friend['last_activity']).".";
						} else {
							if ($last_active_minutes >= $inactive) {
								$html .= __('Offline', 'wp-symposium').'. '.__('Last active', 'wp-symposium').' '.symposium_time_ago($friend['last_activity']).".";
							} else {
								$html .= __('Last active', 'wp-symposium').' '.symposium_time_ago($friend['last_activity']).".";
							}
						}
						if (!get_option('symposium_wps_lite')) {
							$html .= '<br />';
							// Show comment
							$sql = "SELECT cid, comment
								FROM ".$wpdb->base_prefix."symposium_comments
								WHERE author_uid = %d AND subject_uid = %d AND comment_parent = 0 AND type = 'post'
								ORDER BY cid DESC
								LIMIT 0,1";
							$comment = $wpdb->get_row($wpdb->prepare($sql, $id, $id));
							if ($comment) {
								$html .= '<div>'.symposium_smilies(symposium_make_url(stripslashes($comment->comment))).'</div>';
							}
							
							// Show latest non-status activity if applicable
							if (function_exists('symposium_forum')) {
								$sql = "SELECT cid, comment FROM ".$wpdb->base_prefix."symposium_comments
										WHERE author_uid = %d AND subject_uid = %d AND comment_parent = 0 AND type = 'forum' 
										ORDER BY cid DESC 
										LIMIT 0,1";
								$forum = $wpdb->get_row($wpdb->prepare($sql, $id, $id));
								if ($comment && $forum && $forum->cid != $comment->cid) {
									$html .= '<div>'.symposium_smilies(symposium_make_url(stripslashes($forum->comment))).'</div>';
								}
							}
							
							
						}
					$html .= "</div>";

				$html .= "</div>";
								
				if ($count == $limit_count) { $html .= $limit_from+$limit_count.' : '; break; }
			}

			if ($count == $limit_count) {
				$html .= __('Limit reached', 'wp-symposium');
			}
		} else {
			$html .= __("Nothing to show, sorry.", "wp-symposium");
		}		

	echo $html;
	
}

if ($_POST['action'] == 'toggle_following') {

	global $wpdb,$current_user;
	
	$following = $_POST['following'];

	if (is_user_logged_in()) {

		$sql = "SELECT fid FROM ".$wpdb->base_prefix."symposium_following WHERE uid=%d AND following=%d";
		$fid = $wpdb->get_var($wpdb->prepare($sql, $current_user->ID, $following));
		if ($fid) {
			// Exists so clear
			$sql = "DELETE FROM ".$wpdb->base_prefix."symposium_following WHERE fid=%d";
			$wpdb->query($wpdb->prepare($sql, $fid));
		} else {
			// Add as not currently there
			$wpdb->query( $wpdb->prepare( "
			INSERT INTO ".$wpdb->base_prefix."symposium_following
			( 	uid, 
				following,
				created
			)
			VALUES ( %d, %d, %s )", 
			array(
				$current_user->ID, 
				$following,
				date("Y-m-d H:i:s")
				) 
			) );
		}

		echo 'OK';

	} else {
		
		echo 'NOT LOGGED IN';
		
	}
	

	exit;
}	
	
?>

	