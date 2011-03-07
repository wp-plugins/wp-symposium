<?php
/*
Plugin Name: WP Symposium Mail
Plugin URI: http://www.wpsymposium.com
Description: Mail component for the Symposium suite of plug-ins. Put [symposium-mail] on any WordPress page.
Version: 0.46.1
Author: WP Symposium
Author URI: http://www.wpsymposium.com
License: GPL3
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

function symposium_mail() {	
	
	global $wpdb, $current_user;
	wp_get_current_user();

	$thispage = get_permalink();
	if ($thispage[strlen($thispage)-1] != '/') { $thispage .= '/'; }
	$mail_url = $wpdb->get_var($wpdb->prepare("SELECT mail_url FROM ".$wpdb->prefix . 'symposium_config'));

	if (isset($_GET[page_id]) && $_GET[page_id] != '') {
		// No Permalink
		$thispage = $mail_url;
		$q = "&";
	} else {
		$q = "?";
	}
	
	$plugin_dir = WP_PLUGIN_URL.'/wp-symposium/';
	
	$html = "";

	// Includes
	//include_once('symposium_styles.php');
	//include_once('symposium_functions.php');


	/* ================================================================================================================== */

	if (is_user_logged_in()) {

		// Count unread mail in inbox to help decide which is default tab
		$unread_in = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->base_prefix.'symposium_mail'." WHERE mail_to = ".$current_user->ID." AND mail_in_deleted != 'on' AND mail_read != 'on'");
	
		// View (and set tabs)
		$inbox_active = 'inactive';
		$sent_active = 'inactive';
		$compose_active = 'active';
		$view = "compose";
		if ($_GET['view'] == 'sent') {
			$inbox_active = 'inactive';
			$sent_active = 'active';
			$compose_active = 'inactive';
			$view = "sent";
		} 
		if ( !isset($_GET['view']) || ($_GET['view'] == "in") || (isset($_POST['compose_recipient'])) ) {
			$inbox_active = 'active';
			$sent_active = 'inactive';
			$compose_active = 'inactive';
			$view = "in";
		} 
		if (isset($_POST['reply_recipient'])) {
			$inbox_active = 'inactive';
			$sent_active = 'inactive';
			$compose_active = 'active';
			$view = "compose";
		} 
			
		$user_level = symposium_get_current_userlevel();
		
		// Act upon any actions
		if (isset($_POST['delin'])) {
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->base_prefix."symposium_mail SET mail_in_deleted = 'on' WHERE mail_mid = ".$_POST['delin']." AND mail_to = ".$current_user->ID) );
		}
		if (isset($_POST['delsent'])) {
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->base_prefix."symposium_mail SET mail_sent_deleted = 'on' WHERE mail_mid = ".$_POST['delsent']." AND mail_from = ".$current_user->ID) );
		}
		
		// Has a new mail been sent
		if (isset($_POST['compose_recipient'])) {
			
			$recipient_name = $_POST['compose_recipient'];
			$recipient = $wpdb->get_row("SELECT * FROM ".$wpdb->base_prefix."users WHERE lower(display_name) = '".strtolower($recipient_name)."'");
			if (!$recipient) {
				$mail_sent_result = $recipient_name.' could not be found.';
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
					$mail_sent_result = __('Message sent to', 'wp-symposium').' '.$recipient_name.'.';
				 } else {
					$mail_sent_result = '<p><strong>'.__('There was a problem sending your mail to', 'wp-symposium').' '.$recipient_name.'.</strong></p>';
				 }
		
				// Add notification
				$msg = '<a href="'.symposium_get_url('mail').'">You have a new mail message from '.$current_user->display_name.'...</a>';
				symposium_add_notification($msg, $recipient->ID);
				
				// Send real email if chosen
				if ( get_symposium_meta($recipient->ID, 'notify_new_messages') ) {

					$body = "<h1>".$subject."</h1>";
					$body .= "<p><a href='".symposium_get_url('mail')."'>".__(sprintf("Go to %s Mail", get_bloginfo("name")), "wp-symposium")."...</a></p>";
					$body .= "<p>";
					$body .= $_POST['compose_text'];
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
						$mail_sent_result .= '<p><strong>'.__('There was a problem sending an email notification to', 'wp-symposium').' '.$recipient->user_email.'.</strong></p>';
					}
				}

			}
			$view = "result";
		}

		// Get mail id worked out with default message before tabs to include correct unread count
		$show = $_GET['show'];
		if (!isset($_GET['show'])) {
			if ($view == "in" || $view == "result") {
				$show = $wpdb->get_var("SELECT mail_mid FROM ".$wpdb->base_prefix."symposium_mail WHERE mail_in_deleted != 'on' AND mail_to = ".$current_user->ID." ORDER BY mail_mid DESC LIMIT 0,1");
			} else {
				$show = $wpdb->get_var("SELECT mail_mid FROM ".$wpdb->base_prefix."symposium_mail WHERE mail_sent_deleted != 'on' AND mail_from = ".$current_user->ID." ORDER BY mail_mid DESC LIMIT 0,1");
			}
		}
		if ($show > 0) { $message_html .= get_message($show, $view); } else { $message_html .= "&nbsp;"; }

		// Re-count unread mail (in case previously deleted)
		$unread_in = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->base_prefix.'symposium_mail'." WHERE mail_to = ".$current_user->ID." AND mail_in_deleted != 'on' AND mail_read != 'on'");
		$unread_sent = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->base_prefix.'symposium_mail'." WHERE mail_from = ".$current_user->ID." AND mail_sent_deleted != 'on' AND mail_read != 'on'");
		if ($unread_in > 0) { $unread_in_show = " (".$unread_in.")"; } else { $unread_in_show = ""; }

		$html .= '<div class="symposium-wrapper">';
						
			$html .= '<div id="mail_tabs">';
			$html .= '<div class="mail_tab nav-tab-'.$compose_active.'"><a href="'.$thispage.$q.'view=compose" class="nav-tab-'.$compose_active.'-link">'.__('Compose', 'wp-symposium').'</a></div>';
			$html .= '<div class="mail_tab nav-tab-'.$inbox_active.'"><a href="'.$thispage.$q.'view=in" class="nav-tab-'.$inbox_active.'-link">'.__('In Box', 'wp-symposium').' <span id="incount">'.$unread_in_show.'</span></a></div>';
			$html .= '<div class="mail_tab nav-tab-'.$sent_active.'"><a href="'.$thispage.$q.'view=sent" class="nav-tab-'.$sent_active.'-link">'.__('Sent Items', 'wp-symposium').'</a></div>';
			$html .= '</div>';
			
			$html .= '<div id="mail-main">';
			
			// Show result
			if ($view == "result") {
				
				$html .= '<div id="mail_sent" style="padding:10px; width:80%; text-align:center; margin:6px auto;">'.$mail_sent_result.'</div>';
				$view = "in";
				
			}
			
			// COMPOSE
			if ($view == "compose") {
				
				$recipient_id = '';
				if (isset($_POST['reply_recipient'])) { $recipient_id = $_POST['reply_recipient']; }
				if (isset($_GET['to'])) { $recipient_id = $_GET['to']; }
				
				if ($recipient_id != '') {
					$recipient = $wpdb->get_var("SELECT display_name FROM ".$wpdb->base_prefix."users WHERE ID = ".$recipient_id);
					
					if (isset($_POST['reply_mid'])) {
						$mail_message = $wpdb->get_row("SELECT m.*, u.display_name FROM ".$wpdb->base_prefix."symposium_mail m LEFT JOIN ".$wpdb->base_prefix."users u ON m.mail_from = u.ID WHERE mail_mid = ".$_POST['reply_mid']);
						
						$subject = strip_tags($mail_message->mail_subject);
						if (substr($subject, 0, 4) != "Re: ") {
							$subject = "Re: ".$subject;
						}
						$message = stripslashes($mail_message->mail_message);
						
						$header = chr(13).chr(13).chr(13)."--------------------------".chr(13);
						$header .= "From: ".stripslashes($mail_message->display_name).chr(13);
						$header .= "Sent: ".$mail_message->mail_sent.chr(13);
						$header .= "Subject: ".stripslashes($mail_message->mail_subject).chr(13).chr(13);
						
						$message = $header.$message;
					} else {
						$subject = '';
						$message = '';
					}
					
				} else {
					$recipient = '';
					$subject = '';
					$message = '';
				}
				
  				$html .= '<form method="post" action="">';

					$html .= '<div class="floatright send_button">';
					$html .= '<input id="mail_send_button" type="submit" class="symposium-button" style="height:48px" value="'.__('Send', 'wp-symposium').'" />';
					$html .= '</div>';
	
					$html .= '<div id="compose_mail_to">';
					$html .= '<div class="new-topic-subject label">'.__('Start typing the Display Name of the member you are sending to...', 'wp-symposium').'</div>';
	  					$html .= "<input type='text' id='compose_recipient' name='compose_recipient' class='new-topic-subject-input' style='width:75%' value='".htmlentities(stripslashes($recipient), ENT_QUOTES)."'/>";
	  				$html .= '</div>';
	  					
					$html .= '<div id="compose_mail_subject">';
					$html .= '<div class="new-topic-subject label">'.__('Subject', 'wp-symposium').'</div>';
	  					$html .= "<input type='text' name='compose_subject' class='new-topic-subject-input compose_subject' value='".htmlentities(stripslashes($subject), ENT_QUOTES)."' />";
	  				$html .= '</div>';
	  					
					$html .= '<div id="compose_mail_message">';
					$html .= '<div class="new-topic-subject label">'.__('Message', 'wp-symposium').'</div>';
					$html .= '<textarea class="reply-topic-subject-text compose_text" name="compose_text"></textarea>';
	  				$html .= '</div>';

  					if ($message != '') {
						$html .= '<div class="new-topic-subject label">'.__('Previous Message...', 'wp-symposium').'</div>';
  						$html .= str_replace(chr(13), "<br />", symposium_bbcode_replace($message));
  					}
  					
					$html .= '<input type="hidden" name="compose_previous" value="'.str_replace("\"", "&quot;", $message).'" />';

  				$html .= '</form>';
				
			} // End of Compose


			// IN BOX
			if ($view == "in") {
				
				$html .= "<div class='style='width:100%; padding:0px; border: 0px; border:1px solid red;'>";

					// Message
					$html .= "<div style='float: left; width: 100%;'>";
					$html .= "<div id='in_message' style='margin-left: 325px;'>";
					$html .= $message_html;
					$html .= "</div></div>";
					
					// Get list of inbox messages
					$html .= "<div id='mailbox'>";
					
						$html .= "<input id='search_inbox' type='text' style='width: 160px'>";
						$html .= "<input id='search_inbox_go' class='symposium-button' type='submit' style='width: 70px; margin-left:10px;' value='Search'>";
						
						$html .= "<div id='mailbox_list'><img src='".WP_PLUGIN_URL."/wp-symposium/images/busy.gif' /></div>";
						
					$html .= "</div>";
				
				$html .= "</div>";
				
			} // End of Inbox

			// SENT BOX
			if ($view == "sent") {
				
				$show = $_GET['show'];
				if (!isset($_GET['show'])) {
					$show = $wpdb->get_var("SELECT mail_mid FROM ".$wpdb->base_prefix."symposium_mail WHERE mail_sent_deleted != 'on' AND mail_from = ".$current_user->ID." ORDER BY mail_mid DESC LIMIT 0,1");
				}
				
				$html .= "<div class='style='width:100%; padding:0px; border: 0px;'>";

				// Message
				$html .= "<div style='float: left; width: 100%;'>";
				$html .= "<div id='sent_message' style='margin-left: 325px;'>";
				$html .= $message_html;
				$html .= "</div></div>";
				
				// Get list of sent messages
				$html .= "<div id='mailbox'>";
				$mail = $wpdb->get_results("SELECT m.*, u.display_name FROM ".$wpdb->base_prefix."symposium_mail m LEFT JOIN ".$wpdb->base_prefix."users u ON m.mail_to = u.ID WHERE mail_sent_deleted != 'on' AND mail_from = ".$current_user->ID." ORDER BY mail_mid DESC");

				if ($mail) {
					foreach ($mail as $item)
					{
						if ($item->mail_read != "on") {
							$row_bg = "row";
						} else {
							$row_bg = "row_odd";
						}
								
						$html .= "<div id='".$item->mail_mid."' class='mail_item ".$row_bg."'>";
						$html .= "<div class='mail_item_age'>";
						$html .= symposium_time_ago($item->mail_sent);
						$html .= "</div>";
						$html .= "<strong>".stripslashes(symposium_profile_link($item->mail_to))."</strong><br />";
						$html .= "<span class='mailbox_message_subject'>".stripslashes(symposium_bbcode_remove($item->mail_subject))."</span><br />";
						$message = stripslashes($item->mail_message);
						$message = symposium_bbcode_remove($message);
						if ( strlen($message) > 75 ) { $message = substr($message, 0, 75)."..."; }
						$html .= "<span class='mailbox_message'>".$message."</span>";
						$html .= "</div>";
					}
				} else {
					$html .= "<p>".__('You have no sent mail', 'wp-symposium').".</p>";
				}
				$html .= "</div>";
				
				$html .= "</div>";

			} // End of Sent
						
			$html .= '</div>'; 

			 
		$html .= '</div>'; // End of Wrapper
		
	} else {
		// Not logged in
		$html .= __('You have to login to access your mail.', 'wp-symposium');
	}
	
	// Send HTML
	return $html;

}

/* ====================================================== SET SHORTCODE ====================================================== */

if (!is_admin()) {
	add_shortcode('symposium-mail', 'symposium_mail');  
}



?>
