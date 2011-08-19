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
	
	$forum_ranks = $wpdb->get_var("SELECT forum_ranks FROM ".$wpdb->prefix."symposium_config");

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
	        '<iframe title="YouTube video player" width="640" height="390" src="http://www.youtube.com/embed/\\1" frameborder="0" allowfullscreen></iframe>'
	);

   return preg_replace($search, $replace, $text_to_search);

}

function show_profile_menu($uid1, $uid2) {
	
	global $wpdb;

		$meta = get_symposium_meta_row($uid1);					
		$share = $meta->share;		
		$privacy = $meta->wall_share;		
		$is_friend = symposium_friend_of($uid1);
		
		$html = '';

		if ($uid1 > 0) {

			// Filter for additional menu items (see www.wpswiki.com for help)
			$html .= apply_filters ( 'symposium_profile_menu_filter', $html,$uid1, $uid2, $privacy, $is_friend, $meta->extended, $share );
			
			if ($uid1 == $uid2 || symposium_get_current_userlevel() == 5) {
				$config = $wpdb->get_row($wpdb->prepare("SELECT profile_avatars FROM ".$wpdb->prefix . 'symposium_config'));
				if ($config->profile_avatars == 'on') {
					$html .= '<div id="menu_avatar" class="symposium_profile_menu">'.__('Profile Photo', 'wp-symposium').'</div>';
				}
				$html .= '<div id="menu_personal" class="symposium_profile_menu">'.__('Personal', 'wp-symposium').'</div>';
				$html .= '<div id="menu_settings" class="symposium_profile_menu">'.__('Preferences', 'wp-symposium').'</div>';

			}
			
		}
	
	return $html;

}

function symposium_make_url($text) {

	$text = preg_replace("#(^|[\n ])(([\w]+?://[\w\#$%&~.\-;:=,?@\[\]+]*)(/[\w\#$%&~/.\-;:=,?@\[\]+]*)?)#is", "\\1<a href=\"\\2\" target=\"_blank\">\\2</a>", $text);
    $text = preg_replace("#(^|[\n ])(((www|ftp)\.[\w\#$%&~.\-;:=,?@\[\]+]*)(/[\w\#$%&~/.\-;:=,?@\[\]+]*)?)#is", "\\1<a href=\"http://\\2\" target=\"_blank\">\\2</a>", $text);
    
    return $text;

}

function symposium_profile_friends($uid, $limit_from) {

	global $wpdb, $current_user;
	wp_get_current_user();
	
	$limit_count = 10;

	$plugin = WP_PLUGIN_URL.'/wp-symposium';
	$meta = get_symposium_meta_row($uid);					
	$config = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix . 'symposium_config'));

	$mailpage = symposium_get_url('mail');
	if ($mailpage[strlen($mailpage)-1] != '/') { $mailpage .= '/'; }
	$q = symposium_string_query($mailpage);		

	$html = "";	

		// Friend Requests
		if ($uid == $current_user->ID) {
			
			$sql = "SELECT u1.display_name, u1.ID, f.friend_timestamp, f.friend_message, f.friend_from FROM ".$wpdb->base_prefix."symposium_friends f LEFT JOIN ".$wpdb->base_prefix."users u1 ON f.friend_from = u1.ID WHERE f.friend_to = %d AND f.friend_accepted != 'on' ORDER BY f.friend_timestamp DESC";
	
			$requests = $wpdb->get_results($wpdb->prepare($sql, $current_user->ID));
			if ($requests) {
				
				$html .= '<h2>'.__('Friend Requests', 'wp-symposium').'...</h2>';
				
				foreach ($requests as $request) {
				
					$html .= "<div id='request_".$request->friend_from."' style='clear:right; margin-top:8px; overflow: auto; margin-bottom: 15px; '>";		
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
		$sql = "SELECT f.*, m.last_activity FROM ".$wpdb->base_prefix."symposium_friends f LEFT JOIN ".$wpdb->base_prefix."symposium_usermeta m ON m.uid = f.friend_to WHERE f.friend_to > 0 AND f.friend_from = %d ORDER BY last_activity DESC LIMIT %d, %d";
		$friends = $wpdb->get_results($wpdb->prepare($sql, $uid, $limit_from, $limit_count));
		
		if ($friends) {
		
			$count = 0;
		
			$inactive = $config->online;
			$offline = $config->offline;
			
			foreach ($friends as $friend) {
				
				$count++;
				
				$time_now = time();
				$last_active_minutes = strtotime($friend->last_activity);
				$last_active_minutes = floor(($time_now-$last_active_minutes)/60);
												
				$html .= "<div id='friend_".$friend->friend_to."' style='clear:right; margin-top:8px; overflow: auto; margin-bottom: 15px; '>";		
					$html .= "<div style='float: left; width:64px; margin-right: 15px'>";
						$html .= get_avatar($friend->friend_to, 64);
					$html .= "</div>";
					$html .= "<div style='float: left;'>";
						$html .= symposium_profile_link($friend->friend_to)."<br />";
						if ($last_active_minutes >= $offline) {
							$html .= __('Logged out', 'wp-symposium').'. '.__('Last active', 'wp-symposium').' '.symposium_time_ago($friend->last_activity).".";
						} else {
							if ($last_active_minutes >= $inactive) {
								$html .= __('Offline', 'wp-symposium').'. '.__('Last active', 'wp-symposium').' '.symposium_time_ago($friend->last_activity).".";
							} else {
								$html .= __('Last active', 'wp-symposium').' '.symposium_time_ago($friend->last_activity).".";
							}
						}
					$html .= "</div>";

					$html .= "<div style='clear: both; float:right;'>";
						$html .= '<input type="submit" title="'.$friend->friend_to.'" class="symposium-button frienddelete" value="'.__('Remove', 'wp-symposium').'" /> ';
						$html .= '</form>';
					$html .= "</div>";
				
					$html .= "<div style='float:right;'>";
						$html .='<input type="symposium-button" value="'.__('Send Mail', 'wp-symposium').'" class="symposium-button" onclick="document.location = \''.$mailpage.$q.'view=compose&to='.$friend->friend_to.'\';">';
					$html .= "</div>";

				$html .= "</div>";
			}

			if ($count == $limit_count) {
				$html .= "<a href='javascript:void(0)' id='friends' class='showmore_wall' title='".($limit_from+$limit_count)."'>".__("Show more...", "wp-symposium")."</a>";
			}
			
		} else {
			$html .= __("Nothing to show, sorry.", "wp-symposium");
		}						

	return $html;
	
}

function symposium_profile_header($uid1, $uid2, $url, $display_name) {
	
	global $wpdb;
	$plugin = WP_PLUGIN_URL.'/wp-symposium';
	$meta = get_symposium_meta_row($uid1);					

	$config = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix . 'symposium_config'));
	
	$html = str_replace("[]", "", stripslashes($config->template_profile_header));

	$privacy = $meta->share;

	$html = str_replace("[display_name]", $display_name, $html);
	
	// Poke
	if ($config->use_poke == 'on' && is_user_logged_in() && $uid1 != $uid2) {
		$poke = "Poke";
		$html = str_replace("[poke]", '<input type="button" value="'.$config->poke_label.'" class="symposium-button poke-button">', $html);
	} else {
		$html = str_replace("[poke]", '', $html);
	}
	

	$location = "";
	$born = "";
	
	if ( ($uid1 == $uid2) || (strtolower($privacy) == 'everyone') || (strtolower($privacy) == 'friends only' && symposium_friend_of($uid1)) ) {
			
		$city = $meta->city;
		$country = $meta->country;

		if ($city != '') { $location .= $city; }
		if ($city != '' && $country != '') { $location .= ", "; }
		if ($country != '') { $location .= $country; }

		$day = (int)$meta->dob_day;
		$month = $meta->dob_month;
		$year = (int)$meta->dob_year;

		if ($year > 0 || $month > 0 || $day > 0) {
			//if ($city != '' || $country != '') { $location .= ".<br />"; }
			switch($month) {									
				case 0:$monthname = "";break;
				case 1:$monthname = __("January", "wp_symposium");break;
				case 2:$monthname = __("February", "wp_symposium");break;
				case 3:$monthname = __("March", "wp_symposium");break;
				case 4:$monthname = __("April", "wp_symposium");break;
				case 5:$monthname = __("May", "wp_symposium");break;
				case 6:$monthname = __("June", "wp_symposium");break;
				case 7:$monthname = __("July", "wp_symposium");break;
				case 8:$monthname = __("August", "wp_symposium");break;
				case 9:$monthname = __("September", "wp_symposium");break;
				case 10:$monthname = __("October", "wp_symposium");break;
				case 11:$monthname = __("November", "wp_symposium");break;
				case 12:$monthname = __("December", "wp_symposium");break;
			}
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
	if ($config->show_dob == 'on') {
		$html = str_replace("[born]", $born, $html);
	} else {
		$html = str_replace("[born]", "", $html);
	}
	
	if ( is_user_logged_in() ) {
		
		$actions = '';
		
		if ($uid1 == $uid2) {

			// Status Input
			$actions .= '<input type="text" id="symposium_status" name="status" class="input-field" onblur="this.value=(this.value==\'\') ? \''.__("What\'s up?", 'wp-symposium').'\' : this.value;" onfocus="this.value=(this.value==\''.__("What\'s up?", 'wp-symposium').'\') ? \'\' : this.value;" value="'.__("What's up?", 'wp-symposium').'"';
			if ($config->show_buttons) {
				$actions .= ' style="width:250px"';
			}
			$actions .= ' />';
			if ($config->show_buttons) {
				$actions .= '&nbsp;<input id="symposium_add_update" type="submit" class="symposium-button" value="'.__('Update', 'wp-symposium').'" /> ';
			}
			if (function_exists('symposium_facebook')) {
				$actions .= "<div id='facebook_div'>";
				if ( $facebook_id = get_symposium_meta($uid2, 'facebook_id') != '') {
					$actions .= "<input type='checkbox' CHECKED id='post_to_facebook' /> ";
					$actions .= __("Post to Facebook", "wp-symposium");
					$actions .= " (<a href='javascript:void(0)' id='cancel_facebook'>".__("Cancel", "wp-symposium")."</a>)";
				} else {
					$actions .= "<img src='".WP_PLUGIN_URL."/wp-symposium-facebook/images/logo_facebook.png' style='float:left; margin-right: 5px;' />";
					$actions .= "<a href='javascript:void(0)' id='setup_facebook'>".__("Connect to Facebook", "wp-symposium")."</a>";
				}
				$actions .= "</div>";
			}
			
		} else {
									
			// Buttons									
			if (symposium_friend_of($uid1)) {

				// A friend
				// Send mail
				$actions .= '<input type="button" class="symposium-button" id="profile_send_mail_button" onclick="document.location = \''.$url.symposium_string_query($url).'view=compose&to='.$uid1.'\';" value="'.__('Send Mail', 'wp-symposium').'">';
				
			} else {
				
				if (symposium_pending_friendship($uid1)) {
					// Pending
					$actions .= '<input type="submit" title="'.$uid1.'" id="cancelfriendrequest" class="symposium-button" value="'.__('Cancel Friend Request', 'wp-symposium').'" /> ';
					$actions .= '<div id="cancelfriendrequest_done" class="hidden">'.__('Friend Request Cancelled', 'wp-symposium').'</div>';
				} else {							
					// Not a friend
					$actions .= '<div id="addasfriend_done1">';
					$actions .= '<span id="add_as_friend_title">'.__('Add as a Friend', 'wp-symposium').'...</span>';
					$actions .= '<div id="add_as_friend_message">';
					$actions .= '<input type="text" id="addfriend" class="input-field" onclick="this.value=\'\'" value="'.__('Add a personal message...', 'wp-symposium').'">';
					$actions .= '<input type="submit" title="'.$uid1.'" id="addasfriend" class="symposium-button" value="'.__('Add', 'wp-symposium').'" /> ';
					$actions .= '</div></div>';
					$actions .= '<div id="addasfriend_done2" class="hidden">'.__('Friend Request Sent', 'wp-symposium').'</div>';
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
	$meta = get_symposium_meta_row($uid1);					

	if ($uid1 > 0) {
		
		$config = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix . 'symposium_config'));

		if ($config->use_styles == "on") {
			$bg_color_2 = 'background-color: '.$config->bg_color_2;
		} else {
			$bg_color_2 = '';
		}
		$privacy = $meta->wall_share;		
		
		$is_friend = symposium_friend_of($uid1);

		$html = "";
		
		$profile_page = symposium_get_url('profile');
		if ($profile_page[strlen($profile_page)-1] != '/') { $profile_page .= '/'; }
		$q = symposium_string_query($profile_page);		

		if ( ($uid1 == $uid2) || (strtolower($privacy) == 'everyone') || (strtolower($privacy) == 'friends only' && $is_friend) || symposium_get_current_userlevel() == 5) {
		
				// Optional panel
				if ($config->show_wall_extras == "on" && $limit_from == 0) {
						
						$html .= "<div id='profile_right_column'>";
	
						// Extended	
						$meta = get_symposium_meta_row($uid1);					
						$extended = $meta->extended;
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
							$ext_rows = subval_sort($ext_rows,'order',asort);
							foreach ($ext_rows as $row) {
								$html .= "<div style='margin-bottom:0px;overflow: auto;'>";
								$html .= "<div style='font-weight:bold;'>".stripslashes($row['name'])."</div>";
								$html .= "<div>".wpautop(symposium_make_url($row['value']))."</div>";
								$html .= "</div>";
							}
						}
						
															
						// Friends
						$has_friends = false;
						$html .= "<div class='profile_panel_friends_div'>";
				
							$sql = "SELECT f.*, m.last_activity FROM ".$wpdb->base_prefix."symposium_friends f LEFT JOIN ".$wpdb->base_prefix."symposium_usermeta m ON m.uid = f.friend_to WHERE f.friend_from = %d AND friend_accepted = 'on' ORDER BY last_activity DESC LIMIT 0,6";
							$friends = $wpdb->get_results($wpdb->prepare($sql, $uid1));
				
							if ($friends) {
								
								$inactive = $config->online;
								$offline = $config->offline;
								
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
					$content = apply_filters ( 'symposium_profile_wall_header_filter', $uid1, $uid2, $privacy, $is_friend, $meta->extended );
					if ($content != $uid1) { $html .= $content; }
				}
					
				$html .= "<div id='symposium_wall'>";

					if ( ($uid1 != '') && ( ($uid1 != $uid2) || (is_user_logged_in() && $is_friend)) ) {
						// Post Comment Input
						$html .= '<input id="symposium_comment" type="text" name="post_comment" class="input-field" onblur="this.value=(this.value==\'\') ? \''.__('Write a comment...', 'wp-symposium').'\' : this.value;" onfocus="this.value=(this.value==\''.__('Write a comment...', 'wp-symposium').'\') ? \'\' : this.value;" value="'.__('Write a comment...', 'wp-symposium').'"';
						if ($config->show_buttons) {
							$html .= ' style="width:200px"';
						}
						$html .= ' />';
						if ($config->show_buttons) {
							$html .= '&nbsp;<input id="symposium_add_comment" type="submit" style="width:75px" class="symposium-button" value="'.__('Post', 'wp-symposium').'" /> ';
						}
						$html .= '<br /><br />';
					}

					if ($post != '' && symposium_safe_param($post)) {

						$sql = "SELECT c.*, u.display_name, u2.display_name AS subject_name FROM ".$wpdb->base_prefix."symposium_comments c LEFT JOIN ".$wpdb->base_prefix."users u ON c.author_uid = u.ID LEFT JOIN ".$wpdb->base_prefix."users u2 ON c.subject_uid = u2.ID WHERE ( (c.subject_uid = %d) OR (c.author_uid = %d) OR ( c.author_uid IN (SELECT friend_to FROM ".$wpdb->base_prefix."symposium_friends WHERE friend_from = %d)) ) AND c.cid = %d AND c.comment_parent = 0 AND c.is_group != 'on' ORDER BY c.comment_timestamp DESC LIMIT %d,%d";
						$comments = $wpdb->get_results($wpdb->prepare($sql, $uid1, $uid1, $uid1, $post, $limit_from, $limit_count));	
						
					} else {

						if ($version == "all_activity") {
							$sql = "SELECT c.*, u.display_name, u2.display_name AS subject_name FROM ".$wpdb->base_prefix."symposium_comments c LEFT JOIN ".$wpdb->base_prefix."users u ON c.author_uid = u.ID LEFT JOIN ".$wpdb->base_prefix."users u2 ON c.subject_uid = u2.ID WHERE c.comment_parent = 0 AND c.is_group != 'on' ORDER BY c.comment_timestamp DESC LIMIT %d,%d";					
							$comments = $wpdb->get_results($wpdb->prepare($sql, $limit_from, $limit_count));	
						}

						if ($version == "friends_activity") {
							$sql = "SELECT c.*, u.display_name, u2.display_name AS subject_name FROM ".$wpdb->base_prefix."symposium_comments c LEFT JOIN ".$wpdb->base_prefix."users u ON c.author_uid = u.ID LEFT JOIN ".$wpdb->base_prefix."users u2 ON c.subject_uid = u2.ID WHERE ( (c.subject_uid = %d) OR (c.author_uid = %d) OR ( c.author_uid IN (SELECT friend_to FROM ".$wpdb->base_prefix."symposium_friends WHERE friend_from = %d)) ) AND c.comment_parent = 0 AND c.is_group != 'on' ORDER BY c.comment_timestamp DESC LIMIT %d,%d";							
							$comments = $wpdb->get_results($wpdb->prepare($sql, $uid1, $uid1, $uid1, $limit_from, $limit_count));	
						}

						if ($version == "wall") {
							$sql = "SELECT c.*, u.display_name, u2.display_name AS subject_name FROM ".$wpdb->base_prefix."symposium_comments c LEFT JOIN ".$wpdb->base_prefix."users u ON c.author_uid = u.ID LEFT JOIN ".$wpdb->base_prefix."users u2 ON c.subject_uid = u2.ID WHERE (c.subject_uid = %d OR c.author_uid = %d) AND c.comment_parent = 0 AND c.is_group != 'on' ORDER BY c.comment_timestamp DESC LIMIT %d,%d";
							$comments = $wpdb->get_results($wpdb->prepare($sql, $uid1, $uid1, $limit_from, $limit_count));	
						}

					}
					
					// Build wall
					if ($comments) {
						foreach ($comments as $comment) {
	
							$html .= "<div id='".$comment->cid."' class='wall_post_div'>";

								$html .= "<div class='wall_post_avatar'>";
									$html .= get_avatar($comment->author_uid, 64);
								$html .= "</div>";

								$html .= '<a href="'.$profile_page.$q.'uid='.$comment->author_uid.'">'.stripslashes($comment->display_name).'</a> ';
								if ($comment->author_uid != $comment->subject_uid) {
									$html .= ' &rarr; <a href="'.$profile_page.$q.'uid='.$comment->subject_uid.'">'.stripslashes($comment->subject_name).'</a> ';
								}
								$html .= symposium_time_ago($comment->comment_timestamp).".";
								if (symposium_get_current_userlevel() == 5 || $comment->subject_uid == $uid2 || $comment->author_uid == $uid2) {
									$html .= " <a title='".$comment->cid."' href='javascript:void(0);' class='delete_post delete_post_top'><img src='".WP_PLUGIN_URL."/wp-symposium/images/delete.png' style='width:16px;height:16px' /></a>";
								}
								$html .= "<br />";
								$html .= symposium_make_url(stripslashes($comment->comment));

								// Replies
								$sql = "SELECT c.*, u.display_name FROM ".$wpdb->base_prefix."symposium_comments c 
									LEFT JOIN ".$wpdb->base_prefix."users u ON c.author_uid = u.ID 
									LEFT JOIN ".$wpdb->base_prefix."symposium_comments p ON c.comment_parent = p.cid 
									WHERE ( 
											(c.subject_uid = %d) 
									   	OR 	(c.author_uid = %d) 
									   	OR 	(p.subject_uid = %d) 
									   	OR 	(p.author_uid = %d) 
									   	OR 	(p.author_uid = %d) 
									   	OR 	(p.subject_uid IN (SELECT friend_to FROM ".$wpdb->base_prefix."symposium_friends WHERE friend_from = %d)) 
									   	OR 	(p.author_uid IN (SELECT friend_to FROM ".$wpdb->base_prefix."symposium_friends WHERE friend_from = %d)) 
									   ) 
									   AND c.comment_parent = %d AND c.is_group != 'on' ORDER BY c.cid";
								
								$replies = $wpdb->get_results($wpdb->prepare($sql, $uid1, $uid1, $uid1, $uid1, $uid2, $uid2, $uid2, $comment->cid));	
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
											$html .= "<div class='wall_reply_div' style='".$bg_color_2.";'>";
												$html .= "<div class='wall_reply'>";
													$html .= '<a href="'.$profile_page.$q.'uid='.$reply->author_uid.'">'.stripslashes($reply->display_name).'</a> ';
													$html .= symposium_time_ago($reply->comment_timestamp).".";
													if (symposium_get_current_userlevel($uid2) == 5 || $reply->subject_uid == $uid2 || $reply->author_uid == $uid2) {
														$html .= " <a title='".$reply->cid."' href='javascript:void(0);' class='delete_post delete_reply'><img src='".WP_PLUGIN_URL."/wp-symposium/images/delete.png' style='width:16px;height:16px' /></a>";
													}
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
										($uid1 == $uid2) || 
										(
											strtolower($privacy) == 'everyone' || 
											(strtolower($privacy) == 'friends only' && $is_friend) 
										)
									) 
								{
									$html .= '<div>';
									$html .= '<input id="symposium_reply_'.$comment->cid.'" type="text" title="'.$comment->cid.'" class="input-field symposium_reply" onblur="this.value=(this.value==\'\') ? \''.__('Write a comment...', 'wp-symposium').'\' : this.value;" onfocus="this.value=(this.value==\''.__('Write a comment...', 'wp-symposium').'\') ? \'\' : this.value;" value="'.__('Write a comment...', 'wp-symposium').'"';
									if ($config->show_buttons) {
										$html .= ' style="width:150px"';
									}
									$html .= ' />';
									if ($config->show_buttons) {
										$html .= '&nbsp;<input title="'.$comment->cid.'" type="submit" style="width:75px" class="symposium-button symposium_add_reply" value="'.__('Add', 'wp-symposium').'" />';
									}
									$html .= '<input id="symposium_author_'.$comment->cid.'" type="hidden" value="'.$comment->subject_uid.'" />';
									$html .= '</div>';
								}
									
							$html .= "</div>";
							
						}
						
						$id = 'wall';
						if ($version == "all_activity") { $id='all'; }
						if ($version == "friends_activity") { $id='activity'; }
						
						$html .= "<a href='javascript:void(0)' id='".$id."' class='showmore_wall' title='".($limit_from+$limit_count)."'>".__("Show more...", "wp-symposium")."</a>";
						
					} else {
						$html .= "<br />".__("Nothing to show, sorry.", "wp-symposium");
					}
				
				$html .= "</div>";
					
		} else {

			if ($version == "friends_activity") {
				$html .= '<p>'.__("Sorry, this member has chosen not to share their activity.");
			}

			if ($version == "wall") {
				$html .= '<p>'.__("Sorry, this member has chosen not to share their activity.");
			}
			
		}		
		return symposium_smilies($html);
		
	} else {
		
		return '';
		
	}

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
	
	
	return $r;
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

function symposium_friend_of($uid) {
   	global $wpdb, $current_user;
	wp_get_current_user();
	
	if ( $wpdb->get_var($wpdb->prepare("SELECT * FROM ".$wpdb->base_prefix."symposium_friends WHERE (friend_accepted = 'on') AND (friend_from = ".$uid." AND friend_to = ".$current_user->ID." OR friend_to = ".$uid." AND friend_from = ".$current_user->ID.")")) ) {
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
	$urls = $wpdb->get_row($wpdb->prepare("SELECT forum_url, members_url, mail_url, profile_url, groups_url, group_url FROM ".$wpdb->prefix . 'symposium_config'));
	$return = false;
	if ($plugin == 'mail') {
		$return = $urls->mail_url;
	}
	if ($plugin == 'forum') {
		$return = $urls->forum_url;
	}
	if ($plugin == 'profile') {
		$return = $urls->profile_url;
	}
	if ($plugin == 'avatar') {
		$return = $urls->avatar_url;
	}
	if ($plugin == 'members') {
		$return = $urls->members_url;
	}
	if ($plugin == 'groups') {
		$return = $urls->groups_url;
	}
	if ($plugin == 'group') {
		$return = $urls->group_url;
	}
	if ($return == false) {
		$return = "INVALID PLUGIN URL REQUESTED (".$plugin.")";
	}
	if ($return[strlen($return)-1] == '/') { $return = substr($return,0,-1); }

	return get_bloginfo('url').$return;
	
}

function symposium_alter_table($table, $action, $field, $format, $null, $default) {
	
	if ($action == "MODIFY") { $action = "MODIFY COLUMN"; }
	if ($default != "") { $default = "DEFAULT ".$default; }

	global $wpdb;	
	
	$success = false;

	$ok = '';
	$check = $wpdb->get_var("SELECT count(".$field.") FROM ".$wpdb->prefix."symposium_".$table);
	if ($check != '') { 
		$ok = 'exists';
		if ($check > 0) { $ok = 'same'; }
	}
	
	if ($action == "ADD") {
		if ($ok == 'exists' || $ok == 'same') {
			// Do Nothing
		} else {
			if ($format != 'text') {
			  	$wpdb->query("ALTER TABLE ".$wpdb->prefix."symposium_".$table." ".$action." ".$field." ".$format." ".$null." ".$default);
			} else {
			  	$wpdb->query("ALTER TABLE ".$wpdb->prefix."symposium_".$table." ".$action." ".$field." ".$format);
			}
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
	
	if ($value == '') { $value = "''"; }
	
	// check if exists, and create record if not
	if ($wpdb->get_var($wpdb->prepare("SELECT * FROM ".$wpdb->base_prefix.'symposium_usermeta'." WHERE uid = ".$uid))) {
	} else {
		create_wps_usermeta($uid);			
	}

	// now update value
 	$r = false;
  	if ($wpdb->query("UPDATE ".$wpdb->base_prefix."symposium_usermeta SET ".$meta." = ".$value." WHERE uid = ".$uid)) {
  		$r = true;
  	}
  	return $r;
}

// Get user meta data, and create if not yet available
function get_symposium_meta($uid, $meta) {
   	global $wpdb;

	// check if exists, and create record if not
	if ($wpdb->get_var($wpdb->prepare("SELECT * FROM ".$wpdb->base_prefix.'symposium_usermeta'." WHERE uid = ".$uid))) {
	} else {
		create_wps_usermeta($uid);			
	}

	if ($value = $wpdb->get_var($wpdb->prepare("SELECT ".$meta." FROM ".$wpdb->base_prefix.'symposium_usermeta'." WHERE uid = ".$uid)) ) {
		return $value;
	} else {
		return false; 	
	}
}

// Get user meta data row
function get_symposium_meta_row($uid) {
   	global $wpdb;

	$row = '';
	
	// check if exists, and create record if not
	if ($row = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->base_prefix.'symposium_usermeta'." WHERE uid = %d", $uid))) {
	} else {
		create_wps_usermeta($uid);			
	}
	
	if ($row == '') {
		if ($row = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->base_prefix.'symposium_usermeta'." WHERE uid = %d", $uid)) ) {
			return $row;
		} else {
			return false; 	
		}
	} else {
		return $row;
	}
	
}

function create_wps_usermeta($uid) {

	if ($uid > 0) {
		
		global $wpdb;

		// insert initial friend(s) if set
		$initial_friend = $wpdb->get_var($wpdb->prepare("SELECT initial_friend FROM ".$wpdb->prefix."symposium_config"));
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
		$initial_groups = $wpdb->get_var($wpdb->prepare("SELECT initial_groups FROM ".$wpdb->prefix."symposium_config"));
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

		// insert user meta
		$wpdb->insert( $wpdb->base_prefix . "symposium_usermeta", array( 
			'uid' => $uid, 
			'notify_new_messages' => 'on',
			'share' => 'Friends only',
			'visible' => 'on',
			'wall_share' => 'Friends only'
			 ) );
			 
	}
}

// Display array contents (for de-bugging only)
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
	if (strpos($p, '?') != FALSE) { 
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
    $periods = array(__('decade') => 315360000,
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
	$footer = $wpdb->get_var($wpdb->prepare("SELECT footer FROM ".$wpdb->prefix.'symposium_config'));

	// get template
	$template = $wpdb->get_var($wpdb->prepare("SELECT template_email FROM ".$wpdb->prefix . "symposium_config"));
	$template = str_replace("[]", "", stripslashes($template));

	// Body Filter
	$msg = apply_filters ( 'symposium_email_body_filter', $msg );

	$msg = str_replace(chr(10), "<br />", $msg);
	
	$template =  str_replace('[message]', $msg, $template);
	$template =  str_replace('[footer]', $footer, $template);
	$template =  str_replace('[powered_by_message]', __('Powered by WP Symposium - Social Networking for WordPress', 'wp-symposium'), $template);
	$template =  str_replace('[version]', WPS_VER, $template);
	
	// To send HTML mail, the Content-type header must be set
	$headers = "MIME-Version: 1.0" . $crlf;
	$headers .= "Content-type:text/html;charset=utf-8" . $crlf;

	$from_email = trim($wpdb->get_var($wpdb->prepare("SELECT from_email FROM ".$wpdb->base_prefix.'symposium_config')));
	if ($from_email == '') { $from_email = "noreply@".get_bloginfo('url'); }	
	$headers .= "From: ".$from_email . $crlf;
		
	// Header Filter
	$headers = apply_filters ( 'symposium_email_header_filter', $headers );

	// finally send mail
	if (mail($email, $subject, $template, $headers))
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

	$template = $wpdb->get_var($wpdb->prepare("SELECT template_page_footer FROM ".$wpdb->prefix . "symposium_config"));
	$template = str_replace("[]", "", stripslashes($template));
	
	$template =  str_replace('[powered_by_message]', __('Powered by WP Symposium - Social Networking for WordPress', 'wp-symposium'), $template);
	$template =  str_replace('[version]', WPS_VER, $template);

	return $template;
	
}

// Groups

function get_group_avatar($gid, $size) {


	global $wpdb, $blog_id;
	$config = $wpdb->get_row($wpdb->prepare("SELECT img_db, img_url, profile_avatars FROM ".$wpdb->prefix . 'symposium_config'));

	if ($config->img_db == "on") {
	
		$sql = "SELECT group_avatar FROM ".$wpdb->prefix."symposium_groups WHERE gid = %d";
		$group_photo = $wpdb->get_var($wpdb->prepare($sql, $gid));

		if ($group_photo == '' || $group_photo == 'upload_failed') {
			return "<img src='".WP_CONTENT_URL."/plugins/wp-symposium/images/unknown.jpg' style='height:".$size."px; width:".$size."px;' />";
		} else {
			return "<img src='".WP_CONTENT_URL."/plugins/wp-symposium-groups/uploadify/get_group_avatar.php?gid=".$gid."' style='width:".$size."px; height:".$size."px' />";
		}
		
		return $html;
		
	} else {

		$sql = "SELECT profile_photo FROM ".$wpdb->prefix."symposium_groups WHERE gid = %d";
		$profile_photo = $wpdb->get_var($wpdb->prepare($sql, $gid));

		if ($profile_photo == '' || $profile_photo == 'upload_failed') {
			return "<img src='".WP_CONTENT_URL."/plugins/wp-symposium/images/unknown.jpg' style='height:".$size."px; width:".$size."px;' />";
		} else {
			if ($blog_id > 1) {
				$img_url = $config->img_url."/".$blog_id."/groups/".$gid."/profile/";	
			} else {
				$img_url = $config->img_url."/groups/".$gid."/profile/";	
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


function SendApplePushMessage($message, $deviceToken, $useDev = TRUE) {
	
	//Apple Network details
	$apnsHost = '';
	$apnsPort = 2195;
	if($useDev)
	   {
	       $apnsHost = 'gateway.sandbox.push.apple.com';
	   }
	   else
	   {
	       $apnsHost = 'gateway.push.apple.com';
	   }

	$apnsCert = 'apns-dev.pem'; //Place Certificate on file system
	$error = '';
	$errorString = '';

	$message = stripslashes($message);
	$message = "Simon posted on your wall:\n".$message;
	// Max length 199 to keep in Apple's rules
	if (strlen($message) > 150) {
		$message = substr($message, 0, 147)."...";
	}

	$streamContext = stream_context_create();
	stream_context_set_option($streamContext, 'ssl', 'local_cert', $apnsCert);
	$apns = stream_socket_client('ssl://' . $apnsHost . ':' . $apnsPort, $error, $errorString, 2, STREAM_CLIENT_CONNECT, $streamContext);

	$value = 1;
	$goto = 'wall'; // wall, wall_reply
	
	// goto=www&value=http://path-to
	// goto=wall&value=post_ID
	// goto=wall_reply&value=post_ID

	$payload['aps'] = array('alert' => $message, 'badge' => 1, 'sound' => 'default');
	$payload['action'] = array('value' => $value, 'goto' => $goto);
	$payload = json_encode($payload);

	$apnsMessage = chr(0).chr(0).chr(32).pack('H*', str_replace(' ', '', $deviceToken)).chr(0).chr(strlen($payload)).$payload;

	fwrite($apns, $apnsMessage);
	socket_close($apns);
	fclose($apns);

	if ($error == 0) {
		return "OK";
	} else {
		return $error.", ".$errorString.", ".$devicetoken;
	}

}

/* Function to sort multi-dimensional arrays */
/* For sort use asort or arsort              */
function subval_sort($a,$subkey,$sort) {
	if (count($a)) {
		foreach($a as $k=>$v) {
			$b[$k] = strtolower($v[$subkey]);
		}
		$sort($b);
		foreach($b as $key=>$val) {
			$c[] = $a[$key];
		}
		return $c;
	} else {
		return $a;
	}
}

?>