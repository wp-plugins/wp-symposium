<?php

include_once('../../../../wp-config.php');

global $wpdb, $current_user;

// Get id of a display name
if ($_POST['action'] == 'getRecipientId') {
	
	if (is_user_logged_in()) {
	
		$name = $_POST['name'];

		$sql = "SELECT u.ID FROM ".$wpdb->base_prefix."users u 
			INNER JOIN ".$wpdb->base_prefix."symposium_friends f ON u.ID = f.friend_to 
			WHERE (lower(u.display_name) = %s AND f.friend_from = %d)";

		$id = $wpdb->get_results( $wpdb->prepare($sql, strtolower($name), $current_user->ID ) );
		
		if ($id) {
			if ( count($id) > 1 ) {
				echo sprintf(__('Please select a name from the drop down list to make sure you send to the right person (%s).', 'wp_symposium'), $name );
			}
		} else {
			echo $id[0];
		}
	
	}	
	exit;
}

// Load Compose Forum
if ($_POST['action'] == 'loadComposeForm') {

	if (is_user_logged_in()) {

		$mail_to = $_POST["mail_to"];
		$recipient = $wpdb->get_var("SELECT display_name FROM ".$wpdb->base_prefix."users WHERE ID = ".$mail_to);

		echo $recipient;
	}
	exit;
}

// Load Compose Forum
if ($_POST['action'] == 'getComposeRecipients') {

	$term = strtolower($_POST['term']);
	$return_arr = array();

	$current_user_name = strtolower($current_user->display_name);
	$length = strlen($term);
	$startsWith = ( substr($current_user_name, 0, $length) === $term ) ? true : false;
	if ( (strpos($current_user_name, ' '.$term) !== FALSE) || ($startsWith) ) {
		$row_array['id'] = $current_user->ID;
		$row_array['value'] = $current_user->display_name;
		$row_array['label'] = $current_user->display_name;
		$row_array['city'] = '';
		$row_array['country'] = '';
	    array_push($return_arr,$row_array);
	}
    
	$list = $wpdb->get_results("
		SELECT u.ID, u.display_name, m.city, m.country 
		FROM ".$wpdb->base_prefix."users u 
		INNER JOIN ".$wpdb->base_prefix."symposium_usermeta m ON u.ID = m.uid 
		INNER JOIN ".$wpdb->base_prefix."symposium_friends f ON u.ID = f.friend_to 
		WHERE (
				(u.display_name LIKE '".$term."%') 
					OR (m.city LIKE '".$term."%') 
					OR (m.country LIKE '".$term."%') 
					OR (u.display_name LIKE '% %".$term."%')
				) AND (
					f.friend_from = ".$current_user->ID."
				)
		ORDER BY u.display_name");

	if ($list) {
		foreach ($list as $item) {
			$row_array['id'] = $item->ID;
			$row_array['value'] = $item->display_name;
			$row_array['label'] = $item->display_name;
			if ($item->city != '') {
				$row_array['city'] = $item->city;
			} else {
				$row_array['city'] = '';
			}
			if ($item->country != '') {
				if ($item->city != '') {
					$row_array['country'] = ', '.$item->country;
				} else {
					$row_array['country'] = $item->country;
				}
			} else {
				$row_array['country'] = '';
			}
		
	        array_push($return_arr,$row_array);
		}
	}

	echo json_encode($return_arr);
	
}

// Get reply info
if ($_POST['action'] == 'getReply') {

	if (is_user_logged_in()) {

		$mid = $_POST["mail_id"];
		$recipient_id = $_POST["recipient_id"];

		$recipient = $wpdb->get_var("SELECT display_name FROM ".$wpdb->base_prefix."users WHERE ID = ".$recipient_id);
		$mail_message = $wpdb->get_row("SELECT m.*, u.display_name FROM ".$wpdb->base_prefix."symposium_mail m LEFT JOIN ".$wpdb->base_prefix."users u ON m.mail_from = u.ID WHERE mail_mid = ".$mid);
	
		$subject = strip_tags($mail_message->mail_subject);
		if (substr($subject, 0, 4) != "Re: ") {
			$subject = "Re: ".$subject;
		}
		$message = stripslashes($mail_message->mail_message);
	
		$header = chr(13)."--------------------------".chr(13);
		$header .= "From: ".stripslashes($mail_message->display_name).chr(13);
		$header .= "Sent: ".$mail_message->mail_sent.chr(13);
		$header .= "Subject: ".stripslashes($mail_message->mail_subject).chr(13).chr(13);
	
		$message = $header.$message;
	
		// return recipent (name), subject and message as JSON
		$return_arr = array();
		$row_array['recipient_id'] = $recipient_id;
		$row_array['recipient'] = $recipient;
		$row_array['subject'] = $subject;
		$row_array['message'] = $message;
	    array_push($return_arr,$row_array);
	
		echo json_encode($return_arr);
		
	}
	exit;
			
}

// Delete mail
if ($_POST['action'] == 'deleteMail') {

	if (is_user_logged_in()) {

		$mid = $_POST["mid"];
		$tray = $_POST["tray"];
		
		if ($tray == "in") {
			if ($wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->base_prefix."symposium_mail SET mail_in_deleted = 'on' WHERE mail_mid = ".$mid." AND mail_to = ".$current_user->ID) )) {
				echo __("Message deleted.", "wp-symposium");
			} else {
				echo __("Failed to delete message: ".$wpdb->last_query, "wp-symposium");				
			}
		}
		if ($tray == "sent") {
			if ($wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->base_prefix."symposium_mail SET mail_sent_deleted = 'on' WHERE mail_mid = ".$mid." AND mail_from = ".$current_user->ID) )) {
				echo __("Message deleted.", "wp-symposium");
			} else {
				echo __("Failed to delete message: ".$wpdb->last_query, "wp-symposium");				
			}
		}
		

	}
	
}


// Send mail
if ($_POST['action'] == 'sendMail') {

	if (is_user_logged_in()) {

		$compose_recipient_id = $_POST["compose_recipient_id"];
		
		$return = "Problem, not sure what, sorry.";
	
		$recipient = $wpdb->get_row("SELECT * FROM ".$wpdb->base_prefix."users WHERE ID = '".$compose_recipient_id."'");
		if (!$recipient) {
			$return = 'Recipient could not be found, sorry.';
		} else {
			$subject = strip_tags($_POST['compose_subject']);
			$message = $_POST['compose_text'];
			$previous = $_POST['compose_previous'];
		
			$message = $message.$previous;
		
			// Send mail
			if ( $rows_affected = $wpdb->prepare( $wpdb->insert( $wpdb->base_prefix . "symposium_mail", array( 
			'mail_from' => $current_user->ID, 
			'mail_to' => $recipient->ID, 
			'mail_sent' => date("Y-m-d H:i:s"), 
			'mail_subject' => $subject,
			'mail_message' => $message
			 ) ) ) ) {
				$return = __('Message sent to', 'wp-symposium').' '.$recipient->display_name;
			 } else {
				$return = '<p><strong>'.__('There was a problem sending your mail to', 'wp-symposium').' '.$recipient->display_name.'.</strong></p>';
			 }

			$mail_id = $wpdb->insert_id;
			// Filter to allow further actions to take place
			apply_filters ('symposium_sendmessage_filter', $recipient->ID, $current_user->ID, $current_user->display_name, $mail_id);
		
			// Send real email if chosen
			if ( get_symposium_meta($recipient->ID, 'notify_new_messages') ) {

				$url = symposium_get_url('mail');

				$body = "<h1>".$subject."</h1>";
				$body .= "<p><a href='".$url.symposium_string_query($url)."mid=".$mail_id."'>".__(sprintf("Go to %s Mail", symposium_get_url('mail')), "wp-symposium")."...</a></p>";
				$body .= "<p>";
				$body .= $message;
				$body .= "</p>";
				$body .= "<p><em>";
				$body .= $current_user->display_name;
				$body .= "</em></p>";
				$body .= $previous;
			
				$body = str_replace(chr(13), "<br />", $body);
				$body = str_replace("\\r\\n", "<br />", $body);
				$body = str_replace("\\", "", $body);

				if ( symposium_sendmail($recipient->user_email, __('New Mail Message', 'wp-symposium'), $body) ) {
					// email sent ok.
				} else {
					$return .= '<p><strong>'.__('There was a problem sending an email notification to', 'wp-symposium').' '.$recipient->user_email.'.</strong></p>';
				}
			}

		}
		
		echo $return;
	}
}

// Get mail messages
if ($_POST['action'] == 'getBox') {

	if (is_user_logged_in()) {
	
		$tray = $_POST["tray"];
		$term = $_POST["term"];
		
		$start = 0;
		if (isset($_POST['start'])) { $start = $_POST["start"]; }
		$length = 5;
		if (isset($_POST['length'])) { $length = $_POST["length"]; }

		if ($tray == "in") {
			$sql = "SELECT m.*, u.display_name FROM ".$wpdb->base_prefix."symposium_mail m LEFT JOIN ".$wpdb->base_prefix."users u ON m.mail_from = u.ID WHERE mail_in_deleted != 'on' AND mail_to = ".$current_user->ID." AND (u.display_name LIKE '%".$term."%' OR mail_subject LIKE '%".$term."%' OR mail_message LIKE '%".$term."%') ORDER BY mail_mid DESC LIMIT ".$start.",".$length;
		} else {
			$sql = "SELECT m.*, u.display_name FROM ".$wpdb->base_prefix."symposium_mail m LEFT JOIN ".$wpdb->base_prefix."users u ON m.mail_to = u.ID WHERE mail_sent_deleted != 'on' AND mail_from = ".$current_user->ID." AND (u.display_name LIKE '%".$term."%' OR mail_subject LIKE '%".$term."%' OR mail_message LIKE '%".$term."%') ORDER BY mail_mid DESC LIMIT ".$start.",".$length;
		}

		$mail = $wpdb->get_results($sql);
		$sql = $wpdb->last_query;
		
		$return_arr = array();	

		$row_array['next_message_id'] = $start+$length;
        array_push($return_arr,$row_array);

		if ($mail) {
			foreach ($mail as $item)
			{
				if ($item->mail_read != "on") {
					$row_array['mail_read'] = "row";
				} else {
					$row_array['mail_read'] = "row_odd";
				}
				$row_array['mail_mid'] = $item->mail_mid;
				$row_array['mail_sent'] = symposium_time_ago($item->mail_sent);
				if ($tray == "in") {
					$row_array['mail_from'] = stripslashes(symposium_profile_link($item->mail_from));
				} else {
					$row_array['mail_from'] = stripslashes(symposium_profile_link($item->mail_to));
				}
				$row_array['mail_subject'] = symposium_bbcode_remove($item->mail_subject);
				$row_array['mail_subject'] = preg_replace(
				  "/(>|^)([^<]+)(?=<|$)/iesx",
				  "'\\1' . str_replace('" . $term . "', '<span class=\"symposium_search_highlight\">" . $term . "</span>', '\\2')",
				  $row_array['mail_subject']
				);
				$row_array['mail_subject'] = stripslashes($row_array['mail_subject']);
				$message = stripslashes($item->mail_message);
				if ( strlen($message) > 75 ) { $message = substr($message, 0, 75)."..."; }
				$message = preg_replace(
				  "/(>|^)([^<]+)(?=<|$)/iesx",
				  "'\\1' . str_replace('" . $term . "', '<span class=\"symposium_search_highlight\">" . $term . "</span>', '\\2')",
				  $message
				);
				$row_array['message'] = stripslashes(symposium_bbcode_remove($message));

		        array_push($return_arr,$row_array);

			}
		} else {
			//$row_array['nomore'] = $sql;
	        array_push($return_arr,$row_array);			
		}

		echo json_encode($return_arr);
		
	}

}
				
// Get single mail message
if ($_POST['action'] == 'getMailMessage') {

	if (is_user_logged_in()) {
	
		$mail_mid = $_POST['mid'];	
		$tray = $_POST['tray'];	

		if ($tray == "in") {
			$mail = $wpdb->get_row("SELECT m.*, u.display_name FROM ".$wpdb->prefix."symposium_mail m LEFT JOIN ".$wpdb->base_prefix."users u ON m.mail_from = u.ID WHERE mail_mid = ".$mail_mid);
		} else {
			$mail = $wpdb->get_row("SELECT m.*, u.display_name FROM ".$wpdb->prefix."symposium_mail m LEFT JOIN ".$wpdb->base_prefix."users u ON m.mail_to = u.ID WHERE mail_mid = ".$mail_mid);
		}
		
		// check that permission is okay
		if ( ($tray == "in" && $mail->mail_to == $current_user->ID) || ($tray != "in" && $mail->mail_from == $current_user->ID) ) {
			
			$template = WPS_TEMPLATE_MAIL_MESSAGE;
			$template = str_replace("[]", "", stripslashes($template));
			
			// Swap codes from template
			$msg = $template;
	
			// First the avatar
			if (strpos($msg, '[avatar') !== FALSE) {
	
				if ($tray == "in") {
					$uid = $mail->mail_from;
				} else {
					$uid = $mail->mail_to;
				}
	
				if (strpos($msg, '[avatar]')) {
					$msg = str_replace("[avatar]", get_avatar($uid, 44), $msg);						
				} else {
					$x = strpos($msg, '[avatar');
					$avatar = substr($msg, 0, $x);
					$avatar2 = substr($msg, $x+8, 2);
					$avatar3 = substr($msg, $x+11, strlen($msg)-$x-11);
									
					$msg = $avatar . get_avatar($uid, $avatar2) . $avatar3;				
				}
			}
	
			// Now the subject and sender
			$mail_style = "";
			if (WPS_USE_STYLES == "on") {
				$mail_style = "style='font-family:".WPS_HEADINGSFAMILY."; font-size:".WPS_HEADINGSSIZE."px; font-weight:bold;'";
			}
			$msg = str_replace("[mail_subject]", "<span ".$mail_style.">".stripslashes(symposium_bbcode_replace($mail->mail_subject))."</span>", $msg);						
			
			// Sender/recipient
			if ($tray == "in") {
				$msg = str_replace("[mail_recipient]", __('From', 'wp-symposium')." ".stripslashes($mail->display_name), $msg);
			} else {
				$msg = str_replace("[mail_recipient]", __('To', 'wp-symposium')." ".stripslashes($mail->display_name), $msg);
			}
	
			// Sent
			$msg = str_replace("[mail_sent]", symposium_time_ago($mail->mail_sent), $msg);
	
			// Delete button
			$msg = str_replace("[delete_button]", '<input type="submit" id='.$mail_mid.' class="symposium-button message_delete" value="'.__('Delete', 'wp-symposium').'" />', $msg);
	
			// Reply button
			if ($tray == 'in') {
				$msg = str_replace("[reply_button]", '<input type="submit" id='.$mail->mail_from.' title='.$mail_mid.' class="symposium-button message_reply" value="'.__('Reply', 'wp-symposium').'" />', $msg);
			} else {
				$msg = str_replace("[reply_button]", '<input type="submit" id='.$mail->mail_to.' title='.$mail_mid.' class="symposium-button message_reply" value="'.__('Reply', 'wp-symposium').'" />', $msg);
			}
	
			// Message
			$msg = str_replace("[message]", stripslashes(symposium_bbcode_replace($mail->mail_message)), $msg);
			
			// Emoticons
			$msg = symposium_smilies($msg);
			
			// Layout for HTML
			$msg = str_replace(chr(10), "<br />", $msg);
				
			// Mark as read
			if ($tray == "in") {
				$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix."symposium_mail SET mail_read = 'on' WHERE mail_mid = ".$mail_mid." AND mail_to = ".$current_user->ID) );
			}
	
			// Fetch new unread count
			$unread = "?!";
			if ($tray == "in") {
				$unread = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix.'symposium_mail'." WHERE mail_to = ".$current_user->ID." AND mail_".$tray."_deleted != 'on' AND mail_read != 'on'");
			} else {
				$unread = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix.'symposium_mail'." WHERE mail_from = ".$current_user->ID." AND mail_".$tray."_deleted != 'on' AND mail_read != 'on'");
			}
	
			echo $mail_mid."[split]".$unread."[split]".$tray."[split]".$msg;
			
		}
		exit;

	}
}


?>

	