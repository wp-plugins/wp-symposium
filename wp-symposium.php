<?php
/*
Plugin Name: WP Symposium A Social Network For WordPress
Plugin URI: http://www.wpsymposium.com
Description: Core code for Symposium, this plugin must always be activated, before any other Symposium plugins/widgets (they rely upon it).
Version: 12.07.14
Author: WP Symposium
Author URI: http://www.wpsymposium.com
License: GPL3
*/
	
/*  Copyright 2010,2011,2012  Simon Goodchild  (info@wpsymposium.com)

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

// Set WPS version
define('WPS_VER', '12.07.14');

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
	// deactivation
	register_deactivation_hook(__FILE__, 'symposium_deactivate');

}

/* ===================================================== ADMIN ====================================================== */	

// Check for updates
if ( ( get_option("symposium_version") != WPS_VER && is_admin()) || (isset($_GET['force_create_wps']) && $_GET['force_create_wps'] == 'yes' && is_admin())) {

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

	// Create initial versions of tables *************************************************************************************

	include('create_tables.php');
	
	// Copy across all of config table if it exists (from older versions) additions ignored if it's not there
	$res = mysql_query("DESCRIBE ".$wpdb->prefix."symposium_config");
	if ($res) {
		while($row = mysql_fetch_array($res)) {	
			$field = $row['Field'];
			$sql = "SELECT ".$field." FROM ".$wpdb->prefix."symposium_config";
			$value = $wpdb->get_var($sql);
			$option = 'symposium_'.$field;
			update_option($option, $value);
		}		
	   	// Drop config table
	   	$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix."symposium_config");
	}

	// Add new core WPS wp_options if don't yet exist
	if (!$wpdb->get_var($wpdb->prepare("SELECT option_name FROM ".$wpdb->prefix."options WHERE option_name = 'symposium_categories_background'"))) {
		update_option('symposium_categories_background', '#0072bc'); 
		update_option('symposium_categories_color', '#fff'); 
		update_option('symposium_bigbutton_background', '#0072bc'); 
		update_option('symposium_bigbutton_color', '#fff'); 
		update_option('symposium_bigbutton_background_hover', '#00aeef');
		update_option('symposium_bigbutton_color_hover', '#fff'); 
		update_option('symposium_bg_color_1', '#0072bc'); 
		update_option('symposium_bg_color_2', '#ebebeb');
		update_option('symposium_bg_color_3', '#fff'); 
		update_option('symposium_text_color', '#000'); 
		update_option('symposium_table_rollover', '#fbaf5a'); 
		update_option('symposium_link', '#0054a5'); 
		update_option('symposium_link_hover', '#000'); 
		update_option('symposium_table_border', 2); 
		update_option('symposium_replies_border_size', 1); 
		update_option('symposium_text_color_2', '#0054a5'); 
		update_option('symposium_row_border_style', 'dotted'); 
		update_option('symposium_row_border_size', 1); 
		update_option('symposium_border_radius', 2);
		update_option('symposium_label', '#000');
		update_option('symposium_footer', __('Please don\'t reply to this email', "wp-symposium"));
		update_option('symposium_send_summary', 'on');
		update_option('symposium_forum_url', __('Important: Please update!', "wp-symposium"));	 			  
		update_option('symposium_from_email', 'noreply@example.com');
		update_option('symposium_underline', 'on');
		update_option('symposium_preview1', 45);
		update_option('symposium_preview2', 90);
		update_option('symposium_include_admin', 'on');
		update_option('symposium_oldest_first', 'on');
		update_option('symposium_wp_width', '100%');
		update_option('symposium_main_background', '#fff');
		update_option('symposium_closed_opacity', '1.0');
		update_option('symposium_closed_word', __('closed', "wp-symposium"));
		update_option('symposium_fontfamily', 'Georgia,Times');
		update_option('symposium_headingsfamily', 'Arial,Helvetica');
		update_option('symposium_fontsize', 13);
		update_option('symposium_headingssize', 20);
		update_option('symposium_jquery', 'on');
		update_option('symposium_jqueryui', 'on');
		update_option('symposium_emoticons', 'on');
		update_option('symposium_moderation', '');
		update_option('symposium_mail_url', __('Important: Please update!', "wp-symposium"));
		update_option('symposium_online', 3);
		update_option('symposium_offline', 15);
		update_option('symposium_wp_alignment', 'Center');
		update_option('symposium_enable_password', 'on');
		update_option('symposium_use_wp_profile', '');
		update_option('symposium_members_url', __('Important: Please update!', "wp-symposium"));
		update_option('symposium_sharing', '');
		update_option('symposium_use_styles', '');
		update_option('symposium_show_wall_extras', '');
		update_option('symposium_use_chat', 'on');
		update_option('symposium_bar_polling', 120);
		update_option('symposium_chat_polling', 10);
		update_option('symposium_use_chatroom', 'on');
		update_option('symposium_chatroom_banned', '');
		update_option('symposium_profile_google_map', 150);
		update_option('symposium_use_poke', 'on');
		update_option('symposium_poke_label', 'Hey!');
		update_option('symposium_motd', '');
		update_option('symposium_profile_url', __('Important: Please update!', "wp-symposium"));
		update_option('symposium_groups_url', __('Important: Please update!', "wp-symposium"));
		update_option('symposium_group_url', __('Important: Please update!', "wp-symposium"));
		update_option('symposium_group_all_create', 'on');
		update_option('symposium_group_invites', 'on');
		update_option('symposium_group_invites_max', 10);
		update_option('symposium_profile_avatars', 'on');
		update_option('symposium_img_db', '');	
		update_option('symposium_img_path', WP_CONTENT_DIR.'/wps-content');
		$img_url = WP_CONTENT_URL."/wps-content";
		$img_url = str_replace(siteURL(), '', $img_url); 
		update_option('symposium_img_url', $img_url);
		update_option('symposium_img_upload', '');
		update_option('symposium_img_crop', 'on');
		update_option('symposium_forum_ranks', '');
		update_option('symposium_forum_ajax', '');
		update_option('symposium_forum_login', 'on');
		update_option('symposium_initial_friend', '');
		update_option('symposium_initial_groups', '');
		update_option('symposium_template_profile_header', '');
		update_option('symposium_template_profile_body', '');
		update_option('symposium_template_page_footer', '');
		update_option('symposium_template_email', '');
		update_option('symposium_template_forum_header', '');
		update_option('symposium_template_forum_category', '');
		update_option('symposium_template_forum_topic', '');
		update_option('symposium_template_mail_tray', '');
		update_option('symposium_template_mail_message', '');
		update_option('symposium_template_group', '');
		update_option('symposium_template_group_forum_category', '');
		update_option('symposium_template_group_forum_topic', '');
		update_option('symposium_facebook_api', '');
		update_option('symposium_facebook_secret', '');
		update_option('symposium_css', '');
		update_option('symposium_mobile_topics', 20);
		update_option('symposium_bump_topics', 'on');
		update_option('symposium_show_dob', 'on');
		update_option('symposium_use_votes', 'on');
		update_option('symposium_use_votes_remove', 0);
		update_option('symposium_show_buttons', '');
		update_option('symposium_show_admin', 'on');
		update_option('symposium_forumlatestposts_count', 100);
		update_option('symposium_redirect_wp_profile', 'on');
		update_option('symposium_striptags', '');
		update_option('symposium_forum_uploads', '');
		update_option('symposium_forum_thumbs', '');
		update_option('symposium_forum_thumbs_size', 400);
		update_option('symposium_forum_info', 'on');
		update_option('symposium_forum_stars', 'on');
		update_option('symposium_use_votes_min', 10);
		update_option('symposium_use_answers', '');
		update_option('symposium_image_ext', '*.jpg,*.gif,*.png,*.jpeg');
		update_option('symposium_video_ext', '*.mp4');
		update_option('symposium_doc_ext', '*.pdf,*.txt,*.zip');
		update_option('symposium_menu_my_activity', 'on');
		update_option('symposium_menu_friends_activity', 'on');
		update_option('symposium_menu_all_activity', 'on');
		update_option('symposium_menu_profile', 'on');
		update_option('symposium_menu_friends', 'on');
		update_option('symposium_menu_texthtml', '');
		update_option('symposium_mail_all', '');
		update_option('symposium_elastic', 'on');
		update_option('symposium_profile_show_unchecked', 'on');
		$images = WP_PLUGIN_URL."/wp-symposium/images";
		$images = str_replace(siteURL(), '', $images); 
		update_option('symposium_images', $images);
		update_option('symposium_show_dir_buttons', 'on');
		update_option('symposium_dir_page_length', 25);
		update_option('symposium_wps_lite', '');
		update_option('symposium_wps_profile_default', 'activity');
		update_option('symposium_wps_panel_all', '');
		update_option('symposium_wps_default_forum', '');
		update_option('symposium_wps_use_gravatar', 'on');
		update_option('symposium_wps_time_out', 0);
		update_option('symposium_wps_js_file', 'wps.min.js');
		update_option('symposium_wps_css_file', 'wps.min.css');
		update_option('symposium_allow_reports', '');
		update_option('symposium_ajax_widgets', '');
		update_option('symposium_status_label', __('What`s up?', "wp-symposium"));
		update_option('symposium_jscharts', 'on');
		update_option('symposium_use_wysiwyg', '');
		update_option('symposium_use_wysiwyg_1', 'bold,italic,|,fontselect,fontsizeselect,forecolor,backcolor,|,bullist,numlist,|,link,unlink,|,image,media,|,emotions');
		update_option('symposium_use_wysiwyg_2', '');
		update_option('symposium_use_wysiwyg_3', '');
		update_option('symposium_use_wysiwyg_4', '');
		update_option('symposium_use_wysiwyg_css', str_replace(siteURL(), '', WP_PLUGIN_URL."/wp-symposium/tiny_mce/themes/advanced/skins/wps.css"));
		update_option('symposium_use_wysiwyg_skin', 'cirkuit');
		update_option('symposium_use_wysiwyg_width', 563);
		update_option('symposium_use_wysiwyg_height', 300);
		update_option('symposium_forum_refresh', '');
		update_option('symposium_subject_mail_new', __('New Mail Message: [subject]', "wp-symposium"));
		update_option('symposium_subject_forum_new', __('New Forum Topic', "wp-symposium"));
		update_option('symposium_subject_forum_reply', __('New Forum Reply', "wp-symposium"));
		update_option('symposium_profile_comments', 'on');
		update_option('symposium_forum_login_form', 'on');
		update_option('symposium_forum_lock', 30);
		update_option('symposium_use_wysiwyg_3', '');
		update_option('symposium_dir_level', 's:60:\"Everyone,Administrator,Editor,Author,Contributor,Subscriber,\";');		
		update_option('symposium_viewer', 's:16:\"s:9:\"everyone,\";\";');
		update_option('symposium_forum_editor', 's:59:\"s:51:\"Administrator,Editor,Author,Contributor,Subscriber,\";\";');
		update_option('symposium_forum_reply', 's:59:\"s:51:\"Administrator,Editor,Author,Contributor,Subscriber,\";\";');
		update_option('symposium_rewrite_forum_single', '');
		update_option('symposium_rewrite_forum_single_target', '');
		update_option('symposium_rewrite_forum_double', '');
		update_option('symposium_rewrite_forum_double_target', '');
		
	}
	
	// Set default values for if not yet set
	if (get_option('symposium_template_profile_header') == '') {
		update_option('symposium_template_profile_header', "<div id='profile_header_div'>[]<div id='profile_header_panel'>[]<div id='profile_details'>[]<div style='float:right'>[poke]</div>[]<div style='float:right'>[follow]</div>[]<div id='profile_name'>[display_name]</div>[]<p>[location]<br />[born]</p>[]<div style='padding: 0px;'>[actions]</div>[]</div>[]</div>[]<div id='profile_photo' class='corners'>[avatar,170]</div>[]</div>");
	}
	if (get_option('symposium_template_profile_body') == '') {
		update_option('symposium_template_profile_body', "<div id='profile_wrapper'>[]<div id='force_profile_page' style='display:none'>[default]</div>[]<div id='profile_body_wrapper'>[]<div id='profile_body'>[page]</div>[]</div>[]<div id='profile_menu'>[menu]</div>[]</div>");
	}
	if (get_option('symposium_template_page_footer') == '') {
		update_option('symposium_template_page_footer', "<div id='powered_by_wps'>[]<a href='http://www.wpsymposium.com' target='_blank'>[powered_by_message] v[version]</a>[]</div>");
	}
	if (get_option('symposium_template_mail_tray') == '') {
		update_option('symposium_template_mail_tray', "<div id='mail_mid' class='mail_item mail_read'>[]<div class='mailbox_message_from'>[mail_from]</div>[]<div class='mail_item_age'>[mail_sent]</div>[]<div class='mailbox_message_subject'>[mail_subject]</div>[]<div class='mailbox_message'>[mail_message]</div>[]</div>");
	}
	if (get_option('symposium_template_mail_message') == '') {
		update_option('symposium_template_mail_message', "<div id='message_header'><div id='message_header_delete'>[reply_button][delete_button]</div><div id='message_header_avatar'>[avatar,44]</div>[mail_subject]<br />[mail_recipient] [mail_sent]</div></div><div id='message_mail_message'>[message]</div>");
	}
	if (get_option('symposium_template_email') == '') {
		update_option('symposium_template_email', "<style> body { background-color: #eee; } </style>[]<div style='margin: 20px; padding:20px; border-radius:10px; background-color: #fff;border:1px solid #000;'>[][message][]<br /><hr />[][footer]<br />[]<a href='http://www.wpsymposium.com' target='_blank'>[powered_by_message] v[version]</a>[]</div>");
	}
	if (get_option('symposium_template_forum_header') == '') {
		update_option('symposium_template_forum_header', "[breadcrumbs][new_topic_button][new_topic_form][][digest][subscribe][][forum_options][][sharing]");
	}
	if (get_option('symposium_template_group') == '') {
		update_option('symposium_template_group', "<div id='group_header_div'><div id='group_header_panel'>[]<div id='group_details'>[]<div id='group_name'>[group_name]</div>[]<div id='group_description'>[group_description]</div>[]<div style='padding-top: 15px;padding-bottom: 15px;'>[actions]</div>[]</div></div>[]<div id='group_photo' class='corners'>[avatar,170]</div>[]</div>[]<div id='group_wrapper'>[]<div id='force_group_page' style='display:none'>[default]</div>[]<div id='group_body_wrapper'>[]<div id='group_body'>[page]</div>[]</div>[]<div id='group_menu'>[menu]</div>[]</div>");
	}
	if (get_option('symposium_template_forum_category') == '') {
		update_option('symposium_template_forum_category', "<div class='row_startedby'>[]<div class='avatar avatar_last_topic'>[avatar,32]</div>[]<div class='last_topic_text'>[replied][subject][ago]</div>[]</div>[]<div class='row_views'>[post_count]</div>[]<div class='row_topic row_replies'>[topic_count]</div>[]<div class='row_topic'>[category_title]<br />[category_desc]</div>");
	}
	if (get_option('symposium_template_forum_topic') == '') {
		update_option('symposium_template_forum_topic', "<div class='row_startedby'>[]<div class='avatar avatar_last_topic'>[avatar,32]</div>[]<div class='last_topic_text'>[replied][topic][ago]</div>[]</div>[]<div class='row_views'>[views]</div>[]<div class='row_replies'>[replies]</div>[]<div class='row_topic'>[topic_title]</div>");
	}
	if (get_option('symposium_template_group_forum_category') == '') {
		update_option('symposium_template_group_forum_category', "<div class='row_startedby'>[]<div class='avatar avatar_last_topic'>[avatar,32]</div>[replied][subject][ago]</div>[]<div class='row_topic'>[category_title]</div>");
	}
	if (get_option('symposium_template_group_forum_topic') == '') {
		update_option('symposium_template_group_forum_topic', "<div class='row_startedby'>[]<div class='avatar avatar_last_topic'>[avatar,32]</div>[replied][topic][ago]</div>[]<div class='row_topic'>[topic_title]</div>");
	}

	// Default forum ranks
	if (get_option('symposium_forum_ranks') == '') {
		update_option('symposium_forum_ranks', "on;Emperor;0;Monarch;200;Lord;150;Duke;125;Count;100;Earl;75;Viscount;50;Bishop;25;Baron;10;Knight;5;Peasant;0");
	}
	
	// Modify Mail table
	symposium_alter_table("mail", "MODIFY", "mail_sent", "datetime", "", "");

	// Modify Forum Categories table
	symposium_alter_table("cats", "ADD", "cat_parent", "int(11)", "NOT NULL", "0");
	symposium_alter_table("cats", "ADD", "cat_desc", "varchar(256)", "", "''");
	symposium_alter_table("cats", "ADD", "level", "varchar(256)", "", "'s:60:\"Everyone,Administrator,Editor,Author,Contributor,Subscriber,\";'");
	symposium_alter_table("cats", "ADD", "stub", "varchar(256)", "", "''");

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
	symposium_alter_table("topics", "ADD", "topic_answer", "varchar(2)", "", "''");
	symposium_alter_table("topics", "ADD", "for_info", "varchar(2)", "", "''");
	symposium_alter_table("topics", "ADD", "stub", "varchar(256)", "", "''");
	symposium_alter_table("topics", "ADD", "remote_addr", "varchar(32)", "", "''");
	symposium_alter_table("topics", "ADD", "http_x_forwarded_for", "varchar(32)", "", "''");

	// Modify profile extended fields table
	symposium_alter_table("extended", "MODIFY", "extended_name", "varchar(256)", "NOT NULL", "'New field'");
	symposium_alter_table("extended", "ADD", "extended_slug", "varchar(64)", "NOT NULL", "");
	symposium_alter_table("extended", "ADD", "wp_usermeta", "varchar(256)", "", "");
	symposium_alter_table("extended", "ADD", "readonly", "varchar(2)", "", "''");

	// Update motd flag
	update_option('symposium_motd', '');

	// Setup Notifications
	symposium_notification_setoptions();
	
	// ***********************************************************************************************
 	// Update Versions *******************************************************************************
	update_option("symposium_version", WPS_VER);

	// Notify WPS developers (feel free to comment out the next line)
	mail('info@wpsymposium.com', get_bloginfo('url').' installed v'.WPS_VER, get_bloginfo('url'));
	
//    echo "<div class='updated'>".__("WP Symposium database updated to version", "wp-symposium")." ".WPS_VER."</div>";
		
}

// Any admin warnings
function symposium_admin_warnings() {

   	global $wpdb;

	// CSS check
    $myStyleFile = WP_PLUGIN_DIR . '/wp-symposium/css/'.get_option('symposium_wps_css_file');
    if ( !file_exists($myStyleFile) ) {
		echo "<div class='error'><p>WPS Symposium: ";
		_e( sprintf('Stylesheet (%s) not found.', $myStyleFile), 'wp-symposium');
		echo "</p></div>";
    }

	// JS check
    $myJSfile = WP_PLUGIN_DIR . '/wp-symposium/js/'.get_option('symposium_wps_js_file');
    if ( !file_exists($myJSfile) ) {
		echo "<div class='error'><p>WPS Symposium: ";
		_e( sprintf('Javascript file (%s) not found, please check <a href="admin.php?page=symposium_debug"></a>the installation page</a>.', $myJSfile), 'wp-symposium');
		echo "</p></div>";
    }

    // MOTD
    if (get_option('symposium_motd') != 'on' && (!(isset($_GET['page']) && $_GET['page'] == 'symposium_welcome'))) {

		if ( current_user_can( 'edit_theme_options' ) ) {   
			symposium_plugin_welcome();
		}
		
		// Check for legacy plugin folders	    
		$list = '';
		if (file_exists(WP_PLUGIN_DIR.'/wp-symposium-alerts')) { $list .= WP_PLUGIN_DIR.'/wp-symposium-alerts<br />'; }
		if (file_exists(WP_PLUGIN_DIR.'/wp-symposium-events')) { $list .= WP_PLUGIN_DIR.'/wp-symposium-events<br />'; }
		if (file_exists(WP_PLUGIN_DIR.'/wp-symposium-facebook')) { $list .= WP_PLUGIN_DIR.'/wp-symposium-facebook<br />'; }
		if (file_exists(WP_PLUGIN_DIR.'/wp-symposium-gallery')) { $list .= WP_PLUGIN_DIR.'/wp-symposium-gallery<br />'; }
		if (file_exists(WP_PLUGIN_DIR.'/wp-symposium-groups')) { $list .= WP_PLUGIN_DIR.'/wp-symposium-groups<br />'; }
		if (file_exists(WP_PLUGIN_DIR.'/wp-symposium-lounge')) { $list .= WP_PLUGIN_DIR.'/wp-symposium-lounge<br />'; }
		if (file_exists(WP_PLUGIN_DIR.'/wp-symposium-mobile')) { $list .= WP_PLUGIN_DIR.'/wp-symposium-mobile<br />'; }
		if (file_exists(WP_PLUGIN_DIR.'/wp-symposium-plus')) { $list .= WP_PLUGIN_DIR.'/wp-symposium-plus<br />'; }
		if (file_exists(WP_PLUGIN_DIR.'/wp-symposium-mailinglist')) { $list .= WP_PLUGIN_DIR.'/wp-symposium-mailinglist<br />'; }
		if (file_exists(WP_PLUGIN_DIR.'/wp-symposium-rss')) { $list .= WP_PLUGIN_DIR.'/wp-symposium-rss<br />'; }
		if (file_exists(WP_PLUGIN_DIR.'/wp-symposium-yesno')) { $list .= WP_PLUGIN_DIR.'/wp-symposium-yesno<br />'; }
		if ($list != '') {
			echo '<div class="updated" style="margin-top:15px">';
			echo "<strong>".__("WP Symposium", "wp-symposium")."</strong><br /><div style='padding:4px;'>";
			echo __('Please remove the following folders via FTP.<br />Do <strong>NOT</strong> remove them via the plugins admin page as this could delete data from your database:', 'wp-symposium').'<br /><br />';
			echo $list;
			echo '</div></div>';
		}
		
    }
    
}

// Dashboard Widget
function symposium_dashboard_widget(){
	wp_add_dashboard_widget('symposium_id', 'WP Symposium', 'symposium_widget');
}
function symposium_widget() {
	
	global $wpdb, $current_user;
	
	echo '<img src="'.get_option('symposium_images').'/logo_small.png" alt="WP Symposium logo" style="float:right; width:120px;height:120px;" />';

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
				echo __('Forum not activated', 'wp-symposium');
			}
			echo "</td></tr>";
			
			echo '<tr><td colspan="2" style="padding:4px">';
			if (function_exists('symposium_profile')) {
				$url = symposium_get_url('profile');
				echo '<a href="'.$url.symposium_string_query($url).'uid='.$current_user->ID.'">'.__('Go to Profile', 'wp-symposium').'</a>';
			} else {
				echo __('Profile not activated', 'wp-symposium');
			}
			echo "</td></tr>";

			echo '<tr><td colspan="2" style="padding:4px">';
			if (function_exists('symposium_mail')) {
				echo '<a href="'.symposium_get_url('mail').'">'.__('Go to Mail', 'wp-symposium').'</a>';
			} else {
				echo __('Mail not activated', 'wp-symposium');
			}
			echo "</td></tr>";
			
			echo '<tr><td colspan="2" style="padding:4px">';
			if (function_exists('symposium_members')) {
				echo '<a href="'.symposium_get_url('members').'">'.__('Go to Member Directory', 'wp-symposium').'</a>';
			} else {
				echo __('Member Directory not activated', 'wp-symposium');
			}
			echo "</td></tr>";
			
			echo '<tr><td colspan="2" style="padding:4px">';
			if (function_exists('symposium_groups')) {
				echo '<a href="'.symposium_get_url('groups').'">'.__('Go to Group Directory', 'wp-symposium').'</a><br />';
			} else {
				echo __('Groups not activated', 'wp-symposium');
			}
			echo "</td></tr>";
			
		echo "</table>";

	echo "</td></tr></table>";

	if (get_option('symposium_motd')) {
		echo '<a id="show_motd" href="javascript:void(0)">'.__('Show the WP Symposium welcome message', 'wp-symposium').'</a>';
	}	
}

function symposium_deactivate() {

	wp_clear_scheduled_hook('symposium_notification_hook');
	delete_option('symposium_debug_mode');

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
	$sql = "SELECT user_id
			FROM ".$wpdb->base_prefix."usermeta m 
			LEFT JOIN ".$wpdb->base_prefix."users u 
			ON m.user_id = u.ID 
			WHERE u.ID IS NULL;";
			
	$missing_users = $wpdb->get_results($sql); 
	if ($missing_users) {
		foreach ($missing_users as $missing) {
			$sql = "DELETE FROM ".$wpdb->base_prefix."usermeta WHERE user_id = %d";
			$wpdb->query($wpdb->prepare($sql, $missing->uid)); 
			$sql = "DELETE FROM ".$wpdb->base_prefix."symposium_friends WHERE friend_from = %d or friend_to = %d";
			$wpdb->query($wpdb->prepare($sql, $missing->uid, $missing->uid)); 
			$sql = "DELETE FROM ".$wpdb->base_prefix."symposium_group_members WHERE member_id = %d";
			$wpdb->query($wpdb->prepare($sql, $missing->uid)); 			
		}
	}	
    
	// Clear Chat Windows (tidy up anyone who didn't close a chat window)
	$wpdb->query("DELETE FROM ".$wpdb->base_prefix."symposium_chat");
	
	// Add to summary report
	$summary_email .= __("Database cleanup", "wp-symposium").": completed<br />";
	$users_sent_to_success = '';
	$users_sent_to_failed = '';
				
	// ******************************************* Daily Digest ******************************************
	$send_summary = get_option('symposium_send_summary');
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
			
			$users = $wpdb->get_results("SELECT DISTINCT user_email 
			FROM ".$wpdb->base_prefix.'users'." u 
			INNER JOIN ".$wpdb->base_prefix."usermeta m ON u.ID = m.user_id 
			WHERE meta_key = 'forum_digest' and m.meta_value = 'on'"); 
			
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
			$id = $wpdb->get_var("select ID from ".$wpdb->base_prefix."users where user_email = '".$id_or_email."'");
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
		if (get_option('symposium_img_db') == "on") {
		
			$profile_photo = get_symposium_meta($id, 'profile_avatar');
			$profile_avatars = get_option('symposium_profile_avatars');
		
			if ($profile_photo == '' || $profile_photo == 'upload_failed' || $profile_avatars != 'on') {
				$return .= apply_filters('get_avatar', $avatar, $id_or_email, $size, $default, $alt);
			} else {
				$return .= "<img src='".WP_CONTENT_URL."/plugins/wp-symposium/uploadify/get_profile_avatar.php?uid=".$id."' style='width:".$size."px; height:".$size."px' class='avatar avatar-".$size." photo' />";
			}
			
		} else {

			$profile_photo = get_symposium_meta($id, 'profile_photo');
			$profile_avatars = get_option('symposium_profile_avatars');

			if ($profile_photo == '' || $profile_photo == 'upload_failed' || $profile_avatars != 'on') {
				$return .= apply_filters('get_avatar', $avatar, $id_or_email, $size, $default, $alt);
			} else {
				$img_url = get_option('symposium_img_url')."/members/".$id."/profile/";	
				$img_src = str_replace('//','/',$img_url) . $profile_photo;
				$return .= "<img src='".$img_src."' style='width:".$size."px; height:".$size."px' class='avatar avatar-".$size." photo' />";
			}
			
		}
		
		if (!get_option('symposium_wps_use_gravatar') && strpos($return, 'gravatar')) {
			$return = "<img src='".get_option('symposium_images')."/unknown.jpg' style='width:".$size."px; height:".$size."px' class='avatar avatar-".$size." photo' />";
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
			if (get_option('symposium_wps_show_hoverbox') == 'on') {
				if ($id != '') {
					$display_name = str_replace("'", "&apos;", $wpdb->get_var("select display_name from ".$wpdb->base_prefix."users where ID = '".$id."'"));
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
function symposium_buffer($buffer){ // $buffer contains entire page

	if (!get_option('symposium_wps_lite') && !strpos($buffer, "<rss") ) {

		global $wpdb;
		
		if (get_option('symposium_emoticons') == "on") {
			
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
						$buffer = $first_bit."<img src='".$smileys.strip_tags($bit).".png' alt='emoticon'/>".$last_bit;
					}
				}
			} while ($i < 100 && strpos($buffer, "{{")>0);
			
		}
			
		if (get_option('symposium_tags') == "on") {

			// User tagging			
		
			$profile = symposium_get_url('profile').symposium_string_query($profile_url).'uid=';
			$needles = array();
			for($i=0;$i<=47;$i++){ array_push($needles, chr($i)); }
			for($i=58;$i<=63;$i++){ array_push($needles, chr($i)); }
			for($i=91;$i<=96;$i++){ array_push($needles, chr($i)); }
			
			$i = 0;
			do {
				$i++;
				$start = strpos($buffer, "@");
				if ($start === false) {
				} else {
					$end = symposium_strpos($buffer, $needles, $start);
					if ($end === false) $end = strlen($buffer);
					$first_bit = substr($buffer, 0, $start);
					$last_bit = substr($buffer, $end, strlen($buffer)-$end+2);
					$bit = substr($buffer, $start+1, $end-$start-1);
					$sql = 'SELECT ID FROM '.$wpdb->base_prefix.'users WHERE replace(display_name, " ", "") = %s LIMIT 0,1';
					$id = $wpdb->get_var($wpdb->prepare($sql, $bit));
					if ($id) {
						$buffer = $first_bit.'<a href="'.$profile.$id.'" class="symposium_usertag">&#64;'.$bit.'</a>'.$last_bit;
					} else {
						$sql = 'SELECT ID FROM '.$wpdb->base_prefix.'users WHERE user_login = %s LIMIT 0,1';
						$id = $wpdb->get_var($wpdb->prepare($sql, $bit));
						if ($id) {
							$buffer = $first_bit.'<a href="'.$profile.$id.'" class="symposium_usertag">&#64;'.$bit.'</a>'.$last_bit;
						} else {
							$buffer = $first_bit.'&#64;'.$bit.$last_bit;
						}
					}
				}
			} while ($i < 100 && strpos($buffer, "@"));		
		}
		
	}

	return $buffer;
	
;
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

// Add jQuery and jQuery scripts
function js_init() {

	global $wpdb;
		
	$plugin = WP_CONTENT_URL.'/plugins/wp-symposium';

	// Only load if chosen
	if (!is_admin()) {

		if (get_option('symposium_jquery') == "on") {
			wp_enqueue_script('jquery');	 		
		}

		if (get_option('symposium_jqueryui') == "on") {
			wp_enqueue_script('jquery-ui-custom', $plugin.'/js/jquery-ui-1.8.11.custom.min.js', array('jquery'));	
		    wp_register_style('symposium_jquery-ui-css', WP_PLUGIN_URL.'/wp-symposium/css/jquery-ui-1.8.11.custom.css');
			wp_enqueue_style('symposium_jquery-ui-css');
		}	

	 	if (get_option('symposium_use_wysiwyg') == "on" || function_exists('symposium_events_main')) {
	 		wp_enqueue_script('wps-tinymce', $plugin.'/tiny_mce/tiny_mce.js', array('jquery'));	
	 	}

		wp_enqueue_script('plupload-all');
	}
	
}

// Perform admin duties, such as add jQuery and jQuery scripts and other admin jobs
function symposium_admin_init() {
	if (is_admin()) {
		// WordPress color picker
		wp_enqueue_style( 'farbtastic' );
	    wp_enqueue_script( 'farbtastic' );
    	
	}
}

// Add Symposium JS scripts to WordPress for use and other preparatory stuff
function symposium_scriptsAction() {

	$symposium_plugin_url = WP_PLUGIN_URL.'/wp-symposium/';
	$symposium_plugin_path = str_replace("http://".$_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"], "", $symposium_plugin_url);
 
	global $wpdb, $current_user;
	wp_get_current_user();

	// Set script timeout
	if (get_option('symposium_wps_time_out') > 0) {
		set_time_limit(get_option('symposium_wps_time_out'));
	}

	// Debug mode?
	define('WPS_DEBUG', get_option('symposium_debug_mode'));


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
				$get_uid = $wpdb->get_var("SELECT u.ID FROM ".$wpdb->base_prefix."users u WHERE (u.display_name LIKE '".$search."%') OR (u.display_name LIKE '% %".$search."%') ORDER BY u.display_name LIMIT 0,1");
				if ($get_uid) { $page_uid = $get_uid; }
			} 
		}
		define('WPS_CURRENT_USER_PAGE', $page_uid);

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
		
		// Get styles for JS
		if (get_option('symposium_use_styles') == "on") {
			$bg_color_2 = get_option('symposium_bg_color_2');
			$row_border_size = get_option('symposium_row_border_size');
			$row_border_style = get_option('symposium_row_border_style');
			$text_color_2 = get_option('symposium_text_color_2');
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
		$permitted_ext = get_option('symposium_image_ext').','.get_option('symposium_video_ext').','.get_option('symposium_doc_ext');
				
		// Load Symposium JS supporting scripts		
		wp_enqueue_script('jquery-uploadify', WP_PLUGIN_URL.'/wp-symposium/uploadify/jquery.uploadify.v2.1.4.js', array('jquery'));		

		// Load Symposium JS
	 	wp_enqueue_script('symposium', $symposium_plugin_url.'js/'.get_option('symposium_wps_js_file'), array('jquery'));
	
	 	// Load JScharts?
	 	if (get_option('symposium_jscharts')) {
	 	    if (get_option('symposium_wps_js_file') == 'wps.js') {
			 	wp_enqueue_script('wps_jscharts', $symposium_plugin_url.'js/jscharts.js', array('jquery'));
	 	    } else {
			 	wp_enqueue_script('wps_jscharts', $symposium_plugin_url.'js/jscharts.min.js', array('jquery'));
	 	    }
	 	}
	 	
	 	// Use WP editor? (not for use yet!!!!)
	 	update_option('symposium_use_wp_editor', false);

		// Set JS variables
		wp_localize_script( 'symposium', 'symposium', array(
			'permalink' => get_permalink(),
			'plugins' => WP_PLUGIN_URL, 
			'plugin_url' => WP_PLUGIN_URL.'/wp-symposium/', 
			'plugin_path' => $symposium_plugin_path,
			'plugin_pro_url' => WP_PLUGIN_URL.'/wp-symposium-', 
			'images_url' => get_option('symposium_images'),
			'inactive' => get_option('symposium_online'),
			'forum_url' => symposium_get_url('forum'),
			'mail_url' => symposium_get_url('mail'),
			'profile_url' => symposium_get_url('profile'),
			'groups_url' => symposium_get_url('groups'),
			'group_url' => symposium_get_url('group'),
			'gallery_url' => get_option('symposium_gallery_url'),
			'page_gid' => $page_gid,
			'offline' => get_option('symposium_offline'),
			'use_chat' => get_option('symposium_use_chat'),
			'chat_polling' => get_option('symposium_chat_polling'),
			'bar_polling' => get_option('symposium_bar_polling'),
			'view' => $view,
			'profile_default' => get_option('symposium_wps_profile_default'),
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
			'bg_color_2' => $bg_color_2,
			'row_border_size' => $row_border_size,
			'row_border_style' => $row_border_style,
			'text_color_2' => $text_color_2,
			'template_mail_tray' => get_option('symposium_template_mail_tray'),
			'embed' => $embed,
			'to' => $to,
			'is_admin' => 0,
			'mail_id' => $mid,
			'permitted_ext' => $permitted_ext,
			'forum_ajax' => get_option('symposium_forum_ajax'),
			'wps_lite' => get_option('symposium_wps_lite'),
			'wps_use_poke' => get_option('symposium_use_poke'),
			'wps_forum_stars' => get_option('symposium_forum_stars'),
			'wps_forum_refresh' => get_option('symposium_forum_refresh'),
			'wps_wysiwyg' => get_option('symposium_use_wysiwyg'),
			'wps_wysiwyg_1' => get_option('symposium_use_wysiwyg_1'),
			'wps_wysiwyg_2' => get_option('symposium_use_wysiwyg_2'),
			'wps_wysiwyg_3' => get_option('symposium_use_wysiwyg_3'),
			'wps_wysiwyg_4' => get_option('symposium_use_wysiwyg_4'),
			'wps_wysiwyg_css' => get_option('symposium_use_wysiwyg_css'),
			'wps_wysiwyg_skin' => get_option('symposium_use_wysiwyg_skin'),
			'wps_wysiwyg_width' => get_option('symposium_use_wysiwyg_width'),
			'wps_wysiwyg_height' => get_option('symposium_use_wysiwyg_height'),
			'wps_plus' => (defined('WPS_PLUS')) ? WPS_PLUS : '',
			'wps_admin_page' => 'na',
			'dir_page_length' => get_option('symposium_dir_page_length'),
			'dir_full_ver' => get_option('symposium_dir_full_ver') ? true : false,
			'use_elastic' => get_option('symposium_elastic'),
			'events_user_places' => get_option('symposium_events_user_places'),
			'events_use_wysiwyg' => get_option('symposium_events_use_wysiwyg'),
			'debug' => WPS_DEBUG,
			'include_context' => get_option('symposium_include_context'),
			'use_wp_editor' => get_option('symposium_use_wp_editor')
		));

	} else {
		
		// ADMIN JS load

		wp_enqueue_script('jquery-uploadify', WP_PLUGIN_URL.'/wp-symposium/uploadify/jquery.uploadify.v2.1.4.js', array('jquery'));

		// Load Symposium JS
	 	wp_enqueue_script('symposium', $symposium_plugin_url.'js/'.get_option('symposium_wps_js_file'), array('jquery'));

		// Set JS variables
		wp_localize_script( 'symposium', 'symposium', array(
			'plugins' => WP_PLUGIN_URL, 
			'plugin_url' => WP_PLUGIN_URL.'/wp-symposium/', 
			'plugin_path' => $symposium_plugin_path,
			'plugin_pro_url' => WP_PLUGIN_URL.'/wp-symposium-', 
			'images_url' => get_option('symposium_images'),
			'inactive' => get_option('symposium_online'),
			'forum_url' => get_option('symposium_forum_url'),
			'mail_url' => get_option('symposium_mail_url'),
			'profile_url' => get_option('symposium_profile_url'),
			'groups_url' => get_option('symposium_groups_url'),
			'group_url' => get_option('symposium_group_url'),
			'gallery_url' => get_option('symposium_gallery_url'),
			'offline' => get_option('symposium_offline'),
			'use_chat' => get_option('symposium_use_chat'),
			'chat_polling' => get_option('symposium_chat_polling'),
			'bar_polling' => get_option('symposium_bar_polling'),
			'current_user_id' => $current_user->ID,
			'is_admin' => 1,
			'wps_admin_page' => 'symposium_debug'
			
		));
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
	    $myStyleUrl = WP_PLUGIN_URL . '/wp-symposium/css/'.get_option('symposium_wps_css_file');
	    $myStyleFile = WP_PLUGIN_DIR . '/wp-symposium/css/'.get_option('symposium_wps_css_file');
	    if ( file_exists($myStyleFile) ) {
	        wp_register_style('symposium_StyleSheet', $myStyleUrl);
	        wp_enqueue_style('symposium_StyleSheet');
	    }

		// Notices
		include_once('dialogs.php');
			
	}



}

// Language files
function symposium_languages() {

    if ( file_exists(dirname(__FILE__).'/languages/') ) {
        load_plugin_textdomain('wp-symposium', false, dirname(plugin_basename(__FILE__)).'/languages/');
    } else {
        if ( file_exists(dirname(__FILE__).'/lang/') ) {
            load_plugin_textdomain('wp-symposium', false, dirname(plugin_basename(__FILE__)).'/lang/');
        } else {
            load_plugin_textdomain( 'wp-symposium' );
        }
    }

}


?>
