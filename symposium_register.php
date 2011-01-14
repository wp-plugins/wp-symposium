<?php
/*
Plugin Name: WP Symposium Registration
Plugin URI: http://www.wpsymposium.com
Description: Registration component for the Symposium suite of plug-ins. Put [symposium-register] on any WordPress page.
Version: 0.1.25
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

function symposium_register() {	
	
	global $wpdb, $current_user;
	wp_get_current_user();

	$plugin = WP_PLUGIN_URL.'/wp-symposium';
	$get_language = symposium_get_language($current_user->ID);
	$language_key = $get_language['key'];

	$html = '<div id="symposium-wrapper" style="padding-top:10px">';

	include_once('symposium_styles.php');

	/* ================================================================================================================== */

	$html .= '<style>#hdn{display:none}.hidden{display:none}</style>';
	
	if (!is_user_logged_in()) {
		
		if ($_GET['msg'] != '') {
			$html .= '<div class="warning" style="margin-bottom:10px">'.$_GET['msg'].'</div>';
		}

		$username = $_GET['username'];
		$youremail = str_replace("!", "@", $_GET['youremail']);
		$display_name = $_GET['display_name'];

		$html .= '<form id="symposium_registration" onsubmit="return validate_form(this)" method="post" action="'.$plugin.'/symposium_register_db.php"> ';
			
		$html .= '<div style="margin-left:0px">';

			$html .= '<div id="new-topic-subject-label" class="new-topic-subject label">A username</div>';
			$html .= '<input type="text" id="username" name="username" class="new-topic-subject-input" style="width:96%" value="'.$username.'" />';
			$html .= '<div id="username-warning" class="warning hidden">Please enter a username</div>';

			$html .= '<div>Your name as seen by others<br />';
			$html .= '<input type="text" id="display_name" name="display_name" class="new-topic-subject-input" style="width:96%" value="'.$display_name.'"/></div>';
			$html .= '<div id="display_name-warning" class="warning hidden">Please enter a display name</div>';
	
			$html .= '<div>Your email address<br />';
			$html .= '<input type="text" id="youremail" name="youremail" class="new-topic-subject-input" style="width:96%" value="'.$youremail.'"/></div>';
			$html .= '<div id="youremail-warning" class="warning hidden">Please enter a valid email address</div>';
		
			$html .= '<div>A password<br />';
			$html .= '<input type="text" id="pwd" name="pwd" class="new-topic-subject-input" style="width:96%" value="" /></div>';
			$html .= '<div id="password-warning" class="warning hidden">Please enter a password</div>';
	
			$html .= '<div>Re-enter the password<br />';
			$html .= '<input type="text" id="pwd2" name="pwd2" class="new-topic-subject-input" style="width:96%" value="" /></div>';
			$html .= '<div id="password2-warning" class="warning hidden" style="margin-top:20px">You entered different passwords</div>';
	
			$sum1 = rand(1,5);
			$sum2 = rand(1,4);
			$result = $sum1 + $sum2;
			$use_sum = $wpdb->get_var($wpdb->prepare("SELECT register_use_sum FROM ".$wpdb->prefix."symposium_config"));
			if ( $use_sum == "on" ) {
				$html .= '<div style="float:left; width: 400px;">What is the answer?<br />';
				$html .= '<input type="text" id="sum1" name="sum1" class="new-topic-subject-input" style="width:30px; text-align:center;" value="'.$sum1.'"/> + ';
				$html .= '<input type="text" id="sum2" name="sum2" class="new-topic-subject-input" style="width:30px; text-align:center;" value="'.$sum2.'"/> = ';
				$html .= '<input type="text" id="result" name="result" class="new-topic-subject-input" style="width:30px; text-align:center;" /></div>';
				$html .= '<div id="sum-warning" class="warning hidden" style="float:left; width: 400px;">Please enter the result of the sum</div>';
			} else {
				$html .= '<input type="hidden" id="sum1" name="sum1" value="'.$sum1.'"/>';
				$html .= '<input type="hidden" id="sum2" name="sum2" value="'.$sum2.'"/>';
				$html .= '<input type="hidden" id="result" name="result" value="'.$result.'"/>';
			}
	
			$html .= '<input type="text" id="hdn" name="hdn" />';
			
		$html .= '</div>';

		$html .= '<div style="padding:0px;float:right; margin-left:5px;  margin-right:5px">';
		if ( $use_sum == "on" ) { $html .= "<br />"; }
		$html .= '<input type="submit" class="button" style="float: left; height:46px;" value="Register" />';
		$html .= '</div>';

		
		$html .= '</form>';

	}
		
	$html .= '</div>'; // End of Wrapper
	
	// Send HTML
	return $html;

}

/* ====================================================== SET SHORTCODE ====================================================== */
add_shortcode('symposium-register', 'symposium_register');  



?>
