<?php


/*  Copyright 2010,2011,2012  Simon Goodchild  (info@wpsymposium.com)

	License: GPL3

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

function symposium_getTopic($topic_id, $group_id=0) {

	global $wpdb, $current_user;	

	$html = '';
	
	$plugin = WP_CONTENT_URL.'/plugins/wp-symposium/';
	
	$previous_login = get_symposium_meta($current_user->ID, 'previous_login');

	$level = symposium_get_current_userlevel();

	// Check permissions
	$user = get_userdata( $current_user->ID );
	$capabilities = $user->{$wpdb->prefix.'capabilities'};
	
	// Can view topic?
	$can_view = false;
	$viewer = str_replace('_', '', str_replace(' ', '', strtolower(get_option('symposium_viewer'))));
	if ($capabilities) {
		foreach ( $capabilities as $role => $name ) {
			if ($role) {
				$role = strtolower($role);
				$role = str_replace(' ', '', $role);
				$role = str_replace('_', '', $role);
				if (WPS_DEBUG) $html .= 'Checking role '.$role.' against '.$viewer.'<br />';
				if (strpos($viewer, $role) !== FALSE) $can_view = true;
			}
		}		 														
	}
	if (strpos($viewer, __('everyone', 'wp-symposium')) !== FALSE) $can_view = true;
	
	// Can create topic?
	$can_edit = false;
	$viewer = str_replace('_', '', str_replace(' ', '', strtolower(get_option('symposium_forum_editor'))));
	if ($capabilities) {
		foreach ( $capabilities as $role => $name ) {
			if ($role) {
				$role = strtolower($role);
				$role = str_replace(' ', '', $role);
				$role = str_replace('_', '', $role);
				if (WPS_DEBUG) $html .= 'Checking role '.$role.' against '.$viewer.'<br />';
				if (strpos($viewer, $role) !== FALSE) $can_edit = true;
			}
		}		 														
	}
	if (strpos($viewer, __('everyone', 'wp-symposium')) !== FALSE) $can_edit = true;
	if ($group_id > 0) {
		$sql = "SELECT COUNT(*) FROM ".$wpdb->prefix."symposium_group_members WHERE group_id=%d AND valid='on' AND member_id=%d";
		$member_count = $wpdb->get_var($wpdb->prepare($sql, $group_id, $current_user->ID));
		if ($member_count == 0) { $can_edit = false; } else { $can_edit = true; }
	}

	// Can reply to a topic?
	$can_reply = false;
	$viewer = str_replace('_', '', str_replace(' ', '', strtolower(get_option('symposium_forum_reply'))));
	if ($capabilities) {
		foreach ( $capabilities as $role => $name ) {
			if ($role) {
				$role = strtolower($role);
				$role = str_replace(' ', '', $role);
				$role = str_replace('_', '', $role);
				if (WPS_DEBUG) $html .= 'Checking role '.$role.' against '.$viewer.'<br />';
				if (strpos($viewer, $role) !== FALSE) $can_reply = true;
			}
		}		 														
	}
	if (strpos($viewer, __('everyone', 'wp-symposium')) !== FALSE) $can_reply = true;
	if ($group_id > 0) {
		$sql = "SELECT COUNT(*) FROM ".$wpdb->prefix."symposium_group_members WHERE group_id=%d AND valid='on' AND member_id=%d";
		$member_count = $wpdb->get_var($wpdb->prepare($sql, $group_id, $current_user->ID));
		if ($member_count == 0) { $can_reply = false; } else { $can_reply = true; }
	}


	// Get list of roles for this user
	global $current_user;
    $user_roles = $current_user->roles;
    $user_role = strtolower(array_shift($user_roles));
    if ($user_role == '') $user_role = 'NONE';

	// Get list of permitted roles from forum_cat and check allowed for this topic's category
	$sql = "SELECT topic_category FROM ".$wpdb->prefix."symposium_topics WHERE tid = %d";
	$category = $wpdb->get_var($wpdb->prepare($sql, $topic_id));
	$sql = "SELECT level FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d";
	$level = $wpdb->get_var($wpdb->prepare($sql, $category));
	$cat_roles = unserialize($level);

	if ($group_id > 0 || strpos(strtolower($cat_roles), 'everyone,') !== FALSE || strpos(strtolower($cat_roles), $user_role.',') !== FALSE) {	 
	} else {
		$can_view = false;
	}

	if ( $can_view ) {

		// Get forum URL worked out
		$forum_url = symposium_get_url('forum');
		if (strpos($forum_url, '?') !== FALSE) {
			$q = "&";
		} else {
			$q = "?";
		}
		// Get group URL worked out
		if ($group_id > 0) {
			$forum_url = symposium_get_url('group');
			if (strpos($forum_url, '?') !== FALSE) {
				$q = "&gid=".$group_id."&";
			} else {
				$q = "?gid=".$group_id."&";
			}
		}
		
		$post = $wpdb->get_row("
			SELECT tid, topic_subject, topic_approved, topic_category, topic_post, topic_started, display_name, topic_sticky, topic_owner, for_info 
			FROM ".$wpdb->prefix."symposium_topics t INNER JOIN ".$wpdb->base_prefix."users u ON t.topic_owner = u.ID 
			WHERE (t.topic_approved = 'on' OR t.topic_owner = ".$current_user->ID.") AND tid = ".$topic_id);
		
		if ($post) {

			// Store removal limit for votes
			$html .= '<div id="symposium_forum_vote_remove" style="display:none">'.get_option('symposium_use_votes_remove').'</div>';
			$html .= '<div id="symposium_forum_vote_remove_msg" style="display:none">'.__('This post has been voted off the forum', 'wp-symposium').'</div>';
		

			// Breadcrumbs
			$cat_id = $post->topic_category;
			
			$html .= '<div id="topic_breadcrumbs" class="breadcrumbs label">';

				if (!get_option('symposium_wps_lite')) {
					$this_level = $wpdb->get_row($wpdb->prepare("SELECT cid, title, cat_parent, stub FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d", $cat_id));
					if ($this_level) { 

						if ($this_level->cat_parent == 0) { 
							if (get_option('symposium_forum_ajax') == 'on') { 
								$html .= '<a href="#cid=0" class="category_title" title="0">'.__('Forum Home', 'wp-symposium')."</a> &rarr; "; 
								$html .= '<a href="#cid='.$this_level->cid.'" class="category_title" title="'.$this_level->cid.'">'.trim(stripslashes($this_level->title)).'</a>'; 
							} else { 
								if (get_option('symposium_permalink_structure') && $group_id == 0) {
									$html .= '<a href="'.$forum_url.'">'.__('Forum Home', 'wp-symposium')."</a> &rarr; "; 
									$html .= '<a href="'.$forum_url.'/'.$this_level->stub.'" title="'.$this_level->cid.'">'.trim(stripslashes($this_level->title)).'</a>'; 
								} else {
									$html .= '<a href="'.$forum_url.$q.'cid=0" title="0">'.__('Forum Home', 'wp-symposium')."</a> &rarr; "; 
									$html .= '<a href="'.$forum_url.$q."cid=".$this_level->cid.'" title="'.$this_level->cid.'">'.trim(stripslashes($this_level->title)).'</a>'; 
								}
							} 
						} else { 

							$parent_level = $wpdb->get_row($wpdb->prepare("SELECT cid, title, cat_parent, stub FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d", $this_level->cat_parent)); 

							if ($parent_level->cat_parent == 0) { 
								if (get_option('symposium_forum_ajax') == 'on') { 
									$html .= '<a href="#cid=0" class="category_title" title="0">'.__('Forum Home', 'wp-symposium')."</a> &rarr; "; 
								} else { 
									if (get_option('symposium_permalink_structure') && $group_id == 0) {
										$html .= '<a href="'.$forum_url.'" title="0">'.__('Forum Home', 'wp-symposium')."</a> &rarr; "; 
									} else {
										$html .= '<a href="'.$forum_url.$q.'cid=0" title="0">'.__('Forum Home', 'wp-symposium')."</a> &rarr; "; 
									}
								} 
							} else { 
								$parent_level_2 = $wpdb->get_row($wpdb->prepare("SELECT cid, title, cat_parent, stub FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d", $parent_level->cat_parent)); 
								if (get_option('symposium_forum_ajax') == 'on') { 
									$html .= '<a href="#cid=0" class="category_title" title="0">'.__('Forum Home', 'wp-symposium')."</a> &rarr; " ; 
									$html .= '<a href="#cid='.$parent_level_2->cid.'" class="category_title" title="'.$parent_level_2->cid.'">'.trim(stripslashes($parent_level_2->title))."</a> &rarr; "; 
								} else { 
									if (get_option('symposium_permalink_structure') && $group_id == 0) {
										$html .= '<a href="'.$forum_url.'" title="0">'.__('Forum Home', 'wp-symposium')."</a> &rarr; " ; 
										$html .= '<a href="'.$forum_url.'/'.$parent_level_2->stub.'" title="'.$parent_level_2->cid.'">'.trim(stripslashes($parent_level_2->title))."</a> &rarr; "; 
									} else {
										$html .= '<a href="'.$forum_url.$q.'cid=0" title="0">'.__('Forum Home', 'wp-symposium')."</a> &rarr; " ; 
										$html .= '<a href="'.$forum_url.$q."cid=".$parent_level_2->cid.'" title="'.$parent_level_2->cid.'">'.trim(stripslashes($parent_level_2->title))."</a> &rarr; "; 
									}
								} 
							} 
							if (get_option('symposium_forum_ajax') == 'on') { 
								$html .= '<a href="#cid='.$parent_level->cid.'" class="category_title" title="'.$parent_level->cid.'">'.trim(stripslashes($parent_level->title))."</a> &rarr; " ; 
								$html .= '<a href="#cid='.$this_level->cid.'" class="category_title" title="'.$this_level->cid.'">'.trim(stripslashes($this_level->title))."</a>" ; 
							} else { 
								if (get_option('symposium_permalink_structure') && $group_id == 0) {
									$html .= '<a href="'.$forum_url.'/'.$parent_level->stub.'" title="'.$parent_level->cid.'">'.trim(stripslashes($parent_level->title))."</a> &rarr; " ; 
									$html .= '<a href="'.$forum_url.'/'.$this_level->stub.'" title="'.$this_level->cid.'">'.trim(stripslashes($this_level->title))."</a>" ; 
								} else {
									$html .= '<a href="'.$forum_url.$q."cid=".$parent_level->cid.'" title="'.$parent_level->cid.'">'.trim(stripslashes($parent_level->title))."</a> &rarr; " ; 
									$html .= '<a href="'.$forum_url.$q."cid=".$this_level->cid.'" title="'.$this_level->cid.'">'.trim(stripslashes($this_level->title))."</a>" ; 
								}
							} 
						} 
					} else {
						if (get_option('symposium_forum_ajax') == 'on') {
							$html .= '&larr; <a href="#cid=0" class="category_title" title="0">'.__('Forum Home', 'wp-symposium')."</a>";
						} else {
							if (get_option('symposium_permalink_structure') && $group_id == 0) {
								$html .= '&larr; <a href="'.$forum_url.'" title="0">'.__('Forum Home', 'wp-symposium')."</a>";
							} else {
								$html .= '&larr; <a href="'.$forum_url.$q.'cid=0" title="0">'.__('Forum Home', 'wp-symposium')."</a>";
							}
						}
					}

				} else {
					// Lite mode
					$html .= '<a href="#cid=0" class="category_title" title="0">'.__('Forum Home', 'wp-symposium')."</a> &rarr; ";
					$html .= '<a href="#cid='.$post->topic_category.'" class="category_title" title="'.$post->topic_category.'">'.__('Topic list', 'wp-symposium').'</a>';
				}
								
			$html .= '</div>';
		
			// Subscribe, Sticky and Allow Replies
			if (is_user_logged_in()) {
				$html .= "<div class='floatleft label'>";
					$forum_all = get_symposium_meta($current_user->ID, 'forum_all');
					$html .= "<input type='checkbox' title='".$post->tid."' id='subscribe' name='subscribe'";
					if ($forum_all == 'on') {
						$html .= " style='display:none;'";
					}
					$subscribed_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix."symposium_subs WHERE tid = %d and uid = %d", $post->tid, $current_user->ID));
					if ($subscribed_count > 0) { $html .= ' checked'; } 
					$html .= "> ";
					if ($forum_all != 'on') {
						$html .= __("Tell me about replies", "wp-symposium")."&nbsp;&nbsp;&nbsp;";
					}
					if (current_user_can('level_10')) {
						$html .= "<input type='checkbox' title='".$post->tid."' id='sticky' name='sticky'";
						if ($post->topic_sticky > 0) { $html .= ' checked'; }
						$html .= "> ".__("Sticky", "wp-symposium");
						$html .= "&nbsp;&nbsp;&nbsp;<input type='checkbox' title='".$post->tid."' id='replies' name='replies'";
						$allow_replies = $wpdb->get_var($wpdb->prepare("SELECT allow_replies FROM ".$wpdb->prefix."symposium_topics WHERE tid = %d", $post->tid));
						if ($allow_replies == "on") { $html .= ' checked'; }
						$html .= "> ".__("Replies allowed", "wp-symposium");
					}
				$html .= "</div>";
			}

			// Forum options
			$html .= "<div id='forum_options'>";

				$html .= "<a id='show_search' href='javascript:void(0)'>".__("Search", "wp-symposium")."</a>";
				$html .= "&nbsp;&nbsp;&nbsp;&nbsp;<a id='show_all_activity' href='javascript:void(0)'>".__("Activity", "wp-symposium")."</a>";
				$html .= "&nbsp;&nbsp;&nbsp;&nbsp;<a id='show_threads_activity' href='javascript:void(0)'>".__("Latest Topics", "wp-symposium")."</a>";

				if (is_user_logged_in()) {
					$html .= "&nbsp;&nbsp;&nbsp;&nbsp;<a id='show_activity' href='javascript:void(0)'>".__("My Activity", "wp-symposium")."</a>";
					$html .= "&nbsp;&nbsp;&nbsp;&nbsp;<a id='show_favs' href='javascript:void(0)'>".__("Favorites", "wp-symposium")."</a>";
				}

			$html .= "</div>";
			
			// Sharing icons
			if (get_option('symposium_sharing') != '') {
				$html .= show_sharing_icons($cat_id, $post->tid, get_option('symposium_sharing'), $group_id);
			}
		
			// Edit Form
			$html .= '<div id="edit-topic-div">';

				$html .= '<div class="new-topic-subject label">'.__("Topic Subject", "wp-symposium").'</div>';
				$html .= '<div id="'.$post->tid.'" class="edit-topic-tid"></div>';
				$html .= '<div id="" class="edit-topic-parent"></div>';
				$html .= '<input class="new-topic-subject-input" type="text" name="edit_topic_subject">';
				$html .= '<div class="new-topic-subject label">'.__("Topic Text", "wp-symposium").'</div>';
				$html .= '<textarea class="new-topic-subject-text" id="edit_topic_text" name="edit_topic_text"></textarea>';
				if ($group_id == 0) {
					$html .= '<div class="new-category-div" style="float:left;">'.__("Move Category", "wp-symposium").': <select name="new-category" class="new-category" style="width: 200px">';
					$html .= '<option value="">'.__("Select", "wp-symposium").'...</option>';
					$categories = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix.'symposium_cats ORDER BY listorder');			
					if ($categories) {
						foreach ($categories as $category) {
							if ($category->allow_new == "on" || current_user_can('level_10')) {
								$html .= '<option value='.$category->cid.'>'.stripslashes($category->title).'</option>';
							}
						}
					}
					$html .= '</select></div>';
				} else {
					// No categories for groups
					$html .= '<input name="new-category" type="hidden" value="0">';
				}

			$html .= '</div>';

			// Topic starting post
			$html .= "<div id='starting-post'>";
		
				// Show topic header
				$html .= "<div id='top_of_first_post'>";
			
					$html .= "<div class='avatar' style='margin-bottom:0px; margin-top:6px;'>";
						$html .= get_avatar($post->topic_owner, 64);

						if (get_option('symposium_forum_info')) {
						
							$html .= "<div class='forum_info'>";
							
								$sql = "SELECT count(*) FROM ".$wpdb->prefix."symposium_topics WHERE topic_owner = %d";
								$count = $wpdb->get_var($wpdb->prepare($sql, $post->topic_owner));
								$html .= __('Posts:', 'wp-symposium').' ';
								$html .= '<span class="forum_info_numbers">'.$count.'</span>';

							$html .= "</div>";	
						
							if (get_option('symposium_use_answers') == 'on') {
								$html .= "<div class='forum_info'>";
									// Get widget settings (also used under Replies)
									$settings = get_option("widget_forumexperts-widget");
									if (WPS_DEBUG) $html .= symposium_displayArrayContentFunction($settings);
									if (isset($settings[2]['timescale'])) {
										if (WPS_DEBUG) $html .= 'Getting Widget settings<br />';
										$timescale = $settings[2]['timescale'];
										$w_cat_id = $settings[2]['cat_id'];
										$cat_id_exclude = $settings[2]['cat_id_exclude'];
										$groups = $settings[2]['groups'];
									} else {
										if (WPS_DEBUG) $html .= 'Using default settings<br />';
										$timescale = 7;
										$w_cat_id = '';
										$cat_id_exclude = '';
										$groups = '';
									}
									// Now get value of rating (how many answers during timescale)
									$sql = "SELECT COUNT(*) FROM ".$wpdb->prefix."symposium_topics WHERE topic_owner = %d AND topic_answer = 'on' ";
									$sql .= "AND topic_date >= ( CURDATE() - INTERVAL ".$timescale." DAY )";
									if ($w_cat_id != '' && $w_cat_id > 0) {
										$sql .= "AND topic_category IN (".$w_cat_id.") ";
									}
									if ($cat_id_exclude != '' && $cat_id_exclude > 0) {
										$sql .= "AND topic_category NOT IN (".$cat_id_exclude.") ";
									}
									if ($groups != 'on') {
										$sql .= "AND topic_group = 0 ";
									}								
									$count = $wpdb->get_var($wpdb->prepare($sql, $post->topic_owner));
									if ($count > 0) {
										$html .= __('Rating:', 'wp-symposium').' ';
										$html .= '<span class="forum_info_numbers">'.$count.'</span>';
									}
									if (WPS_DEBUG) $html .= $wpdb->last_query;
								$html .= "</div>";
							}

							if ($post->topic_started > $previous_login && $post->topic_owner != $current_user->ID && is_user_logged_in() && get_option('symposium_forum_stars')) {
								$html .= "<img src='".get_option('symposium_images')."/new.gif' alt='New!' /> ";
							}
						}
						
					$html .= "</div>";
			
					$html .= "<div class='topic-post-header-with-fav'>";
			
						$html .= "<div class='topic-post-header'>";

							if (get_option('symposium_allow_reports') == 'on') {
								$html .= "<a href='javascript:void(0)' title='forum_".$post->tid."' class='report label symposium_report' style='display:none; cursor:pointer'><div class='topic-edit-icon'><img src='".get_option('symposium_images')."/warning.png' /></a></div>";
							}
							if ( ($post->topic_owner == $current_user->ID) || (current_user_can('level_10')) ) {
								$now = date('Y-m-d H:i:s', time() - get_option('symposium_forum_lock') * 60);
								if ( (get_option('symposium_forum_lock')==0) || ($post->topic_started > $now) || (current_user_can('level_10')) ) {
									$html .= "<a href='javascript:void(0)' title='".$post->tid."' id='edit-this-topic' class='edit_topic edit label' style='cursor:pointer'><div class='topic-edit-icon'><img src='".get_option('symposium_images')."/edit.png' /></a></div>";
								}
							}

							$post_text = stripslashes(symposium_bbcode_replace(stripslashes($post->topic_subject)));
							$html .= $post_text;
							$topic_subject = $post_text;
			
							if ($post->topic_approved != 'on') { $html .= " <em>[".__("pending approval", "wp-symposium")."]</em>"; }

							// Favourites
							if (is_user_logged_in()) {
								if (strpos(get_symposium_meta($current_user->ID, 'forum_favs'), "[".$post->tid."]") === FALSE) { 
									$html .= "<img title='".$post->tid."' id='fav_link' src='".get_option('symposium_images')."/fav-off.png' style='height:22px; width:22px; cursor:pointer;' alt='".__("Click to add to favorites", "wp-symposium")."' />";						
								} else {
									$html .= "<img title='".$post->tid."' id='fav_link' src='".get_option('symposium_images')."/fav-on.png' style='height:22px; width:22px; cursor:pointer;' alt='".__("Click to remove to favorites", "wp-symposium")."' />";						
								}
							}


						$html .= "</div><div style='clear:both'></div>";
										
						$html .= "<div class='started-by' style='margin-top:10px'>";
						$html .= __("Started by", "wp-symposium");
						if ( substr(get_option('symposium_forum_ranks'), 0, 2) == 'on' ) {
							$html .= " <span class='forum_rank'>".forum_rank($post->topic_owner)."</span>";
						}
						$html .= " ".symposium_profile_link($post->topic_owner);
						$html .= " ".symposium_time_ago($post->topic_started);
						$html .= "</div>";

						$post_text = symposium_make_url(stripslashes($post->topic_post));
						$has_code = strpos($post_text, '[code]') ? true : false;
						$post_text = symposium_bbcode_replace($post_text);
						if (!$has_code) $post_text = symposium_buffer($post_text);
						if (!get_option('symposium_use_wysiwyg')) {
							$post_text = str_replace(chr(13), "<br />", $post_text);
						}
						$html .= "<div class='topic-post-post'>".$post_text."</div><br />";
						
						// Allow owner or site admin to mark topic for information only
						if (get_option('symposium_use_answers') == 'on') {
							if ($post->topic_owner == $current_user->ID || symposium_get_current_userlevel($current_user->ID) == 5) {
								$html .= '<input type="checkbox" id="symposium_for_info" title="'.$post->tid.'"';
								if ($post->for_info == 'on') { $html .= " CHECKED"; }
								$html .= ' /> ';
								$html .= '<em>'.__('This topic is for information only, no answer will be selected.', 'wp-symposium').'</em>';
							} else {
								if ($post->for_info == 'on') { 
									$html .= '<em>'.__('This topic is for information only, no answer will be selected.', 'wp-symposium').'</em>';
								}
							}
						}

						// show any uploaded files
						if (get_option('symposium_img_db') == 'on') {

							$cnt_cont = 1;

							// get list of uploaded files from database
							$sql = "SELECT tmpid, filename FROM ".$wpdb->prefix."symposium_topics_images WHERE tid = ".$post->tid." ORDER BY tmpid";
							$images = $wpdb->get_results($sql);
							foreach ($images as $file) {
								$html .= '<div>';
								$ext = explode('.', $file->filename);
								if ($ext[sizeof($ext)-1]=='gif' || $ext[sizeof($ext)-1]=='jpg' || $ext[sizeof($ext)-1]=='png' || $ext[sizeof($ext)-1]=='jpeg') {
									// Image
									$url = WP_CONTENT_URL."/plugins/wp-symposium/get_attachment.php?tid=".$post->tid."&filename=".$file->filename;
									$html .= "<a target='_blank' href='".$url."' rev='".$cnt_cont."' data-iid='0' class='wps_gallery_album' rel='symposium_gallery_photos_".$post->tid."'  title='".$file->filename."'>";
									$cnt_cont++;
									if (get_option('symposium_forum_thumbs') == 'on') {
										list($width, $height, $type, $attr) = getimagesize($url);
										//list($width, $height, $type, $attr) = getimagesize(parse_url(get_bloginfo('url'),PHP_URL_SCHEME)."://".parse_url(get_bloginfo('url'),PHP_URL_HOST).$url);
										$max_width = get_option('symposium_forum_thumbs_size');
										if ($width > $max_width) {
											$height = $height / ($width / $max_width);
											$width = $max_width;
										}
										$html .= '<img src="'.$url.'" style="width:'.$width.'px; height:'.$height.'px" />';
									} else {
										$html .= $file->filename;
									}
									$html .= "</a> ";
								} else {
									// Video
									if ($ext[sizeof($ext)-1]=='mp4') {
										$html .= "<a href='#' rel='jwplayer'>".$file->filename."</a>";
									} else {
										// Document
										if ($blog_id > 1) {
											$url = get_option('symposium_img_url').'/'.$blog_id.'/forum/'.$post->tid.'/'.$file->filename;
										} else {
											$url = get_option('symposium_img_url').'/forum/'.$post->tid.'/'.$file->filename;
										}
										$url = WP_CONTENT_URL."/plugins/wp-symposium/get_attachment.php?tid=".$post->tid."&filename=".$file->filename;
										
										$html .= "<a href='".$url."' title='".$file->filename."'>".$url."</a><br>";	
										$html .= "<a href='".$url."' title='".$file->filename."' rel='mediaplayer'>".$file->filename."</a>";															
									}
								}
								if ($post->topic_owner == $current_user->ID || symposium_get_current_userlevel($current_user->ID) == 5) {
									$html .= '<img id="'.$post->tid.'" title="'.$file->filename.'" class="remove_forum_post link_cursor" src="'.get_option('symposium_images').'/delete.png" /> ';
								}
								$html .= '</div>';	
							}


						} else {

							// Filesystem

							if ($blog_id > 1) {
								$targetPath = get_option('symposium_img_path')."/".$blog_id."/forum/".$post->tid;
							} else {
								$targetPath = get_option('symposium_img_path')."/forum/".$post->tid;
							}
							if (file_exists($targetPath)) {
								$handler = opendir($targetPath);
								$cnt = 0;
								$cnt_cont = 1;
								while ($file = readdir($handler)) {
									$cnt++;
									if ( ($file != "." && $file != ".." && $file != ".DS_Store") && (!is_dir($targetPath.'/'.$file)) ) {
										$html .= '<div>';
										if ($blog_id > 1) {
											$url = get_option('symposium_img_url').'/'.$blog_id.'/forum/'.$post->tid.'/'.$file;
										} else {
											$url = get_option('symposium_img_url').'/forum/'.$post->tid.'/'.$file;
										}
										$ext = explode('.', $file);
										if (strpos(get_option('symposium_image_ext'), $ext[sizeof($ext)-1]) > 0) {
											// Image
											$html .= "<a target='_blank' href='".$url."' data-iid='0' rel='symposium_gallery_photos_".$post->tid."' title='".$file."'>";
											if (get_option('symposium_forum_thumbs') == 'on') {
												//list($width, $height, $type, $attr) = getimagesize($url);
												list($width, $height, $type, $attr) = getimagesize(parse_url(get_bloginfo('url'),PHP_URL_SCHEME)."://".parse_url(get_bloginfo('url'),PHP_URL_HOST).$url);
												$max_width = get_option('symposium_forum_thumbs_size');
												if ($width > $max_width) {
													$height = $height / ($width / $max_width);
													$width = $max_width;
												}
												$html .= '<img src="'.$url.'" rev="'.$cnt_cont.'" rel="symposium_gallery_photos_'.$post->tid.'" class="wps_gallery_album" title="'.get_bloginfo('name').'" style="width:'.$width.'px; height:'.$height.'px" />';
												$cnt_cont++;

											} else {
												$html .= $file;
											}
											$html .= '</a> ';
										} else {
											// Video
											if (strpos(get_option('symposium_video_ext'), $ext[sizeof($ext)-1]) > 0) {

												if (get_option('symposium_forum_thumbs') == 'on') {
													$html .= '<div id="mediaplayer_'.$cnt.'">JW Player goes here</div> ';
												} else {
													$html .= '<div style="display:none">';
													$html .= '<div id="mediaplayer_'.$cnt.'">JW Player goes here</div> ';
													$html .= '</div>';
													$html .= "<a href='".$url."' class='jwplayer' title='".$file."' rel='mediaplayer'>".$file."</a> ";															
												}

												$html .= '<script type="text/javascript"> ';
												$html .= '	jwplayer("mediaplayer_'.$cnt.'").setup({';
												$html .= '		flashplayer: "'.WP_PLUGIN_URL.'/wp-symposium/jwplayer/player.swf",';
												$html .= '		image: "'.WP_PLUGIN_URL.'/wp-symposium/jwplayer/preview.gif",';
												$html .= '		file: "'.$url.'",';
												$html .= '		width: "'.get_option('symposium_forum_thumbs_size').'px",';
												$html .= '		height: "250px"';
												$html .= '	});';
												$html .= '</script>';	

											} else {
												// Document
												$html .= "<a href='".$url."' title='".$file."' rel='mediaplayer'>".$file."</a>";															
											}
										}
										if ($post->topic_owner == $current_user->ID || symposium_get_current_userlevel($current_user->ID) == 5) {
											$html .= '<img id="'.$post->tid.'" title="'.$file.'" class="remove_forum_post link_cursor" src="'.get_option('symposium_images').'/delete.png" /> ';
										}
										$html .= '</div>';
									}
								}			
								closedir($handler);
							}
						}
						
						// Add Signature
						$signature = get_symposium_meta($post->topic_owner, 'signature');
						if ($signature != '') {
							$html .= '<div class="sep_top"><em>'.symposium_make_url(stripslashes($signature)).'</em></div>';
						}

										
					$html .= "</div><div style='clear:both'></div>";				
												
				$html .= "</div>";

				// Update views
				if (symposium_get_current_userlevel() == 5) {
					if (get_option('symposium_include_admin') == "on") { 
						$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix."symposium_topics SET topic_views = topic_views + 1 WHERE tid = %d", $post->tid) );
					}
				} else {
					$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix."symposium_topics SET topic_views = topic_views + 1 WHERE tid = %d", $post->tid) );
				}
					
			$html .= "</div>";		

			// Replies
			$sql = "SELECT t.tid, (SELECT SUM(score) FROM ".$wpdb->prefix."symposium_topics_scores s WHERE s.tid = t.tid) AS score, topic_subject, topic_approved, topic_post, t.topic_started, t.topic_date, topic_owner, display_name, topic_answer, ID
				FROM ".$wpdb->prefix."symposium_topics t INNER JOIN ".$wpdb->base_prefix."users u ON t.topic_owner = u.ID 
				WHERE (t.topic_approved = 'on' OR t.topic_owner = %d) AND t.topic_parent = %d ORDER BY t.tid";
				
		
			if (get_option('symposium_oldest_first') != "on") { $sql .= " DESC"; }
	
			$child_query = $wpdb->get_results($wpdb->prepare($sql, $current_user->ID, $post->tid));
			$html .= "<div id='child-posts'>";

				if ($child_query) {
										
					// Get current number of votes by this member to see if can vote
					$sql = "SELECT count(*) FROM ".$wpdb->prefix."symposium_topics WHERE topic_owner = %d";
					$post_count = $wpdb->get_var($wpdb->prepare($sql, $current_user->ID));

					// Div to show if can't vote yet
					$html .= '<div id="symposium_novote_dialog" style="display:none">';
					$html .= sprintf(__("Spam Protection", "wp-symposium"), get_option('symposium_use_votes_min'));
					$html .= '</div>';
					$html .= '<div id="symposium_novote" style="display:none">';
					$html .= sprintf(__("Sorry, you can't vote until you have made %d posts.", "wp-symposium"), get_option('symposium_use_votes_min'));
					$html .= '</div>';
					
					foreach ($child_query as $child) {
						
						$score = $child->score;
						if ($score == NULL) { $score = 0; }
						
						$reply_html = '';

						$reply_html .= "<div id='reply".$child->tid."' class='child-reply";
							$trusted = get_symposium_meta($child->topic_owner, 'trusted');
							if ($trusted == 'on') { $reply_html .= " trusted"; }
							$reply_html .= "'>";

							$reply_html .= "<div class='avatar'>";
								$reply_html .= get_avatar($child->ID, 64);
								
								if (get_option('symposium_forum_info')) {
								
									$reply_html .= "<div class='forum_info'>";
									
										$sql = "SELECT count(*) FROM ".$wpdb->prefix."symposium_topics WHERE topic_owner = %d";
										$count = $wpdb->get_var($wpdb->prepare($sql, $child->topic_owner));
										$reply_html .= __('Posts:', 'wp-symposium').' ';								
										$reply_html .= '<span class="forum_info_numbers">'.$count.'</span>';
										
									$reply_html .= "</div>";	

									if (get_option('symposium_use_answers') == 'on') {
										$reply_html .= "<div class='forum_info'>";
											$sql = "SELECT COUNT(*) FROM ".$wpdb->prefix."symposium_topics WHERE topic_owner = %d AND topic_answer = 'on' ";
											$sql .= "AND topic_date >= ( CURDATE() - INTERVAL ".$timescale." DAY )";
											if ($w_cat_id != '' && $w_cat_id > 0) {
												$sql .= "AND topic_category IN (".$w_cat_id.") ";
											}
											if ($cat_id_exclude != '' && $cat_id_exclude > 0) {
												$sql .= "AND topic_category NOT IN (".$cat_id_exclude.") ";
											}
											if ($groups != 'on') {
												$sql .= "AND topic_group = 0 ";
											}								
											$count = $wpdb->get_var($wpdb->prepare($sql, $child->topic_owner));
											if ($count > 0) {
												$reply_html .= __('Rating:', 'wp-symposium').' ';
												$reply_html .= '<span class="forum_info_numbers">'.$count.'</span>';
											}
											if (WPS_DEBUG) $reply_html .= $wpdb->last_query;
										$reply_html .= "</div>";	
									}	
									
								}

								if ($child->topic_date > $previous_login && $child->topic_owner != $current_user->ID && is_user_logged_in() && get_option('symposium_forum_stars')) {
									$reply_html .= "<img src='".get_option('symposium_images')."/new.gif' alt='New!' /> ";
								}

								
							$reply_html .= "</div>";	
													
							$reply_html .= "<div style='padding-left: 85px;'>";
							
								if ( (get_option('symposium_use_votes_remove') == $score) && (symposium_get_current_userlevel() < 5) && ($score != 0) ) {
									$reply_html .= '<p>'.__('This post has been voted off the forum', 'wp-symposium').'</p>';
								} else {
								
									if ( (get_option('symposium_use_votes_remove') == $score) && ($score != 0) ) {
										$reply_html .= '<p>'.__('This post has been voted off the forum (only visible to site admins) with a score of', 'wp-symposium').' '.$score.'.</p>';
									}
									// Votes (if being used)
									if (get_option('symposium_use_votes') == 'on' && ($child->topic_owner != $current_user->ID || symposium_get_current_userlevel() == 5)) {
										$reply_html .= "<div class='floatright forum_post_score' style='width: 24px; text-align:center;'>";
											$reply_html .= "<div style='line-height:16px;'>";
												if ($post_count >= get_option('symposium_use_votes_min')) {
													$reply_html .= "<img id='".$child->tid."' class='forum_post_score_change' title='plus' src='".get_option('symposium_images')."/smilies/good.png' style='cursor:pointer;width:24px; height:24px;' />";
												} else {
													$reply_html .= "<img id='".$child->tid."' class='forum_post_score_change' title='novote' src='".get_option('symposium_images')."/smilies/good.png' style='cursor:pointer;width:24px; height:24px;' />";
												}
											$reply_html .= "</div>";
											$reply_html .= "<div id='forum_score_".$child->tid."' style='margin-bottom:3px'>";
												if ($child->score > 0) { $reply_html .= '+'; }
												$reply_html .= $score;
											$reply_html .= "</div>";
											$reply_html .= "<div>";
												if ($post_count >= get_option('symposium_use_votes_min')) {
													$reply_html .= "<img id='".$child->tid."' class='forum_post_score_change' title='minus' src='".get_option('symposium_images')."/smilies/bad.png' style='cursor:pointer;width:24px; height:24px;' />";
												} else {
													$reply_html .= "<img id='".$child->tid."' class='forum_post_score_change' title='novote' src='".get_option('symposium_images')."/smilies/bad.png' style='cursor:pointer;width:24px; height:24px;' />";
												}
											$reply_html .= "</div>";
										$reply_html .= "</div>";
									}
									// Answer feature (if being used)
									if (get_option('symposium_use_answers') == 'on') {
										$reply_html .= "<div class='floatright'>";
											if ($child->topic_answer == 'on') {
												$reply_html .= "<img id='symposium_accepted_answer' src='".get_option('symposium_images')."/tick.png' style='cursor:pointer;margin-top:3px;width:20px; height:20px;' />";
											} else {
												if ($post->topic_owner == $current_user->ID || symposium_get_current_userlevel() == 5) {
													$reply_html .= "<a id=".$child->tid." class='forum_post_answer' href='javascript:void(0);' style='margin-right:10px;";
													if ($post->for_info == 'on') {
														$reply_html .= "display:none;";
													}
													$reply_html .= "'>".__('Accept answer', 'wp-symposium')."</a>";
												}
											}
										$reply_html .= "</div>";
									}
									
									$reply_html .= "<div class='topic-edit-delete-icon'>";
										// Report warning (if being used)
										if (get_option('symposium_allow_reports') == 'on') {
											$reply_html .= "<a href='javascript:void(0)' class='floatright link_cursor symposium_report' style='display:none' title='reply_".$child->tid."'><img src='".get_option('symposium_images')."/warning.png' /></a>";
										}
										if ( ($child->topic_owner == $current_user->ID) || (current_user_can('level_10')) ) {
											$now = date('Y-m-d H:i:s', time() - get_option('symposium_forum_lock') * 60);
											if ( (get_option('symposium_forum_lock')==0) || ($child->topic_started > $now) || (current_user_can('level_10')) ) {
												$reply_html .= "<a href='javascript:void(0)' class='floatright link_cursor delete_forum_reply' style='display:none' id='".$child->tid."'><img src='".get_option('symposium_images')."/delete.png' /></a>";
												$reply_html .= "<a href='javascript:void(0)' class='floatright link_cursor edit_forum_reply' style='display:none; margin-right: 5px' id='".$child->tid."'><img src='".get_option('symposium_images')."/edit.png' /></a>";
											}
										}
									$reply_html .= "</div>";
									$reply_html .= "<div class='started-by'>";
									if ( substr(get_option('symposium_forum_ranks'), 0, 2) == 'on' ) {
										$reply_html .= "<span class='forum_rank'>".forum_rank($child->topic_owner)."</span> ";
									}
									$reply_html .= symposium_profile_link($child->topic_owner);
									$reply_html .= " ".__("replied", "wp-symposium")." ".symposium_time_ago($child->topic_date)."...";
									$reply_html .= "</div>";
									$reply_html .= "<div id='child_".$child->tid."' class='child-reply-post'>";
// xxx
										$reply_text = symposium_make_url(stripslashes($child->topic_post));
										$has_code = strpos($reply_text, '[code]') ? true : false;

										$reply_text = symposium_bbcode_replace($reply_text);
										if (!$has_code) $reply_text = symposium_buffer($reply_text);
										if (!get_option('symposium_use_wysiwyg')) {
											$reply_text = str_replace(chr(10), "<br />", $reply_text);
											$reply_text = str_replace(chr(13), "<br />", $reply_text);
										}
										$reply_html .= "<p>".$reply_text;
										if ($child->topic_approved != 'on') { $reply_html .= " <em>[".__("pending approval", "wp-symposium")."]</em>"; }
										$reply_html .= "</p>";
	
									$reply_html .= "</div>";
																	
								}
	
								// show any uploaded files
								if (get_option('symposium_img_db') == 'on') {
	
									// get list of uploaded files from database
									$sql = "SELECT tmpid, filename FROM ".$wpdb->prefix."symposium_topics_images WHERE tid = ".$child->tid." ORDER BY tmpid";
									$images = $wpdb->get_results($sql);
									foreach ($images as $file) {

										$reply_html .= '<div>';
										$url = WP_CONTENT_URL."/plugins/wp-symposium/get_attachment.php?tid=".$child->tid."&filename=".$file->filename;

										$reply_html .= "<a target='_blank' href='".$url."' rev='".$cnt_cont."' data-iid='0' ";
										$ext = explode('.', $file->filename);
										if ($ext[sizeof($ext)-1]=='gif' || $ext[sizeof($ext)-1]=='jpg' || $ext[sizeof($ext)-1]=='png' || $ext[sizeof($ext)-1]=='jpeg') {
											$reply_html .= " class='wps_gallery_album' rel='symposium_gallery_photos_".$post->tid."'";
										}
										$reply_html .= ' title="'.$file->filename.'">';
										$cnt_cont++;

										if (get_option('symposium_forum_thumbs') == 'on') {
											list($width, $height, $type, $attr) = getimagesize($url);
											//list($width, $height, $type, $attr) = getimagesize(parse_url(get_bloginfo('url'),PHP_URL_SCHEME)."://".parse_url(get_bloginfo('url'),PHP_URL_HOST).$url);
											$max_width = get_option('symposium_forum_thumbs_size');
											if ($width > $max_width) {
												$height = $height / ($width / $max_width);
												$width = $max_width;
											}
											$reply_html .= '<img src="'.$url.'" style="width:'.$width.'px; height:'.$height.'px" />';
										} else {
											$reply_html .= $file->filename;
										}
										$reply_html .= '</a> ';
										$reply_html .= '<img id="'.$child->tid.'" title="'.$file->filename.'" class="remove_forum_post link_cursor" src="'.get_option('symposium_images').'/delete.png" /> ';
										$reply_html .= '</div>';	
									}
	
								} else {
									
									if ($blog_id > 1) {
										$targetPath = get_option('symposium_img_path')."/".$blog_id."/forum/".$post->tid.'/'.$child->tid;
									} else {
										$targetPath = get_option('symposium_img_path')."/forum/".$post->tid.'/'.$child->tid;
									}

									if (file_exists($targetPath)) {
										$cnt = 0;
										$handler = opendir($targetPath);
										while ($file = readdir($handler)) {
											
											if ($file != "." && $file != ".." && $file != ".DS_Store") {
												
												$cnt++;

												$reply_html .= '<div style="overflow:auto;">';
												if ($blog_id > 1) {
													$url = get_option('symposium_img_url').'/'.$blog_id.'/forum/'.$post->tid.'/'.$child->tid.'/'.$file;
												} else {
													$url = get_option('symposium_img_url').'/forum/'.$post->tid.'/'.$child->tid.'/'.$file;
												}
												$ext = explode('.', $file);
												if (strpos(get_option('symposium_image_ext'), $ext[sizeof($ext)-1]) > 0) {
													// Image
													$reply_html .= "<a target='_blank' href='".$url."' data-iid='0' rel='symposium_gallery_photos_".$post->tid."'";
													$reply_html .= ' class="wps_gallery_album" title="'.$file.'">';
													if (get_option('symposium_forum_thumbs') == 'on') {
														//list($width, $height, $type, $attr) = getimagesize(get_bloginfo('url').$url);
														list($width, $height, $type, $attr) = getimagesize(parse_url(get_bloginfo('url'),PHP_URL_SCHEME)."://".parse_url(get_bloginfo('url'),PHP_URL_HOST).$url);
														$max_width = get_option('symposium_forum_thumbs_size');
														if ($width > $max_width) {
															$height = $height / ($width / $max_width);
															$width = $max_width;
														}
														$reply_html .= '<img src="'.$url.'" rev="'.$cnt_cont.'" rel="symposium_gallery_photos_'.$post->tid.'" class="wps_gallery_album" title="'.get_bloginfo('name').'" style="width:'.$width.'px; height:'.$height.'px" />';
														$cnt_cont++;
													} else {
														$reply_html .= $file;
													}
													$reply_html .= '</a> ';
												} else {
													// Video
													if (strpos(get_option('symposium_video_ext'), $ext[sizeof($ext)-1]) > 0) {
														
														$video_id = $child->tid.'_'.$cnt;
														
														if (get_option('symposium_forum_thumbs') == 'on') {
															$reply_html .= '<div id="mediaplayer'.$video_id.'">JW Player goes here</div> ';
														} else {
															$reply_html .= '<div style="display:none">';
															$reply_html .= '<div id="mediaplayer'.$video_id.'">JW Player goes here</div> ';
															$reply_html .= '</div>';
															$reply_html .= "<a href='#' class='jwplayer' title='".$file."' rel='mediaplayer".$video_id."'>".$file."</a> ";															
														}
														$reply_html .= '<script type="text/javascript"> ';
														$reply_html .= '	jwplayer("mediaplayer'.$video_id.'").setup({';
														$reply_html .= '		flashplayer: "'.WP_PLUGIN_URL.'/wp-symposium/jwplayer/player.swf",';
														$reply_html .= '		image: "'.WP_PLUGIN_URL.'/wp-symposium/jwplayer/preview.gif",';
														$reply_html .= '		file: "'.$url.'",';
														$reply_html .= '		width: "'.get_option('symposium_forum_thumbs_size').'px",';
														$reply_html .= '		height: "250px"';
														$reply_html .= '	});';
														$reply_html .= '</script>';																																												
													} else {
														// Document
														$reply_html .= "<a target='_blank' href='".$url."'>".$file."</a> ";		
													}
												}
												if ($child->topic_owner == $current_user->ID || symposium_get_current_userlevel($current_user->ID) == 5) {
													$reply_html .= '<img id="'.$post->tid.'/'.$child->tid.'" title="'.$file.'" class="remove_forum_post link_cursor" src="'.get_option('symposium_images').'/delete.png" /> ';
												}
												$reply_html .= '</div>';
												
											}
										}			
										closedir($handler);
									}
								}

							// Add Signature
							$signature = get_symposium_meta($child->topic_owner, 'signature');
							if ($signature != '') {
								$reply_html .= '<div class="sep_top"><em>'.symposium_make_url(stripslashes($signature)).'</em></div>';
							}
							
							$reply_html .= "</div>";
							
						$reply_html .= "</div>";
						
						$reply_html = apply_filters( 'wps_forum_replies_filter', $reply_html );
						
						$html .= $reply_html;

					}
			
			} else {
		
				$html .= "<div class='child-reply'>";
				$html .= __("No replies posted yet.", "wp-symposium");
				$html .= "</div>";
				$html .= "<div class='sep'></div>";						
		
			}			

			$html .= "</div>";

			// Quick Reply
			if ($can_reply) {
				
				$html .= '<div id="reply-topic-bottom" name="reply-topic-bottom">';
				if ($wpdb->get_var($wpdb->prepare("SELECT allow_replies FROM ".$wpdb->prefix."symposium_topics WHERE tid = %d", $post->tid)) == "on")
				{
					$html .= '<input type="hidden" id="symposium_reply_tid" value="'.$post->tid.'">';
					$html .= '<input type="hidden" id="symposium_reply_cid" value="'.$cat_id.'">';
										
					$html .= '<div class="reply-topic-subject label">'.__("Reply to this Topic", "wp-symposium").'</div>';

					if (get_option('symposium_elastic') == 'on') { $elastic = ' elastic'; } else { $elastic = ''; }

					$use_wp_editor = false;
					if ($use_wp_editor) {
						// WordPress TinyMCE
						$settings = array(
						    'wpautop' => true,
						    'media_buttons' => false,
						    'tinymce' => array(
						        'theme_advanced_buttons1' => 'bold,italic,strikethrough,|,bullist,numlist,blockquote,|,justifyleft,justifycenter,justifyright,|,link,unlink,wp_more,|,spellchecker,fullscreen,wp_adv',
						        'theme_advanced_buttons2' => 'fontselect,forecolor,backcolor,fontsizeselect,underline,|,media,charmap,|,outdent,indent,|,undo,redo,wp_help',
						        'theme_advanced_buttons3' => '',
						        'theme_advanced_buttons4' => '',
						        'width' => '100%'
						    	),
						    'quicktags' => false,
						    'textarea_rows' => 10
						);					
						ob_start();
						wp_editor( '', 'wpstinymcereply', $settings );
						$editor = ob_get_contents();
						ob_end_clean();
						$html .= $editor;
					} else {
						$html .= '<textarea class="textarea_Editor reply-topic-text'.$elastic.'" id="symposium_reply_text"></textarea>';
						if (get_option('symposium_use_wysiwyg') == 'on') { $html .= '<br />'; }
					}

					// For admin's only set this as the answer
					if (get_option('symposium_use_answers') == 'on' && symposium_get_current_userlevel() == 5) {
						$html .= '<input type="checkbox" id="quick-reply-answer" /> '.__('Set this as the answer', 'wp-symposium').'<br />';
					}

					$html .= '<input type="submit" id="quick-reply-warning" class="symposium-button" style="float: left" value="'.__("Reply", "wp-symposium").'" />';

					// Upload
					if (get_option('symposium_forum_uploads')) {
						$html .= "<div id='symposium_user_login' style='display:none'>".strtolower($current_user->user_login)."</div>";
						$html .= "<div id='symposium_user_email' style='display:none'>".strtolower($current_user->user_email)."</div>";
						$html .= '<input id="forum_file_upload" name="file_upload" type="file" />';					
						$html .= '<div id="forum_file_list" style="clear:both">';
						
						if (get_option('symposium_img_db') == 'on') {
							
							// get list of uploaded files from database
							$sql = "SELECT tmpid, filename FROM ".$wpdb->prefix."symposium_topics_images WHERE tid = 0 AND uid = ".$current_user->ID." ORDER BY tmpid";
							$images = $wpdb->get_results($sql);
							foreach ($images as $file) {
								$html .= '<div>';
								$html .= '<a href=""';
								$ext = explode('.', $file->filename);
								if ($ext[sizeof($ext)-1]=='gif' || $ext[sizeof($ext)-1]=='jpg' || $ext[sizeof($ext)-1]=='png' || $ext[sizeof($ext)-1]=='jpeg') {
									$html .= ' target="_blank" rel="symposium_forum_images-'.$post->tid.'"';
								} else {
									$html .= ' target="_blank"';
								}
								$html .= ' title="'.$file->filename.'">'.$file->filename.'</a> ';
								$html .= '<img id="0" title="'.$file->filename.'" class="remove_forum_post link_cursor" src="'.get_option('symposium_images').'/delete.png" /> ';
								$html .= '</div>';	
							}
							
						} else {
							
							// get list of uploaded files from file system
							$targetPath = get_option('symposium_img_path')."/forum/".$post->tid.'_'.$current_user->ID.'_tmp/';
							if (file_exists($targetPath)) {
								$handler = opendir($targetPath);
								while ($file = readdir($handler)) {
									if ($file != "." && $file != ".." && $file != ".DS_Store") {
										$html .= '<div>';
										$html .= '<a href="'.get_option('symposium_img_url').'/forum/'.$post->tid.'_'.$current_user->ID.'_tmp/'.$file.'"';
										$ext = explode('.', $file);
										if ($ext[sizeof($ext)-1]=='gif' || $ext[sizeof($ext)-1]=='jpg' || $ext[sizeof($ext)-1]=='png' || $ext[sizeof($ext)-1]=='jpeg') {
											$html .= ' target="_blank" rel="symposium_forum_images-'.$post->tid.'"';
										} else {
											$html .= ' target="_blank"';
										}
										$html .= ' title="'.$file.'">'.$file.'</a> ';
										$html .= '<img id="'.$post->tid.'_'.$current_user->ID.'_tmp" title="'.$file.'" class="remove_forum_post link_cursor" src="'.get_option('symposium_images').'/delete.png" /> ';
										$html .= '</div>';
									}
								}			
								closedir($handler);
							}	
						}
						$html .= '</div>';
					}

				}				

				$html .= '</div>';
				
				// Add page title at the start
				if ( get_option('symposium_forum_ajax') ) {
					$html = $topic_subject.' | '.html_entity_decode(get_bloginfo('name'), ENT_QUOTES).'[|]'.$html;
				}

			} else {
				if ($group_id == 0) {
					$html .= "<p>".__("You are not permitted to reply on this forum.", "wp-symposium");
					if (symposium_get_current_userlevel() == 5) $html .= __('<br />Permissions are set via the WordPress admin dashboard->Symposium->Forum.', 'wp-symposium');						
					$html .= "</p>";
					
					// Show login form, and redirect back here
					if (get_option('symposium_forum_login') && !is_user_logged_in()) {

						$html .= wp_login_form(array(
   					 		'echo' => false,
						    'redirect' => get_option('symposium_forum_url').'#cid='.$post->topic_category.',tid='.$post->tid,
						    'form_id' => 'wps_forum_loginform',
						    'label_username' => __('Username'),
						    'label_password' => __('Password'),
						    'label_remember' => __('Remember Me'),
						    'label_log_in' => __('Log In'),
						    'id_username' => 'user_login',
						    'id_password' => 'user_pass',
						    'id_remember' => 'rememberme',
						    'id_submit' => 'wp-submit',
						    'remember' => true,
						    'value_username' => '',
						    'value_remember' => false
							));
					}
				}
			}
			
		
		} else {
			$html = __('Sorry, this topic is no longer available.', 'wp-symposium');
		}
		
		
	} else {
		// Final check if it's just not there
		$sql = "SELECT tid FROM ".$wpdb->prefix."symposium_topics WHERE tid = %d";
		if ($wpdb->get_var($wpdb->prepare($sql, $topic_id))) {
			$html .= __('You do not have permission to view this topic, sorry.', 'wp-symposium');
			if (symposium_get_current_userlevel() == 5) $html .= '<br /><br />'.__('Permissions are set via the WordPress admin dashboard->Symposium->Forum.', 'wp-symposium');
		} else {
			$html = __('Sorry, this topic does not exist.', 'wp-symposium');
		}
	}

	
	// Filter for profile header
	$html = apply_filters ( 'symposium_forum_topic_header_filter', $html, $topic_id );

	return $html;	
}

function symposium_getForum($cat_id, $limit_from=0, $group_id=0) {
	
	global $wpdb, $current_user;
		
	$limit_count = 10; // Use even number to ensure row backgrounds continue to alternate

	$previous_login = get_symposium_meta($current_user->ID, 'previous_login');
	$forum_all = get_symposium_meta($current_user->ID, 'forum_all');

	$plugin = WP_CONTENT_URL.'/plugins/wp-symposium/';
	
	// Get forum URL worked out
	$forum_url = symposium_get_url('forum');
	if (strpos($forum_url, '?') !== FALSE) {
		$q = "&";
	} else {
		$q = "?";
	}

	$html = '';	

	// Get group URL worked out
	$continue = true;
	if ($group_id > 0) {
		$forum_url = symposium_get_url('group');
		if (strpos($forum_url, '?') !== FALSE) {
			$q = "&gid=".$group_id."&";
		} else {
			$q = "?gid=".$group_id."&";
		}
		$group_info = $wpdb->get_row($wpdb->prepare("SELECT content_private, allow_new_topics FROM ".$wpdb->prefix . 'symposium_groups WHERE gid='.$group_id));
		$content_private = $group_info->content_private;
		$allow_new_topics = $group_info->allow_new_topics;
		$continue = false;
		if (symposium_member_of($group_id) == 'yes') {
			$continue = true;
		} else {
			if ($content_private != 'on') {
				$continue = true;
			}			
		}
	}

	// Get list of roles for this user
    $user_roles = $current_user->roles;
    $user_role = strtolower(array_shift($user_roles));
    if ($user_role == '') $user_role = 'NONE';
    					
	// If in a group forum check that they are a member!
	if ( $continue ) {
		
		// Post preview
		$snippet_length = get_option('symposium_preview1');
		if ($snippet_length == '') { $snippet_length = '45'; }
		$snippet_length_long = get_option('symposium_preview2');
		if ($snippet_length_long == '') { $snippet_length_long = '45'; }
		
		if ($limit_from == 0) {
		
			$template = get_option('symposium_template_forum_header');
			$template = str_replace("[]", "", stripslashes($template));
	
			// Breadcrumbs	
			$breadcrumbs = '<div id="forum_breadcrumbs" class="breadcrumbs label">';
			$allow_new = 'on';

			if ($cat_id > 0) {
		
				if (!get_option('symposium_wps_lite')) {
		
					$this_level = $wpdb->get_row($wpdb->prepare("SELECT cid, title, allow_new, cat_parent, stub FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d", $cat_id));
					$allow_new = $this_level->allow_new;
					if ($this_level->cat_parent == 0) {
						if (get_option('symposium_forum_ajax') == 'on') {
							$breadcrumbs .= '<a href="#cid=0" class="category_title" title="0">'.__('Forum Home', 'wp-symposium')."</a> &rarr; ";
							$breadcrumbs .= '<a href="#cid='.$this_level->cid.'" class="category_title" title="'.$this_level->cid.'">'.trim($this_level->title).'</a>';
						} else {
							if (get_option('symposium_permalink_structure') && $group_id == 0) {
								$breadcrumbs .= '<a href="'.$forum_url.'" title="0">'.__('Forum Home', 'wp-symposium')."</a> &rarr; ";
								$breadcrumbs .= '<a href="'.$forum_url.'/'.$this_level->stub.'" title="'.$this_level->cid.'">'.trim($this_level->title).'</a>';
							} else {
								$breadcrumbs .= '<a href="'.$forum_url.$q.'cid=0" title="0">'.__('Forum Home', 'wp-symposium')."</a> &rarr; ";
								$breadcrumbs .= '<a href="'.$forum_url.$q."cid=".$this_level->cid.'" title="'.$this_level->cid.'">'.trim($this_level->title).'</a>';
							}
						}
					} else {
		
						$parent_level = $wpdb->get_row($wpdb->prepare("SELECT cid, title, cat_parent, stub FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d", $this_level->cat_parent));
		
						if ($parent_level->cat_parent == 0) {
							if (get_option('symposium_forum_ajax') == 'on') {
								$breadcrumbs .= '<a href="#cid=0" class="category_title" title="0">'.__('Forum Home', 'wp-symposium')."</a> &rarr; ";
							} else {
								if (get_option('symposium_permalink_structure') && $group_id == 0) {
									$breadcrumbs .= '<a href="'.$forum_url.'" title="0">'.__('Forum Home', 'wp-symposium')."</a> &rarr; ";
								} else {
									$breadcrumbs .= '<a href="'.$forum_url.$q.'cid=0" title="0">'.__('Forum Home', 'wp-symposium')."</a> &rarr; ";
								}
							}
						} else {
							$parent_level_2 = $wpdb->get_row($wpdb->prepare("SELECT cid, title, cat_parent, stub FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d", $parent_level->cat_parent));
							if (get_option('symposium_forum_ajax') == 'on') {
								$breadcrumbs .= '<a href="#cid=0" class="category_title" title="0">'.__('Forum Home', 'wp-symposium')."</a> &rarr; " ;
								$breadcrumbs .= '<a href="#cid='.$parent_level_2->cid.'" class="category_title" title="'.$parent_level_2->cid.'">'.$parent_level_2->title."</a> &rarr; ";
							} else {
								if (get_option('symposium_permalink_structure') && $group_id == 0) {
									$breadcrumbs .= '<a href="'.$forum_url.'" title="0">'.__('Forum Home', 'wp-symposium')."</span></a> &rarr; " ;
									$breadcrumbs .= '<a href="'.$forum_url.'/'.$parent_level_2->stub.'"  title="'.$parent_level_2->cid.'">'.$parent_level_2->title."</a> &rarr; ";
								} else {
									$breadcrumbs .= '<a href="'.$forum_url.$q.'cid=0" title="0">'.__('Forum Home', 'wp-symposium')."</span></a> &rarr; " ;
									$breadcrumbs .= '<a href="'.$forum_url.$q."cid=".$parent_level_2->cid.'"  title="'.$parent_level_2->cid.'">'.$parent_level_2->title."</a> &rarr; ";
								}
							}
						}
						if (get_option('symposium_forum_ajax') == 'on') {
							$breadcrumbs .= '<a href="#cid='.$parent_level->cid.'" class="category_title" title="'.$parent_level->cid.'">'.$parent_level->title."</a> &rarr; " ;
							$breadcrumbs .= '<a href="#cid='.$this_level->cid.'" class="category_title" title="'.$this_level->cid.'">'.$this_level->title."</a>" ;
						} else {
							if (get_option('symposium_permalink_structure') && $group_id == 0) {
								$breadcrumbs .= '<a href="'.$forum_url.'/'.$parent_level->stub.'" title="'.$parent_level->cid.'">'.$parent_level->title."</a> &rarr; " ;
								$breadcrumbs .= '<a href="'.$forum_url.'/'.$this_level->stub.'" title="'.$this_level->cid.'">'.$this_level->title."</a>" ;
							} else {
								$breadcrumbs .= '<a href="'.$forum_url.$q."cid=".$parent_level->cid.'" title="'.$parent_level->cid.'">'.$parent_level->title."</a> &rarr; " ;
								$breadcrumbs .= '<a href="'.$forum_url.$q."cid=".$this_level->cid.'" title="'.$this_level->cid.'">'.$this_level->title."</a>" ;
							}
						}
					}

				} else {
					// Lite mode
					$this_level = $wpdb->get_row($wpdb->prepare("SELECT allow_new, cat_parent FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d", $cat_id));
					$allow_new = $this_level->allow_new;
					$breadcrumbs .= '<a href="#cid=0" class="category_title" title="0">'.__('Forum Home', 'wp-symposium')."</a>";
					if ($this_level->cat_parent > 0) {
						$breadcrumbs .= ' &rarr; <a href="'.$forum_url.$q."cid=".$this_level->cat_parent.'" title="'.$this_level->cat_parent.'">'.__('Up a level', 'wp-symposium')."</a>" ;
					}
				}
			
			}
				
			$breadcrumbs .= '</div>';
						
			// If a group forum, check that is a member - and that new topics can be created
			if ($group_id > 0) {
				// Is user a member of the group?
				$sql = "SELECT COUNT(*) FROM ".$wpdb->prefix."symposium_group_members WHERE group_id=%d AND valid='on' AND member_id=%d";
				$member_count = $wpdb->get_var($wpdb->prepare($sql, $group_id, $current_user->ID));
				if ($member_count == 0) { 
					// Non members can never create topics
					$allow_new = ''; 
				} else {
					$sql = "SELECT member_id FROM ".$wpdb->prefix."symposium_group_members WHERE group_id=%d AND member_id=%d and admin='on'";
					$admin_check = $wpdb->get_var($wpdb->prepare($sql, $group_id, $current_user->ID));
					if ($admin_check || symposium_get_current_userlevel() == 5) {
						// Group and site admin can always create new topics
						$allow_new = 'on';
					} else {
						// Get setting from Group settings
						$allow_new = $allow_new_topics;
					}
				}	
			}
	
			// Check to see if this member is in the included list of roles
			$user = get_userdata( $current_user->ID );
			$capabilities = $user->{$wpdb->prefix.'capabilities'};
			$can_edit = false;
			$viewer = str_replace('_', '', str_replace(' ', '', strtolower(get_option('symposium_forum_editor'))));
			if ($capabilities) {
				
				foreach ( $capabilities as $role => $name ) {
					if ($role) {
						$role = strtolower($role);
						$role = str_replace(' ', '', $role);
						$role = str_replace('_', '', $role);
						if (WPS_DEBUG) $html .= 'Checking role '.$role.' against '.$viewer.'<br />';
						if (strpos($viewer, $role) !== FALSE) $can_edit = true;
					}
				}		 														
			
			}	
			if ($group_id > 0) {
				$sql = "SELECT COUNT(*) FROM ".$wpdb->prefix."symposium_group_members WHERE group_id=%d AND valid='on' AND member_id=%d";
				$member_count = $wpdb->get_var($wpdb->prepare($sql, $group_id, $current_user->ID));
				if ($member_count == 0) { $can_edit = false; } else { $can_edit = true; }
			}	
						
			// New Topic Button & Form	
			$new_topic_form = "";
			if (is_user_logged_in()) {
	
				if ( ($can_edit) && ($allow_new == 'on') ) {
	
					$new_topic_button = '<input type="submit" class="symposium-button floatright" id="new-topic-button" value="'.__("New Topic", "wp-symposium").'" />';
	
					$new_topic_form .= '<div name="new-topic" id="new-topic" style="display:none;">';
						$new_topic_form .= '<input type="hidden" id="cid" value="'.$cat_id.'">';
						$new_topic_form .= '<div id="new-topic-subject-label" class="new-topic-subject label">'.__("Topic Subject", "wp-symposium").'</div>';
						$new_topic_form .= '<input class="new-topic-subject-input" type="text" id="new_topic_subject" value="">';
						$new_topic_form .= '<div class="new-topic-subject label">'.__("First Post in Topic", "wp-symposium").'</div>';
						if (get_option('symposium_elastic') == 'on' && get_option('symposium_use_wysiwyg') != 'on') { $elastic = ' elastic'; } else { $elastic = ''; }
						$new_topic_form .= '<textarea class="new-topic-subject-text'.$elastic.'" id="new_topic_text"></textarea>';
						if (get_option('symposium_use_wysiwyg') == 'on') { $new_topic_form .= '<br />'; }
						
						$defaultcat = $wpdb->get_var($wpdb->prepare("SELECT cid FROM ".$wpdb->prefix."symposium_cats WHERE defaultcat = 'on'"));
	
						if ($group_id == 0) {
	
							$new_topic_form .= '<div class="new-topic-category label">'.__("Select a Category", "wp-symposium").': ';
							if (current_user_can('level_10')) {
								$categories = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix.'symposium_cats ORDER BY title');			
							} else {
								$categories = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix.'symposium_cats WHERE allow_new = "on" ORDER BY title');			
							}
							if ($categories) {
								$new_topic_form .= '<select name="new_topic_category" id="new_topic_category">';
					
								foreach ($categories as $category) {
									
									// Check that permitted to category
									$cat_roles = unserialize($category->level);
									$cat_roles = str_replace('_', '', str_replace(' ', '', $cat_roles));

									if (strpos(strtolower($cat_roles), 'everyone,') !== FALSE || strpos(strtolower($cat_roles), $user_role.',') !== FALSE) {		

										$new_topic_form .= '<option value='.$category->cid;
										if ($cat_id > 0) {
											if ($category->cid == $cat_id) { $new_topic_form .= " SELECTED"; }
										} else {
											if ($category->cid == $defaultcat) { $new_topic_form .= " SELECTED"; }
										}
										$new_topic_form .= '>'.stripslashes($category->title).'</option>';
										
									}
								}
					
								$new_topic_form .= '</select>';
							}
					
						} else {
							// No categories for groups
							$new_topic_form .= '<input name="new_topic_category" type="hidden" value="0">';
						}
	
						// Upload
						if (get_option('symposium_forum_uploads')) {
							$new_topic_form .= '<div style="clear:both; margin-top:10px;">';
							$new_topic_form .= "<div id='symposium_user_login' style='display:none'>".strtolower($current_user->user_login)."</div>";
							$new_topic_form .= "<div id='symposium_user_email' style='display:none'>".strtolower($current_user->user_email)."</div>";
							
							$new_topic_form .= '<input id="forum_file_upload" name="file_upload" type="file" />';					
							$new_topic_form .= '<div id="forum_file_list" style="clear:both">';
							$new_topic_form .= '</div>';
							$new_topic_form .= '</div>';
						}
						
						$new_topic_form .= '<div>';
						if ($forum_all != 'on') {
							$new_topic_form .= '<input style="margin: 0;" type="checkbox" id="new_topic_subscribe"> '.__("Tell me when I get any replies", "wp-symposium").'<br />';
						}
						if (get_option('symposium_use_answers') == 'on') {
							$new_topic_form .= '<input style="margin: 0 0 10px 0;" type="checkbox" id="info_only"> '.__('This topic is for information only, no answer will be selected.', 'wp-symposium');
						}
						$new_topic_form .= '</div>';
	
						$new_topic_form .= '<input id="symposium_new_post" type="submit" class="symposium-button" style="float: left" value="'.__("Post", "wp-symposium").'" />';
						$new_topic_form .= '<input id="cancel_post" type="submit" class="symposium-button clear" onClick="javascript:void(0)" value="'.__("Cancel", "wp-symposium").'" />';
	
	
						$new_topic_form .= '</div>';
	
					$new_topic_form .= '</div>';
	
				} else {
	
					if ($group_id == 0 && $allow_new == 'on') {
						$new_topic_form = "<p>".__("You are not permitted to start a new topic.", "wp-symposium");	
						if (symposium_get_current_userlevel() == 5) $new_topic_form .= __('<br />Permissions are set via the WordPress admin dashboard->Symposium->Forum.', 'wp-symposium');						
						$new_topic_forum .= "</p>";
					}
					if ($group_id > 0 && $allow_new != 'on' && $member_count > 0) {
						$new_topic_form = "<p>".__("New topics are disabled on this forum.", "wp-symposium")."</p>";							
					}
					$new_topic_button = '';
					
				}
				
	
			} else {
	
				$new_topic_button = '';
	
				if (get_option('symposium_forum_login') == "on") {
					$new_topic_form .= "<div>".__("Until you login, you can only view the forum.", "wp-symposium");
					$new_topic_form .= " <a href=".wp_login_url( get_permalink() )." class='simplemodal-login' title='".__("Login", "wp-symposium")."'>".__("Login", "wp-symposium").".</a></div><br />";
				}
	
			}
	
			// Options
			$digest = "";
			$subscribe = "";
			if (is_user_logged_in()) {
		
				$send_summary = get_option('symposium_send_summary');
				if ($send_summary == "on" && $cat_id == 0) {
					$forum_digest = get_symposium_meta($current_user->ID, 'forum_digest');
					$digest = "<div class='symposium_subscribe_option label'>";
					$digest .= "<input type='checkbox' id='symposium_digest' name='symposium_digest'";
					if ($forum_digest == 'on') { $digest .= ' checked'; } 
					$digest .= "> ".__("Receive digests via email", "wp-symposium");
					$digest .= "</div>";
				}
				if ($cat_id > 0 && $forum_all != 'on') {
					$subscribed_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix."symposium_subs WHERE tid = 0 AND cid = %d AND uid = %d", $cat_id, $current_user->ID));
					$subscribe = "<div class='symposium_subscribe_option label'>";
					$subscribe .= "<input type='checkbox' title='".$cat_id."' id='symposium_subscribe' name='symposium_subscribe'";
					if ($subscribed_count > 0) { $subscribe .= ' checked'; } 
					$subscribe .= "> ".__("Tell me when there are new topics posted", "wp-symposium");
					$subscribe .= "</div>";
				}
	
			}	
	
			// Options above forum table
			$forum_options = "<div id='forum_options'>";
	
				$forum_options .= "<a id='show_search' class='label' href='javascript:void(0)'>".__("Search", "wp-symposium")."</a>";
				$forum_options .= "&nbsp;&nbsp;&nbsp;&nbsp;<a id='show_all_activity' href='javascript:void(0)'>".__("Activity", "wp-symposium")."</a>";
				$forum_options .= "&nbsp;&nbsp;&nbsp;&nbsp;<a id='show_threads_activity' class='label' href='javascript:void(0)'>".__("Latest Topics", "wp-symposium")."</a>";
	
				if (is_user_logged_in()) {
					$forum_options .= "&nbsp;&nbsp;&nbsp;&nbsp;<a id='show_activity' class='label' href='javascript:void(0)'>".__("My Activity", "wp-symposium")."</a>";
					$forum_options .= "&nbsp;&nbsp;&nbsp;&nbsp;<a id='show_favs' class='label' href='javascript:void(0)'>".__("Favorites", "wp-symposium")."</a>";
				}
	
			$forum_options .= "</div>";

			// Sharing icons
			if (get_option('symposium_sharing') != '' && $cat_id > 0) {
				$sharing = show_sharing_icons($cat_id, 0, get_option('symposium_sharing'), $group_id);
			} else {
				$sharing = "";
			}
	
			// Replace template tokens and add to output
			$template = str_replace('[new_topic_form]', $new_topic_form, $template);
			$template = str_replace('[new_topic_button]', $new_topic_button, $template);
			$template = str_replace('[breadcrumbs]', $breadcrumbs, $template);
			$template = str_replace('[digest]', $digest, $template);
			$template = str_replace('[subscribe]', $subscribe, $template);
			$template = str_replace('[forum_options]', $forum_options, $template);
			$template = str_replace('[sharing]', $sharing, $template);
	
			$html .= $template;
	
			if ($group_id == 0) {
	
				// Show child categories in this category (and not in a group) ++++++++++++++++++++++++++++++++++++++++++++++++++
				$sql = $wpdb->prepare("SELECT * FROM ".$wpdb->prefix."symposium_cats WHERE cat_parent = %d ORDER BY listorder", $cat_id);
				$categories = $wpdb->get_results($sql);

				// Row template		
				if ( $group_id > 0 ) {
					$template = get_option('symposium_template_group_forum_category');
				} else {
					$template = get_option('symposium_template_forum_category');
				}
				$template = str_replace("[]", "", stripslashes($template));

				if ($categories) {
					
					// Start of table
					$html .= '<div id="symposium_table">';
	
						$num_cats = $wpdb->num_rows;
						$cnt = 0;
						foreach($categories as $category) {

 							// Get list of permitted roles from forum_cat and check allowed
							$cat_roles = unserialize($category->level);
							$cat_roles = str_replace('_', '', str_replace(' ', '', $cat_roles));
							if (strpos(strtolower($cat_roles), 'everyone,') !== FALSE || strpos(strtolower($cat_roles), $user_role.',') !== FALSE) {

								$cnt++;
								if ($cnt&1) {
									$html .= '<div class="row ';
									if ($cnt == $num_cats) { $html .= ' round_bottom_left round_bottom_right'; }
									$html .= '">';
								} else {
									$html .= '<div class="row_odd ';
									if ($cnt == $num_cats) { $html .= ' round_bottom_left round_bottom_right'; }
									$html .= '">';
								}
							
									// Start row template
									$row_template = $template;
						
									// Last Topic/Reply
									$last_topic = $wpdb->get_row($wpdb->prepare("
										SELECT tid, stub, topic_subject, topic_approved, topic_post, topic_date, topic_owner, topic_sticky, topic_parent, display_name, topic_category 
										FROM ".$wpdb->prefix."symposium_topics t 
										INNER JOIN ".$wpdb->base_prefix."users u ON u.ID = t.topic_owner
										WHERE (topic_approved = 'on' OR topic_owner = %d) AND topic_parent = 0 AND topic_category = %d ORDER BY topic_date DESC LIMIT 0,1", $current_user->ID, $category->cid)); 
		
									if ($last_topic) {
										
											if (!get_option('symposium_wps_lite')) {
												$reply = $wpdb->get_row($wpdb->prepare("
													SELECT t.*, u.display_name
													FROM ".$wpdb->prefix."symposium_topics t 
													LEFT JOIN ".$wpdb->base_prefix."users u ON t.topic_owner = u.ID
													WHERE (topic_approved = 'on' OR topic_owner = %d) 
													  AND topic_parent = %d 
													ORDER BY topic_date DESC LIMIT 0,1", $current_user->ID, $last_topic->tid)); 
											} else {
												$reply = false;
											}
		
											// Avatar
											if ($reply) {
												$topic_owner = $reply->topic_owner;
											} else {
												$topic_owner = $last_topic->topic_owner;									
											}
											if (strpos($row_template, '[avatar') !== FALSE) {
	
												if (strpos($row_template, '[avatar]')) {
													$row_template = str_replace("[avatar]", get_avatar($topic_owner, 32), $row_template);
												} else {
													$x = strpos($row_template, '[avatar');
													$avatar = substr($row_template, 0, $x);
													$avatar2 = substr($row_template, $x+8, 2);
													$avatar3 = substr($row_template, $x+11, strlen($row_template)-$x-11);
													
													$row_template = $avatar . get_avatar($topic_owner, $avatar2) . $avatar3;
												}
											}
											
											if ($reply) {
												$row_template = str_replace("[replied]", symposium_profile_link($reply->topic_owner)." ".__("replied to", "wp-symposium")." ", $row_template);	
												$subject = symposium_bbcode_remove($last_topic->topic_subject);
												if (get_option('symposium_forum_ajax') == 'on') {
													$subject = '<a title="'.$last_topic->tid.'" class="topic_subject backto row_link_topic" href="#cid='.$category->cid.',tid='.$last_topic->tid.'">'.stripslashes($subject).'</a> ';
												} else {
													if (get_option('symposium_permalink_structure') && $group_id == 0) {
														$perma_cat = symposium_get_forum_category_part_url($reply->topic_category);
														$subject = '<a class="backto row_link_topic" href="'.$forum_url.'/'.$perma_cat.$last_topic->stub.'">'.stripslashes($subject).'</a> ';
													} else {
														$subject = '<a class="backto row_link_topic" href="'.$forum_url.$q."cid=".$last_topic->topic_category."&show=".$last_topic->tid.'">'.stripslashes($subject).'</a> ';
													}
												}
												if ($reply->topic_approved != 'on') { $subject .= "<em>[".__("pending approval", "wp-symposium")."]</em> "; }
												$row_template = str_replace("[subject]", $subject, $row_template);	
												$row_template = str_replace("[ago]", symposium_time_ago($reply->topic_date), $row_template);	
											} else {
												$row_template = str_replace("[replied]", symposium_profile_link($last_topic->topic_owner)." ".__("started", "wp-symposium")." ", $row_template);	
												$subject = symposium_bbcode_remove($last_topic->topic_subject);
												if (get_option('symposium_forum_ajax') == 'on') {
													$subject = '<a title="'.$last_topic->tid.'" class="topic_subject backto row_link_topic" href="#cid='.$category->cid.',tid='.$last_topic->tid.'">'.stripslashes($subject).'</a> ';
												} else {
													if (get_option('symposium_permalink_structure') && $group_id == 0) {
														$perma_cat = symposium_get_forum_category_part_url($last_topic->topic_category);
														$subject = '<a class="backto row_link_topic" href="'.$forum_url.'/'.$perma_cat.$last_topic->stub.'">'.stripslashes($subject).'</a> ';
													} else {
														$subject = '<a class="backto row_link_topic" href="'.$forum_url.$q."cid=".$last_topic->topic_category."&show=".$last_topic->tid.'">'.stripslashes($subject).'</a> ';
													}
												}
												$row_template = str_replace("[subject]", $subject, $row_template);	
												$row_template = str_replace("[ago]", symposium_time_ago($last_topic->topic_date), $row_template);	
											}
		
									} else {
		
										if (strpos($row_template, '[avatar') !== FALSE) {
											if (strpos($row_template, '[avatar]')) {
												$row_template = str_replace("[avatar]", get_avatar($reply->topic_owner, 32), $row_template);						
											} else {
												$x = strpos($row_template, '[avatar');
												$avatar = substr($row_template, 0, $x);
												$avatar2 = substr($row_template, $x+8, 2);
												$avatar3 = substr($row_template, $x+11, strlen($row_template)-$x-11);
												$row_template = $avatar . $avatar3;									
											}
										}
										$row_template = str_replace("[replied]", "", $row_template);	
										$row_template = str_replace("[subject]", "", $row_template);	
										$row_template = str_replace("[replied]", "", $row_template);	
										$row_template = str_replace("[subject]", "", $row_template);	
										$row_template = str_replace("[ago]", "", $row_template);
									}
						
									// Replies
									if (get_option('symposium_use_styles')) {
										$text_color = get_option('symposium_text_color');
									} else {
										$text_color = '';
									}
									if (!get_option('symposium_wps_lite')) {
										$post_count = 0;
		
										$sql = "SELECT COUNT(t.tid)
												FROM ".$wpdb->prefix."symposium_topics t 
												WHERE (t.topic_approved = 'on' OR t.topic_owner = %d) 
												  AND t.topic_category = %d
												  AND t.topic_parent > 0";
		
										$post_count = $wpdb->get_var($wpdb->prepare($sql, $current_user->ID, $category->cid));
										
										if ($post_count > 0) { 
											$post_count_html = "<div class='post_count' style='color:".$text_color.";'>".$post_count."</div>";
												$post_count_html .= "<div style='color:".$text_color.";' class='post_count_label'>";
												if ($post_count > 1) {
													$post_count_html .= __("REPLIES", "wp-symposium");
												} else {
													$post_count_html .= __("REPLY", "wp-symposium");
												}
												$post_count_html .= "</div>";
												$row_template = str_replace("[post_count]", $post_count_html, $row_template);	
										} else {
												$row_template = str_replace("[post_count]", "", $row_template);	
										}
									} else {
										$row_template = str_replace("[post_count]", "", $row_template);	
									}
										
									// Topic Count
									if (!get_option('symposium_wps_lite')) {
										$topic_count = symposium_get_topic_count($category->cid);
			
										if ($topic_count > 0) {
											$topic_count_html = "<div class='post_count' style='color:".$text_color.";'>".$topic_count."</div>";
											$topic_count_html .= "<div style='color:".$text_color.";' class='post_count_label'>";
											if ($topic_count != 1) {
												$topic_count_html .= __("TOPICS", "wp-symposium");
											} else {
												$topic_count_html .= __("TOPIC", "wp-symposium");
											}
											$topic_count_html .= "</div>";
											$row_template = str_replace("[topic_count]", $topic_count_html, $row_template);	
										} else {
											$row_template = str_replace("[topic_count]", "", $row_template);	
										}
									} else {
										$row_template = str_replace("[topic_count]", "", $row_template);	
									}
		
									// Check for new topics or replies in this category
									$category_title_html = "";
									$recursive_new = false;
									
									if (is_user_logged_in()) {
										$sql = "SELECT COUNT(*) FROM ".$wpdb->prefix."symposium_topics WHERE topic_category = %d AND topic_started >= %s AND topic_owner != %d";
										$new_topics = $wpdb->get_var($wpdb->prepare($sql, $category->cid, $previous_login, $current_user->ID));
									
										if ($new_topics > 0) {
											$recursive_new = true;
										}
									
										$sql = "SELECT COUNT(*) FROM ".$wpdb->prefix."symposium_topics t 
											LEFT JOIN ".$wpdb->prefix."symposium_topics p ON t.topic_parent = p.tid 
											WHERE t.topic_started >= %s
											  AND t.topic_owner != %d 
											  AND p.topic_category = %d";
										$new_replies = $wpdb->get_var($wpdb->prepare($sql, $previous_login, $current_user->ID, $category->cid));
		
										if ($new_replies > 0) {
											$recursive_new = true;
										}
		
										if ($recursive_new && get_option('symposium_forum_stars')) {
											$category_title_html .= "<img src='".get_option('symposium_images')."/new.gif' alt='New!' /> ";
										}
									}
									
									// Category title
									if (get_option('symposium_forum_ajax') == 'on') {
										$category_title_html .= '<a class="category_title backto row_link" href="#cid='.$category->cid.'" title='.$category->cid.'>'.stripslashes($category->title).'</a>';
									} else {
										if (get_option('symposium_permalink_structure') && $group_id == 0) {
											$category_title_html .= '<a class="backto row_link" href="'.$forum_url.'/'.$category->stub.'">'.stripslashes($category->title).'</a> ';
										} else {
											$category_title_html .= '<a class="backto row_link" href="'.$forum_url.$q."cid=".$category->cid.'">'.stripslashes($category->title).'</a> ';
										}
									}
									$subscribed = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix."symposium_subs WHERE cid = %d AND uid = %d", $category->cid, $current_user->ID));
									if ($subscribed > 0 && $forum_all != 'on') { $category_title_html .= ' <img src="'.get_option('symposium_images').'/orange-tick.gif" alt="'.__('Subscribed', 'wp-symposium').'" />'; } 
									$row_template = str_replace("[category_title]", $category_title_html, $row_template);	
									
									// Category description
									$category_desc_html = stripslashes($category->cat_desc);
									$row_template = str_replace("[category_desc]", $category_desc_html, $row_template);	
		
									// Add row template to HTML
									$html .= $row_template;
								
									// Separator
									$html .= "<div class='sep'></div>";											
		
		
								$html .= "</div>"; // Row in the table
							}
						}
	
					$html .= '</div>';
			
				}
			}
		}
	
		// Show topics in this category ++++++++++++++++++++++++++++++++++++++++++++++++++
		if (!get_option('symposium_wps_lite')) {
			$sql = "SELECT tid, stub, topic_subject, topic_approved, topic_post, topic_owner, topic_category, topic_date, display_name, topic_sticky, allow_replies, topic_started, 
				(SELECT COUNT(tid) FROM ".$wpdb->prefix."symposium_topics s WHERE s.topic_parent = t.tid AND s.topic_answer = 'on') AS answers, for_info
				FROM ".$wpdb->prefix."symposium_topics t INNER JOIN ".$wpdb->base_prefix."users u ON t.topic_owner = u.ID 
				WHERE (topic_approved = 'on' OR topic_owner = %d) AND topic_category = %d AND topic_parent = 0 AND topic_group = %d ORDER BY topic_sticky DESC, topic_date DESC 
				LIMIT %d, %d";
			$query = $wpdb->get_results($wpdb->prepare($sql, $current_user->ID, $cat_id, $group_id, $limit_from, $limit_count)); 
		} else {
			$sql = "SELECT tid, stub, topic_subject, topic_approved, topic_post, topic_owner, topic_category, topic_date, display_name, topic_sticky, allow_replies, topic_started,
				0 AS answers, for_info
				FROM ".$wpdb->prefix."symposium_topics t INNER JOIN ".$wpdb->base_prefix."users u ON t.topic_owner = u.ID 
				WHERE (topic_approved = 'on' OR topic_owner = %d) AND topic_category = %d AND topic_parent = 0 AND topic_group = %d ORDER BY topic_sticky DESC, topic_date DESC 
				LIMIT %d, %d";
			$query = $wpdb->get_results($wpdb->prepare($sql, $current_user->ID, $cat_id, $group_id, $limit_from, $limit_count)); 
		}
	
		$num_topics = $wpdb->num_rows;
	
		// Row template		
		if ( $group_id > 0 ) {
			$template = get_option('symposium_template_group_forum_topic');
		} else {
			$template = get_option('symposium_template_forum_topic');
		}
		$template = str_replace("[]", "", stripslashes($template));
			
		// Favourites
		$favs = get_symposium_meta($current_user->ID, 'forum_favs');
	
		$cnt = 0;									
					
		if ($query) {

			// Shouldn't get here, but this is a double check in case of deep links/hackers
			// Get list of permitted roles from forum_cat and check allowed
			$sql = "SELECT level FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d";
			$levels = $wpdb->get_var($wpdb->prepare($sql, $query[0]->topic_category));
			$cat_roles = unserialize($levels);
			$cat_roles = str_replace('_', '', str_replace(' ', '', $cat_roles));
			if ($group_id > 0 || strpos(strtolower($cat_roles), 'everyone,') !== FALSE || strpos(strtolower($cat_roles), $user_role.',') !== FALSE) {		

				if ($limit_from == 0) {
					$html .= '<div id="symposium_table">';		
				}
		
					// For every topic in this category 
					foreach ($query as $topic) {
	
						$cnt++;
	
						if (!get_option('symposium_wps_lite')) {
							$replies = $wpdb->get_var($wpdb->prepare("SELECT COUNT(tid) FROM ".$wpdb->prefix."symposium_topics WHERE (topic_approved = 'on' OR topic_owner = %d) AND topic_parent = %d", $current_user->ID, $topic->tid));
							$reply_views = $wpdb->get_var($wpdb->prepare("SELECT sum(topic_views) FROM ".$wpdb->prefix."symposium_topics WHERE (topic_approved = 'on' OR topic_owner = %d) AND tid = %d", $current_user->ID, $topic->tid));
						} else {
							$replies = false;
						}
						
						if ($cnt&1) {
							$html .= '<div id="row'.$topic->tid.'" style="border-radius:0px;-moz-border-radius:0px" class="row ';
							if ($cnt == $num_topics) { $html .= ' round_bottom_left round_bottom_right'; }
						} else {
							$html .= '<div id="row'.$topic->tid.'" style="border-radius:0px;-moz-border-radius:0px" class="row_odd ';
							if ($cnt == $num_topics) { $html .= ' round_bottom_left round_bottom_right'; }
						}
						$closed_word = strtolower(get_option('symposium_closed_word'));
						if ( strpos(strtolower($topic->topic_subject), "{".$closed_word."}") > 0) {
							$color_check = ' transparent';
						} else {
							$color_check = '';
						}
						$html .= $color_check.'">';

							// Reset template
							$topic_template = $template;
						
							// Started by/Last Reply
							if (!get_option('symposium_wps_lite')) {
								$sql = "SELECT tid, topic_subject, topic_approved, topic_post, topic_owner, topic_date, display_name, topic_sticky, topic_parent 
									FROM ".$wpdb->prefix."symposium_topics t INNER JOIN ".$wpdb->base_prefix."users u ON t.topic_owner = u.ID 
									WHERE (topic_approved = 'on' OR topic_owner = %d) AND topic_parent = %d ORDER BY tid DESC";
								$last_post = $wpdb->get_results($wpdb->prepare($sql, $current_user->ID, $topic->tid)); 
							} else {
								$last_post = false;
							}
								
							$child_is_new = false;
							if ( $last_post ) {
								
								$done_last_reply = false;
								foreach ($last_post as $each_last_post) {
									
									if ($each_last_post->topic_date > $previous_login && $each_last_post->topic_owner != $current_user->ID && is_user_logged_in()) {
										$child_is_new = true;
									}
								
									if (!$done_last_reply) {
										
										$done_last_reply = true;
										
										// Avatar
										if (strpos($topic_template, '[avatar') !== FALSE) {
											if (strpos($topic_template, '[avatar]')) {
												$topic_template = str_replace("[avatar]", get_avatar($each_last_post->topic_owner, 32), $topic_template);						
											} else {
												$x = strpos($topic_template, '[avatar');
												$avatar = substr($topic_template, 0, $x);
												$avatar2 = substr($topic_template, $x+8, 2);
												$avatar3 = substr($topic_template, $x+11, strlen($topic_template)-$x-11);
															
												$topic_template = $avatar . get_avatar($each_last_post->topic_owner, $avatar2) . $avatar3;
											
											}
										}
										$topic_template = str_replace("[replied]", __("Last reply by", "wp-symposium")." ".symposium_profile_link($each_last_post->topic_owner), $topic_template);	
										$topic_template = str_replace("[ago]", " ".symposium_time_ago($each_last_post->topic_date), $topic_template);	
										$post = stripslashes($each_last_post->topic_post);
										if ( strlen($post) > $snippet_length_long ) { $post = substr($post, 0, $snippet_length_long)."..."; }
										$post = symposium_bbcode_remove($post);
										$post = strip_tags($post);
										if ($each_last_post->topic_approved != 'on') { $post .= " <em>[".__("pending approval", "wp-symposium")."]</em>"; }
										$topic_template = str_replace("[topic]", "<br /><span class='row_topic_text'>".$post."</span>", $topic_template);	
										
									}
									
								}
									
							} else {
								
								// Avatar
								if (strpos($topic_template, '[avatar') !== FALSE) {
									if (strpos($topic_template, '[avatar]')) {
										$topic_template = str_replace("[avatar]", get_avatar($topic->topic_owner, 32), $topic_template);						
									} else {
										$x = strpos($topic_template, '[avatar');
										$avatar = substr($topic_template, 0, $x);
										$avatar2 = substr($topic_template, $x+8, 2);
										$avatar3 = substr($topic_template, $x+11, strlen($topic_template)-$x-11);
													
										$topic_template = $avatar . get_avatar($topic->topic_owner, $avatar2) . $avatar3;
									
									}
								}
								$topic_template = str_replace("[replied]", __("Started by", "wp-symposium")." ".symposium_profile_link($topic->topic_owner), $topic_template);	
								$topic_template = str_replace("[ago]", " ".symposium_time_ago($topic->topic_started).".", $topic_template);	
								$topic_template = str_replace("[topic]", "", $topic_template);	
							}
					
							// Views
							if (!get_option('symposium_wps_lite')) {
								$views_html = "<div class='post_count' style='color:".get_option('symposium_text_color').";'>".$reply_views."</div>";
								if ($reply_views != 1) { 
									$views_html .= "<div style='color:".get_option('symposium_text_color').";' class='post_count_label'>".__("VIEWS", "wp-symposium")."</div>";
								} else {
									$views_html .= "<div style='color:".get_option('symposium_text_color').";' class='post_count_label'>".__("VIEW", "wp-symposium")."</div>";						
								}
								$topic_template = str_replace("[views]", $views_html, $topic_template);	
							} else {
								$topic_template = str_replace("[views]", "", $topic_template);	
							}
					
							// Replies
							if (!get_option('symposium_wps_lite')) {
								$replies_html = "<div class='post_count' style='color:".get_option('symposium_text_color').";'>".$replies."</div>";
								$replies_html .= "<div style='color:".get_option('symposium_text_color').";' class='post_count_label'>";
								if ($replies != 1) {
									$replies_html .= __("REPLIES", "wp-symposium");
								} else {
									$replies_html .= __("REPLY", "wp-symposium");
								}
								$replies_html .= "</div>";
								$topic_template = str_replace("[replies]", $replies_html, $topic_template);	
							} else {
								$topic_template = str_replace("[replies]", "", $topic_template);	
							}
		
							// Topic Title		
							$topic_title_html = "";
							// Delete link if applicable
							if (current_user_can('level_10') || $current_user->ID == $topic->topic_owner) {
								$topic_title_html .= "<div class='topic-delete-icon'>";
								$topic_title_html .= "<a class='floatright delete_topic link_cursor' id='".$topic->tid."'style='width:16px;'><img src='".get_option('symposium_images')."/delete.png' style='width:16px; height:16px;' /></a>";
								$topic_title_html .= "</div>";
							}
				
							if (strpos($favs, "[".$topic->tid."]") === FALSE ) { } else {
								$topic_title_html .= "<img src='".get_option('symposium_images')."/fav-on.png' class='floatleft' style='height:18px; width:18px; margin-right:4px; margin-top:4px' />";						
							}								
				
							$subject = stripslashes(symposium_bbcode_remove($topic->topic_subject));
							$topic_title_html .= '<div class="row_link_div">';
		
								if (is_user_logged_in() && get_option('symposium_forum_stars')) {		
									if ( ($topic->topic_started > $previous_login && $topic->topic_owner != $current_user->ID) || ($child_is_new) ) {
										$topic_title_html .= "<img src='".get_option('symposium_images')."/new.gif' alt='New!' /> ";
									}	
								}
											
								if ($topic->for_info == "on") { $topic_title_html .= '<img src="'.get_option('symposium_images').'/info.png" alt="'.__('Information only', 'wp-symposium').'" /> '; }
								if ($topic->answers > 0) { $topic_title_html .= '<img src="'.get_option('symposium_images').'/tick.png" alt="'.__('Answer accepted', 'wp-symposium').'" /> '; }
								if (get_option('symposium_forum_ajax') == 'on') {
									$topic_title_html .= '<a title="'.$topic->tid.'" href="#cid='.$topic->topic_category.',tid='.$topic->tid.'" class="topic_subject backto row_link">'.stripslashes($subject).'</a>';
								} else {
									if (get_option('symposium_permalink_structure') && $group_id == 0) {
										$perma_cat = symposium_get_forum_category_part_url($topic->topic_category);
										$topic_title_html .= '<a class="backto row_link" href="'.$forum_url.'/'.$perma_cat.$topic->stub.'">'.stripslashes($subject).'</a> ';							
									} else {
										$topic_title_html .= '<a class="backto row_link" href="'.$forum_url.$q."cid=".$topic->topic_category."&show=".$topic->tid.'">'.stripslashes($subject).'</a> ';							
									}
								}
								if ($topic->topic_approved != 'on') { $topic_title_html .= " <em>[".__("pending approval", "wp-symposium")."]</em>"; }
								if (is_user_logged_in()) {
									$is_subscribed = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix."symposium_subs WHERE cid = 0 AND tid = ".$topic->tid." AND uid = ".$current_user->ID));
									if ($is_subscribed > 0 && $forum_all != 'on') { $topic_title_html .= ' <img src="'.get_option('symposium_images').'/orange-tick.gif" alt="Subscribed" />'; } 
								}
								if ($topic->allow_replies != 'on') { $topic_title_html .= ' <img src="'.get_option('symposium_images').'/padlock.gif" alt="Replies locked" />'; } 
								if ($topic->topic_sticky) { $topic_title_html .= ' <img src="'.get_option('symposium_images').'/pin.gif" alt="Sticky Topic" />'; } 
				
							$topic_title_html .= "</div>";
							$post = stripslashes($topic->topic_post);
							$post = str_replace("<br />", " ", $post);
							$post = strip_tags($post);
							$post = symposium_bbcode_remove($post);
							if ( strlen($post) > $snippet_length ) { $post = substr($post, 0, $snippet_length)."..."; }
							$topic_title_html .= "<span class='row_topic_text'>".$post."</span>";
		
							$topic_template = str_replace("[topic_title]", $topic_title_html, $topic_template);	
					
						// Add template to HTML				
						$html .= $topic_template;								
		
						$html .= "</div>";
								
						// Separator
						$html .= "<div class='sep'></div>";		
				
					}
		
					if ($num_topics >= $limit_count) {
						$html .= "<a href='javascript:void(0)' id='showmore_forum' title='".($limit_from+$limit_count).",".$cat_id."'>".__("more...", "wp-symposium")."</a>";
					}
		
				if ($limit_from == 0) {
					$html .= "</div>"; // End of table
				}
				
			}
		}

		if ( get_option('symposium_forum_ajax') ) {
			$cat_title = $wpdb->get_var($wpdb->prepare("SELECT title FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d", $cat_id));
			if ($cat_title) {
				$html = $cat_title.' | '.html_entity_decode(get_bloginfo('name'), ENT_QUOTES).'[|]'.$html;
			} else {
				$html = __('Forum', 'wp-symposium').' | '.html_entity_decode(get_bloginfo('name'), ENT_QUOTES).'[|]'.$html;
			}
		}
		
	} else {
		
		$html = "DONTSHOW";
		
	}

	// Filter for header
	$html = apply_filters ( 'symposium_forum_categories_header_filter', $html, $cat_id );

	return $html;

}

function symposium_get_topic_count($cat) {
	
	global $wpdb, $current_user;

	$topic_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix."symposium_topics WHERE (topic_approved = 'on' OR topic_owner = %d) AND topic_parent = 0 AND topic_category = %d", $current_user->ID, $cat));

	$category_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix."symposium_cats WHERE cat_parent = %d", $cat));

	return $topic_count+$category_count;	

}

function show_sharing_icons($cat_id, $topic_id, $sharing, $group_id) {
	
	global $wpdb;
	
	$html = "<div id='share_link' style='text-align:right;width:180px;'>";

		// Sharing icons
		// Work out link to this page, dealing with permalinks or not
		// Get forum URL worked out
		$forum_url = symposium_get_url('forum');
		if (strpos($forum_url, '?') !== FALSE) {
			$q = "&";
		} else {
			$q = "?";
		}
		$pageURL = $forum_url.$q."cid=".$cat_id;
		if ($topic_id > 0) {
			$pageURL .= "%26show=".$topic_id;
			$info = $wpdb->get_row($wpdb->prepare("SELECT topic_subject, stub FROM ".$wpdb->prefix."symposium_topics WHERE tid = %d", $topic_id));
			$title = symposium_strip_smilies($info->topic_subject);
			$stub = $info->stub;
		} else {
			$title = '';
			$stub = '';
		}
		
		// Get group URL worked out
		if ($group_id > 0) {
			$forum_url = symposium_get_url('group');
			if (strpos($forum_url, '?') !== FALSE) {
				$q = "%26gid=".$group_id."%26";
			} else {
				$q = "?gid=".$group_id."%26";
			}
			$pageURL = $forum_url.$q;
			if ($topic_id > 0) {
				$pageURL .= "cid=0%26show=".$topic_id;
			}
		}
		
		$plugin = WP_CONTENT_URL.'/plugins/wp-symposium/';

		// Permalink
		if (!(strpos($sharing, "pl") === FALSE)) {
			if (get_option('symposium_permalink_structure') && $group_id == 0) {
				$perma_cat = symposium_get_forum_category_part_url($cat_id);
				$pageURL = symposium_get_url('forum').'/'.$perma_cat.$stub;
			} else {
				$pageURL = str_replace("%26", "&", $pageURL);	
			}
			$html .= "<img class='symposium_social_share' id='share_permalink' title='".$pageURL."' src='".get_option('symposium_images')."/link-icon.gif' style='cursor:pointer; margin-left:3px; height:22px; width:22px' alt='Permalink icon' />";
		}
		// Email
		if (!(strpos($sharing, "em") === FALSE)) {
			$html .= "<a class='symposium_social_share' id='share_email' title='".__('Share via email', 'wp-symposium')."' href='mailto:%20?subject=".str_replace(" ", "%20", $title)."&body=".$pageURL."'>";
			$html .= "<img src='".get_option('symposium_images')."/email-icon.gif' style='margin-left:3px; height:22px; width:22px;' alt='Email icon' /></a>";
		}
		// Facebook
		if (!(strpos($sharing, "fb") === FALSE)) {
			$pageURL = urlencode($pageURL);
			$html .= "<a class='symposium_social_share' id='share_facebook' target='_blank' title='".__('Share on Facebook', 'wp-symposium')."' href='http://www.facebook.com/share.php?u=".$pageURL."&t=".$title."'>";
			$html .= "<img src='".get_option('symposium_images')."/facebook-icon.gif' style='margin-left:3px; height:22px; width:22px' alt='Facebook icon' /></a>";
		}
		// Twitter
		if (!(strpos($sharing, "tw") === FALSE)) {
			$html .= "<a class='symposium_social_share' id='share_twitter' target='_blank' title='".__('Share on Twitter', 'wp-symposium')."' href='http://twitter.com/home?status=".$pageURL."'>";
			$html .= "<img src='".get_option('symposium_images')."/twitter-icon.gif' style='margin-left:3px; height:22px; width:22px' alt='Twitter icon' /></a>";
		}
		// Bebo
		if (!(strpos($sharing, "be") === FALSE)) {
			$html .= "<a class='symposium_social_share' id='share_bebo' target='_blank' title='".__('Share on Bebo', 'wp-symposium')."' href='http://www.bebo.com/c/share?Url=".$pageURL."&Title=".$title."'>";
			$html .= "<img src='".get_option('symposium_images')."/bebo-icon.gif' style='margin-left:3px; height:22px; width:22px' alt='Bebo icon' /></a>";
		}
		// LinkedIn
		if (!(strpos($sharing, "li") === FALSE)) {
			$html .= "<a class='symposium_social_share' id='share_linkedin' target='_blank' title='".__('Share on LinkedIn', 'wp-symposium')."' href='http://www.linkedin.com/shareArticle?mini=true&url=".$pageURL."&title=".$title."'>";
			$html .= "<img src='".get_option('symposium_images')."/linkedin-icon.gif' style='margin-left:3px; height:22px; width:22px' alt='LinkedIn icon' /></a>";
		}
		// MySpace
		if (!(strpos($sharing, "ms") === FALSE)) {
			$html .= "<a class='symposium_social_share' id='share_myspace' target='_blank' title='".__('Share on MySpace', 'wp-symposium')."' href='http://www.myspace.com/Modules/PostTo/Pages/?u=".$pageURL."&t=".$title."'>";
			$html .= "<img src='".get_option('symposium_images')."/myspace-icon.gif' style='margin-left:3px; height:22px; width:22px' alt='MySpace icon' /></a>";
		}

	$html .= "</div>";	
	
	return $html;
}

function forum_rank($uid) {
	
	global $wpdb;	
	
	$max_sql = "SELECT topic_owner, COUNT(*) AS cnt FROM ".$wpdb->prefix."symposium_topics GROUP BY topic_owner ORDER BY cnt DESC LIMIT 0,1";
	$max = $wpdb->get_row($max_sql);

	$my_sql = "SELECT COUNT(*) AS cnt FROM ".$wpdb->prefix."symposium_topics WHERE topic_owner = ".$uid;
	$my_count = $wpdb->get_var($my_sql);
	
	$forum_ranks = get_option('symposium_forum_ranks');

	$ranks = explode(';', $forum_ranks);
	$my_rank = '';
	
	if ($my_count == $max->cnt) { 
		$my_rank = $ranks[1];
	} else {
		for ( $l = 10; $l >= 1; $l=$l-1) {
			if ($my_count >= $ranks[($l*2)+2]) {
				$my_rank = $ranks[($l*2)+1];
			}
		}
	}
	
	return $my_rank;

}

function symposium_clean_html($dirty) {
	$remove_php_regex = '/(<\?{1}[pP\s]{1}.+\?>)/';
 	$remove_replacement = '';  
 
 	// Get rid of PHP tags
   	$dirty = preg_replace($remove_php_regex, $remove_replacement, $dirty);	

	// No filter for allows HTML tags
	$allowedtags = array(
		'a' => array('href' => array(), 'title' => array(), 'target' => array()),
		'abbr' => array('title' => array()), 'acronym' => array('title' => array()),
		'blockquote' => array(), 
		'br' => array(), 
		'caption' => array(), 
		'code' => array(), 
		'pre' => array(), 
		'em' => array(), 
		'strong' => array(),
		'div' => array(), 
		'p' => array('style' => array()), 
		'ul' => array(), 
		'ol' => array(), 
		'li' => array(),
		'h1' => array(), 'h2' => array(), 'h3' => array(), 'h4' => array(), 'h5' => array(), 'h6' => array(),
		'img' => array('style' => array(), 'src' => array(), 'class' => array(), 'alt' => array(),'height' => array(),'width' => array()),
		'sup' => array(),
		'span' => array('style' => array()), 
		's' => array(), 
		'strike' => array(),
		'table' => array('style' => array(),'border' => array(),'cellspacing' => array(),'cellpadding' => array()), 
		'tbody' => array(),
		'tr' => array(),
		'td' => array('style' => array(),'valign' => array(),'align' => array(),'rowspan' => array(),'colspan' => array()), 
		'sup' => array(),
		'end' => array()
	);
   	
	return wp_kses($dirty, $allowedtags );
}

function symposium_bbcode_remove($text_to_search) {
 $pattern = '|[[\/\!]*?[^\[\]]*?]|si';
 $replace = '';
 return preg_replace($pattern, $replace, $text_to_search);

}

function symposium_bbcode_replace($text_to_search) {

	$text_to_search = str_replace('http://youtu.be/', 'http://www.youtube.com/watch?v=', $text_to_search);
	
	$search = array(
	        '@\[(?i)b\](.*?)\[/(?i)b\]@si',
	        '@\[(?i)i\](.*?)\[/(?i)i\]@si',
	        '@\[(?i)s\](.*?)\[/(?i)s\]@si',
	        '@\[(?i)u\](.*?)\[/(?i)u\]@si',
	        '@\[(?i)img\](.*?)\[/(?i)img\]@si',
	        '@\[(?i)url\](.*?)\[/(?i)url\]@si',
	        '@\[(?i)url=(.*?)\](.*?)\[/(?i)url\]@si',
	        '@\[(?i)code\](.*?)\[/(?i)code\]@si',
			'@\[youtube\].*?(?:v=)?([^?&[]+)(&[^[]*)?\[/youtube\]@is'
	);
	$replace = array(
	        '<b>\\1</b>',
	        '<i>\\1</i>',
	        '<s>\\1</s>',
	        '<u>\\1</u>',
	        '<img src="\\1">',
	        '<a href="\\1">\\1</a>',
	        '<a href="\\1">\\2</a>',
	        '<code>\\1</code>',
	        '<iframe title="YouTube video player" width="475" height="290" src="http://www.youtube.com/embed/\\1" frameborder="0" allowfullscreen></iframe>'
	);

	$r = preg_replace($search, $replace, $text_to_search);
   
   	return $r;

}

function show_profile_menu($uid1, $uid2) {
	
	global $wpdb, $current_user;

		$share = get_symposium_meta($uid1, 'share');		
		$privacy = get_symposium_meta($uid1, 'wall_share');		
		$is_friend = symposium_friend_of($uid1, $current_user->ID);
		if ( $wpdb->get_results( "SELECT meta_key FROM ".$wpdb->base_prefix."usermeta WHERE user_ID = '".$uid1."' AND meta_key LIKE '%symposium_extended_%' AND meta_value != ''" ) > 0 ) { $extended = "on"; } else { $extended = ""; }
		
		$html = '';

		if ($uid1 > 0) {

			// Filter for additional menu items (see www.wpswiki.com for help)
			$html .= apply_filters ( 'symposium_profile_menu_filter', $html,$uid1, $uid2, $privacy, $is_friend, $extended, $share );
			
			if ($uid1 == $uid2 || symposium_get_current_userlevel() == 5) {
				if (get_option('symposium_profile_avatars') == 'on') {
					$html .= '<div id="menu_avatar" class="symposium_profile_menu">'.__('Profile Photo', 'wp-symposium').'</div>';
				}
				$html .= '<div id="menu_personal" class="symposium_profile_menu">'.__('Profile Details', 'wp-symposium').'</div>';
				$html .= '<div id="menu_settings" class="symposium_profile_menu">'.__('Community Settings', 'wp-symposium').'</div>';

			}
			
			// Add mail for admin's so they can read members's mail
			if (symposium_get_current_userlevel() == 5 && $uid1 != $current_user->ID && function_exists('symposium_mail')) {
				$mailpage = symposium_get_url('mail');
				$q = symposium_string_query($mailpage);
				$html .= '<a href="'.$mailpage.$q.'uid='.$uid1.'" class="symposium_profile_menu">'.__('Mail Admin', 'wp-symposium').'</a>';
			}
			
		}
		
		// Filter for additional text/HTML after menu items
		$html .= apply_filters ( 'symposium_profile_menu_end_filter', $html,$uid1, $uid2, $privacy, $is_friend, $extended, $share );

	
	return $html;

}

function symposium_make_url($text) {

    return make_clickable($text);

}


function symposium_safe_param($param) {
	$return = true;
	
	if (is_numeric($param) == FALSE) { $return = false; }
	if (strpos($param, ' ') != FALSE) { $return = false; }
	if (strpos($param, '%20') != FALSE) { $return = false; }
	if (strpos($param, ';') != FALSE) { $return = false; }
	if (strpos($param, '<script>') != FALSE) { $return = false; }
	
	return $return;
}

function symposium_pagination($total, $current, $url) {
	
	$r = '';

	$r .= '<div class="tablenav"><div class="tablenav-pages">';
	for ($i = 0; $i < $total; $i++) {
		if ($i == $current) {
            $r .= "<b>".($i+1)."</b> ";
        } else {
        	if ( ($i == 0) || ($i == $total-1) || ($i+1 == $current) || ($i+1 == $current+2) ) {
	            $r .= " <a href='".$url.($i+1)."'>".($i+1)."</a> ";
        	} else {
        		$r .= "...";
        	}
        }
	}
	$r .= '</div></div>';
	
	while ( strpos($r, "....") > 0) {
		$r = str_replace("....", "...", $r);
	}
	
	if ($i == 1) {
		return '';
	} else {
		return $r;
	}
}

function symposium_pending_friendship($uid) {
   	global $wpdb, $current_user;
	wp_get_current_user();
	
	$sql = "SELECT * FROM ".$wpdb->base_prefix."symposium_friends WHERE (friend_accepted != 'on') AND (friend_from = %d AND friend_to = %d OR friend_to = %d AND friend_from = %d)";
	
	if ( $wpdb->get_var($wpdb->prepare($sql, $uid, $current_user->ID, $uid, $current_user->ID)) ) {
		return true;
	} else {
		return false;
	}

}

function symposium_friend_of($from, $to) {
   	global $wpdb, $current_user;
	wp_get_current_user();
	
	if ( $wpdb->get_var($wpdb->prepare("SELECT * FROM ".$wpdb->base_prefix."symposium_friends WHERE (friend_accepted = 'on') AND (friend_from = ".$from." AND friend_to = ".$to.")")) ) {
		return true;
	} else {
		return false;
	}

}

function symposium_is_following($uid, $following) {
   	global $wpdb, $current_user;
	wp_get_current_user();
	
	if ( $wpdb->get_var($wpdb->prepare("SELECT * FROM ".$wpdb->base_prefix."symposium_following WHERE uid = ".$uid." AND following = ".$following)) ) {
		return true;
	} else {
		return false;
	}

}
	

function symposium_get_current_userlevel() {

   	global $wpdb, $current_user;
	wp_get_current_user();

	// Work out user level
	$user_level = 0; // Guest
	if (is_user_logged_in()) { $user_level = 1; } // Subscriber
	if (current_user_can('edit_posts')) { $user_level = 2; } // Contributor
	if (current_user_can('edit_published_posts')) { $user_level = 3; } // Author
	if (current_user_can('moderate_comments')) { $user_level = 4; } // Editor
	if (current_user_can('activate_plugins')) { $user_level = 5; } // Administrator
	
	return $user_level;

}

function symposium_get_url($plugin) {
	
	global $wpdb;
	$return = false;
	if ($plugin == 'mail' && function_exists('symposium_mail')) {
		$return = get_option('symposium_mail_url');
	}
	if ($plugin == 'forum' && function_exists('symposium_forum')) {
		$return = get_option('symposium_forum_url');
	}
	if ($plugin == 'profile') {
		$return = get_option('symposium_profile_url');
	}
	if ($plugin == 'avatar') {
		$return = WPS_AVATAR_URL;
	}
	if ($plugin == 'members' && function_exists('symposium_members')) {
		$return = get_option('symposium_members_url');
	}
	if ($plugin == 'groups' && function_exists('symposium_group')) {
		$return = get_option('symposium_groups_url');
	}
	if ($plugin == 'group' && function_exists('symposium_group')) {
		$return = get_option('symposium_group_url');
	}
	if ($plugin == 'gallery' && function_exists('symposium_gallery')) {
		$return = get_option('symposium_gallery_url');
	}
	if ($return == false) {
		$return = "INVALID PLUGIN URL REQUESTED (".$plugin.")";
	}
	if ($return[strlen($return)-1] == '/') { $return = substr($return,0,-1); }

	return get_bloginfo('url').$return;
	
}

function page_has_wps_shortcode(array $shortcodes) {
	global $wpdb;
	$found = false;
	foreach ($shortcodes AS $shortcode) {
		$sql = "SELECT ID FROM ".$wpdb->prefix."posts WHERE post_type = 'page' AND post_status = 'publish' AND post_content like '[".$shortcode."]'";
		$ID = $wpdb->get_var($wpdb->prepare($sql, $shortcode));
		echo $wpdb->last_query;
		if ($ID) { $found = true; }
	}
	return $found;
}

function symposium_alter_table($table, $action, $field, $format, $null, $default) {
	
	if ($action == "MODIFY") { $action = "MODIFY COLUMN"; }
	if ($default != "") { $default = "DEFAULT ".$default; }

	global $wpdb;	
	
	$success = false;

	$check = '';
	$res = mysql_query("DESCRIBE ".$wpdb->prefix."symposium_".$table);
	while($row = mysql_fetch_array($res)) {	
		if ($row['Field'] == $field) { 
			$check = 'exists';
		}
	}
		
	if ($action == "ADD" && $check == '') {
		if ($format != 'text') {
		  	$wpdb->query("ALTER TABLE ".$wpdb->prefix."symposium_".$table." ".$action." ".$field." ".$format." ".$null." ".$default);
		} else {
		  	$wpdb->query("ALTER TABLE ".$wpdb->prefix."symposium_".$table." ".$action." ".$field." ".$format);
		}
	}

	if ($action == "MODIFY COLUMN") {
		if ($format != 'text') {
			$sql = "ALTER TABLE ".$wpdb->prefix."symposium_".$table." ".$action." ".$field." ".$format." ".$null." ".$default;
		} else {
			$sql = "ALTER TABLE ".$wpdb->prefix."symposium_".$table." ".$action." ".$field." ".$format;
		}
	  	$wpdb->query($sql);
	}
	
	if ($action == "DROP") {
		$sql = "ALTER TABLE ".$wpdb->prefix."symposium_".$table." DROP ".$field;
	  	$wpdb->query($sql);
	}
	
	return $success;

}

// Updates user meta, and if not yet created, will create it
function update_symposium_meta($uid, $meta, $value) {

	global $wpdb;

	if ($meta != 'profile_avatar') {

		// strip quotes from older version of WPS
		if (is_string($value) && substr($value, 0, 1) == "'") {
			$value = substr($value, 1, strlen($value)-2);
		}
		
		// create if not yet there
		if ($wpdb->get_var($wpdb->prepare("SELECT meta_key FROM ".$wpdb->base_prefix."usermeta WHERE meta_key = 'symposium_extended_city' AND user_id = ".$uid)) != 'symposium_extended_city') create_wps_usermeta($uid);

		// check if linked
		$slug = str_replace('extended_', '', $meta);
		$link = $wpdb->get_row($wpdb->prepare("SELECT extended_type, wp_usermeta FROM ".$wpdb->base_prefix."symposium_extended WHERE extended_slug = %s", $slug));
		if ($link->wp_usermeta) {
			if ($link->extended_type == 'Checkbox') $value = ($value=='on') ? 'true' : 'false';
			update_user_meta($uid, $link->wp_usermeta, $value);
		} else {
			update_user_meta($uid, 'symposium_'.$meta, $value);
		}
		return true;
		
	} else {
			
		if ($value == '') { $value = "''"; }
		
		// check if exists, and create record if not
		// only update is to profile_avatar so can check this directly
		if (!$wpdb->get_var($wpdb->prepare("SELECT profile_avatar FROM ".$wpdb->base_prefix."symposium_usermeta WHERE uid = ".$uid))) {
			$wpdb->insert($wpdb->base_prefix.'symposium_usermeta', array( 'uid' => $uid ) );
		}
				
		// now update value
		if (is_string($value)) {
			$type = '%s';
			if (substr($value, 0, 1) == "'") {
				$value = substr($value, 1, strlen($value)-2);
			}
		} else {
			$type = '%d';
		}
		
		$r = ($wpdb->update( $wpdb->base_prefix.'symposium_usermeta', 
			array( $meta => $value ), 
			array( 'uid' => $uid ), 
			array( $type ), 
			array( '%d' )
			));
			
	  	return $r;
	  	
	}
}

// Get user meta data, and create if not yet available
function get_symposium_meta($uid, $meta, $legacy=false) {

	global $wpdb;

	if (!$legacy && $meta != 'profile_avatar') {

		// create if not yet there
		if ($wpdb->get_var($wpdb->prepare("SELECT meta_key FROM ".$wpdb->base_prefix."usermeta WHERE meta_key = 'symposium_extended_city' AND user_id = ".$uid)) != 'symposium_extended_city') create_wps_usermeta($uid);

		if ($meta == 'city') $meta = 'extended_city';
		if ($meta == 'country') $meta = 'extended_country';

		// check if linked
		$slug = str_replace('extended_', '', $meta);
		$link = $wpdb->get_row($wpdb->prepare("SELECT extended_type, wp_usermeta FROM ".$wpdb->base_prefix."symposium_extended WHERE extended_slug = %s", $slug));
		if ($link->wp_usermeta) {
			$value = get_user_meta($uid, $link->wp_usermeta, true);
			if ($link->extended_type == 'Checkbox') $value = ($value=='true') ? 'on' : '';
		} else {
			$value = get_user_meta($uid, 'symposium_'.$meta, true);
		}
		
		return $value;

	} else {
		
		// create if not yet there
		if ($wpdb->get_var($wpdb->prepare("SELECT meta_key FROM ".$wpdb->base_prefix."usermeta WHERE meta_key = 'symposium_extended_city' AND user_id = ".$uid)) != 'symposium_extended_city') create_wps_usermeta($uid);

		if ($meta == 'extended_city') $meta = 'extended_city';
		if ($meta == 'extended_country') $meta = 'country';
		if ($value = $wpdb->get_var($wpdb->prepare("SELECT ".$meta." FROM ".$wpdb->base_prefix.'symposium_usermeta'." WHERE uid = ".$uid)) ) {
			return $value;
		} else {
			return false; 	
		}
		
	}
}


function create_wps_usermeta($uid) {

	if ($uid > 0) {
		
		global $wpdb;

		// insert initial friend(s) if set
		if (get_option('symposium_all_friends')) {
			// Loop through all users, adding them as friends to each other
			$sql = "SELECT ID FROM ".$wpdb->base_prefix."users WHERE ID != %d";
			$users = $wpdb->get_results($wpdb->prepare($sql, $uid));			
			foreach ($users as $user) {
				$wpdb->query( $wpdb->prepare( "
					INSERT INTO ".$wpdb->prefix."symposium_friends
					( 	friend_from, 
						friend_to,
						friend_accepted,
						friend_message,
						friend_timestamp
					)
					VALUES ( %d, %d, %s, %s, %s )", 
				    array(
				    	$uid,
				    	$user->ID,
				    	'on', 
				    	'',
				    	date("Y-m-d H:i:s")
				    	) 
				    ) );
			}			
		} else {
			$initial_friend = get_option('symposium_initial_friend');
			if ( ($initial_friend != '') && ($initial_friend != '0') ) {
	
				$list = explode(',', $initial_friend);
	
				foreach ($list as $new_friend) {
	
				   if ($new_friend != $uid) {
					$wpdb->query( $wpdb->prepare( "
					INSERT INTO ".$wpdb->base_prefix."symposium_friends
						( 	friend_from, 
							friend_to,
							friend_timestamp,
							friend_message
						)
					VALUES ( %d, %d, %s, %s )", 
					        array(
				        	$new_friend, 
				        	$uid,
			        		date("Y-m-d H:i:s"),
				        	''
			        		) 
				        ) );
				   }
				}
			}
		}
		
		// add to initial groups if set
		$initial_groups = get_option('symposium_initial_groups');
		if ( ($initial_groups != '') && ($initial_groups != '0') ) {

			$list = explode(',', $initial_groups);

			foreach ($list as $new_group) {

				// Add membership
				$wpdb->query( $wpdb->prepare( "
					INSERT INTO ".$wpdb->prefix."symposium_group_members
					( 	group_id, 
						member_id,
						admin,
						valid,
						joined
					)
					VALUES ( %d, %d, %s, %s, %s )", 
			        array(
			        	$new_group, 
			        	$uid, 
			        	'',
			        	'on',
			        	date("Y-m-d H:i:s")
			        	) 
			        ) );
			        
			}
		}

		// add default forum categories subscriptions
		$initial_forums = get_option('symposium_wps_default_forum');
		if ( ($initial_forums != '') && ($initial_forums != '0') ) {

			$list = explode(',', $initial_forums);

			foreach ($list as $new_sub) {

				// Add subscription
				$wpdb->query( $wpdb->prepare( "
					INSERT INTO ".$wpdb->prefix."symposium_subs
					( 	uid, 
						tid,
						cid
					)
					VALUES ( %d, %d, %d )", 
			        array(
			        	$uid, 
			        	0, 
			        	$new_sub
			        	) 
			        ) );
			        
			}
		}
		
		// insert user meta
		update_user_meta($uid, 'symposium_forum_digest', 'on');
		update_user_meta($uid, 'symposium_notify_new_messages', 'on');
		update_user_meta($uid, 'symposium_notify_new_wall', 'on');
		update_user_meta($uid, 'symposium_extended_city', null);
		update_user_meta($uid, 'symposium_extended_country', null);
		update_user_meta($uid, 'symposium_dob_day', null);
		update_user_meta($uid, 'symposium_dob_month', null);
		update_user_meta($uid, 'symposium_dob_year', null);
		update_user_meta($uid, 'symposium_share', 'Friends only');
		update_user_meta($uid, 'symposium_last_activity', null);
		update_user_meta($uid, 'symposium_status', '');
		update_user_meta($uid, 'symposium_visible', 'on');
		update_user_meta($uid, 'symposium_wall_share', 'Friends only');
		update_user_meta($uid, 'symposium_widget_voted', '');
		update_user_meta($uid, 'symposium_profile_photo', '');
		update_user_meta($uid, 'symposium_forum_favs', null);
		update_user_meta($uid, 'symposium_trusted', '');
		update_user_meta($uid, 'symposium_facebook_id', '');
		update_user_meta($uid, 'symposium_last_login', null);
		update_user_meta($uid, 'symposium_previous_login', null);
		update_user_meta($uid, 'symposium_forum_all', '');
		update_user_meta($uid, 'symposium_signature', '');
		update_user_meta($uid, 'symposium_rss_share', '');
		update_user_meta($uid, 'symposium_plus_lat', 0);
		update_user_meta($uid, 'symposium_plus_long', 0);
		
		// Hook for further action to take place after the creation of a user, such as update metadata with different values...
		do_action('symposium_create_user_hook', $uid);
	}
}


// Display array contents (for debugging only)
function symposium_displayArrayContentFunction($arrayname,$tab="&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp",$indent=0) {
 $curtab ="";
 $returnvalues = "";
 while(list($key, $value) = each($arrayname)) {
  for($i=0; $i<$indent; $i++) {
   $curtab .= $tab;
   }
  if (is_array($value)) {
   $returnvalues .= "$curtab$key : Array: <br />$curtab{<br />\n";
   $returnvalues .= symposium_displayArrayContentFunction($value,$tab,$indent+1)."$curtab}<br />\n";
   }
  else $returnvalues .= "$curtab$key => $value<br />\n";
  $curtab = NULL;
  }
 return $returnvalues;
}

// Link to profile if plugin activated
function symposium_profile_link($uid) {
	global $wpdb;

	$display_name = $wpdb->get_var($wpdb->prepare("SELECT display_name FROM ".$wpdb->base_prefix."users WHERE ID = ".$uid));
	if (function_exists('symposium_profile')) {

		$profile_url = symposium_get_url('profile');
		$q = symposium_string_query($profile_url);

		$html = '<a href=\''.$profile_url.$q.'uid='.$uid.'\'>'.$display_name.'</a>';
		
		// Using permalinks?
		if (get_option('symposium_permalink_structure')) {			
			$tag = strtolower(str_replace(' ', '', $display_name));
			$p = get_option('symposium_rewrite_members');
			$p = substr($p, 0, strpos($p, '/'));
			$html = '<a href=\''.get_bloginfo('url').'/'.$p.'/'.$tag.'\'>'.$display_name.'</a>';
		}

	} else {
		$html = $display_name;
	}

	return $html;
}

// Work out query extension
function symposium_string_query($p) {
	if (strpos($p, '?') !== FALSE) { 
		$q = "&"; // No Permalink
	} else {
		$q = "?"; // Permalink
	}
	return $q;
}


// How long ago as text
function symposium_time_ago($date,$granularity=1) {
	
	$retval = '';
    $date = strtotime($date);
    $difference = (time() - $date) + 1;
    $periods = array(__('decade', 'wp-symposium') => 315360000,
        'year' => 31536000,
        'month' => 2628000,
        'week' => 604800, 
        'day' => 86400,
        'hour' => 3600,
        'minute' => 60,
        'second' => 1);

	if ($difference > 315360000) {

	    $return = sprintf (__('a while ago', 'wp-symposium'), $retval);
		
	} else {
		
		if ($difference < 1) {
			
		    $return = sprintf (__('just now', 'wp-symposium'), $retval);
		    
		} else {
                                 
		    foreach ($periods as $key => $value) {
		        if ($difference >= $value) {
		            $time = floor($difference/$value);
		            $difference %= $value;
		            $retval .= ($retval ? ' ' : '').$time.' ';
		            $key = (($time > 1) ? $key.'s' : $key);
		            if ($key == 'year') { $key = __('year', 'wp-symposium'); }
		            if ($key == 'years') { $key = __('years', 'wp-symposium'); }
		            if ($key == 'month') { $key = __('month', 'wp-symposium'); }
		            if ($key == 'months') { $key = __('months', 'wp-symposium'); }
		            if ($key == 'week') { $key = __('week', 'wp-symposium'); }
		            if ($key == 'weeks') { $key = __('weeks', 'wp-symposium'); }
		            if ($key == 'day') { $key = __('day', 'wp-symposium'); }
		            if ($key == 'days') { $key = __('days', 'wp-symposium'); }
		            if ($key == 'hour') { $key = __('hour', 'wp-symposium'); }
		            if ($key == 'hours') { $key = __('hours', 'wp-symposium'); }
		            if ($key == 'minute') { $key = __('minute', 'wp-symposium'); }
		            if ($key == 'minutes') { $key = __('minutes', 'wp-symposium'); }
		            if ($key == 'second') { $key = __('second', 'wp-symposium'); }
		            if ($key == 'seconds') { $key = __('seconds', 'wp-symposium'); }
		            $retval .= $key;
		            $granularity--;
		        }
		        if ($granularity == '0') { break; }
		    }

		    $return = sprintf (__('%s ago', 'wp-symposium'), $retval);
		    
		}
    

	}
    return $return;


}


// Send email
function symposium_sendmail($email, $subject, $msg)
{
	global $wpdb;

	$crlf = PHP_EOL;
	
	// get footer
	$footer = stripslashes(get_option('symposium_footer'));

	// get template
	$template = get_option('symposium_template_email');
	$template = str_replace("[]", "", stripslashes($template));

	// Body Filter
	$msg = apply_filters ( 'symposium_email_body_filter', $msg );

	$template =  str_replace('[message]', $msg, $template);
	$template =  str_replace('[footer]', $footer, $template);
	$template =  str_replace('[powered_by_message]', __('Powered by WP Symposium - Social Networking for WordPress', 'wp-symposium'), $template);
	$template =  str_replace('[version]', WPS_VER, $template);

	$template = str_replace(chr(10), "<br />", $template);
	
	if ( strpos($subject, '#TID') ){
		$from_email = trim(get_option('symposium_mailinglist_from'));
		$from_name = html_entity_decode(trim(stripslashes(get_bloginfo('name'))), ENT_QUOTES, 'UTF-8').' '.__('Forum', 'wp-symposium');
	} else {
		$from_email = trim(get_option('symposium_from_email'));
		$from_name = html_entity_decode(trim(stripslashes(get_bloginfo('name'))), ENT_QUOTES, 'UTF-8');
	}
	
	if ($from_email == '') { 
		// $from_email = "noreply@".get_bloginfo('url'); // old version
		preg_match('@^(?:http://)?([^/]+)@i', get_bloginfo('url'), $matches); 
		preg_match('/[^.]+\.[^.]+$/', $matches[1], $matches);
		$from_email = "noreply@" . $matches[0];
	}	
		
	// To send HTML mail, the Content-type header must be set
	$headers = "MIME-Version: 1.0" . $crlf;
	$headers .= "Content-type:text/html;charset=utf-8" . $crlf;
	$headers .= "From: " . $from_name . " <" . $from_email . ">" . $crlf;

	// Header Filter
	$headers = apply_filters ( 'symposium_email_header_filter', $headers );

	if (WPS_DEBUG) echo 'To: '.$email.'<br />From: '.str_replace($crlf, '<br />', $headers).' '.$from_email.'<br />'.stripslashes($subject).'<br />'.$template;

	// finally send mail
	if (wp_mail($email, stripslashes($subject), $template, $headers))
	{
		return true;
	} else {
		return false;
	}

}

// Function to turn a mysql datetime (YYYY-MM-DD HH:MM:SS) into a unix timestamp 

function convert_datetime($str) { 

	if ($str != '' && $str != NULL) {
		list($date, $time) = explode(' ', $str); 
		list($year, $month, $day) = explode('-', $date); 
		list($hour, $minute, $second) = explode(':', $time); 		
		$timestamp = mktime($hour, $minute, $second, $month, $day, $year); 
     } else {
		$timestamp = 999999999;
	 }
    return $timestamp; 
} 

function powered_by_wps() {

	global $wpdb;

	$template = get_option('symposium_template_page_footer');
	$template = str_replace("[]", "", stripslashes($template));
	
	$template =  str_replace('[powered_by_message]', __('Powered by WP Symposium - Social Networking for WordPress', 'wp-symposium'), $template);
	$template =  str_replace('[version]', WPS_VER, $template);

	return $template;
	
}

// Groups

function get_group_avatar($gid, $size) {


	global $wpdb, $blog_id;

	if (get_option('symposium_img_db') == "on") {
	
		$sql = "SELECT group_avatar FROM ".$wpdb->prefix."symposium_groups WHERE gid = %d";
		$group_photo = $wpdb->get_var($wpdb->prepare($sql, $gid));

		if ($group_photo == '' || $group_photo == 'upload_failed') {
			return "<img src='".get_option('symposium_images')."/unknown.jpg' style='height:".$size."px; width:".$size."px;' />";
		} else {
			return "<img src='".WP_CONTENT_URL."/plugins/wp-symposium/uploadify/get_group_avatar.php?gid=".$gid."' style='width:".$size."px; height:".$size."px' />";
		}
		
		return $html;
		
	} else {

		$sql = "SELECT profile_photo FROM ".$wpdb->prefix."symposium_groups WHERE gid = %d";
		$profile_photo = $wpdb->get_var($wpdb->prepare($sql, $gid));

		if ($profile_photo == '' || $profile_photo == 'upload_failed') {
			return "<img src='".get_option('symposium_images')."/unknown.jpg' style='height:".$size."px; width:".$size."px;' />";
		} else {
			if ($blog_id > 1) {
				$img_url = get_option('symposium_img_url')."/".$blog_id."/groups/".$gid."/profile/";	
			} else {
				$img_url = get_option('symposium_img_url')."/groups/".$gid."/profile/";	
			}
			$img_src =  str_replace('//','/',$img_url) . $profile_photo;
			return "<img src='".$img_src."' style='width:".$size."px; height:".$size."px' />";
		}
		
	}
	
	exit;
	
}

function symposium_member_of($gid) {
	
	global $wpdb, $current_user;

	$sql = "SELECT valid FROM ".$wpdb->prefix."symposium_group_members   
	WHERE group_id = %d AND member_id = %d";
	$members = $wpdb->get_results($wpdb->prepare($sql, $gid, $current_user->ID));
	
	if (!$members) {
		return "no";
	} else {
		$member = $members[0];
		if ($member->valid == "on") {
			return "yes";
		} else {
			return "pending";
		}
	}

}

function symposium_group_admin($gid) {
	
	global $wpdb, $current_user;

	$sql = "SELECT admin FROM ".$wpdb->prefix."symposium_group_members   
	WHERE group_id = %d AND member_id = %d";
	$admin = $wpdb->get_var($wpdb->prepare($sql, $gid, $current_user->ID));
	
	if ($admin) {
		if ($admin == "on") {
			return true;
		} else {
			return false;
		}
	} else {
		return false;
	}
	
}

/* Get site URL with protocol */
function siteURL()
{
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $domainName = $_SERVER['HTTP_HOST'];
    return $protocol.$domainName;
}

/* Function to sort multi-dimensional arrays */
/* For sort use asort or arsort              */
function sub_val_sort($a,$subkey,$asc=true) {
	if (count($a)) {
		foreach($a as $k=>$v) {
			$b[$k] = strtolower($v[$subkey]);
		}
		if ($asc) {
			asort($b);
		} else {
			arsort($b);
		}
		foreach($b as $key=>$val) {
			$c[] = $a[$key];
		}
		return $c;
	} else {
		return $a;
	}
}

// Add index to table
function symposium_add_index($table_name, $key_name, $parameter = "") {
	global $wpdb;
	if(!$wpdb->get_results("SHOW INDEX FROM ".$table_name." WHERE Key_name = '".$key_name."_index'")) {
		$wpdb->query("CREATE ".$parameter." INDEX ".$key_name."_index ON ".$table_name."(".$key_name.")");
	}
}

function symposium_strpos($haystack, $needles=array(), $offset=0) {

	$chr = array();
	foreach($needles as $needle) {
		$res = strpos($haystack, $needle, $offset);
		if ($res !== false) $chr[$needle] = $res;
	}
	if (empty($chr)) return false;
	return min($chr);
}

function symposium_profile_body($uid1, $uid2, $post, $version, $limit_from, $exclude_info_box=true) {
	
	global $wpdb, $current_user;

	$limit_count = 10; // How many new items should be shown

	$plugin = WP_PLUGIN_URL.'/wp-symposium';
	
//	if ($uid1 > 0) {
		
		if (get_option('symposium_use_styles') == "on") {
			$bg_color_2 = 'background-color: '.get_option('symposium_bg_color_2');
		} else {
			$bg_color_2 = '';
		}
		$privacy = ($uid1 > 0) ? get_symposium_meta($uid1, 'wall_share') : 'public';	
		
		$html = "";
			
		if (is_user_logged_in() || $privacy == 'public') {	
		
			$is_friend = ($uid1 > 0) ? symposium_friend_of($uid1, $current_user->ID) : false;
	
			if ( ($uid1 == $uid2) || (is_user_logged_in() && strtolower($privacy) == 'everyone') || (strtolower($privacy) == 'public') || (strtolower($privacy) == 'friends only' && $is_friend) || symposium_get_current_userlevel() == 5) {
			
				// Optional panel
				if ($exclude_info_box && get_option('symposium_show_wall_extras') == "on" && $limit_from == 0 && version != 'stream_activity') {
						
						$html .= "<div id='profile_right_column'>";
	
						// Extended	
						$extended = get_symposium_meta($uid1, 'extended');
						$fields = explode('[|]', $extended);
						$has_extended_fields = false;
						if ($fields) {
							$ext_rows = array();
							foreach ($fields as $field) {
								$split = explode('[]', $field);
								if ( ($split[0] != '') && ($split[1] != '') ) {
								
									$extension = $wpdb->get_row($wpdb->prepare("SELECT extended_name,extended_order FROM ".$wpdb->prefix."symposium_extended WHERE eid = ".$split[0]));
									
									$ext = array (	'name'=>$extension->extended_name,
													'value'=>wpautop(symposium_make_url($split[1])),
													'order'=>$extension->extended_order );
									array_push($ext_rows, $ext);
									
									$has_info = true;
									$has_extended_fields = true;
								}
							}
							$ext_rows = sub_val_sort($ext_rows,'order');
							foreach ($ext_rows as $row) {
								$html .= "<div style='margin-bottom:0px;overflow: auto;'>";
								$html .= "<div style='font-weight:bold;'>".stripslashes($row['name'])."</div>";
								$html .= "<div>".wpautop(symposium_make_url(stripslashes($row['value'])))."</div>";
								$html .= "</div>";
							}
						}
						
															
						// Friends
						$has_friends = false;
						$html .= "<div class='profile_panel_friends_div'>";
				
							$sql = "SELECT f.*, cast(m.meta_value as datetime) as last_activity FROM ".$wpdb->base_prefix."symposium_friends f LEFT JOIN ".$wpdb->base_prefix."usermeta m ON m.user_id = f.friend_to WHERE f.friend_from = %d AND f.friend_accepted = 'on' AND m.meta_key = 'symposium_last_activity'ORDER BY cast(m.meta_value as datetime) DESC LIMIT 0,6";
							$friends = $wpdb->get_results($wpdb->prepare($sql, $uid1));
							
							if ($friends) {
								
								$inactive = get_option('symposium_online');
								$offline = get_option('symposium_offline');
								
								$html .= '<div class="profile_panel_friends_div_title">'.__('Recently Active Friends', 'wp-symposium').'</div>';
								foreach ($friends as $friend) {
									
									$time_now = time();
									$last_active_minutes = strtotime($friend->last_activity);
									$last_active_minutes = floor(($time_now-$last_active_minutes)/60);
																	
									$html .= "<div class='profile_panel_friends_div_row'>";		
										$html .= "<div class='profile_panel_friends_div_avatar'>";
											$html .= get_avatar($friend->friend_to, 42);
										$html .= "</div>";
										$html .= "<div>";
											$html .= symposium_profile_link($friend->friend_to)."<br />";
											$html .= __('Last active', 'wp-symposium').' '.symposium_time_ago($friend->last_activity).".";
										$html .= "</div>";
				
									$html .= "</div>";
								}
								
								$has_friends = true;
							}
													
						$html .= "</div>";
						
						if (!$has_extended_fields && !$has_friends) {
							$html .= __('Make friends and they will be listed here...', 'wp-symposium');
						}
	
					$html .= "</div>";
				
				}				
					
				// Wall
				
				// Filter for additional buttons
				if ($version == "wall") {
					$html = apply_filters ( 'symposium_profile_wall_header_filter', $html, $uid1, $uid2, $privacy, $is_friend, get_symposium_meta($uid1, 'extended') );
				}
				
				
				/* add activity stream */	
				$html .= symposium_activity_stream($uid1, $version, $limit_from, $limit_count, $post);
		
			} else {
	
				if ($version == "friends_activity") {
					$html .= '<p>'.__("Sorry, this member has chosen not to share their activity.", "wp-symposium");
				}
	
				if ($version == "wall") {
					$html .= '<p>'.__("Sorry, this member has chosen not to share their activity.", "wp-symposium");
				}
				
			}		
			return symposium_buffer($html);
			
		} else {

			return __("Please login to view this member's profile.", "wp-symposium");
			
		}
		
//	} else {
//		return '';		
//	}

}

function symposium_activity_stream($uid1='', $version='wall', $limit_from=0, $limit_count=10, $post='', $show_add_comment=true) {
	
	// version = stream_activity, friends_activity, all_activity
	// uid1 = the user's page (which we are looking at)
	// uid2 = the current user
	// $limit_from (starting post)
	// $limit_count (how many to show)
	// $post (individual activity post ID if applicable)
	
	global $wpdb,$current_user;
	if ($uid1 == '') $uid1 = $current_user->ID;
	$uid2 = $current_user->ID;

	// Get privacy level for this member's activity

	$privacy = get_symposium_meta($uid1, 'wall_share');
	
	$html = "";

	
	if (is_user_logged_in() || $privacy == 'public') {	
	
		$is_friend = ($uid1 > 0) ? symposium_friend_of($uid1, $current_user->ID) : false;	
		
		if ( ($uid1 == $uid2) || (is_user_logged_in() && strtolower($privacy) == 'everyone') || (strtolower($privacy) == 'public') || (strtolower($privacy) == 'friends only' && $is_friend) || symposium_get_current_userlevel() == 5) {

			$profile_page = symposium_get_url('profile');
			if ($profile_page[strlen($profile_page)-1] != '/') { $profile_page .= '/'; }
			$q = symposium_string_query($profile_page);	
			
			$html .= "<div id='symposium_wall'>";
		
				if ( 
					( 
					  ( ($version == 'stream_activity') && ($uid2 > 0) ) || 
					  ( 
					    ($limit_from == 0) && 
					    ($post == '') && 
					    ($uid1 != '') && 
					    ( ($uid1 == $uid2) || ($is_friend))
					   ) && (is_user_logged_in())
				     ) 
				   ) {
				       
					// Post Comment Input
					if ($show_add_comment) {

						if ($uid1 == $uid2) {							
							$whatsup = stripslashes(get_option('symposium_status_label'));
							$whatsup = str_replace("'", "`", $whatsup);
						} else {
							$whatsup = __('Write a comment...', 'wp-symposium');
						}

						// Attach an image (HTML5)...
						if (get_option("symposium_activity_images")) {
							$html .= '<div class="uploader" id="symposium_activity_uploader" style="display:none;">';
						        $html .= '<img src="'.get_option('symposium_images').'/add_small.png" /> <a id="pickfiles" href="#">'.__('Attach an image', 'wp-sympsium').'</a>';
							    $html .= '<div id="progressbar" style="border:1px;"></div>';
							$html .= '</div>';
						    $html .= '<div id="symposium_filelist" class="cb">';
						    if (get_option('symposium_activity_images_old')) $html .= __('Install a <a href="http://www.google.com/chrome" target="_blank">modern browser</a> to attach an image', 'wp-symposium');
						    $html .= '</div>';
						}
						
						// The textarea			
						$html .= '<textarea ';
						if (get_option('symposium_elastic')) $html .= 'class="elastic" ';
						$html .= 'id="symposium_comment"  onblur="this.value=(this.value==\'\') ? \''.$whatsup.'\' : this.value;" onfocus="this.value=(this.value==\''.$whatsup.'\') ? \'\' : this.value;">';
						$html .= $whatsup;
						$html .= '</textarea>';

						if (get_option('symposium_show_buttons')) {
							$html .= '<input id="symposium_add_comment" type="submit" style="width:75px;" class="symposium-button" value="'.__('Post', 'wp-symposium').'" /><br />';
						} else {
							$html .= '<br /><br />';
						}

					}
				}
			
				if ($post != '') {
					$post_cid = 'c.cid = '.$post.' AND ';
				} else {
					$post_cid = '';
				}

				// Add groups join if in use
				if (function_exists('symposium_groups')) {
					$groups = "LEFT JOIN ".$wpdb->prefix."symposium_groups g ON c.subject_uid = g.gid";
					$group_field = ", g.content_private";
				} else {
					$groups = "";
					$group_field = ", 'on' as content_private";
				}

				if (WPS_DEBUG) $html .= '$version='.$version.'<br />';
				
				if ($version == "all_activity" || $version == "stream_activity") {
					$sql = "SELECT c.*, u.display_name, u2.display_name AS subject_name" . $group_field . "   
					FROM ".$wpdb->base_prefix."symposium_comments c 
					LEFT JOIN ".$wpdb->base_prefix."users u ON c.author_uid = u.ID 
					LEFT JOIN ".$wpdb->base_prefix."users u2 ON c.subject_uid = u2.ID 
					" . $groups . "
					WHERE ( ".$post_cid." c.comment_parent = 0 
					  ) AND c.type != 'photo' 
					ORDER BY c.comment_timestamp DESC LIMIT %d,%d";					
					$comments = $wpdb->get_results($wpdb->prepare($sql, $limit_from, 30));	
				}
			
				if ($version == "friends_activity") {
					$sql = "SELECT c.*, u.display_name, u2.display_name AS subject_name" . $group_field . " 
					FROM ".$wpdb->base_prefix."symposium_comments c 
					LEFT JOIN ".$wpdb->base_prefix."users u ON c.author_uid = u.ID 
					LEFT JOIN ".$wpdb->base_prefix."users u2 ON c.subject_uid = u2.ID 
					" . $groups . "
					WHERE ( ".$post_cid." (
					      ( (c.subject_uid = %d) OR (c.author_uid = %d)  
					   OR ( c.author_uid IN (SELECT friend_to FROM ".$wpdb->base_prefix."symposium_friends WHERE friend_from = %d)) ) AND c.comment_parent = 0 
				   	   OR ( 
				   	   		%d IN (SELECT author_uid FROM ".$wpdb->base_prefix."symposium_comments WHERE comment_parent = c.cid ) 
							AND ( c.author_uid IN (SELECT friend_to FROM ".$wpdb->base_prefix."symposium_friends WHERE friend_from = %d)) 
				   	   	  ) )
					  ) AND c.type != 'photo' 
					ORDER BY c.comment_timestamp DESC LIMIT %d,%d";	
					$comments = $wpdb->get_results($wpdb->prepare($sql, $uid1, $uid1, $uid1, $uid1, $uid1, $limit_from, $limit_count));	
				}
			
				if ($version == "wall" || !is_user_logged_in()) {
					$sql = "SELECT c.*, u.display_name, u2.display_name AS subject_name" . $group_field . " 
							FROM ".$wpdb->base_prefix."symposium_comments c 
							LEFT JOIN ".$wpdb->base_prefix."users u ON c.author_uid = u.ID 
							LEFT JOIN ".$wpdb->base_prefix."users u2 ON c.subject_uid = u2.ID 
							" . $groups . "
							WHERE (".$post_cid." (
							      ( (c.subject_uid = %d OR c.author_uid = %d) AND c.comment_parent = 0 )
						   	   OR ( %d IN (SELECT author_uid FROM ".$wpdb->base_prefix."symposium_comments WHERE comment_parent = c.cid  ) )
							  ) ) AND c.type != 'photo' 
							ORDER BY c.comment_timestamp DESC LIMIT %d,%d";
					$comments = $wpdb->get_results($wpdb->prepare($sql, $uid1, $uid1, $uid1, $limit_from, $limit_count));	
					
				}

				if (WPS_DEBUG) $html .= $wpdb->last_query.'<br />';
							
				// Build wall
				if ($comments) {
										
					$cnt = 0;
					foreach ($comments as $comment) {
			
						$continue = true;
						if (is_user_logged_in() && $version == "friends_activity" && $comment->author_uid == $uid1 && $comment->subject_uid == $uid1) {
							$sql = "SELECT COUNT(*) FROM ".$wpdb->base_prefix."symposium_comments c 
									WHERE c.comment_parent = %d AND c.is_group != 'on'
									  AND c.author_uid != %d";
							if ($wpdb->get_var($wpdb->prepare($sql, $comment->cid, $uid1)) == 0) $continue = false;
							if (WPS_DEBUG) $html .= $wpdb->last_query.'<br />';
						}

						if ($continue) {

							if (WPS_DEBUG) $html .= '<br>continue<br>';
							$cnt++;
						
							$privacy = get_symposium_meta($comment->author_uid, 'wall_share');
							
							if ( ($comment->subject_uid == $uid1) 
								|| ($comment->author_uid == $uid1) 
								|| (strtolower($privacy) == 'everyone' && $uid2 > 0) 
								|| (strtolower($privacy) == 'public') 
								|| (strtolower($privacy) == 'friends only' && (symposium_friend_of($comment->author_uid, $uid1) || (symposium_friend_of($comment->author_uid, $uid2) && $version == "stream_activity") ) ) 
								) {
									
								// If a group post and user is not the author we need to check privacy of group settings
								if ($comment->is_group == 'on' && $comment->author_uid != $uid2) {
									// If not private group, or a member, then display
									if ($comment->content_private != 'on' || symposium_member_of($comment->subject_uid) == 'yes') {
										$private_group = '';
									} else {
										// Otherwise hide
										$private_group = 'on';
									}
								} else {
									// Not a group post so not applicable
									$private_group = '';
								}
								
								if ($private_group != 'on') {
									
									// Increase shown count
									$shown_count++;
					
									// Check to avoid poke's (as private)								
									if  ( ($comment->type != 'poke') || ($comment->type == 'poke' && ($comment->author_uid == $uid2 || $comment->subject_uid == $uid2 )) ) {	
															
										$html .= "<div class='wall_post_div' id='post_".$comment->cid."'>";
							
											$html .= "<div class='wall_post_avatar'>";
												$html .= get_avatar($comment->author_uid, 64);
											$html .= "</div>";
							
											$html .= '<a href="'.$profile_page.$q.'uid='.$comment->author_uid.'">'.stripslashes($comment->display_name).'</a> ';
											if ($comment->author_uid != $comment->subject_uid && !$comment->is_group) {
												$html .= ' &rarr; <a href="'.$profile_page.$q.'uid='.$comment->subject_uid.'">'.stripslashes($comment->subject_name).'</a> ';
											}
											$html .= symposium_time_ago($comment->comment_timestamp).".";
											$html .= "<div style='width:60px; float:right;height:16px;'>";
											if (get_option('symposium_allow_reports') == 'on') {
												$html .= " <a title='post_".$comment->cid."' href='javascript:void(0);' class='report_post report_post_top symposium_report'><img src='".get_option('symposium_images')."/warning.png' style='width:16px;height:16px' /></a>";
											}
											if (symposium_get_current_userlevel() == 5 || $comment->subject_uid == $uid2 || $comment->author_uid == $uid2) {
												$html .= " <a title='".$comment->cid."' rel='post' href='javascript:void(0);' class='delete_post delete_post_top'><img src='".get_option('symposium_images')."/delete.png' style='width:16px;height:16px' /></a>";
											}
											$html .= '</div>';
											$html .= "<br />";
											
											// Always show reply fields or not?
											$show_class = (get_option('symposium_profile_comments')) ? '' : 'symposium_wall_replies';
											$show_field = (get_option('symposium_profile_comments')) ? '' : 'display:none;';
											
											// $text = the comment
											$text = $comment->comment;
											if ($comment->type == 'gallery' && strpos($text, '[]')) {												
												
												$lib = explode('[]', $text);
												$text = '<div style="width:100%">';
												$text .= $lib[0].'<br />';
												$images = explode('|', $lib[1]);
												$cnt = 0;
												foreach ($images as $info) {
													$info_split = explode(',', $info);
													$image = $info_split[0];
													$iid = $info_split[1];
													$aid = $info_split[2];
													$name = $info_split[3];
													$cnt++;
													if ($cnt == 1) {
														$image = preg_replace('/thumb_/', 'show_', $image, 1);														
									  					$text .= '<a class="symposium_photo_cover_action wps_gallery_album" data-iid="'.$iid.'" href="'.$image.'" rev="'.$cnt.'" rel="symposium_gallery_photos_'.$aid.'" title="'.$name.'">';
														$text .= '<img style="width:100%;" src="'.$image.'" /><br />';
														$text .= '</a>';
													}
													if (sizeof($images) > 2) {
														if ($cnt == 2) {
															$text .= '<div id="wps_comment_plus" style="height:55px;overflow:hidden; width:100%">';
														}
														if ($cnt > 1 && $cnt < sizeof($images)) {
										  					$text .= '<a class="symposium_photo_cover_action wps_gallery_album" data-iid="'.$iid.'" href="'.$image.'" rev="'.$cnt.'" rel="symposium_gallery_photos_'.$aid.'" title="'.$name.'">';
															$text .= '<img style="width:50px;height:50px;margin-right:5px;margin-bottom:5px;float:left;" src="'.$image.'" />';
															$text .= '</a>';
														}
														if ($cnt == sizeof($images)) {
															$text .= '</div>';
														}													
													}
												}
												if ($cnt > 7) {
													$text .= '<div id="wps_gallery_comment_more" style="clear:both;cursor:pointer">';
													$text .= __('more...', 'wp-symposium').'</div>';
												}
												
												$text .= '</div>';

											}
											
											// Check for any associated uploaded images
											$directory = WP_CONTENT_DIR."/wps-content/members/".$current_user->ID.'/activity';
											if (file_exists($directory)) {
												$handler = opendir($directory);
												while ($image = readdir($handler)) {
													$path_parts = pathinfo($image);
													if ($path_parts['filename'] == $comment->cid) {
														$directoryURL = WP_CONTENT_URL."/wps-content/members/".$current_user->ID.'/activity/'.$image;
														$text = '<img src="'.$directoryURL.'" width="100%" /><br />' . $text;
													}
												}
											}											
											
											// Finally show comment...!
											$html .= '<div class="'.$show_class.'" id="'.$comment->cid.'">';
											if ($comment->is_group) {
												$url = symposium_get_url('group');
												$q = symposium_string_query($url);
												$url .= $q.'gid='.$comment->subject_uid.'&post='.$comment->cid;
												$group_name = $wpdb->get_var($wpdb->prepare("SELECT name FROM ".$wpdb->base_prefix."symposium_groups WHERE gid = %d", $comment->subject_uid));
												$html .= __("Group post in", "wp-symposium")." <a href='".$url."'>".$group_name."</a>: ".symposium_make_url(stripslashes($text));
											} else {
												$html .= symposium_make_url(stripslashes($text));
											}
							
											// Replies
											
											$sql = "SELECT c.*, u.display_name FROM ".$wpdb->base_prefix."symposium_comments c 
												LEFT JOIN ".$wpdb->base_prefix."users u ON c.author_uid = u.ID 
												LEFT JOIN ".$wpdb->base_prefix."symposium_comments p ON c.comment_parent = p.cid 
												WHERE c.comment_parent = %d AND c.is_group != 'on' ORDER BY c.cid";
							
											$replies = $wpdb->get_results($wpdb->prepare($sql, $comment->cid));	
							
											$count = 0;
											if ($replies) {
												if (count($replies) > 4) {
													$html .= "<div id='view_all_comments_div'>";
													$html .= "<a title='".$comment->cid."' class='view_all_comments' href='javascript:void(0);'>".__(sprintf("View all %d comments", count($replies)), "wp-symposium")."</a>";
													$html .= "</div>";
												}
												foreach ($replies as $reply) {
													$count++;
													if ($count > count($replies)-4) {
														$reply_style = "";
													} else {
														$reply_style = "display:none; ";
													}
													$html .= "<div id='".$reply->cid."' class='reply_div' style='".$reply_style."'>";
														$html .= "<div class='wall_reply_div'>";
															$html .= "<div class='wall_reply'>";
																$html .= '<a href="'.$profile_page.$q.'uid='.$reply->author_uid.'">'.stripslashes($reply->display_name).'</a> ';
																$html .= symposium_time_ago($reply->comment_timestamp).".";
																$html .= '<div style="width:50px; float:right;">';
																if (get_option('symposium_allow_reports') == 'on') {
																	$html .= " <a title='post_".$reply->cid."' href='javascript:void(0);' style='padding:0px' class='report_post symposium_report reply_warning'><img src='".get_option('symposium_images')."/warning.png' style='width:14px;height:14px' /></a>";
																}
																if (symposium_get_current_userlevel($uid2) == 5 || $reply->subject_uid == $uid2 || $reply->author_uid == $uid2) {
																	$html .= " <a title='".$reply->cid."' rel='reply' href='javascript:void(0);' style='padding:0px' class='delete_post delete_reply'><img src='".get_option('symposium_images')."/delete.png' style='width:14px;height:14px' /></a>";
																}
																$html .= '</div>';
																$html .= "<br />";
																$html .= symposium_make_url(stripslashes($reply->comment));
															$html .= "</div>";
														$html .= "</div>";
														
														$html .= "<div class='wall_reply_avatar'>";
															$html .= get_avatar($reply->author_uid, 40);
														$html .= "</div>";		
													$html .= "</div>";
												}
											} else {
												$html .= "<div class='no_wall_replies'></div>";
											}												
											$html .= "<div style='clear:both;' id='symposium_comment_".$comment->cid."'></div>";
							
											// Reply field
											if ( 
													(is_user_logged_in()) && 
													(
														($uid1 == $uid2) || 
														(
															strtolower($privacy) == 'everyone' || 
															strtolower($privacy) == 'public' || 
															(strtolower($privacy) == 'friends only' && $is_friend) || 
															($version = "stream_activity" && strtolower($privacy) == 'friends only' && symposium_friend_of($comment->author_uid, $current_user->ID))
														)
													)
												) 
											{
												$html .= '<div style="margin-top:5px;'.$show_field.'" id="symposium_reply_div_'.$comment->cid.'" >';

												$html .= '<textarea title="'.$comment->cid.'" class="symposium_reply';
												if (get_option('symposium_elastic')) $html .= ' elastic';
												$html .= '" id="symposium_reply_'.$comment->cid.'" onblur="this.value=(this.value==\'\') ? \''.__('Write a comment...', 'wp-symposium').'\' : this.value;" onfocus="this.value=(this.value==\''.__('Write a comment...', 'wp-symposium').'\') ? \'\' : this.value;">'.__('Write a comment...', 'wp-symposium').'</textarea>';
												
												if (get_option('symposium_show_buttons')) {
													$html .= '<br /><input title="'.$comment->cid.'" type="submit" style="width:75px" class="symposium-button symposium_add_reply" value="'.__('Add', 'wp-symposium').'" />';
												}
												$html .= '<input id="symposium_author_'.$comment->cid.'" type="hidden" value="'.$comment->subject_uid.'" />';
												$html .= '</div>';
											}
					
											$html .= "</div>";
												
										$html .= "</div>";
									
									}
									
								}
								
							} else {
								// Protected by privacy settings
							}	
						} // Comment by member with no replies and looking at friends activity
					}
					
					$id = 'wall';
					if ($version == "all_activity" || $version == "stream_activity") { $id='all'; }
					if ($version == "friends_activity") { $id='activity'; }
			
					if ($post == '' && $cnt > 0) {
						if (is_user_logged_in()) $html .= "<a href='javascript:void(0)' id='".$id."' class='showmore_wall' title='".($limit_from+$cnt+1)."'>".__("more...", "wp-symposium")."</a>";
					} else {
						if ($post == '') {
							$html .= "<br />".__("Nothing to show, sorry.", "wp-symposium");
						}
					}
						
				} else {
					$html .= "<br />".__("Nothing to show, sorry.", "wp-symposium");
				}
			
			$html .= "</div>";

			} else {

			if ($version == "friends_activity") {
				$html .= '<p>'.__("Sorry, this member has chosen not to share their activity.", "wp-symposium");
			}

			if ($version == "wall") {
				$html .= '<p>'.__("Sorry, this member has chosen not to share their activity.", "wp-symposium");
			}
			
		}		
		return symposium_buffer($html);
		
	} else {

		return __("Please login to view this member's profile.", "wp-symposium");
		
	}
		
	return $html;
}

// **********************************************************************************
// FUNCTIONS SHARED BETWEEN AJAX AND NON-AJAX VERSIONS OF WIDGETS
// **********************************************************************************

// New activity/status posts
function do_Symposium_friends_status_Widget($postcount,$preview,$forum) {
	
	global $wpdb, $current_user;
	
	$shown_uid = "";
	$shown_count = 0;	
	$html = '';
	// Work out link to profile page, dealing with permalinks or not
	$profile_url = symposium_get_url('profile');
	$q = symposium_string_query($profile_url);
		
	// Content of widget
	$sql = "SELECT cid, author_uid, comment, comment_timestamp, display_name, type 
	FROM ".$wpdb->base_prefix."symposium_comments c 
	LEFT JOIN ".$wpdb->base_prefix."symposium_friends f ON c.author_uid = f.friend_to
	INNER JOIN ".$wpdb->base_prefix."users u ON c.author_uid = u.ID 
	WHERE f.friend_from = %d
	  AND is_group != 'on' 
	  AND comment_parent = 0 
	  AND author_uid = subject_uid ";
	if ($forum != 'on') { $sql .= "AND type != 'forum' "; }		  
	$sql .= "ORDER BY cid DESC LIMIT 0,250";
	
	
	$posts = $wpdb->get_results($wpdb->prepare($sql, $current_user->ID));
	if (WPS_DEBUG) echo $wpdb->last_query;
			
	if ($posts) {

		$html .= "<div id='symposium_recent_activity'>";
			
			foreach ($posts as $post)
			{
				if ($shown_count < $postcount) {

					if (strpos($shown_uid, $post->author_uid.",") === FALSE) { 

						$share = get_symposium_meta($post->author_uid, 'wall_share');
						$is_friend = symposium_friend_of($post->author_uid, $current_user->ID);

						if ( (is_user_logged_in() && strtolower($share) == 'everyone') || (strtolower($share) == 'public') || (strtolower($share) == 'friends only' && $is_friend) ) {

							$html .= "<div class='symposium_recent_activity_row'>";		
								$html .= "<div class='symposium_recent_activity_row_avatar'>";
									$html .= get_avatar($post->author_uid, 32);
								$html .= "</div>";
								$html .= "<div class='symposium_recent_activity_row_post'>";
									$text = stripslashes($post->comment);
									if ($post->type == 'post') {
										$text = stripslashes($post->comment);
										$text = strip_tags($text);
										if ( strlen($text) > $preview ) { $text = substr($text, 0, $preview)."..."; }
									}
									if ($post->type == 'gallery') {												
										if (strpos($text, '[]')) {
											$lib = explode('[]', $text);
											$text = $lib[0];
										} else {
											if (($x = strpos($text, 'wps_comment_plus')) !== FALSE) {
												$text = substr($text, 0, $x-9);
											}
										}
									}
									if (strpos($text, 'symposium_photo_image')) {
											$text = strip_tags($text,'<img><a></a>');
											$text = str_replace("img", "img style='width:32px;height:32px;'", $text);
											$html .= symposium_time_ago($post->comment_timestamp)." <a href='".$profile_url.$q."uid=".$post->author_uid."&post=".$post->cid."'>".$post->display_name."</a> ".$text."<br>";
									} else {
										$html .= "<a href='".$profile_url.$q."uid=".$post->author_uid."&post=".$post->cid."'>".$post->display_name."</a> ".$text." ".symposium_time_ago($post->comment_timestamp).".<br>";
									}
								$html .= "</div>";
							$html .= "</div>";
						
							$shown_count++;
							$shown_uid .= $post->author_uid.",";							
						}
					}
				} else {
					break;
				}
			}

		$html .= "</div>";

	}
		
	echo $html;
}

// Recently active members
function do_recent_Widget($symposium_recent_count,$symposium_recent_desc,$symposium_recent_show_light,$symposium_recent_show_mail) {
		
	global $wpdb, $current_user;
	
	$html = '';

	// Content of widget
	$sql = "SELECT u.ID, u.display_name, cast(m.meta_value as datetime) as last_activity 
		FROM ".$wpdb->base_prefix."users u 
		LEFT JOIN ".$wpdb->base_prefix."usermeta m ON u.ID = m.user_id
		WHERE m.meta_key = 'symposium_last_activity'
		ORDER BY cast(m.meta_value as datetime) DESC LIMIT 0,".$symposium_recent_count;

	$members = $wpdb->get_results($wpdb->prepare($sql, $current_user->ID));
		
	if ($members) {

		$mail_url = symposium_get_url('mail');
		$profile_url = symposium_get_url('profile');
		$q = symposium_string_query($mail_url);
		$time_now = time();

		$html .= "<div id='symposium_new_members'>";
		
			$cnt = 0;
			foreach ($members as $member)
			{
				$last_active_minutes = strtotime($member->last_activity);
				$last_active_minutes = floor(($time_now-$last_active_minutes)/60);
				
				if ($symposium_recent_desc == 'on') {
					$html .= "<div class='symposium_new_members_row'>";		
						$html .= "<div class='symposium_new_members_row_avatar'>";
							$html .= "<a href='".$profile_url.$q."uid=".$member->ID."'>";
								$html .= get_avatar($member->ID, 32);
							$html .= "</a>";
						$html .= "</div>";
						$html .= "<div class='symposium_new_members_row_member'>";
							$html .= symposium_profile_link($member->ID)." ";
							if ($symposium_recent_show_light == 'on') {
								if ($last_active_minutes >= get_option('symposium_offline')) {
									$html .= '<img src="'.get_option('symposium_images').'/loggedout.gif"> ';
								} else {
									if ($last_active_minutes >= get_option('symposium_online')) {
										$html .= '<img src="'.get_option('symposium_images').'/inactive.gif"> ';
									} else {
										$html .= '<img src="'.get_option('symposium_images').'/online.gif"> ';
									}
								}
							}
							$html .= __('last active', 'wp-symposium')." ";
							$html .= symposium_time_ago($member->last_activity).".";
							if ($symposium_recent_show_mail == 'on' && symposium_friend_of($member->ID, $current_user->ID) ) {
								$html .= " <a title='".$member->display_name."' href='".$mail_url.$q."view=compose&to=".$member->ID."'>".__('Send Mail', 'wp-symposium')."</a>";
							}
						$html .= "</div>";
					$html .= "</div>";
				} else {
					$html .= "<a title='".$member->display_name."' style='padding-right:3px;padding-bottom:3px;float:left;cursor:pointer;' href='".$profile_url.$q."uid=".$member->ID."'>";
						$html .= get_avatar($member->ID, 32);
					$html .= "</a>";
				}
			}
			$html .= "</div>";				
	} else {
		$html .= "<div id='symposium_new_members'>";
		$html .= __("Nobody recently online.", "wp-symposium");
		$html .= "</div>";							
	}
		
	echo $html;
	
}

// New activity/status posts
function do_Recentactivity_Widget($postcount,$preview,$forum) {
	
	global $wpdb, $current_user;
	
	$shown_uid = "";
	$shown_count = 0;	
	$html = '';
	// Work out link to profile page, dealing with permalinks or not
	$profile_url = symposium_get_url('profile');
	$q = symposium_string_query($profile_url);
		
	// Content of widget
	$sql = "SELECT cid, author_uid, comment, comment_timestamp, display_name, type 
	FROM ".$wpdb->base_prefix."symposium_comments c 
	INNER JOIN ".$wpdb->base_prefix."users u ON c.author_uid = u.ID 
	WHERE is_group != 'on' 
	  AND comment_parent = 0 
	  AND author_uid = subject_uid ";
	if ($forum != 'on') { $sql .= "AND type != 'forum' "; }		  
	$sql .= "ORDER BY cid DESC LIMIT 0,250";
	
	$posts = $wpdb->get_results($sql);
			
	if ($posts) {

		$html .= "<div id='symposium_recent_activity'>";
			
			foreach ($posts as $post)
			{
				if ($shown_count < $postcount) {

					if (strpos($shown_uid, $post->author_uid.",") === FALSE) { 

						$share = get_symposium_meta($post->author_uid, 'wall_share');
						$is_friend = symposium_friend_of($post->author_uid, $current_user->ID);

						if ( (is_user_logged_in() && strtolower($share) == 'everyone') || (strtolower($share) == 'public') || (strtolower($share) == 'friends only' && $is_friend) ) {

							$html .= "<div class='symposium_recent_activity_row'>";		
								$html .= "<div class='symposium_recent_activity_row_avatar'>";
									$html .= get_avatar($post->author_uid, 32);
								$html .= "</div>";
								$html .= "<div class='symposium_recent_activity_row_post'>";
									$text = stripslashes($post->comment);
									if ($post->type == 'post') {
										$text = stripslashes($post->comment);
										$text = strip_tags($text);
										if ( strlen($text) > $preview ) { $text = substr($text, 0, $preview)."..."; }
									}
									if (($x = strpos($text, 'wps_comment_plus')) !== FALSE) {
										$text = substr($text, 0, $x-9);
									}
									if ($post->type == 'gallery') {												
										if (strpos($text, '[]')) {
											$lib = explode('[]', $text);
											$text = $lib[0];
										} else {
											if (($x = strpos($text, 'wps_comment_plus')) !== FALSE) {
												$text = substr($text, 0, $x-9);
											}
										}
									}
									if (strpos($text, 'symposium_photo_image')) {
										$text = strip_tags($text,'<img><a></a>');
										$text = str_replace("img", "img style='width:32px;height:32px;'", $text);
										$html .= symposium_time_ago($post->comment_timestamp)." <a href='".$profile_url.$q."uid=".$post->author_uid."&post=".$post->cid."'>".$post->display_name."</a> ".$text."<br>";
									} else {
										$html .= "<a href='".$profile_url.$q."uid=".$post->author_uid."&post=".$post->cid."'>".$post->display_name."</a> ".$text." ".symposium_time_ago($post->comment_timestamp).".<br>";
									}
								$html .= "</div>";
							$html .= "</div>";
						
							$shown_count++;
							$shown_uid .= $post->author_uid.",";							
						}
					}
				} else {
					break;
				}
			}

		$html .= "</div>";

	}
		
	echo $html;
}


// Newly joined members
function do_members_Widget($symposium_members_count) {
	
	global $wpdb, $current_user;
	
	$html = '';
	
	// Content of widget
	$members = $wpdb->get_results("
		SELECT * FROM ".$wpdb->base_prefix."users
		ORDER BY user_registered DESC LIMIT 0,".$symposium_members_count); 
	
	if ($members) {

		$html .= "<div id='symposium_new_members'>";

			foreach ($members as $member)
			{
				$html .= "<div class='symposium_new_members_row'>";		
					$html .= "<div class='symposium_new_members_row_avatar'>";
						$html .= get_avatar($member->ID, 32);
					$html .= "</div>";
					$html .= "<div class='symposium_new_members_row_member'>";
						$html .= symposium_profile_link($member->ID)." ".__('joined', 'wp-symposium')." ";
						$html .= symposium_time_ago($member->user_registered).".";
					$html .= "</div>";
				$html .= "</div>";
			}
			
			$html .= "</div>";				
	}
		
	echo $html;
}

// Show friends
function do_symposium_friends_Widget($symposium_friends_count,$symposium_friends_desc,$symposium_friends_mode,$symposium_friends_show_light,$symposium_friends_show_mail) {
	
	global $wpdb, $current_user;
	$html = '';

	// Content of widget
	$sql = "SELECT u.ID, u.display_name, cast(m.meta_value as datetime) as last_activity 
		FROM ".$wpdb->base_prefix."symposium_friends f
		LEFT JOIN ".$wpdb->base_prefix."users u ON f.friend_to = u.ID
		LEFT JOIN ".$wpdb->base_prefix."usermeta m ON f.friend_to = m.user_id
		WHERE f.friend_from = %d AND f.friend_accepted = 'on' 
		AND m.meta_key = 'symposium_last_activity'
		ORDER BY cast(m.meta_value as datetime) DESC LIMIT 0,".$symposium_friends_count;

	$members = $wpdb->get_results($wpdb->prepare($sql, $current_user->ID));
		
	if ($members) {

		$mail_url = symposium_get_url('mail');
		$profile_url = symposium_get_url('profile');
		$q = symposium_string_query($mail_url);
		$time_now = time();

		$html .= "<div id='symposium_new_members'>";
		
			if ($symposium_friends_mode == 'all' || $symposium_friends_mode == 'online') {
				$loop=1;
			} else {
				$loop=2;
			}
			for ($l=1; $l<=$loop; $l++) {
				
				if ($symposium_friends_mode == 'split') {
					if ($l==1) {
						$html .= '<div style="font-weight:bold">'.__('Online', 'wp-symposium').'</div>';
					} else {
						$html .= '<div style="clear:both;margin-top:6px;font-weight:bold">'.__('Offline', 'wp-symposium').'</div>';
					}
					
				}

				$cnt = 0;
				foreach ($members as $member)
				{
					$last_active_minutes = strtotime($member->last_activity);
					$last_active_minutes = floor(($time_now-$last_active_minutes)/60);
					
					$show = false;
					if ($symposium_friends_mode == 'online' && $last_active_minutes < get_option('symposium_offline')) { $show = true; }
					if ( ($symposium_friends_mode == 'split') && ( ($last_active_minutes < get_option('symposium_offline') && $l == 1) || ($last_active_minutes >= get_option('symposium_offline') && $l == 2) ) ) { $show = true; }
					if ($symposium_friends_mode == 'all') { $show = true; }
					
					if ($show) {
						$cnt++;								
						if ($symposium_friends_desc == 'on') {
							$html .= "<div class='symposium_new_members_row'>";		
								$html .= "<div class='symposium_new_members_row_avatar'>";
									$html .= "<a href='".$profile_url.$q."uid=".$member->ID."'>";
										$html .= get_avatar($member->ID, 32);
									$html .= "</a>";
								$html .= "</div>";
								$html .= "<div class='symposium_new_members_row_member'>";
									$html .= symposium_profile_link($member->ID)." ";
									if ($symposium_friends_show_light == 'on') {
										if ($last_active_minutes >= get_option('symposium_offline')) {
											$html .= '<img src="'.get_option('symposium_images').'/loggedout.gif"> ';
										} else {
											if ($last_active_minutes >= get_option('symposium_online')) {
												$html .= '<img src="'.get_option('symposium_images').'/inactive.gif"> ';
											} else {
												$html .= '<img src="'.get_option('symposium_images').'/online.gif"> ';
											}
										}
									}
									$html .= __('last active', 'wp-symposium')." ";
									$html .= symposium_time_ago($member->last_activity).".";
									if ($symposium_friends_show_mail == 'on') {
										$html .= " <a title='".$member->display_name."' href='".$mail_url.$q."view=compose&to=".$member->ID."'>".__('Send Mail', 'wp-symposium')."</a>";
									}
								$html .= "</div>";
							$html .= "</div>";
						} else {
							$html .= "<a title='".$member->display_name."' style='padding-right:3px;padding-bottom:3px;float:left;cursor:pointer;' href='".$profile_url.$q."uid=".$member->ID."'>";
								$html .= get_avatar($member->ID, 32);
							$html .= "</a>";
						}
					}
				}
				if ($cnt == 0) {
					$html .= __('Nobody', 'wp-symposium');
				}
			}
			
			$html .= "</div>";				
	} else {
		$html .= "<div id='symposium_new_members'>";
		$html .= __("No friends yet, add friends via their profile page.", "wp-symposium");
		$html .= "</div>";							
	}
	
	echo $html;
}

// Recent forum posts
function do_Forumrecentposts_Widget($postcount,$preview,$cat_id,$show_replies) {
	
	global $wpdb, $current_user;
	
	// Content of widget
	$sql = "SELECT t.tid, t.stub, p.stub as parent_stub, t.topic_subject, t.topic_owner, t.topic_post, t.topic_started, t.topic_category, t.topic_date, u.display_name, t.topic_parent, t.topic_group 
	FROM ".$wpdb->prefix.'symposium_topics'." t 
	INNER JOIN ".$wpdb->base_prefix.'users'." u ON t.topic_owner = u.ID 
	LEFT JOIN ".$wpdb->prefix."symposium_topics p ON t.topic_parent = p.tid 
	WHERE t.topic_approved = 'on' ";
	if ($cat_id != '' && $cat_id > 0) {
		$sql .= "AND t.topic_category = ".$cat_id." ";
	}
	if ($show_replies != 'on') {
		$sql .= "AND t.topic_parent = 0 ";
	}
	$sql .= "ORDER BY t.tid DESC LIMIT 0,100";
	$posts = $wpdb->get_results($sql); 
	$count = 0;
	$html = '';
	
	if (WPS_DEBUG) $html .= $wpdb->last_query.'<br />';
	
	// Previous login
	if (is_user_logged_in()) {
		$previous_login = get_symposium_meta($current_user->ID, 'previous_login');
	}

	// Get forum URL worked out
	$forum_url = symposium_get_url('forum');
	$forum_q = symposium_string_query($forum_url);

	// Get list of roles for this user
    $user_roles = $current_user->roles;
    $user_role = strtolower(array_shift($user_roles));
    if ($user_role == '') $user_role = 'NONE';
    						
	if ($posts) {

		$html .= "<div id='symposium_latest_forum'>";
			
			foreach ($posts as $post)
			{
					if ($post->topic_group == 0 || (symposium_member_of($post->topic_group) == "yes") || ($wpdb->get_var($wpdb->prepare("SELECT content_private FROM ".$wpdb->prefix."symposium_groups WHERE gid = ".$post->topic_group)) != "on") ) {

						// Check permitted to see forum category
						$sql = "SELECT level FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d";
						$levels = $wpdb->get_var($wpdb->prepare($sql, $post->topic_category));
						$cat_roles = unserialize($levels);
						if (strpos(strtolower($cat_roles), 'everyone,') !== FALSE || strpos(strtolower($cat_roles), $user_role.',') !== FALSE) {
							
							$html .= "<div class='symposium_latest_forum_row'>";		
								$html .= "<div class='symposium_latest_forum_row_avatar'>";
									$html .= get_avatar($post->topic_owner, 32);
								$html .= "</div>";
								$html .= "<div class='symposium_latest_forum_row_post'>";
									if ($post->topic_parent > 0) {
										$html .= symposium_profile_link($post->topic_owner);
										if ($preview > 0) {
											$text = strip_tags(stripslashes($post->topic_post));
											if ( strlen($text) > $preview ) { $text = substr($text, 0, $preview)."..."; }
											$html .= " ".__('replied', 'wp-symposium');
											if (get_option('symposium_permalink_structure') && $group_id == 0) {
												$perma_cat = symposium_get_forum_category_part_url($post->topic_category);
												$html .= " <a href='".$forum_url.'/'.$perma_cat.$post->parent_stub."'>".$text."</a>";
											} else {
												$html .= " <a href='".$forum_url.$forum_q."cid=".$post->topic_category."&show=".$post->topic_parent."'>".$text."</a>";
											}
										} else {
											$html .= "<br />";
										}
										$html .= " ".symposium_time_ago($post->topic_date).".";
									} else {
										$html .= symposium_profile_link($post->topic_owner);
										if ($preview > 0) {
											$text = stripslashes($post->topic_subject);
											if ( strlen($text) > $preview ) { $text = substr($text, 0, $preview)."..."; }
											if ($post->topic_group == 0) {
												$url = $forum_url;
												$q = $forum_q;
											} else {
												// Get group URL worked out
												$url = symposium_get_url('group');
												if (strpos($url, '?') !== FALSE) {
													$q = "&gid=".$post->topic_group."&";
												} else {
													$q = "?gid=".$post->topic_group."&";
												}
											}
											$html .= " ".__('started', 'wp-symposium');
											if (get_option('symposium_permalink_structure') && $group_id == 0) {
												$perma_cat = symposium_get_forum_category_part_url($post->topic_category);
												$html .= " <a href='".$url.'/'.$perma_cat.$post->stub."'>".$text."</a>";
											} else {
												$html .= " <a href='".$url.$q."cid=".$post->topic_category."&show=".$post->tid."'>".$text."</a>";
											}
										}
										$html .= " ".symposium_time_ago($post->topic_started).".";
									}
										if (is_user_logged_in() && get_option('symposium_forum_stars')) {
											if ($post->topic_date > $previous_login && $post->topic_owner != $current_user->ID) {
												$html .= " <img src='".get_option('symposium_images')."/new.gif' alt='New!' />";
											}
										}
								$html .= "</div>";
							$html .= "</div>";
							
							$count++;
							if ($count >= $postcount) {
								break;
							}
						}
						
					}
			}

		$html .= "</div>";

	}
		
	echo $html;
}

// Summary/login widget
function do_symposium_summary_Widget($show_loggedout,$show_form,$login_url,$show_avatar,$login_username,$login_password,$login_remember_me,$login_button,$login_forgot,$login_register) {
	
	global $wpdb,$current_user;
	
	// Content of widget
	echo "<div id='symposium_summary_widget'>";

	if (is_user_logged_in()) {

		// LOGGED IN
		echo "<ul style='list-style:none'>";

		// Link to profile page
		if (function_exists('symposium_profile')) {

			// Get mail URL worked out
			$profile_url = symposium_get_url('profile');

			echo "<li>";
				if ($show_avatar) {
					echo "<div id='symposium_summary_widget_avatar' style='float:left;margin-right:6px;'>";
					echo get_avatar($current_user->ID, 32);
					echo "</div>";
				}
				echo "<a href='".$profile_url."'>".$current_user->display_name."</a> ";
			echo "</li>";
		}
		
		// Mail
		if (function_exists('symposium_mail')) {

			// Get mail URL worked out
			$mail_url = symposium_get_url('mail');

			echo "<li id='symposium_summary_mail' style='clear:both'>";
				$total_mail = $wpdb->get_var($wpdb->prepare("SELECT count(*) FROM ".$wpdb->base_prefix."symposium_mail WHERE mail_to = ".$current_user->ID." AND mail_in_deleted != 'on'"));
				echo "<a href='".$mail_url."'>".__("Messages:", "wp-symposium")."</a> ".$total_mail;
				$unread_mail = $wpdb->get_var($wpdb->prepare("SELECT count(*) FROM ".$wpdb->base_prefix."symposium_mail WHERE mail_to = ".$current_user->ID." AND mail_in_deleted != 'on' AND mail_read != 'on'"));
				if ($unread_mail > 0) {
					echo " (".$unread_mail." ".__("unread","wp-symposium").")";
				}
			echo "</li>";
		}

		// Friends
		if (function_exists('symposium_profile')) {

			// Get mail URL worked out
			$friends_url = symposium_get_url('profile');
			if (strpos($friends_url, '?') !== FALSE) {
				$q = "&view=friends";
			} else {
				$q = "?view=friends";
			}

			echo "<li id='symposium_summary_profile'>";
				$sql = "SELECT count(*) FROM ".$wpdb->base_prefix."symposium_friends WHERE friend_to = %d AND friend_accepted = 'on'";
				$current_friends = $wpdb->get_var($wpdb->prepare($sql, $current_user->ID));
				echo  "<a href='".$friends_url.$q."'>".__("Friends:", "wp-symposium")."</a> ".$current_friends;
				$sql = "SELECT count(*) FROM ".$wpdb->base_prefix."symposium_friends WHERE friend_to = %d AND friend_accepted != 'on'";
				$friend_requests = $wpdb->get_var($wpdb->prepare($sql, $current_user->ID));

				if ($friend_requests == 1) {	
					echo " (".$friend_requests." ".__("request","wp-symposium").")";
				}
				if ($friend_requests > 1) {	
					echo " (".$friend_requests." ".__("requests","wp-symposium").")";
				}
			echo "</li>";

			// Hook for more list items
			do_action('symposium_widget_summary_hook_loggedin');

			if ( current_user_can('manage_options') ) {
				echo wp_register( "<li id='symposium_summary_dashboard'>", "</li>", true);
			}
			if ($show_loggedout == 'on') {
				echo "<li id='symposium_summary_logout'>";
				echo wp_loginout( get_bloginfo('url'), true );
				echo "</li>";
			}

		}

		echo "</ul>";
				
	} else {

		// LOGGED OUT

		// Hook for more list items
		do_action('symposium_widget_summary_hook_loggedout');

		if ($show_loggedout == 'on' && $show_form == '') {
			echo wp_loginout( get_permalink(), true);
			echo ' (<a href="'.wp_lostpassword_url( get_bloginfo('url') ).'" title="'.__('Forgot Password?', 'wp-symposium').'">'.__('Forgot Password?', 'wp-symposium').'</a>)<br />';
			echo wp_register( "", "", true);
		}

		if ($show_loggedout == 'on' && $show_form == 'on') {
		   if ($login_url != '') {
		      wp_login_form(array(
		         'redirect' => $login_url, 
		         'label_username' => stripslashes($login_username), 
		         'label_password' => stripslashes($login_password),
		         'label_remember' => stripslashes($login_remember_me),
		         'label_log_in' => stripslashes($login_button)
		      ) )  ;
		   } else {
		      wp_login_form(array(
		         'redirect' => get_permalink(), 
		         'label_username' => stripslashes($login_username), 
		         'label_password' => stripslashes($login_password),
		         'label_remember' => stripslashes($login_remember_me),
		         'label_log_in' => stripslashes($login_button)
		      ) )  ;
		   }
		   echo '<a href="'.wp_lostpassword_url( get_bloginfo('url') ).'" title="'.stripslashes($login_forgot).'">'.stripslashes($login_forgot).'</a><br />';
		   echo wp_register("<!--", "--><a href='".get_bloginfo('url')."/wp-login.php?action=register'>".stripslashes($login_register)."</a>", true) ;
		}	

	}
		
	echo "</div>";	
	
}

// Forum posts with no answer
function do_Forumnoanswer_Widget($preview,$cat_id,$cat_id_exclude,$timescale,$postcount,$groups) {
	
	global $wpdb, $current_user;
	
	$html = '';

	// Previous login
	if (is_user_logged_in()) {
		$previous_login = get_symposium_meta($current_user->ID, 'previous_login');
	}
	
	// Content of widget
	
	$sql = "SELECT t.tid, t.topic_subject, t.topic_owner, t.topic_post, t.topic_category, t.topic_date, u.display_name, t.topic_parent, t.topic_group, t.topic_started, 
		(SELECT COUNT(*) FROM ".$wpdb->prefix."symposium_topics v WHERE v.topic_parent = t.tid) AS replies 
		FROM ".$wpdb->prefix."symposium_topics t 
		INNER JOIN ".$wpdb->base_prefix.'users'." u ON t.topic_owner = u.ID
		WHERE t.topic_parent = 0 
		  AND t.for_info != 'on' 
		  AND t.topic_approved = 'on' 
		  AND t.topic_started >= ( CURDATE() - INTERVAL ".$timescale." DAY ) 
		AND NOT EXISTS 
		  (SELECT tid from ".$wpdb->prefix."symposium_topics s 
		    WHERE s.topic_parent = t.tid AND s.topic_answer = 'on') ";
	if ($cat_id != '' && $cat_id > 0) {
		$sql .= "AND topic_category IN (".$cat_id.") ";
	}
	if ($cat_id_exclude != '' && $cat_id_exclude > 0) {
		$sql .= "AND topic_category NOT IN (".$cat_id_exclude.") ";
	}
	if ($groups != 'on') {
		$sql .= "AND topic_group = 0 ";
	}
	$sql .= "ORDER BY t.topic_started DESC LIMIT 0,".$postcount;
	$posts = $wpdb->get_results($sql); 
			
	// Get forum URL worked out
	$forum_url = symposium_get_url('forum');
	$forum_q = symposium_string_query($forum_url);

	// Get list of roles for this user
    $user_roles = $current_user->roles;
    $user_role = strtolower(array_shift($user_roles));
    if ($user_role == '') $user_role = 'NONE';
    							
	if ($posts) {

		$html .= "<div id='symposium_latest_forum'>";
			
			foreach ($posts as $post)
			{
					if ($post->topic_group == 0 || (symposium_member_of($post->topic_group) == "yes") || ($wpdb->get_var($wpdb->prepare("SELECT content_private FROM ".$wpdb->prefix."symposium_groups WHERE gid = ".$post->topic_group)) != "on") ) {

						// Check permitted to see forum category
						$sql = "SELECT level FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d";
						$levels = $wpdb->get_var($wpdb->prepare($sql, $post->topic_category));
						$cat_roles = unserialize($levels);
						if (strpos(strtolower($cat_roles), 'everyone,') !== FALSE || strpos(strtolower($cat_roles), $user_role.',') !== FALSE) {

							$html .= "<div class='symposium_latest_forum_row'>";		
								$html .= "<div class='symposium_latest_forum_row_avatar'>";
									$html .= get_avatar($post->topic_owner, 32);
								$html .= "</div>";
								$html .= "<div class='symposium_latest_forum_row_post'>";
									$html .= symposium_profile_link($post->topic_owner);
									if ($preview > 0) {
										$text = stripslashes($post->topic_subject);
										if ( strlen($text) > $preview ) { $text = substr($text, 0, $preview)."..."; } 
										if ($post->topic_group == 0) {
											$url = $forum_url;
											$q = $forum_q;
										} else {
											// Get group URL worked out
											$url = symposium_get_url('group');
											if (strpos($url, '?') !== FALSE) {
												$q = "&gid=".$post->topic_group."&";
											} else {
												$q = "?gid=".$post->topic_group."&";
											}
										}
										$html .= " ".__('started', 'wp-symposium')." <a href='".$url.$q."cid=".$post->topic_category."&show=".$post->tid."'>".$text."</a>";
									} else {
										$html .= "<br />";
									}
									$html .= " ".symposium_time_ago($post->topic_started).". ";
									if ($post->replies > 0) {
										$html .= $post->replies.' ';
										if ($post->replies != 1) {
											$html .= __('replies', 'wp-symposium');
										} else {
											$html .= __('reply', 'wp-symposium');
										}
										$html .= ".";
									}
									if (is_user_logged_in() && get_option('symposium_forum_stars')) {
										if ($post->topic_started > $previous_login && $post->topic_owner != $current_user->ID) {
											$html .= " <img src='".get_option('symposium_images')."/new.gif' alt='New!' />";
										}
									}
									$html .= "<br />";
								$html .= "</div>";
							$html .= "</div>";
						}								
					}
			}

		$html .= "</div>";

	}
	
	echo $html;
}

function do_Forumexperts_Widget($cat_id,$cat_id_exclude,$timescale,$postcount,$groups) {
	
	global $wpdb,$current_user;
	
	$html = '';

	// Content of widget
	$sql = "SELECT topic_owner, display_name, count(*) AS cnt FROM 
	 		(SELECT topic_owner FROM ".$wpdb->prefix."symposium_topics t 
			 WHERE t.topic_answer = 'on' AND t.topic_date >= ( CURDATE() - INTERVAL ".$timescale." DAY ) "; 
	if ($cat_id != '' && $cat_id > 0) {
		$sql .= "AND topic_category IN (".$cat_id.") ";
	}
	if ($cat_id_exclude != '' && $cat_id_exclude > 0) {
		$sql .= "AND topic_category NOT IN (".$cat_id_exclude.") ";
	}
	if ($groups != 'on') {
		$sql .= "AND topic_group = 0 ";
	}
	$sql .= "ORDER BY topic_owner) AS tmp ";
	$sql .= "LEFT JOIN ".$wpdb->prefix."users u ON topic_owner = u.ID ";
	$sql .= "GROUP BY topic_owner, display_name ";
	$sql .= "ORDER BY cnt DESC";
	$posts = $wpdb->get_results($sql); 
	
	$count = 1;
	
	if ($posts) {

		$html .= "<div id='symposium_latest_forum'>";
			
			foreach ($posts as $post)
			{
				$html .= '<div style="clear:both;">';
					$html .= '<div style="float:left;">';
						$html .= symposium_profile_link($post->topic_owner);
					$html .= '</div>';
					$html .= '<div style="float:right;">';
						$html .= $post->cnt.'<br />';
					$html .= '</div>';
				$html .= '</div>';
				
				if ($count++ == $postcount) {
					break;
				}
			}

		$html .= "</div>";

	}
	
	echo $html;	
}

function display_bronze_message() {

	if ( !symposium_is_plus() ) {
		echo "<strong>".__("WYSWIYG editor", "wp-symposium")."</strong><br />";
		echo __("To access WYSWIYG editor settings, <a href='admin.php?page=symposium_debug'>enter a valid activation code</a>.", "wp-symposium").'<br />';
	}	
	
}

function symposium_is_plus() {

	$saved_code = get_option('symposium_activation_code');
	$code = preg_replace('#[^0-9]#','',$saved_code);
	if (($saved_code) && ($code > time() || $saved_code == 'wps' || substr($saved_code,0,3) == 'vip')) {
		return true;
	} else {
		return false;
	}
	
}

function wps_get_monthname($month) {
	switch($month) {									
		case 0:$monthname = "";break;
		case 1:$monthname = __("January", "wp-symposium");break;
		case 2:$monthname = __("February", "wp-symposium");break;
		case 3:$monthname = __("March", "wp-symposium");break;
		case 4:$monthname = __("April", "wp-symposium");break;
		case 5:$monthname = __("May", "wp-symposium");break;
		case 6:$monthname = __("June", "wp-symposium");break;
		case 7:$monthname = __("July", "wp-symposium");break;
		case 8:$monthname = __("August", "wp-symposium");break;
		case 9:$monthname = __("September", "wp-symposium");break;
		case 10:$monthname = __("October", "wp-symposium");break;
		case 11:$monthname = __("November", "wp-symposium");break;
		case 12:$monthname = __("December", "wp-symposium");break;
	}
	return $monthname;
}

function wps_is_wpmu() {
    global $wpmu_version;
    if (function_exists('is_multisite'))
        if (is_multisite()) return true;
    if (!empty($wpmu_version)) return true;
    return false;
}

function symposium_get_forum_category_part_url($cat_id) {
	if (get_option('symposium_permalinks_cats') && $cat_id) {
		global $wpdb;
		$sql = "select title from ".$wpdb->prefix."symposium_cats WHERE cid = %d";
		return symposium_create_stub($wpdb->get_var($wpdb->prepare($sql, $cat_id))).'/';
	} else {
		return '';
	}
}

function symposium_create_stub($text) {
	global $wpdb;
	$stub = preg_replace("/[^A-Za-z0-9 ]/",'',$text);
	$stub = strtolower(str_replace(' ', '-', $stub));
	$sql = "SELECT COUNT(*) FROM ".$wpdb->prefix."symposium_topics WHERE stub = '".$stub."'";
	$cnt = $wpdb->get_var($sql);
	if ($cnt > 0) $stub .= "-".$cnt;
	$stub = str_replace('--', '-', $stub);
	return $stub;
}

function symposium_get_stub_id($stub, $type) {
	global $wpdb;
	$id = false;

	switch($type) {	
	case 'forum-cat':
		$sql = "SELECT cid FROM ".$wpdb->prefix."symposium_cats WHERE stub = %s";
		$id = $wpdb->get_var($wpdb->prepare($sql, $stub));
		break;								
	case 'forum-topic':
		$sql = "SELECT tid FROM ".$wpdb->prefix."symposium_topics WHERE topic_parent = 0 AND stub = %s";
		$id = $wpdb->get_var($wpdb->prepare($sql, $stub));
		break;
	}	
	return $id;
}


?>
