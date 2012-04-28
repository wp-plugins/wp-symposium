<?php
/*  Copyright 2010,2011  Simon Goodchild  (info@wpsymposium.com)

	License: GPL3

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

function forum_rank($uid) {
	
	global $wpdb;	
	
	$max_sql = "SELECT topic_owner, COUNT(*) AS cnt FROM ".$wpdb->prefix."symposium_topics GROUP BY topic_owner ORDER BY cnt DESC LIMIT 0,1";
	$max = $wpdb->get_row($max_sql);

	$my_sql = "SELECT COUNT(*) AS cnt FROM ".$wpdb->prefix."symposium_topics WHERE topic_owner = ".$uid;
	$my_count = $wpdb->get_var($my_sql);
	
	$forum_ranks = get_option('symposium_forum_ranks');

	$ranks = explode(';', $forum_ranks);
	$my_rank = '';
	
	if ($my_count == $max->cnt) { 
		$my_rank = $ranks[1];
	} else {
		for ( $l = 10; $l >= 1; $l=$l-1) {
			if ($my_count >= $ranks[($l*2)+2]) {
				$my_rank = $ranks[($l*2)+1];
			}
		}
	}
	
	return $my_rank;

}

function symposium_bbcode_remove($text_to_search) {
 $pattern = '|[[\/\!]*?[^\[\]]*?]|si';
 $replace = '';
 return preg_replace($pattern, $replace, $text_to_search);

}

function symposium_bbcode_replace($text_to_search) {

	$text_to_search = str_replace('http://youtu.be/', 'http://www.youtube.com/watch?v=', $text_to_search);
	
	$search = array(
	        '@\[(?i)b\](.*?)\[/(?i)b\]@si',
	        '@\[(?i)i\](.*?)\[/(?i)i\]@si',
	        '@\[(?i)s\](.*?)\[/(?i)s\]@si',
	        '@\[(?i)u\](.*?)\[/(?i)u\]@si',
	        '@\[(?i)img\](.*?)\[/(?i)img\]@si',
	        '@\[(?i)url\](.*?)\[/(?i)url\]@si',
	        '@\[(?i)url=(.*?)\](.*?)\[/(?i)url\]@si',
	        '@\[(?i)code\](.*?)\[/(?i)code\]@si',
			'@\[youtube\].*?(?:v=)?([^?&[]+)(&[^[]*)?\[/youtube\]@is'
	);
	$replace = array(
	        '<b>\\1</b>',
	        '<i>\\1</i>',
	        '<s>\\1</s>',
	        '<u>\\1</u>',
	        '<img src="\\1">',
	        '<a href="\\1">\\1</a>',
	        '<a href="\\1">\\2</a>',
	        '<code>\\1</code>',
	        '<iframe title="YouTube video player" width="475" height="290" src="http://www.youtube.com/embed/\\1" frameborder="0" allowfullscreen></iframe>'
	);

	$r = preg_replace($search, $replace, $text_to_search);
   
   	return $r;

}

function show_profile_menu($uid1, $uid2) {
	
	global $wpdb, $current_user;

		$share = get_symposium_meta($uid1, 'share');		
		$privacy = get_symposium_meta($uid1, 'wall_share');		
		$is_friend = symposium_friend_of($uid1, $current_user->ID);
		if ( $wpdb->get_results( "SELECT meta_key FROM ".$wpdb->base_prefix."usermeta WHERE user_ID = '".$uid1."' AND meta_key LIKE '%symposium_extended_%' AND meta_value != ''" ) > 0 ) { $extended = "on"; } else { $extended = ""; }
		
		$html = '';

		if ($uid1 > 0) {

			// Filter for additional menu items (see www.wpswiki.com for help)
			$html .= apply_filters ( 'symposium_profile_menu_filter', $html,$uid1, $uid2, $privacy, $is_friend, $extended, $share );
			
			if ($uid1 == $uid2 || symposium_get_current_userlevel() == 5) {
				if (get_option('symposium_profile_avatars') == 'on') {
					$html .= '<div id="menu_avatar" class="symposium_profile_menu">'.__('Profile Photo', 'wp-symposium').'</div>';
				}
				$html .= '<div id="menu_personal" class="symposium_profile_menu">'.__('Profile Details', 'wp-symposium').'</div>';
				$html .= '<div id="menu_settings" class="symposium_profile_menu">'.__('Community Settings', 'wp-symposium').'</div>';

			}
			
			// Add mail for admin's so they can read members's mail
			if (symposium_get_current_userlevel() == 5 && $uid1 != $current_user->ID && function_exists('symposium_mail')) {
				$mailpage = symposium_get_url('mail');
				$q = symposium_string_query($mailpage);
				$html .= '<a href="'.$mailpage.$q.'uid='.$uid1.'" class="symposium_profile_menu">'.__('Mail Admin', 'wp-symposium').'</a>';
			}
			
		}
		
		// Filter for additional text/HTML after menu items
		$html .= apply_filters ( 'symposium_profile_menu_end_filter', $html,$uid1, $uid2, $privacy, $is_friend, $extended, $share );

	
	return $html;

}

function symposium_make_url($text) {

	if (strpos($text, 'youtube.com') === FALSE) {
		$url = "\\2";
	} else {
		$url = "<img src='".get_option('symposium_images')."/video.gif' title='".$text."' alt='".$text."' />";;
	}
	$text = preg_replace("#(^|[\n ])(([\w]+?://[\w\#$%&~.\-;:=,?@\[\]+]*)(/[\w\#$%&~/.\-;:=,?@\[\]+]*)?)#is", "\\1<a href=\"\\2\" target=\"_blank\">".$url."</a>", $text);
    $text = preg_replace("#(^|[\n ])(((www|ftp)\.[\w\#$%&~.\-;:=,?@\[\]+]*)(/[\w\#$%&~/.\-;:=,?@\[\]+]*)?)#is", "\\1<a href=\"http://\\2\" target=\"_blank\">".$url."</a>", $text);
    
    return $text;

}

function symposium_profile_friends($uid, $limit_from) {

	global $wpdb, $current_user;
	wp_get_current_user();
	
	$limit_count = 10;

	$privacy = get_symposium_meta($uid, 'share');
	$is_friend = symposium_friend_of($uid, $current_user->ID);
	$html = "";	

	if ( ($uid == $current_user->ID) || (is_user_logged_in() && strtolower($privacy) == 'everyone') || (strtolower($privacy) == 'public') || (strtolower($privacy) == 'friends only' && $is_friend) || symposium_get_current_userlevel() == 5) {

		$plugin = WP_PLUGIN_URL.'/wp-symposium';
	
		$mailpage = symposium_get_url('mail');
		if ($mailpage[strlen($mailpage)-1] != '/') { $mailpage .= '/'; }
		$q = symposium_string_query($mailpage);		

		// Friend Requests
		if ($uid == $current_user->ID) {
			
			$sql = "SELECT u1.display_name, u1.ID, f.friend_timestamp, f.friend_message, f.friend_from 
					FROM ".$wpdb->base_prefix."symposium_friends f 
					LEFT JOIN ".$wpdb->base_prefix."users u1 ON f.friend_from = u1.ID 
					WHERE f.friend_to = %d AND f.friend_accepted != 'on' ORDER BY f.friend_timestamp DESC";
	
			$requests = $wpdb->get_results($wpdb->prepare($sql, $current_user->ID));
			if ($requests) {
				
				$html .= '<h2>'.__('Friend Requests', 'wp-symposium').'...</h2>';
				
				foreach ($requests as $request) {
				
					$html .= "<div id='request_".$request->friend_from."' style='clear:right; margin-top:8px; overflow: auto; margin-bottom: 15px; width:95%;'>";		
						$html .= "<div style='float: left; width:64px; margin-right: 15px'>";
							$html .= get_avatar($request->ID, 64);
						$html .= "</div>";
						$html .= "<div style='float: left;'>";
							$html .= symposium_profile_link($request->ID)."<br />";
							$html .= symposium_time_ago($request->friend_timestamp)."<br />";
							$html .= "<em>".stripslashes($request->friend_message)."</em>";
						$html .= "</div>";
						$html .= "<div style='clear: both; float:right;'>";
							$html .= '<input type="submit" title="'.$request->friend_from.'" id="rejectfriendrequest" class="symposium-button" value="'.__('Reject', 'wp-symposium').'" /> ';
						$html .= "</div>";
						$html .= "<div style='float:right;'>";
							$html .= '<input type="submit" title="'.$request->friend_from.'" id="acceptfriendrequest" class="symposium-button" value="'.__('Accept', 'wp-symposium').'" /> ';
						$html .= "</div>";
					$html .= "</div>";
				}

				$html .= '<hr />';
				
			}
		}
		
		// Friends
		$sql = "SELECT f.*, cast(m.meta_value as datetime) as last_activity 
				FROM ".$wpdb->base_prefix."symposium_friends f 
				LEFT JOIN ".$wpdb->base_prefix."usermeta m ON m.user_id = f.friend_to 
				WHERE f.friend_to > 0 AND f.friend_from = %d 
				AND m.meta_key = 'symposium_last_activity'
				ORDER BY cast(m.meta_value as datetime) DESC LIMIT %d, %d";
		$friends = $wpdb->get_results($wpdb->prepare($sql, $uid, $limit_from, $limit_count));
		
		if ($friends) {
		
			$count = 0;
		
			$inactive = get_option('symposium_online');
			$offline = get_option('symposium_offline');
			
			foreach ($friends as $friend) {
				
				$count++;
				
				$time_now = time();
				$last_active_minutes = strtotime($friend->last_activity);
				$last_active_minutes = floor(($time_now-$last_active_minutes)/60);
												
				$html .= "<div id='friend_".$friend->friend_to."' class='friend_div row_odd corners' style='clear:right; margin-top:8px; overflow: auto; margin-bottom: 15px; padding:6px; width:95%;'>";
				
					$html .= "<div style='width:64px; margin-right: 15px'>";
						$html .= get_avatar($friend->friend_to, 64);
					$html .= "</div>";

					// Send Mail and remove as friend
					$html .= "<div style='width:50px; height: 16px; float:right;'>";
					if ($friend->friend_accepted == 'on') {
						if ($uid == $current_user->ID) {

							$html .= "<div style='display:none;' class='friend_icons'>";
	
								$html .= "<div style='float:right;margin-left:5px;margin-right:5px;'>";
									$html .= '<img style="cursor:pointer" src="'.get_option('symposium_images').'/delete.png" title="'.$friend->friend_to.'" class="frienddelete">';
									$html .= '</form>';
								$html .= "</div>";
							
								$html .= "<div style='float:right;'>";
									$html .= '<img style="cursor:pointer" src="'.get_option('symposium_images').'/orange-tick.gif" onclick="document.location = \''.$mailpage.$q.'view=compose&to='.$friend->friend_to.'\';">';
								$html .= "</div>";

							$html .= "</div>";
							
						}
					}
					$html .= '</div>';
										
					$html .= "<div style='padding-left:74px;'>";
						$html .= symposium_profile_link($friend->friend_to);
						$html .= "<br />";
						if ($last_active_minutes >= $offline) {
							$html .= __('Logged out', 'wp-symposium').'. '.__('Last active', 'wp-symposium').' '.symposium_time_ago($friend->last_activity).".";
						} else {
							if ($last_active_minutes >= $inactive) {
								$html .= __('Offline', 'wp-symposium').'. '.__('Last active', 'wp-symposium').' '.symposium_time_ago($friend->last_activity).".";
							} else {
								$html .= __('Last active', 'wp-symposium').' '.symposium_time_ago($friend->last_activity).".";
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
							$comment = $wpdb->get_row($wpdb->prepare($sql, $friend->friend_to, $friend->friend_to));
							if ($comment) {
								$html .= '<div>'.symposium_smilies(symposium_make_url(stripslashes($comment->comment))).'</div>';
							}
							
							// Show latest non-status activity if applicable
							if (function_exists('symposium_forum')) {
								$sql = "SELECT cid, comment FROM ".$wpdb->base_prefix."symposium_comments
										WHERE author_uid = %d AND subject_uid = %d AND comment_parent = 0 AND type = 'forum' 
										ORDER BY cid DESC 
										LIMIT 0,1";
								$forum = $wpdb->get_row($wpdb->prepare($sql, $friend->friend_to, $friend->friend_to));
								if ($comment && $forum && $forum->cid != $comment->cid) {
									$html .= '<div>'.symposium_smilies(symposium_make_url(stripslashes($forum->comment))).'</div>';
								}
							}
							
							
						}
					$html .= "</div>";

					if ($friend->friend_accepted != 'on') {
						$html .= "<div style='float:left;'>";
							$html .= "<strong>".__("Friend request sent.", "wp-symposium")."</strong>";
						$html .= "</div>";
					}					

				$html .= "</div>";
								
			}

			if ($count == $limit_count) {
				$html .= "<a href='javascript:void(0)' id='friends' class='showmore_wall' title='".($limit_from+$limit_count)."'>".__("more...", "wp-symposium")."</a>";
			}
			
		} else {
			$html .= __("Nothing to show, sorry.", "wp-symposium");
		}
		
	} else {

		if (strtolower($privacy) == 'friends only') {
			$html .=  __("Personal information only for friends.", "wp-symposium");
		}
		if (strtolower($privacy) == 'nobody') {
			$html .= __("Personal information is private.", "wp-symposium");
		}

	}						

	return $html;
	
}

function symposium_profile_header($uid1, $uid2, $url, $display_name) {
	
	global $wpdb, $current_user;
	$plugin = WP_PLUGIN_URL.'/wp-symposium';

	$html = str_replace("[]", "", stripslashes(get_option('symposium_template_profile_header')));

	$privacy = get_symposium_meta($uid1, 'share');

	$html = str_replace("[display_name]", $display_name, $html);
	
	// Follow/Unfollow
	if (function_exists('symposium_profile_plus') && is_user_logged_in() && $uid1 != $uid2) {
		if (symposium_is_following($uid2, $uid1)) {
			$html = str_replace("[follow]", '<input type="submit" ref="unfollow" value="'.__('Unfollow', 'wp-symposium').'" class="symposium-button follow-button">', $html);
		} else {
			$html = str_replace("[follow]", '<input type="submit" ref="follow" value="'.__('Follow', 'wp-symposium').'" class="symposium-button follow-button">', $html);
		}
	} else {
		$html = str_replace("[follow]", '', $html);
	}

	// Poke
	if (get_option('symposium_use_poke') == 'on' && is_user_logged_in() && $uid1 != $uid2) {
		$poke = "Poke";
		$html = str_replace("[poke]", '<input type="submit" value="'.get_option('symposium_poke_label').'" class="symposium-button poke-button">', $html);
	} else {
		$html = str_replace("[poke]", '', $html);
	}

	

	$location = "";
	$born = "";
	
	if ( ($uid1 == $uid2) || (is_user_logged_in() && strtolower($privacy) == 'everyone') || (strtolower($privacy) == 'public') || (strtolower($privacy) == 'friends only' && symposium_friend_of($uid1, $current_user->ID)) ) {
			
		$city = get_symposium_meta($uid1, 'extended_city');
		$country = get_symposium_meta($uid1, 'extended_country');
		
		if ($city != '') { $location .= $city; }
		if ($city != '' && $country != '') { $location .= ", "; }
		if ($country != '') { $location .= $country; }

		$day = (int)get_symposium_meta($uid1, 'dob_day');
		$month = get_symposium_meta($uid1, 'dob_month');
		$year = (int)get_symposium_meta($uid1, 'dob_year');

		if ($year > 0 || $month > 0 || $day > 0) {
			//if ($city != '' || $country != '') { $location .= ".<br />"; }
			$monthname = wps_get_monthname($month);
			if ($day == 0) $day = '';
			if ($year == 0) $year = '';
			$born = sprintf(__("Born %s %s %s", "wp-symposium"), $monthname, $day, $year);
		
		}
		
	} else {
	
		if (strtolower($privacy) == 'friends only') {
			$html = str_replace("[born]", __("Personal information only for friends.", "wp-symposium"), $html);						
		}

		if (strtolower($privacy) == 'nobody') {
			$html = str_replace("[born]", __("Personal information is private.", "wp-symposium"), $html);						
		}
		
	}

	$html = str_replace("[location]", $location, $html);
	if (get_option('symposium_show_dob') == 'on') {
		$html = str_replace("[born]", $born, $html);
	} else {
		$html = str_replace("[born]", "", $html);
	}
	
	if ( is_user_logged_in() ) {
		
		$actions = '';
		
		if ($uid1 == $uid2) {

			// Status Input
			$whatsup = stripslashes(get_option('symposium_status_label'));
			$whatsup = str_replace("'", "`", $whatsup);
			$actions .= '<input type="text" id="symposium_status" name="status" class="input-field" 
				onblur ="this.value=(this.value==\'\') ? \''.$whatsup.'\' : this.value;" 
				onfocus="this.value=(this.value==\''.$whatsup.'\') ? \'\' : this.value;" 
				value="'.$whatsup.'"';
			if (get_option('symposium_show_buttons')) {
				$actions .= ' style="width:250px"';
			}
			$actions .= ' />';
			if (get_option('symposium_show_buttons')) {
				$actions .= '&nbsp;<input id="symposium_add_update" type="submit" class="symposium-button" value="'.__('Update', 'wp-symposium').'" /> ';
			}
			if (function_exists('symposium_facebook')) {
				$actions .= "<div id='facebook_div'>";
				if ( $facebook_id = get_symposium_meta($uid2, 'facebook_id') != '') {
					$actions .= "<input type='checkbox' CHECKED id='post_to_facebook' /> ";
					$actions .= __("Post to Facebook", "wp-symposium");
					$actions .= " (<a href='javascript:void(0)' id='cancel_facebook'>".__("Cancel", "wp-symposium")."</a>)";
				} else {
					$actions .= "<img src='".WP_PLUGIN_URL."/wp-symposium/images/logo_facebook.png' style='float:left; margin-right: 5px;' />";
					$actions .= "<a href='javascript:void(0)' id='setup_facebook'>".__("Connect to Facebook", "wp-symposium")."</a>";
				}
				$actions .= "</div>";
			}
			
		} else {
									
			// Buttons									
			if (symposium_friend_of($uid1, $current_user->ID)) {

				// A friend
				// Send mail
				$actions .= '<input type="submit" class="symposium-button" id="profile_send_mail_button" value="'.__('Send Mail', 'wp-symposium').'">';
				
			} else {
				
				if (symposium_pending_friendship($uid1)) {
					// Pending
					$actions .= '<input type="submit" title="'.$uid1.'" id="cancelfriendrequest" class="symposium-button" value="'.__('Cancel Friend Request', 'wp-symposium').'" /> ';
					$actions .= '<div id="cancelfriendrequest_done" class="hidden">'.__('Friend Request Cancelled', 'wp-symposium').'</div>';
				} else {							
					// Not a friend
					$actions .= '<div id="addasfriend_done1_'.$uid1.'">';
					$actions .= '<span id="add_as_friend_title">'.__('Add as a Friend', 'wp-symposium').'...</span>';
					if (get_option('symposium_mail_all') == 'on') {
						$actions .= ' (<a href="javascript:void(0);" id="profile_send_mail_button" onclick="document.location = \''.$url.symposium_string_query($url).'view=compose&to='.$uid1.'\';">'.__('or send a private mail', 'wp-symposium').'</a>)';
					}
					$actions .= '<div id="add_as_friend_message">';
					$actions .= '<input type="text" title="'.$uid1.'"id="addfriend" class="input-field" onclick="this.value=\'\'" value="'.__('Add a personal message...', 'wp-symposium').'"';
					if (!get_option('symposium_show_buttons')) {
						$actions .= ' style="width:280px"';
					}
					$actions .= '>';
					if (get_option('symposium_show_buttons')) {
						$actions .= '<input type="submit" title="'.$uid1.'" id="addasfriend" class="symposium-button" value="'.__('Add', 'wp-symposium').'" /> ';
					}

					$actions .= '</div></div>';
					$actions .= '<div id="addasfriend_done2_'.$uid1.'" class="hidden">'.__('Friend Request Sent', 'wp-symposium').'</div>';
					
				}
			}
		}
				
		$html = str_replace("[actions]", $actions, $html);						
	} else {
		$html = str_replace("[actions]", "", $html);												
	}
	
	// Photo
	if (strpos($html, '[avatar') !== FALSE) {
		if (strpos($html, '[avatar]')) {
			$html = str_replace("[avatar]", get_avatar($uid1, 200), $html);						
		} else {
			$x = strpos($html, '[avatar');
			$y = strpos($html, ']', $x);
			$diff = $y-$x-8;
			$avatar = substr($html, 0, $x);
			$avatar2 = substr($html, $x+8, $diff);
			$avatar3 = substr($html, $x+$diff+9, strlen($html)-$x-($diff+9));
							
			$html = $avatar . get_avatar($uid1, $avatar2) . $avatar3;
			
			
		}
	}
	
	return $html;


}

function symposium_profile_body($uid1, $uid2, $post, $version, $limit_from) {
	
	global $wpdb, $current_user;

	$limit_count = 10; // How many new items should be shown

	$plugin = WP_PLUGIN_URL.'/wp-symposium';
	

//	if ($uid1 > 0) {
		
		if (get_option('symposium_use_styles') == "on") {
			$bg_color_2 = 'background-color: '.get_option('symposium_bg_color_2');
		} else {
			$bg_color_2 = '';
		}
		$privacy = ($uid1 > 0) ? get_symposium_meta($uid1, 'wall_share') : 'public';	
		
		$html = "";
			
		if (is_user_logged_in() || $privacy == 'public') {	
		
			$is_friend = ($uid1 > 0) ? symposium_friend_of($uid1, $current_user->ID) : false;
	
			if ( ($uid1 == $uid2) || (is_user_logged_in() && strtolower($privacy) == 'everyone') || (strtolower($privacy) == 'public') || (strtolower($privacy) == 'friends only' && $is_friend) || symposium_get_current_userlevel() == 5) {
			
				// Optional panel
				if (get_option('symposium_show_wall_extras') == "on" && $limit_from == 0 && version != 'stream_activity') {
						
						$html .= "<div id='profile_right_column'>";
	
						// Extended	
						$extended = get_symposium_meta($uid1, 'extended');
						$fields = explode('[|]', $extended);
						$has_extended_fields = false;
						if ($fields) {
							$ext_rows = array();
							foreach ($fields as $field) {
								$split = explode('[]', $field);
								if ( ($split[0] != '') && ($split[1] != '') ) {
								
									$extension = $wpdb->get_row($wpdb->prepare("SELECT extended_name,extended_order FROM ".$wpdb->prefix."symposium_extended WHERE eid = ".$split[0]));
									
									$ext = array (	'name'=>$extension->extended_name,
													'value'=>wpautop(symposium_make_url($split[1])),
													'order'=>$extension->extended_order );
									array_push($ext_rows, $ext);
									
									$has_info = true;
									$has_extended_fields = true;
								}
							}
							$ext_rows = sub_val_sort($ext_rows,'order');
							foreach ($ext_rows as $row) {
								$html .= "<div style='margin-bottom:0px;overflow: auto;'>";
								$html .= "<div style='font-weight:bold;'>".stripslashes($row['name'])."</div>";
								$html .= "<div>".wpautop(symposium_make_url(stripslashes($row['value'])))."</div>";
								$html .= "</div>";
							}
						}
						
															
						// Friends
						$has_friends = false;
						$html .= "<div class='profile_panel_friends_div'>";
				
							$sql = "SELECT f.*, cast(m.meta_value as datetime) as last_activity FROM ".$wpdb->base_prefix."symposium_friends f LEFT JOIN ".$wpdb->base_prefix."usermeta m ON m.user_id = f.friend_to WHERE f.friend_from = %d AND f.friend_accepted = 'on' AND m.meta_key = 'symposium_last_activity'ORDER BY cast(m.meta_value as datetime) DESC LIMIT 0,6";
							$friends = $wpdb->get_results($wpdb->prepare($sql, $uid1));
							
							if ($friends) {
								
								$inactive = get_option('symposium_online');
								$offline = get_option('symposium_offline');
								
								$html .= '<div class="profile_panel_friends_div_title">'.__('Recently Active Friends', 'wp-symposium').'</div>';
								foreach ($friends as $friend) {
									
									$time_now = time();
									$last_active_minutes = strtotime($friend->last_activity);
									$last_active_minutes = floor(($time_now-$last_active_minutes)/60);
																	
									$html .= "<div class='profile_panel_friends_div_row'>";		
										$html .= "<div class='profile_panel_friends_div_avatar'>";
											$html .= get_avatar($friend->friend_to, 42);
										$html .= "</div>";
										$html .= "<div>";
											$html .= symposium_profile_link($friend->friend_to)."<br />";
											$html .= __('Last active', 'wp-symposium').' '.symposium_time_ago($friend->last_activity).".";
										$html .= "</div>";
				
									$html .= "</div>";
								}
								
								$has_friends = true;
							}
													
						$html .= "</div>";
						
						if (!$has_extended_fields && !$has_friends) {
							$html .= __('Make friends and they will be listed here...', 'wp-symposium');
						}
	
					$html .= "</div>";
				
				}				
					
				// Wall
				
				// Filter for additional buttons
				if ($version == "wall") {
					$html = apply_filters ( 'symposium_profile_wall_header_filter', $html, $uid1, $uid2, $privacy, $is_friend, get_symposium_meta($uid1, 'extended') );
				}
				
				
				/* add activity stream */	
				$html .= symposium_activity_stream($uid1, $version, $limit_from, $limit_count, $post);
		
			} else {
	
				if ($version == "friends_activity") {
					$html .= '<p>'.__("Sorry, this member has chosen not to share their activity.", "wp-symposium");
				}
	
				if ($version == "wall") {
					$html .= '<p>'.__("Sorry, this member has chosen not to share their activity.", "wp-symposium");
				}
				
			}		
			return symposium_smilies($html);
			
		} else {

			return __("Please login to view this member's profile.", "wp-symposium");
			
		}
		
//	} else {
//		return '';		
//	}

}

function symposium_activity_stream($uid1='', $version='wall', $limit_from=0, $limit_count=10, $post='', $show_add_comment=true) {
	
	// version = stream_activity, friends_activity, all_activity
	// uid1 = the user's page (which we are looking at)
	// uid2 = the current user
	// $limit_from (starting post)
	// $limit_count (how many to show)
	// $post (individual activity post ID if applicable)
	
	global $wpdb,$current_user;
	if ($uid1 == '') $uid1 = $current_user->ID;
	$uid2 = $current_user->ID;

	// Get privacy level for this member's activity

	$privacy = get_symposium_meta($uid1, 'wall_share');
	
	$html = "";
		
	if (is_user_logged_in() || $privacy == 'public') {	
	
		$is_friend = ($uid1 > 0) ? symposium_friend_of($uid1, $current_user->ID) : false;	
		
		if ( ($uid1 == $uid2) || (is_user_logged_in() && strtolower($privacy) == 'everyone') || (strtolower($privacy) == 'public') || (strtolower($privacy) == 'friends only' && $is_friend) || symposium_get_current_userlevel() == 5) {

			$profile_page = symposium_get_url('profile');
			if ($profile_page[strlen($profile_page)-1] != '/') { $profile_page .= '/'; }
			$q = symposium_string_query($profile_page);	
			
			$html .= "<div id='symposium_wall'>";
		
				if ( 
					( 
					  ( ($version == 'stream_activity') && ($uid2 > 0) ) || 
					  ( 
					    ($limit_from == 0) && 
					    ($post == '') && 
					    ($uid1 != '') && 
					    ( ($uid1 != $uid2) || ($is_friend)) ) && (is_user_logged_in())
				     ) 
				   ) {
				       
					// Post Comment Input
					if ($show_add_comment) {
						$html .= '<input id="symposium_comment"  type="text" name="post_comment" class="input-field" onblur="this.value=(this.value==\'\') ? \''.__('Write a comment...', 'wp-symposium').'\' : this.value;" onfocus="this.value=(this.value==\''.__('Write a comment...', 'wp-symposium').'\') ? \'\' : this.value;" value="'.__('Write a comment...', 'wp-symposium').'"';
						if (get_option('symposium_show_buttons')) {
							$html .= ' style="width:200px"';
						}
						$html .= ' />';
						if (get_option('symposium_show_buttons')) {
							$html .= '&nbsp;<input id="symposium_add_comment" type="submit" style="width:75px" class="symposium-button" value="'.__('Post', 'wp-symposium').'" /> ';
						}
						$html .= '<br /><br />';
					}
				}
			
				if ($post != '') {
					$post_cid = 'c.cid = '.$post.' AND ';
				} else {
					$post_cid = '';
				}

				// Add groups join if in use
				if (function_exists('symposium_groups')) {
					$groups = "LEFT JOIN ".$wpdb->prefix."symposium_groups g ON c.subject_uid = g.gid";
					$group_field = ", g.content_private";
				} else {
					$groups = "";
					$group_field = ", 'on' as content_private";
				}
				
				if ($version == "all_activity" || $version == "stream_activity") {
					$sql = "SELECT c.*, u.display_name, u2.display_name AS subject_name" . $group_field . "   
					FROM ".$wpdb->base_prefix."symposium_comments c 
					LEFT JOIN ".$wpdb->base_prefix."users u ON c.author_uid = u.ID 
					LEFT JOIN ".$wpdb->base_prefix."users u2 ON c.subject_uid = u2.ID 
					" . $groups . "
					WHERE ".$post_cid." c.comment_parent = 0 
					ORDER BY c.comment_timestamp DESC LIMIT %d,%d";					
					$comments = $wpdb->get_results($wpdb->prepare($sql, $limit_from, 30));	
				}
			
				if ($version == "friends_activity") {
					$sql = "SELECT c.*, u.display_name, u2.display_name AS subject_name" . $group_field . " 
					FROM ".$wpdb->base_prefix."symposium_comments c 
					LEFT JOIN ".$wpdb->base_prefix."users u ON c.author_uid = u.ID 
					LEFT JOIN ".$wpdb->base_prefix."users u2 ON c.subject_uid = u2.ID 
					" . $groups . "
					WHERE ".$post_cid." (
					      ( (c.subject_uid = %d) OR (c.author_uid = %d)  
					   OR ( c.author_uid IN (SELECT friend_to FROM ".$wpdb->base_prefix."symposium_friends WHERE friend_from = %d)) ) AND c.comment_parent = 0 
				   	   OR ( 
				   	   		%d IN (SELECT author_uid FROM ".$wpdb->base_prefix."symposium_comments WHERE comment_parent = c.cid ) 
							AND ( c.author_uid IN (SELECT friend_to FROM ".$wpdb->base_prefix."symposium_friends WHERE friend_from = %d)) 
				   	   	  ) )
					ORDER BY c.comment_timestamp DESC LIMIT %d,%d";	
					$comments = $wpdb->get_results($wpdb->prepare($sql, $uid1, $uid1, $uid1, $uid1, $uid1, $limit_from, $limit_count));	
				}
			
				if ($version == "wall") {
			
					$sql = "SELECT c.*, u.display_name, u2.display_name AS subject_name" . $group_field . " 
							FROM ".$wpdb->base_prefix."symposium_comments c 
							LEFT JOIN ".$wpdb->base_prefix."users u ON c.author_uid = u.ID 
							LEFT JOIN ".$wpdb->base_prefix."users u2 ON c.subject_uid = u2.ID 
							" . $groups . "
							WHERE ".$post_cid."
							      ( (c.subject_uid = %d OR c.author_uid = %d) AND c.comment_parent = 0 )
						   	   OR ( %d IN (SELECT author_uid FROM ".$wpdb->base_prefix."symposium_comments WHERE comment_parent = c.cid ) )
							ORDER BY c.comment_timestamp DESC LIMIT %d,%d";
					$comments = $wpdb->get_results($wpdb->prepare($sql, $uid1, $uid1, $uid1, $limit_from, $limit_count));	
					
				}
							
				// Build wall
				if ($comments) {
					
					$cnt = 0;
					foreach ($comments as $comment) {
			
						$continue = true;
						if ($version == "friends_activity" && $comment->author_uid == $uid1 && $comment->subject_uid == $uid1) {
							$sql = "SELECT COUNT(*) FROM ".$wpdb->base_prefix."symposium_comments c 
									WHERE c.comment_parent = %d AND c.is_group != 'on'
									  AND c.author_uid != %d";
							if ($wpdb->get_var($wpdb->prepare($sql, $comment->cid, $uid1)) == 0) $continue = false;
						}
						
						if ($continue) {
							
							$cnt++;
						
							$privacy = get_symposium_meta($comment->author_uid, 'wall_share');
							
							if ( ($comment->subject_uid == $uid1) 
								|| ($comment->author_uid == $uid1) 
								|| (strtolower($privacy) == 'everyone' && $uid2 > 0) 
								|| (strtolower($privacy) == 'public') 
								|| (strtolower($privacy) == 'friends only' && (symposium_friend_of($comment->author_uid, $uid1) || (symposium_friend_of($comment->author_uid, $uid2) && $version == "stream_activity") ) ) 
								) {
									
								// If a group post and user is not the author we need to check privacy of group settings
								if ($comment->is_group == 'on' && $comment->author_uid != $uid2) {
									// If not private group, or a member, then display
									if ($comment->content_private != 'on' || symposium_member_of($comment->subject_uid) == 'yes') {
										$private_group = '';
									} else {
										// Otherwise hide
										$private_group = 'on';
									}
								} else {
									// Not a group post so not applicable
									$private_group = '';
								}
								
								if ($private_group != 'on') {
									
									// Increase shown count
									$shown_count++;
					
									// Check to avoid poke's (as private)								
									if ( ($comment->type != 'poke') || ($comment->type == 'poke' && ($comment->author_uid == $uid2 || $comment->subject_uid == $uid2 )) ) {
															
										$html .= "<div class='wall_post_div' id='post_".$comment->cid."'>";
							
											$html .= "<div class='wall_post_avatar'>";
												$html .= get_avatar($comment->author_uid, 64);
											$html .= "</div>";
							
											$html .= '<a href="'.$profile_page.$q.'uid='.$comment->author_uid.'">'.stripslashes($comment->display_name).'</a> ';
											if ($comment->author_uid != $comment->subject_uid && !$comment->is_group) {
												$html .= ' &rarr; <a href="'.$profile_page.$q.'uid='.$comment->subject_uid.'">'.stripslashes($comment->subject_name).'</a> ';
											}
											$html .= symposium_time_ago($comment->comment_timestamp).".";
											$html .= "<div style='width:60px; float:right;height:16px;'>";
											if (get_option('symposium_allow_reports') == 'on') {
												$html .= " <a title='post_".$comment->cid."' href='javascript:void(0);' class='report_post report_post_top symposium_report'><img src='".get_option('symposium_images')."/warning.png' style='width:16px;height:16px' /></a>";
											}
											if (symposium_get_current_userlevel() == 5 || $comment->subject_uid == $uid2 || $comment->author_uid == $uid2) {
												$html .= " <a title='".$comment->cid."' rel='post' href='javascript:void(0);' class='delete_post delete_post_top'><img src='".get_option('symposium_images')."/delete.png' style='width:16px;height:16px' /></a>";
											}
											$html .= '</div>';
											$html .= "<br />";
											
											// Always show reply fields or not?
											$show_class = (get_option('symposium_profile_comments')) ? '' : 'symposium_wall_replies';
											$show_field = (get_option('symposium_profile_comments')) ? '' : 'display:none;';
											
											$html .= '<div class="'.$show_class.'" id="'.$comment->cid.'">';
											if ($comment->is_group) {
												$url = symposium_get_url('group');
												$q = symposium_string_query($url);
												$url .= $q.'gid='.$comment->subject_uid.'&post='.$comment->cid;
												$group_name = $wpdb->get_var($wpdb->prepare("SELECT name FROM ".$wpdb->base_prefix."symposium_groups WHERE gid = %d", $comment->subject_uid));
												$html .= __("Group post in", "wp-symposium")." <a href='".$url."'>".$group_name."</a>: ".symposium_make_url(stripslashes($comment->comment));
											} else {
												$html .= symposium_make_url(stripslashes($comment->comment));
											}
							
											// Replies
											
											$sql = "SELECT c.*, u.display_name FROM ".$wpdb->base_prefix."symposium_comments c 
												LEFT JOIN ".$wpdb->base_prefix."users u ON c.author_uid = u.ID 
												LEFT JOIN ".$wpdb->base_prefix."symposium_comments p ON c.comment_parent = p.cid 
												WHERE c.comment_parent = %d AND c.is_group != 'on' ORDER BY c.cid";
							
											$replies = $wpdb->get_results($wpdb->prepare($sql, $comment->cid));	
							
											$count = 0;
											if ($replies) {
												if (count($replies) > 4) {
													$html .= "<div id='view_all_comments_div'>";
													$html .= "<a title='".$comment->cid."' class='view_all_comments' href='javascript:void(0);'>".__(sprintf("View all %d comments", count($replies)), "wp-symposium")."</a>";
													$html .= "</div>";
												}
												foreach ($replies as $reply) {
													$count++;
													if ($count > count($replies)-4) {
														$reply_style = "";
													} else {
														$reply_style = "display:none; ";
													}
													$html .= "<div id='".$reply->cid."' class='reply_div' style='".$reply_style."'>";
														$html .= "<div class='wall_reply_div'>";
															$html .= "<div class='wall_reply'>";
																$html .= '<a href="'.$profile_page.$q.'uid='.$reply->author_uid.'">'.stripslashes($reply->display_name).'</a> ';
																$html .= symposium_time_ago($reply->comment_timestamp).".";
																$html .= '<div style="width:50px; float:right;">';
																if (get_option('symposium_allow_reports') == 'on') {
																	$html .= " <a title='post_".$reply->cid."' href='javascript:void(0);' style='padding:0px' class='report_post symposium_report reply_warning'><img src='".get_option('symposium_images')."/warning.png' style='width:14px;height:14px' /></a>";
																}
																if (symposium_get_current_userlevel($uid2) == 5 || $reply->subject_uid == $uid2 || $reply->author_uid == $uid2) {
																	$html .= " <a title='".$reply->cid."' rel='reply' href='javascript:void(0);' style='padding:0px' class='delete_post delete_reply'><img src='".get_option('symposium_images')."/delete.png' style='width:14px;height:14px' /></a>";
																}
																$html .= '</div>';
																$html .= "<br />";
																$html .= symposium_make_url(stripslashes($reply->comment));
															$html .= "</div>";
														$html .= "</div>";
														
														$html .= "<div class='wall_reply_avatar'>";
															$html .= get_avatar($reply->author_uid, 40);
														$html .= "</div>";		
													$html .= "</div>";
												}
											} else {
												$html .= "<div class='no_wall_replies'></div>";
											}												
											$html .= "<div style='clear:both;' id='symposium_comment_".$comment->cid."'></div>";
							
											// Reply field
											if ( 
													(is_user_logged_in()) && 
													(
														($uid1 == $uid2) || 
														(
															strtolower($privacy) == 'everyone' || 
															strtolower($privacy) == 'public' || 
															(strtolower($privacy) == 'friends only' && $is_friend) || 
															($version = "stream_activity" && strtolower($privacy) == 'friends only' && symposium_friend_of($comment->author_uid, $current_user->ID))
														)
													)
												) 
											{
												$html .= '<div style="margin-top:5px;'.$show_field.'" id="symposium_reply_div_'.$comment->cid.'" >';
												$html .= '<input id="symposium_reply_'.$comment->cid.'" type="text" title="'.$comment->cid.'" class="input-field symposium_reply" onblur="this.value=(this.value==\'\') ? \''.__('Write a comment...', 'wp-symposium').'\' : this.value;" onfocus="this.value=(this.value==\''.__('Write a comment...', 'wp-symposium').'\') ? \'\' : this.value;" value="'.__('Write a comment...', 'wp-symposium').'"';
												if (get_option('symposium_show_buttons')) {
													$html .= ' style="width:150px"';
												}
												$html .= ' />';
												if (get_option('symposium_show_buttons')) {
													$html .= '&nbsp;<input title="'.$comment->cid.'" type="submit" style="width:75px" class="symposium-button symposium_add_reply" value="'.__('Add', 'wp-symposium').'" />';
												}
												$html .= '<input id="symposium_author_'.$comment->cid.'" type="hidden" value="'.$comment->subject_uid.'" />';
												$html .= '</div>';
											}
					
											$html .= "</div>";
												
										$html .= "</div>";
									
									}
									
								}
								
							} else {
								// Protected by privacy settings
							}	
						} // Comment by member with no replies and looking at friends activity
					}
					
					$id = 'wall';
					if ($version == "all_activity" || $version == "stream_activity") { $id='all'; }
					if ($version == "friends_activity") { $id='activity'; }
			
					if ($post == '' && $cnt > 0) {
						$html .= "<a href='javascript:void(0)' id='".$id."' class='showmore_wall' title='".($limit_from+$cnt+1)."'>".__("more...", "wp-symposium")."</a>";
					} else {
						if ($post == '') {
							$html .= "<br />".__("Nothing to show, sorry.", "wp-symposium");
						}
					}
						
				} else {
					$html .= "<br />".__("Nothing to show, sorry.", "wp-symposium");
				}
			
			$html .= "</div>";

			} else {

			if ($version == "friends_activity") {
				$html .= '<p>'.__("Sorry, this member has chosen not to share their activity.", "wp-symposium");
			}

			if ($version == "wall") {
				$html .= '<p>'.__("Sorry, this member has chosen not to share their activity.", "wp-symposium");
			}
			
		}		
		return symposium_smilies($html);
		
	} else {

		return __("Please login to view this member's profile.", "wp-symposium");
		
	}
		
	return $html;
}

function symposium_safe_param($param) {
	$return = true;
	
	if (is_numeric($param) == FALSE) { $return = false; }
	if (strpos($param, ' ') != FALSE) { $return = false; }
	if (strpos($param, '%20') != FALSE) { $return = false; }
	if (strpos($param, ';') != FALSE) { $return = false; }
	if (strpos($param, '<script>') != FALSE) { $return = false; }
	
	return $return;
}

function symposium_pagination($total, $current, $url) {
	
	$r = '';

	$r .= '<div class="tablenav"><div class="tablenav-pages">';
	for ($i = 0; $i < $total; $i++) {
		if ($i == $current) {
            $r .= "<b>".($i+1)."</b> ";
        } else {
        	if ( ($i == 0) || ($i == $total-1) || ($i+1 == $current) || ($i+1 == $current+2) ) {
	            $r .= " <a href='".$url.($i+1)."'>".($i+1)."</a> ";
        	} else {
        		$r .= "...";
        	}
        }
	}
	$r .= '</div></div>';
	
	while ( strpos($r, "....") > 0) {
		$r = str_replace("....", "...", $r);
	}
	
	if ($i == 1) {
		return '';
	} else {
		return $r;
	}
}

function symposium_pending_friendship($uid) {
   	global $wpdb, $current_user;
	wp_get_current_user();
	
	$sql = "SELECT * FROM ".$wpdb->base_prefix."symposium_friends WHERE (friend_accepted != 'on') AND (friend_from = %d AND friend_to = %d OR friend_to = %d AND friend_from = %d)";
	
	if ( $wpdb->get_var($wpdb->prepare($sql, $uid, $current_user->ID, $uid, $current_user->ID)) ) {
		return true;
	} else {
		return false;
	}

}

function symposium_friend_of($from, $to) {
   	global $wpdb, $current_user;
	wp_get_current_user();
	
	if ( $wpdb->get_var($wpdb->prepare("SELECT * FROM ".$wpdb->base_prefix."symposium_friends WHERE (friend_accepted = 'on') AND (friend_from = ".$from." AND friend_to = ".$to.")")) ) {
		return true;
	} else {
		return false;
	}

}

function symposium_is_following($uid, $following) {
   	global $wpdb, $current_user;
	wp_get_current_user();
	
	if ( $wpdb->get_var($wpdb->prepare("SELECT * FROM ".$wpdb->base_prefix."symposium_following WHERE uid = ".$uid." AND following = ".$following)) ) {
		return true;
	} else {
		return false;
	}

}
	

function symposium_get_current_userlevel() {

   	global $wpdb, $current_user;
	wp_get_current_user();

	// Work out user level
	$user_level = 0; // Guest
	if (is_user_logged_in()) { $user_level = 1; } // Subscriber
	if (current_user_can('edit_posts')) { $user_level = 2; } // Contributor
	if (current_user_can('edit_published_posts')) { $user_level = 3; } // Author
	if (current_user_can('moderate_comments')) { $user_level = 4; } // Editor
	if (current_user_can('activate_plugins')) { $user_level = 5; } // Administrator
	
	return $user_level;

}

function symposium_get_url($plugin) {
	
	global $wpdb;
	$return = false;
	if ($plugin == 'mail' && function_exists('symposium_mail')) {
		$return = get_option('symposium_mail_url');
	}
	if ($plugin == 'forum' && function_exists('symposium_forum')) {
		$return = get_option('symposium_forum_url');
	}
	if ($plugin == 'profile') {
		$return = get_option('symposium_profile_url');
	}
	if ($plugin == 'avatar') {
		$return = WPS_AVATAR_URL;
	}
	if ($plugin == 'members' && function_exists('symposium_members')) {
		$return = get_option('symposium_members_url');
	}
	if ($plugin == 'groups' && function_exists('symposium_group')) {
		$return = get_option('symposium_groups_url');
	}
	if ($plugin == 'group' && function_exists('symposium_group')) {
		$return = get_option('symposium_group_url');
	}
	if ($plugin == 'gallery' && function_exists('symposium_gallery')) {
		$return = get_option('symposium_gallery_url');
	}
	if ($return == false) {
		$return = "INVALID PLUGIN URL REQUESTED (".$plugin.")";
	}
	if ($return[strlen($return)-1] == '/') { $return = substr($return,0,-1); }

	return get_bloginfo('url').$return;
	
}

function page_has_wps_shortcode(array $shortcodes) {
	global $wpdb;
	$found = false;
	foreach ($shortcodes AS $shortcode) {
		$sql = "SELECT ID FROM ".$wpdb->prefix."posts WHERE post_type = 'page' AND post_status = 'publish' AND post_content like '[".$shortcode."]'";
		$ID = $wpdb->get_var($wpdb->prepare($sql, $shortcode));
		echo $wpdb->last_query;
		if ($ID) { $found = true; }
	}
	return $found;
}

function symposium_alter_table($table, $action, $field, $format, $null, $default) {
	
	if ($action == "MODIFY") { $action = "MODIFY COLUMN"; }
	if ($default != "") { $default = "DEFAULT ".$default; }

	global $wpdb;	
	
	$success = false;

	$check = '';
	$res = mysql_query("DESCRIBE ".$wpdb->prefix."symposium_".$table);
	while($row = mysql_fetch_array($res)) {	
		if ($row['Field'] == $field) { 
			$check = 'exists';
		}
	}
		
	if ($action == "ADD" && $check == '') {
		if ($format != 'text') {
		  	$wpdb->query("ALTER TABLE ".$wpdb->prefix."symposium_".$table." ".$action." ".$field." ".$format." ".$null." ".$default);
		} else {
		  	$wpdb->query("ALTER TABLE ".$wpdb->prefix."symposium_".$table." ".$action." ".$field." ".$format);
		}
	}

	if ($action == "MODIFY COLUMN") {
		if ($format != 'text') {
			$sql = "ALTER TABLE ".$wpdb->prefix."symposium_".$table." ".$action." ".$field." ".$format." ".$null." ".$default;
		} else {
			$sql = "ALTER TABLE ".$wpdb->prefix."symposium_".$table." ".$action." ".$field." ".$format;
		}
	  	$wpdb->query($sql);
	}
	
	if ($action == "DROP") {
		$sql = "ALTER TABLE ".$wpdb->prefix."symposium_".$table." DROP ".$field;
	  	$wpdb->query($sql);
	}
	
	return $success;

}

// Updates user meta, and if not yet created, will create it
function update_symposium_meta($uid, $meta, $value) {

	global $wpdb;

	if ($meta != 'profile_avatar') {

		// strip quotes from older version of WPS
		if (is_string($value) && substr($value, 0, 1) == "'") {
			$value = substr($value, 1, strlen($value)-2);
		}
		
		// create if not yet there
		if ($wpdb->get_var($wpdb->prepare("SELECT meta_key FROM ".$wpdb->base_prefix."usermeta WHERE meta_key = 'symposium_extended_city' AND user_id = ".$uid)) != 'symposium_extended_city') create_wps_usermeta($uid);

		// check if linked
		$slug = str_replace('extended_', '', $meta);
		$link = $wpdb->get_row($wpdb->prepare("SELECT extended_type, wp_usermeta FROM ".$wpdb->base_prefix."symposium_extended WHERE extended_slug = %s", $slug));
		if ($link->wp_usermeta) {
			if ($link->extended_type == 'Checkbox') $value = ($value=='on') ? 'true' : 'false';
			update_user_meta($uid, $link->wp_usermeta, $value);
		} else {
			update_user_meta($uid, 'symposium_'.$meta, $value);
		}
		return true;
		
	} else {
			
		if ($value == '') { $value = "''"; }
		
		// check if exists, and create record if not
		// only update is to profile_avatar so can check this directly
		if (!$wpdb->get_var($wpdb->prepare("SELECT profile_avatar FROM ".$wpdb->base_prefix."symposium_usermeta WHERE uid = ".$uid))) {
			$wpdb->insert($wpdb->base_prefix.'symposium_usermeta', array( 'uid' => $uid ) );
		}
				
		// now update value
		if (is_string($value)) {
			$type = '%s';
			if (substr($value, 0, 1) == "'") {
				$value = substr($value, 1, strlen($value)-2);
			}
		} else {
			$type = '%d';
		}
		
		$r = ($wpdb->update( $wpdb->base_prefix.'symposium_usermeta', 
			array( $meta => $value ), 
			array( 'uid' => $uid ), 
			array( $type ), 
			array( '%d' )
			));
			
	  	return $r;
	  	
	}
}

// Get user meta data, and create if not yet available
function get_symposium_meta($uid, $meta, $legacy=false) {

	global $wpdb;

	if (!$legacy && $meta != 'profile_avatar') {

		// create if not yet there
		if ($wpdb->get_var($wpdb->prepare("SELECT meta_key FROM ".$wpdb->base_prefix."usermeta WHERE meta_key = 'symposium_extended_city' AND user_id = ".$uid)) != 'symposium_extended_city') create_wps_usermeta($uid);

		// check if linked
		$slug = str_replace('extended_', '', $meta);
		$link = $wpdb->get_row($wpdb->prepare("SELECT extended_type, wp_usermeta FROM ".$wpdb->base_prefix."symposium_extended WHERE extended_slug = %s", $slug));
		if ($link->wp_usermeta) {
			$value = get_user_meta($uid, $link->wp_usermeta, true);
			if ($link->extended_type == 'Checkbox') $value = ($value=='true') ? 'on' : '';
		} else {
			$value = get_user_meta($uid, 'symposium_'.$meta, true);
		}
		
		return $value;

	} else {
		
		// create if not yet there
		if ($wpdb->get_var($wpdb->prepare("SELECT meta_key FROM ".$wpdb->base_prefix."usermeta WHERE meta_key = 'symposium_extended_city' AND user_id = ".$uid)) != 'symposium_extended_city') create_wps_usermeta($uid);

		if ($meta == 'extended_city') $meta = 'city';
		if ($meta == 'extended_country') $meta = 'country';
		if ($value = $wpdb->get_var($wpdb->prepare("SELECT ".$meta." FROM ".$wpdb->base_prefix.'symposium_usermeta'." WHERE uid = ".$uid)) ) {
			return $value;
		} else {
			return false; 	
		}
		
	}
}


function create_wps_usermeta($uid) {

	if ($uid > 0) {
		
		global $wpdb;

		// insert initial friend(s) if set
		$initial_friend = get_option('symposium_initial_friend');
		if ( ($initial_friend != '') && ($initial_friend != '0') ) {

			$list = explode(',', $initial_friend);

			foreach ($list as $new_friend) {

			   if ($new_friend != $uid) {
				$wpdb->query( $wpdb->prepare( "
				INSERT INTO ".$wpdb->base_prefix."symposium_friends
					( 	friend_from, 
						friend_to,
						friend_timestamp,
						friend_message
					)
				VALUES ( %d, %d, %s, %s )", 
				        array(
			        	$new_friend, 
			        	$uid,
		        		date("Y-m-d H:i:s"),
			        	''
		        		) 
			        ) );
			   }
			}
		}
		
		// add to initial groups if set
		$initial_groups = get_option('symposium_initial_groups');
		if ( ($initial_groups != '') && ($initial_groups != '0') ) {

			$list = explode(',', $initial_groups);

			foreach ($list as $new_group) {

				// Add membership
				$wpdb->query( $wpdb->prepare( "
					INSERT INTO ".$wpdb->prefix."symposium_group_members
					( 	group_id, 
						member_id,
						admin,
						valid,
						joined
					)
					VALUES ( %d, %d, %s, %s, %s )", 
			        array(
			        	$new_group, 
			        	$uid, 
			        	'',
			        	'on',
			        	date("Y-m-d H:i:s")
			        	) 
			        ) );
			        
			}
		}

		// add default forum categories subscriptions
		$initial_forums = get_option('symposium_wps_default_forum');
		if ( ($initial_forums != '') && ($initial_forums != '0') ) {

			$list = explode(',', $initial_forums);

			foreach ($list as $new_sub) {

				// Add subscription
				$wpdb->query( $wpdb->prepare( "
					INSERT INTO ".$wpdb->prefix."symposium_subs
					( 	uid, 
						tid,
						cid
					)
					VALUES ( %d, %d, %d )", 
			        array(
			        	$uid, 
			        	0, 
			        	$new_sub
			        	) 
			        ) );
			        
			}
		}
		
		// insert user meta
		update_user_meta($uid, 'symposium_forum_digest', 'on');
		update_user_meta($uid, 'symposium_notify_new_messages', 'on');
		update_user_meta($uid, 'symposium_notify_new_wall', 'on');
		update_user_meta($uid, 'symposium_extended_city', null);
		update_user_meta($uid, 'symposium_extended_country', null);
		update_user_meta($uid, 'symposium_dob_day', null);
		update_user_meta($uid, 'symposium_dob_month', null);
		update_user_meta($uid, 'symposium_dob_year', null);
		update_user_meta($uid, 'symposium_share', 'Friends only');
		update_user_meta($uid, 'symposium_last_activity', null);
		update_user_meta($uid, 'symposium_status', '');
		update_user_meta($uid, 'symposium_visible', 'on');
		update_user_meta($uid, 'symposium_wall_share', 'Friends only');
		update_user_meta($uid, 'symposium_widget_voted', '');
		update_user_meta($uid, 'symposium_profile_photo', '');
		update_user_meta($uid, 'symposium_forum_favs', null);
		update_user_meta($uid, 'symposium_trusted', '');
		update_user_meta($uid, 'symposium_facebook_id', '');
		update_user_meta($uid, 'symposium_last_login', null);
		update_user_meta($uid, 'symposium_previous_login', null);
		update_user_meta($uid, 'symposium_forum_all', '');
		update_user_meta($uid, 'symposium_signature', '');
		update_user_meta($uid, 'symposium_rss_share', '');
		update_user_meta($uid, 'symposium_plus_lat', 0);
		update_user_meta($uid, 'symposium_plus_long', 0);
		
		// Hook for further action to take place after the creation of a user, such as update metadata with different values...
		do_action('symposium_create_user_hook', $uid);
	}
}


// Display array contents (for debugging only)
function symposium_displayArrayContentFunction($arrayname,$tab="&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp",$indent=0) {
 $curtab ="";
 $returnvalues = "";
 while(list($key, $value) = each($arrayname)) {
  for($i=0; $i<$indent; $i++) {
   $curtab .= $tab;
   }
  if (is_array($value)) {
   $returnvalues .= "$curtab$key : Array: <br />$curtab{<br />\n";
   $returnvalues .= symposium_displayArrayContentFunction($value,$tab,$indent+1)."$curtab}<br />\n";
   }
  else $returnvalues .= "$curtab$key => $value<br />\n";
  $curtab = NULL;
  }
 return $returnvalues;
}

// Link to profile if plugin activated
function symposium_profile_link($uid) {
	global $wpdb;

	$display_name = $wpdb->get_var($wpdb->prepare("SELECT display_name FROM ".$wpdb->base_prefix."users WHERE ID = ".$uid));
	if (function_exists('symposium_profile')) {

		$profile_url = symposium_get_url('profile');
		if (strpos($profile_url, '?') !== FALSE) {
			$q = "&";
		} else {
			$q = "?";
		}
				
		$html = '<a href="'.$profile_url.$q.'uid='.$uid.'">'.$display_name.'</a>';
	} else {
		$html = $display_name;
	}
	return $html;
}

// Work out query extension
function symposium_string_query($p) {
	if (strpos($p, '?') !== FALSE) { 
		$q = "&"; // No Permalink
	} else {
		$q = "?"; // Permalink
	}
	return $q;
}


// How long ago as text
function symposium_time_ago($date,$granularity=1) {
	
	$retval = '';
    $date = strtotime($date);
    $difference = (time() - $date) + 1;
    $periods = array(__('decade', 'wp-symposium') => 315360000,
        'year' => 31536000,
        'month' => 2628000,
        'week' => 604800, 
        'day' => 86400,
        'hour' => 3600,
        'minute' => 60,
        'second' => 1);

	if ($difference > 315360000) {

	    $return = sprintf (__('a while ago', 'wp-symposium'), $retval);
		
	} else {
		
		if ($difference < 1) {
			
		    $return = sprintf (__('just now', 'wp-symposium'), $retval);
		    
		} else {
                                 
		    foreach ($periods as $key => $value) {
		        if ($difference >= $value) {
		            $time = floor($difference/$value);
		            $difference %= $value;
		            $retval .= ($retval ? ' ' : '').$time.' ';
		            $key = (($time > 1) ? $key.'s' : $key);
		            if ($key == 'year') { $key = __('year', 'wp-symposium'); }
		            if ($key == 'years') { $key = __('years', 'wp-symposium'); }
		            if ($key == 'month') { $key = __('month', 'wp-symposium'); }
		            if ($key == 'months') { $key = __('months', 'wp-symposium'); }
		            if ($key == 'week') { $key = __('week', 'wp-symposium'); }
		            if ($key == 'weeks') { $key = __('weeks', 'wp-symposium'); }
		            if ($key == 'day') { $key = __('day', 'wp-symposium'); }
		            if ($key == 'days') { $key = __('days', 'wp-symposium'); }
		            if ($key == 'hour') { $key = __('hour', 'wp-symposium'); }
		            if ($key == 'hours') { $key = __('hours', 'wp-symposium'); }
		            if ($key == 'minute') { $key = __('minute', 'wp-symposium'); }
		            if ($key == 'minutes') { $key = __('minutes', 'wp-symposium'); }
		            if ($key == 'second') { $key = __('second', 'wp-symposium'); }
		            if ($key == 'seconds') { $key = __('seconds', 'wp-symposium'); }
		            $retval .= $key;
		            $granularity--;
		        }
		        if ($granularity == '0') { break; }
		    }

		    $return = sprintf (__('%s ago', 'wp-symposium'), $retval);
		    
		}
    

	}
    return $return;


}


// Send email
function symposium_sendmail($email, $subject, $msg)
{
	global $wpdb;

	$crlf = PHP_EOL;
	
	// get footer
	$footer = stripslashes(get_option('symposium_footer'));

	// get template
	$template = get_option('symposium_template_email');
	$template = str_replace("[]", "", stripslashes($template));

	// Body Filter
	$msg = apply_filters ( 'symposium_email_body_filter', $msg );

	$template =  str_replace('[message]', $msg, $template);
	$template =  str_replace('[footer]', $footer, $template);
	$template =  str_replace('[powered_by_message]', __('Powered by WP Symposium - Social Networking for WordPress', 'wp-symposium'), $template);
	$template =  str_replace('[version]', WPS_VER, $template);

	$template = str_replace(chr(10), "<br />", $template);
	
	
	// To send HTML mail, the Content-type header must be set
	$headers = "MIME-Version: 1.0" . $crlf;
	$headers .= "Content-type:text/html;charset=utf-8" . $crlf;

	if (strpos($subject, '#TID') === FALSE){
		$from_email = trim(get_option('symposium_from_email'));
		$from_name = html_entity_decode(trim(stripslashes(get_bloginfo('name'))), ENT_QUOTES, 'UTF-8');
	} else {
		$from_email = trim(get_option('symposium_mailinglist_from'));
		$from_name = html_entity_decode(trim(stripslashes(get_bloginfo('name'))), ENT_QUOTES, 'UTF-8').' '.__('Forum', 'wp-symposium');
	}
	
	if ($from_email == '') { 
		// $from_email = "noreply@".get_bloginfo('url'); // old version
		preg_match('@^(?:http://)?([^/]+)@i', get_bloginfo('url'), $matches); 
		preg_match('/[^.]+\.[^.]+$/', $matches[1], $matches);
		$from_email = "noreply@" . $matches[0];
	}	
	$headers .= "From: " . $from_name . " <" . $from_email . ">" . $crlf;
		
	// Header Filter
	$headers = apply_filters ( 'symposium_email_header_filter', $headers );
	
	// finally send mail
	if (wp_mail($email, stripslashes($subject), $template, $headers))
	{
		return true;
	} else {
		return false;
	}

}

// Function to turn a mysql datetime (YYYY-MM-DD HH:MM:SS) into a unix timestamp 

function convert_datetime($str) { 

	if ($str != '' && $str != NULL) {
		list($date, $time) = explode(' ', $str); 
		list($year, $month, $day) = explode('-', $date); 
		list($hour, $minute, $second) = explode(':', $time); 		
		$timestamp = mktime($hour, $minute, $second, $month, $day, $year); 
     } else {
		$timestamp = 999999999;
	 }
    return $timestamp; 
} 

function powered_by_wps() {

	global $wpdb;

	$template = get_option('symposium_template_page_footer');
	$template = str_replace("[]", "", stripslashes($template));
	
	$template =  str_replace('[powered_by_message]', __('Powered by WP Symposium - Social Networking for WordPress', 'wp-symposium'), $template);
	$template =  str_replace('[version]', WPS_VER, $template);

	return $template;
	
}

// Groups

function get_group_avatar($gid, $size) {


	global $wpdb, $blog_id;

	if (get_option('symposium_img_db') == "on") {
	
		$sql = "SELECT group_avatar FROM ".$wpdb->prefix."symposium_groups WHERE gid = %d";
		$group_photo = $wpdb->get_var($wpdb->prepare($sql, $gid));

		if ($group_photo == '' || $group_photo == 'upload_failed') {
			return "<img src='".get_option('symposium_images')."/unknown.jpg' style='height:".$size."px; width:".$size."px;' />";
		} else {
			return "<img src='".WP_CONTENT_URL."/plugins/wp-symposium/uploadify/get_group_avatar.php?gid=".$gid."' style='width:".$size."px; height:".$size."px' />";
		}
		
		return $html;
		
	} else {

		$sql = "SELECT profile_photo FROM ".$wpdb->prefix."symposium_groups WHERE gid = %d";
		$profile_photo = $wpdb->get_var($wpdb->prepare($sql, $gid));

		if ($profile_photo == '' || $profile_photo == 'upload_failed') {
			return "<img src='".get_option('symposium_images')."/unknown.jpg' style='height:".$size."px; width:".$size."px;' />";
		} else {
			if ($blog_id > 1) {
				$img_url = get_option('symposium_img_url')."/".$blog_id."/groups/".$gid."/profile/";	
			} else {
				$img_url = get_option('symposium_img_url')."/groups/".$gid."/profile/";	
			}
			$img_src =  str_replace('//','/',$img_url) . $profile_photo;
			return "<img src='".$img_src."' style='width:".$size."px; height:".$size."px' />";
		}
		
	}
	
	exit;
	
}

function symposium_member_of($gid) {
	
	global $wpdb, $current_user;

	$sql = "SELECT valid FROM ".$wpdb->prefix."symposium_group_members   
	WHERE group_id = %d AND member_id = %d";
	$members = $wpdb->get_results($wpdb->prepare($sql, $gid, $current_user->ID));
	
	if (!$members) {
		return "no";
	} else {
		$member = $members[0];
		if ($member->valid == "on") {
			return "yes";
		} else {
			return "pending";
		}
	}

}

function symposium_group_admin($gid) {
	
	global $wpdb, $current_user;

	$sql = "SELECT admin FROM ".$wpdb->prefix."symposium_group_members   
	WHERE group_id = %d AND member_id = %d";
	$admin = $wpdb->get_var($wpdb->prepare($sql, $gid, $current_user->ID));
	
	if ($admin) {
		if ($admin == "on") {
			return true;
		} else {
			return false;
		}
	} else {
		return false;
	}
	
}

/* Get site URL with protocol */
function siteURL()
{
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $domainName = $_SERVER['HTTP_HOST'];
    return $protocol.$domainName;
}

/* Function to sort multi-dimensional arrays */
/* For sort use asort or arsort              */
function sub_val_sort($a,$subkey,$asc=true) {
	if (count($a)) {
		foreach($a as $k=>$v) {
			$b[$k] = strtolower($v[$subkey]);
		}
		if ($asc) {
			asort($b);
		} else {
			arsort($b);
		}
		foreach($b as $key=>$val) {
			$c[] = $a[$key];
		}
		return $c;
	} else {
		return $a;
	}
}

// Add index to table
function symposium_add_index($table_name, $key_name, $parameter = "") {
	global $wpdb;
	if(!$wpdb->get_results("SHOW INDEX FROM ".$table_name." WHERE Key_name = '".$key_name."_index'")) {
		$wpdb->query("CREATE ".$parameter." INDEX ".$key_name."_index ON ".$table_name."(".$key_name.")");
	}
}


// **********************************************************************************
// FUNCTIONS SHARED BETWEEN AJAX AND NON-AJAX VERSIONS OF WIDGETS
// **********************************************************************************

// Recently active members
function do_recent_Widget($symposium_recent_count,$symposium_recent_desc,$symposium_recent_show_light,$symposium_recent_show_mail) {
		
	global $wpdb, $current_user;
	
	$html = '';

	// Content of widget
	$sql = "SELECT u.ID, u.display_name, cast(m.meta_value as datetime) as last_activity 
		FROM ".$wpdb->base_prefix."users u 
		LEFT JOIN ".$wpdb->base_prefix."usermeta m ON u.ID = m.user_id
		WHERE m.meta_key = 'symposium_last_activity'
		ORDER BY cast(m.meta_value as datetime) DESC LIMIT 0,".$symposium_recent_count;

	$members = $wpdb->get_results($wpdb->prepare($sql, $current_user->ID));
		
	if ($members) {

		$mail_url = symposium_get_url('mail');
		$profile_url = symposium_get_url('profile');
		$q = symposium_string_query($mail_url);
		$time_now = time();

		$html .= "<div id='symposium_new_members'>";
		
			$cnt = 0;
			foreach ($members as $member)
			{
				$last_active_minutes = strtotime($member->last_activity);
				$last_active_minutes = floor(($time_now-$last_active_minutes)/60);
				
				if ($symposium_recent_desc == 'on') {
					$html .= "<div class='symposium_new_members_row'>";		
						$html .= "<div class='symposium_new_members_row_avatar'>";
							$html .= "<a href='".$profile_url.$q."uid=".$member->ID."'>";
								$html .= get_avatar($member->ID, 32);
							$html .= "</a>";
						$html .= "</div>";
						$html .= "<div class='symposium_new_members_row_member'>";
							$html .= symposium_profile_link($member->ID)." ";
							if ($symposium_recent_show_light == 'on') {
								if ($last_active_minutes >= get_option('symposium_offline')) {
									$html .= '<img src="'.get_option('symposium_images').'/loggedout.gif"> ';
								} else {
									if ($last_active_minutes >= get_option('symposium_online')) {
										$html .= '<img src="'.get_option('symposium_images').'/inactive.gif"> ';
									} else {
										$html .= '<img src="'.get_option('symposium_images').'/online.gif"> ';
									}
								}
							}
							$html .= __('last active', 'wp-symposium')." ";
							$html .= symposium_time_ago($member->last_activity).".";
							if ($symposium_recent_show_mail == 'on' && symposium_friend_of($member->ID, $current_user->ID) ) {
								$html .= " <a title='".$member->display_name."' href='".$mail_url.$q."view=compose&to=".$member->ID."'>".__('Send Mail', 'wp-symposium')."</a>";
							}
						$html .= "</div>";
					$html .= "</div>";
				} else {
					$html .= "<a title='".$member->display_name."' style='padding-right:3px;padding-bottom:3px;float:left;cursor:pointer;' href='".$profile_url.$q."uid=".$member->ID."'>";
						$html .= get_avatar($member->ID, 32);
					$html .= "</a>";
				}
			}
			$html .= "</div>";				
	} else {
		$html .= "<div id='symposium_new_members'>";
		$html .= __("Nobody recently online.", "wp-symposium");
		$html .= "</div>";							
	}
		
	echo $html;
	
}

// New activity/status posts
function do_Recentactivity_Widget($postcount,$preview,$forum) {
	
	global $wpdb, $current_user;
	
	$shown_uid = "";
	$shown_count = 0;	
	$html = '';
	// Work out link to profile page, dealing with permalinks or not
	$profile_url = symposium_get_url('profile');
	$q = symposium_string_query($profile_url);
		
	// Content of widget
	$sql = "SELECT cid, author_uid, comment, comment_timestamp, display_name, type 
	FROM ".$wpdb->base_prefix."symposium_comments c 
	INNER JOIN ".$wpdb->base_prefix."users u ON c.author_uid = u.ID 
	WHERE is_group != 'on' 
	  AND comment_parent = 0 
	  AND author_uid = subject_uid ";
	if ($forum != 'on') { $sql .= "AND type != 'forum' "; }		  
	$sql .= "ORDER BY cid DESC LIMIT 0,50";
	
	$posts = $wpdb->get_results($sql);
			
	if ($posts) {

		$html .= "<div id='symposium_recent_activity'>";
			
			foreach ($posts as $post)
			{
				if ($shown_count < $postcount) {

					if (strpos($shown_uid, $post->author_uid.",") === FALSE) { 

						$share = get_symposium_meta($post->author_uid, 'wall_share');
						$is_friend = symposium_friend_of($post->author_uid, $current_user->ID);

						if ( (is_user_logged_in() && strtolower($share) == 'everyone') || (strtolower($share) == 'public') || (strtolower($share) == 'friends only' && $is_friend) ) {

							$html .= "<div class='symposium_recent_activity_row'>";		
								$html .= "<div class='symposium_recent_activity_row_avatar'>";
									$html .= get_avatar($post->author_uid, 32);
								$html .= "</div>";
								$html .= "<div class='symposium_recent_activity_row_post'>";
									$text = stripslashes($post->comment);
									if ($post->type == 'post') {
										$text = stripslashes($post->comment);
										$text = strip_tags($text);
										if ( strlen($text) > $preview ) { $text = substr($text, 0, $preview)."..."; }
									}
									if (($x = strpos($text, 'wps_comment_plus')) !== FALSE) {
										$text = substr($text, 0, $x-9);
									}
									$html .= "<a href='".$profile_url.$q."uid=".$post->author_uid."&post=".$post->cid."'>".$post->display_name."</a> ".$text." ".symposium_time_ago($post->comment_timestamp).".<br>";
								$html .= "</div>";
							$html .= "</div>";
						
							$shown_count++;
							$shown_uid .= $post->author_uid.",";							
						}
					}
				} else {
					break;
				}
			}

		$html .= "</div>";

	}
		
	echo $html;
}


// Newly joined members
function do_members_Widget($symposium_members_count) {
	
	global $wpdb, $current_user;
	
	$html = '';
	
	// Content of widget
	$members = $wpdb->get_results("
		SELECT * FROM ".$wpdb->base_prefix."users
		ORDER BY user_registered DESC LIMIT 0,".$symposium_members_count); 
	
	if ($members) {

		$html .= "<div id='symposium_new_members'>";

			foreach ($members as $member)
			{
				$html .= "<div class='symposium_new_members_row'>";		
					$html .= "<div class='symposium_new_members_row_avatar'>";
						$html .= get_avatar($member->ID, 32);
					$html .= "</div>";
					$html .= "<div class='symposium_new_members_row_member'>";
						$html .= symposium_profile_link($member->ID)." ".__('joined', 'wp-symposium')." ";
						$html .= symposium_time_ago($member->user_registered).".";
					$html .= "</div>";
				$html .= "</div>";
			}
			
			$html .= "</div>";				
	}
		
	echo $html;
}

// Show friends
function do_symposium_friends_Widget($symposium_friends_count,$symposium_friends_desc,$symposium_friends_mode,$symposium_friends_show_light,$symposium_friends_show_mail) {
	
	global $wpdb, $current_user;
	$html = '';

	// Content of widget
	$sql = "SELECT u.ID, u.display_name, cast(m.meta_value as datetime) as last_activity 
		FROM ".$wpdb->base_prefix."symposium_friends f
		LEFT JOIN ".$wpdb->base_prefix."users u ON f.friend_to = u.ID
		LEFT JOIN ".$wpdb->base_prefix."usermeta m ON f.friend_to = m.user_id
		WHERE f.friend_from = %d AND f.friend_accepted = 'on' 
		AND m.meta_key = 'symposium_last_activity'
		ORDER BY cast(m.meta_value as datetime) DESC LIMIT 0,".$symposium_friends_count;

	$members = $wpdb->get_results($wpdb->prepare($sql, $current_user->ID));
		
	if ($members) {

		$mail_url = symposium_get_url('mail');
		$profile_url = symposium_get_url('profile');
		$q = symposium_string_query($mail_url);
		$time_now = time();

		$html .= "<div id='symposium_new_members'>";
		
			if ($symposium_friends_mode == 'all' || $symposium_friends_mode == 'online') {
				$loop=1;
			} else {
				$loop=2;
			}
			for ($l=1; $l<=$loop; $l++) {
				
				if ($symposium_friends_mode == 'split') {
					if ($l==1) {
						$html .= '<div style="font-weight:bold">'.__('Online', 'wp-symposium').'</div>';
					} else {
						$html .= '<div style="clear:both;margin-top:6px;font-weight:bold">'.__('Offline', 'wp-symposium').'</div>';
					}
					
				}

				$cnt = 0;
				foreach ($members as $member)
				{
					$last_active_minutes = strtotime($member->last_activity);
					$last_active_minutes = floor(($time_now-$last_active_minutes)/60);
					
					$show = false;
					if ($symposium_friends_mode == 'online' && $last_active_minutes < get_option('symposium_offline')) { $show = true; }
					if ( ($symposium_friends_mode == 'split') && ( ($last_active_minutes < get_option('symposium_offline') && $l == 1) || ($last_active_minutes >= get_option('symposium_offline') && $l == 2) ) ) { $show = true; }
					if ($symposium_friends_mode == 'all') { $show = true; }
					
					if ($show) {
						$cnt++;								
						if ($symposium_friends_desc == 'on') {
							$html .= "<div class='symposium_new_members_row'>";		
								$html .= "<div class='symposium_new_members_row_avatar'>";
									$html .= "<a href='".$profile_url.$q."uid=".$member->ID."'>";
										$html .= get_avatar($member->ID, 32);
									$html .= "</a>";
								$html .= "</div>";
								$html .= "<div class='symposium_new_members_row_member'>";
									$html .= symposium_profile_link($member->ID)." ";
									if ($symposium_friends_show_light == 'on') {
										if ($last_active_minutes >= get_option('symposium_offline')) {
											$html .= '<img src="'.get_option('symposium_images').'/loggedout.gif"> ';
										} else {
											if ($last_active_minutes >= get_option('symposium_online')) {
												$html .= '<img src="'.get_option('symposium_images').'/inactive.gif"> ';
											} else {
												$html .= '<img src="'.get_option('symposium_images').'/online.gif"> ';
											}
										}
									}
									$html .= __('last active', 'wp-symposium')." ";
									$html .= symposium_time_ago($member->last_activity).".";
									if ($symposium_friends_show_mail == 'on') {
										$html .= " <a title='".$member->display_name."' href='".$mail_url.$q."view=compose&to=".$member->ID."'>".__('Send Mail', 'wp-symposium')."</a>";
									}
								$html .= "</div>";
							$html .= "</div>";
						} else {
							$html .= "<a title='".$member->display_name."' style='padding-right:3px;padding-bottom:3px;float:left;cursor:pointer;' href='".$profile_url.$q."uid=".$member->ID."'>";
								$html .= get_avatar($member->ID, 32);
							$html .= "</a>";
						}
					}
				}
				if ($cnt == 0) {
					$html .= __('Nobody', 'wp-symposium');
				}
			}
			
			$html .= "</div>";				
	} else {
		$html .= "<div id='symposium_new_members'>";
		$html .= __("No friends yet, add friends via their profile page.", "wp-symposium");
		$html .= "</div>";							
	}
	
	echo $html;
}

// Recent forum posts
function do_Forumrecentposts_Widget($postcount,$preview,$cat_id,$show_replies) {
	
	global $wpdb, $current_user;
	
	// Content of widget
	$sql = "SELECT tid, topic_subject, topic_owner, topic_post, topic_started, topic_category, topic_date, display_name, topic_parent, topic_group 
	FROM ".$wpdb->prefix.'symposium_topics'." t 
	INNER JOIN ".$wpdb->base_prefix.'users'." u ON t.topic_owner = u.ID 
	WHERE topic_approved = 'on' ";
	if ($cat_id != '' && $cat_id > 0) {
		$sql .= "AND topic_category = ".$cat_id." ";
	}
	if ($show_replies != 'on') {
		$sql .= "AND topic_parent = 0 ";
	}
	$sql .= "ORDER BY tid DESC LIMIT 0,100";
	$posts = $wpdb->get_results($sql); 
	$count = 0;
	$html = '';
	
	// Previous login
	if (is_user_logged_in()) {
		$previous_login = get_symposium_meta($current_user->ID, 'previous_login');
	}

	// Get forum URL worked out
	$forum_url = symposium_get_url('forum');
	$forum_q = symposium_string_query($forum_url);

	// Get list of roles for this user
    $user_roles = $current_user->roles;
    $user_role = strtolower(array_shift($user_roles));
    if ($user_role == '') $user_role = 'NONE';
    						
	if ($posts) {

		$html .= "<div id='symposium_latest_forum'>";
			
			foreach ($posts as $post)
			{
					if ($post->topic_group == 0 || (symposium_member_of($post->topic_group) == "yes") || ($wpdb->get_var($wpdb->prepare("SELECT content_private FROM ".$wpdb->prefix."symposium_groups WHERE gid = ".$post->topic_group)) != "on") ) {

						// Check permitted to see forum category
						$sql = "SELECT level FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d";
						$levels = $wpdb->get_var($wpdb->prepare($sql, $post->topic_category));
						$cat_roles = unserialize($levels);
						if (strpos(strtolower($cat_roles), 'everyone,') !== FALSE || strpos(strtolower($cat_roles), $user_role.',') !== FALSE) {
							
							$html .= "<div class='symposium_latest_forum_row'>";		
								$html .= "<div class='symposium_latest_forum_row_avatar'>";
									$html .= get_avatar($post->topic_owner, 32);
								$html .= "</div>";
								$html .= "<div class='symposium_latest_forum_row_post'>";
									if ($post->topic_parent > 0) {
										$html .= symposium_profile_link($post->topic_owner);
										if ($preview > 0) {
											$text = strip_tags(stripslashes($post->topic_post));
											if ( strlen($text) > $preview ) { $text = substr($text, 0, $preview)."..."; }
											$html .= " ".__('replied', 'wp-symposium')." <a href='".$forum_url.$forum_q."cid=".$post->topic_category."&show=".$post->topic_parent."'>".$text."</a>";
										} else {
											$html .= "<br />";
										}
										$html .= " ".symposium_time_ago($post->topic_date).".";
									} else {
										$html .= symposium_profile_link($post->topic_owner);
										if ($preview > 0) {
											$text = stripslashes($post->topic_subject);
											if ( strlen($text) > $preview ) { $text = substr($text, 0, $preview)."..."; }
											if ($post->topic_group == 0) {
												$url = $forum_url;
												$q = $forum_q;
											} else {
												// Get group URL worked out
												$url = symposium_get_url('group');
												if (strpos($url, '?') !== FALSE) {
													$q = "&gid=".$post->topic_group."&";
												} else {
													$q = "?gid=".$post->topic_group."&";
												}
											}
											$html .= " ".__('started', 'wp-symposium')." <a href='".$url.$q."cid=".$post->topic_category."&show=".$post->tid."'>".$text."</a>";
										}
										$html .= " ".symposium_time_ago($post->topic_started).".";
									}
										if (is_user_logged_in() && get_option('symposium_forum_stars')) {
											if ($post->topic_date > $previous_login && $post->topic_owner != $current_user->ID) {
												$html .= " <img src='".get_option('symposium_images')."/new.gif' alt='New!' />";
											}
										}
								$html .= "</div>";
							$html .= "</div>";
							
							$count++;
							if ($count >= $postcount) {
								break;
							}
						}
						
					}
			}

		$html .= "</div>";

	}
		
	echo $html;
}

// Summary/login widget
function do_symposium_summary_Widget($show_loggedout,$show_form,$login_url,$show_avatar) {
	
	global $wpdb,$current_user;
	
	// Content of widget
	echo "<div id='symposium_summary_widget'>";

	if (is_user_logged_in()) {

		// LOGGED IN
		echo "<ul style='list-style:none'>";

		// Link to profile page
		if (function_exists('symposium_profile')) {

			// Get mail URL worked out
			$profile_url = symposium_get_url('profile');

			echo "<li>";
				if ($show_avatar) {
					echo "<div id='symposium_summary_widget_avatar' style='float:left;margin-right:6px;'>";
					echo get_avatar($current_user->ID, 32);
					echo "</div>";
				}
				echo "<a href='".$profile_url."'>".$current_user->display_name."</a> ";
			echo "</li>";
		}
		
		// Mail
		if (function_exists('symposium_mail')) {

			// Get mail URL worked out
			$mail_url = symposium_get_url('mail');

			echo "<li id='symposium_summary_mail' style='clear:both'>";
				$total_mail = $wpdb->get_var($wpdb->prepare("SELECT count(*) FROM ".$wpdb->base_prefix."symposium_mail WHERE mail_to = ".$current_user->ID." AND mail_in_deleted != 'on'"));
				echo "<a href='".$mail_url."'>".__("Messages:", "wp-symposium")."</a> ".$total_mail;
				$unread_mail = $wpdb->get_var($wpdb->prepare("SELECT count(*) FROM ".$wpdb->base_prefix."symposium_mail WHERE mail_to = ".$current_user->ID." AND mail_in_deleted != 'on' AND mail_read != 'on'"));
				if ($unread_mail > 0) {
					echo " (".$unread_mail." ".__("unread","wp-symposium").")";
				}
			echo "</li>";
		}

		// Friends
		if (function_exists('symposium_profile')) {

			// Get mail URL worked out
			$friends_url = symposium_get_url('profile');
			if (strpos($friends_url, '?') !== FALSE) {
				$q = "&view=friends";
			} else {
				$q = "?view=friends";
			}

			echo "<li id='symposium_summary_profile'>";
				$sql = "SELECT count(*) FROM ".$wpdb->base_prefix."symposium_friends WHERE friend_to = %d AND friend_accepted = 'on'";
				$current_friends = $wpdb->get_var($wpdb->prepare($sql, $current_user->ID));
				echo  "<a href='".$friends_url.$q."'>".__("Friends:", "wp-symposium")."</a> ".$current_friends;
				$sql = "SELECT count(*) FROM ".$wpdb->base_prefix."symposium_friends WHERE friend_to = %d AND friend_accepted != 'on'";
				$friend_requests = $wpdb->get_var($wpdb->prepare($sql, $current_user->ID));

				if ($friend_requests == 1) {	
					echo " (".$friend_requests." ".__("request","wp-symposium").")";
				}
				if ($friend_requests > 1) {	
					echo " (".$friend_requests." ".__("requests","wp-symposium").")";
				}
			echo "</li>";

			// Hook for more list items
			do_action('symposium_widget_summary_hook_loggedin');

			if ( current_user_can('manage_options') ) {
				echo wp_register( "<li id='symposium_summary_dashboard'>", "</li>", true);
			}
			if ($show_loggedout == 'on') {
				echo "<li id='symposium_summary_logout'>";
				echo wp_loginout( get_bloginfo('url'), true );
				echo "</li>";
			}

		}

		echo "</ul>";
				
	} else {

		// LOGGED OUT

		// Hook for more list items
		do_action('symposium_widget_summary_hook_loggedout');

		if ($show_loggedout == 'on' && $show_form == '') {
			echo wp_loginout( get_permalink(), true);
			echo ' (<a href="'.wp_lostpassword_url( get_bloginfo('url') ).'" title="'.__('Forgot Password?', 'wp-symposium').'">'.__('Forgot Password?', 'wp-symposium').'</a>)<br />';
			echo wp_register( "", "", true);
		}

		if ($show_loggedout == 'on' && $show_form == 'on') {
			if ($login_url != '') {
				wp_login_form(array('redirect' => $login_url ));
			} else {
				wp_login_form(get_permalink());
			}
			echo '<a href="'.wp_lostpassword_url( get_bloginfo('url') ).'" title="'.__('Forgot Password?', 'wp-symposium').'">'.__('Forgot Password?', 'wp-symposium').'</a><br />';
			echo wp_register("", "", true);
		}
	
	}
		
	echo "</div>";	
	
}

// Forum posts with no answer
function do_Forumnoanswer_Widget($preview,$cat_id,$cat_id_exclude,$timescale,$postcount,$groups) {
	
	global $wpdb, $current_user;
	
	$html = '';

	// Previous login
	if (is_user_logged_in()) {
		$previous_login = get_symposium_meta($current_user->ID, 'previous_login');
	}
	
	// Content of widget
	
	$sql = "SELECT t.tid, t.topic_subject, t.topic_owner, t.topic_post, t.topic_category, t.topic_date, u.display_name, t.topic_parent, t.topic_group, t.topic_started, 
		(SELECT COUNT(*) FROM ".$wpdb->prefix."symposium_topics v WHERE v.topic_parent = t.tid) AS replies 
		FROM ".$wpdb->prefix."symposium_topics t 
		INNER JOIN ".$wpdb->base_prefix.'users'." u ON t.topic_owner = u.ID
		WHERE t.topic_parent = 0 
		  AND t.for_info != 'on' 
		  AND t.topic_approved = 'on' 
		  AND t.topic_started >= ( CURDATE() - INTERVAL ".$timescale." DAY ) 
		AND NOT EXISTS 
		  (SELECT tid from ".$wpdb->prefix."symposium_topics s 
		    WHERE s.topic_parent = t.tid AND s.topic_answer = 'on') ";
	if ($cat_id != '' && $cat_id > 0) {
		$sql .= "AND topic_category IN (".$cat_id.") ";
	}
	if ($cat_id_exclude != '' && $cat_id_exclude > 0) {
		$sql .= "AND topic_category NOT IN (".$cat_id_exclude.") ";
	}
	if ($groups != 'on') {
		$sql .= "AND topic_group = 0 ";
	}
	$sql .= "ORDER BY t.topic_started DESC LIMIT 0,".$postcount;
	$posts = $wpdb->get_results($sql); 
			
	// Get forum URL worked out
	$forum_url = symposium_get_url('forum');
	$forum_q = symposium_string_query($forum_url);

	// Get list of roles for this user
    $user_roles = $current_user->roles;
    $user_role = strtolower(array_shift($user_roles));
    if ($user_role == '') $user_role = 'NONE';
    							
	if ($posts) {

		$html .= "<div id='symposium_latest_forum'>";
			
			foreach ($posts as $post)
			{
					if ($post->topic_group == 0 || (symposium_member_of($post->topic_group) == "yes") || ($wpdb->get_var($wpdb->prepare("SELECT content_private FROM ".$wpdb->prefix."symposium_groups WHERE gid = ".$post->topic_group)) != "on") ) {

						// Check permitted to see forum category
						$sql = "SELECT level FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d";
						$levels = $wpdb->get_var($wpdb->prepare($sql, $post->topic_category));
						$cat_roles = unserialize($levels);
						if (strpos(strtolower($cat_roles), 'everyone,') !== FALSE || strpos(strtolower($cat_roles), $user_role.',') !== FALSE) {

							$html .= "<div class='symposium_latest_forum_row'>";		
								$html .= "<div class='symposium_latest_forum_row_avatar'>";
									$html .= get_avatar($post->topic_owner, 32);
								$html .= "</div>";
								$html .= "<div class='symposium_latest_forum_row_post'>";
									$html .= symposium_profile_link($post->topic_owner);
									if ($preview > 0) {
										$text = stripslashes($post->topic_subject);
										if ( strlen($text) > $preview ) { $text = substr($text, 0, $preview)."..."; } 
										if ($post->topic_group == 0) {
											$url = $forum_url;
											$q = $forum_q;
										} else {
											// Get group URL worked out
											$url = symposium_get_url('group');
											if (strpos($url, '?') !== FALSE) {
												$q = "&gid=".$post->topic_group."&";
											} else {
												$q = "?gid=".$post->topic_group."&";
											}
										}
										$html .= " ".__('started', 'wp-symposium')." <a href='".$url.$q."cid=".$post->topic_category."&show=".$post->tid."'>".$text."</a>";
									} else {
										$html .= "<br />";
									}
									$html .= " ".symposium_time_ago($post->topic_started).". ";
									if ($post->replies > 0) {
										$html .= $post->replies.' ';
										if ($post->replies != 1) {
											$html .= __('replies', 'wp-symposium');
										} else {
											$html .= __('reply', 'wp-symposium');
										}
										$html .= ".";
									}
									if (is_user_logged_in() && get_option('symposium_forum_stars')) {
										if ($post->topic_started > $previous_login && $post->topic_owner != $current_user->ID) {
											$html .= " <img src='".get_option('symposium_images')."/new.gif' alt='New!' />";
										}
									}
									$html .= "<br />";
								$html .= "</div>";
							$html .= "</div>";
						}								
					}
			}

		$html .= "</div>";

	}
	
	echo $html;
}

function do_Forumexperts_Widget($cat_id,$cat_id_exclude,$timescale,$postcount,$groups) {
	
	global $wpdb,$current_user;
	
	$html = '';

	// Content of widget
	$sql = "SELECT topic_owner, display_name, count(*) AS cnt FROM 
	 		(SELECT topic_owner FROM ".$wpdb->prefix."symposium_topics t 
			 WHERE t.topic_answer = 'on' AND t.topic_date >= ( CURDATE() - INTERVAL ".$timescale." DAY ) "; 
	if ($cat_id != '' && $cat_id > 0) {
		$sql .= "AND topic_category IN (".$cat_id.") ";
	}
	if ($cat_id_exclude != '' && $cat_id_exclude > 0) {
		$sql .= "AND topic_category NOT IN (".$cat_id_exclude.") ";
	}
	if ($groups != 'on') {
		$sql .= "AND topic_group = 0 ";
	}
	$sql .= "ORDER BY topic_owner) AS tmp ";
	$sql .= "LEFT JOIN ".$wpdb->prefix."users u ON topic_owner = u.ID ";
	$sql .= "GROUP BY topic_owner, display_name ";
	$sql .= "ORDER BY cnt DESC";
	$posts = $wpdb->get_results($sql); 
	
	$count = 1;
	
	if ($posts) {

		$html .= "<div id='symposium_latest_forum'>";
			
			foreach ($posts as $post)
			{
				$html .= '<div style="clear:both;">';
					$html .= '<div style="float:left;">';
						$html .= symposium_profile_link($post->topic_owner);
					$html .= '</div>';
					$html .= '<div style="float:right;">';
						$html .= $post->cnt.'<br />';
					$html .= '</div>';
				$html .= '</div>';
				
				if ($count++ == $postcount) {
					break;
				}
			}

		$html .= "</div>";

	}
	
	echo $html;	
}

function display_bronze_message() {

	if ( !symposium_is_plus() ) {
		echo "<strong>".__("Bronze Member plugins", "wp-symposium")."</strong><br />";
		echo __("To enable the WYSIWYG editor, purchase the <a href='http://www.wpsymposium.com/downloadinstall/' target='_blank'>Bronze member subscription</a>, and <a href='plugins.php?plugin_status=inactive'>activate</a> at least one Bronze plugin.", "wp-symposium").'<br />';
	}	
	
}

function symposium_is_plus() {

	$saved_code = get_option('symposium_activation_code');
	$code = preg_replace('#[^0-9]#','',$saved_code);
	if (($saved_code) && ($code > time() || $saved_code == 'wps')) {
		return true;
	} else {
		return false;
	}
	
}

function wps_get_monthname($month) {
	switch($month) {									
		case 0:$monthname = "";break;
		case 1:$monthname = __("January", "wp-symposium");break;
		case 2:$monthname = __("February", "wp-symposium");break;
		case 3:$monthname = __("March", "wp-symposium");break;
		case 4:$monthname = __("April", "wp-symposium");break;
		case 5:$monthname = __("May", "wp-symposium");break;
		case 6:$monthname = __("June", "wp-symposium");break;
		case 7:$monthname = __("July", "wp-symposium");break;
		case 8:$monthname = __("August", "wp-symposium");break;
		case 9:$monthname = __("September", "wp-symposium");break;
		case 10:$monthname = __("October", "wp-symposium");break;
		case 11:$monthname = __("November", "wp-symposium");break;
		case 12:$monthname = __("December", "wp-symposium");break;
	}
	return $monthname;
}

function wps_is_wpmu() {
    global $wpmu_version;
    if (function_exists('is_multisite'))
        if (is_multisite()) return true;
    if (!empty($wpmu_version)) return true;
    return false;
}


?>
