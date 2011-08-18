<?php
/*
Plugin Name: WP Symposium Forum
Plugin URI: http://www.wpsymposium.com
Description: Forum component for the Symposium suite of plug-ins. Put [symposium-forum] on any WordPress page to display forum.
Version: 11.8.18
Author: WP Symposium
Author URI: http://www.wpsymposium.com
License: GPL3
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

function symposium_forum() {	

	global $wpdb;
	$viewer = $wpdb->get_var($wpdb->prepare("SELECT viewer FROM ".$wpdb->prefix.'symposium_config'));
	$level = symposium_get_current_userlevel();
	
	$html = '';
		
	// Wrapper
	$html .= "<div class='symposium-wrapper'>";

	if ( ($viewer == "Guest")
	 || ($viewer == "Subscriber" && $level >= 1)
	 || ($viewer == "Contributor" && $level >= 2)
	 || ($viewer == "Author" && $level >= 3)
	 || ($viewer == "Editor" && $level >= 4)
	 || ($viewer == "Administrator" && $level == 5) ) {

		$html .= "<div id='symposium-forum-div'></div>";
		
	 } else {

		$html .= "<p>".__("The minimum level for this forum is", "wp-symposium")." ".$viewer."</p>";

	 }

	$html .= "</div>";
	// End Wrapper
	
	$html .= "<div style='clear: both'></div>";
	
	// Send HTML
	return $html;

}


function symposium_forum_latestposts($attr) {
	
	global $wpdb;

	$html = '<div id="forum_activity_div">';
	$html .= symposium_forum_latestposts_showThreadChildren(isset($attr['count']) ? $attr['count'] : '', 0, 0);	
	$html .= '</div>';

	return $html;

}
function symposium_forum_latestposts_showThreadChildren($count, $parent, $level) {
	
	global $wpdb;

	$thispage = symposium_get_url('forum');
	if ($thispage[strlen($thispage)-1] != '/') { $thispage .= '/'; }
	$q = symposium_string_query($thispage);		
	
	$html = "";
	
	$preview = 30;	
	if ($count != '') { 
		$postcount = $count; 
	} else {
		$postcount = $wpdb->get_var($wpdb->prepare("SELECT symposium_forumlatestposts_count FROM ".$wpdb->prefix."symposium_config"));
	}
	
	if ($level == 0) {
		$avatar_size = 30;
		$margin_top = 10;
		$desc = "DESC";
	} else {
		$avatar_size = 20;
		$margin_top = 0;
		$desc = "";
	}

	// All topics started
	$posts = $wpdb->get_results("
		SELECT tid, topic_subject, topic_owner, topic_post, topic_category, topic_started, display_name, topic_parent 
		FROM ".$wpdb->prefix.'symposium_topics'." t INNER JOIN ".$wpdb->base_prefix.'users'." u ON t.topic_owner = u.ID 
		WHERE topic_parent = ".$parent." AND topic_group = 0 ORDER BY tid ".$desc." LIMIT 0,".$postcount); 

	if ($posts) {

		foreach ($posts as $post)
		{
			$html .= "<div style='clear:both; padding-left: ".($level*40)."px; margin-top:".$margin_top."px;'>";		
				$html .= "<div class='symposium_latest_forum_row_avatar'>";
					$html .= get_avatar($post->topic_owner, $avatar_size);
				$html .= "</div>";
				$html .= "<div>";
					if ($post->topic_parent > 0) {
						$text = stripslashes($post->topic_post);
						if ( strlen($text) > $preview ) { $text = substr($text, 0, $preview)."..."; }
						$html .= symposium_profile_link($post->topic_owner)." ".__('replied', 'wp-symposium')." ";
						$html .= "<a href='".$thispage.symposium_permalink($post->topic_parent, "topic").$q."cid=".$post->topic_category."&show=".$post->topic_parent."'>";
						$html .= $text."</a> ".symposium_time_ago($post->topic_started).".<br>";
					} else {
						$text = stripslashes($post->topic_subject);
						if ( strlen($text) > $preview ) { $text = substr($text, 0, $preview)."..."; }
						$html .= symposium_profile_link($post->topic_owner)." ".__('started', 'wp-symposium')." <a href='".$thispage.symposium_permalink($post->tid, "topic").$q."cid=".$post->topic_category."&show=".$post->tid."'>".$text."</a> ".symposium_time_ago($post->topic_started).".<br>";
					}
				$html .= "</div>";
			$html .= "</div>";
			
			$html .= symposium_forum_latestposts_showThreadChildren($count, $post->tid, $level+1);
			
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
