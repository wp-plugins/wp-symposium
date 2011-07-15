<?php
/*
Plugin Name: WP Symposium Profile
Plugin URI: http://www.wpsymposium.com
Description: Member Profile component for the Symposium suite of plug-ins. Also enables Friends. Put [symposium-profile], [symposium-settings], [symposium-personal], [symposium-friends] or [symposium-extended] on any WordPress page to display relevant content.
Version: 0.59
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

// [symposium-profile] (wall)
function symposium_profile()  
{  
	        			
	return symposium_show_profile("wall");
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
	
	if (is_user_logged_in()) {

		$uid = '';
		
		if (isset($_POST['from']) && $_POST['from'] == 'small_search') {
			if ($_POST['uid'] == '') {
				$search = $_POST['member_small'];
				$uid = $wpdb->get_var("SELECT u.ID FROM ".$wpdb->base_prefix."users u LEFT JOIN ".$wpdb->base_prefix."symposium_usermeta m ON u.ID = m.uid WHERE (u.display_name LIKE '".$search."%') OR (m.city LIKE '".$search."%') OR (m.country LIKE '".$search."%') OR (u.display_name LIKE '% %".$search."%') ORDER BY u.display_name LIMIT 0,1");
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
		
		$user = $wpdb->get_row("SELECT display_name FROM ".$wpdb->base_prefix."users WHERE ID = ".$uid);
		
		if ($user) {
			
			$show_profile_menu = $wpdb->get_var("SELECT show_profile_menu FROM ".$wpdb->prefix."symposium_config");
			
			$html = "";
		
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
					if ($page == '') { $page = "wall"; }
					
					$template = $wpdb->get_var("SELECT template_profile_body FROM ".$wpdb->prefix."symposium_config");
					$template = str_replace("[]", "", stripslashes($template));
					
					// Put in forced profile page
					$template = str_replace("[default]", $page, stripslashes($template));

					// Put in busy image
					$template = str_replace("[page]", "<img src='".WP_PLUGIN_URL."/wp-symposium/images/busy.gif' />", stripslashes($template));

					// Put in menu
					$template = str_replace("[menu]", show_profile_menu($uid, $current_user->ID), stripslashes($template));

					$html .= $template;
  				
					$html .= "<br class='clear' />";
					
				}
					
			
				$html .= "</div>";
				$html .= "<div style='clear: both'></div>";
			
		} else {
			
			$html = __("Member not found, sorry", "wp_symposium");
		}
	
	} else {
		
		$html = __("Please login to view this member's profile.", "wp-symposium");
	}	

								
	return $html;
	exit;

}  

/* ====================================================== SET SHORTCODE ====================================================== */

if (!is_admin()) {

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
