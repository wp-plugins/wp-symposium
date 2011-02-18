<?php
/*
Plugin Name: WP Symposium Avatar
Plugin URI: http://www.wpsymposium.com
Description: Allows members of a WP Symposium powered site to upload a profile photo. Put [symposium-avatar] on any WordPress page.
Version: 0.39.1
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
			
			// strip out any comments appended to return from AJAX (typical in cheap shared hosting)
			if (strpos($img, "<")) {
				$img = substr($img, 0, strpos($img, "<"));
			}			
			
			// Get path where the uploaded file is
			if ( WPS_TMP_DIR != "WPS_TMP_DIR" ) {
				$targetPath = WPS_TMP_DIR.'/';
			} else {
				$targetPath = $_SERVER['DOCUMENT_ROOT'].'/wp-content/plugins/wp-symposium/uploads/';
			}
			$targetPath =  str_replace('//','/',$targetPath);

			$img_path = $targetPath.$img;
			$save_img_path = WP_PLUGIN_DIR.'/wp-symposium/uploads/'.$img;
	
			if (file_exists($img_path)) {
				
				ini_set("memory_limit","100M");
		
				include_once('SimpleImage.php');
	
			   	$image = new symposium_SimpleImage();
			   	$image->load($img_path);
			   	$image->resizeToWidth(600);
			   	$image->save($save_img_path);
			   
				$html .= '<div id="image_to_crop">';
				$html .= '<img src="'.WP_PLUGIN_URL.'/wp-symposium/uploads/'.$img.'" id="profile_jcrop_target" /> ';
				$html .= '</div>';
				
				$html .= '<div id="image_preview"> ';
				$html .= '<img src="'.WP_PLUGIN_URL.'/wp-symposium/uploads/'.$img.'" id="profile_preview" /> ';
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
				
				// There was a problem, so try to work out why
				$html .= "<h1>There is a problem, some possible reasons and solutions...</h1>";
				
				$html .= "<h2>Some information</h2>";
				$html .= "<p>Upload directory: ".$targetPath."</p>";
				
				$html .= __("<h2>1. PHP restriction?</h2><p>If you get <em>Warning: file_exists() [function.file-exists]: open_basedir restriction in effect</em> errors this means you have restrictions on your server, often on shared servers. Contact your server hosting administrator.</p>", "wp-symposium");

				$html .= __("<h2>2. Server error?</h2><p>Also, check the full text after the 'img=' parameter in the current page URL as this might show more information, it should be just a filename representing the image your uploaded (some characters, like ampersands, are replaced with _).</p>", "wp-symposium");

				$html .= __("<h2>3. Set your own upload path for WP Symposium</h2><p>Edit wp-config.php in the root of your WordPress installation, and add the line <b>define('WPS_TMP_DIR', '/tmp');</b> where /tmp is a publicly accessible directory on your server.</p>", "wp-symposium");

				if (!file_exists($targetPath)) {
					$html .= "<h2>4. Directory not created</h2><p>".__(sprintf("[%s] didn't get created, probably due to permissions or because of restrictions in PHP (this is configured at server level, contact your server administrator). Create it manually and CHMOD it 777 (ie. full public permissions).", $targetPath), "wp_symposium")."</p>";  
				}
				
				if (!file_exists($img_path)) {
					$html .= "<h2>5. Image not stored</h2><p>".__(sprintf("[%s] doesn't exist, initial upload to temporary directory may have failed.", $img_path), "wp_symposium")."</p>";  
				}
				
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
