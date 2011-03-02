<?php

include_once('../../../../wp-config.php');
//include_once('../../../../wp-includes/wp-db.php');
//include_once('../symposium_functions.php');

global $wpdb, $current_user;
wp_get_current_user();

// Get Forum ****************************************************************

if ($_POST['action'] == 'getForum') {
	
	$cat_id = $_POST['cat_id'];
	$config = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."symposium_config"));

	// Post preview
	$snippet_length = $config->preview1;
	if ($snippet_length == '') { $snippet_length = '45'; }
	$snippet_length_long = $config->preview2;
	if ($snippet_length_long == '') { $snippet_length_long = '45'; }
	
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

	// Options above forum table
	$html .= "<div class='floatright'>";

		$html .= "<a id='show_search' class='label' href='javascript:void(0)'>".__("Search", "wp-symposium")."</a>";

		if (is_user_logged_in()) {
			$html .= "&nbsp;&nbsp;&nbsp;&nbsp;<a id='show_activity' class='label' href='javascript:void(0)'>".__("My Activity", "wp-symposium")."</a>";
			$html .= "&nbsp;&nbsp;&nbsp;&nbsp;<a id='show_favs' class='label' href='javascript:void(0)'>".__("My Favourites", "wp-symposium")."</a>";
		}

		if ($cat_id > 0) {
			if ($show != '') {
				$category_title = $wpdb->get_var($wpdb->prepare("SELECT title FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d", $cat_id));
				$html .= "&nbsp;&nbsp;&nbsp;&nbsp;<a class='backto label' href='javascript:void(0)'>".__("Back to", "wp-symposium")." ".stripslashes($category_title)."</a>";
			}
			$html .= "&nbsp;&nbsp;&nbsp;<a class='backto label' href='javascript:void(0)'>".__("Back to Forum", "wp-symposium")."</a>";
		}
		
	$html .= "</div>";

	if ($config->sharing != '' ) {

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
	
	// Start of table
	$html .= '<div id="symposium_table">';

		// Show child categories in this category ++++++++++++++++++++++++++++++++++++++++++++++++++

		$sql = $wpdb->prepare("SELECT * FROM ".$wpdb->prefix."symposium_cats WHERE cat_parent = %d ORDER BY listorder", $cat_id);
		$categories = $wpdb->get_results($sql);

		if ($categories) {
			
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
						FROM ".$wpdb->prefix."symposium_topics t INNER JOIN ".$wpdb->base_prefix."users u ON t.topic_owner = u.ID 
						WHERE (topic_approved = 'on' OR topic_owner = %d) AND topic_parent = 0 AND topic_category = %d ORDER BY topic_date DESC", $current_user->ID, $category->cid)); 
					$html .= "<div class='row_startedby' style='float:right;'>";
					if ($last_topic) {
						$reply = $wpdb->get_row($wpdb->prepare("
							SELECT tid, topic_subject, topic_approved, topic_post, topic_owner, topic_date, display_name, topic_category 
							FROM ".$wpdb->prefix."symposium_topics t INNER JOIN ".$wpdb->base_prefix."users u ON t.topic_owner = u.ID 
							WHERE (topic_approved = 'on' OR topic_owner = %d) AND topic_parent = %d ORDER BY topic_date DESC", $current_user->ID, $last_topic->tid)); 
									
							if ($reply) {
								$html .= "<div class='avatar avatar_last_topic'>";
									$html .= get_avatar($reply->topic_owner, 32);
								$html .= "</div>";
								$html .= symposium_profile_link($reply->topic_owner)." ".__("replied to", "wp-symposium")." ";
								$subject = symposium_bbcode_remove($last_topic->topic_subject);
								$html .= '<a class="backto row_link_topic" href="'.$thispage.symposium_permalink($last_topic->tid, "topic").$q.'cid='.$last_topic->topic_category.'&show='.$last_topic->tid.'">'.stripslashes($subject).'</a> ';
								$html .= symposium_time_ago($reply->topic_date).".";
								if ($reply->topic_approved != 'on') { $html .= " <em>[".__("pending approval", "wp-symposium")."]</em>"; }
							} else {
								$html .= "<div class='avatar avatar_last_topic'>";
									$html .= get_avatar($last_topic->topic_owner, 32);
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
					$post_count = $wpdb->get_var($wpdb->prepare("SELECT count(*) FROM ".$wpdb->prefix."symposium_topics t INNER JOIN ".$wpdb->prefix."symposium_topics u ON u.topic_parent = t.tid WHERE t.topic_parent = 0 AND (t.topic_approved = 'on' OR t.topic_owner = %d) AND t.topic_category = %d", $current_user->ID, $category->cid));

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
					$topic_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix."symposium_topics WHERE (topic_approved = 'on' OR topic_owner = %d) AND topic_parent = 0 AND topic_category = %d", $current_user->ID, $category->cid));
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
					$subscribed = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix."symposium_subs WHERE cid = %d AND uid = %d", $category->cid, $current_user->ID));
					if ($subscribed > 0) { $html .= ' <img src="'.$plugin.'images/orange-tick.gif" alt="'.__('Subscribed', 'wp-symposium').'" />'; } 
					$html .= '</div>';

					// Separator
					$html .= "<div class='sep'></div>";											


				$html .= "</div>"; // Row in the table

			}
			
		}
		
	$html .= '</div>';
	$html .= '<div id="symposium_table">';		
		
		// Show topics in this category ++++++++++++++++++++++++++++++++++++++++++++++++++

		$query = $wpdb->get_results($wpdb->prepare("
			SELECT tid, topic_subject, topic_approved, topic_post, topic_owner, topic_date, display_name, topic_sticky, allow_replies 
			FROM ".$wpdb->prefix."symposium_topics t INNER JOIN ".$wpdb->base_prefix."users u ON t.topic_owner = u.ID 
			WHERE (topic_approved = 'on' OR topic_owner = %d) AND topic_category = %d AND topic_parent = 0 ORDER BY topic_sticky DESC, topic_date DESC", $current_user->ID, $cat_id)); 
			
		$num_topics = $wpdb->num_rows;
	
		// Favourites
		$favs = get_symposium_meta($current_user->ID, 'forum_favs');
		
		$cnt = 0;
						
		if ($query) {
	
			// For every topic in this category 
			foreach ($query as $topic) {
						
				$cnt++;
			
				$replies = $wpdb->get_var($wpdb->prepare("SELECT COUNT(tid) FROM ".$wpdb->prefix."symposium_topics WHERE (topic_approved = 'on' OR topic_owner = %d) AND topic_parent = %d", $current_user->ID, $topic->tid));
				$reply_views = $wpdb->get_var($wpdb->prepare("SELECT sum(topic_views) FROM ".$wpdb->prefix."symposium_topics WHERE (topic_approved = 'on' OR topic_owner = %d) AND tid = %d", $current_user->ID, $topic->tid));
					
				if ($cnt&1) {
					$html .= '<div style="border-radius:0px;-moz-border-radius:0px" class="row ';
					if ($cnt == $num_topics) { $html .= ' round_bottom_left round_bottom_right'; }
				} else {
					$html .= '<div style="border-radius:0px;-moz-border-radius:0px" class="row_odd ';
					if ($cnt == $num_topics) { $html .= ' round_bottom_left round_bottom_right'; }
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
						FROM ".$wpdb->prefix."symposium_topics t INNER JOIN ".$wpdb->base_prefix."users u ON t.topic_owner = u.ID 
						WHERE (topic_approved = 'on' OR topic_owner = %d) AND topic_parent = %d ORDER BY tid DESC", $current_user->ID, $topic->tid)); 
					if ( $last_post ) {
						$html .= "<div class='avatar avatar_last_topic'>";
							$html .= get_avatar($last_post->topic_owner, 32);
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
							$html .= get_avatar($topic->topic_owner, 32);
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
				
					if (strpos($favs, "[".$topic->tid."]") === FALSE ) { } else {
						$html .= "<img src='".$plugin."images/star-on.gif' class='floatleft' style='height:12px; width:12px; margin-right:4px;' />";						
					}								
				
					$subject = symposium_bbcode_remove($topic->topic_subject);
					$html .= '<div class="row_link_div"><a href="'.$thispage.symposium_permalink($topic->tid, "topic").$q.'cid='.$cat_id.'&show='.$topic->tid.'" class="backto row_link">'.stripslashes($subject).'</a>';
					if ($topic->topic_approved != 'on') { $html .= " <em>[".__("pending approval", "wp-symposium")."]</em>"; }
					if (is_user_logged_in()) {
						$is_subscribed = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix."symposium_subs WHERE tid = %d AND uid = %d", ".$topic->tid.", $current_user->ID));
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

	$html .= "</div>"; // End of table
	
	echo $html;
	
}

// Reply to Topic ****************************************************************
if ($_POST['action'] == 'reply') {
	
	$tid = $_POST['tid'];
	$cat_id = $_POST['cid'];
	$reply_text = $_POST['reply_text'];
	
	$wpdb->show_errors;
	
	if ($reply_text != '') {
	
		if (is_user_logged_in()) {

			// Work out link to this page, dealing with permalinks or not
			$forum_url = symposium_get_url('forum');
			if ($forum_url[strlen($forum_url)-1] != '/') { $forum_url .= '/'; }
			if (isset($_GET[page_id]) && $_GET[page_id] != '') {
				// No Permalink
				$q = "&";
			} else {
				$q = "?";
			}
			
			// Check for moderation
			$moderation = $wpdb->get_var($wpdb->prepare("SELECT moderation FROM ".$wpdb->prefix.'symposium_config'));
			if ($moderation == "on") {
				$topic_approved = "";
			} else {
				$topic_approved = "on";
			}
			
			// Invalidate HTML
			$reply_text = str_replace("<", "&lt;", $reply_text);
			$reply_text = str_replace(">", "&gt;", $reply_text);
			
			// Store new topic in post					
			$wpdb->query( $wpdb->prepare( "
			INSERT INTO ".$wpdb->prefix."symposium_topics
			( 	topic_subject, 
				topic_category,
				topic_post, 
				topic_date, 
				topic_started, 
				topic_owner, 
				topic_parent, 
				topic_views,
				topic_approved
			)
			VALUES ( %s, %d, %s, %s, %s, %d, %d, %d, %s )", 
	        array(
	        	'', 
	        	$cat_id,
	        	$reply_text, 
	        	date("Y-m-d H:i:s"), 
				date("Y-m-d H:i:s"), 
				$current_user->ID, 
				$tid,
				0,
				$topic_approved
	        	) 
	        ) );
	        
	        $r = $wpdb->last_query;

			// Update main topic date for freshness
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix."symposium_topics SET topic_date = NOW() WHERE tid = ".$tid) );					
			
			// Email people who want to know and prepare body
			$owner_name = $wpdb->get_var($wpdb->prepare("SELECT display_name FROM ".$wpdb->base_prefix."users WHERE ID = ".$current_user->ID));
			$parent = $wpdb->get_var($wpdb->prepare("SELECT topic_subject FROM ".$wpdb->prefix."symposium_topics WHERE tid = ".$tid));
			
			$body = "<span style='font-size:24px'>".$parent."</span><br /><br />";
			$body .= "<p>".$owner_name." ".__('replied', 'wp-symposium')."...</p>";
			$body .= "<p>".$reply_text."</p>";
			$body .= "<p>".$forum_url.$q."cid=".$cat_id."&show=".$tid."</p>";
			$body = str_replace(chr(13), "<br />", $body);
			$body = str_replace("\\r\\n", "<br />", $body);
			$body = str_replace("\\", "", $body);
			
			if ($topic_approved == "on") {
				$query = $wpdb->get_results("
					SELECT user_email
					FROM ".$wpdb->base_prefix."users u RIGHT JOIN ".$wpdb->prefix."symposium_subs ON ".$wpdb->prefix."symposium_subs.uid = u.ID 
					WHERE u.ID != ".$current_user->ID." AND tid = ".$tid);
					
				if ($query) {						
					foreach ($query as $user) {		
						symposium_sendmail($user->user_email, __('New Forum Reply', 'wp-symposium'), $body);							
					}
				}						
			} else {
				// Email admin if post needs approval
				$body = "<span style='font-size:24px; font-style:italic;'>".__("Moderation required for a reply", "wp-symposium")."</span><br /><br />".$body;
				symposium_sendmail(get_bloginfo('admin_email'), __('Moderation required for a reply', 'wp-symposium'), $body);
			}					

			echo $r;			
			exit;
			
		}
	}	
}

	
// AJAX to fetch forum activity
if ($_POST['action'] == 'getActivity') {

	$config = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."symposium_config"));

	// Work out link to this page, dealing with permalinks or not
	$thispage = symposium_get_url('forum');
	if ($thispage[strlen($thispage)-1] != '/') { $thispage .= '/'; }
	if (strpos($thispage, "?") === FALSE) { 
		$q = "?";
	} else {
		// No Permalink
		$q = "&";
	}
	
	$snippet_length = $config->preview1;
	if ($snippet_length == '') { $snippet_length = '45'; }
	
	$html = '<div id="forum_activity_div">';
	
		$html .= '<div id="forum_activity_all_new_topics">';
		
			$html .= '<div id="forum_activity_title">'.__('Recently added topics', 'wp-symposium').'</div>';
		
			// All topics started
			$sql = "SELECT t.*, u.display_name FROM ".$wpdb->prefix."symposium_topics t LEFT JOIN ".$wpdb->base_prefix."users u ON t.topic_owner = u.ID WHERE topic_parent = 0 ORDER BY topic_started DESC LIMIT 0,40";
	
			$topics = $wpdb->get_results($sql);
			if ($topics) {
				foreach ($topics as $topic) {		
					$html .= "<div class='forum_activity_new_topic_subject'><a href='".$thispage.symposium_permalink($topic->tid, "topic").$q.'cid='.$topic->topic_category.'&show='.$topic->tid."'>".symposium_bbcode_remove(stripslashes($topic->topic_subject))."</a></div>";
					$text = symposium_bbcode_remove(stripslashes($topic->topic_post));
					if ( strlen($text) > $snippet_length ) { $text = substr($text, 0, $snippet_length)."..."; }
					$html .= $text."<br />";

					$html .= "<em>".__("Started by", "wp-symposium")." ".$topic->display_name.", ".symposium_time_ago($topic->topic_started);
					
					// Replies
					$replies = $wpdb->get_results($wpdb->prepare("SELECT t.*, u.display_name FROM ".$wpdb->prefix."symposium_topics t LEFT JOIN ".$wpdb->base_prefix."users u ON t.topic_owner = u.ID WHERE topic_parent = ".$topic->tid." ORDER BY topic_date DESC"));
					if ($replies) {
						$cnt = 0;
						$dt = '';
						foreach ($replies as $reply) {
							$cnt++;
							if ($dt == '') { $dt = $reply->topic_date; }
						}
						
						if ($cnt > 0) {
							$html .= ". ".$cnt." ";
							if ($cnt == 1) 
							{ 
								$html .= __("reply", "wp-symposium");
								$html .= ", ".symposium_time_ago($dt)." by ".$reply->display_name;
							} else {
								$html .= __("replies", "wp-symposium");
								$html .= ", ".__("last one", "wp-symposium")." ".symposium_time_ago($dt)." by ".$reply->display_name;
							}
							
						}
					}	
					
					$html .= ".</em>";			
					
				}
			} else {
				$html .= "<p>".__("No topics started yet", "wp-symposium").".</p>";
			}
		
		$html .= '</div>';

		$html .= '<div id="forum_activity_new_topics">';
		
			$html .= '<div id="forum_activity_title">'.__('Forum topics you recently started', 'wp-symposium').'</div>';
		
			// Topics Started
			$sql = "SELECT * FROM ".$wpdb->prefix."symposium_topics WHERE topic_owner = ".$current_user->ID." AND topic_parent = 0 ORDER BY topic_started DESC LIMIT 0,100";
	
			$topics = $wpdb->get_results($sql);
			if ($topics) {
				foreach ($topics as $topic) {		
					$html .= "<div class='forum_activity_new_topic_subject'><a href='".$thispage.symposium_permalink($topic->tid, "topic").$q.'cid='.$topic->topic_category.'&show='.$topic->tid."'>".symposium_bbcode_remove(stripslashes($topic->topic_subject))."</a>, ".symposium_time_ago($topic->topic_date)."</div>";
					$text = symposium_bbcode_remove(stripslashes($topic->topic_post));
					if ( strlen($text) > $snippet_length ) { $text = substr($text, 0, $snippet_length)."..."; }
					$html .= $text."<br />";

					// Replies
					$replies = $wpdb->get_results($wpdb->prepare("SELECT t.*, u.display_name FROM ".$wpdb->prefix."symposium_topics t LEFT JOIN ".$wpdb->base_prefix."users u ON t.topic_owner = u.ID WHERE topic_parent = ".$topic->tid." ORDER BY tid DESC"));
					if ($replies) {
						$cnt = 0;
						$dt = '';
						$display_name = '';
						foreach ($replies as $reply) {
							$cnt++;
							if ($dt == '') { $dt = $reply->topic_date; $display_name = $reply->display_name; }
						}
						
						if ($cnt > 0) {
							$html .= "<em>".$cnt." ";
							if ($cnt == 1) 
							{ 
								$html .= __("reply", "wp-symposium");
								$html .= ", ".symposium_time_ago($dt)." by ".$display_name.".</em>";
							} else {
								$html .= __("replies", "wp-symposium");
								$html .= ", ".__("last one", "wp-symposium")." ".symposium_time_ago($dt)." by ".$display_name.".</em>";
							}
							
						}
					} else {
						$html .= "<em>".__("No replies", "wp-symposium")."</em>";
					}				
					
				}
			} else {
				$html .= __("<p>You have not started any forum topics.</p>", "wp-symposium");
			}
		
		$html .= '</div>';
		
		$html .= '<div id="forum_activity_replies">';
		
			$html .= '<div id="forum_activity_title">'.__('Forum topics you recently replied to', 'wp-symposium').'</div>';
		
			// Topics Replied to
			
			$shown = '';
			$sql = "SELECT t.*, t2.topic_subject, p.tid as parent_tid, p.topic_owner as parent_owner, p.topic_date as parent_date FROM ".$wpdb->prefix."symposium_topics t LEFT JOIN ".$wpdb->prefix."symposium_topics t2 ON t.topic_parent = t2.tid LEFT JOIN ".$wpdb->prefix."symposium_topics p ON t.topic_parent = p.tid WHERE t.topic_owner = ".$current_user->ID." AND t.topic_parent > 0 ORDER BY t.topic_date DESC LIMIT 0,75";
	
			$topics = $wpdb->get_results($sql);
			if ($topics) {
				foreach ($topics as $topic) {	
					
					if (strpos($shown, $topic->topic_parent.",") === FALSE) { 
						$html .= "<div class='forum_activity_new_topic_subject'><a href='".$thispage.symposium_permalink($topic->topic_parent, "topic").$q.'cid='.$topic->topic_category.'&show='.$topic->topic_parent."'>".symposium_bbcode_remove(stripslashes($topic->topic_subject))."</a></div>";
						$text = symposium_bbcode_remove(stripslashes($topic->topic_post));
						if ( strlen($text) > $snippet_length ) { $text = substr($text, 0, $snippet_length)."..."; }
						$html .= $text."<br />";
						$html .= "<em>";
						$html .= __("You replied", "wp_symposium")." ".symposium_time_ago($topic->topic_date);
						$last_reply = $wpdb->get_row($wpdb->prepare("SELECT t.*, u.display_name FROM ".$wpdb->prefix."symposium_topics t LEFT JOIN ".$wpdb->base_prefix."users u ON t.topic_owner = u.ID WHERE topic_parent = ".$topic->topic_parent." ORDER BY tid DESC LIMIT 0,1"));
						if ($last_reply->topic_owner != $topic->topic_owner) {
							$html .= ", ".__("last reply by", "wp-symposium")." ".$last_reply->display_name." ".symposium_time_ago($last_reply->topic_date).".";
						} else {
							$html .= ".";
						}
						$html .= "</em>";
						
						$shown .= $topic->topic_parent.",";
					}
				}
			} else {
				$html .= __("<p>You have not replied to any forum topics.</p>", "wp-symposium");
			}
		
		$html .= '</div>';		

	$html .= '</div>';
	
	echo $html;
	exit;
}

// AJAX to fetch favourites
if ($_POST['action'] == 'getFavs') {

	$config = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."symposium_config"));

	// Work out link to this page, dealing with permalinks or not
	$thispage = symposium_get_url('forum');
	if ($thispage[strlen($thispage)-1] != '/') { $thispage .= '/'; }
	if (strpos($thispage, "?") === FALSE) { 
		$q = "?";
	} else {
		// No Permalink
		$q = "&";
	}
	
	$snippet_length_long = $config->preview2;
	if ($snippet_length_long == '') { $snippet_length_long = '45'; }
	
	$html = '';
	
	$favs = get_symposium_meta($current_user->ID, 'forum_favs');
	$favs = explode('[', $favs);
	if ($favs) {
		foreach ($favs as $fav) {
			$fav = str_replace("]", "", $fav);
			if ($fav != '') {
				
				$post = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."symposium_topics WHERE tid = %d", $fav));
				$html .= '<div id="fav_'.$fav.'" class="fav_row" style="padding:6px; margin-bottom:10px;">';

					$html .= " <a title='".$fav."' class='symposium-delete-fav' style='cursor:pointer'>".__("Remove", "wp-symposium")."</a>";
				
					$html .= '<div class="forum_activity_new_topic_subject"><a href="'.$thispage.symposium_permalink($post->tid, "topic").$q.'cid='.$post->topic_category.'&show='.$post->tid.'">'.stripslashes($post->topic_subject).'</a></div>';

					$replies = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$wpdb->prefix.'symposium_topics'." WHERE topic_parent = ".$post->tid." ORDER BY topic_date DESC"));
					if ($replies) {
						$cnt = 0;
						$dt = '';
						foreach ($replies as $reply) {
							$cnt++;
							if ($dt == '') { $dt = $reply->topic_date; }
						}
						
						if ($cnt > 0) {
							$html .= "<em>".$cnt." ";
							if ($cnt == 1) 
							{ 
								$html .= __("reply", "wp-symposium");
								$html .= ", ".symposium_time_ago($dt).".</em>";
							} else {
								$html .= __("replies", "wp-symposium");
								$html .= ", ".__("last one", "wp-symposium")." ".symposium_time_ago($dt).".</em>";
							}
							
						}
					}

					$text = stripslashes($post->topic_post);
					if ( strlen($text) > $snippet_length_long ) { $text = substr($text, 0, $snippet_length_long)."..."; }
					
					$html .= "<br />".$text;
					
				$html .= '</div>';
			}
		}
	}
	
	if ($html == '') {
		
		$html .= __("You can add your favourite forum topics by clicking on the star beside any forum topic title.", "wp-symposium");
	}
	
	echo $html;
	exit;
}

// AJAX function to toggle post as a favourite
if ($_POST['action'] == 'toggleFav') {

	$tid = $_POST['tid'];	

	// Update meta record exists for user
	$favs = get_symposium_meta($current_user->ID, "forum_favs");
	if (strpos($favs, "[".$tid."]") === FALSE) { 
		$favs .= "[".$tid."]";
		$r = "added";
	} else {
		$favs = str_replace("[".$tid."]", "", $favs);
		$r = $tid;
	}
	update_symposium_meta($current_user->ID, "forum_favs", "'".$favs."'");

	echo $r;
	exit;

}
	
// AJAX function to get topic details for editing
if ($_POST['action'] == 'getEditDetails') {

	$tid = $_POST['tid'];	
	
	$details = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix.'symposium_topics'." WHERE tid = ".$tid); 
	if ($details->topic_subject == '') {
		$parent = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix.'symposium_topics'." WHERE tid = ".$details->topic_parent); 
		$subject = $parent->topic_subject;
	} else {
		$subject = $details->topic_subject;
	}

	if ($details) {
		echo stripslashes($subject)."[split]".stripslashes($details->topic_post)."[split]".$details->topic_parent."[split]".$details->tid."[split]".$details->topic_category;
	} else {
		echo "Problem retrieving topic information[split]Passed Topic ID = ".$tid;
	}
	exit;
}

// AJAX function to update Digest subscription
if ($_POST['action'] == 'updateDigest') {

	$value = $_POST['value'];	

	// Update meta record exists for user
	update_symposium_meta($current_user->ID, "forum_digest", "'".$value."'");
	echo $value;
	exit;

}

// AJAX function to update topic details after editing
if ($_POST['action'] == 'updateEditDetails') {

	$tid = $_POST['tid'];	
	$topic_subject = addslashes($_POST['topic_subject']);	
	$topic_post = addslashes($_POST['topic_post']);	
	$topic_post = str_replace("\n", chr(13), $topic_post);	
	$topic_category = $_POST['topic_category'];
	
	if ($topic_category == "") {
		$topic_category = $wpdb->get_var($wpdb->prepare("SELECT topic_category FROM ".$wpdb->prefix.'symposium_topics'." WHERE tid = ".$tid));
	}

	$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_topics'." SET topic_category = ".$topic_category." WHERE topic_parent = ".$tid) );

	$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix.'symposium_topics'." SET topic_subject = '".$topic_subject."', topic_post = '".$topic_post."', topic_category = ".$topic_category." WHERE tid = ".$tid) );
	
	$parent = $wpdb->get_var($wpdb->prepare("SELECT topic_parent FROM ".$wpdb->prefix.'symposium_topics'." WHERE tid = ".$tid));

	echo $topic_post;
	
	exit;
}

// AJAX function to subscribe/unsubscribe to symposium topic
if ($_POST['action'] == 'updateForum') {

	$subs = $wpdb->prefix . 'symposium_subs';

	$tid = $_POST['tid'];
	$action = $_POST['value'];

	// Store subscription if wanted
	if (symposium_safe_param($tid)) {
		$wpdb->query("DELETE FROM ".$subs." WHERE uid = ".$current_user->ID." AND tid = ".$tid);
	}
	
	if ($action == 1)
	{		
		// Store subscription if wanted
		$wpdb->query( $wpdb->prepare( "
			INSERT INTO ".$subs."
			( 	uid, 
				tid
			)
			VALUES ( %d, %d )", 
	        array(
	        	$current_user->ID, 
	        	$tid
	        	) 
	        ) );
		exit;
		
	} else {
			
		exit;
		// Removed, and not re-added
	}
	
	echo "Sorry - subscription failed";
	exit;
}

// AJAX function to change sticky status
if ($_POST['action'] == 'updateForumSticky') {

	$topics = $wpdb->prefix . 'symposium_topics';

	$tid = $_POST['tid'];
	$value = $_POST['value'];

	// Store subscription if wanted
	$wpdb->query("UPDATE ".$topics." SET topic_sticky = ".$value." WHERE tid = ".$tid);
	
	if ($value==1) {
		echo "Topic is sticky";
	} else {
		echo "Topic is NOT sticky";
	}
	exit;
}

// AJAX function to change allow replies status
if ($_POST['action'] == 'updateTopicReplies') {

	$topics = $wpdb->prefix . 'symposium_topics';

	$tid = $_POST['tid'];
	$value = $_POST['value'];

	// Store subscription if wanted
	$wpdb->query("UPDATE ".$topics." SET allow_replies = '".$value."' WHERE tid = ".$tid);
	
	if ($value=='on') {
		echo "Topic is sticky";
	} else {
		echo "Topic is NOT sticky";
	}
	exit;
}

// AJAX function to subscribe/unsubscribe to new symposium topics
if ($_POST['action'] == 'updateForumSubscribe') {

	$subs = $wpdb->prefix . 'symposium_subs';

	$action = $_POST['value'];
	$cid = $_POST['cid'];

	// Store subscription if wanted
	if (symposium_safe_param($cid)) {
		$wpdb->query("DELETE FROM ".$subs." WHERE uid = ".$current_user->ID." AND tid = 0 AND (cid = ".$cid." OR cid = 0)");
	}
	
	if ($action == 1)
	{		
		// Store subscription if wanted
		$wpdb->query( $wpdb->prepare( "
			INSERT INTO ".$subs."
			( 	uid, 
				tid,
				cid
			)
			VALUES ( %d, %d, %d )", 
	        array(
	        	$current_user->ID, 
	        	0,
	        	$cid
	        	) 
	        ) );
			echo 'Subscription added.';			
		exit;
		
	} else {

		echo 'Subscription removed.';			
		exit;
	}
	
	echo "Sorry - subscription failed";
	exit;

}

// Do search
if ($_POST['action'] == 'getSearch') {

	$gid = $_POST['gid'];
	$term = $_POST['term'];

	$config = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."symposium_config"));

	// Work out link to this page, dealing with permalinks or not
	$thispage = symposium_get_url('forum');
	if ($thispage[strlen($thispage)-1] != '/') { $thispage .= '/'; }
	if (strpos($thispage, "?") === FALSE) { 
		$q = "?";
	} else {
		// No Permalink
		$q = "&";
	}
	
	$html = '<div id="forum_activity_div">';
	
		$sql = "SELECT t.*, p.tid AS parent_tid, u2.display_name as parent_display_name, p.topic_subject AS parent_topic_subject, p.topic_started AS parent_topic_started, u.display_name FROM ".$wpdb->prefix."symposium_topics t LEFT JOIN ".$wpdb->base_prefix."users u ON t.topic_owner = u.ID LEFT JOIN ".$wpdb->prefix."symposium_topics p ON t.topic_parent = p.tid LEFT JOIN ".$wpdb->base_prefix."users u2 ON p.topic_owner = u2.ID WHERE (t.topic_subject LIKE '%".$term."%' OR t.topic_post LIKE '%".$term."%') AND t.topic_group = ".$gid." ORDER BY t.topic_started DESC LIMIT 0,40";

		$topics = $wpdb->get_results($sql);
		if ($topics) {
			foreach ($topics as $topic) {		
				$html .= "<div class='symposium_search_subject_row_div'>";
				
					$html .= "<div class='symposium_search_subject_div'>";

					if ($topic->topic_parent != 0) {
						$html .= __("In reply to", "wp-symposium")." ";
						$topic_subject = symposium_bbcode_remove(stripslashes($topic->parent_topic_subject));
						$topic_subject = preg_replace(
						  "/(>|^)([^<]+)(?=<|$)/esx",
						  "'\\1' . str_replace('" . $term . "', '<span class=\"symposium_search_highlight\">" . $term . "</span>', '\\2')",
						  $topic_subject
						);
						$html .= "<a class='symposium_search_subject' href='".$thispage.symposium_permalink($topic->tid, "topic").$q.'cid='.$topic->topic_category.'&show='.$topic->parent_tid."'>".stripslashes($topic_subject)."</a> ";
						$html .= __("by", "wp-symposium")." ".$topic->parent_display_name.", ".symposium_time_ago($topic->parent_topic_started).".";
					} else {
						$topic_subject = symposium_bbcode_remove(stripslashes($topic->topic_subject));
						$topic_subject = preg_replace(
						  "/(>|^)([^<]+)(?=<|$)/iesx",
						  "'\\1' . str_replace('" . $term . "', '<span class=\"symposium_search_highlight\">" . $term . "</span>', '\\2')",
						  $topic_subject
						);
						$html .= "<a class='symposium_search_subject' href='".$thispage.symposium_permalink($topic->tid, "topic").$q.'cid='.$topic->topic_category.'&show='.$topic->tid."'>".stripslashes($topic_subject)."</a> ";
						$html .= __("by", "wp-symposium")." ".$topic->display_name.", ".symposium_time_ago($topic->topic_started).".";
					}

					$html .= "</div>";

					$text = symposium_bbcode_remove(stripslashes($topic->topic_post));
					
					$result = "";
					$buffer = 20;
					
					for ($i = 0; $i <= strlen($text)-strlen($term); $i++) {
						if ( substr(strtolower($text), $i, strlen($term)) == strtolower($term) ) {
							$start = ($i - $buffer >= 0) ? $i - $buffer : 0;
							$end = strlen($term) + ($buffer * 2);
							$end = ($end >= strlen($text)) ? strlen($text) : $end;
							$snippet = substr($text, $start, $end);
							if ($start > 0) { $snippet = "...".$snippet; }
							if ($end < strlen($text)) { $snippet .= "...&nbsp;&nbsp;"; }
							$snippet = preg_replace('/('.$term.')/i', "<span class=\"symposium_search_highlight\">$1</span>", $snippet); 
							$result .= $snippet;
						}
					}

					if ($result != '') {
						
						$html .= stripslashes($result)."<br />";	
						if ($topic->topic_parent != 0) {
							$html .= "<em>".__("Posted by", "wp-symposium")." ".$topic->display_name.", ".symposium_time_ago($topic->topic_started).".</em>";
						}
					}

				$html .= '</div>';
				
			}
		} else {
			$html .= "<p>".__("No result found", "wp-symposium").".</p>";
		}

	$html .= '</div>';
	
	//$html .= " ".$wpdb->last_query;
	echo $html;
	exit;
}

?>