<?php
/*
Plugin Name: WP Symposium Notification Alerts
Plugin URI: http://www.wpsymposium.com
Description: <strong><a href="http://wpswiki.com/index.php?title=Bronze_membership">BRONZE PLUGIN</a></strong>. Updates a menu item (or DIV) with alerts/notifications for the logged in member.
Version: 12.11
Author: Simon Goodchild
Author URI: http://www.wpsymposium.com
License: Commercial
Requires at least: WordPress 3.0 and WP Symposium 11.8.21
*/

define('WPS_NEWS_VER', '12.11');
if(!defined('WPS_PLUS')) define('WPS_PLUS', '12.11');
	
/*  Copyright 2010,2011,2012  Simon Goodchild  (info@wpsymposium.com)

EULA stands for End User Licensing Agreement. This is the agreement through which the software is licensed to the software user. 

END-USER LICENSE AGREEMENT FOR ALERTS PLUGINS

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


function __wps__news_main() {
	// This function is used to information Wordpress that it is activated.
	// Ties in with __wps__add_news_to_admin_menu() function below.		
}

// ----------------------------------------------------------------------------------------------------------------------------------------------------------

function __wps__news_add($author, $subject, $news) {

	global $wpdb,$current_user;

	if (	$wpdb->query( $wpdb->prepare( "
			INSERT INTO ".$wpdb->base_prefix."symposium_news
			( 	author,
				subject, 
				added,
				news
			)
			VALUES ( %d, %d, %s, %s )", 
	        array(
	        	$author,
				$subject, 
	        	date("Y-m-d H:i:s"),
	        	$news
	        	) 
        	) ) 
	) {
		return "OK";
	} else { 
		return $wpdb->last_query;
	}

}


/* ===================================================================== ADMIN =========================================================================== */

// Check for updates
if ( ( get_option(WPS_OPTIONS_PREFIX."_news_version") != WPS_NEWS_VER && is_admin()) ) {

 	// Update Version *******************************************************************************
	update_option(WPS_OPTIONS_PREFIX."_news_version", WPS_NEWS_VER);
	__wps__news_activate();	
}

function __wps__news_activate() {

	global $wpdb;

   	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    require_once(WPS_PLUGIN_DIR . '/functions.php');

	$table_name = $wpdb->base_prefix . "symposium_news";
	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {

		$sql = "CREATE TABLE " . $table_name . " (
		  nid int(11) NOT NULL AUTO_INCREMENT,
		  author int(11) NOT NULL DEFAULT 0,
		  subject int(11) NOT NULL DEFAULT 0,
		  added datetime NOT NULL,
		  news text NOT NULL,
		  new_item varchar(2) NOT NULL DEFAULT 'on',
		  PRIMARY KEY (nid)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;";

	    dbDelta($sql);
	
	}
	// Add index
	__wps__add_index($table_name, 'subject');
	__wps__add_index($table_name, 'author');
	
	// Default offset that can be changed via admin page
	if (get_option(WPS_OPTIONS_PREFIX."_news_x_offset") === FALSE) {
		update_option(WPS_OPTIONS_PREFIX."_news_x_offset", 0);
		update_option(WPS_OPTIONS_PREFIX."_news_y_offset", 0);
	}

	if (get_option(WPS_OPTIONS_PREFIX."_news_polling") === FALSE) {
		update_option(WPS_OPTIONS_PREFIX."_news_polling", 60);
	}
	
}

function __wps__news_deactivate() {
}

function __wps__news_uninstall() {
}

register_activation_hook(__FILE__,'__wps__news_activate');
register_deactivation_hook(__FILE__, '__wps__news_deactivate');
register_uninstall_hook(__FILE__, '__wps__news_uninstall');

// ----------------------------------------------------------------------------------------------------------------------------------------------------------

function __wps__news_init()
{
	if (!is_admin()) {
	}
}
function __wps__add_news_footer() {
	echo '<div id="__wps__news_polling" style="display:none">'.get_option(WPS_OPTIONS_PREFIX."_news_polling").'</div>';
}
add_action('init', '__wps__news_init');
add_action('wp_footer', '__wps__add_news_footer');

// ----------------------------------------------------------------------------------------------------------------------------------------------------------

/* ====================================================== HOOKS/FILTERS INTO WORDPRESS/WP Symposium ====================================================== */

// Add row to installation page showing status of the Alerts plugin through hook provided
function __wps__add_news_installation_row()
{
	__wps__install_row(__('Alerts', WPS_TEXT_DOMAIN).' v'.get_option(WPS_OPTIONS_PREFIX."_news_version"), WPS_SHORTCODE_PREFIX.'-alerts', '__wps__news_main', '-', 'wp-symposium/news.php', 'admin.php?page='.WPS_DIR.'/news_admin.php', 
	__('The News plugin must be installed in ', WPS_TEXT_DOMAIN).WPS_PLUGIN_DIR.". The page title should be <div id='__wps__alerts'>Alerts</div>");
}
add_action('__wps__installation_hook', '__wps__add_news_installation_row');

// ----------------------------------------------------------------------------------------------------------------------------------------------------------

// Add "Alerts" to admin menu via hook
function __wps__add_news_to_admin_menu()
{
	$hidden = get_option(WPS_OPTIONS_PREFIX.'_long_menu') == "on" ? '_hidden': '';
	add_submenu_page('symposium_debug'.$hidden, __('Alerts', WPS_TEXT_DOMAIN), __('Alerts', WPS_TEXT_DOMAIN), 'manage_options', WPS_DIR.'/news_admin.php');
}
add_action('__wps__admin_menu_hook', '__wps__add_news_to_admin_menu');

// ----------------------------------------------------------------------------------------------------------------------------------------------------------

function __wps__news_offsets() {
	// Place Alerts offset settings in DOM so accessible via Javascript
 	echo "<div id='__wps__news_y_offset' style='display:none'>".get_option(WPS_OPTIONS_PREFIX."_news_x_offset")."</div>";
	echo "<div id='__wps__news_y_offset' style='display:none'>".get_option(WPS_OPTIONS_PREFIX."_news_y_offset")."</div>";
}
add_action('wp_footer', '__wps__news_offsets');

// ----------------------------------------------------------------------------------------------------------------------------------------------------------

// Add [symposium-alerts] shortcode for history of news items
function __wps__alerts_history() {	

	global $wpdb, $current_user;
	$html = "";
	
	if (is_user_logged_in()) {

		// Get link to profile page
		$profile_url = __wps__get_url('profile');
		if (strpos($profile_url, '?') !== FALSE) {
			$q = "&";
		} else {
			$q = "?";
		}
	
		// Wrapper
		$html .= "<div class='__wps__wrapper'>";

		$sql = "SELECT n.*, u.display_name FROM ".$wpdb->base_prefix."symposium_news n 
			LEFT JOIN ".$wpdb->base_prefix."users u ON n.author = u.ID 
			WHERE subject = %d 
			ORDER BY nid DESC LIMIT 0,50";
		$news = $wpdb->get_results($wpdb->prepare($sql, $current_user->ID));

		if ($news) {
			foreach ($news as $item) {

				$html .= "<div class='__wps__news_history_row'>";
					$html .= "<div class='__wps__news_history_avatar'>";
					$html .= '<a href="'.$profile_url.$q.'uid='.$item->author.'">'.get_avatar($item->author, 40).'</a>';
					$html .= '</div>';
					$html .= "<div class='__wps__news_history_avatar'>";
					$html .= $item->news;
					$html .= "<br /><span class='__wps__news_history_ago'>".__wps__time_ago($item->added)."</span>";
					$html .= ' '.__('by', WPS_TEXT_DOMAIN).' <a href="'.$profile_url.$q.'uid='.$item->author.'">'.stripslashes($item->display_name).'</a>';
					$html .= "</div>";
				$html .= "</div>";
			}
		} else {

			$html .= __("Nothing to show yet.", WPS_TEXT_DOMAIN);

		}
		$html .= "</div>";
		// End Wrapper
	
		$html .= "<div style='clear: both'></div>";
	
		// Clear read news items
		$wpdb->query("UPDATE ".$wpdb->base_prefix."symposium_news SET new_item = '' WHERE subject = ".$current_user->ID);

	} else {
		
		$html .= __("Please login, thank you.", WPS_TEXT_DOMAIN);
		
	}

	// Send HTML
	return $html;

}
if (!is_admin()) {
	add_shortcode(WPS_SHORTCODE_PREFIX.'-alerts', '__wps__alerts_history');  
}

/* ====================================================== ALERTS (if available) ====================================================== */


// Add news item that a poke was sent
function __wps__send_poke($message_to, $message_from, $from_name, $poke, $cid) {
	$url = __wps__get_url('profile');
	$message = $from_name.__(' has sent you a ', WPS_TEXT_DOMAIN).$poke;
	__wps__news_add($message_from, $message_to, "<a href='".$url.__wps__string_query($url)."uid=".$message_from."&post=".$cid."'>".$message."</a>");
}
add_filter('__wps__send_poke_filter', '__wps__send_poke', 10, 5);

// ----------------------------------------------------------------------------------------------------------------------------------------------------------

// Add news item that mail was sent
function __wps__news_add_message($message_to, $message_from, $from_name, $mail_id) {
	$url = __wps__get_url('mail');
	__wps__news_add($message_from, $message_to, "<a href='".$url.__wps__string_query($url)."mid=".$mail_id."'>".__("You have a new message from", WPS_TEXT_DOMAIN)." ".$from_name."</a>");
}
add_filter('__wps__sendmessage_filter', '__wps__news_add_message', 10, 4);

// ----------------------------------------------------------------------------------------------------------------------------------------------------------

// Add news item that friend request was made
function __wps__news_add_friendrequest($message_to, $message_from, $from_name) {
	$url = __wps__get_url('profile');
	__wps__news_add($message_from, $message_to, "<a href='".$url.__wps__string_query($url)."view=friends'>".sprintf(__("New %s request from", WPS_TEXT_DOMAIN), get_option(WPS_OPTIONS_PREFIX.'_alt_friend'))." ".$from_name."</a>");
}
add_filter('__wps__friendrequest_filter', '__wps__news_add_friendrequest', 10, 3);

// ----------------------------------------------------------------------------------------------------------------------------------------------------------

// Add news item that friend request was accepted
function __wps__news_add_friendaccepted($message_to, $message_from, $from_name) {
	$url = __wps__get_url('profile');
	__wps__news_add($message_from, $message_to, "<a href='".$url.__wps__string_query($url)."view=friends'>".sprintf(__("%s request accepted by", WPS_TEXT_DOMAIN), get_option(WPS_OPTIONS_PREFIX.'_alt_friend'))." ".$from_name."</a>");
}
add_filter('__wps__friendaccepted_filter', '__wps__news_add_friendaccepted', 10, 3);

// ----------------------------------------------------------------------------------------------------------------------------------------------------------

// Add news item that new forum topic posted (when subscribed)
function __wps__news_add_newtopic($message_to, $from_id, $from_name, $url) {
	__wps__news_add($from_id, $message_to, "<a href='".$url."'>".__("Subscribed forum topic by", WPS_TEXT_DOMAIN)." ".$from_name."</a>");
}
add_filter('__wps__forum_newtopic_filter', '__wps__news_add_newtopic', 10, 4);

// ----------------------------------------------------------------------------------------------------------------------------------------------------------

// Add news item that new forum reply posted (when subscribed)
function __wps__news_add_newreply($message_to, $message_from, $from_name, $url) {
	if ($message_to != $message_from) {
		__wps__news_add($message_from, $message_to, "<a href='".$url."'>".__("Subscribed forum reply by", WPS_TEXT_DOMAIN)." ".$from_name."</a>");
	}
}
add_filter('__wps__forum_newreply_filter', '__wps__news_add_newreply', 10, 4);

// ----------------------------------------------------------------------------------------------------------------------------------------------------------

// Add news item that new forum reply comment added (when subscribed)
function __wps__news_add_newreplycomment($message_to, $message_from, $from_name, $url) {
	if ($message_to != $message_from) {
		__wps__news_add($message_from, $message_to, "<a href='".$url."'>".__("Subscribed forum comment by", WPS_TEXT_DOMAIN)." ".$from_name."</a>");
	}
}
add_filter('__wps__forum_newreplycomment_filter', '__wps__news_add_newreplycomment', 10, 4);

// ----------------------------------------------------------------------------------------------------------------------------------------------------------

// Add news item that new post has been posted on member's profile
function __wps__news_add_wall_newpost($post_to, $post_from, $from_name) {
	if ($post_to != $post_from) {
		__wps__news_add($post_from, $post_to, "<a href='".__wps__get_url('profile')."'>".$from_name." ".__("has posted on your profile.", WPS_TEXT_DOMAIN)."</a>");
	}
}
add_filter('__wps__wall_newpost_filter', '__wps__news_add_wall_newpost', 10, 3);

// ----------------------------------------------------------------------------------------------------------------------------------------------------------

// Add news item that new comment has been added as a reply to a post on member's profile
function __wps__news_add_wall_reply($first_post_subject, $first_post_author, $from_id, $from_name, $url) {
	global $current_user;

	if ($first_post_subject != $current_user->ID) {
		__wps__news_add($from_id, $first_post_subject, "<a href='".$url."'>".$from_name." ".__("has replied to a post on your profile", WPS_TEXT_DOMAIN)."</a>");
	} else {
		if ($first_post_author != $current_user->ID) {
			__wps__news_add($from_id, $first_post_author, "<a href='".$url."'>".$from_name." ".__("has replied to a post you started", WPS_TEXT_DOMAIN)."</a>");
		}
	}

}
add_filter('__wps__wall_postreply_filter', '__wps__news_add_wall_reply', 10, 5);

// ----------------------------------------------------------------------------------------------------------------------------------------------------------

// Add news item that new comment has been added as a reply to a post this member is involved in
function __wps__news_add_wall_reply_involved_in($post_to, $post_from, $from_name, $url) {
	if ($post_to != $post_from) {
		__wps__news_add($post_from, $post_to, "<a href='".$url."'>".$from_name." ".__("has replied to a post you are involved in", WPS_TEXT_DOMAIN)."</a>");
	}
}
add_filter('__wps__wall_postreply_involved_filter', '__wps__news_add_wall_reply_involved_in', 10, 4);



?>
