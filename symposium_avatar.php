<?php
/*
Plugin Name: WP Symposium Avatar
Plugin URI: http://www.wpsymposium.com
Description: Allows members of a WP Symposium powered site to upload a profile photo. Put [symposium-avatar] on any WordPress page.
Version: 0.36
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

function symposium_avatar()  
{  
	
	global $wpdb, $current_user;
	
	$html = '';

	if (is_user_logged_in()) {

		include_once('symposium_styles.php');
		
		// Wrapper
		$html .= "<div class='symposium-wrapper'>";
		
		$html .= "<h2>".__('Profile Photo')."</h2>";
		
		if ($_GET['crop'] != 'y') {
			
			$html .= '<p>'.__('Choose an image...', 'wp-symposium').'</p>';
			
			$html .= '<input id="file_upload" name="file_upload" type="file" />';
			
		} else {
	
			$db_page = get_site_url().'/wp-content/plugins/wp-symposium/symposium_profile_db.php';	
		
			$img = $_GET['img'];
			$img_path = WP_PLUGIN_DIR.'/wp-symposium/uploads/'.$img;
	
			if (file_exists($img_path)) {
				
				ini_set("memory_limit","100M");
		
				include_once('SimpleImage.php');
	
			   	$image = new symposium_SimpleImage();
			   	$image->load($img_path);
			   	$image->resizeToWidth(600);
			   	$image->save($img_path);
			   
				$html .= '<div id="image_to_crop">';
				$html .= '<img src="/wp-content/plugins/wp-symposium/uploads/'.$img.'" id="profile_jcrop_target" /> ';
				$html .= '</div>';
				
				$html .= '<div id="image_preview"> ';
				$html .= '<img src="/wp-content/plugins/wp-symposium/uploads/'.$img.'" id="profile_preview" /> ';
				$html .= '</div>';
			
				$html .= '<div id="image_instructions"> ';
				$html .= '<p>'.__('Select an area above...', 'wp-symposium').'</p>';
				$html .= '<form action="'.$db_page.'" method="post">';
					$html .= '<input type="hidden" name="action" value="crop_photo" />';
					$html .= '<input type="hidden" name="img" value="'.$img.'" />';
					$html .= '<input type="hidden" name="uid" value="'.$current_user->ID.'" />';
					$html .= '<input type="hidden" id="x" name="x" />';
					$html .= '<input type="hidden" id="y" name="y" />';
					$html .= '<input type="hidden" id="x2" name="x2" />';
					$html .= '<input type="hidden" id="y2" name="y2" />';
					$html .= '<input type="hidden" id="w" name="w" />';
					$html .= '<input type="hidden" id="h" name="h" />';
					$html .= '<input type="submit" class="button" value="OK" />';
				$html .= '</form>';
				$html .= '</div>';
				
			} else {
				
				$html .= "<p>".__(sprintf("%s does not exist, upload probably failed", $img_path), "wp_symposium").".</p>";  
				
			}
			
		}
	
		$html .= '</div>';
			
		
	}
	
	return $html;
	
	exit;

}  




/* ====================================================== SET SHORTCODE ====================================================== */
add_shortcode('symposium-avatar', 'symposium_avatar');  

?>
