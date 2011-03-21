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


include_once('../../../../wp-config.php');
	
$action = "";
if (isset($_POST['action'])) { $action .= $_POST['action']; }
if (isset($_GET['action'])) { $action .= $_GET['action']; }

// Update Admin Avatar
if ($_POST['action'] == 'saveAdminAvatar') {

	global $wpdb;

	$uid = $_POST['uid'];
	$x = $_POST['x'];
	$y = $_POST['y'];
	$w = $_POST['w'];
	$h = $_POST['h'];
	
	$r = '';

	if ($w > 0) {	

		// set new size and quality
		$targ_w = $targ_h = 200;
		$jpeg_quality = 90;

		// database or filesystem
		$config = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix.'symposium_config'));
		
		if ($config->img_db == 'on') {
			
			// Using database
		
			$sql = "SELECT img_upload FROM ".$wpdb->base_prefix."symposium_config";
			$avatar = stripslashes($wpdb->get_var($sql));	
			$img_r = imagecreatefromstring($avatar);

			// create temporary image
			$dst_r = ImageCreateTrueColor( $targ_w, $targ_h );		
			// copy to new image, with new dimensions
			imagecopyresampled($dst_r,$img_r,0,0,$x,$y,$targ_w,$targ_h,$w,$h);
			// copy to variable
			ob_start();
			imageJPEG($dst_r);
			$new_img = ob_get_contents();
			ob_end_clean();
			
			// update database with resized blob
			$wpdb->update( $wpdb->base_prefix.'symposium_config', 
				array( 'img_upload' => addslashes($new_img) ), 
				array( 'img_db' => 'on' ), 
				array( '%s' ), 
				array( '%s' )
				);
				
		} else {
			
			// Using filesystem

			$profile_photo = $wpdb->get_var($wpdb->prepare("SELECT profile_photo FROM ".$wpdb->prefix.'symposium_usermeta'));
			
			$src = $config->img_path."/members/".$uid."/profile/".$profile_photo;
			
			$img_r = imagecreatefromjpeg($src);
			$dst_r = ImageCreateTrueColor( $targ_w, $targ_h );
		
			imagecopyresampled($dst_r,$img_r,0,0,$x,$y,$targ_w,$targ_h,$w,$h);
	
			$to_path = $config->img_path."/members/".$uid."/profile/";
			$filename = time().'.jpg';
			$to_file = $to_path.$filename;
			if (file_exists($to_path)) {
			    // folder already there
			} else {
				mkdir(str_replace('//','/',$to_path), 0777, true);
			}
				
			if ( imagejpeg($dst_r,$to_file,$jpeg_quality) ) {
				
				// update database
				$wpdb->update( $wpdb->base_prefix.'symposium_usermeta', 
					array( 'profile_photo' => $filename ), 
					array( 'uid' => $uid ), 
					array( '%s' ), 
					array( '%d' )
					);
					
			} else {
				
				$r = 'resize failed: '.$wpdb->last_query;
					
			}
			
		}
			
	} else {
		
		$r = 'No size details sent (x, y, w, h)';
	}
	
	echo $r;
	
	exit;
	
}

if ($action == "symposium_logout") {

  	wp_logout();
	exit;
}

if ($action == "symposium_test_ajax") {

	$value = $_POST['postID'];	
	echo $value*100;
	exit;
}

if ($action == "symposium_motd") {

	global $wpdb;

	// Update motd flag
	$sql = "UPDATE ".$wpdb->prefix."symposium_config SET motd = 'on'";
	$wpdb->query($sql); 
	
	exit;	
}

?>
