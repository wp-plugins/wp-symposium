<?php
/*
WP Symposium Profile Plus
Description: Adds additional Profile features to WP Symposium
*/

define('WPS_PROFILE_PLUS', '12.12');
if(!defined('WPS_PLUS')) define('WPS_PLUS', '12.12');
	
/*  Copyright 2010,2011,2012  Simon Goodchild  (info@wpsymposium.com)

EULA stands for End User Licensing Agreement. This is the agreement through which the software is licensed to the software user. 

END-USER LICENSE AGREEMENT FOR PROFILE PLUS PLUGIN

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

// Get constants
require_once(dirname(__FILE__).'/default-constants.php');


function __wps__profile_plus(){}

/* ====================================================== ADMIN ====================================================== */

require_once(WPS_PLUGIN_DIR . '/functions.php');

// Check for updates
if ( ( get_option(WPS_OPTIONS_PREFIX."_profile_plus_version") != WPS_PROFILE_PLUS && is_admin()) || (isset($_GET['force_create_wps']) && $_GET['force_create_wps'] == 'yes' && is_admin()) ) {

 	// Update Version *******************************************************************************
	update_option(WPS_OPTIONS_PREFIX."_profile_plus_version", WPS_PROFILE_PLUS);
	symposium_plus_activate();	
}

function symposium_plus_activate() {
	
	global $wpdb;

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    require_once(WPS_PLUGIN_DIR . '/functions.php');

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
	__wps__add_index($table_name, 'uid');
	__wps__add_index($table_name, 'following');
	
	// Add fields to user meta
	if (get_option(WPS_OPTIONS_PREFIX.'_plus_lat') === false)
		update_option(WPS_OPTIONS_PREFIX.'_plus_lat', 0); 
	if (get_option(WPS_OPTIONS_PREFIX.'_plus_long') === false)
		update_option(WPS_OPTIONS_PREFIX.'_plus_long', 0); 

	// Add option
	if (get_option(WPS_OPTIONS_PREFIX.'_wps_show_hoverbox') === false)
		update_option(WPS_OPTIONS_PREFIX.'_wps_show_hoverbox', 'on');
	
}

function symposium_plus_deactivate() {
}

function symposium_plus_uninstall() {
}

register_activation_hook(__FILE__,'symposium_plus_activate');
register_deactivation_hook(__FILE__, 'symposium_plus_deactivate');
register_uninstall_hook(__FILE__, 'symposium_plus_uninstall');

/* ====================================================== HOOKS/FILTERS INTO WORDPRESS/WP Symposium ====================================================== */

// Add plugin to admin menu via hook
function symposium_add_profile_plus_to_admin_menu()
{
	$hidden = get_option(WPS_OPTIONS_PREFIX.'_long_menu') == "on" ? '_hidden': '';
	add_submenu_page('symposium_debug'.$hidden, __('Profile Plus', WPS_TEXT_DOMAIN), __('Profile Plus', WPS_TEXT_DOMAIN), 'manage_options', WPS_DIR.'/plus_admin.php');
}
add_action('__wps__admin_menu_hook', 'symposium_add_profile_plus_to_admin_menu');

// ----------------------------------------------------------------------------------------------------------------------------------------------------------

// Add Menu item to @mentions

function __wps__add_mentions_menu($html,$uid1,$uid2,$privacy,$is_friend,$extended,$share,$extra_class)  
{  
	global $current_user;

	if ( (is_user_logged_in() && strtolower($share) == 'everyone') || (strtolower($share) == 'public') || (strtolower($share) == 'friends only' && $is_friend) || __wps__get_current_userlevel() == 5) {

		if ($uid1 == $uid2) {
			if (get_option(WPS_OPTIONS_PREFIX.'_menu_mentions'))
				$html .= '<div id="menu_mentions" class="__wps__profile_menu '.$extra_class.'">'.(($t = get_option(WPS_OPTIONS_PREFIX.'_menu_mentions_text')) != '' ? $t :  __('Forum @mentions', WPS_TEXT_DOMAIN)).'</div>';  
		} else {
			if (get_option(WPS_OPTIONS_PREFIX.'_menu_mentions_other'))
				$html .= '<div id="menu_mentions" class="__wps__profile_menu '.$extra_class.'">'.(($t = get_option(WPS_OPTIONS_PREFIX.'_menu_mentions_other_text')) != '' ? $t :  __('Forum @mentions', WPS_TEXT_DOMAIN)).'</div>';  
		}
		
	}
	
	return $html;
	
}  
add_filter('__wps__profile_menu_filter', '__wps__add_mentions_menu', 10, 8);

function __wps__add_mentions_menu_tabs($html,$title,$value,$uid1,$uid2,$privacy,$is_friend,$extended,$share)  
{  
	if ($value == 'mentions') {
		
		global $current_user;
	
		if ( (($uid1 == $uid2) || is_user_logged_in() && strtolower($share) == 'everyone') || (strtolower($share) == 'public') || (strtolower($share) == 'friends only' && $is_friend) || __wps__get_current_userlevel() == 5)
			$html .= '<li id="menu_mentions" class="__wps__profile_menu" href="javascript:void(0)">'.$title.'</li>';
		
	}
	
	return $html;
	
}  
add_filter('__wps__profile_menu_tabs', '__wps__add_mentions_menu_tabs', 10, 9);

// Add Menu item to Profile Menu through filter provided

function __wps__add_following_menu($html,$uid1,$uid2,$privacy,$is_friend,$extended,$share,$extra_class)  
{  
	global $current_user;

	if ( ((is_user_logged_in() && strtolower($share) == 'everyone') || (strtolower($share) == 'public') || (strtolower($share) == 'friends only' && $is_friend) || __wps__get_current_userlevel() == 5) ) {

		if ($uid1 == $uid2) {
			if (get_option(WPS_OPTIONS_PREFIX.'_menu_following'))
				$html .= '<div id="menu_plus" class="__wps__profile_menu '.$extra_class.'">'.(($t = get_option(WPS_OPTIONS_PREFIX.'_menu_following_text')) != '' ? $t :  __('I am Following', WPS_TEXT_DOMAIN)).'</div>';  
			if (get_option(WPS_OPTIONS_PREFIX.'_menu_followers'))
				$html .= '<div id="menu_plus_me" class="__wps__profile_menu '.$extra_class.'">'.(($t = get_option(WPS_OPTIONS_PREFIX.'_menu_followers_text')) != '' ? $t :  __('My Followers', WPS_TEXT_DOMAIN)).'</div>';  
		} else {
			if (get_option(WPS_OPTIONS_PREFIX.'_menu_following_other'))
				$html .= '<div id="menu_plus" class="__wps__profile_menu '.$extra_class.'">'.(($t = get_option(WPS_OPTIONS_PREFIX.'_menu_following_other_text')) != '' ? $t :  __('Following', WPS_TEXT_DOMAIN)).'</div>';  
			if (get_option(WPS_OPTIONS_PREFIX.'_menu_followers_other'))
				$html .= '<div id="menu_plus_me" class="__wps__profile_menu '.$extra_class.'">'.(($t = get_option(WPS_OPTIONS_PREFIX.'_menu_followers_other_text')) != '' ? $t :  __('Followers', WPS_TEXT_DOMAIN)).'</div>';  
		}
		
	}
	
	return $html;
	
}  
add_filter('__wps__profile_menu_filter', '__wps__add_following_menu', 10, 8);

function __wps__add_following_menu_tabs($html,$title,$value,$uid1,$uid2,$privacy,$is_friend,$extended,$share)  
{  
	if ($value == 'following') {
		
		global $current_user;
	
		if ( (($uid1 == $uid2) || (is_user_logged_in() && strtolower($share) == 'everyone') || (strtolower($share) == 'public') || (strtolower($share) == 'friends only' && $is_friend) || __wps__get_current_userlevel() == 5) )
			$html .= '<li id="menu_plus" class="__wps__profile_menu" href="javascript:void(0)">'.$title.'</li>';
			
	}
	
	return $html;
	
}  
add_filter('__wps__profile_menu_tabs', '__wps__add_following_menu_tabs', 10, 9);

function __wps__add_followers_menu_tabs($html,$title,$value,$uid1,$uid2,$privacy,$is_friend,$extended,$share)  
{  
	if ($value == 'followers') {
		
		global $current_user;
	
		if ( (($uid1 == $uid2) || (is_user_logged_in() && strtolower($share) == 'everyone') || (strtolower($share) == 'public') || (strtolower($share) == 'friends only' && $is_friend) || __wps__get_current_userlevel() == 5) )	
			$html .= '<li id="menu_plus_me" class="__wps__profile_menu" href="javascript:void(0)">'.$title.'</li>';
		
	}
	
	return $html;
	
}  
add_filter('__wps__profile_menu_tabs', '__wps__add_followers_menu_tabs', 10, 9);

function __wps__search($width='200')  
{  
	$width = 'style="width:'.$width.'px"';
   	$prompt = ($prompt = get_option('wps_site_search_prompt')) ? $prompt : __('Search...', WPS_TEXT_DOMAIN);
	
	$html = '<input type="text" id="__wps__member_small" '.$width.' 
				onblur="this.value=(this.value==\'\') ? \''.$prompt.'\' : this.value;" 
				onfocus="this.value=(this.value==\''.$prompt.'\') ? \'\' : this.value;" 
				value="'.$prompt.'" />';				
	
	return $html;
}

/* ====================================================== SET SHORTCODE ====================================================== */

// [symposium-following] (for profile page)
function __wps__profile_following()  
{  
	return __wps__show_profile("plus");
	exit;	
}
add_shortcode(WPS_SHORTCODE_PREFIX.'-following', '__wps__profile_following');  


if (!is_admin()) {
	add_shortcode(WPS_SHORTCODE_PREFIX.'-search', '__wps__search');  
}


?>
