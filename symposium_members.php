<?php
/*
Plugin Name: WP Symposium Members Directory
Plugin URI: http://www.wpsymposium.com
Description: Directory component for the Symposium suite of plug-ins. Put [symposium-members] on any WordPress page.
Version: 11.10.29
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
				$html .= '<div id="symposium_directory_start" style="display:none">'.WPS_DIR_PAGE_LENGTH.'</div>';
				$html .= '<div id="symposium_directory_page_length" style="display:none">'.WPS_DIR_PAGE_LENGTH.'</div>';
				
				$term = "";
				if (isset($_POST['member'])) { $term .= strtolower($_POST['member']); }
				if (isset($_GET['term'])) { $term .= strtolower($_GET['term']); }

				$html .= "<div class='members_row' style='padding:0px'>";
					$html .= '<div style="float:right; padding:0px;">';
					$html .= '<input id="members_go_button" type="submit" class="symposium-button" value="'.__("Search", "wp-symposium").'" />';
					if (is_user_logged_in()) {
						$html .= '<div style="clear:both;"><input type="checkbox" id="symposium_member_friends" /> '.__('Show only friends', 'wp-symposium').'</div>';
					}
					$html .= '</div>';	
					$html .= '<input type="text" id="symposium_member" autocomplete="off" name="symposium_member" class="members_search_box" value="'.$term.'" />';
				$html .= "</div>";
				
				$html .= '<br /><div id="symposium_members_orderby_div">';
					$html .= __('Sort by:', 'wp-symposium').' ';
					$html .= '<select id="symposium_members_orderby">';
						$html .= '<option value="last_activity">'.__('Last activity', 'wp-symposium').'</option>';
						$html .= '<option value="display_name">'.__('Name', 'wp-symposium').'</option>';
					$html .= '</select>';
				$html .= '</div>';

				$html .= '<div id="symposium_members">';
					
					$members = $wpdb->get_results("
					SELECT m.uid, u.display_name, m.city, m.country, m.share, m.last_activity 
					FROM ".$wpdb->base_prefix."symposium_usermeta m 
					LEFT JOIN ".$wpdb->base_prefix."users u ON m.uid = u.ID 					
					WHERE (lower(u.display_name) LIKE '".$term."%') 
					    OR (lower(u.display_name) LIKE '% %".$term."%') 
						OR (lower(m.city) LIKE '".$term."%') 
						OR (lower(m.country) LIKE '".$term."%') 
						OR (lower(m.extended) LIKE '%".$term."%') 
					ORDER BY m.last_activity DESC 
					LIMIT 0,".WPS_DIR_PAGE_LENGTH);
										
					if ($members) {
					
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
															
								$html .= "<div class='members_row";
									
									$is_friend = symposium_friend_of($member->uid, $current_user->ID);
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
													$html .= "<div class='mail_icon' style='display:none;float:right; margin-left:5px;'>";
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
														WHERE author_uid = %d AND comment_parent = 0 AND type = 'forum' 
														ORDER BY cid DESC 
														LIMIT 0,1";
												$forum = $wpdb->get_row($wpdb->prepare($sql, $member->uid));
												if ($forum && (!$comment || $forum->cid != $comment->cid)) {
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
													$html .= '<input title="'.$member->uid.'" id="addtext_'.$member->uid.'" type="text" class="addfriend_text input-field" onclick="this.value=\'\'" value="'.__('Add as a Friend...', 'wp-symposium').'">';
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

						$html .= "<div style='text-align:center; width:100%'><a href='javascript:void(0)' id='showmore_directory'>".__("more...", "wp-symposium")."</a></div>";
		
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
