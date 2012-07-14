<?php
/*  Copyright 2010,2011,2012  Simon Goodchild  (info@wpsymposium.com)

	License: GPL3

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

/* ====================================================== ADMIN MENU ====================================================== */


function symposium_plugin_menu() {
	
	global $wpdb, $current_user;
	
	
	// User migration from wp_symposium_usermeta to wp_usermeta
	if (!get_option('symposium_done_usermeta_migration')) {
		
		$sql = "SELECT COUNT(*) FROM ".$wpdb->base_prefix."symposium_usermeta";
		$sum = $wpdb->get_var($wpdb->prepare($sql));
		$sql = "SELECT COUNT(*) FROM ".$wpdb->base_prefix."usermeta WHERE meta_key = 'symposium_extended_city'";
		$wum = $wpdb->get_var($wpdb->prepare($sql));
		
		if ($sum > $wum) {
			
			// Now start/continue migration					
			echo '<div style="font-family: arial, helvetica; font-size: 12px; width:50%; box-shadow: 0px 0px 8px #888888; padding:10px; margin-left:auto; margin-right: auto; margin-top:100px; border-radius:3px; border:1px solid #000; background-color:#eee">';
			
			echo '<div style="font-weight:bold; font-size:14px; margin-bottom:10px">'.__('WP Symposium user migration process - this must be completed, please let it finish!', 'wp-symposium').'</div>';
	
			$step = 10;
			$from = isset($_GET['from']) ? $_GET['from'] : 0;
			$to = isset($_GET['to']) ? $_GET['to'] : ($from+$step);
		
			$sql = "SELECT MAX(uid) FROM ".$wpdb->base_prefix."symposium_usermeta";
			$max_uid = $wpdb->get_var($wpdb->prepare($sql));
			
			$sql = "SELECT m.*, u.display_name FROM ".$wpdb->base_prefix."symposium_usermeta m 
			LEFT JOIN ".$wpdb->base_prefix."users u ON m.uid = u.ID 
			WHERE m.uid >= %d AND uid <= %d";
			$users = $wpdb->get_results($wpdb->prepare($sql, $from, $to));
			
			$to = ($max_uid > $to) ? $to : $max_uid;
			foreach ($users as $user) {
				
				update_user_meta($user->uid, 'symposium_forum_digest', get_symposium_meta($user->uid, 'forum_digest', true));
				update_user_meta($user->uid, 'symposium_notify_new_messages', get_symposium_meta($user->uid, 'notify_new_messages', true));
				update_user_meta($user->uid, 'symposium_notify_new_wall', get_symposium_meta($user->uid, 'forum_digest', true));
				update_user_meta($user->uid, 'symposium_extended_city', get_symposium_meta($user->uid, 'city', true));
				update_user_meta($user->uid, 'symposium_extended_country', get_symposium_meta($user->uid, 'country', true));
				update_user_meta($user->uid, 'symposium_dob_day', get_symposium_meta($user->uid, 'dob_day', true));
				update_user_meta($user->uid, 'symposium_dob_month', get_symposium_meta($user->uid, 'dob_month', true));
				update_user_meta($user->uid, 'symposium_dob_year', get_symposium_meta($user->uid, 'dob_year', true));
				update_user_meta($user->uid, 'symposium_share', get_symposium_meta($user->uid, 'share', true));
				update_user_meta($user->uid, 'symposium_last_activity', get_symposium_meta($user->uid, 'last_activity', true));
				update_user_meta($user->uid, 'symposium_status', get_symposium_meta($user->uid, 'status', true));
				update_user_meta($user->uid, 'symposium_visible', get_symposium_meta($user->uid, 'visible', true));
				update_user_meta($user->uid, 'symposium_wall_share', get_symposium_meta($user->uid, 'wall_share', true));
				update_user_meta($user->uid, 'symposium_widget_voted', get_symposium_meta($user->uid, 'widget_voted', true));
				update_user_meta($user->uid, 'symposium_profile_photo', get_symposium_meta($user->uid, 'profile_photo', true));
				update_user_meta($user->uid, 'symposium_forum_favs', get_symposium_meta($user->uid, 'forum_favs', true));
				update_user_meta($user->uid, 'symposium_trusted', get_symposium_meta($user->uid, 'trusted', true));
				update_user_meta($user->uid, 'symposium_facebook_id', get_symposium_meta($user->uid, 'facebook_id', true));
				update_user_meta($user->uid, 'symposium_last_login', get_symposium_meta($user->uid, 'last_login', true));
				update_user_meta($user->uid, 'symposium_previous_login', get_symposium_meta($user->uid, 'previous_login', true));
				update_user_meta($user->uid, 'symposium_forum_all', get_symposium_meta($user->uid, 'forum_all', true));
				update_user_meta($user->uid, 'symposium_signature', get_symposium_meta($user->uid, 'signature', true));
				update_user_meta($user->uid, 'symposium_rss_share', get_symposium_meta($user->uid, 'rss_share', true));
				update_user_meta($user->uid, 'symposium_plus_lat', get_symposium_meta($user->uid, 'plus_lat', true));
				update_user_meta($user->uid, 'symposium_plus_long', get_symposium_meta($user->uid, 'plus_long', true));
	 
				// Now update WP user meta for Extended Fields
				$fields = explode('[|]', get_symposium_meta($user->uid, 'extended', true));
				
				if ($fields) {
					foreach ($fields as $field) {
						$split = explode('[]', $field);
	
						if ( $split[0] != '') {
							
							$extension = $wpdb->get_row($wpdb->prepare("SELECT extended_name,extended_type FROM ".$wpdb->prefix."symposium_extended WHERE eid = ".$split[0]));
							$extension_slug = 'symposium_extended_slug_'.$split[0];
							
							if ($extension->extended_type == 'Checkbox') {
								$value = $split[1];
								if ($value == 'on') $value = true;
							}
							if ($extension->extended_type == 'List') {
								$value = $split[1];
							}
							if ($extension->extended_type == 'Text' || $extension->extended_type == 'Textarea') {
								$value = wpautop($split[1]);
								$value = str_replace('<p>', '', $value);
								$value = str_replace('</p>', '', $value);
								$value = trim($value, "' \t\n\r\0\x0B");
							}
							update_user_meta($user->uid, $extension_slug, $value);
						}
					}
				}
			}
			
			if ($to < $max_uid) {
				
				echo __('Progress', 'wp-symposium').': '.(floor($to/$max_uid*100)).'%<br />';
				echo '<div style="background-color:#fff; border:1px solid #000; width:100%; height:20px">';
				echo '<div style="background-color:#99f; width:'.(floor($to/$max_uid*100)).'%; height:20px;"></div>';
				echo '</div>';
				echo '<p><em>'.__('What is this migration?', 'wp-symposium').'</em></p>';
				echo '<p>'.__('Information about WP Symposium members was previously stored in a dedicated table ('.$wpdb->base_prefix.'symposium_usermeta) in the WordPress database.', 'wp-symposium').'</p>';
				echo '<p>'.__('After this migration, it will be stored in a WordPress table ('.$wpdb->base_prefix.'usermeta) which is more integrated and allows this information to be shared across your installation, with other plugins for example.', 'wp-symposium').'</p>';
				echo '<p>'.__('In addition, configuration options have been moved from a dedicated table ('.$wpdb->prefix.'symposium_config) to the core Wordpress options table ('.$wpdb->prefix.'options).', 'wp-symposium').'</p>';
				echo '<p>'.__('On WPMS installations, you should ensure that a network update is performed.', 'wp-symposium').'</p>';
				echo '<p>'.__('Thank you for your patience...', 'wp-symposium').'</p>';
	
				printf("<script>location.href='admin.php?page=symposium_debug&action=migrate_usermeta&from=".($to+1)."&to=".($to+$step+1)."'</script>");
				
			} else {
				
				// Finally add the slugs to symposium_extended
				symposium_alter_table("extended", "ADD", "extended_slug", "varchar(64)", "NOT NULL", "");
				$sql = "UPDATE ".$wpdb->base_prefix."symposium_extended SET extended_slug = CONCAT('slug_', eid)";
				$wpdb->query($sql);
	
				// Add a field to symposium_extended, that may hold a pointer to WP metadata
				symposium_alter_table("extended", "ADD", "wp_usermeta", "varchar(256)", "", "");
				$sql = "UPDATE ".$wpdb->base_prefix."symposium_extended SET wp_usermeta = ''";
				$wpdb->query($sql);
	
				// Add a field to symposium_extended, such that it can be readonly (for use with linking to wp_usermeta mostly)
				symposium_alter_table("extended", "ADD", "readonly", "varchar(2)", "", "''");
				
				// Done
				echo '<br />'.__('Migration complete. <a href="index.php">Close</a>', 'wp-symposium');
				printf("<script>location.href='index.php'</script>");
			}
			
			echo '</div>';		
		}
		
		// Update done flag to avoid repeats if users are deleted
		update_option('symposium_done_usermeta_migration', true);

	}
	
	
	// Act on any parameters, so menu counts are correct
	if (isset($_GET['action'])) {
		
		switch($_GET['action']) {
				
			case "post_del":
				if (isset($_GET['tid'])) {

					if (symposium_safe_param($_GET['tid'])) {

						// Get details
						$post = $wpdb->get_row( $wpdb->prepare("SELECT t.*, u.user_email FROM ".$wpdb->prefix."symposium_topics t LEFT JOIN ".$wpdb->base_prefix."users u ON t.topic_owner = u.ID WHERE tid = ".$_GET['tid']) );
	
						$body = "<span style='font-size:24px'>".__('Your forum post has been rejected by the moderator', 'wp-symposium').".</span>";
						if ($post->topic_parent == 0) { $body .= "<p><strong>".stripslashes($post->topic_subject)."</strong></p>"; }
						$body .= "<p>".stripslashes($post->topic_post)."</p>";
						$body = str_replace(chr(13), "<br />", $body);
						$body = str_replace("\\r\\n", "<br />", $body);
						$body = str_replace("\\", "", $body);
							
						// Email author to let them know it was deleted
						symposium_sendmail($post->user_email, __('Forum Post Rejected', 'wp-symposium'), $body);

						// Update
						$wpdb->query( $wpdb->prepare( "DELETE FROM ".$wpdb->prefix."symposium_topics WHERE tid = %d", $_GET['tid'] ) );

					} else {
						echo "BAD PARAMETER PASSED: ".$_GET['tid'];
					}
					
				}
				break;

			case "post_approve":
				if (isset($_GET['tid'])) {

					$forum_url = symposium_get_url('forum');
					$group_url = symposium_get_url('group');
					$q = symposium_string_query($forum_url);		
					
					if (symposium_safe_param($_GET['tid'])) {

						// Update
						$wpdb->query( $wpdb->prepare( "UPDATE ".$wpdb->prefix."symposium_topics SET topic_approved = 'on' WHERE tid = %d", $_GET['tid'] ) );
						
						// Get details
						$post = $wpdb->get_row( $wpdb->prepare("SELECT t.*, u.user_email, u.display_name FROM ".$wpdb->prefix."symposium_topics t LEFT JOIN ".$wpdb->base_prefix."users u ON t.topic_owner = u.ID WHERE tid = ".$_GET['tid']) );
	
						$body = "<span style='font-size:24px'>".__('Your forum post has been approved by the moderator', 'wp-symposium').".</span>";
						if ($post->topic_parent == 0) { $body .= "<p><strong>".stripslashes($post->topic_subject)."</strong></p>"; }
						$body .= "<p>".stripslashes($post->topic_post)."</p>";
						$url = $forum_url.$q."cid=".$post->topic_category."&show=".$_GET['tid'];
						$body .= "<p><a href='".$url."'>".$url."</a></p>";
						$body = str_replace(chr(13), "<br />", $body);
						$body = str_replace("\\r\\n", "<br />", $body);
						$body = str_replace("\\", "", $body);
						
						// Work out URL
						$parent = $wpdb->get_row($wpdb->prepare("SELECT tid, topic_subject FROM ".$wpdb->prefix."symposium_topics WHERE tid = ".$post->topic_parent));
						if ($post->topic_group == 0) {	

							if ($post->topic_parent == 0) {					
								$url = $forum_url.$q."cid=".$post->topic_category."&show=".$_GET['tid'];
							} else {
								$url = $forum_url.$q."cid=".$post->topic_category."&show=".$parent->tid;
							}	
						
						} else {
							
							if ($post->topic_parent == 0) {					
								$url = $group_url.$q."gid=".$post->topic_group."&cid=".$post->topic_category."&show=".$_GET['tid'];
							} else {
								$url = $group_url.$q."gid=".$post->topic_group."&cid=".$post->topic_category."&show=".$parent->tid;
							}							
						
						}						

						// Email author to let them know
						symposium_sendmail($post->user_email, __('Forum Post Approved', 'wp-symposium'), $body);
	
						// Email people who want to know and prepare body (and post activity comment)
						if ($post->topic_parent > 0) {						
							$body = "<span style='font-size:24px'>".$parent->topic_subject."</span><br /><br />";
							$body .= "<p>".$post->display_name." ".__('replied', 'wp-symposium')."...</p>";
						} else {
							$body = "<span style='font-size:24px'>".$post->topic_subject."</span><br /><br />";
							$body .= "<p>".$post->display_name." ".__('started', 'wp-symposium')."...</p>";
							$post_url = __('Started a new forum topic:', 'wp-symposium').' <a href="'.$url.'">'.$post->topic_subject.'</a>';
							do_action('symposium_forum_newtopic_hook', $post->topic_owner, $post->display_name, $post->topic_owner, $post_url, 'forum', $_GET['tid']);	
						}
						
						$body .= "<p>".$post->topic_post."</p>";
						$body .= "<p>".$url."</p>";
						$body = str_replace(chr(13), "<br />", $body);
						$body = str_replace("\\r\\n", "<br />", $body);
						$body = str_replace("\\", "", $body);

						$email_list = '0,';				
						if ($post->topic_group == 0) {	
							
							// Main Forum			
												
							if ($post->topic_parent > 0) {
								$query = $wpdb->get_results("
									SELECT u.ID, u.user_email
									FROM ".$wpdb->base_prefix."users u RIGHT JOIN ".$wpdb->prefix."symposium_subs s ON s.uid = u.ID 
									WHERE tid = ".$parent->tid);
							} else {
								$query = $wpdb->get_results("
									SELECT u.ID, u.user_email
									FROM ".$wpdb->base_prefix."users u RIGHT JOIN ".$wpdb->prefix."symposium_subs s ON s.uid = u.ID 
									WHERE cid = ".$post->topic_category);
							}
														
							if ($query) {						
								foreach ($query as $user) {		
									// Filter to allow further actions to take place
									if ($post->topic_parent > 0) {
										apply_filters ('symposium_forum_newreply_filter', $user->ID, $post->topic_owner, $post->display_name, $url);								
									} else {
										apply_filters ('symposium_forum_newtopic_filter', $user->ID, $post->topic_owner, $post->display_name, $url);
									}										

									// Keep track of who sent to so far
									$email_list .= $user->ID.',';

									symposium_sendmail($user->user_email, __('New Forum Post', 'wp-symposium'), $body);							
								}
							}
							
						} else {
							
							// Group Forum
							$group_name = $wpdb->get_var($wpdb->prepare("SELECT name FROM ".$wpdb->base_prefix."symposium_groups WHERE gid = %d", $post->topic_group));
			
							$sql = "SELECT ID, user_email FROM ".$wpdb->base_prefix."users u 
							LEFT JOIN ".$wpdb->prefix."symposium_group_members g ON u.ID = g.member_id 
							WHERE u.ID > 0 AND g.group_id = %d AND u.ID != %d";
			
							$members = $wpdb->get_results($wpdb->prepare($sql, $post->topic_group, $current_user->ID));
			
							if ($members) {
								foreach ($members as $member) {
									if ($post->topic_parent > 0) {
										apply_filters ('symposium_forum_newreply_filter', $member->ID, $post->topic_owner, $post->display_name, $url);								
									} else {
										apply_filters ('symposium_forum_newtopic_filter', $member->ID, $post->topic_owner, $post->display_name, $url);
									}										

									// Keep track of who sent to so far
									$email_list .= $member->ID.',';

									symposium_sendmail($member->user_email, __('New Group Forum Post', 'wp-symposium'), $body);							
								}
							}
						}							

						// Now send to everyone who wants to know about all new topics and replies
						$email_list .= '0';
						$sql = "SELECT ID,user_email FROM ".$wpdb->base_prefix."users u 
							WHERE ID != %d AND 
							ID NOT IN (".$email_list.")";
						$list = $wpdb->get_results($wpdb->prepare($sql, $current_user->ID));
		
						if ($list) {
							
							$list_array = array();
							foreach ($list as $item) {
				
								if (get_symposium_meta($item->ID, 'forum_all') == 'on') {
									$add = array (	
										'ID' => $item->ID,
										'user_email' => $item->user_email
									);						
									array_push($list_array, $add);
								}
								
							}
							$query = sub_val_sort($list_array, 'last_activity');	
							
						} else {
						
							$query = false;
							
						}	

						// Get list of permitted roles for this topic category
						$sql = "SELECT level FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d";
						$level = $wpdb->get_var($wpdb->prepare($sql, $post->topic_category));
						$cat_roles = unserialize($level);
							
						if ($query) {						
							foreach ($query as $user) {	

								// Get role of recipient user
								$the_user = get_userdata( $user->ID );
								$capabilities = $the_user->{$wpdb->prefix . 'capabilities'};
		
								if ( !isset( $wp_roles ) )
									$wp_roles = new WP_Roles();
									
								$user_role = 'NONE';
								foreach ( $wp_roles->role_names as $role => $name ) {
									
									if ( array_key_exists( $role, $capabilities ) ) {
										$user_role = $role;
									}
								}
								
								// Check in this topics category level
								if (strpos(strtolower($cat_roles), 'everyone,') !== FALSE || strpos(strtolower($cat_roles), $user_role.',') !== FALSE) {	 
		
									// Filter to allow further actions to take place
									apply_filters ('symposium_forum_newreply_filter', $user->ID, $current_user->ID, $current_user->display_name, $url);

									// Send mail
									symposium_sendmail($user->user_email, __('New Forum Post', 'wp-symposium'), $body);							
									
								}
							}
						}
											
					} else {
						echo "BAD PARAMETER PASSED: ".$_GET['tid'];
					}

				}
				break;

		}
	}


	// Build menu
	$count = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix.'symposium_topics'." WHERE topic_approved != 'on'"); 
	if ($count > 0) {
		$count1 = "<span class='update-plugins' title='".$count." comments to moderate'><span class='update-count'>".$count."</span></span>";
		$count2 = " (".$count.")";
	} else {
		$count1 = "";
		$count2 = "";
	}

	// Build menus
	if (wps_is_wpmu()) {
		// WPMS
		add_menu_page('Symposium','Symposium'.$count1, 'manage_options', 'symposium_debug', 'symposium_plugin_debug', '', 7); 
		add_submenu_page('symposium_debug', __('Installation', 'wp-symposium'), __('Installation', 'wp-symposium'), 'manage_options', 'symposium_debug', 'symposium_plugin_debug');
		add_submenu_page('symposium_debug', __('Welcome message', 'wp-symposium'), __('Welcome message', 'wp-symposium'), 'manage_options', 'symposium_welcome', 'symposium_plugin_welcome');
		add_submenu_page('symposium_debug', __('Settings', 'wp-symposium'), __('Settings', 'wp-symposium'), 'manage_options', 'symposium_settings', 'symposium_plugin_settings');
		add_submenu_page('symposium_debug', __('Templates', 'wp-symposium'), __('Templates', 'wp-symposium'), 'manage_options', 'symposium_templates', 'symposium_plugin_templates');
		add_submenu_page('symposium_debug', __('Styles', 'wp-symposium'), __('Styles', 'wp-symposium'), 'manage_options', 'symposium_styles', 'symposium_plugin_styles');
		
		if (function_exists('symposium_profile')) {
			add_submenu_page('symposium_debug', __('Profile', 'wp-symposium'), __('Profile', 'wp-symposium'), 'manage_options', 'symposium_profile', 'symposium_plugin_profile');
		}
		if (function_exists('symposium_forum')) {
			add_submenu_page('symposium_debug', __('Forum', 'wp-symposium'), __('Forum', 'wp-symposium'), 'manage_options', 'symposium_forum', 'symposium_plugin_forum');
			add_submenu_page('symposium_debug', __('Forum Categories', 'wp-symposium'), __('Forum Categories', 'wp-symposium'), 'manage_options', 'symposium_categories', 'symposium_plugin_categories');
			add_submenu_page('symposium_debug', __('Forum Posts', 'wp-symposium'), sprintf(__('Forum Posts %s', 'wp-symposium'), $count2), 'manage_options', 'symposium_moderation', 'symposium_plugin_moderation');
		}
		if (function_exists('add_notification_bar')) {
			add_submenu_page('symposium_debug', __('Panel', 'wp-symposium'), __('Panel', 'wp-symposium'), 'manage_options', 'symposium_bar', 'symposium_plugin_bar');
		}
		if (function_exists('symposium_members')) {
			add_submenu_page('symposium_debug', __('Member Directory', 'wp-symposium'), __('Member Directory', 'wp-symposium'), 'manage_options', 'symposium_members_menu', 'symposium_members_menu');
		}
		if (function_exists('symposium_mail')) {
			add_submenu_page('symposium_debug', __('Mail', 'wp-symposium'), __('Mail', 'wp-symposium'), 'update_core', 'symposium_mail_menu', 'symposium_mail_menu');
			add_submenu_page('symposium_debug', __('Mail Messages', 'wp-symposium'), __('Mail Messages', 'wp-symposium'), 'update_core', 'symposium_mail_messages_menu', 'symposium_mail_messages_menu');
		}
	} else {
		// Single intallation
		add_menu_page('Symposium','Symposium'.$count1, 'manage_options', 'symposium_debug', 'symposium_plugin_debug', '', 7); 
		add_submenu_page('symposium_debug', __('Installation', 'wp-symposium'), __('Installation', 'wp-symposium'), 'manage_options', 'symposium_debug', 'symposium_plugin_debug');
		add_submenu_page('symposium_debug', __('Welcome message', 'wp-symposium'), __('Welcome message', 'wp-symposium'), 'manage_options', 'symposium_welcome', 'symposium_plugin_welcome');
		add_submenu_page('symposium_debug', __('Settings', 'wp-symposium'), __('Settings', 'wp-symposium'), 'manage_options', 'symposium_settings', 'symposium_plugin_settings');
		add_submenu_page('symposium_debug', __('Templates', 'wp-symposium'), __('Templates', 'wp-symposium'), 'manage_options', 'symposium_templates', 'symposium_plugin_templates');
		add_submenu_page('symposium_debug', __('Styles', 'wp-symposium'), __('Styles', 'wp-symposium'), 'manage_options', 'symposium_styles', 'symposium_plugin_styles');
		
		if (function_exists('symposium_profile')) {
			add_submenu_page('symposium_debug', __('Profile', 'wp-symposium'), __('Profile', 'wp-symposium'), 'manage_options', 'symposium_profile', 'symposium_plugin_profile');
		}
		if (function_exists('symposium_forum')) {
			add_submenu_page('symposium_debug', __('Forum', 'wp-symposium'), __('Forum', 'wp-symposium'), 'manage_options', 'symposium_forum', 'symposium_plugin_forum');
			add_submenu_page('symposium_debug', __('Forum Categories', 'wp-symposium'), __('Forum Categories', 'wp-symposium'), 'manage_options', 'symposium_categories', 'symposium_plugin_categories');
			add_submenu_page('symposium_debug', __('Forum Posts', 'wp-symposium'), sprintf(__('Forum Posts %s', 'wp-symposium'), $count2), 'manage_options', 'symposium_moderation', 'symposium_plugin_moderation');
		}
		if (function_exists('add_notification_bar')) {
			add_submenu_page('symposium_debug', __('Panel', 'wp-symposium'), __('Panel', 'wp-symposium'), 'manage_options', 'symposium_bar', 'symposium_plugin_bar');
		}
		if (function_exists('symposium_members')) {
			add_submenu_page('symposium_debug', __('Member Directory', 'wp-symposium'), __('Member Directory', 'wp-symposium'), 'manage_options', 'symposium_members_menu', 'symposium_members_menu');
		}
		if (function_exists('symposium_mail')) {
			add_submenu_page('symposium_debug', __('Mail', 'wp-symposium'), __('Mail', 'wp-symposium'), 'manage_options', 'symposium_mail_menu', 'symposium_mail_menu');
			add_submenu_page('symposium_debug', __('Mail Messages', 'wp-symposium'), __('Mail Messages', 'wp-symposium'), 'manage_options', 'symposium_mail_messages_menu', 'symposium_mail_messages_menu');
		}
	}
	do_action('symposium_admin_menu_hook');
}

function symposium_plugin_welcome() {

			?>
			<div id="wps-welcome-panel" class="welcome-panel" style="margin-top:40px">
				<div id="motd" class="welcome-panel-content">
	
				<form action="index.php" method="post">
				<div style="float:right;margin:-20px 0 0 -15px">
					<input type="submit" class="button-primary" value="<?php _e("Dismiss"); ?>" />
					<input type="hidden" name="symposium_hide_motd" value="Y" />
					<?php wp_nonce_field('symposium_hide_motd_nonce','symposium_hide_motd_nonce'); ?>
				</div>
				</form>
		    
				<div style="float:left; width:180px; text-align:center;">
				<img src="<?php echo WP_PLUGIN_URL; ?>/wp-symposium/images/logo_small.png" /><br />
				<strong>Version <?php echo WPS_VER; ?></strong>
				</div>
			    <h3><?php _e("WP Symposium", "wp-symposium"); ?></h3>		    
			    
				<p class="about-description">
				<?php echo sprintf(__( 'Thank you for installing WP Symposium, welcome aboard! Please visit the WP Symposium <a href="%s">Installation page</a> to complete your installation/upgrade.' ), "admin.php?page=symposium_debug"); ?>
			    <?php
			    $ver = str_replace('.', '-', WPS_VER);
			    if (strpos($ver, ' ') !== false) $ver = substr($ver, 0, strpos($ver, ' ')); 
			    echo ' '.sprintf(__('For more information and release notes, check out the <a href="%s" target="_blank">WP Symposium blog</a>.', 'wp-symposium'), 'ttp://www.wpsymposium.com/2012/07/release-notes-for-v'.$ver);
				echo ' '.__( 'And remember, drink tea, tea is good.' );
				?>
			    </p>
	
				<div class="welcome-panel-column-container">
					<div class="welcome-panel-column">
						<h4><span class="icon16 icon-settings"></span> <?php _e('New in this release', 'wp-symposium') ?></h4>
						<p><?php sprintf(_e( 'What\'s new in this release? For more detail and release notes, head on over to the <a href="%s" target="_blank">WP Symposium blog</a>.', 'wp-symposium' ), "http://www.wpsymposiu,.com/blog"); ?></p>
						<ul>
						<li><?php _e('Newly added profile activity images can be &quot;zoomed&quot;', 'wp-symposium'); ?></li>
						<li><?php _e('Choose the order of event listings', 'wp-symposium'); ?></li>
						<li><?php _e('Events checked that they start before they end!', 'wp-symposium'); ?></li>
						<li><?php _e('Option to hide Forum context menu', 'wp-symposium'); ?></li>
						<li><?php _e('Forum inline images no longer case sensitive', 'wp-symposium'); ?></li>
						<li><?php _e('Temporarily hide forum reply field after use', 'wp-symposium'); ?></li>
						<li><?php _e('Put mail box/message template back...', 'wp-symposium'); ?></li>
						<li><?php _e('Fixes to extended fields migration', 'wp-symposium'); ?></li>
						<li><?php _e('Added clear to notification drop-down to remove all items', 'wp-symposium'); ?></li>
						<li><?php _e('Integrity check now removes orphaned friendships', 'wp-symposium'); ?></li>
						<li><?php _e('This welcome screen :)', 'wp-symposium'); ?></li>
						</ul>
					</div>
					<div class="welcome-panel-column">
						<h4><span class="icon16 icon-page"></span> <?php _e( 'Getting Started' ); ?></h4>
						<p><?php _e( 'First time setting up WP Symposium? Try the following to get you up and running.', 'wp-symposium' ); ?></p>
						<ul>
						<li><?php echo sprintf(	__( '<a href="%s">Activate some WPS plugins</a>' ), esc_url( admin_url('plugins.php') ) ); ?></li>
						<li><?php echo sprintf( __( '<a href="%s">Add WPS to your site pages</a>' ), esc_url( admin_url('admin.php?page=symposium_debug') ) ); ?></li>
						<li><?php echo sprintf( __( '<a href="%s">Check your Settings</a>' ), esc_url( admin_url('admin.php?page=symposium_settings') ) ); ?></li>
						<li><?php echo sprintf( __( '<a href="%s">Pick a color scheme</a>' ), esc_url( admin_url('admin.php?page=symposium_styles') ) ); ?></li>
						<li><?php echo sprintf( __( '<a href="%s" target="_blank">Take a look at the WPS videos</a>' ), esc_url('http://www.youtube.com/user/wpsymposium?feature=watch') ); ?> <img src='<?php echo WP_PLUGIN_URL; ?>/wp-symposium/images/new.png' title='New!' alt='New feature' /></li>
						<li><?php echo sprintf( __( '<a href="%s" target="_blank">Check out WPS Wiki setup page</a>' ), esc_url( 'http://www.wpswiki.com/index.php?title=Setting_up_for_the_first_time') ); ?></li>
						<li><?php echo sprintf(	__( '<a href="%s" target="_blank">Download the admin guide (work in progress!)</a>' ), esc_url( 'https://dl.dropbox.com/u/49355018/wps.pdf' ) ); ?></li>
						<li><?php echo sprintf( __( '<a href="%s" target="_blank">Upgrade to Bronze membership</a>' ), esc_url( 'http://www.wpsymposium.com/membership') ); ?></li>
						<li><?php echo sprintf(	__( '<a href="%s">Have fun exploring the options via the menu items</a>' ), esc_url( admin_url('admin.php?page=symposium_debug') ) ); ?></li>
						</ul>
					</div>
					<div class="welcome-panel-column welcome-panel-last">
						<h4><span class="icon16 icon-appearance"></span> <?php _e( 'Something not right?', 'wp-symposium' ); ?></h4>
						<p><?php _e( 'Got a problem? Sorry to hear that, here\'s where you can get help.', 'wp-symposium' ); ?></p>
						<ul>
						<li><?php echo sprintf(	__( '<a href="%s" target="_blank">Frequently Asked Questions</a>' ), esc_url( 'http://www.wpsymposium.com/faqs' ) ); ?></li>
						<li><?php echo sprintf( __( '<a href="%s" target="_blank">Try this first!</a>' ), esc_url('http://www.wpswiki.com/index.php?title=Try_this_first') ); ?></li>
						<li><?php echo sprintf( __( '<a href="%s" target="_blank">Read the tutorials</a>' ), esc_url('http://www.wpsymposium.com/tutorials') ); ?></li>
						<li><?php echo sprintf(	__( '<a href="%s">Download the admin guide (work in progress!)</a>' ), esc_url( 'https://dl.dropbox.com/u/49355018/wps.pdf' ) ); ?></li>
						<li><?php echo sprintf( __( '<a href="%s" target="_blank">Visit the WPS Forum</a>' ), esc_url('http://www.wpsymposium.com/discuss') ); ?></li>
						<li><?php echo sprintf(	__( '<a href="%s">Consider Silver Membership</a>' ), esc_url( 'http://www.wpsymposium.com/membership' ) ); ?></li>
						</ul>
					</div>
				</div>
	
			</div>		 
		</div>
		<?php

}

function symposium_plugin_templates() {
	
	global $wpdb;
	if (isset($_POST['profile_header_textarea'])) {
		update_option('symposium_template_profile_header', str_replace(chr(13), "[]", $_POST['profile_header_textarea']));
	}
	if (isset($_POST['profile_body_textarea'])) {
		update_option('symposium_template_profile_body', str_replace(chr(13), "[]", $_POST['profile_body_textarea']));
	}
	if (isset($_POST['page_footer_textarea'])) {
		if ($_POST['page_footer_textarea'] == "") {
			update_option('symposium_template_page_footer', str_replace(chr(13), "[]", "<!-- Powered by WP Symposium v".get_option("symposium_version")." -->"));
		} else {
			update_option('symposium_template_page_footer', str_replace(chr(13), "[]", $_POST['page_footer_textarea']));
		}
	}
	if (isset($_POST['email_textarea'])) {
		update_option('symposium_template_email', str_replace(chr(13), "[]", $_POST['email_textarea']));
	}
	if (isset($_POST['template_mail_tray_textarea'])) {
		update_option('symposium_template_mail_tray', str_replace(chr(13), "[]", $_POST['template_mail_tray_textarea']));
	}
	if (isset($_POST['template_mail_message_textarea'])) {
		update_option('symposium_template_mail_message', str_replace(chr(13), "[]", $_POST['template_mail_message_textarea']));
	}
	if (isset($_POST['template_forum_header_textarea'])) {
		update_option('symposium_template_forum_header', str_replace(chr(13), "[]", $_POST['template_forum_header_textarea']));
	}
	if (isset($_POST['template_group_textarea'])) {
		update_option('symposium_template_group', str_replace(chr(13), "[]", $_POST['template_group_textarea']));
	}
	if (isset($_POST['template_forum_category_textarea'])) {
		update_option('symposium_template_forum_category', str_replace(chr(13), "[]", $_POST['template_forum_category_textarea']));
	}
	if (isset($_POST['template_forum_topic_textarea'])) {
		update_option('symposium_template_forum_topic', str_replace(chr(13), "[]", $_POST['template_forum_topic_textarea']));
	}
	if (isset($_POST['template_group_forum_category_textarea'])) {
		// Not currently supported
	}
	if (isset($_POST['template_group_forum_topic_textarea'])) {
		update_option('symposium_template_group_forum_topic', str_replace(chr(13), "[]", $_POST['template_group_forum_topic_textarea']));
	}

	$template_profile_header = str_replace("[]", chr(13), stripslashes(get_option('symposium_template_profile_header')));
	$template_profile_body = str_replace("[]", chr(13), stripslashes(get_option('symposium_template_profile_body')));
	$template_page_footer = str_replace("[]", chr(13), stripslashes(get_option('symposium_template_page_footer')));
	$template_email = str_replace("[]", chr(13), stripslashes(get_option('symposium_template_email')));
	$template_mail_tray = str_replace("[]", chr(13), stripslashes(get_option('symposium_template_mail_tray')));
	$template_mail_message = str_replace("[]", chr(13), stripslashes(get_option('symposium_template_mail_message')));
	$template_forum_header = str_replace("[]", chr(13), stripslashes(get_option('symposium_template_forum_header')));
	$template_group = str_replace("[]", chr(13), stripslashes(get_option('symposium_template_group')));
	$template_forum_category = str_replace("[]", chr(13), stripslashes(get_option('symposium_template_forum_category')));
	$template_forum_topic = str_replace("[]", chr(13), stripslashes(get_option('symposium_template_forum_topic')));
	$template_group_forum_category = str_replace("[]", chr(13), stripslashes(get_option('symposium_template_group_forum_category')));
	$template_group_forum_topic = str_replace("[]", chr(13), stripslashes(get_option('symposium_template_group_forum_topic')));

  	echo '<div class="wrap">';
  	
	  	echo '<div id="icon-themes" class="icon32"><br /></div>';
	  	echo '<h2>'.__('Templates', 'wp-symposium').'</h2>';

		// Import
		echo '<div id="symposium_import_templates_form" style="display:none">';
		echo '<input type="submit" class="symposium_templates_cancel button" style="margin-top:10px;" value="Cancel">';
		echo '<p>'.__('Paste previous exported templates into the text area below - please ensure that you are not including any suspicious code.', 'wp-symposium').'</h3>';
		echo '<br /><table class="widefat">';
		echo '<thead>';
		echo '<tr>';
		echo '<th style="font-size:1.2em">'.__('Import Template', 'wp-symposium').'<input id="symposium_import_file_button" type="submit" class="button-primary" style="float:right; padding:2px 6px 2px 6px;margin-bottom:0px;" value="Import"><div id="symposium_import_file_pleasewait" style="width:15px; float:right;"></div></th>';
		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';
		echo '<tr>';
		echo '<td>';

			echo '<textarea id="symposium_import_file" style="width:100%; height:600px;font-family:courier;font-size:11px;background-color:#fff;"></textarea>';

		echo '</td>';
		echo '</tr>';
		echo '</tbody>';
		echo '</table>';
		echo '</div>';
			
		// Export
		echo '<div id="symposium_export_templates_form" style="display:none">';
		echo '<input type="submit" class="symposium_templates_cancel button" style="margin-top:10px;" value="Cancel">';
		echo '<p>'.__('Copy and paste the following into a text editor to backup or share with others. Do not change the comments!', 'wp-symposium').'</h3>';
		echo '<br /><table class="widefat">';
		echo '<thead>';
		echo '<tr>';
		echo '<th style="font-size:1.2em">'.__('Export Template', 'wp-symposium').'</th>';
		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';
		echo '<tr>';
		echo '<td>';

			echo '<textarea style="width:100%; height:600px;font-family:courier;font-size:11px;background-color:transparent;border:0px;">';
		
				echo '<!-- template_profile_header -->'.chr(13).chr(10);
				echo $template_profile_header.chr(13).chr(10);
				echo '<!-- end_template_profile_header -->'.chr(13).chr(10).chr(13).chr(10);

				echo '<!-- template_profile_body -->'.chr(13).chr(10);
				echo $template_profile_body.chr(13).chr(10);
				echo '<!-- end_template_profile_body -->'.chr(13).chr(10).chr(13).chr(10);

				echo '<!-- template_page_footer -->'.chr(13).chr(10);
				echo $template_page_footer.chr(13).chr(10);
				echo '<!-- end_template_page_footer -->'.chr(13).chr(10).chr(13).chr(10);

				echo '<!-- template_email -->'.chr(13).chr(10);
				echo $template_email.chr(13).chr(10);
				echo '<!-- end_template_email -->'.chr(13).chr(10).chr(13).chr(10);

				echo '<!-- template_mail_tray -->'.chr(13).chr(10);
				echo $template_mail_tray.chr(13).chr(10);
				echo '<!-- end_template_mail_tray -->'.chr(13).chr(10).chr(13).chr(10);
		
				echo '<!-- template_mail_message -->'.chr(13).chr(10);
				echo $template_mail_message.chr(13).chr(10);
				echo '<!-- end_template_mail_message -->'.chr(13).chr(10).chr(13).chr(10);

				echo '<!-- template_forum_header -->'.chr(13).chr(10);
				echo $template_forum_header.chr(13).chr(10);
				echo '<!-- end_template_forum_header -->'.chr(13).chr(10).chr(13).chr(10);

				echo '<!-- template_group -->'.chr(13).chr(10);
				echo $template_group.chr(13).chr(10);
				echo '<!-- end_template_group -->'.chr(13).chr(10).chr(13).chr(10);
		
				echo '<!-- template_forum_category -->'.chr(13).chr(10);
				echo $template_forum_category.chr(13).chr(10);
				echo '<!-- end_template_forum_category -->'.chr(13).chr(10).chr(13).chr(10);
		
				echo '<!-- template_forum_topic -->'.chr(13).chr(10);
				echo $template_forum_topic.chr(13).chr(10);
				echo '<!-- end_template_forum_topic -->'.chr(13).chr(10).chr(13).chr(10);
		
				echo '<!-- template_group_forum_category -->'.chr(13).chr(10);
				echo $template_group_forum_category.chr(13).chr(10);
				echo '<!-- end_template_group_forum_category -->'.chr(13).chr(10).chr(13).chr(10);
		
				echo '<!-- template_group_forum_topic -->'.chr(13).chr(10);
				echo $template_group_forum_topic.chr(13).chr(10);
				echo '<!-- end_template_group_forum_topic -->'.chr(13).chr(10).chr(13).chr(10);
		
			echo '</textarea>';

		echo '</td>';
		echo '</tr>';
		echo '</tbody>';
		echo '</table>';
		echo '</div>';

		echo '<div id="symposium_templates_values">';

			echo '<input id="symposium_export_templates" type="submit" class="button" style="margin-top:10px;margin-right:6px;" value="'.__('Export', 'wp-symposium').'">';
			echo '<input id="symposium_import_templates" type="submit" class="button" style="margin-top:10px;margin-right:6px;" value="'.__('Import', 'wp-symposium').'">';
			
			echo '<form action="" method="post">';
		
			// Profile Page Header
			echo '<br /><table class="widefat">';
			echo '<thead>';
			echo '<tr>';
			echo '<th style="font-size:1.2em">'.__('Profile Page Header', 'wp-symposium').'<input type="submit" class="button-primary" style="float:right; padding:2px 6px 2px 6px;margin-bottom:0px;" value="'.__('Save', 'wp-symposium').'"></th>';
			echo '</tr>';
			echo '</thead>';
			echo '<tbody>';
			echo '<tr>';
			echo '<td>';
				echo '<table style="float:right;width:39%">';
				echo '<tr>';
				echo '<td width="33%">'.__('Codes available', 'wp-symposium').'</td>';
				echo '<td>'.__('Output', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tbody>';
				echo '<tr>';
				echo '<td>[follow]</td>';
				echo '<td>'.__('\'Follow\' and \'Unfollow\' buttons (requires Profile Plus)').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[poke]</td>';
				echo '<td>'.__('Show \'poke\' button as defined in <a href=\'admin.php?page=symposium_profile\'>Profile settings</a>', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[display_name]</td>';
				echo '<td>'.__('Display Name', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[location]</td>';
				echo '<td>'.__('City and/or Country', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[born]</td>';
				echo '<td>'.__('Birthday', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[actions]</td>';
				echo '<td>'.__('Friend Request/Send Mail/etc buttons', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[avatar,x]</td>';
				echo '<td>'.__('Show avatar, size x in pixels (no spaces)', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '</tbody>';
				echo '</table>';
				echo '<textarea id="profile_header_textarea" name="profile_header_textarea" style="width:60%;height: 200px;">';
				echo $template_profile_header;
				echo '</textarea>';
				echo '<br /><a id="reset_profile_header" href="javascript:void(0)">'.__('Reset to default', 'wp-symposium').'</a>';
			echo '</td>';
			echo '</tr>';
			echo '</tbody>';
			echo '</table>';

			// Profile Page Body
			echo '<br /><table class="widefat">';
			echo '<thead>';
			echo '<tr>';
			echo '<th style="font-size:1.2em">'.__('Profile Page Body', 'wp-symposium').'<input type="submit" class="button-primary" style="float:right; padding:2px 6px 2px 6px;" value="'.__('Save', 'wp-symposium').'"></th>';
			echo '</tr>';
			echo '</thead>';
			echo '<tbody>';
			echo '<tr>';
			echo '<td>';
				echo '<table style="float:right;width:39%">';
				echo '<tr>';
				echo '<td width="33%">'.__('Codes available', 'wp-symposium').'</td>';
				echo '<td>'.__('Output', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tbody>';
				echo '<tr>';
				echo '<td>[default]</td>';
				echo '<td>'.__('Used to force page parameter (important)', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[page]</td>';
				echo '<td>'.__('Where page content will be placed', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[menu]</td>';
				echo '<td>'.__('Profile menu', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '</tbody>';
				echo '</table>';
				echo '<textarea id="profile_body_textarea" name="profile_body_textarea" style="width:60%;height: 200px;">';
				echo $template_profile_body;
				echo '</textarea>';
				echo '<br /><a id="reset_profile_body" href="javascript:void(0)">'.__('Reset to default', 'wp-symposium').'</a>';
			echo '</td>';
			echo '</tr>';
			echo '</tbody>';
			echo '</table>';

			// WPS Page Footer
			echo '<br /><table class="widefat">';
			echo '<thead>';
			echo '<tr>';
			echo '<th style="font-size:1.2em">'.__('Page Footer', 'wp-symposium').'<input type="submit" class="button-primary" style="float:right; padding:2px 6px 2px 6px;" value="'.__('Save', 'wp-symposium').'"></th>';
			echo '</tr>';
			echo '</thead>';
			echo '<tbody>';
			echo '<tr>';
			echo '<td>';
				echo '<table style="float:right;width:39%">';
				echo '<tr>';
				echo '<td width="33%">'.__('Codes available', 'wp-symposium').'</td>';
				echo '<td>'.__('Output', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tbody>';
				echo '<tr>';
				echo '<td>[powered_by_message]</td>';
				echo '<td>'.__('Default Powered By WPS message', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[version]</td>';
				echo '<td>'.__('Version of WPS', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '</tbody>';
				echo '</table>';
				echo '<textarea id="page_footer_textarea" name="page_footer_textarea" style="width:60%;height: 200px;">';
				echo $template_page_footer;
				echo '</textarea>';
				echo '<br /><a id="reset_page_footer" href="javascript:void(0)">'.__('Reset to default', 'wp-symposium').'</a>';
			echo '</td>';
			echo '</tr>';
			echo '</tbody>';
			echo '</table>';

			// Mail Tray Item
			echo '<br /><table class="widefat">';
			echo '<thead>';
			echo '<tr>';
			echo '<th style="font-size:1.2em">'.__('Mail Page: Tray Item', 'wp-symposium').'<input type="submit" class="button-primary" style="float:right; padding:2px 6px 2px 6px;" value="'.__('Save', 'wp-symposium').'"></th>';
			echo '</tr>';
			echo '</thead>';
			echo '<tbody>';
			echo '<tr>';
			echo '<td>';
				echo '<table style="float:right;width:39%">';
				echo '<tr>';
				echo '<td width="33%">'.__('Codes available', 'wp-symposium').'</td>';
				echo '<td>'.__('Output', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tbody>';
				echo '<tr>';
				echo '<td>[mail_sent]</td>';
				echo '<td>'.__('When the message was sent', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[mail_from]</td>';
				echo '<td>'.__('Sender/recipient of the message', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[mail_subject]</td>';
				echo '<td>'.__('Subject of the message', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[mail_message]</td>';
				echo '<td>'.__('A snippet of the mail message', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '</tbody>';
				echo '</table>';
				echo '<textarea id="template_mail_tray_textarea" name="template_mail_tray_textarea" style="width:60%;height: 200px;">';
				echo $template_mail_tray;
				echo '</textarea>';
				echo '<br /><a id="reset_mail_tray" href="javascript:void(0)">'.__('Reset to default', 'wp-symposium').'</a>';
			echo '</td>';
			echo '</tr>';
			echo '</tbody>';
			echo '</table>';
		
			// Mail Message
			echo '<br /><table class="widefat">';
			echo '<thead>';
			echo '<tr>';
			echo '<th style="font-size:1.2em">'.__('Mail Page: Message', 'wp-symposium').'<input type="submit" class="button-primary" style="float:right; padding:2px 6px 2px 6px;" value="'.__('Save', 'wp-symposium').'"></th>';
			echo '</tr>';
			echo '</thead>';
			echo '<tbody>';
			echo '<tr>';
			echo '<td>';
				echo '<table style="float:right;width:39%">';
				echo '<tr>';
				echo '<td width="33%">'.__('Codes available', 'wp-symposium').'</td>';
				echo '<td>'.__('Output', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tbody>';
				echo '<tr>';
				echo '<td>[avatar,x]</td>';
				echo '<td>'.__('Show avatar, size x in pixels (no spaces)', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[mail_subject]</td>';
				echo '<td>'.__('Subject of the message', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[mail_recipient]</td>';
				echo '<td>'.__('Sender/recipient of the message', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[mail_sent]</td>';
				echo '<td>'.__('When the message was sent', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[delete_button]</td>';
				echo '<td>'.__('Delete mail button', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[reply_button]</td>';
				echo '<td>'.__('Reply to mail button', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[message]</td>';
				echo '<td>'.__('The mail message', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '</tbody>';
				echo '</table>';
				echo '<textarea id="template_mail_message_textarea" name="template_mail_message_textarea" style="width:60%;height: 200px;">';
				echo $template_mail_message;
				echo '</textarea>';
				echo '<br /><a id="reset_mail_message" href="javascript:void(0)">'.__('Reset to default', 'wp-symposium').'</a>';
			echo '</td>';
			echo '</tr>';
			echo '</tbody>';
			echo '</table>';
			
			// Forum Header
			echo '<br /><table class="widefat">';
			echo '<thead>';
			echo '<tr>';
			echo '<th style="font-size:1.2em">'.__('Forum Header', 'wp-symposium').'<input type="submit" class="button-primary" style="float:right; padding:2px 6px 2px 6px;" value="'.__('Save', 'wp-symposium').'"></th>';
			echo '</tr>';
			echo '</thead>';
			echo '<tbody>';
			echo '<tr>';
			echo '<td>';
				echo '<table style="float:right;width:39%">';
				echo '<tr>';
				echo '<td width="33%">'.__('Codes available', 'wp-symposium').'</td>';
				echo '<td>'.__('Output', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tbody>';
				echo '<tr>';
				echo '<td>[breadcrumbs]</td>';
				echo '<td>'.__('Breadcrumb trail', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[new_topic_button]</td>';
				echo '<td>'.__('New Topic button', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[new_topic_form]</td>';
				echo '<td>'.__('Form for new topic', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[digest]</td>';
				echo '<td>'.__('Subscribe to daily digest', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[subscribe]</td>';
				echo '<td>'.__('Receive email for new topics', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[forum_options]</td>';
				echo '<td>'.__('Search, All Activity, etc', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[sharing]</td>';
				echo '<td>'.__('Sharing icons', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '</tbody>';
				echo '</table>';
				echo '<textarea id="template_forum_header_textarea" name="template_forum_header_textarea" style="width:60%;height: 200px;">';
				echo $template_forum_header;
				echo '</textarea>';
				echo '<br /><a id="reset_forum_header" href="javascript:void(0)">'.__('Reset to default', 'wp-symposium').'</a>';
			echo '</td>';
			echo '</tr>';
			echo '</tbody>';
			echo '</table>';

			// Forum Categories
			echo '<br /><table class="widefat">';
			echo '<thead>';
			echo '<tr>';
			echo '<th style="font-size:1.2em">'.__('Forum Categories (list)', 'wp-symposium').'<input type="submit" class="button-primary" style="float:right; padding:2px 6px 2px 6px;" value="'.__('Save', 'wp-symposium').'"></th>';
			echo '</tr>';
			echo '</thead>';
			echo '<tbody>';
			echo '<tr>';
			echo '<td>';
				echo '<table style="float:right;width:39%">';
				echo '<tr>';
				echo '<td width="33%">'.__('Codes available', 'wp-symposium').'</td>';
				echo '<td>'.__('Output', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tbody>';
				echo '<tr>';
				echo '<td>[avatar,x]</td>';
				echo '<td>'.__('Show avatar, size x in pixels (no spaces)', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[replied]</td>';
				echo '<td>'.__('replied or started text', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[subject]</td>';
				echo '<td>'.__('Subject of last post/reply', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[ago]</td>';
				echo '<td>'.__('Age of last post/reply', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[post_count]</td>';
				echo '<td>'.__('How many posts in next level of this category', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[topic_count]</td>';
				echo '<td>'.__('How many topics in next level of this category', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[category_title]</td>';
				echo '<td>'.__('Title of the category', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[category_desc]</td>';
				echo '<td>'.__('Description of the category', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '</tbody>';
				echo '</table>';
				echo '<textarea id="template_forum_category_textarea" name="template_forum_category_textarea" style="width:60%;height: 200px;">';
				echo $template_forum_category;
				echo '</textarea>';
				echo '<br /><a id="reset_template_forum_category" href="javascript:void(0)">'.__('Reset to default', 'wp-symposium').'</a>';
			echo '</td>';
			echo '</tr>';
			echo '</tbody>';
			echo '</table>';

			// Forum Topics
			echo '<br /><table class="widefat">';
			echo '<thead>';
			echo '<tr>';
			echo '<th style="font-size:1.2em">'.__('Forum Topics (list)', 'wp-symposium').'<input type="submit" class="button-primary" style="float:right; padding:2px 6px 2px 6px;" value="'.__('Save', 'wp-symposium').'"></th>';
			echo '</tr>';
			echo '</thead>';
			echo '<tbody>';
			echo '<tr>';
			echo '<td>';
				echo '<table style="float:right;width:39%">';
				echo '<tr>';
				echo '<td width="33%">'.__('Codes available', 'wp-symposium').'</td>';
				echo '<td>'.__('Output', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tbody>';
				echo '<tr>';
				echo '<td>[avatar,x]</td>';
				echo '<td>'.__('Show avatar, size x in pixels (no spaces)', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[replied]</td>';
				echo '<td>'.__('replied or started text', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[subject]</td>';
				echo '<td>'.__('Subject of topic/last reply', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[ago]</td>';
				echo '<td>'.__('Age of topic/last reply', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[views]</td>';
				echo '<td>'.__('View count for this topic', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[replies]</td>';
				echo '<td>'.__('Reply count for this topic', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[topic_title]</td>';
				echo '<td>'.__('Title of the topic', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '</tbody>';
				echo '</table>';
				echo '<textarea id="template_forum_topic_textarea" name="template_forum_topic_textarea" style="width:60%;height: 200px;">';
				echo $template_forum_topic;
				echo '</textarea>';
				echo '<br /><a id="reset_template_forum_topic" href="javascript:void(0)">'.__('Reset to default', 'wp-symposium').'</a>';
			echo '</td>';
			echo '</tr>';
			echo '</tbody>';
			echo '</table>';

			// Group
			echo '<br /><table class="widefat">';
			echo '<thead>';
			echo '<tr>';
			echo '<th style="font-size:1.2em">'.__('Group Page', 'wp-symposium').'<input type="submit" class="button-primary" style="float:right; padding:2px 6px 2px 6px;" value="'.__('Save', 'wp-symposium').'"></th>';
			echo '</tr>';
			echo '</thead>';
			echo '<tbody>';
			echo '<tr>';
			echo '<td>';
			if (function_exists('symposium_groups')) {
				echo '<table style="float:right;width:39%">';
				echo '<tr>';
				echo '<td width="33%">'.__('Codes available', 'wp-symposium').'</td>';
				echo '<td>'.__('Output', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tbody>';
				echo '<tr>';
				echo '<td>[group_name]</td>';
				echo '<td>'.__('Group name', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[group_description]</td>';
				echo '<td>'.__('Group description', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[actions]</td>';
				echo '<td>'.__('Join/delete/etc buttons', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[avatar,x]</td>';
				echo '<td>'.__('Show avatar, size x in pixels (no spaces)', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[default]</td>';
				echo '<td>'.__('Used to force page parameter (important)', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[page]</td>';
				echo '<td>'.__('Where page content will be placed', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[menu]</td>';
				echo '<td>'.__('Group menu', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '</tbody>';
				echo '</table>';
				echo '<textarea id="template_group_textarea" name="template_group_textarea" style="width:60%;height: 200px;">';
				echo $template_group;
				echo '</textarea>';
				echo '<br /><a id="reset_group" href="javascript:void(0)">'.__('Reset to default', 'wp-symposium').'</a>';
			} else {
				echo __('Only available to Bronze or higher members at <a href="http://www.wpsymposium.com">WP Symposium</a>.', 'wp-symposium');
			}
			echo '</td>';
			echo '</tr>';
			echo '</tbody>';
			echo '</table>';

			// Group Forum Categories
			// Not currently supported
			if (1==0) {
			echo '<br /><table class="widefat">';
			echo '<thead>';
			echo '<tr>';
			echo '<th style="font-size:1.2em">'.__('Group Forum Categories (list)', 'wp-symposium').'<input type="submit" class="button-primary" style="float:right; padding:2px 6px 2px 6px;" value="'.__('Save', 'wp-symposium').'"></th>';
			echo '</tr>';
			echo '</thead>';
			echo '<tbody>';
			echo '<tr>';
			echo '<td>';
				echo '<table style="float:right;width:39%">';
				echo '<tr>';
				echo '<td width="33%">'.__('Codes available', 'wp-symposium').'</td>';
				echo '<td>'.__('Output', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tbody>';
				echo '<tr>';
				echo '<td>[avatar,x]</td>';
				echo '<td>'.__('Show avatar, size x in pixels (no spaces)', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[replied]</td>';
				echo '<td>'.__('replied or started text', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[subject]</td>';
				echo '<td>'.__('Subject of last post/reply', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[ago]</td>';
				echo '<td>'.__('Age of last post/reply', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[post_count]</td>';
				echo '<td>'.__('How many posts in next level of this category', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[topic_count]</td>';
				echo '<td>'.__('How many topics in next level of this category', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[category_title]</td>';
				echo '<td>'.__('Title of the category', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '</tbody>';
				echo '</table>';
				echo '<textarea id="template_group_forum_category_textarea" name="template_group_forum_category_textarea" style="width:60%;height: 200px;">';
				echo $template_forum_category;
				echo '</textarea>';
				echo '<br /><a id="reset_template_group_forum_category" href="javascript:void(0)">'.__('Reset to default', 'wp-symposium').'</a>';
			echo '</td>';
			echo '</tr>';
			echo '</tbody>';
			echo '</table>';
			}

			// Group Forum Topics
			echo '<br /><table class="widefat">';
			echo '<thead>';
			echo '<tr>';
			echo '<th style="font-size:1.2em">'.__('Group Forum Topics (list)', 'wp-symposium').'<input type="submit" class="button-primary" style="float:right; padding:2px 6px 2px 6px;" value="'.__('Save', 'wp-symposium').'"></th>';
			echo '</tr>';
			echo '</thead>';
			echo '<tbody>';
			echo '<tr>';
			echo '<td>';
				echo '<table style="float:right;width:39%">';
				echo '<tr>';
				echo '<td width="33%">'.__('Codes available', 'wp-symposium').'</td>';
				echo '<td>'.__('Output', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tbody>';
				echo '<tr>';
				echo '<td>[avatar,x]</td>';
				echo '<td>'.__('Show avatar, size x in pixels (no spaces)', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[replied]</td>';
				echo '<td>'.__('replied or started text', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[subject]</td>';
				echo '<td>'.__('Subject of topic/last reply', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[ago]</td>';
				echo '<td>'.__('Age of topic/last reply', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[views]</td>';
				echo '<td>'.__('View count for this topic', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[replies]</td>';
				echo '<td>'.__('Reply count for this topic', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[topic_title]</td>';
				echo '<td>'.__('Title of the topic', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '</tbody>';
				echo '</table>';
				echo '<textarea id="template_group_forum_topic_textarea" name="template_group_forum_topic_textarea" style="width:60%;height: 200px;">';
				echo $template_group_forum_topic;
				echo '</textarea>';
				echo '<br /><a id="reset_template_group_forum_topic" href="javascript:void(0)">'.__('Reset to default', 'wp-symposium').'</a>';
			echo '</td>';
			echo '</tr>';
			echo '</tbody>';
			echo '</table>';

			// WPS Email Notifications
			echo '<br /><table class="widefat">';
			echo '<thead>';
			echo '<tr>';
			echo '<th style="font-size:1.2em">'.__('WPS Emails', 'wp-symposium').'<input type="submit" class="button-primary" style="float:right; padding:2px 6px 2px 6px;" value="'.__('Save', 'wp-symposium').'"></th>';
			echo '</tr>';
			echo '</thead>';
			echo '<tbody>';
			echo '<tr>';
			echo '<td>';
				echo '<table style="float:right;width:39%">';
				echo '<tr>';
				echo '<td width="33%">'.__('Codes available', 'wp-symposium').'</td>';
				echo '<td>'.__('Output', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tbody>';
				echo '<tr>';
				echo '<td>[message]</td>';
				echo '<td>'.__('The email message', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[footer]</td>';
				echo '<td>'.__('Footer Message', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[powered_by_message]</td>';
				echo '<td>'.__('Default Powered By WPS message', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[version]</td>';
				echo '<td>'.__('Version of WPS', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '</tbody>';
				echo '</table>';
				echo '<textarea id="email_textarea" name="email_textarea" style="width:60%;height: 200px;">';
				echo $template_email;
				echo '</textarea>';
				echo '<br /><a id="reset_email" href="javascript:void(0)">'.__('Reset to default', 'wp-symposium').'</a>';
			echo '</td>';
			echo '</tr>';

			echo '</tbody>';
			echo '</table>';
		
			echo '</form>';
			
		echo '</div>';
		
			  	
	echo '</div>';
}

function symposium_plugin_moderation() {

	global $wpdb;

  	echo '<div class="wrap">';
  	
	  	echo '<div id="icon-themes" class="icon32"><br /></div>';
	  	echo '<h2>'.__('Forum Posts', 'wp-symposium').'</h2>';
	  	
	  	$all = $wpdb->get_var("SELECT count(*) FROM ".$wpdb->prefix."symposium_topics"); 
	  	$approved = $wpdb->get_var("SELECT count(*) FROM ".$wpdb->prefix."symposium_topics WHERE topic_approved = 'on'"); 
	  	$unapproved = $all-$approved;
	  	
	  	$mod = 'all';
	  	if (isset($_GET['mod']) && $_GET['mod'] != '') { $mod = $_GET['mod']; }
	  	
	  	if ($mod == "all") { $all_class='current'; $approved_class=''; $unapproved_class=''; }
	  	if ($mod == "approved") { $all_class=''; $approved_class='current'; $unapproved_class=''; }
	  	if ($mod == "unapproved") { $all_class=''; $approved_class=''; $unapproved_class='current'; }
	  	
	  	echo '<ul class="subsubsub">';
		echo "<li><a href='admin.php?page=symposium_moderation' class='".$all_class."'>".__('All', 'wp-symposium')." <span class='count'>(".$all.")</span></a> |</li>";
		echo "<li><a href='admin.php?page=symposium_moderation&mod=approved' class='".$approved_class."'>".__('Approved', 'wp-symposium')." <span class='count'>(".$approved.")</span></a> |</li>"; 
		echo "<li><a href='admin.php?page=symposium_moderation&mod=unapproved' class='".$unapproved_class."'>".__('Unapproved', 'wp-symposium')." <span class='count'>(".$unapproved.")</span></a></li>";
		echo "</ul>";
		
		// Paging info
		$showpage = 0;
		$pagesize = 20;
		$numpages = floor($all / $pagesize);
		if ($all % $pagesize > 0) { $numpages++; }
	  	if (isset($_GET['showpage']) && $_GET['showpage']) { $showpage = $_GET['showpage']-1; } else { $showpage = 0; }
	  	if ($showpage >= $numpages) { $showpage = $numpages-1; }
		$start = ($showpage * $pagesize);		
		if ($start < 0) { $start = 0; }  
				
		// Query
		$sql = "SELECT t.*, u.display_name FROM ".$wpdb->prefix.'symposium_topics'." t LEFT JOIN ".$wpdb->base_prefix.'users'." u ON t.topic_owner = u.ID ";
		if ($mod == "approved") { $sql .= "WHERE t.topic_approved = 'on' "; }
		if ($mod == "unapproved") { $sql .= "WHERE t.topic_approved != 'on' "; }
		$sql .= "ORDER BY tid DESC "; 
		$sql .= "LIMIT ".$start.", ".$pagesize;
		$posts = $wpdb->get_results($sql);
	
		// Pagination (top)
		echo symposium_pagination($numpages, $showpage, "admin.php?page=symposium_moderation&mod=".$mod."&showpage=");
		
		echo '<br /><table class="widefat">';
		echo '<thead>';
		echo '<tr>';
		echo '<th>ID</td>';
		echo '<th>'.__('Author', 'wp-symposium').'</th>';
		echo '<th style="width: 30px; text-align:center;">'.__('Status', 'wp-symposium').'</th>';
		echo '<th>'.__('Preview', 'wp-symposium').'</th>';
		echo '<th>'.__('IP &amp; Proxy', 'wp-symposium').'</th>';
		echo '<th>'.__('Time', 'wp-symposium').'</th>';
		echo '<th>'.__('Action', 'wp-symposium').'</th>';
		echo '</tr>';
		echo '</thead>';
		echo '<tfoot>';
		echo '<tr>';
		echo '<th>ID</th>';
		echo '<th>'.__('Author', 'wp-symposium').'</th>';
		echo '<th style="width: 30px; text-align:center;">'.__('Status', 'wp-symposium').'</th>';
		echo '<th>'.__('Preview', 'wp-symposium').'</th>';
		echo '<th>'.__('IP &amp; Proxy', 'wp-symposium').'</th>';
		echo '<th>'.__('Time', 'wp-symposium').'</th>';
		echo '<th>'.__('Action', 'wp-symposium').'</th>';
		echo '</tr>';
		echo '</tfoot>';
		echo '<tbody>';
		
		if ($posts) {
			
			foreach ($posts as $post) {
	
				echo '<tr>';
				echo '<td valign="top" style="width: 30px">'.$post->tid.'</td>';
				echo '<td valign="top" style="width: 175px">'.$post->display_name.'</td>';
				echo '<td valign="top" style="width: 30px; text-align:center;">';
				if ($post->topic_approved != "on") {
					echo '<img src="'.get_option('symposium_images').'/forum_orange.png" alt="Unapproved" />';
				} else {
					echo '<img src="'.get_option('symposium_images').'/forum_green.png" alt="Unapproved" />';
				}
				echo '</td>';
				echo '<td valign="top">';
				if ($post->topic_parent == 0) {
					echo '<strong>'.__('New Topic', 'wp-symposium').'</strong><br />';
				} else {
					echo '<strong>'.__('New Reply', 'wp-symposium').'</strong><br />';
				}
				$preview = stripslashes($post->topic_post);
				if ( strlen($preview) > 150 ) { $preview = substr($preview, 0, 150)."..."; }
				echo '<div style="float: left;">'.$preview;
				if ( strlen($preview) > 150 ) { 
					echo '<span class="show_full_post" title="'.stripslashes(str_replace('"', '&quot;', $post->topic_post)).'" style="margin-left:6px; cursor:pointer; text-decoration:underline;">'.__('View', 'wp-symposium').'</span>';
				}
				echo '</div>';
				echo '</td>';
				echo '<td valign="top" style="width: 150px">'.$post->remote_addr.'<br />'.$post->http_x_forwarded_for.'</td>';
				echo '<td valign="top" style="width: 150px">'.$post->topic_started.'</td>';
				echo '<td valign="top" style="width: 150px">';
				$showpage = (isset($_GET['showpage'])) ? $_GET['showpage'] : 0;
				if ($post->topic_approved != "on" ) {
					echo "<a href='admin.php?page=symposium_moderation&action=post_approve&showpage=".$showpage."&tid=".$post->tid."'>".__('Approve', 'wp-symposium')."</a> | ";
				}
				echo "<span class='trash delete'><a href='admin.php?page=symposium_moderation&action=post_del&showpage=".$showpage."&tid=".$post->tid."'>".__('Trash', 'wp-symposium')."</a></span>";
				echo '</td>';
				echo '</tr>';			
	
			}
		} else {
			echo '<tr><td colspan="6">&nbsp;</td></tr>';
		}
		echo '</tbody>';
		echo '</table>';
	
		// Pagination (bottom)
		echo symposium_pagination($numpages, $showpage, "admin.php?page=symposium_moderation&mod=".$mod."&showpage=");
		
	echo '</div>'; // End of wrap div

}

function symposium_plugin_debug() {

/* ============================================================================================================================ */

	global $wpdb, $current_user;
	wp_get_current_user();

 	$wpdb->show_errors();

  	$fail = "<span style='color:red; font-weight:bold;'>";
  	$fail2 = "</span><br /><br />";
 	
  	echo '<div class="wrap">';

/*
		$home_path = get_home_path();
        $htaccess_file = $home_path.'.htaccess';
        $marker = 'WPS';
		$insertion = array( '<IfModule mod_rewrite.c>', 'Options +FollowSymlinks', 'RewriteBase /', 'RewriteEngine on', 'RewriteRule ^forum/(.*)$ test.php?p=$1 [nc]', '</IfModule>');
        
        if (extract_from_markers( $filename, $marker )) {	        
			if ( is_writable($home_path) && is_writable($htaccess_file)) {
                if ( got_mod_rewrite() ) {
					insert_with_markers( $htaccess_file, $marker, $insertion );
                }
        	}
        } else {
            echo 'This <strong><u>MUST</u></strong> go at the <strong><u>TOP</u></strong> of the .htaccess file:<div style="margin:10px;padding:6px;border:1px solid #000; background-color:#fff; font-family:courier new; font-size:12px;">';
			echo '# BEGIN WPS<br />';
            foreach ($insertion as $line) {
                echo $line.'<br />';
            }
			echo '# END WPS';
			echo '</div>';
        }	
*/

        	
	  	echo '<div id="icon-themes" class="icon32"><br /></div>';
	  	echo '<h2>'.__('WP Symposium Installation', "wp-symposium").'</h2>';
	  	
	  	// ********** Summary
		echo '<div style="margin-top:10px; margin-bottom:10px">';
			echo __("Visit this page to complete installation; after you add a WP Symposium shortcode to a page; change pages with WP Symposium shortcodes; if you change WordPress Permalinks; or if you experience problems.", "wp-symposium");
		echo '</div>';

		// Validation code		
		echo '<div id="icon-themes" class="icon32"><br /></div>';
		echo '<h2>' . __('Activation Code', 'wp-symposium') . '</h2>';

		echo '<p>'.__('If you have Bronze plugins activated (see your <a href="plugins.php">plugins page</a>), you will see a notice at the top of your website until you enter a valid Activation Code. <strong>If you have purchased Bronze membership at www.wpsymposium.com</strong>, your activation code is shown at <a href="http://www.wpsymposium.com/membership" target="_new">http://www.wpsymposium.com/membership</a> (make sure you&apos;ve logged in!). <br /><br />To obtain an Activation Code for the Bronze plugins, <a href="http://www.wpsymposium.com/membership" target="_new">upgrade your membership</a> at www.wpsymposium.com to "Bronze".', 'wp-symposium').'</p>';

		if (isset($_POST['symposium_validation_code'])) {
			update_option('symposium_activation_code', $_POST['symposium_validation_code']);
		}

	   	echo '<form method="post" action="">';
	   	echo __('Activation Code:', 'wp-symposium').' <input type="text" name="symposium_validation_code" value="'.get_option('symposium_activation_code').'" style="width:300px; background-color: #ff9; border:1px solid #333;">';
	   	echo '<p class="submit"><input type="submit" name="Submit" class="button-primary" value="'.__('Update', 'wp-symposium').'" />';	   	

	   	if ($code=get_option('symposium_activation_code')) {
	   	    if (($code != 'wps') && (substr($code,0,3) != 'vip')) {
		   	    $code =  preg_replace('#[^0-9]#','',$code);
		   	    if ($code > time()) {
					echo '<br /><br /> '.sprintf(__('This activation code expires on %s, <a href="http://www.wpsymposium.com/membership" target="_new">get a new activation code</a> before then to extend the date.', 'wp-symposium'), date('l d F Y', $code));
					echo '<br />'.__('Note that this may not tie in with your WPS expiry date, simply re-enter a new activation code at any time to reset to another 90 days.', 'wp-symposium');
		   	    } else {
					echo '<br /><br /> <strong>'.__('This activation code has expired! <a href="http://www.wpsymposium.com/membership" target="_new">Get a new activation code</a> to extend the date.', 'wp-symposium').'</strong>';
		   	    }
	   	    } else {
	   	        if ($code=='wps') {
		   	        echo '<br /><br />'.__('This is a temporary activation code, it should not be used permenantly.', 'wp-symposium');
	   	        }
	   	        if (substr($code,0,3) == 'vip') {
		   	        echo '<br /><br />'.__('This is a lifetime activation code.', 'wp-symposium');
	   	        }
	   	    }
	   	} else {
	   	    echo ' (no activation code entered)';
	   	}
	   	echo '</p></form>';

		// Status
		echo '<div id="icon-themes" class="icon32"><br /></div>';
		echo '<h2>' . __('Installed Plugins', 'wp-symposium') . '</h2>';
		
		if (current_user_can('update_core')) {

			echo '<div style="float:right"><em>';
			echo __('Quick links:', 'wp-symposium').' ';
			echo '<a href="#image">'.__('Image uploading', 'wp-symposium').'</a> | ';
			echo '<a href="#ric">'.__('Integrity check', 'wp-symposium').'</a> | ';
			echo '<a href="#purge">'.__('Database Purge Tool', 'wp-symposium').'</a> | ';
			echo '<a href="#bbpress">'.__('bbPress migration', 'wp-symposium').'</a> | ';
			echo '<a href="#mingle">'.__('Mingle migration', 'wp-symposium').'</a>';
			echo '</em></div>';
			
		}

		echo "<div style='margin-top:15px'>";

			echo '<table class="widefat">';
			echo '<thead>';
			echo '<tr>';
			/*
			if (current_user_can('update_core'))
				echo '<th width="30px">'.__('Installed', 'wp-symposium').'</th>';
			echo '<th width="30px">'.__('Activated', 'wp-symposium').'</th>';
			*/
			echo '<th width="260px">'.__('Plugin', 'wp-symposium').'</th>';
			echo '<th>'.__('WordPress page/URL Found', 'wp-symposium').'</th>';
			echo '<th width="90px;">'.__('Status', 'wp-symposium');
			if (current_user_can('update_core'))
				echo ' [<a href="javascript:void(0);" id="symposium_url">?</a>]</tg>';
			if (current_user_can('update_core'))
				echo '<th class="symposium_url">'.__('WPS Setting', 'wp-symposium').'</th>';
			echo '</tr>';
			echo '</thead>';
			echo '<tbody>';
			echo '<tr>';
				/*
				echo '<td style="text-align:center"><img src="'.get_option('symposium_images').'/tick.png" /></td>';
				if (current_user_can('update_core'))		
					echo '<td style="text-align:center"><img src="'.get_option('symposium_images').'/tick.png" /></td>';
				*/
				echo '<td>'.__('Core', 'wp-symposium').'</td>';
				echo '<td>&nbsp;</td>';
				echo '<td style="text-align:center"><img src="'.get_option('symposium_images').'/smilies/good.png" /></td>';
				if (current_user_can('update_core'))
					echo '<td class="symposium_url" style="background-color:#efefef">-</td>';
			echo '</tr>';

			// Get version numbers installed (if applicable)
			$mobile_ver = get_option("symposium_mobile_version");
			if ($mobile_ver != '') $mobile_ver = "v".$mobile_ver;

			install_row('Forum', 'symposium-forum', 'symposium_forum', get_option('symposium_forum_url'), 'wp-symposium/symposium_forum.php', 'admin.php?page=symposium_forum', __('The forum plugin must be installed in ', 'wp-symposium').WP_PLUGIN_DIR.'/wp-symposium.');
			install_row('Profile', 'symposium-profile', 'symposium_profile', get_option('symposium_profile_url'), 'wp-symposium/symposium_profile.php', 'admin.php?page=symposium_profile', __('The profile plugin must be installed in ', 'wp-symposium').WP_PLUGIN_DIR.'/wp-symposium.');
			install_row('Mail', 'symposium-mail', 'symposium_mail', get_option('symposium_mail_url'), 'wp-symposium/symposium_mail.php', '', __('The mail plugin must be installed in ', 'wp-symposium').WP_PLUGIN_DIR.'/wp-symposium.');
			install_row('Members', 'symposium-members', 'symposium_members', get_option('symposium_members_url'), 'wp-symposium/symposium_members.php', 'admin.php?page=symposium_members_menu', __('The members directory plugin must be installed in ', 'wp-symposium').WP_PLUGIN_DIR.'/wp-symposium.');
			install_row('Panel', '', 'add_notification_bar', '-', 'wp-symposium/symposium_bar.php', 'admin.php?page=symposium_bar', __('The panel plugin must be installed in ', 'wp-symposium').WP_PLUGIN_DIR.'/wp-symposium.');

			do_action('symposium_installation_hook');

			echo '</tbody>';
			echo '</table>';
				
		echo "</div>";

		// Only show following to admins and above
		if (current_user_can('update_core')) {
			
			echo "<div style='width:45%; float:left;'>";

		  		echo '<div id="icon-themes" class="icon32"><br /></div>';
				echo '<h2>'.__('Core Information', 'wp-symposium').'</h2>';
	
				echo '<p>';
				echo __('Site domain name', 'wp-symposium').': '.get_bloginfo('url').'<br />';
				echo '</p>';
	
				echo "<p>";
	
					global $blog_id;
					echo __("WordPress site ID:", "wp-symposium")." ".$blog_id.'<br />';
					echo __("WordPress site name:", "wp-symposium")." ".get_bloginfo('name').'<br />';
					echo '<br />';
					echo __("WP Symposium internal code version:", "wp-symposium")." ";
					$ver = get_option("symposium_version");
					if (!$ver) { 
						echo "<br /><span style='clear:both;color:red; font-weight:bold;'>Error!</span> ".__('No code version set. Try <a href="admin.php?page=symposium_debug&force_create_wps=yes">re-creating/modifying</a> the database tables.', 'wp-symposium')."</span><br />"; 
					} else {
						echo $ver."<br />";
					}
			
				echo "</p>";
				
				// Curl / JSON
				$disabled_functions=explode(',', ini_get('disable_functions'));
				$ok=true;
				if (!is_callable('curl_init')) {
					echo $fail.__('CURL PHP extension is not installed, please contact your hosting company.', 'wp-symposium').$fail2;
					$ok=false;
				} else {
					if (in_array('curl_init', $disabled_functions)) {
						echo $fail.__('CURL PHP extension is disabled in php.ini, please contact your hosting company.', 'wp-symposium').$fail2;
						$ok=false;
					} else {
						echo '<p>'.__('CURL PHP extension is installed and enabled in php.ini.', 'wp-symposium').'</p>';
					}
				}
				if (!is_callable('json_decode')) {
					echo $fail.__('JSON PHP extension is not installed, please contact your hosting company.', 'wp-symposium').$fail2;
					$ok=false;
				} else {
					if (in_array('json_decode', $disabled_functions)) {
						echo $fail.__('JSON PHP extension is disabled in php.ini, please contact your hosting company.', 'wp-symposium').$fail2;
						$ok=false;
					} else {
						echo "<p>".__('JSON PHP extension is installed and enabled in php.ini.', 'wp-symposium')."</p>";
					}
				}
				if (!$ok)
					echo $fail.__('Please contact your hosting company to ask for the above to be installed/enabled.', 'wp-symposium').$fail2;
				
				// Debug mode?
				if (WPS_DEBUG) {
					echo "<p style='font-weight:bold'>".__('Running in DEBUG mode.', 'wp-symposium')."</p>";
				}
				
			echo "</div>";
			echo "<div style='width:50%; float:left; padding-bottom:15px;'>";
					
				// Permalinks
				echo '<a name="perma"></a>';
			  	echo '<div id="icon-themes" class="icon32"><br /></div>';
			   	echo '<h2>'.__('WPS Permalinks', 'wp-symposium').'</h2>';
			   	echo '<p style="font-weight:bold">'.__('It is recommended that you test these before implementing.', 'wp-symposium').'</p>';
			   	
			   	// Act on submit
			   	$just_switched_on = false;
				if (isset($_POST[ 'symposium_permalinks' ])) {
					if ( $_POST[ 'symposium_permalinks_enable' ] == 'on' ) {
						// If switching on, default categories to on
						if (!get_option('symposium_permalink_structure')) {
							update_option('symposium_permalinks_cats', 'on');	
							$just_switched_on = true;			   	    
						} else {
							// If already on, act on categories checkbox
							if (isset($_POST[ 'symposium_permalinks_cats' ])) {
								update_option('symposium_permalinks_cats', 'on');
							} else {
								update_option('symposium_permalinks_cats', '');
							}
						}
						update_option('symposium_permalink_structure', 'on');				   	    
						
					} else {

						if (get_option('symposium_permalink_structure')) {
							echo '<p>'.__('The first time you enable permalinks, please be patient while your database is updated.', 'wp-symposium').'</p>'; 
						}
				   	    delete_option('symposium_permalink_structure');
				   	    delete_option('symposium_permalinks_cats');
					}
			   	}

				if ( get_option('permalink_structure') != '' ) {

					echo '<form method="post" action="#perma">';

						if ( get_option('symposium_permalink_structure')  ) {
							
							// Can't work with Forum in AJAX mode
							if (get_option('symposium_forum_ajax')) {
								update_option('symposium_forum_ajax', '');
								echo '<p style="color:green; font-weight:bold;">'.__('Forum "AJAX mode" has been disabled, as this is not compatible with permalinks.', 'wp-symposium').'</p>'; 
							}
	
							// Do a check to ensure all forum categories have a slug
							$sql = "SELECT * FROM ".$wpdb->prefix."symposium_cats WHERE stub = ''";
							$cats = $wpdb->get_results($sql);
							if ($cats) {
								foreach ($cats as $cat) {
									$stub = symposium_create_stub($cat->title);
									$sql = "UPDATE ".$wpdb->prefix."symposium_cats SET stub = '".$stub."' WHERE cid = %d";
									$wpdb->query($wpdb->prepare($sql, $cat->cid));
									if (WPS_DEBUG) echo $wpdb->last_query.'<br>';
								}
							}
							// Do a check to ensure all forum topics have a slug
							$sql = "SELECT * FROM ".$wpdb->prefix."symposium_topics WHERE topic_parent = 0 AND stub = '' ORDER BY tid DESC";
							$topics = $wpdb->get_results($sql);
							if ($topics) {
								foreach ($topics as $topic) {
									$stub = symposium_create_stub($topic->topic_subject);
									$sql = "UPDATE ".$wpdb->prefix."symposium_topics SET stub = '".$stub."' WHERE tid = %d";
									$wpdb->query($wpdb->prepare($sql, $topic->tid));
									if (WPS_DEBUG) echo $wpdb->last_query.'<br>';
								}
							} 

							// update any POSTed values or update default values if necessary
							$reset = isset($_POST['symposium_permalinks_reset']) ? true : false;

							if ( (!$just_switched_on) && (!$reset) && ( get_option('symposium_rewrite_forum_single') || get_option('symposium_rewrite_forum_double') || get_option('symposium_rewrite_members') ) )  {
									
									if ($_POST['symposium_permalinks'] == 'Y') {
										update_option('symposium_rewrite_forum_single', $_POST['symposium_rewrite_forum_single']);
										update_option('symposium_rewrite_forum_single_target', $_POST['symposium_rewrite_forum_single_target']);
										update_option('symposium_rewrite_forum_double', $_POST['symposium_rewrite_forum_double']);
										update_option('symposium_rewrite_forum_double_target', $_POST['symposium_rewrite_forum_double_target']);
										update_option('symposium_rewrite_members', $_POST['symposium_rewrite_members']);
										update_option('symposium_rewrite_members_target', $_POST['symposium_rewrite_members_target']);
									}
									flush_rewrite_rules();
									
							} else {
								
								// check that options exist if not put in defaults
//								if ( ($reset) || ( !get_option('symposium_rewrite_forum_single') && !get_option('symposium_rewrite_forum_double')  && !get_option('symposium_rewrite_members') ) ) {

									// get forum path and pagename
									$sql = "SELECT ID, post_title FROM ".$wpdb->prefix."posts WHERE post_content LIKE  '%[symposium-forum]%' AND post_status =  'publish' AND post_type =  'page'";
									$page = $wpdb->get_row($sql);
									$permalink = symposium_get_url('forum');
									$p = strtolower(trim(str_replace(get_bloginfo('url'), '', $permalink), '/'));
									$post_title = rawurlencode($page->post_title);

									// get profile path and pagename
									$sql = "SELECT ID, post_title FROM ".$wpdb->prefix."posts WHERE post_content LIKE  '%[symposium-profile]%' AND post_status =  'publish' AND post_type =  'page'";
									$page = $wpdb->get_row($sql);
									$permalink = symposium_get_url('profile');
									$m = strtolower(trim(str_replace(get_bloginfo('url'), '', $permalink), '/'));
									$members_title = rawurlencode($page->post_title);
									
									update_option('symposium_rewrite_forum_single', $p.'/([^/]+)/?');
									update_option('symposium_rewrite_forum_single_target', 'index.php?pagename='.$post_title.'&stub=/$matches[1]');
									update_option('symposium_rewrite_forum_double', $p.'/([^/]+)/([^/]+)/?');
									update_option('symposium_rewrite_forum_double_target', 'index.php?pagename='.$post_title.'&stub=$matches[1]/$matches[2]');
									update_option('symposium_rewrite_members', $m.'/([^/]+)/?');
									update_option('symposium_rewrite_members_target', 'index.php?pagename='.$members_title.'&stub=$matches[1]');

									flush_rewrite_rules();
									echo '<p style="color:green; font-weight:bold;">'.__('Re-write rules saved as default suggested values.', 'wp-symposium').'</p>'; 
									
									update_option('symposium_permalinks_cats', 'on');

//								}
							}

							// Display fields allowing them to be altered												
													
							echo '<strong>'.__('Forum', 'wp-symposium').'</strong><br />';
							echo '<input type="text" name="symposium_rewrite_forum_single" style="width:150px" value="'.get_option('symposium_rewrite_forum_single').'" /> => ';
							echo '<input type="text" name="symposium_rewrite_forum_single_target" style="width:400px" value="'.get_option('symposium_rewrite_forum_single_target').'" /><br />';
							echo '<input type="text" name="symposium_rewrite_forum_double" style="width:150px" value="'.get_option('symposium_rewrite_forum_double').'" /> => ';
							echo '<input type="text" name="symposium_rewrite_forum_double_target" style="width:400px" value="'.get_option('symposium_rewrite_forum_double_target').'" /><br />';
							echo '<br /><strong>'.__('Member Profile', 'wp-symposium').'</strong><br />';
							echo '<input type="text" name="symposium_rewrite_members" style="width:150px" value="'.get_option('symposium_rewrite_members').'" /> => ';
							echo '<input type="text" name="symposium_rewrite_members_target" style="width:400px" value="'.get_option('symposium_rewrite_members_target').'" /><br /><br />';
							
							echo '<input type="hidden" name="symposium_permalinks" value="Y">';
							echo '<input type="checkbox" name="symposium_permalinks_enable" CHECKED > '.__('WPS Permalinks enabled', 'wp-symposium').'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
							echo '<input type="checkbox" name="symposium_permalinks_cats"';
								if (get_option('symposium_permalinks_cats')) echo ' CHECKED';
								echo '> '.__('Include categories in forum hyperlinks', 'wp-symposium').'<br /><br />';
							echo '<input type="checkbox" name="symposium_permalinks_reset" /> '.__('Reset to default suggested values (if you have altered page names for example)', 'wp-symposium').'<br /><br />';
						   	echo '<input type="submit" class="button-primary" value="'.__('Update', 'wp-symposium').'" /> ';
							
						} else {
							echo '<input type="hidden" name="symposium_permalinks" value="Y">';
							echo '<input type="checkbox" name="symposium_permalinks_enable"> '.__('Check to enable WPS Permalinks', 'wp-symposium').'<br /><br />';
						   	echo '<input type="submit" class="button-primary" value="'.__('Update', 'wp-symposium').'" />';
						}


					echo '</form>';

				} else {
					echo '<p>'.__('You cannot use Permalinks if your WordPress <a href="options-permalink.php">permalink setting</a> is default.', 'wp-symposium').'</p>'; 
				}
				
			echo "</div>";
			
			echo "<div style='clear:both'></div>";

			echo "<div style='width:45%; float:left;'>";

				// ********** Reset database version
			  	echo '<div id="icon-themes" class="icon32"></div>';
			   	echo '<h2>'.__('Refresh WP Symposium', 'wp-symposium').'</h2>';
			   	echo "<p>".__('To re-run the database table creation/modifications, <a href="admin.php?page=symposium_debug&force_create_wps=yes">click here</a>.<br /><strong>This will not destroy any existing tables or data</strong>.', 'wp-symposium')."</p>";
			   	echo "<p>".sprintf(__('This will also display the WP Symposium <a href="%s">welcome page</a>.', 'wp-symposium'), "admin.php?page=symposium_welcome")."</p>";
			
			echo "</div>";
			echo "<div style='width:50%; float:left;'>";
			
				// ********** Test Email   	
				
				if( isset($_POST[ 'symposium_testemail' ]) && $_POST[ 'symposium_testemail' ] == 'Y' ) {
					$to = $_POST['symposium_testemail_address'];
					if (symposium_sendmail($to, "WP Symposium Test Email", __("This is a test email sent from", "wp-symposium")." ".get_bloginfo('url'))) {
						echo "<div class='updated'><p>";
						$from = get_option('symposium_from_email');
						echo sprintf(__('Email sent to %s from', 'wp-symposium'), $to);
						echo ' '.$from;
						echo "</p></div>";
					} else {
						echo "<div class='error'><p>".__("Email failed to send", "wp-symposium").".</p></div>";
					}
				}
			  	echo '<div id="icon-themes" class="icon32"><br /></div>';
			   	echo '<h2>'.__('Send a test email', 'wp-symposium').'</h2>';
	
			   	echo '<p>'.__('Enter a valid email address to test sending an email from the server', 'wp-symposium').'.</p>';
			   	echo '<form method="post" action="">';
				echo '<input type="hidden" name="symposium_testemail" value="Y">';
			   	echo '<input type="text" name="symposium_testemail_address" value="" style="width:300px" class="regular-text">';
			   	echo '<p class="submit"><input type="submit" name="Submit" class="button-primary" value="'.__('Send email', 'wp-symposium').'" /></p>';
			   	echo '</form>';
			   	
			echo "</div>";

			echo "<div style='clear:both'></div>";

			echo "<div style='width:45%; float:left;'>";

				// Integrity check
				echo '<a name="ric"></a>';
			  	echo '<div id="icon-themes" class="icon32"><br /></div>';
			   	echo '<h2>'.__('Integrity check', 'wp-symposium').'</h2>';
			   	
				if (isset($_POST['symposium_ric'])) {
					$report = '';
					// Update topic categories (if category missing and with a parent)
					$sql = "SELECT * FROM ".$wpdb->prefix."symposium_topics
							WHERE topic_category = 0 AND topic_parent > 0";
					$topics = $wpdb->get_results($sql);
					if ($topics) {
						foreach ($topics AS $topic) {
							// Get the category of the parent and update
							$sql = "SELECT topic_category FROM ".$wpdb->prefix."symposium_topics WHERE tid = %d";
							$parent_cat = $wpdb->get_var($wpdb->prepare($sql, $topic->topic_parent));
							// Update this topic's category to it
							$sql = "UPDATE ".$wpdb->prefix."symposium_topics SET topic_category = %d WHERE tid = %d";
							$wpdb->query($wpdb->prepare($sql, $parent_cat, $topic->tid));
						}
						$report .= sprintf( __("%d topics had missing categories and a parent", "wp-symposium"), count($topics) )."<br />";
					}
					
					// If a members folder exists in wps-content, but user doesn't exist, report that it exists (can remove?)
					$path = get_option('symposium_img_path').'/members';
					if(file_exists($path) && is_dir($path)) { 
						if ($handler = opendir($path)) {
							while (($sub = readdir($handler)) !== FALSE) {
								if ($sub != "." && $sub != ".." && $sub != "Thumb.db" && $sub != "Thumbs.db") {
									if (is_dir($path."/".$sub)) {
										$id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM ".$wpdb->base_prefix."users WHERE ID = %d", $sub));
										if (!$id) {
											$report .= 'User ID '.$sub.' not found but '.$path."/".$sub.' exists<br />';
											//symposium_rrmdir($path."/".$sub);
										}
									}
								}
							}
						}
					} else {
						// Folder doesn't exist so create it
						if (!mkdir($path, 0777, true)) {
							$report .= sprintf(__("The Symposium images/media path could not be created (%s), check rights and re-run the Integrity Check", "wp-symposium"), $path);
						} else {
							$report .= sprintf(__("The Symposium images/media path (%s) was created", "wp-symposium"), $path);
						}
					}
					
					// Remove any users with user_id = Null
					$sql = "DELETE FROM ".$wpdb->base_prefix."usermeta WHERE user_id IS Null";
					$wpdb->query($sql);

					// Get a list of users that have duplicate keys in wp_usermeta
					$sql = "SELECT DISTINCT user_id FROM (
							SELECT user_id, meta_key, COUNT( user_id ) AS cnt
							FROM ".$wpdb->base_prefix."usermeta
							GROUP BY user_id, meta_key
							HAVING meta_key LIKE  '%symposium%'
							AND cnt > 1
							) AS results";
					$users = $wpdb->get_results($sql);

					// Loop through each user
					if ($users) {
						foreach ($users AS $user) {

							if ($user->user_id != null) {

								$report .= '<strong>'.sprintf(__("Found duplicate meta_keys for user %d", "wp-symposium"), $user->user_id).'</strong><br />';

								// Get list of WPS meta keys that have duplicates
								$sql = "SELECT DISTINCT meta_key 
										FROM ".$wpdb->base_prefix."usermeta
										WHERE user_id = ".$user->user_id."
										AND meta_key LIKE '%symposium%'";

								$meta_keys = $wpdb->get_results($sql);

								// For each meta_key get latest, delete them all and re-add just one
								if ($meta_keys) {
									foreach ($meta_keys AS $meta) {

										$sql = "SELECT umeta_id, meta_key, meta_value 
												FROM ".$wpdb->base_prefix."usermeta
												WHERE user_id = %d
												AND meta_key =  %s
												ORDER BY umeta_id DESC 
												LIMIT 0 , 1";

										$single = $wpdb->get_row($wpdb->prepare($sql, $user->user_id, $meta->meta_key));

										$report .= sprintf(__("Setting user %d meta_key '%s' as %s", "wp-symposium"), $user->user_id, $single->meta_key, $single->meta_value).'<br />';

										// Do the clean up
										$sql = "DELETE FROM ".$wpdb->base_prefix."usermeta WHERE user_id = %d AND meta_key = %s";
										$wpdb->query($wpdb->prepare($sql, $user->user_id, $single->meta_key));
										update_user_meta( $user->user_id, $single->meta_key, $single->meta_value );

									}
								}
							}
						}
					}
					
					// Remove dead friendships
					$del_count = 0;
					$sql = "SELECT fid from ".$wpdb->base_prefix."symposium_friends f
							left JOIN wp_users u ON u.ID = f.friend_from
							WHERE u.ID is null";
					$orphaned = $wpdb->get_results($wpdb->prepare($sql));
					foreach ($orphaned as $orphan) {
						$sql = "DELETE FROM ".$wpdb->base_prefix."symposium_friends WHERE fid = %d";
						$wpdb->query($wpdb->prepare($sql, $orphan->fid));
						$del_count++;
					}
					$sql = "SELECT fid from ".$wpdb->base_prefix."symposium_friends f
							left JOIN wp_users u ON u.ID = f.friend_to
							WHERE u.ID is null";
					$orphaned = $wpdb->get_results($wpdb->prepare($sql));
					foreach ($orphaned as $orphan) {
						$sql = "DELETE FROM ".$wpdb->base_prefix."symposium_friends WHERE fid = %d";
						$wpdb->query($wpdb->prepare($sql, $orphan->fid));
						$del_count++;
					}
					if ($del_count) $report .= sprintf(__("%d orphaned friendships removed.", "wp-symposium"), $del_count).'<br />';

					// Filter
					$report = apply_filters( 'symposium_integrity_check_hook', $report );						

					// Done
					echo "<div style='margin-top:15px;margin-right:15px; border:1px solid #060;background-color: #9f9; border-radius:5px;padding-left:8px; margin-bottom:10px;'>";
					if ($report == '') { $report = __('No problems found.', 'wp-symposium'); }
					echo __("Integrity check completed.", "wp-symposium")."<br />".$report;
					echo "</div>";
					
				}
	
							
			   	echo "<p>".__('You should run the integrity check regularly, preferably daily. Before reporting a support request, please run the WPS integrity check. This will remove potential inaccuracies within the database.', 'wp-symposium')."</p>";
	
			   	echo '<form method="post" action="#ric">';
				echo '<input type="hidden" name="symposium_ric" value="Y">';
			   	echo '<p></p><input type="submit" name="Submit" class="button-primary" value="'.__('Run integrity check', 'wp-symposium').'" /></p>';
			   	echo '</form>';
				
			echo "</div>";
			echo "<div style='width:45%; float:left;'>";

				// Image uploading
				echo '<a name="image"></a>';
			  	echo '<div id="icon-themes" class="icon32"><br /></div>';
			   	echo '<h2>'.__('Image Uploading', 'wp-symposium').'</h2>';
			
				echo "<div>";
		 		echo "<div id='symposium_user_login' style='display:none'>".strtolower($current_user->user_login)."</div>";
				echo "<div id='symposium_user_email' style='display:none'>".strtolower($current_user->user_email)."</div>";
				if (get_option('symposium_img_db') == "on") {
					echo __("<p>You are storing images in the database.</p>", "wp-symposium");
				} else {
					echo __("<p>You are storing images in the file system.</p>", "wp-symposium");			
		
					if (file_exists(get_option('symposium_img_path'))) {
						echo "<p>".sprintf(__('The folder %s exists, where images uploaded will be placed.', 'wp-symposium'), get_option('symposium_img_path'))."</p>";
					} else {
						if (!mkdir(get_option('symposium_img_path'), 0777, true)) {
							echo '<p>Failed to create '.get_option('symposium_img_path').'...</p>';
						} else {
							echo '<p>Created '.get_option('symposium_img_path').'.</p>';
						}
					}
					
					if (get_option('symposium_img_url') == '') {
				   		echo "<p>".$fail.__('You must update the URL for your images on the <a href="admin.php?page=symposium_settings">Settings</a>.', 'wp-symposium').$fail2."</p>";
					} else {
						echo "<p>".__('The URL to your images folder is', 'wp-symposium')." <a href='".get_option('symposium_img_url')."'>".get_option('symposium_img_url')."</a>.</p>";
					}

					$tmpDir = get_option('symposium_img_path').'/tmp';
					$tmpFile = '.txt';
					$tmpFile = time().'.tmp';
					$targetTmpFile = $tmpDir.'/'.$tmpFile;
					
					// Does tmp folder exist?
					if (!file_exists($tmpDir)) {
						if (@mkdir($tmpDir)) {
							echo '<p>'.sprintf(__('The WPS temporary image folder (%s) does not currently exist', 'wp-symposium'), $tmpDir);
							echo __(', and has been created.', 'wp-symposium').'</p>';
						} else {
							echo '<p>'.$fail.sprintf(__('The WPS temporary image folder (%s) does not currently exist', 'wp-symposium'), $tmpDir);
							echo __(', and cound not be created - please check permissions of this path.', 'wp-symposium').$fail2.'</p>';
						}
					} else {
						echo '<p>'.sprintf(__('The WPS temporary image folder (%s) exists.', 'wp-symposium'), $tmpDir).'</p>';
						
						// Check creating a temporary file in tmp
						if (touch($targetTmpFile)) {
							@unlink($targetTmpFile);
							echo "<p>".sprintf(__('Temporary file (%s) created and removed okay.', 'wp-symposium'), $tmpFile)."</p>";
						} else {
							echo '<p>'.$fail.sprintf(__('A temporary file (%s) could not be created (in %s), please check permissions.', 'wp-symposium'), $targetTmpFile, $tmpDir);
						}
					}
					
				}
				echo "</div>";

			echo "</div>";
			echo "<div style='clear:both;'></div>";

			echo "<div style='width:45%; float:left;'>";

				echo "<a name='purge'></a>";
			  	echo '<div id="icon-themes" class="icon32"></div>';
			   	echo '<h2>'.__('Purge forum/chat', 'wp-symposium').'</h2>';

				// Purge chat
				if (isset($_POST['purge_chat']) && $_POST['purge_chat'] != '' && is_numeric($_POST['purge_chat']) ) {
					
					$sql = "SELECT COUNT(chid) FROM ".$wpdb->prefix."symposium_chat WHERE chat_timestamp <= '".date("Y-m-d H:i:s",strtotime('-'.$_POST['purge_chat'].' days'))."'";	
					$cnt = $wpdb->get_var( $wpdb->prepare($sql) );
					$sql = "DELETE FROM ".$wpdb->prefix."symposium_chat WHERE chat_timestamp <= '".date("Y-m-d H:i:s",strtotime('-'.$_POST['purge_chat'].' days'))."'";	
					$wpdb->query( $wpdb->prepare($sql) );
					
					echo "<div style='margin-top:10px; border:1px solid #060;background-color: #9f9; border-radius:5px;padding-left:8px; margin-bottom:10px;'>";
					echo "Chat purged: ".$cnt;
					echo "</div>";
				}
				
				// Purge topics
				if (isset($_POST['purge_topics']) && $_POST['purge_topics'] != '' && is_numeric($_POST['purge_topics']) ) {
					
					$sql = "SELECT tid FROM ".$wpdb->prefix."symposium_topics WHERE topic_started <= '".date("Y-m-d H:i:s",strtotime('-'.$_POST['purge_topics'].' days'))."'";	
					$topics = $wpdb->get_results( $wpdb->prepare($sql) );
					
					$cnt = 0;
					if ($topics) {
						foreach ($topics as $topic) {
							$cnt++;
							$wpdb->query($wpdb->prepare("DELETE FROM ".$wpdb->prefix."symposium_subs WHERE tid = %d", $topic->tid));
							$wpdb->query($wpdb->prepare("DELETE FROM ".$wpdb->prefix."symposium_topics WHERE tid = %d", $topic->tid));
						}
					}
					
					echo "<div style='margin-top:10px; border:1px solid #060;background-color: #9f9; border-radius:5px;padding-left:8px; margin-bottom:10px;'>";
					echo "Topics purged: ".$cnt;
					echo "</div>";
				}

				echo '<p>'.__('Forum activity and chat purged are <strong>deleted</strong> - you cannot undo this! I recommend you take a backup first.', 'wp-symposium').'</p>';
	
				echo '<form action="" method="post"><p>';
				echo __('Chat older than', 'wp-symposium');
					echo ' <input type="text" size="3" name="purge_chat"> ';
					echo __('days', 'wp-symposium')."<br />";
				echo __('Forum topics older than', 'wp-symposium');
					echo ' <input type="text" size="3" name="purge_topics"> ';
					echo __('days', 'wp-symposium')."</p>";
				echo '<input type="submit" class="button-primary delete" value="'.__('Purge', 'wp-symposium').'">';
				echo '</form><br />';
					
			echo "</div>";
			echo "<div style='width:50%; float:left;'>";

				// ********** Daily Digest 
				
			  	echo '<div id="icon-themes" class="icon32"><br /></div>';
			   	echo '<h2>'.__('Daily Digest', 'wp-symposium').'</h2>';
	
				if( isset($_POST[ 'symposium_dailydigest' ]) && $_POST[ 'symposium_dailydigest' ] == 'Y' ) {
					$to_users = $_POST['symposium_dailydigest_users'];
					echo "<div style='border:1px solid #060;background-color: #9f9; border-radius:5px;padding-left:8px; margin-bottom:10px;'>";
					echo "Running... ";
					if ($to_users == "on") {
						echo "Sending summary report and to all users... ";
						$success = symposium_notification_do_jobs('send_admin_summary_and_to_users');
					} else {
						echo "Sending summary report only... ";
						$success = symposium_notification_do_jobs('send_admin_summary_only');
					}			
					echo $success;
					echo "Complete.<br />";
					if ($success == 'OK') {
						echo "Summary report sent to ".get_bloginfo('admin_email').".";
					}
					echo "</div>";
				}
			   	echo '<p>'.__('The Daily Digest also performs some basic database cleanup operations, which can be run at any time', 'wp-symposium').'.</p>';
			   	echo '<p>'.__('It will also email an admin summary report. Choose below to optionally send Daily Digest immediately to all subscribed users', 'wp-symposium').'.</p>';
			   	echo '<form method="post" action="">';
				echo '<input type="hidden" name="symposium_dailydigest" value="Y">';
			   	echo '<input type="checkbox" name="symposium_dailydigest_users" > '.__('Send Daily Digest to users', 'wp-symposium');
			   	echo '<p class="submit"><input type="submit" name="Submit" class="button-primary" value="'.__('Send Daily Digest', 'wp-symposium').'" /></p>';
			   	echo '</form>';
			   				
			echo "</div>";

			echo "<div style='clear:both;'></div>";
				
			// ********** Stylesheets
	
		  	echo '<div id="icon-themes" class="icon32"><br /></div>';
		   	echo '<h2>'.__('Stylesheets', 'wp-symposium').'</h2>';
	
			// CSS check
			$myStyleFile = WP_PLUGIN_DIR . '/wp-symposium/css/'.get_option('symposium_wps_css_file');
			if ( !file_exists($myStyleFile) ) {
				echo $fail . sprintf(__('Stylesheet (%s) not found.', 'wp-symposium'), $myStyleFile) . $fail2;
			} else {
				echo "<p style='color:green; font-weight:bold;'>" . sprintf(__('Stylesheet (%s) found.', 'wp-symposium'), $myStyleFile) . "</p>";
			}
				
			// ********** Javascript
	
		  	echo '<div id="icon-themes" class="icon32"><br /></div>';
		   	echo '<h2>'.__('Javascript', 'wp-symposium').'</h2>';
	
			// JS check
			$myJSfile = WP_PLUGIN_DIR . '/wp-symposium/js/'.get_option('symposium_wps_js_file');
			if ( !file_exists($myJSfile) ) {
				echo $fail . sprintf(__('Javascript file (%s) not found.', 'wp-symposium'), $myJSfile) . $fail2;
			} else {
				echo "<p style='color:green; font-weight:bold;'>" . sprintf(__("Javascript file (%s) found.", 'wp-symposium'), $myJSfile) . "</p>";
			}
			echo "<p>" . __("If you find that certain WPS things don't work, like buttons or uploading profile photos, it is probably because the Symposium Javascript file isn't loading and/or working. Usually, this is because of another WordPress plugin. Try deactivating all non-WPS plugins and switching to the TwentyEleven theme. If WPS then works, re-activate the plug-ins one at a time until the error re-occurs, this will help you locate the plugin that is clashing. Then switch your theme back. Also try using Firefox, with the Firebug add-in installed - this will show you where the Javascript error is occuring.", 'wp-symposium')."</p>";
			echo "<p>".__("If you are experiencing problems, <a href='http://www.wpsymposium.com/trythisfirst' target='_blank'>try this first</a>.", "wp-symposium")."</p>";
				  	
			echo "<div id='jstest'>".$fail.__( "You have problems with Javascript. This may be because a plugin is loading another version of jQuery or jQuery UI - try deactivating all plugins apart from WPS plugins, and re-activate them one at a time until the error re-occurs, this will help you locate the plugin that is clashing. It might also be because there is an error in a JS file, either the symposium.js or another plugin script.", "wp-symposium").$fail2."</div>";

	
			// ********** bbPress migration
			
			echo '<a name="bbpress"></a>';
		  	echo '<div id="icon-themes" class="icon32"><br /></div>';
		   	echo '<h2>'.__('bbPress Migration', 'wp-symposium').'</h2>';
	
			// migrate any chosen bbPress forums
			if( isset($_POST[ 'symposium_bbpress' ]) && $_POST[ 'symposium_bbpress' ] == 'Y' ) {
				$id = $_POST['bbPress_forum'];
				$cat_title = $_POST['bbPress_category'];
				
				$success = true;
				$success_message = "";
				
				if ($cat_title != '') {
					
					$sql = "SELECT * FROM ".$wpdb->prefix."posts WHERE post_type = 'forum' AND ID = %d";
					$forum = $wpdb->get_row($wpdb->prepare($sql, $id));
					$success_message .= "Creating &quot;".$cat_title."&quot; from &quot;".$forum->post_title."&quot;. ";

					// Add new forum category
					if ( $wpdb->query( $wpdb->prepare( "
						INSERT INTO ".$wpdb->prefix.'symposium_cats'."
						( 	title, 
							cat_parent,
							listorder,
							cat_desc,
							allow_new
						)
						VALUES ( %s, %d, %d, %s, %s )", 
						array(
							$cat_title, 
							0,
							0,
							$forum->post_content,
							'on'
							) 
						) )
					) {
						
						$success_message .= __("Forum created OK.", "wp-symposium")."<br />";
						
						$new_forum_id = $wpdb->insert_id;

						$sql = "SELECT * FROM ".$wpdb->prefix."posts WHERE post_type = 'topic' AND post_parent = %d";
						$topics = $wpdb->get_results($wpdb->prepare($sql, $id));
						$success_message .= "Migrating topics to &quot;".$cat_title."&quot;.<br />";
						
						if ($topics) {
							
							$failed = 0;
							foreach ($topics AS $topic) {
								
								if ( $wpdb->query( $wpdb->prepare( "
									INSERT INTO ".$wpdb->prefix."symposium_topics 
									( 	topic_subject,
										topic_category, 
										topic_post, 
										topic_date, 
										topic_started, 
										topic_owner, 
										topic_parent, 
										topic_views,
										topic_approved,
										for_info,
										topic_group
									)
									VALUES ( %s, %d, %s, %s, %s, %d, %d, %d, %s, %s, %d )", 
									array(
										$topic->post_title, 
										$new_forum_id,
										$topic->post_content, 
										$topic->post_modified,
										$topic->post_date, 
										$topic->post_author, 
										0,
										0,
										'on',
										'',
										0
										) 
									) ) ) {

										$success_message .= "Migrated &quot;".$topic->post_title."&quot; OK.<br />";	
										
										$new_topic_id = $wpdb->insert_id;
				
										$sql = "SELECT * FROM ".$wpdb->prefix."posts WHERE post_type = 'reply' AND post_parent = %d";
										$replies = $wpdb->get_results($wpdb->prepare($sql, $topic->ID));
										
										if ($replies) {
											$success_message .= "Migrating replies to &quot;".$topic->post_title."&quot; OK. ";	
										
											$failed_replies = 0;
											foreach ($replies AS $reply) {

												if ( $wpdb->query( $wpdb->prepare( "
												INSERT INTO ".$wpdb->prefix."symposium_topics
												( 	topic_subject, 
													topic_category,
													topic_post, 
													topic_date, 
													topic_started, 
													topic_owner, 
													topic_parent, 
													topic_views,
													topic_approved,
													topic_group,
													topic_answer
												)
												VALUES ( %s, %d, %s, %s, %s, %d, %d, %d, %s, %d, %s )", 
												array(
													'', 
													$new_forum_id,
													$reply->post_content, 
													$reply->post_modified,
													$reply->post_date, 
													$reply->post_author, 
													$new_topic_id,
													0,
													'on',
													0,
													''
													) 
												) ) ) {
												} else {
													$failed_replies++;
												}
												
											}

											if ($failed_replies == 0) {
					
												$success_message .= __("Replies migrated OK.", "wp-symposium")."<br />";
												
											} else {
												$success_message .= sprintf(__("Failed to migrate %d replies.", "wp-symposium"), $failed_replies)."<br />";
												$success = false;
											}

										} else {
											$success_message .= __("No replies to migrate.", "wp-symposium")."<br />";
										}
								
								} else {
									$failed++;
								}
								   
							}
							
							if ($failed == 0) {
	
								$success_message .= __("Topics and replies migrated OK.", "wp-symposium")."<br />";
								
							} else {
								$success_message .= sprintf(__("Failed to migrate %d topics.", "wp-symposium"), $failed)."<br />";
								$success = false;
							}
						} else {
								$success_message .= __("No topics to migrate.", "wp-symposium")."<br />";
						}
						
					} else {
						$success_message .= __("Forum failed to migrate", "wp-symposium")."<br />";
						$success_message .= $wpdb->last_query."<br />";
						$success = false;
					}
						
						
				} else {
					$success_message .= __('Please enter a new forum category title', 'wp-symposium');
				}
				
				if ($success) {
					echo "<div style='margin-top:10px;border:1px solid #060;background-color: #9f9; border-radius:5px;padding-left:8px; margin-bottom:10px;'>";
					echo $success_message;
					echo "Complete.<br />";			
					echo "</div>";
				} else {
					echo "<div style='margin-top:10px;border:1px solid #600;background-color: #f99; border-radius:5px;padding-left:8px; margin-bottom:10px;'>";
					echo $success_message;
					echo "</div>";
				}
				
			}
	
			// check to see if any bbPress forums exist
			$sql = "SELECT * FROM ".$wpdb->prefix."posts WHERE post_type = 'forum'";
			$forums = $wpdb->get_results($sql);
			if ($forums) {
			   	echo '<p>'.__('If you have bbPress v2 plugin forums, you can migrate them to your WP Symposium forum as a new category.', 'wp-symposium').'</p>';
			   	echo '<p>'.__('This migration works with the <a href="" target="_blank">WordPress bbPress plugin v2</a>. If you are running a previous or stand-alone version of bbPress, you should upgrade your installation first.', 'wp-symposium').'</p>';
			   	echo '<p>'.__('You should take a backup of your database before migrating, just in case there is a problem.', 'wp-symposium').'</p>';
			   	echo '<form method="post" action="#bbpress">';
				echo '<input type="hidden" name="symposium_bbpress" value="Y">';
			   	echo __('Select forum to migrate:', 'wp-symposium').' ';
				echo '<select name="bbPress_forum">';
				foreach ($forums AS $forum) {
					echo '<option value="'.$forum->ID.'">'.$forum->post_title.'</option>';
				}
				echo '</select><br />';
			   	echo __('Enter new forum category title:', 'wp-symposium').' ';
			   	echo '<input type="text" name="bbPress_category" />';
			   	echo '<p><em>' . __("Although your bbPress forum is not altered, and only new categories/topics/replies are added, it is recommended that you backup your database first.", 'wp-symposium') . '</em></p>';
			   	echo '<p class="submit"><input type="submit" name="Submit" class="button-primary" value="'.__('Migrate bbPress', 'wp-symposium').'" /></p>';
			   	echo '</form>';
			} else {
			   	echo '<p>'.__('No bbPress forums found', 'wp-symposium').'.</p>';
			}


			// ********** Mingle migration
			
			echo '<a name="mingle"></a>';
		  	echo '<div id="icon-themes" class="icon32"><br /></div>';
		   	echo '<h2>'.__('Mingle Migration', 'wp-symposium').'</h2>';

			// migrate any chosen mingle forums
			if( isset($_POST[ 'symposium_mingle' ]) && $_POST[ 'symposium_mingle' ] == 'Y' ) {
				$id = $_POST['mingle_forum'];
				$cat_title = $_POST['mingle_category'];
				
				$success = true;
				$success_message = "";
				
				if ($cat_title != '') {
					
					$sql = "SELECT * FROM ".$wpdb->prefix."forum_forums WHERE id = %d";
					$forum = $wpdb->get_row($wpdb->prepare($sql, $id));
					$success_message .= "Creating &quot;".$cat_title."&quot; from &quot;".$forum->name."&quot;. ";

					// Add new forum category
					if ( $wpdb->query( $wpdb->prepare( "
						INSERT INTO ".$wpdb->prefix.'symposium_cats'."
						( 	title, 
							cat_parent,
							listorder,
							cat_desc,
							allow_new
						)
						VALUES ( %s, %d, %d, %s, %s )", 
						array(
							$cat_title, 
							0,
							0,
							$forum->description,
							'on'
							) 
						) )
					) {
						
						$success_message .= __("Forum created OK.", "wp-symposium")."<br />";
						
						$new_forum_id = $wpdb->insert_id;
						
						// Get Mingle threads	
						$sql = "SELECT * FROM ".$wpdb->prefix."forum_threads WHERE parent_id = %d";
						$topics = $wpdb->get_results($wpdb->prepare($sql, $id));
						$success_message .= "Migrating topics to &quot;".$cat_title."&quot;.<br />";
						
						if ($topics) {
							
							$failed = 0;								
							foreach ($topics AS $topic) {
								
								if ( $wpdb->query( $wpdb->prepare( "
									INSERT INTO ".$wpdb->prefix."symposium_topics 
									( 	topic_subject,
										topic_category, 
										topic_post, 
										topic_date, 
										topic_started, 
										topic_owner, 
										topic_parent, 
										topic_views,
										topic_approved,
										for_info,
										topic_group
									)
									VALUES ( %s, %d, %s, %s, %s, %d, %d, %d, %s, %s, %d )", 
									array(
										$topic->subject, 
										$new_forum_id,
										'nopost', 
										$topic->last_post,
										$topic->date, 
										$topic->starter, 
										0,
										0,
										'on',
										'',
										0
										) 
									) ) ) {
										
										// Set up topic, now add all the replies	
										$success_message .= "Migrated &quot;".$topic->subject."&quot; OK.<br />";	
										
										$new_topic_id = $wpdb->insert_id;
				
										$sql = "SELECT * FROM ".$wpdb->prefix."forum_posts WHERE parent_id = %d";
										$replies = $wpdb->get_results($wpdb->prepare($sql, $topic->id));
										
										if ($replies) {
											$success_message .= "Migrating replies to &quot;".$topic->subject."&quot;.<br />";	
										
											$failed_replies = 0;
											$done_first_reply = false;
											foreach ($replies AS $reply) {
												
												if ($done_first_reply) {

													if ( $wpdb->query( $wpdb->prepare( "
													INSERT INTO ".$wpdb->prefix."symposium_topics
													( 	topic_subject, 
														topic_category,
														topic_post, 
														topic_date, 
														topic_started, 
														topic_owner, 
														topic_parent, 
														topic_views,
														topic_approved,
														topic_group,
														topic_answer
													)
													VALUES ( %s, %d, %s, %s, %s, %d, %d, %d, %s, %d, %s )", 
													array(
														'', 
														$new_forum_id,
														$reply->text, 
														$reply->date,
														$reply->date, 
														$reply->author_id, 
														$new_topic_id,
														0,
														'on',
														0,
														''
														) 
													) ) ) {
													} else {
														$failed_replies++;
													}
													
												} else {
													$done_first_reply = true;
													if ( $wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix."symposium_topics SET topic_post = '".$reply->text."' WHERE tid = ".$new_topic_id) ) ) {
														$success_message .= "Updated topic with intial post OK.<br />";
													} else {
														$failed_replies++;
													}	
													
												}
												
											}

											if ($failed_replies == 0) {
					
												$success_message .= __("Replies migrated OK.", "wp-symposium")."<br />";
												
											} else {
												$success_message .= sprintf(__("Failed to migrate %d replies.", "wp-symposium"), $failed_replies)."<br />";
												$success = false;
											}

										} else {
											$success_message .= __("No replies to migrate.", "wp-symposium")."<br />";
										}
																		
								} else {
									$failed++;
								}
								   
							}
							
							if ($failed == 0) {
	
								$success_message .= __("Topics and replies migrated OK.", "wp-symposium")."<br />";
								
							} else {
								$success_message .= sprintf(__("Failed to migrate %d topics.", "wp-symposium"), $failed)."<br />";
								$success = false;
							}
						} else {
								$success_message .= __("No topics to migrate.", "wp-symposium")."<br />";
						}
						
					} else {
						$success_message .= __("Forum failed to migrate", "wp-symposium")."<br />";
						$success_message .= $wpdb->last_query."<br />";
						$success = false;
					}
						
						
				} else {
					$success_message .= __('Please enter a new forum category title', 'wp-symposium');
				}
				
				if ($success) {
					echo "<div style='margin-top:10px;border:1px solid #060;background-color: #9f9; border-radius:5px;padding-left:8px; margin-bottom:10px;'>";
						echo 'Please now check the forum for your new migrated category. If you need to, you can move the position of the category (or delete it) in <a href="admin.php?page=symposium_categories">forum categories</a>.<br />';
						echo 'Migration complete. ';
						echo '<a href="javascript:void(0)" class="symposium_expand">View report</a>';
						echo '<div class="expand_this" style="display:none">';
							echo $success_message;
						echo "</div>";
					echo "</div>";
				} else {
					echo "<div style='margin-top:10px;border:1px solid #600;background-color: #f99; border-radius:5px;padding-left:8px; margin-bottom:10px;'>";
					echo $success_message;
					echo "</div>";
				}
				
			}
	
			// check to see if any Mingle forums exist
			if($wpdb->get_var("show tables like '%".$wpdb->prefix."forum_forums%'") == $wpdb->prefix."forum_forums") {
				$sql = "SELECT * FROM ".$wpdb->prefix."forum_forums";
				$forums = $wpdb->get_results($sql);
				if ($forums) {
				   	echo '<p>'.__('If you have the Mingle v1.0.33 (or higher) plugin, you can migrate the forums to your WP Symposium forum as a new category.', 'wp-symposium').'</p>';
				   	echo '<p>'.__('This migration works with the <a href="" target="_blank">WordPress Mingle plugin</a>. If you are running a previous version of Mingle, you should upgrade your installation first.', 'wp-symposium').'</p>';
				   	echo '<p>'.__('You should take a backup of your database before migrating, just in case there is a problem.', 'wp-symposium').'</p>';
				   	echo '<form method="post" action="#mingle">';
					echo '<input type="hidden" name="symposium_mingle" value="Y">';
				   	echo __('Select forum to migrate:', 'wp-symposium').' ';
					echo '<select name="mingle_forum">';
					foreach ($forums AS $forum) {
						echo '<option value="'.$forum->id.'">'.$forum->name.' ('.$forum->description.')</option>';
					}
					echo '</select><br />';
				   	echo __('Enter new forum category title:', 'wp-symposium').' ';
				   	echo '<input type="text" name="mingle_category" />';
				   	echo '<p><em>' . __("Although your Mingle forum is not altered, and only new categories/topics/replies are added, it is recommended that you backup your database first.", 'wp-symposium') . '</em></p>';
				   	echo '<p class="submit"><input type="submit" name="Submit" class="button-primary" value="'.__('Migrate Mingle', 'wp-symposium').'" /></p>';
				   	echo '</form>';
				} else {
				   	echo '<p>'.__('No Mingle forums found', 'wp-symposium').'.</p>';
				}
			} else {
				   	echo '<p>'.__('Mingle forum not installed', 'wp-symposium').'.</p>';
			}
		   	

	  	echo '</div>'; 	


	} // end admin check	
		
}
	  
function symposium_rrmdir($dir) {
   if (is_dir($dir)) {
	 $objects = scandir($dir);
	 foreach ($objects as $object) {
	   if ($object != "." && $object != "..") {
		 if (filetype($dir."/".$object) == "dir") symposium_rrmdir($dir."/".$object); else unlink($dir."/".$object);
	   }
	 }
	 reset($objects);
	 rmdir($dir);
   }
}  

function install_row($name, $shortcode, $function, $config_url, $plugin_dir, $settings_url, $install_help) {

	if (function_exists($function)) {
		
		global $wpdb;
		$install_help = str_replace('\\', '/', $install_help);
		if (strpos($name, " ") ) {
			list($name, $ver, $rc) = explode(" ",$name." !");
			if ($rc != '!') { $ver .= ' '.$rc; }
		} else {
			$ver = '';
		}
		$name = str_replace('_', ' ', $name);
	
		echo '<tr>';
			
			// Name of Plugin
			echo '<td style="height:30px">';
				echo $name;
				if ($ver != 'v'.WPS_VER && $ver != '') {
					echo ' '.$ver;
					echo '<br />';
					echo '<a href="http://www.wpsymposium.com/downloadinstall" target="_blank">';
					echo __('Upgrade Required', 'wp-symposium').'</a>';
					$status = 'upgrade';
				}
			echo '</td>';
					
			// Shortcode on a page?
			$sql = "SELECT ID FROM ".$wpdb->prefix."posts WHERE lower(post_content) LIKE '%[".$shortcode."]%' AND post_type = 'page' AND post_status = 'publish';";
			$pages = $wpdb->get_results($sql);	
			if ( ($pages) && ($shortcode != '') ) {
				$page = $pages[0];
				$url = str_replace(get_bloginfo('url'), '', get_permalink($page->ID));
				echo '<td>';
					echo '<a href="'.get_permalink($page->ID).'" target="_blank">'.$url.'</a> ';
					echo '[<a href="post.php?post='.$page->ID.'&action=edit">'.__('Edit', 'wp-symposium').'</a>] ';
					if ($status == 'tick') {
						if ($settings_url != '') {
							echo '[<a href="'.$settings_url.'">'.__('Configure', 'wp-symposium').'</a>]';
						}
					}
				if ($url != $config_url && $status != 'cross') $status = 'error';
				if ($config_url == '-') $status = 'tick';
				echo '</td>';
			} else {
				$url = '';
				echo '<td>';
				if ( ($status != 'cross') && ($status != 'notinstalled') && ($shortcode != '') ) {
					$status = 'add';
					echo '<div style="float:left; width:175px">'.sprintf(__('Add [%s] to:', 'wp-symposium'), $shortcode).'</div>';
					echo '<input type="submit" class="button symposium_addnewpage" id="'.$name.'" title="'.$shortcode.'" value="'.__('New Page', 'wp-symposium').'" />';
					$sql = "SELECT * FROM ".$wpdb->prefix."posts WHERE post_status = 'publish' AND post_type = 'page' ORDER BY post_title";
					$pages = $wpdb->get_results($sql);
					if ($pages) {
						echo ' '.__('or', 'wp-symposium').' ';
						echo '<select id="symposium_pagechoice_'.$shortcode.'" style="width:120px">';
						foreach ($pages as $page) {
							echo '<option value="'.$page->ID.'">'.$page->post_title;
						}
						echo '</select> ';
						echo '<input type="submit" class="button symposium_addtopage" id="'.$name.'" title="'.$shortcode.'" value="'.__('Add', 'wp-symposium').'" />';
					}
				} else {
					if ($status == 'tick') {
						if ($settings_url != '') {
							echo '[<a href="'.$settings_url.'">'.__('Configure', 'wp-symposium').'</a>]';
						}
					}
					if ($function == 'add_notification_bar') {
						if (current_user_can('update_core'))
							echo ' [<a href="http://www.wpswiki.com/index.php?title=Chat_options" target="_blank">'.__('Read this!', 'wp-symposium').'</a>]';
					}
					if ($status == '') $status = 'tick';
				}
				echo '</td>';
			}
			
			// Status
			echo '<td style="text-align:center">';
	
				// Fix URL
				$fixed_url = false;
				$current_value = get_option('symposium_'.strtolower($name).'_url');
					if ($current_value != $url) {
						update_option('symposium_'.strtolower($name).'_url', $url);
						$fixed_url = true;
						if ($url != '') {
							echo '[<a href="javascript:void(0)" class="symposium_help" title="'.__("URL updated successfully. It is important to visit this page to complete installation; after you add a WP Symposium shortcode to a page; change pages with WP Symposium shortcodes; if you change WordPress Permalinks; or if you experience problems.", "wp-symposium").'">'.__('Updated ok!', 'wp-symposium').'</a>]';
						} else {
							echo '[<a href="javascript:void(0)" class="symposium_help" title="'.__("URL removed. It is important to visit this page to complete installation; after you add a WP Symposium shortcode to a page; change pages with WP Symposium shortcodes; if you change WordPress Permalinks; or if you experience problems.", "wp-symposium").'">'.__('URL removed', 'wp-symposium').'</a>]';
						}
					} else {
						if ($current_value) {
							$status = 'tick';
						}
					}
				
				if (!$fixed_url) {
						
					if ($status == 'notinstalled') {
						if ($function != 'symposium_gallery') {
							echo '[<a href="javascript:void(0)" class="symposium_help" title="'.$install_help.'">'.__('Install', 'wp-symposium').'</a>]';
						} else {
							echo __('Coming soon', 'wp-symposium');
						}
					}
					if ($status == 'tick') {
						echo '<img src="'.get_option('symposium_images').'/smilies/good.png" />';
					}
					if ($status == 'upgrade') {
						echo '<img src="'.get_option('symposium_images').'/warning.png" />';
					}
					if ($status == 'cross') {			
						echo '[<a href="plugins.php?plugin_status=inactive">'.__('Activate', 'wp-symposium').'</a>]';
					}
		
					if ($status == 'add') {
						echo '<img src="'.get_option('symposium_images').'/'.$status.'.png" />';
					}
					
				}
				
			echo '</td>';
	
			// Setting in database
			if (current_user_can('update_core')) {
				echo '<td class="symposium_url" style="background-color:#efefef">';
				
					$value = get_option('symposium_'.strtolower($name).'_url');
					if (!$value && $status != 'add') { 
						echo 'n/a';
					} else {
						if ($value != 'Important: Please update!') {
							echo $value;
						}	
					}
				echo '</td>';
			}
			
		echo '</tr>';
		
	}

}

function symposium_field_exists($tablename, $fieldname) {
	global $wpdb;
	$fields = $wpdb->get_results("SHOW fields FROM ".$tablename." LIKE '".$fieldname."'");

	if ($fields) {
		return true;
	} else {
		echo __('Missing Field', 'wp-symposium').": ".$fieldname."<br />";
		return false;
	}

	return true;
}

function symposium_plugin_bar() {

  	echo '<div class="wrap">';
  	echo '<div id="icon-themes" class="icon32"><br /></div>';
  	echo '<h2>'.__('Panel Options', 'wp-symposium').'</h2><br />';

	global $wpdb;

		// See if the user has posted notification bar settings
		if( isset($_POST[ 'symposium_update' ]) && $_POST[ 'symposium_update' ] == 'symposium_plugin_bar' ) {

			update_option('symposium_use_chat', isset($_POST[ 'use_chat' ]) ? $_POST[ 'use_chat' ] : '');
			update_option('symposium_use_chatroom', isset($_POST[ 'use_chatroom' ]) ? $_POST[ 'use_chatroom' ] : '');
			update_option('symposium_chatroom_banned', $_POST[ 'chatroom_banned' ]);
			update_option('symposium_bar_polling', $_POST[ 'bar_polling' ]);
			update_option('symposium_chat_polling', $_POST[ 'chat_polling' ]);
			update_option('symposium_use_wp_profile', isset($_POST[ 'use_wp_profile' ]) ? $_POST[ 'use_wp_profile' ] : '');
			update_option('symposium_wps_panel_all', isset($_POST[ 'wps_panel_all' ]) ? $_POST[ 'wps_panel_all' ] : '');
			
			// Put an settings updated message on the screen
			echo "<div class='updated slideaway'><p>".__('Saved', 'wp-symposium').".</p></div>";
			
		}


		echo '<div class="metabox-holder"><div id="toc" class="postbox">';

			if (!function_exists('symposium_profile')) { 		
				echo "<div class='error'><p>".__('The Profile plugin must be activated for chat windows to work. The chat room will work without the Profile plugin.', 'wp-symposium')."</p></div>";
			} 
			?>
			
			<form method="post" action=""> 
			<input type="hidden" name="symposium_update" value="symposium_plugin_bar">
		
			<table class="form-table">
			
			<tr valign="top"> 
			<td scope="row"><label for="wps_panel_all"><?php echo __('Show all members', 'wp-symposium'); ?></label></td>
			<td>
			<input type="checkbox" name="wps_panel_all" id="wps_panel_all" 
				<?php 
				if (get_option('symposium_wps_panel_all') == "on") { echo "CHECKED"; } 
				?> 
			/>
			<span class="description"><?php echo __('Enable to include all members, disable to only include friends', 'wp-symposium'); ?></span></td> 
			</tr> 
		
			<tr valign="top"> 
			<td scope="row"><label for="use_chat"><?php echo __('Enable chat windows', 'wp-symposium'); ?></label></td>
			<td>
			<input type="checkbox" name="use_chat" id="use_chat" 
				<?php 
				if (!function_exists('symposium_profile')) { echo 'disabled="disabled" '; }
				if (get_option('symposium_use_chat') == "on") { echo "CHECKED"; } 
				?>
			/>
			<span class="description"><?php echo __('Real-time chat windows', 'wp-symposium'); ?></span></td> 
			</tr> 
		
			<tr valign="top"> 
			<td scope="row"><label for="use_chatroom"><?php echo __('Enable chatroom', 'wp-symposium'); ?></label></td>
			<td>
			<input type="checkbox" name="use_chatroom" id="use_chatroom" <?php if (get_option('symposium_use_chatroom') == "on") { echo "CHECKED"; } ?>/>
			<span class="description"><?php echo __('Real-time chatroom (chat seen by all members)', 'wp-symposium'); ?></span></td> 
			</tr> 

			<tr valign="top"> 
			<td scope="row"><label for="chatroom_banned"><?php echo __('Banned chatroom words', 'wp-symposium'); ?></label></td> 
			<td><input name="chatroom_banned" type="text" id="chatroom_banned"  value="<?php echo get_option('symposium_chatroom_banned'); ?>" /> 
			<span class="description"><?php echo __('Comma separated list of words not allowed in the chatroom', 'wp-symposium'); ?></td> 
			</tr> 
										
			<tr valign="top"> 
			<td scope="row"><label for="bar_polling"><?php echo __('Polling Intervals', 'wp-symposium'); ?></label></td> 
			<td><input name="bar_polling" type="text" id="bar_polling"  value="<?php echo get_option('symposium_bar_polling'); ?>" /> 
			<span class="description"><?php echo __('Frequency of checks for new mail, friends online, etc, in seconds', 'wp-symposium'); ?></td> 
			</tr> 
						
			<tr valign="top"> 
			<td scope="row"><label for="chat_polling">&nbsp;</label></td> 
			<td><input name="chat_polling" type="text" id="chat_polling"  value="<?php echo get_option('symposium_chat_polling'); ?>" /> 
			<span class="description"><?php echo __('Frequency of chat window updates in seconds', 'wp-symposium'); ?></td> 
			</tr> 

			<tr valign="top"> 
			<td scope="row"><label for="use_wp_profile"><?php echo __('Profile Link', 'wp-symposium'); ?></label></td> 
			<td><input type="checkbox" name="use_wp_profile" id="use_wp_profile" <?php if (get_option('symposium_use_wp_profile') == "on") { echo "CHECKED"; } ?>/>
			<span class="description"><?php echo __('Link to WordPress user profile page?', 'wp-symposium'); ?></td> 
			</tr> 


			</table> 
			 
			<p class="submit" style="margin-left:6px"> 
			<input type="submit" name="Submit" class="button-primary" value="<?php echo __('Save Changes', 'wp-symposium'); ?>" /> 
			</p> 
			</form> 
			
			<p style="margin-left:6px">
			<strong><?php echo __('Notes:', 'wp-symposium'); ?></strong>
			<ol>
			<li><?php echo __('The polling intervals occur in addition to an initial check on each page load.', 'wp-symposium'); ?></li>
			<li><?php echo __('The more frequent the polling intervals, the greater the load on your server.', 'wp-symposium'); ?></li>
			<li><?php echo __('Disabling chat windows will reduce the load on the server.', 'wp-symposium'); ?></li>
			</ol>
			</div>
			
			<?php
		echo '</div></div>';
				
	echo '</div>';
}

function symposium_plugin_profile() {

	echo '<div class="wrap">';
	echo '<div id="icon-themes" class="icon32"><br /></div>';
  	echo '<h2>'.__('Profile Options', 'wp-symposium').'</h2><br />';

	global $wpdb;
	global $user_ID;
	get_currentuserinfo();
	
	include_once( ABSPATH . 'wp-includes/formatting.php' );
	
		// Delete an extended field?
   		if ( isset($_GET['del_eid']) && $_GET['del_eid'] != '') {

			// get slug
			$sql = "SELECT extended_slug from ".$wpdb->prefix."symposium_extended WHERE eid = %d";
			$slug = $wpdb->query($wpdb->prepare($sql, $_GET['del_eid']));

			// now delete extended field
			$wpdb->query( $wpdb->prepare( "DELETE FROM ".$wpdb->prefix.'symposium_extended'." WHERE eid = %d", $_GET['del_eid']  ) );
				
			// finally delete all of these extended fields
			$sql = "DELETE FROM ".$wpdb->base_prefix."usermeta WHERE meta_key = 'symposium_".$slug."'";
			$wpdb->query($wpdb->prepare($sql));

		}	

		// See if the user has posted profile settings
			if( isset($_POST[ 'symposium_update' ]) && $_POST[ 'symposium_update' ] == 'symposium_plugin_profile' ) {

			update_option('symposium_online', $_POST['online'] != '' ? $_POST['online'] : 5);
			update_option('symposium_offline', $_POST['offline'] != '' ? $_POST['offline'] : 15);
			update_option('symposium_use_poke', isset($_POST['use_poke']) ? $_POST['use_poke'] : '');
			update_option('symposium_poke_label', $_POST['poke_label'] != '' ? $_POST['poke_label'] : __('Hey!', "wp-symposium"));
			update_option('symposium_status_label', $_POST['status_label'] != '' ? str_replace("'", "`", $_POST['status_label']) : __('What`s up?', "wp-symposium"));
			update_option('symposium_enable_password', isset($_POST['enable_password']) ? $_POST['enable_password'] : '');
			update_option('symposium_show_wall_extras', isset($_POST['show_wall_extras']) ? $_POST['show_wall_extras'] : '');
			update_option('symposium_profile_google_map', $_POST['profile_google_map'] != '' ? $_POST['profile_google_map'] : 250);
			update_option('symposium_profile_comments', isset($_POST['profile_comments']) ? $_POST['profile_comments'] : '');
			update_option('symposium_show_dob', isset($_POST['show_dob']) ? $_POST['show_dob'] : '');
			update_option('symposium_profile_avatars', isset($_POST['profile_avatars']) ? $_POST['profile_avatars'] : '');
			update_option('symposium_initial_friend', $_POST['initial_friend']);
			update_option('symposium_redirect_wp_profile', isset($_POST['redirect_wp_profile']) ? $_POST['redirect_wp_profile'] : '');
			update_option('symposium_menu_my_activity', isset($_POST['menu_my_activity']) ? $_POST['menu_my_activity'] : '');
			update_option('symposium_menu_friends_activity', isset($_POST['menu_friends_activity']) ? $_POST['menu_friends_activity'] : '');
			update_option('symposium_menu_all_activity', isset($_POST['menu_all_activity']) ? $_POST['menu_all_activity'] : '');
			update_option('symposium_menu_profile', isset($_POST['menu_profile']) ? $_POST['menu_profile'] : '');
			update_option('symposium_menu_friends', isset($_POST['menu_friends']) ? $_POST['menu_friends'] : '');
			update_option('symposium_menu_texthtml', isset($_POST['menu_texthtml']) ? $_POST['menu_texthtml'] : '');
			update_option('symposium_menu_profile', isset($_POST['menu_profile']) ? $_POST['menu_profile'] : '');
			update_option('symposium_menu_mentions', isset($_POST['menu_mentions']) ? $_POST['menu_mentions'] : '');
			update_option('symposium_profile_show_unchecked', isset($_POST['profile_show_unchecked']) ? $_POST['profile_show_unchecked'] : '');
			update_option('symposium_wps_profile_default', isset($_POST['wps_profile_default']) ? $_POST['wps_profile_default'] : '');
			update_option('symposium_wps_use_gravatar', isset($_POST['wps_use_gravatar']) ? $_POST['wps_use_gravatar'] : '');
			update_option('symposium_hide_location', isset($_POST['symposium_hide_location']) ? $_POST['symposium_hide_location'] : '');

			
			// Update extended fields
	   		if (isset($_POST['eid']) && $_POST['eid'] != '') {
		   		$range = array_keys($_POST['eid']);
				foreach ($range as $key) {
					$eid = $_POST['eid'][$key];
					$order = $_POST['order'][$key];
					$type = $_POST['type'][$key];
					$default = $_POST['default'][$key];
					$readonly = $_POST['readonly'][$key];
					$name = $_POST['name'][$key];
					$slug = strtolower(preg_replace("/[^A-Za-z0-9_]/", '',$_POST['slug'][$key]));
					if (in_array($slug, array( "city", "country" ))) $slug .= '_2';
					$wp_usermeta = $_POST['wp_usermeta'][$key];
					$old_wp_usermeta = $_POST['old_wp_usermeta'][$key];
					
					if ( $wp_usermeta != $old_wp_usermeta ) {
						// Hook for connecting/disconnecting EF to/from WP metadata, do something with user data based on admin's choice
						do_action('symposium_update_extended_metadata_hook', $slug, $wp_usermeta, $old_wp_usermeta);
					}
					
					$wpdb->query( $wpdb->prepare( "
						UPDATE ".$wpdb->prefix.'symposium_extended'."
						SET extended_name = %s, extended_order = %s, extended_slug = %s, extended_type = %s, readonly = %s, extended_default = %s, wp_usermeta = %s
						WHERE eid = %d", 
						$name, $order, $slug, $type, $readonly, $default, $wp_usermeta, $eid ) );
				}		
			}
			
			// Add new extended field if applicable
			if ($_POST['new_name'] != '' && $_POST['new_name'] != __('New label', 'wp-symposium') ) {

				if ( ( $_POST['new_slug'] == '' ) || ( $_POST['new_slug'] == __('New slug', 'wp-symposium') ) ) { $slug = $_POST['new_name']; } else { $slug = $_POST['new_slug']; }
				$slug = sanitize_title_with_dashes( $slug );
				$slug = substr( $slug, 0, 64 );
				
				if (in_array($slug, array( "city", "country" ))) $slug .= '_2';

				$wpdb->query( $wpdb->prepare( "
					INSERT INTO ".$wpdb->prefix.'symposium_extended'."
					( 	extended_name, 
						extended_order,
						extended_slug,
						readonly,
						extended_type,
						extended_default,
						wp_usermeta
					)
					VALUES ( %s, %d, %s, %s, %s, %s, %s )", 
					array(
						$_POST['new_name'], 
						$_POST['new_order'],
						$slug,
						$_POST['new_readonly'],
						$_POST['new_type'],
						$_POST['new_default'],
						$_POST['new_wp_usermeta']
					) 
				) );
			}
			
			// Put an settings updated message on the screen
			echo "<div class='updated slideaway'><p>".__('Saved', 'wp-symposium').".</p></div>";
			
		}

				echo '<div class="metabox-holder"><div id="toc" class="postbox">';

					?>
						
					<form method="post" action=""> 
					<input type="hidden" name="symposium_update" value="symposium_plugin_profile">
				
					<table class="form-table"> 

					<tr valign="top"> 
					<td scope="row"><label for="redirect_wp_profile"><?php echo __('Redirect profile page', 'wp-symposium'); ?></label></td>
					<td>
					<input type="checkbox" name="redirect_wp_profile" id="redirect_wp_profile" <?php if (get_option('symposium_redirect_wp_profile') == "on") { echo "CHECKED"; } ?>/>
					<span class="description"><?php echo __('Redirect WordPress generated links for WordPress profile page to WPS profile page', 'wp-symposium'); ?></span></td> 
					</tr> 
				
					<tr valign="top">
					<td scope="row"><label for="wps_default_profile"><?php echo __('Default profile view', 'wp-symposium'); ?></label></td> 
					<td>
					<select name="wps_profile_default">
						<option value='extended'<?php if (get_option('symposium_wps_profile_default') == 'extended') { echo ' SELECTED'; } ?>><?php echo __('Profile', 'wp-symposium'); ?></option>
						<option value='wall'<?php if (get_option('symposium_wps_profile_default') == 'wall') { echo ' SELECTED'; } ?>><?php echo __('My activity', 'wp-symposium'); ?></option>
						<option value='activity'<?php if (get_option('symposium_wps_profile_default') == 'activity') { echo ' SELECTED'; } ?>><?php echo __('Friends activity (excludes my activity)', 'wp-symposium'); ?></option>
						<option value='all'<?php if (get_option('symposium_wps_profile_default') == 'all') { echo ' SELECTED'; } ?>><?php echo __('All activity', 'wp-symposium'); ?></option>
					</select> 
					<span class="description"><?php echo __("Default view for the member's own profile page", "wp-symposium"); ?></span></td> 
					</tr> 		

					<tr valign="top"> 
					<td scope="row"><label for="initial_friend"><?php echo __('Default Friend', 'wp-symposium'); ?></label></td> 
					<td><input name="initial_friend" type="text" id="initial_friend"  value="<?php echo get_option('symposium_initial_friend'); ?>" /> 
					<span class="description"><?php echo __('Comma separated list of user ID\'s that automatically become friends of new users (leave blank for no-one)', 'wp-symposium'); ?></span></td> 
					</tr> 

					<tr valign="top"> 
					<td scope="row"><label for="profile_avatars"><?php echo __('Profile Photos', 'wp-symposium'); ?></label></td>
					<td>
					<input type="checkbox" name="profile_avatars" id="profile_avatars" <?php if (get_option('symposium_profile_avatars') == "on") { echo "CHECKED"; } ?>/>
					<span class="description"><?php echo __('Allow members to upload their own profile photos, over-riding the internal WordPress avatars', 'wp-symposium'); ?></span></td> 
					</tr> 

					<tr valign="top"> 
					<td scope="row"><label for="wps_use_gravatar"><?php echo __('Use Gravatar', 'wp-symposium'); ?></label></td>
					<td>
					<input type="checkbox" name="wps_use_gravatar" id="wps_use_gravatar" <?php if (get_option('symposium_wps_use_gravatar') == "on") { echo "CHECKED"; } ?>/>
					<span class="description"><?php echo __('If allowing member to upload profile photos, should <a href="http://www.gravatar.com" target="_blank">gravatar</a> be used if they have not yet done so?', 'wp-symposium'); ?></span></td> 
					</tr> 

					<tr valign="top"> 
					<td scope="row"><label for="menu_profile"><?php echo __('Profile Menu Items', 'wp-symposium'); ?></label></td>
					<td>
					<input type="checkbox" name="menu_profile" id="menu_profile" <?php if (get_option('symposium_menu_profile') == "on") { echo "CHECKED"; } ?>/>
					<span class="description"><?php echo __('Profile', 'wp-symposium'); ?></span></td> 
					</tr> 

					<tr valign="top"> 
					<td scope="row"></td>
					<td>
					<input type="checkbox" name="menu_my_activity" id="menu_my_activity" <?php if (get_option('symposium_menu_my_activity') == "on") { echo "CHECKED"; } ?>/>
					<span class="description"><?php echo __('My Activity', 'wp-symposium'); ?></span></td> 
					</tr> 

					<tr valign="top"> 
					<td scope="row"></td>
					<td>
					<input type="checkbox" name="menu_friends_activity" id="menu_friends_activity" <?php if (get_option('symposium_menu_friends_activity') == "on") { echo "CHECKED"; } ?>/>
					<span class="description"><?php echo __('Friends Activity', 'wp-symposium'); ?></span></td> 
					</tr> 

					<tr valign="top"> 
					<td scope="row"></td>
					<td>
					<input type="checkbox" name="menu_all_activity" id="menu_all_activity" <?php if (get_option('symposium_menu_all_activity') == "on") { echo "CHECKED"; } ?>/>
					<span class="description"><?php echo __('All Activity', 'wp-symposium'); ?></span></td> 
					</tr> 

					<tr valign="top"> 
					<td scope="row"></td>
					<td>
					<input type="checkbox" name="menu_friends" id="menu_friends" <?php if (get_option('symposium_menu_friends') == "on") { echo "CHECKED"; } ?>/>
					<span class="description"><?php echo __('Friends', 'wp-symposium'); ?></span></td> 
					</tr> 

					<?php if ( function_exists('symposium_profile_plus') ) { ?>
						<tr valign="top"> 
						<td scope="row"></td>
						<td>
						<input type="checkbox" name="menu_mentions" id="menu_mentions" <?php if (get_option('symposium_menu_mentions') == "on") { echo "CHECKED"; } ?>/>
						<span class="description"><?php echo __('Forum @mentions', 'wp-symposium'); ?></span></td> 
						</tr> 
					<?php } else { ?>
						<input type="checkbox" style="display:none" name="menu_mentions" id="menu_mentions" />
					<?php } ?>

					<tr valign="top"> 
					<td scope="row"><label for="menu_texthtml"><?php echo __('Profile Menu Text/HTML', 'wp-symposium'); ?></label></td>
					<td>
					<textarea name="menu_texthtml" id="menu_texthtml" rows="4" cols="22" style="float:left"><?php echo get_option('symposium_menu_texthtml'); ?></textarea>
					<span class="description"><?php echo __('Text/HTML that appears at the end of the profile menu', 'wp-symposium'); ?></span></td> 
					</tr> 

					<tr valign="top"> 
					<td scope="row"><label for="use_poke"><?php echo __('Poke/Nudge/Wink/etc', 'wp-symposium'); ?></label></td>
					<td>
					<input type="checkbox" name="use_poke" id="use_poke" <?php if (get_option('symposium_use_poke') == "on") { echo "CHECKED"; } ?>/>
					<span class="description"><?php echo __('Enable this feature', 'wp-symposium'); ?></span></td> 
					</tr> 

					<tr valign="top"> 
					<td scope="row"><label for="status_label"><?php echo __('Status label', 'wp-symposium'); ?></label></td> 
					<td><input name="status_label" type="text" id="status_label"  value="<?php echo stripslashes(get_option('symposium_status_label')); ?>" /> 
					<span class="description"><?php echo __('The default prompt for new activity posts on the profile page', 'wp-symposium'); ?></span></td> 
					</tr> 

					<tr valign="top"> 
					<td scope="row"><label for="poke_label"><?php echo __('Poke label', 'wp-symposium'); ?></label></td> 
					<td><input name="poke_label" type="text" id="poke_label"  value="<?php echo get_option('symposium_poke_label'); ?>" /> 
					<span class="description"><?php echo __('The "poke" button label for your site, beware of trademarked words (includes Poke and Nudge for example)', 'wp-symposium'); ?></span></td> 
					</tr> 

					<tr valign="top"> 
					<td scope="row"><label for="show_dob"><?php echo __('Use Date of Birth', 'wp-symposium'); ?></label></td>
					<td>
					<input type="checkbox" name="show_dob" id="show_dob" <?php if (get_option('symposium_show_dob') == "on") { echo "CHECKED"; } ?>/>
					<span class="description"><?php echo __('Use date of birth on profile', 'wp-symposium'); ?></span></td> 
					</tr> 
										
					<tr valign="top"> 
					<td scope="row"><label for="show_wall_extras"><?php echo __('Recently Active Friends Box', 'wp-symposium'); ?></label></td>
					<td>
					<input type="checkbox" name="show_wall_extras" id="show_wall_extras" <?php if (get_option('symposium_show_wall_extras') == "on") { echo "CHECKED"; } ?>/>
					<span class="description"><?php echo __('Show Recently Active Friends box on side of wall (may take up space, depending on page template)', 'wp-symposium'); ?></span></td> 
					</tr> 
										
					<tr valign="top"> 
					<td scope="row"><label for="profile_google_map"><?php echo __('Google Map', 'wp-symposium'); ?></label></td> 
					<td><input name="profile_google_map" type="text" id="profile_google_map" style="width:50px" value="<?php echo get_option('symposium_profile_google_map'); ?>" /> 
					<span class="description"><?php echo __('Size of location map, in pixels. eg: 250. Set to 0 to hide.', 'wp-symposium'); ?></span></td> 
					</tr> 
										
					<tr valign="top"> 
					<td scope="row"><label for="profile_comments"><?php echo __('Show comment fields', 'wp-symposium'); ?></label></td>
					<td>
					<input type="checkbox" name="profile_comments" id="profile_comments" <?php if (get_option('symposium_profile_comments') == "on") { echo "CHECKED"; } ?>/>
					<span class="description"><?php echo __('Always show post comment fields (or hover to show)', 'wp-symposium'); ?></span></td> 
					</tr> 
										
					<tr valign="top"> 
					<td scope="row"><label for="symposium_hide_location"><?php echo __('Remove location fields', 'wp-symposium'); ?></label></td>
					<td>
					<input type="checkbox" name="symposium_hide_location" id="symposium_hide_location" <?php if (get_option('symposium_hide_location') == "on") { echo "CHECKED"; } ?>/>
					<span class="description"><?php echo __('Hide and disable location profile fields, and exclude distance from member directory', 'wp-symposium'); ?></span></td> 
					</tr> 

					<tr valign="top"> 
					<td scope="row"><label for="enable_password"><?php echo __('Enable Password Change', 'wp-symposium'); ?></label></td>
					<td>
					<input type="checkbox" name="enable_password" id="enable_password" <?php if (get_option('symposium_enable_password') == "on") { echo "CHECKED"; } ?>/>
					<span class="description"><?php echo __('Allow members to change their password', 'wp-symposium'); ?></span></td> 
					</tr> 

					<tr valign="top"> 
					<td scope="row"><label for="online"><?php echo __('Inactivity period', 'wp-symposium'); ?></label></td> 
					<td><input name="online" type="text" id="online" style="width:50px"  value="<?php echo get_option('symposium_online'); ?>" /> 
					<span class="description"><?php echo __('How many minutes before a member is assumed off-line', 'wp-symposium'); ?></span></td> 
					</tr> 
										
					<tr valign="top"> 
					<td scope="row"><label for="offline">&nbsp;</label></td> 
					<td><input name="offline" type="text" id="offline" style="width:50px"  value="<?php echo get_option('symposium_offline'); ?>" /> 
					<span class="description"><?php echo __('How many minutes before a member is assumed logged out', 'wp-symposium'); ?></span></td> 
					</tr> 
					<?php
						// Hook to add items to the Profile settings page
						echo apply_filters ( 'symposium_profile_settings_before_ef_hook', "" );
					?>						
					<tr valign="top"> 
					<td scope="row"><?php echo __('Extended Fields', 'wp-symposium'); ?></td><td>
					
						<?php
						echo '<input type="checkbox" name="profile_show_unchecked" id="profile_show_unchecked"';
						if (get_option('symposium_profile_show_unchecked') == "on") { echo "CHECKED"; }
						echo '/> <span class="description">'. __('Display checkboxes fields that are not selected (on member profile page)', 'wp-symposium').'</span>';

						// Extended Fields table
						$extensions = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."symposium_extended ORDER BY extended_order, extended_name"));
						$sql = " WHERE meta_key NOT LIKE 'symposium_%'";
						$sql .= " AND meta_key NOT LIKE '%wp_%'";
						$sql .= " AND meta_key NOT LIKE '%level%'";
						$sql .= " AND meta_key NOT LIKE '%role%'";
						$sql .= " AND meta_key NOT LIKE '%capabilit%'";
						$sql = apply_filters( 'symposium_query_wp_metadata_hook', $sql );						
						$rows = $wpdb->get_results("SELECT DISTINCT meta_key FROM ".$wpdb->prefix."usermeta".$sql);
						
						echo '<style>.widefat td { border:0 } </style>';
						echo '<table class="widefat">';
						echo '<thead>';
						echo '<tr>';
						echo '<th style="width:40px">'.__('Order', 'wp-symposium').'</th>';
						echo '<th style="width:40px">'.__('Slug', 'wp-symposium').'</th>';
						echo '<th>'.__('Label', 'wp-symposium').'</th>';
						echo '<th>'.__('Default Value', 'wp-symposium').'</th>';
						echo '<th>'.__('Read Only?', 'wp-symposium').'</th>';
						echo '<th style="width:80px">'.__('Type', 'wp-symposium').'</th>';
						echo '<th style="width:30px">&nbsp;</th>';
						echo '</tr>';
						echo '</thead>';
						echo '<tbody>';
						$cnt = 0;
						if ($extensions) {
							foreach ($extensions as $extension) {

								$slug = (!$extension->extended_slug) ? 'slug_'.$extension->eid : $extension->extended_slug ;
								$cnt++;
								if ( $cnt % 2 != 0 ) {
									echo '<tr>';
								} else {
									echo '<tr style="background-color:#eee">';
								}
									echo '<td>';
									echo '<input type="hidden" name="eid[]" value="'.$extension->eid.'" />';
									echo '<input type="text" name="order[]" style="width:40px" value="'.$extension->extended_order.'" />';
									echo '</td>';
									echo '<td>';
									echo '<input type="hidden" name="slug[]" value="'.$slug.'" />'.$slug;
									echo '</td>';
									echo '<td>';
									echo '<input type="text" name="name[]" value="'.stripslashes($extension->extended_name).'" />';
									echo '</td>';
									echo '<td>';
									echo '<input type="text" name="default[]" value="'.stripslashes($extension->extended_default).'" />';
									echo '</td>';
									echo '<td>';
									echo '<select name="readonly[]">';
									echo '<option value=""';
										if ($extension->readonly != 'on') echo ' SELECTED';
										echo '>'.__('No', 'wp-symposium').'</option>';
									echo '<option value="on"';
										if ($extension->readonly == 'on') echo ' SELECTED';
										echo '>'.__('Yes', 'wp-symposium').'</option>';
									echo '</select>';
									echo '</td>';
									echo '<td>';
									echo '<select name="type[]">';
									echo '<option value="Text"';
										if ($extension->extended_type == 'Text') { echo ' SELECTED'; }
										echo '>'.__('Text', 'wp-symposium').'</option>';
									echo '<option value="Checkbox"';
										if ($extension->extended_type == 'Checkbox') { echo ' SELECTED'; }
										echo '>'.__('Checkbox', 'wp-symposium').'</option>';
									echo '<option value="List"';
										if ($extension->extended_type == 'List') { echo ' SELECTED'; }
										echo '>'.__('List', 'wp-symposium').'</option>';
									echo '<option value="Textarea"';
										if ($extension->extended_type == 'Textarea') { echo ' SELECTED'; }
										echo '>'.__('Textarea', 'wp-symposium').'</option>';
									echo '</select>';
									echo '</td>';
									echo '<td>';
									echo "<a href='admin.php?page=symposium_profile&view=profile&del_eid=".$extension->eid."' class='delete'>".__('Delete', 'wp-symposium')."</a>";
									echo '</td>';
								echo '</tr>';
								if ( $cnt % 2 != 0 ) {
									echo '<tr>';
								} else {
									echo '<tr style="background-color:#eee">';
								}
								echo '<td colspan="2"></td><td colspan="5">';
									echo __('Linked WP Metadata', 'wp-symposium').':<br />';
                                    echo '<input type="hidden" name="old_wp_usermeta[]" value="'.$extension->wp_usermeta.'" />';
									echo '<select name="wp_usermeta[]"><option value="" SELECTED></option>';
									if ($rows) {
										foreach ($rows as $row) {
											echo '<option value="'.$row->meta_key .'"';
											if ( $row->meta_key == $extension->wp_usermeta ) { echo ' SELECTED'; }
											echo '>'.$row->meta_key.'</option>';
										}
									}
									echo '</select>';
								echo '</td>';
								echo '</tr>';
							}
						}
						echo '</table>';
						
						echo '<tr valign="top">';
						echo '<td scope="row">'.__('Add extended field', 'wp-symposium').'</td><td>';

						echo '<table class="widefat">';
						echo '<thead><tr>';
						echo '<th style="width:40px">'.__('Order', 'wp-symposium').'</th>';
						echo '<th style="width:40px">'.__('Slug', 'wp-symposium').'</th>';
						echo '<th>'.__('Label', 'wp-symposium').'</th>';
						echo '<th>'.__('Default Value', 'wp-symposium').'</th>';
						echo '<th>&nbsp;</th>';
						echo '<th style="width:80px">'.__('Type', 'wp-symposium').'</th>';
						echo '<th style="width:30px">&nbsp;</th>';
						echo '</tr></thead>';
						echo '<tr>';
							echo '<td>';
							echo '<input type="text" name="new_order" style="width:40px" onclick="javascript:this.value = \'\'" value="0" />';
							echo '</td>';
							echo '<td>';
							echo '<input type="text" name="new_slug" style="width:75px" onclick="javascript:this.value = \'\'" value="'.__('New slug', 'wp-symposium').'" />';
							echo '</td>';
							echo '<td>';
							echo '<input type="text" name="new_name" onclick="javascript:this.value = \'\'" value="'.__('New label', 'wp-symposium').'" />';
							echo '</td>';
							echo '<td>';
							echo '<input type="text" name="new_default" onclick="javascript:this.value = \'\'" value="" />';
							echo '</td>';
							echo '<td>';
							echo '<select name="new_readonly">';
							echo '<option value="" SELECTED>'.__('No', 'wp-symposium').'</option>';
							echo '<option value="on">'.__('Yes', 'wp-symposium').'</option>';
							echo '</select>';
							echo '</td>';
							echo '<td>';
							echo '<select name="new_type">';
							echo '<option value="Text" SELECTED>'.__('Text', 'wp-symposium').'</option>';
							echo '<option value="Checkbox">'.__('Checkbox', 'wp-symposium').'</option>';
							echo '<option value="List">'.__('List', 'wp-symposium').'</option>';
							echo '<option value="Textarea">'.__('Textarea', 'wp-symposium').'</option>';
							echo '</select>';
							echo '</td>';
							echo '<td>&nbsp;</td>';
						echo '</tr>';
						echo '<tr>';
							echo '<td colspan="2"></td><td colspan="5">';
							echo __('Linked WP Metadata', 'wp-symposium').':<br />';
							echo '<select name="new_wp_usermeta"><option value="" SELECTED></option>';
							if ($rows) {
								foreach ($rows as $row) {
									echo '<option value="'.$row->meta_key .'">'.$row->meta_key.'</option>';
								}
							}
							echo '</select>';
							echo '</td>';
						echo '</tr>';
						echo '<tr><td colspan="7"><span class="description">';
						echo __('For lists, enter all the values separated by commas - the first value is the default choice.', 'wp-symposium');
						echo '<br />'.__('For checkboxes, enter a value of \'on\' to default to checked.', 'wp-symposium');
						echo '<br />'.__('Slugs should be a single descriptive word.', 'wp-symposium');
						echo '<br />'.__('Members extended field values are not shown when they are left empty, except checkboxes where you can choose what happens above.', 'wp-symposium');

						echo '<br /><br /><strong>'.__('Extended Fields and WordPress Profile Metadata', 'wp-symposium').'</strong>';
						echo '<br />'.__('Extended fields can be linked to WordPress profile metadata - make sure you choose the correct type to match the WordPress profile metadata.', 'wp-symposium');
						echo '<br />'.__('Only link to WordPress profile metadata that you want your user\'s to access, and use the read-only setting to stop them making changes.', 'wp-symposium');

						// Display user info as an example
						$rows = $wpdb->get_results("SELECT meta_key, meta_value".$sql." AND user_id = '".$user_ID."'");
						echo '<br /><br />';
						echo '<input id="symposium_meta_show_button" style="margin-bottom:10px;" onclick="document.getElementById(\'symposium_meta_show\').style.display=\'block\';document.getElementById(\'symposium_meta_show_button\').style.display=\'none\';document.getElementById(\'symposium_meta_show_button_hide\').style.display=\'block\';" value="'.__('Show WP metadata for current user', 'wp-symposium').'" type="button">';
						echo '<input id="symposium_meta_show_button_hide" style="margin-bottom:10px;display:none;" onclick="document.getElementById(\'symposium_meta_show\').style.display=\'none\';document.getElementById(\'symposium_meta_show_button\').style.display=\'block\';document.getElementById(\'symposium_meta_show_button_hide\').style.display=\'none\';" value="'.__('Hide WP metadata', 'wp-symposium').'" type="button">';
						echo '<div id="symposium_meta_show" style="display:none;">';
						
						echo '<table class="widefat" style="width:400px"><thead><tr>';
						echo '<th>'.__('WP Metadata', 'wp-symposium').'</th>';
						echo '<th>'.__('Value', 'wp-symposium').'</th>';
						echo '</tr></thead><tbody>';
						foreach ($rows as $row) {
							echo '<tr><td>'.$row->meta_key.'</td><td>';
							$meta_value = maybe_unserialize($row->meta_value);
							if (is_array($meta_value)) {
								echo '<input class="regular-text all-options disabled" type="text" value="'.__('SERIALIZED DATA', 'wp-symposium').'" disabled="disabled" />';
							} else {
								// let's cut very long strings in parts so that browsers display them correctly
								$v = str_replace(",", ", ", $row->meta_value);
								$v = str_replace(";", "; ", $v);
								echo $v;
							}
							echo '</td></tr>';
						}
						echo '</tbody></table>';
						echo '</div>';
						
						echo '</td></tr></tbody></table>'; // class="widefat"
						
						// Hook to add items to the Profile settings page
						echo apply_filters ( 'symposium_profile_settings_hook', "" );
						
						echo '</table>'; // class="form-table"
					?>
					</td></tr>
					
					</table>
	
					<?php
					echo '<p class="submit" style="margin-left:6px">';
					echo '<input type="submit" name="Submit" class="button-primary" value="'.__('Save Changes', 'wp-symposium').'" />';
					echo '</p>';
					echo '</form>';
					
				echo '</div></div>';
	echo '</div>';									  

}

function symposium_plugin_settings() {

  	echo '<div class="wrap">';
  	echo '<div id="icon-themes" class="icon32"><br /></div>';
  	echo '<h2>'.__('Settings', 'wp-symposium').'</h2><br />';

	global $wpdb;

		// See if the user has posted general settings
		if( isset($_POST[ 'symposium_update' ]) && $_POST[ 'symposium_update' ] == 'symposium_plugin_settings' ) {

			update_option('symposium_footer', $_POST[ 'email_footer' ]);
			update_option('symposium_from_email', $_POST[ 'from_email' ]);
			update_option('symposium_jquery', isset($_POST[ 'jquery' ]) ? $_POST[ 'jquery' ] : '');
			update_option('symposium_jqueryui', isset($_POST[ 'jqueryui' ]) ? $_POST[ 'jqueryui' ] : '');
			update_option('symposium_emoticons', isset($_POST[ 'emoticons' ]) ? $_POST[ 'emoticons' ] : '');
			update_option('symposium_wp_width', str_replace('%', 'pc', ($_POST[ 'wp_width' ])));
			update_option('symposium_wp_alignment', $_POST[ 'wp_alignment' ]);
			update_option('symposium_img_db', isset($_POST[ 'img_db' ]) ? $_POST[ 'img_db' ] : '');
			update_option('symposium_img_path', $_POST[ 'img_path' ]);
			update_option('symposium_img_url', $_POST[ 'img_url' ]);
			update_option('symposium_img_crop', isset($_POST[ 'img_crop' ]) ? $_POST[ 'img_crop' ] : '');
			update_option('symposium_show_buttons', isset($_POST[ 'show_buttons' ]) ? $_POST[ 'show_buttons' ] : '');
			update_option('symposium_striptags', isset($_POST[ 'striptags' ]) ? $_POST[ 'striptags' ] : '');
			update_option('symposium_image_ext', strtolower($_POST[ 'image_ext' ]));
			update_option('symposium_video_ext', strtolower($_POST[ 'video_ext' ]));
			update_option('symposium_doc_ext', strtolower($_POST[ 'doc_ext' ]));
			update_option('symposium_elastic', isset($_POST[ 'elastic' ]) ? $_POST[ 'elastic' ] : '');
			update_option('symposium_images', $_POST[ 'images' ]);
			update_option('symposium_wps_lite', isset($_POST[ 'wps_lite' ]) ? $_POST[ 'wps_lite' ] : '');
			update_option('symposium_wps_time_out', $_POST[ 'wps_time_out' ] != '' ? $_POST[ 'wps_time_out' ] : 0);
			update_option('symposium_wps_js_file', $_POST[ 'wps_js_file' ]);
			update_option('symposium_wps_css_file', $_POST[ 'wps_css_file' ]);
			update_option('symposium_allow_reports', isset($_POST[ 'allow_reports' ]) ? $_POST[ 'allow_reports' ] : '');
			update_option('symposium_ajax_widgets', isset($_POST[ 'wps_ajax_widgets' ]) ? $_POST[ 'wps_ajax_widgets' ] : '');
			update_option('symposium_jscharts', isset($_POST[ 'jscharts' ]) ? $_POST[ 'jscharts' ] : '');
			update_option('symposium_subject_mail_new', $_POST[ 'subject_mail_new' ]);
			update_option('symposium_subject_forum_new', $_POST[ 'subject_forum_new' ]);
			update_option('symposium_subject_forum_reply', $_POST[ 'subject_forum_reply' ]);
			update_option('symposium_debug_mode', isset($_POST[ 'debug_mode' ]) ? $_POST[ 'debug_mode' ] : '');
			
			
			echo "<div class='updated slideaway'>";
			
			// Making content path if it doesn't exist
			$img_db = isset($_POST[ 'img_db' ]) ? $_POST[ 'img_db' ] : '';
			if ($img_db != 'on') {
				
				if (!file_exists($_POST[ 'img_path' ])) {
					if (!mkdir($_POST[ 'img_path' ], 0777, true)) {
						echo '<p>Failed to create '.$_POST[ 'img_path' ].'...</p>';
					} else {
						echo '<p>Created '.$_POST[ 'img_path' ].'.</p>';
					}
				}
			
			}
			
			// Put an settings updated message on the screen
			echo "<p>".__('Saved', 'wp-symposium').".</p></div>";
			
		}

				echo '<div class="metabox-holder"><div id="toc" class="postbox">';

					?>
									
					<form method="post" action=""> 
					<input type="hidden" name="symposium_update" value="symposium_plugin_settings">

					<table class="form-table"> 

					<tr valign="top"> 
					<td scope="row"><label for="wps_time_out"><?php echo __('Script time out', 'wp-symposium'); ?></label></td>
					<td><input name="wps_time_out" type="text" id="wps_time_out" style="width:50px" value="<?php echo get_option('symposium_wps_time_out'); ?>"/> 
					<span class="description"><?php echo __('Maximum PHP script time out value, set to 0 to disable this setting.', 'wp-symposium'); ?></span></td> 
					</tr> 

					<tr valign="top">
					<td scope="row"><label for="wps_js_file"><?php echo __('WPS JS files', 'wp-symposium'); ?></label></td> 
					<td>
					<select name="wps_js_file">
						<option value='wps.min.js'<?php if (get_option('symposium_wps_js_file') == 'wps.min.js') { echo ' SELECTED'; } ?>><?php echo __('Minimized', 'wp-symposium'); ?></option>
						<option value='wps.js'<?php if (get_option('symposium_wps_js_file') == 'wps.js') { echo ' SELECTED'; } ?>><?php echo __('Normal', 'wp-symposium'); ?></option>
					</select> 
					<span class="description"><?php echo __('Minimized loads faster, Normal can be edited', 'wp-symposium'); ?></span></td> 
					</tr> 		
					
					<tr valign="top">
					<td scope="row"><label for="wps_css_file"><?php echo __('WPS CSS files', 'wp-symposium'); ?></label></td> 
					<td>
					<select name="wps_css_file">
						<option value='wps.min.css'<?php if (get_option('symposium_wps_css_file') == 'wps.min.css') { echo ' SELECTED'; } ?>><?php echo __('Minimized', 'wp-symposium'); ?></option>
						<option value='wps.css'<?php if (get_option('symposium_wps_css_file') == 'wps.css') { echo ' SELECTED'; } ?>><?php echo __('Normal', 'wp-symposium'); ?></option>
					</select> 
					<span class="description"><?php echo __('Minimized loads faster, Normal can be edited', 'wp-symposium'); ?></span></td> 
					</tr> 		
					
					<tr valign="top"> 
					<td scope="row" style="width:150px;"><label for="wps_ajax_widgets"><?php echo __('Widgets AJAX mode', 'wp-symposium'); ?></label></td>
					<td>
					<input type="checkbox" name="wps_ajax_widgets" id="wps_ajax_widgets" <?php if (get_option('symposium_ajax_widgets') == "on") { echo "CHECKED"; } ?>/>
					<span class="description"><?php echo __("Use AJAX to load WPS widgets (or load with page).", 'wp-symposium'); ?></span></td> 
					</tr> 

					<tr valign="top"> 
					<td scope="row" style="width:150px;"><label for="wps_lite"><?php echo __('Enable LITE mode', 'wp-symposium'); ?></label></td>
					<td>
					<input type="checkbox" name="wps_lite" id="wps_lite" <?php if (get_option('symposium_wps_lite') == "on") { echo "CHECKED"; } ?>/>
					<span class="description"><?php echo __("Recommended for shared hosting, or where server load is an issue.", 'wp-symposium'); ?></span></td> 
					</tr> 

					<?php if (get_option('symposium_wps_lite') == "on") { ?>
						
						<tr valign="top"></tr> 
						<td></td><td style="border:1px dotted #999; background-color: #fff;">
							<strong><?php echo __('WP Symposium LITE mode', 'wp-symposium'); ?></strong>
							<p>
							<?php echo __('You are running WP Symposium in LITE mode, which reduces server load, but disables/reduces certain features of the WP Symposium plugins, and will take priority over any other settings you have made.', 'wp-symposium').' '; ?>
							<?php echo __('If you activate additional plugins, return to this page to see an updated list below.', 'wp-symposium'); ?>
							</p>

							<p><?php echo __('To improve performance further, it is recommended that you:', 'wp-symposium'); ?></p>
							<ul style="list-style-type: circle; margin: 10px 0 20px 30px;">
								<li><?php echo __('minimize the total number of all WordPress plugins and widgets used (WP Symposium and others). <a href="plugins.php?plugin_status=active">De-activate as many as possible!</a>', 'wp-symposium'); ?></li>
								<?php if (function_exists('add_notification_bar')) { ?>
									<li><?php echo __('<a href="plugins.php?plugin_status=active">De-activate Panel</a> or <a href="admin.php?page=symposium_bar">set the polling intervals</a> high, eg: at least 300 and 20 seconds.', 'wp-symposium'); ?></li>
								<?php } ?>
								<?php if (function_exists('symposium_news_main')) { ?>
									<li><?php echo __('<a href="plugins.php?plugin_status=active">De-activate Alerts</a> or <a href="admin.php?page=wp-symposium/symposium_news_admin.php">set the polling interval</a> high, eg: at least 120 seconds.', 'wp-symposium'); ?></li>
								<?php } ?>
							</ul>
							
							<?php if (function_exists('add_notification_bar')) { ?>
								<p><strong><?php echo __('Panel', 'wp-symposium'); ?></strong></p>
								<ul style="list-style-type: circle; margin: 10px 0 10px 30px;">
									<li><?php echo __('Chat windows and the chatroom are disabled', 'wp-symposium'); ?></li>
									<li><?php echo __('Notification of new mail (etc) requires a page reload', 'wp-symposium'); ?></li>
								</ul>
							<?php } ?>
							
							<?php if (function_exists('symposium_news_main')) { ?>
							<p><strong><?php echo __('Alerts', 'wp-symposium'); ?></strong></p>
							<ul style="list-style-type: circle; margin: 10px 0 10px 30px;">
								<li><?php echo __('Live notification of new messages disabled (page reload required)', 'wp-symposium'); ?></li>
							</ul>
							<?php } ?>
							
							<p><strong><?php echo __('Forum', 'wp-symposium'); ?></strong></p>
							<ul style="list-style-type: circle; margin: 10px 0 10px 30px;">
								<li><?php echo __('Topic, post and reply counts are not displayed', 'wp-symposium'); ?></li>
								<li><?php echo __('Only new topics are shown, not latest replies', 'wp-symposium'); ?></li>
								<li><?php echo __('Answered topics not shown in topics list', 'wp-symposium'); ?></li>
								<li><?php echo __('Simplified breadcrumbs (forum navigation links)', 'wp-symposium'); ?></li>
								<li><?php echo __('Smilies/emoticons not replaced with images', 'wp-symposium'); ?></li>
								<li><?php echo __('User @tagging will not work', 'wp-symposium'); ?></li>
							</ul>
							
							<p><strong><?php echo __('Member Directory', 'wp-symposium'); ?></strong></p>
							<ul style="list-style-type: circle; margin: 10px 0 10px 30px;">
								<li><?php echo __('Latest activity post not shown', 'wp-symposium'); ?></li>
								<li><?php echo __('Add as a friend/Send Mail buttons disabled', 'wp-symposium'); ?></li>
							</ul>
							
							<p><strong><?php echo __('Profile', 'wp-symposium'); ?></strong></p>
							<ul style="list-style-type: circle; margin: 10px 0 10px 30px;">
								<li><?php echo __('Friends: Latest activity post not shown', 'wp-symposium'); ?></li>
							</ul>
							
						</td>
						</tr> 	
					
					<?php } ?>
										
					<tr valign="top"> 
					<td scope="row"><label for="img_db"><?php echo __('Store uploads in database', 'wp-symposium'); ?></label></td>
					<td>
					<input type="checkbox" name="img_db" id="img_db" <?php if (get_option('symposium_img_db') == "on") { echo "CHECKED"; } ?>/>
					<span class="description"><?php echo __('Off by default to save to the file system (recommended). Select to upload to database', 'wp-symposium').' - '; ?><span style='font-weight:bold; text-decoration: underline'><?php echo __("if you change, images will have to be reloaded, they remain in their storage 'state'.", 'wp-symposium'); ?></span></span></td> 
					</tr> 
					
					<?php if (get_option('symposium_img_db') != "on") { ?>
						
						<tr valign="top" style='background-color: #ccc;'> 
						<td scope="row"><label for="img_path"><?php echo __('Images directory', 'wp-symposium'); ?></label></td> 
						<td><input name="img_path" type="text" id="img_path"  value="<?php echo get_option('symposium_img_path'); ?>" class="regular-text" /> 
						<span class="description">
						<?php echo __('Path to images directory, eg:', 'wp-symposium').' '.WP_CONTENT_DIR.'/wps-content'; ?>
						<input type="button" onclick="document.getElementById('img_path').value='<?php echo WP_CONTENT_DIR.'/wps-content'; ?>'" value="<?php _e('Suggest', 'wp-symposium'); ?>" class="button" /></td> 
						</tr> 					
						
						<tr valign="top" style='background-color: #ccc;'> 
						<td scope="row"><label for="img_url"><?php echo __('Images URL', 'wp-symposium'); ?></label></td> 
						<td><input name="img_url" type="text" id="img_url"  value="<?php echo get_option('symposium_img_url'); ?>" class="regular-text" /> 
						<?php $url = WP_CONTENT_URL.'/wps-content'; $url = str_replace(siteURL(), '', $url); ?>
						<span class="description"><?php echo __('URL to the images folder, Do not include http:// or your domain name eg: ', 'wp-symposium').' <a href="'.$url.'">'.$url.'</a>'; ?>
						<input type="button" onclick="document.getElementById('img_url').value='<?php echo $url; ?>'" value="<?php _e('Suggest', 'wp-symposium'); ?>" class="button" /></td> 
						</tr> 					

						<tr valign="top" style='background-color: #ccc;'> 
						<td colspan=2>
							<?php $img_tmp = ini_get('upload_tmp_dir'); ?>
							<?php echo __('For information, from PHP.INI on your server, the PHP temporary upload folder is:', 'wp-symposium').' '.$img_tmp; ?>
							<?php if ($img_tmp == '') { echo '<strong>'.__("You need to <a href='http://uk.php.net/manual/en/ini.core.php#ini.upload-tmp-dir'>set this in your php.ini</a> file", 'wp-symposium').'</strong>'; } ?>
						</td>
						</tr> 	

					<?php } else { ?>

						<input name="img_path" type="hidden" id="img_path"  value="<?php echo get_option('symposium_img_path'); ?>" /> 
						<input name="img_url" type="hidden" id="img_url"  value="<?php echo get_option('symposium_img_url'); ?>" /> 
						
					<?php } ?>

					<tr valign="top"> 
					<td scope="row"><label for="images"><?php echo __('WPS images URL', 'wp-symposium'); ?></label></td> 
					<td><input name="images" type="text" id="images" class="regular-text" value="<?php echo get_option('symposium_images'); ?>"/> 
					<span class="description"><?php echo __('Change if you want to create your own set of custom images.', 'wp-symposium'); ?></span>
					<input type="button" onclick="document.getElementById('images').value='<?php echo str_replace(siteURL(), '', WP_PLUGIN_URL.'/wp-symposium/images'); ?>'" value="<?php _e('Suggest', 'wp-symposium'); ?>" class="button" /></td> 
					</tr> 
						
					<tr valign="top"> 
					<td scope="row"><label for="img_crop"><?php echo __('Crop avatar images', 'wp-symposium'); ?></label></td>
					<td>
					<input type="checkbox" name="img_crop" id="img_crop" <?php if (get_option('symposium_img_crop') == "on") { echo "CHECKED"; } ?>/>
					<span class="description"><?php echo __("Allow uploaded images to be cropped</span>", 'wp-symposium'); ?></span></td> 
					</tr> 

					<tr valign="top"> 
					<td scope="row"><label for="image_ext"><?php echo __('Image extensions', 'wp-symposium'); ?></label></td> 
					<td><input name="image_ext" type="text" id="image_ext" class="regular-text" value="<?php echo get_option('symposium_image_ext'); ?>"/> 
					<span class="description"><?php echo __('A comma separated list of permitted file extensions, leave blank for none. *.jpg,*.jpeg,*.png and *.gif supported.', 'wp-symposium'); ?></span></td> 
					</tr> 

					<?php if (get_option('symposium_img_db') != "on") { ?>

						<tr valign="top"> 
						<td scope="row"><label for="video_ext"><?php echo __('Video extensions', 'wp-symposium'); ?></label></td> 
						<td><input name="video_ext" type="text" id="video_ext" class="regular-text" value="<?php echo get_option('symposium_video_ext'); ?>"/> 
						<span class="description"><?php echo sprintf(__('A comma separated list of permitted file extensions, leave blank for none. H.264 format supported, <a %s>see here</a>.', 'wp-symposium'), 'href="http://www.longtailvideo.com/support/jw-player/jw-player-for-flash-v5/12539/supported-video-and-audio-formats" target="_blank"'); ?></span></td> 
						</tr> 
	
						<tr valign="top"> 
						<td scope="row"><label for="doc_ext"><?php echo __('Document extensions', 'wp-symposium'); ?></label></td> 
						<td><input name="doc_ext" type="text" id="doc_ext" class="regular-text" value="<?php echo get_option('symposium_doc_ext'); ?>"/> 
						<span class="description"><?php echo __('A comma separated list of permitted file extensions, leave blank for none. Viewed in separate window or downloaded.', 'wp-symposium'); ?></span></td> 
						</tr> 
						
					<?php } else { ?>

						<tr valign="top"> 
						<td scope="row"><label for="video_ext"><?php echo __('Video extensions', 'wp-symposium'); ?></label></td> 
						<td><input name="video_ext" type="hidden" id="video_ext" value="<?php echo get_option('symposium_video_ext'); ?>"/> 
						<span class="description"><?php echo __('Sorry, videos can only be saved when storing to the filesystem.', 'wp-symposium'); ?></span></td> 
						</tr> 
	
						<tr valign="top"> 
						<td scope="row"><label for="doc_ext"><?php echo __('Document extensions', 'wp-symposium'); ?></label></td> 
						<td><input name="doc_ext" type="hidden" id="doc_ext" value="<?php echo get_option('symposium_doc_ext'); ?>"/> 
						<span class="description"><?php echo __('Sorry, documents can only be saved when storing to the filesystem.', 'wp-symposium'); ?></span></td> 
						</tr> 

					<?php } ?>
					
					<tr valign="top"> 
					<td scope="row"><label for="email_footer"><?php echo __('Email Notifications', 'wp-symposium'); ?></label></td> 
					<td><input name="email_footer" type="text" id="email_footer"  value="<?php echo stripslashes(get_option('symposium_footer')); ?>" class="regular-text" /> 
					<span class="description"><?php echo __('Footer appended to notification emails', 'wp-symposium'); ?></span></td> 
					</tr> 

					<tr valign="top"> 
					<td scope="row"><label for="from_email">&nbsp;</label></td> 
					<td><input name="from_email" type="text" id="from_email"  value="<?php echo get_option('symposium_from_email'); ?>" class="regular-text" /> 
					<span class="description"><?php echo __('Email address used for email notifications', 'wp-symposium'); ?></span></td> 
					</tr> 
												
					<tr valign="top"> 
					<td scope="row"><label for="subject_mail_new"><?php echo __('Mail subject lines', 'wp-symposium'); ?></label></td> 
					<td><input name="subject_mail_new" type="text" id="subject_mail_new"  value="<?php echo stripslashes(get_option('symposium_subject_mail_new')); ?>" class="regular-text" /> 
					<span class="description"><?php echo __('New Mail Message, [subject] will be replaced by the message subject', 'wp-symposium'); ?></span></td> 
					</tr> 

					<tr valign="top"> 
					<td scope="row"><label for="subject_forum_new">&nbsp;</label></td> 
					<td><input name="subject_forum_new" type="text" id="subject_forum_new"  value="<?php echo stripslashes(get_option('symposium_subject_forum_new')); ?>" class="regular-text" /> 
					<span class="description"><?php echo __('New Forum Topic, [topic] will be replaced by the topic subject', 'wp-symposium'); ?></span></td> 
					</tr> 

					<tr valign="top"> 
					<td scope="row"><label for="subject_forum_reply">&nbsp;</label></td> 
					<td><input name="subject_forum_reply" type="text" id="subject_forum_reply"  value="<?php echo stripslashes(get_option('symposium_subject_forum_reply')); ?>" class="regular-text" /> 
					<span class="description"><?php echo __('New Forum Reply, [topic] will be replaced by the topic subject', 'wp-symposium'); ?></span></td> 
					</tr> 
<?php				
					// Hook to add items to the plugin settings page, just under mail titles
					do_action ( 'symposium_plugin_settings_mail_title_hook' );
?>					
					<tr valign="top"> 
					<td scope="row"><label for="wp_width"><?php echo __('Width', 'wp-symposium'); ?></label></td> 
					<td><input name="wp_width" type="text" id="wp_width" style="width:50px" value="<?php echo str_replace('pc', '%', get_option('symposium_wp_width')); ?>"/> 
					<span class="description"><?php echo __('Width of all WP Symposium plugins, eg: 600px or 100%', 'wp-symposium'); ?></span></td> 
					</tr> 

					<tr valign="top">
					<td scope="row"><label for="wp_alignment"><?php echo __('Alignment', 'wp-symposium'); ?></label></td> 
					<td>
					<select name="wp_alignment">
						<option value='Left'<?php if (get_option('symposium_wp_alignment') == 'Left') { echo ' SELECTED'; } ?>><?php echo __('Left', 'wp-symposium'); ?></option>
						<option value='Center'<?php if (get_option('symposium_wp_alignment') == 'Center') { echo ' SELECTED'; } ?>><?php echo __('Center', 'wp-symposium'); ?></option>
						<option value='Right'<?php if (get_option('symposium_wp_alignment') == 'Right') { echo ' SELECTED'; } ?>><?php echo __('Right', 'wp-symposium'); ?></option>
					</select> 
					<span class="description"><?php echo __('Alignment of all WP Symposium plugins', 'wp-symposium'); ?></span></td> 
					</tr> 		

					<tr valign="top"> 
					<td scope="row"><label for="show_buttons"><?php echo __('Buttons on Activity pages', 'wp-symposium'); ?></label></td>
					<td>
					<input type="checkbox" name="show_buttons" id="show_buttons" <?php if (get_option('symposium_show_buttons') == "on") { echo "CHECKED"; } ?>/>
					<span class="description"><?php echo __("Pressing return submits a post/comment, select this option to also show submit buttons.</span>", 'wp-symposium'); ?></span></td> 
					</tr>

					<tr valign="top"> 
					<td scope="row"><label for="striptags"><?php echo __('Strip tags', 'wp-symposium'); ?></label></td>
					<td>
					<input type="checkbox" name="striptags" id="striptags" <?php if (get_option('symposium_striptags') == "on") { echo "CHECKED"; } ?>/>
					<span class="description"><?php 
					echo __("Completely remove HTML/script tags. If unchecked &lt; and &gt; will be replaced with &amp;lt; and &amp;gt;.", 'wp-symposium'); 
					echo "<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".__("NB. The Bronze member WYSIWYG editor, if in use, will display tags, whatever you set here, but not interpret them.", 'wp-symposium'); 
					?></span></td> 
					</tr>
										
					<tr valign="top"> 
					<td scope="row"><label for="allow_reports"><?php echo __('Allow reports', 'wp-symposium'); ?></label></td>
					<td>
					<input type="checkbox" name="allow_reports" id="allow_reports" <?php if (get_option('symposium_allow_reports') == "on") { echo "CHECKED"; } ?>/>
					<span class="description"><?php echo __("Shows a warning symbol to report content to the site administrator.", 'wp-symposium'); ?></span></td> 
					</tr>

					<tr valign="top"> 
					<td scope="row"><label for="debug_mode"><?php echo __('Debug mode', 'wp-symposium'); ?></label></td>
					<td>
					<input type="checkbox" name="debug_mode" id="debug_mode" <?php if (get_option('symposium_debug_mode') == "on") { echo "CHECKED"; } ?>/>
					<span class="description"><?php echo __("Only for use by WP Symposium developers.", 'wp-symposium'); ?></span></td> 
					</tr>
					<?php				
					// Hook to add items to the plugin settings page
					echo apply_filters( 'symposium_plugin_settings_hook', "" );
					?>					
					<tr valign="top"> 
					<td colspan="2"><hr /><?php echo __('The following can be disabled if clashes with other WordPress plugins are occuring', 'wp-symposium'); ?>:</td>
					</tr> 

					<tr valign="top"> 
					<td scope="row"><label for="jquery"><?php echo __('Load jQuery', 'wp-symposium'); ?></label></td>
					<td>
					<input type="checkbox" name="jquery" id="jquery" <?php if (get_option('symposium_jquery') == "on") { echo "CHECKED"; } ?>/>
					<span class="description"><?php echo __('Load jQuery on non-admin pages, disable if causing problems', 'wp-symposium'); ?></span></td> 
					</tr> 

					<tr valign="top"> 
					<td scope="row"><label for="jqueryui"><?php echo __('Load jQuery UI', 'wp-symposium'); ?></label></td>
					<td>
					<input type="checkbox" name="jqueryui" id="jqueryui" <?php if (get_option('symposium_jqueryui') == "on") { echo "CHECKED"; } ?>/>
					<span class="description"><?php echo __('Load jQuery UI on non-admin pages, disable if causing problems', 'wp-symposium'); ?></span></td> 
					</tr> 
				
					<tr valign="top"> 
					<td scope="row"><label for="jscharts"><?php echo __('Load JScharts/Jcrop', 'wp-symposium'); ?></label></td>
					<td>
					<input type="checkbox" name="jscharts" id="jscharts" <?php if (get_option('symposium_jscharts') == "on") { echo "CHECKED"; } ?>/>
					<span class="description"><?php echo __('Load JSCharts and Jcrop on non-admin pages, disable if causing problems', 'wp-symposium'); ?></span></td> 
					</tr>					
				
					<tr valign="top"> 
					<td scope="row"><label for="emoticons"><?php echo __('Smilies/Emoticons', 'wp-symposium'); ?></label></td>
					<td>
					<input type="checkbox" name="emoticons" id="emoticons" <?php if (get_option('symposium_emoticons') == "on") { echo "CHECKED"; } ?>/>
					<span class="description"><?php echo __('Automatically replace smilies/emoticons with graphical images', 'wp-symposium'); ?></span></td> 
					</tr> 		
															
					<tr valign="top"> 
					<td scope="row"><label for="elastic"><?php echo __('Elastic Textboxes', 'wp-symposium'); ?></label></td>
					<td>
					<input type="checkbox" name="elastic" id="elastic" <?php if (get_option('symposium_elastic') == "on") { echo "CHECKED"; } ?>/>
					<span class="description"><?php echo __('Include jQuery elastic function (automatically expand textboxes)', 'wp-symposium'); ?></span></td> 
					</tr> 		
															
					</table>
					 
					<p class="submit" style="margin-left:6px"> 
					<input type="submit" name="Submit" class="button-primary" value="<?php echo __('Save Changes', 'wp-symposium'); ?>" /> 
					</p> 
					
					<?php
				
				echo '</form></div></div>';
	echo '</div>';					  
}

function symposium_plugin_forum() {

  	echo '<div class="wrap">';
  	echo '<div id="icon-themes" class="icon32"><br /></div>';
  	echo '<h2>'.__('Forum Options', 'wp-symposium').'</h2><br />';

	global $wpdb;

		// See if the user has posted forum settings
		if( isset($_POST[ 'symposium_update' ]) && $_POST[ 'symposium_update' ] == 'symposium_plugin_forum' ) {

			update_option('symposium_send_summary', isset($_POST[ 'send_summary' ]) ? $_POST[ 'send_summary' ] : '');
			update_option('symposium_include_admin', isset($_POST[ 'include_admin' ]) ? $_POST[ 'include_admin' ] : '');
			update_option('symposium_oldest_first', isset($_POST[ 'oldest_first' ]) ? $_POST[ 'oldest_first' ] : '');
			update_option('symposium_use_votes', isset($_POST[ 'use_votes' ]) ? $_POST[ 'use_votes' ] : '');
			update_option('symposium_use_votes_remove', $_POST[ 'use_votes_remove' ] != '' ? $_POST[ 'use_votes_remove' ] : 0);
			update_option('symposium_use_votes_min', $_POST[ 'use_votes_min' ] != '' ? $_POST[ 'use_votes_min' ] : 10);
			update_option('symposium_preview1', $_POST[ 'preview1' ] != '' ? $_POST[ 'preview1' ] : 45);
			update_option('symposium_preview2', $_POST[ 'preview2' ] != '' ? $_POST[ 'preview2' ] : 90);
			update_option('symposium_chatroom_banned', $_POST[ 'chatroom_banned' ]);
			update_option('symposium_closed_word', $_POST[ 'closed_word' ]);
			update_option('symposium_bump_topics', isset($_POST[ 'bump_topics' ]) ? $_POST[ 'bump_topics' ] : '');
			update_option('symposium_forum_ajax', isset($_POST[ 'forum_ajax' ]) ? $_POST[ 'forum_ajax' ] : '');
			update_option('symposium_forum_login', isset($_POST[ 'forum_login' ]) ? $_POST[ 'forum_login' ] : '');
			update_option('symposium_moderation', isset($_POST[ 'moderation' ]) ? $_POST[ 'moderation' ] : '');
			$sharing_permalink = (isset($_POST[ 'sharing_permalink' ])) ? "pl;" : ""; 
			$sharing_facebook = (isset($_POST[ 'sharing_facebook' ])) ? "fb;" : ""; 
			$sharing_twitter = (isset($_POST[ 'sharing_twitter' ])) ? "tw;" : ""; 
			$sharing_myspace = (isset($_POST[ 'sharing_myspace' ])) ? "ms;" : ""; 
			$sharing_bebo = (isset($_POST[ 'sharing_bebo' ])) ? "be;" : ""; 
			$sharing_linkedin = (isset($_POST[ 'sharing_linkedin' ])) ? "li;" : ""; 
			$sharing_email = (isset($_POST[ 'sharing_email' ])) ? "em;" : ""; 
			$sharing = $sharing_permalink.$sharing_facebook.$sharing_twitter.$sharing_myspace.$sharing_bebo.$sharing_linkedin.$sharing_email;
			update_option('symposium_sharing', $sharing);
			$forum_ranks = (isset($_POST[ 'forum_ranks' ])) ? $_POST[ 'forum_ranks' ].';' : '';
			for ( $rank = 1; $rank <= 11; $rank ++) {
				$forum_ranks .= $_POST['rank'.$rank].";";
				$forum_ranks .= $_POST['score'.$rank].";";
			}
			update_option('symposium_forum_ranks', $forum_ranks);
			update_option('symposium_symposium_forumlatestposts_count', $_POST[ 'symposium_forumlatestposts_count' ] != '' ? $_POST[ 'symposium_forumlatestposts_count' ] : 10);
			update_option('symposium_forum_uploads', isset($_POST[ 'forum_uploads' ]) ? $_POST[ 'forum_uploads' ] : '');
			update_option('symposium_forum_thumbs', isset($_POST[ 'forum_thumbs' ]) ? $_POST[ 'forum_thumbs' ] : '');
			update_option('symposium_forum_thumbs_size', $_POST[ 'forum_thumbs_size' ]);
			update_option('symposium_forum_login_form', isset($_POST[ 'forum_login_form' ]) ? $_POST[ 'forum_login_form' ] : '');
			update_option('symposium_forum_info', isset($_POST[ 'forum_info' ]) ? $_POST[ 'forum_info' ] : '');
			update_option('symposium_forum_stars', isset($_POST[ 'forum_stars' ]) ? $_POST[ 'forum_stars' ] : '');
			update_option('symposium_forum_refresh', isset($_POST[ 'forum_refresh' ]) ? $_POST[ 'forum_refresh' ] : '');
			update_option('symposium_use_answers', isset($_POST[ 'use_answers' ]) ? $_POST[ 'use_answers' ] : '');
			update_option('symposium_wps_default_forum', $_POST[ 'wps_default_forum' ]);
			update_option('symposium_use_wysiwyg', isset($_POST[ 'use_wysiwyg' ]) ? $_POST[ 'use_wysiwyg' ] : '');
			update_option('symposium_use_wysiwyg_1', $_POST[ 'use_wysiwyg_1' ]);
			update_option('symposium_use_wysiwyg_2', $_POST[ 'use_wysiwyg_2' ]);
			update_option('symposium_use_wysiwyg_3', $_POST[ 'use_wysiwyg_3' ]);
			update_option('symposium_use_wysiwyg_4', $_POST[ 'use_wysiwyg_4' ]);
			update_option('symposium_use_wysiwyg_css', $_POST[ 'use_wysiwyg_css' ]);
			update_option('symposium_use_wysiwyg_skin', $_POST[ 'use_wysiwyg_skin' ]);
			update_option('symposium_use_wysiwyg_width', $_POST[ 'use_wysiwyg_width' ]);
			update_option('symposium_use_wysiwyg_height', $_POST[ 'use_wysiwyg_height' ]);
			update_option('symposium_forum_lock', $_POST[ 'forum_lock' ] != '' ? $_POST[ 'forum_lock' ] : 0);
			update_option('symposium_include_context', isset($_POST[ 'include_context' ]) ? $_POST[ 'include_context' ] : '');

			// Forum viewers
			if (isset($_POST['viewers'])) {
		   		$range = array_keys($_POST['viewers']);
		   		$level = '';
	   			foreach ($range as $key) {
					$level .= $_POST['viewers'][$key].',';
		   		}
			} else {
				$level = '';
			}
			update_option('symposium_viewer', serialize($level));
			
			// Forum editors (new topic)
			if (isset($_POST['editors'])) {
		   		$range = array_keys($_POST['editors']);
		   		$level = '';
	   			foreach ($range as $key) {
					$level .= $_POST['editors'][$key].',';
		   		}
			} else {
				$level = '';
			}
			update_option('symposium_forum_editor', serialize($level));

			// Forum replies
			if (isset($_POST['repliers'])) {
		   		$range = array_keys($_POST['repliers']);
		   		$level = '';
	   			foreach ($range as $key) {
					$level .= $_POST['repliers'][$key].',';
		   		}
			} else {
				$level = '';
			}
			update_option('symposium_forum_reply', serialize($level));					
			
			// Put an settings updated message on the screen
			echo "<div class='updated slideaway'><p>".__('Saved', 'wp-symposium').".</p></div>";

		}
		
		display_bronze_message();
		?>

		<div class="metabox-holder"><div id="toc" class="postbox"> 
			
			<form method="post" action=""> 
			<input type="hidden" name="symposium_update" value="symposium_plugin_forum">
				
			<table class="form-table"> 
		
			<tr valign="top"> 
			<td scope="row"><label for="wps_default_forum"><?php echo __('Default Categories', 'wp-symposium'); ?></label></td>
			<td><input name="wps_default_forum" type="text" id="wps_default_forum"  value="<?php echo get_option('symposium_wps_default_forum'); ?>" /> 
			<span class="description"><?php echo __('List of forum categories IDs, that new site members automatically subscribe to (comma separated)', 'wp-symposium'); ?></span></td> 
			</tr> 
			
			<?php if ( symposium_is_plus() ) { ?>
				<tr valign="top"> 
				<td scope="row"><label for="use_wysiwyg"><?php echo __('WYSIWYG editor', 'wp-symposium'); ?></label></td>
				<td>
				<input type="checkbox" name="use_wysiwyg" id="use_wysiwyg" <?php if (get_option('symposium_use_wysiwyg') == "on") { echo "CHECKED"; } ?>/>
				<span class="description">
				<?php echo __('Use the TinyMCE WYSIWYG editor/toolbar on the forums.', 'wp-symposium'); ?><br />
				<?php echo __('NB. Some themes cause layout problems with TinyMCE. Verified with TwentyEleven and tested with many others, but', 'wp-symposium'); ?><br />
				<?php echo __('if your editor toolbar layout is broken, check your theme stylesheets.', 'wp-symposium'); ?>
				</span></td> 
				</tr> 

				<tr valign="top" style='background-color: #ccc;'> 
				<td scope="row"><label for="include_context"><?php echo __('Context menu', 'wp-symposium'); ?></label></td>
				<td>
				<input type="checkbox" name="include_context" id="include_context" <?php if (get_option('symposium_include_context') == "on") { echo "CHECKED"; } ?>/>
				<span class="description"><?php echo __('Activate right-mouse click context menu.', 'wp-symposium'); ?></span></td> 
				</tr> 
				
				<?php if (get_option('symposium_use_wysiwyg') == 'on') { ?>					
					<tr valign="top" style='background-color: #ccc;'> 
					<td scope="row"><label for="use_wysiwyg_1"><?php echo __('Editor Toolbars', 'wp-symposium'); ?><br />
					<a href="http://www.tinymce.com/wiki.php/Buttons/controls" target="_blank"><?php echo __('See all buttons/controls', 'wp-symposium') ?></a><br />
					<a href="javascript:void(0);" id="use_wysiwyg_reset"><?php echo __('Reset (full)', 'wp-symposium'); ?></a><br />
					<a href="javascript:void(0);" id="use_wysiwyg_reset_min"><?php echo __('Reset (minimal)', 'wp-symposium'); ?></a>
					</label></td>
					<td>
						<span class="description"><?php echo __('Toolbar row 1', 'wp-symposium'); ?></span><br />
						<textarea name="use_wysiwyg_1" style="width:350px; height:80px" id="use_wysiwyg_1"><?php echo get_option('symposium_use_wysiwyg_1'); ?></textarea><br />
						<span class="description"><?php echo __('Toolbar row 2', 'wp-symposium'); ?></span><br />
						<textarea name="use_wysiwyg_2" style="width:350px; height:80px" id="use_wysiwyg_2"><?php echo get_option('symposium_use_wysiwyg_2'); ?></textarea><br />
						<span class="description"><?php echo __('Toolbar row 3', 'wp-symposium'); ?></span><br />
						<textarea name="use_wysiwyg_3" style="width:350px; height:80px" id="use_wysiwyg_3"><?php echo get_option('symposium_use_wysiwyg_3'); ?></textarea><br />
						<span class="description"><?php echo __('Toolbar row 4', 'wp-symposium'); ?></span><br />
						<textarea name="use_wysiwyg_4" style="width:350px; height:80px" id="use_wysiwyg_4"><?php echo get_option('symposium_use_wysiwyg_4'); ?></textarea><br />
					</td> 
					</tr> 
					<tr valign="top" style='background-color: #ccc;'> 
					<td scope="row"><label for="use_wysiwyg_css"><?php echo __('Editor CSS', 'wp-symposium'); ?></label></td>
					<td><span class="description"><?php echo __('Path for CSS file, eg:', 'wp-symposium').' '.str_replace(siteURL(), '', WP_PLUGIN_URL."/wp-symposium/tiny_mce/themes/advanced/skins/wps.css"); ?></span><br />
					<span class="description"><?php echo __('You may need to clear your browsing cache if changing the content of the file.', 'wp-symposium'); ?></span><br />
					<?php if (!get_option('symposium_use_wysiwyg_css')) update_option('symposium_use_wysiwyg_css', str_replace(siteURL(), '', WP_PLUGIN_URL."/wp-symposium/tiny_mce/themes/advanced/skins/wps.css")); ?>
					<input name="use_wysiwyg_css" style="width:350px" type="text" id="use_wysiwyg_css"  value="<?php echo get_option('symposium_use_wysiwyg_css'); ?>" />
					</td> 
					</tr> 
					<tr valign="top" style='background-color: #ccc;'> 
					<td scope="row"><label for="use_wysiwyg_skin"><?php echo __('Skin folder', 'wp-symposium'); ?></label></td>
					<td><span class="description"><?php echo sprintf(__('Folders are stored in %s/wp-symposium/tiny_mce/themes/advanced/skins; eg: cirkuit', 'wp-symposium'), str_replace(get_bloginfo('url'), '', WP_PLUGIN_URL)); ?></span><br />
					<?php if (!get_option('symposium_use_wysiwyg_skin')) update_option('symposium_use_wysiwyg_skin', 'cirkuit'); ?>
					<input name="use_wysiwyg_skin" type="text" id="use_wysiwyg_skin"  value="<?php echo get_option('symposium_use_wysiwyg_skin'); ?>" />
					</td> 
					</tr> 
					<tr valign="top" style='background-color: #ccc;'> 
					<td scope="row"><label for="use_wysiwyg_width"><?php echo __('Width', 'wp-symposium'); ?></label></td>
					<td><span class="description"><?php echo __('Width of editor', 'wp-symposium'); ?></span><br />
					<input name="use_wysiwyg_width" type="text" id="use_wysiwyg_width"  value="<?php echo get_option('symposium_use_wysiwyg_width'); ?>" />
					</td> 
					</tr> 
					<tr valign="top" style='background-color: #ccc;'> 
					<td scope="row"><label for="use_wysiwyg_height"><?php echo __('Height', 'wp-symposium'); ?></label></td>
					<td><span class="description"><?php echo __('Height of editor', 'wp-symposium'); ?></span><br />
					<input name="use_wysiwyg_height" type="text" id="use_wysiwyg_height"  value="<?php echo get_option('symposium_use_wysiwyg_height'); ?>" />
					</td> 
					</tr> 
				<?php } else {
					echo '<input type="hidden" name="use_wysiwyg_1" id="use_wysiwyg_1" value="'.get_option('symposium_use_wysiwyg_1').'" />';
					echo '<input type="hidden" name="use_wysiwyg_2" id="use_wysiwyg_2" value="'.get_option('symposium_use_wysiwyg_2').'" />';
					echo '<input type="hidden" name="use_wysiwyg_3" id="use_wysiwyg_3" value="'.get_option('symposium_use_wysiwyg_3').'" />';
					echo '<input type="hidden" name="use_wysiwyg_4" id="use_wysiwyg_4" value="'.get_option('symposium_use_wysiwyg_4').'" />';
					echo '<input type="hidden" name="use_wysiwyg_css" id="use_wysiwyg_css" value="'.get_option('symposium_use_wysiwyg_css').'" />';
					echo '<input type="hidden" name="use_wysiwyg_skin" id="use_wysiwyg_skin" value="'.get_option('symposium_use_wysiwyg_skin').'" />';
					echo '<input type="hidden" name="use_wysiwyg_width" id="use_wysiwyg_width" value="'.get_option('symposium_use_wysiwyg_width').'" />';
					echo '<input type="hidden" name="use_wysiwyg_height" id="use_wysiwyg_height" value="'.get_option('symposium_use_wysiwyg_height').'" />';
				} ?>					
			<?php } else {
				echo '<input type="hidden" name="use_wysiwyg" id="use_wysiwyg" value="'.get_option('symposium_use_wysiwyg_1').'" />';
			} ?>

			<tr valign="top"> 
			<td scope="row"><label for="forum_login"><?php echo __('Login Link', 'wp-symposium'); ?></label></td>
			<td>
			<input type="checkbox" name="forum_login" id="forum_login" <?php if (get_option('symposium_forum_login') == "on") { echo "CHECKED"; } ?>/>
			<span class="description"><?php echo __('Show login link on forum when not logged in?', 'wp-symposium'); ?></span></td> 
			</tr> 

			<tr valign="top"> 
			<td scope="row"><label for="forum_ajax"><?php echo __('Use AJAX', 'wp-symposium'); ?></label></td>
			<td>
			<input type="checkbox" name="forum_ajax" id="forum_ajax" <?php if (get_option('symposium_forum_ajax') == "on") { echo "CHECKED"; } ?>/>
			<span class="description"><?php echo __('Use AJAX, or hyperlinks and page re-loading?', 'wp-symposium'); ?></span></td> 
			</tr> 

			<tr valign="top"> 
			<td scope="row"><label for="forum_refresh"><?php echo __('Refresh forum after reply', 'wp-symposium'); ?></label></td>
			<td>
			<input type="checkbox" name="forum_refresh" id="forum_refresh" <?php if (get_option('symposium_forum_refresh') == "on") { echo "CHECKED"; } ?>/>
			<span class="description"><?php echo __('Reload the page after posting a reply on the forum.', 'wp-symposium'); ?></span></td> 
			</tr> 
	
			<tr valign="top"> 
			<td scope="row"><label for="moderation"><?php echo __('Moderation', 'wp-symposium'); ?></label></td>
			<td>
			<input type="checkbox" name="moderation" id="moderation" <?php if (get_option('symposium_moderation') == "on") { echo "CHECKED"; } ?>/>
			<span class="description"><?php echo __('New topics and posts require admin approval', 'wp-symposium'); ?></span></td> 
			</tr> 
	
			<tr valign="top"> 
			<td scope="row"><label for="send_summary"><?php echo __('Daily Digest', 'wp-symposium'); ?></label></td>
			<td>
			<input type="checkbox" name="send_summary" id="send_summary" <?php if (get_option('symposium_send_summary') == "on") { echo "CHECKED"; } ?>/>
			<span class="description"><?php echo __('Enable daily summaries to all members via email', 'wp-symposium'); ?></span></td> 
			</tr> 
	
			<tr valign="top"> 
			<td scope="row"><label for="include_admin"><?php echo __('Admin views', 'wp-symposium'); ?></label></td>
			<td>
			<input type="checkbox" name="include_admin" id="include_admin" <?php if (get_option('symposium_include_admin') == "on") { echo "CHECKED"; } ?>/>
			<span class="description"><?php echo __('Include administrator viewing a topic in the total view count', 'wp-symposium'); ?></span></td> 
			</tr> 
	
			<tr valign="top"> 
			<td scope="row"><label for="bump_topics"><?php echo __('Bump topics', 'wp-symposium'); ?></label></td>
			<td>
			<input type="checkbox" name="bump_topics" id="bump_topics" <?php if (get_option('symposium_bump_topics') == "on") { echo "CHECKED"; } ?>/>
			<span class="description"><?php echo __('Bumps topics to top of forum when new replies are posted', 'wp-symposium'); ?></span></td> 
			</tr> 
	
			<tr valign="top"> 
			<td scope="row"><label for="oldest_first"><?php echo __('Order of replies', 'wp-symposium'); ?></label></td>
			<td>
			<input type="checkbox" name="oldest_first" id="oldest_first" <?php if (get_option('symposium_oldest_first') == "on") { echo "CHECKED"; } ?>/>
			<span class="description"><?php echo __('Show oldest replies first (uncheck to reverse order)', 'wp-symposium'); ?></span></td> 
			</tr> 
	
			<tr valign="top"> 
			<td scope="row"><label for="forum_uploads"><?php echo __('Allow uploads', 'wp-symposium'); ?></label></td>
			<td>
			<input type="checkbox" name="forum_uploads" id="forum_uploads" <?php if (get_option('symposium_forum_uploads') == "on") { echo "CHECKED"; } ?>/>
			<span class="description"><?php echo __('Allow members to upload files with forum posts (requires Flash to be installed)', 'wp-symposium'); ?></span></td> 
			</tr> 

			<tr valign="top"> 
			<td scope="row"><label for="forum_thumbs"><?php echo __('Inline attachments', 'wp-symposium'); ?></label></td>
			<td>
			<input type="checkbox" name="forum_thumbs" id="forum_thumbs" <?php if (get_option('symposium_forum_thumbs') == "on") { echo "CHECKED"; } ?>/>
			<span class="description"><?php echo __('Show uploaded forum attachments as images/videos (not links). Documents are always links.', 'wp-symposium'); ?></span></td> 
			</tr> 
		
			<tr valign="top"> 
			<td scope="row"><label for="forum_thumbs_size"><?php echo __('Thumbnail size', 'wp-symposium'); ?></label></td>
			<td><input name="forum_thumbs_size" style="width:50px" type="text" id="forum_thumbs_size"  value="<?php echo get_option('symposium_forum_thumbs_size'); ?>" /> 
			<span class="description"><?php echo __('If using inline attachments, maximum width', 'wp-symposium'); ?></span></td> 
			</tr> 
	
			<tr valign="top"> 
			<td scope="row"><label for="forum_info"><?php echo __('Member Info', 'wp-symposium'); ?></label></td>
			<td>
			<input type="checkbox" name="forum_info" id="forum_info" <?php if (get_option('symposium_forum_info') == "on") { echo "CHECKED"; } ?>/>
			<span class="description"><?php echo __('Show member info underneath avatar on forum', 'wp-symposium'); ?></span></td> 
			</tr> 
	
			<tr valign="top"> 
			<td scope="row"><label for="forum_stars"><?php echo __('New post stars', 'wp-symposium'); ?></label></td>
			<td>
			<input type="checkbox" name="forum_stars" id="forum_stars" <?php if (get_option('symposium_forum_stars') == "on") { echo "CHECKED"; } ?>/>
			<span class="description"><?php echo __('Show stars for posts added since last login.', 'wp-symposium'); ?></span></td> 
			</tr> 
	
			<tr valign="top"> 
			<td scope="row"><label for="forum_login_form"><?php echo __('Show login form', 'wp-symposium'); ?></label></td>
			<td>
			<input type="checkbox" name="forum_login_form" id="forum_login_form" <?php if (get_option('symposium_forum_login_form') == "on") { echo "CHECKED"; } ?>/>
			<span class="description"><?php echo __('If a user has to log in, show the login form underneath the topic/replies.', 'wp-symposium'); ?></span></td> 
			</tr> 
	
			<tr valign="top"> 
			<td scope="row"><label for="forum_lock"><?php echo __('Post lock time', 'wp-symposium'); ?></label></td>
			<td><input name="forum_lock" style="width:50px" type="text" id="forum_lock"  value="<?php echo get_option('symposium_forum_lock'); ?>" /> 
			<span class="description"><?php echo __('How many minutes before a forum topic/reply can no longer be edited, 0 for no limit.', 'wp-symposium'); ?></span></td> 
			</tr> 
	
			<tr valign="top"> 
			<td scope="row"><label for="use_votes"><?php echo __('Use Votes', 'wp-symposium'); ?></label></td>
			<td>
			<input type="checkbox" name="use_votes" id="use_votes" <?php if (get_option('symposium_use_votes') == "on") { echo "CHECKED"; } ?>/>
			<span class="description"><?php echo __('Allow members to vote (plus or minus) on forum posts', 'wp-symposium'); ?></span></td> 
			</tr> 

			<tr valign="top"> 
			<td scope="row"><label for="use_votes_min"><?php echo __('Votes (minimum posts)', 'wp-symposium'); ?></label></td>
			<td><input name="use_votes_min" style="width:50px" type="text" id="use_votes_min"  value="<?php echo get_option('symposium_use_votes_min'); ?>" /> 
			<span class="description"><?php echo __('How many posts a member must have made in order to vote', 'wp-symposium'); ?></span></td> 
			</tr> 
	
	
			<tr valign="top"> 
			<td scope="row"><label for="use_votes_remove"><?php echo __('Votes (removal point)', 'wp-symposium'); ?></label></td>
			<td><input name="use_votes_remove" style="width:50px" type="text" id="use_votes_remove"  value="<?php echo get_option('symposium_use_votes_remove'); ?>" /> 
			<span class="description"><?php echo __('When a forum post gets this many votes, it is removed. Can be + or -. Leave as 0 to ignore.', 'wp-symposium'); ?></span></td> 
			</tr> 

			<tr valign="top"> 
			<td scope="row"><label for="use_answers"><?php echo __('Votes (answers)', 'wp-symposium'); ?></label></td>
			<td>
			<input type="checkbox" name="use_answers" id="use_answers" <?php if (get_option('symposium_use_answers') == "on") { echo "CHECKED"; } ?>/>
			<span class="description"><?php echo __('Allows topic owners and administrators to mark a reply as an answer (one per topic)', 'wp-symposium'); ?></span></td> 
			</tr> 
	
			<tr valign="top"> 
			<td scope="row"><label for="preview1"><?php echo __('Preview length', 'wp-symposium'); ?></label></td>
			<td><input name="preview1" style="width:50px" type="text" id="preview1"  value="<?php echo get_option('symposium_preview1'); ?>" /> 
			<span class="description"><?php echo __('Maximum number of characters to show in topic preview', 'wp-symposium'); ?></span></td> 
			</tr> 
	
			<tr valign="top"> 
			<td scope="row"><label for="preview2"></label></td>
			<td><input name="preview2" style="width:50px" type="text" id="preview2"  value="<?php echo get_option('symposium_preview2'); ?>" /> 
			<span class="description"><?php echo __('Maximum number of characters to show in reply preview', 'wp-symposium'); ?></span></td> 
			</tr> 

			<tr valign="top"> 
			<td scope="row"><label for="viewer"><?php echo __('View forum roles', 'wp-symposium'); ?></label></td> 
			<td>
			<?php		
				// Get list of roles
				global $wp_roles;
				$all_roles = $wp_roles->roles;
		
				$view_roles = get_option('symposium_viewer');

				echo '<input type="checkbox" name="viewers[]" value="'.__('everyone', 'wp-symposium').'"';
				if (strpos(strtolower($view_roles), strtolower(__('everyone', 'wp-symposium')).',') !== FALSE) {
					echo ' CHECKED';
				}
				echo '> '.__('Guests', 'wp-symposium').' ... <span class="description">'.__('means everyone can view the forum if checked', 'wp-symposium').'</span><br />';						
				foreach ($all_roles as $role) {
					echo '<input type="checkbox" name="viewers[]" value="'.$role['name'].'"';
					if (strpos(strtolower($view_roles), strtolower($role['name']).',') !== FALSE) {
						echo ' CHECKED';
					}
					echo '> '.$role['name'].'<br />';
				}			
			?>
			<span class="description"><?php echo __('The WordPress roles that can view the entire forum (fine tune with <a href="admin.php?page=symposium_categories">forum categories</a>)', 'wp-symposium'); ?></span></td> 
			</tr> 
	
			<tr valign="top"> 
			<td scope="row"><label for="forum_editor"><?php echo __('Forum new topic roles', 'wp-symposium'); ?></label></td> 
			<td>
			<?php		
				// Get list of roles
				global $wp_roles;
				$all_roles = $wp_roles->roles;
		
				$view_roles = get_option('symposium_forum_editor');

				foreach ($all_roles as $role) {
					echo '<input type="checkbox" name="editors[]" value="'.$role['name'].'"';
					if (strpos(strtolower($view_roles), strtolower($role['name']).',') !== FALSE) {
						echo ' CHECKED';
					}
					echo '> '.$role['name'].'<br />';
				}			
			?>
			<span class="description"><?php echo __('The WordPress roles that can post a new topic on the forum', 'wp-symposium'); ?></span></td> 
			</tr> 

			<tr valign="top"> 
			<td scope="row"><label for="forum_reply"><?php echo __('Forum reply roles', 'wp-symposium'); ?></label></td> 
			<td>
			<?php		
				// Get list of roles
				global $wp_roles;
				$all_roles = $wp_roles->roles;
		
				$reply_roles = get_option('symposium_forum_reply');

				foreach ($all_roles as $role) {
					echo '<input type="checkbox" name="repliers[]" value="'.$role['name'].'"';
					if (strpos(strtolower($reply_roles), strtolower($role['name']).',') !== FALSE) {
						echo ' CHECKED';
					}
					echo '> '.$role['name'].'<br />';
				}			
			?>
			<span class="description"><?php echo __('The WordPress roles that can reply to a topic on the forum', 'wp-symposium'); ?></span></td> 
			</tr> 
		
			<tr valign="top"> 
			<td scope="row"><label for="chatroom_banned"><?php echo __('Banned forum words', 'wp-symposium'); ?></label></td> 
			<td><input name="chatroom_banned" type="text" id="chatroom_banned"  value="<?php echo get_option('symposium_chatroom_banned'); ?>" /> 
			<span class="description"><?php echo __('Comma separated list of words not allowed in the forum', 'wp-symposium'); ?></td> 
			</tr> 

									
			<tr valign="top"> 
			<td scope="row"><label for="closed_word"><?php echo __('Closed word', 'wp-symposium'); ?></label></td>
			<td><input name="closed_word" type="text" id="closed_word"  value="<?php echo get_option('symposium_closed_word'); ?>" /> 
			<span class="description"><?php echo __('Word used to denote a topic that is closed (see also Styles)', 'wp-symposium'); ?></span></td> 
			</tr> 

			<?php
			$sharing = get_option('symposium_sharing');
			if ( strpos($sharing, "pl") === FALSE ) { $sharing_permalink = ''; } else { $sharing_permalink = 'on'; }
			if ( strpos($sharing, "fb") === FALSE ) { $sharing_facebook = ''; } else { $sharing_facebook = 'on'; }
			if ( strpos($sharing, "tw") === FALSE ) { $sharing_twitter = ''; } else { $sharing_twitter = 'on'; }
			if ( strpos($sharing, "ms") === FALSE ) { $sharing_myspace = ''; } else { $sharing_myspace = 'on'; }
			if ( strpos($sharing, "li") === FALSE ) { $sharing_linkedin = ''; } else { $sharing_linkedin = 'on'; }
			if ( strpos($sharing, "be") === FALSE ) { $sharing_bebo = ''; } else { $sharing_bebo = 'on'; }
			if ( strpos($sharing, "em") === FALSE ) { $sharing_email = ''; } else { $sharing_email = 'on'; }
			?>
			

			<tr valign="top"> 
			<td scope="row"><label for="sharing_permalink"><?php echo __('Sharing icons included', 'wp-symposium'); ?></label></td>
			<td>
			<input type="checkbox" name="sharing_permalink" id="sharing_permalink" <?php if ($sharing_permalink == "on") { echo "CHECKED"; } ?>/>
			<span class="description"><?php echo __('Permalink (to copy)', 'wp-symposium'); ?></span></td> 
			</tr> 
	
			<tr valign="top"> 
			<td scope="row"><label for="sharing_email">&nbsp;</label></td>
			<td>
			<input type="checkbox" name="sharing_email" id="sharing_email" <?php if ($sharing_email == "on") { echo "CHECKED"; } ?>/>
			<span class="description"><?php echo __('Email', 'wp-symposium'); ?></span></td> 
			</tr> 
	
			<tr valign="top"> 
			<td scope="row"><label for="sharing_facebook">&nbsp;</label></td>
			<td>
			<input type="checkbox" name="sharing_facebook" id="sharing_facebook" <?php if ($sharing_facebook == "on") { echo "CHECKED"; } ?>/>
			<span class="description"><?php echo __('Facebook', 'wp-symposium'); ?></span></td> 
			</tr> 
	
			<tr valign="top"> 
			<td scope="row"><label for="sharing_twitter">&nbsp;</label></td>
			<td>
			<input type="checkbox" name="sharing_twitter" id="sharing_twitter" <?php if ($sharing_twitter == "on") { echo "CHECKED"; } ?>/>
			<span class="description"><?php echo __('Twitter', 'wp-symposium'); ?></span></td> 
			</tr> 
	
			<tr valign="top"> 
			<td scope="row"><label for="sharing_myspace">&nbsp;</label></td>
			<td>
			<input type="checkbox" name="sharing_myspace" id="sharing_myspace" <?php if ($sharing_myspace == "on") { echo "CHECKED"; } ?>/>
			<span class="description"><?php echo __('MySpace', 'wp-symposium'); ?></span></td> 
			</tr> 
	
			<tr valign="top"> 
			<td scope="row"><label for="sharing_bebo">&nbsp;</label></td>
			<td>
			<input type="checkbox" name="sharing_bebo" id="sharing_bebo" <?php if ($sharing_bebo == "on") { echo "CHECKED"; } ?>/>
			<span class="description"><?php echo __('Bebo', 'wp-symposium'); ?></span></td> 
			</tr> 
	
			<tr valign="top"> 
			<td scope="row"><label for="sharing_linkedin">&nbsp;</label></td>
			<td>
			<input type="checkbox" name="sharing_linkedin" id="sharing_linkedin" <?php if ($sharing_linkedin == "on") { echo "CHECKED"; } ?>/>
			<span class="description"><?php echo __('LinkedIn', 'wp-symposium'); ?></span></td> 
			</tr> 

			<?php
			$ranks = explode(';', get_option('symposium_forum_ranks'));
			?>
			<tr valign="top"> 
			<td scope="row"><label for="forum_ranks"><?php echo __('Forum ranks', 'wp-symposium'); ?></label></td>
			<td>
			<input type="checkbox" name="forum_ranks" id="forum_ranks" <?php if ($ranks[0] == "on") { echo "CHECKED"; } ?>/>
			<span class="description"><?php echo __('Use ranks on the forum?', 'wp-symposium'); ?></span></td> 
			</tr>

			<?php
			for ( $rank = 1; $rank <= 11; $rank ++) {
				echo '<tr valign="top">';
					if ($rank == 1) { 

						echo '<td scope="row">';
							echo __('Title and Posts Required', 'wp-symposium');
						echo '</td>';

					} else {

						echo '<td scope="row">';
						
							if ($rank == 11) {
								echo '<em>'.__('(blank ranks are not used)', 'wp-symposium').'</em>';
							} else {
								echo "&nbsp;";
							}
						
						echo '</td>';

					}
					?>
					<td>
						<?php 
							$this_rank = $rank*2-1;
							$this_rank_label = $ranks[$this_rank];
							$this_rank_value = $ranks[$this_rank+1];
							
							if ($this_rank_label != '') {
								echo '<input name="rank'.$rank.'" type="text" id="rank'.$rank.'"  value="'.$this_rank_label.'" /> ';
								if ($rank > 1) {
									echo '<input name="score'.$rank.'" type="text" id="score'.$rank.'" style="width:50px" value="'.$this_rank_value.'" /> ';
								} else {
									echo '<input name="score'.$rank.'" type="text" id="score'.$rank.'" style="width:50px; display:none;"" /> ';
								} 
							} else {
								echo '<input name="rank'.$rank.'" type="text" id="rank'.$rank.'"  value="" /> ';
								if ($rank > 1) {
									echo '<input name="score'.$rank.'" type="text" id="score'.$rank.'" style="width:50px" value="" /> ';
								}
							}
						?>

						<span class="description">
						<?php 
						if ($rank == 1) {
							echo __('Most posts', 'wp-symposium'); 
						} else {
							echo __('Rank', 'wp-symposium').' '.($rank-1); 							
						}
						?></span>
					</td> 
				</tr>
			<?php
			}
			do_action('symposium_menu_forum_hook');	
			?>
			

			<tr valign="top"> 
			<td colspan=2>
				<p>
				<span class="description">
				<strong><?php echo __('Notes', 'wp-symposium'); ?></strong>
				<ul style='margin-left:6px'>
				<li>&middot;&nbsp;<?php echo __('Daily summaries (if there is anything to send) are sent when the first visitor comes to the site after midnight, local time.', 'wp-symposium'); ?></li>
				<li>&middot;&nbsp;<?php echo __('Be aware of any limits set by your hosting provider for sending out bulk emails, they may suspend your website.', 'wp-symposium'); ?></li>
				</ul>
				</p>
			<hr />
				<strong><?php echo __('Shortcode options', 'wp-symposium'); ?></td> 
			</tr> 

			<tr valign="top"> 
			<td scope="row"><label for="symposium_forumlatestposts_count"><?php echo __('[symposium-forumlatestposts]', 'wp-symposium'); ?></label></td>
			<td><input name="symposium_forumlatestposts_count" style="width:50px" type="text" id="symposium_forumlatestposts_count"  value="<?php echo get_option('symposium_symposium_forumlatestposts_count'); ?>" /> 
			<span class="description"><?php 
			echo __('Default number of topics to show. Can be overridden, eg: [symposium-forumlatestposts count=10]', 'wp-symposium').'<br />'; 
			echo '<span style="margin-left:55px">'.__('Forum category IDs can be specified in the shortcode, eg: [symposium-forumlatestposts cat=1]', 'wp-symposium').'</span>'; ?></span></td> 
			</tr> 

															
			</table> 	
		 
			<p class="submit" style='margin-left:6px;'> 
			<input type="submit" name="Submit" class="button-primary" value="<?php echo __('Save Changes', 'wp-symposium'); ?>" /> 
			</p> 
			</form> 

		</div></div>		  
	</div>
<?php
}



function symposium_plugin_categories() {

	global $wpdb;

  	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.', 'wp-symposium') );
  	}
  	
  	if (isset($_GET['action'])) {
		$action = $_GET['action'];
	} else {
		$action = '';
	}

	// Update values
	if (isset($_POST['title'])) {
		
   		$range = array_keys($_POST['cid']);
		foreach ($range as $key) {
			$cid = $_POST['cid'][$key];
			$cat_parent = $_POST['cat_parent'][$key];
			$title = $_POST['title'][$key];

			if (isset($_POST['level_'.$cid])) {
		   		$range2 = array_keys($_POST['level_'.$cid]);
		   		$level = '';
	   			foreach ($range2 as $key2) {
					$level .= $_POST['level_'.$cid][$key2].',';
		   		}
			} else {
				$level = '';
			}
			
			$listorder = $_POST['listorder'][$key];
			$allow_new = $_POST['allow_new'][$key];
			$cat_desc = $_POST['cat_desc'][$key];
			
			if ($cid == $_POST['default_category']) {
				$defaultcat = "on";
			} else {
				$defaultcat = "";
			}
			
			$wpdb->query( $wpdb->prepare( "
				UPDATE ".$wpdb->prefix.'symposium_cats'."
				SET title = %s, cat_parent = %d, listorder = %s, allow_new = %s, cat_desc = %s, defaultcat = %s, level = %s
				WHERE cid = %d", 
				$title, $cat_parent, $listorder, $allow_new, $cat_desc, $defaultcat, serialize($level), $cid  ) );
							
		}

	}
		
  	// Add new category?
  	if ( (isset($_POST['new_title']) && $_POST['new_title'] != '') && ($_POST['new_title'] != __('Add New Category', 'wp-symposium').'...') ) {
  		
  		$new_cat_desc = $_POST['new_cat_desc'];
  		if ($new_cat_desc == __('Optional Description', 'wp-symposium')."...") {
  			$new_cat_desc = '';  		
  		}
		$wpdb->query( $wpdb->prepare( "
			INSERT INTO ".$wpdb->prefix.'symposium_cats'."
			( 	title, 
				cat_parent,
				listorder,
				cat_desc,
				allow_new
			)
			VALUES ( %s, %d, %d, %s, %s )", 
			array(
				$_POST['new_title'], 
				$_POST['new_parent'],
				$_POST['new_listorder'],
				$new_cat_desc,
				$_POST['new_allow_new']
				) 
			) );
		  
	}

  	// Delete a category?
  	if ( ($action == 'delcid') && (current_user_can('level_10')) ) {
  		// Must leave at least one category, so check
		$cat_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix.'symposium_cats'));
		if ($cat_count > 1) {
			$wpdb->query( $wpdb->prepare("DELETE FROM ".$wpdb->prefix.'symposium_cats'." WHERE cid = ".$_GET['cid']) );
			if ($_GET['all'] == 1) {
				$wpdb->query( $wpdb->prepare("DELETE FROM ".$wpdb->prefix.'symposium_topics'." WHERE topic_category = ".$_GET['cid']) );
			} else {
				$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_topics'." SET topic_category = 0 WHERE topic_category = ".$_GET['cid']) );
			}
		} else {
			echo "<div class='error'><p>".__('You must have at least one category', 'wp-symposium').".</p></div>";
		}
  	}
 
		// See if the user has posted updated category information
		if( isset($_POST[ 'categories_update' ]) && $_POST[ 'categories_update' ] == 'Y' ) {
			
	   		$range = array_keys($_POST['tid']);
			foreach ($range as $key) {
		
				$tid = $_POST['tid'][$key];
				$topic_category = $_POST['topic_category'][$key];
				$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_topics'." SET topic_category = ".$topic_category." WHERE tid = ".$tid) );					
			}
	
			// Put an settings updated message on the screen
			echo "<div class='updated slideaway'><p>".__('Categories saved', 'wp-symposium')."</p></div>";
	
		}
 	

  	echo '<div class="wrap">';
  	echo '<div id="icon-themes" class="icon32"><br /></div>';
  	echo '<h2>'.__('Forum Categories', 'wp-symposium').'</h2><br />';
	 
	?> 
 
	<form method="post" action=""> 

	<table class="widefat">
	<thead>
	<tr>
	<th style="width:40px">ID</th>
	<th style="width:60px"><?php echo __('Parent ID', 'wp-symposium'); ?></th>
	<th><?php echo __('Category Title and Description', 'wp-symposium'); ?></th>
	<th><?php echo __('Permitted Roles', 'wp-symposium'); ?></th>
	<th style="text-align:center"><?php echo __('Topics', 'wp-symposium'); ?></th>
	<th><?php echo __('Order', 'wp-symposium'); ?></th>
	<th><?php echo __('Allow new topics', 'wp-symposium'); ?></th>
	<th>&nbsp;</th>
	</tr> 
	</thead>
	<?php	
	$included = show_forum_children(0, 0, '');

	// Get list of roles
	global $wp_roles;
	$all_roles = $wp_roles->roles;
	
	// Check for categories with incorrect Parent IDs
	$categories = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."symposium_cats ORDER BY cid");
	$shown_header = false;
	if ($categories) {
		foreach ($categories as $category) {

			if (!inHaystack($included, $category->cid)) {
				
				if (!$shown_header) {
					$shown_header = true;
					?>
					<thead>
					<tr>
					<th style="width:20px"></th>
					<th style="width:60px">&nbsp;</th>
					<th><strong><?php echo __('The following will not be displayed due to Parent ID (update or delete)', 'wp-symposium'); ?>...</strong></th>
					<th>&nbsp;</th>
					<th>&nbsp;</th>
					<th>&nbsp;</th>
					<th>&nbsp;</th>
					<th>&nbsp;</th>
					</tr> 
					</thead>
					<?php
				}
				echo '<tr valign="top">';
				echo '<input name="cid[]" type="hidden" value="'.$category->cid.'" />';
				echo '<td>'.$category->cid.'</td>';
				echo '<td><input name="cat_parent[]" type="text" value="'.stripslashes($category->cat_parent).'" style="width:50px" /></td>';
				echo '<td>';
				echo '<input name="title[]" type="text" value="'.stripslashes($category->title).'" class="regular-text" style="width:150px" /><br />';
				echo '<input name="cat_desc[]" type="text" value="'.stripslashes($category->cat_desc).'" class="regular-text" style="width:150px" />';
				echo '</td>';
				echo '<td>';
				$cat_roles = unserialize($category->level);
				echo '<input type="checkbox" class="wps_forum_cat_'.$category->cid.'" name="level_'.$category->cid.'[]" value="everyone"';
				if (strpos(strtolower($cat_roles), 'everyone,') !== FALSE) {
					echo ' CHECKED';
				}
				echo '> Everyone<br />';
				foreach ($all_roles as $role) {
					echo '<input type="checkbox" class="wps_forum_cat_'.$category->cid.'" name="level_'.$category->cid.'[]" value="'.$role['name'].'"';
					if (strpos(strtolower($cat_roles), strtolower($role['name']).',') !== FALSE) {
						echo ' CHECKED';
					}
					echo '> '.$role['name'].'<br />';
				}				
				echo '<a href="javascript:void(0);" title="'.$category->cid.'" class="symposium_cats_check">'.__('Check/uncheck all', 'wp-symposium').'</a><br />';
				echo '</td>';
				echo '<td style="text-align:center">';
				echo $wpdb->get_var("SELECT count(*) FROM ".$wpdb->prefix."symposium_topics WHERE topic_category = ".$category->cid);
				echo '</td>';
				echo '<td><input name="listorder[]" type="text" value="'.$category->listorder.'" style="width:50px" /></td>';
				echo '<td>';
				echo '<select name="allow_new[]">';
				echo '<option value="on"';
					if ($category->allow_new == "on") { echo " SELECTED"; }
					echo '>'.__('Yes', 'wp-symposium').'</option>';
				echo '<option value=""';
					if ($category->allow_new != "on") { echo " SELECTED"; }
					echo '>'.__('No', 'wp-symposium').'</option>';
				echo '</select>';
				echo '</td>';
				echo '<td>';
				echo '<a class="delete" href="?page=symposium_categories&action=delcid&all=0&cid='.$category->cid.'">'.__('Delete category', 'wp-symposium').'</a><br />';
				echo '<a class="delete" href="?page=symposium_categories&action=delcid&all=1&cid='.$category->cid.'">'.__('Delete category and posts', 'wp-symposium').'</a>';
				echo '</td>';
				echo '</tr>';
				
			}
		}
	}
	echo '<tr><td colspan="8">';
	echo sprintf(__('Note: "View forum roles", "Forum new topic roles" and "Forum reply roles" on <a href="%s">forum settings</a> effect the overall forum, the above permitted roles are for view and edit per forum category.', 'wp-symposium'), "admin.php?page=symposium_forum");
	echo '</td></tr>';
	
	?>
	
	<thead>
	<tr>
	<th style="width:20px"></th>
	<th style="width:60px"><?php echo __('Parent ID', 'wp-symposium'); ?></th>
	<th><?php echo __('Add New Category', 'wp-symposium'); ?></th>
	<th>&nbsp;</th>
	<th>&nbsp;</th>
	<th><?php echo __('Order', 'wp-symposium'); ?></th>
	<th><?php echo __('Allow new topics', 'wp-symposium'); ?></th>
	<th>&nbsp;</th>
	</tr> 
	</thead>

	<tr valign="top">
	<td>&nbsp;</td>
	<td><input name="new_parent" type="text" value="0" style="width:50px" /></td>
	<td>
		<input name="new_title" type="text" onclick="javascript:this.value = ''" value="<?php echo __('Add New Category', 'wp-symposium'); ?>..." class="regular-text" style="width:150px" /><br />
		<input name="new_cat_desc" type="text" onclick="javascript:this.value = ''" value="<?php echo __('Optional Description', 'wp-symposium'); ?>..." class="regular-text" style="width:150px" />
	</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>
		<input name="new_listorder" type="text" value="0" style="width:50px" />
	</td>
	<td>
	<input type="checkbox" name="new_allow_new" CHECKED />
	</td>
	<td colspan=2>&nbsp;</td>
	</tr>
	</table> 

	<br /><?php echo __('Default Category', 'wp-symposium'); ?>:
	<?php
	$categories = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix.'symposium_cats ORDER BY listorder');

	if ($categories) {
		echo "<select name='default_category'>";
		foreach ($categories as $category) {
			echo "<option value=".$category->cid;
			if ($category->defaultcat == "on") { echo " SELECTED"; }
			echo ">".$category->title."</option>";
		}
		echo "</select>";
	}	
	?>
	 
	<p class="submit"> 
	<input type="submit" name="Submit" class="button-primary" value="<?php echo __('Save Changes', 'wp-symposium'); ?>" /> 
	</p> 
	
	<p>
	<?php
	echo __('Note:', 'wp-symposium');
	echo '<li>'.__('choose "Delete category and posts" to delete a category and all topics in that category.', 'wp-symposium').'</li>';
	echo '<li>'.__('if category descriptions are not showing, check that [category_desc] is in your <a href="admin.php?page=symposium_templates">Forum Categories (list)</a> template.', 'wp-symposium').'</li>';
	?>
	<p>
	</form> 
	
	<?php
  
  	echo '</div>';

} 	
function inHaystack($haystack, $needle) {
	$haystack = explode(',', $haystack);
	return in_array($needle, $haystack);
}

function show_forum_children($id, $indent, $list) {
	
	global $wpdb;

	// Get list of roles
	global $wp_roles;
	$all_roles = $wp_roles->roles;
	
	$categories = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."symposium_cats WHERE cat_parent = ".$id." ORDER BY listorder");

	if ($categories) {
		foreach ($categories as $category) {
			
			$list = $list.$category->cid.",";
			
			switch($indent) {
			case 0:
				$style="background-color:#aaf";
				break;
			case 1:
				$style="background-color:#bfb";
				break;
			case 2:
				$style="background-color:#fcc";
				break;
			case 3:
				$style="background-color:#ddd";
				break;
			case 4:
				$style="background-color:#eee";
				break;
			case 5:
				$style="background-color:#fff";
				break;
			default:
				$style="background-color:#fff";
				break;
			}

			echo '<tr valign="top">';
			echo '<input name="cid[]" type="hidden" value="'.$category->cid.'" />';
			echo '<td style="'.$style.'">'.str_repeat("...", $indent).'&nbsp;'.$category->cid.'</td>';
			echo '<td><input name="cat_parent[]" type="text" value="'.stripslashes($category->cat_parent).'" style="width:50px" /></td>';
			echo '<td>';
			echo str_repeat("&nbsp;&nbsp;&nbsp;", $indent).'<input name="title[]" type="text" value="'.stripslashes($category->title).'" class="regular-text" style="width:150px" /><br />';
			echo str_repeat("&nbsp;&nbsp;&nbsp;", $indent).'<input name="cat_desc[]" type="text" value="'.stripslashes($category->cat_desc).'" class="regular-text" style="width:150px" />';
			echo '</td>';
			echo '<td>';
			$cat_roles = unserialize($category->level);
			echo '<input type="checkbox" class="wps_forum_cat_'.$category->cid.'" name="level_'.$category->cid.'[]" value="everyone"';
			if (strpos(strtolower($cat_roles), 'everyone,') !== FALSE) {
				echo ' CHECKED';
			}
			echo '> Everyone<br />';
			foreach ($all_roles as $role) {
				echo '<input type="checkbox" class="wps_forum_cat_'.$category->cid.'" name="level_'.$category->cid.'[]" value="'.$role['name'].'"';
				if (strpos(strtolower($cat_roles), strtolower($role['name']).',') !== FALSE) {
					echo ' CHECKED';
				}
				echo '> '.$role['name'].'<br />';
			}
			
			echo '<a href="javascript:void(0);" title="'.$category->cid.'" class="symposium_cats_check">'.__('Check/uncheck all', 'wp-symposium').'</a><br />';
			echo '</td>';
			echo '<td style="text-align:center">';
			echo $wpdb->get_var("SELECT count(*) FROM ".$wpdb->prefix."symposium_topics WHERE topic_parent = 0 AND topic_category = ".$category->cid);
			echo '</td>';
			echo '<td><input name="listorder[]" type="text" value="'.$category->listorder.'" style="width:50px" /></td>';
			echo '<td>';
			echo '<select name="allow_new[]">';
			echo '<option value="on"';
				if ($category->allow_new == "on") { echo " SELECTED"; }
				echo '>'.__('Yes', 'wp-symposium').'</option>';
			echo '<option value=""';
				if ($category->allow_new != "on") { echo " SELECTED"; }
				echo '>'.__('No', 'wp-symposium').'</option>';
			echo '</select>';
			echo '</td>';
			echo '</td>';
			echo '<td>';
			echo '<a class="delete" href="?page=symposium_categories&action=delcid&all=0&cid='.$category->cid.'">'.__('Delete category', 'wp-symposium').'</a><br />';
			echo '<a class="delete" href="?page=symposium_categories&action=delcid&all=1&cid='.$category->cid.'">'.__('Delete category and posts', 'wp-symposium').'</a>';
			echo '</td>';
			echo '</tr>';

			$list = show_forum_children($category->cid, $indent+1, $list);
	
		}
	}
	
	return $list;
}

function symposium_plugin_styles() {
	
	global $wpdb;

	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.', 'wp-symposium') );
	}

  	echo '<div class="wrap">';
  		echo '<div id="icon-themes" class="icon32"><br /></div>';
	  	echo '<h2>'.__('Styles', 'wp-symposium').'</h2>';

		// See if the user has saved CSS
		if( isset($_POST[ 'symposium_update' ]) && $_POST[ 'symposium_update' ] == 'CSS' ) {
			$css = str_replace(chr(13), "[]", $_POST['css']);
			update_option('symposium_css', $css);
  		}

		// See if the user is deleting a style
		if ( isset($_GET[ 'delstyle' ]) ) {
			$sql = "DELETE FROM ".$wpdb->prefix."symposium_styles WHERE sid = %d";
			if ( $wpdb->query( $wpdb->prepare( $sql, $_GET[ 'delstyle' ])) ) {
				echo "<div class='updated slideaway'><p>".__('Template Deleted', 'wp-symposium')."</p></div>";
			}
		}	
		// See if the user has selected a template
		if( isset($_POST[ 'sid' ]) ) {
			$style = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix.'symposium_styles'." WHERE sid = ".$_POST['sid']);
			if ($style) {
				update_option('symposium_use_styles', 'on');
				update_option('symposium_categories_background', $style->categories_background);
				update_option('symposium_categories_color', $style->categories_color);
				update_option('symposium_border_radius', $style->border_radius);
				update_option('symposium_main_background', $style->main_background);
				update_option('symposium_bigbutton_background', $style->bigbutton_background);
				update_option('symposium_bigbutton_background_hover', $style->bigbutton_background_hover);
				update_option('symposium_bigbutton_color', $style->bigbutton_color);
				update_option('symposium_bigbutton_color_hover', $style->bigbutton_color_hover);
				update_option('symposium_bg_color_1', $style->bg_color_1);
				update_option('symposium_bg_color_2', $style->bg_color_2);
				update_option('symposium_bg_color_3', $style->bg_color_3);
				update_option('symposium_row_border_style', $style->row_border_style);
				update_option('symposium_row_border_size', $stylerow_border_sizeyyy);
				update_option('symposium_replies_border_size', $style->replies_border_size);
				update_option('symposium_table_rollover', $style->table_rollover);
				update_option('symposium_table_border', $style->table_border);
				update_option('symposium_text_color', $style->text_color);
				update_option('symposium_text_color_2', $style->text_color_2);
				update_option('symposium_link', $style->link);
				update_option('symposium_underline', $style->underline);
				update_option('symposium_link_hover', $style->link_hover);
				update_option('symposium_label', $style->label);
				update_option('symposium_fontfamily', $style->fontfamily);
				update_option('symposium_fontsize', $style->fontsize);
				update_option('symposium_headingsfamily', $style->headingsfamily);
				update_option('symposium_headingssize', $style->headingssize);

				$style_save_as = $style->title;
				$style_id = $style->sid;

				// Put an settings updated message on the screen
				echo "<div class='updated slideaway'><p>".__('Template Applied', 'wp-symposium')."</p></div>";
			} else {
				echo "<div class='error'><p>".__('Template Not Found', 'wp-symposium')."</p></div>";
			}
		}

		// See if the user has posted us some information
		if( isset($_POST[ 'symposium_update' ]) && $_POST[ 'symposium_update' ] == 'Y' ) {

			update_option('symposium_use_styles', isset($_POST['use_styles']) ? $_POST['use_styles'] : '');
			update_option('symposium_categories_background', $_POST['categories_background']);
			update_option('symposium_categories_color', $_POST['categories_color']);
			update_option('symposium_border_radius', $_POST['border_radius']);
			update_option('symposium_bigbutton_background', $_POST['bigbutton_background']);
			update_option('symposium_bigbutton_background_hover', $_POST['bigbutton_background_hover']);
			update_option('symposium_bigbutton_color', $_POST['bigbutton_color']);
			update_option('symposium_bigbutton_color_hover', $_POST['bigbutton_color_hover']);
			update_option('symposium_bg_color_1', $_POST['bg_color_1']);
			update_option('symposium_bg_color_2', $_POST['bg_color_2']);
			update_option('symposium_bg_color_3', $_POST['bg_color_3']);
			update_option('symposium_row_border_style', $_POST['row_border_style']);
			update_option('symposium_row_border_size', $_POST['row_border_size']);
			update_option('symposium_table_rollover', $_POST['table_rollover']);
			update_option('symposium_table_border', $_POST['table_border']);
			update_option('symposium_replies_border_size', $_POST['replies_border_size']);
			update_option('symposium_text_color', $_POST['text_color']);
			update_option('symposium_text_color_2', $_POST['text_color_2']);
			update_option('symposium_link', $_POST['link']);
			update_option('symposium_underline', $_POST['underline']);
			update_option('symposium_link_hover', $_POST['link_hover']);
			update_option('symposium_label', $_POST['label']);
			update_option('symposium_closed_opacity', $_POST['closed_opacity']);
			update_option('symposium_fontfamily', $_POST['fontfamily']);
			update_option('symposium_fontsize', str_replace("px", "", strtolower($_POST[ 'fontsize' ])));
			update_option('symposium_headingsfamily', $_POST['headingsfamily']);
			update_option('symposium_headingssize', str_replace("px", "", strtolower($_POST[ 'headingssize' ])));
			update_option('symposium_main_background', $_POST['main_background']);
			
			if( $_POST[ 'style_save_as' ] != '' ) {

				// Delete previous version if it exists
				$wpdb->query( $wpdb->prepare( "DELETE FROM ".$wpdb->prefix."symposium_styles WHERE title = %s", $_POST['style_save_as'] ) );

				// Save new template
			   	$rows_affected = $wpdb->insert( $wpdb->prefix."symposium_styles", array( 
				'title' => $_POST['style_save_as'], 
				'border_radius' => $_POST['border_radius'],
				'bigbutton_background' => $_POST['bigbutton_background'], 
				'bigbutton_background_hover' => $_POST['bigbutton_background_hover'],
				'bigbutton_color' => $_POST['bigbutton_color'], 
				'bigbutton_color_hover' => $_POST['bigbutton_color_hover'], 
				'bg_color_1' => $_POST['bg_color_1'], 
				'bg_color_2' => $_POST['bg_color_2'],
				'bg_color_3' => $_POST['bg_color_3'], 
				'table_rollover' => $_POST['table_rollover'], 
				'table_border' => $_POST['table_border'], 
				'row_border_style' => $_POST['row_border_style'], 
				'row_border_size' => $_POST['row_border_size'], 
				'replies_border_size' => $_POST['replies_border_size'], 
				'categories_background' => $_POST['categories_background'], 
				'categories_color' => $_POST['categories_color'], 
				'text_color' => $_POST['text_color'], 
				'text_color_2' => $_POST['text_color_2'], 
				'link' => $_POST['link'], 
				'underline' => $_POST['underline'], 
				'link_hover' => $_POST['link_hover'], 
				'label' => $_POST['label'],
				'main_background' => $_POST['main_background'],
				'closed_opacity' => $_POST['closed_opacity'],
				'fontfamily' => $_POST['fontfamily'],
				'fontsize' => str_replace("px", "", strtolower($_POST[ 'fontsize' ])),
				'headingsfamily' => $_POST['headingsfamily'],
				'headingssize' => str_replace("px", "", strtolower($_POST[ 'headingssize' ]))
				) );	
			
				// Put an settings updated message on the screen
				echo "<div class='updated slideaway'><p>".__('Template Saved', 'wp-symposium')."</p></div>";
				
				$style_save_as = $_POST[ 'style_save_as' ];	   
			} else {
				$style_save_as = '';	   
			}

		}

		
		// Start tabs
		?> 
	
		<style>
			.symposium-wrapper #mail_tabs {
				width: 100%;
				border-radius:0px;
				-moz-border-radius:0px;
				margin-left: 10px;
				overflow: auto;
				position: relative;
				top: 1px;
			}
		
			.symposium-wrapper .mail_tab {
				border: 1px solid #666;
				padding: 8px;
				border-radius:0px;
				-moz-border-radius:0px;
			 	border-top-left-radius:5px;
				-moz-border-radius-topleft:5px;
			 	border-top-right-radius:5px;
				-moz-border-radius-topright:5px;
				width: 10%;
				text-align: center;
				float: left;
				margin-right: 10px;
			}
		
			.symposium-wrapper #mail_tabs .nav-tab-active {
				z-index: 3;
				border-bottom: 1px solid #fff;
				background-color: #fff;
			}
		
			.symposium-wrapper #mail_tabs .nav-tab-inactive {
				z-index: 1;
				border-bottom: 1px solid #666;
				background-color: #efefef;
			}
		
			.symposium-wrapper #mail_tabs .nav-tab-active-link {
				text-decoration: none;
				color: #000;
				font-size: 18px;
			}
		
			.symposium-wrapper #mail_tabs .nav-tab-inactive-link {
				text-decoration: none;
				color: #999;
				font-size: 18px;
			}
		
			.symposium-wrapper #mail-main {
				z-index: 2;
				width: 98%;
				border-radius: 5px;
				-moz-border-radius:5px;
				border: 1px solid #666;
				background-color: #fff;
				padding: 10px;
				overflow: auto;
				margin-bottom: 15px;
			}
		
		</style>	

		<?php

		// View
		$styles_active = 'active';
		$css_active = 'inactive';
		$view = "styles";
		if (isset($_GET['view']) && $_GET['view'] == 'css') {
			$styles_active = 'inactive';
			$css_active = 'active';
			$view = "css";
		}
	
		echo '<div class="symposium-wrapper" style="margin-top:15px">';
	
			echo '<div id="mail_tabs">';
			echo '<div class="mail_tab nav-tab-'.$styles_active.'"><a href="admin.php?page=symposium_styles&view=styles" class="nav-tab-'.$styles_active.'-link">'.__('Styles', 'wp-symposium').'</a></div>';
			echo '<div class="mail_tab nav-tab-'.$css_active.'"><a href="admin.php?page=symposium_styles&view=css" class="nav-tab-'.$css_active.'-link">'.__('CSS', 'wp-symposium').'</a></div>';
			echo '</div>';
		
			echo '<div id="mail-main">';
	
				// CSS
				if ($view == "css") {

					$css = get_option('symposium_css');
					$css = str_replace("[]", chr(13), stripslashes($css));

					echo '<form method="post" action=""> ';
					echo '<input type="hidden" name="symposium_update" value="CSS">';

					echo '<table class="widefat">';
					echo '<thead>';
					echo '<tr>';
					echo '<th style="font-size:1.2em">'.__('CSS', 'wp-symposium').'<input type="submit" class="button-primary" style="float:right; padding:2px 6px 2px 6px;" value="'.__('Save', 'wp-symposium').'"></th>';
					echo '</tr>';
					echo '</thead>';
					echo '<tbody>';
					echo '<tr>';
					echo '<td>';
					echo '<table style="float:right;width:39%">';
					echo '<tr>';
					echo '<td>'.__('Notes', 'wp-symposium').'</td>';
					echo '</tr>';
					echo '<tbody>';
					echo '<tr><td>';
					echo __('To speed things up, why not open a new window and refresh it each time you save a change here?', 'wp-symposium');
					echo '</td></tr>';
					echo '<tr><td>';
					echo __('CSS will over-ride the WP Symposium Styles (other tab), but your theme may take priority.', 'wp-symposium');
					echo '</td></tr>';
					echo '<tr><td>';
					echo __('If a style doesn\'t apply, try putting !important after it. eg: color:red !important;', 'wp-symposium');
					echo '</td></tr>';
					echo '<tr><td>';
					echo __('Refer to www.wpswiki.com for more help and examples.', 'wp-symposium');
					echo '</td></tr>';
					echo '</tbody>';
					echo '</table>';
					echo '<textarea id="css" name="css" style="width:60%;height: 600px;">';
					echo $css;
					echo '</textarea>';
					echo '</td>';
					echo '</tr>';
					echo '</tbody>';
					echo '</table>';
					
					echo '</form>';
					
				}
			
				// STYLES
				if ($view == "styles") {
			
						?> 

					<form method="post" action=""> 
					<input type="hidden" name="symposium_update" value="Y">

					<table class="form-table"> 

					<tr valign="top"> 
					<td scope="row"><label for="use_styles"><?php echo __('Use Styles?', 'wp-symposium'); ?></label></td>
					<td>
					<input type="checkbox" name="use_styles" id="use_styles" <?php if (get_option('symposium_use_styles') == "on") { echo "CHECKED"; } ?>/>
					<span class="description"><?php echo __('Enable to use styles on this page, disable to rely on stylesheet', 'wp-symposium'); ?></span></td> 
					</tr> 
	
					<tr valign="top"> 
					<td scope="row"><label for="fontfamily"><?php echo __('Body Text', 'wp-symposium'); ?></label></td> 
					<td><input name="fontfamily" type="text" id="fontfamily" value="<?php echo stripslashes(get_option('symposium_fontfamily')); ?>"/> 
					<span class="description"><?php echo __('Font family for body text', 'wp-symposium'); ?></span></td> 
					</tr> 

					<tr valign="top"> 
					<td scope="row"><label for="fontsize"></label></td> 
					<td><input name="fontsize" type="text" id="fontsize" value="<?php echo get_option('symposium_fontsize'); ?>"/> 
					<span class="description"><?php echo __('Font size in pixels for body text', 'wp-symposium'); ?></span></td> 
					</tr> 

					<tr valign="top"> 
					<td scope="row"><label for="headingsfamily"><?php echo __('Headings', 'wp-symposium'); ?></label></td> 
					<td><input name="headingsfamily" type="text" id="headingsfamily" value="<?php echo get_option('symposium_headingsfamily'); ?>"/> 
					<span class="description"><?php echo __('Font family for headings and large text', 'wp-symposium'); ?></span></td> 
					</tr> 

					<tr valign="top"> 
					<td scope="row"><label for="headingssize"></label></td> 
					<td><input name="headingssize" type="text" id="headingssize" value="<?php echo get_option('symposium_headingssize'); ?>"/> 
					<span class="description"><?php echo __('Font size in pixels for headings and large text', 'wp-symposium'); ?></span></td> 
					</tr> 

					<tr valign="top"> 
					<td scope="row"><label for="main_background"><?php echo __('Main background', 'wp-symposium'); ?></label></td> 
					<td><input name="main_background" type="text" id="main_background" class="wps_pickColor" value="<?php echo get_option('symposium_main_background'); ?>"  /> 
					<div style="position: absolute; margin-left:130px; margin-top:-110px;" class="colorpicker"></div>

					<span class="description"><?php echo __('Main background colour (for example, new/edit forum topic/post)', 'wp-symposium'); ?></span></td> 
					</tr> 

					<tr valign="top"> 
					<td scope="row"><label for="label"><?php echo __('Labels', 'wp-symposium'); ?></label></td> 
					<td><input name="label" type="text" id="label" class="wps_pickColor" value="<?php echo get_option('symposium_label'); ?>"  /> 
					<div style="position: absolute; margin-left:130px; margin-top:-110px;" class="colorpicker"></div>
					<span class="description"><?php echo __('Colour of text labels outside forum areas', 'wp-symposium'); ?></span></td> 
					</tr> 
	
					<tr valign="top"> 
					<td scope="row"><label for="text_color"><?php echo __('Text Colour', 'wp-symposium'); ?></label></td> 
					<td><input name="text_color" type="text" id="text_color" class="wps_pickColor" value="<?php echo get_option('symposium_text_color'); ?>"  /> 
					<div style="position: absolute; margin-left:130px; margin-top:-110px;" class="colorpicker"></div>
					<span class="description"><?php echo __('Primary Text Colour', 'wp-symposium'); ?></span></td> 
					</tr> 

					<tr valign="top"> 
					<td scope="row"><label for="text_color_2"></label></td> 
					<td><input name="text_color_2" type="text" id="text_color_2" class="wps_pickColor" value="<?php echo get_option('symposium_text_color_2'); ?>"  /> 
					<div style="position: absolute; margin-left:130px; margin-top:-110px;" class="colorpicker"></div>
					<span class="description"><?php echo __('Secondary Text Colour', 'wp-symposium'); ?></span></td> 
					</tr> 

					<tr valign="top"> 
					<td scope="row"><label for="link"><?php echo __('Links', 'wp-symposium'); ?></label></td> 
					<td><input name="link" type="text" id="link" class="wps_pickColor" value="<?php echo get_option('symposium_link'); ?>"  /> 
					<div style="position: absolute; margin-left:130px; margin-top:-110px;" class="colorpicker"></div>
					<span class="description"><?php echo __('Link Colour', 'wp-symposium'); ?></span></td> 
					</tr> 

					<tr valign="top"> 
					<td scope="row"><label for="link_hover"</label></td> 
					<td><input name="link_hover" type="text" id="link_hover" class="wps_pickColor" value="<?php echo get_option('symposium_link_hover'); ?>"  /> 
					<div style="position: absolute; margin-left:130px; margin-top:-110px;" class="colorpicker"></div>
					<span class="description"><?php echo __('Link Colour on mouse hover', 'wp-symposium'); ?></span></td> 
					</tr> 

					<tr valign="top"> 
					<td scope="row"><label for="underline"><?php echo __('Underlined?', 'wp-symposium'); ?></label></td> 
					<td>
					<select name="underline" id="underline"> 
						<option <?php if ( get_option('symposium_underline')=='') { echo "selected='selected'"; } ?> value=''><?php echo __('No', 'wp-symposium'); ?></option> 
						<option <?php if ( get_option('symposium_underline')=='on') { echo "selected='selected'"; } ?> value='on'><?php echo __('Yes', 'wp-symposium'); ?></option> 
					</select> 
					<span class="description"><?php echo __('Whether links are underlined or not', 'wp-symposium'); ?></span></td> 
					</tr> 
			
					<tr valign="top"> 
					<td scope="row"><label for="border_radius"><?php echo __('Corners', 'wp-symposium'); ?></label></td> 
					<td>
					<select name="border_radius" id="border_radius"> 
						<option <?php if ( get_option('symposium_border_radius')=='0') { echo "selected='selected'"; } ?> value='0'>0 pixels</option> 
						<option <?php if ( get_option('symposium_border_radius')=='1') { echo "selected='selected'"; } ?> value='1'>1 pixels</option> 
						<option <?php if ( get_option('symposium_border_radius')=='2') { echo "selected='selected'"; } ?> value='2'>2 pixels</option> 
						<option <?php if ( get_option('symposium_border_radius')=='3') { echo "selected='selected'"; } ?> value='3'>3 pixels</option> 
						<option <?php if ( get_option('symposium_border_radius')=='4') { echo "selected='selected'"; } ?> value='4'>4 pixels</option> 
						<option <?php if ( get_option('symposium_border_radius')=='5') { echo "selected='selected'"; } ?> value='5'>5 pixels</option> 
						<option <?php if ( get_option('symposium_border_radius')=='6') { echo "selected='selected'"; } ?> value='6'>6 pixels</option> 
						<option <?php if ( get_option('symposium_border_radius')=='7') { echo "selected='selected'"; } ?> value='7'>7 pixels</option> 
						<option <?php if ( get_option('symposium_border_radius')=='8') { echo "selected='selected'"; } ?> value='8'>8 pixels</option> 
						<option <?php if ( get_option('symposium_border_radius')=='9') { echo "selected='selected'"; } ?> value='9'>9 pixels</option> 
						<option <?php if ( get_option('symposium_border_radius')=='10') { echo "selected='selected'"; } ?> value='10'>10 pixels</option> 
						<option <?php if ( get_option('symposium_border_radius')=='11') { echo "selected='selected'"; } ?> value='11'>11 pixels</option> 
						<option <?php if ( get_option('symposium_border_radius')=='12') { echo "selected='selected'"; } ?> value='12'>12 pixels</option> 
						<option <?php if ( get_option('symposium_border_radius')=='13') { echo "selected='selected'"; } ?> value='13'>13 pixels</option> 
						<option <?php if ( get_option('symposium_border_radius')=='14') { echo "selected='selected'"; } ?> value='14'>14 pixels</option> 
						<option <?php if ( get_option('symposium_border_radius')=='15') { echo "selected='selected'"; } ?> value='15'>15 pixels</option> 
					</select> 
					<span class="description"><?php echo __('Rounded Corner radius (not supported in all browsers)', 'wp-symposium'); ?></span></td> 
					</tr> 

					<tr valign="top"> 
					<td scope="row"><label for="bigbutton_background"><?php echo __('Buttons', 'wp-symposium'); ?></label></td> 
					<td><input name="bigbutton_background" type="text" id="bigbutton_background" class="wps_pickColor" value="<?php echo get_option('symposium_bigbutton_background'); ?>"  /> 
					<div style="position: absolute; margin-left:130px; margin-top:-110px;" class="colorpicker"></div>
					<span class="description"><?php echo __('Background Colour', 'wp-symposium'); ?></span></td> 
					</tr> 

					<tr valign="top"> 
					<td scope="row"><label for="bigbutton_background_hover"></label></td> 
					<td><input name="bigbutton_background_hover" type="text" id="bigbutton_background_hover" class="wps_pickColor" value="<?php echo get_option('symposium_bigbutton_background_hover'); ?>"  /> 
					<div style="position: absolute; margin-left:130px; margin-top:-110px;" class="colorpicker"></div>
					<span class="description"><?php echo __('Background Colour on mouse hover', 'wp-symposium'); ?></span></td> 
					</tr> 

					<tr valign="top"> 
					<td scope="row"><label for="bigbutton_color"></label></td> 
					<td><input name="bigbutton_color" type="text" id="bigbutton_color" class="wps_pickColor" value="<?php echo get_option('symposium_bigbutton_color'); ?>"  /> 
					<div style="position: absolute; margin-left:130px; margin-top:-110px;" class="colorpicker"></div>
					<span class="description"><?php echo __('Text Colour', 'wp-symposium'); ?></span></td> 
					</tr> 

					<tr valign="top"> 
					<td scope="row"><label for="bigbutton_color_hover"></label></td> 
					<td><input name="bigbutton_color_hover" type="text" id="bigbutton_color_hover" class="wps_pickColor" value="<?php echo get_option('symposium_bigbutton_color_hover'); ?>"  /> 
					<div style="position: absolute; margin-left:130px; margin-top:-110px;" class="colorpicker"></div>
					<span class="description"><?php echo __('Text Colour on mouse hover', 'wp-symposium'); ?></span></td> 
					</tr> 

					<tr valign="top"> 
					<td scope="row"><label for="bg_color_1"><?php echo __('Tables', 'wp-symposium'); ?></label></td> 
					<td><input name="bg_color_1" type="text" id="bg_color_1" class="wps_pickColor" value="<?php echo get_option('symposium_bg_color_1'); ?>"  /> 
					<div style="position: absolute; margin-left:130px; margin-top:-110px;" class="colorpicker"></div>
					<span class="description"><?php echo __('Primary Colour', 'wp-symposium'); ?></span></td> 
					</tr> 

					<tr valign="top"> 
					<td scope="row"><label for="bg_color_2"></label></td> 
					<td><input name="bg_color_2" type="text" id="bg_color_2" class="wps_pickColor" value="<?php echo get_option('symposium_bg_color_2'); ?>"  /> 
					<div style="position: absolute; margin-left:130px; margin-top:-110px;" class="colorpicker"></div>
					<span class="description"><?php echo __('Row Colour', 'wp-symposium'); ?></span></td> 
					</tr> 

					<tr valign="top"> 
					<td scope="row"><label for="bg_color_3"></label></td> 
					<td><input name="bg_color_3" type="text" id="bg_color_3" class="wps_pickColor" value="<?php echo get_option('symposium_bg_color_3'); ?>"  /> 
					<div style="position: absolute; margin-left:130px; margin-top:-110px;" class="colorpicker"></div>
					<span class="description"><?php echo __('Alternative Row Colour', 'wp-symposium'); ?></span></td> 
					</tr> 

					<tr valign="top"> 
					<td scope="row"><label for="table_rollover"></label></td> 
					<td><input name="table_rollover" type="text" id="table_rollover" class="wps_pickColor" value="<?php echo get_option('symposium_table_rollover'); ?>"  /> 
					<div style="position: absolute; margin-left:130px; margin-top:-110px;" class="colorpicker"></div>
					<span class="description"><?php echo __('Row colour on mouse hover', 'wp-symposium'); ?></span></td> 
					</tr> 
		
					<tr valign="top"> 
					<td scope="row"><label for="table_border"></label></td> 
					<td>
					<select name="table_border" id="table_border"> 
						<option <?php if ( get_option('symposium_table_border')=='0') { echo "selected='selected'"; } ?> value='0'>0 pixels</option> 
						<option <?php if ( get_option('symposium_table_border')=='1') { echo "selected='selected'"; } ?> value='1'>1 pixels</option> 
						<option <?php if ( get_option('symposium_table_border')=='2') { echo "selected='selected'"; } ?> value='2'>2 pixels</option> 
						<option <?php if ( get_option('symposium_table_border')=='3') { echo "selected='selected'"; } ?> value='3'>3 pixels</option> 
					</select> 
					<span class="description"><?php echo __('Border Size', 'wp-symposium'); ?></span></td> 
					</tr> 

					<tr valign="top"> 
					<td scope="row"><label for="row_border_style"><?php echo __('Table/Rows', 'wp-symposium'); ?></label></td> 
					<td>
					<select name="row_border_style" id="row_border_styledefault_role"> 
						<option <?php if ( get_option('symposium_row_border_style')=='dotted') { echo "selected='selected'"; } ?> value='dotted'><?php echo __('Dotted', 'wp-symposium'); ?></option> 
						<option <?php if ( get_option('symposium_row_border_style')=='dashed') { echo "selected='selected'"; } ?> value='dashed'><?php echo __('Dashed', 'wp-symposium'); ?></option> 
						<option <?php if ( get_option('symposium_row_border_style')=='solid') { echo "selected='selected'"; } ?> value='solid'><?php echo __('Solid', 'wp-symposium'); ?></option> 
					</select> 
					<span class="description"><?php echo __('Border style between rows', 'wp-symposium'); ?></span></td> 
					</tr> 
		
					<tr valign="top"> 
					<td scope="row"><label for="row_border_size"></label></td> 
					<td>
					<select name="row_border_size" id="row_border_size"> 
						<option <?php if ( get_option('symposium_row_border_size')=='0') { echo "selected='selected'"; } ?> value='0'>0 pixels</option> 
						<option <?php if ( get_option('symposium_row_border_size')=='1') { echo "selected='selected'"; } ?> value='1'>1 pixels</option> 
						<option <?php if ( get_option('symposium_row_border_size')=='2') { echo "selected='selected'"; } ?> value='2'>2 pixels</option> 
						<option <?php if ( get_option('symposium_row_border_size')=='3') { echo "selected='selected'"; } ?> value='3'>3 pixels</option> 
					</select> 
					<span class="description"><?php echo __('Border size between rows', 'wp-symposium'); ?></span></td> 
					</tr> 
		
					<tr valign="top"> 
					<td scope="row"><label for="replies_border_size"><?php echo __('Other borders', 'wp-symposium'); ?></label></td> 
					<td>
					<select name="replies_border_size" id="replies_border_size"> 
						<option <?php if ( get_option('symposium_replies_border_size')=='0') { echo "selected='selected'"; } ?> value='0'>0 pixels</option> 
						<option <?php if ( get_option('symposium_replies_border_size')=='1') { echo "selected='selected'"; } ?> value='1'>1 pixels</option> 
						<option <?php if ( get_option('symposium_replies_border_size')=='2') { echo "selected='selected'"; } ?> value='2'>2 pixels</option> 
						<option <?php if ( get_option('symposium_replies_border_size')=='3') { echo "selected='selected'"; } ?> value='3'>3 pixels</option> 
					</select> 
					<span class="description"><?php echo __('For new topics/replies and topic replies', 'wp-symposium'); ?></span></td> 
					</tr> 

					<tr valign="top"> 
					<td scope="row"><label for="categories_background"><?php echo __('Miscellaneous', 'wp-symposium'); ?></label></td> 
					<td><input name="categories_background" type="text" id="categories_background" class="wps_pickColor" value="<?php echo get_option('symposium_categories_background'); ?>"  /> 
					<div style="position: absolute; margin-left:130px; margin-top:-110px;" class="colorpicker"></div>
					<span class="description"><?php echo __('Background colour of, for example, current category', 'wp-symposium'); ?></span></td> 
					</tr> 

					<tr valign="top"> 
					<td scope="row"><label for="categories_color"></label></td> 
					<td><input name="categories_color" type="text" id="categories_color" class="wps_pickColor" value="<?php echo get_option('symposium_categories_color'); ?>"  /> 
					<div style="position: absolute; margin-left:130px; margin-top:-110px;" class="colorpicker"></div>
					<span class="description"><?php echo __('Text Colour', 'wp-symposium'); ?></span></td> 
					</tr> 

					<tr valign="top"> 
					<td colspan="2"><h3><?php echo __('Forum Styles', 'wp-symposium'); ?></h3></td> 
					</tr> 
	
					<tr valign="top"> 
					<td scope="row"><label for="closed_opacity"><?php echo __('Closed topics', 'wp-symposium'); ?></label></td> 
					<td><input name="closed_opacity" type="text" id="closed_opacity" value="<?php echo get_option('symposium_closed_opacity'); ?>"  /> 
					<?php
					$closed_word = get_option('symposium_closed_word');
					?>
					<span class="description"><?php echo sprintf(__('Opacity of topics with {%s} in the subject (between 0.0 and 1.0)', 'wp-symposium'), $closed_word); ?></span></td> 
					</tr> 

					</table> 
					<br />
	 
					<h2><?php echo __('Style Templates', 'wp-symposium'); ?></h2>
						
					<p><?php echo __('To save as a new style template, enter a name below, otherwise leave blank.', 'wp-symposium'); ?></p>

					<p>
					<?php echo __('Save as:', 'wp-symposium'); ?>
					<input type='text' id='style_save_as' name='style_save_as' value='<?php if (isset($style_save_as)) { echo str_replace("'", "&apos;", stripslashes($style_save_as)); } ?>' />
					<input type="submit" name="Submit" class="button-primary" value="<?php echo __('Save', 'wp-symposium') ?>" /> 
					</p>
					</form>
						
					<?php
					$styles_lib = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix.'symposium_styles ORDER BY title');
					if ($styles_lib) {
						
						echo '<table class="widefat" style="width:450px">';
						echo '<thead>';
						echo '<tr>';
						echo '<th style="font-size:1.2em">'.__('Load Style Template', 'wp-symposium').'</th>';
						echo '<th style="font-size:1.2em"></th>';
						echo '</tr>';
						echo '</thead>';
						echo '<tbody>';
						foreach ($styles_lib as $style_lib)
						{
							echo '<form method="post" action="">';
							echo "<input type='hidden' name='sid' value='".$style_lib->sid."' />";
							echo '<tr valign="top"><td>';
								echo stripslashes($style_lib->title);
							echo "</td><td style='text-align:right'>";
								echo "<input type='submit' id='style_save_as_button' style='margin-right:10px;' class='button' value='".__('Load', 'wp-symposium')."' />";
								echo "<a class='delete' href='admin.php?page=symposium_styles&delstyle=".$style_lib->sid."'>".__('Delete', 'wp-symposium')."</a>";
							echo "</td>";
							
							echo "</tr>";
							echo "</form>";
						}
						echo "</tbody></table>";
					}
					?>
					<p style='clear:both;'><br />
					<?php echo __("NB. If changes don't follow the above, you may be overriding them with the theme stylesheet.", 'wp-symposium') ?>
					</p>
	
					<?php	
				}

			echo '</div>';
	
	 	echo '</div>'; // End of Styles 

 	echo '</div>'; // End of wrap

} 	


function symposium_mail_messages_menu() {

	global $wpdb;

	if (isset($_GET['mail_mid_del'])) {

		if (symposium_safe_param($_GET['mail_mid_del'])) {
			// Update
			$wpdb->query( $wpdb->prepare( "DELETE FROM ".$wpdb->base_prefix."symposium_mail WHERE mail_mid = %d", $_GET['mail_mid_del'] ) );
		} else {
			echo "BAD PARAMETER PASSED: ".$_GET['mail_mid_del'];
		}
		
	}

	// Used to show mail message	
	echo '<div class="mail_message_dialog"></div>';
	
  	echo '<div class="wrap">';
  	
	  	echo '<div id="icon-themes" class="icon32"><br /></div>';
	  	echo '<h2>'.__('Mail Messages', 'wp-symposium').'</h2>';
	  			
	  	$all = $wpdb->get_var("SELECT count(*) FROM ".$wpdb->base_prefix."symposium_mail"); 
		// Paging info
		$showpage = 0;
		$pagesize = 20;
		$numpages = floor($all / $pagesize);
		if ($all % $pagesize > 0) { $numpages++; }
	  	if (isset($_GET['showpage']) && $_GET['showpage']) { $showpage = $_GET['showpage']-1; } else { $showpage = 0; }
	  	if ($showpage >= $numpages) { $showpage = $numpages-1; }
		$start = ($showpage * $pagesize);		
		if ($start < 0) { $start = 0; }  
				
		// Query
		$sql = "SELECT m.* FROM ".$wpdb->base_prefix."symposium_mail m ";
		$sql .= "ORDER BY m.mail_mid DESC ";
		$sql .= "LIMIT ".$start.", ".$pagesize;
		$messages = $wpdb->get_results($sql);
				
		// Pagination (top)
		echo symposium_pagination($numpages, $showpage, "admin.php?page=symposium_mail_messages_menu&showpage=");
		
		echo '<br /><table class="widefat">';
		echo '<thead>';
		echo '<tr>';
		echo '<th>ID</td>';
		echo '<th>'.__('From', 'wp-symposium').'</th>';
		echo '<th>'.__('To', 'wp-symposium').'</th>';
		echo '<th>'.__('Subject', 'wp-symposium').'</th>';
		echo '<th>'.__('Sent', 'wp-symposium').'</th>';
		echo '<th>'.__('Action', 'wp-symposium').'</th>';
		echo '</tr>';
		echo '</thead>';
		echo '<tfoot>';
		echo '<tr>';
		echo '<th>ID</th>';
		echo '<th>'.__('From', 'wp-symposium').'</th>';
		echo '<th>'.__('To', 'wp-symposium').'</th>';
		echo '<th>'.__('Subject', 'wp-symposium').'</th>';
		echo '<th>'.__('Sent', 'wp-symposium').'</th>';
		echo '<th>'.__('Action', 'wp-symposium').'</th>';
		echo '</tr>';
		echo '</tfoot>';
		echo '<tbody>';
		
		echo '<style>.mail_rollover:hover { background-color: #ccc; } </style>';

		if ($messages) {
			
			foreach ($messages as $message) {
	
				echo '<tr class="mail_rollover">';
				echo '<td valign="top" style="width: 30px">'.$message->mail_mid.'</td>';
				echo '<td valign="top" style="width: 100px">'.symposium_profile_link($message->mail_from).'</td>';
				echo '<td valign="top" style="width: 100px">'.symposium_profile_link($message->mail_to).'</td>';
				echo '<td valign="top" style="width: 200px; text-align:center;">';
				$preview = stripslashes($message->mail_subject);
				$preview_length = 150;
				if ( strlen($preview) > $preview_length ) { $preview = substr($preview, 0, $preview_length)."..."; }
				echo '<div style="float: left;">';
				echo '<a class="show_full_message" id="'.$message->mail_mid.'" style="cursor:pointer;margin-left:6px;">';
				echo $preview;
				echo '</a></div>';
				echo '</td>';
				echo '<td valign="top" style="width: 150px">'.$message->mail_sent.'</td>';
				echo '<td valign="top" style="width: 50px">';
				$showpage = (isset($_GET['showpage'])) ? $_GET['showpage'] : 0;
				echo "<span class='trash delete'><a href='admin.php?page=symposium_mail_messages_menu&action=message_del&showpage=".$showpage."&mail_mid_del=".$message->mail_mid."'>".__('Trash', 'wp-symposium')."</a></span>";
				echo '</td>';
				echo '</tr>';			
	
			}
		} else {
			echo '<tr><td colspan="6">&nbsp;</td></tr>';
		}

		echo '</tbody>';
		echo '</table>';
	
		// Pagination (bottom)
		echo symposium_pagination($numpages, $showpage, "admin.php?page=symposium_mail_messages_menu&showpage=");
		
	echo '</div>'; // End of wrap div

}

function symposium_mail_menu() {

	global $wpdb, $current_user;

	// See if the user has posted forum settings
	if( isset($_POST[ 'symposium_update' ]) && $_POST[ 'symposium_update' ] == 'symposium_mail_menu' ) {
		$mail_all = (isset($_POST[ 'mail_all' ])) ? $_POST[ 'mail_all' ] : '';
		
		// Update database
		update_option('symposium_mail_all', $mail_all);

	}
	
	if ( isset($_POST['bulk_message']) ) {

		$cnt = 0;

		$subject = $_POST['bulk_subject'];
		$message =$_POST['bulk_message'];
		
		if ($subject == '' || $message == '') {
			echo "<div class='error'><p>".__('Please fill in the subject and message fields.', 'wp-symposium').".</p></div>";
		} else {

			$url = symposium_get_url('mail');	
	
			$sql = "SELECT * FROM ".$wpdb->base_prefix."users";
			$members = $wpdb->get_results($wpdb->prepare($sql));
			
			foreach ($members as $member) {
				
				// Send mail
				if ( $rows_affected = $wpdb->prepare( $wpdb->insert( $wpdb->base_prefix . "symposium_mail", array( 
				'mail_from' => $current_user->ID, 
				'mail_to' => $member->ID, 
				'mail_sent' => date("Y-m-d H:i:s"), 
				'mail_subject' => $subject,
				'mail_message' => $message
				 ) ) ) ) {
					 $cnt++;
				 }
		
				$mail_id = $wpdb->insert_id;
				
				// Filter to allow further actions to take place
				apply_filters ('symposium_sendmessage_filter', $member->ID, $current_user->ID, $current_user->display_name, $mail_id);
			
				// Send real email if chosen
				if ( get_symposium_meta($member->ID, 'notify_new_messages') ) {
		
					$body = "<h1>".$subject."</h1>";
					$body .= "<p><a href='".$url.symposium_string_query($url)."mid=".$mail_id."'>".sprintf(__("Go to %s Mail", "wp-symposium"), symposium_get_url('mail'))."...</a></p>";
					$body .= "<p>";
					$body .= $message;
					$body .= "</p>";
					$body .= "<p><em>";
					$body .= $current_user->display_name;
					$body .= "</em></p>";
				
					$body = str_replace(chr(13), "<br />", $body);
					$body = str_replace("\\r\\n", "<br />", $body);
					$body = str_replace("\\", "", $body);
		
					// Send real email
					if (isset($_POST['bulk_email'])) {
						symposium_sendmail($member->user_email, __('New Mail Message', 'wp-symposium'), $body);
					}
				}		
			}
			
			echo "<div class='updated slideaway'><p>".sprintf(__('Bulk message sent to %d members', 'wp-symposium'), $cnt).".</p></div>";	
			$subject = '';
			$message = '';			
		}
	} else {
		$subject = '';
		$message = '';
	}

	// Get config data to show
	$mail_all = get_option('symposium_mail_all');
	
  	echo '<div class="wrap">';
  	
	  	echo '<div id="icon-themes" class="icon32"><br /></div>';
	  	echo '<h2>'.__('Mail', 'wp-symposium').'</h2>';
		?>
		<div class="metabox-holder"><div id="toc" class="postbox"> 
			
			<form method="post" action=""> 
			<input type="hidden" name="symposium_update" value="symposium_mail_menu">
	
			<table class="form-table"> 
		
			<tr valign="top"> 
			<td scope="row"><label for="mail_all"><?php echo __('Mail to all', 'wp-symposium'); ?></label></td>
			<td>
			<input type="checkbox" name="mail_all" id="mail_all" <?php if ($mail_all == "on") { echo "CHECKED"; } ?>/>
			<span class="description"><?php echo __('Allow mail to all members, even if not a friend?', 'wp-symposium'); ?></span></td> 
			</tr> 
															
			</table> 	
		 
			<p class="submit" style='margin-left:6px;'> 
			<input type="submit" name="Submit" class="button-primary" value="<?php echo __('Save Changes', 'wp-symposium'); ?>" /> 
			</p> 
			</form> 

		</div></div>	
		
		<?php
		echo '<h3>'.__('Send bulk mail', 'wp-symposium').'</h3>';
		echo '<p>'.sprintf(__('Send a message from you (%s) to all members of this website - if running WordPress MultiSite, this means all members on your site network.', 'wp-symposium'), $current_user->display_name).'</p>';
		echo '<form method="post" action="">';
		echo '<strong>'.__('Subject', 'wp-symposium').'</strong><br />';
		echo '<textarea name="bulk_subject" style="width:500px; height:23px; margin-bottom:15px; overflow:hidden;">'.$subject.'</textarea><br />';
		echo '<strong>'.__('Message', 'wp-symposium').'</strong><br />';
		echo '<textarea name="bulk_message" style="width:500px; height:200px;">'.$message.'</textarea><br />';
		echo '<p><em>'.__('You can include HTML.', 'wp-symposium').'</em></p>';
		echo '<input type="checkbox" name="bulk_email" CHECKED> '.__('Send out email notifications?', 'wp-symposium');
		echo '<p><em>'.__('Be wary of limitations from your hosting provider. Members who do not want email notifications will not be sent one.', 'wp-symposium').'</em></p>';
		echo '<input type="submit" name="Submit" class="button-primary" value="'.__('Send', 'wp-symposium').'" />';
		echo '</form>';

	echo '</div>';
	

}

function symposium_members_menu() {
	
	global $wpdb;

	// See if the user has posted notification bar settings
	if( isset($_POST[ 'symposium_update' ]) && $_POST[ 'symposium_update' ] == 'symposium_members_menu' ) {

		$show_dir_buttons = (isset($_POST['show_dir_buttons'])) ? $_POST['show_dir_buttons'] : '';
		$dir_page_length = ($_POST['dir_page_length'] != '') ? $_POST['dir_page_length'] : '25';
		$dir_full_ver = ($_POST['dir_full_ver'] != '') ? $_POST['dir_full_ver'] : '';
		
		update_option('symposium_show_dir_buttons', $show_dir_buttons);
		update_option('symposium_dir_page_length', $dir_page_length);
		update_option('symposium_dir_full_ver', $dir_full_ver);
		

		// Included roles
		if (isset($_POST['dir_level'])) {
	   		$range = array_keys($_POST['dir_level']);
	   		$level = '';
   			foreach ($range as $key) {
				$level .= $_POST['dir_level'][$key].',';
	   		}
		} else {
			$level = '';
		}

		update_option('symposium_dir_level', serialize($level));
		
		// Put an settings updated message on the screen
		echo "<div class='updated slideaway'><p>".__('Saved', 'wp-symposium').".</p></div>";
		
	}

	// Get values to show
	$show_dir_buttons = get_option('symposium_show_dir_buttons');
	$dir_page_length = get_option('symposium_dir_page_length');
	
  	echo '<div class="wrap">';
  	
	  	echo '<div id="icon-themes" class="icon32"><br /></div>';
	  	echo '<h2>'.__('Member Directory', 'wp-symposium').'</h2>';
		?>

		<div class="metabox-holder"><div id="toc" class="postbox"> 
		
			<form method="post" action=""> 
			<input type="hidden" name="symposium_update" value="symposium_members_menu">

			<table class="form-table">

			<tr valign="top"> 
			<td scope="row"><label for="dir_full_ver"><?php echo __('Faster search?', 'wp-symposium') ?></label></td>
			<td>
			<input type="checkbox" name="dir_full_ver" id="dir_full_ver" <?php if ($dir_full_ver == "on") { echo "CHECKED"; } ?>/>
			<span class="description"><?php echo __('Improves search time, but search results are limited and cannot re-order search results', 'wp-symposium'); ?></span></td> 
			</tr> 
			
			<tr valign="top"> 
			<td scope="row"><label for="show_dir_buttons"><?php echo __('Include member actions?', 'wp-symposium') ?></label></td>
			<td>
			<input type="checkbox" name="show_dir_buttons" id="show_dir_buttons" <?php if ($show_dir_buttons == "on") { echo "CHECKED"; } ?>/>
			<span class="description"><?php echo __('Should buttons to add as a friend, or send mail, be shown on the directory?', 'wp-symposium'); ?></span></td> 
			</tr> 
			
			<tr valign="top"> 
			<td scope="row"><label for="dir_page_length"><?php echo __('Page Length', 'wp-symposium') ?></label></td> 
			<td><input name="dir_page_length" type="text" id="dir_page_length" style="width:50px" value="<?php echo $dir_page_length; ?>"  /> 
			<span class="description"><?php echo __('Number of members shown at a time on the directory', 'wp-symposium'); ?></span></td> 
			</tr> 	

			<tr valign="top"> 
			<td scope="row"><label for="dir_level"><?php echo __('Roles to include in directory', 'wp-symposium') ?></label></td> 
			<td>
			<?php

				// Get list of roles
				global $wp_roles;
				$all_roles = $wp_roles->roles;

				$dir_roles = get_option('symposium_dir_level');

				foreach ($all_roles as $role) {
					echo '<input type="checkbox" name="dir_level[]" value="'.$role['name'].'"';
					if (strpos(strtolower($dir_roles), strtolower($role['name']).',') !== FALSE) {
						echo ' CHECKED';
					}
					echo '> '.$role['name'].'<br />';
				}	

			?>
			</td></tr>
						
			</table>
		
			<p class="submit" style="margin-left:6px;"> 
			<input type="submit" name="Submit" class="button-primary" value="<?php echo __('Save Changes', 'wp-symposium'); ?>" /> 
			</p> 
			</form> 
		
		</div></div>

	<?php
	echo '</div>';
		
}
	

/* =============== ADD TO ADMIN MENU =============== */

if (is_admin()) {
	add_action('admin_menu', 'symposium_plugin_menu');
}

?>
