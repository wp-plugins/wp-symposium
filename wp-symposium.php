<?php
/*
Plugin Name: WP Symposium
Plugin URI: http://www.wpsymposium.com
Description: Core code for Symposium, this plugin must always be activated, before any other Symposium plugins/widgets (they rely upon it).
Version: 0.1.29.4
Author: WP Symposium
Author URI: http://www.wpsymposium.com
License: GPL2
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

global $wpdb, $symposium_db_version;
$symposium_db_version = "1";

add_action('init', 'symposium_languages');
add_action('init', 'js_init');
add_action('init', 'symposium_notification_setoptions');
add_action('symposium_notification_hook','symposium_notification_trigger_schedule');
add_action('wp_login', 'symposium_redirect_login', 10);
add_action('wp_logout', 'symposium_redirect_logout', 10);
add_action('wp_footer', 'symposium_lastactivity', 10);
add_action('template_redirect', 'symposium_replace');
add_action('wp_print_styles', 'add_symposium_stylesheet');
add_action('wp_print_scripts', 'symposium_scriptsAction');

if (is_admin()) {
	include('symposium_menu.php');
	add_action('admin_notices', 'symposium_mail_warning');
	add_action('wp_dashboard_setup', 'symposium_dashboard_widget');	
	add_action('init', 'symposium_admin_init');
	add_action('admin_notices', 'symposium_admin_check');
}		   	

register_activation_hook(__FILE__,'symposium_activate');
register_deactivation_hook(__FILE__, 'symposium_deactivate');
register_uninstall_hook(__FILE__, 'symposium_uninstall');

/* ===================================================== ADMIN ====================================================== */


// Any admin warnings
function symposium_mail_warning() {

   	global $wpdb;

	$parts = explode('.',get_option("symposium_version"));	
	$major = $parts[0];
	$minor = $parts[1];
	$db = $parts[2];
	$patch = $parts[3];
	$db_ver = get_option("symposium_db_version");

	if ($db != $db_ver) {
		echo "<div class='updated'><p>";
		_e("You need to update your WP Symposium database - please deactivate, then re-activate the WP Symposium core plugin.");
		echo "</p></div>";
	}

	// CSS check
    $myStyleFile = WP_PLUGIN_DIR . '/wp-symposium/css/symposium.css';
    if ( !file_exists($myStyleFile) ) {
		echo "<div class='error'><p>WPS Symposium: ";
		_e( sprintf('Stylesheet (%s) not found.', $myStyleFile), 'wp-symposium');
		echo "</p></div>";
    }

}

// Dashboard Widget
function symposium_dashboard_widget(){
	wp_add_dashboard_widget('symposium_id', 'WP Symposium', 'symposium_widget');
}
function symposium_widget() {
	
	global $wpdb;
	
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
		$mailcount = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix.'symposium_mail');
		$unread = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix.'symposium_mail'." WHERE mail_read != 'on'");
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
				echo '<a href="'.$url.'">'.__('Go to Profile', 'wp-symposium').'</a>';
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
			
		echo "</table>";
	
	
	echo "</td></tr></table>";

}

function symposium_activate() {
	
   	global $wpdb, $current_user;
   	global $symposium_db_version;
	wp_get_current_user();

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

	// Version of WP Symposium
	$symposium_version = "0.1.29.4";
	$symposium_db_ver = 29;
	
	// Code version *************************************************************************************
	$ver = get_option("symposium_version");
	if ($ver != false) {		 
	    update_option("symposium_version", $symposium_version);	    	   	
	} else {
		// Set Database Version		
	    add_option("symposium_version", $symposium_version);	    	   	
	}

	// Database version *************************************************************************************
	$db_ver = get_option("symposium_db_version");
	if ($db_ver != false) {
		$db_ver = (int) $db_ver;
	} else {
		// Set Database Version		
	    add_option("symposium_db_version", 1);	    	   	
	}


	// Create initial versions of tables *************************************************************************************

	include('create_tables.php');

  	// Update tables *************************************************************************************

   	// Add option fields	
	symposium_alter_table("config", "ADD", "allow_new_topics", "varchar(2)", "NOT NULL", "''");
	symposium_alter_table("config", "ADD", "underline", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("config", "ADD", "preview1", "int(11)", "NOT NULL", "'45'");
	symposium_alter_table("config", "ADD", "preview2", "int(11)", "NOT NULL", "'90'");
	symposium_alter_table("config", "ADD", "viewer", "varchar(32)", "NOT NULL", "'Guest'");
	symposium_alter_table("config", "ADD", "include_admin", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("config", "ADD", "oldest_first", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("config", "ADD", "wp_width", "varchar(6)", "NOT NULL", "'99pc'");
	symposium_alter_table("config", "ADD", "main_background", "varchar(12)", "NOT NULL", "'#fff'");
	symposium_alter_table("config", "ADD", "closed_opacity", "varchar(6)", "NOT NULL", "'1.0'");
	symposium_alter_table("config", "ADD", "closed_word", "varchar(32)", "NOT NULL", "'closed'");
	symposium_alter_table("config", "ADD", "fontfamily", "varchar(64)", "NOT NULL", "'Georgia,Times'");
	symposium_alter_table("config", "ADD", "fontsize", "varchar(16)", "NOT NULL", "'15'");
	symposium_alter_table("config", "ADD", "headingsfamily", "varchar(64)", "NOT NULL", "'Arial,Helvetica'");
	symposium_alter_table("config", "ADD", "headingssize", "varchar(16)", "NOT NULL", "'20'");
	symposium_alter_table("config", "ADD", "jquery", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("config", "ADD", "jqueryui", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("config", "ADD", "emoticons", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("config", "ADD", "seo", "varchar(2)", "NOT NULL", "''");
	symposium_alter_table("config", "ADD", "moderation", "varchar(2)", "NOT NULL", "''");
	symposium_alter_table("config", "ADD", "mail_url", "varchar(128)", "NOT NULL", "'Important: Please update!'");
	symposium_alter_table("config", "ADD", "online", "int(11)", "NOT NULL", "'3'");
	symposium_alter_table("config", "ADD", "offline", "int(11)", "NOT NULL", "'15'");
	symposium_alter_table("config", "ADD", "wp_alignment", "varchar(16)", "NOT NULL", "'Center'");
	symposium_alter_table("config", "ADD", "enable_password", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("config", "ADD", "enable_redirects", "varchar(2)", "NOT NULL", "''");
	symposium_alter_table("config", "ADD", "login_redirect", "varchar(128)", "NOT NULL", "'WordPress default'");
	symposium_alter_table("config", "ADD", "login_redirect_url", "varchar(128)", "NOT NULL", "''");
	symposium_alter_table("config", "ADD", "logout_redirect", "varchar(128)", "NOT NULL", "'WordPress default'");
	symposium_alter_table("config", "ADD", "logout_redirect_url", "varchar(128)", "NOT NULL", "''");
	symposium_alter_table("config", "ADD", "use_wp_profile", "varchar(2)", "NOT NULL", "''");
	symposium_alter_table("config", "ADD", "use_wp_login", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("config", "ADD", "custom_login_url", "varchar(128)", "NOT NULL", "''");
	symposium_alter_table("config", "ADD", "use_wp_register", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("config", "ADD", "custom_register_url", "varchar(128)", "NOT NULL", "''");
	symposium_alter_table("config", "ADD", "register_use_sum", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("config", "ADD", "register_url", "varchar(128)", "NOT NULL", "'Important: Please update!'");
	symposium_alter_table("config", "ADD", "members_url", "varchar(128)", "NOT NULL", "'Important: Please update!'");
	symposium_alter_table("config", "ADD", "login_url", "varchar(128)", "NOT NULL", "'Important: Please update!'");
	symposium_alter_table("config", "ADD", "avatar_url", "varchar(128)", "NOT NULL", "'Important: Please update!'");
	symposium_alter_table("config", "ADD", "sharing", "varchar(32)", "", "''");
	symposium_alter_table("config", "ADD", "register_message", "text", "", "''");
	symposium_alter_table("config", "ADD", "use_styles", "varchar(2)", "NOT NULL", "'on'");
	
	// Modify Mail table
	symposium_alter_table("mail", "MODIFY", "mail_sent", "datetime", "", "");

	// Modify Profile table
	symposium_alter_table("config", "ADD", "profile_url", "varchar(128)", "NOT NULL", "'Important: Please update!'");

	// Modify Comments table
	symposium_alter_table("comments", "MODIFY", "comment_timestamp", "datetime", "", "");
	
	// Modify Friends table
	symposium_alter_table("friends", "MODIFY", "friend_timestamp", "datetime", "", "");

	// Modify Notification bar table
	symposium_alter_table("config", "ADD", "sound", "varchar(32)", "NOT NULL", "'chime.mp3'");
	symposium_alter_table("config", "ADD", "bar_position", "varchar(6)", "NOT NULL", "'bottom'");
	symposium_alter_table("config", "ADD", "bar_label", "text", "NOT NULL", "''");
	symposium_alter_table("notifications", "ADD", "notification_old", "varchar(2)", "NOT NULL", "''");
	symposium_alter_table("config", "ADD", "use_chat", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("config", "ADD", "bar_polling", "int(11)", "NOT NULL", "'120'");
	symposium_alter_table("config", "ADD", "chat_polling", "int(11)", "NOT NULL", "'10'");
	symposium_alter_table("config", "ADD", "visitors", "varchar(2)", "NOT NULL", "'on'");

	// Modify user meta table
	symposium_alter_table("usermeta", "ADD", "sound", "varchar(32)", "NOT NULL", "'chime.mp3'");
	symposium_alter_table("usermeta", "ADD", "soundchat", "varchar(32)", "NOT NULL", "'tap.mp3'");
	symposium_alter_table("usermeta", "ADD", "bar_position", "varchar(6)", "NOT NULL", "'bottom'");
	symposium_alter_table("usermeta", "ADD", "notify_new_messages", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("usermeta", "ADD", "notify_new_wall", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("usermeta", "ADD", "timezone", "int(11)", "", "0");
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

	// Modify styles table
	symposium_alter_table("styles", "ADD", "underline", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("styles", "ADD", "main_background", "varchar(12)", "NOT NULL", "'#fff'");
	symposium_alter_table("styles", "ADD", "closed_opacity", "varchar(6)", "NOT NULL", "'1.0'");
	symposium_alter_table("styles", "ADD", "fontfamily", "varchar(128)", "NOT NULL", "'Georgia,Times'");
	symposium_alter_table("styles", "ADD", "fontsize", "varchar(8)", "NOT NULL", "'15'");
	symposium_alter_table("styles", "ADD", "headingsfamily", "varchar(128)", "NOT NULL", "'Georgia,Times'");
	symposium_alter_table("styles", "ADD", "headingssize", "varchar(8)", "NOT NULL", "'20'");
	
	// Add moderation field to topics
	symposium_alter_table("topics", "ADD", "allow_replies", "varchar(2)", "NOT NULL", "'on'");
	symposium_alter_table("topics", "ADD", "topic_approved", "varchar(2)", "NOT NULL", "'on'");

						      	
	// ***********************************************************************************************
 	// Update Database Version ***********************************************************************
	update_option("symposium_db_version", $symposium_db_ver);
	
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

	// Delete Notification options
	delete_option("symposium_notification_inseconds");
	delete_option("symposium_notification_recc");
	delete_option("symposium_notification_triggercount");
	wp_clear_scheduled_hook('symposium_notification_hook');
	
	// Delete any options thats stored also
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
	
	global $wpdb;

	// *************************************** First do daily jobs ***************************************
	// Wipe Audit
	$wpdb->query("DELETE FROM ".$wbdb->prefix."symposium_audit");
	// Clear Chat Windows (tidy up anyone who didn't close a chat window)
	$wpdb->query("DELETE FROM ".$wbdb->prefix."symposium_chat");
	// Clean irrelevant notifications
	$wpdb->query("DELETE FROM ".$wbdb->prefix."symposium_notifications WHERE notification_to = 0");
	
	// ******************************************* Daily Digest ******************************************
	$send_summary = $wpdb->get_var($wpdb->prepare("SELECT send_summary FROM ".$wpdb->prefix . 'symposium_config'));
	if ($send_summary == "on") {
		// Calculate yesterday			
		$startTime = mktime(0, 0, 0, date('m'), date('d')-1, date('Y'));
		$endTime = mktime(23, 59, 59, date('m'), date('d')-1, date('Y'));
		
		// Get all new topics from previous period
		$topics_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix.'symposium_topics'." WHERE topic_parent = 0 AND UNIX_TIMESTAMP(topic_date) >= ".$startTime." AND UNIX_TIMESTAMP(topic_date) <= ".$endTime));

		if ($topics_count > 0) {

			// Get Forum URL
			$forum_url = $wpdb->get_var($wpdb->prepare("SELECT forum_url FROM ".$wpdb->prefix . 'symposium_config'));

			$body = "";
			
			$categories = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix.'symposium_cats'." ORDER BY listorder"); 
			if ($categories) {
				foreach ($categories as $category) {
					
					$shown_category = false;
					$topics = $wpdb->get_results("
						SELECT tid, topic_subject, topic_parent, topic_post, topic_date, display_name, topic_category 
						FROM ".$wpdb->prefix.'symposium_topics'." INNER JOIN ".$wpdb->prefix.'users'." ON ".$wpdb->prefix.'symposium_topics'.".topic_owner = ".$wpdb->prefix.'users'.".ID 
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
							$body .= "<li><strong><a href='".$forum_url."?cid=".$category->cid."&show=".$topic->tid."'>".stripslashes($topic->topic_subject)."</a></strong>";
							$body .= " started by ".$topic->display_name.":<br />";																
							$body .= stripslashes($topic->topic_post);
							$body .= "</li>";
						}
						$body .= "</ol>";
					}

					$replies = $wpdb->get_results("
						SELECT tid, topic_subject, topic_parent, topic_post, topic_date, display_name, topic_category 
						FROM ".$wpdb->prefix.'symposium_topics'." INNER JOIN ".$wpdb->prefix.'users'." ON ".$wpdb->prefix.'symposium_topics'.".topic_owner = ".$wpdb->prefix.'users'.".ID 
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
							$body .= " <a href='".$forum_url."?cid=".$category->cid."&show=".$topic->tid."'>".__('View topic', 'wp-symposium')."...</a>";
							$body .= "<br />";
							$body .= "<br />";
						}						
					}	
				}
			}
			
			$body .= "<p>".__("You can stop receiving these emails at", "wp-symposium")." <a href='".$forum_url."'>".$forum_url."</a>.</p>";
			
			$users = $wpdb->get_results("SELECT DISTINCT user_email FROM ".$wpdb->prefix.'users'." u INNER JOIN ".$wpdb->prefix.'symposium_usermeta'." m ON u.ID = m.uid WHERE m.forum_digest = 'on'"); 
			if ($users) {
				foreach ($users as $user) {
					if(symposium_sendmail($user->user_email, __('Daily Forum Digest', 'wp-symposium'), $body)) {
						update_option("symposium_notification_triggercount",get_option("symposium_notification_triggercount")+1);
					}			
				}
			}

			// Report back to monitor the service - you can delete the following lines if you do not want this support
			// but in providing this anonymous information you can help us to help you
			if ($topics_count > 4) {
				$mail_to = 'info@wpsymposium.com';
				$forum_url = $wpdb->get_var($wpdb->prepare("SELECT forum_url FROM ".$config));				
				if(symposium_sendmail($mail_to, 'Forum Digest Report: '.get_site_url(), get_site_url().'<br />'.$forum_url.'<br /><br />'.$topics_count.' post(s)')) {
					update_option("symposium_notification_triggercount",get_option("symposium_notification_triggercount")+1);
				}
			}

		}
	}

}

/* ====================================================== PHP FUNCTIONS ====================================================== */

// Redirect user after log in
function symposium_redirect_login() {
	global $wpdb;

	if (!(function_exists('symposium_login'))) {
		
		$redirect = $wpdb->get_row($wpdb->prepare("SELECT enable_redirects, login_redirect, login_redirect_url FROM ".$wpdb->prefix . 'symposium_config'));
	
		if ($redirect->enable_redirects == 'on') {
			
			$url = "";	

			switch($redirect->login_redirect) {			
				case "Profile Wall":
					$url = symposium_get_url('profile');	
					break;
				case "Profile Settings":
					$url = symposium_get_url('profile')."?view=settings";	
					break;
				case "Profile Personal":
					$url = symposium_get_url('profile')."?view=personal";	
					break;
				case "Mail":
					$url = symposium_get_url('mail');	
					break;
				case "Forum":
					$url = symposium_get_url('forum');	
					break;
				case "Custom":
					$url = $redirect->login_redirect_url;	
					break;
				default:
					if (function_exists('symposium_login')) {
						$url = symposium_get_url('profile');	
					} else {
						$url = "/";
					}
					break;
			}

			if ($url != '') {
				wp_redirect($url);	
				exit;
			}

		}
	
	}
}

// Redirect user after logging out
function symposium_redirect_logout() {
	global $wpdb;
	$redirect = $wpdb->get_var($wpdb->prepare("SELECT enable_redirects, logout_redirect, logout_redirect_url FROM ".$wpdb->prefix . 'symposium_config'));

	if ( ($redirect->enable_redirects == 'on') && ($redirect->logout_redirect != "WordPress default") ) {
		switch($redirect) {			
			default:
				wp_redirect($redirect->logout_redirect_url);	
				exit;
		}
	}
}

// Update user activity on page load
function symposium_lastactivity() {
   	global $wpdb, $current_user;
	wp_get_current_user();
			
	if (is_user_logged_in()) {
		update_symposium_meta($current_user->ID, 'last_activity', "'".date("Y-m-d H:i:s")."'");
	}
	
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

// Hook for URL redirect
function symposium_redirect($buffer){ 
	
	global $wpdb;
	
	$seo = $wpdb->get_var($wpdb->prepare("SELECT seo FROM ".$wpdb->prefix . 'symposium_config'));
	
	// check for forum redirect
		
	if ($seo == "on") {
	
		$thispage = get_permalink();
	
		if (function_exists('symposium_forum')) {
				
			$forum_url = $wpdb->get_var($wpdb->prepare("SELECT forum_url FROM ".$wpdb->prefix."symposium_config"));
			if ($forum_url[strlen($forum_url)-1] != '/') { $forum_url .= '/'; }
			
			$parsed_url=parse_url($_SERVER['REQUEST_URI']);
			
			if ( substr(get_site_url().$parsed_url['path'], 0, strlen($forum_url)) == $forum_url ) {
				
				$path = $parsed_url['path'];
				if ($path[strlen($path)-1] != '/') { $path .= '/'; }
				$paths = explode('/',$path);
				$query = $parsed_url['query'];
				
				$max = count($paths);
				$id = $paths[$max-4];
				$category = $paths[$max-3];
				$topic = $paths[$max-2];
				if (is_numeric($category)) {
					// Categories not in use
					$id = $category;
					$category = "-";
				}
						
				// If an ID was passed	
				if ($id != '') {
					if (!(isset($_GET['show']))) {
						// Just show category
						header("Location: ".$forum_url."?cid=".$id);
						exit;					
					} else {				
						// Try getting category for id
						$cat_id = $wpdb->get_var($wpdb->prepare("SELECT topic_category FROM ".$wpdb->prefix."symposium_topics"." WHERE tid = ".$id));
						if ($cat_id != 0) {
							header("Location: ".$forum_url."?cid=".$cat_id."&show=".$id);
							exit;
						} else {
							header("Location: ".$forum_url."?cid=&show=".$id);
							exit;
						}
					}
				}
			}
		}
		
	}
	
    return $buffer;
}

// Hook for adding unread mail, etc
function symposium_unread($buffer){ 
	
   	global $wpdb, $current_user;
	wp_get_current_user();

	// Unread mail
	$unread_in = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix.'symposium_mail'." WHERE mail_to = ".$current_user->ID." AND mail_in_deleted != 'on' AND mail_read != 'on'");
	if ($unread_in > 0) {
		$buffer = str_replace("%m", "(".$unread_in.")", $buffer);
	} else {
		$buffer = str_replace("%m", "", $buffer);
	}
	
    // Pending friends
	$pending_friends = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."symposium_friends f WHERE f.friend_to = ".$current_user->ID." AND f.friend_accepted != 'on'");

	if ($pending_friends > 0) {
		$buffer = str_replace("%f", "(".$pending_friends.")", $buffer);
	} else {
		$buffer = str_replace("%f", "", $buffer);
	}

    return $buffer;
    
}

function symposium_admin_check() {
	global $wpdb;
	$urls = $wpdb->get_row($wpdb->prepare("SELECT forum_url, mail_url, login_url, members_url, register_url, profile_url FROM ".$wpdb->prefix . 'symposium_config'));
	if ( ($urls->forum_url == "Important: Please update!") || ($urls->members_url == "Important: Please update!") || ($urls->login_url == "Important: Please update!") || ($urls->register_url == "Important: Please update!") || ($urls->mail_url == "Important: Please update!") || ($urls->profile_url == "Important: Please update!") ) {
		echo "<div class='updated'><p><strong>".__("Important! Please set URLs in WP Symposium Options immediately (set to none if you are not using a particular plugin)", "wp-symposium").".</strong></p></div>";
	}
	
	// Check that user meta matches user table and delete to synchronise
	$sql = "SELECT uid
			FROM ".$wpdb->prefix."symposium_usermeta m 
			LEFT JOIN ".$wpdb->prefix."users u 
			ON m.uid = u.ID 
			WHERE u.ID IS NULL;";
			
	$missing_users = $wpdb->get_results($sql); 
	if ($missing_users) {
		foreach ($missing_users as $missing) {
			$sql = "DELETE FROM ".$wpdb->prefix."symposium_usermeta WHERE uid = ".$missing->uid;
			$wpdb->query($sql); 
		}
	}	
}

/* ====================================================== PAGE LOADED FUNCTIONS ====================================================== */

function symposium_replace(){
	ob_start();
	ob_start('symposium_unread');
	ob_start('symposium_smilies');
	ob_start('symposium_redirect');
}

/* ====================================================== ADMIN FUNCTIONS ====================================================== */

// Add Stylesheet
function add_symposium_stylesheet() {
	global $wpdb;

	if (!is_admin()) {

	    // Check to see if there is a theme css instead
	    $myStyleUrl = WP_PLUGIN_URL . '/wp-symposium/css/symposium.css';
	    $myStyleFile = WP_PLUGIN_DIR . '/wp-symposium/css/symposium.css';
	    if ( file_exists($myStyleFile) ) {
	        wp_register_style('symposium_StyleSheet', $myStyleUrl);
	        wp_enqueue_style('symposium_StyleSheet');
	    }
	    
		// Load custom CSS if exists
	    $myStyleUrl = get_bloginfo('stylesheet_directory')."/my-symposium.css";
	    $myStyleFile = TEMPLATEPATH."/my-symposium.css";
	    if ( file_exists($myStyleFile) ) {
	        wp_register_style('symposium_my-StyleSheet', $myStyleUrl);
	        wp_enqueue_style('symposium_my-StyleSheet');
	    }
	    
	    // Load other CSS's
	    
		wp_register_style('symposium_jcrop-css', WP_PLUGIN_URL.'/wp-symposium/css/jquery.Jcrop.css');
		wp_enqueue_style('symposium_jcrop-css');
		
		wp_register_style('symposium_uploadify-css', WP_PLUGIN_URL.'/uploadify/uploadify.css');
		wp_enqueue_style('symposium_uploadify-css');
	    

	}

	// Only load if chosen
	$jquery = $wpdb->get_var($wpdb->prepare("SELECT jquery FROM ".$wpdb->prefix . 'symposium_config'));
	if ($jquery=="on" && !is_admin()) {

        wp_register_style('symposium_jquery-ui-css', WP_PLUGIN_URL.'/wp-symposium/css/jquery-ui.css');
        wp_enqueue_style('symposium_jquery-ui-css');

	}    

	// Notices
	echo "<div class='symposium_notice' style='display:none; z-index:999999;'><img src='".WP_PLUGIN_URL."/wp-symposium/images/busy.gif' /> ".__('Saving...', 'wp-symposium')."</div>";
	echo "<div class='symposium_pleasewait' style='display:none; z-index:999999;'><img src='".WP_PLUGIN_URL."/wp-symposium/images/busy.gif' /> ".__('Please Wait...', 'wp-symposium')."</div>";
	
}

// Language files
function symposium_languages() {
		$plugin_path = dirname(plugin_basename(__FILE__)) . '/language';
		load_plugin_textdomain( 'wp-symposium', false, $plugin_path );		
}

// Add jQuery and jQuery scripts
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
	 		wp_enqueue_script('jquery-ui-custom', $plugin.'/js/jquery-ui-1.8.8.custom.min.js', array('jquery'));	
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
function symposium_scriptsAction()
{

	$symposium_plugin_url = WP_PLUGIN_URL.'/wp-symposium/';
	$symposium_plugin_path = str_replace("http://".$_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"], "", $symposium_plugin_url);
 
	global $wpdb, $current_user;
	wp_get_current_user();

	// Config
	$config = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."symposium_config"));
	
	// Mail
	$view = $_GET['view'];
	if ( !isset($_GET['view']) ) { $view = "in"; } 

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
	
	// Forum
	if (isset($_GET['show'])) {
		$show_tid = $_GET['show']*1;
	} else {
		$show_tid = 0;
		if (isset($_POST['tid'])) { $show_tid = $_POST['tid']*1; }
	}
	$cat_id = 0;
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
		
	// Load Symposium JS supporting scrtipts		
	
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
		'inactive' => $config->online,
		'forum_url' => $config->forum_url,
		'mail_url' => $config->mail_url,
		'profile_url' => $config->profile_url,
		'avatar_url' => $config->avatar_url,
		'offline' => $config->offline,
		'use_chat' => $config->use_chat,
		'chat_polling' => $config->chat_polling,
		'bar_polling' => $config->bar_polling,
		'soundchat' => $config->soundchat,
		'sound' => $config->sound,
		'view' => $view,
		'show_tid' => $show_tid,
		'cat_id' => $cat_id,
		'current_user_id' => $current_user->ID,
		'current_user_page' => $page_uid,
		'widget_vote_yes' => $yes, 
		'widget_vote_no' => $no,
		'post' => $_GET['post']
	));


}


?>
