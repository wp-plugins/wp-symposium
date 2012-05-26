<?php
include_once('../../../../wp-config.php');

// Member search (autocomplete)
if (isset($_GET['term'])) {
		
	global $wpdb, $current_user;	
	$return_arr = array();
	$term = $_GET['term'];

	$sql = "SELECT g.gid, g.owner, g.name, u.display_name, g.sharing FROM ".$wpdb->prefix."symposium_gallery g
	LEFT JOIN ".$wpdb->base_prefix."users u ON g.owner = u.ID
	WHERE ( ( name LIKE '%".$term."%') OR ( display_name LIKE '%".$term."%') ) AND u.display_name is not null
	ORDER BY name LIMIT 0,25";
	
	$list = $wpdb->get_results($sql);
	
	if ($list) {
		foreach ($list as $item) {

			// check for privacy
			if ( ($item->owner == $current_user->ID) || (strtolower($item->sharing) == 'public') || (is_user_logged_in() && strtolower($item->sharing) == 'everyone') || (strtolower($item->sharing) == 'public') || (strtolower($item->sharing) == 'friends only' && symposium_friend_of($item->owner, $current_user->ID)) || symposium_get_current_userlevel() == 5) {
				
				$row_array['id'] = $item->gid;	
				$row_array['owner'] = $item->owner;
				$row_array['display_name'] = $item->display_name;
				$row_array['name'] = $item->name;
				$row_array['avatar'] = get_avatar($item->owner, 40);
				
		        array_push($return_arr,$row_array);
		        
			}
		}
	}

	echo json_encode($return_arr);
	exit;

}


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

			// DB or Filesystem?
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
	  					$images .= '<a class="symposium_photo_cover_action wps_gallery_album" data-iid="'.$photo->iid.'" href="'.$img_src.'" rev="'.$cnt.'" rel="symposium_gallery_photos_'.$aid.'" title="'.$name.'">';
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

// Comments for photo
if ($_POST['action'] == 'symposium_get_photo_comments') {

	global $wpdb;	
	$photo_id = $_POST['photo_id'];

	$sql = "SELECT c.*, u.display_name FROM ".$wpdb->base_prefix."symposium_comments c 
		LEFT JOIN ".$wpdb->base_prefix."users u ON c.author_uid = u.ID 
		WHERE c.comment_parent = 0 AND c.type = 'photo' AND c.subject_uid = %d ORDER BY c.cid DESC";

	$comments = $wpdb->get_results($wpdb->prepare($sql, $photo_id));	
	
	$comments_array = array();
	foreach ($comments as $comment) {
		$add = array (
			'ID' => $comment->cid,
			'author_id' => $comment->author_uid,
			'avatar' => get_avatar($comment->author_uid, 32),
			'display_name' => $comment->display_name,
			'display_name_link' => symposium_profile_link($comment->author_uid),
			'comment' => symposium_buffer(symposium_make_url(stripslashes($comment->comment))),
			'timestamp' => symposium_time_ago($comment->comment_timestamp)
		);
		array_push($comments_array, $add);
	}
	
	echo json_encode($comments_array);

	exit;
	
}	

// Add comment to photo
if ($_POST['action'] == 'symposium_add_photo_comment') {

	global $wpdb, $current_user;
	
	if (is_user_logged_in()) {
			
		$photo_id = $_POST['photo_id'];
		$comment = $_POST['comment'];
	
		// Insert comment
		$wpdb->query( $wpdb->prepare( "
		INSERT INTO ".$wpdb->prefix."symposium_comments
		( 	subject_uid, 
			author_uid,
			comment_parent, 
			comment_timestamp, 
			comment, 
			is_group, 
			type
		)
		VALUES ( %d, %d, %d, %s, %s, %s, %s )", 
		array(
			$photo_id,
			$current_user->ID, 
			0,
			date("Y-m-d H:i:s"), 
			$comment, 
			'',
			'photo'
			) 
		) );
		
		// get album_id and photo title
		$sql = "SELECT g.name,i.title, i.gid, g.owner FROM ".$wpdb->prefix."symposium_gallery_items i LEFT JOIN ".$wpdb->prefix."symposium_gallery g ON i.gid = g.gid WHERE iid = %d";
		$info = $wpdb->get_row($wpdb->prepare($sql, $photo_id));
		$aid = $info->gid;
		$title = $info->name;
		$name = $info->title;
		$owner = $info->owner;
						
		// Add activity post
		$msg = __("Commented on", "wp-symposium").' '.$name;
		$thumbnail_size = (get_option("symposium_gallery_thumbnail_size") != '') ? get_option("symposium_gallery_thumbnail_size") : 75;
		

		// Include reference to other images in album so they appear in gallery popup, but show photo commented on
		$sql = "SELECT * FROM ".$wpdb->prefix."symposium_gallery_items WHERE gid = %d";
		$photos = $wpdb->get_results($wpdb->prepare($sql, $aid));

		$rev = 1;
		foreach($photos as $photo) {

			// DB or Filesystem
			if (get_option('symposium_img_db') == "on") {
				$img_src = WP_CONTENT_URL."/plugins/wp-symposium/get_album_item.php?iid=".$photo->iid."&size=photo";
				$thumb_src = WP_CONTENT_URL."/plugins/wp-symposium/get_album_item.php?iid=".$photo->iid."&size=thumbnail";
			} else {
				if (get_option("symposium_gallery_show_resized") == 'on') {
	            	$img_src = get_option('symposium_img_url').'/members/'.$owner.'/media/'.$aid.'/show_'.$photo->name;
				} else {
	            	$img_src = get_option('symposium_img_url').'/members/'.$owner.'/media/'.$aid.'/'.$photo->name;
				}
		        $thumb_src = get_option('symposium_img_url').'/members/'.$owner.'/media/'.$aid.'/thumb_'.$photo->name;
			}
			if ($photo->iid == $photo_id) {
    		   	$images .= '<div class="symposium_photo_outer" style="margin-right:5px; margin-bottom: 5px; width:'.$thumbnail_size.'px; height:'.$thumbnail_size.'px;">';
		   			$images .= '<div class="symposium_photo_inner">';
						$images .= '<div class="symposium_photo_cover">';
							$images .= '<a class="symposium_photo_cover_action wps_gallery_album" data-iid="'.$photo->iid.'" href="'.$img_src.'" rev="'.$rev.'" rel="symposium_gallery_photos_'.$aid.'" title="'.$title.'">';
								$images .= '<img class="symposium_photo_image" style="width:'.$thumbnail_size.'px; height:'.$thumbnail_size.'px;" src="'.$thumb_src.'" />';
							$images .= '</a>';
						$images .= '</div>';
					$images .= '</div>';
				$images .= '</div>';
			} else {
				$images .= '<a class="symposium_photo_cover_action wps_gallery_album" data-iid="'.$photo->iid.'" href="'.$img_src.'" rev="'.$rev.'" rel="symposium_gallery_photos_'.$aid.'" title="'.$title.'"></a>';
			}
			$rev++;
		}
		

		$msg = $msg.$images;	
		symposium_add_activity_comment($current_user->ID, $current_user->display_name, $current_user->ID, $msg, 'gallery');		
		
	}
		
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
				
					$html .= "<div id='wps_gallery_album_name_".$album->gid."' class='topic-post-header'>".stripslashes($album->name)."</div>";
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
			      						$html .= '<a class="symposium_photo_cover_action wps_gallery_album" data-iid="'.$photo->iid.'" rev="'.$cnt.'" href="'.$img_src.'" rel="symposium_gallery_photos_'.$album->gid.'" title="'.stripslashes($photo->title).'">';
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
	$sql = "DELETE FROM ".$wpdb->base_prefix."symposium_comments WHERE subject_uid = ".$current_user->ID." AND author_uid = ".$current_user->ID." AND comment LIKE '%".$name."%' AND (type = 'gallery' OR type = 'photo')";
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

			$cnt=0;
	
	       	foreach ($photos as $photo) {

				$cnt++;
	              					
	            //Add photo
				$thumbnail_size = ($value = get_option("symposium_gallery_thumbnail_size")) ? $value : '75';
	
				// DB or Filesystem
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
						$html .= '<a class="symposium_photo_cover_action wps_gallery_album" data-iid="'.$photo->iid.'" href="'.$img_src.'" rev="'.$cnt.'" rel="symposium_gallery_photos_'.$album_id.'" title="'.stripslashes($this_album->name).'">';
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

	
