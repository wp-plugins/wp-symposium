<?php
/*
Plugin Name: WP Symposium Profile
Plugin URI: http://www.wpsymposium.com
Description: Member Profile component for the Symposium suite of plug-ins. Also enables Friends. Put [symposium-profile], [symposium-settings], [symposium-personal], [symposium-friends] or [symposium-extended] on any WordPress page to display relevant content.
Version: 12.06.23
Author: WP Symposium
Author URI: http://www.wpsymposium.com
License: GPL3
*/
	
/*  Copyright 2010,2011,2012  Simon Goodchild  (info@wpsymposium.com)

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


// [symposium-member-header] (just header)
function symposium_profile_member_header()  
{  
	        			
	return symposium_show_profile("header");
	exit;
		
}

// [symposium-profile-menu] 
function symposium_profile_member_menu()  
{  
	global $wpdb, $current_user;

	$html = "";
	
	if (is_user_logged_in()) {
		
		if (isset($_GET['uid'])) {
			$uid = $_GET['uid'];
		} else {
			$uid = $current_user->ID;
		}	        			
	
		$html .= "<div class='symposium-wrapper'>";
		$html .= "<div id='profile_menu' style='margin-left: 0px'>";
		$html .= show_profile_menu($uid, $current_user->ID);
		$html .= "</div>";
		$html .= "</div>";
		
	} else {
	
		$html = "&nbsp;";
		
	}
		
	return $html;
	exit;
		
}

// [symposium-stream] (aggregated wall)
function symposium_stream($view)  
{  
	global $current_user;
	
	$view = ($view == '') ? $view = 'activity' : $view = $view['view'];
	
	$html = "<div class='symposium-wrapper'>";
	$html .= symposium_buffer(symposium_profile_body(0, $current_user->ID, 0, "stream_".$view, 0, false));
	$html .= "</div>";
	        			
	return $html;
	exit;
		
}

// [symposium-profile] (wall)
function symposium_profile()  
{  
	        			
	return symposium_show_profile(get_option('symposium_wps_profile_default'));
	exit;
		
}

// [symposium-activity] (friends activity)
function symposium_profile_activity()  
{  
										
	return symposium_show_profile("activity");
	exit;
		
}

// [symposium-all] (all activity)
function symposium_profile_all()  
{  
										
	return symposium_show_profile("all");
	exit;
		
}

// [symposium-friends]
function symposium_profile_friend()  
{  

	return symposium_show_profile("friends");
	exit;
		
}

// [symposium-personal]
function symposium_profile_personal()  
{  
										
	return symposium_show_profile("personal");
	exit;
		
}

// [symposium-settings]
function symposium_profile_settings()  
{  
										
	return symposium_show_profile("settings");
	exit;
		
}

// [symposium-extended]
function symposium_profile_extended()  
{  
										
	return symposium_show_profile("extended");
	exit;
		
}

// [symposium-avatar]
function symposium_profile_avatar()  
{  
										
	return symposium_show_profile("avatar");
	exit;
		
}

// Adds profile page
function symposium_show_profile($page)  
{  

	global $wpdb, $current_user;

	$uid = '';
	
	if (isset($_POST['from']) && $_POST['from'] == 'small_search') {
		if ($_POST['uid'] == '') {
			$search = $_POST['member_small'];
			$uid = $wpdb->get_var("SELECT u.ID FROM ".$wpdb->base_prefix."users u WHERE u.display_name LIKE '".$search."%'");
		}
	} 
	if ($uid == '') {

		if (isset($_GET['uid'])) {
			$uid = $_GET['uid'];
		} else {
			if (isset($_POST['uid'])) {
				$uid = $_POST['uid'];
			} else {
				$uid = $current_user->ID;
			}
		}

	}

	// resolve stubs if using WPS permalinks
	if ( get_option('symposium_permalink_structure') && get_query_var('stub')) {
		$stubs = explode('/', get_query_var('stub'));
		$stub0 = $stubs[0];
		if (WPS_DEBUG) echo $stub0.'<br />';
		
		if ($stub0) {
			$sql = "SELECT ID FROM ".$wpdb->base_prefix."users WHERE replace(display_name, ' ', '') = %s";
			$id = $wpdb->get_var($wpdb->prepare($sql, $stub0));
			if (WPS_DEBUG) echo $wpdb->last_query.'<br />';
			if ($id) {
				$uid = $id;
			}
		}
	}
	
	
	$share = get_symposium_meta($uid, 'share');
	if (WPS_DEBUG) echo 'UID:'.$uid.'<br />';
	$html = '<div id="symposium_current_user_page" style="display:none">'.$uid.'</div>';
	
	if (is_user_logged_in() || $share == 'public') {		
		
		$user = $wpdb->get_row("SELECT display_name FROM ".$wpdb->base_prefix."users WHERE ID = ".$uid);
		
		if ($user) {
			
			// Wrapper
			$html .= "<div class='symposium-wrapper'>";
			
				$html .= symposium_profile_header($uid, $current_user->ID, symposium_get_url('mail'), $user->display_name);

				if ($page != 'header') {
					
					if (isset($_GET['view']) && $_GET['view'] != '') {
						$page = $_GET['view'];
					}
					if (isset($_POST['view']) && $_POST['view'] != '') {
						$page = $_POST['view'];
					}
					if ($page == '') { $page = get_option('symposium_wps_profile_default'); }
					
					$template = get_option('symposium_template_profile_body');
					$template = str_replace("[]", "", stripslashes($template));
					
					// Put in forced profile page
					$template = str_replace("[default]", $page, stripslashes($template));

					// Put in busy image
					$template = str_replace("[page]", "<img src='".get_option('symposium_images')."/busy.gif' />", stripslashes($template));

					// Put in menu
					$template = str_replace("[menu]", show_profile_menu($uid, $current_user->ID), stripslashes($template));

					$html .= $template;
  				
					$html .= "<br class='clear' />";
					
				}
					
			
				$html .= "</div>";
				$html .= "<div style='clear: both'></div>";
			
		} else {
			
			$html = __("Member not found, sorry", "wp-symposium");
		}
	
	} else {
		
		$html = __("Please login to view this member's profile.", "wp-symposium");
	}	

				
	return $html;
	exit;

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
	
	// Filter for profile header
	$html = apply_filters ( 'symposium_profile_header_filter', $html, $uid1 );

	return $html;


}

/* ====================================================== SET SHORTCODE ====================================================== */

if (!is_admin()) {

	add_shortcode('symposium-stream', 'symposium_stream');  
	add_shortcode('symposium-profile', 'symposium_profile');  
	add_shortcode('symposium-friends', 'symposium_profile_friend');  
	add_shortcode('symposium-activity', 'symposium_profile_activity');  
	add_shortcode('symposium-all', 'symposium_profile_all');  
	add_shortcode('symposium-personal', 'symposium_profile_personal');  
	add_shortcode('symposium-settings', 'symposium_profile_settings');  
	add_shortcode('symposium-extended', 'symposium_profile_extended');  
	add_shortcode('symposium-avatar', 'symposium_profile_avatar');  
	add_shortcode('symposium-menu', 'symposium_profile_member_menu');  
	add_shortcode('symposium-member-header', 'symposium_profile_member_header');  

}
?>
