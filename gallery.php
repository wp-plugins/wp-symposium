<?php
/*
Plugin Name: WP Symposium Gallery
Plugin URI: http://www.wpsymposium.com
Description: <strong><a href="http://wpswiki.com/index.php?title=Bronze_membership">BRONZE PLUGIN</a></strong>. Photo Albums for WP Symposium. Add [symposium-galleries] to display all galleries across the site.
Version: 12.11
Author: Simon Goodchild
Author URI: http://www.wpsymposium.com
License: Commercial
Requires at least: WordPress 3.0 and WP Symposium 11.8.21
*/

define('WPS_GALLERY_VER', '12.11');
if(!defined('WPS_PLUS')) define('WPS_PLUS', '12.11');
	
/*  Copyright 2010,2011,2012  Simon Goodchild  (info@wpsymposium.com)

EULA stands for End User Licensing Agreement. This is the agreement through which the software is licensed to the software user. 

END-USER LICENSE AGREEMENT FOR GALLERY PLUGIN 

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

// Get constants
require_once(dirname(__FILE__).'/default-constants.php');
	
function __wps__gallery(){}

/* ====================================================== ADMIN ====================================================== */

require_once(WPS_PLUGIN_DIR . '/functions.php');

// Check for updates
if ( ( get_option(WPS_OPTIONS_PREFIX."_gallery_version") != WPS_GALLERY_VER && is_admin()) ) {

 	// Update Version *******************************************************************************
	update_option(WPS_OPTIONS_PREFIX."_gallery_version", WPS_GALLERY_VER);
	__wps__gallery_activate();	
}

function __wps__gallery_activate() {
	
	global $wpdb;

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    require_once(WPS_PLUGIN_DIR . '/functions.php');

	// Create gallery table
	$table_name = $wpdb->prefix . "symposium_gallery";
	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {

		$sql = "CREATE TABLE " . $table_name . " (
		  gid int(11) NOT NULL AUTO_INCREMENT,
		  parent_gid int(11) NOT NULL DEFAULT 0,
		  name varchar(256) NOT NULL DEFAULT 'My album',
		  description text NOT NULL,
		  owner int(11) NOT NULL,
		  sharing varchar(16) NOT NULL DEFAULT 'friends',
		  editing varchar(16) NOT NULL DEFAULT 'owner',
		  created datetime NOT NULL,
		  updated datetime NOT NULL,
		  is_group varchar(2) NOT NULL,
		  PRIMARY KEY (gid)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;";

	    dbDelta($sql);
	
	}
	// Add index
	__wps__add_index($table_name, 'parent_gid');
	__wps__add_index($table_name, 'name');

	// Create gallery item table
	$table_name = $wpdb->prefix . "symposium_gallery_items";
	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {

		$sql = "CREATE TABLE " . $table_name . " (
		  iid int(11) NOT NULL AUTO_INCREMENT,
		  gid int(11),
		  owner int(11) NOT NULL,
		  name varchar(256) NOT NULL,
		  created datetime NOT NULL,
		  cover varchar(2) NOT NULL,
		  original MEDIUMBLOB NOT NULL,		  
		  photo MEDIUMBLOB NOT NULL,		  
		  thumbnail MEDIUMBLOB NOT NULL,
		  groupid int(11) NOT NULL,
		  title varchar(256) NOT NULL,
		  photo_order int(11) NOT NULL,
		  PRIMARY KEY (iid)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;";

	    dbDelta($sql);	
	}
	// Add index
	__wps__add_index($table_name, 'gid');
	
	// Set up default option values
	update_option(WPS_OPTIONS_PREFIX."_gallery_show_resized", 'on');
	update_option(WPS_OPTIONS_PREFIX."_gallery_thumbnail_size", 75);
	update_option(WPS_OPTIONS_PREFIX."_gallery_page_length", 10);
	update_option(WPS_OPTIONS_PREFIX."_gallery_preview", 5);
	
}

function __wps__gallery_deactivate() {
}

function __wps__gallery_uninstall() {
}

register_activation_hook(__FILE__,'__wps__gallery_activate');
register_deactivation_hook(__FILE__, '__wps__gallery_deactivate');
register_uninstall_hook(__FILE__, '__wps__gallery_uninstall');

/* ====================================================== ADD PLUGIN JAVASCRIPT TO WORDPRESS ====================================================== */

function __wps__gallery_init()
{

}
add_action('init', '__wps__gallery_init');

/* ===================================================================== WIDGETS ======================================================================== */

add_action( 'widgets_init', '__wps__gallery_load_widgets' );

function __wps__gallery_load_widgets() {
	register_widget( 'Gallery_Widget' );
}

class Gallery_Widget extends WP_Widget {

	function Gallery_Widget() {
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'widget_gallery', 'description' => __('Shows albums that have had new items recently uploaded into them.', WPS_TEXT_DOMAIN) );
		
		/* Widget control settings. */
		$control_ops = array( 'id_base' => 'gallery-widget' );
		
		/* Create the widget. */
		$this->WP_Widget( 'gallery-widget', WPS_WL_SHORT.': '.__('Gallery', WPS_TEXT_DOMAIN), $widget_ops, $control_ops );
	}
	
	// This is shown on the page
	function widget( $args, $instance ) {
		global $wpdb, $current_user;
		wp_get_current_user();
	
		extract( $args );
				
		// Get options
		$wtitle = apply_filters('widget_title', $instance['wtitle'] );
		$albumcount = apply_filters('widget_postcount', $instance['albumcount'] );
		
		// Start widget
		echo $before_widget;
		echo $before_title . $wtitle . $after_title;
		
		if (get_option(WPS_OPTIONS_PREFIX.'_ajax_widgets') == 'on') {		
			// Parameters for AJAX
			echo '<div id="symposium_Gallery_Widget">';
			echo "<img src='".get_option(WPS_OPTIONS_PREFIX.'_images')."/busy.gif' />";
			echo '<div id="symposium_Gallery_Widget_albumcount" style="display:noneX">'.$albumcount.'</div>';
			echo '</div>';
		} else {
			__wps__do_Gallery_Widget($albumcount);			
		}

		
		// End content
		
		echo $after_widget;
		// End widget
	}
	
	// This updates the stored values
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		
		/* Strip tags (if needed) and update the widget settings. */
		$instance['wtitle'] = strip_tags( $new_instance['wtitle'] );
		$instance['albumcount'] = strip_tags( $new_instance['albumcount'] );
		return $instance;
	}
	
	// This is the admin form for the widget
	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'wtitle' => 'Recent photos', 'albumcount' => '5' );
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<p>
			<label for="<?php echo $this->get_field_id( 'wtitle' ); ?>"><?php echo __('Widget Title', WPS_TEXT_DOMAIN); ?>:</label>
			<input id="<?php echo $this->get_field_id( 'wtitle' ); ?>" name="<?php echo $this->get_field_name( 'wtitle' ); ?>" value="<?php echo $instance['wtitle']; ?>" />
		<br /><br />
			<label for="<?php echo $this->get_field_id( 'albumcount' ); ?>"><?php echo __('Max number of albums', WPS_TEXT_DOMAIN); ?>:</label>
			<input id="<?php echo $this->get_field_id( 'albumcount' ); ?>" name="<?php echo $this->get_field_name( 'albumcount' ); ?>" value="<?php echo $instance['albumcount']; ?>" style="width: 30px" />
		</p>
		<?php
	}

}

// Shared function for AJAX and NON-AJAX mode of widget
function __wps__do_Gallery_Widget($albumcount) {
	
	global $wpdb, $current_user;
	
	$shown_aid = "";
	$shown_count = 0;

	// Get profile URL worked out
	$profile_url = __wps__get_url('profile');
	$q = __wps__string_query($profile_url);

	// Content of widget
	$sql = "SELECT * FROM ".$wpdb->prefix."symposium_gallery g INNER JOIN ".$wpdb->base_prefix."users u ON g.owner = u.ID WHERE is_group != 'on' ORDER BY updated DESC LIMIT 0,50";
	$albums = $wpdb->get_results($sql);
		
	if ($albums) {

		echo "<div id='__wps__gallery_recent_activity'>";
			
			foreach ($albums as $album)
			{
				if ($shown_count < $albumcount) {

					if (strpos($shown_aid, $album->gid.",") === FALSE) { 

						if ( (is_user_logged_in() && strtolower($album->sharing) == 'everyone') || (strtolower($album->sharing) == 'public') || (strtolower($album->sharing) == 'friends only' && __wps__friend_of($album->owner, $current_user->ID)) ) {

							echo "<div class='__wps__gallery_recent_activity_row'>";		
								echo "<div class='__wps__gallery_recent_activity_row_avatar'>";
									echo get_avatar($album->owner, 32);
								echo "</div>";
								echo "<div class='__wps__gallery_recent_activity_row_post'>";
 									$text = __('added to ', WPS_TEXT_DOMAIN)." <a href='".$profile_url.$q."uid=".$album->owner."&embed=on&album_id=".$album->gid."'>".stripslashes($album->name)."</a>";
									echo "<a href='".$profile_url.$q."uid=".$album->owner."'>".$album->display_name."</a> ".$text." ".__wps__time_ago($album->updated);
								echo "</div>";
							echo "</div>";
						
							$shown_count++;
							$shown_aid .= $album->gid.",";							
						}
					}
				} else {
					break;
				}
			}

		echo "</div>";

	}
}

// Add [symposium-galleries] shortcode for site wide list of albums
function __wps__show_gallery() {
	
	global $wpdb, $current_user;
		
	$html = '';
	$html .= "<div class='__wps__wrapper'>";

	$term = "";
	if (isset($_GET['term'])) { $term .= strtolower($_GET['term']); }	
		
	$html .= "<div style='padding:0px'>";
	$html .= '<input type="text" id="gallery_member" autocomplete="off" name="gallery_member" class="gallery_member_box" value="'.$term.'" style="margin-right:10px" />';
	$html .= '<input id="gallery_go_button" type="submit" class="__wps__button" value="'.__("Search", WPS_TEXT_DOMAIN).'" />';
	$html .= "</div>";	
	
	$sql = "SELECT g.*, u.display_name FROM ".$wpdb->prefix."symposium_gallery g
			INNER JOIN ".$wpdb->base_prefix."users u ON u.ID = g.owner
			WHERE g.name LIKE '%".$term."%' 
			   OR u.display_name LIKE '%".$term."%' 
			ORDER BY gid DESC 
			LIMIT 0,50";

	$albums = $wpdb->get_results($sql);
	
	$album_count = 0;	
	$total_count = 0;
	
	if ($albums) {
		
		$page_length = (get_option(WPS_OPTIONS_PREFIX."_gallery_page_length") != '') ? get_option(WPS_OPTIONS_PREFIX."_gallery_page_length") : 10;

		$html .= "<div id='symposium_gallery_albums'>";
		
		foreach ($albums AS $album) {

			$total_count++;				

			// check for privacy
			if ( ($album->owner == $current_user->ID) || (strtolower($album->sharing) == 'public') || (is_user_logged_in() && strtolower($album->sharing) == 'everyone') || (strtolower($album->sharing) == 'friends only' && __wps__friend_of($album->owner, $current_user->ID)) || __wps__get_current_userlevel() == 5) {

				$sql = "SELECT COUNT(iid) FROM ".$wpdb->prefix."symposium_gallery_items WHERE gid = %d";
				$photo_count = $wpdb->get_var($wpdb->prepare($sql, $album->gid));	
				
				if ($photo_count > 0) {

					$sql = "SELECT * FROM ".$wpdb->prefix."symposium_gallery_items WHERE gid = %d ORDER BY photo_order DESC";
					$photos = $wpdb->get_results($wpdb->prepare($sql, $album->gid));	
					
					// Check that at least one actually exists
					$tmpDir = get_option(WPS_OPTIONS_PREFIX.'_img_path');
					$img_exists = false;
					if ($photos && get_option(WPS_OPTIONS_PREFIX.'_img_db') != "on") {
						foreach ($photos as $photo) {
		                	$img_src = '/members/'.$album->owner.'/media/'.$album->gid.'/'.$photo->name;
		                	if (file_exists($tmpDir.$img_src)) {
		                	    $img_exists = true;
		                	    break;
		                	}
						}
					} else {
						$img_exists = true;
					}
					
					if ($img_exists) {
						
						$html .= "<div id='__wps__album_content' style='padding-bottom:30px;'>";
	
							$html .= "<div id='wps_gallery_album_name_".$album->gid."' class='topic-post-header'>".stripslashes($album->name)."</div>";
							$html .= "<p>".__wps__profile_link($album->owner)."</p>";
				
							if ($photos) {
								
								$album_count++;
								
								$cnt = 0;
			
								$thumbnail_size = (get_option(WPS_OPTIONS_PREFIX."_gallery_thumbnail_size") != '') ? get_option(WPS_OPTIONS_PREFIX."_gallery_thumbnail_size") : 75;
								$html .= '<div id="wps_comment_plus" style="width:98%;height:'.($thumbnail_size+10).'px;overflow:hidden; ">';
					
								$preview_count = (get_option(WPS_OPTIONS_PREFIX."_gallery_preview") != '') ? get_option(WPS_OPTIONS_PREFIX."_gallery_preview") : 5;
					       		foreach ($photos as $photo) {
					       		    
					       		    $cnt++;
					              					
									if (get_option(WPS_OPTIONS_PREFIX.'_img_db') == "on") {
										
										$img_src = WP_CONTENT_URL."/plugins/wp-symposium/get_album_item.php?iid=".$photo->iid."&size=photo";
										$thumb_src = WP_CONTENT_URL."/plugins/wp-symposium/get_album_item.php?iid=".$photo->iid."&size=thumbnail";
										
									} else {

					                	$tmp_src = '/members/'.$album->owner.'/media/'.$album->gid.'/thumb_'.$photo->name;
              							if (file_exists($tmpDir.$tmp_src)) { 

											if (get_option(WPS_OPTIONS_PREFIX."_gallery_show_resized") == 'on') {
							                	$img_src = get_option(WPS_OPTIONS_PREFIX.'_img_url').'/members/'.$album->owner.'/media/'.$album->gid.'/show_'.$photo->name;
											} else {
							                	$img_src = get_option(WPS_OPTIONS_PREFIX.'_img_url').'/members/'.$album->owner.'/media/'.$album->gid.'/'.$photo->name;
											}
						        	        $thumb_src = get_option(WPS_OPTIONS_PREFIX.'_img_url').'/members/'.$album->owner.'/media/'.$album->gid.'/thumb_'.$photo->name;
					        	        
              							} else {
              							    $img_src = __wps__siteURL().get_option(WPS_OPTIONS_PREFIX.'_images').'/broken_file_link.png';
						        	        $thumb_src = __wps__siteURL().get_option(WPS_OPTIONS_PREFIX.'_images').'/broken_file_link.png';
              							}              							    
              							
									}
					
					               	$html .= '<div class="__wps__photo_outer">';
					           			$html .= '<div class="__wps__photo_inner">';
					      						$html .= '<div class="__wps__photo_cover">';
												$html .= '<a class="__wps__photo_cover_action wps_gallery_album" data-owner="'.$album->owner.'" data-iid="'.$photo->iid.'" data-name="'.stripslashes($photo->title).'" href="'.$img_src.'" rev="'.$cnt.'" rel="symposium_gallery_photos_'.$album->gid.'" title="'.stripslashes($album->name).'">';
					        						$thumbnail_size = (get_option(WPS_OPTIONS_PREFIX."_gallery_thumbnail_size") != '') ? get_option(WPS_OPTIONS_PREFIX."_gallery_thumbnail_size") : 75;
					        						$html .= '<img class="__wps__photo_image" style="width:'.$thumbnail_size.'px; height:'.$thumbnail_size.'px;" src="'.$thumb_src.'" />';
					        					$html .= '</a>';
					     						$html .= '</div>';
					       					$html .= '</div>';
					     				$html .= '</div>';
			
						       		if (count($photos) > $preview_count && $cnt == $preview_count) {
						       		    $html .= '<div id="wps_gallery_comment_more" style="cursor:pointer">'.__('more...', WPS_TEXT_DOMAIN).'<div style="clear:both"></div></div>';
						       		}   		
					      				
					       		}
					       		
					       		$html .= '</div>';							
							
							} else {
							
						      	 $html .= __("No photos yet.", WPS_TEXT_DOMAIN);
						     
							}
			
						$html .= '</div>';	
					}
				}	
	
				if ($album_count == $page_length) { break; }
				
			}
		
		}
	
		$html .= "<div style='clear:both;text-align:center; margin-top:20px; width:100%'><a href='javascript:void(0)' id='showmore_gallery'>".__("more...", WPS_TEXT_DOMAIN)."</a></div>";
		
		$html .= '</div>';
		
		
	}

	// Stores start value for more
	$html .= '<div id="symposium_gallery_start" style="display:none">'.$total_count.'</div>';
	$html .= '<div id="symposium_gallery_page_length" style="display:none">'.$page_length.'</div>';


	$html .= '</div>';
	return $html;
}
if (!is_admin()) {
	add_shortcode(WPS_SHORTCODE_PREFIX.'-galleries', '__wps__show_gallery');  
}

/* ====================================================== HOOKS/FILTERS INTO WORDPRESS/WP Symposium ====================================================== */

// Add plugin to admin menu via hook
function __wps__add_gallery_to_admin_menu()
{
	$hidden = get_option(WPS_OPTIONS_PREFIX.'_long_menu') == "on" ? '_hidden': '';
	add_submenu_page('symposium_debug'.$hidden, __('Gallery', WPS_TEXT_DOMAIN), __('Gallery', WPS_TEXT_DOMAIN), 'manage_options', WPS_DIR.'/gallery_admin.php');
}
add_action('__wps__admin_menu_hook', '__wps__add_gallery_to_admin_menu');

// ----------------------------------------------------------------------------------------------------------------------------------------------------------

// Add Menu item to Profile Menu through filter provided
// The menu picks up the id of div with id of menu_ (eg: menu_GALLERY) and will then run
// 'path-to/wp-symposium-GALLERY/ajax/GALLERY_functions.php' when clicked.
// It will pass $_POST['action'] set to menu_GALLERY to then be acted upon.
// See www.wpswiki.com for help

// ----------------------------------------------------------------------------------------------------------------------------------------------------------

function __wps__add_gallery_menu($html,$uid1,$uid2,$privacy,$is_friend,$extended,$share)  
{  
	global $current_user;

	if ( ($uid1 == $uid2) || (is_user_logged_in() && strtolower($privacy) == 'everyone') || (strtolower($privacy) == 'public') || (strtolower($privacy) == 'friends only' && $is_friend) || __wps__get_current_userlevel() == 5) {
  
		if ($uid1 == $uid2) {
			if (get_option(WPS_OPTIONS_PREFIX.'_menu_gallery'))
				$html .= '<div id="menu_gallery" class="__wps__profile_menu '.$extra_class.'">'.(($t = get_option(WPS_OPTIONS_PREFIX.'_menu_gallery_text')) != '' ? $t :  __('My Gallery', WPS_TEXT_DOMAIN)).'</div>';  
		} else {
			if (get_option(WPS_OPTIONS_PREFIX.'_menu_gallery_other'))
				$html .= '<div id="menu_gallery" class="__wps__profile_menu '.$extra_class.'">'.(($t = get_option(WPS_OPTIONS_PREFIX.'_menu_gallery_other_text')) != '' ? $t :  __('Gallery', WPS_TEXT_DOMAIN)).'</div>';  
		}
	}
	return $html;
}  
add_filter('__wps__profile_menu_filter', '__wps__add_gallery_menu', 10, 7);

function __wps__add_gallery_menu_tabs($html,$title,$value,$uid1,$uid2,$privacy,$is_friend,$extended,$share)  
{  
	if ($value == 'gallery') {

		global $current_user;
	
		if ( ($uid1 == $uid2) || (is_user_logged_in() && strtolower($privacy) == 'everyone') || (strtolower($privacy) == 'public') || (strtolower($privacy) == 'friends only' && $is_friend) || __wps__get_current_userlevel() == 5)	  
			$html .= '<li id="menu_gallery" class="__wps__profile_menu" href="javascript:void(0)">'.$title.'</li>';		
	} 
	
	return $html;

}  
add_filter('__wps__profile_menu_tabs', '__wps__add_gallery_menu_tabs', 10, 9);

// ----------------------------------------------------------------------------------------------------------------------------------------------------------

// Add row to installation page showing status of the Gallery plugin through hook provided
// __wps__install_row(
//	name, 
//	shortcode or '' if not application,
//	main plugin function, 
//	wp_option holding page plugin has been added (eg: profile_url for profile page) or a '-' for none
//	relative path within plugins folder to plugin,
//	url for configuration or '',
//	message about where to install/download
// )
function __wps__installation_hook_gallery()
{
	__wps__install_row(
		__('Gallery', WPS_TEXT_DOMAIN).' v'.get_option(WPS_OPTIONS_PREFIX."_gallery_version"), 
		WPS_SHORTCODE_PREFIX.'-galleries', 
		'__wps__gallery', 
		'/gallery/', 
		'wp-symposium/gallery.php', 
		'admin.php?page='.WPS_DIR.'/gallery_admin.php', 
		__('The Gallery plugin must be installed in ', WPS_TEXT_DOMAIN).WPS_PLUGIN_DIR.chr(10).chr(10).'Download from http://www.wpsymposium.com/downloadinstall.'
	);

}
add_action('__wps__installation_hook', '__wps__installation_hook_gallery');

?>
