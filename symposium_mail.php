<?php
/*
Plugin Name: WP Symposium Mail
Plugin URI: http://www.wpsymposium.com
Description: Mail component for the Symposium suite of plug-ins. Put [symposium-mail] on any WordPress page.
Version: 0.53.5
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

	if (isset($_GET['page_id']) && $_GET['page_id'] != '') {
		// No Permalink
		$thispage = $mail_url;
		$q = "&";
	} else {
		$q = "?";
	}
	
	$plugin_dir = WP_PLUGIN_URL.'/wp-symposium/';
	
	if (is_user_logged_in()) {

		$template = $wpdb->get_var("SELECT template_mail FROM ".$wpdb->prefix."symposium_config");
		$template = str_replace("[]", "", stripslashes($template));

		$html = '<div class="symposium-wrapper">'.$template.'</div>';
			
		// Compose Form	
		$compose = '<div id="compose_form" style="display:none">';
	
			$compose .= '<div class="floatright send_button">';
			$compose .= '<input id="mail_send_button" type="submit" class="symposium-button" value="'.__('Send', 'wp-symposium').'" />';
			$compose .= '<input id="mail_cancel_button" type="submit" class="symposium-button" value="'.__('Cancel', 'wp-symposium').'" />';
			$compose .= '</div>';

			$compose .= '<div id="compose_mail_to">';
				$compose .= '<div class="new-topic-subject label">'.__('Start typing a friend\'s name...', 'wp-symposium').'</div>';
 				$compose .= "<input type='text' id='compose_recipient' class='new-topic-subject-input' style='width:50%' value=''/>";
 			$compose .= '</div>';
			
			$compose .= '<div id="compose_mail_subject">';
				$compose .= '<div class="new-topic-subject label">'.__('Subject', 'wp-symposium').'</div>';
 				$compose .= "<input type='text' id='compose_subject' class='new-topic-subject-input' value='' />";
 			$compose .= '</div>';
			
			$compose .= '<div id="compose_mail_message">';
				$compose .= '<div class="new-topic-subject label">'.__('Message', 'wp-symposium').'</div>';
				$compose .= '<textarea class="reply-topic-subject-text" id="compose_text"></textarea>';
 			$compose .= '</div>';
			
			$compose .= '<input type="hidden" id="compose_previous" value="" />';
	
		$compose .= "</div>";
		
		// Replace template codes
		$html = str_replace("[compose_form]", $compose, stripslashes($html));
		$html = str_replace("[compose]", __("Compose", "wp-symposium"), stripslashes($html));
		$html = str_replace("[inbox]", __("Inbox", "wp-symposium"), stripslashes($html));
		$html = str_replace("[sent]", __("Sent", "wp-symposium"), stripslashes($html));
		

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
