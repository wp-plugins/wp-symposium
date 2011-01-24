<?php
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

include_once('../../../wp-config.php');
include_once('../../../wp-includes/wp-db.php');
include_once('symposium_functions.php');
	

global $wpdb, $current_user;
wp_get_current_user();

if (is_user_logged_in()) {

	$uid = $_POST['uid'];	
		
	if ($uid == $current_user->ID) {

		if ($_POST['action'] == 'crop_photo') {

			$targ_w = $targ_h = 150;
			$jpeg_quality = 90;
			
			$src = WP_PLUGIN_DIR.'/wp-symposium/uploads/'.$_POST['img'];

			$img_r = imagecreatefromjpeg($src);
			$dst_r = ImageCreateTrueColor( $targ_w, $targ_h );
		
			imagecopyresampled($dst_r,$img_r,0,0,$_POST['x'],$_POST['y'],
			$targ_w,$targ_h,$_POST['w'],$_POST['h']);
	
			$wp_content_dir = ABSPATH . 'wp-content';
			$to_path = $wp_content_dir.'/wp-symposium-members/'.$uid.'/media/photos/profile_pictures/';	
			$filename = time().'.jpg';
			$to_file = $to_path.$filename;
			if (file_exists($to_path)) {
			    // folder already there
			} else {
				mkdir(str_replace('//','/',$to_path), 0777, true);
			}
				
			if ( imagejpeg($dst_r,$to_file,$jpeg_quality) ) {
				
				// update database
				$wpdb->update( $wpdb->prefix.'symposium_usermeta', 
					array( 'profile_photo' => $filename ), 
					array( 'uid' => $uid ), 
					array( '%s' ), 
					array( '%d' )
					);
					
			} else {
				
				$wpdb->update( $wpdb->prefix.'symposium_usermeta', 
					array( 'profile_photo' => 'upload_failed' ), 
					array( 'uid' => $uid ), 
					array( '%s' ), 
					array( '%d' )
					);
					
			}
			
			header ("Location: ".symposium_get_url('profile'));
			exit;
		
		}
		
	}

}

	
?>