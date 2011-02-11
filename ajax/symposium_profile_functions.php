<?php

include_once('../../../../wp-config.php');
include_once('../../../../wp-includes/wp-db.php');
include_once('../symposium_functions.php');

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
						
			exit;
			
		} else {

			exit;
			
		}

	} else {

		exit;
	}
		
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
								
			exit;

		} else {

			exit;
			
		}
			
			
	} else {
		
		exit;
		
	}
}

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

// Show Extended
if ($_POST['action'] == 'menu_extended') {

	global $wpdb, $current_user;
	wp_get_current_user();

	$uid1 = $_POST['uid1'];
	$uid2 = $_POST['uid2'];

	$plugin = WP_PLUGIN_URL.'/wp-symposium';
	$dbpage = $plugin.'/symposium_profile_db.php';
	$meta = get_symposium_meta_row($uid1);					
	$config = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix . 'symposium_config'));

	$html .= "<div id='profile_left_column' style='";
	if ($config->show_profile_menu != 'on') {
		$html .= " border-left:0px;";
	}			
	$html .= "'>";
	
		// Google map
		if ( ($uid1 == $uid2) || (strtolower($privacy) == 'everyone') || (strtolower($privacy) == 'friends only' && symposium_friend_of($uid1)) ) {

			$city = $meta->city;
			$country = $meta->country;

			if ($city != '' || $country != '') { 	
									
				$html .= "<div id='google_profile_map' style='width:".$config->profile_google_map."px; height:".$config->profile_google_map."px'>";
				$html .= '<a target="_blank" href="http://maps.google.co.uk/maps?f=q&amp;source=embed&amp;hl=en&amp;geocode=&amp;q='.$city.',+'.$country.'&amp;ie=UTF8&amp;hq=&amp;hnear='.$city.',+'.$country.'&amp;output=embed&amp;z=5" alt="Click on map to enlarge" title="Click on map to englarge">';
				$html .= '<img src="http://maps.google.com/maps/api/staticmap?center='.$city.',.+'.$country.'&zoom=5&size='.$config->profile_google_map.'x'.$config->profile_google_map.'&maptype=roadmap&markers=color:blue|label:&nbsp;|'.$city.',+'.$country.'&sensor=false" />';
				$html .= "</a></div>";
				
			}
			
		}
		
		// Extended Information
		$extended = $meta->extended;
		$fields = explode('[|]', $extended);
		if ($fields) {
			foreach ($fields as $field) {
				$split = explode('[]', $field);
				if ( ($split[0] != '') && ($split[1] != '') ) {
					$label = $wpdb->get_var($wpdb->prepare("SELECT extended_name FROM ".$wpdb->prefix."symposium_extended WHERE eid = ".$split[0]));
					$html .= "<div style='margin-bottom:15px;overflow: auto;'>";
					$html .= "<div style='font-weight:bold;'>".$label."</div>";
					$html .= "<div>".symposium_make_url($split[1])."</div>";
					$html .= "</div>";
				}
			}
		}
					
		$html .= "</div>";

	$html .= "</div>";
		
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

	// get values
	$sound = $config->sound;
	$soundchat = $config->soundchat;
	$bar_position = $config->bar_position;
	
	if ($meta->sound != '') { $sound = $meta->sound; }
	if ($meta->soundchat != '') { $soundchat = $meta->soundchat; }
	if ($meta->bar_position != '') { $bar_position = $meta->bar_position; }
		
	$timezone = $meta->timezone;
	$notify_new_messages = $meta->notify_new_messages;
	$notify_new_wall = $meta->notify_new_wall;
	
	$html .= "<div id='profile_left_column'>";
	
		$html .= '<div id="symposium_settings_table">';
		
			// Time zone adjustment
			$html .= '<div style="margin-bottom:15px;">';
				$html .= sprintf (__('Your local time zone adjustment in hours (difference from GMT which is %s).', 'wp-symposium'), date('jS \of M h:i:s A'));
				$html .= '<div style="float:right;">';
					$html .= '<select id="timezone" name="timezone">';
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
				$html .= '<div style="clear: right; margin-bottom:15px;">';
					$html .= __('Notification bar alert that sounds when you get new mail, relevant forum posts, etc', 'wp-symposium');
					$html .= '<div style="float:right;">';
						$html .= '<select id="sound" name="sound">';
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
				$html .= '<div style="clear: right; margin-bottom:15px;">';;
					$html .= __('Notification bar alert that sounds when a new chat message arrives', 'wp-symposium');
					$html .= '<div style="float:right;">';
						$html .= '<select id="soundchat" name="soundchat">';
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
				$html .= '<div style="clear: right; margin-bottom:15px;">';
					$html .= __('Where do you want the notification bar?', 'wp-symposium');
					$html .= '<div style="float: right;">';
						$html .= '<select id="bar_position" name="bar_position">';
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
			$html .= '<div style="clear:right; margin-bottom:15px;">';
				$html .= __('Your name as shown', 'wp-symposium');
				$html .= '<div style="float:right;">';
					$html .= '<input type="text" class="input-field" id="display_name" name="display_name" value="'.$current_user->display_name.'">';
				$html .= '</div>';
			$html .= '</div>';
			
			// Email address
			$html .= '<div style="clear: right; margin-bottom:15px;">';
				$html .= __('Your email address', 'wp-symposium');
				$html .= '<div style="float:right;">';
					$html .= '<input type="text" class="input-field" id="user_email" name="user_email" style="width:300px" value="'.$current_user->user_email.'">';
				$html .= '</div>';
			$html .= '</div>';
			
			// Email notifications
			$html .= '<div style="clear: right; margin-bottom:15px;">';
				$html .= __('Do you want to receive an email when you get new mail messages?', 'wp-symposium');
				$html .= '<div style="float:right;">';
					$html .= '<input type="checkbox" name="notify_new_messages" id="notify_new_messages"';
						if ($notify_new_messages == "on") { $html .= "CHECKED"; }
						$html .= '/>';
				$html .= '</div>';
			$html .= '</div>';

			// Email wall
			$html .= '<div style="clear:right; margin-bottom:15px;">';
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
				$html .= '<div style="clear: right; margin-bottom:15px; padding-top:15px;">';
					$html .= __('Change your password', 'wp-symposium');
					$html .= '<div style="float:right;">';
						$html .= '<input class="input-field" type="text" id="xyz1" name="xyz1" value="">';
					$html .= '</div>';
				$html .= '</div>';
				$html .= '<div style="clear:both">';
					$html .= __('Re-enter to confirm', 'wp-symposium');
					$html .= '<div style="float:right;">';
						$html .= '<input class="input-field" type="text" id="xyz2" name="xyz2" value="">';
					$html .= '</div>';
				$html .= '</div>';
															
			}
		
		$html .= '</div> ';
		 
		$html .= '<p style="clear:right" class="submit"> ';
		$html .= '<input type="submit" id="updateSettingsButton" name="Submit" class="button" value="'.__('Save', 'wp-symposium').'" /> ';
		$html .= '</p> ';
	
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
	
	$html .= "<div id='profile_left_column' style='";
	if ($config->show_profile_menu != 'on') {
		$html .= " border-left:0px;";
	}			
	$html .= "'>";

		$html .= '<input type="hidden" name="symposium_update" value="P">';
		$html .= '<input type="hidden" name="uid" value="'.$uid.'">';
	
		$html .= '<div id="symposium_settings_table">';
		
			// Sharing personal information
			$html .= '<div style="clear:right; margin-bottom:15px;">';
				$html .= __('Who do you want to share personal information with?', 'wp-symposium');
				$html .= '<div style="float:right;">';
					$html .= '<select id="share" name="share">';
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
			$html .= '<div style="clear:right; margin-bottom:15px;">';
				$html .= __('Who do you want to share your wall with?', 'wp-symposium');
				$html .= '<div style="float:right;">';
					$html .= '<select id="wall_share" name="wall_share">';
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
			$html .= '<div style="clear:right; margin-bottom:15px;">';
				$html .= __('Your date of birth (day/month/year)', 'wp-symposium');
				$html .= '<div style="float:right;">';
					$html .= "<select id='dob_day' name='dob_day'>";
						for ($i = 1; $i <= 31; $i++) {
							$html .= "<option value='".$i."'";
								if ($dob_day == $i) { $html .= ' SELECTED'; }
								$html .= '>'.$i.'</option>';
						}
					$html .= '</select> / ';									
					$html .= "<select id='dob_month' name='dob_month'>";
						for ($i = 1; $i <= 12; $i++) {
							$html .= "<option value='".$i."'";
								if ($dob_month == $i) { $html .= ' SELECTED'; }
								$html .= '>'.$i.'</option>';
						}
					$html .= '</select> / ';									
					$html .= "<select id='dob_year' name='dob_year'>";
						for ($i = date("Y"); $i >= 1900; $i--) {
							$html .= "<option value='".$i."'";
								if ($dob_year == $i) { $html .= ' SELECTED'; }
								$html .= '>'.$i.'</option>';
						}
					$html .= '</select>';									
				$html .= '</div>';
			$html .= '</div>';
				
			// City
			$html .= '<div style="clear:right; margin-bottom:15px;">';
				$html .= __('Which town/city are you in?', 'wp-symposium');
				$html .= '<div style="float:right;">';
					$html .= '<input type="text" id="city" name="city" value="'.$city.'">';
				$html .= '</div>';
			$html .= '</div>';
				
			// Country
			$html .= '<div style="clear:right; margin-bottom:15px;">';
				$html .= __('Which country are you in?', 'wp-symposium');
				$html .= '<div style="float:right;">';
					$html .= '<input type="text" id="country" name="country" value="'.$country.'">';
				$html .= '</div>';
			$html .= '</div>';
			
			// Extensions
			$extensions = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."symposium_extended ORDER BY extended_order, extended_name"));
			if ($extensions) {

				$fields = explode('[|]', $meta->extended);
	
				foreach ($extensions as $extension) {
					
					$value = $extension->extended_default;
					if ($extension->extended_type == "List") {
						$tmp = explode(',', $extension->extended_default);
						$value = $tmp[0];

					}
					foreach ($fields as $field) {
						$split = explode('[]', $field);
						if ($split[0] == $extension->eid) {
							$value = $split[1];
						 }
					}
					
					$html .= '<div style="clear:right; margin-bottom:15px;">';
						$html .= $extension->extended_name;
						$html .= '<input type="hidden" name="eid[]" value="'.$extension->eid.'">';
						$html .= '<input type="hidden" name="extended_name[]" value="'.$extension->extended_name.'">';
						$html .= '<div style="float:right;">';
							if ($extension->extended_type == 'Text') {
								$html .= '<input title="'.$extension->eid.'" class="eid_value" type="text" name="extended_value[]" value="'.$value.'">';
							}
							if ($extension->extended_type == 'List') {
								$html .= '<select title="'.$extension->eid.'" class="eid_value" name="extended_value[]">';
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
		 
		$html .= '<p style="clear:right" class="submit"> ';
			$html .= '<input type="submit" id="updatePersonalButton" name="Submit" class="button" value="'.__('Save', 'wp-symposium').'" /> ';
		$html .= '</p> ';
	
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


// Update Settings
if ($_POST['action'] == 'updateSettings') {

	global $wpdb, $current_user;
	wp_get_current_user();

	if (is_user_logged_in()) {
	
		$notify_new_messages = $_POST['notify_new_messages'];
		$notify_new_wall = $_POST['notify_new_wall'];
		$bar_position = $_POST['bar_position'];
		$timezone = $_POST['timezone'];
		$sound = $_POST['sound'];
		$soundchat = $_POST['soundchat'];
		$password1 = $_POST['xyz1'];
		$password2 = $_POST['xyz2'];
		$display_name = $_POST['display_name'];
		$user_email = $_POST['user_email'];
		
		update_symposium_meta($current_user->ID, 'timezone', $timezone);
		update_symposium_meta($current_user->ID, 'notify_new_messages', "'".$notify_new_messages."'");
		update_symposium_meta($current_user->ID, 'notify_new_wall', "'".$notify_new_wall."'");
		update_symposium_meta($current_user->ID, 'bar_position', "'".$bar_position."'");
		update_symposium_meta($current_user->ID, 'sound', "'".$sound."'");
		update_symposium_meta($current_user->ID, 'soundchat', "'".$soundchat."'");
		
		$pwmsg = 'OK';
	
		$email_exists = $wpdb->get_row("SELECT ID, user_email FROM ".$wpdb->prefix."users WHERE lower(user_email) = '".strtolower($user_email)."'");
		if ($email_exists->user_email == $user_email && $email_exists->ID != $current_user->ID) {
	    	$pwmsg = __("Email already exists, sorry.", "wp-symposium");				
		} else {
			$rows_affected = $wpdb->update( $wpdb->prefix.'users', array( 'display_name' => $display_name, 'user_email' => $user_email ), array( 'ID' => $current_user->ID ), array( '%s', '%s' ), array( '%d' ) );
		}
				
		if ($password1 != '') {
			if ($password1 == $password2) {
				$pwd = wp_hash_password($password1);
				$sql = "UPDATE ".$wpdb->prefix."users SET user_pass = '".$pwd."' WHERE ID = ".$current_user->ID;
			    if ($wpdb->query( $wpdb->prepare($sql) ) ) {
	
					$sql = "SELECT user_login FROM ".$wpdb->prefix."users WHERE ID = ".$current_user->ID;
					$username = $wpdb->get_var($sql);
					$id = $current_user->ID;
					$url = symposium_get_url('profile')."?view=settings&msg=".$pwmsg;
	
			    	wp_login($username, $pwd, true);
			        wp_setcookie($username, $pwd, true);
			        wp_set_current_user($id, $username);
			    	
					$pwmsg = "PASSWORD CHANGED";										
					
			    } else {
			    	$pwmsg = __("Failed to update password, sorry.", "wp-symposium");
			    }
			} else {
		    	$pwmsg = __("Passwords different, please try again.", "wp-symposium");
			}
		}
			
		echo $pwmsg;
		
	} else {
		
		echo "NOT LOGGED IN";
		
	}
	
	exit;
	
}
	

// personal updates
if ($_POST['action'] == 'updatePersonal') {

	global $wpdb, $current_user;
	wp_get_current_user();

	if (is_user_logged_in()) {

		$dob_day = $_POST['dob_day'];
		$dob_month = $_POST['dob_month'];
		$dob_year = $_POST['dob_year'];
		$city = $_POST['city'];
		$country = $_POST['country'];
		$share = $_POST['share'];
		$wall_share = $_POST['wall_share'];
		$extended = $_POST['extended'];
		
		update_symposium_meta($current_user->ID, 'dob_day', $dob_day);
		update_symposium_meta($current_user->ID, 'dob_month', $dob_month);
		update_symposium_meta($current_user->ID, 'dob_year', $dob_year);
		update_symposium_meta($current_user->ID, 'city', "'".$city."'");
		update_symposium_meta($current_user->ID, 'country', "'".$country."'");
		update_symposium_meta($current_user->ID, 'share', "'".$share."'");
		update_symposium_meta($current_user->ID, 'wall_share', "'".$wall_share."'");
		update_symposium_meta($current_user->ID, 'extended', "'".$extended."'");
			
		echo "OK";
	
	} else {
		echo "NOT LOGGED IN";
	}
	
	exit;
}


// Delete friendship
if ($_POST['action'] == 'deleteFriend') {

	global $wpdb, $current_user;
	wp_get_current_user();

	if (is_user_logged_in()) {

		$friend = $_POST['friend'];
		
		$sql = "DELETE FROM ".$wpdb->prefix."symposium_friends WHERE (friend_from = ".$friend." AND friend_to = ".$current_user->ID.") OR (friend_to = ".$friend." AND friend_from = ".$current_user->ID.")";
		if (symposium_safe_param($friend)) {
			$wpdb->query( $wpdb->prepare( $sql ) );	
		}
	
		echo $friend;
		
	} else {
		echo "NOT LOGGED IN";
	}
	exit;
}	

// Friend request made
if ($_POST['action'] == 'addFriend') {

	global $wpdb, $current_user;
	wp_get_current_user();

	if (is_user_logged_in()) {
	
		$friend_from = $current_user->ID;
		$friend_to = $_POST['friend_to'];;					
		$friend_message = $_POST['friendmessage'];
		
		// check that request isn't already there
		if ( $wpdb->get_var($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."symposium_friends WHERE (friend_from = ".$friend_to." AND friend_to = ".$current_user->ID." OR friend_to = ".$friend_to." AND friend_from = ".$current_user->ID.")")) ) {
			// already exists
		} else {
	
			$wpdb->query( $wpdb->prepare( "
				INSERT INTO ".$wpdb->prefix."symposium_friends
				( 	friend_from, 
					friend_to,
					friend_timestamp,
					friend_message
				)
				VALUES ( %d, %d, %s, %s )", 
		        array(
		        	$friend_from, 
		        	$friend_to,
		        	date("Y-m-d H:i:s"),
		        	$friend_message
		        	) 
		        ) );
		}
		
		// send email
		$friend_to = $wpdb->get_var($wpdb->prepare("SELECT user_email FROM ".$wpdb->prefix."users WHERE ID = ".$friend_to));
		$body = sprintf(__("You have received a friend request from %s", "wp-symposium"), $current_user->display_name);
		symposium_sendmail($friend_to->user_email, "fr", $body);						
	    // add notification
		$msg = '<a href="'.symposium_get_url('profile').'?view=friends">'.sprintf(__('You have a friend request from %s...', 'wp-symposium'), $current_user->display_name).'</a>';
		symposium_add_notification($msg, $friend_to);

		echo "OK";		
	} else {
		echo "NOT LOGGED IN";
	}
	
	exit;
	
}

// Is someone cancelling friend request
if ($_POST['action'] == 'cancelFriend') {

	global $wpdb, $current_user;
	wp_get_current_user();

	if (is_user_logged_in()) {

		$friend_to = $_POST['friend_to'];		
		$friend_from = $current_user->ID;
		
		if (symposium_safe_param($friend_to)) {
			$wpdb->query( $wpdb->prepare( "DELETE FROM ".$wpdb->prefix."symposium_friends WHERE (friend_from = ".$friend_from." AND friend_to = ".$friend_to.") OR (friend_from = ".$friend_to." AND friend_to = ".$friend_from.")" ) );	
		}
		
		echo "OK";		
	} else {
		echo "NOT LOGGED IN";
	}

	exit;
	
}

// Rejected friendship
if ($_POST['action'] == 'rejectFriend') {

	global $wpdb, $current_user;
	wp_get_current_user();

	if (is_user_logged_in()) {

		$friend_to = $_POST['friend_to'];		
		$friend_from = $current_user->ID;

		$sql = "DELETE FROM ".$wpdb->prefix."symposium_friends WHERE (friend_from = ".$friend_to." AND friend_to = ".$current_user->ID.") OR (friend_to = ".$friend_to." AND friend_from = ".$current_user->ID.")";
		if (symposium_safe_param($friend_to)) {
			$wpdb->query( $wpdb->prepare( $sql ) );	
		}

		echo $friend_to;		
	} else {
		echo "NOT LOGGED IN";
	}
	
	exit;
}


// Accepted friendship
if ($_POST['action'] == 'acceptFriend') {

	global $wpdb, $current_user;
	wp_get_current_user();

	if (is_user_logged_in()) {

		$friend_from = $current_user->ID;
		$friend_to = $_POST['friend_to'];		
	
		// Check to see if already a friend
		$sql = "SELECT COUNT(*) FROM ".$wpdb->prefix."symposium_friends WHERE friend_accepted == 'on' AND ((friend_from = ".$friend_to." AND friend_to = ".$current_user->ID.") OR (friend_to = ".$friend_to." AND friend_from = ".$current_user->ID."))";
		$already_a_friend = $wpdb->get_var($sql);
		if ($already_a_friend >= 1) {
			// already a friend
		} else {
		
			// Delete pending request
			$sql = "DELETE FROM ".$wpdb->prefix."symposium_friends WHERE (friend_from = ".$friend_to." AND friend_to = ".$current_user->ID.") OR (friend_to = ".$friend_to." AND friend_from = ".$current_user->ID.")";
			if (symposium_safe_param($friend_from)) {
				$wpdb->query( $wpdb->prepare( $sql ) );	
			}
			
			// Add the two friendship rows
			$wpdb->query( $wpdb->prepare( "
				INSERT INTO ".$wpdb->prefix."symposium_friends
				( 	friend_from, 
					friend_to,
					friend_timestamp,
					friend_accepted
				)
				VALUES ( %d, %d, %s, %s )", 
		        array(
		        	$current_user->ID, 
		        	$friend_to,
		        	date("Y-m-d H:i:s"),
		        	'on'
		        	) 
		        ) );
			$wpdb->query( $wpdb->prepare( "
				INSERT INTO ".$wpdb->prefix."symposium_friends
				( 	friend_to, 
					friend_from,
					friend_timestamp,
					friend_accepted
				)
				VALUES ( %d, %d, %s, %s )", 
		        array(
		        	$current_user->ID, 
		        	$friend_to,
		        	date("Y-m-d H:i:s"),
		        	'on'
		        	) 
		        ) );
	
			// notify friendship requestor
			$msg = '<a href="'.symposium_get_url('profile').'?uid='.$current_user->ID.'">'.__('Your friend request has been accepted by', 'wp-symposium').' '.$current_user->display_name.'...</a>';
			
			symposium_add_notification($msg, $friend_to);
		}
	
		echo $friend_to;		
	} else {
		echo "NOT LOGGED IN";
	}

	exit;
	
}
		
?>

	