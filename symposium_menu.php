<?php
/*  Copyright 2010,2011  Simon Goodchild  (info@wpsymposium.com)

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
						$body .= "<p>".$forum_url.$q."cid=".$post->topic_category."&show=".$_GET['tid']."</p>";
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
	
						// Email people who want to know and prepare body
	
						if ($post->topic_parent > 0) {						
							$body = "<span style='font-size:24px'>".$parent->topic_subject."</span><br /><br />";
							$body .= "<p>".$post->display_name." ".__('replied', 'wp-symposium')."...</p>";
						} else {
							$body = "<span style='font-size:24px'>".$post->topic_subject."</span><br /><br />";
							$body .= "<p>".$post->display_name." ".__('started', 'wp-symposium')."...</p>";
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
									WHERE tid = ".$parent->tid." AND u.ID != ".$current_user->ID);
							} else {
								$query = $wpdb->get_results("
									SELECT u.ID, u.user_email
									FROM ".$wpdb->base_prefix."users u RIGHT JOIN ".$wpdb->prefix."symposium_subs s ON s.uid = u.ID 
									WHERE cid = ".$post->topic_category." AND u.ID != ".$current_user->ID);
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
							INNER JOIN ".$wpdb->base_prefix."symposium_usermeta m ON u.ID = m.uid 
							WHERE m.forum_all = 'on' AND
							ID != %d AND 
							ID NOT IN (%s)";
						$query = $wpdb->get_results($wpdb->prepare($sql, $current_user->ID, $email_list));
		
						if ($query) {						
							foreach ($query as $user) {	
		
								// Filter to allow further actions to take place
								apply_filters ('symposium_forum_newreply_filter', $user->ID, $current_user->ID, $current_user->display_name, $url);
						
								// Send mail
								symposium_sendmail($user->user_email, __('New Forum Post', 'wp-symposium'), $body);							
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
	
	// xxx
	add_menu_page('Symposium','Symposium'.$count1, 'edit_themes', 'symposium_debug', 'symposium_plugin_debug', '', 7); 
	add_submenu_page('symposium_debug', __('Installation', 'wp-symposium'), __('Installation', 'wp-symposium'), 'edit_themes', 'symposium_debug', 'symposium_plugin_debug');
	add_submenu_page('symposium_debug', __('Settings', 'wp-symposium'), __('Settings', 'wp-symposium'), 'edit_themes', 'symposium_settings', 'symposium_plugin_settings');
	add_submenu_page('symposium_debug', __('Templates', 'wp-symposium'), __('Templates', 'wp-symposium'), 'edit_themes', 'symposium_templates', 'symposium_plugin_templates');
	add_submenu_page('symposium_debug', __('Styles', 'wp-symposium'), __('Styles', 'wp-symposium'), 'edit_themes', 'symposium_styles', 'symposium_plugin_styles');

	if (function_exists('symposium_profile')) {
		add_submenu_page('symposium_debug', __('Profile', 'wp-symposium'), __('Profile', 'wp-symposium'), 'edit_themes', 'symposium_profile', 'symposium_plugin_profile');
	}
	if (function_exists('symposium_forum')) {
		add_submenu_page('symposium_debug', __('Forum', 'wp-symposium'), __('Forum', 'wp-symposium'), 'edit_themes', 'symposium_forum', 'symposium_plugin_forum');
		add_submenu_page('symposium_debug', __('Forum Categories', 'wp-symposium'), __('Forum Categories', 'wp-symposium'), 'edit_themes', 'symposium_categories', 'symposium_plugin_categories');
		add_submenu_page('symposium_debug', __('Forum Posts', 'wp-symposium'), sprintf(__('Forum Posts %s', 'wp-symposium'), $count2), 'edit_themes', 'symposium_moderation', 'symposium_plugin_moderation');
	}
	if (function_exists('add_notification_bar')) {
		add_submenu_page('symposium_debug', __('Panel', 'wp-symposium'), __('Panel', 'wp-symposium'), 'edit_themes', 'symposium_bar', 'symposium_plugin_bar');
	}
	if (function_exists('symposium_members')) {
		add_submenu_page('symposium_debug', __('Member Directory', 'wp-symposium'), __('Member Directory', 'wp-symposium'), 'edit_themes', 'symposium_members_menu', 'symposium_members_menu');
	}
	if (function_exists('symposium_mail')) {
		add_submenu_page('symposium_debug', __('Mail', 'wp-symposium'), __('Mail', 'wp-symposium'), 'edit_themes', 'symposium_mail_menu', 'symposium_mail_menu');
	}
	do_action('symposium_admin_menu_hook');
}


function symposium_plugin_templates() {
	
	global $wpdb;
	if (isset($_POST['profile_header_textarea'])) {
		$sql = "UPDATE ".$wpdb->prefix."symposium_config SET template_profile_header = '".addslashes(str_replace(chr(13), "[]", $_POST['profile_header_textarea']))."'";
		$wpdb->query( $wpdb->prepare($sql) );
	}
	if (isset($_POST['profile_body_textarea'])) {
		$sql = "UPDATE ".$wpdb->prefix."symposium_config SET template_profile_body = '".addslashes(str_replace(chr(13), "[]", $_POST['profile_body_textarea']))."'";
		$wpdb->query( $wpdb->prepare($sql) );
	}
	if (isset($_POST['page_footer_textarea'])) {
		if ($_POST['page_footer_textarea'] == "") {
			$sql = "UPDATE ".$wpdb->prefix."symposium_config SET template_page_footer = '".addslashes(str_replace(chr(13), "[]", "<!-- Powered by WP Symposium v".get_option("symposium_version")." -->"))."'";
		} else {
			$sql = "UPDATE ".$wpdb->prefix."symposium_config SET template_page_footer = '".addslashes(str_replace(chr(13), "[]", $_POST['page_footer_textarea']))."'";
		}
		$wpdb->query( $wpdb->prepare($sql) );
	}
	if (isset($_POST['email_textarea'])) {
		$sql = "UPDATE ".$wpdb->prefix."symposium_config SET template_email = '".addslashes(str_replace(chr(13), "[]", $_POST['email_textarea']))."'";
		$wpdb->query( $wpdb->prepare($sql) );
	}
	if (isset($_POST['template_forum_header_textarea'])) {
		$sql = "UPDATE ".$wpdb->prefix."symposium_config SET template_forum_header = '".addslashes(str_replace(chr(13), "[]", $_POST['template_forum_header_textarea']))."'";
		$wpdb->query( $wpdb->prepare($sql) );
	}
	if (isset($_POST['template_mail_textarea'])) {
		$sql = "UPDATE ".$wpdb->prefix."symposium_config SET template_mail = '".addslashes(str_replace(chr(13), "[]", $_POST['template_mail_textarea']))."'";
		$wpdb->query( $wpdb->prepare($sql) );
	}
	if (isset($_POST['template_mail_tray_textarea'])) {
		$sql = "UPDATE ".$wpdb->prefix."symposium_config SET template_mail_tray = '".addslashes(str_replace(chr(13), "[]", $_POST['template_mail_tray_textarea']))."'";
		$wpdb->query( $wpdb->prepare($sql) );
	}
	if (isset($_POST['template_mail_message_textarea'])) {
		$sql = "UPDATE ".$wpdb->prefix."symposium_config SET template_mail_message = '".addslashes(str_replace(chr(13), "[]", $_POST['template_mail_message_textarea']))."'";
		$wpdb->query( $wpdb->prepare($sql) );
	}
	if (isset($_POST['template_group_textarea'])) {
		$sql = "UPDATE ".$wpdb->prefix."symposium_config SET template_group = '".addslashes(str_replace(chr(13), "[]", $_POST['template_group_textarea']))."'";
		$wpdb->query( $wpdb->prepare($sql) );
	}
	if (isset($_POST['template_forum_category_textarea'])) {
		$sql = "UPDATE ".$wpdb->prefix."symposium_config SET template_forum_category = '".addslashes(str_replace(chr(13), "[]", $_POST['template_forum_category_textarea']))."'";
		$wpdb->query( $wpdb->prepare($sql) );
	}
	if (isset($_POST['template_forum_topic_textarea'])) {
		$sql = "UPDATE ".$wpdb->prefix."symposium_config SET template_forum_topic = '".addslashes(str_replace(chr(13), "[]", $_POST['template_forum_topic_textarea']))."'";
		$wpdb->query( $wpdb->prepare($sql) );
	}
	if (isset($_POST['template_group_forum_category_textarea'])) {
		// Not currently supported
		// $sql = "UPDATE ".$wpdb->prefix."symposium_config SET template_group_forum_category = '".addslashes(str_replace(chr(13), "[]", $_POST['template_group_forum_category_textarea']))."'";
		// $wpdb->query( $wpdb->prepare($sql) );
	}
	if (isset($_POST['template_group_forum_topic_textarea'])) {
		$sql = "UPDATE ".$wpdb->prefix."symposium_config SET template_group_forum_topic = '".addslashes(str_replace(chr(13), "[]", $_POST['template_group_forum_topic_textarea']))."'";
		$wpdb->query( $wpdb->prepare($sql) );
	}

	$config = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix.'symposium_config'));
    $template_profile_header = str_replace("[]", chr(13), stripslashes($config->template_profile_header));
    $template_profile_body = str_replace("[]", chr(13), stripslashes($config->template_profile_body));
    $template_page_footer = str_replace("[]", chr(13), stripslashes($config->template_page_footer));
    $template_email = str_replace("[]", chr(13), stripslashes($config->template_email));
    $template_forum_header = str_replace("[]", chr(13), stripslashes($config->template_forum_header));
    $template_mail = str_replace("[]", chr(13), stripslashes($config->template_mail));
    $template_mail_tray = str_replace("[]", chr(13), stripslashes($config->template_mail_tray));
    $template_mail_message = str_replace("[]", chr(13), stripslashes($config->template_mail_message));
    $template_group = str_replace("[]", chr(13), stripslashes($config->template_group));
    $template_forum_category = str_replace("[]", chr(13), stripslashes($config->template_forum_category));
    $template_forum_topic = str_replace("[]", chr(13), stripslashes($config->template_forum_topic));
    $template_group_forum_category = str_replace("[]", chr(13), stripslashes($config->template_group_forum_category));
    $template_group_forum_topic = str_replace("[]", chr(13), stripslashes($config->template_group_forum_topic));

  	echo '<div class="wrap">';
  	
	  	echo '<div id="icon-themes" class="icon32"><br /></div>';
	  	echo '<h2>Templates</h2>';

		// Import
		echo '<div id="symposium_import_templates_form" style="display:none">';
		echo '<input type="submit" class="symposium_templates_cancel button" style="margin-top:10px;" value="Cancel">';
		echo '<p>'.__('Paste previous exported templates into the text area below.', 'wp-symposium').'</h3>';
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

				echo '<!-- template_forum_header -->'.chr(13).chr(10);
				echo $template_forum_header.chr(13).chr(10);
				echo '<!-- end_template_forum_header -->'.chr(13).chr(10).chr(13).chr(10);

				echo '<!-- template_mail -->'.chr(13).chr(10);
				echo $template_mail.chr(13).chr(10);
				echo '<!-- end_template_mail -->'.chr(13).chr(10).chr(13).chr(10);
		
				echo '<!-- template_mail -->'.chr(13).chr(10);
				echo $template_mail.chr(13).chr(10);
				echo '<!-- end_template_mail -->'.chr(13).chr(10).chr(13).chr(10);
		
				echo '<!-- template_mail_tray -->'.chr(13).chr(10);
				echo $template_mail_tray.chr(13).chr(10);
				echo '<!-- end_template_mail_tray -->'.chr(13).chr(10).chr(13).chr(10);
		
				echo '<!-- template_mail_message -->'.chr(13).chr(10);
				echo $template_mail_message.chr(13).chr(10);
				echo '<!-- end_template_mail_message -->'.chr(13).chr(10).chr(13).chr(10);
		
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

			echo '<input id="symposium_export_templates" type="submit" class="button" style="margin-top:10px;margin-right:6px;" value="Export">';
			echo '<input id="symposium_import_templates" type="submit" class="button" style="margin-top:10px;margin-right:6px;" value="Import">';
			echo "<strong>BETA! If it doesn't work, use the 'Reset to default' links, then Save...</strong>";
	
			echo '<form action="" method="post">';
		
			// Profile Page Header
			echo '<br /><table class="widefat">';
			echo '<thead>';
			echo '<tr>';
			echo '<th style="font-size:1.2em">'.__('Profile Page Header', 'wp-symposium').'<input type="submit" class="button-primary" style="float:right; padding:2px 6px 2px 6px;margin-bottom:0px;" value="Save"></th>';
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
			echo '<th style="font-size:1.2em">'.__('Profile Page Body', 'wp-symposium').'<input type="submit" class="button-primary" style="float:right; padding:2px 6px 2px 6px;" value="Save"></th>';
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
			echo '<th style="font-size:1.2em">'.__('Page Footer', 'wp-symposium').'<input type="submit" class="button-primary" style="float:right; padding:2px 6px 2px 6px;" value="Save"></th>';
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

			// Forum Header
			echo '<br /><table class="widefat">';
			echo '<thead>';
			echo '<tr>';
			echo '<th style="font-size:1.2em">'.__('Forum Header', 'wp-symposium').'<input type="submit" class="button-primary" style="float:right; padding:2px 6px 2px 6px;" value="Save"></th>';
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
			echo '<th style="font-size:1.2em">'.__('Forum Categories (list)', 'wp-symposium').'<input type="submit" class="button-primary" style="float:right; padding:2px 6px 2px 6px;" value="Save"></th>';
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
			echo '<th style="font-size:1.2em">'.__('Forum Topics (list)', 'wp-symposium').'<input type="submit" class="button-primary" style="float:right; padding:2px 6px 2px 6px;" value="Save"></th>';
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
			echo '<th style="font-size:1.2em">'.__('Group Page', 'wp-symposium').'<input type="submit" class="button-primary" style="float:right; padding:2px 6px 2px 6px;" value="Save"></th>';
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
			echo '<th style="font-size:1.2em">'.__('Group Forum Categories (list)', 'wp-symposium').'<input type="submit" class="button-primary" style="float:right; padding:2px 6px 2px 6px;" value="Save"></th>';
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
			echo '<th style="font-size:1.2em">'.__('Group Forum Topics (list)', 'wp-symposium').'<input type="submit" class="button-primary" style="float:right; padding:2px 6px 2px 6px;" value="Save"></th>';
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

			// Mail
			echo '<br /><table class="widefat">';
			echo '<thead>';
			echo '<tr>';
			echo '<th style="font-size:1.2em">'.__('Mail Page', 'wp-symposium').'<input type="submit" class="button-primary" style="float:right; padding:2px 6px 2px 6px;" value="Save"></th>';
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
				echo '<td>[compose_form]</td>';
				echo '<td>'.__('Compose new message form', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[compose]</td>';
				echo '<td>'.__('Compose label, translated', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[inbox]</td>';
				echo '<td>'.__('In Box label, translated', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td>[sent]</td>';
				echo '<td>'.__('Sent label, translater', 'wp-symposium').'</td>';
				echo '</tr>';
				echo '</tbody>';
				echo '</table>';
				echo '<textarea id="template_mail_textarea" name="template_mail_textarea" style="width:60%;height: 200px;">';
				echo $template_mail;
				echo '</textarea>';
				echo '<br /><a id="reset_mail" href="javascript:void(0)">'.__('Reset to default', 'wp-symposium').'</a>';
			echo '</td>';
			echo '</tr>';
			echo '</tbody>';
			echo '</table>';

			// Mail Tray Item
			echo '<br /><table class="widefat">';
			echo '<thead>';
			echo '<tr>';
			echo '<th style="font-size:1.2em">'.__('Mail Page: Tray Item', 'wp-symposium').'<input type="submit" class="button-primary" style="float:right; padding:2px 6px 2px 6px;" value="Save"></th>';
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
			echo '<th style="font-size:1.2em">'.__('Mail Page: Message', 'wp-symposium').'<input type="submit" class="button-primary" style="float:right; padding:2px 6px 2px 6px;" value="Save"></th>';
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

			// WPS Email Notifications
			echo '<br /><table class="widefat">';
			echo '<thead>';
			echo '<tr>';
			echo '<th style="font-size:1.2em">'.__('WPS Emails', 'wp-symposium').'<input type="submit" class="button-primary" style="float:right; padding:2px 6px 2px 6px;" value="Save"></th>';
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
				echo '<td>'.__('Footer Message)', 'wp-symposium').'</td>';
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
	$config = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix.'symposium_config'));

  	echo '<div class="wrap">';
  	
	  	echo '<div id="icon-themes" class="icon32"><br /></div>';
	  	echo '<h2>Forum Posts</h2>';
	  	
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
					echo '<img src="'.WPS_IMAGES_URL.'/forum_orange.png" alt="Unapproved" />';
				} else {
					echo '<img src="'.WPS_IMAGES_URL.'/forum_green.png" alt="Unapproved" />';
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

	  	echo '<div id="icon-themes" class="icon32"><br /></div>';
	  	echo '<h2>WP Symposium Installation</h2>';
	  	
	  	// ********** Summary

		// Get config (after any possible changes)
		$config = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix.'symposium_config'));

		echo '<div style="margin-top:10px; margin-bottom:10px">';
			_e("Visit this page to complete installation; after you add a WP Symposium shortcode to a page; change pages with WP Symposium shortcodes; if you change WordPress Permalinks; or if you experience problems.", "wp-symposium");
			echo '<br />';
			echo sprintf(__("For the recent change log see the <a href='%s' target='_blank'>WPS Trac</a>.", "wp-symposium"), 'https://wpsymposium-trac.sourcerepo.com/wpsymposium_trac');
		echo '</div>';

		echo '<table class="widefat">';
		echo '<thead>';
		echo '<tr>';
		echo '<th>'.__('For information, other WPS Plugins available', 'wp-symposium').'</th>';
		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';
		echo '<tr>';
			echo '<td style="padding:10px">';
		  	echo '<a target="_blank" href="http://www.wpsymposium.com/downloadinstall"><img border="0" src="http://www.wpsymposium.com/wp-content/themes/socialsymposium/images/banner.png" /></a>';
			echo '</td>';
		echo '</tr>';
		echo '</tbody>';
		echo '</table>';
		
		
		echo "<div style='margin-top:15px'>";

			echo '<table class="widefat">';
			echo '<thead>';
			echo '<tr>';
			echo '<th width="30px">'.__('Installed', 'wp-symposium').'</th>';
			echo '<th width="30px">'.__('Activated', 'wp-symposium').'</th>';
			echo '<th width="100px">'.__('Plugin', 'wp-symposium').'</th>';
			echo '<th>'.__('WordPress page/URL Found', 'wp-symposium').'</th>';
			echo '<th width="90px;">'.__('Status', 'wp-symposium').' [<a href="javascript:void(0);" id="symposium_url">?</a>]</tg>';
			echo '<th class="symposium_url">'.__('WPS Setting', 'wp-symposium').'</th>';
			echo '</tr>';
			echo '</thead>';
			echo '<tbody>';
			echo '<tr>';
				echo '<td style="text-align:center"><img src="'.WPS_IMAGES_URL.'/tick.png" /></td>';
				echo '<td style="text-align:center"><img src="'.WPS_IMAGES_URL.'/tick.png" /></td>';
				echo '<td>'.__('Core', 'wp-symposium').' v'.get_option("symposium_version").'</td>';
				echo '<td>-</td>';
				echo '<td style="text-align:center"><img src="'.WPS_IMAGES_URL.'/smilies/good.png" /></td>';
				echo '<td class="symposium_url" style="background-color:#efefef">-</td>';
			echo '</tr>';

			// Get version numbers installed (if applicable)
			$mobile_ver = get_option("symposium_mobile_version");
			if ($mobile_ver != '') $mobile_ver = "v".$mobile_ver;

			install_row('Forum', 'symposium-forum', 'symposium_forum', $config->forum_url, 'wp-symposium/symposium_forum.php', 'admin.php?page=symposium_forum', __('The forum plugin must be installed in ', 'wp-symposium').WP_PLUGIN_DIR.'/wp-symposium.');
			install_row('Profile', 'symposium-profile', 'symposium_profile', $config->profile_url, 'wp-symposium/symposium_profile.php', 'admin.php?page=symposium_profile', __('The profile plugin must be installed in ', 'wp-symposium').WP_PLUGIN_DIR.'/wp-symposium.');
			install_row('Mail', 'symposium-mail', 'symposium_mail', $config->mail_url, 'wp-symposium/symposium_mail.php', '', __('The mail plugin must be installed in ', 'wp-symposium').WP_PLUGIN_DIR.'/wp-symposium.');
			install_row('Members', 'symposium-members', 'symposium_members', $config->members_url, 'wp-symposium/symposium_members.php', 'admin.php?page=symposium_members_menu', __('The members directory plugin must be installed in ', 'wp-symposium').WP_PLUGIN_DIR.'/wp-symposium.');
			install_row('Panel', '', 'add_notification_bar', '-', 'wp-symposium/symposium_bar.php', 'admin.php?page=symposium_bar', __('The panel plugin must be installed in ', 'wp-symposium').WP_PLUGIN_DIR.'/wp-symposium.');

			do_action('symposium_installation_hook');

			echo '</tbody>';
			echo '</table>';
				
		echo "</div>";

		echo "<div style='width:45%; float:right'>";

	  		echo '<div id="icon-themes" class="icon32"><br /></div>';
			echo '<h2>Core Information</h2>';

			echo '<p>';
			echo __('Site domain name', 'wp-symposium').': '.get_bloginfo('url').'<br />';
			if ( get_option('permalink_structure') != '' ) { 
				echo __('Permalinks: Enabled', 'wp-symposium'); 
			} else {
				echo __('Permalinks: Disabled', 'wp-symposium'); 
			}
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

		echo "</div>";
		echo "<div style='width:45%; float:left'>";

	  		echo '<div id="icon-themes" class="icon32"><br /></div>';
			echo '<h2>Available Versions</h2>';
			
			echo '<p>'.__('Bronze member plugin version numbers match the core version number.', 'wp-symposium').'</p>';
			echo '<p>'.__('If your WPS plugin versions don\'t match download from', 'wp-symposium').' <a href="http://www.wpsymposium.com/downloadinstall">http://www.wpsymposium.com/downloadinstall</a>.</p>';
			echo '<p>'.__('To install WPS plugins, download the ZIP file from above, extract the contents, and copy into your plugins folder.', 'wp-symposium').' ('.WP_PLUGIN_DIR.')</p>';

		echo "</div>";
		
		// End of Summary

		echo "<div style='clear: both;'></div>";
	  	echo '<div id="icon-themes" class="icon32"><br /></div>';
	  	echo '<h2>Health Check</h2>';
		
		echo "<div>";
		_e("The following sections provide a 'health check' of your installation, and allows you to test/check various WP Symposium features.", "wp-symposium");
		echo "</div>";
		
		echo "<div style='clear: both; width:45%; float:right'>";

		  	echo '<h3 style="clear:both">'.__('Database Purge Tool', 'wp-symposium').'</h3><p>';
		
			// Purge users
			if (isset($_POST['purge_users']) && $_POST['purge_users'] != '' && $_POST['purge_users'] > 0 && is_numeric($_POST['purge_users']) ) {
				
				$sql = "SELECT uid FROM ".$wpdb->prefix."symposium_usermeta WHERE last_activity <= '".date("Y-m-d H:i:s",strtotime('-'.$_POST['purge_users'].' days'))."'";	
				$members = $wpdb->get_results($sql);
				
				$cnt = 0;
				foreach ($members as $member) {
					$cnt++;
					$wpdb->query( $wpdb->prepare("DELETE FROM ".$wpdb->prefix."users WHERE ID = ".$member->uid) );
					$wpdb->query( $wpdb->prepare("DELETE FROM ".$wpdb->prefix."symposium_subs WHERE uid = ".$member->uid) );
					$wpdb->query( $wpdb->prepare("DELETE FROM ".$wpdb->prefix."symposium_mail WHERE mail_from = ".$member->uid." OR mail_to = ".$member->uid) );
					$wpdb->query( $wpdb->prepare("DELETE FROM ".$wpdb->prefix."symposium_topics WHERE topic_owner = ".$member->uid) );
					$wpdb->query( $wpdb->prepare("DELETE FROM ".$wpdb->prefix."symposium_group_members WHERE member_id = ".$member->uid) );
					$wpdb->query( $wpdb->prepare("DELETE FROM ".$wpdb->prefix."symposium_friends WHERE friend_from = ".$member->uid." OR friend_to = ".$member->uid) );
					$wpdb->query( $wpdb->prepare("DELETE FROM ".$wpdb->prefix."symposium_chat WHERE chat_to = ".$member->uid." OR chat_from = ".$member->uid) );
					$wpdb->query( $wpdb->prepare("DELETE FROM ".$wpdb->prefix."symposium_comments WHERE subject_uid = ".$member->uid." OR author_uid = ".$member->uid) );
				}
				$sql = "DELETE FROM ".$wpdb->prefix."symposium_usermeta WHERE last_activity <= '".date("Y-m-d H:i:s",strtotime('-'.$_POST['purge_users'].' days'))."'";	
				$wpdb->query( $wpdb->prepare($sql) );
				
		        echo "<div style='border:1px solid #060;background-color: #9f9; border-radius:5px;padding-left:8px; margin-bottom:10px;'>";
				echo "Users deleted: ".$cnt;
				echo "</div>";
			}
	
			// Purge chat
			if (isset($_POST['purge_chat']) && $_POST['purge_chat'] != '' && is_numeric($_POST['purge_chat']) ) {
				
				$sql = "DELETE FROM ".$wpdb->prefix."symposium_chat WHERE chat_timestamp <= '".date("Y-m-d H:i:s",strtotime('-'.$_POST['purge_chat'].' days'))."'";	
				$wpdb->query( $wpdb->prepare($sql) );
				
		        echo "<div style='border:1px solid #060;background-color: #9f9; border-radius:5px;padding-left:8px; margin-bottom:10px;'>";
				echo "Chat purged.";
				echo "</div>";
			}
	
			/*
			// Remove Duplicate/Rogue members
			$sql = "CREATE TABLE ".$wpdb->prefix."wps_tmp AS SELECT * FROM ".$wpdb->prefix."symposium_usermeta WHERE 1 GROUP BY uid; ";
			$wpdb->query( $wpdb->prepare($sql) );
			$sql = "DELETE FROM ".$wpdb->prefix."symposium_usermeta WHERE mid NOT IN (SELECT mid FROM ".$wpdb->prefix."wps_tmp); ";
			$wpdb->query( $wpdb->prepare($sql) );
			$sql = "DROP TABLE ".$wpdb->prefix."wps_tmp;";
			$wpdb->query( $wpdb->prepare($sql) );
			$sql = "DELETE FROM ".$wpdb->prefix."symposium_usermeta WHERE uid = 0;";
			$wpdb->query( $wpdb->prepare($sql) );
			*/
			
			echo '<p>'.__('Users and chat purged are <strong>deleted</strong> - you cannot undo this! I recommend you take a backup first.', 'wp-symposium').'</p>';
			echo '<p>'.__('Mail, forum topics/replies, activity, group membership, friends and chat are also removed.', 'wp-symposium').'</p>';
			echo '<form action="" method="post"><p>';
			echo __('Users who have not logged in for at least', 'wp-symposium');
				echo ' <input type="text" size="3" name="purge_users"> ';
				echo __('days', 'wp-symposium')."<br />";
			echo __('Chat older than', 'wp-symposium');
				echo ' <input type="text" size="3" name="purge_chat"> ';
				echo __('days', 'wp-symposium')."</p>";
			echo '<input type="submit" class="button-primary delete" value="Purge">';
			echo '</form>';
	
		  	echo '<h3>'.__('Image Uploading', 'wp-symposium').'</h3><p>';
		
	 		echo "<div id='symposium_user_login' style='display:none'>".strtolower($current_user->user_login)."</div>";
			echo "<div id='symposium_user_email' style='display:none'>".strtolower($current_user->user_email)."</div>";
			if ($config->img_db == "on") {
				echo __("<p>You are storing images in the database.</p>", "wp-symposium");
				if ($config->img_upload != '' ) {
					echo "<p><img src='".WP_CONTENT_URL."/plugins/wp-symposium/uploadify/get_admin_avatar.php?' style='width:100px; height:100px' /></p>";
				}
			} else {
				echo __("<p>You are storing images in the file system.</p>", "wp-symposium");			
				$profile_photo = $wpdb->get_var($wpdb->prepare("SELECT profile_photo FROM ".$wpdb->prefix.'symposium_usermeta WHERE uid='.$current_user->ID));
				$src = $config->img_url."/members/".$current_user->ID."/profile/".$profile_photo;
	
				if ($profile_photo != '') {
					echo "<p><img src='".$src."' style='width:100px; height:100px' /></p>";
				}
	
				if (file_exists($config->img_path)) {
				    echo "<p>The folder ".$config->img_path." exists, where images uploaded will be placed.</p>";
				} else {
					if (!mkdir($config->img_path, 0777, true)) {
					    echo '<p>Failed to create '.$config->img_path.'...</p>';
					} else {
						echo '<p>Created '.$config->img_path.'.</p>';
					}
				}
				
				if ($config->img_url == '') {
			   		echo "<p>".$fail.__('You must update the URL for your images on the <a href="admin.php?page=symposium_settings">Settings</a>.', 'wp-symposium').$fail2."</p>";
				} else {
					echo "<p>The URL to your images folder is ".$config->img_url.".</p>";
				}
			}
			
			
			echo '<input id="admin_file_upload" name="file_upload" type="file" />';
			echo '<div id="admin_image_to_crop"></div>';
		
			// ********** Reset database version
			
			echo '<br style="clear:both" />';
		   	echo '<h3>'.__('Re-apply database upgrades', 'wp-symposium').'</h3>';
		   	echo "<p>".__('To re-run the database table creation/modifications, <a href="admin.php?page=symposium_debug&force_create_wps=yes">click here</a>. This will not destroy any existing tables or data.', 'wp-symposium')."</p>";
		
			// ********** Test AJAX
			
		   	echo '<h3>'.__('Anindya AJAX test', 'wp-symposium').'</h3>';
		   	echo '<p>'.__('An AJAX function will be called, passing a random number as a parameter. If the AJAX call is successful, that value will be returned multipled by 100, and shown below on screen', 'wp-symposium').'.</p>';
		   	echo '<input type="text" id="testAJAX_results" style="width: 200px" value="'.__('Result will be posted here', 'wp-symposium').'.">';   		
		   	echo '<p class="submit"><input type="submit" id="testAJAX" name="Submit" class="button-primary" value="'.__('Click to test', 'wp-symposium').'" /></p>';
		   	echo '</p>';
	   		   	
		echo "</div><div style='width:45%; float:left'>";

			// ********** Stylesheets
	
		   	echo '<h3>Stylesheets</h3>';
	
			// CSS check
		    $myStyleFile = WP_PLUGIN_DIR . '/wp-symposium/css/symposium.css';
		    if ( !file_exists($myStyleFile) ) {
				echo $fail.__( sprintf('Stylesheet (%s) not found.', $myStyleFile), 'wp-symposium').$fail2;
		    } else {
		    	echo "<p style='color:green; font-weight:bold;'>".__( sprintf("Stylesheet (%s) found.", $myStyleFile) )."</p>";
		    }
		    
			// ********** Javascript
	
		   	echo '<h3>Javascript</h3>';
	
			// JS check
		    $myJSfile = WP_PLUGIN_DIR . '/wp-symposium/js/symposium.js';
		    if ( !file_exists($myJSfile) ) {
				echo $fail.__( sprintf('Javascript file (%s) not found, try de-activating and re-activating the core WPS plugin.', $myJSfile), 'wp-symposium').$fail2;
		    } else {
		    	echo "<p style='color:green; font-weight:bold;'>".__( sprintf("Javascript file (%s) found.", $myJSfile) )."</p>";
		    }
		    echo "<p>If you find that certain WPS things don't work, like buttons or uploading profile photos, it is probably because the Symposium Javascript file isn't loading and/or working. Usually, this is because of another WordPress plugin. Try deactivating all non-WPS plugins and switching to the TwentyEleven theme. If WPS then works, re-activate the plug-ins one at a time until the error re-occurs, this will help you locate the plugin that is clashing. Then switch your theme back. Also try using Firefox, with the Firebug add-in installed - this will show you where the Javascript error is occuring.</p>";
		    	  	
		    echo "<div id='jstest'>".$fail.__( "You have problems with Javascript. This may be because a plugin is loading another version of jQuery or jQuery UI - try deactivating all plugins apart from WPS plugins, and re-activate them one at a time until the error re-occurs, this will help you locate the plugin that is clashing. It might also be because there is an error in a JS file, either the symposium.js or another plugin script. Always try re-activating the core WPS plugin.", "wp-symposium").$fail2."</div>";
		    
			// ********** Test Email   	
			
		    if( isset($_POST[ 'symposium_testemail' ]) && $_POST[ 'symposium_testemail' ] == 'Y' ) {
		    	$to = $_POST['symposium_testemail_address'];
				if (symposium_sendmail($to, "WP Symposium Test Email", __("This is a test email sent from", "wp-symposium")." ".get_bloginfo('url'))) {
					echo "<div class='updated'><p>";
					$from = $wpdb->get_var($wpdb->prepare("SELECT from_email FROM ".$wpdb->prefix.'symposium_config'))."\r\n";
					echo sprintf(__('Email sent to %s from', 'wp-symposium'), $to);
					echo ' '.$from;
					echo "</p></div>";
				} else {
					echo "<div class='error'><p>".__("Email failed to send", "wp-symposium").".</p></div>";
				}
		    }
		   	echo '<h3>'.__('Send a test email', 'wp-symposium').'</h3>';
		   	echo '<p>'.__('Enter a valid email address to test sending an email from the server', 'wp-symposium').'.</p>';
		   	echo '<form method="post" action="">';
			echo '<input type="hidden" name="symposium_testemail" value="Y">';
		   	echo '<input type="text" name="symposium_testemail_address" value="" style="width:300px" class="regular-text">';
		   	echo '<p class="submit"><input type="submit" name="Submit" class="button-primary" value="'.__('Send email', 'wp-symposium').'" /></p>';
		   	echo '</form>';
			
			// ********** Daily Digest 
			
			echo '<h3>'.__('Daily Digest', 'wp-symposium').'</h3>';
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
		   	echo '<input type="checkbox" name="symposium_dailydigest_users" > '.__('Also send Daily Digest to users', 'wp-symposium');
		   	echo '<p class="submit"><input type="submit" name="Submit" class="button-primary" value="'.__('Send Daily Digest', 'wp-symposium').'" /></p>';
		   	echo '</form>';

	  	echo '</div>';
	  	
	  	
		// Update version on WPS (you can remove this section if you like) ===================
		// It provides information to help manage the product development
		$url = get_bloginfo('url');
		$localhost = false;
		if (strpos($url, '127.0.0.1') != FALSE) { $localhost = true; }
		if (strpos($url, 'localhost') != FALSE) { $localhost = true; }
	
		$goto = "-";
		$version = get_option("symposium_version");
		$users = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->base_prefix."users"); 
		$goto = "http://www.wpsymposium.com/wp-content/symposium_activation.php?action=symposium_activationlog&url=".$url."&version=".$version."&users=".$users;
		?>
		<iframe style="display:none; height: 50px; width:50px;" src="<?php echo $goto; ?>"></iframe>
		<?php
		// End Update ========================================================================
	   	   	
  	echo '</div>';
}
	    

function install_row($name, $shortcode, $function, $config_url, $plugin_dir, $settings_url, $install_help) {

	global $wpdb;
	$install_help = str_replace('\\', '/', $install_help);
	if (strpos($name, " ") ) {
		list($name, $ver) = explode(" ",$name);
	} else {
		$ver = '';
	}
	$name = str_replace('_', ' ', $name);

	echo '<tr>';
		
		// Installed?
		echo '<td style="text-align:center">';
			if ( file_exists(WP_PLUGIN_DIR.'/'.$plugin_dir) ) {
				$status = 'installed';
				echo '<img src="'.WPS_IMAGES_URL.'/tick.png" />';
			} else {
				$status = 'notinstalled';
				echo '<img src="'.WPS_IMAGES_URL.'/cross.png" />';
			}
		echo '</td>';
		
		// Activated?
		echo '<td style="text-align:center">';
		if (function_exists($function)) { 
			$status = 'tick'; 
			echo '<a href="plugins.php?plugin_status=active">';
			echo '<img src="'.WPS_IMAGES_URL.'/tick.png" />';
		} else {
			if ($status != 'notinstalled') { $status = 'cross'; }
			echo '<a href="plugins.php?plugin_status=inactive">';
			echo '<img src="'.WPS_IMAGES_URL.'/cross.png" />';
		}
		echo '</a></td>';
		
		// Name of Plugin
		echo '<td>';
			echo $name.' '.$ver;
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
			echo '<td>';
			if ( ($status != 'cross') && ($status != 'notinstalled') && ($shortcode != '') ) {
				$status = 'add';
				echo '<div style="float:left" id="symposium_wait_'.$shortcode.'"></div>';
				echo '<div style="float:left; width:175px">'.sprintf(__('Add [%s] to:', 'wp-symposium'), $shortcode).'</div>';
				echo '<input type="submit" class="button symposium_addnewpage" id="'.$name.'" title="'.$shortcode.'" value="'.__('New Page', 'wp-symposium').'" />';
				echo ' '.__('or', 'wp-symposium').' ';
				echo '<select id="symposium_pagechoice_'.$shortcode.'" style="width:120px">';
				$sql = "SELECT * FROM ".$wpdb->prefix."posts WHERE post_status = 'publish' AND post_type = 'page' ORDER BY post_title";
				$pages = $wpdb->get_results($sql);
				if ($pages) {
					foreach ($pages as $page) {
						echo '<option value="'.$page->ID.'">'.$page->post_title;
					}
				}
				echo '</select> ';
				echo '<input type="submit" class="button symposium_addtopage" id="'.$name.'" title="'.$shortcode.'" value="'.__('Apply', 'wp-symposium').'" />';
			} else {
				if ($status == 'tick') {
					if ($settings_url != '') {
						echo '[<a href="'.$settings_url.'">'.__('Configure', 'wp-symposium').'</a>]';
					}
				}
				if ($function == 'add_notification_bar') {
					echo ' [<a href="http://www.wpswiki.com/index.php?title=Chat_options" target="_blank">'.__('Read this!', 'wp-symposium').'</a>]';
				}
			}
			echo '</td>';
		}
		
		// Status
		echo '<td style="text-align:center">';

			if ($status == 'notinstalled') {
				if ($function != 'symposium_gallery') {
					echo '[<a href="javascript:void(0)" class="symposium_help" title="'.$install_help.'">'.__('Install', 'wp-symposium').'</a>]';
				} else {
					echo __('Coming soon', 'wp-symposium');
				}
			}
			if ($status == 'tick') {
				echo '<img src="'.WPS_IMAGES_URL.'/smilies/good.png" />';
			}
			if ($status == 'cross') {			
				echo '[<a href="plugins.php?plugin_status=inactive">'.__('Activate', 'wp-symposium').'</a>]';
			}
			if ( ($status == 'error') && ($shortcode != '') ) {
				// Fix URL
				$field_exists = $wpdb->get_results("SHOW fields FROM ".$wpdb->prefix . "symposium_config"." WHERE Field = '".strtolower($name)."_url'");
				if ($field_exists) {
					$wpdb->query( $wpdb->prepare( "
						UPDATE ".$wpdb->prefix.'symposium_config'."
						SET ".strtolower($name)."_url = %s", $url  ) );
					echo '[<a href="javascript:void(0)" class="symposium_help" title="'.__("URL updated successfully. It is important to visit this page to complete installation; after you add a WP Symposium shortcode to a page; change pages with WP Symposium shortcodes; if you change WordPress Permalinks; or if you experience problems.", "wp-symposium").'">'.__('Updated ok!', 'wp-symposium').'</a>]';
				}
			}
			if ($status == 'add') {
				echo '<img src="'.WPS_IMAGES_URL.'/'.$status.'.png" />';
			}
		echo '</td>';

		// Setting in database
		echo '<td class="symposium_url" style="background-color:#efefef">';
			$field_exists = $wpdb->get_results("SHOW fields FROM ".$wpdb->prefix."symposium_config"." WHERE Field = '".strtolower($name)."_url'");

			if (!$field_exists) { 

				echo 'n/a';

			} else {

				$sql = "SELECT ".strtolower($name)."_url FROM ".$wpdb->prefix."symposium_config";
				$value = $wpdb->get_var($wpdb->prepare($sql) );

				if ($value != 'Important: Please update!') {
					echo $value;
				}

			}
		echo '</td>';
		
	echo '</tr>';

}

function symposium_field_exists($tablename, $fieldname) {
	global $wpdb;
	$fields = $wpdb->get_results("SHOW fields FROM ".$tablename." WHERE Field = '".$fieldname."'");

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
	        $use_chat = isset($_POST[ 'use_chat' ]) ? $_POST[ 'use_chat' ] : '';
	        $use_chatroom = isset($_POST[ 'use_chatroom' ]) ? $_POST[ 'use_chatroom' ] : '';
	        $chatroom_banned = $_POST[ 'chatroom_banned' ];
	        $bar_polling = $_POST[ 'bar_polling' ];
	        $chat_polling = $_POST[ 'chat_polling' ];
	        $use_wp_profile = isset($_POST[ 'use_wp_profile' ]) ? $_POST[ 'use_wp_profile' ] : 0;

			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix."symposium_config SET use_chat = '".$use_chat."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix."symposium_config SET use_chatroom = '".$use_chatroom."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix."symposium_config SET chatroom_banned = '".$chatroom_banned."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix."symposium_config SET bar_polling = '".$bar_polling."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix."symposium_config SET chat_polling = '".$chat_polling."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix."symposium_config SET use_wp_profile = '".$use_wp_profile."'") );					
			
	        // Put an settings updated message on the screen
			echo "<div class='updated slideaway'><p>".__('Saved', 'wp-symposium').".</p></div>";
			
	    }

		$config = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix.'symposium_config'));					

				echo '<div class="metabox-holder"><div id="toc" class="postbox">';

					$sound = $config->sound;
					$use_chat = $config->use_chat;
					$use_chatroom = $config->use_chatroom;
					$chatroom_banned = $config->chatroom_banned;
					$bar_polling = $config->bar_polling;
					$chat_polling = $config->chat_polling;
					$use_wp_profile = $config->use_wp_profile;
					?>

					<form method="post" action=""> 
					<input type="hidden" name="symposium_update" value="symposium_plugin_bar">
				
					<table class="form-table">

					<!--
					<tr valign="top"> 
					<td scope="row"><label for="sound">Default Sound Alert</label></td> 
					<td>
					<select name="sound">
						<option value='None'<?php if ($sound == 'None') { echo ' SELECTED'; } ?>>None</option>
						<option value='baby.mp3'<?php if ($sound == 'baby.mp3') { echo ' SELECTED'; } ?>>Baby</option>
						<option value='beep.mp3'<?php if ($sound == 'beep.mp3') { echo ' SELECTED'; } ?>>Beep</option>
						<option value='bell.mp3'<?php if ($sound == 'bell.mp3') { echo ' SELECTED'; } ?>>Bell</option>
						<option value='buzzer.mp3'<?php if ($sound == 'buzzer.mp3') { echo ' SELECTED'; } ?>>Buzzer</option>
						<option value='chime.mp3'<?php if ($sound == 'chime.mp3') { echo ' SELECTED'; } ?>>Chime</option>
						<option value='doublechime.mp3'<?php if ($sound == 'doublechime.mp3') { echo ' SELECTED'; } ?>>Double Chime</option>
						<option value='dudeyougotmail.mp3'<?php if ($sound == 'dudeyougotmail.mp3') { echo ' SELECTED'; } ?>>Dude! You got mail!</option>
						<option value='hacksaw.mp3'<?php if ($sound == 'hacksaw.mp3') { echo ' SELECTED'; } ?>>Hacksaw</option>
						<option value='incoming.mp3'<?php if ($sound == 'incoming.mp3') { echo ' SELECTED'; } ?>>Incoming!</option>
						<option value='tap.mp3'<?php if ($sound == 'tap.mp3') { echo ' SELECTED'; } ?>>Tap</option>
						<option value='youvegotmail.mp3'<?php if ($sound == 'youvegotmail.mp3') { echo ' SELECTED'; } ?>>You've got mail</option>
					</select> 
					<span class="description"><?php echo __('Plays for new mail, chat message, subscribed forum topic post made, etc', 'wp-symposium'); ?></span></td> 
					</tr> 
					-->
					
					<?php //if ($sound != 'None') { echo '<embed src="'.WP_PLUGIN_URL.'/wp-symposium/soundmanager/'.$sound.'" width="0" height="0" loop="false" autostart="true"></embed>'; } ?>
					
					<tr valign="top"> 
					<td scope="row"><label for="use_chat">Enable chat windows</label></td>
					<td>
					<input type="checkbox" name="use_chat" id="use_chat" <?php if ($use_chat == "on") { echo "CHECKED"; } ?>/>
					<span class="description"><?php echo __('Real-time chat windows', 'wp-symposium'); ?></span></td> 
					</tr> 
				
					<tr valign="top"> 
					<td scope="row"><label for="use_chatroom">Enable chatroom</label></td>
					<td>
					<input type="checkbox" name="use_chatroom" id="use_chatroom" <?php if ($use_chatroom == "on") { echo "CHECKED"; } ?>/>
					<span class="description"><?php echo __('Real-time chatroom (chat seen by all members)', 'wp-symposium'); ?></span></td> 
					</tr> 

					<tr valign="top"> 
					<td scope="row"><label for="chatroom_banned">Banned chatroom words</label></td> 
					<td><input name="chatroom_banned" type="text" id="chatroom_banned"  value="<?php echo $chatroom_banned; ?>" /> 
					<span class="description"><?php echo __('Comma separated list of words not allowed in the chatroom', 'wp-symposium'); ?></td> 
					</tr> 
												
					<tr valign="top"> 
					<td scope="row"><label for="bar_polling">Polling Intervals</label></td> 
					<td><input name="bar_polling" type="text" id="bar_polling"  value="<?php echo $bar_polling; ?>" /> 
					<span class="description"><?php echo __('Frequency of checks for new mail, friends online, etc, in seconds', 'wp-symposium'); ?></td> 
					</tr> 
								
					<tr valign="top"> 
					<td scope="row"><label for="chat_polling">&nbsp;</label></td> 
					<td><input name="chat_polling" type="text" id="chat_polling"  value="<?php echo $chat_polling; ?>" /> 
					<span class="description"><?php echo __('Frequency of chat window updates in seconds', 'wp-symposium'); ?></td> 
					</tr> 

					<tr valign="top"> 
					<td scope="row"><label for="use_wp_profile">Profile Link</label></td> 
					<td><input type="checkbox" name="use_wp_profile" id="use_wp_profile" <?php if ($use_wp_profile == "on") { echo "CHECKED"; } ?>/>
					<span class="description"><?php echo __('Link to WordPress user profile page?', 'wp-symposium'); ?></td> 
					</tr> 


					</table> 
					 
					<p class="submit" style="margin-left:6px"> 
					<input type="submit" name="Submit" class="button-primary" value="<?php echo __('Save Changes', 'wp-symposium'); ?>" /> 
					</p> 
					</form> 
					
					<p style="margin-left:6px">
					<strong><?php _e('Notes:', 'wp-symposium'); ?></strong>
					<ol>
					<li><?php _e('The polling intervals occur in addition to an initial check on each page load.', 'wp-symposium'); ?></li>
					<li><?php _e('The more frequent the polling intervals, the greater the load on your server.', 'wp-symposium'); ?></li>
					<li><?php _e('Disabling chat windows will reduce the load on the server.', 'wp-symposium'); ?></li>
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

		// Delete an extended field?
   		if ( isset($_GET['del_eid']) && $_GET['del_eid'] != '') {
			$wpdb->query( $wpdb->prepare( "
				DELETE FROM ".$wpdb->prefix.'symposium_extended'." WHERE eid = %d", 
		        $_GET['del_eid']  ) );
		        
		    // Loop through all users
			$users = $wpdb->get_results("SELECT uid, extended from ".$wpdb->base_prefix."symposium_usermeta");
			foreach ($users as $user) {
				$tmp = '';
				$fields = explode('[|]', $user->extended);
				foreach ($fields as $field) {
					$split = explode('[]', $field);
					if ( ($split[0] != $_GET['del_eid']) && ($split[0] != '') ) {
						$tmp .= addslashes($split[0])."[]".addslashes($split[1])."[|]";
					}
				}
				update_symposium_meta($user->uid, 'extended', "'".$tmp."'");
			}
		}	

	    // See if the user has posted profile settings
	    	if( isset($_POST[ 'symposium_update' ]) && $_POST[ 'symposium_update' ] == 'symposium_plugin_profile' ) {
	        if (isset($_POST[ 'online' ])) 					{ $online = $_POST[ 'online' ]; } 									else { $online = ''; }
        	if (isset($_POST[ 'offline' ])) 				{ $offline = $_POST[ 'offline' ]; } 								else { $offline = ''; }
        	if (isset($_POST[ 'use_poke' ])) 				{ $use_poke = $_POST[ 'use_poke' ]; } 								else { $use_poke = ''; }
        	if (isset($_POST[ 'poke_label' ])) 				{ $poke_label = $_POST[ 'poke_label' ]; } 							else { $poke_label = ''; }
	    	if (isset($_POST[ 'enable_password' ])) 		{ $enable_password = $_POST['enable_password']; } 					else { $enable_password = ''; }
	    	if (isset($_POST[ 'show_wall_extras' ])) 		{ $show_wall_extras = $_POST['show_wall_extras']; } 				else { $show_wall_extras = ''; }
	    	if (isset($_POST[ 'profile_google_map' ])) 		{ $profile_google_map = $_POST['profile_google_map']; } 			else { $profile_google_map = ''; }
	    	if (isset($_POST[ 'show_dob' ])) 				{ $show_dob = $_POST['show_dob']; } 								else { $show_dob = ''; }
	    	if (isset($_POST[ 'profile_avatars' ])) 		{ $profile_avatars = $_POST['profile_avatars']; } 					else { $profile_avatars = ''; }
	    	if (isset($_POST[ 'initial_friend' ])) 			{ $initial_friend = $_POST['initial_friend']; } 					else { $initial_friend = ''; }
	    	if (isset($_POST[ 'redirect_wp_profile' ])) 	{ $redirect_wp_profile = $_POST['redirect_wp_profile']; } 			else { $redirect_wp_profile = ''; }
	    	if (isset($_POST[ 'menu_my_activity' ])) 		{ $menu_my_activity = $_POST['menu_my_activity']; } 				else { $menu_my_activity = ''; }
	    	if (isset($_POST[ 'menu_friends_activity' ])) 	{ $menu_friends_activity = $_POST['menu_friends_activity']; } 		else { $menu_friends_activity = ''; }
	    	if (isset($_POST[ 'menu_all_activity' ])) 		{ $menu_all_activity = $_POST['menu_all_activity']; } 				else { $menu_all_activity = ''; }
	    	if (isset($_POST[ 'menu_profile' ])) 			{ $menu_profile = $_POST['menu_profile']; } 						else { $menu_profile = ''; }
	    	if (isset($_POST[ 'menu_friends' ])) 			{ $menu_friends = $_POST['menu_friends']; } 						else { $menu_friends = ''; }
	    	if (isset($_POST[ 'menu_texthtml' ])) 			{ $menu_texthtml = $_POST['menu_texthtml']; } 						else { $menu_texthtml = ''; }		    
	    	if (isset($_POST[ 'profile_show_unchecked' ])) 	{ $profile_show_unchecked = $_POST['profile_show_unchecked']; } 	else { $profile_show_unchecked = ''; }		    

		    
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix."symposium_config SET online = '".$online."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix."symposium_config SET offline = '".$offline."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix."symposium_config SET use_poke = '".$use_poke."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix."symposium_config SET poke_label = '".$poke_label."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix."symposium_config SET enable_password = '".$enable_password."'") );
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix."symposium_config SET show_wall_extras = '".$show_wall_extras."'") );
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix."symposium_config SET profile_google_map = '".$profile_google_map."'") );
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix."symposium_config SET show_dob = '".$show_dob."'") );
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix."symposium_config SET profile_avatars = '".$profile_avatars."'") );
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix."symposium_config SET initial_friend = '".$initial_friend."'") );
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix."symposium_config SET redirect_wp_profile = '".$redirect_wp_profile."'") );
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix."symposium_config SET menu_my_activity = '".$menu_my_activity."'") );
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix."symposium_config SET menu_friends_activity = '".$menu_friends_activity."'") );
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix."symposium_config SET menu_all_activity = '".$menu_all_activity."'") );
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix."symposium_config SET menu_profile = '".$menu_profile."'") );
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix."symposium_config SET menu_friends = '".$menu_friends."'") );
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix."symposium_config SET menu_texthtml = '".$menu_texthtml."'") );
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix."symposium_config SET profile_show_unchecked = '".$profile_show_unchecked."'") );

			// Update extended fields
	   		if (isset($_POST['eid']) && $_POST['eid'] != '') {
		   		$range = array_keys($_POST['eid']);
				foreach ($range as $key) {
				    $eid = $_POST['eid'][$key];
				    $name = $_POST['name'][$key];
				    $order = $_POST['order'][$key];
				    $type = $_POST['type'][$key];
				    $default = $_POST['default'][$key];
				    
					$wpdb->query( $wpdb->prepare( "
						UPDATE ".$wpdb->prefix.'symposium_extended'."
						SET extended_name = %s, extended_order = %s, extended_type = %s, extended_default = %s
						WHERE eid = %d", 
				        $name, $order, $type, $default, $eid  ) );
				}		
			}
			
			if ($_POST['new_name'] != '' && $_POST['new_name'] != 'New name') {
				$wpdb->query( $wpdb->prepare( "
					INSERT INTO ".$wpdb->prefix.'symposium_extended'."
					( 	extended_name, 
						extended_order,
						extended_type,
						extended_default
					)
					VALUES ( %s, %d, %s, %s )", 
			        array(
			        	$_POST['new_name'], 
			        	$_POST['new_order'],
			        	$_POST['new_type'],
			        	$_POST['new_default']
			        	) 
			        ) );			        
			}
			
	        // Put an settings updated message on the screen
			echo "<div class='updated slideaway'><p>".__('Saved', 'wp-symposium').".</p></div>";
			
	    }


		$config = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix.'symposium_config'));					

				echo '<div class="metabox-holder"><div id="toc" class="postbox">';

				    // Get values from database  
					$online = $config->online;
					$offline = $config->offline;
					$use_poke = $config->use_poke;
					$poke_label = $config->poke_label;
					$initial_friend = $config->initial_friend;
					$profile_avatars = $config->profile_avatars;
					$enable_password = $config->enable_password;
					$show_wall_extras = $config->show_wall_extras;
					$profile_google_map = $config->profile_google_map;
					$show_dob = $config->show_dob;
					$redirect_wp_profile = $config->redirect_wp_profile;
					$menu_my_activity = $config->menu_my_activity;
					$menu_friends_activity = $config->menu_friends_activity;
					$menu_all_activity = $config->menu_all_activity;
					$menu_profile = $config->menu_profile;
					$menu_friends = $config->menu_friends;
					$menu_texthtml = $config->menu_texthtml;
					$profile_show_unchecked = $config->profile_show_unchecked;
					?>
						
					<form method="post" action=""> 
					<input type="hidden" name="symposium_update" value="symposium_plugin_profile">
				
					<table class="form-table"> 

					<tr valign="top"> 
					<td scope="row"><label for="redirect_wp_profile"><?php _e('Redirect profile page', 'wp-symposium'); ?></label></td>
					<td>
					<input type="checkbox" name="redirect_wp_profile" id="redirect_wp_profile" <?php if ($redirect_wp_profile == "on") { echo "CHECKED"; } ?>/>
					<span class="description"><?php echo __('If activated, WordPress profile page will be redirected to WPS profile page', 'wp-symposium'); ?></span></td> 
					</tr> 
				
					<tr valign="top"> 
					<td scope="row"><label for="initial_friend"><?php _e('Default Friend', 'wp-symposium'); ?></label></td> 
					<td><input name="initial_friend" type="text" id="initial_friend"  value="<?php echo $initial_friend; ?>" /> 
					<span class="description"><?php echo __('Comma separated list of user ID\'s that automatically become friends of new users (leave blank for no-one)', 'wp-symposium'); ?></td> 
					</tr> 

					<tr valign="top"> 
					<td scope="row"><label for="profile_avatars"><?php _e('Profile Photos', 'wp-symposium'); ?></label></td>
					<td>
					<input type="checkbox" name="profile_avatars" id="profile_avatars" <?php if ($profile_avatars == "on") { echo "CHECKED"; } ?>/>
					<span class="description"><?php echo __('Allow members to upload their own profile photos, over-riding the internal WordPress avatars', 'wp-symposium'); ?></span></td> 
					</tr> 

					<tr valign="top"> 
					<td scope="row"><label for="menu_profile"><?php _e('Profile Menu Items', 'wp-symposium'); ?></label></td>
					<td>
					<input type="checkbox" name="menu_profile" id="menu_profile" <?php if ($menu_profile == "on") { echo "CHECKED"; } ?>/>
					<span class="description"><?php echo __('Profile', 'wp-symposium'); ?></span></td> 
					</tr> 

					<tr valign="top"> 
					<td scope="row"></td>
					<td>
					<input type="checkbox" name="menu_my_activity" id="menu_my_activity" <?php if ($menu_my_activity == "on") { echo "CHECKED"; } ?>/>
					<span class="description"><?php echo __('My Activity', 'wp-symposium'); ?></span></td> 
					</tr> 

					<tr valign="top"> 
					<td scope="row"></td>
					<td>
					<input type="checkbox" name="menu_friends_activity" id="menu_friends_activity" <?php if ($menu_friends_activity == "on") { echo "CHECKED"; } ?>/>
					<span class="description"><?php echo __('Friends Activity', 'wp-symposium'); ?></span></td> 
					</tr> 

					<tr valign="top"> 
					<td scope="row"></td>
					<td>
					<input type="checkbox" name="menu_all_activity" id="menu_all_activity" <?php if ($menu_all_activity == "on") { echo "CHECKED"; } ?>/>
					<span class="description"><?php echo __('All Activity', 'wp-symposium'); ?></span></td> 
					</tr> 

					<tr valign="top"> 
					<td scope="row"></td>
					<td>
					<input type="checkbox" name="menu_friends" id="menu_friends" <?php if ($menu_friends == "on") { echo "CHECKED"; } ?>/>
					<span class="description"><?php echo __('Friends', 'wp-symposium'); ?></span></td> 
					</tr> 

					<tr valign="top"> 
					<td scope="row"><label for="menu_texthtml"><?php _e('Profile Menu Text/HTML', 'wp-symposium'); ?></label></td>
					<td>
					<textarea name="menu_texthtml" id="menu_texthtml" rows="4" cols="80" style="float:left"><?php echo $menu_texthtml; ?></textarea>
					<span class="description"><?php echo __('Text/HTML that appears at the end of the profile menu', 'wp-symposium'); ?></span></td> 
					</tr> 

					<tr valign="top"> 
					<td scope="row"><label for="use_poke"><?php _e('Poke/Nudge/Wink/etc', 'wp-symposium'); ?></label></td>
					<td>
					<input type="checkbox" name="use_poke" id="use_poke" <?php if ($use_poke == "on") { echo "CHECKED"; } ?>/>
					<span class="description"><?php echo __('Enable this feature', 'wp-symposium'); ?></span></td> 
					</tr> 

					<tr valign="top"> 
					<td scope="row"><label for="poke_label"><?php _e('Poke label', 'wp-symposium'); ?></label></td> 
					<td><input name="poke_label" type="text" id="poke_label"  value="<?php echo $poke_label; ?>" /> 
					<span class="description"><?php echo __('The "poke" button label for your site, beware of trademarked words (includes Poke and Nudge for example)', 'wp-symposium'); ?></td> 
					</tr> 

					<tr valign="top"> 
					<td scope="row"><label for="show_dob"><?php _e('Use Date of Birth', 'wp-symposium'); ?></label></td>
					<td>
					<input type="checkbox" name="show_dob" id="show_dob" <?php if ($show_dob == "on") { echo "CHECKED"; } ?>/>
					<span class="description"><?php echo __('Use date of birth on profile', 'wp-symposium'); ?></span></td> 
					</tr> 
										
					<tr valign="top"> 
					<td scope="row"><label for="show_wall_extras"><?php _e('Profile Info On Wall', 'wp-symposium'); ?></label></td>
					<td>
					<input type="checkbox" name="show_wall_extras" id="show_wall_extras" <?php if ($show_wall_extras == "on") { echo "CHECKED"; } ?>/>
					<span class="description"><?php echo __('Show summary of profile information on wall', 'wp-symposium'); ?></span></td> 
					</tr> 
										
					<tr valign="top"> 
					<td scope="row"><label for="profile_google_map"><?php _e('Google Map', 'wp-symposium'); ?></label></td> 
					<td><input name="profile_google_map" type="text" id="profile_google_map"  value="<?php echo $profile_google_map; ?>" /> 
					<span class="description"><?php echo __('Size of location map, in pixels. eg: 250. Set to 0 to hide.', 'wp-symposium'); ?></td> 
					</tr> 
										
					<tr valign="top"> 
					<td scope="row"><label for="enable_password"><?php _e('Enable Password Change', 'wp-symposium'); ?></label></td>
					<td>
					<input type="checkbox" name="enable_password" id="enable_password" <?php if ($enable_password == "on") { echo "CHECKED"; } ?>/>
					<span class="description"><?php echo __('Allow members to change their password', 'wp-symposium'); ?></span></td> 
					</tr> 

					<tr valign="top"> 
					<td scope="row"><label for="online"><?php _e('Inactivity period', 'wp-symposium'); ?></label></td> 
					<td><input name="online" type="text" id="online"  value="<?php echo $online; ?>" /> 
					<span class="description"><?php echo __('How many minutes before a member is assumed off-line', 'wp-symposium'); ?></td> 
					</tr> 
										
					<tr valign="top"> 
					<td scope="row"><label for="offline">&nbsp;</label></td> 
					<td><input name="offline" type="text" id="offline"  value="<?php echo $offline; ?>" /> 
					<span class="description"><?php echo __('How many minutes before a member is assumed logged out', 'wp-symposium'); ?></td> 
					</tr> 

					<tr valign="top"> 
					<td scope="row"><label for="offline"><?php _e('Extended Fields', 'wp-symposium'); ?></label></td><td>
					<input type="checkbox" name="profile_show_unchecked" id="profile_show_unchecked" <?php if ($profile_show_unchecked == "on") { echo "CHECKED"; } ?>/>
					<span class="description"><?php echo __('Display checkboxes fields that are not selected (on member profile page)', 'wp-symposium'); ?></span><br />
					
					
					<?php
					echo '<br /><table class="widefat">';
					echo '<thead>';
					echo '<tr>';
					echo '<th>Order</th>';
					echo '<th>Name</th>';
					echo '<th>Type</th>';
					echo '<th>Default Value</th>';
					echo '<th style="width:30px">&nbsp;</th>';
					echo '</tr>';
					echo '</thead>';
					echo '<tbody>';
					$extensions = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."symposium_extended ORDER BY extended_order, extended_name"));
					if ($extensions) {
						foreach ($extensions as $extension) {
							echo '<tr>';
								echo '<td>';
								echo '<input type="hidden" name="eid[]" value="'.$extension->eid.'" />';
								echo '<input type="text" name="order[]" value="'.$extension->extended_order.'" />';
								echo '</td>';
								echo '<td>';
								echo '<input type="text" name="name[]" value="'.stripslashes($extension->extended_name).'" />';
								echo '</td>';
								echo '<td>';
								echo '<select name="type[]">';
								echo '<option value="Text"';
									if ($extension->extended_type == 'Text') { echo ' SELECTED'; }
									echo '>Text</option>';
								echo '<option value="Checkbox"';
									if ($extension->extended_type == 'Checkbox') { echo ' SELECTED'; }
									echo '>Checkbox</option>';
								echo '<option value="List"';
									if ($extension->extended_type == 'List') { echo ' SELECTED'; }
									echo '>List</option>';
								echo '<option value="Textarea"';
									if ($extension->extended_type == 'Textarea') { echo ' SELECTED'; }
									echo '>Text Area</option>';
								echo '</select>';
								echo '</td>';
								echo '<td>';
								echo '<input type="text" name="default[]" value="'.stripslashes($extension->extended_default).'" />';
								echo '</td>';
								echo '<td>';
								echo "<a href='admin.php?page=symposium_profile&view=profile&del_eid=".$extension->eid."' class='delete'>".__('Delete', 'wp-symposium')."</a>";
								echo '</td>';
							echo '</tr>';
						}
					}
					echo '<tr>';
						echo '<td><p>New extended field:</p>';
						echo '<input type="text" name="new_order" onclick="javascript:this.value = \'\'" value="0" />';
						echo '</td>';
						echo '<td><p>&nbsp;</p>';
						echo '<input type="text" name="new_name" onclick="javascript:this.value = \'\'" value="New name" />';
						echo '</td>';
						echo '<td><p>&nbsp;</p>';
						echo '<select name="new_type">';
						echo '<option value="Text" SELECTED>Text</option>';
						echo '<option value="Checkbox">Checkbox</option>';
						echo '<option value="Textarea">Textarea</option>';
						echo '<option value="List">List</option>';
						echo '</select>';
						echo '</td>';
						echo '<td><p>&nbsp;</p>';
						echo '<input type="text" name="new_default" onclick="javascript:this.value = \'\'" value="" />';
						echo '</td>';
						echo '<td>&nbsp;';
						echo '</td>';
					echo '</tr>';
					echo '<tr><td colspan="4"><span class="description">For lists, enter all the values separated by commas - the first value is the default choice.';
					echo '<br />For checkboxes, enter a value of \'on\' to default to checked.';
					echo '<br />Members extended field values are not shown when they are left empty.';
					echo '</tbody>';
					echo '</thead>';
					echo '</table>';

					echo '</td></tr>';										
					echo '</table>';
					 					
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
  	echo '<h2>'.__('Setting', 'wp-symposium').'</h2><br />';

	global $wpdb;

	    // See if the user has posted general settings
	    if( isset($_POST[ 'symposium_update' ]) && $_POST[ 'symposium_update' ] == 'symposium_plugin_settings' ) {
	        $footer = $_POST[ 'email_footer' ];
	        $from_email = $_POST[ 'from_email' ];
	        $jquery = $_POST[ 'jquery' ];
	        $jqueryui = $_POST[ 'jqueryui' ];
	        $emoticons = $_POST[ 'emoticons' ];
	        $wp_width = str_replace('%', 'pc', ($_POST[ 'wp_width' ]));
	        $wp_alignment = $_POST[ 'wp_alignment' ];
	        if (isset($_POST[ 'img_db' ])) { $img_db = $_POST[ 'img_db' ]; } else { $img_db = ''; }
	        $img_path = $_POST[ 'img_path' ];
	        $img_url = $_POST[ 'img_url' ];
	        $img_crop = $_POST[ 'img_crop' ];
	        if (isset($_POST[ 'show_buttons' ])) { $show_buttons = $_POST[ 'show_buttons' ]; } else { $show_buttons = ''; }
	        if (isset($_POST[ 'striptags' ])) { $striptags = $_POST[ 'striptags' ]; } else { $striptags = ''; }
	        $image_ext = $_POST[ 'image_ext' ];
	        $video_ext = $_POST[ 'video_ext' ];
	        $doc_ext = $_POST[ 'doc_ext' ];
	        if (isset($_POST[ 'elastic' ])) { $elastic = $_POST[ 'elastic' ]; } else { $elastic = ''; }
	        $images = $_POST[ 'images' ];

			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET footer = '".$footer."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET from_email = '".$from_email."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET jquery = '".$jquery."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET jqueryui = '".$jqueryui."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET emoticons = '".$emoticons."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET wp_width = '".$wp_width."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET wp_alignment = '".$wp_alignment."'") );				
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET img_db = '".$img_db."'") );				
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET img_path = '".$img_path."'") );				
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET img_url = '".$img_url."'") );				
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET img_crop = '".$img_crop."'") );		
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET show_buttons = '".$show_buttons."'") );			
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET striptags = '".$striptags."'") );			
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET image_ext = '".$image_ext."'") );			
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET video_ext = '".$video_ext."'") );			
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET doc_ext = '".$doc_ext."'") );			
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET elastic = '".$elastic."'") );			
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET images = '".$images."'") );			
			
			echo "<div class='updated slideaway'>";
			
			// Making content path if it doesn't exist
			if ($img_db != 'on') {
				
				if (!file_exists($img_path)) {
					if (!mkdir($img_path, 0777, true)) {
					    echo '<p>Failed to create '.$img_path.'...</p>';
					} else {
						echo '<p>Created '.$img_path.'.</p>';
					}
				}

			}
			
	        // Put an settings updated message on the screen
			echo "<p>".__('Saved', 'wp-symposium').".</p></div>";
			
	    }

		$config = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix.'symposium_config'));

				echo '<div class="metabox-holder"><div id="toc" class="postbox">';


				    // Get values from database  
					$wp_width = str_replace('pc', '%', $config->wp_width);
					$footer = $config->footer;
					$from_email = $config->from_email;
					$jquery = $config->jquery;
					$jqueryui = $config->jqueryui;
					$emoticons = $config->emoticons;	
					$wp_alignment = $config->wp_alignment;
					$img_db = $config->img_db;
					$img_path = $config->img_path;
					$img_url = $config->img_url;
					$img_crop = $config->img_crop;
					$img_tmp = ini_get('upload_tmp_dir');
					$show_buttons = $config->show_buttons;
					$striptags = $config->striptags;
					$image_ext = $config->image_ext;
					$video_ext = $config->video_ext;
					$doc_ext = $config->doc_ext;
					$elastic = $config->elastic;
					$images = $config->images;
					
					?>
									
					<form method="post" action=""> 
					<input type="hidden" name="symposium_update" value="symposium_plugin_settings">

					<table class="form-table"> 

					<tr valign="top"> 
					<td scope="row"><label for="img_db">Store uploads in database</label></td>
					<td>
					<input type="checkbox" name="img_db" id="img_db" <?php if ($img_db == "on") { echo "CHECKED"; } ?>/>
					<span class="description"><?php echo __("Off by default to save to the file system. Select to upload to database - <span style='font-weight:bold; text-decoration: underline'>if you change, images will have to be reloaded, they remain in their storage 'state'.</span>", 'wp-symposium'); ?></span></td> 
					</tr> 
					
					<?php if ($img_db != "on") { ?>
						
						<tr valign="top" style='background-color: #ccc;'> 
						<td scope="row"><label for="img_path">Images directory</label></td> 
						<td><input name="img_path" type="text" id="img_path"  value="<?php echo $img_path; ?>" class="regular-text" /> 
						<span class="description">
						<?php echo __('Path to images directory, eg:', 'wp-symposium').' '.WP_CONTENT_DIR.'/wps-content'; ?>
						</td> 
						</tr> 					

						<tr valign="top" style='background-color: #ccc;'> 
						<td colspan=2>
							From PHP.INI on your server, the PHP temporary upload folder is: <?php echo $img_tmp; ?>
							<?php if ($img_tmp == '') { echo " <strong>You need to <a href='http://uk.php.net/manual/en/ini.core.php#ini.upload-tmp-dir'>set this in your php.ini</a> file</strong>"; } ?>
						</td>
						</tr> 	

						<tr valign="top" style='background-color: #ccc;'> 
						<td scope="row"><label for="img_url">Images URL</label></td> 
						<td><input name="img_url" type="text" id="img_url"  value="<?php echo $img_url; ?>" class="regular-text" /> 
						<?php $url = WP_CONTENT_URL.'/wps-content'; $url = str_replace(siteURL(), '', $url); ?>
						<span class="description"><?php echo __('URL to the images folder, eg: ', 'wp-symposium').$url; ?></td> 
						</tr> 					

					<?php } else { ?>

						<input name="img_path" type="hidden" id="img_path"  value="<?php echo $img_path; ?>" /> 
						<input name="img_url" type="hidden" id="img_url"  value="<?php echo $img_url; ?>" /> 
						
					<?php } ?>

					<tr valign="top"> 
					<td scope="row"><label for="images">WPS images URL</label></td> 
					<td><input name="images" type="text" id="images" class="regular-text" value="<?php echo $images; ?>"/> 
					<span class="description"><?php echo __('Change if you want to create your own set of custom images.', 'wp-symposium'); ?></span></td> 
					</tr> 
						
					<tr valign="top"> 
					<td scope="row"><label for="img_crop">Crop avatar images</label></td>
					<td>
					<input type="checkbox" name="img_crop" id="img_crop" <?php if ($img_crop == "on") { echo "CHECKED"; } ?>/>
					<span class="description"><?php echo __("Allow uploaded images to be cropped</span>", 'wp-symposium'); ?></span></td> 
					</tr> 

					<tr valign="top"> 
					<td scope="row"><label for="image_ext">Image extensions</label></td> 
					<td><input name="image_ext" type="text" id="image_ext" class="regular-text" value="<?php echo $image_ext; ?>"/> 
					<span class="description"><?php echo __('A comma separated list of permitted file extensions, leave blank for none. *.jpg,*.jpeg,*.png and *.gif supported.', 'wp-symposium'); ?></span></td> 
					</tr> 

					<?php if ($img_db != "on") { ?>

						<tr valign="top"> 
						<td scope="row"><label for="video_ext">Video extensions</label></td> 
						<td><input name="video_ext" type="text" id="video_ext" class="regular-text" value="<?php echo $video_ext; ?>"/> 
						<span class="description"><?php echo sprintf(__('A comma separated list of permitted file extensions, leave blank for none. H.264 format supported, <a %s>see here</a>.', 'wp-symposium'), 'href="http://www.longtailvideo.com/support/jw-player/jw-player-for-flash-v5/12539/supported-video-and-audio-formats" target="_blank"'); ?></span></td> 
						</tr> 
	
						<tr valign="top"> 
						<td scope="row"><label for="doc_ext">Document extensions</label></td> 
						<td><input name="doc_ext" type="text" id="doc_ext" class="regular-text" value="<?php echo $doc_ext; ?>"/> 
						<span class="description"><?php echo __('A comma separated list of permitted file extensions, leave blank for none. Viewed in separate window or downloaded.', 'wp-symposium'); ?></span></td> 
						</tr> 
						
					<?php } else { ?>

						<tr valign="top"> 
						<td scope="row"><label for="video_ext">Video extensions</label></td> 
						<td><input name="video_ext" type="hidden" id="video_ext" value="<?php echo $video_ext; ?>"/> 
						<span class="description"><?php echo __('Sorry, videos can only be saved when storing to the filesystem.', 'wp-symposium'); ?></span></td> 
						</tr> 
	
						<tr valign="top"> 
						<td scope="row"><label for="doc_ext">Document extensions</label></td> 
						<td><input name="doc_ext" type="hidden" id="doc_ext" value="<?php echo $doc_ext; ?>"/> 
						<span class="description"><?php echo __('Sorry, documents can only be saved when storing to the filesystem.', 'wp-symposium'); ?></span></td> 
						</tr> 

					<?php } ?>
					
					<tr valign="top"> 
					<td scope="row"><label for="email_footer">Email Notifications</label></td> 
					<td><input name="email_footer" type="text" id="email_footer"  value="<?php echo $footer; ?>" class="regular-text" /> 
					<span class="description"><?php echo __('Footer appended to notification emails', 'wp-symposium'); ?></span></td> 
					</tr> 

					<tr valign="top"> 
					<td scope="row"><label for="from_email">&nbsp;</label></td> 
					<td><input name="from_email" type="text" id="from_email"  value="<?php echo $from_email; ?>" class="regular-text" /> 
					<span class="description"><?php echo __('Email address used for email notifications', 'wp-symposium'); ?></span></td> 
					</tr> 
												
					<tr valign="top"> 
					<td scope="row"><label for="wp_width">Width</label></td> 
					<td><input name="wp_width" type="text" id="wp_width" value="<?php echo $wp_width; ?>"/> 
					<span class="description"><?php echo __('Width of all WP Symposium plugins, eg: 600px or 100%', 'wp-symposium'); ?></span></td> 
					</tr> 

					<tr valign="top">
					<td scope="row"><label for="wp_alignment">Alignment</label></td> 
					<td>
					<select name="wp_alignment">
						<option value='Left'<?php if ($wp_alignment == 'Left') { echo ' SELECTED'; } ?>>Left</option>
						<option value='Center'<?php if ($wp_alignment == 'Center') { echo ' SELECTED'; } ?>>Center</option>
						<option value='Right'<?php if ($wp_alignment == 'Right') { echo ' SELECTED'; } ?>>Right</option>
					</select> 
					<span class="description"><?php echo __('Alignment of all WP Symposium plugins', 'wp-symposium'); ?></span></td> 
					</tr> 		

					<tr valign="top"> 
					<td scope="row"><label for="show_buttons">Buttons on Activity pages</label></td>
					<td>
					<input type="checkbox" name="show_buttons" id="show_buttons" <?php if ($show_buttons == "on") { echo "CHECKED"; } ?>/>
					<span class="description"><?php echo __("Pressing return submits a post/comment, select this option to also show submit buttons.</span>", 'wp-symposium'); ?></span></td> 
					</tr>

					<tr valign="top"> 
					<td scope="row"><label for="striptags">Strip tags</label></td>
					<td>
					<input type="checkbox" name="striptags" id="striptags" <?php if ($striptags == "on") { echo "CHECKED"; } ?>/>
					<span class="description"><?php echo __("Completely remove HTML/script tags. If unchecked &lt; and &gt; will be replaced with &amp;lt; and &amp;gt;.</span>", 'wp-symposium'); ?></span></td> 
					</tr>
										
					<tr valign="top"> 
					<td colspan="2"><hr /><p><?php echo __('The following can be disabled if clashes with other WordPress plugins are occuring', 'wp-symposium'); ?>:</p></td>
					</tr> 

					<tr valign="top"> 
					<td scope="row"><label for="jquery">Load jQuery</label></td>
					<td>
					<input type="checkbox" name="jquery" id="jquery" <?php if ($jquery == "on") { echo "CHECKED"; } ?>/>
					<span class="description"><?php echo __('Load jQuery on non-admin pages, disable if causing problems', 'wp-symposium'); ?></span></td> 
					</tr> 
				
					<tr valign="top"> 
					<td scope="row"><label for="jqueryui">Load jQuery UI</label></td>
					<td>
					<input type="checkbox" name="jqueryui" id="jqueryui" <?php if ($jqueryui == "on") { echo "CHECKED"; } ?>/>
					<span class="description"><?php echo __('Load jQuery UI on non-admin pages, disable if causing problems', 'wp-symposium'); ?></span></td> 
					</tr> 
				
					<tr valign="top"> 
					<td scope="row"><label for="emoticons">Smilies/Emoticons</label></td>
					<td>
					<input type="checkbox" name="emoticons" id="emoticons" <?php if ($emoticons == "on") { echo "CHECKED"; } ?>/>
					<span class="description"><?php echo __('Automatically replace smilies/emoticons with graphical images', 'wp-symposium'); ?></span></td> 
					</tr> 		
															
					<tr valign="top"> 
					<td scope="row"><label for="elastic">Elastic Textboxes</label></td>
					<td>
					<input type="checkbox" name="elastic" id="elastic" <?php if ($elastic == "on") { echo "CHECKED"; } ?>/>
					<span class="description"><?php echo __('Include jQuery elastic function (automatically expand textboxes)', 'wp-symposium'); ?></span></td> 
					</tr> 		
															
					</table>
					 
					<p class="submit" style="margin-left:6px"> 
					<input type="submit" name="Submit" class="button-primary" value="<?php echo __('Save Changes', 'wp-symposium'); ?>" /> 
					</p> 
					</form> 
					
					<?php
				echo '</div></div>';
	echo '</div>';					  
}

function symposium_plugin_forum() {

  	echo '<div class="wrap">';
  	echo '<div id="icon-themes" class="icon32"><br /></div>';
  	echo '<h2>'.__('Forum Options', 'wp-symposium').'</h2><br />';

	global $wpdb;

	    // See if the user has posted forum settings
	    if( isset($_POST[ 'symposium_update' ]) && $_POST[ 'symposium_update' ] == 'symposium_plugin_forum' ) {
	    	    	        
// yyy
	        $send_summary = (isset($_POST[ 'send_summary' ])) ? $_POST[ 'send_summary' ] : '';
	        $include_admin = (isset($_POST[ 'include_admin' ])) ? $_POST[ 'include_admin' ] : '';
	        $oldest_first = (isset($_POST[ 'oldest_first' ])) ? $_POST[ 'oldest_first' ] : '';
	        $use_votes = (isset($_POST[ 'use_votes' ])) ? $_POST[ 'use_votes' ] : '';
	        $use_votes_remove = $_POST[ 'use_votes_remove' ];
	        $preview1 = $_POST[ 'preview1' ];
	        $preview2 = $_POST[ 'preview2' ];
	        $viewer = $_POST[ 'viewer' ];
	        $forum_editor = $_POST[ 'forum_editor' ];
        	$chatroom_banned = $_POST[ 'chatroom_banned' ];
    		$closed_word = $_POST[ 'closed_word' ];
	        $fontfamily = (isset($_POST[ 'fontfamily' ])) ? $_POST[ 'fontfamily' ] : '';
	        $moderation = (isset($_POST[ 'moderation' ])) ? $_POST[ 'moderation' ] : '';
	        $bump_topics = (isset($_POST[ 'bump_topics' ])) ? $_POST[ 'bump_topics' ] : '';
	        $forum_ajax = (isset($_POST[ 'forum_ajax' ])) ? $_POST[ 'forum_ajax' ] : '';
	        $forum_login = (isset($_POST[ 'forum_login' ])) ? $_POST[ 'forum_login' ] : '';
	        $forum_ranks = (isset($_POST[ 'forum_ranks' ])) ? $_POST[ 'forum_ranks' ].';' : '';
			for ( $rank = 1; $rank <= 11; $rank ++) {
				$forum_ranks .= $_POST['rank'.$rank].";";
				$forum_ranks .= $_POST['score'.$rank].";";
			}
	
			$sharing_permalink = (isset($_POST[ 'sharing_permalink' ])) ? "pl;" : ""; 
			$sharing_facebook = (isset($_POST[ 'sharing_facebook' ])) ? "fb;" : ""; 
			$sharing_twitter = (isset($_POST[ 'sharing_twitter' ])) ? "tw;" : ""; 
			$sharing_myspace = (isset($_POST[ 'sharing_myspace' ])) ? "ms;" : ""; 
			$sharing_bebo = (isset($_POST[ 'sharing_bebo' ])) ? "be;" : ""; 
			$sharing_linkedin = (isset($_POST[ 'sharing_linkedin' ])) ? "li;" : ""; 
			$sharing_email = (isset($_POST[ 'sharing_email' ])) ? "em;" : ""; 
	        $sharing = $sharing_permalink.$sharing_facebook.$sharing_twitter.$sharing_myspace.$sharing_bebo.$sharing_linkedin.$sharing_email;
	
	        $symposium_forumlatestposts_count = $_POST[ 'symposium_forumlatestposts_count' ];	
	        $forum_uploads = (isset($_POST[ 'forum_uploads' ])) ? $_POST[ 'forum_uploads' ] : '';	
	        $forum_thumbs = (isset($_POST[ 'forum_thumbs' ])) ? $_POST[ 'forum_thumbs' ] : '';	
	        $forum_thumbs_size = $_POST[ 'forum_thumbs_size' ];	
	        $forum_info = (isset($_POST[ 'forum_info' ])) ? $_POST[ 'forum_info' ] : '';	
	        $use_votes_min = $_POST[ 'use_votes_min' ];	
	        $use_answers = (isset($_POST[ 'use_answers' ])) ? $_POST[ 'use_answers' ] : '';	

			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET send_summary = '".$send_summary."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET include_admin = '".$include_admin."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET oldest_first = '".$oldest_first."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET use_votes = '".$use_votes."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET use_votes_remove = '".$use_votes_remove."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET preview1 = ".$preview1) );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET preview2 = ".$preview2) );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET viewer = '".$viewer."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET forum_editor = '".$forum_editor."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET chatroom_banned = '".$chatroom_banned."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET closed_word = '".$closed_word."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET bump_topics = '".$bump_topics."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET forum_ajax = '".$forum_ajax."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET forum_login = '".$forum_login."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET moderation = '".$moderation."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET sharing = '".$sharing."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET forum_ranks = '".$forum_ranks."'") );		
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET symposium_forumlatestposts_count = '".$symposium_forumlatestposts_count."'") );		
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET forum_uploads = '".$forum_uploads."'") );		
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET forum_thumbs = '".$forum_thumbs."'") );		
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET forum_thumbs_size = '".$forum_thumbs_size."'") );		
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET forum_info = '".$forum_info."'") );		
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET use_votes_min = '".$use_votes_min."'") );		
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET use_answers = '".$use_answers."'") );		
						

	        // Put an settings updated message on the screen
			echo "<div class='updated slideaway'><p>".__('Saved', 'wp-symposium').".</p></div>";

	    }

		$config = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix.'symposium_config'));

		$send_summary = $config->send_summary;
		$include_admin = $config->include_admin;
		$use_votes = $config->use_votes;
		$use_votes_remove = $config->use_votes_remove;
		$preview1 = $config->preview1;
		$preview2 = $config->preview2;
		$viewer = $config->viewer;
		$forum_editor = $config->forum_editor;
		$chatroom_banned = $config->chatroom_banned;
		$closed_word = $config->closed_word;
		$moderation = $config->moderation;
		$forum_ajax = $config->forum_ajax;
		$forum_login = $config->forum_login;
		$forum_ranks = $config->forum_ranks;
		$sharing = $config->sharing;
		$bump_topics = $config-> bump_topics;
		if ( strpos($sharing, "pl") === FALSE ) { $sharing_permalink = ''; } else { $sharing_permalink = 'on'; }
		if ( strpos($sharing, "fb") === FALSE ) { $sharing_facebook = ''; } else { $sharing_facebook = 'on'; }
		if ( strpos($sharing, "tw") === FALSE ) { $sharing_twitter = ''; } else { $sharing_twitter = 'on'; }
		if ( strpos($sharing, "ms") === FALSE ) { $sharing_myspace = ''; } else { $sharing_myspace = 'on'; }
		if ( strpos($sharing, "li") === FALSE ) { $sharing_linkedin = ''; } else { $sharing_linkedin = 'on'; }
		if ( strpos($sharing, "be") === FALSE ) { $sharing_bebo = ''; } else { $sharing_bebo = 'on'; }
		if ( strpos($sharing, "em") === FALSE ) { $sharing_email = ''; } else { $sharing_email = 'on'; }
		$symposium_forumlatestposts_count = $config->symposium_forumlatestposts_count;
		$forum_uploads = $config->forum_uploads;
		$forum_thumbs = $config->forum_thumbs;
		$forum_thumbs_size = $config->forum_thumbs_size;
		$oldest_first = $config->oldest_first;
		$forum_info = $config->forum_info;
		$use_votes_min = $config->use_votes_min;
		$use_answers = $config->use_answers;
		?>

		<div class="metabox-holder"><div id="toc" class="postbox"> 
			
			<form method="post" action=""> 
			<input type="hidden" name="symposium_update" value="symposium_plugin_forum">
	
			<table class="form-table"> 
		
			<tr valign="top"> 
			<td scope="row"><label for="forum_login"><?php _e('Login Link', 'wp-symposium'); ?></label></td>
			<td>
			<input type="checkbox" name="forum_login" id="forum_login" <?php if ($forum_login == "on") { echo "CHECKED"; } ?>/>
			<span class="description"><?php echo __('Show login link on forum when not logged in?', 'wp-symposium'); ?></span></td> 
			</tr> 

			<tr valign="top"> 
			<td scope="row"><label for="forum_ajax"><?php _e('Use AJAX', 'wp-symposium'); ?></label></td>
			<td>
			<input type="checkbox" name="forum_ajax" id="forum_ajax" <?php if ($forum_ajax == "on") { echo "CHECKED"; } ?>/>
			<span class="description"><?php echo __('Use AJAX, or hyperlinks and page re-loading?', 'wp-symposium'); ?></span></td> 
			</tr> 

			<tr valign="top"> 
			<td scope="row"><label for="moderation"><?php _e('Moderation', 'wp-symposium'); ?></label></td>
			<td>
			<input type="checkbox" name="moderation" id="moderation" <?php if ($moderation == "on") { echo "CHECKED"; } ?>/>
			<span class="description"><?php echo __('New topics and posts require admin approval', 'wp-symposium'); ?></span></td> 
			</tr> 
	
			<tr valign="top"> 
			<td scope="row"><label for="send_summary"><?php _e('Daily Digest', 'wp-symposium'); ?></label></td>
			<td>
			<input type="checkbox" name="send_summary" id="send_summary" <?php if ($send_summary == "on") { echo "CHECKED"; } ?>/>
			<span class="description"><?php echo __('Enable daily summaries to all members via email', 'wp-symposium'); ?></span></td> 
			</tr> 
	
			<tr valign="top"> 
			<td scope="row"><label for="include_admin"><?php _e('Admin views', 'wp-symposium'); ?></label></td>
			<td>
			<input type="checkbox" name="include_admin" id="include_admin" <?php if ($include_admin == "on") { echo "CHECKED"; } ?>/>
			<span class="description"><?php echo __('Include administrator viewing a topic in the total view count', 'wp-symposium'); ?></span></td> 
			</tr> 
	
			<tr valign="top"> 
			<td scope="row"><label for="bump_topics"><?php _e('Bump topics', 'wp-symposium'); ?></label></td>
			<td>
			<input type="checkbox" name="bump_topics" id="bump_topics" <?php if ($bump_topics == "on") { echo "CHECKED"; } ?>/>
			<span class="description"><?php echo __('Bumps topics to top of forum when new replies are posted', 'wp-symposium'); ?></span></td> 
			</tr> 
	
			<tr valign="top"> 
			<td scope="row"><label for="oldest_first"><?php _e('Order of replies', 'wp-symposium'); ?></label></td>
			<td>
			<input type="checkbox" name="oldest_first" id="oldest_first" <?php if ($oldest_first == "on") { echo "CHECKED"; } ?>/>
			<span class="description"><?php echo __('Show oldest replies first (uncheck to reverse order)', 'wp-symposium'); ?></span></td> 
			</tr> 
	
			<tr valign="top"> 
			<td scope="row"><label for="forum_uploads"><?php _e('Allow uploads', 'wp-symposium'); ?></label></td>
			<td>
			<input type="checkbox" name="forum_uploads" id="forum_uploads" <?php if ($forum_uploads == "on") { echo "CHECKED"; } ?>/>
			<span class="description"><?php echo __('Allow members to upload files with forum posts (requires Flash to be installed)', 'wp-symposium'); ?></span></td> 
			</tr> 
	
			<tr valign="top"> 
			<td scope="row"><label for="forum_info"><?php _e('Member Info', 'wp-symposium'); ?></label></td>
			<td>
			<input type="checkbox" name="forum_info" id="forum_info" <?php if ($forum_info == "on") { echo "CHECKED"; } ?>/>
			<span class="description"><?php echo __('Show member info underneath avatar on forum', 'wp-symposium'); ?></span></td> 
			</tr> 
	
			<tr valign="top"> 
			<td scope="row"><label for="forum_thumbs"><?php _e('Inline attachments', 'wp-symposium'); ?></label></td>
			<td>
			<input type="checkbox" name="forum_thumbs" id="forum_thumbs" <?php if ($forum_thumbs == "on") { echo "CHECKED"; } ?>/>
			<span class="description"><?php echo __('Show uploaded forum attachments as images/videos (not links). Documents are always links.', 'wp-symposium'); ?></span></td> 
			</tr> 
	
			<tr valign="top"> 
			<td scope="row"><label for="forum_thumbs_size"><?php _e('Thumbnail size', 'wp-symposium'); ?></label></td>
			<td><input name="forum_thumbs_size" style="width:50px" type="text" id="forum_thumbs_size"  value="<?php echo $forum_thumbs_size; ?>" /> 
			<span class="description"><?php echo __('If using inline attachments, maximum width', 'wp-symposium'); ?></span></td> 
			</tr> 
	
			<tr valign="top"> 
			<td scope="row"><label for="use_votes"><?php _e('Use Votes', 'wp-symposium'); ?></label></td>
			<td>
			<input type="checkbox" name="use_votes" id="use_votes" <?php if ($use_votes == "on") { echo "CHECKED"; } ?>/>
			<span class="description"><?php echo __('Allow members to vote (plus or minus) on forum posts', 'wp-symposium'); ?></span></td> 
			</tr> 

			<tr valign="top"> 
			<td scope="row"><label for="use_votes_min"><?php _e('Votes (minimum posts)', 'wp-symposium'); ?></label></td>
			<td><input name="use_votes_min" style="width:50px" type="text" id="use_votes_min"  value="<?php echo $use_votes_min; ?>" /> 
			<span class="description"><?php echo __('How many posts a member must have made in order to vote', 'wp-symposium'); ?></span></td> 
			</tr> 
	
	
			<tr valign="top"> 
			<td scope="row"><label for="use_votes_remove"><?php _e('Votes (removal point)', 'wp-symposium'); ?></label></td>
			<td><input name="use_votes_remove" style="width:50px" type="text" id="use_votes_remove"  value="<?php echo $use_votes_remove; ?>" /> 
			<span class="description"><?php echo __('When a forum post gets this many votes, it is removed. Can be + or -. Leave as 0 to ignore.', 'wp-symposium'); ?></span></td> 
			</tr> 

			<tr valign="top"> 
			<td scope="row"><label for="use_answers"><?php _e('Votes (answers)', 'wp-symposium'); ?></label></td>
			<td>
			<input type="checkbox" name="use_answers" id="use_answers" <?php if ($use_answers == "on") { echo "CHECKED"; } ?>/>
			<span class="description"><?php echo __('Allows topic owners and administrators to mark a reply as an answer (one per topic)', 'wp-symposium'); ?></span></td> 
			</tr> 
	
			<tr valign="top"> 
			<td scope="row"><label for="preview1"><?php _e('Preview length', 'wp-symposium'); ?></label></td>
			<td><input name="preview1" style="width:50px" type="text" id="preview1"  value="<?php echo $preview1; ?>" /> 
			<span class="description"><?php echo __('Maximum number of characters to show in topic preview', 'wp-symposium'); ?></span></td> 
			</tr> 
	
			<tr valign="top"> 
			<td scope="row"><label for="preview2"></label></td>
			<td><input name="preview2" style="width:50px" type="text" id="preview2"  value="<?php echo $preview2; ?>" /> 
			<span class="description"><?php echo __('Maximum number of characters to show in reply preview', 'wp-symposium'); ?></span></td> 
			</tr> 
	
			<tr valign="top"> 
			<td scope="row"><label for="viewer"><?php _e('View forum level', 'wp-symposium'); ?></label></td> 
			<td>
			<select name="viewer">
				<option value='Guest'<?php if ($viewer == 'Guest') { echo ' SELECTED'; } ?>><?php _e('Guest', 'wp-symposium'); ?></option>
				<option value='Subscriber'<?php if ($viewer == 'Subscriber') { echo ' SELECTED'; } ?>><?php _e('Subscriber', 'wp-symposium'); ?></option>
				<option value='Contributor'<?php if ($viewer == 'Contributor') { echo ' SELECTED'; } ?>><?php _e('Contributor', 'wp-symposium'); ?></option>
				<option value='Editor'<?php if ($viewer == 'Editor') { echo ' SELECTED'; } ?>><?php _e('Editor', 'wp-symposium'); ?></option>
				<option value='Administrator'<?php if ($viewer == 'Administrator') { echo ' SELECTED'; } ?>><?php _e('Administrator', 'wp-symposium'); ?></option>
			</select> 
			<span class="description"><?php echo __('The minimum level a visitor has to be to view the forum', 'wp-symposium'); ?></span></td> 
			</tr> 
	
			<tr valign="top"> 
			<td scope="row"><label for="forum_editor"><?php _e('Forum post level', 'wp-symposium'); ?></label></td> 
			<td>
			<select name="forum_editor">
				<option value='Guest'<?php if ($forum_editor == 'Guest') { echo ' SELECTED'; } ?>><?php _e('Guest', 'wp-symposium'); ?></option>
				<option value='Subscriber'<?php if ($forum_editor == 'Subscriber') { echo ' SELECTED'; } ?>><?php _e('Subscriber', 'wp-symposium'); ?></option>
				<option value='Contributor'<?php if ($forum_editor == 'Contributor') { echo ' SELECTED'; } ?>><?php _e('Contributor', 'wp-symposium'); ?></option>
				<option value='Editor'<?php if ($forum_editor == 'Editor') { echo ' SELECTED'; } ?>><?php _e('Editor', 'wp-symposium'); ?></option>
				<option value='Administrator'<?php if ($forum_editor == 'Administrator') { echo ' SELECTED'; } ?>><?php _e('Administrator', 'wp-symposium'); ?></option>
			</select> 
			<span class="description"><?php echo __('The minimum level a visitor has to be to be able to post/reply on the forum', 'wp-symposium'); ?></span></td> 
			</tr> 
	
			<tr valign="top"> 
			<td scope="row"><label for="chatroom_banned">Banned forum words</label></td> 
			<td><input name="chatroom_banned" type="text" id="chatroom_banned"  value="<?php echo $chatroom_banned; ?>" /> 
			<span class="description"><?php echo __('Comma separated list of words not allowed in the forum', 'wp-symposium'); ?></td> 
			</tr> 
									
			<tr valign="top"> 
			<td scope="row"><label for="closed_word"><?php _e('Closed word', 'wp-symposium'); ?></label></td>
			<td><input name="closed_word" type="text" id="closed_word"  value="<?php echo $closed_word; ?>" /> 
			<span class="description"><?php echo __('Word used to denote a topic that is closed (see also Styles)', 'wp-symposium'); ?></span></td> 
			</tr> 

			<tr valign="top"> 
			<td scope="row"><label for="sharing_permalink"><?php _e('Sharing icons included', 'wp-symposium'); ?></label></td>
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
			$ranks = explode(';', $forum_ranks);
			?>
			<tr valign="top"> 
			<td scope="row"><label for="forum_ranks"><?php _e('Forum ranks', 'wp-symposium'); ?></label></td>
			<td>
			<input type="checkbox" name="forum_ranks" id="forum_ranks" <?php if ($ranks[0] == "on") { echo "CHECKED"; } ?>/>
			<span class="description"><?php echo __('Use ranks on the forum?', 'wp-symposium'); ?></span></td> 
			</tr>

			<?php
			for ( $rank = 1; $rank <= 11; $rank ++) {
				echo '<tr valign="top">';
					if ($rank == 1) { 

						echo '<td scope="row">';
							echo 'Title and Posts Required';
						echo '</td>';

					} else {

						echo '<td scope="row"><label for="closed_word">';
						
							if ($rank == 11) {
								echo '<em>'.__('(blank ranks are not used)', 'wp-symposium').'</em>';
							} else {
								echo "&nbsp;";
							}
						
						echo '</label></td>';

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
							echo __('Rank'.' '.($rank-1), 'wp-symposium'); 							
						}
						?></span>
					</td> 
				</tr>
			<?php
			}
			?>

			<tr valign="top"> 
			<td colspan=2>
				<p>
				<span class="description">
				<strong>Notes</strong>
				<ul style='margin-left:6px'>
				<li>&middot;&nbsp;<?php _e('Daily summaries (if there is anything to send) are sent when the first visitor comes to the site after midnight, local time.', 'wp-symposium'); ?></li>
				<li>&middot;&nbsp;<?php _e('Be aware of any limits set by your hosting provider for sending out bulk emails, they may suspend your website.', 'wp-symposium'); ?></li>
				</ul>
				</p>
			<hr />
				<strong><?php _e('Shortcode options', 'wp-symposium'); ?></td> 
			</tr> 

			<tr valign="top"> 
			<td scope="row"><label for="symposium_forumlatestposts_count"><?php _e('[symposium-forumlatestposts]', 'wp-symposium'); ?></label></td>
			<td><input name="symposium_forumlatestposts_count" style="width:50px" type="text" id="symposium_forumlatestposts_count"  value="<?php echo $symposium_forumlatestposts_count; ?>" /> 
			<span class="description"><?php echo __('Default number of topics to show, can be changed overridden, eg: [symposium-forumlatestposts count=10]', 'wp-symposium'); ?></span></td> 
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
				SET title = %s, cat_parent = %d, listorder = %s, allow_new = %s, cat_desc = %s, defaultcat = %s
				WHERE cid = %d", 
		        $title, $cat_parent, $listorder, $allow_new, $cat_desc, $defaultcat, $cid  ) );
		        			
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
	<th style="text-align:center"><?php echo __('Topics', 'wp-symposium'); ?></th>
	<th><?php echo __('Order', 'wp-symposium'); ?></th>
	<th><?php echo __('Allow new topics', 'wp-symposium'); ?></th>
	<th>&nbsp;</th>
	</tr> 
	</thead>
	
	<?php	
	$included = show_forum_children(0, 0, '');
	
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
					</tr> 
					</thead>
					<?php
				}
				echo '<tr valign="top">';
				echo '<input name="cid[]" type="hidden" value="'.$category->cid.'" />';
				echo '<td>'.str_repeat("...", $indent).'&nbsp;'.$category->cid.'</td>';
				echo '<td><input name="cat_parent[]" type="text" value="'.stripslashes($category->cat_parent).'" style="width:50px" /></td>';
				echo '<td>';
				echo '<input name="title[]" type="text" value="'.stripslashes($category->title).'" class="regular-text" /><br />';
				echo '<input name="cat_desc[]" type="text" value="'.stripslashes($category->cat_desc).'" class="regular-text" />';
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
				echo '<a class="delete" href="?page=symposium_categories&action=delcid&all=1&cid='.$category->cid.'">'.__('Delete all', 'wp-symposium').'</a>';
				echo '</td>';
				echo '</tr>';
				
			}
		}
	}
	
	?>
	
	<thead>
	<tr>
	<th style="width:20px"></th>
	<th style="width:60px"><?php echo __('Parent ID', 'wp-symposium'); ?></th>
	<th><?php echo __('Add New Category', 'wp-symposium'); ?></th>
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
		<input name="new_title" type="text" onclick="javascript:this.value = ''" value="<?php echo __('Add New Category', 'wp-symposium'); ?>..." class="regular-text" /><br />
		<input name="new_cat_desc" type="text" onclick="javascript:this.value = ''" value="<?php echo __('Optional Description', 'wp-symposium'); ?>..." class="regular-text" />
	</td>
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
	echo '<li>'.__('if you "Delete category" any topics will be placed at the top level of the forum.', 'wp-symposium').'</li>';
	echo '<li>'.__('choose "Delete all" to delete a category and all topics in that category.', 'wp-symposium').'</li>';
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
	
	$categories = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."symposium_cats WHERE cat_parent = ".$id." ORDER BY listorder");

	if ($categories) {
		foreach ($categories as $category) {
			
			$list = $list.$category->cid.",";
			
			echo '<tr valign="top">';
			echo '<input name="cid[]" type="hidden" value="'.$category->cid.'" />';
			echo '<td>'.str_repeat("...", $indent).'&nbsp;'.$category->cid.'</td>';
			echo '<td><input name="cat_parent[]" type="text" value="'.stripslashes($category->cat_parent).'" style="width:50px" /></td>';
			echo '<td>';
			echo '<input name="title[]" type="text" value="'.stripslashes($category->title).'" class="regular-text" /><br />';
			echo '<input name="cat_desc[]" type="text" value="'.stripslashes($category->cat_desc).'" class="regular-text" />';
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
			echo '<a class="delete" href="?page=symposium_categories&action=delcid&all=1&cid='.$category->cid.'">'.__('Delete all', 'wp-symposium').'</a>';
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
	$config = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix.'symposium_config'));

  	echo '<div class="wrap">';
  		echo '<div id="icon-themes" class="icon32"><br /></div>';
	  	echo '<h2>'.__('Styles', 'wp-symposium').'</h2>';

	    // See if the user has saved CSS
	    if( isset($_POST[ 'symposium_update' ]) && $_POST[ 'symposium_update' ] == 'CSS' ) {
			$css = str_replace(chr(13), "[]", $_POST['css']);
			$wpdb->query( $wpdb->prepare( "UPDATE ".$wpdb->prefix."symposium_config SET css = %s", $css ) );
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
				$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET use_styles = 'on'") );					
				$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET categories_background = '".$style->categories_background."'") );					
				$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET categories_color = '".$style->categories_color."'") );					
				$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET border_radius = '".$style->border_radius."'") );					
				$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET main_background = '".$style->main_background."'") );					
				$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET bigbutton_background = '".$style->bigbutton_background."'") );					
				$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET bigbutton_background_hover = '".$style->bigbutton_background_hover."'") );					
				$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET bigbutton_color = '".$style->bigbutton_color."'") );					
				$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET bigbutton_color_hover = '".$style->bigbutton_color_hover."'") );					
				$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET bg_color_1 = '".$style->bg_color_1."'") );					
				$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET bg_color_2 = '".$style->bg_color_2."'") );					
				$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET bg_color_3 = '".$style->bg_color_3."'") );					
				$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET row_border_style = '".$style->row_border_style."'") );					
				$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET row_border_size = ".$style->row_border_size) );					
				$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET replies_border_size = ".$style->replies_border_size) );					
				$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET table_rollover = '".$style->table_rollover."'") );					
				$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET table_border = ".$style->table_border) );					
				$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET text_color = '".$style->text_color."'") );					
				$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET text_color_2 = '".$style->text_color_2."'") );					
				$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET link = '".$style->link."'") );					
				$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET underline = '".$style->underline."'") );					
				$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET link_hover = '".$style->link_hover."'") );					
				$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET label = '".$style->label."'") );
				$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET fontfamily = '".$style->fontfamily."'") );
				$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET fontsize = '".$style->fontsize."'") );
				$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET headingsfamily = '".$style->headingsfamily."'") );
				$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET headingssize = '".$style->headingssize."'") );

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

	        // Read their posted value
			if (isset($_POST[ 'use_styles' ])) {
		        $use_styles = $_POST[ 'use_styles' ];			
			} else {
		        $use_styles = '';						
			}
	        $categories_background = $_POST[ 'categories_background' ];
	        $categories_color = $_POST[ 'categories_color' ];
	        $border_radius = $_POST[ 'border_radius' ];
	        $bigbutton_background = $_POST[ 'bigbutton_background' ];
	        $bigbutton_background_hover = $_POST[ 'bigbutton_background_hover' ];
	        $bigbutton_color = $_POST[ 'bigbutton_color' ];
	        $bigbutton_color_hover = $_POST[ 'bigbutton_color_hover' ];
	        $bg_color_1 = $_POST[ 'bg_color_1' ];
	        $bg_color_2 = $_POST[ 'bg_color_2' ];
	        $bg_color_3 = $_POST[ 'bg_color_3' ];
	        $row_border_style = $_POST[ 'row_border_style' ];
	        $row_border_size = $_POST[ 'row_border_size' ];
	        $table_rollover = $_POST[ 'table_rollover' ];
	        $table_border = $_POST[ 'table_border' ];
	        $replies_border_size = $_POST[ 'replies_border_size' ];
	        $text_color = $_POST[ 'text_color' ];
	        $text_color_2 = $_POST[ 'text_color_2' ];
	        $link = $_POST[ 'link' ];
	        $underline = $_POST[ 'underline' ];
	        $link_hover = $_POST[ 'link_hover' ];
	        $label = $_POST[ 'label' ];
	        $closed_opacity = $_POST[ 'closed_opacity' ];
	        $fontfamily = $_POST[ 'fontfamily' ];
	        $fontsize = str_replace("px", "", strtolower($_POST[ 'fontsize' ]));
	        $headingsfamily = $_POST[ 'headingsfamily' ];
	        $headingssize = str_replace("px", "", strtolower($_POST[ 'headingssize' ]));
	        $main_background = $_POST[ 'main_background' ];

	        // Save the posted value in the database
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET use_styles = '".$use_styles."'") );				
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET categories_background = '".$categories_background."'") );				
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET categories_color = '".$categories_color."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET border_radius = '".$border_radius."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET bigbutton_background = '".$bigbutton_background."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET bigbutton_background_hover = '".$bigbutton_background_hover."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET bigbutton_color = '".$bigbutton_color."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET bigbutton_color_hover = '".$bigbutton_color_hover."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET bg_color_1 = '".$bg_color_1."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET bg_color_2 = '".$bg_color_2."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET bg_color_3 = '".$bg_color_3."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET row_border_style = '".$row_border_style."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET row_border_size = ".$row_border_size) );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET table_rollover = '".$table_rollover."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET table_border = ".$table_border) );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET replies_border_size = '".$replies_border_size."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET text_color = '".$text_color."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET text_color_2 = '".$text_color_2."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET link = '".$link."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET underline = '".$underline."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET link_hover = '".$link_hover."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET label = '".$label."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET closed_opacity = '".$closed_opacity."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET fontfamily = '".$fontfamily."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET fontsize = '".$fontsize."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET headingsfamily = '".$headingsfamily."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET headingssize = '".$headingssize."'") );					
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET main_background = '".$main_background."'") );					

		    if( $_POST[ 'style_save_as' ] != '' ) {

				// Delete previous version if it exists
				$wpdb->query( $wpdb->prepare( "DELETE FROM ".$wpdb->prefix."symposium_styles WHERE title = %s", $_POST['style_save_as'] ) );

				// Save new template
			   	$rows_affected = $wpdb->insert( $wpdb->prefix."symposium_styles", array( 
				'title' => $_POST['style_save_as'], 
				'border_radius' => $border_radius,
				'bigbutton_background' => $bigbutton_background, 
				'bigbutton_background_hover' => $bigbutton_background_hover,
				'bigbutton_color' => $bigbutton_color, 
				'bigbutton_color_hover' => $bigbutton_color_hover, 
				'bg_color_1' => $bg_color_1, 
				'bg_color_2' => $bg_color_2,
				'bg_color_3' => $bg_color_3, 
				'table_rollover' => $table_rollover, 
				'table_border' => $table_border, 
				'row_border_style' => $row_border_style, 
				'row_border_size' => $row_border_size, 
				'replies_border_size' => $replies_border_size, 
				'categories_background' => $categories_background, 
				'categories_color' => $categories_color, 
				'text_color' => $text_color, 
				'text_color_2' => $text_color_2, 
				'link' => $link, 
				'underline' => $underline, 
				'link_hover' => $link_hover, 
				'label' => $label,
				'main_background' => $main_background,
				'closed_opacity' => $closed_opacity,
				'fontfamily' => $fontfamily,
				'fontsize' => $fontsize,
				'headingsfamily' => $headingsfamily,
				'headingssize' => $headingssize
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

					$css = $wpdb->get_var($wpdb->prepare("SELECT css FROM ".$wpdb->prefix.'symposium_config'));
				    $css = str_replace("[]", chr(13), stripslashes($css));

					echo '<form method="post" action=""> ';
					echo '<input type="hidden" name="symposium_update" value="CSS">';

					echo '<table class="widefat">';
					echo '<thead>';
					echo '<tr>';
					echo '<th style="font-size:1.2em">'.__('CSS', 'wp-symposium').'<input type="submit" class="button-primary" style="float:right; padding:2px 6px 2px 6px;" value="Save"></th>';
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
					echo 'To speed things up, why not open a new window and refresh it each time you save a change here?';
					echo '</td></tr>';
					echo '<tr><td>';
					echo 'CSS will over-ride the WP Symposium Styles (other tab), but your theme may take priority.';
					echo '</td></tr>';
					echo '<tr><td>';
					echo 'If a style doesn\'t apply, try putting !important after it. eg: color:red !important;';
					echo '</td></tr>';
					echo '<tr><td>';
					echo 'Refer to www.wpswiki.com for more help and examples.';
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
		    
					$style = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix.'symposium_config');

					if ($style) {
			
						?> 

						<form method="post" action=""> 
						<input type="hidden" name="symposium_update" value="Y">
	
						<table class="form-table"> 

						<tr valign="top"> 
						<td scope="row"><label for="use_styles"><?php echo __('Use Styles?', 'wp-symposium'); ?></label></td>
						<td>
						<input type="checkbox" name="use_styles" id="use_styles" <?php if ($style->use_styles == "on") { echo "CHECKED"; } ?>/>
						<span class="description"><?php echo __('Enable to use styles on this page, disable to rely on stylesheet', 'wp-symposium'); ?></span></td> 
						</tr> 
		
						<tr valign="top"> 
						<td scope="row"><label for="fontfamily">Body Text</label></td> 
						<td><input name="fontfamily" type="text" id="fontfamily" value="<?php echo $style->fontfamily; ?>"/> 
						<span class="description"><?php echo __('Font family for body text', 'wp-symposium'); ?></span></td> 
						</tr> 
	
						<tr valign="top"> 
						<td scope="row"><label for="fontsize"></label></td> 
						<td><input name="fontsize" type="text" id="fontsize" value="<?php echo $style->fontsize; ?>"/> 
						<span class="description"><?php echo __('Font size in pixels for body text', 'wp-symposium'); ?></span></td> 
						</tr> 
	
						<tr valign="top"> 
						<td scope="row"><label for="headingsfamily">Headings</label></td> 
						<td><input name="headingsfamily" type="text" id="headingsfamily" value="<?php echo $style->headingsfamily; ?>"/> 
						<span class="description"><?php echo __('Font family for headings and large text', 'wp-symposium'); ?></span></td> 
						</tr> 
	
						<tr valign="top"> 
						<td scope="row"><label for="headingssize"></label></td> 
						<td><input name="headingssize" type="text" id="headingssize" value="<?php echo $style->headingssize; ?>"/> 
						<span class="description"><?php echo __('Font size in pixels for headings and large text', 'wp-symposium'); ?></span></td> 
						</tr> 
	
						<tr valign="top"> 
						<td scope="row"><label for="main_background">Main background</label></td> 
						<td><input name="main_background" type="text" id="main_background" class="iColorPicker" value="<?php echo $style->main_background; ?>"  /> 
						<span class="description"><?php echo __('Main background colour (for example, new/edit forum topic/post)', 'wp-symposium'); ?></span></td> 
						</tr> 

						<tr valign="top"> 
						<td scope="row"><label for="label">Labels</label></td> 
						<td><input name="label" type="text" id="label" class="iColorPicker" value="<?php echo $style->label; ?>"  /> 
						<span class="description"><?php echo __('Colour of text labels outside forum areas', 'wp-symposium'); ?></span></td> 
						</tr> 
		
						<tr valign="top"> 
						<td scope="row"><label for="text_color">Text Colour</label></td> 
						<td><input name="text_color" type="text" id="text_color" class="iColorPicker" value="<?php echo $style->text_color; ?>"  /> 
						<span class="description"><?php echo __('Primary Text Colour', 'wp-symposium'); ?></span></td> 
						</tr> 
	
						<tr valign="top"> 
						<td scope="row"><label for="text_color_2"></label></td> 
						<td><input name="text_color_2" type="text" id="text_color_2" class="iColorPicker" value="<?php echo $style->text_color_2; ?>"  /> 
						<span class="description"><?php echo __('Secondary Text Colour', 'wp-symposium'); ?></span></td> 
						</tr> 

						<tr valign="top"> 
						<td scope="row"><label for="link">Links</label></td> 
						<td><input name="link" type="text" id="link" class="iColorPicker" value="<?php echo $style->link; ?>"  /> 
						<span class="description"><?php echo __('Link Colour', 'wp-symposium'); ?></span></td> 
						</tr> 
	
						<tr valign="top"> 
						<td scope="row"><label for="link_hover"</label></td> 
						<td><input name="link_hover" type="text" id="link_hover" class="iColorPicker" value="<?php echo $style->link_hover; ?>"  /> 
						<span class="description"><?php echo __('Link Colour on mouse hover', 'wp-symposium'); ?></span></td> 
						</tr> 

						<tr valign="top"> 
						<td scope="row"><label for="underline">Underlined?</label></td> 
						<td>
						<select name="underline" id="underline"> 
							<option <?php if ( $style->underline=='') { echo "selected='selected'"; } ?> value=''>No</option> 
							<option <?php if ( $style->underline=='on') { echo "selected='selected'"; } ?> value='on'>Yes</option> 
						</select> 
						<span class="description"><?php echo __('Whether links are underlined or not', 'wp-symposium'); ?></span></td> 
						</tr> 
				
						<tr valign="top"> 
						<td scope="row"><label for="border_radius">Corners</label></td> 
						<td>
						<select name="border_radius" id="border_radius"> 
							<option <?php if ( $style->border_radius=='0') { echo "selected='selected'"; } ?> value='0'>0 pixels</option> 
							<option <?php if ( $style->border_radius=='1') { echo "selected='selected'"; } ?> value='1'>1 pixels</option> 
							<option <?php if ( $style->border_radius=='2') { echo "selected='selected'"; } ?> value='2'>2 pixels</option> 
							<option <?php if ( $style->border_radius=='3') { echo "selected='selected'"; } ?> value='3'>3 pixels</option> 
							<option <?php if ( $style->border_radius=='4') { echo "selected='selected'"; } ?> value='4'>4 pixels</option> 
							<option <?php if ( $style->border_radius=='5') { echo "selected='selected'"; } ?> value='5'>5 pixels</option> 
							<option <?php if ( $style->border_radius=='6') { echo "selected='selected'"; } ?> value='6'>6 pixels</option> 
							<option <?php if ( $style->border_radius=='7') { echo "selected='selected'"; } ?> value='7'>7 pixels</option> 
							<option <?php if ( $style->border_radius=='8') { echo "selected='selected'"; } ?> value='8'>8 pixels</option> 
							<option <?php if ( $style->border_radius=='9') { echo "selected='selected'"; } ?> value='9'>9 pixels</option> 
							<option <?php if ( $style->border_radius=='10') { echo "selected='selected'"; } ?> value='10'>10 pixels</option> 
							<option <?php if ( $style->border_radius=='11') { echo "selected='selected'"; } ?> value='11'>11 pixels</option> 
							<option <?php if ( $style->border_radius=='12') { echo "selected='selected'"; } ?> value='12'>12 pixels</option> 
							<option <?php if ( $style->border_radius=='13') { echo "selected='selected'"; } ?> value='13'>13 pixels</option> 
							<option <?php if ( $style->border_radius=='14') { echo "selected='selected'"; } ?> value='14'>14 pixels</option> 
							<option <?php if ( $style->border_radius=='15') { echo "selected='selected'"; } ?> value='15'>15 pixels</option> 
						</select> 
						<span class="description"><?php echo __('Rounded Corner radius (not supported in all browsers)', 'wp-symposium'); ?></span></td> 
						</tr> 
	
						<tr valign="top"> 
						<td scope="row"><label for="bigbutton_background">Buttons</label></td> 
						<td><input name="bigbutton_background" type="text" id="bigbutton_background" class="iColorPicker" value="<?php echo $style->bigbutton_background; ?>"  /> 
						<span class="description"><?php echo __('Background Colour', 'wp-symposium'); ?></span></td> 
						</tr> 
	
						<tr valign="top"> 
						<td scope="row"><label for="bigbutton_background_hover"></label></td> 
						<td><input name="bigbutton_background_hover" type="text" id="bigbutton_background_hover" class="iColorPicker" value="<?php echo $style->bigbutton_background_hover; ?>"  /> 
						<span class="description"><?php echo __('Background Colour on mouse hover', 'wp-symposium'); ?></span></td> 
						</tr> 
	
						<tr valign="top"> 
						<td scope="row"><label for="bigbutton_color"></label></td> 
						<td><input name="bigbutton_color" type="text" id="bigbutton_color" class="iColorPicker" value="<?php echo $style->bigbutton_color; ?>"  /> 
						<span class="description"><?php echo __('Text Colour', 'wp-symposium'); ?></span></td> 
						</tr> 
	
						<tr valign="top"> 
						<td scope="row"><label for="bigbutton_color_hover"></label></td> 
						<td><input name="bigbutton_color_hover" type="text" id="bigbutton_color_hover" class="iColorPicker" value="<?php echo $style->bigbutton_color_hover; ?>"  /> 
						<span class="description"><?php echo __('Text Colour on mouse hover', 'wp-symposium'); ?></span></td> 
						</tr> 
	
						<tr valign="top"> 
						<td scope="row"><label for="bg_color_1">Tables</label></td> 
						<td><input name="bg_color_1" type="text" id="bg_color_1" class="iColorPicker" value="<?php echo $style->bg_color_1; ?>"  /> 
						<span class="description"><?php echo __('Primary Colour', 'wp-symposium'); ?></span></td> 
						</tr> 
	
						<tr valign="top"> 
						<td scope="row"><label for="bg_color_2"></label></td> 
						<td><input name="bg_color_2" type="text" id="bg_color_2" class="iColorPicker" value="<?php echo $style->bg_color_2; ?>"  /> 
						<span class="description"><?php echo __('Row Colour', 'wp-symposium'); ?></span></td> 
						</tr> 
	
						<tr valign="top"> 
						<td scope="row"><label for="bg_color_3"></label></td> 
						<td><input name="bg_color_3" type="text" id="bg_color_3" class="iColorPicker" value="<?php echo $style->bg_color_3; ?>"  /> 
						<span class="description"><?php echo __('Alternative Row Colour', 'wp-symposium'); ?></span></td> 
						</tr> 

						<tr valign="top"> 
						<td scope="row"><label for="table_rollover"></label></td> 
						<td><input name="table_rollover" type="text" id="table_rollover" class="iColorPicker" value="<?php echo $style->table_rollover; ?>"  /> 
						<span class="description"><?php echo __('Row colour on mouse hover', 'wp-symposium'); ?></span></td> 
						</tr> 
			
						<tr valign="top"> 
						<td scope="row"><label for="table_border"></label></td> 
						<td>
						<select name="table_border" id="table_border"> 
							<option <?php if ( $style->table_border=='0') { echo "selected='selected'"; } ?> value='0'>0 pixels</option> 
							<option <?php if ( $style->table_border=='1') { echo "selected='selected'"; } ?> value='1'>1 pixels</option> 
							<option <?php if ( $style->table_border=='2') { echo "selected='selected'"; } ?> value='2'>2 pixels</option> 
							<option <?php if ( $style->table_border=='3') { echo "selected='selected'"; } ?> value='3'>3 pixels</option> 
						</select> 
						<span class="description"><?php echo __('Border Size', 'wp-symposium'); ?></span></td> 
						</tr> 
	
						<tr valign="top"> 
						<td scope="row"><label for="row_border_style">Table/Rows</label></td> 
						<td>
						<select name="row_border_style" id="row_border_styledefault_role"> 
							<option <?php if ( $style->row_border_style=='dotted') { echo "selected='selected'"; } ?> value='dotted'>Dotted</option> 
							<option <?php if ( $style->row_border_style=='dashed') { echo "selected='selected'"; } ?> value='dashed'>Dashed</option> 
							<option <?php if ( $style->row_border_style=='solid') { echo "selected='selected'"; } ?> value='solid'>Solid</option> 
						</select> 
						<span class="description"><?php echo __('Border style between rows', 'wp-symposium'); ?></span></td> 
						</tr> 
			
						<tr valign="top"> 
						<td scope="row"><label for="row_border_size"></label></td> 
						<td>
						<select name="row_border_size" id="row_border_size"> 
							<option <?php if ( $style->row_border_size=='0') { echo "selected='selected'"; } ?> value='0'>0 pixels</option> 
							<option <?php if ( $style->row_border_size=='1') { echo "selected='selected'"; } ?> value='1'>1 pixels</option> 
							<option <?php if ( $style->row_border_size=='2') { echo "selected='selected'"; } ?> value='2'>2 pixels</option> 
							<option <?php if ( $style->row_border_size=='3') { echo "selected='selected'"; } ?> value='3'>3 pixels</option> 
						</select> 
						<span class="description"><?php echo __('Border size between rows', 'wp-symposium'); ?></span></td> 
						</tr> 
			
						<tr valign="top"> 
						<td scope="row"><label for="replies_border_size">Other borders</label></td> 
						<td>
						<select name="replies_border_size" id="replies_border_size"> 
							<option <?php if ( $style->replies_border_size=='0') { echo "selected='selected'"; } ?> value='0'>0 pixels</option> 
							<option <?php if ( $style->replies_border_size=='1') { echo "selected='selected'"; } ?> value='1'>1 pixels</option> 
							<option <?php if ( $style->replies_border_size=='2') { echo "selected='selected'"; } ?> value='2'>2 pixels</option> 
							<option <?php if ( $style->replies_border_size=='3') { echo "selected='selected'"; } ?> value='3'>3 pixels</option> 
						</select> 
						<span class="description"><?php echo __('For new topics/replies and topic replies', 'wp-symposium'); ?></span></td> 
						</tr> 

						<tr valign="top"> 
						<td scope="row"><label for="categories_background">Miscellaneous</label></td> 
						<td><input name="categories_background" type="text" id="categories_background" class="iColorPicker" value="<?php echo $style->categories_background; ?>"  /> 
						<span class="description"><?php echo __('Background colour of, for example, current category', 'wp-symposium'); ?></span></td> 
						</tr> 
	
						<tr valign="top"> 
						<td scope="row"><label for="categories_color"></label></td> 
						<td><input name="categories_color" type="text" id="categories_color" class="iColorPicker" value="<?php echo $style->categories_color; ?>"  /> 
						<span class="description"><?php echo __('Text Colour', 'wp-symposium'); ?></span></td> 
						</tr> 
	
						<tr valign="top"> 
						<td colspan="2"><h3>Forum Styles</h3></td> 
						</tr> 
		
						<tr valign="top"> 
						<td scope="row"><label for="closed_opacity">Closed topics</label></td> 
						<td><input name="closed_opacity" type="text" id="closed_opacity" class="iColorPicker" value="<?php echo $style->closed_opacity; ?>"  /> 
						<?php
						$closed_word = $config->closed_word;
						?>
						<span class="description"><?php echo __('Opacity of topics', 'wp-symposium'); ?> with [<?php echo $closed_word; ?>] in the subject (between 0.0 and 1.0)</span></td> 
						</tr> 
	
						</table> 
						<br />
		 
						<h2><?php echo __('Style Templates', 'wp-symposium'); ?></h2>
							
						<p><?php echo __('To save as a new style template, enter a name below, otherwise leave blank.', 'wp-symposium'); ?></p>

						<p>
						<?php echo __('Save as:', 'wp-symposium'); ?>
						<input type='text' id='style_save_as' name='style_save_as' value='<?php if (isset($style_save_as)) { echo str_replace("'", "&apos;", stripslashes($style_save_as)); } ?>' />
						<input type="submit" name="Submit" class="button-primary" value="<?php _e('Save', 'wp-symposium') ?>" /> 
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
						NB. If changes don't follow the above, you may be overriding them with the theme stylesheet.
						</p>
		
						<?php	  	
					}
				}

			echo '</div>';
	
	 	echo '</div>'; // End of Styles 

 	echo '</div>'; // End of wrap

} 	

function symposium_mail_menu() {

	global $wpdb, $current_user;

    // See if the user has posted forum settings
    if( isset($_POST[ 'symposium_update' ]) && $_POST[ 'symposium_update' ] == 'symposium_mail_menu' ) {
		$mail_all = (isset($_POST[ 'mail_all' ])) ? $_POST[ 'mail_all' ] : '';
		
		// Update database
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_config'." SET mail_all = '".$mail_all."'") );					

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
					$body .= "<p><a href='".$url.symposium_string_query($url)."mid=".$mail_id."'>".__(sprintf("Go to %s Mail", symposium_get_url('mail')), "wp-symposium")."...</a></p>";
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
	$config = $wpdb->get_row($wpdb->prepare("SELECT mail_all FROM ".$wpdb->prefix.'symposium_config'));

	$mail_all = $config->mail_all;
	
  	echo '<div class="wrap">';
  	
	  	echo '<div id="icon-themes" class="icon32"><br /></div>';
	  	echo '<h2>Mail</h2>';
		?>
		<div class="metabox-holder"><div id="toc" class="postbox"> 
			
			<form method="post" action=""> 
			<input type="hidden" name="symposium_update" value="symposium_mail_menu">
	
			<table class="form-table"> 
		
			<tr valign="top"> 
			<td scope="row"><label for="mail_all"><?php _e('Mail to all', 'wp-symposium'); ?></label></td>
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

		$show_admin = (isset($_POST['show_admin'])) ? $_POST['show_admin'] : '';
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix."symposium_config SET show_admin = '".$show_admin."'") );
		
        // Put an settings updated message on the screen
		echo "<div class='updated slideaway'><p>".__('Saved', 'wp-symposium').".</p></div>";
		
    }

	// Get values to show
	$config = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix.'symposium_config'));
	$show_admin = $config->show_admin;
	
  	echo '<div class="wrap">';
  	
	  	echo '<div id="icon-themes" class="icon32"><br /></div>';
	  	echo '<h2>Member Directory</h2>';
		?>

		<div class="metabox-holder"><div id="toc" class="postbox"> 
		
			<form method="post" action=""> 
			<input type="hidden" name="symposium_update" value="symposium_members_menu">

			<table class="form-table">

			<tr valign="top"> 
			<td scope="row"><label for="show_admin">Include admin in directory?</label></td>
			<td>
			<input type="checkbox" name="show_admin" id="show_admin" <?php if ($show_admin == "on") { echo "CHECKED"; } ?>/>
			<span class="description"><?php echo __('Whether to show site administrators in the member directory', 'wp-symposium'); ?></span></td> 
			</tr> 
	
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
