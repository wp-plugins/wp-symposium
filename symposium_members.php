<?php
/*
Plugin Name: WP Symposium Members Directory
Plugin URI: http://www.wpsymposium.com
Description: Directory component for the Symposium suite of plug-ins. Put [symposium-members] on any WordPress page.
Version: 0.1.25
Author: WP Symposium
Author URI: http://www.wpsymposium.com
License: GPL2
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

function symposium_members() {	
	
	global $wpdb, $current_user;
	wp_get_current_user();

	$plugin = WP_PLUGIN_URL.'/wp-symposium';
	$dbpage = $plugin.'/symposium_members_db.php';
	$get_language = symposium_get_language($current_user->ID);
	$language_key = $get_language['key'];

	include_once('symposium_styles.php');


	$html .= '<div id="symposium-wrapper">';

	if ( $_POST['member'] != '' || $_GET['term'] != '' ) {
		
		$me = $current_user->ID;
		$page = 1;
		$page_length = 25;
		

		$html .= '<form method="post" action="'.$dbpage.'"> ';

		$term = $_POST['member'].$_GET['term'];
		$html .= '<input type="text" id="member" name="member" class="new-topic-subject-input" style="width:75%" value="'.$term.'" />';
		$html .= '<input type="hidden" id="member_id" name="member_id" />';
		$html .= '<div style="float:right; padding:0px;">';
		$html .= '<input type="submit" class="button" style="float: left; height:46px;" value="Go" />';
		$html .= '</div>';

		$html .= '</form>';
	
		$config = $wpdb->get_row($wpdb->prepare("SELECT online,offline FROM ".$wpdb->prefix . 'symposium_config'));
		
		$sql = "SELECT u.ID, m.last_activity, m.city, m.country, m.share, m.wall_share, 
		(SELECT comment FROM ".$wpdb->prefix."symposium_comments WHERE author_uid = u.ID AND subject_uid = author_uid and comment_parent = 0 ORDER BY cid DESC LIMIT 0,1) AS latest_comment, 
		(SELECT COUNT(*) FROM ".$wpdb->prefix."symposium_friends WHERE friend_from = ".$me." AND friend_to = u.ID) AS is_friend 
		FROM ".$wpdb->prefix."symposium_usermeta m 
		RIGHT JOIN ".$wpdb->prefix."users u ON m.uid = u.ID 
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
												
				$html .= "<div style='clear:both; margin-top:8px; overflow: auto; width:95%; margin-bottom: 0px;padding:6px;padding-bottom:3px;'";
				if ($member->is_friend == 1 || $member->ID == $me) {
					$html .= " class='row corners'>";		
				} else {
					$html .= " class='row_odd corners'>";		
				}
					$html .= "<div style='width:100%; padding-left: 75px; margin-left:-75px;'>";
	
						if ( ($member->ID == $me) || (strtolower($member->share) == 'everyone') || (strtolower($member->share) == 'friends only' && $member->is_friend) ) {
							$html .= "<div style='float: right;font-style:italic;'>";
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
	
						$html .= "<div style='float: left; width:75px;'>";
							$html .= get_avatar($member->ID, 64);
						$html .= "</div>";
						$html .= symposium_profile_link($member->ID).', last active '.symposium_time_ago($member->last_activity, $language_key).". ";
						if ($last_active_minutes >= $offline) {
							//$html .= '<img src="'.$plugin_dir.'images/loggedout.gif">';
						} else {
							if ($last_active_minutes >= $inactive) {
								$html .= '<img src="'.$plugin_dir.'images/inactive.gif">';
							} else {
								$html .= '<img src="'.$plugin_dir.'images/online.gif">';
							}
						}
						if ( ($member->ID == $me) || (strtolower($member->wall_share) == 'everyone') || (strtolower($member->wall_share) == 'friends only' && $member->is_friend) ) {
							$html .= "<br />".stripslashes($member->latest_comment);
						}
					$html .= "</div>";
				$html .= "</div>";
			}

		} else {
			$html .= "No members....";
		}
		
	} else {

		$html .= '<form method="post" action="'.$dbpage.'"> ';

		$html .= '<div style="float:right; padding:0px;">';
		$html .= '<input type="submit" class="button" style="float: left; height:46px;" value="Go" />';
		$html .= '</div>';

		$html .= '<input type="text" id="member" name="member" class="new-topic-subject-input" style="width:75%" onfocus="this.value = \'\';" value="" />';
		$html .= '<input type="hidden" id="member_id" name="member_id" />';
		
		$html .= '</form>';

		$html .= '<div id="symposium_members"><img src="'.$plugin.'/images/busy.gif" /></div>';
		
	}
		
	$html .= '</div>'; // End of Wrapper
	
	// Send HTML
	return $html;

}

/* ====================================================== SET SHORTCODE ====================================================== */
add_shortcode('symposium-members', 'symposium_members');  



?>
