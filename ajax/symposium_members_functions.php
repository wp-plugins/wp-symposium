<?php

include_once('../../../../wp-config.php');

global $wpdb, $current_user;
wp_get_current_user();

// Members list search
if ($_POST['action'] == 'getMembers') {

	$me = $current_user->ID;
	$page = 1;
	$html = '';
	
	$term = ($_POST['action'] != '') ? strtolower($_POST['term']) : '';
	$friends = ($_POST['friends'] != '') ? $_POST['friends'] : '';
	$orderby = ($_POST['orderby'] != '') ? strtolower($_POST['orderby']) : '';
	$start = ($_POST['start'] != '') ? $_POST['start'] : 0;

    switch ($orderby)
    {
        case 'display_name': $order = ''; break;
        case 'last_activity': $order = 'DESC'; break;
        default: return '';  break;
    }
    
	$members = $wpdb->get_results("
	SELECT m.uid, u.display_name, m.city, m.country, m.share, m.last_activity 
	FROM ".$wpdb->base_prefix."symposium_usermeta m 
	LEFT JOIN ".$wpdb->base_prefix."users u ON m.uid = u.ID 					
	WHERE (lower(u.display_name) LIKE '".$term."%') 
	    OR (lower(u.display_name) LIKE '% %".$term."%') 
		OR (lower(m.city) LIKE '".$term."%') 
		OR (lower(m.country) LIKE '".$term."%') 
		OR (lower(m.extended) LIKE '%".$term."%') 
	ORDER BY ".$orderby." ".$order."
	LIMIT ".$start.",".WPS_DIR_PAGE_LENGTH);
	
	if ($members) {
	
		$cnt = 0;
		$inactive = WPS_ONLINE;
		$offline = WPS_OFFLINE;
		$profile = symposium_get_url('profile');
		$mailpage = symposium_get_url('mail');
		$q = symposium_string_query($mailpage);			
		
		foreach ($members as $member) {

			$user_info = get_userdata($member->uid);							
			if ($user_info->user_level > 9 && WPS_SHOW_ADMIN != 'on') {							
				// don't show admin's
			} else {
			
				$time_now = time();
				$last_active_minutes = strtotime($member->last_activity);
				$last_active_minutes = floor(($time_now-$last_active_minutes)/60);

				$is_friend = symposium_friend_of($member->uid, $current_user->ID);
				if ( ($friends == '') || ($friends == 'on' && $is_friend) ) {
					
					$cnt++;
											
					$html .= "<div class='members_row";
						
						if ($is_friend || $member->uid == $me) {
							$html .= " row_odd corners";		
						} else {
							$html .= " row corners";		
						}
						$html .= "'>";					
						$html .= "<div class='members_info'>";

							if (!WPS_LITE) {
								// Show Send Mail button
								if (WPS_SHOW_DIR_BUTTONS && $member->uid != $current_user->ID) {
									if ($is_friend) {
										// A friend
										$html .= "<div class='mail_icon' style='display:none;float:right;margin-left:5px;'>";
										$html .= '<img style="cursor:pointer" src="'.WPS_IMAGES_URL.'/orange-tick.gif" onclick="document.location = \''.$mailpage.$q.'view=compose&to='.$member->uid.'\';">';
										$html .= "</div>";
									}
								}
							}	
															
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

							if (!WPS_LITE) {
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
											WHERE author_uid = %d AND comment_parent = 0 AND type != 'post' 
											ORDER BY cid DESC 
											LIMIT 0,1";
									$forum = $wpdb->get_row($wpdb->prepare($sql, $member->uid));
									if ($forum && $forum->cid != $comment->cid) {
										$html .= '<div>'.symposium_smilies(symposium_make_url(stripslashes($forum->comment))).'</div>';
									}
								}
							}
							
							// Show add as a friend
							if (WPS_SHOW_DIR_BUTTONS && $member->uid != $current_user->ID) {
								if (symposium_pending_friendship($member->uid)) {
									// Pending
									$html .= __('Friend request sent.', 'wp-symposium');
								} else {
									if (!$is_friend) {
										// Not a friend
										$html .= '<div id="addasfriend_done1_'.$member->uid.'">';
										$html .= '<div id="add_as_friend_message">';
										$html .= '<input type="text" class="addfriend_text input-field" onclick="this.value=\'\'" value="'.__('Add as a Friend...', 'wp-symposium').'">';
										$html .= '<input type="submit" title="'.$member->uid.'" class="addasfriend symposium-button" value="'.__('Add', 'wp-symposium').'" /> ';						
										$html .= '</div></div>';
										$html .= '<div id="addasfriend_done2_'.$member->uid.'" class="hidden">'.__('Friend Request Sent', 'wp-symposium').'</div>';	
									}
								}
							}
																					
						$html .= "</div>";
					
					$html .= "</div>";	
					
				}
					
			}
			
		}

		if ($cnt == WPS_DIR_PAGE_LENGTH) {
			$html .= "<div style='text-align:center; width:100%;'><a href='javascript:void(0)' id='showmore_directory'>".__("more...", "wp-symposium")."</a></div>";
		}

	} else {
		$html .= '<div style="clear:both;text-align:center; width:100%;">'.__('No members found', 'wp-symposium')."....</div>";
	}
	
	echo $html;


}

?>

	