<?php

include_once('../../../../wp-config.php');

// Request group delete
if ($_POST['action'] == 'requestDelete') {
	global $wpdb, $current_user;

	$r = 'OK';

	$group_id = $_POST['group_id'];
	$request_text = $_POST['request_text'];

	symposium_sendmail(get_bloginfo('admin_email'), __('Group Delete Request', 'wp-symposium'), __('From:', 'wp-symposium').': '.$current_user->display_name.'<br /><br />'.$request_text.'<br /><br />Ref: '.$group_id);							

	exit;	
}

// Group Invites
if ($_POST['action'] == 'group_menu_invites') {
	
	$html = '';

	if (is_user_logged_in()) {

		$html .= '<h1>'.__('Group Invitations', 'wp-symposium').'</h1>';
		
		$html .= '<p>'.__('Enter email addresses of people you would like to invite to your group, separated by commas, or on separate lines.', 'wp-symposium').' ';
		$html .= __('They will receive an email with a link to click on that will take them to this site and group page.', 'wp-symposium').' ';
		$html .= __('If they are not members of this site, they will be able to register before logging in.', 'wp-symposium').'</p>';

		$html .= '<p style="font-weight:bold">'.sprintf(__('You can invite a maximum of %d people at a time.', 'wp-symposium'), get_option('symposium_group_invites_max')).'</p>';
		
		$html .= '<textarea id="symposium_group_invites" rows="10" style="width:98%; margin-bottom:10px;"></textarea>';
		$html .= '<input type="submit" id="symposium_group_invites_button" name="Submit" class="symposium-button" value="'.__('Invite', 'wp-symposium').'" /> ';

		$html .= '<div id="symposium_group_invites_sent" style="display:none"></div>';
		
	}
	
	echo $html;
	exit;
}

// Send group invites
if ($_POST['action'] == 'sendInvites') {

	$html = '';

	$from_email = get_option('symposium_from_email');
	if ($from_email == '') { $from_email = "noreply@".get_bloginfo('url'); }	

	$group = $wpdb->get_row($wpdb->prepare("SELECT name, description FROM ".$wpdb->prefix."symposium_groups WHERE gid = %d", $_POST['group_id']));

	$crlf = PHP_EOL;
	$html = 'Emails sent to:<br />';
	$blog_name = get_bloginfo('name');
	$url = symposium_get_url('groups');
	$url = $url.symposium_string_query($url).'gid='.$_POST['group_id'];

	$emails = $_POST['emails'];	
	$emails = eregi_replace(" ", "", $emails);
	$emails = eregi_replace(";", ",", $emails);
	$emails = eregi_replace(PHP_EOL, ",", $emails);
		
	$email_addresses = explode(',', $emails);
	
	if ($email_addresses) {
		foreach ($email_addresses as $email_address) {
				
			$body = "<h1>".__('Group Invitation', 'wp-symposium')."</h1>";

			$body .= '<p>'.__('To:', 'wp-symposium').' '.$email_address.'<br />';
			$body .= __('From:', 'wp-symposium').' '.$current_user->user_email.'</p>';

			$body .= '<p>'.sprintf(__("Please come and join my group on %s", "wp-symposium"), $blog_name).'!</p>';

			$body .= '<h2>'.$group->name.'</h2>';
			$body .= '<p>'.$group->description.'</p>';
			$body .= '<p>'.$url.'</p>';

			$body .= "<p><em>";
			$body .= $current_user->display_name;
			$body .= "</em></p>";

			$body = str_replace(chr(13), "<br />", $body);
			$body = str_replace("\\r\\n", "<br />", $body);
			$body = str_replace("\\", "", $body);
		
			// To send HTML mail, the Content-type header must be set
			$headers = "MIME-Version: 1.0" . $crlf;
			$headers .= "Content-type:text/html;charset=utf-8" . $crlf;
			$headers .= "From: ".$from_email . $crlf;

			// finally send mail
			if (symposium_sendmail($email_address, __('Group Invitation', 'wp-symposium'), $body)) {
				$html .= $email_address.'<br />';
			} else {
				$html .= $email_address.' (failed)<br />';
			}
		}			
	}

	echo $html;
	exit;
}

// Member delete
if ($_POST['action'] == 'member_delete') {
	global $wpdb, $current_user;

	if (is_user_logged_in()) {
		
		$uid = $current_user->ID;		
		$gid = $_POST['group_id'];		
		$id = $_POST['id'];		

		// First check is a group admin
		$sql = "SELECT member_id FROM ".$wpdb->prefix."symposium_group_members WHERE group_id=%d AND member_id=%d and admin='on'";
		$admin_check = $wpdb->get_var($wpdb->prepare($sql, $gid, $uid));
		if ($admin_check || symposium_get_current_userlevel() == 5) {
			$sql = "DELETE FROM ".$wpdb->prefix."symposium_group_members WHERE group_id=%d AND member_id = %d";
			$wpdb->query( $wpdb->prepare( $sql, $gid, $id ) );	
		}
	}
	
}

// Group Forum
if ($_POST['action'] == 'group_menu_forum') {
	// WPS Forum is used	
}

// Group Delete
if ($_POST['action'] == 'deleteGroup') {

	global $wpdb, $current_user;

	if (is_user_logged_in()) {

		$uid = $current_user->ID;		
		$gid = $_POST['gid'];	
		
		// first check this user is a group admin
		$sql = "SELECT admin FROM ".$wpdb->prefix."symposium_group_members WHERE group_id = %d AND member_id = %d";
		$admin = $wpdb->get_var($wpdb->prepare($sql, $gid, $uid));	
		
		if ($admin == "on" || symposium_get_current_userlevel() == 5) {

			if (symposium_safe_param($gid)) {
			
				// delete all wall comments
				$sql = "DELETE FROM ".$wpdb->prefix."symposium_comments WHERE is_group = 'on' AND subject_uid = %d";
				$wpdb->query( $wpdb->prepare( $sql, $gid ) );	
	
				// delete members			
				$sql = "DELETE FROM ".$wpdb->prefix."symposium_group_members WHERE group_id = %d";
				$wpdb->query( $wpdb->prepare( $sql, $gid ) );	
				
				// delete group			
				$sql = "DELETE FROM ".$wpdb->prefix."symposium_groups WHERE gid = %d";
				$wpdb->query( $wpdb->prepare( $sql, $gid ) );	
	
				// delete topics			
				$sql = "DELETE FROM ".$wpdb->prefix."symposium_topics WHERE topic_group = %d";
				$wpdb->query( $wpdb->prepare( $sql, $gid ) );	
				
				// delete from news (if plugin activated)
				if (function_exists('symposium_news_main')) {
					$sql = "DELETE FROM ".$wpdb->prefix."symposium_news WHERE news LIKE %s";
					$wpdb->query( $wpdb->prepare( $sql, '%gid='.$gid.'&%' ) );	
				}
			
			}
			echo "OK";
			
		} else {
			echo "NOT GROUP ADMIN";
		}
				
	} else {
		echo "NOT LOGGED IN";
	}
		
	exit;
}

// Group Accept
if ($_POST['action'] == 'acceptGroup') {

	global $wpdb;

	if (is_user_logged_in()) {

		$uid = $_POST['uid'];		
		$gid = $_POST['gid'];		

		$sql = "UPDATE ".$wpdb->prefix."symposium_group_members SET valid = 'on' WHERE group_id = %d AND member_id = %d";
		if (symposium_safe_param($gid)) {
			$wpdb->query( $wpdb->prepare( $sql, $gid, $uid ) );	
		}

		// Email to let the member know the result
		$sql = "SELECT ID, user_email FROM ".$wpdb->base_prefix."users u WHERE ID = %d";
		$recipient = $wpdb->get_row($wpdb->prepare($sql, $uid));	
				
		if ($recipient) {
							
			$body = "<h1>".__("Group Membership", "wp-symposium")."</h1>";
			$body .= "<p>".__('You have successfully joined this group', 'wp-symposium').".</p>";
			$body .= "<p><a href='".symposium_get_url('group')."?gid=".$gid."'>".__('Go to the group', 'wp-symposium')."...</a></p>";
			if ( $recipient->ID != $current_user->ID) {
				symposium_sendmail($recipient->user_email, __('Group Membership', 'wp-symposium'), $body);
			}
		}

		// Get group name
		$sql = "SELECT name, new_member_emails FROM ".$wpdb->prefix."symposium_groups WHERE gid = %d";
		$group = $wpdb->get_row($wpdb->prepare($sql, $gid));

		// Tell other members
		$html = symposium_inform_members($group->name, $group->gid, $group->new_member_emails);	
			
		echo $uid;		
		
	} else {
		echo "NOT LOGGED IN";
	}
		
	exit;
}

// Group Reject
if ($_POST['action'] == 'rejectGroup') {

	global $wpdb;

	if (is_user_logged_in()) {

		$uid = $_POST['uid'];		
		$gid = $_POST['gid'];		

		$sql = "DELETE FROM ".$wpdb->prefix."symposium_group_members WHERE group_id = %d AND member_id = %d";
		if (symposium_safe_param($gid)) {
			$wpdb->query( $wpdb->prepare( $sql, $gid, $uid ) );	
		}

		echo $uid;		
		
	} else {
		echo "NOT LOGGED IN";
	}
		
	exit;
}


// Group Subscribe
if ($_POST['action'] == 'group_subscribe') {

	global $wpdb;	

	if (is_user_logged_in()) {
	
		$notify = $_POST['notify'];
		$gid = $_POST['gid'];
		
		$wpdb->query("UPDATE ".$wpdb->prefix."symposium_group_members SET notify = '".$notify."' WHERE member_id = ".$current_user->ID." AND group_id = ".$gid);
		
		echo $wpdb->last_query;
		
	}
	
	exit;
}

// Leave Group
if ($_POST['action'] == 'leaveGroup') {

	global $wpdb;

	if (is_user_logged_in()) {
	
		$gid = $_POST['gid'];
		if (symposium_safe_param($gid)) {
			$wpdb->query("DELETE FROM ".$wpdb->prefix."symposium_group_members WHERE member_id = ".$current_user->ID." AND group_id = ".$gid);
		}
	}
	
	exit;
		
}

// Join Group
if ($_POST['action'] == 'joinGroup') {


	global $wpdb;

	if (is_user_logged_in()) {
	
		$gid = $_POST['gid'];
		
		// Check if private or public
		$sql = "SELECT private FROM ".$wpdb->prefix."symposium_groups WHERE gid = %d";
		$private = $wpdb->get_var($wpdb->prepare($sql, $gid));
		
		if ($private == "on") {
			$valid = '';
		} else {
			$valid = 'on';
		}

		// First delete (to avoid any duplicate entries)
		$sql = "DELETE FROM ".$wpdb->prefix."symposium_group_members WHERE group_id = %d AND member_id = %d";
	   	$wpdb->query($wpdb->prepare($sql, $gid, $current_user->ID));
		
		// Add membership
		$wpdb->query( $wpdb->prepare( "
			INSERT INTO ".$wpdb->prefix."symposium_group_members
			( 	group_id, 
				member_id,
				admin,
				valid,
				joined
			)
			VALUES ( %d, %d, %s, %s, %s )", 
	        array(
	        	$gid, 
	        	$current_user->ID, 
	        	'',
	        	$valid,
	        	date("Y-m-d H:i:s")
	        	) 
	        ) );

		// Get group name
		$sql = "SELECT name, new_member_emails FROM ".$wpdb->prefix."symposium_groups WHERE gid = %d";
		$group = $wpdb->get_row($wpdb->prepare($sql, $gid));
	        
		if ($private == "on") {

			// Send email to group admin, so get group admin email address
			$sql = "SELECT u.user_email 
					FROM ".$wpdb->base_prefix."users u 
					LEFT JOIN ".$wpdb->prefix."symposium_group_members m ON u.ID = m.member_id 
					WHERE m.group_id = %d AND m.admin = 'on'";
			$email_address = $wpdb->get_var($wpdb->prepare($sql, $gid));
	
			$body = "<h1>".__('Group Request', 'wp-symposium')."</h1>";
			$body .= '<p>'.sprintf(__("New group member request for %s", "wp-symposium"), $group->name).': '.$current_user->display_name.'</p>';
	
			$url = symposium_get_url('group');
			$url .= symposium_string_query($url);
			$url .= "gid=".$gid;
			
			$body .= '<p><a href="'.$url.'">'.$url.'</a></p>';
			
			$body = str_replace(chr(13), "<br />", $body);
			$body = str_replace("\\r\\n", "<br />", $body);
			$body = str_replace("\\", "", $body);
		
			// finally send mail
			if (symposium_sendmail($email_address, __('Group Request', 'wp-symposium'), $body)) {
				$html = '';
			} else {
				$html = 'Failed to send email to '.$email_address;
			}
		} else {
			// Tell other members
			$html = symposium_inform_members($group->name, $gid, $group->new_member_emails);
		}		
		echo $html;	
			        
		exit;
			        
	} else {
		
		echo "NOT LOGGED IN";
		
	}
	
	exit;
		
}

function symposium_inform_members($group_name, $gid, $new_member_emails) {
	
	
	global $wpdb, $current_user;

	$html = '';
	
	// First check that this group tells about new members
	if ($new_member_emails == 'on') {
		
		$body = "<h1>".$group_name."</h1>";
		$body .= '<p>'.__("New group member", "wp-symposium").': '.$current_user->display_name.'</p>';
	
		$url = symposium_get_url('group');
		$url .= symposium_string_query($url);
		$url .= "gid=".$gid;
		
		$body .= '<p><a href="'.$url.'">'.$url.'</a></p>';
		
	    $sql = "SELECT u.user_email 
				FROM ".$wpdb->base_prefix."users u 
				LEFT JOIN ".$wpdb->prefix."symposium_group_members m ON u.ID = m.member_id 
				WHERE m.group_id = %d";
				
		$recipients = $wpdb->get_results($wpdb->prepare($sql, $gid));	
	
		foreach ($recipients AS $recipient) {
			if (symposium_sendmail($recipient->user_email, __('New group member', 'wp-symposium'), $body)) {
				//$html .= 'Sent to '.$recipient->user_email.' ';
			} else {
				$html .= 'Failed to send email to '.$recipient->user_email.'<br />';
			}
		}

	} else {
		//$html .= 'Not sending emails for this group!';
	}
	
	return $html;
	
}

// Update Group Settings
if ($_POST['action'] == 'updateGroupSettings') {

	global $wpdb, $blog_id;

	if (is_user_logged_in()) {

		$gid = $_POST['gid'];
		
		$sql = "SELECT member_id FROM ".$wpdb->prefix."symposium_group_members WHERE group_id = %d AND admin='on'";
		$current_group_admin = $wpdb->get_var($wpdb->prepare($sql, $gid));
		
		if ($current_group_admin == $current_user->ID || symposium_get_current_userlevel() == 5) {
	
			$groupname = $_POST['groupname'];
			$groupdescription = $_POST['groupdescription'];
			$private = (isset($_POST['is_private'])) ? $_POST['is_private'] : '';
			$content_private = (isset($_POST['content_private'])) ? $_POST['content_private'] : '';
			$group_forum = (isset($_POST['group_forum'])) ? $_POST['group_forum'] : '';
			$show_forum_default = (isset($_POST['show_forum_default'])) ? $_POST['show_forum_default'] : '';
			$allow_new_topics = (isset($_POST['allow_new_topics'])) ? $_POST['allow_new_topics'] : '';
			$new_member_emails = (isset($_POST['new_member_emails'])) ? $_POST['new_member_emails'] : '';		
			$add_alerts = (isset($_POST['add_alerts'])) ? $_POST['add_alerts'] : '';		
			$group_admin = $_POST['group_admin'];
			
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix."symposium_groups SET 
				name = '".$groupname."',  
				description = '".$groupdescription."',  
				private = '".$private."',  
				content_private = '".$content_private."',
				group_forum = '".$group_forum."',
				show_forum_default = '".$show_forum_default."',
				allow_new_topics = '".$allow_new_topics."',
				add_alerts = '".$add_alerts."',
				new_member_emails = '".$new_member_emails."'  
				WHERE gid = ".$gid ) );
				
			// Save group image
			if (isset($_POST['x'])) {
				$x = $_POST['x'];
				$y = $_POST['y'];
				$w = $_POST['w'];
				$h = $_POST['h'];
			} else {
				$x = 0;
				$y = 0;
				$w = 0;
				$h = 0;
			}
			
			// update group admin, first clear current admin
			$sql = "UPDATE ".$wpdb->prefix."symposium_group_members
					SET admin = ''
					WHERE group_id = %d";
			$wpdb->query($wpdb->prepare($sql, $gid));
			// then set new one
			$sql = "UPDATE ".$wpdb->prefix."symposium_group_members
					SET admin = 'on'
					WHERE group_id = %d AND member_id = %d";
			$wpdb->query($wpdb->prepare($sql, $gid, $group_admin));
			
			$r = '';
	
			if ($w > 0) {	
	
				// set new size and quality
				$targ_w = $targ_h = 200;
				$jpeg_quality = 90;
				
				// database or filesystem
				if (get_option('symposium_img_db') == 'on') {
					
					// Using database
				
					$sql = "SELECT group_avatar FROM ".$wpdb->prefix."symposium_groups WHERE gid = %d";
					$avatar = stripslashes($wpdb->get_var($wpdb->prepare($sql, $gid)));	
			
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
					$wpdb->update( $wpdb->prefix.'symposium_groups', 
						array( 'group_avatar' => addslashes($new_img) ), 
						array( 'gid' => $gid ), 
						array( '%s' ), 
						array( '%d' )
						);
					
					$r .= 'reload';
					
				} else {
					
					// Using filesystem
	
					$profile_photo = $wpdb->get_var($wpdb->prepare("SELECT profile_photo FROM ".$wpdb->prefix.'symposium_groups WHERE gid = '.$gid));
				
					if ($blog_id > 1) {
						$src = get_option('symposium_img_path')."/".$blog_id."/groups/".$gid."/profile/".$profile_photo;				
						$to_path = get_option('symposium_img_path')."/".$blog_id."/groups/".$gid."/profile/";
					} else {
						$src = get_option('symposium_img_path')."/groups/".$gid."/profile/".$profile_photo;
						$to_path = get_option('symposium_img_path')."/groups/".$gid."/profile/";
					}
					
					$img_r = imagecreatefromjpeg($src);
					$dst_r = ImageCreateTrueColor( $targ_w, $targ_h );
				
					imagecopyresampled($dst_r,$img_r,0,0,$x,$y,$targ_w,$targ_h,$w,$h);
			
					$filename = time().'.jpg';
					$to_file = $to_path.$filename;
					if (file_exists($to_path)) {
					    // folder already there
					} else {
						mkdir(str_replace('//','/',$to_path), 0777, true);
					}
						
					if ( imagejpeg($dst_r,$to_file,$jpeg_quality) ) {
						
						// update database
						$wpdb->update( $wpdb->base_prefix.'symposium_groups', 
							array( 'profile_photo' => $filename ), 
							array( 'gid' => $gid ), 
							array( '%s' ), 
							array( '%d' )
							);
							
						$r .= 'reload';
							
					} else {
						
						$r .= 'resize failed: '.$wpdb->last_query;
							
					}
									
				}
			
			}
			
		} else {
			
			$r = "NOT ADMIN (".$current_group_admin.")";
		}
		
	} else {
		
		$r = "NOT LOGGED IN";
		
	}
	
	echo $r;
	exit;
	
}

// Show Group Settings
if ($_POST['action'] == 'group_menu_settings') {

	global $wpdb, $current_user;

	$gid = $_POST['uid1'];

	$group = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix . 'symposium_groups WHERE gid='.$gid));

	$groupname = stripslashes($group->name);
	$groupdescription = stripslashes($group->description);
	$private = $group->private;
	$content_private = $group->content_private;
	$group_forum = $group->group_forum;
	$show_forum_default = $group->show_forum_default;
	$allow_new_topics = $group->allow_new_topics;
	$new_member_emails = $group->new_member_emails;
	$add_alerts = $group->add_alerts;
	
	$html = '';
	
	if (symposium_group_admin($gid) == "yes" || symposium_get_current_userlevel($current_user->ID) == 5 ) {
	
		$html .= "<div id='profile_left_column'>";
		
			$html .= '<div id="symposium_settings_table">';
	
				// Display name
				$html .= '<div style="clear:right; margin-bottom:15px;">';
					$html .= __('Group name', 'wp-symposium');
					$html .= '<div style="float:right;">';
						$html .= '<input type="text" id="groupname" value="'.$groupname.'">';
					$html .= '</div>';
				$html .= '</div>';
				
				// Description
				$html .= '<div style="clear: right; margin-bottom:15px;">';
					$html .= __('Group description', 'wp-symposium');
					$html .= '<div style="float:right;">';
						$html .= '<input type="text" id="groupdescription" value="'.$groupdescription.'">';
					$html .= '</div>';
				$html .= '</div>';
				
				// Private?
				$html .= '<div style="clear: right; margin-bottom:15px;">';
					$html .= __('Do new members have to be accepted?', 'wp-symposium');
					$html .= '<div style="float:right;">';
						$html .= '<input type="checkbox" name="private" id="private"';
							if ($private == "on") { $html .= "CHECKED"; }
							$html .= '/>';
					$html .= '</div>';
				$html .= '</div>';
			
				// Private Content to non-members?
				$html .= '<div style="clear: right; margin-bottom:15px;">';
					$html .= __('Is content hidden from non-members?', 'wp-symposium');
					$html .= '<div style="float:right;">';
						$html .= '<input type="checkbox" name="content_private" id="content_private"';
							if ($content_private == "on") { $html .= "CHECKED"; }
							$html .= '/>';
					$html .= '</div>';
				$html .= '</div>';

				// Forum?
				$html .= '<div style="clear: right; margin-bottom:15px;">';
					$html .= __('Enable the group forum?', 'wp-symposium');
					$html .= '<div style="float:right;">';
						$html .= '<input type="checkbox" name="group_forum" id="group_forum"';
							if ($group_forum == "on") { $html .= "CHECKED"; }
							$html .= '/>';
					$html .= '</div>';
				$html .= '</div>';
			
				// Alloq new topics
				$html .= '<div style="clear: right; margin-bottom:15px;">';
					$html .= __('Allow members to create forum topics?', 'wp-symposium');
					$html .= '<div style="float:right;">';
						$html .= '<input type="checkbox" name="allow_new_topics" id="allow_new_topics"';
							if ($allow_new_topics == "on") { $html .= "CHECKED"; }
							$html .= '/>';
					$html .= '</div>';
				$html .= '</div>';
						
				// Inform members of new group members
				$html .= '<div style="clear: right; margin-bottom:15px;">';
					$html .= __('New member emails?', 'wp-symposium');
					$html .= '<div style="float:right;">';
						$html .= '<input type="checkbox" name="new_member_emails" id="new_member_emails"';
							if ($new_member_emails == "on") { $html .= "CHECKED"; }
							$html .= '/>';
					$html .= '</div>';
				$html .= '</div>';
			
				// Forum as default?
				$html .= '<div style="clear: right; margin-bottom:15px;">';
					$html .= __('Forum as default page?', 'wp-symposium');
					$html .= '<div style="float:right;">';
						$html .= '<input type="checkbox" name="show_forum_default" id="show_forum_default"';
							if ($show_forum_default == "on") { $html .= "CHECKED"; }
							$html .= '/>';
					$html .= '</div>';
				$html .= '</div>';
			
				// Add activity to alerts?
				if (function_exists('symposium_news_add')) {
					$html .= '<div style="clear: right; margin-bottom:15px;">';
						$html .= __('Include activity posts in Alerts?', 'wp-symposium');
						$html .= '<div style="float:right;">';
							$html .= '<input type="checkbox" name="add_alerts" id="add_alerts"';
								if ($add_alerts == "on") { $html .= "CHECKED"; }
								$html .= '/>';
						$html .= '</div>';
					$html .= '</div>';
				}
			
				// Transfer group ownership
				$html .= '<div style="clear: right; margin-bottom:15px;">';
					$html .= __('Transfer group admin to:', 'wp-symposium');
					$html .= '<div style="float:right;">';
						$sql = "SELECT u.ID, u.display_name, m.admin
								FROM ".$wpdb->base_prefix."users u
								LEFT JOIN ".$wpdb->prefix."symposium_group_members m ON u.ID = m.member_id 
								WHERE m.group_id = %d 
								ORDER BY u.display_name";
						$members = $wpdb->get_results($wpdb->prepare($sql, $gid));
						$html .= '<select name="transfer_admin" id="transfer_admin">';
						foreach ($members AS $member) {
							$html .= '<option value="'.$member->ID.'"';
							if ($member->admin == 'on') { $html .= ' SELECTED'; }
							$html .= '>'.$member->display_name.'</option>';
						}
						$html .= '</select>';
					$html .= '</div>';
				$html .= '</div>';
			
				// Choose a new avatar
				$html .= '<div style="clear: right; margin-bottom:15px;">';
				$html .= "<div id='symposium_user_login' style='display:none'>".strtolower($current_user->user_login)."</div>";
				$html .= "<div id='symposium_user_email' style='display:none'>".strtolower($current_user->user_email)."</div>";
				$html .= '<p>'.__('Choose an image for the group...', 'wp-symposium').'</p>';
				$html .= '<input id="file_upload" name="file_upload" type="file" />';
				$html .= '<div id="group_image_to_crop" style="margin-bottom:15px"></div>';
				$html .= '</div>';								
			
			$html .= '</div> ';
			 
			$html .= '<p style="clear:right" class="submit"> ';
			$html .= '<input type="submit" id="updateGroupSettingsButton" name="Submit" class="symposium-button" value="'.__('Save', 'wp-symposium').'" /> ';
			$html .= '</p> ';
		
		$html .= "</div>";
		
	} else {
		
		$html .= "Group admin only";
		
	}
	
	echo $html;
	exit;
	
}

// AJAX function to add comment
if ($_POST['action'] == 'group_addComment') {

	global $wpdb, $current_user;

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
		        	'on'
		        	) 
		        ) );

			// New Post ID
			$author_name = $wpdb->get_var($wpdb->prepare("SELECT display_name FROM ".$wpdb->base_prefix."users WHERE ID = %d", $current_user->ID));
			$group_name = $wpdb->get_var($wpdb->prepare("SELECT name FROM ".$wpdb->base_prefix."symposium_groups WHERE gid = %d", $uid));
		        
			// Update last activity
			$wpdb->query( $wpdb->prepare( "UPDATE ".$wpdb->base_prefix."symposium_groups SET last_activity = %s WHERE gid = %d", array( date("Y-m-d H:i:s"), $uid ) ));

			// Email all members who want to know about it
			$sql = "SELECT u.ID, u.user_email, m.notify FROM ".$wpdb->base_prefix."users u 
			INNER JOIN ".$wpdb->prefix."symposium_group_members m ON u.ID = m.member_id
			WHERE group_id = %d";

			$recipients = $wpdb->get_results($wpdb->prepare($sql, $uid));	

			// Group post URL					
			$url = symposium_get_url('group');
			$url .= symposium_string_query($url);
			$url .= "gid=".$uid."&post=".$parent;
						
			// Should alerts be sent out?
			$add_alerts = $wpdb->get_var($wpdb->prepare("SELECT add_alerts FROM ".$wpdb->prefix."symposium_groups WHERE gid = %d", $uid));
			
			if ($recipients) {
								
				$body = "<h1>".stripslashes($group_name)."</h1>";
				$body .= "<p>".$author_name." ".__('has added a new reply to the group', 'wp-symposium').":</p>";
				$body .= "<p>".stripslashes($text)."</p>";
				$body .= "<p><a href='".$url."'>".__('Go to the group post', 'wp-symposium')."...</a></p>";
				foreach ($recipients as $recipient) {
					if ( $recipient->ID != $current_user->ID) {
						if ($recipient->notify == 'on') {
							symposium_sendmail($recipient->user_email, __('New Group Post', 'wp-symposium'), $body);
						}
						if (function_exists('symposium_news_add') && $add_alerts == 'on') {
							symposium_news_add($current_user->ID, $recipient->ID, "<a href='".$url."'>".__("Group reply:", "wp-symposium")." ".$author_name." ".__("has replied in", "wp-symposium")." ".$group_name."</a>");
						}
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

// AJAX function to add status
if ($_POST['action'] == 'group_addStatus') {

	global $wpdb, $current_user;

	$subject_uid = $_POST['subject_uid'];
	$author_uid = $_POST['author_uid'];
	$text = $_POST['text'];
	$group_id = $_POST['gid'];

	if (is_user_logged_in()) {
		
		if ( ($text != __("Write a comment...", 'wp-symposium')) && ($text != '') ) {

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
		        	'on'
		        	) 
		        ) );

			// New Post ID
			$new_id = $wpdb->insert_id;
						
			$author_name = $wpdb->get_var($wpdb->prepare("SELECT display_name FROM ".$wpdb->base_prefix."users WHERE ID = %d", $author_uid));
			$group_name = $wpdb->get_var($wpdb->prepare("SELECT name FROM ".$wpdb->base_prefix."symposium_groups WHERE gid = %d", $subject_uid));

			// Update last activity
			$wpdb->query( $wpdb->prepare( "UPDATE ".$wpdb->base_prefix."symposium_groups SET last_activity = %s WHERE gid = %d", array( date("Y-m-d H:i:s"), $subject_uid ) ));
		        
			// Email all members who want to know about it
			$sql = "SELECT u.ID, u.user_email, m.notify FROM ".$wpdb->base_prefix."users u 
			INNER JOIN ".$wpdb->prefix."symposium_group_members m ON u.ID = m.member_id
			WHERE group_id = %d";

			$recipients = $wpdb->get_results($wpdb->prepare($sql, $subject_uid));
			
			// URL of group post
			$url = symposium_get_url('group');
			$url .= symposium_string_query($url);
			$url .= "gid=".$subject_uid."&post=".$new_id;
			
			// Should alerts be sent out?
			$add_alerts = $wpdb->get_var($wpdb->prepare("SELECT add_alerts FROM ".$wpdb->prefix."symposium_groups WHERE gid = %d", $subject_uid));
					
			if ($recipients) {
								
				$body = "<h1>".stripslashes($group_name)."</h1>";
				$body .= "<p>".$author_name." ".__('has added a new post to the group', 'wp-symposium').":</p>";
				$body .= "<p>".stripslashes($text)."</p>";
				$body .= "<p><a href='".$url."'>".__('Go to the group post', 'wp-symposium')."...</a></p>";
				foreach ($recipients as $recipient) {
					if ( $recipient->ID != $current_user->ID) {
						if ($recipient->notify == 'on') {
							symposium_sendmail($recipient->user_email, __('New Group Post', 'wp-symposium'), $body);
						}
						if (function_exists('symposium_news_add') && $add_alerts == 'on') {
							symposium_news_add($author_uid, $recipient->ID, "<a href='".$url."'>".__("Group post:", "wp-symposium")." ".$author_name." ".__("has posted in", "wp-symposium")." ".$group_name."</a>");							
						}
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
if ($_POST['action'] == 'group_menu_wall') {

	global $wpdb, $current_user;
	
	$uid1 = $_POST['uid1'];
	$uid2 = $current_user->ID;
	$post = $_POST['post'];
	$limit_from = $_POST['limit_from'];

	$limit_count = 10; // How many new items should be shown
	
	$plugin = WP_PLUGIN_URL.'/wp-symposium';

	if (get_option('symposium_use_styles') == "on") {
		$bg_color_2 = 'background-color: '.get_option('symposium_bg_color_2');
	} else {
		$bg_color_2 = '';
	}
	
	$html = "";
	
	$profile_page = get_option('symposium_profile_url');
	if ($profile_page[strlen($profile_page)-1] != '/') { $profile_page .= '/'; }
	$q = symposium_string_query($profile_page);		

	$html .= "<div id='profile_left_column' style='";
	if (get_option('symposium_show_wall_extras') != 'on') {
		$html .= " border-left:0px;";
	}			
	$html .= "'>";		
	
		// Notification choice
		if (symposium_member_of($uid1) == "yes" && $limit_from == 0) {
			$notify = $wpdb->get_var($wpdb->prepare("SELECT notify FROM ".$wpdb->prefix . "symposium_group_members WHERE group_id = ".$uid1." AND member_id = ".$uid2));
			$html .= "<input type='checkbox' id='group_notify'";
			if ($notify == "on") { $html .= " CHECKED"; }
			$html .= "> ".__("Receive emails when there are new posts and replies", "wp-symposium");
		}
			
		// Wall
		$html .= "<div id='symposium_wall'>";
		
			// Post Comment Input
			if (is_user_logged_in() && symposium_member_of($uid1) == "yes" && $limit_from == 0) {
				$html .= '<input id="symposium_group_comment" type="text" name="post_group_comment" class="input-field" 
					onblur="this.value=(this.value==\'\') ? \''.__("Write a comment...", 'wp-symposium').'\' : this.value;" 
					onfocus="this.value=(this.value==\''.__("Write a comment...", 'wp-symposium').'\') ? \'\' : this.value;" 
					value="'.__("Write a comment...", 'wp-symposium').'"';
				if (get_option('symposium_show_buttons')) {
					$html .= ' style="width:200px"';
				}
				$html .= ' />';
				if (get_option('symposium_show_buttons')) {
					$html .= '&nbsp;<input id="symposium_group_add_comment" type="submit" style="width:75px" class="symposium-button" value="'.__('Post', 'wp-symposium').'" /> ';
				}
			}

			if ($post != '' && symposium_safe_param($post)) {
				
				// Re-act to a single post (probably from mail link)

				$sql = "SELECT c.*, u.display_name FROM ".$wpdb->base_prefix."symposium_comments c LEFT JOIN ".$wpdb->base_prefix."users u ON c.author_uid = u.ID WHERE c.cid = %d AND c.comment_parent = 0 AND c.is_group = 'on' ORDER BY c.comment_timestamp DESC LIMIT %d, %d";
				$comments = $wpdb->get_results($wpdb->prepare($sql, $post, $limit_from, $limit_count));	
				
			} else {
				
				// Show whole wall

				$sql = "SELECT c.*, u.display_name FROM ".$wpdb->base_prefix."symposium_comments c LEFT JOIN ".$wpdb->base_prefix."users u ON c.author_uid = u.ID WHERE c.comment_parent = 0 AND c.subject_uid = %d AND c.is_group = 'on' ORDER BY c.comment_timestamp DESC LIMIT %d, %d";	
				$comments = $wpdb->get_results($wpdb->prepare($sql, $uid1, $limit_from, $limit_count));	

			}
			

			if ($comments) {
				foreach ($comments as $comment) {

					$html .= "<div id='".$comment->cid."' class='wall_post_div'>";

						$html .= "<div class='wall_post_avatar' style='width:64px;'>";
							$html .= get_avatar($comment->author_uid, 64);
						$html .= "</div>";

						$html .= "<div class='wall_post_entry'>";
							$html .= "<div class='wall_post'>";
							
								if (symposium_get_current_userlevel($uid2) == 5 || $comment->author_uid == $uid2) {
									$html .= "<a title='".$comment->cid."' href='javascript:void(0);' class='delete_post delete_post_top'><img src='".get_option('symposium_images')."/delete.png' style='width:16px;height:16px' /></a>";
								}
								$html .= '<a href="'.$profile_page.$q.'uid='.$comment->author_uid.'">'.stripslashes($comment->display_name).'</a> ';
								$html .= symposium_time_ago($comment->comment_timestamp).".<br />";
								$html .= symposium_make_url(stripslashes($comment->comment));

								// Replies
								$sql = "SELECT c.*, u.display_name FROM ".$wpdb->base_prefix."symposium_comments c 
									LEFT JOIN ".$wpdb->base_prefix."users u ON c.author_uid = u.ID 
									WHERE c.comment_parent = %d ORDER BY c.cid";
									
								$replies = $wpdb->get_results($wpdb->prepare($sql, $comment->cid));	
								$count = 0;
								if ($replies) {
									if (count($replies) > 4) {
										$html .= "<div id='view_all_comments_div'>";
										$html .= "<a title='".$comment->cid."' class='view_all_comments' href='javascript:void(0);'>".__(sprintf("View all %d comments", count($replies)), "wp-symposium")."</a>";
										$html .= "</div>";
									}
									foreach ($replies as $reply) {
										$count++;
										if ($count > count($replies)-4) {
											$reply_style = "";
										} else {
											$reply_style = "display:none; ";
										}
										$html .= "<div id='".$reply->cid."' class='reply_div' style='".$reply_style."'>";
											$html .= "<div class='wall_reply_div' style='".$bg_color_2.";'>";
												$html .= "<div class='wall_reply'>";
													if (symposium_get_current_userlevel($uid2) == 5 || $reply->subject_uid == $uid2 || $reply->author_uid == $uid2) {
														$html .= "<a title='".$reply->cid."' href='javascript:void(0);' class='delete_post delete_reply'><img src='".get_option('symposium_images')."/delete.png' style='width:16px;height:16px' /></a>";
													}
													$html .= '<a href="'.$profile_page.$q.'uid='.$reply->author_uid.'">'.stripslashes($reply->display_name).'</a> ';
													$html .= symposium_time_ago($reply->comment_timestamp).".<br />";
													$html .= symposium_make_url(stripslashes($reply->comment));
												$html .= "</div>";
											$html .= "</div>";
											
											$html .= "<div class='wall_reply_avatar'>";
												$html .= get_avatar($reply->author_uid, 40);
											$html .= "</div>";		
										$html .= "</div>";
									}
								} else {
									$html .= "<div class='no_wall_replies'></div>";
								}												
								$html .= "<div style='clear:both;' id='symposium_comment_".$comment->cid."'></div>";

								// Reply field
								if (is_user_logged_in() && symposium_member_of($uid1) == "yes") {
									$html .= '<div>';
									$html .= '<input title="'.$comment->cid.'" id="symposium_reply_'.$comment->cid.'" type="text" name="wall_comment" style="float:left;';
									if (!get_option('symposium_show_buttons')) {
										$html .= 'width:230px;';
									}
									$html .= '" class="input-field reply_field" onblur="this.value=(this.value==\'\') ? \''.__("Write a comment...", 'wp-symposium').'\' : this.value;" onfocus="this.value=(this.value==\''.__("Write a comment...", 'wp-symposium').'\') ? \'\' : this.value;" value="'.__("Write a comment...", 'wp-symposium').'" />';
									$html .= '<input id="symposium_author_'.$comment->cid.'" type="hidden" value="'.$comment->author_uid.'" />';
									$html .= '</div>';
									if (get_option('symposium_show_buttons')) {
										$html .= '&nbsp;<input title="'.$comment->cid.'" id="symposium_reply_'.$comment->cid.'" type="submit" style="width:75px;" class="symposium-button reply_field-button" value="'.__('Post', 'wp-symposium').'" /> ';
									}
								}
								
							$html .= "</div>";
						$html .= "</div>";
					$html .= "</div>";
							
				}

				$html .= "<a href='javascript:void(0)' id='showmore_group_wall' title='".($limit_from+$limit_count)."'>".__("more...", "wp-symposium")."</a>";

			} else {
				$html .= "<br />".__("Nothing to show, sorry.", "wp-symposium");
			}
		
		$html .= "</div>";
			
	$html .= "</div>";

	echo $html;
	
	exit;
	
}

// Show Members
if ($_POST['action'] == 'group_menu_members') {

	global $wpdb, $current_user;
	
	$uid1 = $_POST['uid1'];
	$uid2 = $current_user->ID;
	$post = $_POST['post'];

	$plugin = WP_PLUGIN_URL.'/wp-symposium';

	$html = "";
	
	$me = $current_user->ID;
	$page = 1;
	$page_length = 25;

	$html .= "<div id='profile_left_column'>";				

		$sql = "SELECT COUNT(*) FROM ".$wpdb->prefix."symposium_group_members WHERE group_id=".$uid1;
		$member_count = $wpdb->get_var($wpdb->prepare($sql, $uid1));
		
		$html .= "<div id='group_member_count'>".__("Member Count:", "wp-symposium")." ".$member_count."</div>";
		
		$sql = "SELECT u.ID, g.admin, g.valid 
		FROM ".$wpdb->prefix."symposium_group_members g 
		RIGHT JOIN ".$wpdb->base_prefix."users u ON g.member_id = u.ID 
		WHERE u.ID > 0 AND g.group_id = %d ORDER BY g.valid DESC LIMIT ".($page*$page_length-$page_length).",".$page_length;
		
		$get_members = $wpdb->get_results($wpdb->prepare($sql, $uid1));
		
		if ($get_members) {

			$members_array = array();
			foreach ($get_members as $member) {

				$add = array (	
					'ID' => $member->ID,
					'admin' => $member->admin,
					'valid' => $member->valid,
					'last_activity' => get_symposium_meta($member->ID, 'last_activity'),
					'city' => get_symposium_meta($member->ID, 'city'),
					'country' => get_symposium_meta($member->ID, 'country'),
					'share' => get_symposium_meta($member->ID, 'share')
				);

				array_push($members_array, $add);
			}
			$members = sub_val_sort($members_array, 'last_activity', false);
			
			$inactive = get_option('symposium_online');
			$offline = get_option('symposium_offline');
			$profile = symposium_get_url('profile');
			
			$shown_pending_title = false;
			$shown_members_title = true;
			
			foreach ($members as $member) {
				
				if ($member['valid'] != "on" && $shown_pending_title == false) {
					$html .= "<br /><p><strong>".__("Awaiting approval", "wp-symposium")."</strong></p>";
					$shown_pending_title = true;
					$shown_members_title = false;					
				}
				
				if ($member['valid'] == "on" && $shown_members_title == false) {
					$html .= "<br /><p><strong>".__("Members", "wp-symposium")."</strong></p>";
				}
				
				$time_now = time();
				$last_active_minutes = strtotime($member['last_activity']);
				$last_active_minutes = floor(($time_now-$last_active_minutes)/60);
												
				$html .= "<div id='request_".$member['ID']."' class='wall_post_div members_row row_odd corners'>";		

					$html .= "<div class='members_info'>";

						// Delete icons
						if ( (symposium_get_current_userlevel() == 5 || symposium_group_admin($uid1) == "yes") && ($member['admin'] != 'on') ) {
							$html .= " <a title='".$member['ID']."' href='javascript:void(0);' style='display:none; float:right;' class='delete_group_member delete delete_post_top'><img src='".get_option('symposium_images')."/delete.png' style='width:16px;height:16px' /></a>";
						}

						if ( ($member['ID'] == $me) || (is_user_logged_in() && strtolower($member['share']) == 'everyone') || (strtolower($member['share']) == 'public') || (strtolower($member['share']) == 'friends only' && symposium_friend_of($member['ID'], $current_user->ID)) ) {
							$html .= "<div class='members_location'>";
								if ($city != '') {
									$html .= $member['city'];
								}
								if ($country != '') {
									if ($city != '') {
										$html .= ', '.$member['country'];
									} else {
										$html .= $member['country'];
									}
								}								
							$html .= "</div>";
						}
	
						$html .= "<div class='members_avatar'>";
							$html .= get_avatar($member['ID'], 64);
						$html .= "</div>";
						$html .= symposium_profile_link($member['ID']).', '.__('last active', 'wp-symposium').' '.symposium_time_ago($member['last_activity']).". ";
						if ($last_active_minutes >= $offline) {
							//$html .= '<img src="'.get_option('symposium_images').'/loggedout.gif">';
						} else {
							if ($last_active_minutes >= $inactive) {
								$html .= '<img src="'.get_option('symposium_images').'/inactive.gif">';
							} else {
								$html .= '<img src="'.get_option('symposium_images').'/online.gif">';
							}
						}
						if ($member['admin'] == "on") {
							$html .= "<br />[".__("group admin", "wp-symposium")."]";
						}
						
						// Requesting group membership...
						if ($member['valid'] != "on") {
							$html .= "<div style='clear: both; margin-bottom:15px;'>";
								$html .= "<div style='float:right;'>";
									$html .= '<input type="submit" title="'.$member['ID'].'" id="rejectgrouprequest" class="symposium-button" value="'.__('Reject', 'wp-symposium').'" /> ';
								$html .= "</div>";
								$html .= "<div style='float:right;'>";
									$html .= '<input type="submit" title="'.$member['ID'].'" id="acceptgrouprequest" class="symposium-button" value="'.__('Accept', 'wp-symposium').'" /> ';
								$html .= "</div>";
							$html .= "</div>";
						}
					$html .= "</div>";
				$html .= "</div>";
			}

		} else {
			$html .= __('No members', 'wp-symposium')."....";
		}			
			
	$html .= "</div>";
		
	echo $html;
	
	exit;
	
}

		
?>

	
