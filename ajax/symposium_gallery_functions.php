<?php
/*
Plugin Name: WP Symposium Media Gallery
Plugin URI: http://www.wpsymposium.com
Description: Media Gallery plugin compatible with WP Symposium. Activate to use.
Version: 1.0
Author: WP Symposium
Author URI: http://www.wpsymposium.com
License: Commercial
Requires at least: WordPress 3.0 and WP Symposium 0.53
*/
	
/*  Copyright 2011  Web Technology Solutions Ltd  (info@wpsymposium.com)

EULA stands for End User Licensing Agreement. This is the agreement through which the software is licensed to the software user. 

END-USER LICENSE AGREEMENT FOR WPS Media Gallery

IMPORTANT PLEASE READ THE TERMS AND CONDITIONS OF THIS LICENSE AGREEMENT CAREFULLY BEFORE CONTINUING WITH THIS PROGRAM 

INSTALL: Web Technology Solutions Ltd End-User License Agreement ("EULA") is a legal agreement between you (either an individual or a single entity) and Web Technology Solutions Ltd, for the software product(s) identified above which may include associated software components, media, printed materials, and "online" or electronic documentation ("SOFTWARE PRODUCT"). 

By installing, copying, or otherwise using the SOFTWARE PRODUCT, you agree to be bound by the terms of this EULA. This license agreement represents the entire agreement concerning the program between you and Web Technology Solutions Ltd, (referred to as "licenser"), and it supersedes any prior proposal, representation, or understanding between the parties. If you do not agree to the terms of this EULA, do not install or use the SOFTWARE PRODUCT.

The SOFTWARE PRODUCT is protected by copyright laws and international copyright treaties, as well as other intellectual property laws and treaties. 

The SOFTWARE PRODUCT is licensed, not sold.

1. GRANT OF LICENSE. 
The SOFTWARE PRODUCT is licensed as follows: 
(a) Installation and Use.
Web Technology Solutions Ltd grants you the right to install and use copies of the SOFTWARE PRODUCT on your computer running a validly licensed copy of the operating system for which the SOFTWARE PRODUCT was designed.
(b) Backup Copies.
You may also make copies of the SOFTWARE PRODUCT as may be necessary for backup and archival purposes.

2. DESCRIPTION OF OTHER RIGHTS AND LIMITATIONS.
(a) Maintenance of Copyright Notices.
You must not remove or alter any copyright notices on any and all copies of the SOFTWARE PRODUCT.
(b) Distribution.
You may not distribute registered copies of the SOFTWARE PRODUCT to third parties. Evaluation versions available for download from Web Technology Solutions Ltd's websites may be freely distributed.
(c) Prohibition on Reverse Engineering, Decompilation, and Disassembly.
You may not reverse engineer, decompile, or disassemble the SOFTWARE PRODUCT, except and only to the extent that such activity is expressly permitted by applicable law notwithstanding this limitation. 
(d) Rental.
You may not rent, lease, or lend the SOFTWARE PRODUCT.
(e) Support Services.
Web Technology Solutions Ltd may provide you with support services related to the SOFTWARE PRODUCT ("Support Services"). Any supplemental software code provided to you as part of the Support Services shall be considered part of the SOFTWARE PRODUCT and subject to the terms and conditions of this EULA. 
(f) Compliance with Applicable Laws.
You must comply with all applicable laws regarding use of the SOFTWARE PRODUCT.

3. TERMINATION 
Without prejudice to any other rights, Web Technology Solutions Ltd may terminate this EULA if you fail to comply with the terms and conditions of this EULA. In such event, you must destroy all copies of the SOFTWARE PRODUCT in your possession.

4. COPYRIGHT
All title, including but not limited to copyrights, in and to the SOFTWARE PRODUCT and any copies thereof are owned by Web Technology Solutions Ltd or its suppliers. All title and intellectual property rights in and to the content which may be accessed through use of the SOFTWARE PRODUCT is the property of the respective content owner and may be protected by applicable copyright or other intellectual property laws and treaties. This EULA grants you no rights to use such content. All rights not expressly granted are reserved by Web Technology Solutions Ltd.

5. NO WARRANTIES
Web Technology Solutions Ltd expressly disclaims any warranty for the SOFTWARE PRODUCT. The SOFTWARE PRODUCT is provided 'As Is' without any express or implied warranty of any kind, including but not limited to any warranties of merchantability, noninfringement, or fitness of a particular purpose. Web Technology Solutions Ltd does not warrant or assume responsibility for the accuracy or completeness of any information, text, graphics, links or other items contained within the SOFTWARE PRODUCT. Web Technology Solutions Ltd makes no warranties respecting any harm that may be caused by the transmission of a computer virus, worm, time bomb, logic bomb, or other such computer program. Web Technology Solutions Ltd further expressly disclaims any warranty or representation to Authorized Users or to any third party.

6. LIMITATION OF LIABILITY
In no event shall Web Technology Solutions Ltd be liable for any damages (including, without limitation, lost profits, business interruption, or lost information) rising out of 'Authorized Users' use of or inability to use the SOFTWARE PRODUCT, even if Web Technology Solutions Ltd has been advised of the possibility of such damages. In no event will Web Technology Solutions Ltd be liable for loss of data or for indirect, special, incidental, consequential (including lost profit), or other damages based in contract, tort or otherwise. Web Technology Solutions Ltd shall have no liability with respect to the content of the SOFTWARE PRODUCT or any part thereof, including but not limited to errors or omissions contained therein, libel, infringements of rights of publicity, privacy, trademark rights, business interruption, personal injury, loss of privacy, moral rights or the disclosure of confidential information.

*/

include_once('../../../../wp-config.php');

// Update to alerts and then redirect
if (isset($_GET['href'])) {
	
	global $wpdb, $current_user;
	
	$num = $_GET['num'];
	$aid = $_GET['aid'];
	
	// Add to activity feed
	add_to_create_activity_feed($aid);
		
	// Then re-direct
	$href = symposium_get_url('profile');
	$href .= symposium_string_query($href);
	$href .= "uid=".$current_user->ID."&embed=on&album_id=".$aid;
	
	wp_redirect( $href ); 
	exit;	
	
}

function add_to_create_activity_feed($aid) {

	global $wpdb, $current_user;

	$url = symposium_get_url('profile');
	$url .= symposium_string_query($url);	
	$url .= "uid=".$current_user->ID."&embed=on&album_id=".$aid;	

	// Get name of album
	$sql = "SELECT name FROM ".$wpdb->prefix."symposium_gallery WHERE gid = %d";
	$name = $wpdb->get_var($wpdb->prepare($sql, $aid));

	// Work out message
	$msg = '';

	$sql = "SELECT * FROM ".$wpdb->prefix."symposium_gallery_items WHERE gid = %d ORDER BY iid DESC";
	$photos = $wpdb->get_results($wpdb->prepare($sql, $aid));		
	
	$cnt = 0;
	if ($photos) {

		$msg .= __("Added to", "wp-symposium").' <a href="'.$url.'">'.$name.'</a> '.__('gallery', 'wp-symposium');	

		$thumbnail_size = (get_option("symposium_gallery_thumbnail_size") != '') ? get_option("symposium_gallery_thumbnail_size") : 75;
		$images = '<div id="wps_comment_plus" style="width:'.($thumbnail_size * 3.8).'px;height:'.$thumbnail_size.'px;overflow:hidden;">';
		
   		foreach ($photos as $photo) {	

			$cnt++;      					

            // Add photo
			$thumbnail_size = ($value = get_option("symposium_gallery_thumbnail_size")) ? $value : '75';

			// Filesystem
			if (get_option('symposium_img_db') == "on") {
				$img_src = WP_CONTENT_URL."/plugins/wp-symposium/get_album_item.php?iid=".$photo->iid."&size=photo";
				$thumb_src = WP_CONTENT_URL."/plugins/wp-symposium/get_album_item.php?iid=".$photo->iid."&size=thumbnail";
			} else {
				if (get_option("symposium_gallery_show_resized") == 'on') {
                	$img_src = get_option('symposium_img_url').'/members/'.$current_user->ID.'/media/'.$aid.'/show_'.$photo->name;
				} else {
                	$img_src = get_option('symposium_img_url').'/members/'.$current_user->ID.'/media/'.$aid.'/'.$photo->name;
				}
    	        $thumb_src = get_option('symposium_img_url').'/members/'.$current_user->ID.'/media/'.$aid.'/thumb_'.$photo->name;
			}
			
           	$images .= '<div class="symposium_photo_outer" style="float:left; margin-right:5px; margin-bottom: 5px; width:75px; height:75px;">';
       			$images .= '<div class="symposium_photo_inner">';
  					$images .= '<div class="symposium_photo_cover">';
  					$images .= '<a class="symposium_photo_cover_action wps_profile_album" href="'.$img_src.'" rel="symposium_gallery_photos'.$aid.'" title="'.stripslashes($photo->title).'">';
						$images .= '<img class="symposium_photo_image" style="width:75px; height:75px;" src="'.$thumb_src.'" />';
					$images .= '</a>';
					$images .= '</div>';
				$images .= '</div>';
			$images .= '</div>';
		    
   		}

		$images .= '</div>';   		

		if ($cnt > 0) { $msg .= $images; }
   		if ($cnt > 3) {
   		    $msg .= '<div id="wps_gallery_comment_more" style="clear:both;cursor:pointer">';
   		    $msg .= __('more...', 'wp-symposium').'</div>';
   		}
   		    
	}

	// First remove any older messages to avoid duplication that mention this album
	$sql = "DELETE FROM ".$wpdb->base_prefix."symposium_comments WHERE subject_uid = ".$current_user->ID." AND author_uid = ".$current_user->ID." AND comment LIKE '%".$name."%' AND type = 'gallery'";
	$wpdb->query($sql);

	// Now add to activity feed
	symposium_add_activity_comment($current_user->ID, $current_user->display_name, $current_user->ID, $msg, 'gallery');
	
}

// Search gallery (shortcode)
if ($_POST['action'] == 'getGallery') {
	
	global $wpdb, $current_user;
	
	$start = $_POST['start'];
	$term = $_POST['term'];

	$sql = "SELECT g.*, u.display_name FROM ".$wpdb->prefix."symposium_gallery g
			INNER JOIN ".$wpdb->base_prefix."users u ON g.owner = u.ID
			WHERE g.name LIKE '%".$term."%' 
			   OR u.display_name LIKE '%".$term."%' 
			ORDER BY gid DESC 
			LIMIT ".$start.",50";
	$albums = $wpdb->get_results($sql);

	$album_count = 0;	
	$total_count = 0;	
	$html = '';

	if ($albums) {

		$page_length = (get_option("symposium_gallery_page_length") != '') ? get_option("symposium_gallery_page_length") : 10;

		$html .= "<div id='symposium_gallery_albums'>";
		
		foreach ($albums AS $album) {

			$total_count++;	
			
			// check for privacy
			if ( ($album->owner == $current_user->ID) || (strtolower($album->sharing) == 'public') || (is_user_logged_in() && strtolower($album->sharing) == 'everyone') || (strtolower($album->sharing) == 'public') || (strtolower($album->sharing) == 'friends only' && symposium_friend_of($album->owner, $current_user->ID)) || symposium_get_current_userlevel() == 5) {

				$sql = "SELECT COUNT(iid) FROM ".$wpdb->prefix."symposium_gallery_items WHERE gid = %d";
				$photo_count = $wpdb->get_var($wpdb->prepare($sql, $album->gid));				
	
				if ($photo_count > 0) {
					
					$html .= "<div id='symposium_album_content' style='margin-bottom:30px'>";
				
					$html .= "<div class='topic-post-header'>".stripslashes($album->name)."</div>";
					$html .= "<p>".symposium_profile_link($album->owner)."</p>";
		
					$sql = "SELECT * FROM ".$wpdb->prefix."symposium_gallery_items WHERE gid = %d ORDER BY iid DESC";
					$photos = $wpdb->get_results($wpdb->prepare($sql, $album->gid));	
				
					if ($photos) {
	
						$album_count++;
						
						$cnt = 0;
						$thumbnail_size = (get_option("symposium_gallery_thumbnail_size") != '') ? get_option("symposium_gallery_thumbnail_size") : 75;
						$html .= '<div id="wps_comment_plus" style="width:98%;height:'.($thumbnail_size+10).'px;overflow:hidden; ">';
			
						$preview_count = (get_option("symposium_gallery_preview") != '') ? get_option("symposium_gallery_preview") : 5;
			       		foreach ($photos as $photo) {
			       		    
			       		    $cnt++;
			              					
							// Filesystem
							if (get_option('symposium_img_db') == "on") {
								$img_src = WP_CONTENT_URL."/plugins/wp-symposium/get_album_item.php?iid=".$photo->iid."&size=photo";
								$thumb_src = WP_CONTENT_URL."/plugins/wp-symposium/get_album_item.php?iid=".$photo->iid."&size=thumbnail";
							} else {
								if (get_option("symposium_gallery_show_resized") == 'on') {
				                	$img_src = get_option('symposium_img_url').'/members/'.$album->owner.'/media/'.$album->gid.'/show_'.$photo->name;
								} else {
				                	$img_src = get_option('symposium_img_url').'/members/'.$album->owner.'/media/'.$album->gid.'/'.$photo->name;
								}
			        	        $thumb_src = get_option('symposium_img_url').'/members/'.$album->owner.'/media/'.$album->gid.'/thumb_'.$photo->name;
							}
			
			               	$html .= '<div class="symposium_photo_outer">';
			           			$html .= '<div class="symposium_photo_inner">';
			      					$html .= '<div class="symposium_photo_cover">';
			      						$html .= '<a class="symposium_photo_cover_action wps_gallery_album" href="'.$img_src.'" rel="symposium_gallery_photos_'.$album->gid.'" title="'.stripslashes($photo->title).'">';
				        					$html .= '<img class="symposium_photo_image" style="width:'.$thumbnail_size.'px; height:'.$thumbnail_size.'px;" src="'.$thumb_src.'" />';
				        				$html .= '</a>';
			     					$html .= '</div>';
			       				$html .= '</div>';
			     			$html .= '</div>';
	
				       		if ($cnt == $preview_count) {
				       		    $html .= '<div id="wps_gallery_comment_more" style="cursor:pointer">'.__('more...', 'wp-symposium').'<div style="clear:both"></div></div>';
				       		}   		
			      				
			       		}
			       		
			       		$html .= '</div>';
					
					} else {
					
				      	 $html .= __("No photos yet.", "wp-symposium");
				     
					}
	
					$html .= '</div>';
				
					if ($album_count == $page_length) { break; }
					
				}
			}

		}
		$html .= "<div style='clear:both;text-align:center; margin-top:20px; width:100%'><a href='javascript:void(0)' id='showmore_gallery'>".__("more...", "wp-symposium")."</a></div>";
		
		$html .= '</div>';
			
	} else {
		$html .= '<div style="clear:both;text-align:center; width:100%;">'.__('No albums to show', 'wp-symposium').".</div>";
	}
	
	$html = $total_count."[split]".$html;
	echo $html;	
	exit;
}

// Select cover photo for album
if ($_POST['action'] == 'menu_gallery_select_cover') {
	
	global $wpdb;
	if (isset($_POST['item_id'])) { $item_id = $_POST['item_id']; } else { $item_id = 0; }
	if (isset($_POST['gallery_id'])) { $gallery_id = $_POST['gallery_id']; } else { $gallery_id = 0; }

	if ($item_id > 0 && $gallery_id > 0) {
		$wpdb->query( $wpdb->prepare( "UPDATE ".$wpdb->prefix."symposium_gallery_items SET cover = 'on' WHERE gid = %d AND iid = %d", $gallery_id, $item_id  ) );  
		$wpdb->query( $wpdb->prepare( "UPDATE ".$wpdb->prefix."symposium_gallery_items SET cover = '' WHERE gid = %d AND iid != %d", $gallery_id, $item_id  ) );  
		echo 'OK';
	} else {
		echo 'No item ID passed';
	}

	exit;
}

// Change sharing status
if ($_POST['action'] == 'menu_gallery_change_share') {

	global $wpdb;

	if (isset($_POST['album_id'])) { $album_id = $_POST['album_id']; } else { $album_id = 0; }
	if (isset($_POST['new_share'])) { $new_share = $_POST['new_share']; } else { $new_share = ''; }

	if ($album_id > 0 && $new_share != '') {
		$wpdb->query( $wpdb->prepare( "UPDATE ".$wpdb->prefix."symposium_gallery SET sharing = %s WHERE gid = %d", $new_share, $album_id  ) );  
		echo 'OK';
	} else {
		echo 'Wrong parameters';
	}

	exit;
}


// Delete photo
if ($_POST['action'] == 'menu_gallery_manage_delete') {

    global $wpdb, $current_user;
	
    $item_id = 0;
    if (isset($_POST['item_id'])) { $item_id = $_POST['item_id']; }
 
    if ($item_id != 0) {

	// Get owner
	$this_owner = stripslashes($wpdb->get_var($wpdb->prepare("SELECT owner FROM ".$wpdb->prefix."symposium_gallery_items WHERE iid = %d", $item_id)));

	// check to see if storing in filesystem or database
	if (get_option('symposium_img_db') == "on") {

		// when deleting item, the fields in the record are deleted too, nothing to do here

	} else {

		// physically delete files from filesystem
		
		// get album ID
	    $sql = "SELECT gid, name FROM ".$wpdb->prefix."symposium_gallery_items WHERE iid = %d";
    	$photo = $wpdb->get_row($wpdb->prepare($sql, $item_id));	

		// delete files...
		$thumb_src = WP_CONTENT_DIR.'/wps-content/members/'.$this_owner.'/media/'.$photo->gid.'/thumb_'.$photo->name;
		$show_src = WP_CONTENT_DIR.'/wps-content/members/'.$this_owner.'/media/'.$photo->gid.'/show_'.$photo->name;
		$original_src = WP_CONTENT_DIR.'/wps-content/members/'.$this_owner.'/media/'.$photo->gid.'/'.$photo->name;
		unlink($thumb_src);	
		unlink($show_src);	
		unlink($original_src);	

	}

	// remove from album table
	$wpdb->query( $wpdb->prepare( "DELETE FROM ".$wpdb->prefix."symposium_gallery_items WHERE iid = %d", $item_id  ) );  

	// Rebuild activity entry
	add_to_create_activity_feed($photo->gid);
	
	echo 'OK';

    } else {
      echo __('No item ID passed', 'wp-symposium');
    }

    exit;   
    
}

// Delete all photos in an album
if ($_POST['action'] == 'menu_gallery_manage_delete_all') {

    global $wpdb, $current_user;
	
    $album_id = 0;
    if (isset($_POST['album_id'])) { $album_id = $_POST['album_id']; }
 
    if ($album_id != 0) {

	// Get owner
	$this_owner = stripslashes($wpdb->get_var($wpdb->prepare("SELECT owner FROM ".$wpdb->prefix."symposium_gallery WHERE gid = %d", $album_id)));

	// check to see if storing in filesystem or database
	if (get_option('symposium_img_db') == "on") {

		$wpdb->query( $wpdb->prepare( "DELETE FROM ".$wpdb->prefix."symposium_gallery_items WHERE gid = %d AND groupid=0", $album_id  ) );  

	} else {

		// physically delete files from filesystem within album folder
		$dir = WP_CONTENT_DIR.'/wps-content/members/'.$this_owner.'/media/'.$album_id;
		$handle = opendir($dir);
		while (($file = readdir($handle)) !== false) {
			if (!is_dir($file)) {
				unlink($dir.'/'.$file);	
			}
		}
		closedir($handle);

		$wpdb->query( $wpdb->prepare( "DELETE FROM ".$wpdb->prefix."symposium_gallery_items WHERE gid = %d AND groupid=0", $album_id  ) );  
		
	}
	
	// Delete entire entry from activity
	// First get name of album
	$sql = "SELECT name FROM ".$wpdb->prefix."symposium_gallery WHERE gid = %d";
	$name = $wpdb->get_var($wpdb->prepare($sql, $album_id));
	// Then delete
	$sql = "DELETE FROM ".$wpdb->base_prefix."symposium_comments WHERE subject_uid = ".$current_user->ID." AND author_uid = ".$current_user->ID." AND comment LIKE '%".$name."%' AND type = 'gallery'";
	$wpdb->query($sql);

	echo 'OK';

    } else {
      echo __('No item ID passed', 'wp-symposium');
    }

    exit;   
    
}

// Rename photo title
if ($_POST['action'] == 'menu_gallery_manage_rename') {

    global $wpdb, $current_user;
	
    $item_id = 0;
    if (isset($_POST['item_id'])) { $item_id = $_POST['item_id']; }
 
    $new_name = '';
    if (isset($_POST['new_name'])) { $new_name = $_POST['new_name']; }

    if ($item_id != 0 && $new_name != '') {
      $wpdb->query( $wpdb->prepare( "UPDATE ".$wpdb->prefix."symposium_gallery_items SET title = %s WHERE iid = %d", addslashes($new_name), $item_id  ) );  
      echo 'OK';
    } else {
      echo __('Please enter a title', 'wp-symposium');
    }

    exit;   
    
}

// Manage album (titles, delete item)
if ($_POST['action'] == 'menu_gallery_manage') {

	global $wpdb, $current_user;
	
	$album_id = 0;
    	if (isset($_POST['album_id'])) { $album_id = $_POST['album_id']; }

    	$sql = "SELECT * FROM ".$wpdb->prefix."symposium_gallery WHERE gid = %d";
    	$this_album = $wpdb->get_row($wpdb->prepare($sql, $album_id));	
	
	$html = '';

     	$html .= '<div id="gallery_options">';
  
     	if ($album_id != 0) {

		$html .= '<input title="'.$album_id.'" type="submit" class="symposium_photo_delete_all symposium-button" value="'.__('Delete all', 'wp-symposium').'" /> ';

		$share = $this_album->sharing;
		$album_owner = $this_album->owner;

		$html .= __('Share with:', 'wp-symposium').' ';
		$html .= '<select title = '.$album_id.' id="gallery_share">';
			$html .= "<option value='nobody'";
				if ($share == 'nobody') { $html .= ' SELECTED'; }
				$html .= '>'.__('Nobody', 'wp-symposium').'</option>';
			$html .= "<option value='friends only'";
				if ($share == 'friends only') { $html .= ' SELECTED'; }
				$html .= '>'.__('Friends Only', 'wp-symposium').'</option>';
			$html .= "<option value='everyone'";
				if ($share == 'everyone') { $html .= ' SELECTED'; }
				$html .= '>'.__('Everyone', 'wp-symposium').'</option>';
			$html .= "<option value='public'";
				if ($share == 'public') { $html .= ' SELECTED'; }
				$html .= '>'.__('Public', 'wp-symposium').'</option>';
		$html .= '</select>';
		$html .= " <img id='symposium_album_sharing_save' style='display:none' src='".get_option('symposium_images')."/busy.gif' /><br />";

		$html .= '<a class="symposium_album_cover_action" href="javascript:void(0);" title="'.$album_id.'">'.__('Back to album', 'wp-symposium').'</a>';
	        $html .= "<div id='symposium_album_content'>";
	        $html .= '<div class="album_name">'.$this_album->name.'</div>';
			$html .= '<p style="clear:both">&darr; Select album cover</p>';
         
	     	$sql = "SELECT * FROM ".$wpdb->prefix."symposium_gallery_items WHERE gid = %d ORDER BY iid DESC";
	     	$photos = $wpdb->get_results($wpdb->prepare($sql, $album_id));	
     	
	     	if ($photos) {

	        	foreach ($photos as $photo) {

	                	// Add photo
	                	$html .= '<div class="symposium_photo_row" id="symposium_photo_row_'.$photo->iid.'">';

							if (get_option('symposium_img_db') == "on") {
								// Database
								$thumb_src = WP_CONTENT_URL."/plugins/wp-symposium/get_album_item.php?iid=".$photo->iid."&size=thumbnail";
							} else {
								// Filesystem
		        			    $thumb_src = get_option('symposium_img_url').'/members/'.$album_owner.'/media/'.$album_id.'/thumb_'.$photo->name;
							}
	
							$html .= '<div class="symposium_photo_cover_choice">';
							$html .= '<input type="radio" id="'.$photo->gid.'" title="'.$photo->iid.'" class="symposium_photo_select_cover_button" name="cover"';
							if ($photo->cover == 'on') { $html .= ' CHECKED'; }
							$html .= ' />';
							$html .= '</div>';
	
				            $html .= '<div class="symposium_photo_outer " style="padding-top:0px">';
				        	$html .= '<img class="symposium_photo_image" src="'.$thumb_src.'" />';
							$html .= '</div>';
	    		
							$html .= '<div>';
	
				           		$html .= '<input type="text" class="symposium_photo_title_input input-field" style="width:50%;margin-bottom:4px; margin-left: 0px;" id="symposium_photo_'.$photo->iid.'" value="'.stripslashes($photo->title).'" /><br />';
			        	   		$html .= '<input title="'.$photo->iid.'" type="submit" class="symposium_photo_delete symposium-button" style="width:60px;" value="'.__('Delete', 'wp-symposium').'" /> ';
			           			$html .= '<input title="'.$photo->iid.'" type="submit" class="symposium_photo_update symposium-button" style="width:60px;" value="'.__('Update', 'wp-symposium').'" /> ';
								$html .= "<img id='symposium_photo_saving_".$photo->iid."' style='display:none' src='".get_option('symposium_images')."/busy.gif' />";
	    			
		    				$html .= '</div>';
		    				
		    			$html .= '</div>';
        				
        		}
     	      	
	     	}
      
	      	$html .= "</div>";
    	}
	
	echo $html;
	exit;
	
}

// List albums / Create album form
if ($_POST['action'] == 'menu_gallery') {

	global $wpdb, $current_user;
	
	$album_id = 0;
	if (isset($_POST['album_id'])) { $album_id = $_POST['album_id']; }
	$user_page = $_POST['uid1'];
	$user_id = $current_user->ID;
	
	$html = '';
	
    if ($user_page == $user_id || symposium_get_current_userlevel() == 5) {

     		$html .= '<input type="submit" class="symposium_new_album_button symposium-button" value="'.__("Create", "wp-symposium").'" />';
	
			if ($album_id != 0) {
				$html .= '<input type="submit" class="symposium-button" style="width:75px" title="'.$album_id.'" id="symposium_manage_album_button" value="'.__("Manage", "wp-symposium").'" />';
         		$html .= '<input type="submit" class="symposium-button" style="width:75px" title="'.$album_id.'" id="symposium_delete_album_button" value="'.__("Delete", "wp-symposium").'" />';
      	  	}

	}

	// Get current album
	if ($album_id > 0) {
		
	    $sql = "SELECT * FROM ".$wpdb->prefix."symposium_gallery WHERE gid = %d";
	    $this_album = $wpdb->get_row($wpdb->prepare($sql, $album_id));	      	
	    
		$html .= '<div id="symposium_gallery_breadcrumb">';
	
			$html .= '<a href="javascript:void(0);" id="symposium_gallery_top">'.__('All albums', 'wp-symposium').'</a>';
	
		   	if ($this_album->parent_gid != 0) {
				$sql = "SELECT gid, name FROM ".$wpdb->prefix."symposium_gallery WHERE gid = %d";
				$parent_album = $wpdb->get_row($wpdb->prepare($sql, $this_album->parent_gid));	      	
				$html .= '&nbsp;&rarr;&nbsp;<a href="javascript:void(0);" title="'.$parent_album->gid.'" id="symposium_gallery_up">'.stripslashes($parent_album->name).'</a>';
		    }           	

		$html .= '<div class="album_name">'.stripslashes($this_album->name).'</div>';

		$html .= '</div>';
	
	
	}

   	$html .= "<div id='symposium_album_covers'>";

   	$sql = "SELECT * FROM ".$wpdb->prefix."symposium_gallery WHERE owner = %d AND (parent_gid = %d OR parent_gid = 0) ORDER BY updated DESC";
       $albums = $wpdb->get_results($wpdb->prepare($sql, $user_page, $album_id));	
       
	// Show album covers
   	if ($albums) {

		$html = apply_filters('symposium_gallery_header', $html);
 
       	foreach ($albums as $album) {

			// Get cover image
	     	$sql = "SELECT * FROM ".$wpdb->prefix."symposium_gallery_items WHERE gid = %d AND cover = 'on'";
			$cover = $wpdb->get_row($wpdb->prepare($sql, $album->gid));
			
			if ($cover) {

				if (get_option('symposium_img_db') == "on") {
					// Database
					$thumb_src = WP_CONTENT_URL."/plugins/wp-symposium/get_album_item.php?iid=".$cover->iid."&size=thumbnail";
				} else {
					// Filesystem
	        	    $thumb_src = get_option('symposium_img_url').'/members/'.$cover->owner.'/media/'.$album->gid.'/thumb_'.$cover->name;
				}
				
			} else {
				$thumb_src = get_option('symposium_images')."/unknown.jpg";
			}

       		// Add cover
        		if ($album->parent_gid == $album_id) {
    				$html .= '<div class="symposium_album_outer">';
       				$html .= '<div class="symposium_album_inner">';
    						$html .= '<div class="symposium_album_cover">';
     							$html .= '<a class="symposium_album_cover_action" href="javascript:void(0);" title="'.$album->gid.'">';
     								$html .= '<img class="symposium_album_image" src="'.$thumb_src.'" />';
     								$html .= '</a>';
    							$html .= '</div>';
     						$html .= '</div>';
     					$html .= '<div class="symposium_album_title">'.stripslashes($album->name).'</div>';
      				$html .= '</div>';
 				}
       
       	}
       		
       } else {

     		if ($user_page == $user_id) {
        	$html .= "<div class='symposium_new_album_button symposium_menu_gallery_alert'>".__("Start by creating an album", "wp-symposium")."</div>";
        } else {
        	$html .= __("No albums yet.", "wp-symposium");
       	}
       		
       }
   	
 		$html .= "</div>";

		// Show contents of album (so long as in an album)
		if ($album_id > 0) {
			$html .= "<div id='symposium_album_content'>";
   
  			$sql = "SELECT * FROM ".$wpdb->prefix."symposium_gallery_items WHERE gid = %d ORDER BY iid DESC";
  			$photos = $wpdb->get_results($wpdb->prepare($sql, $album_id));	

     		if ($user_page == $user_id) {

				// Show maximum file upload size as set in PHP.INI to admin's
				if (symposium_get_current_userlevel($current_user->ID) == 5) {
					$html .= '<p>As set in PHP.INI, the upload_max_filesize is: '.ini_get('upload_max_filesize').'<br />(this message is only shown to site administrators)</p>';
				} else {
					$html .= '<p>'.__('The maximum size of uploaded files is', 'wp-symposium').' '.ini_get('upload_max_filesize').'.</p>';
				}

				$html .= "<div id='symposium_user_login' style='display:none'>".strtolower($current_user->user_login)."</div>";
				$html .= "<div id='symposium_user_email' style='display:none'>".strtolower($current_user->user_email)."</div>";

				$html .= '<div class="symposium_menu_gallery_alert"><input type="file" id="menu_gallery_file_upload" name="file_upload" /></div>';
       		}
  	
    		if ($photos) {
	
	       		foreach ($photos as $photo) {
	              					
	                // Add photo
					$thumbnail_size = ($value = get_option("symposium_gallery_thumbnail_size")) ? $value : '75';
	
					// Filesystem
					if (get_option('symposium_img_db') == "on") {
						$img_src = WP_CONTENT_URL."/plugins/wp-symposium/get_album_item.php?iid=".$photo->iid."&size=photo";
						$thumb_src = WP_CONTENT_URL."/plugins/wp-symposium/get_album_item.php?iid=".$photo->iid."&size=thumbnail";
					} else {
						if (get_option("symposium_gallery_show_resized") == 'on') {
		                	$img_src = get_option('symposium_img_url').'/members/'.$user_page.'/media/'.$album_id.'/show_'.$photo->name;
						} else {
		                	$img_src = get_option('symposium_img_url').'/members/'.$user_page.'/media/'.$album_id.'/'.$photo->name;
						}
	        	        $thumb_src = get_option('symposium_img_url').'/members/'.$user_page.'/media/'.$album_id.'/thumb_'.$photo->name;
					}

	               	$html .= '<div class="symposium_photo_outer">';
	           			$html .= '<div class="symposium_photo_inner">';
	      						$html .= '<div class="symposium_photo_cover">';
	        					$html .= '<a class="symposium_photo_cover_action" href="'.$img_src.'" rel="symposium_gallery_photos" title="'.stripslashes($photo->title).'">';
	        						$html .= '<img class="symposium_photo_image" style="width:'.$thumbnail_size.'px; height:'.$thumbnail_size.'px;" src="'.$thumb_src.'" />';
	        					$html .= '</a>';
	     						$html .= '</div>';
	       					$html .= '</div>';
	     				$html .= '</div>';
	      				
	       		}
  	
    	} else {
  	
          	 	$html .= __("No photos yet.", "wp-symposium");
         
    	}
   
	$html .= "</div>";
}	

	// Create new album form
	if ($album_id != '') {
		$this_album = stripslashes($wpdb->get_var($wpdb->prepare("SELECT name FROM ".$wpdb->prefix."symposium_gallery WHERE gid = %d", $album_id)));
		$this_id = $album_id; 
	} else {
		$this_album = 'None';
		$this_id = 0;
	}

	$html .= "<div id='symposium_create_gallery'>";

		$html .= '<div class="new-topic-subject label">'.__("Name of new album", "wp-symposium").'</div>';
		$html .= "<input id='symposium_new_album_title' class='new-topic-subject-input' type='text'>";

		$html .= "<div class='symposium_create_sub_gallery label'>";
		$html .= "<input type='checkbox' title='".$this_id."' id='symposium_create_sub_gallery_select'> ".__("Create as a sub-album of ".$this_album, "wp-symposium");
		$html .= "</div>";
		
		$html .= "<div style='margin-top:10px'>";
		$html .= '<input id="symposium_new_album" type="submit" class="symposium-button" style="float: left" value="'.__("Create", "wp-symposium").'" />';
		$html .= '<input id="symposium_cancel_album" type="submit" class="symposium-button clear" onClick="javascript:void(0)" value="'.__("Cancel", "wp-symposium").'" />';
		$html .= "</div>";

	$html .= "</div>";

	echo $html;
	exit;
	
}

// Delete album
if ($_POST['action'] == 'delete_album') {

	global $wpdb, $current_user;	
	$album_id = $_POST['album_id'];

    	// Check for children albums first
	if (symposium_get_current_userlevel() == 5) {
		$sql = "SELECT * FROM ".$wpdb->prefix."symposium_gallery WHERE parent_gid = %d ORDER BY updated DESC";
		$albums = $wpdb->get_results($wpdb->prepare($sql, $album_id));	
	} else {
		$sql = "SELECT * FROM ".$wpdb->prefix."symposium_gallery WHERE owner = %d AND parent_gid = %d ORDER BY updated DESC";
		$albums = $wpdb->get_results($wpdb->prepare($sql, $current_user->ID, $album_id));	
	}

	if ($albums) {
      		echo __('Please delete sub albums first.', 'wp-symposium');
    	} else {

      		$sql = "SELECT * FROM ".$wpdb->prefix."symposium_gallery_items WHERE gid = %d";
      		$photos = $wpdb->get_results($wpdb->prepare($sql, $album_id));	
      		if ($photos) {
            		echo __('Please delete album contents first.', 'wp-symposium');
			exit;
      		} else {        

				// Get owner
				$this_owner = stripslashes($wpdb->get_var($wpdb->prepare("SELECT owner FROM ".$wpdb->prefix."symposium_gallery WHERE gid = %d", $album_id)));

	      			$wpdb->query("DELETE FROM ".$wpdb->prefix."symposium_gallery_items WHERE gid = ".$album_id." AND owner = ".$this_owner);    
	      			$wpdb->query("DELETE FROM ".$wpdb->prefix."symposium_gallery WHERE gid = ".$album_id." AND owner = ".$this_owner);

				// if using filesystem, remove the folder
				$dir = WP_CONTENT_DIR.'/wps-content/members/'.$this_owner.'/media/'.$album_id;
				if (file_exists($dir)) {
					rmdir($dir);
					}
          		}

           		echo 'OK';

    	}

		exit;
}

// Create album
if ($_POST['action'] == 'create_album') {

	global $wpdb, $current_user;
	
	$name = $_POST['name'];
	$sub_album = $_POST['sub_album'];
	if ($sub_album == true) {
		$parent = $_POST['parent'];
	} else {
		$parent = 0;
	}

	// Create new album
	$wpdb->query( $wpdb->prepare( "
	INSERT INTO ".$wpdb->prefix."symposium_gallery
	( 	parent_gid, 
		name,
		description, 
		owner, 
		sharing, 
		editing, 
		created, 
		updated, 
		is_group
	)
	VALUES ( %d, %s, %s, %d, %s, %s, %s, %s, %s )", 
	array(
		$parent, 
		$name,
		'', 
		$current_user->ID, 
		'everyone', 
		'nobody', 
		date("Y-m-d H:i:s"),
		date("Y-m-d H:i:s"),
		''
		) 
	) );

	echo $wpdb->insert_id;
	exit;

}		

// Widget
if ($_POST['action'] == 'Gallery_Widget') {

	$albumcount = $_POST['albumcount'];
	do_Gallery_Widget($albumcount);
}
?>

	
