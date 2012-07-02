<?php
/*
Plugin Name: WP Symposium Forum
Plugin URI: http://www.wpsymposium.com
Description: Forum component for the Symposium suite of plug-ins. Put [symposium-forum] on any WordPress page to display forum.
Version: 12.07.02
Author: WP Symposium
Author URI: http://www.wpsymposium.com
License: GPL3
*/
	
/*  Copyright 2010,2011,2012  Simon Goodchild  (info@wpsymposium.com)

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

	global $wpdb, $current_user;
	$level = symposium_get_current_userlevel();
	
	$html = '';
	$topic_id = '';
	$cat_id = '';
	
	// resolve stubs if using WPS permalinks
	if ( get_option('symposium_permalink_structure') && get_query_var('stub')) {
		$stubs = explode('/', get_query_var('stub'));
		$stub0 = $stubs[0];
		$stub1 = $stubs[1];
		if (WPS_DEBUG) echo $stub0.'/'.$stub1.'<br />';
		
		if ($stub0) {
			// Two parameters, so go to topic
			$cat_id = symposium_get_stub_id($stub0, 'forum-cat');
			$topic_id = symposium_get_stub_id($stub1, 'forum-topic');
			if (!$cat_id) $cat_id = '';
			if (!$topic_id) $topic_id = '';
			if (WPS_DEBUG) echo '(1):'.$cat_id.'/'.$topic_id.' ('.$stub0.'/'.$stub1.')<br />';
		} else {
			// One parameter, so go to category
			$cat_id = symposium_get_stub_id($stub1, 'forum-cat');
			if (WPS_DEBUG) echo '(2):'.$cat_id.' ('.$stub1.')<br />';
			if (!$cat_id) {
				// Couldn't find category, so look for topic instead
				$cat_id = '';
				$topic_id = symposium_get_stub_id($stub1, 'forum-topic');
				if (WPS_DEBUG) echo '(3):'.$topic_id.' ('.$stub1.')<br />';
				if (!$topic_id) $topic_id = '';
			}
		}
		$html .= "<div id='symposium_perma_cat_id' style='display:none'>".$cat_id."</div>";
		$html .= "<div id='symposium_perma_topic_id' style='display:none'>".$topic_id."</div>";
	}
	
	
	// not using AJAX (or permalinks not found, for backward compatibility with old links)
	if ( ( $topic_id == '' && $cat_id == '') || ( !get_option('symposium_forum_ajax') && !get_option('symposium_permalink_structure') ) ) {
		$cat_id = isset($_GET['cid']) ? $_GET['cid'] : 0;
		$topic_id = isset($_GET['show']) ? $_GET['show'] : 0;
	}
		
	// Wrapper
	$html .= "<div class='symposium-wrapper'>";

	// Check to see if this member is in the included list of roles
	$user = get_userdata( $current_user->ID );
	$capabilities = $user->{$wpdb->base_prefix.'capabilities'};
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
	
	if ( $can_view || strpos($viewer, __('everyone', 'wp-symposium')) !== FALSE) {

		$html .= "<div id='symposium-forum-div'>";
		
		if ( get_option('symposium_permalink_structure') || !get_option('symposium_forum_ajax') ) {
			if ($topic_id == 0) {
				$forum = symposium_getForum($cat_id);
				if (($x = strpos($forum, '[|]')) !== FALSE) $forum = substr($forum, $x+3);
			} else {
				$html .= symposium_getTopic($topic_id);	
			}
		}
		
		$html .= "</div>";
		
		
	 } else {

		$html .= "<p>".__("Sorry, but you are not permitted to view the forum.", "wp-symposium")."</p>";
		if (symposium_get_current_userlevel() == 5) $html .= __('Permissions are set via the WordPress admin dashboard->Symposium->Forum.', 'wp-symposium');

	 }

	$html .= "</div>";
	// End Wrapper
	
	$html .= "<div style='clear: both'></div>";
	
	// Send HTML
	return $html;

}


function symposium_forum_latestposts($attr) {
	
	global $wpdb;
	$use_answers = get_option('symposium_use_answers');

	$count = isset($attr['count']) ? $attr['count'] : '';
	$cat_id = isset($attr['cat']) ? $attr['cat'] : 0;
	
	$html = '<div id="forum_activity_div">';
	$html .= symposium_forum_latestposts_showThreadChildren($count, $cat_id, 0, 0, $use_answers);	
	$html .= '</div>';

	return $html;

}
function symposium_forum_latestposts_showThreadChildren($count, $cat_id, $parent, $level, $use_answers) {
	
	global $wpdb, $current_user;

	$thispage = symposium_get_url('forum');
	if ($thispage[strlen($thispage)-1] != '/') { $thispage .= '/'; }
	$q = symposium_string_query($thispage);		

	$previous_login = get_symposium_meta($current_user->ID, 'previous_login');
	
	$html = "";
	
	$preview = 30;	
	if ($count != '') { 
		$postcount = $count; 
	} else {
		$postcount = get_option('symposium_symposium_forumlatestposts_count');
	}
	
	if ($level == 0) {
		$avatar_size = 30;
		$margin_top = 10;
		$desc = "DESC";
	} else {
		$avatar_size = 20;
		$margin_top = 6;
		$desc = "";
	}

	// All topics started
	$cat_sql = ($cat_id) ? " AND t.topic_category = ".$cat_id : '';
	$posts = $wpdb->get_results("
		SELECT t.tid, t.topic_subject, t.stub, p.stub as parent_stub, t.topic_owner, t.topic_post, t.topic_category, t.topic_started, u.display_name, t.topic_parent, t.topic_answer, t.topic_date, t.topic_approved 
		FROM ".$wpdb->prefix.'symposium_topics'." t INNER JOIN ".$wpdb->base_prefix.'users'." u ON t.topic_owner = u.ID 
		LEFT JOIN ".$wpdb->prefix.'symposium_topics'." p ON t.topic_parent = p.tid 
		WHERE t.topic_parent = ".$parent." AND t.topic_group = 0".$cat_sql." ORDER BY t.tid ".$desc." LIMIT 0,".$postcount); 

	if ($posts) {

		foreach ($posts as $post)
		{
			if ( ($post->topic_approved == 'on') || ($post->topic_approved != 'on' && ($post->topic_owner == $current_user->ID || current_user_can('level_10'))) ) {

				$html .= "<div style='clear:both; overflow:auto; padding-left: ".($level*40)."px; margin-top:".$margin_top."px;'>";		
					$html .= "<div class='symposium_latest_forum_row_avatar' style='float:left'>";
						$html .= get_avatar($post->topic_owner, $avatar_size);
					$html .= "</div>";
					$html .= "<div style='float:left'>";
						if ($post->topic_parent > 0) {
							$text = strip_tags(stripslashes($post->topic_post));
							if ( strlen($text) > $preview ) { $text = substr($text, 0, $preview)."..."; }
							$html .= symposium_profile_link($post->topic_owner)." ".__('replied', 'wp-symposium')." ";
							if (get_option('symposium_permalink_structure')) {
								$perma_cat = symposium_get_forum_category_part_url($post->topic_category);
								$html .= "<a title='".$text."' href='".$thispage.$perma_cat.$post->parent_stub."'>";
							} else {
								$html .= "<a title='".$text."' href='".$thispage.$q."cid=".$post->topic_category."&show=".$post->topic_parent."'>";
							}
							$html .= $text."</a> ".symposium_time_ago($post->topic_started);
							if ($use_answers == 'on' && $post->topic_answer == 'on') {
								$html .= ' <img style="width:12px;height:12px" src="'.get_option('symposium_images').'/tick.png" alt="'.__('Answer Accepted', 'wp-symposium').'" />';
							}
							$html .= "<br>";
						} else {
							$text = stripslashes($post->topic_subject);
							if ( strlen($text) > $preview ) { $text = substr($text, 0, $preview)."..."; }
							$html .= symposium_profile_link($post->topic_owner)." ".__('started', 'wp-symposium');
							if (get_option('symposium_permalink_structure')) {
								$perma_cat = symposium_get_forum_category_part_url($post->topic_category);
								$html .= " <a title='".$text."'  href='".$thispage.$perma_cat.$post->stub."'>".$text."</a> ";
							} else {
								$html .= " <a title='".$text."'  href='".$thispage.$q."cid=".$post->topic_category."&show=".$post->tid."'>".$text."</a> ";
							}
							$html .= symposium_time_ago($post->topic_started).".<br>";
						}
					$html .= "</div>";
					if ($post->topic_date > $previous_login && $post->topic_owner != $current_user->ID) {
						$html .= "<div style='float:left;'>";
							$html .= "&nbsp;<img src='".get_option('symposium_images')."/new.gif' alt='New!' />";
						$html .= "</div>";
					}		
					if ($post->topic_approved != 'on') {
						$html .= "&nbsp;<em>[".__("pending approval", "wp-symposium")."]</em>";
					}
				$html .= "</div>";
				
			}
			
			$html .= symposium_forum_latestposts_showThreadChildren($count, $cat_id, $post->tid, $level+1, $use_answers);
			
		}
	}	
	
	return $html;
}


/* ====================================================== SET SHORTCODE ====================================================== */

if (!is_admin()) {
	add_shortcode('symposium-forum', 'symposium_forum');  
	add_shortcode('symposium-forumlatestposts', 'symposium_forum_latestposts');  
}



?>
