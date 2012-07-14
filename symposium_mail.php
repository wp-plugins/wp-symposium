<?php
/*
Plugin Name: WP Symposium Mail
Plugin URI: http://www.wpsymposium.com
Description: Mail component for the Symposium suite of plug-ins. Put [symposium-mail] on any WordPress page.
Version: 12.07.14
Author: WP Symposium
Author URI: http://www.wpsymposium.com
License: GPL3
*/
	
/*  Copyright 2010,2011,2012  Simon Goodchild  (info@wpsymposium.com)

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
	$mail_url = get_option('symposium_mail_url');
	$mail_all = get_option('symposium_mail_all');

	if (isset($_GET['page_id']) && $_GET['page_id'] != '') {
		// No Permalink
		$thispage = $mail_url;
		$q = "&";
	} else {
		$q = "?";
	}
	
	$plugin_dir = WP_PLUGIN_URL.'/wp-symposium/';
	
	$html = '';
	
	if (is_user_logged_in()) {

		$inbox_active = 'active';
		$sent_active = 'inactive';
		$compose_active = 'inactive';

		$template = '';
		$template .= '<div id="mail_tabs">';
		$template .= '<div id="symposium_compose_tab" class="mail_tab nav-tab-'.$compose_active.'"><a href="javascript:void(0)" class="nav-tab-'.$compose_active.'-link" style="text-decoration:none !important;">'.__('Compose', 'wp-symposium').'</a></div>';
		$template .= '<div id="symposium_inbox_tab" class="mail_tab nav-tab-'.$inbox_active.'"><a href="javascript:void(0)" class="nav-tab-'.$inbox_active.'-link" style="text-decoration:none !important;">'.__('In Box', 'wp-symposium').' <span id="in_unread"></span></a></div>';
		$template .= '<div id="symposium_sent_tab" class="mail_tab nav-tab-'.$sent_active.'"><a href="javascript:void(0)" class="nav-tab-'.$sent_active.'-link" style="text-decoration:none !important;">'.__('Sent Items', 'wp-symposium').'</a></div>';
		$template .= '</div>';	
		
		$template .= '<div id="mail-main-div">';

			$template .= "<div id='mail_sent_message'></div>";
		
			$template .= "[compose_form]";

			$template .= "<div id='mailbox'>";
			$template .= "<div id='symposium_search'>";
			$template .= "<input id='search_inbox' type='text' style='width: 160px'>";
			$template .= "<input id='search_inbox_go' class='symposium-button message_search' type='submit' style='margin-left:10px;' value='Search'>";
			$template .= "[unread]";
			$template .= "</div>";
			$template .= "<div id='mailbox_list'></div>";
			$template .= "<div id='messagebox'></div>";
			$template .= "</div>";
		
		$template .= '</div>';	
		
		$html .= '<div id="next_message_id" style="display:none">0</div>';
		$html .= '<div class="symposium-wrapper">'.$template.'</div>';
			
		// Compose Form	
		if (WPS_CURRENT_USER_PAGE == $current_user->ID) {
		
			$compose = '<div id="compose_form" style="display:none">';
			
				$compose .= '<div id="compose_mail_to">';

					$compose .= '<div class="send_button" style="padding:4px;">';
					$compose .= '<input type="submit" id="mail_cancel_button" class="symposium-button" value="'.__('Cancel', 'wp-symposium').'" />';
					$compose .= '<input type="submit" id="mail_send_button" style="float:right;margin:0;" class="symposium-button" value="'.__('Send', 'wp-symposium').'" />';
					$compose .= '</div>';
	 	
					$compose .= '<select id="mail_recipient_list">';
					$compose .= '<option class="mail_recipient_list_option" value='.$current_user->ID.'>'.$current_user->display_name.'</option>';
	
					if ($mail_all == 'on') {
						
						$sql = "SELECT u.ID AS friend_to, u.display_name
						FROM ".$wpdb->base_prefix."users u 
						ORDER BY u.display_name";
					
					} else {
						
						$sql = "SELECT f.friend_to, u.display_name
						FROM ".$wpdb->base_prefix."symposium_friends f 
						INNER JOIN ".$wpdb->base_prefix."users u ON f.friend_to = u.ID 
						WHERE f.friend_from = %d AND f.friend_accepted = 'on' 
						ORDER BY u.display_name";
					}
					
					$friends = $wpdb->get_results($wpdb->prepare($sql, $current_user->ID));	
							
					if ($friends) {
						foreach ($friends as $friend) {
							$compose .= '<option class="mail_recipient_list_option" value='.$friend->friend_to.'>'.$friend->display_name.'</option>';
						}
					}
					$compose .= '</select>';
	 			$compose .= '</div>';	
				
				$compose .= '<div class="new-topic-subject label">'.__('Subject', 'wp-symposium').'</div>';
 				$compose .= "<input type='text' id='compose_subject' class='new-topic-subject-input' value='' />";
				
				$compose .= '<div id="compose_mail_message">';
					$compose .= '<div class="new-topic-subject label">'.__('Message', 'wp-symposium').'</div>';
					$compose .= '<textarea class="reply-topic-subject-text" id="compose_text"></textarea>';
	 			$compose .= '</div>';
				
				$compose .= '<input type="hidden" id="compose_previous" value="" />';
		
			$compose .= "</div>";

		} else {
			
			$compose = '<div id="compose_form" style="display:none">';
				$compose .= __('New mail can only be sent by this member.', 'wp-symposium').'<br /><br />';
				$compose .= '<input id="mail_cancel_button" type="submit" class="symposium-button" value="'.__('Back to mail', 'wp-symposium').'" />';
			$compose .= "</div>";
			
			
		}
				
		// Replace template codes
		$html = str_replace("[compose_form]", $compose, stripslashes($html));
		$html = str_replace("[compose]", __("Compose", "wp-symposium"), stripslashes($html));
		$html = str_replace("[inbox]", __("Inbox", "wp-symposium"), stripslashes($html));
		$html = str_replace("[sent]", __("Sent", "wp-symposium"), stripslashes($html));
		$html = str_replace("[unread]", "<input type='checkbox' id='unread_only' /> ".__("Unread only", "wp-symposium"), stripslashes($html));
		

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
