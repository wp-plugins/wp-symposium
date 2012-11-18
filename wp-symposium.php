<?php
/*
Plugin Name: WP Symposium A Social Network For WordPress
Plugin URI: http://www.wpsymposium.com
Description: <strong>Core Plugin code for WP Symposium, this plugin must always be activated, before activating other WP Symposium plugins.</strong>
Version: 12.11
Author: Simon Goodchild
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

// Get constants
require_once(dirname(__FILE__).'/default-constants.php');
include_once(dirname(__FILE__).'/functions.php');
include_once(dirname(__FILE__).'/hooks_filters.php');

global $wpdb, $current_user;

// Set version
define('WPS_VER', '12.11');

// Actions that are loaded before WordPress can check on page content
add_action('init', '__wps__scriptsAction');
add_action('init', '__wps__languages');
add_action('init', '__wps__js_init');

// Front end actions (includes check if required)
add_action('wp_head', '__wps__header', 10);
add_action('wp_footer', '__wps__concealed_avatar', 10);
add_action('template_redirect', '__wps__replace');
add_action('wp_print_styles', '__wps__add_stylesheet');

// Following required whether features on the page or not
add_action('wp_login', '__wps__login');
add_action('init', '__wps__notification_setoptions');
add_action('wp_footer', '__wps__lastactivity', 10);

// ----------------------------------------------------------------------------------------------------------------------------------------------------------

// Used in WordPress admin
if (is_admin()) {
	include(dirname(__FILE__).'/menu.php');
	add_filter('admin_footer_text', '__wps__footer_admin');
	add_action('admin_notices', '__wps__admin_warnings');
	if (!WPS_HIDE_DASHBOARAD_W) add_action('wp_dashboard_setup', '__wps__dashboard_widget');	
	add_action('init', '__wps__admin_init');
	// deactivation
	register_deactivation_hook(__FILE__, '__wps__deactivate');

}

/* ===================================================== ADMIN ====================================================== */	

// Check for updates
if ( ( get_option(WPS_OPTIONS_PREFIX."_version") != WPS_VER && is_admin()) || (isset($_GET['force_create_wps']) && $_GET['force_create_wps'] == 'yes' && is_admin())) {

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

	// Add new core wp_options if don't yet exist
	if (get_option(WPS_OPTIONS_PREFIX.'_categories_background') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_categories_background', '#0072bc'); 
	if (get_option(WPS_OPTIONS_PREFIX.'_categories_color') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_categories_color', '#fff'); 
	if (get_option(WPS_OPTIONS_PREFIX.'_bigbutton_background') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_bigbutton_background', '#0072bc'); 
	if (get_option(WPS_OPTIONS_PREFIX.'_bigbutton_color') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_bigbutton_color', '#fff'); 
	if (get_option(WPS_OPTIONS_PREFIX.'_bigbutton_background_hover') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_bigbutton_background_hover', '#00aeef');
	if (get_option(WPS_OPTIONS_PREFIX.'_bigbutton_color_hover') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_bigbutton_color_hover', '#fff'); 
	if (get_option(WPS_OPTIONS_PREFIX.'_bg_color_1') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_bg_color_1', '#0072bc'); 
	if (get_option(WPS_OPTIONS_PREFIX.'_bg_color_2') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_bg_color_2', '#ebebeb');
	if (get_option(WPS_OPTIONS_PREFIX.'_bg_color_3') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_bg_color_3', '#fff'); 
	if (get_option(WPS_OPTIONS_PREFIX.'_text_color') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_text_color', '#000'); 
	if (get_option(WPS_OPTIONS_PREFIX.'_table_rollover') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_table_rollover', '#fbaf5a'); 
	if (get_option(WPS_OPTIONS_PREFIX.'_link') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_link', '#0054a5'); 
	if (get_option(WPS_OPTIONS_PREFIX.'_link_hover') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_link_hover', '#000'); 
	if (get_option(WPS_OPTIONS_PREFIX.'_table_border') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_table_border', 2); 
	if (get_option(WPS_OPTIONS_PREFIX.'_replies_border_size') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_replies_border_size', 1); 
	if (get_option(WPS_OPTIONS_PREFIX.'_text_color_2') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_text_color_2', '#0054a5'); 
	if (get_option(WPS_OPTIONS_PREFIX.'_row_border_style') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_row_border_style', 'dotted'); 
	if (get_option(WPS_OPTIONS_PREFIX.'_row_border_size') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_row_border_size', 1); 
	if (get_option(WPS_OPTIONS_PREFIX.'_border_radius') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_border_radius', 2);
	if (get_option(WPS_OPTIONS_PREFIX.'_label') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_label', '#000');
	if (get_option(WPS_OPTIONS_PREFIX.'_footer') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_footer', __('Please don\'t reply to this email', "wp-symposium"));
	if (get_option(WPS_OPTIONS_PREFIX.'_send_summary') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_send_summary', 'on');
	if (get_option(WPS_OPTIONS_PREFIX.'_forum_url') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_forum_url', __('Important: Please Visit Installation Page!', "wp-symposium"));	 			  
	if (get_option(WPS_OPTIONS_PREFIX.'_from_email') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_from_email', 'noreply@example.com');
	if (get_option(WPS_OPTIONS_PREFIX.'_underline') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_underline', 'on');
	if (get_option(WPS_OPTIONS_PREFIX.'_preview1') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_preview1', 45);
	if (get_option(WPS_OPTIONS_PREFIX.'_preview2') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_preview2', 90);
	if (get_option(WPS_OPTIONS_PREFIX.'_include_admin') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_include_admin', 'on');
	if (get_option(WPS_OPTIONS_PREFIX.'_oldest_first') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_oldest_first', 'on');
	if (get_option(WPS_OPTIONS_PREFIX.'_wp_width') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_wp_width', '100%');
	if (get_option(WPS_OPTIONS_PREFIX.'_main_background') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_main_background', '#fff');
	if (get_option(WPS_OPTIONS_PREFIX.'_closed_opacity') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_closed_opacity', '1.0');
	if (get_option(WPS_OPTIONS_PREFIX.'_closed_word') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_closed_word', __('closed', "wp-symposium"));
	if (get_option(WPS_OPTIONS_PREFIX.'_fontfamily') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_fontfamily', 'Georgia,Times');
	if (get_option(WPS_OPTIONS_PREFIX.'_headingsfamily') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_headingsfamily', 'Arial,Helvetica');
	if (get_option(WPS_OPTIONS_PREFIX.'_fontsize') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_fontsize', 13);
	if (get_option(WPS_OPTIONS_PREFIX.'_headingssize') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_headingssize', 20);
	if (get_option(WPS_OPTIONS_PREFIX.'_jquery') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_jquery', 'on');
	if (get_option(WPS_OPTIONS_PREFIX.'_jqueryui') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_jqueryui', 'on');
	if (get_option(WPS_OPTIONS_PREFIX.'_emoticons') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_emoticons', 'on');
	if (get_option(WPS_OPTIONS_PREFIX.'_moderation') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_moderation', '');
	if (get_option(WPS_OPTIONS_PREFIX.'_mail_url') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_mail_url', __('Important: Please Visit Installation Page!', "wp-symposium"));
	if (get_option(WPS_OPTIONS_PREFIX.'_online') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_online', 3);
	if (get_option(WPS_OPTIONS_PREFIX.'_offline') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_offline', 15);
	if (get_option(WPS_OPTIONS_PREFIX.'_wp_alignment') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_wp_alignment', 'Center');
	if (get_option(WPS_OPTIONS_PREFIX.'_enable_password') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_enable_password', 'on');
	if (get_option(WPS_OPTIONS_PREFIX.'_use_wp_profile') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_use_wp_profile', '');
	if (get_option(WPS_OPTIONS_PREFIX.'_members_url') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_members_url', __('Important: Please Visit Installation Page!', "wp-symposium"));
	if (get_option(WPS_OPTIONS_PREFIX.'_sharing') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_sharing', '');
	if (get_option(WPS_OPTIONS_PREFIX.'_use_styles') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_use_styles', '');
	if (get_option(WPS_OPTIONS_PREFIX.'_show_wall_extras') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_show_wall_extras', '');
	if (get_option(WPS_OPTIONS_PREFIX.'_use_chat') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_use_chat', 'on');
	if (get_option(WPS_OPTIONS_PREFIX.'_bar_polling') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_bar_polling', 120);
	if (get_option(WPS_OPTIONS_PREFIX.'_chat_polling') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_chat_polling', 10);
	if (get_option(WPS_OPTIONS_PREFIX.'_use_chatroom') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_use_chatroom', 'on');
	if (get_option(WPS_OPTIONS_PREFIX.'_chatroom_banned') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_chatroom_banned', '');
	if (get_option(WPS_OPTIONS_PREFIX.'_profile_google_map') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_profile_google_map', 150);
	if (get_option(WPS_OPTIONS_PREFIX.'_use_poke') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_use_poke', 'on');
	if (get_option(WPS_OPTIONS_PREFIX.'_poke_label') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_poke_label', 'Hey!');
	if (get_option(WPS_OPTIONS_PREFIX.'_motd') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_motd', '');
	if (get_option(WPS_OPTIONS_PREFIX.'_profile_url') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_profile_url', __('Important: Please Visit Installation Page!', "wp-symposium"));
	if (get_option(WPS_OPTIONS_PREFIX.'_groups_url') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_groups_url', __('Important: Please Visit Installation Page!', "wp-symposium"));
	if (get_option(WPS_OPTIONS_PREFIX.'_group_url') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_group_url', __('Important: Please Visit Installation Page!', "wp-symposium"));
	if (get_option(WPS_OPTIONS_PREFIX.'_group_all_create') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_group_all_create', 'on');
	if (get_option(WPS_OPTIONS_PREFIX.'_group_invites') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_group_invites', 'on');
	if (get_option(WPS_OPTIONS_PREFIX.'_group_invites_max') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_group_invites_max', 10);
	if (get_option(WPS_OPTIONS_PREFIX.'_profile_avatars') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_profile_avatars', 'on');
	if (get_option(WPS_OPTIONS_PREFIX.'_img_db') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_img_db', '');	
	if (get_option(WPS_OPTIONS_PREFIX.'_img_path') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_img_path', WP_CONTENT_DIR.'/wps-content');

	$img_url = WP_CONTENT_URL."/wps-content";
	$img_url = str_replace(__wps__siteURL(), '', $img_url); 
	if (get_option(WPS_OPTIONS_PREFIX.'_img_url') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_img_url', $img_url);
	if (get_option(WPS_OPTIONS_PREFIX.'_img_upload') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_img_upload', '');
	if (get_option(WPS_OPTIONS_PREFIX.'_img_crop') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_img_crop', 'on');
	if (get_option(WPS_OPTIONS_PREFIX.'_forum_ranks') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_forum_ranks', '');
	if (get_option(WPS_OPTIONS_PREFIX.'_forum_ajax') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_forum_ajax', '');
	if (get_option(WPS_OPTIONS_PREFIX.'_forum_login') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_forum_login', 'on');
	if (get_option(WPS_OPTIONS_PREFIX.'_initial_friend') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_initial_friend', '');
	if (get_option(WPS_OPTIONS_PREFIX.'_initial_groups') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_initial_groups', '');
	if (get_option(WPS_OPTIONS_PREFIX.'_template_profile_header') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_template_profile_header', '');
	if (get_option(WPS_OPTIONS_PREFIX.'_template_profile_body') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_template_profile_body', '');
	if (get_option(WPS_OPTIONS_PREFIX.'_template_page_footer') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_template_page_footer', '');
	if (get_option(WPS_OPTIONS_PREFIX.'_template_email') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_template_email', '');
	if (get_option(WPS_OPTIONS_PREFIX.'_template_forum_header') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_template_forum_header', '');
	if (get_option(WPS_OPTIONS_PREFIX.'_template_forum_category') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_template_forum_category', '');
	if (get_option(WPS_OPTIONS_PREFIX.'_template_forum_topic') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_template_forum_topic', '');
	if (get_option(WPS_OPTIONS_PREFIX.'_template_mail_tray') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_template_mail_tray', '');
	if (get_option(WPS_OPTIONS_PREFIX.'_template_mail_message') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_template_mail_message', '');
	if (get_option(WPS_OPTIONS_PREFIX.'_template_group') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_template_group', '');
	if (get_option(WPS_OPTIONS_PREFIX.'_template_group_forum_category') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_template_group_forum_category', '');
	if (get_option(WPS_OPTIONS_PREFIX.'_template_group_forum_topic') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_template_group_forum_topic', '');
	if (get_option(WPS_OPTIONS_PREFIX.'_facebook_api') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_facebook_api', '');
	if (get_option(WPS_OPTIONS_PREFIX.'_facebook_secret') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_facebook_secret', '');
	if (get_option(WPS_OPTIONS_PREFIX.'_css') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_css', '');
	if (get_option(WPS_OPTIONS_PREFIX.'_mobile_topics') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_mobile_topics', 20);
	if (get_option(WPS_OPTIONS_PREFIX.'_bump_topics') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_bump_topics', 'on');
	if (get_option(WPS_OPTIONS_PREFIX.'_show_dob') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_show_dob', 'on');
	if (get_option(WPS_OPTIONS_PREFIX.'_use_votes') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_use_votes', 'on');
	if (get_option(WPS_OPTIONS_PREFIX.'_use_votes_remove') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_use_votes_remove', 0);
	if (get_option(WPS_OPTIONS_PREFIX.'_show_buttons') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_show_buttons', '');
	if (get_option(WPS_OPTIONS_PREFIX.'_show_admin') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_show_admin', 'on');
	if (get_option(WPS_OPTIONS_PREFIX.'_forumlatestposts_count') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_forumlatestposts_count', 100);
	if (get_option(WPS_OPTIONS_PREFIX.'_redirect_wp_profile') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_redirect_wp_profile', 'on');
	if (get_option(WPS_OPTIONS_PREFIX.'_striptags') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_striptags', '');
	if (get_option(WPS_OPTIONS_PREFIX.'_forum_uploads') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_forum_uploads', '');
	if (get_option(WPS_OPTIONS_PREFIX.'_forum_thumbs') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_forum_thumbs', '');
	if (get_option(WPS_OPTIONS_PREFIX.'_forum_thumbs_size') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_forum_thumbs_size', 400);
	if (get_option(WPS_OPTIONS_PREFIX.'_forum_info') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_forum_info', 'on');
	if (get_option(WPS_OPTIONS_PREFIX.'_forum_stars') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_forum_stars', 'on');
	if (get_option(WPS_OPTIONS_PREFIX.'_use_votes_min') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_use_votes_min', 10);
	if (get_option(WPS_OPTIONS_PREFIX.'_use_answers') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_use_answers', '');
	if (get_option(WPS_OPTIONS_PREFIX.'_image_ext') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_image_ext', '*.jpg,*.gif,*.png,*.jpeg');
	if (get_option(WPS_OPTIONS_PREFIX.'_video_ext') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_video_ext', '*.mp4');
	if (get_option(WPS_OPTIONS_PREFIX.'_doc_ext') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_doc_ext', '*.pdf,*.txt,*.zip');

	if (get_option(WPS_OPTIONS_PREFIX.'_menu_profile') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_menu_profile', 'on');
	if (get_option(WPS_OPTIONS_PREFIX.'_menu_my_activity') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_menu_my_activity', 'on');
	if (get_option(WPS_OPTIONS_PREFIX.'_menu_friends_activity') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_menu_friends_activity', 'on');
	if (get_option(WPS_OPTIONS_PREFIX.'_menu_all_activity') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_menu_all_activity', 'on');
	if (get_option(WPS_OPTIONS_PREFIX.'_menu_friends') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_menu_friends', 'on');
	if (get_option(WPS_OPTIONS_PREFIX.'_menu_profile_other') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_menu_profile_other', 'on');
	if (get_option(WPS_OPTIONS_PREFIX.'_menu_my_activity_other') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_menu_my_activity_other', 'on');
	if (get_option(WPS_OPTIONS_PREFIX.'_menu_friends_activity_other') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_menu_friends_activity_other', 'on');
	if (get_option(WPS_OPTIONS_PREFIX.'_menu_all_activity_other') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_menu_all_activity_other', 'on');
	if (get_option(WPS_OPTIONS_PREFIX.'_menu_friends_other') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_menu_friends_other', 'on');		

	if (get_option(WPS_OPTIONS_PREFIX.'_menu_texthtml') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_menu_texthtml', '');
	if (get_option(WPS_OPTIONS_PREFIX.'_mail_all') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_mail_all', '');
	if (get_option(WPS_OPTIONS_PREFIX.'_elastic') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_elastic', 'on');
	if (get_option(WPS_OPTIONS_PREFIX.'_profile_show_unchecked') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_profile_show_unchecked', 'on');
	$images = WPS_PLUGIN_URL."/images";
	$images = str_replace(__wps__siteURL(), '', $images); 
	if (get_option(WPS_OPTIONS_PREFIX.'_images') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_images', $images);
	if (get_option(WPS_OPTIONS_PREFIX.'_show_dir_buttons') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_show_dir_buttons', 'on');
	if (get_option(WPS_OPTIONS_PREFIX.'_dir_page_length') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_dir_page_length', 25);
	if (get_option(WPS_OPTIONS_PREFIX.'_wps_lite') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_wps_lite', '');
	if (get_option(WPS_OPTIONS_PREFIX.'_wps_profile_default') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_wps_profile_default', 'activity');
	if (get_option(WPS_OPTIONS_PREFIX.'_wps_panel_all') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_wps_panel_all', '');
	if (get_option(WPS_OPTIONS_PREFIX.'_wps_default_forum') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_wps_default_forum', '');
	if (get_option(WPS_OPTIONS_PREFIX.'_wps_use_gravatar') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_wps_use_gravatar', 'on');
	if (get_option(WPS_OPTIONS_PREFIX.'_wps_time_out') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_wps_time_out', 0);
	if (get_option(WPS_OPTIONS_PREFIX.'_wps_js_file') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_wps_js_file', 'wps.min.js');
	if (get_option(WPS_OPTIONS_PREFIX.'_wps_css_file') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_wps_css_file', 'wps.min.css');
	if (get_option(WPS_OPTIONS_PREFIX.'_allow_reports') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_allow_reports', '');
	if (get_option(WPS_OPTIONS_PREFIX.'_ajax_widgets') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_ajax_widgets', '');
	if (get_option(WPS_OPTIONS_PREFIX.'_status_label') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_status_label', __('What`s up?', "wp-symposium"));
	if (get_option(WPS_OPTIONS_PREFIX.'_jscharts') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_jscharts', 'on');
	if (get_option(WPS_OPTIONS_PREFIX.'_use_wysiwyg') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_use_wysiwyg', '');
	if (get_option(WPS_OPTIONS_PREFIX.'_use_wysiwyg_1') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_use_wysiwyg_1', 'bold,italic,|,fontselect,fontsizeselect,forecolor,backcolor,|,bullist,numlist,|,link,unlink,|,image,media,|,emotions');
	if (get_option(WPS_OPTIONS_PREFIX.'_use_wysiwyg_2') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_use_wysiwyg_2', '');
	if (get_option(WPS_OPTIONS_PREFIX.'_use_wysiwyg_3') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_use_wysiwyg_3', '');
	if (get_option(WPS_OPTIONS_PREFIX.'_use_wysiwyg_4') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_use_wysiwyg_4', '');
	if (get_option(WPS_OPTIONS_PREFIX.'_use_wysiwyg_css') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_use_wysiwyg_css', str_replace(__wps__siteURL(), '', WPS_PLUGIN_URL."/tiny_mce/themes/advanced/skins/wps.css"));
	if (get_option(WPS_OPTIONS_PREFIX.'_use_wysiwyg_skin') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_use_wysiwyg_skin', 'cirkuit');
	if (get_option(WPS_OPTIONS_PREFIX.'_use_wysiwyg_width') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_use_wysiwyg_width', 563);
	if (get_option(WPS_OPTIONS_PREFIX.'_use_wysiwyg_height') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_use_wysiwyg_height', 300);
	if (get_option(WPS_OPTIONS_PREFIX.'_forum_refresh') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_forum_refresh', '');
	if (get_option(WPS_OPTIONS_PREFIX.'_subject_mail_new') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_subject_mail_new', __('New Mail Message: [subject]', "wp-symposium"));
	if (get_option(WPS_OPTIONS_PREFIX.'_subject_forum_new') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_subject_forum_new', __('New Forum Topic', "wp-symposium"));
	if (get_option(WPS_OPTIONS_PREFIX.'_subject_forum_reply') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_subject_forum_reply', __('New Forum Reply', "wp-symposium"));
	if (get_option(WPS_OPTIONS_PREFIX.'_profile_comments') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_profile_comments', 'on');
	if (get_option(WPS_OPTIONS_PREFIX.'_forum_login_form') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_forum_login_form', 'on');
	if (get_option(WPS_OPTIONS_PREFIX.'_forum_lock') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_forum_lock', 30);
	if (get_option(WPS_OPTIONS_PREFIX.'_use_wysiwyg_3') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_use_wysiwyg_3', '');
	if (get_option(WPS_OPTIONS_PREFIX.'_dir_level') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_dir_level', 's:60:\"Everyone,Administrator,Editor,Author,Contributor,Subscriber,\";');		
	if (get_option(WPS_OPTIONS_PREFIX.'_viewer') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_viewer', 's:16:\"s:9:\"everyone,\";\";');
	if (get_option(WPS_OPTIONS_PREFIX.'_forum_editor') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_forum_editor', 's:59:\"s:51:\"Administrator,Editor,Author,Contributor,Subscriber,\";\";');
	if (get_option(WPS_OPTIONS_PREFIX.'_forum_reply') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_forum_reply', 's:59:\"s:51:\"Administrator,Editor,Author,Contributor,Subscriber,\";\";');
	if (get_option(WPS_OPTIONS_PREFIX.'_rewrite_forum_single') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_rewrite_forum_single', '');
	if (get_option(WPS_OPTIONS_PREFIX.'_rewrite_forum_single_target') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_rewrite_forum_single_target', '');
	if (get_option(WPS_OPTIONS_PREFIX.'_rewrite_forum_double') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_rewrite_forum_double', '');
	if (get_option(WPS_OPTIONS_PREFIX.'_rewrite_forum_double_target') === false)	
		update_option(WPS_OPTIONS_PREFIX.'_rewrite_forum_double_target', '');
	
	// Set default values for if not yet set
	if (get_option(WPS_OPTIONS_PREFIX.'_long_menu') === false) update_option(WPS_OPTIONS_PREFIX.'_long_menu', 'on');
	if (get_option(WPS_OPTIONS_PREFIX.'_alt_friend') === false) update_option(WPS_OPTIONS_PREFIX.'_alt_friend', 'Friend');
	if (get_option(WPS_OPTIONS_PREFIX.'_alt_friends') === false) update_option(WPS_OPTIONS_PREFIX.'_alt_friends', 'Friends');
	if (get_option(WPS_OPTIONS_PREFIX.'_dir_atoz_order') === false) update_option(WPS_OPTIONS_PREFIX.'_dir_atoz_order', 'last_activity');
	
	// Profile menu text
	if (!get_option(WPS_OPTIONS_PREFIX.'_menu_profile_text')) update_option(WPS_OPTIONS_PREFIX.'_menu_profile_text', __('My Profile', WPS_TEXT_DOMAIN));
	if (!get_option(WPS_OPTIONS_PREFIX.'_menu_my_activity_text')) update_option(WPS_OPTIONS_PREFIX.'_menu_my_activity_text', __('My Activity', WPS_TEXT_DOMAIN));
	if (!get_option(WPS_OPTIONS_PREFIX.'_menu_friends_activity_text')) update_option(WPS_OPTIONS_PREFIX.'_menu_friends_activity_text', __('Friends Activity', WPS_TEXT_DOMAIN));
	if (!get_option(WPS_OPTIONS_PREFIX.'_menu_all_activity_text')) update_option(WPS_OPTIONS_PREFIX.'_menu_all_activity_text', __('All Activity', WPS_TEXT_DOMAIN));
	if (!get_option(WPS_OPTIONS_PREFIX.'_menu_friends_text')) update_option(WPS_OPTIONS_PREFIX.'_menu_friends_text', __('My Friends', WPS_TEXT_DOMAIN));
	if (!get_option(WPS_OPTIONS_PREFIX.'_menu_mentions_text')) update_option(WPS_OPTIONS_PREFIX.'_menu_mentions_text', __('Forum @mentions', WPS_TEXT_DOMAIN));
	if (!get_option(WPS_OPTIONS_PREFIX.'_menu_groups_text')) update_option(WPS_OPTIONS_PREFIX.'_menu_groups_text', __('My Groups', WPS_TEXT_DOMAIN));
	if (!get_option(WPS_OPTIONS_PREFIX.'_menu_events_text')) update_option(WPS_OPTIONS_PREFIX.'_menu_events_text', __('My Events', WPS_TEXT_DOMAIN));
	if (!get_option(WPS_OPTIONS_PREFIX.'_menu_gallery_text')) update_option(WPS_OPTIONS_PREFIX.'_menu_gallery_text', __('My Gallery', WPS_TEXT_DOMAIN));
	if (!get_option(WPS_OPTIONS_PREFIX.'_menu_following_text')) update_option(WPS_OPTIONS_PREFIX.'_menu_following_text', __('I am Following', WPS_TEXT_DOMAIN));
	if (!get_option(WPS_OPTIONS_PREFIX.'_menu_followers_text')) update_option(WPS_OPTIONS_PREFIX.'_menu_followers_text', __('My Followers', WPS_TEXT_DOMAIN));
	if (!get_option(WPS_OPTIONS_PREFIX.'_menu_lounge_text')) update_option(WPS_OPTIONS_PREFIX.'_menu_lounge_text', __('The Lounge', WPS_TEXT_DOMAIN));
	if (!get_option(WPS_OPTIONS_PREFIX.'_menu_avatar_text')) update_option(WPS_OPTIONS_PREFIX.'_menu_avatar_text', __('Profile Photo', WPS_TEXT_DOMAIN));
	if (!get_option(WPS_OPTIONS_PREFIX.'_menu_details_text')) update_option(WPS_OPTIONS_PREFIX.'_menu_details_text', __('Profile Details', WPS_TEXT_DOMAIN));
	if (!get_option(WPS_OPTIONS_PREFIX.'_menu_settings_text')) update_option(WPS_OPTIONS_PREFIX.'_menu_settings_text', __('Community Settings', WPS_TEXT_DOMAIN));
	if (!get_option(WPS_OPTIONS_PREFIX.'_menu_profile_other_text')) update_option(WPS_OPTIONS_PREFIX.'_menu_profile_other_text', __('Profile', WPS_TEXT_DOMAIN));
	if (!get_option(WPS_OPTIONS_PREFIX.'_menu_my_activity_other_text')) update_option(WPS_OPTIONS_PREFIX.'_menu_my_activity_other_text', __('Activity', WPS_TEXT_DOMAIN));
	if (!get_option(WPS_OPTIONS_PREFIX.'_menu_friends_activity_other_text')) update_option(WPS_OPTIONS_PREFIX.'_menu_friends_activity_other_text', __('Friends Activity', WPS_TEXT_DOMAIN));
	if (!get_option(WPS_OPTIONS_PREFIX.'_menu_all_activity_other_text')) update_option(WPS_OPTIONS_PREFIX.'_menu_all_activity_other_text', __('All Activity', WPS_TEXT_DOMAIN));
	if (!get_option(WPS_OPTIONS_PREFIX.'_menu_friends_other_text')) update_option(WPS_OPTIONS_PREFIX.'_menu_friends_other_text', __('Friends', WPS_TEXT_DOMAIN));
	if (!get_option(WPS_OPTIONS_PREFIX.'_menu_mentions_other_text')) update_option(WPS_OPTIONS_PREFIX.'_menu_mentions_other_text', __('Forum @mentions', WPS_TEXT_DOMAIN));
	if (!get_option(WPS_OPTIONS_PREFIX.'_menu_groups_other_text')) update_option(WPS_OPTIONS_PREFIX.'_menu_groups_other_text', __('Groups', WPS_TEXT_DOMAIN));
	if (!get_option(WPS_OPTIONS_PREFIX.'_menu_events_other_text')) update_option(WPS_OPTIONS_PREFIX.'_menu_events_other_text', __('Events', WPS_TEXT_DOMAIN));
	if (!get_option(WPS_OPTIONS_PREFIX.'_menu_gallery_other_text')) update_option(WPS_OPTIONS_PREFIX.'_menu_gallery_other_text', __('Gallery', WPS_TEXT_DOMAIN));
	if (!get_option(WPS_OPTIONS_PREFIX.'_menu_following_other_text')) update_option(WPS_OPTIONS_PREFIX.'_menu_following_other_text', __('Following', WPS_TEXT_DOMAIN));
	if (!get_option(WPS_OPTIONS_PREFIX.'_menu_followers_other_text')) update_option(WPS_OPTIONS_PREFIX.'_menu_followers_other_text', __('Followers', WPS_TEXT_DOMAIN));
	if (!get_option(WPS_OPTIONS_PREFIX.'_menu_lounge_other_text')) update_option(WPS_OPTIONS_PREFIX.'_menu_lounge_other_text', __('The Lounge', WPS_TEXT_DOMAIN));

$default_menu_structure = '[Profile]
View Profile=viewprofile
Profile Details=details
Community Settings=settings
Upload Avatar=avatar
[Activity]
My Activity=activitymy
Friends Activity=activityfriends
All Activity=activityall
[Social]
My Friends=myfriends
My Groups=mygroups
The Lounge=lounge
My @mentions=mentions
Who I am Following=following
My Followers=followers
[More]
My Events=events
My Gallery=gallery';

$default_menu_structure_other = '[Profile]
View Profile=viewprofile
Profile Details=details
Community Settings=settings
Upload Avatar=avatar
[Activity]
Activity=activitymy
Friends Activity=activityfriends
All Activity=activityall
[Social]
Friends=myfriends
Groups=mygroups
The Lounge=lounge
@mentions=mentions
Following=following
Followers=followers
[More]
Events=events
Gallery=gallery';

	if (!get_option(WPS_OPTIONS_PREFIX.'_profile_menu_structure')) update_option(WPS_OPTIONS_PREFIX.'_profile_menu_structure', (isset($_POST['profile_menu_structure']) && $_POST['profile_menu_structure']) ? $_POST['profile_menu_structure'] : $default_menu_structure);
	if (!get_option(WPS_OPTIONS_PREFIX.'_profile_menu_structure_other')) update_option(WPS_OPTIONS_PREFIX.'_profile_menu_structure_other', (isset($_POST['profile_menu_structure_other']) && $_POST['profile_menu_structure_other']) ? $_POST['profile_menu_structure_other'] : $default_menu_structure_other);
			
	
	if (get_option(WPS_OPTIONS_PREFIX.'_template_profile_header') == '') {
		update_option(WPS_OPTIONS_PREFIX.'_template_profile_header', "<div id='profile_header_div'>[]<div id='profile_header_panel'>[]<div id='profile_details'>[]<div style='float:right'>[poke]</div>[]<div style='float:right'>[follow]</div>[]<div id='profile_name'>[display_name]</div>[]<div id='profile_label'>[profile_label]</div>[]<p>[location]<br />[born]</p>[]<div style='padding: 0px;'>[actions]</div>[]</div>[]</div>[]<div id='profile_photo' class='corners'>[avatar,170]</div>[]</div>");
	}
	if (get_option(WPS_OPTIONS_PREFIX.'_template_profile_body') == '') {
		update_option(WPS_OPTIONS_PREFIX.'_template_profile_body', "<div id='profile_wrapper'>[]<div id='force_profile_page' style='display:none'>[default]</div>[]<div id='profile_body_wrapper'>[]<div id='profile_body'>[page]</div>[]</div>[]<div id='profile_menu'>[menu]</div>[]</div>");
	}
	if (get_option(WPS_OPTIONS_PREFIX.'_template_page_footer') == '') {
		update_option(WPS_OPTIONS_PREFIX.'_template_page_footer', "<div id='powered_by_wps'>[]<a href='http://www.wpsymposium.com' target='_blank'>[powered_by_message] v[version]</a>[]</div>");
	}
	if (get_option(WPS_OPTIONS_PREFIX.'_template_mail_tray') == '') {
		update_option(WPS_OPTIONS_PREFIX.'_template_mail_tray', "<div id='mail_mid' class='mail_item mail_read'>[]<div class='mailbox_message_from'>[mail_from]</div>[]<div class='mail_item_age'>[mail_sent]</div>[]<div class='mailbox_message_subject'>[mail_subject]</div>[]<div class='mailbox_message'>[mail_message]</div>[]</div>");
	}
	if (get_option(WPS_OPTIONS_PREFIX.'_template_mail_message') == '') {
		update_option(WPS_OPTIONS_PREFIX.'_template_mail_message', "<div id='message_header'><div id='message_header_delete'>[reply_button][delete_button]</div><div id='message_header_avatar'>[avatar,44]</div>[mail_subject]<br />[mail_recipient] [mail_sent]</div></div><div id='message_mail_message'>[message]</div>");
	}
	if (get_option(WPS_OPTIONS_PREFIX.'_template_email') == '') {
		update_option(WPS_OPTIONS_PREFIX.'_template_email', "<style> body { background-color: #eee; } </style>[]<div style='margin: 20px; padding:20px; border-radius:10px; background-color: #fff;border:1px solid #000;'>[][message][]<br /><hr />[][footer]<br />[]<a href='http://www.wpsymposium.com' target='_blank'>[powered_by_message] v[version]</a>[]</div>");
	}
	if (get_option(WPS_OPTIONS_PREFIX.'_template_forum_header') == '') {
		update_option(WPS_OPTIONS_PREFIX.'_template_forum_header', "[breadcrumbs][new_topic_button][new_topic_form][][digest][subscribe][][forum_options][][sharing]");
	}
	if (get_option(WPS_OPTIONS_PREFIX.'_template_group') == '') {
		update_option(WPS_OPTIONS_PREFIX.'_template_group', "<div id='group_header_div'><div id='group_header_panel'>[]<div id='group_details'>[]<div id='group_name'>[group_name]</div>[]<div id='group_description'>[group_description]</div>[]<div style='padding-top: 15px;padding-bottom: 15px;'>[actions]</div>[]</div></div>[]<div id='group_photo' class='corners'>[avatar,170]</div>[]</div>[]<div id='group_wrapper'>[]<div id='force_group_page' style='display:none'>[default]</div>[]<div id='group_body_wrapper'>[]<div id='group_body'>[page]</div>[]</div>[]<div id='group_menu'>[menu]</div>[]</div>");
	}
	if (get_option(WPS_OPTIONS_PREFIX.'_template_forum_category') == '') {
		update_option(WPS_OPTIONS_PREFIX.'_template_forum_category', "<div class='row_startedby'>[]<div class='avatar avatar_last_topic'>[avatar,32]</div>[]<div class='last_topic_text'>[replied][subject][ago]</div>[]</div>[]<div class='row_views'>[post_count]</div>[]<div class='row_topic row_replies'>[topic_count]</div>[]<div class='row_topic'>[category_title]<br />[category_desc]</div>");
	}
	if (get_option(WPS_OPTIONS_PREFIX.'_template_forum_topic') == '') {
		update_option(WPS_OPTIONS_PREFIX.'_template_forum_topic', "<div class='row_startedby'>[]<div class='first_topic'>[]<div class='avatar avatar_first_topic'>[avatarfirst,32]</div>[]<div class='first_topic_text'>[startedby][started]</div>[]</div><div class='last_reply'>[]<div class='avatar avatar_last_topic'>[avatar,32]</div>[]<div class='last_topic_text'>[replied][topic][ago]</div>[]</div>[]</div>[]<div class='row_views'>[views]</div>[]<div class='row_replies'>[replies]</div>[]<div class='row_topic'>[topic_title]</div>");
	}
	if (get_option(WPS_OPTIONS_PREFIX.'_template_group_forum_category') == '') {
		update_option(WPS_OPTIONS_PREFIX.'_template_group_forum_category', "<div class='row_startedby'>[]<div class='avatar avatar_last_topic'>[avatar,32]</div>[replied][subject][ago]</div>[]<div class='row_topic'>[category_title]</div>");
	}
	if (get_option(WPS_OPTIONS_PREFIX.'_template_group_forum_topic') == '') {
		update_option(WPS_OPTIONS_PREFIX.'_template_group_forum_topic', "<div class='row_startedby'>[]<div class='first_topic'>[]<div class='avatar avatar_first_topic'>[avatarfirst,32]</div>[]<div class='first_topic_text'>[startedby][started]</div>[]</div><div class='last_reply'>[]<div class='avatar avatar_last_topic'>[avatar,32]</div>[]</div>[]<div class='last_topic_text'>[replied][topic][ago]</div>[]</div>[]<div class='row_topic'>[topic_title]</div>");
	}

	// Default forum ranks
	if (get_option(WPS_OPTIONS_PREFIX.'_forum_ranks') == '') {
		update_option(WPS_OPTIONS_PREFIX.'_forum_ranks', "on;Emperor;0;Monarch;200;Lord;150;Duke;125;Count;100;Earl;75;Viscount;50;Bishop;25;Baron;10;Knight;5;Peasant;0");
	}
	
	// Modify Mail table
	__wps__alter_table("mail", "MODIFY", "mail_sent", "datetime", "", "");

	// Modify Forum Categories table
	__wps__alter_table("cats", "ADD", "cat_parent", "int(11)", "NOT NULL", "0");
	__wps__alter_table("cats", "ADD", "cat_desc", "varchar(256)", "", "''");
	__wps__alter_table("cats", "ADD", "level", "varchar(256)", "", "'s:60:\"Everyone,Administrator,Editor,Author,Contributor,Subscriber,\";'");
	__wps__alter_table("cats", "ADD", "stub", "varchar(256)", "", "''");
	__wps__alter_table("cats", "ADD", "min_rank", "int(11)", "NOT NULL", "0");

	// Modify Comments table
	__wps__alter_table("comments", "MODIFY", "comment_timestamp", "datetime", "", "");
	__wps__alter_table("comments", "ADD", "is_group", "varchar(2)", "NOT NULL", "''");
	__wps__alter_table("comments", "ADD", "type", "varchar(16)", "NOT NULL", "'post'");
	__wps__alter_table("comments", "MODIFY", "comment", "text", "", "");
	
	// Modify Friends table
	__wps__alter_table("friends", "MODIFY", "friend_timestamp", "datetime", "", "");

	// Modify Chat table
	__wps__alter_table("chat", "ADD", "chat_room", "int(11)", "NOT NULL", "'0'");
	__wps__alter_table("chat", "MODIFY", "chat_timestamp", "datetime", "", "");

	// Modify Gallery items table
	__wps__alter_table("gallery_items", "ADD", "photo_order", "int(11)", "NOT NULL", "'0'");

	// Modify styles table
	__wps__alter_table("styles", "ADD", "underline", "varchar(2)", "NOT NULL", "'on'");
	__wps__alter_table("styles", "ADD", "main_background", "varchar(12)", "NOT NULL", "'#fff'");
	__wps__alter_table("styles", "ADD", "closed_opacity", "varchar(6)", "NOT NULL", "'1.0'");
	__wps__alter_table("styles", "ADD", "fontfamily", "varchar(128)", "NOT NULL", "'Georgia,Times'");
	__wps__alter_table("styles", "ADD", "fontsize", "varchar(8)", "NOT NULL", "'13'");
	__wps__alter_table("styles", "ADD", "headingsfamily", "varchar(128)", "NOT NULL", "'Georgia,Times'");
	__wps__alter_table("styles", "ADD", "headingssize", "varchar(8)", "NOT NULL", "'20'");
	
	// Modify topics table
	__wps__alter_table("topics", "ADD", "allow_replies", "varchar(2)", "NOT NULL", "'on'");
	__wps__alter_table("topics", "ADD", "topic_approved", "varchar(2)", "NOT NULL", "'on'");
	__wps__alter_table("topics", "ADD", "topic_answer", "varchar(2)", "", "''");
	__wps__alter_table("topics", "ADD", "for_info", "varchar(2)", "", "''");
	__wps__alter_table("topics", "ADD", "stub", "varchar(256)", "", "''");
	__wps__alter_table("topics", "ADD", "remote_addr", "varchar(32)", "", "''");
	__wps__alter_table("topics", "ADD", "http_x_forwarded_for", "varchar(32)", "", "''");

	// Modify profile extended fields table
	__wps__alter_table("extended", "MODIFY", "extended_name", "varchar(256)", "NOT NULL", "'New field'");
	__wps__alter_table("extended", "ADD", "extended_slug", "varchar(64)", "NOT NULL", "");
	__wps__alter_table("extended", "ADD", "wp_usermeta", "varchar(256)", "", "");
	__wps__alter_table("extended", "ADD", "readonly", "varchar(2)", "", "''");

	// Update motd flag
	update_option(WPS_OPTIONS_PREFIX.'_motd', '');

	// Setup Notifications
	__wps__notification_setoptions();
	
	// ***********************************************************************************************
 	// Update Versions *******************************************************************************
	update_option(WPS_OPTIONS_PREFIX."_version", WPS_VER);

	// Notify developers (feel free to comment out the next line)
	@mail('info@wpsymposium.com', get_bloginfo('url').' installed v'.WPS_VER, get_bloginfo('url'));
		
		
}

// Does the current page feature WPS?
function __wps__required() {
	
	// Using panel?
	if (function_exists('__wps__add_notification_bar'))
		return true;

	// Page/post contains shortcode?
	global $post;
	$content = $post->post_content;	
	if (strpos($content, '[symposium-') !== FALSE)
		return true;
	
	if (get_option(WPS_OPTIONS_PREFIX.'_always_load')) {
		return true;
	} else {
		return false;
	}
}

// Any admin warnings
function __wps__admin_warnings() {

   	global $wpdb; 	

	// CSS check
    $myStyleFile = WPS_PLUGIN_DIR . '/css/'.get_option(WPS_OPTIONS_PREFIX.'_wps_css_file');
    if ( !file_exists($myStyleFile) ) {
		echo "<div class='error'><p>".WPS_WL.": ";
		_e( sprintf('Stylesheet (%s) not found.', $myStyleFile), WPS_TEXT_DOMAIN);
		echo "</p></div>";
    }

	// JS check
    $myJSfile = WPS_PLUGIN_DIR . '/js/'.get_option(WPS_OPTIONS_PREFIX.'_wps_js_file');
    if ( !file_exists($myJSfile) ) {
		echo "<div class='error'><p>".WPS_WL.": ";
		_e( sprintf('Javascript file (%s) not found, please check <a href="admin.php?page=symposium_debug"></a>the installation page</a>.', $myJSfile), WPS_TEXT_DOMAIN);
		echo "</p></div>";
    }

    // MOTD
    if (get_option(WPS_OPTIONS_PREFIX.'_motd') != 'on' && (!(isset($_GET['page']) && $_GET['page'] == 'symposium_welcome'))) {

		if ( current_user_can( 'edit_theme_options' ) ) {   
			if (isset($_POST['symposium_hide_motd']) && $_POST['symposium_hide_motd'] == 'Y') {
				if (wp_verify_nonce($_POST['symposium_hide_motd_nonce'],'symposium_hide_motd_nonce')) {
					update_option(WPS_OPTIONS_PREFIX.'_motd', 'on');
				}
			} else {
				__wps__plugin_welcome();
			}
		}
		
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
		echo "<strong>".WPS_WL."</strong><br /><div style='padding:4px;'>";
		echo __('Please remove the following folders via FTP.<br />Do <strong>NOT</strong> remove them via the plugins admin page as this could delete data from your database:', WPS_TEXT_DOMAIN).'<br /><br />';
		echo $list;
		echo '</div></div>';
	}
    
}

// Dashboard Widget
function __wps__dashboard_widget(){
	wp_add_dashboard_widget('symposium_id', WPS_WL, '__wps__widget');
}
function __wps__widget() {
	
	global $wpdb, $current_user;
	
	echo '<img src="'.get_option(WPS_OPTIONS_PREFIX.'_images').'/logo_small.png" alt="Logo" style="float:right; width:120px;height:120px;" />';

	if (get_option(WPS_OPTIONS_PREFIX.'_activation_code') && has_bronze_plug_actived()) {
		echo __wps__bronze_countdown();
	}
	
	echo '<table><tr><td valign="top">';
	
		echo '<table>';
		echo '<tr><td colspan="2" style="padding:4px"><strong>'.__('Forum', WPS_TEXT_DOMAIN).'</strong></td></tr>';
		echo '<tr><td style="padding:4px"><a href="admin.php?page=symposium_categories">'.__('Categories', WPS_TEXT_DOMAIN).'</a></td>';
		echo '<td style="padding:4px">'.$wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix.'symposium_cats').'</td></tr>';
		echo '<tr><td style="padding:4px">'.__('Topics', WPS_TEXT_DOMAIN).'</td>';
		echo '<td style="padding:4px">'.$wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix.'symposium_topics'." WHERE topic_parent = 0").'</td></tr>';
		echo '<tr><td style="padding:4px">'.__('Replies', WPS_TEXT_DOMAIN).'</td>';
		echo '<td style="padding:4px">'.$wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix.'symposium_topics'." WHERE topic_parent > 0").'</td></tr>';
		echo '<tr><td style="padding:4px">'.__('Views', WPS_TEXT_DOMAIN).'</td>';
		echo '<td style="padding:4px">'.$wpdb->get_var("SELECT SUM(topic_views) FROM ".$wpdb->prefix.'symposium_topics'." WHERE topic_parent = 0").'</td></tr>';
		echo '<tr><td style="padding:4px">'.__('Mail', WPS_TEXT_DOMAIN).'</td>';
		$mailcount = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->base_prefix.'symposium_mail');
		$unread = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->base_prefix.'symposium_mail'." WHERE mail_read != 'on'");
		echo '<td style="padding:4px">'.$mailcount.' ';
		printf (__('(%s unread)', WPS_TEXT_DOMAIN), $unread);
		echo '</td></tr>';
		echo '</table>';
		
	echo "</td><td valign='top'>";

		echo '<table>';
			echo '<tr><td colspan="2" style="padding:4px"><strong>'.__('Plugins', WPS_TEXT_DOMAIN).'</strong></td></tr>';
			echo '<tr><td colspan="2" style="padding:4px">';
			if (function_exists('__wps__forum')) {
				echo '<a href="'.__wps__get_url('forum').'">'.__('Go to Forum', WPS_TEXT_DOMAIN).'</a>';
			} else {
				echo __('Forum not activated', WPS_TEXT_DOMAIN);
			}
			echo "</td></tr>";
			
			echo '<tr><td colspan="2" style="padding:4px">';
			if (function_exists('__wps__profile')) {
				$url = __wps__get_url('profile');
				echo '<a href="'.$url.__wps__string_query($url).'uid='.$current_user->ID.'">'.__('Go to Profile', WPS_TEXT_DOMAIN).'</a>';
			} else {
				echo __('Profile not activated', WPS_TEXT_DOMAIN);
			}
			echo "</td></tr>";
	
			echo '<tr><td colspan="2" style="padding:4px">';
			if (function_exists('__wps__mail')) {
				echo '<a href="'.__wps__get_url('mail').'">'.__('Go to Mail', WPS_TEXT_DOMAIN).'</a>';
			} else {
				echo __('Mail not activated', WPS_TEXT_DOMAIN);
			}
			echo "</td></tr>";
			
			echo '<tr><td colspan="2" style="padding:4px">';
			if (function_exists('__wps__members')) {
				echo '<a href="'.__wps__get_url('members').'">'.__('Go to Member Directory', WPS_TEXT_DOMAIN).'</a>';
			} else {
				echo __('Member Directory not activated', WPS_TEXT_DOMAIN);
			}
			echo "</td></tr>";
			
			echo '<tr><td colspan="2" style="padding:4px">';
			if (function_exists('__wps__group')) {
				echo '<a href="'.__wps__get_url('groups').'">'.__('Go to Group Directory', WPS_TEXT_DOMAIN).'</a><br />';
			} else {
				echo __('Groups not activated', WPS_TEXT_DOMAIN);
			}
			echo "</td></tr>";
			
		echo "</table>";

	echo "</td></tr></table>";

}

function __wps__deactivate() {

	wp_clear_scheduled_hook('symposium_notification_hook');
	delete_option('symposium_debug_mode');

}

/* ====================================================== NOTIFICATIONS ====================================================== */

function __wps__notification_setoptions() {
	update_option(WPS_OPTIONS_PREFIX."_notification_inseconds",86400);
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
add_filter('cron_schedules', '__wps__notification_more_reccurences');
function __wps__notification_more_reccurences($recc) {
	$recc['symposium_notification_recc'] = array('interval' => get_option(WPS_OPTIONS_PREFIX."_notification_inseconds"), 'display' => WPS_WL_SHORT.' Notification Schedule');
	return $recc;
}
	
/* This is the scheduling hook for our plugin that is triggered by cron */
function __wps__notification_trigger_schedule() {
	__wps__notification_do_jobs('cron');
}

/* This is called by the scheduled cron job, and by Health Check Daily Digest check */
function __wps__notification_do_jobs($mode) {
	
	global $wpdb;
	$summary_email = __("Website Title", WPS_TEXT_DOMAIN).": ".get_bloginfo('name')."<br />";
	$summary_email .= __("Website URL", WPS_TEXT_DOMAIN).": ".get_bloginfo('wpurl')."<br />";
	$summary_email .= __("Admin Email", WPS_TEXT_DOMAIN).": ".get_bloginfo('admin_email')."<br />";
	$summary_email .= __("WordPress version", WPS_TEXT_DOMAIN).": ".get_bloginfo('version')."<br />";
	$summary_email .= sprintf(__("%s version", WPS_TEXT_DOMAIN), WPS_WL).": ".WPS_VER."<br />";
	$summary_email .= __("Daily Digest mode", WPS_TEXT_DOMAIN).": ".$mode."<br /><br />";
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
	$summary_email .= __("Database cleanup", WPS_TEXT_DOMAIN).": completed<br />";
	$users_sent_to_success = '';
	$users_sent_to_failed = '';
				
	// ******************************************* Daily Digest ******************************************
	$send_summary = get_option(WPS_OPTIONS_PREFIX.'_send_summary');
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
			// The __wps__get_url() function is in functions.php
			$forum_url = __wps__get_url('forum');
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
						$body .= "<h2>".__('New Topics', WPS_TEXT_DOMAIN)."</h2>";
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
						$body .= "<h2>".__('Replies in', WPS_TEXT_DOMAIN)." ".$category->title."</h2>";
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
							$body .= " <a href='".$forum_url.$q."cid=".$category->cid."&show=".$topic->tid."'>".__('View topic', WPS_TEXT_DOMAIN)."...</a>";
							$body .= "<br />";
							$body .= "<br />";
						}						
					}	
				}
			}
			
			$body .= "<p>".__("You can stop receiving these emails at", WPS_TEXT_DOMAIN)." <a href='".$forum_url."'>".$forum_url."</a>.</p>";
			
			$users = $wpdb->get_results("SELECT DISTINCT user_email 
			FROM ".$wpdb->base_prefix.'users'." u 
			INNER JOIN ".$wpdb->base_prefix."usermeta m ON u.ID = m.user_id 
			WHERE meta_key = 'forum_digest' and m.meta_value = 'on'"); 
			
			if ($users) {
				foreach ($users as $user) {
					if ($mode == 'cron' || $mode == 'send_admin_summary_and_to_users') {
						$user_count++;
						$email = $user->user_email;
						if(__wps__sendmail($email, __('Daily Forum Digest', WPS_TEXT_DOMAIN), $body)) {
							$users_sent_to_success .= $user->user_email.'<br />';
							update_option(WPS_OPTIONS_PREFIX."_notification_triggercount",get_option(WPS_OPTIONS_PREFIX."_notification_triggercount")+1);
						} else {
							$users_sent_to_failed .= $user->user_email.'<br />';
						}						
					}
				}
			}

		}
	}
	
	// Send admin summary
	$summary_email .= __("Forum topic count for previous day (midnight to midnight)", WPS_TEXT_DOMAIN).": ".$topics_count."<br />";
	$summary_email .= __("Daily Digest sent count", WPS_TEXT_DOMAIN).": ".$user_count."<br /><br />";
	$summary_email .= "<b>".__("List of recipients sent to:", WPS_TEXT_DOMAIN)."</b><br />";
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
	if (__wps__sendmail($email, __('Daily Digest Summary Report', WPS_TEXT_DOMAIN), $summary_email)) {
		$success = "OK (summary sent to ".get_bloginfo('admin_email')."). ";
	} else {
		$success = "FAILED sending to ".get_bloginfo('admin_email').". ";
	}
	
	return $success;
	
}

// Record last logged in and previously logged in 
function __wps__login($user_login) {

	global $wpdb, $current_user;

	// Get ID for this user
	$sql = "SELECT ID from ".$wpdb->prefix."users WHERE user_login = %s";
	$id = $wpdb->get_var($wpdb->prepare($sql, $user_login));
	// Get last time logged in
	$last_login = __wps__get_meta($id, 'last_login');
	$previous_login = __wps__get_meta($id, 'previous_login');
	// Store as previous time last logged in
	if ($previous_login == NULL) {
		__wps__update_meta($id, 'previous_login', "'".date("Y-m-d H:i:s")."'");
	} else {
		__wps__update_meta($id, 'previous_login', "'".$last_login."'");
	}
	// Store this log in as the last time logged in
	__wps__update_meta($id, 'last_login', "'".date("Y-m-d H:i:s")."'");
	
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
		if (get_option(WPS_OPTIONS_PREFIX.'_img_db') == "on") {
		
			$profile_photo = __wps__get_meta($id, 'profile_avatar');
			$profile_avatars = get_option(WPS_OPTIONS_PREFIX.'_profile_avatars');
		
			if ($profile_photo == '' || $profile_photo == 'upload_failed' || $profile_avatars != 'on') {
				$return .= apply_filters('get_avatar', $avatar, $id_or_email, $size, $default, $alt);
			} else {
				$return .= "<img src='".WP_CONTENT_URL."/plugins/wp-symposium/uploadify/get_profile_avatar.php?uid=".$id."' style='width:".$size."px; height:".$size."px' class='avatar avatar-".$size." photo' />";
			}
			
		} else {

			$profile_photo = __wps__get_meta($id, 'profile_photo');
			$profile_avatars = get_option(WPS_OPTIONS_PREFIX.'_profile_avatars');

			if ($profile_photo == '' || $profile_photo == 'upload_failed' || $profile_avatars != 'on') {
				$return .= apply_filters('get_avatar', $avatar, $id_or_email, $size, $default, $alt);
			} else {
				$img_url = get_option(WPS_OPTIONS_PREFIX.'_img_url')."/members/".$id."/profile/";	
				$img_src = str_replace('//','/',$img_url) . $profile_photo;
				$return .= "<img src='".$img_src."' style='width:".$size."px; height:".$size."px' class='avatar avatar-".$size." photo' />";
			}
			
		}
		
		if (!get_option(WPS_OPTIONS_PREFIX.'_wps_use_gravatar') && strpos($return, 'gravatar')) {
			$return = "<img src='".get_option(WPS_OPTIONS_PREFIX.'_images')."/unknown.jpg' style='width:".$size."px; height:".$size."px' class='avatar avatar-".$size." photo' />";
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
		
		// Replace Simon's border (sorry for this messy bit of code!!)
		if ($id == 85) {
			$return = str_replace("8C7853", "450048", $return);                          
		}

		// Get URL to profile
		if (function_exists('__wps__profile') && $id != '' ) {
			$profile_url = __wps__get_url('profile');
			$profile_url = $profile_url.__wps__string_query($profile_url).'uid='.$id;
	       	$return = str_replace("/>", " style='cursor:pointer' onclick='javascript:document.location=\"".$profile_url."\";' />", $return);                          
		}
		
		// Add Profile Plus if installed
		if (function_exists('__wps__profile_plus')) {
			if (get_option(WPS_OPTIONS_PREFIX.'_wps_show_hoverbox') == 'on') {
				if ($id != '') {
					$display_name = str_replace("'", "&apos;", $wpdb->get_var("select display_name from ".$wpdb->base_prefix."users where ID = '".$id."'"));
				} else {
					$display_name = '';
				}
				if (__wps__friend_of($id, $current_user->ID)) {
			       	$return = str_replace("class='", "rel='friend' title = '".$display_name."' id='".$id."' class='__wps__follow ", $return);
				} else {
					if (__wps__pending_friendship($id)) {
				       	$return = str_replace("class='", "rel='pending' title = '".$display_name."' id='".$id."' class='__wps__follow ", $return);
					} else {
				       	$return = str_replace("class='", "rel='' title = '".$display_name."' id='".$id."' class='__wps__follow ", $return);
					}
				}
				if (__wps__is_following($current_user->ID, $id)) {
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
function __wps__lastactivity() {
   	global $wpdb, $current_user;
	wp_get_current_user();
	
	// Update last logged in
	if (is_user_logged_in()) {
		__wps__update_meta($current_user->ID, 'last_activity', "'".date("Y-m-d H:i:s")."'");
	}
	// Powered by message
	echo __wps__powered_by();

}

function __wps__concealed_avatar() {
	if (__wps__required()) {
		global $current_user;
		// Place hidden div of current user to use when adding to screen
		echo "<div id='__wps__current_user_avatar' style='display:none;'>";
		echo get_avatar($current_user->ID, 200);
		echo "</div>";
	}
}

function __wps__footer_admin () {
	// Hidden DIV for admin dialog boxes
	echo '<span id="footer-thankyou">' . __( 'Thank you for creating with <a href="http://wordpress.org/">WordPress</a>.' ) . '</span>';
	echo "<div id='symposium_dialog' class='wp-dialog' style='padding:10px;display:none'></div>";				
}

// Hook to replace Smilies
function __wps__buffer($buffer){ // $buffer contains entire page

	if (!get_option(WPS_OPTIONS_PREFIX.'_wps_lite') && !strpos($buffer, "<rss") ) {

		global $wpdb;
		
		if (get_option(WPS_OPTIONS_PREFIX.'_emoticons') == "on") {
			
			$smileys = WPS_PLUGIN_URL . '/images/smilies/';
			$smileys_dir = WPS_PLUGIN_DIR . '/images/smilies/';
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
			
		if (get_option(WPS_OPTIONS_PREFIX.'_tags') == "on") {

			// User tagging			
		
			$profile = __wps__get_url('profile').__wps__string_query($profile_url).'uid=';
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
					$end = __wps__strpos($buffer, $needles, $start);
					if ($end === false) $end = strlen($buffer);
					$first_bit = substr($buffer, 0, $start);
					$last_bit = substr($buffer, $end, strlen($buffer)-$end+2);
					$bit = substr($buffer, $start+1, $end-$start-1);
					$sql = 'SELECT ID FROM '.$wpdb->base_prefix.'users WHERE replace(display_name, " ", "") = %s LIMIT 0,1';
					$id = $wpdb->get_var($wpdb->prepare($sql, $bit));
					if ($id) {
						$buffer = $first_bit.'<a href="'.$profile.$id.'" class="__wps__usertag">&#64;'.$bit.'</a>'.$last_bit;
					} else {
						$sql = 'SELECT ID FROM '.$wpdb->base_prefix.'users WHERE user_login = %s LIMIT 0,1';
						$id = $wpdb->get_var($wpdb->prepare($sql, $bit));
						if ($id) {
							$buffer = $first_bit.'<a href="'.$profile.$id.'" class="__wps__usertag">&#64;'.$bit.'</a>'.$last_bit;
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

function __wps__strip_smilies($buffer){ 
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
function __wps__unread($buffer){ 
	
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
function __wps__js_init() {

	global $wpdb;
		
	$plugin = WPS_PLUGIN_URL;

	// Only load if not admin (and chosen in Settings)
	if (!is_admin()) {

		if (get_option(WPS_OPTIONS_PREFIX.'_jquery') == "on") {
			wp_enqueue_script('jquery');	 		
		}

		if (get_option(WPS_OPTIONS_PREFIX.'_jqueryui') == "on") {
			wp_enqueue_script('jquery-ui-custom', $plugin.'/js/jquery-ui-1.8.11.custom.min.js', array('jquery'));	
		    wp_register_style('__wps__jquery-ui-css', WPS_PLUGIN_URL.'/css/jquery-ui-1.8.11.custom.css');
			wp_enqueue_style('__wps__jquery-ui-css');
		}	

	 	if (get_option(WPS_OPTIONS_PREFIX.'_use_wysiwyg') == "on" || function_exists('__wps__events_main')) {
	 		wp_enqueue_script('wps-tinymce', $plugin.'/tiny_mce/tiny_mce.js', array('jquery'));	
	 	}

		wp_enqueue_script('plupload-all');

	}
	
}

// Perform admin duties, such as add jQuery and jQuery scripts and other admin jobs
function __wps__admin_init() {
	if (is_admin()) {

		// jQuery dialog box for use in admin
		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_style( 'wp-jquery-ui-dialog' );
		
		// WordPress color picker
		wp_enqueue_style( 'farbtastic' );
	    wp_enqueue_script( 'farbtastic' );

	  // Load admin CSS
	  $myStyleUrl = WPS_PLUGIN_URL . '/css/wps-admin.css';
	  $myStyleFile = WPS_PLUGIN_DIR . '/css/wps-admin.css';
	  if ( file_exists($myStyleFile) ) {
	    wp_register_style('__wps__Admin_StyleSheet', $myStyleUrl);
	    wp_enqueue_style('__wps__Admin_StyleSheet');
	  }

	}
}

// Add JS scripts to WordPress for use and other preparatory stuff
function __wps__scriptsAction() {

	$__wps__plugin_url = WPS_PLUGIN_URL;
	$__wps__plugin_path = str_replace("http://".$_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"], "", $__wps__plugin_url);
 
	global $wpdb, $current_user;
	wp_get_current_user();

	// Set script timeout
	if (get_option(WPS_OPTIONS_PREFIX.'_wps_time_out') > 0) {
		set_time_limit(get_option(WPS_OPTIONS_PREFIX.'_wps_time_out'));
	}

	// Debug mode?
	define('WPS_DEBUG', get_option(WPS_OPTIONS_PREFIX.'_debug_mode'));


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
			$forum = $wpdb->get_row($wpdb->prepare("SELECT group_forum, $default_page FROM ".$wpdb->prefix."symposium_groups WHERE gid = %d", $page_gid));
			if ($forum->default_page == 'forum' && $forum->group_forum == 'on') {
				$cat_id = 0;
			}
		}
								
		// Gallery
		$album_id = 0;
		if (isset($_GET['album_id'])) { $album_id = $_GET['album_id']; }
		if (isset($_POST['album_id'])) { $album_id = $_POST['album_id']; }
		
		// Get styles for JS
		if (get_option(WPS_OPTIONS_PREFIX.'_use_styles') == "on") {
			$bg_color_2 = get_option(WPS_OPTIONS_PREFIX.'_bg_color_2');
			$row_border_size = get_option(WPS_OPTIONS_PREFIX.'_row_border_size');
			$row_border_style = get_option(WPS_OPTIONS_PREFIX.'_row_border_style');
			$text_color_2 = get_option(WPS_OPTIONS_PREFIX.'_text_color_2');
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
		$permitted_ext = get_option(WPS_OPTIONS_PREFIX.'_image_ext').','.get_option(WPS_OPTIONS_PREFIX.'_video_ext').','.get_option(WPS_OPTIONS_PREFIX.'_doc_ext');
				
		// Load JS supporting scripts	
		wp_enqueue_script('jquery-uploadify', WPS_PLUGIN_URL.'/uploadify/jquery.uploadify.v2.1.4.js', array('jquery'));		

		// Load JS
	 	wp_enqueue_script('__wps__', $__wps__plugin_url.'/js/'.get_option(WPS_OPTIONS_PREFIX.'_wps_js_file'), array('jquery'));
	
	 	// Load JScharts?
	 	if (get_option(WPS_OPTIONS_PREFIX.'_jscharts')) {
	 	    if (get_option(WPS_OPTIONS_PREFIX.'_wps_js_file') == 'wps.js') {
			 	wp_enqueue_script('wps_jscharts', $__wps__plugin_url.'/js/jscharts.js', array('jquery'));
	 	    } else {
			 	wp_enqueue_script('wps_jscharts', $__wps__plugin_url.'/js/jscharts.min.js', array('jquery'));
	 	    }
	 	}
	 	
	 	// Use WP editor? (not for use yet!!!!)
	 	update_option(WPS_OPTIONS_PREFIX.'_use_wp_editor', false);

		// Set JS variables
		wp_localize_script( '__wps__', '__wps__', array(
			'permalink' => get_permalink(),
			'plugins' => WP_PLUGIN_URL, 
			'plugin_url' => WPS_PLUGIN_URL.'/', 
			'plugin_path' => $__wps__plugin_path,
			'images_url' => get_option(WPS_OPTIONS_PREFIX.'_images'),
			'inactive' => get_option(WPS_OPTIONS_PREFIX.'_online'),
			'forum_url' => __wps__get_url('forum'),
			'mail_url' => __wps__get_url('mail'),
			'profile_url' => __wps__get_url('profile'),
			'groups_url' => __wps__get_url('groups'),
			'group_url' => __wps__get_url('group'),
			'gallery_url' => get_option(WPS_OPTIONS_PREFIX.'_gallery_url'),
			'page_gid' => $page_gid,
			'offline' => get_option(WPS_OPTIONS_PREFIX.'_offline'),
			'use_chat' => get_option(WPS_OPTIONS_PREFIX.'_use_chat'),
			'chat_polling' => get_option(WPS_OPTIONS_PREFIX.'_chat_polling'),
			'bar_polling' => get_option(WPS_OPTIONS_PREFIX.'_bar_polling'),
			'view' => $view,
			'profile_default' => get_option(WPS_OPTIONS_PREFIX.'_wps_profile_default'),
			'show_tid' => $show_tid,
			'cat_id' => $cat_id,
			'album_id' => $album_id,
			'current_user_id' => $current_user->ID,
			'current_user_display_name' => $display_name,
			'current_user_level' => __wps__get_current_userlevel($current_user->ID),
			'current_user_page' => $page_uid,
			'current_group' => $page_gid,
			'post' => $GETpost,
			'please_wait' => __('Please Wait...', WPS_TEXT_DOMAIN),
			'saving' => __('Saving...', WPS_TEXT_DOMAIN),
			'site_title' => get_bloginfo('name'),
			'site_url' => get_bloginfo('url'),
			'bg_color_2' => $bg_color_2,
			'row_border_size' => $row_border_size,
			'row_border_style' => $row_border_style,
			'text_color_2' => $text_color_2,
			'template_mail_tray' => get_option(WPS_OPTIONS_PREFIX.'_template_mail_tray'),
			'embed' => $embed,
			'to' => $to,
			'is_admin' => 0,
			'mail_id' => $mid,
			'permitted_ext' => $permitted_ext,
			'forum_ajax' => get_option(WPS_OPTIONS_PREFIX.'_forum_ajax'),
			'wps_lite' => get_option(WPS_OPTIONS_PREFIX.'_wps_lite'),
			'wps_use_poke' => get_option(WPS_OPTIONS_PREFIX.'_use_poke'),
			'wps_forum_stars' => get_option(WPS_OPTIONS_PREFIX.'_forum_stars'),
			'wps_forum_refresh' => get_option(WPS_OPTIONS_PREFIX.'_forum_refresh'),
			'wps_wysiwyg' => get_option(WPS_OPTIONS_PREFIX.'_use_wysiwyg'),
			'wps_wysiwyg_1' => get_option(WPS_OPTIONS_PREFIX.'_use_wysiwyg_1'),
			'wps_wysiwyg_2' => get_option(WPS_OPTIONS_PREFIX.'_use_wysiwyg_2'),
			'wps_wysiwyg_3' => get_option(WPS_OPTIONS_PREFIX.'_use_wysiwyg_3'),
			'wps_wysiwyg_4' => get_option(WPS_OPTIONS_PREFIX.'_use_wysiwyg_4'),
			'wps_wysiwyg_css' => get_option(WPS_OPTIONS_PREFIX.'_use_wysiwyg_css'),
			'wps_wysiwyg_skin' => get_option(WPS_OPTIONS_PREFIX.'_use_wysiwyg_skin'),
			'wps_wysiwyg_width' => get_option(WPS_OPTIONS_PREFIX.'_use_wysiwyg_width'),
			'wps_wysiwyg_height' => get_option(WPS_OPTIONS_PREFIX.'_use_wysiwyg_height'),
			'wps_plus' => (defined('WPS_PLUS')) ? WPS_PLUS : '',
			'wps_admin_page' => 'na',
			'dir_page_length' => get_option(WPS_OPTIONS_PREFIX.'_dir_page_length'),
			'dir_full_ver' => get_option(WPS_OPTIONS_PREFIX.'_dir_full_ver') ? true : false,
			'use_elastic' => get_option(WPS_OPTIONS_PREFIX.'_elastic'),
			'events_user_places' => get_option(WPS_OPTIONS_PREFIX.'_events_user_places'),
			'events_use_wysiwyg' => get_option(WPS_OPTIONS_PREFIX.'_events_use_wysiwyg'),
			'debug' => WPS_DEBUG,
			'include_context' => get_option(WPS_OPTIONS_PREFIX.'_include_context'),
			'use_wp_editor' => get_option(WPS_OPTIONS_PREFIX.'_use_wp_editor'),
			'profile_menu_scrolls' => get_option(WPS_OPTIONS_PREFIX.'_profile_menu_scrolls'),
			'profile_menu_delta' => get_option(WPS_OPTIONS_PREFIX.'_profile_menu_delta'),
			'profile_menu_adjust' => get_option(WPS_OPTIONS_PREFIX.'_profile_menu_adjust')
		));

	}
	
	if (is_admin()) {
		
		// ADMIN JS load
		// wp_enqueue_script('jquery-uploadify', WPS_PLUGIN_URL.'/uploadify/jquery.uploadify.v2.1.4.js', array('jquery'));

		// Load JS
	 	// wp_enqueue_script('__wps__', $__wps__plugin_url.'/js/'.get_option(WPS_OPTIONS_PREFIX.'_wps_js_file'), array('jquery'));

		// Load admin JS
	 	wp_enqueue_script('__wps__', $__wps__plugin_url.'/js/wps-admin.js', array('jquery'));
	 	
		// Set JS variables
		wp_localize_script( '__wps__', '__wps__', array(
			'plugins' => WP_PLUGIN_URL, 
			'plugin_url' => WPS_PLUGIN_URL.'/', 
			'plugin_path' => $__wps__plugin_path,
			'images_url' => get_option(WPS_OPTIONS_PREFIX.'_images'),
			'inactive' => get_option(WPS_OPTIONS_PREFIX.'_online'),
			'forum_url' => get_option(WPS_OPTIONS_PREFIX.'_forum_url'),
			'mail_url' => get_option(WPS_OPTIONS_PREFIX.'_mail_url'),
			'profile_url' => get_option(WPS_OPTIONS_PREFIX.'_profile_url'),
			'groups_url' => get_option(WPS_OPTIONS_PREFIX.'_groups_url'),
			'group_url' => get_option(WPS_OPTIONS_PREFIX.'_group_url'),
			'gallery_url' => get_option(WPS_OPTIONS_PREFIX.'_gallery_url'),
			'offline' => get_option(WPS_OPTIONS_PREFIX.'_offline'),
			'use_chat' => get_option(WPS_OPTIONS_PREFIX.'_use_chat'),
			'chat_polling' => get_option(WPS_OPTIONS_PREFIX.'_chat_polling'),
			'bar_polling' => get_option(WPS_OPTIONS_PREFIX.'_bar_polling'),
			'current_user_id' => $current_user->ID,
			'is_admin' => 1,
			'wps_admin_page' => 'symposium_debug'
			
		));
	}
	
}

/* ====================================================== PAGE LOADED FUNCTIONS ====================================================== */

function __wps__replace() {
	if (__wps__required()) {	
		ob_start();
		ob_start('__wps__unread');
	}
}

/* ====================================================== ADMIN FUNCTIONS ====================================================== */

// Add Stylesheet
function __wps__add_stylesheet() {

	global $wpdb;

	if (!is_admin() && __wps__required()) {

	    // Load CSS
	    $myStyleUrl = WPS_PLUGIN_URL . '/css/'.get_option(WPS_OPTIONS_PREFIX.'_wps_css_file');
	    $myStyleFile = WPS_PLUGIN_DIR . '/css/'.get_option(WPS_OPTIONS_PREFIX.'_wps_css_file');
	    if ( file_exists($myStyleFile) ) {
	        wp_register_style('__wps__StyleSheet', $myStyleUrl);
	        wp_enqueue_style('__wps__StyleSheet');
	    }

		// Notices
		include_once(dirname(__FILE__).'/dialogs.php');
			
	}

}

// Language files
function __wps__languages() {

    if ( file_exists(dirname(__FILE__).'/languages/') ) {
        load_plugin_textdomain(WPS_TEXT_DOMAIN, false, dirname(plugin_basename(__FILE__)).'/languages/');
    } else {
        if ( file_exists(dirname(__FILE__).'/lang/') ) {
            load_plugin_textdomain(WPS_TEXT_DOMAIN, false, dirname(plugin_basename(__FILE__)).'/lang/');
        } else {
            load_plugin_textdomain(WPS_TEXT_DOMAIN);
        }
    }

}


?>
