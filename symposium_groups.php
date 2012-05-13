<?php
/*
Plugin Name: WP Symposium Groups
Plugin URI: http://www.wpsymposium.com
Description: <strong><a href="http://wpswiki.com/index.php?title=Bronze_membership">BRONZE PLUGIN</a></strong>. Groups Directory and Page plugin compatible with WP Symposium. Put [symposium-groups] and [symposium-group] on any WordPress page.
Version: 12.05.13
Author: WP Symposium
Author URI: http://www.wpsymposium.com
License: Commercial
Requires at least: WordPress 3.0 and WP Symposium 11.8.21
*/

define('WPS_GROUPS_VER', '12.05.13');
if(!defined('WPS_PLUS')) define('WPS_PLUS', '12.05.13');
	
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

/* ***************************************************** GROUP PAGE ***************************************************** */

global $wpdb;

// [symposium-group] (wall)
function symposium_group()  
{  
	        			
	return symposium_show_group("wall");
	exit;
		
}

// [symposium-group-members]
function symposium_group_members()  
{  

	return symposium_show_group("members");
	exit;
		
}

// [symposium-group-settings]
function symposium_group_settings()  
{  
										
	return symposium_show_group("settings");
	exit;
		
}


// Adds group page
function symposium_show_group($page)  
{  

	global $wpdb, $current_user;

	$gid = '';

	if (isset($_GET['gid'])) {
		$gid = $_GET['gid'];
	} else {
		if (isset($_POST['gid'])) {
			$gid = $_POST['gid'];
		}
	}
	
	$group_url = symposium_get_url('group');
	if (strpos($group_url, '?') !== FALSE) {
		$q = "&";
	} else {
		$q = "?";
	}
	
	// Check if private or public
	$sql = "SELECT private FROM ".$wpdb->prefix."symposium_groups WHERE gid = %d";
	$private = $wpdb->get_var($wpdb->prepare($sql, $gid));

	if (is_user_logged_in()) {
		
		if ($gid != '') {
			
			// Wrapper
			$html = "<div class='symposium-wrapper'>";
					
				$plugin = WP_PLUGIN_URL.'/wp-symposium';

				$group = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix . 'symposium_groups WHERE gid='.$gid));

				// Get template
				$template = get_option('symposium_template_group');
				$template = str_replace("[]", "", stripslashes($template));
				
				// Buttons									
				$buttons = "";
				$member_of = symposium_member_of($gid);
				
				if (is_user_logged_in()) {
				
					if ($member_of != "yes") {
						
						if ($member_of == "no") {

							// Not a member, or pending, so show join button
							if ($group->private != "on") {
								$buttons .='<input type="submit" value="'.__("Join Group", "wp-symposium").'" id="groups_join_button" class="symposium-button">';
								$buttons .='<p id="groups_join_button_done" style="padding:6px;display:none">'.__('You are now a member of this group.', 'wp-symposium').'</p>';
							} else {
								$buttons .='<input type="submit" value="'.__("Request to Join", "wp-symposium").'" id="groups_join_button" class="symposium-button">';
								$buttons .='<p id="groups_join_button_done" style="padding:6px;display:none">'.__('Your membership is awaiting approval.', 'wp-symposium').'</p>';
							}

						} else {
							
							// Asked to join, waiting for decision
							$buttons .= "<p>".__("Your request to join is awaiting approval.", "wp-symposium")."</p>";

						}
									
					} else {

						if (symposium_group_admin($gid) != "yes") {
							// Is a member, so show leave button (if not an admin)
							$buttons .='<input type="submit" value="'.__("Leave Group", "wp-symposium").'" id="groups_leave_button" class="symposium-button">';
							$buttons .='<p id="groups_leave_button_done" style="padding:6px;display:none">'.__('You are no longer a member of this group.', 'wp-symposium').'</p>';
						}
						
					}

					if (symposium_group_admin($gid) == "yes" || symposium_get_current_userlevel() == 5) {
						// Admin, so can delete group
						if (symposium_get_current_userlevel() == 5) {
							$buttons .= '<input type="submit" value="'.__("Delete Group", "wp-symposium").'" id="groups_delete_button" class="symposium-button">';
							$buttons .='<p id="groups_delete_button_done" style="padding:6px;display:none">'.__('Group deleted.', 'wp-symposium').'</p>';
						} else {
							$buttons .= '<input type="submit" title="'.$gid.'" value="'.__("Delete Group", "wp-symposium").'" id="groups_delete_button_request" class="symposium-button">';
						}
					} 
				
				} else {
				
					$buttons = "";
				
				}
				// Replace Header Codes
				$template = str_replace("[group_name]", stripslashes($group->name), $template);
				$template = str_replace("[group_description]", stripslashes($group->description), $template);
				$template = str_replace("[actions]", $buttons, $template);

				// Avatar
				if (strpos($template, '[avatar') !== FALSE) {
					if (strpos($template, '[avatar]')) {
						$template = str_replace("[avatar]", get_group_avatar($gid, 200), $template);						
					} else {
						$x = strpos($template, '[avatar');
						$avatar = substr($template, 0, $x);
						$avatar2 = substr($template, $x+8, 3);
						$avatar3 = substr($template, $x+12, strlen($template)-$x-12);

						$template = $avatar . get_group_avatar($gid, $avatar2) . $avatar3;
					}
				}
				
				// Menu
				$menu = "";
				$menu .= '<div id="group_menu_all" class="symposium_group_menu">'.__('All Groups', 'wp-symposium').'</div>';
				if ($member_of == "yes" || $group->content_private != "on") {
					$menu .= '<div id="group_menu_wall" class="symposium_group_menu">'.__('Group Activity', 'wp-symposium').'</div>';
					if ($group->group_forum == "on") {
						$menu .= '<div id="group_menu_forum" class="symposium_group_menu">'.__('Group Forum', 'wp-symposium').'</div>';
					}
					$menu .= '<div id="group_menu_members" class="symposium_group_menu">'.__('Group Members', 'wp-symposium').'</div>';
				}
				if (symposium_group_admin($gid) == "yes" || symposium_get_current_userlevel() == 5) {
					$menu .= '<div id="group_menu_settings" class="symposium_group_menu">'.__('Group Settings', 'wp-symposium').'</div>';
					if (get_option('symposium_group_invites') == 'on') {
						$menu .= '<div id="group_menu_invites" class="symposium_group_menu">'.__('Group Invites', 'wp-symposium').'</div>';
					}
				}
				$template = str_replace("[menu]", $menu, $template);

				// Body
				if ($member_of == "yes" || $group->content_private != "on") {
					$template = str_replace("[page]", "<img src='".get_option('symposium_images')."/busy.gif' />", $template);
					$template = str_replace("[default]", "wall", $template);
				} else {
					$private_link = __("This group's activity is private.", "wp-symposium");
					if (!is_user_logged_in()) {
						$private_link .= " <a href=".wp_login_url( $group_url.$q.'gid='.$gid )." class='simplemodal-login' title='".__("Login", "wp-symposium")."'>".__("Login", "wp-symposium").".</a>";
					}
					$template = str_replace("[page]", $private_link, $template);
					$template = str_replace("[default]", "", $template);
				}
				$template .= "<br class='clear' />";
				
				$html .= $template;
					

			$html .= "</div>"; // End of Wrapper
			$html .= "<br class='clear' />";
						
		} else {
			
			$html = __("Group not found, sorry.", "wp-symposium");
		}
		
	} else {
		
		$html = __("You need to log in to access this group.", "wp-symposium");
		
	}
	
	return $html;								
	exit;

}  

/* ***************************************************** GROUPS ***************************************************** */

function symposium_groups() {	
	
	
	global $wpdb, $current_user;
	
	$dbpage = WP_PLUGIN_URL.'/wp-symposium/ajax/symposium_groups_functions.php';

	// View (and set tabs)
	if (!isset($_GET['view']) || $_GET['term'] != '') {
		$browse_active = 'active';
		$create_active = 'inactive';
		$view = "browse";
	} 
	if ( isset($_GET['view']) && $_GET['view'] == "create") {
		$browse_active = 'inactive';
		$create_active = 'active';
		$view = "create";
	} 

	$thispage = get_permalink();
	if ($thispage[strlen($thispage)-1] != '/') { $thispage .= '/'; }

	$group_url = get_option('symposium_group_url');
	$group_all_create = get_option('symposium_group_all_create');

	if (isset($_GET['page_id']) && $_GET['page_id'] != '') {
		// No Permalink
		$thispage = $group_url;
		$q = "&";
	} else {
		$q = "?";
	}

	if (isset($_GET['term'])) {
		$term = $_GET['term'];
	} else {
		$term = '';
	}

	$html = '<div class="symposium-wrapper">';

		if ( (is_user_logged_in()) && ($group_all_create == "on" || symposium_get_current_userlevel() == 5) ) {

			$html .= "<input type='submit' id='show_create_group_button' class='symposium-button' value='".__("Create Group", "wp-symposium")."'>";

			$html .= "<div id='create_group_form' style='display:none'>";
				$html .= "<div>";
				$html .= "<strong>".__("Name of Group", "wp-symposium")."</strong><br />";
				$html .= "<input type='text' id='name_of_group' class='new-topic-subject-input' style='width: 50%'>";
				$html .= "</div>";

				$html .= "<div>";
				$html .= "<strong>".__("Description", "wp-symposium")."</strong><br />";
				$html .= "<input type='text' id='description_of_group' style='width: 99%'>";
				$html .= "</div>";

				$html .= "<div style='margin-top:10px'>";
				$html .= "<input type='submit' id='create_group_button' class='symposium-button' value='".__("Create", "wp-symposium")."'>";
				$html .= "<input type='submit' id='cancel_create_group_button' class='symposium-button' value='".__("Cancel", "wp-symposium")."'>";
				$html .= "</div>";
			$html .= "</div>";

		}
		
		$html .= "<div id='groups_results'>";
		
		if ( $term != '' ) {
	
			$me = $current_user->ID;
			$page = 1;
			$page_length = 25;
	
			$html .= '<form method="post" action="'.$dbpage.'"> ';
	
			$term = "";
			if (isset($_POST['group'])) { $term .= $_POST['group']; }
			if (isset($_GET['term'])) { $term .= $_GET['term']; }

			$html .= '<input type="text" id="group" name="group" class="groups_search_box" value="'.$term.'" />';
			$html .= '<input type="hidden" id="group_id" name="group_id" />';
			$html .= '<div style="float:right; padding:0px;">';
			$html .= '<input id="groups_go_button" type="submit" class="symposium-button" value="'.__("Go", "wp-symposium").'" />';
			$html .= '</div>';
	
			$html .= '</form>';
		
			$sql = "SELECT g.*, (SELECT COUNT(*) FROM ".$wpdb->prefix."symposium_group_members WHERE group_id = g.gid) AS member_count
			FROM ".$wpdb->prefix."symposium_groups g WHERE  
			( g.name LIKE '%".$term."%') OR 
			( g.description LIKE '%".$term."%' )
			ORDER BY last_activity DESC LIMIT 0,25";
			
			$groups = $wpdb->get_results($sql);


			if ($groups) {
				
				foreach ($groups as $group) {

					if (symposium_member_of($group->gid) == 'yes') { 
						$html .= "<div class='groups_row row_odd corners'>";
					} else {
						$html .= "<div class='groups_row row corners'>";
					}					
					
						$html .= "<div class='groups_avatar'>";
							$html .= get_group_avatar($group->gid, 64);
						$html .= "</div>";

						$html .= "<div class='group_name'>";
						$html .= "<a class='row_link' href='".symposium_get_url('group')."?gid=".$group->gid."'>".$group->name."</a>";
						$html .= "</div>";
						
						$html .= "<div class='group_member_count'>";
						$html .= __("Member Count:", "wp-symposium")." ".$group->member_count;
						if ($group->last_activity) {
							$html .= '<br /><em>'.__('last active', 'wp-symposium').' '.symposium_time_ago($group->last_activity)."</em>";
						}
						$html .= "</div>";
					
						$html .= "<div class='group_description'>";
						$html .= $group->description;
						$html .= "</div>";
						
					$html .= "</div>";
					
				}
	
			}
			
		} else {
	
			$html .= '<form method="post" action="'.$dbpage.'"> ';
	
			$html .= '<input type="text" id="group" name="group" class="groups_search_box" onfocus="this.value = \'\';" value="" />';
			$html .= '<input type="hidden" id="group_id" name="group_id" />';

			$html .= '<div style="float:right; padding:0px;">';
			$html .= '<input id="groups_go_button" type="submit" class="symposium-button" value="'.__("Go", "wp-symposium").'" />';
			$html .= '</div>';
	
			
			$html .= '</form>';
	
			$html .= "<div id='symposium_groups'><img src='".get_option('symposium_images')."/busy.gif' /></div>";
			
		}
		
		$html .= "</div>"; // End of Groups Results
		
		if (isset($groups) && !$groups) 
				$html .= "<div style='clear:both'>".__("No group found....", "wp-symposium")."</div>";
		
	$html .= '</div>'; // End of Wrapper
	
	// Send HTML
	return $html;

}

/* ====================================================== ADMIN ====================================================== */

// Check for updates
if ( ( get_option("symposium_groups_version") != WPS_GROUPS_VER && is_admin()) ) {

 	// Update Version *******************************************************************************
	symposium_groups_activate();
	update_option("symposium_groups_version", WPS_GROUPS_VER);
	
}

// Add plugin to WP Symposium admin menu via hook
function symposium_add_groups_to_admin_menu()
{
	add_submenu_page('symposium_debug', __('Groups', 'wp-symposium'), __('Groups', 'wp-symposium'), 'manage_options', 'wp-symposium/symposium_groups_admin.php');
}
add_action('symposium_admin_menu_hook', 'symposium_add_groups_to_admin_menu');

// Add row to WPS installation page showing status of the Lounge plugin through hook provided
// 1. plugin title
// 2. shortcode
// 3. main function
// 4. internal URL path or -
// 5. main plugin file
// 6. admin page
// 7. install help text
function add_groups_installation_row()
{
	global $wpdb;
	
	install_row('Groups v'.get_option("symposium_groups_version"), 'symposium-groups', 'symposium_groups', get_option('symposium_groups_url'), 'wp-symposium/symposium_groups.php', 'admin.php?page=wp-symposium/symposium_groups_admin.php', __('The groups plugin must be installed in ', 'wp-symposium').WP_PLUGIN_DIR.'/wp-symposium.'.chr(10).chr(10).'Download from http://www.wpsymposium.com/downloadinstall.');
	install_row('Group v'.get_option("symposium_groups_version"), 'symposium-group', 'symposium_group', get_option('symposium_group_url'), 'wp-symposium/symposium_groups.php', '', __('The groups plugin must be installed in ', 'wp-symposium').WP_PLUGIN_DIR.'/wp-symposium.'.chr(10).chr(10).'Download from http://www.wpsymposium.com/downloadinstall.');

}
add_action('symposium_installation_hook', 'add_groups_installation_row');

// Add Symposium JS scripts to WordPress for use
function symposium_groups_init()
{
}

function symposium_groups_activate() {
	
	global $wpdb;

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    require_once(WP_PLUGIN_DIR . '/wp-symposium/symposium_functions.php');

	// Create groups table
	$table_name = $wpdb->prefix . "symposium_groups";
	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {

		$sql = "CREATE TABLE " . $table_name . " (
		  gid int(11) NOT NULL AUTO_INCREMENT,
		  name varchar(256) NOT NULL,
		  description text NOT NULL,
		  last_activity datetime NOT NULL,
		  private varchar(2) NOT NULL,
		  created datetime NOT NULL,
		  forum varchar(2) NOT NULL,
		  photos varchar(2) NOT NULL,
		  wall varchar(2) NOT NULL,
		  content_private varchar(2) NOT NULL,
		  group_avatar MEDIUMBLOB NOT NULL,		  
		  PRIMARY KEY (gid)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;";

	    dbDelta($sql);
	
	}

	// Create group members table
	$table_name = $wpdb->prefix . "symposium_group_members";
	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {

		$sql = "CREATE TABLE " . $table_name . " (
		  gmid int(11) NOT NULL AUTO_INCREMENT,
		  group_id int(11) NOT NULL,
		  member_id int(11) NOT NULL,
		  admin varchar(2) NOT NULL,
		  valid varchar(2) NOT NULL,
		  joined datetime NOT NULL,
		  notify varchar(2) NOT NULL DEFAULT 'on',
		  PRIMARY KEY (gmid)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;";

	    dbDelta($sql);
	
	}
	// Add index
	symposium_add_index($table_name, 'group_id');
	symposium_add_index($table_name, 'member_id');
	
	// Modify table(s) for upgrades
	symposium_alter_table("groups", "ADD", "profile_photo", "varchar(64)", "", "''");
	symposium_alter_table("groups", "ADD", "group_forum", "varchar(2)", "", "'on'");
	symposium_alter_table("groups", "ADD", "show_forum_default", "varchar(2)", "", "''");
	symposium_alter_table("groups", "ADD", "allow_new_topics", "varchar(2)", "", "'on'");
	symposium_alter_table("groups", "ADD", "new_member_emails", "varchar(2)", "", "'on'");
	symposium_alter_table("groups", "ADD", "add_alerts", "varchar(2)", "", "'on'");


}

function symposium_groups_deactivate() {
}

function symposium_groups_uninstall() {
}

/* ====================================================== SET SHORTCODE ====================================================== */

register_activation_hook(__FILE__,'symposium_groups_activate');
register_deactivation_hook(__FILE__, 'symposium_groups_deactivate');
register_uninstall_hook(__FILE__, 'symposium_groups_uninstall');

add_action('init', 'symposium_groups_init');
add_shortcode('symposium-groups', 'symposium_groups');  
add_shortcode('symposium-group', 'symposium_group');  
add_shortcode('symposium-group-members', 'symposium_group_members');  
add_shortcode('symposium-group-settings', 'symposium_group_settings');  


?>
