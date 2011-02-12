<?php
/*
Plugin Name: WP Symposium Forum
Plugin URI: http://www.wpsymposium.com
Description: Forum component for the Symposium suite of plug-ins. Put [symposium-forum] on any WordPress page to display forum.
Version: 0.36.1
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

function symposium_forum() {	

	global $wpdb, $current_user;

	$plugin_dir = 'wp-symposium';
	$plugin = get_site_url().'/wp-content/plugins/'.$plugin_dir.'/';
	
	$config = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."symposium_config"));

	$forum_url = $config->forum_url;
	$seo = $config->seo;

	// Work out link to this page, dealing with permalinks or not
	$thispage = get_permalink();
	if ($thispage[strlen($thispage)-1] != '/') { $thispage .= '/'; }
	if (isset($_GET[page_id]) && $_GET[page_id] != '') {
		// No Permalink
		$thispage = $forum_url;
		$q = "&";
	} else {
		$q = "?";
	}
	
		
	$dbpage = WP_PLUGIN_URL.'/'.$plugin_dir.'/symposium_forum_db.php';
	


	$html = "";
	
	wp_get_current_user();
	$users = $wpdb->prefix . 'users';
	$topics = $wpdb->prefix . 'symposium_topics';
	$subs = $wpdb->prefix . 'symposium_subs';
	$cats = $wpdb->prefix . 'symposium_cats';

	// Includes
	include_once('symposium_styles.php');
	include_once('symposium_functions.php');

	// Get user level
	$user_level = symposium_get_current_userlevel();
	
	// Post preview
	$snippet_length = $config->preview1;
	if ($snippet_length == '') { $snippet_length = '45'; }
	$snippet_length_long = $config->preview2;
	if ($snippet_length_long == '') { $snippet_length_long = '45'; }
		
	// Get Topic ID and Category ID for use in jQuery functions	
	if (isset($_GET['show'])) {
		$show_tid = $_GET['show']*1;
	} else {
		$show_tid = 0;
		if (isset($_POST['tid'])) { $show_tid = $_POST['tid']*1; }
	}

	$cat_id = 0;
	if (isset($_GET['cid'])) { $cat_id = $_GET['cid']; }
	if (isset($_POST['cid'])) { $cat_id = $_POST['cid']; }
	
	// Wrapper
	$html .= "<div class='symposium-wrapper'>";

		// default message
		$msg = "";
		
		// Check for delete topic (and posts/subs, admin only)
		if ( ($_GET['action'] == 'deltopic') && (current_user_can('level_10')) ) {	
			if ( symposium_safe_param($_GET['tid']) ) {
				$wpdb->query($wpdb->prepare( "DELETE FROM ".$wpdb->prefix."symposium_topics WHERE tid = %d", $_GET['tid']));
				$wpdb->query($wpdb->prepare( "DELETE FROM ".$wpdb->prefix."symposium_topics WHERE topic_parent = %d", $_GET['tid']));
				$wpdb->query($wpdb->prepare( "DELETE FROM ".$wpdb->prefix."symposium_subs WHERE tid = %d", $_GET['tid']));
			}
		}
		
		// Check for delete post (admin and owner only)
		if ($_GET['action'] == 'del') {	
			// get owner
			$post_owner = $wpdb->get_var($wpdb->prepare("SELECT topic_owner FROM ".$wpdb->prefix."symposium_topics WHERE tid = %d", $_GET['tid']));
			// delete if you can
			if ( (current_user_can('level_10')) || ($current_user->ID == $post_owner) ) {
				if ( symposium_safe_param($_GET['tid']) ) {
					$wpdb->query($wpdb->prepare("DELETE FROM ".$wpdb->prefix."symposium_topics WHERE tid = %d", $_GET['tid']));
				}
			}
		}
				
		// any page id
		if(!isset($_GET['show'])){
			$topic = '0';
		} else {
			$topic = $_GET['show'];
		}
		
		// error message?
		if ($msg != '') {
			$html .= '<div class="warning">'.$msg.'</div>';
		}
		
		// Get Topic ID if applicable
		$show = '';
		if ($_GET['show'] != '') { $show = $_GET['show']; }
		if ($_POST['show'] != '') { $show = $_POST['show']; }
		if ($tid != '') { $show = $tid; }
									
		$html .= "<div class='floatright'>";

			if (is_user_logged_in()) {
				$html .= "<a id='show_activity' class='backto label' href='javascript:void(0)'>".__("My Activity", "wp-symposium")."</a>";
				$html .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a id='show_favs' class='backto label' href='javascript:void(0)'>".__("My Favourites", "wp-symposium")."</a>";
			}

			if ($cat_id > 0) {
				if ($show != '') {
					$category_title = $wpdb->get_var($wpdb->prepare("SELECT title FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d", $cat_id));
					$html .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a class='backto label' href='".$thispage.$q."cid=".$cat_id."'>".__("Back to", "wp-symposium")." ".stripslashes($category_title)."</a>&nbsp;&nbsp;&nbsp;&nbsp;";
					$html .= "&nbsp;&nbsp;<a class='backto label' href='".get_permalink()."'>".__("Back to Forum", "wp-symposium")."</a>";
				} else {
					$html .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a class='backto label' href='".get_permalink()."'>".__("Back to Forum", "wp-symposium")."</a>";
				}
			}
			
		$html .= "</div>";

		if ($show != '' && $config->sharing != '' ) {
	
			$html .= "<div id='share_link' class='floatright'>";

			// Sharing icons
				$pageURL = $thispage."?cid=".$topic."%26show=".$show;
				$title = get_bloginfo();
	
				// MySpace
				if (!(strpos($config->sharing, "ms") === FALSE)) {
					$html .= "<div class='floatright'>";
					$html .= "<a target='_blank' title='".__('Share on MySpace', 'wp-symposium')."' href='http://www.myspace.com/Modules/PostTo/Pages/?u=".$pageURL."&t=".$title."'>";
					$html .= "<img src='".$plugin."images/myspace-icon.gif' style='height:22px; width:22px' alt='MySpace icon' /></a>";
					$html .= "</div>";
				}
				// LinkedIn
				if (!(strpos($config->sharing, "li") === FALSE)) {
					$html .= "<div class='floatright'>";
					$html .= "<a target='_blank' title='".__('Share on LinkedIn', 'wp-symposium')."' href='http://www.linkedin.com/shareArticle?mini=true&url=".$pageURL."&title=".$title."'>";
					$html .= "<img src='".$plugin."images/linkedin-icon.gif' style='height:22px; width:22px' alt='LinkedIn icon' /></a>";
					$html .= "</div>";
				}
				// Bebo
				if (!(strpos($config->sharing, "be") === FALSE)) {
					$html .= "<div class='floatright'>";
					$html .= "<a target='_blank' title='".__('Share on Bebo', 'wp-symposium')."' href='http://www.bebo.com/c/share?Url=".$pageURL."&Title=".$title."'>";
					$html .= "<img src='".$plugin."images/bebo-icon.gif' style='height:22px; width:22px' alt='Bebo icon' /></a>";
					$html .= "</div>";
				}
				// Twitter
				if (!(strpos($config->sharing, "tw") === FALSE)) {
					$html .= "<div class='floatright'>";
					$html .= "<a target='_blank' title='".__('Share on Twitter', 'wp-symposium')."' href='http://twitter.com/home?status=".$pageURL."'>";
					$html .= "<img src='".$plugin."images/twitter-icon.gif' style='height:22px; width:22px' alt='Twitter icon' /></a>";
					$html .= "</div>";
				}
				// Facebook
				if (!(strpos($config->sharing, "fb") === FALSE)) {
					$html .= "<div class='floatright'>";
					$html .= "<a target='_blank' title='".__('Share on Facebook', 'wp-symposium')."' href='http://www.facebook.com/share.php?u=".$pageURL."&t=".$title."'>";
					$html .= "<img src='".$plugin."images/facebook-icon.gif' style='height:22px; width:22px' alt='Facebook icon' /></a>";
					$html .= "</div>";
				}
				// Email
				if (!(strpos($config->sharing, "em") === FALSE)) {
					$html .= "<div class='floatright'>";
					$html .= "<a title='".__('Share via email', 'wp-symposium')."' href='mailto:%20?subject=".str_replace(" ", "%20", $title)."&body=".$pageURL."'>";
					$html .= "<img src='".$plugin."images/email-icon.gif' style='height:22px; width:22px' alt='Email icon' /></a>";
					$html .= "</div>";					
				}

				$html .= "<div id='share_label' class='floatright'>".__('Share: ', 'wp-symposium')."</div>";
				
			$html .= "</div>";

		}

	
		// SHOW FORUM ***************************************************************************************************
		$show_forum = false;
		$viewer = $config->viewer;
		if ($viewer == "Guest") { $show_forum = true; }
		if ($viewer == "Subscriber" && $user_level >= 1) { $show_forum = true; }
		if ($viewer == "Contributor" && $user_level >= 2) { $show_forum = true; }
		if ($viewer == "Author" && $user_level >= 3) { $show_forum = true; }
		if ($viewer == "Editor" && $user_level <= 4) { $show_forum = true; }
		if ($viewer == "Administrator" && $user_level >= 5) { $show_forum = true; }
		
		if ($show_forum) {
			if (is_user_logged_in()) {
				
				// Sub Menu for Logged in User
				if ($show == '') {
					
					$html .= '<input type="submit" class="button" id="new-topic-button" value="'.__("Start a New Topic", "wp-symposium").'" />';

				}
				
				// New Topic Form	
				$html .= '<div name="new-topic" id="new-topic"';
					if ($edit_new_topic == false) { $html .= ' style="display:none;"'; } 
					$html .= '>';
					$html .= '<form id="start-new-topic" onsubmit="return validate_form(this)" action="'.$dbpage.'" method="post">';
					$html .= '<div><input type="hidden" name="action" value="post">';
					$html .= '<input type="hidden" name="url" value="'.$thispage.$q.'">';
					$html .= '<input type="hidden" name="cid" value="'.$cat_id.'">';
					$html .= '<div id="new-topic-subject-label" class="new-topic-subject label">'.__("Topic Subject", "wp-symposium").'</div>';
					$html .= '<input class="new-topic-subject-input" type="text" name="new_topic_subject" value="';
					$html .= ($new_topic_subject); 
					$html .= '"></div>';
					$html .= '<div class="new-topic-subject-warning warning">'.__("Please enter a subject", "wp-symposium").'.</div>';
					$html .= '<div><div class="new-topic-subject label">'.__("First Post in Topic", "wp-symposium").'</div>';
					$html .= '<textarea class="new-topic-subject-text elastic" name="new_topic_text">';
					$html .= ($new_topic_text);
					$html .= '</textarea></div>';
					$html .= '<div class="new_topic_text-warning warning" style="display:none">'.__("Please enter a message", "wp-symposium").'</div>';
					$show_categories = $config->show_categories;
					$defaultcat = $wpdb->get_var($wpdb->prepare("SELECT cid FROM ".$cats." WHERE defaultcat = 'on'"));
					if ($show_categories == "on") {
						$html .= '<div class="new-topic-category label">'.__("Select a Category", "wp-symposium").': ';
						if (current_user_can('level_10')) {
							$categories = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix.'symposium_cats ORDER BY listorder');			
						} else {
							$categories = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix.'symposium_cats WHERE allow_new = "on" ORDER BY listorder');			
						}
						if ($categories) {
							$html .= '<select name="new_topic_category" id="new_topic_category">';
							foreach ($categories as $category) {
								$html .= '<option value='.$category->cid;
								if ($cat_id > 0) {
									if ($category->cid == $cat_id) { $html .= " SELECTED"; }
								} else {
									if ($category->cid == $defaultcat) { $html .= " SELECTED"; }
								}
								$html .= '>'.stripslashes($category->title).'</option>';
							}
							$html .= '</select>';
						}
						$html .= '</div>';
					} else {
						$html .= '<input type="hidden" name="new_topic_category" value="0" />';
					}
					$html .= '<div class="emailreplies label"><input type="checkbox" name="new_topic_subscribe"';
					if ($new_topic_subscribe != '') { $html .= 'checked'; } 
					$html .= '> '.__("Email me when I get any replies", "wp-symposium").'</div>';
					$html .= '<input type="submit" class="button" style="float: left" value="'.__("Post", "wp-symposium").'" />';
					$html .= '</form>';
					$html .= '<input id="cancel_post" type="submit" class="button clear" onClick="javascript:void(0)" value="'.__("Cancel", "wp-symposium").'" />';
					
				$html .= '</div>';
					
			} else {
		
				$html .= __("Until you login, you can only view the forum.", "wp-symposium");
				if ($config->use_wp_login == "on") {
					$html .= " <a href=".wp_login_url( get_permalink() )." class='simplemodal-login' title='".__("Login", "wp-symposium")."'>".__("Login", "wp-symposium").".</a>";
				} else {
					$html .= ' <a href="'.$config->custom_login_url.'?redirect_to='.get_permalink().'">'.__("Login", "wp-symposium").'.</a>';
				}
				$html .= "<br />";
		
			}
		
			if ($show == '') {
						
				// Show Forum ***************************************************************************************************
				
				// Forum Subscribe
				if (is_user_logged_in()) {
					
					$send_summary = $config->send_summary;
					if ($send_summary == "on") {
						$forum_digest = get_symposium_meta($current_user->ID, 'forum_digest');
						$html .= "<div class='symposium_subscribe_option label'>";
						$html .= "<input type='checkbox' id='symposium_digest' name='symposium_digest'";
						if ($forum_digest == 'on') { $html .= ' checked'; } 
						$html .= "> ".__("Receive digests via email", "wp-symposium");
						$html .= "</div>";
					}
					if ($cat_id > 0) {
						$subscribed_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$subs." WHERE tid = 0 AND cid = %d AND uid = %d", $cat_id, $current_user->ID));
						$html .= "<div class='symposium_subscribe_option label'>";
						$html .= "<input type='checkbox' id='symposium_subscribe' name='symposium_subscribe'";
						if ($subscribed_count > 0) { $html .= ' checked'; } 
						$html .= "> ".__("Receive emails when there are new topics posted", "wp-symposium");
						$html .= "</div>";
					}

				}	
			  
				// Start of table
				$html .= '<div id="symposium_table">';
			
				// Top level (categories)
				$use_categories = $config->show_categories;
				
				// If using categories and no category selected, show top level of forum
				if ( ($use_categories == "on") && ($cat_id == 0) ) {
		
					$html .= "<div class='table_header'>";
					$html .= "<div class='table_topic' style='margin-left:3px;'>".__("Category", "wp-symposium")."<div style='clear:both;'></div></div>";
					$html .= "</div>";
					
					$categories = $wpdb->get_results("SELECT * FROM ".$cats." ORDER BY listorder");
					
					$num_cats = $wpdb->num_rows;
					$cnt = 0;
					foreach($categories as $category) {
						$cnt++;
						if ($cnt&1) {
							$html .= '<div style="border-radius:0px;-moz-border-radius:0px" class="row ';
							if ($cnt == $num_cats) { $html .= ' round_bottom_left round_bottom_right'; }
							$html .= '">';
						} else {
							$html .= '<div style="border-radius:0px;-moz-border-radius:0px" class="row_odd ';
							if ($cnt == $num_cats) { $html .= ' round_bottom_left round_bottom_right'; }
							$html .= '">';
						}
							// Last Topic
							$last_topic = $wpdb->get_row($wpdb->prepare("
								SELECT tid, topic_subject, topic_approved, topic_post, topic_date, topic_owner, topic_sticky, topic_parent, display_name, topic_category 
								FROM ".$topics." INNER JOIN ".$users." ON ".$topics.".topic_owner = ".$users.".ID 
								WHERE (topic_approved = 'on' OR topic_owner = %d) AND topic_parent = 0 AND topic_category = %d ORDER BY topic_date DESC", $current_user->ID, $category->cid)); 
							$html .= "<div class='row_startedby' style='float:right;'>";
							if ($last_topic) {
								$reply = $wpdb->get_row($wpdb->prepare("
									SELECT tid, topic_subject, topic_approved, topic_post, topic_owner, topic_date, display_name, topic_category 
									FROM ".$topics." INNER JOIN ".$users." ON ".$topics.".topic_owner = ".$users.".ID 
									WHERE (topic_approved = 'on' OR topic_owner = %d) AND topic_parent = %d ORDER BY topic_date DESC", $current_user->ID, $last_topic->tid)); 
												
									if ($reply) {
										$html .= "<div class='avatar avatar_last_topic'>";
											$html .= get_user_avatar($reply->topic_owner, 32);
										$html .= "</div>";
										$html .= symposium_profile_link($reply->topic_owner)." ".__("replied to", "wp-symposium")." ";
										$subject = symposium_bbcode_remove($last_topic->topic_subject);
										$html .= '<a class="backto row_link_topic" href="'.$thispage.symposium_permalink($last_topic->tid, "topic").$q.'cid='.$last_topic->topic_category.'&show='.$last_topic->tid.'">'.stripslashes($subject).'</a> ';
										$html .= symposium_time_ago($reply->topic_date).".";
										if ($reply->topic_approved != 'on') { $html .= " <em>[".__("pending approval", "wp-symposium")."]</em>"; }
									} else {
										$html .= "<div class='avatar avatar_last_topic'>";
											$html .= get_user_avatar($last_topic->topic_owner, 32);
										$html .= "</div>";
										$html .= symposium_profile_link($last_topic->topic_owner)." ".__("started", "wp-symposium")." ";
										$subject = symposium_bbcode_remove($last_topic->topic_subject);
										$html .= '<a class="backto row_link_topic" href="'.$thispage.symposium_permalink($last_topic->tid, "topic").$q.'cid='.$last_topic->topic_category.'&show='.$last_topic->tid.'">'.stripslashes($subject).'</a> ';
										$html .= symposium_time_ago($last_topic->topic_date).".";
									}
		
							}
							$html .= "</div>";
							
							// Posts
							$html .= "<div class='row_views'>";
							$post_count = $wpdb->get_var($wpdb->prepare("SELECT count(*) FROM ".$topics." t INNER JOIN ".$topics." u ON u.topic_parent = t.tid WHERE t.topic_parent = 0 AND (t.topic_approved = 'on' OR t.topic_owner = %d) AND t.topic_category = %d", $current_user->ID, $category->cid));
	
							if ($post_count) { 
								$html .= "<div class='row_link post_count' style='color:".$text_color.";'>".$post_count."</div>";
									$html .= "<div style='color:".$text_color.";' class='post_count_label'>";
									if ($post_count > 1) {
										$html .= __("POSTS", "wp-symposium");
									} else {
										$html .= __("POST", "wp-symposium");
									}
									$html .= "</div>";
							}
							$html .= "</div>";
	
							// Topic Count
							$topic_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$topics." WHERE (topic_approved = 'on' OR topic_owner = %d) AND topic_parent = 0 AND topic_category = %d", $current_user->ID, $category->cid));
							$html .= "<div class='row_topic row_replies'>";
								$html .= "<div class='row_link post_count' style='color:".$text_color.";'>".$topic_count."</div>";
								$html .= "<div style='color:".$text_color.";' class='post_count_label'>";
								if ($topic_count != 1) {
									$html .= __("TOPICS", "wp-symposium");
								} else {
									$html .= __("TOPIC", "wp-symposium");
								}
								$html .= "</div>";
							$html .= "</div>";
	
							// Category title
							$html .= '<div class="row_topic">';
							$html .= '<a class="backto row_link" href="'.$thispage.symposium_permalink($category->cid, "category").$q.'cid='.$category->cid.'">'.stripslashes($category->title).'</a>';
							$subscribed = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$subs." WHERE cid = %d AND uid = %d", $category->cid, $current_user->ID));
							if ($subscribed > 0) { $html .= ' <img src="'.$plugin.'images/orange-tick.gif" alt="'.__('Subscribed', 'wp-symposium').'" />'; } 
							$html .= '</div>';
	
							// Separator
							$html .= "<div class='sep'></div>";											

	
						$html .= "</div>"; // Row in the table
	
					}
		
		
				}
				
				// If not using categories OR a category has been selected
				if ( ($use_categories != "on") || ($cat_id > 0) ) {
		
					$html .= "<div class='table_header'>";
					if ($use_categories == "on") {
						$category_title = $wpdb->get_var($wpdb->prepare("SELECT title FROM ".$cats." WHERE cid = %d", $cat_id));
						$html .= "<div class='table_topic'><div style='margin:4px'><a style='color:".$categories_color.";' href='".get_permalink()."'>".stripslashes($category_title)."</a></div></div>";
					} else {
						$html .= "<div class='table_topic'>".stripslashes(__("TOPIC", "wp-symposium"))."</div>";
					}
					$html .= "</div><div style='clear:both;'></div>";
	
					// Get Forums	
					if ($use_categories == "on") {
							
						$query = $wpdb->get_results($wpdb->prepare("
							SELECT tid, topic_subject, topic_approved, topic_post, topic_owner, topic_date, display_name, topic_sticky, allow_replies 
							FROM ".$topics." INNER JOIN ".$users." ON ".$topics.".topic_owner = ".$users.".ID 
							WHERE (topic_approved = 'on' OR topic_owner = %d) AND topic_parent = 0 AND topic_category = %d ORDER BY topic_sticky DESC, topic_date DESC", $current_user->ID, $cat_id)); 
							
					} else {
						
						$query = $wpdb->get_results($wpdb->prepare("
							SELECT tid, topic_subject, topic_approved, topic_post, topic_owner, topic_date, display_name, topic_sticky, allow_replies 
							FROM ".$topics." INNER JOIN ".$users." ON ".$topics.".topic_owner = ".$users.".ID 
							WHERE (topic_approved = 'on' OR topic_owner = %d) AND topic_parent = 0 ORDER BY topic_sticky DESC, topic_date DESC", $current_user->ID)); 
							
					}
		
					$num_topics = $wpdb->num_rows;
					
					// Favourites
					$favs = get_symposium_meta($current_user->ID, 'forum_favs');
										
					if ($query) {
					
						$row_cnt=0;
					
						// For every topic in this category (or all if not using categories)
						foreach ($query as $topic) {
						
							$row_cnt++;
							
							$replies = $wpdb->get_var($wpdb->prepare("SELECT COUNT(tid) FROM ".$topics." WHERE (topic_approved = 'on' OR topic_owner = %d) AND topic_parent = %d", $current_user->ID, $topic->tid));
							$reply_views = $wpdb->get_var($wpdb->prepare("SELECT sum(topic_views) FROM ".$topics." WHERE (topic_approved = 'on' OR topic_owner = %d) AND tid = %d", $current_user->ID, $topic->tid));
									
							if ($row_cnt&1) {
								$html .= '<div style="border-radius:0px;-moz-border-radius:0px" class="row ';
								if ($row_cnt == $num_topics) { $html .= ' round_bottom_left round_bottom_right'; }
							} else {
								$html .= '<div style="border-radius:0px;-moz-border-radius:0px" class="row_odd ';
								if ($row_cnt == $num_topics) { $html .= ' round_bottom_left round_bottom_right'; }
							}
							$closed_word = strtolower($config->closed_word);
							if ( strpos(strtolower($topic->topic_subject), "[".$closed_word."]") > 0) {
								$color_check = ' transparent';
							} else {
								$color_check = '';
							}
							$html .= $color_check.'">';

								// Started by/Last Reply
								$html .= "<div class='row_startedby' style='float:right;'>";
								$last_post = $wpdb->get_row($wpdb->prepare("
									SELECT tid, topic_subject, topic_approved, topic_post, topic_owner, topic_date, display_name, topic_sticky 
									FROM ".$topics." INNER JOIN ".$users." ON ".$topics.".topic_owner = ".$users.".ID 
									WHERE (topic_approved = 'on' OR topic_owner = %d) AND topic_parent = %d ORDER BY tid DESC", $current_user->ID, $topic->tid)); 
								if ( $last_post ) {
									$html .= "<div class='avatar avatar_last_topic'>";
										$html .= get_user_avatar($last_post->topic_owner, 32);
									$html .= "</div>";
									$html .= __("Last reply by", "wp-symposium")." ".symposium_profile_link($last_post->topic_owner);
									$html .= " ".symposium_time_ago($last_post->topic_date).".";
									$post = stripslashes($last_post->topic_post);
									if ( strlen($post) > $snippet_length_long ) { $post = substr($post, 0, $snippet_length_long)."..."; }
									$post = symposium_bbcode_remove($post);
									$html .= "<br /><span class='row_topic_text'>".$post."</span>";
									if ($last_post->topic_approved != 'on') { $html .= " <em>[".__("pending approval", "wp-symposium")."]</em>"; }
								} else {
									$html .= "<div class='avatar avatar_last_topic'>";
										$html .= get_user_avatar($topic->topic_owner, 32);
									$html .= "</div>";
									$html .= __("Started by", "wp-symposium")." ".symposium_profile_link($topic->topic_owner);
									$html .= " ".symposium_time_ago($topic->topic_date).".";
								}
								$html .= "</div>";
								
								// Views
								$html .= "<div class='row_views'>";
								if ($reply_views) { 
									$html .= "<div class='row_link post_count' style='color:".$text_color.";'>".$reply_views."</div>";
									$html .= "<div style='color:".$text_color.";' class='post_count_label'>".__("VIEWS", "wp-symposium")."</div>";
								}
								$html .= "</div>";
								
								// Replies
								$html .= "<div class='row_replies'>";
								$html .= "<div class='row_link post_count' style='color:".$text_color.";'>".$replies."</div>";
								$html .= "<div style='color:".$text_color.";' class='post_count_label'>";
								if ($replies != 1) {
									$html .= __("REPLIES", "wp-symposium");
								} else {
									$html .= __("REPLY", "wp-symposium");
								}
								$html .= "</div>";
								$html .= "</div>";
	
								// Topic Title		
								$html .= "<div class='row_topic'>";

								if (strpos($favs, "[".$topic->tid."]") === FALSE) { } else {
									$html .= "<img src='".$plugin."images/star-on.gif' class='floatleft' style='height:12px; width:12px; margin-right:4px;' />";						
								}								
								
								$subject = symposium_bbcode_remove($topic->topic_subject);
								$html .= '<div class="row_link_div"><a href="'.$thispage.symposium_permalink($topic->tid, "topic").$q.'cid='.$cat_id.'&show='.$topic->tid.'" class="backto row_link">'.stripslashes($subject).'</a>';
								if ($topic->topic_approved != 'on') { $html .= " <em>[".__("pending approval", "wp-symposium")."]</em>"; }
								if (is_user_logged_in()) {
									$is_subscribed = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$subs." WHERE tid = %d AND uid = %d", ".$topic->tid.", $current_user->ID));
									if ($is_subscribed > 0) { $html .= ' <img src="'.$plugin.'images/orange-tick.gif" alt="Subscribed" />'; } 
								}
								if ($topic->allow_replies != 'on') { $html .= ' <img src="'.$plugin.'images/padlock.gif" alt="Replies locked" />'; } 
								if ($topic->topic_sticky) { $html .= ' <img src="'.$plugin.'images/pin.gif" alt="Sticky Topic" />'; } 
								
								// Delete link if applicable
								if (current_user_can('level_10')) {
									$html .= " <a class='delete_post' href='".$thispage.$q."show=".$show."&cid=".$cat_id."&action=deltopic&tid=".$topic->tid."'>".__("Delete", "wp-symposium")."</a>";
								}
	
								$html .= "</div>";
								$post = stripslashes($topic->topic_post);
								$post = symposium_bbcode_remove($post);
								if ( strlen($post) > $snippet_length ) { $post = substr($post, 0, $snippet_length)."..."; }
								$html .= "<span class='row_topic_text'>".$post."</span>";
								$html .= "</div>";
															
								// Separator
								$html .= "<div class='sep'></div>";		
								
							$html .= "</div>"; // End of Table Row
							
						}
					
					} else {
					
						$html .= "<div style='padding: 6px'>".__("No topics started yet", "wp-symposium").".</div>";
					
					}
	
				}
			
				$html .= "</div>"; // End of table
				
			} else {
				
				// Show topic ***************************************************************************************************
				
				$post = $wpdb->get_row("
					SELECT tid, topic_subject, topic_approved, topic_post, topic_started, display_name, topic_sticky, topic_owner 
					FROM ".$topics." INNER JOIN ".$users." ON ".$topics.".topic_owner = ".$users.".ID 
					WHERE (topic_approved = 'on' OR topic_owner = ".$current_user->ID.") AND tid = ".$show);
					
				if ($post) {
				
					// Edit Form
					$html .= '<div id="edit-topic-div" class="shadow">';
						$html .= '<div class="new-topic-subject label">'.__("Topic Subject", "wp-symposium").'</div>';
						$html .= '<div id="'.$post->tid.'" class="edit-topic-tid"></div>';
						$html .= '<div id="" class="edit-topic-parent"></div>';
						$html .= '<input class="new-topic-subject-input" id="edit_topic_subject" type="text" name="edit_topic_subject" value="">';
						$html .= '<div class="new-topic-subject label">'.__("Topic Text", "wp-symposium").'</div>';
						$html .= '<textarea class="new-topic-subject-text" id="edit_topic_text" name="edit_topic_text"></textarea>';
						$html .= '<div id="new-category-div" style="float:left">'.__("Move Category", "wp-symposium").': <select name="new-category" id="new-category" style="width: 200px">';
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
						$html .= '<div style="float:right; margin-right:15px;">';
						$html .= '<input type="submit" class="button edit_topic_submit" value="'.__("Update", "wp-symposium").'" />';
						$html .= '<input type="submit" class="button edit_topic_cancel" value="'.__("Cancel", "wp-symposium").'" />';
						$html .= '</div>';
					$html .= '</div>';
					
					$html .= "<div id='starting-post'>";
					
					if ( ($post->topic_owner == $current_user->ID) || (current_user_can('level_10')) ) {
						$html .= "<div id='edit-this-topic' class='edit_topic edit label' style='cursor:pointer'>".__("Edit", "wp-symposium")."</div>";
					}
	
					$html .= "<div id='top_of_first_post' style='height:80px'>";
						
						$html .= "<div class='avatar' style='margin-bottom:0px; margin-top:6px;'>";
							$html .= get_user_avatar($post->topic_owner, 64);
						$html .= "</div>";
						
						$html .= "<div class='topic-post-header-with-fav'>";
						
							$html .= "<div class='topic-post-header'>";
								$post_text = symposium_bbcode_replace(stripslashes($post->topic_subject));
								$html .= stripslashes($post_text);
						
								if ($post->topic_approved != 'on') { $html .= " <em>[".__("pending approval", "wp-symposium")."]</em>"; }

								// Favourites
								if ($show != '') {
									if (strpos(get_symposium_meta($current_user->ID, 'forum_favs'), "[".$show."]") === FALSE) { 
										$html .= "<img title='".__("Click to add to favourites", "wp-symposium")."' id='fav_link' src='".$plugin."images/star-off.gif' class='floatleftx' style='height:22px; width:22px; cursor:pointer;' alt='".__("Click to add to favourites", "wp-symposium")."' />";						
									} else {
										$html .= "<img title='".__("Click to remove to favourites", "wp-symposium")."' id='fav_link' src='".$plugin."images/star-on.gif' class='floatleftx' style='height:22px; width:22px; cursor:pointer;' alt='".__("Click to remove to favourites", "wp-symposium")."' />";						
									}
								}
														
								$html .= "<div class='started-by'>".__("Started by", "wp-symposium")." ".symposium_profile_link($post->topic_owner)." ".symposium_time_ago($post->topic_started)."</div>";
								$html .= "</div>";

							$html .= "</div>";					
						

						$html .= "</div><div style='clear:both'></div>";

						$post_text = symposium_make_url(stripslashes($post->topic_post));
						$post_text = symposium_bbcode_replace($post_text);
						$html .= "<div class='topic-post-post'>".str_replace(chr(13), "<br />", $post_text)."</div>";
																			
					$html .= "</div>";
		
					// Update views
					if ($user_level == 5) {
						if ($config->include_admin == "on") { 
							$wpdb->query( $wpdb->prepare("UPDATE ".$topics." SET topic_views = topic_views + 1 WHERE tid = %d", $post->tid) );
						}
					} else {
						$wpdb->query( $wpdb->prepare("UPDATE ".$topics." SET topic_views = topic_views + 1 WHERE tid = %d", $post->tid) );
					}
										
					// Subscribe, Sticky and Allow Replies
					if (is_user_logged_in()) {
		
						$html .= "<br /><div class='floatleft label'>";
						$html .= "<form action='symposium.php'>";
						$html .= "<input type='checkbox' id='subscribe' name='subscribe'";
						$subscribed_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$subs." WHERE tid = %d and uid = %d", $show, $current_user->ID));
						if ($subscribed_count > 0) { $html .= ' checked'; } 
						$html .= "> ".__("Receive emails when there are replies to this topic", "wp-symposium");
						if (current_user_can('level_10')) {
							$html .= "&nbsp;&nbsp;&nbsp;<input type='checkbox' id='sticky' name='sticky'";
							if ($post->topic_sticky > 0) { $html .= ' checked'; }
							$html .= "> ".__("Topic is Sticky", "wp-symposium");
							$html .= "&nbsp;&nbsp;&nbsp;<input type='checkbox' id='replies' name='replies'";
							$allow_replies = $wpdb->get_var($wpdb->prepare("SELECT allow_replies FROM ".$wpdb->prefix."symposium_topics WHERE tid = %d", $post->tid));
							if ($allow_replies == "on") { $html .= ' checked'; }
							$html .= "> ".__("Replies allowed", "wp-symposium");
						}
						$html .= "</form>";
						
						$html .= "</div>";
					} else {
						$html .= "<br />";
					}
				
				}
		
				// Replies
				$sql = "SELECT tid, topic_subject, topic_approved, topic_post, topic_date, topic_owner, display_name, ID
					FROM ".$topics." INNER JOIN ".$users." ON ".$topics.".topic_owner = ".$users.".ID 
					WHERE (topic_approved = 'on' OR topic_owner = %d) AND topic_parent = %d ORDER BY tid";
					
				if ($config->oldest_first != "on") { $sql .= " DESC"; }
				
				$child_query = $wpdb->get_results($wpdb->prepare($sql, $current_user->ID, $show));
		
				$html .= "<div id='child-posts'>";

					if ($child_query) {
			
						foreach ($child_query as $child) {
			
							$html .= "<div class='child-reply'>";
								if ( ($child->topic_owner == $current_user->ID) || (current_user_can('level_10')) ) {
									$html .= "<div style='float:right;padding-top:6px;'><a class='delete_post' href='".$thispage.$q."show=".$show."&cid=".$cat_id."&action=del&tid=".$child->tid."'>".__("Delete", "wp-symposium")."</a></div>";
									$html .= "<div id='".$child->tid."' class='edit-child-topic edit_topic edit label' style='cursor:pointer;'>".__("Edit", "wp-symposium")."&nbsp;&nbsp;|&nbsp;&nbsp;</div>";
								}
								$html .= "<div class='avatar'>";
									$html .= get_user_avatar($child->ID, 64);
								$html .= "</div>";
								$html .= "<div class='started-by'>".symposium_profile_link($child->topic_owner)." ".__("replied", "wp-symposium")." ".symposium_time_ago($child->topic_date)."...";
								$html .= "</div>";
								$html .= "<div id='child_".$child->tid."' class='child-reply-post'>";
									$reply_text = symposium_make_url(stripslashes($child->topic_post));
									$reply_text = symposium_bbcode_replace($reply_text);
									$reply_text = str_replace(chr(10), "<br />", $reply_text);
									$reply_text = str_replace(chr(13), "<br />", $reply_text);
									$html .= "<p>".$reply_text;
									if ($child->topic_approved != 'on') { $html .= " <em>[".__("pending approval", "wp-symposium")."]</em>"; }
									$html .= "</p>";
								$html .= "</div>";
							$html .= "</div>";
		
							// Separator
							$html .= "<div class='sep'></div>";						
			
						}
						
				} else {
					
					$html .= "<div class='child-reply'>";
					$html .= __("No replies posted yet.", "wp-symposium");
					$html .= "</div>";
					$html .= "<div class='sep'></div>";						
					
				}			

				$html .= "</div>";
				
				// Quick Reply
				if (is_user_logged_in()) {
					$html .= '<div id="reply-topic-bottom" name="reply-topic-bottom">';
					if ($wpdb->get_var($wpdb->prepare("SELECT allow_replies FROM ".$wpdb->prefix."symposium_topics WHERE tid = %d", $post->tid)) == "on")
					{
						$html .= '<input type="hidden" id="symposium_reply_tid" value="'.$show.'">';
						$html .= '<input type="hidden" id="symposium_reply_cid" value="'.$cat_id.'">';
						$html .= '<div class="reply-topic-subject label">'.__("Reply to this Topic", "wp-symposium").'</div>';
						$html .= '<textarea class="reply-topic-text elastic" id="symposium_reply_text"></textarea>';
						$html .= '<div class="quick-reply-warning warning" style="display:none">'.__("Please enter a message", "wp-symposium").'</div>';
						$html .= '<input type="submit" id="quick-reply-warning" class="button" style="float: left" value="'.__("Reply", "wp-symposium").'" />';
					}				
					$html .= '</div>';
				}
			}
		
		} else {
			
			if ($viewer == "Subscriber") {
				$html .= "<p>".__("Sorry, this forum can only be used by registered members. :(", "wp-symposium")."</p>";
			} else {
				$html .= "<p>".sprintf(__("Sorry, the minimum user level for this forum is %s. :(", "wp-symposium"), $viewer)."</p>";
			}
		}			
	
		
	// End Wrapper
	$html .= "</div>";
	
	$html .= "<div style='clear: both'></div>";
	
	// Send HTML
	return $html;

}

/* ====================================================== SET SHORTCODE ====================================================== */
add_shortcode('symposium-forum', 'symposium_forum');  



?>
