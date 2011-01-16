<?php
/*
Plugin Name: WP Symposium Login
Plugin URI: http://www.wpsymposium.com
Description: Login component for the Symposium suite of plug-ins. Put [symposium-login] on any WordPress page.
Version: 0.1.26
Author: WP Symposium
Author URI: http://www.wpsymposium.com
License: GPL2
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

function symposium_login() {	
	
	global $wpdb, $current_user;
	wp_get_current_user();

	$plugin = WP_PLUGIN_URL.'/wp-symposium';

	$config = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix.'symposium_config'));
		
	$html = '<div id="symposium-wrapper" style="padding-top:10px">';

		include_once('symposium_styles.php');
	
		/* ================================================================================================================== */
	
		if (!is_user_logged_in()) {

			$html .= '<style>.hidden{display:none}</style>';
			
			$html .= '<div style="margin:0px; width:400px;">';

				$html .= '<form id="symposium_login" onsubmit="return validate_form(this)" method="post" action=""> ';
	
					$html .= '<div class="new-topic-subject label">'.__('Username', 'wp-symposium').'</div>';
					$html .= '<input type="text" id="symposium_login_username" name="username" class="new-topic-subject-input" style="width:100%" value="'.$username.'" />';
					$html .= '<div id="username-warning" class="warning hidden">'.__('Please enter a username', 'wp-symposium').'</div>';
			
					$html .= '<div>'.__('Password', 'wp-symposium').'<br />';
					$html .= '<input type="password" id="symposium_login_pwd" name="pwd" class="new-topic-subject-input" style="width:100%" value="" /></div>';
					$html .= '<div id="pwd-warning" class="warning hidden">'.__('Please enter a password', 'wp-symposium').'</div>';
			
					$html .= '<div id="symposium_forgotten_password" style="clear:both;display:none;">';

						$html .= '<strong>'.__('Forgotten Password', 'wp-symposium').'</strong><br /><br />'.__('Your email address', 'wp-symposium').'<br />';
						$html .= '<input type="text" id="forgotten_email" name="forgotten_email" class="new-topic-subject-input" style="width:100%" value="" />';

						$sum1 = rand(1,5);
						$sum2 = rand(1,4);
						$html .= '<div style="float:left;">'.__('What is the answer?', 'wp-symposium').'<br />';
						$html .= '<input type="text" id="sum1" name="sum1" class="new-topic-subject-input" style="width:30px; text-align:center;" value="'.$sum1.'"/> + ';
						$html .= '<input type="text" id="sum2" name="sum2" class="new-topic-subject-input" style="width:30px; text-align:center;" value="'.$sum2.'"/> = ';
						$html .= '<input type="text" id="result" name="result" class="new-topic-subject-input" style="width:30px; text-align:center;" /></div>';
						$html .= '<div id="sum-warning" class="warning hidden" style="float:left; width: 400px;">'.__('Please enter the result of the sum', 'wp-symposium').'</div>';
						
					$html .= '</div>';
					$html .= '<div id="symposium_forgotten_password_msg" style="clear:both;display:none;">';
						$html .= '<p>'.__('A new password has been sent to your email address', 'wp-symposium').'.</p>';
					$html .= '</div>';
				
					$html .= '<div style="clear: both; float:right; text-align:right;">';
					$html .= '<a id="symposium_forgotten" href="javascript:void(0);">'.__('Forgotten Password', 'wp-symposium').'</a><br />';
					if ($config->use_wp_register == "on") {
						$html .= '<a href="/wp-login.php?action=register">'.__('Register', 'wp-symposium').'</a>';
					} else {
						$html .= '<a href="'.$config->register_url.'">'.__('Register', 'wp-symposium').'</a>';
					}
					$html .= '</div>';

					$html .= '<input id="previous-page" type="hidden" value="'.$_GET['redirect_to'].'" />';
			
					$html .= '<div style="float:left;">';
					$html .= '<input id="symposium_login" type="submit" class="button" style="float: left; height:46px;" value="'.__('OK', 'wp-symposium').'" />';
					$html .= '</div>';

				$html .= '</form>';

			// If you are using the free version of WP Symposium, you must keep this following line. Thank you!
			$html .= "<div style='clear:both;width:100%;font-style:italic; font-size: 10px;text-align:center;'>".__('Powered by WP Symposium - Social Network for WordPress', 'wp-symposium').", ".get_option("symposium_version")."</div>";
				
	
			$html .= '</div>';

			
		} else {			
			wp_logout();
			if ($config->use_wp_login == "on") {
				wp_redirect("/");	
			} else {
				wp_redirect($config->logout_redirect_url);	
			}

		}


		// Notices
		$html .= "<div class='pleasewait' style='display:none;z-index:999999;'><img src='".$plugin."/images/busy.gif' /> ".__('Please Wait...', 'wp-symposium')."</div>";

	$html .= '</div>'; // End of Wrapper
	
	// Send HTML
	return $html;


}

/* ====================================================== SET SHORTCODE ====================================================== */
add_shortcode('symposium-login', 'symposium_login');  



?>
