<?php

include_once('../../../../wp-config.php');
include_once('../../../../wp-includes/wp-db.php');
include_once('../symposium_functions.php');


// Show Wall
if ($_POST['action'] == 'menu_wall') {

	$uid1 = $_POST['uid1'];
	$uid2 = $_POST['uid2'];
	$post = $_POST['post'];

	$html = symposium_smilies(symposium_profile_body($uid1, $uid2, $post, "wall"));
	
	echo $html;
	exit;
	
}

// Show Friends Activity
if ($_POST['action'] == 'menu_activity') {

	$uid1 = $_POST['uid1'];
	$uid2 = $_POST['uid2'];
	$post = $_POST['post'];

	$html = symposium_smilies(symposium_profile_body($uid1, $uid2, $post, "friends_activity"));
	
	echo $html;
	exit;
	
}

// Show All
if ($_POST['action'] == 'menu_all') {

	$uid1 = $_POST['uid1'];
	$uid2 = $_POST['uid2'];
	$post = $_POST['post'];

	$html = symposium_smilies(symposium_profile_body($uid1, $uid2, $post, "all_activity"));
	
	echo $html;
	exit;
	
}

// Show Settings
if ($_POST['action'] == 'menu_settings') {

	global $wpdb, $current_user;
	wp_get_current_user();

	$uid = $_POST['uid1'];

	$plugin = WP_PLUGIN_URL.'/wp-symposium';
	$dbpage = $plugin.'/symposium_profile_db.php';
	$meta = get_symposium_meta_row($uid);					
	$config = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix . 'symposium_config'));

	$allow_personal_settings = $config->allow_personal_settings;
	
	// get values
	if ($allow_personal_settings == "on") {	
		$sound = $meta->sound;
		$soundchat = $meta->soundchat;
		$bar_position = $meta->bar_position;
	}
	
	$timezone = $meta->timezone;
	$notify_new_messages = $meta->notify_new_messages;
	$notify_new_wall = $meta->notify_new_wall;
	
	$html .= "<div id='profile_left_column'>";

		$html .= '<h2>'.__('Preferences', 'wp-symposium').'...</h2>';
	
		$html .= '<form method="post" action="'.$dbpage.'"> ';
			$html .= '<input type="hidden" name="symposium_update" value="U">';
			$html .= '<input type="hidden" name="uid" value="'.$uid.'">';

		
			$html .= '<div id="symposium_settings_table">';
			
				// Time zone adjustment
				$html .= '<div style="clear:both; margin-bottom:15px;">';
					$html .= sprintf (__('Your local time zone adjustment in hours (difference from GMT which is %s).', 'wp-symposium'), date('jS \of M h:i:s A'));
					$html .= '<div style="float:right;">';
						$html .= '<select name="timezone">';
						for ($i = -12; $i <= 14; $i++) {
							$html .= "<option value='".$i."'";
								if ($timezone == $i) { $html .= ' SELECTED'; }
								$html .= '>'.$i.'</option>';
						}
						$html .= '</select>';									
					$html .= '</div>';
				$html .= '</div>';

				if ( function_exists('add_notification_bar') ) {
					
					// Sound alert
					$html .= '<div style="clear:both; margin-bottom:15px;">';
						$html .= __('Notification bar alert that sounds when you get new mail, relevant forum posts, etc', 'wp-symposium');
						$html .= '<div style="float:right;">';
							$html .= '<select name="sound">';
								$html .= "<option value='None'";
									if ($sound == 'None') { $html .= ' SELECTED'; }
									$html .= '>None</option>';
								$html .= "<option value='baby.mp3'";
									if ($sound == 'baby.mp3') { $html .= ' SELECTED'; }
									$html .= '>Baby</option>';
								$html .= "<option value='beep.mp3'";
									if ($sound == 'beep.mp3') { $html .= ' SELECTED'; }
									$html .= '>Beep</option>';
								$html .= "<option value='bell.mp3'";
									if ($sound == 'bell.mp3') { $html .= ' SELECTED'; }
									$html .= '>Bell</option>';
								$html .= "<option value='buzzer.mp3'";
									if ($sound == 'buzzer.mp3') { $html .= ' SELECTED'; }
									$html .= '>Buzzer</option>';
								$html .= "<option value='chime.mp3'";
									if ($sound == 'chime.mp3') { $html .= ' SELECTED'; }
									$html .= '>Chime</option>';
								$html .= "<option value='doublechime.mp3'";
									if ($sound == 'doublechime.mp3') { $html .= ' SELECTED'; }
									$html .= '>Double Chime</option>';
								$html .= "<option value='dudeyougotmail.mp3'";
									if ($sound == 'dudeyougotmail.mp3') { $html .= ' SELECTED'; }
									$html .= '>Dude! You got mail!</option>';
								$html .= "<option value='hacksaw.mp3'";
									if ($sound == 'hacksaw.mp3') { $html .= ' SELECTED'; }
									$html .= '>Hacksaw</option>';
								$html .= "<option value='incoming.mp3'";
									if ($sound == 'incoming.mp3') { $html .= ' SELECTED'; }
									$html .= '>Incoming!</option>';
								$html .= "<option value='tap.mp3'";
									if ($sound == 'tap.mp3') { $html .= ' SELECTED'; }
									$html .= '>Tap</option>';
								$html .= "<option value='youvegotmail.mp3'";
									if ($sound == 'youvegotmail.mp3') { $html .= ' SELECTED'; }
									$html .= ">You've got mail</option>";
							$html .= '</select>';									
						$html .= '</div>';								
					$html .= '</div>';
					
					// Sound alert (for chat)
					$html .= '<div style="clear:both; margin-bottom:15px;">';;
						$html .= __('Notification bar alert that sounds when a new chat message arrives', 'wp-symposium');
						$html .= '<div style="float:right;">';
							$html .= '<select name="soundchat">';
								$html .= "<option value='None'";
									if ($soundchat == 'None') { $html .= ' SELECTED'; }
									$html .= '>None</option>';
								$html .= "<option value='baby.mp3'";
									if ($soundchat == 'baby.mp3') { $html .= ' SELECTED'; }
									$html .= '>Baby</option>';
								$html .= "<option value='beep.mp3'";
									if ($soundchat == 'beep.mp3') { $html .= ' SELECTED'; }
									$html .= '>Beep</option>';
								$html .= "<option value='bell.mp3'";
									if ($soundchat == 'bell.mp3') { $html .= ' SELECTED'; }
									$html .= '>Bell</option>';
								$html .= "<option value='buzzer.mp3'";
									if ($soundchat == 'buzzer.mp3') { $html .= ' SELECTED'; }
									$html .= '>Buzzer</option>';
								$html .= "<option value='chime.mp3'";
									if ($soundchat == 'chime.mp3') { $html .= ' SELECTED'; }
									$html .= '>Chime</option>';
								$html .= "<option value='doublechime.mp3'";
									if ($soundchat == 'doublechime.mp3') { $html .= ' SELECTED'; }
									$html .= '>Double Chime</option>';
								$html .= "<option value='dudeyougotmail.mp3'";
									if ($soundchat == 'dudeyougotmail.mp3') { $html .= ' SELECTED'; }
									$html .= '>Dude! You got mail!</option>';
								$html .= "<option value='hacksaw.mp3'";
									if ($soundchat == 'hacksaw.mp3') { $html .= ' SELECTED'; }
									$html .= '>Hacksaw</option>';
								$html .= "<option value='incoming.mp3'";
									if ($soundchat == 'incoming.mp3') { $html .= ' SELECTED'; }
									$html .= '>Incoming!</option>';
								$html .= "<option value='tap.mp3'";
									if ($soundchat == 'tap.mp3') { $html .= ' SELECTED'; }
									$html .= '>Tap</option>';
								$html .= "<option value='youvegotmail.mp3'";
									if ($soundchat == 'youvegotmail.mp3') { $html .= ' SELECTED'; }
									$html .= ">You've got mail</option>";
							$html .= '</select>';									
						$html .= '</div>';								
					$html .= '</div>';
					
					// Bar position
					$html .= '<div style="clear:both; margin-bottom:15px;">';
						$html .= __('Where do you want the notification bar?', 'wp-symposium');
						$html .= '<div style="float: right;">';
							$html .= '<select name="bar_position">';
								$html .= "<option value='bottom'";
									if ($bar_position == 'bottom') { $html .= ' SELECTED'; }
									$html .= '>Bottom</option>';
								$html .= "<option value='top'";
									if ($bar_position == 'top') { $html .= ' SELECTED'; }
									$html .= '>Top</option>';
							$html .= '</select>';
						$html .= '</div>';
					$html .= '</div>';	
					
				}

				// Display name
				$html .= '<div style="clear:both; margin-bottom:15px;">';
					$html .= __('Your name as shown', 'wp-symposium');
					$html .= '<div style="float:right;">';
						$html .= '<input type="text" class="input-field" name="display_name" value="'.$current_user->display_name.'">';
					$html .= '</div>';
				$html .= '</div>';
				
				// Email address
				$html .= '<div style="clear:both; margin-bottom:15px;">';
					$html .= __('Your email address', 'wp-symposium');
					$html .= '<div style="float:right;">';
						$html .= '<input type="text" class="input-field" name="user_email" style="width:300px" value="'.$current_user->user_email.'">';
					$html .= '</div>';
				$html .= '</div>';
				
				// Email notifications
				$html .= '<div style="clear:both; margin-bottom:15px;">';
					$html .= __('Do you want to receive an email when you get new mail messages?', 'wp-symposium');
					$html .= '<div style="float:right;">';
						$html .= '<input type="checkbox" name="notify_new_messages" id="notify_new_messages"';
							if ($notify_new_messages == "on") { $html .= "CHECKED"; }
							$html .= '/>';
					$html .= '</div>';
				$html .= '</div>';

				// Email wall
				$html .= '<div style="clear:both; margin-bottom:15px;">';
					$html .= __('Do you want to receive an email when a friend adds a new wall post or reply?', 'wp-symposium');
					$html .= '<div style="float:right;">';
						$html .= '<input type="checkbox" name="notify_new_wall" id="notify_new_wall"';
							if ($notify_new_wall == "on") { $html .= "CHECKED"; }
							$html .= '/>';
					$html .= '</div>';
				$html .= '</div>';
															
				// Password
				if ($config->enable_password == "on") {
					$html .= '<div class="sep"></div>';
					$html .= '<div style="clear:both; margin-bottom:15px; padding-top:15px;">';
						$html .= __('Change your password', 'wp-symposium');
						$html .= '<div style="float:right;">';
							$html .= '<input class="input-field" type="text" name="xyz1" value="">';
						$html .= '</div>';
					$html .= '</div>';
					$html .= '<div style="clear:both">';
						$html .= __('Re-enter to confirm', 'wp-symposium');
						$html .= '<div style="float:right;">';
							$html .= '<input class="input-field" type="text" name="xyz2" value="">';
						$html .= '</div>';
					$html .= '</div>';
																
				}
			
			$html .= '</div> ';
			 
			$html .= '<p style="clear: both;" class="submit"> ';
			$html .= '<input type="submit" name="Submit" class="button" value="'.__('Save', 'wp-symposium').'" /> ';
			$html .= '</p> ';
		$html .= '</form> ';
	
	$html .= "</div>";
	
	echo $html;
	exit;
	
}

// Show Personal
if ($_POST['action'] == 'menu_personal') {

	global $wpdb, $current_user;
	wp_get_current_user();

	$uid = $_POST['uid1'];

	$plugin = WP_PLUGIN_URL.'/wp-symposium';
	$dbpage = $plugin.'/symposium_profile_db.php';
	$meta = get_symposium_meta_row($uid);					
	$config = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix . 'symposium_config'));
	
	// get values
	$dob_day = $meta->dob_day;
	$dob_month = $meta->dob_month;
	$dob_year = $meta->dob_year;
	$city = $meta->city;
	$country = $meta->country;
	$share = $meta->share;
	$wall_share = $meta->wall_share;
	$extended = $meta->extended;
	
	$html .= "<div id='profile_left_column'>";

		$html .= '<h2>'.__('Personal', 'wp-symposium').'...</h2>';
	
		$html .= '<form method="post" action="'.$dbpage.'"> ';
			$html .= '<input type="hidden" name="symposium_update" value="P">';
			$html .= '<input type="hidden" name="uid" value="'.$uid.'">';
		
			$html .= '<div id="symposium_settings_table">';
			
				// Sharing personal information
				$html .= '<div style="clear:both; margin-bottom:15px;">';
					$html .= __('Who do you want to share personal information with?', 'wp-symposium');
					$html .= '<div style="float:right;">';
						$html .= '<select name="share">';
							$html .= "<option value='Nobody'";
								if ($share == 'Nobody') { $html .= ' SELECTED'; }
								$html .= '>'.__('Nobody', 'wp-symposium').'</option>';
							$html .= "<option value='Friends only'";
								if ($share == 'Friends only') { $html .= ' SELECTED'; }
								$html .= '>'.__('Friends Only', 'wp-symposium').'</option>';
							$html .= "<option value='Everyone'";
								if ($share == 'Everyone') { $html .= ' SELECTED'; }
								$html .= '>'.__('Everyone', 'wp-symposium').'</option>';
						$html .= '</select>';
					$html .= '</div>';
				$html .= '</div>';
				
				// Sharing wall
				$html .= '<div style="clear:both; margin-bottom:15px;">';
					$html .= __('Who do you want to share your wall with?', 'wp-symposium');
					$html .= '<div style="float:right;">';
						$html .= '<select name="wall_share">';
							$html .= "<option value='Nobody'";
								if ($wall_share == 'Nobody') { $html .= ' SELECTED'; }
								$html .= '>'.__('Nobody', 'wp-symposium').'</option>';
							$html .= "<option value='Friends only'";
								if ($wall_share == 'Friends only') { $html .= ' SELECTED'; }
								$html .= '>'.__('Friends Only', 'wp-symposium').'</option>';
							$html .= "<option value='Everyone'";
								if ($wall_share == 'Everyone') { $html .= ' SELECTED'; }
								$html .= '>'.__('Everyone', 'wp-symposium').'</option>';
						$html .= '</select>';
					$html .= '</div>';
				$html .= '</div>';
				
				// Birthday
				$html .= '<div style="clear:both; margin-bottom:15px;">';
					$html .= __('Your date of birth (day/month/year)', 'wp-symposium');
					$html .= '<div style="float:right;">';
						$html .= "<select name='dob_day'>";
							for ($i = 1; $i <= 31; $i++) {
								$html .= "<option value='".$i."'";
									if ($dob_day == $i) { $html .= ' SELECTED'; }
									$html .= '>'.$i.'</option>';
							}
						$html .= '</select> / ';									
						$html .= "<select name='dob_month'>";
							for ($i = 1; $i <= 12; $i++) {
								$html .= "<option value='".$i."'";
									if ($dob_month == $i) { $html .= ' SELECTED'; }
									$html .= '>'.$i.'</option>';
							}
						$html .= '</select> / ';									
						$html .= "<select name='dob_year'>";
							for ($i = date("Y"); $i >= 1900; $i--) {
								$html .= "<option value='".$i."'";
									if ($dob_year == $i) { $html .= ' SELECTED'; }
									$html .= '>'.$i.'</option>';
							}
						$html .= '</select>';									
					$html .= '</div>';
				$html .= '</div>';
					
				// City
				$html .= '<div style="clear:both; margin-bottom:15px;">';
					$html .= __('Which town/city are you in?', 'wp-symposium');
					$html .= '<div style="float:right;">';
						$html .= '<input type="text" name="city" value="'.$city.'">';
					$html .= '</div>';
				$html .= '</div>';
					
				// Country
				$html .= '<div style="clear:both; margin-bottom:15px;">';
					$html .= __('Which country are you in?', 'wp-symposium');
					$html .= '<div style="float:right;">';
						$html .= '<input type="text" name="country" value="'.$country.'">';
					$html .= '</div>';
				$html .= '</div>';
				
				// Extensions
				$extensions = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."symposium_extended ORDER BY extended_order, extended_name"));
				$fields = explode('[|]', $extended);
				if ($extensions) {
					foreach ($extensions as $extension) {
						$value = $extension->extended_default;
						if ($extension->extended_type == "List") {
							$tmp = explode(',', $extension->extended_default);
							$value = $tmp[0];

						}
						foreach ($fields as $field) {
							$split = explode('[]', $field);
							if ($split[0] == $extension->extended_name) { 
								$value = $split[1];
							 }
						}
						
						$html .= '<div style="clear:both; margin-bottom:15px;">';
							$html .= $extension->extended_name;
							$html .= '<input type="hidden" name="eid[]" value="'.$extension->eid.'">';
							$html .= '<input type="hidden" name="extended_name[]" value="'.$extension->extended_name.'">';
							$html .= '<div style="float:right;">';
								if ($extension->extended_type == 'Text') {
									$html .= '<input type="text" name="extended_value[]" value="'.$value.'">';
								}
								if ($extension->extended_type == 'List') {
									$html .= '<select name="extended_value[]">';
									$items = explode(',', $extension->extended_default);
									foreach ($items as $item) {
										$html .= '<option value="'.$item.'"';
											if ($value == $item) { $html .= " SELECTED"; }
											$html .= '>'.$item.'</option>';
									}												
									$html .= '</select>';
								}
							$html .= '</div>';
						$html .= '</div>';
					}
				}

					
			$html .= '</div> ';
			 
			$html .= '<p style="clear: both" class="submit"> ';
				$html .= '<input type="submit" name="Submit" class="button" value="'.__('Save', 'wp-symposium').'" /> ';
			$html .= '</p> ';
		$html .= '</form> ';
	
	$html .= "</div>";
		
	echo $html;
	exit;
	
}

// Show Friends
if ($_POST['action'] == 'menu_friends') {

	$uid1 = $_POST['uid1'];

	$html = symposium_profile_friends($uid1);
	
	echo $html;
	exit;
	
}
						
						
// AJAX function to send new password
if ($_POST['action'] == 'deletePost') {

	global $wpdb, $current_user;
	wp_get_current_user();

	$cid = $_POST['cid'];
	$uid = $_POST['uid'];

	if (is_user_logged_in()) {
	
		if ( symposium_safe_param($cid) && symposium_safe_param($uid) ) {
			
			if ( symposium_get_current_userlevel($uid) == 5 ) {
				$sql = "DELETE FROM ".$wpdb->prefix."symposium_comments WHERE cid = ".$cid;
			} else {
				$sql = "DELETE FROM ".$wpdb->prefix."symposium_comments WHERE cid = ".$cid." AND (subject_uid = ".$uid." OR author_uid = ".$uid.")";
			}
			$rows_affected = $wpdb->query( $wpdb->prepare($sql) );
			if ( $rows_affected > 0 ) {
	
				// Delete any replies
				$sql = "DELETE FROM ".$wpdb->prefix."symposium_comments WHERE comment_parent = ".$cid.")";
				$rows_affected = $wpdb->query( $wpdb->prepare($sql) );
	
				echo "#".$cid;
			} else {
				echo "FAILED TO DELETE ".$wpdb->last_query;
			}
			
		} else {
			echo "FAIL, INVALID PARAMETERS (".$uid.":".$cid.")";
		}
	} else {
		echo "FAIL, NOT LOGGED IN";
	}

	exit;
}

// AJAX function to add comment
if ($_POST['action'] == 'addComment') {

	global $wpdb, $current_user;
	wp_get_current_user();

	$uid = $_POST['uid'];
	$text = $_POST['text'];
	$parent = $_POST['parent'];

	if (is_user_logged_in()) {

		if ( ($text != __(addslashes("Write a comment..."), "wp-symposium")) && ($text != '') ) {
	
			$wpdb->query( $wpdb->prepare( "
				INSERT INTO ".$wpdb->prefix."symposium_comments
				( 	subject_uid, 
					author_uid,
					comment_parent,
					comment_timestamp,
					comment
				)
				VALUES ( %d, %d, %d, %s, %s )", 
		        array(
		        	$uid, 
		        	$current_user->ID, 
		        	$parent,
		        	date("Y-m-d H:i:s"),
		        	$text
		        	) 
		        ) );

			// New Post ID
			$new_id = $wpdb->insert_id;
		        		        
		    // Subject's name for use below
			$subject_name = $wpdb->get_var($wpdb->prepare("SELECT display_name FROM ".$wpdb->prefix."users WHERE ID = %d", $uid));
		
			// Email all friends who want to know about it
			$sql = "SELECT u.ID, f.friend_to, u.user_email, m.notify_new_wall 
			 FROM ".$wpdb->prefix."symposium_friends f 
			 LEFT JOIN ".$wpdb->prefix."symposium_usermeta m ON m.uid = f.friend_to 
			 LEFT JOIN ".$wpdb->prefix."users u ON f.friend_to = u.ID 
			WHERE f.friend_from = ".$current_user->ID;
			$recipients = $wpdb->get_results($sql);			
			if ($recipients) {
				if ($parent == 0) {
					$email_subject = __('New Wall Post', 'wp-symposium');
					if ($current_user->ID == $uid) {
						$body = "<p>".$current_user->display_name." ".__('has added a new status to their wall', 'wp-symposium').":</p>";
					} else {
						$body = "<p>".$current_user->display_name." ".sprintf(__("has added a new post to %s's wall", "wp-symposium"), $subject_name).":</p>";
					}
				} else {
					$email_subject = __('New Wall Reply', 'wp-symposium');
					if ($current_user->ID == $uid) {
						$body = "<p>".$current_user->display_name." has replied to their post:</p>";
					} else {
						$body = "<p>".$current_user->display_name." has replied to ".$subject_name."'s post:</p>";
					}
				}
				$body .= "<p>".stripslashes($text)."</p>";
				$body .= "<p><a href='".symposium_get_url('profile')."?uid=".$uid."&post=".$parent."'>".__('Go to their wall', 'wp-symposium')."...</a></p>";
				foreach ($recipients as $recipient) {
					if ( ($recipient->ID != $current_user->ID) && ($recipient->notify_new_wall == 'on') ) {
						symposium_sendmail($recipient->user_email, $email_subject, $body);
					}
				}
			}
		
			// Build HTML to prepend to Comment
			$styles = $wpdb->get_row($wpdb->prepare("SELECT bg_color_2 FROM ".$wpdb->prefix . 'symposium_config'));
		
			$html = "<div style='background-color: ".$styles->bg_color_2."; padding:4px; padding-bottom:0px; clear: both; overflow: auto; margin-top:10px;'>";
				$html .= "<div style='float: left; overflow:auto; width:100%;padding:0px;'>";
					$html .= "<div style='margin-left: 45px;overflow:auto;'>";
						$html .= '<a href="'.symposium_get_url('profile').'?uid='.$current_user->ID.'">'.stripslashes($current_user->display_name).'</a>.<br />';
						$html .= symposium_make_url(stripslashes($text));
					$html .= "</div>";
				$html .= "</div>";
				
				$html .= "<div style='float:left;width:45px;margin-left:-100%;'>";
					$html .= get_user_avatar($current_user->ID, 40);
				$html .= "</div>";
													
			$html .= "</div>";
						
			echo $html;
			exit;

		} else {

			echo '';
			exit;
			
		}
			
			
	} else {
		echo "FAIL, NOT LOGGED IN";
	}
}

// AJAX function to add status
if ($_POST['action'] == 'addStatus') {

	global $wpdb, $current_user;
	wp_get_current_user();

	$subject_uid = $_POST['subject_uid'];
	$author_uid = $_POST['author_uid'];
	$text = $_POST['text'];

	if (is_user_logged_in()) {
		
		if ( ($text != __(addslashes("What's on your mind?"), "wp-symposium")) && ($text != '') ) {
	
			$wpdb->query( $wpdb->prepare( "
				INSERT INTO ".$wpdb->prefix."symposium_comments
				( 	subject_uid, 
					author_uid,
					comment_parent,
					comment_timestamp,
					comment
				)
				VALUES ( %d, %d, %d, %s, %s )", 
		        array(
		        	$subject_uid, 
		        	$author_uid, 
		        	0,
		        	date("Y-m-d H:i:s"),
		        	$text
		        	) 
		        ) );

			// New Post ID
			$new_id = $wpdb->insert_id;

		    // Subject's name for use below
			$subject_name = $wpdb->get_var($wpdb->prepare("SELECT display_name FROM ".$wpdb->prefix."users WHERE ID = %d", $subject_uid));
		        
			// Email all friends who want to know about it
			$sql = "SELECT u.ID, f.friend_to, u.user_email, m.notify_new_wall 
			 FROM ".$wpdb->prefix."symposium_friends f 
			 LEFT JOIN ".$wpdb->prefix."symposium_usermeta m ON m.uid = f.friend_to 
			 LEFT JOIN ".$wpdb->prefix."users u ON f.friend_to = u.ID 
			WHERE f.friend_from = ".$current_user->ID;
			$recipients = $wpdb->get_results($sql);	
					
			if ($recipients) {
				if ($subject_uid == $author_uid) {
					$body = "<p>".$current_user->display_name." ".__('has added a new status to their wall', 'wp-symposium').":</p>";
				} else {
					$body = "<p>".$current_user->display_name." ".__( sprintf("has added a new status to %s's wall", $subject_name), 'wp-symposium').":</p>";
				}
				$body .= "<p>".stripslashes($text)."</p>";
				$body .= "<p><a href='".symposium_get_url('profile')."?uid=".$subject_uid."&post=".$new_id."'>".__('Go to their wall', 'wp-symposium')."...</a></p>";
				foreach ($recipients as $recipient) {
					if ( ($recipient->ID != $current_user->ID) && ($recipient->notify_new_wall == 'on') ) {
						symposium_sendmail($recipient->user_email, __('New Wall Post', 'wp-symposium'), $body);
					}
				}
			}
		
		
			// Build HTML to prepend to Wall
			$styles = $wpdb->get_row($wpdb->prepare("SELECT row_border_size, row_border_style, text_color_2 FROM ".$wpdb->prefix . 'symposium_config'));
		
			$html = "<div style='overflow: auto; padding-top: 10px;margin-right: 15px;margin-bottom:15px;border-top: ".$styles->row_border_size."px ".$styles->row_border_style." ".$styles->text_color_2.";'>";
				$html .= "<div style='float: left; overflow:auto; width:100%;padding:0px;'>";
					$html .= "<div style='margin-left: 74px;overflow:auto;'>";
						$html .= '<a href="'.symposium_get_url('profile').'?uid='.$current_user->ID.'">'.stripslashes($current_user->display_name).'</a><br />';
						$html .= symposium_make_url(stripslashes($text));
					$html .= "</div>";
				$html .= "</div>";
				$html .= "<div style='float:left;width:74px;margin-left:-100%;'>";
					$html .= get_user_avatar($current_user->ID, 64);
				$html .= "</div>";
			$html .= "</div>";
						
			echo $html;
			exit;
			
		} else {

			echo '';
			exit;
			
		}

	} else {
		echo "FAIL, NOT LOGGED IN";
	}
		
}


?>

	