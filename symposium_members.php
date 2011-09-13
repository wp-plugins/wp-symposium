<?php
/*
Plugin Name: WP Symposium Members Directory
Plugin URI: http://www.wpsymposium.com
Description: Directory component for the Symposium suite of plug-ins. Put [symposium-members] on any WordPress page.
Version: 11.09.13
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
				$page_length = 25;
	
				$term = "";
				if (isset($_POST['member'])) { $term .= strtolower($_POST['member']); }
				if (isset($_GET['term'])) { $term .= strtolower($_GET['term']); }

				$html .= '<div style="float:right; padding:0px;">';
				$html .= '<input id="members_go_button" type="submit" class="symposium-button" value="'.__("Search", "wp-symposium").'" />';
				$html .= '</div>';
	
				$html .= '<input type="text" id="symposium_member" autocomplete="off" name="symposium_member" class="members_search_box new-topic-subject-input" value="'.$term.'" />';

				$html .= '<div id="symposium_members">';
					
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
					ORDER BY m.last_activity DESC 
					LIMIT 0,".$page_length);
										
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
											//$html .= '<img src="'.$plugin.'/images/loggedout.gif">';
										} else {
											if ($last_active_minutes >= $inactive) {
												$html .= '<img src="'.$plugin.'/images/inactive.gif">';
											} else {
												$html .= '<img src="'.$plugin.'/images/online.gif">';
											}
										}

									$html .= "</div>";
								$html .= "</div>";	
							}
						}
		
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
