<?php

include_once('../../../../wp-config.php');

global $wpdb, $current_user;
wp_get_current_user();

// Members list search
if ($_POST['action'] == 'getMembers') {

	$me = $current_user->ID;
	$page = 1;
	$page_length = 25;
	$html = '';
	
	$term = ($_POST['action'] != '') ? strtolower($_POST['term']) : '';

	$config = $wpdb->get_row($wpdb->prepare("SELECT online,offline,show_admin FROM ".$wpdb->prefix . 'symposium_config'));

	$members = $wpdb->get_results("
	SELECT m.uid, u.display_name, m.city, m.country, m.share, m.last_activity 
	FROM ".$wpdb->base_prefix."symposium_usermeta m 
	LEFT JOIN ".$wpdb->base_prefix."users u ON m.uid = u.ID 					
	WHERE (lower(u.display_name) LIKE '".$term."%') 
	    OR (lower(u.display_name) LIKE '% %".$term."%') 
		OR (lower(m.city) LIKE '".$term."%') 
		OR (lower(m.country) LIKE '".$term."%') 
		OR (lower(m.extended) LIKE '%".$term."%') 
	ORDER BY m.last_activity DESC");
						
	if ($members) {
	
		$inactive = $config->online;
		$offline = $config->offline;
		$profile = symposium_get_url('profile');
	
		foreach ($members as $member) {

			$user_info = get_userdata($member->uid);							
			if ($user_info->user_level > 9 && $config->show_admin != 'on') {							
				// don't show admin's
			} else {
			
				$time_now = time();
				$last_active_minutes = strtotime($member->last_activity);
				$last_active_minutes = floor(($time_now-$last_active_minutes)/60);
											
				$html .= "<div class='members_row";
					
					$is_friend = symposium_friend_of($member->uid);
					if ($is_friend || $member->uid == $me) {
						$html .= " row_odd corners";		
					} else {
						$html .= " row corners";		
					}
					$html .= "'>";

					$html .= "<div class='members_info'>";
								
						if ( ($member->uid == $me) || (strtolower($member->share) == 'everyone') || (strtolower($member->share) == 'friends only' && $is_friend) ) {
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
						
						$html .= symposium_profile_link($member->uid);

						$html .= ', '.__('last active', 'wp-symposium').' '.symposium_time_ago($member->last_activity).". ";
						if ($last_active_minutes >= $offline) {
							//$html .= '<img src="'.WP_PLUGIN_URL.'/wp-symposiumimages/loggedout.gif">';
						} else {
							if ($last_active_minutes >= $inactive) {
								$html .= '<img src="'.WP_PLUGIN_URL.'/wp-symposium/images/inactive.gif">';
							} else {
								$html .= '<img src="'.WP_PLUGIN_URL.'/wp-symposium/images/online.gif">';
							}
						}

					$html .= "</div>";
				$html .= "</div>";	
			}
		}

	} else {
		$html .= '<br />'.__('No members found', 'wp-symposium')."....";
	}
	
	echo $html;


}

?>

	