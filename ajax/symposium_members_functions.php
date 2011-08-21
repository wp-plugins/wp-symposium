<?php

include_once('../../../../wp-config.php');

global $wpdb, $current_user;
wp_get_current_user();


// Members list search
if (isset($_GET['term']) && $_GET['term'] != '') {
	
	global $wpdb;	
	$return_arr = array();

	$list = $wpdb->get_results("
	SELECT u.ID, u.display_name, m.city, m.country, m.extended 
	FROM ".$wpdb->base_prefix."users u 
	LEFT JOIN ".$wpdb->base_prefix."symposium_usermeta m ON u.ID = m.uid 
	WHERE (
		lower(u.display_name) LIKE '".strtolower($_GET['term'])."%') 
	OR (lower(m.city) LIKE '".strtolower($_GET['term'])."%') 
	OR (lower(m.country) LIKE '".strtolower($_GET['term'])."%') 
	OR (lower(u.display_name) LIKE '% %".strtolower($_GET['term'])."%') 
	OR (lower(m.extended) LIKE '%".strtolower($_GET['term'])."%') 
	ORDER BY u.display_name");
	
	if ($list) {
		foreach ($list as $item) {
			$row_array['id'] = $item->ID;
			$row_array['value'] = $item->ID;
			$row_array['label'] = $item->display_name;
			if ($item->city != '') {
				$row_array['city'] = $item->city;
			} else {
				$row_array['city'] = '';
			}
			if ($item->country != '') {
				if ($item->city != '') {
					$row_array['country'] = ', '.$item->country;
				} else {
					$row_array['country'] = $item->country;
				}
			} else {
				$row_array['country'] = '';
			}
			$row_array['avatar'] = get_avatar($item->ID, 40);
			
	        array_push($return_arr,$row_array);
		}
	}

	echo json_encode($return_arr);
	exit;

}

// AJAX function to get members
if ($_POST['action'] == 'getMembers') {

	global $current_user;
	
	$html = '';
	$page = $_POST['page'];
	$page_length = 25;
	$me = $current_user->ID;

	$plugin_dir = WP_PLUGIN_URL.'/wp-symposium/';
	$config = $wpdb->get_row($wpdb->prepare("SELECT online,offline FROM ".$wpdb->prefix . 'symposium_config'));
	
	$sql = "SELECT m.uid, m.last_activity, m.status, m.city, m.country, m.share, m.wall_share,
	(SELECT comment FROM ".$wpdb->base_prefix."symposium_comments WHERE author_uid = m.uid AND subject_uid = author_uid and comment_parent = 0 ORDER BY cid DESC LIMIT 0,1) AS latest_comment, 
	(SELECT COUNT(*) FROM ".$wpdb->base_prefix."symposium_friends WHERE friend_from = ".$me." AND friend_to = m.uid) AS is_friend 
	FROM ".$wpdb->base_prefix."symposium_usermeta m 
	WHERE m.uid > 0 
	ORDER BY m.last_activity DESC LIMIT ".($page*$page_length-$page_length).",".$page_length;

	$members = $wpdb->get_results($sql);

	if ($members) {
					
		$inactive = $config->online;
		$offline = $config->offline;
		$profile = symposium_get_url('profile');
	
		foreach ($members as $member) {
		
			$time_now = time();
			if ($member->last_activity && $member->status != 'offline') {
				$last_active_minutes = convert_datetime($member->last_activity);
				$last_active_minutes = floor(($time_now-$last_active_minutes)/60);
			} else {
				$last_active_minutes = 999999999;
			}
													
			$html .= "<div class='members_row";
			if ($member->is_friend == 1 || $member->uid == $me) {
				$html .= " row corners'>";		
			} else {
				$html .= " row_odd corners'>";		
			}
				$html .= "<div class='members_info'>";

					if ( ($member->uid == $me) || (strtolower($member->share) == 'everyone') || (strtolower($member->share) == 'friends only' && $member->is_friend) ) {
						$html .= "<div class='members_location'>";
							if ($member->city != '') {
								$html .= $member->city;
							}
							if ($member->country != '') {
								if ($member->city != '') {
									$html .= ', '.$member->country;
								} else {
									$html .= $member->country;
								}
							}
						$html .= "</div>";
					}

					$html .= "<div class='members_avatar'>";
						$html .= get_avatar($member->uid, 64);
					$html .= "</div>";
					$html .= symposium_profile_link($member->uid).', last active '.symposium_time_ago($member->last_activity).". ";
					if ($last_active_minutes >= $offline) {
						//$html .= '<img src="'.$plugin_dir.'images/loggedout.gif">';
					} else {
						if ($last_active_minutes >= $inactive) {
							$html .= '<img src="'.$plugin_dir.'images/inactive.gif">';
						} else {
							$html .= '<img src="'.$plugin_dir.'images/online.gif">';
						}
					}
					if ( ($member->uid == $me) || (strtolower($member->wall_share) == 'everyone') || (strtolower($member->wall_share) == 'friends only' && $member->is_friend) ) {
						$html .= "<br />".stripslashes($member->latest_comment);
					}
				$html .= "</div>";
			$html .= "</div>";
			
		}
		
	} else {
		$html = "No members....";
	}
	
	echo $html;
	exit;
}


?>

	