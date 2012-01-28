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

	$start = ($_POST['start'] != '') ? $_POST['start'] : 0;
	$term = ($_POST['action'] != '') ? strtolower($_POST['term']) : '';
	$orderby = ($_POST['orderby'] != '') ? strtolower($_POST['orderby']) : 'display_name';
	if ($orderby == 'display_name' || $orderby == 'distance') {
		$asc = '';
	} else {
		$asc = 'DESC';
	}
	$friends = ($_POST['friends'] != '') ? $_POST['friends'] : '';

	$config = $wpdb->get_row($wpdb->prepare("SELECT online,offline,show_admin FROM ".$wpdb->prefix . 'symposium_config'));

	
	if (function_exists('symposium_profile_plus') && is_user_logged_in() && ($lat = get_symposium_meta($current_user->ID, 'plus_lat')) != '0') {
		$long = get_symposium_meta($current_user->ID, 'plus_long');
		$measure = ($value = get_option("symposium_plus_lat_long")) ? $value : '';

		$members = $wpdb->get_results("
		SELECT m.uid, u.display_name, m.city, m.country, m.share, m.last_activity, m.share, m.wall_share,
		CASE plus_lat
		  WHEN '0' THEN 99999
		  ELSE ROUND (((ACOS(SIN(".$lat." * PI() / 180) * SIN(plus_lat * PI() / 180) + COS(".$lat." * PI() / 180) * COS(plus_lat * PI() / 180) * COS((".$long." - plus_long) * PI() / 180)) * 180 / PI()) * 60 * 1.1515),0)
		END AS distance 
		FROM ".$wpdb->base_prefix."symposium_usermeta m 
		LEFT JOIN ".$wpdb->base_prefix."users u ON m.uid = u.ID 					
		WHERE (lower(u.display_name) LIKE '".$term."%') 
		    OR (lower(u.display_name) LIKE '% %".$term."%') 
			OR (lower(m.city) LIKE '".$term."%') 
			OR (lower(m.country) LIKE '".$term."%') 
			OR (lower(m.extended) LIKE '%".$term."%') 
		ORDER BY ".$orderby." ".$asc."  
		LIMIT ".$start.",".WPS_DIR_PAGE_LENGTH);	
						
	} else {
		
		$members = $wpdb->get_results("
		SELECT m.uid, u.display_name, m.city, m.country, m.share, m.last_activity, m.share, m.wall_share, 99999 as distance
		FROM ".$wpdb->base_prefix."symposium_usermeta m 
		LEFT JOIN ".$wpdb->base_prefix."users u ON m.uid = u.ID 					
		WHERE (lower(u.display_name) LIKE '".$term."%') 
		    OR (lower(u.display_name) LIKE '% %".$term."%') 
			OR (lower(m.city) LIKE '".$term."%') 
			OR (lower(m.country) LIKE '".$term."%') 
			OR (lower(m.extended) LIKE '%".$term."%') 
		ORDER BY ".$orderby." ".$asc."  
		LIMIT ".$start.",".WPS_DIR_PAGE_LENGTH);

	}
	
	if ($members) {
	
		$cnt = 0;
		$inactive = $config->online;
		$offline = $config->offline;
		$profile = symposium_get_url('profile');
		
		$mailpage = symposium_get_url('mail');
		if ($mailpage[strlen($mailpage)-1] != '/') { $mailpage .= '/'; }
		$q = symposium_string_query($mailpage);			
		
		foreach ($members as $member) {

							$user_info = get_userdata($member->uid);							
			if ($user_info->user_level > 9 && WPS_SHOW_ADMIN != 'on') {							
				// don't show admin's
			} else {
			
				$time_now = time();
				$last_active_minutes = strtotime($member->last_activity);
				$last_active_minutes = floor(($time_now-$last_active_minutes)/60);
											
				$html .= "<div class='members_row";
					
					$is_friend = symposium_friend_of($member->uid, $current_user->ID);
					if ($is_friend || $member->uid == $me) {
						$html .= " row_odd corners";		
					} else {
						$html .= " row corners";		
					}
					$html .= "'>";

					$html .= "<div class='members_info'>";

						$html .= "<div class='members_avatar'>";
							$html .= get_avatar($member->uid, 64);
						$html .= "</div>";	
																
						$html .= "<div style='padding-left: 71px;'>";						
					
							if ( ($member->uid == $me) || (is_user_logged_in() && strtolower($member->share) == 'everyone') || (strtolower($member->share) == 'everyone') || (strtolower($member->share) == 'friends only' && $is_friend) ) {
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
	
							if (!WPS_LITE) {
								// Show Send Mail button
								if (WPS_SHOW_DIR_BUTTONS && $member->uid != $current_user->ID) {
									if ($is_friend) {
										// A friend
										$html .= "<div class='mail_icon' style='display:none;float:right; margin-right:5px;'>";
										$html .= '<img style="cursor:pointer" src="'.WPS_IMAGES_URL.'/orange-tick.gif" onclick="document.location = \''.$mailpage.$q.'view=compose&to='.$member->uid.'\';">';
										$html .= "</div>";
									}
								}
							}

							$html .= symposium_profile_link($member->uid);

							if (!WPS_LITE) {
								$html .= ', ';
							} else {
								$html .= '<br />';
							}
							$html .= __('last active', 'wp-symposium').' '.symposium_time_ago($member->last_activity).". ";
							if ($last_active_minutes >= $offline) {
								//$html .= '<img src="'.WPS_IMAGES_URL.'/loggedout.gif">';
							} else {
								if ($last_active_minutes >= $inactive) {
									$html .= '<img src="'.WPS_IMAGES_URL.'/inactive.gif">';
								} else {
									$html .= '<img src="'.WPS_IMAGES_URL.'/online.gif">';
								}
							}

							// Distance
							if (function_exists('symposium_profile_plus') && is_user_logged_in() && $member->distance < 99999 && $member->uid != $current_user->ID) {
								// if privacy settings permit
								if ( (strtolower($member->share) == 'everyone') 
									|| (strtolower($member->share) == 'public') 
									|| (strtolower($member->share) == 'friends only' && symposium_friend_of($member->uid, $current_user->ID)) 
									) {		
									if ($measure != 'on') { 
										$distance = intval(($member->distance/5)*8);
										$miles = __('km', 'wp-symposium');
									} else {
										$distance = $member->distance;
										$miles = __('miles', 'wp-symposium');
									}	
									$html .= '<br />'.__('Distance', 'wp-symposium').': '.$distance.' '.$miles;
								}
							}
							
							if (!WPS_LITE) {

								// if privacy settings permit
								if ( (strtolower($member->wall_share) == 'everyone') 
									|| (strtolower($member->wall_share) == 'public') 
									|| (strtolower($member->wall_share) == 'friends only' && symposium_friend_of($member->uid, $current_user->ID)) 
									) {		
																				
									// Show comment
									$sql = "SELECT cid, comment, type FROM ".$wpdb->base_prefix."symposium_comments
											WHERE author_uid = %d AND comment_parent = 0 AND type = 'post'
											ORDER BY cid DESC 
											LIMIT 0,1";
									$comment = $wpdb->get_row($wpdb->prepare($sql, $member->uid));
									if ($comment) {
										$html .= '<div>'.symposium_smilies(symposium_make_url(stripslashes($comment->comment))).'</div>';
									}
									// Show latest non-status activity if applicable
									if (function_exists('symposium_forum')) {
										$sql = "SELECT cid, comment FROM ".$wpdb->base_prefix."symposium_comments
												WHERE author_uid = %d AND comment_parent = 0 AND type = 'forum' 
												ORDER BY cid DESC 
												LIMIT 0,1";
										$forum = $wpdb->get_row($wpdb->prepare($sql, $member->uid));
										if ($forum && (!$comment || $forum->cid != $comment->cid)) {
											$html .= '<div>'.symposium_smilies(symposium_make_url(stripslashes($forum->comment))).'</div>';
										}
									}
								}
							}
							
							// Show add as a friend
							if (is_user_logged_in() && WPS_SHOW_DIR_BUTTONS && $member->uid != $current_user->ID) {
								if (symposium_pending_friendship($member->uid)) {
									// Pending
									$html .= __('Friend request sent.', 'wp-symposium');
								} else {
									if (!$is_friend) {
										// Not a friend
										$html .= '<div id="addasfriend_done1_'.$member->uid.'">';
										$html .= '<div id="add_as_friend_message">';
										$html .= '<input title="'.$member->uid.'" id="addtext_'.$member->uid.'" type="text" class="addfriend_text input-field" onclick="this.value=\'\'" value="'.__('Add as a Friend...', 'wp-symposium').'">';
										$html .= '<input type="submit" title="'.$member->uid.'" class="addasfriend symposium-button" value="'.__('Add', 'wp-symposium').'" /> ';						
										$html .= '</div></div>';
										$html .= '<div id="addasfriend_done2_'.$member->uid.'" class="hidden">'.__('Friend Request Sent', 'wp-symposium').'</div>';	
									}
								}
							}
							
						$html .= "</div>";
					$html .= "</div>";
				$html .= "</div>";	
			}
		}

		$html .= "<div style='text-align:center; width:100%'><a href='javascript:void(0)' id='showmore_directory'>".__("more...", "wp-symposium")."</a></div>";

	} else {
		$html .= '<br />'.__('No members found', 'wp-symposium')."....";
	}
	
	echo $html;


}

?>

	