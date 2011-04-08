<?php
/*
Plugin Name: WP Symposium
Plugin URI: http://www.wpsymposium.com
Description: Core code for Symposium, this plugin must always be activated, before any other Symposium plugins/widgets (they rely upon it).
Version: 0.52.3
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

/* ====================================================== SETUP ====================================================== */

include_once('symposium_functions.php');

global $wpdb, $current_user;
define('WPS_VER', '0.52.3');
define('WPS_DBVER', '52');

add_action('init', 'symposium_languages');
add_action('init', 'js_init');
add_action('init', 'symposium_notification_setoptions');
add_action('init', 'symposium_scriptsAction');
add_action('wp_footer', 'symposium_lastactivity', 10);
add_action('wp_head', 'symposium_header', 10);
add_action('template_redirect', 'symposium_replace');
add_action('wp_print_styles', 'add_symposium_stylesheet');

if (is_admin()) {
	include('symposium_menu.php');
	add_action('admin_notices', 'symposium_admin_warnings');
	add_action('wp_dashboard_setup', 'symposium_dashboard_widget');	
	add_action('init', 'symposium_admin_init');
	add_action('admin_notices', 'symposium_admin_check');
}

register_activation_hook(__FILE__,'symposium_activate');
register_deactivation_hook(__FILE__, 'symposium_deactivate');
register_uninstall_hook(__FILE__, 'symposium_uninstall');

/* ===================================================== ADMIN ====================================================== */

// Check for updates
if ( ( get_option("symposium_version") != WPS_VER && is_admin()) || ($_GET['force_create_wps'] == 'yes' && is_admin())) {

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

	// Create initial versions of tables *************************************************************************************

	include('create_tables.php');

  	// Update tables *************************************************************************************

   	// Modify config table
	symposium_alter_table("config", "ADD", "allow_new_topics", "varchar(2)", "NOT NULL", "''");
	symposium_alter_table("config", "ADD", "underline", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("config", "ADD", "preview1", "int(11)", "NOT NULL", "'45'");
	symposium_alter_table("config", "ADD", "preview2", "int(11)", "NOT NULL", "'90'");
	symposium_alter_table("config", "ADD", "viewer", "varchar(32)", "NOT NULL", "'Guest'");
	symposium_alter_table("config", "ADD", "include_admin", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("config", "ADD", "oldest_first", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("config", "ADD", "wp_width", "varchar(6)", "NOT NULL", "'100%'");
	symposium_alter_table("config", "ADD", "main_background", "varchar(12)", "NOT NULL", "'#fff'");
	symposium_alter_table("config", "ADD", "closed_opacity", "varchar(6)", "NOT NULL", "'1.0'");
	symposium_alter_table("config", "ADD", "closed_word", "varchar(32)", "NOT NULL", "'closed'");
	symposium_alter_table("config", "ADD", "fontfamily", "varchar(64)", "NOT NULL", "'Georgia,Times'");
	symposium_alter_table("config", "ADD", "fontsize", "varchar(16)", "NOT NULL", "'13'");
	symposium_alter_table("config", "ADD", "headingsfamily", "varchar(64)", "NOT NULL", "'Arial,Helvetica'");
	symposium_alter_table("config", "ADD", "headingssize", "varchar(16)", "NOT NULL", "'20'");
	symposium_alter_table("config", "ADD", "jquery", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("config", "ADD", "jqueryui", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("config", "ADD", "emoticons", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("config", "ADD", "moderation", "varchar(2)", "NOT NULL", "''");
	symposium_alter_table("config", "ADD", "mail_url", "varchar(128)", "NOT NULL", "'Important: Please update!'");
	symposium_alter_table("config", "ADD", "online", "int(11)", "NOT NULL", "'3'");
	symposium_alter_table("config", "ADD", "offline", "int(11)", "NOT NULL", "'15'");
	symposium_alter_table("config", "ADD", "wp_alignment", "varchar(16)", "NOT NULL", "'Center'");
	symposium_alter_table("config", "ADD", "enable_password", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("config", "ADD", "use_wp_profile", "varchar(2)", "NOT NULL", "''");
	symposium_alter_table("config", "ADD", "members_url", "varchar(128)", "NOT NULL", "'Important: Please update!'");
	symposium_alter_table("config", "ADD", "sharing", "varchar(32)", "", "''");
	symposium_alter_table("config", "ADD", "use_styles", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("config", "ADD", "show_wall_extras", "varchar(2)", "NOT NULL", "''");
	symposium_alter_table("config", "ADD", "sound", "varchar(32)", "NOT NULL", "'chime.mp3'");
	symposium_alter_table("config", "ADD", "use_chat", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("config", "ADD", "bar_polling", "int(11)", "NOT NULL", "'120'");
	symposium_alter_table("config", "ADD", "chat_polling", "int(11)", "NOT NULL", "'10'");
	symposium_alter_table("config", "ADD", "use_chatroom", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("config", "ADD", "chatroom_banned", "text", "", "''");
	symposium_alter_table("config", "ADD", "profile_google_map", "int(11)", "NOT NULL", "'150'");
	symposium_alter_table("config", "ADD", "use_poke", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("config", "ADD", "motd", "varchar(2)", "NOT NULL", "''");
	symposium_alter_table("config", "ADD", "profile_url", "varchar(128)", "NOT NULL", "'Important: Please update!'");
	symposium_alter_table("config", "ADD", "groups_url", "varchar(128)", "NOT NULL", "'Important: Please update!'");
	symposium_alter_table("config", "ADD", "group_url", "varchar(128)", "NOT NULL", "'Important: Please update!'");
	symposium_alter_table("config", "ADD", "group_all_create", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("config", "ADD", "profile_avatars", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("config", "ADD", "img_db", "varchar(2)", "NOT NULL", "''");
	symposium_alter_table("config", "ADD", "img_path", "varchar(128)", "NOT NULL", "'".WP_CONTENT_DIR."/wps-content'");
	symposium_alter_table("config", "ADD", "img_url", "varchar(128)", "NOT NULL", "'/wp-content/wps-content'");
	symposium_alter_table("config", "ADD", "img_upload", "mediumblob", "", "");
	symposium_alter_table("config", "ADD", "img_crop", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("config", "ADD", "forum_ranks", "varchar(128)", "NOT NULL", "''");
	symposium_alter_table("config", "MODIFY", "forum_ranks", "varchar(256)", "NOT NULL", "''");
	symposium_alter_table("config", "ADD", "forum_ajax", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("config", "ADD", "forum_login", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("config", "ADD", "initial_friend", "int(11)", "NOT NULL", "'0'");
	symposium_alter_table("config", "ADD", "template_profile_header", "text", "NOT NULL", "''");
	symposium_alter_table("config", "ADD", "template_profile_body", "text", "NOT NULL", "''");
	symposium_alter_table("config", "ADD", "template_page_footer", "text", "NOT NULL", "''");
	symposium_alter_table("config", "ADD", "template_email", "text", "NOT NULL", "''");
	symposium_alter_table("config", "ADD", "template_forum_header", "text", "NOT NULL", "''");
	symposium_alter_table("config", "ADD", "template_mail", "text", "NOT NULL", "''");
	symposium_alter_table("config", "ADD", "template_mail_tray", "text", "NOT NULL", "''");
	symposium_alter_table("config", "ADD", "template_mail_message", "text", "NOT NULL", "''");
	symposium_alter_table("config", "ADD", "template_forum_category", "text", "NOT NULL", "''");
	symposium_alter_table("config", "ADD", "template_forum_topic", "text", "NOT NULL", "''");
	symposium_alter_table("config", "ADD", "template_group", "text", "NOT NULL", "''");
	symposium_alter_table("config", "ADD", "template_group_forum_category", "text", "NOT NULL", "''");
	symposium_alter_table("config", "ADD", "template_group_forum_topic", "text", "NOT NULL", "''");
	symposium_alter_table("config", "ADD", "facebook_api", "varchar(128)", "NOT NULL", "''");
	symposium_alter_table("config", "ADD", "facebook_secret", "varchar(128)", "NOT NULL", "''");
	symposium_alter_table("config", "ADD", "css", "text", "NOT NULL", "''");
	symposium_alter_table("config", "ADD", "mobile_topics", "int(11)", "NOT NULL", "'20'");

	// Set default values for some config fields
	if ($wpdb->get_var("SELECT template_profile_header FROM ".$wpdb->prefix.'symposium_config') == '') {
		$wpdb->query("UPDATE ".$wpdb->prefix."symposium_config SET template_profile_header = \"<div id='profile_header_div'>[]<div id='profile_header_panel'>[]<div id='profile_details'>[]<div id='profile_name'>[display_name]</div>[]<p>[location]<br />[born]</p>[]<div style='padding: 0px;'>[actions]</div>[]</div>[]</div>[]<div id='profile_photo' class='corners'>[avatar,200]</div>[]</div>\""); 
	}
	if ($wpdb->get_var("SELECT template_profile_body FROM ".$wpdb->prefix.'symposium_config') == '') {
		$wpdb->query("UPDATE ".$wpdb->prefix."symposium_config SET template_profile_body = \"<div id='profile_wrapper'>[]<div id='force_profile_page' style='display:none'>[default]</div>[]<div id='profile_body_wrapper'>[]<div id='profile_body'>[page]</div>[]</div>[]<div id='profile_menu'>[menu]</div>[]</div>\""); 
	}
	if ($wpdb->get_var("SELECT template_page_footer FROM ".$wpdb->prefix.'symposium_config') == '') {
		$wpdb->query("UPDATE ".$wpdb->prefix."symposium_config SET template_page_footer = \"<div id='powered_by_wps'>[]<a href='http://www.wpsymposium.com' target='_blank'>[powered_by_message] v[version]</a>[]</div>\""); 
	}
	if ($wpdb->get_var("SELECT template_email FROM ".$wpdb->prefix.'symposium_config') == '') {
		$wpdb->query("UPDATE ".$wpdb->prefix."symposium_config SET template_email = \"<style> body { background-color: #eee; } </style>[]<div style='margin: 20px; padding:20px; border-radius:10px; background-color: #fff;border:1px solid #000;'>[][message][]<br /><hr />[][footer]<br />[]<a href='http://www.wpsymposium.com' target='_blank'>[powered_by_message] v[version]</a>[]</div>\""); 
	}
	if ($wpdb->get_var("SELECT template_forum_header FROM ".$wpdb->prefix.'symposium_config') == '') {
		$wpdb->query("UPDATE ".$wpdb->prefix."symposium_config SET template_forum_header = \"[breadcrumbs][new_topic_button][new_topic_form][][digest][subscribe][][forum_options][][sharing]\""); 
	}
	if ($wpdb->get_var("SELECT template_mail FROM ".$wpdb->prefix.'symposium_config') == '') {
		$wpdb->query("UPDATE ".$wpdb->prefix."symposium_config SET template_mail = \"[compose_form][]<div id='mail_sent_message'></div>[]<div id='mail_office'>[]<div id='mail_toolbar'>[]<input id='compose_button' class='symposium-button' type='submit' value='[compose]'>[]<div id='trays'>[]<input type='radio' id='in' class='mail_tray' name='tray' checked> [inbox] <span id='in_unread'></span>&nbsp;&nbsp;[]<input type='radio' id='sent' class='mail_tray' name='tray'> [sent][]</div>[]<div id='search'>[]<input id='search_inbox' type='text' style='width: 160px'>[]<input id='search_inbox_go' class='symposium-button' type='submit' style='width: 70px; margin-left:10px;' value='Search'>[]</div>[]</div>[]<div id='mailbox'>[]<div id='mailbox_list'></div>[]</div>[]<div id='messagebox'></div>[]</div>\""); 
	}
	if ($wpdb->get_var("SELECT template_mail_tray FROM ".$wpdb->prefix.'symposium_config') == '') {
		$wpdb->query("UPDATE ".$wpdb->prefix."symposium_config SET template_mail_tray = \"<div id='mail_mid' class='mail_item mail_read'>[]<div class='mailbox_message_from'>[mail_from]</div>[]<div class='mail_item_age'>[mail_sent]</div>[]<div class='mailbox_message_subject'>[mail_subject]</div>[]<div class='mailbox_message'>[mail_message]</div>[]</div>\""); 
	}
	if ($wpdb->get_var("SELECT template_mail_message FROM ".$wpdb->prefix.'symposium_config') == '') {
		$wpdb->query("UPDATE ".$wpdb->prefix."symposium_config SET template_mail_message = \"<div id='message_header'>[]<div id='message_header_avatar'>[avatar,44]</div>[mail_subject]<br />[mail_recipient] [mail_sent]</div>[]<div id='message_header_delete'>[delete_button]</div><div id='message_header_reply'>[reply_button]</div>[]<div id='message_mail_message'>[message]</div>\""); 
	}
	if ($wpdb->get_var("SELECT template_group FROM ".$wpdb->prefix.'symposium_config') == '') {
		$wpdb->query("UPDATE ".$wpdb->prefix."symposium_config SET template_group = \"<div id='group_header_div'><div id='group_header_panel'>[]<div id='group_details'>[]<div id='group_name'>[group_name]</div>[]<div id='group_description'>[group_description]</div>[]<div style='padding: 15px;'>[actions]</div>[]</div></div>[]<div id='group_photo' class='corners'>[avatar,200]</div>[]</div>[]<div id='group_wrapper'>[]<div id='force_group_page' style='display:none'>[default]</div>[]<div id='group_body_wrapper'>[]<div id='group_body'>[page]</div>[]</div>[]<div id='group_menu'>[menu]</div>[]</div>\""); 
	}
	if ($wpdb->get_var("SELECT template_forum_category FROM ".$wpdb->prefix.'symposium_config') == '') {
		$wpdb->query("UPDATE ".$wpdb->prefix."symposium_config SET template_forum_category = \"<div class='row_startedby'>[]<div class='avatar avatar_last_topic'>[avatar,32]</div>[replied][subject][ago]</div>[]<div class='row_views'>[post_count]</div>[]<div class='row_topic row_replies'>[topic_count]</div>[]<div class='row_topic'>[category_title]</div>\""); 
	}
	if ($wpdb->get_var("SELECT template_forum_topic FROM ".$wpdb->prefix.'symposium_config') == '') {
		$wpdb->query("UPDATE ".$wpdb->prefix."symposium_config SET template_forum_topic = \"<div class='row_startedby'>[]<div class='avatar avatar_last_topic'>[avatar,32]</div>[][replied][topic][ago]</div>[]<div class='row_views'>[views]</div>[]<div class='row_replies'>[replies]</div>[]<div class='row_topic'>[topic_title]</div>\""); 
	}
	if ($wpdb->get_var("SELECT template_group_forum_category FROM ".$wpdb->prefix.'symposium_config') == '') {
		$wpdb->query("UPDATE ".$wpdb->prefix."symposium_config SET template_group_forum_category = \"<div class='row_startedby'>[]<div class='avatar avatar_last_topic'>[avatar,32]</div>[replied][subject][ago]</div>[]<div class='row_topic'>[category_title]</div>\""); 
	}
	if ($wpdb->get_var("SELECT template_group_forum_topic FROM ".$wpdb->prefix.'symposium_config') == '') {
		$wpdb->query("UPDATE ".$wpdb->prefix."symposium_config SET template_group_forum_topic = \"<div class='row_startedby'>[]<div class='avatar avatar_last_topic'>[avatar,32]</div>[replied][topic][ago]</div>[]<div class='row_topic'>[topic_title]</div>\""); 
	}
	
	// Default forum ranks
	if ($wpdb->get_var("SELECT forum_ranks FROM ".$wpdb->prefix.'symposium_config') == '') {
		$wpdb->query("UPDATE ".$wpdb->prefix."symposium_config SET forum_ranks = 'on;Emperor;0;Monarch;200;Lord;150;Duke;125;Count;100;Earl;75;Viscount;50;Bishop;25;Baron;10;Knight;5;Peasant;0'"); 
	}

	// Modify Mail table
	symposium_alter_table("mail", "MODIFY", "mail_sent", "datetime", "", "");

	// Modify Forum Categories table
	symposium_alter_table("cats", "ADD", "cat_parent", "int(11)", "NOT NULL", "0");

	// Modify Comments table
	symposium_alter_table("comments", "MODIFY", "comment_timestamp", "datetime", "", "");
	symposium_alter_table("comments", "ADD", "is_group", "varchar(2)", "NOT NULL", "''");
	
	// Modify Friends table
	symposium_alter_table("friends", "MODIFY", "friend_timestamp", "datetime", "", "");

	// Modify Chat table
	symposium_alter_table("chat", "ADD", "chat_room", "int(11)", "NOT NULL", "'0'");
	symposium_alter_table("chat", "MODIFY", "chat_timestamp", "datetime", "", "");

	// Modify Notification bar table
	symposium_alter_table("notifications", "ADD", "notification_old", "varchar(2)", "NOT NULL", "''");

	// Modify user meta table
	symposium_alter_table("usermeta", "ADD", "notify_new_messages", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("usermeta", "ADD", "notify_new_wall", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("usermeta", "ADD", "city", "varchar(32)", "", "");
	symposium_alter_table("usermeta", "ADD", "country", "varchar(32)", "", "");
	symposium_alter_table("usermeta", "ADD", "dob_day", "int(11)", "", "");
	symposium_alter_table("usermeta", "ADD", "dob_month", "int(11)", "", "");
	symposium_alter_table("usermeta", "ADD", "dob_year", "int(11)", "", "");
	symposium_alter_table("usermeta", "ADD", "share", "varchar(32)", "", "'Friends only'");
	symposium_alter_table("usermeta", "ADD", "last_activity", "timestamp", "", "");
	symposium_alter_table("usermeta", "MODIFY", "last_activity", "datetime", "", "");
	symposium_alter_table("usermeta", "ADD", "status", "varchar(32)", "NOT NULL", "''");
	symposium_alter_table("usermeta", "ADD", "visible", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("usermeta", "ADD", "wall_share", "varchar(32)", "", "'Friends only'");
	symposium_alter_table("usermeta", "ADD", "extended", "text", "NOT NULL", "''");
	symposium_alter_table("usermeta", "ADD", "widget_voted", "varchar(2)", "", "''");
	symposium_alter_table("usermeta", "ADD", "profile_photo", "varchar(64)", "", "''");
	symposium_alter_table("usermeta", "ADD", "forum_favs", "TEXT", "", "");
	symposium_alter_table("usermeta", "ADD", "profile_avatar", "mediumblob", "", "");
	symposium_alter_table("usermeta", "ADD", "trusted", "varchar(2)", "", "''");
	symposium_alter_table("usermeta", "ADD", "devicetoken", "varchar(128)", "NOT NULL", "''");
	symposium_alter_table("usermeta", "ADD", "facebook_id", "varchar(128)", "NOT NULL", "''");

	// Modify styles table
	symposium_alter_table("styles", "ADD", "underline", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("styles", "ADD", "main_background", "varchar(12)", "NOT NULL", "'#fff'");
	symposium_alter_table("styles", "ADD", "closed_opacity", "varchar(6)", "NOT NULL", "'1.0'");
	symposium_alter_table("styles", "ADD", "fontfamily", "varchar(128)", "NOT NULL", "'Georgia,Times'");
	symposium_alter_table("styles", "ADD", "fontsize", "varchar(8)", "NOT NULL", "'13'");
	symposium_alter_table("styles", "ADD", "headingsfamily", "varchar(128)", "NOT NULL", "'Georgia,Times'");
	symposium_alter_table("styles", "ADD", "headingssize", "varchar(8)", "NOT NULL", "'20'");
	
	// Add moderation field to topics
	symposium_alter_table("topics", "ADD", "allow_replies", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("topics", "ADD", "topic_approved", "varchar(2)", "NOT NULL", "'on'");
						      	
	// Update motd flag
	$sql = "UPDATE ".$wpdb->prefix."symposium_config SET motd = ''";
	$wpdb->query($sql); 

	// Setup Notifications
	symposium_notification_setoptions();
	
	// ***********************************************************************************************
 	// Update Versions *******************************************************************************
	update_option("symposium_db_version", WPS_DBVER);
	update_option("symposium_version", WPS_VER);
		
}

// Any admin warnings
function symposium_admin_warnings() {

   	global $wpdb;

	// CSS check
    $myStyleFile = WP_PLUGIN_DIR . '/wp-symposium/css/symposium.css';
    if ( !file_exists($myStyleFile) ) {
		echo "<div class='error'><p>WPS Symposium: ";
		_e( sprintf('Stylesheet (%s) not found.', $myStyleFile), 'wp-symposium');
		echo "</p></div>";
    }

	// JS check
    $myJSfile = WP_PLUGIN_DIR . '/wp-symposium/js/symposium.js';
    if ( !file_exists($myJSfile) ) {
		echo "<div class='error'><p>WPS Symposium: ";
		_e( sprintf('Javascript file (%s) not found, try de-activating and re-activating the core WPS plugin.', $myJSfile), 'wp-symposium');
		echo "</p></div>";
    }

    // MOTD
    if ($wpdb->get_var("SELECT motd FROM ".$wpdb->prefix.'symposium_config') != 'on') {

	    echo "<div class='updated' id='motd'><strong>".__("WP Symposium", "wp-symposium")."</strong><br /><div style='padding:4px;'>";
	    	    
	    echo "<p>";
		echo "<input type='submit' id='hide_motd' class='button-primary' style='float:right' value='".__('OK (and hide this message)', 'wp-symposium')."' />";
	    echo "</p>";
	    echo "<p style='line-height:15px'>";
	    echo __("Please visit the WP Symposium Options page for important release notes.", "wp-symposium");
	    echo "</p>";
	
	    echo "</div></div>";    	
	    
    }

}

// Dashboard Widget
function symposium_dashboard_widget(){
	wp_add_dashboard_widget('symposium_id', 'WP Symposium', 'symposium_widget');
}
function symposium_widget() {
	
	global $wpdb, $current_user;
	
	echo '<img src="'.WP_PLUGIN_URL.'/wp-symposium/images/logo_small.gif" alt="WP Symposium logo" style="float:right; width:100px;height:120px;" />';

	echo '<table><tr><td valign="top">';
	
		echo '<table>';
		echo '<tr><td colspan="2" style="padding:4px"><strong>'.__('Forum', 'wp-symposium').'</strong></td></tr>';
		echo '<tr><td style="padding:4px"><a href="admin.php?page=symposium_categories">'.__('Categories', 'wp-symposium').'</a></td>';
		echo '<td style="padding:4px">'.$wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix.'symposium_cats').'</td></tr>';
		echo '<tr><td style="padding:4px">'.__('Topics', 'wp-symposium').'</td>';
		echo '<td style="padding:4px">'.$wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix.'symposium_topics'." WHERE topic_parent = 0").'</td></tr>';
		echo '<tr><td style="padding:4px">'.__('Replies', 'wp-symposium').'</td>';
		echo '<td style="padding:4px">'.$wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix.'symposium_topics'." WHERE topic_parent > 0").'</td></tr>';
		echo '<tr><td style="padding:4px">'.__('Views', 'wp-symposium').'</td>';
		echo '<td style="padding:4px">'.$wpdb->get_var("SELECT SUM(topic_views) FROM ".$wpdb->prefix.'symposium_topics'." WHERE topic_parent = 0").'</td></tr>';
		echo '<tr><td style="padding:4px">'.__('Mail', 'wp-symposium').'</td>';
		$mailcount = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->base_prefix.'symposium_mail');
		$unread = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->base_prefix.'symposium_mail'." WHERE mail_read != 'on'");
		echo '<td style="padding:4px">'.$mailcount.' ';
		printf (__('(%s unread)', 'wp-symposium'), $unread);
		echo '</td></tr>';
		echo '</table>';
		
	echo "</td><td valign='top'>";

	
		echo '<table>';
			echo '<tr><td colspan="2" style="padding:4px"><strong>'.__('Plugins', 'wp-symposium').'</strong></td></tr>';
			echo '<tr><td colspan="2" style="padding:4px">';
			if (function_exists('symposium_forum')) {
				$url = $wpdb->get_var($wpdb->prepare("SELECT forum_url FROM ".$wpdb->prefix . 'symposium_config'));
				echo '<a href="'.$url.'">'.__('Go to Forum', 'wp-symposium').'</a>';
			} else {
				echo 'Forum not activated';
			}
			echo "</td></tr>";
			
			echo '<tr><td colspan="2" style="padding:4px">';
			if (function_exists('symposium_profile')) {
				$url = $wpdb->get_var($wpdb->prepare("SELECT profile_url FROM ".$wpdb->prefix . 'symposium_config'));
				echo '<a href="'.$url.'?uid='.$current_user->ID.'">'.__('Go to Profile', 'wp-symposium').'</a>';
			} else {
				echo 'Profile not activated';
			}
			echo "</td></tr>";

			echo '<tr><td colspan="2" style="padding:4px">';
			if (function_exists('symposium_mail')) {
				$url = $wpdb->get_var($wpdb->prepare("SELECT mail_url FROM ".$wpdb->prefix . 'symposium_config'));
				echo '<a href="'.$url.'">'.__('Go to Mail', 'wp-symposium').'</a>';
			} else {
				echo 'Profile not activated';
			}
			echo "</td></tr>";
			
			echo '<tr><td colspan="2" style="padding:4px">';
			if (function_exists('symposium_members')) {
				$url = $wpdb->get_var($wpdb->prepare("SELECT members_url FROM ".$wpdb->prefix . 'symposium_config'));
				echo '<a href="'.$url.'">'.__('Go to Member Directory', 'wp-symposium').'</a>';
			} else {
				echo 'Member Directory not activated';
			}
			echo "</td></tr>";
			
			echo '<tr><td colspan="2" style="padding:4px">';
			if (function_exists('symposium_groups')) {
				$url = $wpdb->get_var($wpdb->prepare("SELECT groups_url FROM ".$wpdb->prefix . 'symposium_config'));
				echo '<a href="'.$url.'">'.__('Go to Group Directory', 'wp-symposium').'</a><br />';
				$url = $wpdb->get_var($wpdb->prepare("SELECT group_url FROM ".$wpdb->prefix . 'symposium_config'));
				echo '<a href="'.$url.'">'.__('Go to Group Profile', 'wp-symposium').'</a>';
			} else {
				echo 'Groups not activated';
			}
			echo "</td></tr>";
			
		echo "</table>";
	
	
	echo "</td></tr></table>";

}

function symposium_activate() {	
}
/* End of Activation */

function symposium_uninstall() {
   
   	global $wpdb;

   	$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix."symposium_config");
   	$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix."symposium_topics");
   	$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix."symposium_subs");
   	$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix."symposium_cats");
   	$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix."symposium_styles");
   	$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix."symposium_lang");
   	$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix."symposium_usermeta");
   	$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix."symposium_mail");
   	$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix."symposium_audit");
   	$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix."symposium_chat");
   	$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix."symposium_comments");
   	$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix."symposium_extended");
   	$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix."symposium_friends");
   	$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix."symposium_notifications");

	// Delete Notification options
	delete_option("symposium_notification_inseconds");
	delete_option("symposium_notification_recc");
	delete_option("symposium_notification_triggercount");
	wp_clear_scheduled_hook('symposium_notification_hook');
	
	// Delete any options thats stored also
	delete_option('symposium_version');
	delete_option('symposium_db_version');
	
}
/* End of Un-install */

function symposium_deactivate() {

	wp_clear_scheduled_hook('symposium_notification_hook');

}

/* ====================================================== NOTIFICATIONS ====================================================== */

function symposium_notification_setoptions() {
	update_option("symposium_notification_inseconds",86400);
	// 60 = 1 minute, 3600 = 1 hour, 10800 = 3 hours, 21600 = 6 hours, 43200 = 12 hours, 86400 = Daily, 604800 = Weekly
	/* This is where the actual recurring event is scheduled */
	if (!wp_next_scheduled('symposium_notification_hook')) {
		$dt=explode(':',date('d:m:Y',time()));
		$schedule=mktime(0,1,0,$dt[1],$dt[0],$dt[2])+86400;
		// set for 00:01 from tomorrow
		wp_schedule_event($schedule, "symposium_notification_recc", "symposium_notification_hook");
	}
}

/* a reccurence has to be added to the cron_schedules array */
add_filter('cron_schedules', 'symposium_notification_more_reccurences');
function symposium_notification_more_reccurences($recc) {
	$recc['symposium_notification_recc'] = array('interval' => get_option("symposium_notification_inseconds"), 'display' => 'Symposium Notification Schedule');
	return $recc;
}
	
/* This is the scheduling hook for our plugin that is triggered by cron */
function symposium_notification_trigger_schedule() {
	symposium_notification_do_jobs('cron');
}

/* This is called by the scheduled cron job, and by Health Check Daily Digest check */
function symposium_notification_do_jobs($mode) {

	global $wpdb;
	$summary_email = __("Website Title", "wp-symposium").": ".get_bloginfo('name')."<br />";
	$summary_email .= __("Website URL", "wp-symposium").": ".get_bloginfo('wpurl')."<br />";
	$summary_email .= __("Admin Email", "wp-symposium").": ".get_bloginfo('admin_email')."<br />";
	$summary_email .= __("WordPress version", "wp-symposium").": ".get_bloginfo('version')."<br />";
	$summary_email .= __("WP Symposium version", "wp-symposium").": ".WPS_VER."<br />";
	$summary_email .= __("Daily Digest mode", "wp-symposium").": ".$mode."<br /><br />";
	$topics_count = 0;
	$user_count = 0;
	$success = "INCOMPLETE. ";
	

	// *************************************** First do daily jobs ***************************************
	// Clear Chat Windows (tidy up anyone who didn't close a chat window)
	$wpdb->query("DELETE FROM ".$wpdb->base_prefix."symposium_chat");
	// Clean irrelevant notifications
	$wpdb->query("DELETE FROM ".$wpdb->base_prefix."symposium_notifications WHERE notification_to = 0");
	// Remove duplicate/rogue members
	$sql = "CREATE TABLE ".$wpdb->base_prefix."wps_tmp AS SELECT * FROM ".$wpdb->prefix."symposium_usermeta WHERE 1 GROUP BY uid";
	$wpdb->query( $wpdb->prepare($sql) );
	$sql = "DELETE FROM ".$wpdb->base_prefix."symposium_usermeta WHERE mid NOT IN (SELECT mid FROM ".$wpdb->prefix."wps_tmp)";
	$wpdb->query( $wpdb->prepare($sql) );
	$sql = "DROP TABLE ".$wpdb->base_prefix."wps_tmp";
	$wpdb->query( $wpdb->prepare($sql) );
	$sql = "DELETE FROM ".$wpdb->base_prefix."symposium_usermeta WHERE uid = 0";
	$wpdb->query( $wpdb->prepare($sql) );
	// Add to summary report
	$summary_email .= __("Database cleanup", "wp-symposium").": completed<br />";
		
	// ******************************************* Daily Digest ******************************************
	$send_summary = $wpdb->get_var($wpdb->prepare("SELECT send_summary FROM ".$wpdb->prefix . 'symposium_config'));
	if ($send_summary == "on" || $mode != 'cron') {
		
		// Calculate yesterday			
		$startTime = mktime(0, 0, 0, date('m'), date('d')-1, date('Y'));
		$endTime = mktime(23, 59, 59, date('m'), date('d')-1, date('Y'));
		
		// Get all new topics from previous period
		$topics_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix.'symposium_topics'." WHERE topic_parent = 0 AND UNIX_TIMESTAMP(topic_date) >= ".$startTime." AND UNIX_TIMESTAMP(topic_date) <= ".$endTime));

		if ($topics_count > 0) {

			// Get Forum URL 
			// Will be something like /forum, /social-network/discuss or /?page_id=123
			// It is taken from WPS->Options->Settings
			// The symposium_get_url() function is in symposium_functions.php
			$forum_url = symposium_get_url('forum');
			// Decide on query suffix on whether a permalink or not
			if (strpos($forum_url, '?') !== FALSE) {
				$q = "&";
			} else {
				$q = "?";
			}

			$body = "";
			
			$categories = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix.'symposium_cats'." ORDER BY listorder"); 
			if ($categories) {
				foreach ($categories as $category) {
					
					$shown_category = false;
					$topics = $wpdb->get_results("
						SELECT tid, topic_subject, topic_parent, topic_post, topic_date, display_name, topic_category 
						FROM ".$wpdb->prefix.'symposium_topics'." INNER JOIN ".$wpdb->base_prefix.'users'." ON ".$wpdb->prefix.'symposium_topics'.".topic_owner = ".$wpdb->base_prefix.'users'.".ID 
						WHERE topic_parent = 0 AND topic_category = ".$category->cid." AND UNIX_TIMESTAMP(topic_date) >= ".$startTime." AND UNIX_TIMESTAMP(topic_date) <= ".$endTime." 
						ORDER BY tid"); 
					if ($topics) {
						if (!$shown_category) {
							$shown_category = true;
							$body .= "<h1>".stripslashes($category->title)."</h1>";
						}
						$body .= "<h2>".__('New Topics', 'wp-symposium')."</h2>";
						$body .= "<ol>";
						foreach ($topics as $topic) {
							$body .= "<li><strong><a href='".$forum_url.$q."cid=".$category->cid."&show=".$topic->tid."'>".stripslashes($topic->topic_subject)."</a></strong>";
							$body .= " started by ".$topic->display_name.":<br />";																
							$body .= stripslashes($topic->topic_post);
							$body .= "</li>";
						}
						$body .= "</ol>";
					}

					$replies = $wpdb->get_results("
						SELECT tid, topic_subject, topic_parent, topic_post, topic_date, display_name, topic_category 
						FROM ".$wpdb->prefix.'symposium_topics'." INNER JOIN ".$wpdb->base_prefix.'users'." ON ".$wpdb->prefix.'symposium_topics'.".topic_owner = ".$wpdb->base_prefix.'users'.".ID 
						WHERE topic_parent > 0 AND topic_category = ".$category->cid." AND UNIX_TIMESTAMP(topic_date) >= ".$startTime." AND UNIX_TIMESTAMP(topic_date) <= ".$endTime."
						ORDER BY topic_parent, tid"); 
					if ($replies) {
						if (!$shown_category) {
							$shown_category = true;
							$body .= "<h1>".$category->title."</h1>";
						}
						$body .= "<h2>".__('Replies in', 'wp-symposium')." ".$category->title."</h2>";
						$current_parent = '';
						foreach ($replies as $reply) {
							$parent = $wpdb->get_var($wpdb->prepare("SELECT topic_subject FROM ".$wpdb->prefix.'symposium_topics'." WHERE tid = ".$reply->topic_parent));
							if ($parent != $current_parent) {
								$body .= "<h3>".$parent."</h3>";
								$current_parent = $parent;
							}
							$body .= "<em>".$reply->display_name." wrote:</em> ";
							$post = $reply->topic_post;
							if ( strlen($post) > 100 ) { $post = substr($post, 0, 100)."..."; }
							$body .= stripslashes($post);
							$body .= " <a href='".$forum_url.$q."cid=".$category->cid."&show=".$topic->tid."'>".__('View topic', 'wp-symposium')."...</a>";
							$body .= "<br />";
							$body .= "<br />";
						}						
					}	
				}
			}
			
			$body .= "<p>".__("You can stop receiving these emails at", "wp-symposium")." <a href='".$forum_url."'>".$forum_url."</a>.</p>";
			
			$users = $wpdb->get_results("SELECT DISTINCT user_email FROM ".$wpdb->base_prefix.'users'." u INNER JOIN ".$wpdb->base_prefix.'symposium_usermeta'." m ON u.ID = m.uid WHERE m.forum_digest = 'on'"); 
			
			if ($users) {
				foreach ($users as $user) {
					if ($mode == 'cron' || $mode == 'send_admin_summary_and_to_users') {
						$user_count++;
						if(symposium_sendmail($user->user_email, __('Daily Forum Digest', 'wp-symposium'), $body)) {
							update_option("symposium_notification_triggercount",get_option("symposium_notification_triggercount")+1);
						}			
					}
				}
			}

		}
	}
	
	// Send admin summary
	$summary_email .= __("Forum topic count for previous day (midnight to midnight)", "wp-symposium").": ".$topics_count."<br />";
	$summary_email .= __("Daily Digest sent count", "wp-symposium").": ".$user_count."<br />";

	if (symposium_sendmail(get_bloginfo('admin_email'), __('Daily Digest Summary Report', 'wp-symposium'), $summary_email)) {
		$success = "OK (summary sent to ".get_bloginfo('admin_email')."). ";
	} else {
		$success = "FAILED sending to ".get_bloginfo('admin_email').". ";
	}
	
	return $success;
	
}

/* ====================================================== PHP FUNCTIONS ====================================================== */

// Replace get_avatar

if ( !function_exists('get_avatar') ) {
		
	function get_avatar( $id_or_email, $size = '96', $default = '', $alt = false ) {

		global $wpdb;
							
		if ( false === $alt)
			$safe_alt = '';
		else
			$safe_alt = esc_attr( $alt );
	
		if ( !is_numeric($size) )
			$size = '96';
	
		$email = '';
		if ( is_numeric($id_or_email) ) {
			$id = (int) $id_or_email;
			$user = get_userdata($id);
			if ( $user )
				$email = $user->user_email;
		} elseif ( is_object($id_or_email) ) {
			// No avatar for pingbacks or trackbacks
			$allowed_comment_types = apply_filters( 'get_avatar_comment_types', array( 'comment' ) );
			if ( ! empty( $id_or_email->comment_type ) && ! in_array( $id_or_email->comment_type, (array) $allowed_comment_types ) )
				return false;
	
			if ( !empty($id_or_email->user_id) ) {
				$id = (int) $id_or_email->user_id;
				$user = get_userdata($id);
				if ( $user)
					$email = $user->user_email;
			} elseif ( !empty($id_or_email->comment_author_email) ) {
				$email = $id_or_email->comment_author_email;
			}
		} else {
			$email = $id_or_email;
		}
	
		if ( empty($default) ) {
			$avatar_default = get_option('avatar_default');
			if ( empty($avatar_default) )
				$default = 'mystery';
			else
				$default = $avatar_default;
		}
	
		if ( !empty($email) )
			$email_hash = md5( strtolower( $email ) );
	
		if ( is_ssl() ) {
			$host = 'https://secure.gravatar.com';
		} else {
			if ( !empty($email) )
				$host = sprintf( "http://%d.gravatar.com", ( hexdec( $email_hash[0] ) % 2 ) );
			else
				$host = 'http://0.gravatar.com';
		}
	
		// If on www.wpsymposium.com then change image size to include border if a subscriber (this is only for use on www.wpsymposium.com)
		$member = "";
		if ($_SERVER['HTTP_HOST'] == 'www.wpsymposium.com') {
			$level = $wpdb->get_var("select meta_value from ".$wpdb->base_prefix."usermeta where meta_key = 'ym_user' and (meta_value like '%Bronze%' OR meta_value like '%Silver%') and meta_value like '%YourMember_User%' and user_id = ".$id);

	        if ( strpos($level, "Bronze") ) {
					$member = "Bronze";
					$size = $size - 4;
	        }

	        if ( strpos($level, "Silver") ) {
					$member = "Silver";
					$size = $size - 4;
	        }			

	        if ( strpos($level, "Gold") ) {
					$member = "Gold";
					$size = $size - 4;
	        }			
		}
			
		if ( 'mystery' == $default )
			$default = "$host/avatar/ad516503a11cd5ca435acc9bb6523536?s={$size}"; // ad516503a11cd5ca435acc9bb6523536 == md5('unknown@gravatar.com')
		elseif ( 'blank' == $default )
			$default = includes_url('images/blank.gif');
		elseif ( !empty($email) && 'gravatar_default' == $default )
			$default = '';
		elseif ( 'gravatar_default' == $default )
			$default = "$host/avatar/s={$size}";
		elseif ( empty($email) )
			$default = "$host/avatar/?d=$default&amp;s={$size}";
		elseif ( strpos($default, 'http://') === 0 )
			$default = add_query_arg( 's', $size, $default );
	
		if ( !empty($email) ) {
			$out = "$host/avatar/";
			$out .= $email_hash;
			$out .= '?s='.$size;
			$out .= '&amp;d=' . urlencode( $default );
	
			$rating = get_option('avatar_rating');
			if ( !empty( $rating ) )
				$out .= "&amp;r={$rating}";
	
			$avatar = "<img alt='{$safe_alt}' src='{$out}' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";
		} else {
			$avatar = "<img alt='{$safe_alt}' src='{$default}' class='avatar avatar-{$size} photo avatar-default' height='{$size}' width='{$size}' />";
		}
	
		$return = '';
		
		$config = $wpdb->get_row($wpdb->prepare("SELECT img_db, img_url, profile_avatars FROM ".$wpdb->prefix . 'symposium_config'));

		if ($config->img_db == "on") {
		
			$profile_photo = get_symposium_meta($id, 'profile_avatar');
			$profile_avatars = $config->profile_avatars;
		
			if ($profile_photo == '' || $profile_photo == 'upload_failed' || $profile_avatars != 'on') {
				$return .= apply_filters('get_avatar', $avatar, $id_or_email, $size, $default, $alt);
			} else {
				$return .= "<img src='".WP_CONTENT_URL."/plugins/wp-symposium/uploadify/get_profile_avatar.php?uid=".$id."' style='width:".$size."px; height:".$size."px' class='avatar avatar-".$size." photo' />";
			}
			
		} else {
	
			$profile_photo = get_symposium_meta($id, 'profile_photo');
			$profile_avatars = $config->profile_avatars;
	
			if ($profile_photo == '' || $profile_photo == 'upload_failed' || $profile_avatars != 'on') {
				$return .= apply_filters('get_avatar', $avatar, $id_or_email, $size, $default, $alt);
			} else {
				$img_url = $config->img_url."/members/".$id."/profile/";	
				$img_src =  str_replace('//','/',$img_url) . $profile_photo;
				$return .= "<img src='".$img_src."' style='width:".$size."px; height:".$size."px' class='avatar avatar-".$size." photo' />";
			}
			
		}
		
		// Add border for subscribers (this is only for use on www.wpsymposium.com)
		if ($member != '') {

	        if ( $member == "Bronze" ) {
	        	$return = str_replace("style='", "style='border:2px solid #8C7853;", $return);                          
	        }

	        if ( $member == "Silver" ) {
	        	$return = str_replace("style='", "style='border:2px solid #C0C0C0;", $return);
	        }
			
	        if ( $member == "Gold" ) {
	        	$return = str_replace("style='", "style='border:2px solid #FFD700;", $return);                          
	        }

		}

		return $return;

		
	}
	
}

// Header hook
function symposium_header() {
	include_once('symposium_styles.php');
}

// Update user activity on page load
function symposium_lastactivity() {
   	global $wpdb, $current_user;
	wp_get_current_user();
	
	// Update last logged in
	if (is_user_logged_in()) {
		update_symposium_meta($current_user->ID, 'last_activity', "'".date("Y-m-d H:i:s")."'");
	}
	// Powered by message
	echo powered_by_wps();

	// Place hidden div of current user to use when adding to screen
	echo "<div id='symposium_current_user_avatar' style='display:none;'>";
	echo get_avatar($current_user->ID, 200);
	echo "</div>";
	
}

// Hook to replace Smilies
function symposium_smilies($buffer){ // $buffer contains entire page

	if ( !strpos($buffer, "<rss") ) {

		global $wpdb;
		$emoticons = $wpdb->get_var($wpdb->prepare("SELECT emoticons FROM ".$wpdb->prefix . 'symposium_config'));
		
		if ($emoticons == "on") {
			
			$smileys = WP_PLUGIN_URL . '/wp-symposium/images/smilies/';
			$smileys_dir = WP_PLUGIN_DIR . '/wp-symposium/images/smilies/';
			// Smilies as classic text
			$buffer = str_replace(":)", "<img src='".$smileys."smile.png' alt='emoticon'/>", $buffer);
			$buffer = str_replace(":(", "<img src='".$smileys."sad.png' alt='emoticon'/>", $buffer);
			$buffer = str_replace(":'(", "<img src='".$smileys."crying.png' alt='emoticon'/>", $buffer);
			$buffer = str_replace(":x", "<img src='".$smileys."kiss.png' alt='emoticon'/>", $buffer);
			$buffer = str_replace(":X", "<img src='".$smileys."shutup.png' alt='emoticon'/>", $buffer);
			$buffer = str_replace(":D", "<img src='".$smileys."laugh.png' alt='emoticon'/>", $buffer);
			$buffer = str_replace(":|", "<img src='".$smileys."neutral.png' alt='emoticon'/>", $buffer);
			$buffer = str_replace(":?", "<img src='".$smileys."question.png' alt='emoticon'/>", $buffer);
			$buffer = str_replace(":z", "<img src='".$smileys."sleepy.png' alt='emoticon'/>", $buffer);
			$buffer = str_replace(":P", "<img src='".$smileys."tongue.png' alt='emoticon'/>", $buffer);
			$buffer = str_replace(";)", "<img src='".$smileys."wink.png' alt='emoticon'/>", $buffer);
			// Other images
			
			$i = 0;
			do {
				$i++;
				$start = strpos($buffer, "{{");
				if ($start === false) {
				} else {
					$end = strpos($buffer, "}}");
					if ($end === false) {
					} else {
						$first_bit = substr($buffer, 0, $start);
						$last_bit = substr($buffer, $end+2, strlen($buffer)-$end-2);
						$bit = substr($buffer, $start+2, $end-$start-2);
						if (file_exists($smileys_dir.$bit.".png")) {
							$buffer = $first_bit."<img src='".$smileys.$bit.".png' alt='emoticon'/>".$last_bit;
						} else {
							$buffer = $first_bit."&#123;&#123;".$bit."&#125;&#125;".$last_bit;
						}
					}
				}
			} while ($i < 100 && strpos($buffer, "{{")>0);
			
		}
		
	}

	return $buffer;
}

// Hook for adding unread mail, etc
function symposium_unread($buffer){ 
	
   	global $wpdb, $current_user;
	wp_get_current_user();

	// Unread mail
	$unread_in = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->base_prefix.'symposium_mail'." WHERE mail_to = ".$current_user->ID." AND mail_in_deleted != 'on' AND mail_read != 'on'");
	if ($unread_in > 0) {
		$buffer = str_replace("%m", "(".$unread_in.")", $buffer);
	} else {
		$buffer = str_replace("%m", "", $buffer);
	}
	
    // Pending friends
	$pending_friends = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->base_prefix."symposium_friends f WHERE f.friend_to = ".$current_user->ID." AND f.friend_accepted != 'on'");

	if ($pending_friends > 0) {
		$buffer = str_replace("%f", "(".$pending_friends.")", $buffer);
	} else {
		$buffer = str_replace("%f", "", $buffer);
	}

    return $buffer;
    
}

function symposium_admin_check() {
	global $wpdb;
	$urls = $wpdb->get_row($wpdb->prepare("SELECT forum_url, mail_url, members_url, profile_url, groups_url, group_url FROM ".$wpdb->prefix . 'symposium_config'));
	
	$warning = false;
	if ( ($urls->forum_url == "Important: Please update!") || ($urls->members_url == "Important: Please update!") || ($urls->mail_url == "Important: Please update!") || ($urls->profile_url == "Important: Please update!") ) {
		$warning = true;
	}
	if ( (function_exists('symposium_group')) && ( ($urls->groups_url == "Important: Please update!") || ($urls->group_url == "Important: Please update!") ) ) {
		$warning = true;
	}
	
	if ($warning == true) {
		echo "<div class='updated'><p><strong>".__("Important! Please set URLs in WP Symposium Options immediately (set to none if you are not using a particular plugin)", "wp-symposium").".</strong></p></div>";
	}
	
	// Check that user meta matches user table and delete to synchronise
	$sql = "SELECT uid
			FROM ".$wpdb->base_prefix."symposium_usermeta m 
			LEFT JOIN ".$wpdb->base_prefix."users u 
			ON m.uid = u.ID 
			WHERE u.ID IS NULL;";
			
	$missing_users = $wpdb->get_results($sql); 
	if ($missing_users) {
		foreach ($missing_users as $missing) {
			$sql = "DELETE FROM ".$wpdb->base_prefix."symposium_usermeta WHERE uid = ".$missing->uid;
			$wpdb->query($sql); 
			$sql = "DELETE FROM ".$wpdb->base_prefix."symposium_friends WHERE friend_from = ".$missing->uid." or friend_to = ".$missing->uid;
			$wpdb->query($sql); 
		}
	}	
}

/* ====================================================== PAGE LOADED FUNCTIONS ====================================================== */

function symposium_replace(){
	ob_start();
	ob_start('symposium_unread');
}

/* ====================================================== ADMIN FUNCTIONS ====================================================== */

// Add Stylesheet
function add_symposium_stylesheet() {
	global $wpdb;

	if (!is_admin()) {

	    // Load CSS
	    $myStyleUrl = WP_PLUGIN_URL . '/wp-symposium/css/symposium.css';
	    $myStyleFile = WP_PLUGIN_DIR . '/wp-symposium/css/symposium.css';
	    if ( file_exists($myStyleFile) ) {
	        wp_register_style('symposium_StyleSheet', $myStyleUrl);
	        wp_enqueue_style('symposium_StyleSheet');
	    }

		// Load pro CSS if exists (Groups)
	    $myStyleUrl = WP_PLUGIN_URL . '/wp-symposium-pro/css/symposium-groups.css';
	    $myStyleFile = WP_PLUGIN_DIR . '/wp-symposium-pro/css/symposium-groups.css';
	    if ( file_exists($myStyleFile) ) {
	        wp_register_style('symposium_StyleSheet', $myStyleUrl);
	        wp_enqueue_style('symposium_StyleSheet');
	    }
	    
	    // Load other CSS's
	    
		wp_register_style('symposium_uploadify-css', WP_PLUGIN_URL.'/wp-symposium/uploadify/uploadify.css');
		wp_enqueue_style('symposium_uploadify-css');
	    

	}

	wp_register_style('symposium_jcrop-css', WP_PLUGIN_URL.'/wp-symposium/css/jquery.Jcrop.css');
	wp_enqueue_style('symposium_jcrop-css');
	

	// Only load if chosen
	$jquery = $wpdb->get_var($wpdb->prepare("SELECT jquery FROM ".$wpdb->prefix . 'symposium_config'));
	if ($jquery=="on" && !is_admin()) {

        wp_register_style('symposium_jquery-ui-css', WP_PLUGIN_URL.'/wp-symposium/css/jquery-ui-1.8.11.custom.css');
        wp_enqueue_style('symposium_jquery-ui-css');

	}    

	// Dialog
	echo "<div id='dialog' style='display:none'></div>";
	
	// Notices
	echo "<div class='symposium_notice' style='display:none; z-index:999999;'><img src='".WP_PLUGIN_URL."/wp-symposium/images/busy.gif' /> ".__('Saving...', 'wp-symposium')."</div>";
	echo "<div class='symposium_pleasewait' style='display:none; z-index:999999;'><img src='".WP_PLUGIN_URL."/wp-symposium/images/busy.gif' /> ".__('Please Wait...', 'wp-symposium')."</div>";	

}

// Language files
function symposium_languages() {
		$plugin_path = dirname(plugin_basename(__FILE__)) . '/lang';
		load_plugin_textdomain( 'wp-symposium', false, $plugin_path );		
}

// Add jQuery and jQuery scripts (and TinyMCE)
function js_init() {
	global $wpdb;
	$jquery = $wpdb->get_row($wpdb->prepare("SELECT jquery, jqueryui FROM ".$wpdb->prefix . 'symposium_config'));

	// Only load if chosen
	if (!is_admin()) {
		
		$plugin = get_site_url().'/wp-content/plugins/wp-symposium';

		if ($jquery->jquery == "on") {
			wp_enqueue_script('jquery');	 		
		}
		if ($jquery->jqueryui == "on") {
	 		wp_enqueue_script('jquery-ui-custom', $plugin.'/js/jquery-ui-1.8.11.custom.min.js', array('jquery'));	
		}	

	}		
	
}

// Add jQuery and jQuery scripts
function symposium_admin_init() {
	if (is_admin()) {
		// Color Picker
		wp_register_script('symposium_iColorPicker', WP_PLUGIN_URL . '/wp-symposium/js/iColorPicker.js');
	    wp_enqueue_script('symposium_iColorPicker');
	}
}

// Add Symposium JS scripts to WordPress for use
function symposium_scriptsAction() {

	$symposium_plugin_url = WP_PLUGIN_URL.'/wp-symposium/';
	$symposium_plugin_path = str_replace("http://".$_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"], "", $symposium_plugin_url);
 
	global $wpdb, $current_user;
	wp_get_current_user();

	// Config
	$config = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."symposium_config"));
	
	if (!is_admin()) {

		// Mail
		if ( !isset($_GET['view']) ) { 
			$view = "in"; 
		} else {
			$view = $_GET['view'];
		} 
	
		// Current User Page (eg. a profile page)
		if (isset($_GET['uid'])) {
			$page_uid = $_GET['uid']*1;
		} else {
			$page_uid = 0;
			if (isset($_POST['uid'])) { 
				$page_uid = $_POST['uid']*1; 
			} else {
				$page_uid = $current_user->ID;
			}
		}
		if ($page_uid == 0) {
			if (isset($_POST['from']) && $_POST['from'] == 'small_search') {
				$search = $_POST['member_small'];
				$get_uid = $wpdb->get_var("SELECT u.ID FROM ".$wpdb->prefix."base_users u LEFT JOIN ".$wpdb->base_prefix."symposium_usermeta m ON u.ID = m.uid WHERE (u.display_name LIKE '".$search."%') OR (m.city LIKE '".$search."%') OR (m.country LIKE '".$search."%') OR (u.display_name LIKE '% %".$search."%') ORDER BY u.display_name LIMIT 0,1");
				if ($get_uid) { $page_uid = $get_uid; }
			} 
		}
		
		// Group page
		if (isset($_GET['gid'])) {
			$page_gid = $_GET['gid']*1;
		} else {
			$page_gid = 0;
			if (isset($_POST['gid'])) { 
				$page_gid = $_POST['gid']*1; 
			}
		}
				
		// Forum
		if (isset($_GET['show'])) {
			$show_tid = $_GET['show']*1;
		} else {
			$show_tid = 0;
			if (isset($_POST['tid'])) { $show_tid = $_POST['tid']*1; }
		}
		$cat_id = '';
		if (isset($_GET['cid'])) { $cat_id = $_GET['cid']; }
		if (isset($_POST['cid'])) { $cat_id = $_POST['cid']; }
		
		// Widget (vote)
		$symposium_vote_yes = get_option("symposium_vote_yes");
		if ($symposium_vote_yes != false) {
			$symposium_vote_yes = (int) $symposium_vote_yes;
		} else {
		    add_option("symposium_vote_yes", 0);	    	   	
			$symposium_vote_yes = 0;
		}
		$symposium_vote_no = get_option("symposium_vote_no");
		if ($symposium_vote_no != false) {
			$symposium_vote_no = (int) $symposium_vote_no;
		} else {
		    add_option("symposium_vote_no", 0);	    	   	
			$symposium_vote_no = 0;
		}
		if ($symposium_vote_yes > 0) {
			if ($symposium_vote_no > 0) {
				$yes = floor($symposium_vote_yes/($symposium_vote_yes+$symposium_vote_no)*100);
				$no = 100 - $yes;
				$yes = $yes."%";
				$no = $no."%";
			} else {
				$yes = "100%";
				$no = "0%";
			}
		} else {
			$yes = "0%";
			if ($symposium_vote_no > 0) {
				$no = "100%";
			} else {
				$no = "0%";
			}
		}
	
		// Permalink in use?
		$thispage = get_permalink();
		if ($thispage[strlen($thispage)-1] != '/') { $thispage .= '/'; }
		if (isset($_GET['page_id']) && $_GET['page_id'] != '') {
			// No Permalink
			$q = "&";
		} else {
			$q = "?";
		}
		
		// Get styles for JS
		if ($config->use_styles == "on") {
			$bg_color_2 = $config->bg_color_2;
			$row_border_size = $config->row_border_size;
			$row_border_style = $config->row_border_style;
			$text_color_2 = $config->text_color_2;
		} else {
			$bg_color_2 = '';
			$row_border_size = '';
			$row_border_style = '';
			$text_color_2 = '';
		}
	
		// GET post?
		if (isset($_GET['post'])) {
			$GETpost = $_GET['post'];
		} else {
			$GETpost = '';
		}
	
		// Display Name
		if (isset($current_user->display_name)) {
			$display_name = stripslashes($current_user->display_name);
		} else {
			$display_name = '';
		}

		// Load Symposium JS supporting scrtipts		

		wp_enqueue_script('jquery-swfobject', WP_PLUGIN_URL.'/wp-symposium/uploadify/swfobject.js', array('jquery'));
		wp_enqueue_script('jquery-uploadify', WP_PLUGIN_URL.'/wp-symposium/uploadify/jquery.uploadify.v2.1.4.js', array('jquery'));
		wp_enqueue_script('jquery-jcrop', WP_PLUGIN_URL.'/wp-symposium/js/jquery.Jcrop.js', array('jquery'));
		wp_enqueue_script('jquery-elastic', WP_PLUGIN_URL.'/wp-symposium/js/jquery.elastic.source.js', array('jquery'));

		// Load Symposium JS
	 	wp_enqueue_script('symposium', $symposium_plugin_url.'js/symposium.js', array('jquery'));
	
		// Set JS variables
		wp_localize_script( 'symposium', 'symposium', array(
			'plugins' => WP_PLUGIN_URL, 
			'plugin_url' => WP_PLUGIN_URL.'/wp-symposium/', 
			'plugin_path' => $symposium_plugin_path,
			'plugin_pro_url' => WP_PLUGIN_URL.'/wp-symposium-', 
			'inactive' => $config->online,
			'forum_url' => $config->forum_url,
			'mail_url' => $config->mail_url,
			'profile_url' => $config->profile_url,
			'groups_url' => $config->groups_url,
			'group_url' => $config->group_url,
			'offline' => $config->offline,
			'use_chat' => $config->use_chat,
			'chat_polling' => $config->chat_polling,
			'bar_polling' => $config->bar_polling,
			'view' => $view,
			'show_tid' => $show_tid,
			'cat_id' => $cat_id,
			'current_user_id' => $current_user->ID,
			'current_user_display_name' => $display_name,
			'current_user_page' => $page_uid,
			'current_group' => $page_gid,
			'widget_vote_yes' => $yes, 
			'widget_vote_no' => $no,
			'post' => $GETpost,
			'please_wait' => __('Please Wait...', 'wp-symposium'),
			'saving' => __('Saving...', 'wp-symposium'),
			'site_title' => get_bloginfo('name'),
			'q' => $q,
			'bg_color_2' => $bg_color_2,
			'row_border_size' => $row_border_size,
			'row_border_style' => $row_border_style,
			'text_color_2' => $text_color_2,
			'template_mail_tray' => $config->template_mail_tray
		));

	} else {
		
		// ADMIN JS load

		// Load Symposium JS supporting scripts
		wp_enqueue_script('jquery-swfobject', WP_PLUGIN_URL.'/wp-symposium/uploadify/swfobject.js', array('jquery'));
		wp_enqueue_script('jquery-uploadify', WP_PLUGIN_URL.'/wp-symposium/uploadify/jquery.uploadify.v2.1.4.js', array('jquery'));
		wp_enqueue_script('jquery-jcrop', WP_PLUGIN_URL.'/wp-symposium/js/jquery.Jcrop.js', array('jquery'));

		// Load Symposium JS
	 	wp_enqueue_script('symposium', $symposium_plugin_url.'js/symposium.js', array('jquery'));

		// Set JS variables
		wp_localize_script( 'symposium', 'symposium', array(
			'plugins' => WP_PLUGIN_URL, 
			'plugin_url' => WP_PLUGIN_URL.'/wp-symposium/', 
			'plugin_path' => $symposium_plugin_path,
			'plugin_pro_url' => WP_PLUGIN_URL.'/wp-symposium-', 
			'inactive' => $config->online,
			'forum_url' => $config->forum_url,
			'mail_url' => $config->mail_url,
			'profile_url' => $config->profile_url,
			'groups_url' => $config->groups_url,
			'group_url' => $config->group_url,
			'offline' => $config->offline,
			'use_chat' => $config->use_chat,
			'chat_polling' => $config->chat_polling,
			'bar_polling' => $config->bar_polling,
			'current_user_id' => $current_user->ID
		));
	}


}


?>
