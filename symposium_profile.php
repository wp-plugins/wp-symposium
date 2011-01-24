<?php
/*
Plugin Name: WP Symposium Profile
Plugin URI: http://www.wpsymposium.com
Description: Member Profile component for the Symposium suite of plug-ins. Also enables Friends. Put [symposium-profile], [symposium-settings], [symposium-personal] or [symposium-friends] on any WordPress page to display.
Version: 0.1.30
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



// Adds profile page
function symposium_show_profile($page)  
{  

   	global $wpdb, $current_user;
	wp_get_current_user();
	if (isset($_GET['uid'])) {
		$uid = $_GET['uid'];
	} else {
		$uid = $current_user->ID;
	}
	$user = $wpdb->get_row("SELECT display_name FROM ".$wpdb->prefix."users WHERE ID = ".$uid);
	$show_profile_menu = $wpdb->get_var("SELECT show_profile_menu FROM ".$wpdb->prefix."symposium_config");
	
	$html = "";

	// Includes
	include_once('symposium_styles.php');
	include_once('symposium_functions.php');
	
	// Wrapper
	$html .= "<div id='symposium-wrapper'>";

		$html .= symposium_profile_header($uid, $current_user->ID, symposium_get_url('mail'), $user->display_name);
		if ($show_profile_menu == "on") {
			$html .= show_profile_menu($uid, $current_user->ID);
		}
		$html .= "<div id='force_profile_page' style='display:none'>".$page."</div>";
		$html .= "<div id='profile_body'>";
		$html .= "</div>";
				
		
		// If you are using the free version of Symposium Forum, the following link must be kept in place! Thank you.		
		$html .= powered_by_wps();
	

	$html .= "</div>";
	$html .= "<div style='clear: both'></div>";
										
	return $html;
	exit;

}  

/* ====================================================== SET SHORTCODE ====================================================== */
add_shortcode('symposium-profile', 'symposium_profile');  
add_shortcode('symposium-friends', 'symposium_profile_friend');  
add_shortcode('symposium-activity', 'symposium_profile_activity');  
add_shortcode('symposium-all', 'symposium_profile_all');  
add_shortcode('symposium-personal', 'symposium_profile_personal');  
add_shortcode('symposium-settings', 'symposium_profile_settings');  

?>
