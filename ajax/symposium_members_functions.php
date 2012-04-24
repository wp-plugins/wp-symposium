<?php

include_once('../../../../wp-config.php');

global $wpdb, $current_user;
wp_get_current_user();

// Members list search
if ($_POST['action'] == 'getMembers') {

	$me = $current_user->ID;
	$page = 1;
	$html = '';
	$search_limit = 100;


	$page_length = ($_POST['page_length'] != '') ? $_POST['page_length'] : 25;
	$extended = isset($_POST['extended']) ? $_POST['extended'] : '';
	$start = ($_POST['start'] != '') ? $_POST['start'] : 0;
	$term = ($_POST['action'] != '') ? strtolower($_POST['term']) : '';
	$orderby = ($_POST['orderby'] != '') ? strtolower($_POST['orderby']) : 'display_name';
	if ($orderby == 'display_name') { $orderby = 'u.display_name'; }
	if ($orderby == 'distance') { $orderby = 'distance, u.display_name'; }
	if ($orderby == 'last_activity') { $orderby = 'cast(m4.meta_value as datetime) DESC'; }
	
	$friends = ($_POST['friends'] != '') ? $_POST['friends'] : '';
	$sql_ext = strlen($term) != 1 ? "OR (lower(u.display_name) LIKE '% %".$term."%')" : "";

	
	if (function_exists('symposium_profile_plus') && is_user_logged_in() && ($lat = get_symposium_meta($current_user->ID, 'plus_lat')) != '') {
		
		$long = get_symposium_meta($current_user->ID, 'plus_long');
		$measure = ($value = get_option("symposium_plus_lat_long")) ? $value : '';	

		$members = $wpdb->get_results("
		SELECT u.ID as uid, u.display_name, cast(m4.meta_value as datetime) as last_activity,
		CASE m7.meta_value
		  WHEN '0' THEN 99999
		  ELSE ROUND (((ACOS(SIN(".$lat." * PI() / 180) * SIN(m7.meta_value * PI() / 180) + COS(".$lat." * PI() / 180) * COS(m7.meta_value * PI() / 180) * COS((".$long." - m8.meta_value) * PI() / 180)) * 180 / PI()) * 60 * 1.1515),0)
		END AS distance 
		FROM ".$wpdb->base_prefix."users u 
		LEFT JOIN ".$wpdb->base_prefix."usermeta m4 ON u.ID = m4.user_id
		LEFT JOIN ".$wpdb->base_prefix."usermeta m7 ON u.ID = m7.user_id
		LEFT JOIN ".$wpdb->base_prefix."usermeta m8 ON u.ID = m8.user_id
		WHERE 
		m4.meta_key = 'symposium_last_activity' AND 
		m7.meta_key = 'symposium_plus_lat' AND 
		m8.meta_key = 'symposium_plus_long' AND 
		(u.display_name IS NOT NULL) AND
		(
		      (lower(u.display_name) LIKE '".$term."%') 
		    ".$sql_ext." 
		)
		ORDER BY ".$orderby." 
		LIMIT 0,".$search_limit);
		
	} else {

		$members = $wpdb->get_results("
		SELECT u.ID as uid, u.display_name, cast(m4.meta_value as datetime) as last_activity, 99999 as distance
		FROM ".$wpdb->base_prefix."users u 
		LEFT JOIN ".$wpdb->base_prefix."usermeta m4 ON u.ID = m4.user_id
		WHERE 
		m4.meta_key = 'symposium_last_activity' AND 
		(u.display_name IS NOT NULL) AND
		(
	       (lower(u.display_name) LIKE '".$term."%') 
		    ".$sql_ext." 
		)
		ORDER BY ".$orderby."
		LIMIT 0,".$search_limit);	
		
	}
	
	if ($members) {
		
		$inactive = get_option('symposium_online');
		$offline = get_option('symposium_offline');
		$profile = symposium_get_url('profile');
		$count = 0;
		$skip = 0;
				
		$mailpage = symposium_get_url('mail');
		if ($mailpage[strlen($mailpage)-1] != '/') { $mailpage .= '/'; }
		$q = symposium_string_query($mailpage);			

		if ( !isset( $wp_roles ) ) $wp_roles = new WP_Roles();									
		// Get included levels
		$dir_levels = strtolower(get_option('symposium_dir_level'));
		if (strpos($dir_levels, ' ') !== FALSE) $dir_levels = str_replace(' ', '', $dir_levels);
		if (strpos($dir_levels, '_') !== FALSE) $dir_levels = str_replace('_', '', $dir_levels);
		
		// Get Extended Field info for advanced search
		if (!get_option('symposium_wps_lite')) {
			$sql = "SELECT * FROM ".$wpdb->prefix."symposium_extended ORDER BY eid";
			$extensions = $wpdb->get_results($wpdb->prepare($sql));
		}

					
		foreach ($members as $member) {
			
			// Check to see if this member is in the included list of roles
			$user = get_userdata( $member->uid );
			$capabilities = $user->{$wpdb->base_prefix.'capabilities'};

			$include = false;
			foreach ( $capabilities as $role => $name ) {
				if ($role) {
					$role = strtolower($role);
					$role = str_replace(' ', '', $role);
					$role = str_replace('_', '', $role);
					if (strpos($dir_levels, $role) !== FALSE) $include = true;
				}
			}
			
			if ($include) {	
				
				$skip++;
				if ($skip < $start) {
					// skip through those already shown
				} else {							
				
					$time_now = time();
					$last_active_minutes = strtotime($member->last_activity);
					$last_active_minutes = floor(($time_now-$last_active_minutes)/60);

					$continue = true;

					// Check against extended fields
					if ($extended && !get_option('symposium_wps_lite')) {
						foreach($extended as $extended_field) {
							$extend_field_parts = explode('|', $extended_field);
							$type = $extend_field_parts[0];
							$eid = $extend_field_parts[1];
							$value = $extend_field_parts[2];
							
							foreach ($extensions as $extension) {
								
								if ($extension->eid == $eid) {

									// Get stub
									$stub = 'extended_'.$extension->extended_slug;

									// List
									if ($type == 'list') {
										if ($value != __('Any', 'wp-symposium')) {
											if (get_symposium_meta($member->uid, $stub) != $value) {
												$continue = false;
											}
										}
									}
									// Checkbox
									if ($type == 'checkbox') {
										if ($value == 'on') {
											if (!get_symposium_meta($member->uid, $stub)) {
												$continue = false;
											}
										} else {
											if (get_symposium_meta($member->uid, $stub)) {
												$continue = false;
											}
										}
									}
								}
							}							
						}
					}
							
							
					// Now carry on if okay to do so	
					if ($continue) {

						$count++;
						if ($count > get_option('symposium_dir_page_length')) break;
						
						$city = get_symposium_meta($member->uid, 'extended_city');
						$country = get_symposium_meta($member->uid, 'extended_country');
						$share = get_symposium_meta($member->uid, 'share');
						$wall_share = get_symposium_meta($member->uid, 'wall_share');

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
							
									if ( ($member->uid == $me) || (is_user_logged_in() && strtolower($share) == 'everyone') || (strtolower($share) == 'everyone') || (strtolower($share) == 'friends only' && $is_friend) ) {
										$html .= "<div class='members_location'>";
											if ($city != '') {
												$html .= $city;
											}
											if ($country != '') {
												if ($city != '') {
													$html .= ', '.$country;
												} else {
													$html .= $country;
												}
											}
										$html .= "</div>";
									}
			
									if (!get_option('symposium_wps_lite')) {
										// Show Send Mail button
										if (get_option('symposium_show_dir_buttons') && $member->uid != $current_user->ID) {
											if ($is_friend) {
												// A friend
												$html .= "<div class='mail_icon' style='display:none;float:right; margin-right:5px;'>";
												$html .= '<img style="cursor:pointer" src="'.get_option('symposium_images').'/orange-tick.gif" onclick="document.location = \''.$mailpage.$q.'view=compose&to='.$member->uid.'\';">';
												$html .= "</div>";
											}
										}
									}
		
									$html .= symposium_profile_link($member->uid);
		
									if (!get_option('symposium_wps_lite')) {
										$html .= ', ';
									} else {
										$html .= '<br />';
									}
									$html .= __('last active', 'wp-symposium').' '.symposium_time_ago($member->last_activity).". ";
									if ($last_active_minutes >= $offline) {
										//$html .= '<img src="'.get_option('symposium_images').'/loggedout.gif">';
									} else {
										if ($last_active_minutes >= $inactive) {
											$html .= '<img src="'.get_option('symposium_images').'/inactive.gif">';
										} else {
											$html .= '<img src="'.get_option('symposium_images').'/online.gif">';
										}
									}
		
									// Distance
									if (function_exists('symposium_profile_plus') && is_user_logged_in() && $member->distance < 99999 && $member->uid != $current_user->ID) {
										// if privacy settings permit
										if ( (strtolower($share) == 'everyone') 
											|| (strtolower($share) == 'public') 
											|| (strtolower($share) == 'friends only' && symposium_friend_of($member->uid, $current_user->ID)) 
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
									
									if (!get_option('symposium_wps_lite')) {
		
										// if privacy settings permit
										if ( (strtolower($wall_share) == 'everyone') 
											|| (strtolower($wall_share) == 'public') 
											|| (strtolower($wall_share) == 'friends only' && symposium_friend_of($member->uid, $current_user->ID)) 
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
									if (is_user_logged_in() && get_option('symposium_show_dir_buttons') && $member->uid != $current_user->ID) {
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
			}
		}
		
		if ($count > 0) {
			if ($count > $page_length) {
				$html .= "<div id='showmore_directory_div' style='text-align:center; width:100%'><a href='javascript:void(0)' id='showmore_directory'>".__("more...", "wp-symposium")."</a></div>";
			}				
		} else {
			$html .= "<div style='text-align:center; width:100%'>".__("No members found", "wp-symposium")."</div>";
		}

	} else {
		$html .= "<div style='text-align:center; width:100%'>".__("No members found", "wp-symposium")."</div>";
	}
	
	echo $html;


}

?>

	
