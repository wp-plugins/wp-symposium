<?php

include_once('../../../../wp-config.php');

// Update Facebook ID
if ($_POST['action'] == 'facebook_id') {

	global $wpdb, $current_user;	

	if (is_user_logged_in()) {
		update_symposium_meta($current_user->ID, 'facebook_id', $_POST['facebook_id']);

		if ($_POST['facebook_id'] != '') {
			$config = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix.'symposium_config'));

			require_once '../../wp-symposium-facebook/library/facebook.php';
			$app_id = $config->facebook_api;
			$app_secret = $config->facebook_secret;
			$facebook = new Facebook(array(
				'appId' => $app_id,
				'secret' => $app_secret,
				'cookie' => true
			));

			echo "{$facebook->getLoginUrl(array('req_perms' => 'user_status,publish_stream,user_photos'))}";
			exit;
		}
		
		echo "OK";
	}
	
	exit;
}

// Check for return from Facebook application acceptance
if (isset($_GET['session'])) {
	header("Location:".symposium_get_url('profile'));
}

// Update Profile Avatar
if ($_POST['action'] == 'saveProfileAvatar') {

	global $wpdb;

	if (is_user_logged_in()) {
	
		$uid = $_POST['uid'];
		$x = $_POST['x'];
		$y = $_POST['y'];
		$w = $_POST['w'];
		$h = $_POST['h'];
		
		$r = '';
		
		if ($w > 0) {	

			// set new size and quality
			$targ_w = $targ_h = 200;
			$jpeg_quality = 90;

			// database or filesystem
			$config = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix.'symposium_config'));
		
			if ($config->img_db == 'on') {
				
				// Using database
			
				$sql = "SELECT profile_avatar FROM ".$wpdb->base_prefix."symposium_usermeta WHERE uid = ".$uid;
				$avatar = stripslashes($wpdb->get_var($sql));	
			
				// create master from database
				$img_r = imagecreatefromstring($avatar);
				// set new size
				$targ_w = $targ_h = 200;
				// create temporary image
				$dst_r = ImageCreateTrueColor( $targ_w, $targ_h );		
				// copy to new image, with new dimensions
				imagecopyresampled($dst_r,$img_r,0,0,$x,$y,$targ_w,$targ_h,$w,$h);
				// copy to variable
				ob_start();
				imageJPEG($dst_r);
				$new_img = ob_get_contents();
				ob_end_clean();
				
				// update database with resized blob
				$wpdb->update( $wpdb->base_prefix.'symposium_usermeta', 
					array( 'profile_avatar' => addslashes($new_img) ), 
					array( 'uid' => $uid ), 
					array( '%s' ), 
					array( '%d' )
					);

				$r = 'reload';
					
			} else {
				
				// Using filesystem
				
				$profile_photo = $wpdb->get_var($wpdb->prepare("SELECT profile_photo FROM ".$wpdb->prefix.'symposium_usermeta WHERE uid = '.$uid));
			
				$src = $config->img_path."/members/".$uid."/profile/".$profile_photo;
				
				$img_r = imagecreatefromjpeg($src);
				$dst_r = ImageCreateTrueColor( $targ_w, $targ_h );
			
				if ( imagecopyresampled($dst_r,$img_r,0,0,$x,$y,$targ_w,$targ_h,$w,$h) ) {
		
					$to_path = $config->img_path."/members/".$uid."/profile/";
					$filename = time().'.jpg';
					$to_file = $to_path.$filename;
					if (file_exists($to_path)) {
					    // folder already there
					} else {
						mkdir(str_replace('//','/',$to_path), 0777, true);
					}
					
					if ( imagejpeg($dst_r,$to_file,$jpeg_quality) ) {
					
						// update database
						$wpdb->update( $wpdb->base_prefix.'symposium_usermeta', 
							array( 'profile_photo' => $filename ), 
							array( 'uid' => $uid ), 
							array( '%s' ), 
							array( '%d' )
							);
						
						$r = 'reload';
						
					} else {
					
						$r = 'conversion to jpeg failed';
						
					}
						
				} else {

					$r = 'crop failed: '.$src.','.$dst_r.','.$img_r.','.$wpdb->last_query;
					
				}
			}
			
		}
		
	} else {
		
		$r = "NOT LOGGED IN";
		
	}
	
	echo $r;	
	exit;
	
}

// AJAX function to add status
if ($_POST['action'] == 'addStatus') {

	global $wpdb, $current_user;
	wp_get_current_user();

	$subject_uid = $_POST['subject_uid'];
	$author_uid = $_POST['author_uid'];
	$text = $_POST['text'];
	$facebook = $_POST['facebook'];

	if (is_user_logged_in()) {
		
		if ( ($text != __(addslashes("What's on your mind?"), "wp-symposium")) && ($text != '') ) {
	
			$wpdb->query( $wpdb->prepare( "
				INSERT INTO ".$wpdb->base_prefix."symposium_comments
				( 	subject_uid, 
					author_uid,
					comment_parent,
					comment_timestamp,
					comment,
					is_group
				)
				VALUES ( %d, %d, %d, %s, %s, %s )", 
		        array(
		        	$subject_uid, 
		        	$author_uid, 
		        	0,
		        	date("Y-m-d H:i:s"),
		        	$text,
		        	''
		        	) 
		        ) );

			// New Post ID
			$new_id = $wpdb->insert_id;

		    // Subject's name for use below
			$subject_name = $wpdb->get_var($wpdb->prepare("SELECT display_name FROM ".$wpdb->base_prefix."users WHERE ID = %d", $subject_uid));
			
			// Email the subject (if they want to know about it and not self-posting)		        
			if ($author_uid != $subject_uid) {

				$sql = "SELECT u.user_email FROM ".$wpdb->base_prefix."users u LEFT JOIN ".$wpdb->base_prefix."symposium_usermeta m ON u.ID = m.uid
				WHERE u.ID = ".$subject_uid." AND m.notify_new_wall = 'on'";

				$recipient = $wpdb->get_row($sql);	
			
				if ($recipient) {
					$body = "<p>".$current_user->display_name." ".__('has added a new post on your profile', 'wp-symposium').":</p>";
					$body .= "<p>".stripslashes($text)."</p>";
					$body .= "<p><a href='".symposium_get_url('profile')."?uid=".$subject_uid."&post=".$new_id."'>".__('Go to the post', 'wp-symposium')."...</a></p>";
					symposium_sendmail($recipient->user_email, __('New Profile Post', 'wp-symposium'), $body);				
					
				}
			}
			
			// Sent to Facebook?
			if ($facebook == 1 && function_exists('symposium_facebook')) {
				$facebook_id = get_symposium_meta($author_uid, 'facebook_id');
				if ($facebook_id != '') {
					$config = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix.'symposium_config'));

					require_once '../../wp-symposium-facebook/library/facebook.php';
					$app_id = $config->facebook_api;
					$app_secret = $config->facebook_secret;
					$facebook = new Facebook(array(
						'appId' => $app_id,
						'secret' => $app_secret,
						'cookie' => true
					));
					$status = $facebook->api('/'.$facebook_id.'/feed', 'POST', array('message' => $text));
					echo $status;
				}
			}
			
			// Send to iPhone if devicetoken exists
			$devicetoken = $wpdb->get_var($wpdb->prepare("SELECT devicetoken FROM ".$wpdb->base_prefix."symposium_usermeta WHERE uid = %d", $subject_uid));
			if ($devicetoken != '') {
				$error = SendApplePushMessage($text,$devicetoken,TRUE);
				if ($error != "OK") {
					echo "DID NOT SEND NOTIFICATION: ".$devicetoken." ".$wpdb->last_query;
					exit;
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
				INSERT INTO ".$wpdb->base_prefix."symposium_comments
				( 	subject_uid, 
					author_uid,
					comment_parent,
					comment_timestamp,
					comment,
					is_group
				)
				VALUES ( %d, %d, %d, %s, %s, %s )", 
		        array(
		        	$uid, 
		        	$current_user->ID, 
		        	$parent,
		        	date("Y-m-d H:i:s"),
		        	$text,
		        	''
		        	) 
		        ) );

			// New Post ID
			$new_id = $wpdb->insert_id;
		        		        
		    // Subject's name for use below
			$subject_name = $wpdb->get_var($wpdb->prepare("SELECT display_name FROM ".$wpdb->base_prefix."users WHERE ID = %d", $uid));
			
			// Email the subject of the parent (ie. first post) and want to be notified
			$sql = "SELECT * FROM ".$wpdb->base_prefix."symposium_comments WHERE cid = ".$parent;
			$parent_post = $wpdb->get_row($sql);
			
			$sql = "SELECT u.user_email, m.notify_new_wall
				FROM ".$wpdb->base_prefix."users u 
				LEFT JOIN ".$wpdb->base_prefix."symposium_usermeta m ON u.ID = m.uid 
				WHERE ID = ".$parent_post->author_uid;
			
			$parent_post_recipient = $wpdb->get_row($sql);

			if ($parent_post_recipient->notify_new_wall == 'on') {
				$body = "<p>".$current_user->display_name." ".__('has replied to one of your posts', 'wp-symposium').":</p>";
				$body .= "<p>".stripslashes($text)."</p>";
				$body .= "<p><a href='".symposium_get_url('profile')."?uid=".$uid."&post=".$parent_post->cid."'>".__('Go to the post', 'wp-symposium')."...</a></p>";
				symposium_sendmail($parent_post_recipient->user_email, __('Profile Reply', 'wp-symposium'), $body);				
			}
			
			// Email all the people who have replied to this post and want to be notified
			$sql = "SELECT DISTINCT u.user_email 
				FROM ".$wpdb->base_prefix."symposium_comments c 
				LEFT JOIN ".$wpdb->base_prefix."users u ON c.author_uid = u.ID 
				LEFT JOIN ".$wpdb->base_prefix."symposium_usermeta m ON c.author_uid = m.uid 
				WHERE c.comment_parent = ".$parent." 
				AND m.notify_new_wall = 'on'";
			
			$reply_recipients = $wpdb->get_results($sql);

			foreach ($reply_recipients as $reply_recipient) {
				
				if ($reply_recipient->user_email != $parent_post_recipient->user_email && $reply_recipient->user_email != $current_user->user_email) {

					$body = "<p>".$current_user->display_name." ".__('has replied to a post you are involved in', 'wp-symposium').":</p>";
					$body .= "<p>".stripslashes($text)."</p>";
					$body .= "<p><a href='".symposium_get_url('profile')."?uid=".$uid."&post=".$parent_post->cid."'>".__('Go to the post', 'wp-symposium')."...</a></p>";
					symposium_sendmail($reply_recipient->user_email, __('New Post Reply', 'wp-symposium'), $body);				
					
				}
				
			}

			// Send to iPhone if devicetoken exists
			$devicetoken = $wpdb->get_var($wpdb->prepare("SELECT devicetoken FROM ".$wpdb->base_prefix."symposium_usermeta WHERE uid = %d", $subject_uid));
			if ($devicetoken != '') {
				$error = SendApplePushMessage($text,$devicetoken,TRUE);
				if ($error != "OK") {
					echo "DID NOT SEND NOTIFICATION: ".$devicetoken." ".$wpdb->last_query;
					exit;
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
	$limit_from = $_POST['limit_from'];

	$html = symposium_smilies(symposium_profile_body($uid1, $uid2, $post, "wall", $limit_from));
	
	echo $html;
	exit;
	
}

// Show Friends Activity
if ($_POST['action'] == 'menu_activity') {

	$uid1 = $_POST['uid1'];
	$uid2 = $_POST['uid2'];
	$post = $_POST['post'];
	$limit_from = $_POST['limit_from'];

	$html = symposium_smilies(symposium_profile_body($uid1, $uid2, $post, "friends_activity", $limit_from));
	
	echo $html;
	exit;
	
}


// Show All
if ($_POST['action'] == 'menu_all') {

	$uid1 = $_POST['uid1'];
	$uid2 = $_POST['uid2'];
	$post = $_POST['post'];
	$limit_from = $_POST['limit_from'];

	$html = symposium_smilies(symposium_profile_body($uid1, $uid2, $post, "all_activity", $limit_from));
	
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
	
	$html = "";

		if ( ($uid1 == $uid2) || (strtolower($meta->share) == 'everyone') || (strtolower($meta->share) == 'friends only' && symposium_friend_of($uid1)) ) {
	
			// Google map
	
			$city = $meta->city;
			$country = $meta->country;
			$has_map = false;

			if ($city != '' || $country != '') { 	
									
				$html .= "<div id='google_profile_map' style='width:".$config->profile_google_map."px; height:".$config->profile_google_map."px'>";
				$html .= '<a target="_blank" href="http://maps.google.co.uk/maps?f=q&amp;source=embed&amp;hl=en&amp;geocode=&amp;q='.$city.',+'.$country.'&amp;ie=UTF8&amp;hq=&amp;hnear='.$city.',+'.$country.'&amp;output=embed&amp;z=5" alt="Click on map to enlarge" title="Click on map to englarge">';
				$html .= '<img src="http://maps.google.com/maps/api/staticmap?center='.$city.',.+'.$country.'&zoom=5&size='.$config->profile_google_map.'x'.$config->profile_google_map.'&maptype=roadmap&markers=color:blue|label:&nbsp;|'.$city.',+'.$country.'&sensor=false" />';
				$html .= "</a></div>";
				
				$has_map = true;
				
			}
			
			// Extended Information
			$has_info = false;
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
						$has_info = true;
					}
				}
				
			} 
				
			
			if (!$has_map && !$has_info) {
	
				$html .= '<p>'.__("This member has no personal information to show.").'</p>';
	
			}
						
		} else {
			
			$html .= '<p>'.__("Sorry, this member has chosen not to share their personal information.").'</p>';
			
		}

	echo $html;
	exit;
	
}

// Profile Avatar
if ($_POST['action'] == 'menu_avatar') {

	$html = "";
	
		// Choose a new avatar
		$html .= '<p>'.__('Choose an image...', 'wp-symposium').'</p>';
		$html .= '<input id="profile_file_upload" name="file_upload" type="file" />';
		$html .= '<div id="profile_image_to_crop"></div>';

	echo $html;
	exit;				
}
				
// Show Settings
if ($_POST['action'] == 'menu_settings') {

	global $wpdb, $current_user;
	wp_get_current_user();

	$html = "";
	
	$uid = $_POST['uid1'];

	if ($uid == $current_user->ID || symposium_get_current_userlevel($current_user->ID) == 5) {
		
		$plugin = WP_PLUGIN_URL.'/wp-symposium';
		$dbpage = $plugin.'/symposium_profile_db.php';
		$meta = get_symposium_meta_row($uid);					
		$config = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->base_prefix . 'symposium_config'));
		$user = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->base_prefix . 'users WHERE ID = '.$uid));

		// get values
		$trusted = $meta->trusted;
		$notify_new_messages = $meta->notify_new_messages;
		$notify_new_wall = $meta->notify_new_wall;
	
		$html .= '<div id="symposium_settings_table">';

			// Trusted member (for example, for support staff)
			if (symposium_get_current_userlevel() == 5) {
				$html .= '<div class="symposium_settings_row">';
					$html .= __('Is this member trusted?', 'wp-symposium');
					$html .= '<div>';
						$html .= '<input type="checkbox" name="trusted" id="trusted"';
							if ($trusted == "on") { $html .= "CHECKED"; }
							$html .= '/>';
					$html .= '</div>';
				$html .= '</div>';
			} else {
				$html .= '<input type="hidden" name="trusted_hidden" id="trusted_hidden" value="'.$trusted.'" />';
			}

			// Display name
			$html .= '<div class="symposium_settings_row">';
				$html .= __('Your name as shown', 'wp-symposium');
				$html .= '<div>';
					$html .= '<input type="text" class="input-field" id="display_name" name="display_name" value="'.$user->display_name.'">';
				$html .= '</div>';
			$html .= '</div>';
			
			// Email address
			$html .= '<div class="symposium_settings_row">';
				$html .= __('Your email address', 'wp-symposium');
				$html .= '<div>';
					$html .= '<input type="text" class="input-field" id="user_email" name="user_email" style="width:300px" value="'.$user->user_email.'">';
				$html .= '</div>';
			$html .= '</div>';
			
			// Email notifications
			$html .= '<div class="symposium_settings_row">';
				$html .= '<input type="checkbox" name="notify_new_messages" id="notify_new_messages"';
					if ($notify_new_messages == "on") { $html .= "CHECKED"; }
					$html .= '/> ';
					$html .= __('Receive an email when you get new mail messages?', 'wp-symposium');
			$html .= '</div>';

			// Email wall
			$html .= '<div class="symposium_settings_row">';
				$html .= '<input type="checkbox" name="notify_new_wall" id="notify_new_wall"';
					if ($notify_new_wall == "on") { $html .= "CHECKED"; }
					$html .= '/> ';
					$html .= __('Receive an email when a friend adds a post?', 'wp-symposium');
			$html .= '</div>';
														
			// Password
			if ($config->enable_password == "on") {
				$html .= '<div class="symposium_settings_row">';
					$html .= '<div class="sep"></div>';
					$html .= '<div style="margin-bottom:15px; padding-top:15px;">';
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
				$html .= '</div>';
															
			}
				 
			$html .= '<br /><div class="symposium_settings_row">';
			$html .= '<input type="submit" id="updateSettingsButton" name="Submit" class="symposium-button" value="'.__('Save', 'wp-symposium').'" /> ';
			$html .= '</div>';

		$html .= '</div>';

	}
	
	echo $html;
	exit;
	
}

// Show Personal
if ($_POST['action'] == 'menu_personal') {

	global $wpdb, $current_user;
	wp_get_current_user();

	$uid = $_POST['uid1'];

	$html = "";

	if ($uid == $current_user->ID || symposium_get_current_userlevel($current_user->ID) == 5) {
		
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
	
		$html .= '<input type="hidden" name="symposium_update" value="P">';
		$html .= '<input type="hidden" name="uid" value="'.$uid.'">';

		$html .= '<div id="symposium_settings_table">';
	
			// Sharing personal information
			$html .= '<div class="symposium_settings_row">';
				$html .= __('Who do you want to share personal information with?', 'wp-symposium');
				$html .= '<div>';
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
			$html .= '<div class="symposium_settings_row">';
				$html .= __('Who do you want to share your wall with?', 'wp-symposium');
				$html .= '<div>';
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
			$html .= '<div class="symposium_settings_row">';
				$html .= __('Your date of birth (day/month/year)', 'wp-symposium');
				$html .= '<div>';
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
			$html .= '<div class="symposium_settings_row">';
				$html .= __('Which town/city are you in?', 'wp-symposium');
				$html .= '<div>';
					$html .= '<input type="text" id="city" name="city" value="'.$city.'">';
				$html .= '</div>';
			$html .= '</div>';
			
			// Country
			$html .= '<div class="symposium_settings_row">';
				$html .= __('Which country are you in?', 'wp-symposium');
				$html .= '<div>';
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
				
					$html .= '<div class="symposium_settings_row">';
						$html .= $extension->extended_name;
						$html .= '<input type="hidden" name="eid[]" value="'.$extension->eid.'">';
						$html .= '<input type="hidden" name="extended_name[]" value="'.$extension->extended_name.'">';
						$html .= '<div>';
							if ($extension->extended_type == 'Textarea') {
								$html .= '<textarea title="'.$extension->eid.'" class="eid_value profile_textarea" name="extended_value[]">'.$value.'</textarea>';
							}
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
	 
		$html .= '<p class="submit"> ';
			$html .= '<input type="submit" id="updatePersonalButton" name="Submit" class="symposium-button" value="'.__('Save', 'wp-symposium').'" /> ';
		$html .= '</p>';
		
	}
	
	echo $html;
	exit;
	
}

// Show Groups
if ($_POST['action'] == 'menu_groups') {

	global $wpdb, $current_user;

	$uid = $_POST['uid1'];

	$plugin = WP_PLUGIN_URL.'/wp-symposium';
	$config = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix . 'symposium_config'));
	
		$sql = "SELECT m.*, g.*, (SELECT COUNT(*) FROM ".$wpdb->prefix."symposium_group_members WHERE group_id = g.gid) AS member_count  
		FROM ".$wpdb->prefix."symposium_group_members m 
		LEFT JOIN ".$wpdb->prefix."symposium_groups g ON m.group_id = g.gid 
		WHERE m.member_id = ".$uid;
		
		$groups = $wpdb->get_results($sql);	
		
		$html = "";
		
		if ($groups) {
			foreach ($groups as $group) {	
				
				$html .= "<div class='groups_row corners'>";	
					
					$html .= "<div class='groups_info'>";
	
						$html .= "<div class='groups_avatar'>";
							$html .= get_group_avatar($group->gid, 64);
						$html .= "</div>";

						$html .= "<div class='group_name'>";
						$html .= "<a href='".$config->group_url."?gid=".$group->gid."'>".stripslashes($group->name)."</a>";
						$html .= "</div>";
						
						$html .= "<div class='group_member_count'>";
						$html .= __("Member Count:", "wp-symposium")." ".$group->member_count;
						if ($group->last_activity) {
							$html .= '<br /><em>'.__('last active', 'wp-symposium').' '.symposium_time_ago($group->last_activity).".</em>";
						}
						$html .= "</div>";
						
					$html .= "</div>";
					
				$html .= "</div>";
				
			}
		} else {
			$html .= __("Not a member of any groups.", "wp-symposium");
		}

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
				$sql = "DELETE FROM ".$wpdb->base_prefix."symposium_comments WHERE cid = ".$cid;
			} else {
				$sql = "DELETE FROM ".$wpdb->base_prefix."symposium_comments WHERE cid = ".$cid." AND (subject_uid = ".$uid." OR author_uid = ".$uid.")";
			}
			$rows_affected = $wpdb->query( $wpdb->prepare($sql) );
			if ( $rows_affected > 0 ) {
	
				// Delete any replies
				$sql = "DELETE FROM ".$wpdb->base_prefix."symposium_comments WHERE comment_parent = ".$cid.")";
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

	if (is_user_logged_in()) {
	
		$uid = $_POST['uid'];
		$notify_new_messages = $_POST['notify_new_messages'];
		$notify_new_wall = $_POST['notify_new_wall'];
		$password1 = $_POST['xyz1'];
		$password2 = $_POST['xyz2'];
		$display_name = $_POST['display_name'];
		$user_email = $_POST['user_email'];
		$trusted = $_POST['trusted'];
		

		update_symposium_meta($uid, 'notify_new_messages', "'".$notify_new_messages."'");
		update_symposium_meta($uid, 'notify_new_wall', "'".$notify_new_wall."'");
		update_symposium_meta($uid, 'trusted', "'".$trusted."'");
		
		$pwmsg = 'OK';
	
		$email_exists = $wpdb->get_row("SELECT ID, user_email FROM ".$wpdb->base_prefix."users WHERE lower(user_email) = '".strtolower($user_email)."'");
		if ($email_exists && $email_exists->user_email == $user_email && $email_exists->ID != $current_user->ID && symposium_get_current_userlevel($current_user->ID) < 5) {
			$rows_affected = $wpdb->update( $wpdb->base_prefix.'users', array( 'display_name' => $display_name ), array( 'ID' => $uid ), array( '%s' ), array( '%d' ) );			
	    	$pwmsg = __("Email already exists, sorry.".$email_exists->ID, "wp-symposium");				
		} else {
			$rows_affected = $wpdb->update( $wpdb->base_prefix.'users', array( 'display_name' => $display_name, 'user_email' => $user_email ), array( 'ID' => $uid ), array( '%s', '%s' ), array( '%d' ) );
		}
				
		if ($password1 != '') {
			if ($password1 == $password2) {
				$pwd = wp_hash_password($password1);
				$sql = "UPDATE ".$wpdb->base_prefix."users SET user_pass = '".$pwd."' WHERE ID = ".$uid;
			    if ($wpdb->query( $wpdb->prepare($sql) ) ) {
	
					$sql = "SELECT user_login FROM ".$wpdb->base_prefix."users WHERE ID = ".$uid;
					$username = $wpdb->get_var($sql);
					$id = $uid;
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
	
	if (is_user_logged_in()) {

		$uid = $_POST['uid'];
		$dob_day = $_POST['dob_day'];
		$dob_month = $_POST['dob_month'];
		$dob_year = $_POST['dob_year'];
		$city = $_POST['city'];
		$country = $_POST['country'];
		$share = $_POST['share'];
		$wall_share = $_POST['wall_share'];
		$extended = $_POST['extended'];
		
		update_symposium_meta($uid, 'dob_day', $dob_day);
		update_symposium_meta($uid, 'dob_month', $dob_month);
		update_symposium_meta($uid, 'dob_year', $dob_year);
		update_symposium_meta($uid, 'city', "'".$city."'");
		update_symposium_meta($uid, 'country', "'".$country."'");
		update_symposium_meta($uid, 'share', "'".$share."'");
		update_symposium_meta($uid, 'wall_share', "'".$wall_share."'");
		update_symposium_meta($uid, 'extended', "'".$extended."'");
			
		echo "OK";
		
		
	} else {
		echo "NOT LOGGED IN";
	}
	
	exit;
}


// Delete friendship
if ($_POST['action'] == 'deleteFriend') {

	global $wpdb, $current_user;

	if (is_user_logged_in()) {

		$friend = $_POST['friend'];
		
		$sql = "DELETE FROM ".$wpdb->base_prefix."symposium_friends WHERE (friend_from = ".$friend." AND friend_to = ".$current_user->ID.") OR (friend_to = ".$friend." AND friend_from = ".$current_user->ID.")";
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

	if (is_user_logged_in()) {
	
		$friend_from = $current_user->ID;
		$friend_to = $_POST['friend_to'];;					
		$friend_message = $_POST['friendmessage'];
		
		// check that request isn't already there
		if ( $wpdb->get_var($wpdb->prepare("SELECT * FROM ".$wpdb->base_prefix."symposium_friends WHERE (friend_from = ".$friend_to." AND friend_to = ".$current_user->ID." OR friend_to = ".$friend_to." AND friend_from = ".$current_user->ID.")")) ) {
			// already exists
		} else {
	
			$wpdb->query( $wpdb->prepare( "
				INSERT INTO ".$wpdb->base_prefix."symposium_friends
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
		$friend_to = $wpdb->get_var($wpdb->prepare("SELECT user_email FROM ".$wpdb->base_prefix."users WHERE ID = ".$friend_to));
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

	if (is_user_logged_in()) {

		$friend_to = $_POST['friend_to'];		
		$friend_from = $current_user->ID;
		
		if (symposium_safe_param($friend_to)) {
			$wpdb->query( $wpdb->prepare( "DELETE FROM ".$wpdb->base_prefix."symposium_friends WHERE (friend_from = ".$friend_from." AND friend_to = ".$friend_to.") OR (friend_from = ".$friend_to." AND friend_to = ".$friend_from.")" ) );	
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

	if (is_user_logged_in()) {

		$friend_to = $_POST['friend_to'];		
		$friend_from = $current_user->ID;

		$sql = "DELETE FROM ".$wpdb->base_prefix."symposium_friends WHERE (friend_from = ".$friend_to." AND friend_to = ".$current_user->ID.") OR (friend_to = ".$friend_to." AND friend_from = ".$current_user->ID.")";
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

	if (is_user_logged_in()) {

		$friend_from = $current_user->ID;
		$friend_to = $_POST['friend_to'];		
	
		// Check to see if already a friend
		$sql = "SELECT COUNT(*) FROM ".$wpdb->base_prefix."symposium_friends WHERE friend_accepted == 'on' AND ((friend_from = ".$friend_to." AND friend_to = ".$current_user->ID.") OR (friend_to = ".$friend_to." AND friend_from = ".$current_user->ID."))";
		$already_a_friend = $wpdb->get_var($sql);
		if ($already_a_friend >= 1) {
			// already a friend
		} else {
		
			// Delete pending request
			$sql = "DELETE FROM ".$wpdb->base_prefix."symposium_friends WHERE (friend_from = ".$friend_to." AND friend_to = ".$current_user->ID.") OR (friend_to = ".$friend_to." AND friend_from = ".$current_user->ID.")";
			if (symposium_safe_param($friend_from)) {
				$wpdb->query( $wpdb->prepare( $sql ) );	
			}
			
			// Add the two friendship rows
			$wpdb->query( $wpdb->prepare( "
				INSERT INTO ".$wpdb->base_prefix."symposium_friends
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
				INSERT INTO ".$wpdb->base_prefix."symposium_friends
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

	