<?php
/*
WP Symposium Lounge
Description: Shoutbox plugin compatible with WP Symposium. Put [symposium-lounge] on any WordPress page. Also acts as demonstration for WP Symposium development - see www.wpswiki.com.
*/

define('WPS_LOUNGE_VER', '12.12');
if(!defined('WPS_PLUS')) define('WPS_PLUS', '12.12');
	
/*  Copyright 2010,2011,2012  Simon Goodchild  (info@wpsymposium.com)

EULA stands for End User Licensing Agreement. This is the agreement through which the software is licensed to the software user. 

END-USER LICENSE AGREEMENT FOR LOUNGE PLUGINS

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


/* ====================================================================== MAIN =========================================================================== */

// Get constants
require_once(dirname(__FILE__).'/default-constants.php');

function __wps__lounge_main() {
	// This function is also used to information Wordpress that it is activated.
	// Ties in with __wps__add_lounge_to_admin_menu() function below.

	// The following duplicates the AJAX code in lounge_functions.php (ref. // Start lounge content)
	$html = '<div class="__wps__wrapper">';

		// This filter allows others to add text (or whatever) above the output
		$html = apply_filters ( '__wps__lounge_filter_top', $html);

		if (is_user_logged_in()) {
	
			// Display the comment form
			$html .= '<div id="__wps__lounge_add_comment_div">';
			$html .= '<input type="text" class="input-field" id="__wps__lounge_add_comment" onblur="this.value=(this.value==\'\') ? \''.__("Add a comment..", WPS_TEXT_DOMAIN).'\' : this.value;" onfocus="this.value=(this.value==\''.__("Add a comment..", WPS_TEXT_DOMAIN).'\') ? \'\' : this.value;" value="'.__("Add a comment..", WPS_TEXT_DOMAIN).'">';
			$html .= '&nbsp;<input id="__wps__lounge_add_comment_button" type="submit" class="__wps__button" value="'.__('Add', WPS_TEXT_DOMAIN).'" /> ';
			$html .= '</div>';
		
		}

		// Prepare for the output (which is created via AJAX)
		$html .= '<div id="__wps__lounge_div">';
		$html .= "<img src='".get_option(WPS_OPTIONS_PREFIX.'_images')."/busy.gif' />";
		$html .= '</div>';
	
	$html .= '</div>';
	
	// Send HTML
	return $html;
	
}


/* ===================================================================== ADMIN =========================================================================== */

// Check for updates
if ( ( get_option(WPS_OPTIONS_PREFIX."_lounge_version") != WPS_LOUNGE_VER && is_admin()) ) {

 	// Update Version *******************************************************************************
	update_option(WPS_OPTIONS_PREFIX."_lounge_version", WPS_LOUNGE_VER);
	__wps__lounge_activate();	

}

function __wps__lounge_activate() {
	
	global $wpdb;

   	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    require_once(WPS_PLUGIN_DIR . '/functions.php');

	// Create lounge table in main table in WordPress so available across all sites
	// To change to site specific in a Multisite installation, change base_prefix to just prefix

	$table_name = $wpdb->base_prefix . "symposium_lounge";
	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {

		$sql = "CREATE TABLE " . $table_name . " (
		  lid int(11) NOT NULL AUTO_INCREMENT,
		  author int(11) NOT NULL DEFAULT 0,
		  added datetime NOT NULL,
		  comment text NOT NULL,
		  PRIMARY KEY (lid)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;";

	    dbDelta($sql);
	
	}

}

function __wps__lounge_deactivate() {
}

function __wps__lounge_uninstall() {
}

register_activation_hook(__FILE__,'__wps__lounge_activate');
register_deactivation_hook(__FILE__, '__wps__lounge_deactivate');
register_uninstall_hook(__FILE__, '__wps__lounge_uninstall');

// ----------------------------------------------------------------------------------------------------------------------------------------------------------

function __wps__lounge_init()
{
}
add_action('init', '__wps__lounge_init');

// ----------------------------------------------------------------------------------------------------------------------------------------------------------



/* ================================================================== SET SHORTCODE ====================================================================== */

if (!is_admin()) {
	add_shortcode(WPS_SHORTCODE_PREFIX.'-lounge', '__wps__lounge_main');  
}

/* ====================================================== HOOKS/FILTERS INTO WORDPRESS/WP Symposium ====================================================== */

// Add Menu item to Profile Menu through filter provided
// The menu picks up the id of div with id of menu_ (eg: menu_lounge) and will then run
// 'path-to/wp-symposium/ajax/lounge_functions.php' when clicked.
// It will pass $_POST['action'] set to menu_lounge to that file to then be acted upon.
// See www.wpswiki.com for help

function __wps__add_lounge_menu($html,$uid1,$uid2,$privacy,$is_friend,$extended,$share,$extra_class)  
{  
	global $current_user;

	// Do a check that user is logged in, if so create the HTML to add to the menu
	if (is_user_logged_in()) {  

		if ( ($uid1 == $uid2) || (is_user_logged_in() && strtolower($privacy) == 'everyone') || (strtolower($privacy) == 'public') || (strtolower($privacy) == 'friends only' && $is_friend) || __wps__get_current_userlevel() == 5) {
	
			if ($uid1 == $uid2) {
				if (get_option(WPS_OPTIONS_PREFIX.'_menu_lounge'))
					$html .= '<div id="menu_lounge" class="__wps__profile_menu '.$extra_class.'">'.(($t = get_option(WPS_OPTIONS_PREFIX.'_menu_lounge_text')) != '' ? $t :  __('The Lounge', WPS_TEXT_DOMAIN)).'</div>';  
			} else {
				if (get_option(WPS_OPTIONS_PREFIX.'_menu_lounge_other'))
					$html .= '<div id="menu_lounge" class="__wps__profile_menu '.$extra_class.'">'.(($t = get_option(WPS_OPTIONS_PREFIX.'_menu_lounge_other_text')) != '' ? $t :  __('The Lounge', WPS_TEXT_DOMAIN)).'</div>';  
			}
		}
		
	}
	return $html;
}  
add_filter('__wps__profile_menu_filter', '__wps__add_lounge_menu', 10, 8);

function __wps__add_lounge_menu_tabs($html,$title,$value,$uid1,$uid2,$privacy,$is_friend,$extended,$share)  
{  
	
	if ($value == 'lounge') {
		

		global $current_user;
	
		// Do a check that user is logged in, if so create the HTML to add to the menu
		if (is_user_logged_in()) {  
	
			if ( ($uid1 == $uid2) || (is_user_logged_in() && strtolower($privacy) == 'everyone') || (strtolower($privacy) == 'public') || (strtolower($privacy) == 'friends only' && $is_friend) || __wps__get_current_userlevel() == 5) 
				$html .= '<li id="menu_lounge" class="__wps__profile_menu" href="javascript:void(0)">'.$title.'</a></li>';
			
		}
		
	}
	
	return $html;
}  
add_filter('__wps__profile_menu_tabs', '__wps__add_lounge_menu_tabs', 10, 9);

// ----------------------------------------------------------------------------------------------------------------------------------------------------------

// Add "The Lounge" to admin menu via hook
function __wps__add_lounge_to_admin_menu()
{
	$hidden = get_option(WPS_OPTIONS_PREFIX.'_long_menu') == "on" ? '_hidden': '';
	add_submenu_page('symposium_debug'.$hidden, __('The Lounge', WPS_TEXT_DOMAIN), __('The Lounge', WPS_TEXT_DOMAIN), 'edit_themes', WPS_DIR.'/lounge_admin.php');
}
add_action('__wps__admin_menu_hook', '__wps__add_lounge_to_admin_menu');


?>
