<?php
/*
Plugin Name: WP Symposium Members Directory
Plugin URI: http://www.wpsymposium.com
Description: Directory component for the Symposium suite of plug-ins. Put [symposium-members] on any WordPress page.
Version: 12.04.21
Author: WP Symposium
Author URI: http://www.wpsymposium.com
License: GPL3
*/
	
/*  Copyright 2010,2011  Simon Goodchild  (info@wpsymposium.com)

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

function symposium_members($ver) {	
	
	global $wpdb, $current_user;
	wp_get_current_user();

	$plugin = WP_PLUGIN_URL.'/wp-symposium';
	$dbpage = $plugin.'/symposium_members_db.php';

		if ($ver == '') {

			$html = '<div class="symposium-wrapper">';

				// If 'term' is passed as a parameter, it will influence the results
				$me = $current_user->ID;
				$page = 1;
				
				// Stores start value for more
				$start = get_option('symposium_dir_page_length')+1;
				$html .= '<div id="symposium_directory_start" style="display:none">'.$start.'</div>';
				$html .= '<div id="symposium_directory_page_length" style="display:none">'.get_option('symposium_dir_page_length').'</div>';
				
				$term = "";
				if (isset($_POST['member'])) { $term .= strtolower($_POST['member']); }
				if (isset($_GET['term'])) { $term .= strtolower($_GET['term']); }

				$html .= "<div class='members_row' style='padding:0px'>";
					$html .= '<div style="float:right; padding:0px;padding-top:2px;">';
					$html .= '<input id="members_go_button" type="submit" class="symposium-button" value="'.__("Search", "wp-symposium").'" />';
					if (is_user_logged_in()) {
						$html .= '<div style="clear:both;"><input type="checkbox" id="symposium_member_friends" /> '.__('Show only friends', 'wp-symposium').'</div>';
					}
					$html .= '</div>';	
					$html .= '<input type="text" id="symposium_member" autocomplete="off" name="symposium_member" class="members_search_box" value="'.$term.'" />';
					if (!get_option('symposium_wps_lite') && function_exists('symposium_profile_plus')) {
						$html .= '<div style="clear:both">';
						$html .= '<a href="javascript:void(0);" id="symposium_show_advanced" /> '.__('Advanced search', 'wp-symposium').'</a>';
						$html .= '<a href="javascript:void(0);" style="display:none" id="symposium_show_simple" /> '.__('Simple search', 'wp-symposium').'</a>';
						$html .= '</div>';
					}
				$html .= "</div>";
				
				if (!get_option('symposium_wps_lite') && function_exists('symposium_profile_plus')) {
					
					// Loop through extended fields and offer as a search options (if there are any)
					$extensions = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."symposium_extended ORDER BY extended_order, extended_name"));
					if ($extensions) {

						$html .= "<div id='symposium_advanced_search' style='width:90%;padding:0px;display:none;'>";	
	
						foreach ($extensions as $extension) {
											
							$html .= '<div style="margin-bottom:3px">';

								if ($extension->extended_type == 'Checkbox') {
									$html .= '<div style="width:150px;float:left;">';
									$html .= $extension->extended_name;
									$html .= '</div><div>';
									$html .= '<input rel="checkbox" id="'.$extension->eid.'" class="symposium_extended_search" type="checkbox" name="extended_value[]" />';
									$html .= '</div>';
								}
								if ($extension->extended_type == 'List') {
									$html .= '<div style="width:150px;float:left;">';
									$html .= $extension->extended_name.':';
									$html .= '</div><div>';
									$html .= '<select rel="list" id="'.$extension->eid.'" class="symposium_extended_search" name="extended_value[]">';
									$items = explode(',', $extension->extended_default);
									$html .= '<option value="'.__('Any', 'wp-symposium').'">'.__('Any', 'wp-symposium').'</option>';
									foreach ($items as $item) {
										$html .= '<option value="'.$item.'">'.$item.'</option>';
									}												
									$html .= '</select>';
									$html .= '</div>';
								}

							$html .= '</div>';
						}
						$html .= "</div>";					
					}
				}			
				
				// Sort by option
				$html .= '<br /><div id="symposium_members_orderby_div">';
					$html .= __('Sort by:', 'wp-symposium').' ';
					$html .= '<select id="symposium_members_orderby">';
						$html .= '<option value="last_activity">'.__('Last activity', 'wp-symposium').'</option>';
						$html .= '<option value="display_name">'.__('Name', 'wp-symposium').'</option>';
						if (function_exists('symposium_profile_plus')) {
							$html .= '<option value="distance">'.__('Distance', 'wp-symposium').'</option>';
						}
					$html .= '</select>';
				$html .= '</div>';

				
				// A to Z
				$html .= '<div id="symposium_members_atoz">';
					for ($i = 65; $i <= 90; $i++) { 
						if (chr($i) != strtoupper($term)) {
							$html .= '<a href="?term='.chr($i).'">'.chr($i).'</a>&nbsp;&nbsp;';
						} else {
							$html .= '<strong>'.chr($i).'</strong>&nbsp;&nbsp;';
						}
					}
				$html .= '</div>';

				$html .= '<div id="symposium_members">';
			
					$search_limit = 100;
					$sql_ext = strlen($term) != 1 ? "OR (lower(u.display_name) LIKE '% %".$term."%')" : "";
					
					$lat = get_symposium_meta($current_user->ID, 'plus_lat');
					if ($lat != 0 && is_user_logged_in() && function_exists('symposium_profile_plus')) {
						
						$long = get_symposium_meta($current_user->ID, 'plus_long');
						$measure = ($value = get_option("symposium_plus_lat_long")) ? $value : '';
						$show_alt = ($value = get_option("symposium_plus_show_alt")) ? $value : '';
						
						$sql = "SELECT u.ID as uid, u.display_name, cast(m4.meta_value as datetime) as last_activity, 
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
						ORDER BY cast(m4.meta_value as datetime) DESC 
						LIMIT 0,".$search_limit;
						
						$members = $wpdb->get_results($sql);							
										
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
						ORDER BY cast(m4.meta_value as datetime) DESC 
						LIMIT 0,".$search_limit);	
						
					}

					if ($members) {
					
						$inactive = get_option('symposium_online');
						$offline = get_option('symposium_offline');
						$profile = symposium_get_url('profile');
						$mailpage = symposium_get_url('mail');
						$q = symposium_string_query($mailpage);
						$count = 0;

						if ( !isset( $wp_roles ) ) $wp_roles = new WP_Roles();									

						// Get included levels
						$dir_levels = strtolower(get_option('symposium_dir_level'));
										
						foreach ($members as $member) {
							
							$user_info = get_userdata($member->uid);							

 							// Check to see if this member is in the included list of roles
							$user = get_userdata( $member->uid );
							$capabilities = $user->{$wpdb->base_prefix.'capabilities'};

							$include = false;
							if ($capabilities) {
								
								foreach ( $capabilities as $role => $name ) {
									if ($role) 
										if (strpos($dir_levels, $role) !== FALSE) $include = true;
								}		 														
							
							}
							
							if ($include) {							

									$city = get_symposium_meta($member->uid, 'extended_city');
									$country = get_symposium_meta($member->uid, 'extended_country');
									$share = get_symposium_meta($member->uid, 'share');
									$wall_share = get_symposium_meta($member->uid, 'wall_share');
			
									$count++;
									if ($count > get_option('symposium_dir_page_length')) break;
								
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
														if ($show_alt == 'on') {
															if ($measure != 'on') { 
																$html .= ' ('.intval(($distance/8)*5).' '.__('miles', 'wp-symposium').')';
															} else {
																$html .= ' ('.intval(($distance/5)*8).' '.__('km', 'wp-symposium').')';
															}
														}
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

						$html .= "<div id='showmore_directory_div' style='text-align:center; width:100%'><a href='javascript:void(0)' id='showmore_directory'>".__("more...", "wp-symposium")."</a></div>";
		
					} else {
						$html .= '<br />'.__('No members found', 'wp-symposium')."....";
					}
				
				$html .= '</div>';


			$html .= '</div>'; // End of Wrapper		

			// Send HTML
			return $html;
			
		} else {
			
			// Small version (for use in menu bar's, etc)
			
			$members_url = symposium_get_url('members');
			$members_url .= symposium_string_query($members_url);
			echo '<form method="post" action="'.$members_url.'"> ';
			
			echo '<div id="symposium_small_members">';
	
				echo '<div id="symposium_small_members_input">';
				echo '<input type="text" name="member" id="symposium_member_small" onfocus="this.value = \'\';" value="'.__('Member search...', 'wp-symposium').'" />';
				echo '</div>';
				
			echo '</div>';
			
			echo '</form>';
				
		}
		

}

/* ====================================================== SET SHORTCODE ====================================================== */
if (!is_admin()) {
	add_shortcode('symposium-members', 'symposium_members');  
}



?>
