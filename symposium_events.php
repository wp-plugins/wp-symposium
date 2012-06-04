<?php
/*
Plugin Name: WP Symposium Events
Plugin URI: http://www.wpsymposium.com
Description: <strong><a href="http://wpswiki.com/index.php?title=Bronze_membership">BRONZE PLUGIN</a></strong>. Create public or private events, invite other members, allow others to join, etc!
Version: 12.06.02
Author: WP Symposium
Author URI: http://www.wpsymposium.com
License: Commercial
Requires at least: WordPress 3.0 and WP Symposium 11.8.21
*/

define('WPS_EVENTS_VER', '12.06.02');
if(!defined('WPS_PLUS')) define('WPS_PLUS', '12.06.02');
	
/*  Copyright 2010,2011,2012  Simon Goodchild  (info@wpsymposium.com)

EULA stands for End User Licensing Agreement. This is the agreement through which the software is licensed to the software user. 

END-USER LICENSE AGREEMENT FOR WPS Groups 

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


function symposium_events_main() {

	global $wpdb, $current_user; 
	

	$html = '<div class="symposium-wrapper">';

		// Content
		$include = get_option("symposium_events_global_list");

		// get events
		$html .= '<div id="symposium_events_list" style="width:95%">';
		
			if ($include) {
				$sql = "SELECT e.*, u.ID, u.display_name FROM ".$wpdb->prefix."symposium_events e LEFT JOIN ".$wpdb->base_prefix."users u ON event_owner = ID WHERE event_owner IN (".$include.") AND event_live = 'on' ORDER BY event_start";
				$events = $wpdb->get_results($wpdb->prepare($sql));
			} else {
				$sql = "SELECT e.*, u.ID, u.display_name FROM ".$wpdb->prefix."symposium_events e LEFT JOIN ".$wpdb->base_prefix."users u ON event_owner = ID WHERE event_live = 'on' ORDER BY event_start";
				$events = $wpdb->get_results($wpdb->prepare($sql));
			}

			if (WPS_DEBUG) $html .= $wpdb->last_query;
			if ($events) {
				foreach ($events as $event) {
					$html .= '<div class="symposium_event_list_item row">';
					
						if ($event->event_google_map == 'on') {
							$html .= "<div id='event_google_profile_map' style='float:right; margin-left:5px; width:128px; height:128px'>";
							$html .= '<a target="_blank" href="http://maps.google.co.uk/maps?f=q&amp;source=embed&amp;hl=en&amp;geocode=&amp;q='.$event->event_location.'&amp;ie=UTF8&amp;hq=&amp;hnear='.$event->event_location.'&amp;output=embed&amp;z=5" alt="Click on map to enlarge" title="Click on map to enlarge">';
							$html .= '<img src="http://maps.google.com/maps/api/staticmap?center='.$event->event_location.'&zoom=5&size=128x128&maptype=roadmap&markers=color:blue|label:&nbsp;|'.$event->event_location.'&sensor=false" />';
							$html .= "</a></div>";
						}

						if ( ($event->event_owner == $current_user->ID) || (symposium_get_current_userlevel() == 5) ) {
							$html .= "<div class='symposium_event_list_item_icons'>";
							if ($event->event_live != 'on') {
								$html .= '<div style="font-style:italic;float:right;">'.__('Edit to publish', 'wp-symposium').'</div>';
							}
							$html .= "<a href='javascript:void(0)' class='symposium_delete_event floatright link_cursor' style='display:none;margin-right: 5px' id='".$event->eid."'><img src='".get_option('symposium_images')."/delete.png' /></a>";
							$html .= "<a href='javascript:void(0)' class='symposium_edit_event floatright link_cursor' style='display:none;margin-right: 5px' id='".$event->eid."'><img src='".get_option('symposium_images')."/edit.png' /></a>";
							$html .= "</div>";
						}
											
						$html .= '<div class="symposium_event_list_owner">'.__("Added by", "wp-symposium")." ".symposium_profile_link($event->ID).'</div>';
						$html .= '<div class="symposium_event_list_name">'.stripslashes($event->event_name).'</div>';
						$html .= '<div class="symposium_event_list_location">'.stripslashes($event->event_location).'</div>';
						if ($event->event_enable_places && $event->event_show_max) {
							$html .= '<div class="symposium_event_list_places">'.__('Maximum places:', 'wp-symposium').' '.$event->event_max_places.'</div>';
						}
						if ($event->cost !== null) {
							$html .= '<div class="symposium_event_cost">'.__('Cost per ticket:', 'wp-symposium').' '.$event->cost.'</div>';
						}
						$html .= '<div class="symposium_event_list_description">';
						$html .= stripslashes($event->event_description);
						$html .= '</div>';
						$html .= '<div class="symposium_event_list_dates">';
							if ($event->event_start != '0000-00-00 00:00:00') {
								$html .= date("D, d M Y", convert_datetime($event->event_start));
							}
							if ($event->event_start != $event->event_end) {
								if ($event->event_end != '0000-00-00 00:00:00') {
									$html .= ' &rarr; ';
									$html .= date("D, d M Y", convert_datetime($event->event_end));
								}
							}
						$html .= '</div>';
						$html .= '<div class="symposium_event_list_times">';
							if ($event->event_start_hours != 99) {
								$html .= __('Start: ', 'wp-symposium').$event->event_start_hours.":".sprintf('%1$02d', $event->event_start_minutes);
							}
							if ($event->event_end_hours != 99) {
								$html .= ' '.__('End: ', 'wp-symposium').$event->event_end_hours.":".sprintf('%1$02d', $event->event_end_minutes);
							}
						$html .= '</div>';

						if ($event->event_more) {
							$html .= '<div id="symposium_more_'.$event->eid.'" title="'.stripslashes($event->event_name).'" class="symposium-dialog-content">'.stripslashes($event->event_more).'</div>';
							$html .= '<input type="submit" id="symposium_event_more" rel="symposium_more_'.$event->eid.'" class="symposium-dialog symposium-button" value="'.__("More info", "wp-symposium").'" /><br />';
						}
						if (is_user_logged_in() && $event->event_enable_places) {
							$html .= '<div>';
								// check to see if already booked
								$sql = "select tickets, confirmed, bid FROM ".$wpdb->base_prefix."symposium_events_bookings WHERE event_id = %d AND uid = %d";
								$ret = $wpdb->get_row($wpdb->prepare($sql, $event->eid, $current_user->ID));
								if (!$ret->tickets) {
									$html .= '<input type="submit" id="symposium_book_event" data-eid="'.$event->eid.'" data-max="'.$event->event_tickets_per_booking.'" class="symposium-button" value="'.__("Book", "wp-symposium").'" /><br />';
								} else {
									$html .= '<input type="submit" id="symposium_cancel_event" data-eid="'.$event->eid.'"  class="symposium-button" value="'.__("Cancel", "wp-symposium").'" /><br />';
								}
								if ( !$ret->confirmed && $ret->tickets ) {
									$html .= '<input type="submit" id="symposium_pay_event" data-bid="'.$ret->bid.'"  class="symposium-button" value="'.__("Payment", "wp-symposium").'" /><br />';
									$html .= sprintf(_n('Awaiting confirmation from the organiser for %d ticket.','Awaiting confirmation from the organiser for %d tickets.', $ret->tickets, 'wp-symposium'), $ret->tickets);
								}
							$html .= '</div>';
						}
					$html .= '</div>';
				}
			}
		
		$html .= '</div>';		
		
	$html .= '</div>';

	// This filter allows others to filter content
	$html = apply_filters ( 'symposium_events_shortcode_filter', $html);
	
	// Send HTML
	return $html;
	
}

/* ===================================================================== ADMIN =========================================================================== */

// Check for updates
if ( ( get_option("symposium_events_version") != WPS_EVENTS_VER && is_admin()) ) {

 	// Update Version *******************************************************************************
	update_option("symposium_events_version", WPS_EVENTS_VER);
	symposium_events_activate();

}

function symposium_events_activate() {
	
	global $wpdb;

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    require_once(WP_PLUGIN_DIR . '/wp-symposium/symposium_functions.php');


	$table_name = $wpdb->base_prefix . "symposium_events";
//	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {

		$sql = "CREATE TABLE " . $table_name . " (
		  eid int(11) NOT NULL AUTO_INCREMENT,
		  event_name varchar(256) NOT NULL,
		  event_description text,
		  event_location varchar(256),
		  event_url varchar(256),
		  event_google_map varchar(2),
		  event_created datetime NOT NULL,
		  event_start datetime NOT NULL,
		  event_start_hours int(11) NOT NULL,
		  event_start_minutes int(11) NOT NULL,
		  event_end datetime NOT NULL,
		  event_end_hours int(11) NOT NULL,
		  event_end_minutes int(11) NOT NULL,
		  event_owner int(11) NOT NULL,
		  event_group int(11),
		  event_list_template text,
		  event_item_template text,
		  event_live varchar(2) NOT NULL DEFAULT 'on',
		  event_enable_places varchar(2),
		  event_max_places int(11),
		  event_show_max varchar(2),
		  event_tickets_per_booking int(11),
		  event_confirmation varchar(2),
		  event_send_email varchar(2),
		  event_email text,
		  event_cost varchar(16),
		  event_pay_link varchar(2048),
		  event_more text,
		  PRIMARY KEY (eid)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;";

	    dbDelta($sql);

//	}

	$table_name = $wpdb->base_prefix . "symposium_events_bookings";
	$sql = "CREATE TABLE " . $table_name . " (
	  bid int(11) NOT NULL AUTO_INCREMENT,
	  uid int(11) NOT NULL,
	  event_id int(11) NOT NULL,
	  confirmed varchar(2) NOT NULL,
	  booked datetime,
	  email_sent datetime,
	  payment_processed datetime,
	  tickets int(11),
	  PRIMARY KEY (bid)
	) CHARACTER SET utf8 COLLATE utf8_general_ci;";

    dbDelta($sql);
}

function symposium_events_deactivate() {
}

function symposium_events_uninstall() {	
}

register_activation_hook(__FILE__,'symposium_events_activate');
register_deactivation_hook(__FILE__, 'symposium_events_deactivate');
register_uninstall_hook(__FILE__, 'symposium_events_uninstall');

// ----------------------------------------------------------------------------------------------------------------------------------------------------------

function symposium_events_init()
{

}
add_action('init', 'symposium_events_init');




/* ================================================================== SET SHORTCODE ====================================================================== */

if (!is_admin()) {
	add_shortcode('symposium-events', 'symposium_events_main');  
}

/* ====================================================== HOOKS/FILTERS INTO WORDPRESS/WP SYMPOSIUM ====================================================== */

// Add Menu item to Profile Menu through filter provided
// The menu picks up the id of div with id of menu_ (eg: menu_lounge) and will then run
// 'path-to/wp-symposium/ajax/symposium_lounge_functions.php' when clicked.
// It will pass $_POST['action'] set to menu_lounge to that file to then be acted upon.
// See www.wpswiki.com for help

function add_events_menu($html,$uid1,$uid2,$privacy,$is_friend,$extended,$share)  
{  
	global $wpdb, $current_user;

	// Get included roles
	$dir_levels = strtolower(get_option('symposium_events_profile_include'));
	if (strpos($dir_levels, ' ') !== FALSE) $dir_levels = str_replace(' ', '', $dir_levels);
	if (strpos($dir_levels, '_') !== FALSE) $dir_levels = str_replace('_', '', $dir_levels);

	if (WPS_DEBUG) $html .= 'Events, allowed roles = '.$dir_levels.'<br />';
	
	// Check to see if this member is in the included list of roles
	$user = get_userdata( $current_user->ID );
	$capabilities = $user->{$wpdb->base_prefix.'capabilities'};
	
	if (WPS_DEBUG) $html .= 'Events, user capabilities = '.$capabilities.'<br />';

	$include = false;
	if ($capabilities) {
		
		foreach ( $capabilities as $role => $name ) {
			if ($role) {
				$role = strtolower($role);
				$role = str_replace(' ', '', $role);
				$role = str_replace('_', '', $role);
				if (WPS_DEBUG) $html .= 'Checking role '.$role.' against '.$dir_levels.'<br />';
				if (strpos($dir_levels, $role) !== FALSE) $include = true;
			}
		}		 														
	
	}	
	
	if ( ($include) && ( ($uid1 == $uid2) || (is_user_logged_in() && strtolower($privacy) == 'everyone') || (strtolower($privacy) == 'public') || (strtolower($privacy) == 'friends only' && $is_friend) || symposium_get_current_userlevel() == 5) ) {
  
		if ($uid1 == $uid2) {
			$html .= '<div id="menu_events" class="symposium_profile_menu">';
			$html .= __('My Events', 'wp-symposium'); 
			$html .= '</div>';  
		} else {
			$html .= '<div id="menu_events" class="symposium_profile_menu">';
			$html .= __('Events', 'wp-symposium'); 
			$html .= '</div>';  
		}
	}
	return $html;
}  
add_filter('symposium_profile_menu_filter', 'add_events_menu', 9, 7);

// ----------------------------------------------------------------------------------------------------------------------------------------------------------

// Add row to WPS installation page showing status of the Lounge plugin through hook provided
// 1. plugin title
// 2. shortcode
// 3. main function
// 4. internal URL path or -
// 5. main plugin file
// 6. admin page
// 7. install help text
function add_events_installation_row()
{
	install_row(
		__('Events', 'wp-symposium').' v'.get_option("symposium_events_version"), 
		'symposium-events', 
		'symposium_events_main',
		'-', 
		'wp-symposium/symposium_events.php', 
		'admin.php?page=wp-symposium/symposium_events_admin.php', 
		__('The Events plugin must be installed in ', 'wp-symposium').WP_PLUGIN_DIR.'/wp-symposium.'
	);

}
add_action('symposium_installation_hook', 'add_events_installation_row');

// ----------------------------------------------------------------------------------------------------------------------------------------------------------

// Add to WP Symposium admin menu via hook
function symposium_add_events_to_admin_menu()
{
	add_submenu_page('symposium_debug', __('Events', 'wp-symposium'), __('Events', 'wp-symposium'), 'manage_options', 'wp-symposium/symposium_events_admin.php');
}
add_action('symposium_admin_menu_hook', 'symposium_add_events_to_admin_menu');



?>
