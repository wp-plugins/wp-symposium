<?php
/*
Plugin Name: WP Symposium Forum
Plugin URI: http://www.wpsymposium.com
Description: Forum component for the Symposium suite of plug-ins. Put [symposium-forum] on any WordPress page to display forum.
Version: 0.49.7
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

	$html = '';
		
	// Wrapper
	$html .= "<div class='symposium-wrapper'>";

		$html .= "<div id='symposium-forum-div'></div>";

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
