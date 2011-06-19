<?php
/*
Plugin Name: WP Symposium Forum
Plugin URI: http://www.wpsymposium.com
Description: Forum component for the Symposium suite of plug-ins. Put [symposium-forum] on any WordPress page to display forum.
Version: 0.56
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

function symposium_forum() {	

	global $wpdb;
	$viewer = $wpdb->get_var($wpdb->prepare("SELECT viewer FROM ".$wpdb->prefix.'symposium_config'));
	$level = symposium_get_current_userlevel();
	
	$html = '';
		
	// Wrapper
	$html .= "<div class='symposium-wrapper'>";

	if ( ($viewer == "Guest")
	 || ($viewer == "Subscriber" && $level >= 1)
	 || ($viewer == "Contributor" && $level >= 2)
	 || ($viewer == "Author" && $level >= 3)
	 || ($viewer == "Editor" && $level >= 4)
	 || ($viewer == "Administrator" && $level == 5) ) {

		$html .= "<div id='symposium-forum-div'></div>";
		
	 } else {

		$html .= "<p>".__("The minimum level for this forum is", "wp-symposium")." ".$viewer."</p>";

	 }

	$html .= "</div>";
	// End Wrapper
	
	$html .= "<div style='clear: both'></div>";
	
	// Send HTML
	return $html;

}

/* ====================================================== SET SHORTCODE ====================================================== */

if (!is_admin()) {
	add_shortcode('symposium-forum', 'symposium_forum');  
}



?>
