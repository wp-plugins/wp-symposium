<?php
/*
Plugin Name: WP Symposium A Social Network For WordPress
Plugin URI: http://www.wpsymposium.com
Description: Core code for Symposium, this plugin must always be activated, before any other Symposium plugins/widgets (they rely upon it).
Version: 11.12.08
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
include_once('symposium_hooks_filters.php');

global $wpdb, $current_user;

define('WPS_VER', '11.12.08');

add_action('init', 'symposium_scriptsAction');
add_action('init', 'symposium_languages');
add_action('init', 'js_init');
add_action('init', 'symposium_notification_setoptions');
add_action('wp_footer', 'symposium_lastactivity', 10);
add_action('wp_head', 'symposium_header', 10);
add_action('template_redirect', 'symposium_replace');
add_action('wp_print_styles', 'add_symposium_stylesheet');
add_action('admin_init', 'add_symposium_stylesheet');
add_action('wp_login', 'symposium_login');

// ----------------------------------------------------------------------------------------------------------------------------------------------------------


if (is_admin()) {
	include('symposium_menu.php');
	add_action('admin_notices', 'symposium_admin_warnings');
	add_action('wp_dashboard_setup', 'symposium_dashboard_widget');	
	add_action('init', 'symposium_admin_init');
}

register_activation_hook(__FILE__,'symposium_activate');
register_deactivation_hook(__FILE__, 'symposium_deactivate');
register_uninstall_hook(__FILE__, 'symposium_uninstall');

/* ===================================================== ADMIN ====================================================== */	

// Check for updates
if ( ( get_option("symposium_version") != WPS_VER && is_admin()) || (isset($_GET['force_create_wps']) && $_GET['force_create_wps'] == 'yes' && is_admin())) {

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
	symposium_alter_table("config", "ADD", "forum_editor", "varchar(32)", "NOT NULL", "'Subscriber'");
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
	symposium_alter_table("config", "ADD", "use_styles", "varchar(2)", "NOT NULL", "''");
	symposium_alter_table("config", "ADD", "show_wall_extras", "varchar(2)", "NOT NULL", "''");
	symposium_alter_table("config", "ADD", "sound", "varchar(32)", "NOT NULL", "'chime.mp3'");
	symposium_alter_table("config", "ADD", "use_chat", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("config", "ADD", "bar_polling", "int(11)", "NOT NULL", "'120'");
	symposium_alter_table("config", "ADD", "chat_polling", "int(11)", "NOT NULL", "'10'");
	symposium_alter_table("config", "ADD", "use_chatroom", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("config", "ADD", "chatroom_banned", "text", "", "''");
	symposium_alter_table("config", "ADD", "profile_google_map", "int(11)", "NOT NULL", "'150'");
	symposium_alter_table("config", "ADD", "use_poke", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("config", "ADD", "poke_label", "varchar(32)", "NOT NULL", "'Hey!'");
	symposium_alter_table("config", "ADD", "motd", "varchar(2)", "NOT NULL", "''");
	symposium_alter_table("config", "ADD", "profile_url", "varchar(128)", "NOT NULL", "'Important: Please update!'");
	symposium_alter_table("config", "ADD", "groups_url", "varchar(128)", "NOT NULL", "'Important: Please update!'");
	symposium_alter_table("config", "ADD", "group_url", "varchar(128)", "NOT NULL", "'Important: Please update!'");
	symposium_alter_table("config", "ADD", "group_all_create", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("config", "ADD", "group_invites", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("config", "ADD", "group_invites_max", "int(11)", "NOT NULL", "'10'");
	symposium_alter_table("config", "ADD", "profile_avatars", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("config", "ADD", "img_db", "varchar(2)", "NOT NULL", "''");
	symposium_alter_table("config", "ADD", "img_path", "varchar(128)", "NOT NULL", "'".WP_CONTENT_DIR."/wps-content'");
	$img_url = WP_CONTENT_URL."/wps-content";
	$img_url = str_replace(siteURL(), '', $img_url); 
	symposium_alter_table("config", "ADD", "img_url", "varchar(128)", "NOT NULL", "'".$img_url."'");
	symposium_alter_table("config", "ADD", "img_upload", "mediumblob", "", "");
	symposium_alter_table("config", "ADD", "img_crop", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("config", "ADD", "forum_ranks", "varchar(128)", "NOT NULL", "''");
	symposium_alter_table("config", "MODIFY", "forum_ranks", "varchar(256)", "NOT NULL", "''");
	symposium_alter_table("config", "ADD", "forum_ajax", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("config", "ADD", "forum_login", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("config", "ADD", "initial_friend", "int(11)", "NOT NULL", "'0'");
	symposium_alter_table("config", "MODIFY", "initial_friend", "varchar(128)", "", "");
	symposium_alter_table("config", "ADD", "initial_groups", "varchar(128)", "", "");
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
	symposium_alter_table("config", "ADD", "bump_topics", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("config", "ADD", "show_dob", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("config", "ADD", "use_votes", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("config", "ADD", "use_votes_remove", "int(11)", "NOT NULL", "'0'");
	symposium_alter_table("config", "ADD", "show_buttons", "varchar(2)", "NOT NULL", "''");
	symposium_alter_table("config", "ADD", "show_admin", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("config", "ADD", "symposium_forumlatestposts_count", "int(11)", "NOT NULL", "'100'");
	symposium_alter_table("config", "ADD", "redirect_wp_profile", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("config", "ADD", "striptags", "varchar(2)", "NOT NULL", "''");
	symposium_alter_table("config", "ADD", "forum_uploads", "varchar(2)", "NOT NULL", "''");
	symposium_alter_table("config", "ADD", "forum_thumbs", "varchar(2)", "NOT NULL", "''");
	symposium_alter_table("config", "ADD", "forum_thumbs_size", "int(11)", "NOT NULL", "'400'");
	symposium_alter_table("config", "ADD", "forum_info", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("config", "ADD", "forum_stars", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("config", "ADD", "use_votes_min", "int(11)", "NOT NULL", "'10'");
	symposium_alter_table("config", "ADD", "use_answers", "varchar(2)", "NOT NULL", "''");
	symposium_alter_table("config", "ADD", "image_ext", "varchar(64)", "NOT NULL", "'*.jpg,*.gif,*.png,*.jpeg'");
	symposium_alter_table("config", "ADD", "video_ext", "varchar(64)", "NOT NULL", "'*.mp4'");
	symposium_alter_table("config", "ADD", "doc_ext", "varchar(64)", "NOT NULL", "'*.pdf,*.txt,*.zip'");
	symposium_alter_table("config", "ADD", "menu_my_activity", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("config", "ADD", "menu_friends_activity", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("config", "ADD", "menu_all_activity", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("config", "ADD", "menu_profile", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("config", "ADD", "menu_friends", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("config", "ADD", "menu_texthtml", "text", "NOT NULL", "''");
	symposium_alter_table("config", "ADD", "mail_all", "varchar(2)", "NOT NULL", "''");
	symposium_alter_table("config", "ADD", "elastic", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("config", "ADD", "profile_show_unchecked", "varchar(2)", "NOT NULL", "'on'");
	$images = WP_PLUGIN_URL."/wp-symposium/images";
	$images = str_replace(siteURL(), '', $images); 
	symposium_alter_table("config", "ADD", "images", "varchar(128)", "NOT NULL", "'".$images."'");
	symposium_alter_table("config", "ADD", "show_dir_buttons", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("config", "ADD", "dir_page_length", "int(11)", "NOT NULL", "'25'");
	symposium_alter_table("config", "ADD", "wps_lite", "varchar(2)", "NOT NULL", "''");
	symposium_alter_table("config", "ADD", "wps_profile_default", "varchar(12)", "NOT NULL", "'activity'");
	symposium_alter_table("config", "ADD", "wps_panel_all", "varchar(2)", "NOT NULL", "''");
	symposium_alter_table("config", "ADD", "wps_default_forum", "varchar(128)", "NOT NULL", "''");
	symposium_alter_table("config", "ADD", "wps_use_gravatar", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("config", "ADD", "wps_time_out", "int(11)", "NOT NULL", "'0'");
	symposium_alter_table("config", "ADD", "wps_js_file", "varchar(20)", "NOT NULL", "'wps.min.js'");
	symposium_alter_table("config", "ADD", "wps_css_file", "varchar(20)", "NOT NULL", "'wps.min.css'");
	symposium_alter_table("config", "ADD", "allow_reports", "varchar(2)", "NOT NULL", "''");
	symposium_alter_table("config", "ADD", "ajax_widgets", "varchar(2)", "NOT NULL", "''");
	symposium_alter_table("config", "ADD", "status_label", "varchar(64)", "NOT NULL", "'What\'s up?'");
	symposium_alter_table("config", "ADD", "colorbox", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("config", "ADD", "jscharts", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("config", "ADD", "use_wysiwyg", "varchar(2)", "NOT NULL", "''");
	symposium_alter_table("config", "ADD", "use_wysiwyg_1", "varchar(256)", "NOT NULL", "'bold,italic,|,fontselect,fontsizeselect,forecolor,backcolor,|,bullist,numlist,|,link,unlink,|,image,media,|,emotions'");
	symposium_alter_table("config", "ADD", "use_wysiwyg_2", "varchar(256)", "NOT NULL", "''");
	symposium_alter_table("config", "ADD", "use_wysiwyg_3", "varchar(256)", "NOT NULL", "''");
	symposium_alter_table("config", "ADD", "use_wysiwyg_4", "varchar(256)", "NOT NULL", "''");
	symposium_alter_table("config", "ADD", "use_wysiwyg_css", "varchar(256)", "NOT NULL", "'".str_replace(siteURL(), '', WP_PLUGIN_URL."/wp-symposium/tiny_mce/themes/advanced/skins/wps.css")."'");
	symposium_alter_table("config", "ADD", "use_wysiwyg_skin", "varchar(32)", "NOT NULL", "'cirkuit'");
	symposium_alter_table("config", "ADD", "use_wysiwyg_width", "varchar(8)", "NOT NULL", "'563'");
	symposium_alter_table("config", "ADD", "use_wysiwyg_height", "varchar(8)", "NOT NULL", "'300'");

	// Set default values for some config fields
	if ($wpdb->get_var("SELECT template_profile_header FROM ".$wpdb->prefix.'symposium_config') == '') {
		$wpdb->query("UPDATE ".$wpdb->prefix."symposium_config SET template_profile_header = \"<div id='profile_header_div'>[]<div id='profile_header_panel'>[]<div id='profile_details'>[]<div id='profile_name'>[display_name]</div>[]<p>[location]<br />[born]</p>[]<div style='padding: 0px;'>[actions]</div>[]</div>[]</div>[]<div id='profile_photo' class='corners'>[avatar,170]</div>[]</div>\""); 
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
		$wpdb->query("UPDATE ".$wpdb->prefix."symposium_config SET template_mail = \"[compose_form][]<div id='mail_sent_message'></div>[]<div id='mail_office'>[]<div id='mail_toolbar'>[]<input id='compose_button' class='symposium-button' type='submit' value='[compose]'>[]<div id='trays'>[]<input type='radio' id='in' class='mail_tray' name='tray' checked> [inbox] <span id='in_unread'></span>&nbsp;&nbsp;[]<input type='radio' id='sent' class='mail_tray' name='tray'> [sent][]</div>[]<div id='search'>[]<input id='search_inbox' type='text' style='width: 160px'>[]<input id='search_inbox_go' class='symposium-button message_search' type='submit' style='margin-left:10px;' value='Search'>[]</div>[]</div>[]<div id='mailbox'>[]<div id='mailbox_list'></div>[]</div>[]<div id='messagebox'></div>[]</div>\""); 
	}
	if ($wpdb->get_var("SELECT template_mail_tray FROM ".$wpdb->prefix.'symposium_config') == '') {
		$wpdb->query("UPDATE ".$wpdb->prefix."symposium_config SET template_mail_tray = \"<div id='mail_mid' class='mail_item mail_read'>[]<div class='mailbox_message_from'>[mail_from]</div>[]<div class='mail_item_age'>[mail_sent]</div>[]<div class='mailbox_message_subject'>[mail_subject]</div>[]<div class='mailbox_message'>[mail_message]</div>[]</div>\""); 
	}
	if ($wpdb->get_var("SELECT template_mail_message FROM ".$wpdb->prefix.'symposium_config') == '') {
		$wpdb->query("UPDATE ".$wpdb->prefix."symposium_config SET template_mail_message = \"<div id='message_header'>[]<div id='message_header_avatar'>[avatar,44]</div>[mail_subject]<br />[mail_recipient] [mail_sent]</div>[]<div id='message_header_delete'>[delete_button]</div><div id='message_header_reply'>[reply_button]</div>[]<div id='message_mail_message'>[message]</div>\""); 
	}
	if ($wpdb->get_var("SELECT template_group FROM ".$wpdb->prefix.'symposium_config') == '') {
		$wpdb->query("UPDATE ".$wpdb->prefix."symposium_config SET template_group = \"<div id='group_header_div'><div id='group_header_panel'>[]<div id='group_details'>[]<div id='group_name'>[group_name]</div>[]<div id='group_description'>[group_description]</div>[]<div style='padding-top: 15px;padding-bottom: 15px;'>[actions]</div>[]</div></div>[]<div id='group_photo' class='corners'>[avatar,170]</div>[]</div>[]<div id='group_wrapper'>[]<div id='force_group_page' style='display:none'>[default]</div>[]<div id='group_body_wrapper'>[]<div id='group_body'>[page]</div>[]</div>[]<div id='group_menu'>[menu]</div>[]</div>\""); 
	}
	if ($wpdb->get_var("SELECT template_forum_category FROM ".$wpdb->prefix.'symposium_config') == '') {
		$wpdb->query("UPDATE ".$wpdb->prefix."symposium_config SET template_forum_category = \"<div class='row_startedby'>[]<div class='avatar avatar_last_topic'>[avatar,32]</div>[]<div class='last_topic_text'>[replied][subject][ago]</div>[]</div>[]<div class='row_views'>[post_count]</div>[]<div class='row_topic row_replies'>[topic_count]</div>[]<div class='row_topic'>[category_title]<br />[category_desc]</div>\""); 
	}
	if ($wpdb->get_var("SELECT template_forum_topic FROM ".$wpdb->prefix.'symposium_config') == '') {
		$wpdb->query("UPDATE ".$wpdb->prefix."symposium_config SET template_forum_topic = \"<div class='row_startedby'>[]<div class='avatar avatar_last_topic'>[avatar,32]</div>[]<div class='last_topic_text'>[replied][topic][ago]</div>[]</div>[]<div class='row_views'>[views]</div>[]<div class='row_replies'>[replies]</div>[]<div class='row_topic'>[topic_title]</div>\""); 
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
	symposium_alter_table("cats", "ADD", "cat_desc", "varchar(256)", "", "''");
	symposium_alter_table("cats", "ADD", "level", "varchar(256)", "", "'s:60:\"Everyone,Administrator,Editor,Author,Contributor,Subscriber,\";'");

	// Modify Comments table
	symposium_alter_table("comments", "MODIFY", "comment_timestamp", "datetime", "", "");
	symposium_alter_table("comments", "ADD", "is_group", "varchar(2)", "NOT NULL", "''");
	symposium_alter_table("comments", "ADD", "type", "varchar(16)", "NOT NULL", "'post'");
	symposium_alter_table("comments", "MODIFY", "comment", "text", "", "");
	
	// Modify Friends table
	symposium_alter_table("friends", "MODIFY", "friend_timestamp", "datetime", "", "");

	// Modify Chat table
	symposium_alter_table("chat", "ADD", "chat_room", "int(11)", "NOT NULL", "'0'");
	symposium_alter_table("chat", "MODIFY", "chat_timestamp", "datetime", "", "");

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
	symposium_alter_table("usermeta", "ADD", "facebook_id", "varchar(128)", "NOT NULL", "''");
	symposium_alter_table("usermeta", "ADD", "last_login", "datetime", "", "");
	symposium_alter_table("usermeta", "ADD", "previous_login", "datetime", "", "");
	symposium_alter_table("usermeta", "ADD", "forum_all", "varchar(2)", "", "''");
	symposium_alter_table("usermeta", "ADD", "signature", "varchar(128)", "NOT NULL", "''");

	// Modify styles table
	symposium_alter_table("styles", "ADD", "underline", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("styles", "ADD", "main_background", "varchar(12)", "NOT NULL", "'#fff'");
	symposium_alter_table("styles", "ADD", "closed_opacity", "varchar(6)", "NOT NULL", "'1.0'");
	symposium_alter_table("styles", "ADD", "fontfamily", "varchar(128)", "NOT NULL", "'Georgia,Times'");
	symposium_alter_table("styles", "ADD", "fontsize", "varchar(8)", "NOT NULL", "'13'");
	symposium_alter_table("styles", "ADD", "headingsfamily", "varchar(128)", "NOT NULL", "'Georgia,Times'");
	symposium_alter_table("styles", "ADD", "headingssize", "varchar(8)", "NOT NULL", "'20'");
	
	// Modify topics table
	symposium_alter_table("topics", "ADD", "allow_replies", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("topics", "ADD", "topic_approved", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("topics", "ADD", "topic_images", "varchar(256)", "", "''");
	symposium_alter_table("topics", "ADD", "topic_answer", "varchar(2)", "", "''");
	symposium_alter_table("topics", "ADD", "for_info", "varchar(2)", "", "''");

	// Modify profile extended fields table
	symposium_alter_table("extended", "MODIFY", "extended_name", "varchar(256)", "NOT NULL", "'New field'");
							      	
	// Update motd flag
	$sql = "UPDATE ".$wpdb->prefix."symposium_config SET motd = ''";
	$wpdb->query($sql); 

	// Setup Notifications
	symposium_notification_setoptions();
	
	// ***********************************************************************************************
 	// Update Versions *******************************************************************************
	update_option("symposium_version", WPS_VER);
		
}

// Any admin warnings
function symposium_admin_warnings() {

   	global $wpdb;

	// CSS check
    $myStyleFile = WP_PLUGIN_DIR . '/wp-symposium/css/'.WPS_CSS_FILE;
    if ( !file_exists($myStyleFile) ) {
		echo "<div class='error'><p>WPS Symposium: ";
		_e( sprintf('Stylesheet (%s) not found.', $myStyleFile), 'wp-symposium');
		echo "</p></div>";
    }

	// JS check
    $myJSfile = WP_PLUGIN_DIR . '/wp-symposium/js/'.WPS_JS_FILE;
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
	    echo __("Please visit the WP Symposium Installation page to complete your installation/upgrade.", "wp-symposium");
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
	
	echo '<img src="'.WPS_IMAGES_URL.'/logo_small.png" alt="WP Symposium logo" style="float:right; width:120px;height:120px;" />';

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
				echo '<a href="'.symposium_get_url('forum').'">'.__('Go to Forum', 'wp-symposium').'</a>';
			} else {
				echo 'Forum not activated';
			}
			echo "</td></tr>";
			
			echo '<tr><td colspan="2" style="padding:4px">';
			if (function_exists('symposium_profile')) {
				$url = symposium_get_url('profile');
				echo '<a href="'.$url.symposium_string_query($url).'uid='.$current_user->ID.'">'.__('Go to Profile', 'wp-symposium').'</a>';
			} else {
				echo 'Profile not activated';
			}
			echo "</td></tr>";

			echo '<tr><td colspan="2" style="padding:4px">';
			if (function_exists('symposium_mail')) {
				echo '<a href="'.symposium_get_url('mail').'">'.__('Go to Mail', 'wp-symposium').'</a>';
			} else {
				echo 'Profile not activated';
			}
			echo "</td></tr>";
			
			echo '<tr><td colspan="2" style="padding:4px">';
			if (function_exists('symposium_members')) {
				echo '<a href="'.symposium_get_url('members').'">'.__('Go to Member Directory', 'wp-symposium').'</a>';
			} else {
				echo 'Member Directory not activated';
			}
			echo "</td></tr>";
			
			echo '<tr><td colspan="2" style="padding:4px">';
			if (function_exists('symposium_groups')) {
				echo '<a href="'.symposium_get_url('groups').'">'.__('Go to Group Directory', 'wp-symposium').'</a><br />';
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
   	$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix."symposium_chat");
   	$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix."symposium_comments");
   	$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix."symposium_extended");
   	$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix."symposium_friends");

	// Delete Notification options
	delete_option("symposium_notification_inseconds");
	delete_option("symposium_notification_recc");
	delete_option("symposium_notification_triggercount");
	wp_clear_scheduled_hook('symposium_notification_hook');
	
	// Delete any options thats stored also
	delete_option('symposium_version');
	
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
	
	// Check that user meta matches user table and delete to synchronise
	$sql = "SELECT uid
			FROM ".$wpdb->base_prefix."symposium_usermeta m 
			LEFT JOIN ".$wpdb->base_prefix."users u 
			ON m.uid = u.ID 
			WHERE u.ID IS NULL;";
			
	$missing_users = $wpdb->get_results($sql); 
	if ($missing_users) {
		foreach ($missing_users as $missing) {
			$sql = "DELETE FROM ".$wpdb->base_prefix."symposium_usermeta WHERE uid = %d";
			$wpdb->query($wpdb->prepare($sql, $missing->uid)); 
			$sql = "DELETE FROM ".$wpdb->base_prefix."symposium_friends WHERE friend_from = %d or friend_to = %d";
			$wpdb->query($wpdb->prepare($sql, $missing->uid, $missing->uid)); 
			$sql = "DELETE FROM ".$wpdb->base_prefix."symposium_group_members WHERE member_id = %d";
			$wpdb->query($wpdb->prepare($sql, $missing->uid)); 			
		}
	}	

	// Remove duplicate/rogue members
	$check = $wpdb->get_var("show tables like '".$wpdb->base_prefix."symposium_tmp'");
	if($check != $wpdb->base_prefix."symposium_tmp") {
	
	    $sql = "CREATE TABLE ".$wpdb->base_prefix."symposium_tmp AS SELECT * FROM ".$wpdb->base_prefix."symposium_usermeta WHERE 1 GROUP BY uid";
	    $wpdb->query( $wpdb->prepare($sql) );
	    $sql = "DELETE FROM ".$wpdb->base_prefix."symposium_usermeta WHERE mid NOT IN (SELECT mid FROM ".$wpdb->base_prefix."symposium_tmp)";
	    $wpdb->query( $wpdb->prepare($sql) );
	    $sql = "DROP TABLE ".$wpdb->base_prefix."symposium_tmp";
	    $wpdb->query( $wpdb->prepare($sql) );
	} else {
		$sql = "DROP TABLE ".$wpdb->base_prefix."symposium_tmp";
	    $wpdb->query( $wpdb->prepare($sql) );
	}
    $sql = "DELETE FROM ".$wpdb->base_prefix."symposium_usermeta WHERE uid = 0";
    $wpdb->query( $wpdb->prepare($sql) );
    
	// Clear Chat Windows (tidy up anyone who didn't close a chat window)
	$wpdb->query("DELETE FROM ".$wpdb->base_prefix."symposium_chat");
	
	// Add to summary report
	$summary_email .= __("Database cleanup", "wp-symposium").": completed<br />";
	$users_sent_to_success = '';
	$users_sent_to_failed = '';
				
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
						$email = $user->user_email;
						if(symposium_sendmail($email, __('Daily Forum Digest', 'wp-symposium'), $body)) {
							$users_sent_to_success .= $user->user_email.'<br />';
							update_option("symposium_notification_triggercount",get_option("symposium_notification_triggercount")+1);
						} else {
							$users_sent_to_failed .= $user->user_email.'<br />';
						}						
					}
				}
			}

		}
	}
	
	// Send admin summary
	$summary_email .= __("Forum topic count for previous day (midnight to midnight)", "wp-symposium").": ".$topics_count."<br />";
	$summary_email .= __("Daily Digest sent count", "wp-symposium").": ".$user_count."<br /><br />";
	$summary_email .= "<b>List of recipients sent to:</b><br />";
	if ($users_sent_to_success != '') {
	$summary_email .= $users_sent_to_success;
	} else {
		$summary_email .= 'None.';
	}
	$summary_email .= "<br /><br /><b>List of sent failures:</b><br />";
	if ($users_sent_to_failed != '') {
		$summary_email .= $users_sent_to_failed;
	} else {
		$summary_email .= 'None.';
	}
	$email = get_bloginfo('admin_email');
	if (symposium_sendmail($email, __('Daily Digest Summary Report', 'wp-symposium'), $summary_email)) {
		$success = "OK (summary sent to ".get_bloginfo('admin_email')."). ";
	} else {
		$success = "FAILED sending to ".get_bloginfo('admin_email').". ";
	}
	
	return $success;
	
}


// Record last logged in and previously logged in 
function symposium_login($user_login) {

	global $wpdb, $current_user;

	// Get ID for this user
	$sql = "SELECT ID from ".$wpdb->prefix."users WHERE user_login = %s";
	$id = $wpdb->get_var($wpdb->prepare($sql, $user_login));
	// Get last time logged in
	$last_login = get_symposium_meta($id, 'last_login');
	$previous_login = get_symposium_meta($id, 'previous_login');
	// Store as previous time last logged in
	if ($previous_login == NULL) {
		update_symposium_meta($id, 'previous_login', "'".date("Y-m-d H:i:s")."'");
	} else {
		update_symposium_meta($id, 'previous_login', "'".$last_login."'");
	}
	// Store this log in as the last time logged in
	update_symposium_meta($id, 'last_login', "'".date("Y-m-d H:i:s")."'");
	
}


// Replace get_avatar 
if ( ( !function_exists('get_avatar') ) ) {

	function get_avatar( $id_or_email, $size = '96', $default = '', $alt = false ) {

		global $wpdb, $current_user;
							
		if ( false === $alt)
			$safe_alt = '';
		else
			$safe_alt = esc_attr( $alt );
	
		if ( !is_numeric($size) )
			$size = '96';
	
		$email = '';
		$display_name = '';
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
			$id = $wpdb->get_var("select display_name from ".$wpdb->base_prefix."users where user_email = '".$email."'");
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
			if ($_SERVER['HTTP_HOST'] == 'www.wpsymposium.com' && $id != '') {
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
		
		if (!isset($id)) { $id = 0; }
		if (WPS_IMG_DB == "on") {
		
			$profile_photo = get_symposium_meta($id, 'profile_avatar');
			$profile_avatars = WPS_PROFILE_AVATARS;
		
			if ($profile_photo == '' || $profile_photo == 'upload_failed' || $profile_avatars != 'on') {
				$return .= apply_filters('get_avatar', $avatar, $id_or_email, $size, $default, $alt);
			} else {
				$return .= "<img src='".WP_CONTENT_URL."/plugins/wp-symposium/uploadify/get_profile_avatar.php?uid=".$id."' style='width:".$size."px; height:".$size."px' class='avatar avatar-".$size." photo' />";
			}
			
		} else {

			$profile_photo = get_symposium_meta($id, 'profile_photo');
			$profile_avatars = WPS_PROFILE_AVATARS;

			if ($profile_photo == '' || $profile_photo == 'upload_failed' || $profile_avatars != 'on') {
				$return .= apply_filters('get_avatar', $avatar, $id_or_email, $size, $default, $alt);
			} else {
				$img_url = WPS_IMG_URL."/members/".$id."/profile/";	
				$img_src = str_replace('//','/',$img_url) . $profile_photo;
				$return .= "<img src='".$img_src."' style='width:".$size."px; height:".$size."px' class='avatar avatar-".$size." photo' />";
			}
			
		}
		
		if (!WPS_USE_GRAVATAR && strpos($return, 'gravatar')) {
			$return = "<img src='".WPS_IMAGES_URL."/unknown.jpg' style='width:".$size."px; height:".$size."px' class='avatar avatar-".$size." photo' />";
		}
		
		// Add border for subscribers (this is only for use on www.wpsymposium.com)
		if ($member != '') {

			if (strpos($return, 'style=')) {
		        if ( $member == "Bronze" ) {
		        	$return = str_replace("style='", "style='border:2px solid #8C7853;", $return);                          
		        }
	
		        if ( $member == "Silver" ) {
		        	$return = str_replace("style='", "style='border:2px solid #C0C0C0;", $return);
		        }
				
		        if ( $member == "Gold" ) {
		        	$return = str_replace("style='", "style='border:2px solid #FFD700;", $return);                          
		        }
			} else {
		        if ( $member == "Bronze" ) {
		        	$return = str_replace("/>", "style='border:2px solid #8C7853;' />", $return);                          
		        }
	
		        if ( $member == "Silver" ) {
		        	$return = str_replace("/>", "style='border:2px solid #C0C0C0;' />", $return);
		        }
				
		        if ( $member == "Gold" ) {
		        	$return = str_replace("/>", "style='border:2px solid #FFD700;' />", $return);                          
		        }
				
			}
		}
		
		// Replace Simon's border for WPS (sorry for this messy bit of code!!)
		if ($id == 85) {
			$return = str_replace("8C7853", "450048", $return);                          
		}

		// Get URL to profile
		if (function_exists('symposium_profile') && $id != '' ) {
			$profile_url = symposium_get_url('profile');
			$profile_url = $profile_url.symposium_string_query($profile_url).'uid='.$id;
	       	$return = str_replace("/>", " style='cursor:pointer' onclick='javascript:document.location=\"".$profile_url."\";' />", $return);                          
		}
		
		// Add Profile Plus if installed
		if (function_exists('symposium_profile_plus')) {
			if ($id != '') {
				$display_name = $wpdb->get_var("select display_name from ".$wpdb->base_prefix."users where ID = '".$id."'");
			} else {
				$display_name = '';
			}
			if (symposium_friend_of($id, $current_user->ID)) {
		       	$return = str_replace("class='", "rel='friend' title = '".$display_name."' id='".$id."' class='symposium-follow ", $return);
			} else {
				if (symposium_pending_friendship($id)) {
			       	$return = str_replace("class='", "rel='pending' title = '".$display_name."' id='".$id."' class='symposium-follow ", $return);
				} else {
			       	$return = str_replace("class='", "rel='' title = '".$display_name."' id='".$id."' class='symposium-follow ", $return);
				}
			}
			if (symposium_is_following($current_user->ID, $id)) {
				$return = str_replace("class='", "rev='following' class='", $return);
			} else {
				$return = str_replace("class='", "rev='' class='", $return);
			}

		}

		return $return;
	
	}
	
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

	if (!WPS_LITE && !strpos($buffer, "<rss") ) {

		global $wpdb;
		$emoticons = WPS_EMOTICONS;
		
		if ($emoticons == "on") {
			
			$smileys = WP_PLUGIN_URL . '/wp-symposium/images/smilies/';
			$smileys_dir = WP_PLUGIN_DIR . '/wp-symposium/images/smilies/';
			// Smilies as classic text
			$buffer = str_replace(":)", "<img src='".$smileys."smile.png' alt='emoticon'/>", $buffer);
			$buffer = str_replace(":-)", "<img src='".$smileys."smile.png' alt='emoticon'/>", $buffer);
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

function symposium_strip_smilies($buffer){ 
	$buffer = str_replace(":)", "", $buffer);
	$buffer = str_replace(":-)", "", $buffer);
	$buffer = str_replace(":(", "", $buffer);
	$buffer = str_replace(":'(", "", $buffer);
	$buffer = str_replace(":x", "", $buffer);
	$buffer = str_replace(":X", "", $buffer);
	$buffer = str_replace(":D", "", $buffer);
	$buffer = str_replace(":|", "", $buffer);
	$buffer = str_replace(":?", "", $buffer);
	$buffer = str_replace(":z", "", $buffer);
	$buffer = str_replace(":P", "", $buffer);
	$buffer = str_replace(";)", "", $buffer);
	
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
	    $myStyleUrl = WP_PLUGIN_URL . '/wp-symposium/css/'.WPS_CSS_FILE;
	    $myStyleFile = WP_PLUGIN_DIR . '/wp-symposium/css/'.WPS_CSS_FILE;
	    if ( file_exists($myStyleFile) ) {
	        wp_register_style('symposium_StyleSheet', $myStyleUrl);
	        wp_enqueue_style('symposium_StyleSheet');
	    }

	 	// Load Colorbox?
	 	if (WPS_COLORBOX) {
	 	    if (WPS_CSS_FILE == 'wps.css') {
			    $colorboxStyleUrl = WP_PLUGIN_URL . '/wp-symposium/css/colorbox.css';
		    	$colorboxStyleFile = WP_PLUGIN_DIR . '/wp-symposium/css/colorbox.css';
	 	    } else {
			    $colorboxStyleUrl = WP_PLUGIN_URL . '/wp-symposium/css/colorbox.min.css';
		    	$colorboxStyleFile = WP_PLUGIN_DIR . '/wp-symposium/css/colorbox.min.css';
	 	    }
	    	// Load CSS
		    if ( file_exists($colorboxStyleFile) ) {
		        wp_register_style('symposium_Colorbox_StyleSheet', $colorboxStyleUrl);
		        wp_enqueue_style('symposium_Colorbox_StyleSheet');
		    }
	 	}

		// Notices
		include_once('dialogs.php');
			
	}



}

// Language files
function symposium_languages() {
	load_plugin_textdomain( 'wp-symposium' );		
}

// Add jQuery and jQuery scripts
function js_init() {
	global $wpdb;
		
	$plugin = WP_CONTENT_URL.'/plugins/wp-symposium';

	// Only load if chosen
	if (!is_admin()) {

		if (WPS_JQUERY == "on") {
			wp_enqueue_script('jquery');	 		
		}
	 	
	 	if (WPS_WYSIWYG == "on") {
	 		wp_enqueue_script('wps-tinymce', $plugin.'/tiny_mce/tiny_mce.js', array('jquery'));	
	 	}
	}

	if (WPS_JQUERYUI == "on" || is_admin()) {
 		wp_enqueue_script('jquery-ui-custom', $plugin.'/js/jquery-ui-1.8.11.custom.min.js', array('jquery'));	
        wp_register_style('symposium_jquery-ui-css', WP_PLUGIN_URL.'/wp-symposium/css/jquery-ui-1.8.11.custom.css');
        wp_enqueue_style('symposium_jquery-ui-css');

	}	
	
}

// Perform admin duties, such as add jQuery and jQuery scripts and other admin jobs
function symposium_admin_init() {
	if (is_admin()) {

		// Color Picker
     	wp_register_script('symposium_iColorPicker', WP_PLUGIN_URL . '/wp-symposium/js/iColorPicker.js');
	    wp_enqueue_script('symposium_iColorPicker');
	
	}
}

// Add Symposium JS scripts to WordPress for use and other preparatory stuff
function symposium_scriptsAction() {

	$symposium_plugin_url = WP_PLUGIN_URL.'/wp-symposium/';
	$symposium_plugin_path = str_replace("http://".$_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"], "", $symposium_plugin_url);
 
	global $wpdb, $current_user;
	wp_get_current_user();

	// Config
	$config = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."symposium_config"));

	// Set core constants (to reduce database calls throughout)
	define('WPS_CATEGORIES_BACKRGOUND', $config->categories_background);
	define('WPS_CATEGORIES_COLOR', $config->categories_color);
	define('WPS_BIGBUTTON_BACKGROUND', $config->bigbutton_background);
	define('WPS_BIGBUTTON_COLOR', $config->bigbutton_color);
	define('WPS_BIGBUTTON_BACKGROUND_HOVER', $config->bigbutton_background_hover);
	define('WPS_BIGBUTTON_COLOR_HOVER', $config->bigbutton_color_hover);
	define('WPS_BG_COLOR_1', $config->bg_color_1);
	define('WPS_BG_COLOR_2', $config->bg_color_2);
	define('WPS_BG_COLOR_3', $config->bg_color_3);
	define('WPS_TEXT_COLOR', $config->text_color);
	define('WPS_TABLE_ROLLOVER', $config->table_rollover);
	define('WPS_LINK', $config->link);
	define('WPS_LINK_HOVER', $config->link_hover);
	define('WPS_TABLE_BORDER', $config->table_border);
	define('WPS_REPLIES_BORDER_SIZE', $config->replies_border_size);
	define('WPS_TEXT_COLOR_2', $config->text_color_2);
	define('WPS_ROW_BORDER_STYLE', $config->row_border_style);
	define('WPS_ROW_BORDER_SIZE', $config->row_border_size);
	define('WPS_BORDER_RADIUS', $config->border_radius);
	define('WPS_LABEL', $config->label);
	define('WPS_FOOTER', $config->footer);
	define('WPS_SEND_SUMMARY', $config->send_summary);
	define('WPS_FORUM_URL', $config->forum_url);
	define('WPS_FROM_EMAIL', $config->from_email);
	define('WPS_UNDERLINE', $config->underline);
	define('WPS_PREVIEW1', $config->preview1);
	define('WPS_PREVIEW2', $config->preview2);
	define('WPS_VIEWER', $config->viewer);
	define('WPS_INCLUDE_ADMIN', $config->include_admin);
	define('WPS_OLDEST_FIRST', $config->oldest_first);
	define('WPS_WP_WIDTH', $config->wp_width);
	define('WPS_MAIN_BACKGROUND', $config->main_background);
	define('WPS_CLOSED_OPACITY', $config->closed_opacity);
	define('WPS_CLOSED_WORD', $config->closed_word);
	define('WPS_FONTFAMILY', $config->fontfamily);
	define('WPS_FONTSIZE', $config->fontsize);
	define('WPS_HEADINGSFAMILY', $config->headingsfamily);
	define('WPS_HEADINGSSIZE', $config->headingssize);
	define('WPS_JQUERY', $config->jquery);
	define('WPS_EMOTICONS', $config->emoticons);
	define('WPS_MODERATION', $config->moderation);
	define('WPS_ALLOW_NEW_TOPICS', $config->allow_new_topics);
	define('WPS_MAIL_URL', $config->mail_url);
	define('WPS_SOUND', $config->sound);
	define('WPS_PROFILE_URL', $config->profile_url);
	define('WPS_ONLINE', $config->online);
	define('WPS_OFFLINE', $config->offline);
	define('WPS_USE_CHAT', $config->use_chat);
	define('WPS_BAR_POLLING', $config->bar_polling);
	define('WPS_CHAT_POLLING', $config->chat_polling);
	define('WPS_USE_WP_PROFILE', $config->use_wp_profile);
	define('WPS_WP_ALIGNMENT', $config->wp_alignment);
	define('WPS_ENABLE_PASSWORD', $config->enable_password);
	define('WPS_JQUERYUI', $config->jqueryui);
	define('WPS_MEMBERS_URL', $config->members_url);
	define('WPS_SHARING', $config->sharing);
	define('WPS_USE_STYLES', $config->use_styles);
	define('WPS_SHOW_WALL_EXTRAS', $config->show_wall_extras);
	define('WPS_USE_CHATROOM', $config->use_chatroom);
	define('WPS_CHATROOM_BANNED', $config->chatroom_banned);
	define('WPS_PROFILE_GOOGLE_MAP', $config->profile_google_map);
	define('WPS_USE_POKE', $config->use_poke);
	define('WPS_MOTD', $config->motd);
	define('WPS_GROUPS_URL', $config->groups_url);
	define('WPS_GROUP_URL', $config->group_url);
	define('WPS_GROUP_ALL_CREATE', $config->group_all_create);
	define('WPS_PROFILE_AVATARS', $config->profile_avatars);
	define('WPS_IMG_DB', $config->img_db);
	define('WPS_IMG_PATH', $config->img_path);
	define('WPS_IMG_UPLOAD', $config->img_upload);
	define('WPS_IMG_URL', $config->img_url);
	define('WPS_IMG_CROP', $config->img_crop);
	define('WPS_FORUM_RANKS', $config->forum_ranks);
	define('WPS_FORUM_AJAX', $config->forum_ajax);
	define('WPS_TEMPLATE_PROFILE_HEADER', $config->template_profile_header);
	define('WPS_INITIAL_FRIEND', $config->initial_friend);
	define('WPS_TEMPLATE_PROFILE_BODY', $config->template_profile_body);
	define('WPS_TEMPLATE_PAGE_FOOTER', $config->template_page_footer);
	define('WPS_TEMPLATE_EMAIL', $config->template_email);
	define('WPS_TEMPLATE_FORUM_HEADER', $config->template_forum_header);
	define('WPS_TEMPLATE_MAIL', $config->template_mail);
	define('WPS_TEMPLATE_MAIL_TRAY', $config->template_mail_tray);
	define('WPS_TEMPLATE_MAIL_MESSAGE', $config->template_mail_message);
	define('WPS_TEMPLATE_GROUP', $config->template_group);
	define('WPS_FACEBOOK_API', $config->facebook_api);
	define('WPS_FACEBOOK_SECRET', $config->facebook_secret);
	define('WPS_FORUM_LOGIN', $config->forum_login);
	define('WPS_CSS', $config->css);
	define('WPS_TEMPLATE_FORUM_CATEGORY', $config->template_forum_category);
	define('WPS_TEMPLATE_FORUM_TOPIC', $config->template_forum_topic);
	define('WPS_TEMPLATE_GROUP_FORUM_CATEGORY', $config->template_group_forum_category);
	define('WPS_TEMPLATE_GROUP_FORUM_TOPIC', $config->template_group_forum_topic);
	define('WPS_MOBILE_TOPICS', $config->mobile_topics);
	define('WPS_BUMP_TOPICS', $config->bump_topics);
	define('WPS_SHOW_DOB', $config->show_dob);
	define('WPS_FORUM_EDITOR', $config->forum_editor);
	define('WPS_USE_VOTES', $config->use_votes);
	define('WPS_SHOW_BUTTONS', $config->show_buttons);
	define('WPS_USE_VOTES_REMOVE', $config->use_votes_remove);
	define('WPS_SHOW_ADMIN', $config->show_admin);
	define('WPS_POKE_LABEL', $config->poke_label);
	define('WPS_SYMPOSIUM_FORUMLATESTPOSTS_COUNT', $config->symposium_forumlatestposts_count);
	define('WPS_REDIRECT_WP_PROFILE', $config->redirect_wp_profile);
	define('WPS_STRIPTAGS', $config->striptags);
	define('WPS_GROUP_INVITES', $config->group_invites);
	define('WPS_GROUP_INVITES_MAX', $config->group_invites_max);
	define('WPS_FORUM_UPLOADS', $config->forum_uploads);
	define('WPS_FORUM_THUMBS', $config->forum_thumbs);
	define('WPS_FORUM_THUMBS_SIZE', $config->forum_thumbs_size);
	define('WPS_FORUM_INFO', $config->forum_info);
	define('WPS_USE_VOTES_MIN', $config->use_votes_min);
	define('WPS_USE_ANSWERS', $config->use_answers);
	define('WPS_INITIAL_GROUPS', $config->initial_groups);
	define('WPS_IMAGE_EXT', $config->image_ext);
	define('WPS_VIDEO_EXT', $config->video_ext);
	define('WPS_DOC_EXT', $config->doc_ext);
	define('WPS_MENU_MY_ACTIVITY', $config->menu_my_activity);
	define('WPS_MENU_FRIENDS_ACTIVITY', $config->menu_friends_activity);
	define('WPS_MENU_ALL_ACTIVITY', $config->menu_all_activity);
	define('WPS_MENU_PROFILE', $config->menu_profile);
	define('WPS_MENU_FRIENDS', $config->menu_friends);
	define('WPS_MENU_TEXTHTML', $config->menu_texthtml);
	define('WPS_MAIL_ALL', $config->mail_all);
	define('WPS_ELASTIC', $config->elastic);
	define('WPS_PROFILE_SHOW_UNCHECKED', $config->profile_show_unchecked);
	define('WPS_IMAGES_URL', $config->images);
	define('WPS_SHOW_DIR_BUTTONS', $config->show_dir_buttons);
	define('WPS_DIR_PAGE_LENGTH', $config->dir_page_length);
	define('WPS_LITE', $config->wps_lite);
	define('WPS_PROFILE_DEFAULT', $config->wps_profile_default);
	define('WPS_PANEL_ALL', $config->wps_panel_all);
	define('WPS_DEFAULT_FORUM', $config->wps_default_forum);
	define('WPS_USE_GRAVATAR', $config->wps_use_gravatar);
	define('WPS_JS_FILE', $config->wps_js_file);
	define('WPS_CSS_FILE', $config->wps_css_file);
	define('WPS_COLORBOX', $config->colorbox);
	define('WPS_JSCHARTS', $config->jscharts);
	define('WPS_ALLOW_REPORTS', $config->allow_reports);
	define('WPS_AJAX_WIDGETS', $config->ajax_widgets);
	define('WPS_STATUS_POST', $config->status_label);
	define('WPS_FORUM_STARS', $config->forum_stars);	
	if ( symposium_is_plus() ) {	
		define('WPS_WYSIWYG', $config->use_wysiwyg);	
		define('WPS_WYSIWYG_1', $config->use_wysiwyg_1);	
		define('WPS_WYSIWYG_2', $config->use_wysiwyg_2);	
		define('WPS_WYSIWYG_3', $config->use_wysiwyg_3);	
		define('WPS_WYSIWYG_4', $config->use_wysiwyg_4);	
		define('WPS_WYSIWYG_CSS', $config->use_wysiwyg_css);	
		define('WPS_WYSIWYG_SKIN', $config->use_wysiwyg_skin);	
		define('WPS_WYSIWYG_WIDTH', $config->use_wysiwyg_width);	
		define('WPS_WYSIWYG_HEIGHT', $config->use_wysiwyg_height);	
	} else {
		define('WPS_WYSIWYG', '');	
		define('WPS_WYSIWYG_1', '');	
		define('WPS_WYSIWYG_2', '');	
		define('WPS_WYSIWYG_3', '');	
		define('WPS_WYSIWYG_4', '');	
		define('WPS_WYSIWYG_CSS', '');	
		define('WPS_WYSIWYG_SKIN', '');	
		define('WPS_WYSIWYG_WIDTH', '');	
		define('WPS_WYSIWYG_HEIGHT', '');	
	}
	
	// Set script timeout
	if ($config->wps_time_out > 0) {
		set_time_limit($config->wps_time_out);
	}

	// Non-core
	define('WPS_GALLERY_URL', isset($config->gallery_url) ? $config->gallery_url : '');
	
	// Set up variables for use throughout
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

		// Group page
		if (isset($_GET['gid'])) {
			$page_gid = $_GET['gid']*1;
		} else {
			$page_gid = 0;
			if (isset($_POST['gid'])) { 
				$page_gid = $_POST['gid']*1; 
			}
		}
		// If visiting a group page, check to see if forum is default view
		if (is_user_logged_in() && $page_gid > 0) {
			$forum = $wpdb->get_row($wpdb->prepare("SELECT group_forum, show_forum_default FROM ".$wpdb->prefix."symposium_groups WHERE gid = %d", $page_gid));
			if ($forum->show_forum_default == 'on' && $forum->group_forum == 'on') {
				$cat_id = $forum->show_forum_default;
			}
		}
								
		// Gallery
		$album_id = 0;
		if (isset($_GET['album_id'])) { $album_id = $_GET['album_id']; }
		if (isset($_POST['album_id'])) { $album_id = $_POST['album_id']; }
			
		// Permalink in use?
		$http = 'http';
		if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == 'on') { $http .= "s"; }
		$url = $http.'://'.dirname($_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']);
		if ( get_option('permalink_structure') != '' || $url == symposium_get_url('profile') ) { 
			$q = "?";
		} else {
			$q = "&";
		}
		
		// Get styles for JS
		if (WPS_USE_STYLES == "on") {
			$bg_color_2 = WPS_BG_COLOR_2;
			$row_border_size = WPS_ROW_BORDER_SIZE;
			$row_border_style = WPS_ROW_BORDER_STYLE;
			$text_color_2 = WPS_TEXT_COLOR_2;
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

		// Embedded content from external plugin?
		if (isset($_GET['embed'])) {
			$embed = 'on';
		} else {
			$embed = '';
		}
	
		// to parameter
		if (isset($_GET['to'])) {
			$to = $_GET['to'];
		} else {
			$to = '';
		}
		
		// mail ID
		if (isset($_GET['mid'])) {
			$mid = $_GET['mid'];
		} else {
			$mid = '';
		}
		
		// Get forum upload valid extensions
		$permitted_ext = WPS_IMAGE_EXT.','.WPS_VIDEO_EXT.','.WPS_DOC_EXT;
				
		// Load Symposium JS supporting scripts		
		wp_enqueue_script('jquery-uploadify', WP_PLUGIN_URL.'/wp-symposium/uploadify/jquery.uploadify.v2.1.4.js', array('jquery'));		

		// Load Symposium JS
	 	wp_enqueue_script('symposium', $symposium_plugin_url.'js/'.WPS_JS_FILE, array('jquery'));
	 	
	 	// Load Colorbox?
	 	if (WPS_COLORBOX) {
	 	    if (WPS_JS_FILE == 'wps.js') {
			 	wp_enqueue_script('wps_colorbox', $symposium_plugin_url.'js/colorbox.js', array('jquery'));
	 	    } else {
			 	wp_enqueue_script('wps_colorbox', $symposium_plugin_url.'js/colorbox.min.js', array('jquery'));
	 	    }
	 	}
	
	 	// Load JScharts and jCrop?
	 	if (WPS_JSCHARTS) {
	 	    if (WPS_JS_FILE == 'wps.js') {
			 	wp_enqueue_script('wps_jscharts', $symposium_plugin_url.'js/jscharts.js', array('jquery'));
	 	    } else {
			 	wp_enqueue_script('wps_jscharts', $symposium_plugin_url.'js/jscharts.min.js', array('jquery'));
	 	    }
	 	}
	
		// Set JS variables
		wp_localize_script( 'symposium', 'symposium', array(
			'plugins' => WP_PLUGIN_URL, 
			'plugin_url' => WP_PLUGIN_URL.'/wp-symposium/', 
			'plugin_path' => $symposium_plugin_path,
			'plugin_pro_url' => WP_PLUGIN_URL.'/wp-symposium-', 
			'images_url' => WPS_IMAGES_URL,
			'inactive' => WPS_ONLINE,
			'forum_url' => symposium_get_url('forum'),
			'mail_url' => symposium_get_url('mail'),
			'profile_url' => symposium_get_url('profile'),
			'groups_url' => symposium_get_url('groups'),
			'group_url' => symposium_get_url('group'),
			'gallery_url' => WPS_GALLERY_URL,
			'page_gid' => $page_gid,
			'offline' => WPS_OFFLINE,
			'use_chat' => WPS_USE_CHAT,
			'chat_polling' => WPS_CHAT_POLLING,
			'bar_polling' => WPS_BAR_POLLING,
			'view' => $view,
			'profile_default' => WPS_PROFILE_DEFAULT,
			'show_tid' => $show_tid,
			'cat_id' => $cat_id,
			'album_id' => $album_id,
			'current_user_id' => $current_user->ID,
			'current_user_display_name' => $display_name,
			'current_user_level' => symposium_get_current_userlevel($current_user->ID),
			'current_user_page' => $page_uid,
			'current_group' => $page_gid,
			'post' => $GETpost,
			'please_wait' => __('Please Wait...', 'wp-symposium'),
			'saving' => __('Saving...', 'wp-symposium'),
			'site_title' => get_bloginfo('name'),
			'site_url' => get_bloginfo('url'),
			'q' => $q,
			'bg_color_2' => $bg_color_2,
			'row_border_size' => $row_border_size,
			'row_border_style' => $row_border_style,
			'text_color_2' => $text_color_2,
			'template_mail_tray' => WPS_TEMPLATE_MAIL_TRAY,
			'embed' => $embed,
			'to' => $to,
			'is_admin' => 0,
			'mail_id' => $mid,
			'permitted_ext' => $permitted_ext,
			'forum_ajax' => WPS_FORUM_AJAX,
			'wps_lite' => WPS_LITE,
			'wps_use_poke' => WPS_USE_POKE,
			'wps_forum_stars' => WPS_FORUM_STARS,
			'wps_wysiwyg' => WPS_WYSIWYG,
			'wps_wysiwyg_1' => WPS_WYSIWYG_1,
			'wps_wysiwyg_2' => WPS_WYSIWYG_2,
			'wps_wysiwyg_3' => WPS_WYSIWYG_3,
			'wps_wysiwyg_4' => WPS_WYSIWYG_4,
			'wps_wysiwyg_css' => WPS_WYSIWYG_CSS,
			'wps_wysiwyg_skin' => WPS_WYSIWYG_SKIN,
			'wps_wysiwyg_width' => WPS_WYSIWYG_WIDTH,
			'wps_wysiwyg_height' => WPS_WYSIWYG_HEIGHT,
			'wps_plus' => (defined('WPS_PLUS')) ? WPS_PLUS : ''
		));

	} else {
		
		// ADMIN JS load

		wp_enqueue_script('jquery-uploadify', WP_PLUGIN_URL.'/wp-symposium/uploadify/jquery.uploadify.v2.1.4.js', array('jquery'));

		// Load Symposium JS
	 	wp_enqueue_script('symposium', $symposium_plugin_url.'js/'.WPS_JS_FILE, array('jquery'));

		// Set JS variables
		wp_localize_script( 'symposium', 'symposium', array(
			'plugins' => WP_PLUGIN_URL, 
			'plugin_url' => WP_PLUGIN_URL.'/wp-symposium/', 
			'plugin_path' => $symposium_plugin_path,
			'plugin_pro_url' => WP_PLUGIN_URL.'/wp-symposium-', 
			'images_url' => WPS_IMAGES_URL,
			'inactive' => WPS_ONLINE,
			'forum_url' => WPS_FORUM_URL,
			'mail_url' => WPS_MAIL_URL,
			'profile_url' => WPS_PROFILE_URL,
			'groups_url' => WPS_GROUPS_URL,
			'group_url' => WPS_GROUP_URL,
			'gallery_url' => WPS_GALLERY_URL,
			'offline' => WPS_OFFLINE,
			'use_chat' => WPS_USE_CHAT,
			'chat_polling' => WPS_CHAT_POLLING,
			'bar_polling' => WPS_BAR_POLLING,
			'current_user_id' => $current_user->ID,
			'is_admin' => 1
		));
	}


}


?>
