<?php
/*
Plugin Name: WP Symposium Registration
Plugin URI: http://www.wpsymposium.com
Description: NOTE READY FOR USE YET! Registration component for the Symposium suite of plug-ins. Put [symposium-register] on any WordPress page.
Version: 0.1.23
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
	
			$html .= '<input type="text" id="hdn" name="hdn" />';
			
		$html .= '</div>';

		$html .= '<div style="padding:0px;margin-left:5px">';
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
