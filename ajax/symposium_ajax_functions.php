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

// Warning report
if ($action == "sendReport") {
	
	global $wpdb, $current_user;

	$r = 'OK';

	$code = $_POST['code'];
	$report_text = $_POST['report_text'];

	symposium_sendmail(get_bloginfo('admin_email'), __('Warning Report', 'wp-symposium'), __('From:', 'wp-symposium').': '.$current_user->display_name.'<br /><br />'.$report_text.'<br /><br />Ref: '.$code);							

	exit;	
}

// Add new page
if ($action == "add_new_page") {
	
	global $wpdb, $current_user;

	$r = 'OK';

	$shortcode = $_POST['shortcode'];
	$name = $_POST['name'];
	$post_name = str_replace(' ', '', strtolower($name));
	
	$wpdb->query( $wpdb->prepare( "
		INSERT INTO ".$wpdb->prefix."posts
		( 	post_author, 
			post_date,
			post_date_gmt,
			post_content,
			post_title,
			post_status,
			comment_status,
			ping_status,
			post_name,
			post_modified,
			post_modified_gmt,
			post_parent,
			menu_order,
			post_type,
			comment_count
		)
		VALUES ( %d, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %d, %d, %s, %d )", 
	    array(
	    	$current_user->ID, 
	    	date("Y-m-d H:i:s"), 
	    	gmdate("Y-m-d H:i:s"),
	    	'['.$shortcode.']',
	    	$name,
	    	'publish',
	    	'closed',
	    	'open',
	    	$post_name,
	    	date("Y-m-d H:i:s"), 
	    	gmdate("Y-m-d H:i:s"),
	    	0,
	    	0,
	    	'page',
	    	0
	    	) 
	    ) );
	    
	    // get new ID
		$new_id = $wpdb->insert_id;
		
		// page meta data
		$wpdb->query( $wpdb->prepare( "
			INSERT INTO ".$wpdb->base_prefix."postmeta
			( 	post_id, 
				meta_key,
				meta_value
			)
			VALUES ( %d, %s, %s )", 
		    array(
		    	$new_id, 
		    	'_wp_page_template',
		    	'sidebar-page.php'
		    	) 
		    ) );
	    		
	    // update guid
	    $url = get_bloginfo('url');
    	if ($url[strlen($url)-1] == '/') { $url = substr($url,0,-1); }
    	$url .= '/?p='.$new_id;
		$sql = "UPDATE ".$wpdb->prefix."posts SET guid = '%s' WHERE ID = %d";
		$wpdb->query($wpdb->prepare($sql, $url, $new_id)); 
	    	    
	echo $r;
	exit;	
}

// Add to existing page
if ($action == "add_to_page") {
	
	global $wpdb, $current_user;

	$r = 'OK';

	$shortcode = $_POST['shortcode'];
	$id = $_POST['id'];

	// get existing value
	$sql = "SELECT post_content FROM ".$wpdb->prefix."posts WHERE ID = %d";
	$tmp = $wpdb->get_var($wpdb->prepare($sql, $id));	
	$tmp .= '['.$shortcode.']';

	// update
	$sql = "UPDATE ".$wpdb->prefix."posts SET post_content = %s WHERE ID = %d";
	$wpdb->query($wpdb->prepare($sql, $tmp, $id)); 	    	    
	
	echo $r;
	exit;	
}

// Import templates file
if ($action == "import_template_file") {

	$import_file = $_POST['import_file'];
	
	symposium_update_import_snippet("template_profile_header", $import_file);
	symposium_update_import_snippet("template_profile_body", $import_file);
	symposium_update_import_snippet("template_page_footer", $import_file);
	symposium_update_import_snippet("template_email", $import_file);
	symposium_update_import_snippet("template_forum_header", $import_file);
	symposium_update_import_snippet("template_mail", $import_file);
	symposium_update_import_snippet("template_mail_tray", $import_file);
	symposium_update_import_snippet("template_mail_message", $import_file);
	symposium_update_import_snippet("template_group", $import_file);
	symposium_update_import_snippet("template_forum_category", $import_file);
	symposium_update_import_snippet("template_forum_topic", $import_file);
	symposium_update_import_snippet("template_group_forum_category", $import_file);
	symposium_update_import_snippet("template_group_forum_topic", $import_file);

	echo 'OK';
	exit;
}
function symposium_update_import_snippet($tag, $import_file) {
	global $wpdb;
	$start = strpos($import_file, "<!-- ".$tag." -->") + strlen("<!-- ".$tag." -->")+1;
	$end = strpos($import_file, "<!-- end_".$tag." -->");
	$snippet = substr($import_file, $start, $end-$start-1);
	$sql = "UPDATE ".$wpdb->prefix."symposium_config SET ".$tag." = '".$snippet."'";
	$wpdb->query($wpdb->prepare($sql)); 
}

// Update Admin Avatar
if ($_POST['action'] == 'saveAdminAvatar') {

	global $wpdb, $current_user;
	
	if (is_user_logged_in()) {

		$uid = $current_user->ID;
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
			if (WPS_IMG_DB == 'on') {
			
				// Using database		
				$avatar = stripslashes(WPS_IMG_UPLOAD);	
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

				$profile_photo = $wpdb->get_var($wpdb->prepare("SELECT profile_photo FROM ".$wpdb->prefix.'symposium_usermeta WHERE uid='.$current_user->ID));
			
				$src = WPS_IMG_PATH."/members/".$uid."/profile/".$profile_photo;
			
				$img_r = imagecreatefromjpeg($src);
				$dst_r = ImageCreateTrueColor( $targ_w, $targ_h );
		
				imagecopyresampled($dst_r,$img_r,0,0,$x,$y,$targ_w,$targ_h,$w,$h);
	
				$to_path = WPS_IMG_PATH."/members/".$uid."/profile/";
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
		
	}
	
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
