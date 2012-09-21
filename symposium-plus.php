<?php
/*
Plugin Name: WP Symposium Profile Plus
Plugin URI: http://www.wpsymposium.com
Description: <strong><a href="http://wpswiki.com/index.php?title=Bronze_membership">BRONZE PLUGIN</a></strong>. Adds additional Profile features to WP Symposium
Version: 12.10
Author: WP Symposium
Author URI: http://www.wpsymposium.com
License: Commercial
Requires at least: WordPress 3.0 and WP Symposium 11.8.21
*/

define('WPS_PROFILE_PLUS', '12.10');
if(!defined('WPS_PLUS')) define('WPS_PLUS', '12.10');
	
/*  Copyright 2010,2011,2012  Simon Goodchild  (info@wpsymposium.com)

EULA stands for End User Licensing Agreement. This is the agreement through which the software is licensed to the software user. 

END-USER LICENSE AGREEMENT FOR WPS Profile Plus 

IMPORTANT PLEASE READ THE TERMS AND CONDITIONS OF THIS LICENSE AGREEMENT CAREFULLY BEFORE CONTINUING WITH THIS PROGRAM 

INSTALL: Web Technology Solutions Ltd End-User License Agreement ("EULA") is a legal agreement between you (either an individual or a single entity) and Web Technology Solutions Ltd, for the software product(s) identified above which may include associated software components, media, printed materials, and "online" or electronic documentation ("SOFTWARE PRODUCT"). 

By installing, copying, or otherwise using the SOFTWARE PRODUCT, you agree to be bound by the terms of this EULA. This license agreement represents the entire agreement concerning the program between you and Web Technology Solutions Ltd, (referred to as "licenser"), and it supersedes any prior proposal, representation, or understanding between the parties. If you do not agree to the terms of this EULA, do not install or use the SOFTWARE PRODUCT.

The SOFTWARE PRODUCT is protected by copyright laws and international copyright treaties, as well as other intellectual property laws and treaties. 

The SOFTWARE PRODUCT is licensed, not sold.

1. GRANT OF LICENSE. 
The SOFTWARE PRODUCT is licensed as follows: 
(a) Installation and Use.
Web Technology Solutions Ltd grants you the right to install and use copies of the SOFTWARE PRODUCT on your computer running a validly licensed copy of the operating system for which the SOFTWARE PRODUCT was designed.
(b) Backup Copies.
You may also make copies of the SOFTWARE PRODUCT as may be necessary for backup and archival purposes.

2. DESCRIPTION OF OTHER RIGHTS AND LIMITATIONS.
(a) Maintenance of Copyright Notices.
You must not remove or alter any copyright notices on any and all copies of the SOFTWARE PRODUCT.
(b) Distribution.
You may not distribute registered copies of the SOFTWARE PRODUCT to third parties. Evaluation versions available for download from Web Technology Solutions Ltd's websites may be freely distributed.
(c) Prohibition on Reverse Engineering, Decompilation, and Disassembly.
You may not reverse engineer, decompile, or disassemble the SOFTWARE PRODUCT, except and only to the extent that such activity is expressly permitted by applicable law notwithstanding this limitation. 
(d) Rental.
You may not rent, lease, or lend the SOFTWARE PRODUCT.
(e) Support Services.
Web Technology Solutions Ltd may provide you with support services related to the SOFTWARE PRODUCT ("Support Services"). Any supplemental software code provided to you as part of the Support Services shall be considered part of the SOFTWARE PRODUCT and subject to the terms and conditions of this EULA. 
(f) Compliance with Applicable Laws.
You must comply with all applicable laws regarding use of the SOFTWARE PRODUCT.

3. TERMINATION 
Without prejudice to any other rights, Web Technology Solutions Ltd may terminate this EULA if you fail to comply with the terms and conditions of this EULA. In such event, you must destroy all copies of the SOFTWARE PRODUCT in your possession.

4. COPYRIGHT
All title, including but not limited to copyrights, in and to the SOFTWARE PRODUCT and any copies thereof are owned by Web Technology Solutions Ltd or its suppliers. All title and intellectual property rights in and to the content which may be accessed through use of the SOFTWARE PRODUCT is the property of the respective content owner and may be protected by applicable copyright or other intellectual property laws and treaties. This EULA grants you no rights to use such content. All rights not expressly granted are reserved by Web Technology Solutions Ltd.

5. NO WARRANTIES
Web Technology Solutions Ltd expressly disclaims any warranty for the SOFTWARE PRODUCT. The SOFTWARE PRODUCT is provided 'As Is' without any express or implied warranty of any kind, including but not limited to any warranties of merchantability, noninfringement, or fitness of a particular purpose. Web Technology Solutions Ltd does not warrant or assume responsibility for the accuracy or completeness of any information, text, graphics, links or other items contained within the SOFTWARE PRODUCT. Web Technology Solutions Ltd makes no warranties respecting any harm that may be caused by the transmission of a computer virus, worm, time bomb, logic bomb, or other such computer program. Web Technology Solutions Ltd further expressly disclaims any warranty or representation to Authorized Users or to any third party.

6. LIMITATION OF LIABILITY
In no event shall Web Technology Solutions Ltd be liable for any damages (including, without limitation, lost profits, business interruption, or lost information) rising out of 'Authorized Users' use of or inability to use the SOFTWARE PRODUCT, even if Web Technology Solutions Ltd has been advised of the possibility of such damages. In no event will Web Technology Solutions Ltd be liable for loss of data or for indirect, special, incidental, consequential (including lost profit), or other damages based in contract, tort or otherwise. Web Technology Solutions Ltd shall have no liability with respect to the content of the SOFTWARE PRODUCT or any part thereof, including but not limited to errors or omissions contained therein, libel, infringements of rights of publicity, privacy, trademark rights, business interruption, personal injury, loss of privacy, moral rights or the disclosure of confidential information.

*/

// Main function - tells WP Symposium that this plugin is activated
function symposium_profile_plus(){}

/* ====================================================== ADMIN ====================================================== */

require_once(WP_PLUGIN_DIR . '/wp-symposium/symposium_functions.php');

// Check for updates
if ( ( get_option("symposium_profile_plus_version") != WPS_PROFILE_PLUS && is_admin()) || (isset($_GET['force_create_wps']) && $_GET['force_create_wps'] == 'yes' && is_admin()) ) {

 	// Update Version *******************************************************************************
	update_option("symposium_profile_plus_version", WPS_PROFILE_PLUS);
	symposium_plus_activate();	
}

function symposium_plus_activate() {
	
	global $wpdb;

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    require_once(WP_PLUGIN_DIR . '/wp-symposium/symposium_functions.php');

	// Create gallery table
	$table_name = $wpdb->prefix . "symposium_following";
	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {

		$sql = "CREATE TABLE " . $table_name . " (
		  fid int(11) NOT NULL AUTO_INCREMENT,
		  uid int(11) NOT NULL DEFAULT 0,
		  following int(11) NOT NULL,
		  created datetime NOT NULL,
		  PRIMARY KEY (fid)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;";

	    dbDelta($sql);
	
	}
	// Add index
	symposium_add_index($table_name, 'uid');
	symposium_add_index($table_name, 'following');
	
	// Add fields to user meta
	update_option('symposium_plus_lat', 0); 
	update_option('symposium_plus_long', 0); 

	// Add option
	update_option('symposium_wps_show_hoverbox', 'on');
	
}

function symposium_plus_deactivate() {
}

function symposium_plus_uninstall() {
}

register_activation_hook(__FILE__,'symposium_plus_activate');
register_deactivation_hook(__FILE__, 'symposium_plus_deactivate');
register_uninstall_hook(__FILE__, 'symposium_plus_uninstall');

/* ====================================================== HOOKS/FILTERS INTO WORDPRESS/WP SYMPOSIUM ====================================================== */

// Add plugin to WP Symposium admin menu via hook
function symposium_add_profile_plus_to_admin_menu()
{
	$hidden = get_option('symposium_long_menu') == "on" ? '_hidden': '';
	add_submenu_page('symposium_debug'.$hidden, __('Profile Plus', 'wp-symposium'), __('Profile Plus', 'wp-symposium'), 'manage_options', 'wp-symposium/symposium_plus_admin.php');
}
add_action('symposium_admin_menu_hook', 'symposium_add_profile_plus_to_admin_menu');

// ----------------------------------------------------------------------------------------------------------------------------------------------------------

// Add row to WPS installation page showing status of the Profile Plus plugin through hook provided
// install_row(
//	name, 
//	shortcode or '' if not application,
//	main plugin function, 
//	wp_option holding page plugin has been added (eg: profile_url for profile page) or a '-' for none
//	relative path within plugins folder to plugin,
//	url for configuration or '',
//	message about where to install/download
// )
function symposium_installation_hook_plus()
{
	install_row(
		__('Profile_Plus', 'wp-symposium').' v'.get_option("symposium_profile_plus_version"), 
		'', 
		'symposium_profile_plus', 
		'-', 
		'wp-symposium/symposium-plus.php', 
		'admin.php?page=wp-symposium/symposium_plus_admin.php', 
		__('The Profile Plus plugin must be installed in ', 'wp-symposium').WP_PLUGIN_DIR.'/wp-symposium.'.chr(10).chr(10).'Download from http://www.wpsymposium.com/downloadinstall.'
	);

}
add_action('symposium_installation_hook', 'symposium_installation_hook_plus');

// Add Menu item to @mentions

function add_mentions_menu($html,$uid1,$uid2,$privacy,$is_friend,$extended,$share)  
{  
	global $current_user;

	if ( (is_user_logged_in() && strtolower($share) == 'everyone') || (strtolower($share) == 'public') || (strtolower($share) == 'friends only' && $is_friend) || symposium_get_current_userlevel() == 5) {

		if ($uid1 == $uid2) {
			if (get_option('symposium_menu_mentions'))
				$html .= '<div id="menu_mentions" class="symposium_profile_menu">'.(($t = get_option('symposium_menu_mentions_text')) != '' ? $t :  __('Forum @mentions', 'wp-symposium')).'</div>';  
		} else {
			if (get_option('symposium_menu_mentions_other'))
				$html .= '<div id="menu_mentions" class="symposium_profile_menu">'.(($t = get_option('symposium_menu_mentions_other_text')) != '' ? $t :  __('Forum @mentions', 'wp-symposium')).'</div>';  
		}
		
	}
	
	return $html;
	
}  
add_filter('symposium_profile_menu_filter', 'add_mentions_menu', 10, 7);

// Add Menu item to Profile Menu through filter provided

function add_following_menu($html,$uid1,$uid2,$privacy,$is_friend,$extended,$share)  
{  
	global $current_user;

	if ( ((is_user_logged_in() && strtolower($share) == 'everyone') || (strtolower($share) == 'public') || (strtolower($share) == 'friends only' && $is_friend) || symposium_get_current_userlevel() == 5) ) {

		if ($uid1 == $uid2) {
			if (get_option('symposium_menu_following'))
				$html .= '<div id="menu_plus" class="symposium_profile_menu">'.(($t = get_option('symposium_menu_following_text')) != '' ? $t :  __('I am Following', 'wp-symposium')).'</div>';  
			if (get_option('symposium_menu_followers'))
				$html .= '<div id="menu_plus_me" class="symposium_profile_menu">'.(($t = get_option('symposium_menu_followers_text')) != '' ? $t :  __('My Followers', 'wp-symposium')).'</div>';  
		} else {
			if (get_option('symposium_menu_following_other'))
				$html .= '<div id="menu_plus" class="symposium_profile_menu">'.(($t = get_option('symposium_menu_following_other_text')) != '' ? $t :  __('Following', 'wp-symposium')).'</div>';  
			if (get_option('symposium_menu_followers_other'))
				$html .= '<div id="menu_plus_me" class="symposium_profile_menu">'.(($t = get_option('symposium_menu_followers_other_text')) != '' ? $t :  __('Followers', 'wp-symposium')).'</div>';  
		}
		
	}
	
	return $html;
	
}  
add_filter('symposium_profile_menu_filter', 'add_following_menu', 10, 7);

function symposium_search($width='200')  
{  
	$width = 'style="width:'.$width.'px"';
   	$prompt = ($prompt = get_option('wps_site_search_prompt')) ? $prompt : __('Search...', 'wp-symposium');
	
	$html = '<input type="text" id="symposium_member_small" '.$width.' 
				onblur="this.value=(this.value==\'\') ? \''.$prompt.'\' : this.value;" 
				onfocus="this.value=(this.value==\''.$prompt.'\') ? \'\' : this.value;" 
				value="'.$prompt.'" />';				
	
	return $html;
}

/* ====================================================== SET SHORTCODE ====================================================== */

// [symposium-following] (for profile page)
function symposium_profile_following()  
{  
	return symposium_show_profile("plus");
	exit;	
}
add_shortcode('symposium-following', 'symposium_profile_following');  


if (!is_admin()) {
	add_shortcode('symposium-search', 'symposium_search');  
}
?>
