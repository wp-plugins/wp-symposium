<?php

include_once('../../../../wp-config.php');

// Site search (autocomplete) for small quick search [symposium-search]
if (isset($_GET['term'])) {
		
	global $wpdb;	
	$return_arr = array();
	$term = $_GET['term'];

	$done_groups = false;
	$done_gallery = false;
	$done_posts = false;
	$done_pages = false;
	$done_topics = false;

	$groups_sql = get_option('wps_site_search_groups') ? "UNION ALL SELECT g.gid AS ID, g.name AS display_name, 'group' AS type, g.private AS sharing, '' AS owner, '' AS url FROM ".$wpdb->base_prefix."symposium_groups g WHERE ( name LIKE '%".$term."%') LIMIT 0,25 " : '';
	$gallery_sql = get_option('wps_site_search_gallery') ? "UNION ALL SELECT ga.gid AS ID, ga.name AS display_name, 'gallery' AS type, ga.sharing, ga.owner, '' AS url FROM ".$wpdb->prefix."symposium_gallery ga WHERE ( name LIKE '%".$term."%') LIMIT 0,25 " : '';
	$posts_sql = get_option('wps_site_search_posts') ? "UNION ALL SELECT p.id AS ID, p.post_title AS display_name, 'post' AS type, 'public' AS sharing, p.post_author AS owner, guid AS url FROM ".$wpdb->prefix."posts p WHERE ( p.post_type = 'post' AND p.post_status = 'publish' AND ( p.post_title LIKE '%".$term."%' OR p.post_content LIKE '%".$term."%' ) ) LIMIT 0,25 " : '';
	$pages_sql = get_option('wps_site_search_pages') ? "UNION ALL SELECT pa.id AS ID, pa.post_title AS display_name, 'page' AS type, 'public' AS sharing, pa.post_author AS owner, id AS url FROM ".$wpdb->prefix."posts pa WHERE ( pa.post_type = 'page' AND pa.post_status = 'publish' AND ( pa.post_title LIKE '%".$term."%' OR pa.post_content LIKE '%".$term."%' ) ) LIMIT 0,25 " : '';
	$topics_sql = get_option('wps_site_search_topics') ? "UNION ALL SELECT t.tid AS ID, t.topic_post AS display_name, 'topic' AS type, 'public' AS sharing, t.topic_owner AS owner, topic_parent AS url FROM ".$wpdb->prefix."symposium_topics t WHERE ( t.topic_subject LIKE '%".$term."%' OR t.topic_post LIKE '%".$term."%' ) AND t.topic_group = 0 LIMIT 0,25 " : '';
	$sql = "SELECT * FROM (
				SELECT u.ID AS ID, display_name, 'amember' AS type, '' AS sharing, '' AS owner, '' AS url FROM ".$wpdb->base_prefix."users u WHERE  ( display_name LIKE '".$term."%') LIMIT 0,25	
				".$groups_sql."
				".$gallery_sql."
				".$posts_sql."
				".$pages_sql."
				".$topics_sql."
			) AS results
			ORDER BY type, display_name
			";

	
	$list = $wpdb->get_results($sql);
	
	if ($list) {
		foreach ($list as $item) {
			
			if ($item->type == 'amember') {
				$row_array['avatar'] = get_avatar($item->ID, 40);

				$share = get_symposium_meta($item->ID, 'share');							
				$is_friend = symposium_friend_of($item->ID, $current_user->ID);
	
				if ( ($item->ID == $current_user->ID) || (is_user_logged_in() && strtolower($share) == 'everyone') || (strtolower($share) == 'public') || (strtolower($share) == 'friends only' && $is_friend) ) {
					$row_array['city'] = get_symposium_meta($item->ID, 'extended_city');
					$row_array['country'] = get_symposium_meta($item->ID, 'extended_country');
				} else {
					$row_array['city'] = '';
					$row_array['country'] = '';
				}
				$row_array['id'] = $item->ID;
				$row_array['value'] = $item->ID;
				$row_array['name'] = str_replace('&nbsp;', ' ', stripslashes(strip_tags($item->display_name)));
				$row_array['type'] = $item->type;
				$row_array['owner'] = $item->owner;
				$row_array['url'] = $item->url;
				array_push($return_arr,$row_array);

			} else {
				
				switch($item->type) {
					case 'topic': 
						if ( ($item->owner == $current_user->ID) || (strtolower($item->sharing) == 'public') || (is_user_logged_in() && strtolower($item->sharing) == 'everyone') || (strtolower($item->sharing) == 'public') || (strtolower($item->sharing) == 'friends only' && symposium_friend_of($item->owner, $current_user->ID)) || symposium_get_current_userlevel() == 5) {
							if (!$done_topics) { $row_array['name'] = __('FORUM', 'wp-symposium'); $row_array['type'] = 'sep'; array_push($return_arr,$row_array); $done_topics = true; }
							$row_array['country'] = '';
							$row_array['id'] = $item->ID;
							$row_array['value'] = $item->ID;
							$row_array['name'] = str_replace('&nbsp;', ' ', stripslashes(strip_tags($item->display_name)));
							$row_array['type'] = $item->type;
							$row_array['owner'] = $item->owner;
							$row_array['url'] = $item->url;
							$row_array['avatar'] = get_avatar($item->owner, 40);
							if ($item->url == 0) {
								$row_array['city'] = __('Forum Topic', 'wp-symposium');
							} else {
								$row_array['id'] = $item->url;
								$row_array['city'] = __('Forum Reply', 'wp-symposium');
							}
					        array_push($return_arr,$row_array);
						}
						break;
					case 'post': 
						if ( ($item->owner == $current_user->ID) || (strtolower($item->sharing) == 'public') || (is_user_logged_in() && strtolower($item->sharing) == 'everyone') || (strtolower($item->sharing) == 'public') || (strtolower($item->sharing) == 'friends only' && symposium_friend_of($item->owner, $current_user->ID)) || symposium_get_current_userlevel() == 5) {
							if (!$done_posts) { $row_array['name'] = __('POSTS', 'wp-symposium'); $row_array['type'] = 'sep'; array_push($return_arr,$row_array); $done_posts = true; }
							$row_array['avatar'] = get_avatar($item->owner, 40);
							$row_array['city'] = __('Blog Post', 'wp-symposium');
							$row_array['country'] = '';
							$row_array['country'] = '';
							$row_array['id'] = $item->ID;
							$row_array['value'] = $item->ID;
							$row_array['name'] = str_replace('&nbsp;', ' ', stripslashes(strip_tags($item->display_name)));
							$row_array['type'] = $item->type;
							$row_array['owner'] = $item->owner;
							$row_array['url'] = $item->url;
					        array_push($return_arr,$row_array);
						}
						break;
					case 'page': 
						if ( ($item->owner == $current_user->ID) || (strtolower($item->sharing) == 'public') || (is_user_logged_in() && strtolower($item->sharing) == 'everyone') || (strtolower($item->sharing) == 'public') || (strtolower($item->sharing) == 'friends only' && symposium_friend_of($item->owner, $current_user->ID)) || symposium_get_current_userlevel() == 5) {
							if (!$done_pages) { $row_array['name'] = __('PAGES', 'wp-symposium'); $row_array['type'] = 'sep'; array_push($return_arr,$row_array); $done_pages = true; }
							$row_array['avatar'] = get_avatar($item->owner, 40);
							$row_array['city'] = __('Page', 'wp-symposium');
							$row_array['country'] = '';
							$row_array['url'] = home_url().'/?p='.$item->url;
							$row_array['country'] = '';
							$row_array['id'] = $item->ID;
							$row_array['value'] = $item->ID;
							$row_array['name'] = str_replace('&nbsp;', ' ', stripslashes(strip_tags($item->display_name)));
							$row_array['type'] = $item->type;
							$row_array['owner'] = $item->owner;
							$row_array['url'] = $item->url;
					        array_push($return_arr,$row_array);
						}
						break;
					case 'gallery': 
						if ( ($item->owner == $current_user->ID) || (strtolower($item->sharing) == 'public') || (is_user_logged_in() && strtolower($item->sharing) == 'everyone') || (strtolower($item->sharing) == 'public') || (strtolower($item->sharing) == 'friends only' && symposium_friend_of($item->owner, $current_user->ID)) || symposium_get_current_userlevel() == 5) {
							if (!$done_gallery) { $row_array['name'] = __('PHOTO ALBUMS', 'wp-symposium'); $row_array['type'] = 'sep'; array_push($return_arr,$row_array); $done_gallery = true; }
							$row_array['avatar'] = get_avatar($item->ID, 40);
							$row_array['city'] = __('Photo Album', 'wp-symposium');
							$row_array['country'] = '';
							$row_array['country'] = '';
							$row_array['id'] = $item->ID;
							$row_array['value'] = $item->ID;
							$row_array['name'] = str_replace('&nbsp;', ' ', stripslashes(strip_tags($item->display_name)));
							$row_array['type'] = $item->type;
							$row_array['owner'] = $item->owner;
							$row_array['url'] = $item->url;
					        array_push($return_arr,$row_array);
						}
						break;
					case 'group': 
							if (!$done_groups) { $row_array['name'] = __('GROUPS', 'wp-symposium'); $row_array['type'] = 'sep'; array_push($return_arr,$row_array); $done_groups = true; }
							$row_array['avatar'] = get_group_avatar($item->ID, 40);
							$row_array['city'] = __('Group', 'wp-symposium');
							$row_array['country'] = '';
							$row_array['country'] = '';
							$row_array['id'] = $item->ID;
							$row_array['value'] = $item->ID;
							$row_array['name'] = str_replace('&nbsp;', ' ', stripslashes(strip_tags($item->display_name)));
							$row_array['type'] = $item->type;
							$row_array['owner'] = $item->owner;
							$row_array['url'] = $item->url;
					        array_push($return_arr,$row_array);
						break;
				}			
			}
			
		}
	}

	echo json_encode($return_arr);
	exit;

}

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

			$friends_list = $wpdb->get_results($wpdb->prepare($sql, $id));
			
			$friends_array = array();
			foreach ($friends_list as $friend) {

				$add = array (	
					'following' => $friend->following,
					'uid' => $friend->uid,				
					'last_activity' => get_symposium_meta($friend->following, 'last_activity')
				);
			
				array_push($friends_array, $add);
			}

		} else {
			$sql = "SELECT f.uid, f.following
				FROM ".$wpdb->base_prefix."symposium_following f 
				WHERE f.following = %d";

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
								$html .= '<div>'.symposium_buffer(symposium_make_url(stripslashes($comment->comment))).'</div>';
							}
							
							// Show latest non-status activity if applicable
							if (function_exists('symposium_forum')) {
								$sql = "SELECT cid, comment FROM ".$wpdb->base_prefix."symposium_comments
										WHERE author_uid = %d AND subject_uid = %d AND comment_parent = 0 AND type = 'forum' 
										ORDER BY cid DESC 
										LIMIT 0,1";
								$forum = $wpdb->get_row($wpdb->prepare($sql, $id, $id));
								if ($comment && $forum && $forum->cid != $comment->cid) {
									$html .= '<div>'.symposium_buffer(symposium_make_url(stripslashes($forum->comment))).'</div>';
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
			echo __('Follow', 'wp-symposium');
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
			echo __('Unfollow', 'wp-symposium');
		}

	} else {
		
		echo 'NOT LOGGED IN';
		
	}
	

	exit;
}	

if ($_POST['action'] == 'likeDislike') {

	global $wpdb,$current_user;
	
	if (is_user_logged_in()) {

		$choice = $_POST['choice'];
		$cid = $_POST['cid'];

		$sql = "DELETE FROM ".$wpdb->base_prefix."symposium_likes WHERE cid=%d AND uid=%d";
		$wpdb->query($wpdb->prepare($sql, $cid, $current_user->ID));

		$wpdb->query( $wpdb->prepare( "
		INSERT INTO ".$wpdb->base_prefix."symposium_likes
		( 	type,
			cid, 
			uid,
			liked_date
		)
		VALUES ( %s, %d, %d, %s )", 
		array(
			$choice,
			$cid,
			$current_user->ID, 
			date("Y-m-d H:i:s")
			) 
		) );
		
		// Send a mail informing the author that a like/dislike has been chosen
		$sql = "SELECT author_uid, comment, type, comment_parent FROM ".$wpdb->base_prefix."symposium_comments WHERE cid = %d";
		$author = $wpdb->get_row($wpdb->prepare($sql, $cid));

		if (get_symposium_meta($author->author_uid, 'notify_likes')) {
			
			$sql = "SELECT u.user_email FROM ".$wpdb->base_prefix."users u WHERE u.ID = %d";
			$recipient = $wpdb->get_row($wpdb->prepare($sql, $author->author_uid));	

			if ($recipient) {

				if ($choice == 'like') {
					$verb = "likes";
				} else {
					$verb = "dislikes";
				}
				
				if ($author->comment_parent == 0) {
					$type = 'post';
					$goto = $cid;
				} else {
					$type = 'reply';
					$goto = $author->comment_parent;
				}
				
				$body = "<p>".$current_user->display_name." ".sprintf(__('%s your %s', 'wp-symposium'), $verb, $type).":</p>";
				$comment = $author->comment;
				if ($author->type == 'gallery' && strpos($comment, "[]")) {
					$comment = substr($comment, 0, strpos($comment, "[]")); // strip off images
				}
				$body .= "<p>".$comment."</p>";
				$body .= "<p><a href='".symposium_get_url('profile')."?uid=".$author->author_uid."&post=".$goto."'>".__('Go to the post', 'wp-symposium')."...</a></p>";
				symposium_sendmail($recipient->user_email, $current_user->display_name." ".sprintf(__('%s your %s', 'wp-symposium'), $verb, $type), $body);
				
				echo $current_user->display_name." ".sprintf(__('%s your %s', 'wp-symposium'), $verb, $type);

			}
			
		}	
			
		echo 'OK';
		
	} else {
		
		echo "NOT LOGGED IN";
		
	}	

	exit;
}	
	
if ($_POST['action'] == 'getLikesDislikes') {

	global $wpdb;
	
	if (is_user_logged_in()) {

		$cid = $_POST['cid'];
		$type = $_POST['type'];

		$sql = "SELECT l.*, u.display_name FROM ".$wpdb->base_prefix."symposium_likes l 
				LEFT JOIN ".$wpdb->base_prefix."users u ON u.ID = l.uid
				WHERE cid=%d AND type=%s ORDER BY u.display_name";
		$getlikes = $wpdb->get_results($wpdb->prepare($sql, $cid, $type));

		if ($getlikes) {
			$likes_array = array();
			foreach ($getlikes as $alike) {
	
				$link = symposium_profile_link($alike->uid);

				$add = array (	
					'display_name' => $link,
					'avatar' => get_avatar($alike->uid, 20)
				);
				
				array_push($likes_array, $add);
			}			
			echo json_encode($likes_array);
		} else {
			echo 'None';
		}
		
	} else {
		
		echo "NOT LOGGED IN";
		
	}	

	exit;
}	
	
		
	

		
?>

	
