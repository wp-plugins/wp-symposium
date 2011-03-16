<?php

include_once('../../../../wp-config.php');

global $wpdb, $current_user;

// Compose - recipients list (autocomplete)
if (isset($_GET['term']) && $_GET['term'] != '') {
	
	$return_arr = array();
	$term = $_GET['term'];

	$list = $wpdb->get_results("SELECT u.ID, u.display_name, m.city, m.country FROM ".$wpdb->base_prefix."users u LEFT JOIN ".$wpdb->base_prefix."symposium_usermeta m ON u.ID = m.uid WHERE (u.display_name LIKE '".$term."%') OR (m.city LIKE '".$term."%') OR (m.country LIKE '".$term."%') OR (u.display_name LIKE '% %".$term."%') ORDER BY u.display_name");

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
	exit;

}

// Get reply info
if ($_POST['action'] == 'getReply') {

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
	$row_array['recipient'] = $recipient;
	$row_array['subject'] = $subject;
	$row_array['message'] = $message;
    array_push($return_arr,$row_array);
	
	echo json_encode($return_arr);
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

		$recipient_name = $_POST["compose_recipient"];
		
		$return = "Problem, not sure what, sorry.";
	
		$recipient = $wpdb->get_row("SELECT * FROM ".$wpdb->base_prefix."users WHERE lower(display_name) = '".strtolower($recipient_name)."'");
		if (!$recipient) {
			$return = $recipient_name.' could not be found.';
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
				$return = __('Message sent to', 'wp-symposium').' '.$recipient_name.'.';
			 } else {
				$return = '<p><strong>'.__('There was a problem sending your mail to', 'wp-symposium').' '.$recipient_name.'.</strong></p>';
			 }

			// Add notification
			$msg = '<a href="'.symposium_get_url('mail').'">You have a new mail message from '.$current_user->display_name.'...</a>';
			symposium_add_notification($msg, $recipient->ID);
		
			// Send real email if chosen
			if ( get_symposium_meta($recipient->ID, 'notify_new_messages') ) {

				$body = "<h1>".$subject."</h1>";
				$body .= "<p><a href='".symposium_get_url('mail')."'>".__(sprintf("Go to %s Mail", get_bloginfo("name")), "wp-symposium")."...</a></p>";
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
	
	$tray = $_POST["tray"];
	$term = $_POST["term"];

	if ($tray == "in") {
		$mail = $wpdb->get_results("SELECT m.*, u.display_name FROM ".$wpdb->base_prefix."symposium_mail m LEFT JOIN ".$wpdb->base_prefix."users u ON m.mail_from = u.ID WHERE mail_in_deleted != 'on' AND mail_to = ".$current_user->ID." AND (u.display_name LIKE '%".$term."%' OR mail_subject LIKE '%".$term."%' OR mail_message LIKE '%".$term."%') ORDER BY mail_mid DESC LIMIT 0,30");
	} else {
		$mail = $wpdb->get_results("SELECT m.*, u.display_name FROM ".$wpdb->base_prefix."symposium_mail m LEFT JOIN ".$wpdb->base_prefix."users u ON m.mail_to = u.ID WHERE mail_sent_deleted != 'on' AND mail_from = ".$current_user->ID." AND (u.display_name LIKE '%".$term."%' OR mail_subject LIKE '%".$term."%' OR mail_message LIKE '%".$term."%') ORDER BY mail_mid DESC LIMIT 0,30");
	}

	$return_arr = array();	

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
			$row_array['mail_from'] = stripslashes(symposium_profile_link($item->mail_from));
			$row_array['mail_subject'] = stripslashes(symposium_bbcode_remove($item->mail_subject));
			$row_array['mail_subject'] = preg_replace(
			  "/(>|^)([^<]+)(?=<|$)/iesx",
			  "'\\1' . str_replace('" . $term . "', '<span class=\"symposium_search_highlight\">" . $term . "</span>', '\\2')",
			  $row_array['mail_subject']
			);
			$message = stripslashes($item->mail_message);
			if ( strlen($message) > 75 ) { $message = substr($message, 0, 75)."..."; }
			$message = preg_replace(
			  "/(>|^)([^<]+)(?=<|$)/iesx",
			  "'\\1' . str_replace('" . $term . "', '<span class=\"symposium_search_highlight\">" . $term . "</span>', '\\2')",
			  $message
			);
			$row_array['message'] = symposium_bbcode_remove($message);

	        array_push($return_arr,$row_array);

		}
	}

	echo json_encode($return_arr);

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

		$styles = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."symposium_config");

		// Mail message header
		$msg = "<div id='message_header'>";

			// Show avatar
			$msg .= "<div id='message_header_avatar'>";
				$msg .= get_avatar($mail->mail_from, 44);
			$msg .= "</div>";

			// Get styles
			$mail_style = "";
			if ($styles->use_styles == "on") {
				$mail_style = "style='font-family:".$styles->headingsfamily."; font-size:".$styles->headingssize."px; font-weight:bold;'";
			}
			// Sender/recipient
			$msg .= "<span ".$mail_style.">".stripslashes(symposium_bbcode_replace($mail->mail_subject))."</span><br />";
			if ($tray == "in") {
				$msg .= __('From', 'wp-symposium')." ";
			} else {
				$msg .= __('To', 'wp-symposium')." ";
			}
			$msg .= stripslashes($mail->display_name)." ".symposium_time_ago($mail->mail_sent).".<br />";

		$msg .= "</div>";

		// Delete
		$msg .= "<div id='message_header_delete'>";
		$msg .= '<input type="submit" id='.$mail_mid.' class="symposium-button message_delete" value="'.__('Delete', 'wp-symposium').'" />';
		$msg .= "</div>";

		// Reply
		if ($tray == "in") {
			$msg .= "<div id='message_header_reply'>";
			$msg .= '<input type="submit" id='.$mail->mail_from.' title='.$mail_mid.' class="symposium-button message_reply" value="'.__('Reply', 'wp-symposium').'" />';
			$msg .= "</div>";
		}

		// Mail message body
		$msg .= "<div id='message_mail_message'>";
		$msg .= stripslashes(str_replace('chr(13)', "<br />", symposium_bbcode_replace($mail->mail_message)));
		$msg .= "</div>";


		// Mark as read
		if ($tray == "in") {
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix."symposium_mail SET mail_read = 'on' WHERE mail_mid = ".$mail_mid." AND mail_to = ".$current_user->ID) );
		}

		$msg = symposium_smilies($msg);
		
		$msg = str_replace(chr(10), "<br />", $msg);
			
		// Fetch new unread count
		if ($tray == "in") {
			$unread = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix.'symposium_mail'." WHERE mail_to = ".$current_user->ID." AND mail_".$tray."_deleted != 'on' AND mail_read != 'on'");
		} else {
			$unread = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix.'symposium_mail'." WHERE mail_from = ".$current_user->ID." AND mail_".$tray."_deleted != 'on' AND mail_read != 'on'");
		}

		echo $mail_mid."[split]".$unread."[split]".$tray."[split]".$msg;
		exit;

	}
}


?>

	