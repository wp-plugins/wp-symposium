<?php
/*
WP Symposium RSS Feed
Description:  Plugin to provide RSS feed of profile pages (to follow members activity, if they have permitted it) - see www.wpswiki.com.
*/

define('WPS_RSS_VER', '12.12');
if(!defined('WPS_PLUS')) define('WPS_PLUS', '12.12');
	
/*  Copyright 2010,2011,2012  Simon Goodchild  (info@wpsymposium.com)

EULA stands for End User Licensing Agreement. This is the agreement through which the software is licensed to the software user. 

END-USER LICENSE AGREEMENT FOR RSS FEED PLUGIN

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

/* ====================================================================== MAIN =========================================================================== */


function __wps__rss_main() {
	// Although there is nothing to put here, it is used to information Wordpress that it is activated.
}


/* ===================================================================== ADMIN =========================================================================== */

// Check for updates
if ( ( get_option(WPS_OPTIONS_PREFIX."_rss_version") != WPS_RSS_VER && is_admin()) ) {

 	// Update Version *******************************************************************************
	update_option(WPS_OPTIONS_PREFIX."_rss_version", WPS_RSS_VER);
	__wps__rss_activate();

}

function __wps__rss_activate() {

    require_once(WPS_PLUGIN_DIR . '/functions.php');
	
	// Added field to user meta
	if (get_option(WPS_OPTIONS_PREFIX.'_rss_share') === false)
		update_option(WPS_OPTIONS_PREFIX.'_rss_share', ''); 
	
}

function __wps__rss_deactivate() {
}

function __wps__rss_uninstall() {
}

register_activation_hook(__FILE__,'__wps__rss_activate');
register_deactivation_hook(__FILE__, '__wps__rss_deactivate');
register_uninstall_hook(__FILE__, '__wps__rss_uninstall');

// ----------------------------------------------------------------------------------------------------------------------------------------------------------

function __wps__rss_init()
{

}
add_action('init', '__wps__rss_init');

// ----------------------------------------------------------------------------------------------------------------------------------------------------------


/* ================================================================== SET SHORTCODE ====================================================================== */

// Not applicable.

/* ====================================================== HOOKS/FILTERS INTO WORDPRESS/WP Symposium ====================================================== */

function __wps__add_rss_icon($html,$uid1,$uid2,$privacy,$is_friend,$extended)  
{  

	global $wpdb;
	$rss_share = __wps__get_meta($uid1, 'rss_share');

	if ($rss_share == 'on') {

		$display_name = $wpdb->get_var($wpdb->prepare("SELECT display_name FROM ".$wpdb->base_prefix."users WHERE ID = %d", $uid1));

		$html = "<div id='__wps__rss_icon' title='".$display_name."'></div>".$html;
	}		
	return $html;
}  
add_filter('__wps__profile_wall_header_filter', '__wps__add_rss_icon', 10, 6);

// ----------------------------------------------------------------------------------------------------------------------------------------------------------


?>
