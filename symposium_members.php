<?php
/*
Plugin Name: WP Symposium Members Directory
Plugin URI: http://www.wpsymposium.com
Description: Directory component for the Symposium suite of plug-ins. Put [symposium-members] on any WordPress page.
Version: 0.44
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

	//include_once('symposium_styles.php');

	$html .= '<div class="symposium-wrapper">';
	
		if ($ver == '') {
			
			// Full page version

			if ( $_POST['member'] != '' || $_GET['term'] != '' ) {
				
				$me = $current_user->ID;
				$page = 1;
				$page_length = 25;
		
				$html .= '<form method="post" action="'.$dbpage.'"> ';
		
				$term = $_POST['member'].$_GET['term'];
				$html .= '<input type="text" id="member" name="member" class="members_search_box new-topic-subject-input" value="'.$term.'" />';
				$html .= '<input type="hidden" id="member_id" name="member_id" />';
				$html .= '<div style="float:right; padding:0px;">';
				$html .= '<input id="members_go_button" type="submit" class="symposium-button" value="'.__("Go", "wp-symposium").'" />';
				$html .= '</div>';
		
				$html .= '</form>';
			
				$config = $wpdb->get_row($wpdb->prepare("SELECT online,offline FROM ".$wpdb->prefix . 'symposium_config'));
				
				$sql = "SELECT u.ID, m.last_activity, m.city, m.country, m.share, m.wall_share, 
				(SELECT comment FROM ".$wpdb->base_prefix."symposium_comments WHERE author_uid = u.ID AND subject_uid = author_uid and comment_parent = 0 ORDER BY cid DESC LIMIT 0,1) AS latest_comment, 
				(SELECT COUNT(*) FROM ".$wpdb->base_prefix."symposium_friends WHERE friend_from = ".$me." AND friend_to = u.ID) AS is_friend 
				FROM ".$wpdb->base_prefix."symposium_usermeta m 
				RIGHT JOIN ".$wpdb->base_prefix."users u ON m.uid = u.ID 
				WHERE u.ID > 0 AND 
				( (u.display_name LIKE '".$term."%') OR (m.city LIKE '".$term."%') OR (m.country LIKE '".$term."%') OR (u.display_name LIKE '% %".$term."%') )
				ORDER BY m.last_activity DESC LIMIT ".($page*$page_length-$page_length).",".$page_length;
				
				$members = $wpdb->get_results($sql);
			
				if ($members) {
					
					$inactive = $config->online;
					$offline = $config->offline;
					$profile = symposium_get_url('profile');
					
					foreach ($members as $member) {
						
						$time_now = time();
						$last_active_minutes = strtotime($member->last_activity);
						$last_active_minutes = floor(($time_now-$last_active_minutes)/60);
														
						$html .= "<div class='members_row";
							if ($member->is_friend == 1 || $member->ID == $me) {
								$html .= " row corners'>";		
							} else {
								$html .= " row_odd corners'>";		
							}

							$html .= "<div class='members_info'>";
			
								if ( ($member->ID == $me) || (strtolower($member->share) == 'everyone') || (strtolower($member->share) == 'friends only' && $member->is_friend) ) {
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
									$html .= get_avatar($member->ID, 64);
								$html .= "</div>";
								$html .= symposium_profile_link($member->ID).', '.__('last active', 'wp-symposium').' '.symposium_time_ago($member->last_activity).". ";
								if ($last_active_minutes >= $offline) {
									//$html .= '<img src="'.$plugin.'/images/loggedout.gif">';
								} else {
									if ($last_active_minutes >= $inactive) {
										$html .= '<img src="'.$plugin.'/images/inactive.gif">';
									} else {
										$html .= '<img src="'.$plugin.'/images/online.gif">';
									}
								}
								if ( ($member->ID == $me) || (strtolower($member->wall_share) == 'everyone') || (strtolower($member->wall_share) == 'friends only' && $member->is_friend) ) {
									$html .= "<br />".stripslashes($member->latest_comment);
								}
							$html .= "</div>";
						$html .= "</div>";
					}
		
				} else {
					$html .= __('No members', 'wp-symposium')."....";
				}
				
			} else {
		
				$html .= '<form method="post" action="'.$dbpage.'"> ';
		
				$html .= '<div style="float:right; padding:0px;">';
				$html .= '<input id="members_go_button" type="submit" class="symposium-button" value="'.__("Go", "wp-symposium").'" />';
				$html .= '</div>';
		
				$html .= '<input type="text" id="member" name="member" class="members_search_box new-topic-subject-input" onfocus="this.value = \'\';" value="" />';
				$html .= '<input type="hidden" id="member_id" name="member_id" />';
				
				$html .= '</form>';
		
				$html .= '<div id="symposium_members"></div>';
				
			}
		
			
		} else {
					
			// Small version (for use in menu bar's, etc)
			$profile_url = $wpdb->get_var($wpdb->prepare("SELECT profile_url FROM ".$wpdb->prefix."symposium_config"));
			$html .= '<form method="post" action="'.$profile_url.'"> ';
			
			$html .= '<div id="symposium_small_members">';
	
				$html .= '<div id="symposium_small_members_button">'.__('Go', 'wp-symposium').'</div>';
	
				$html .= '<div id="symposium_small_members_input">';
				$html .= '<input type="text" name="member_small" id="symposium_member_small" onfocus="this.value = \'\';" value="'.__('Member search...', 'wp-symposium').'" />';
				$html .= '<input type="hidden" id="uid" name="uid" />';
				$html .= '<input type="hidden" name="view" value="wall" />';
				$html .= '<input type="hidden" name="from" value="small_search" />';
				$html .= '</div>';
				
			$html .= '</div>';
			
			$html .= '</form>';
				
		}
		
	$html .= '</div>'; // End of Wrapper
	
	// Send HTML
	return $html;

}

/* ====================================================== SET SHORTCODE ====================================================== */
if (!is_admin()) {
	add_shortcode('symposium-members', 'symposium_members');  
}



?>
