<?php

include_once('../../../../wp-config.php');

global $wpdb, $current_user, $blog_id;
wp_get_current_user();

// Accept Answer *************************************************************
if ($_POST['action'] == 'acceptAnswer') {
	
	$tid = $_POST['tid'];

	if (is_user_logged_in()) {

		if (symposium_safe_param($tid)) {
			
			$r = 'OK';

			// Get parent tid first
			$sql = "SELECT topic_parent FROM ".$wpdb->prefix."symposium_topics WHERE tid = %d";
			$topic_parent = $wpdb->get_var($wpdb->prepare($sql, $tid));
			// Now clear all accepted answers for this topic (in case selected a different answer from previously chosen answer)			
			$sql = "UPDATE ".$wpdb->prefix."symposium_topics SET topic_answer = '' WHERE topic_parent = %d";
			$wpdb->get_var($wpdb->prepare($sql, $topic_parent));
			// Finally update new accepted answer
			$sql = "UPDATE ".$wpdb->prefix."symposium_topics SET topic_answer = 'on' WHERE tid = %d";
			$wpdb->get_var($wpdb->prepare($sql, $tid));
	
			// Prepare to return comments in JSON format
			$return_arr = array();
			$row_array['message'] = __('You have accepted this answer. If a better answer is posted, you can change your selection.', 'wp-symposium');
			$row_array['title'] = __('Answer accepted', 'wp-symposium');
		        
			array_push($return_arr, $row_array);
			
			echo json_encode($return_arr);
			exit;		        
		}

	}
	
}

// Remove Uploaded Image ****************************************************************
if ($_POST['action'] == 'removeUploadedImage') {

	$folder = $_POST['folder'];
	$file = $_POST['file'];

	$html = '';
	
	if (WPS_IMG_DB == "on") {

		if ($wpdb->query( $wpdb->prepare( "DELETE FROM ".$wpdb->prefix."symposium_topics_images WHERE filename = %s AND tid = %d AND uid = %d", $file, $folder, $current_user->ID  ) ) ) {
			$html .= 'OK';
		} else {
			$html .= __('Failed to remove image from database', 'wp-symposium');
		}

	} else {
		
		if ($blog_id > 1) {			
			$src = WP_CONTENT_DIR.'/wps-content/'.$blog_id.'/forum/'.$folder.'/'.$file;
		} else {
			$src = WP_CONTENT_DIR.'/wps-content/forum/'.$folder.'/'.$file;
		}
		
		if (file_exists($src)) {
			if (unlink($src)) {
				$html .= 'OK';
			} else {
				$html .= __('Failed to remove image', 'wp-symposium');
			}
		} else {
			$html .= __('Image to remove is not there...', 'wp-symposium').' '.$src;
		}
	}
	
	echo $html;
	exit;
	
}

// Update Score *************************************************************
if ($_POST['action'] == 'updateTopicScore') {
	
	$tid = $_POST['tid'];
	$change = $_POST['change'];

	if (is_user_logged_in()) {

		if (symposium_safe_param($tid)) {
			
			$r = 'OK';
			
			// Check if already voted on this post and remove if so
			$sql = "SELECT COUNT(*) FROM ".$wpdb->prefix."symposium_topics_scores WHERE tid = %d and uid = %d";
			$already_voted = $wpdb->get_var($wpdb->prepare($sql, $tid, $current_user->ID));

			if ($already_voted > 0) {
				$sql = "DELETE FROM ".$wpdb->prefix."symposium_topics_scores WHERE tid = %d and uid = %d";
				$wpdb->query($wpdb->prepare($sql, $tid, $current_user->ID));
				
				$r = __('Thank you for voting. You can only have one vote per post, so your previous vote has been replaced.', 'wp-symposium');
			}			

			// Insert new vote
			$wpdb->query( $wpdb->prepare( "
				INSERT INTO ".$wpdb->prefix."symposium_topics_scores 
				( 	tid,
					uid, 
					score, 
					topic_date
				)
				VALUES ( %d, %d, %s, %s )", 
		        array(
		        	$tid, 
		        	$current_user->ID,
		        	$change, 
		        	date("Y-m-d H:i:s")
		        	) 
		        ) );

			// Get latest vote total
			$sql = "SELECT SUM(score) FROM ".$wpdb->prefix."symposium_topics_scores WHERE tid = %d";
			$voted = $wpdb->get_var($wpdb->prepare($sql, $tid));

			// Prepare to return comments in JSON format
			$return_arr = array();
			$row_array['str'] = stripslashes($r);
			$row_array['score'] = $voted;
		        
			array_push($return_arr, $row_array);
			
			echo json_encode($return_arr);
			exit;		        
		}

	}
	
}


// Delete Reply *************************************************************
if ($_POST['action'] == 'deleteReply') {

	if (is_user_logged_in()) {

		$tid = $_POST['topic_id'];

		// Get owner of this reply
		$sql = "SELECT topic_owner FROM ".$wpdb->prefix."symposium_topics WHERE tid = %d";
		$owner = $wpdb->get_var($wpdb->prepare($sql, $tid));
		
		if (current_user_can('level_10') || $owner == $current_user->ID) {
			if (symposium_safe_param($tid)) {
				$wpdb->query($wpdb->prepare("DELETE FROM ".$wpdb->prefix."symposium_topics WHERE tid = %d", $tid));
			}
		
			echo $tid;
		
		} else {
			echo "NOT ADMIN OR OWNER";
		}
	
	}
}

// Delete Topic and Replies *************************************************
if ($_POST['action'] == 'deleteTopic') {

	if (is_user_logged_in()) {

		if (current_user_can('level_10')) {
			$tid = $_POST['topic_id'];
			if (symposium_safe_param($tid)) {
				$wpdb->query("DELETE FROM ".$wpdb->prefix."symposium_topics WHERE topic_parent = ".$tid);
				$wpdb->query("DELETE FROM ".$wpdb->prefix."symposium_topics_scores WHERE tid = ".$tid);
				$wpdb->query("DELETE FROM ".$wpdb->prefix."symposium_topics WHERE tid = ".$tid);
				$wpdb->query("DELETE FROM ".$wpdb->prefix."symposium_subs WHERE tid = ".$tid);
			}
		
			echo $tid;
		
		} else {
			echo "NOT ADMIN";
		}
	}
}

// New Topic ****************************************************************
if ($_POST['action'] == 'forumNewPost') {

	if (is_user_logged_in()) {

		$new_topic_subject = $_POST['subject'];
		$new_topic_text = $_POST['text'];
		
		if (isset($_POST['category'])) { $new_topic_category = $_POST['category']; } else { $new_topic_category = 0; }
		$new_topic_subscribe = $_POST['subscribed'];
		$info_only = $_POST['info_only'];
		$group_id = $_POST['group_id'];
		if ($group_id > 0) { $new_topic_category = 0; }

		if (WPS_STRIPTAGS == 'on') {
			$new_topic_subject = strip_tags($new_topic_subject);
			$new_topic_text = strip_tags($new_topic_text);
		}
		
		// Check for moderation
		if (WPS_MODERATION == "on") {
			$topic_approved = "";
		} else {
			$topic_approved = "on";
		}

		if ($new_topic_subject == '') { $new_topic_subject = __('No subject', 'wp-symposium'); }
		if ($new_topic_text == '') { $new_topic_text = __('No message', 'wp-symposium');  }
	
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
			
		
		// Store new topic in post
	
		// Replace carriage returns
		$new_topic_text = str_replace("\n", chr(13), $new_topic_text);	
	
		// Don't allow HTML
		$new_topic_subject = str_replace("<", "&lt;", $new_topic_subject);
		$new_topic_subject = str_replace(">", "&gt;", $new_topic_subject);
		$new_topic_text = str_replace("<", "&lt;", $new_topic_text);
		$new_topic_text = str_replace(">", "&gt;", $new_topic_text);

		// Check for banned words
		$chatroom_banned = WPS_CHATROOM_BANNED;
		if ($chatroom_banned != '') {
			$badwords = $pieces = explode(",", $chatroom_banned);
		
			 for($i=0;$i < sizeof($badwords);$i++){
			 	if (strpos(' '.$new_topic_subject.' ', $badwords[$i])) {
				 	$new_topic_subject=eregi_replace($badwords[$i], "***", $new_topic_subject);
			 	}
			 	if (strpos(' '.$new_topic_text.' ', $badwords[$i])) {
				 	$new_topic_text=eregi_replace($badwords[$i], "***", $new_topic_text);
			 	}
			 }
		}

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
				topic_approved,
				for_info,
				topic_group
			)
			VALUES ( %s, %d, %s, %s, %s, %d, %d, %d, %s, %s, %d )", 
	        array(
	        	$new_topic_subject, 
	        	$new_topic_category,
	        	$new_topic_text, 
	        	date("Y-m-d H:i:s"), 
				date("Y-m-d H:i:s"), 
				$current_user->ID, 
				0,
				0,
				$topic_approved,
				$info_only,
				$group_id
	        	) 
	        ) );
        
		// Store subscription if wanted
		$new_tid = $wpdb->insert_id;
		if ($new_topic_subscribe == 'on') {
			$wpdb->query( $wpdb->prepare( "
				INSERT INTO ".$wpdb->prefix."symposium_subs 
				( 	uid, 
					tid
				)
				VALUES ( %d, %d )", 
		        array(
		        	$current_user->ID, 
		        	$new_tid
		        	) 
		        ) );
		}

		// Check for any tmp uploaded files for this post and transfer to permenant storage
		if (WPS_IMG_DB == "on") {
			
			$wpdb->query( $wpdb->prepare( "UPDATE ".$wpdb->base_prefix."symposium_topics_images SET tid = %d WHERE tid = 0 AND uid = %d", $new_tid, $current_user->ID ));
			
		} else {

			if ($blog_id > 1) {
				$tmp_path = WP_CONTENT_DIR.'/wps-content/'.$blog_id.'/forum/0_'.$current_user->ID.'_tmp';
				$to_path = WP_CONTENT_DIR.'/wps-content/'.$blog_id.'/forum/'.$new_tid;
			} else {
				$tmp_path = WP_CONTENT_DIR.'/wps-content/forum/0_'.$current_user->ID.'_tmp';
				$to_path = WP_CONTENT_DIR.'/wps-content/forum/'.$new_tid;
			}
			if (file_exists($tmp_path)) {
				mkdir($to_path, 0777, true);
				// copy tmp files to new location
				$handler = opendir($tmp_path);
				while ($file = readdir($handler)) {
					if ($file != "." && $file != ".." && $file != ".DS_Store") {
						copy($tmp_path.'/'.$file, $to_path.'/'.$file);
						unlink($tmp_path.'/'.$file);
					}
				}
				rmdir($tmp_path);
				closedir($handler);
			}
			
		}
				
		// Set category to the category posted into
		$cat_id = $new_topic_category;
	
		// Update last activity (if posting to a group)
		if ($group_id > 0) {
			$wpdb->query( $wpdb->prepare( "UPDATE ".$wpdb->base_prefix."symposium_groups SET last_activity = %s WHERE gid = %d", array( date("Y-m-d H:i:s"), $group_id ) ));
		}
					
		// Get post owner name and prepare email body
		$owner_name = $wpdb->get_var($wpdb->prepare("SELECT display_name FROM ".$wpdb->base_prefix."users WHERE ID = ".$current_user->ID));
		$body = "<p>".$owner_name." ".__('has started a new topic', 'wp-symposium');
		$category = $wpdb->get_var($wpdb->prepare("SELECT title FROM ".$wpdb->prefix."symposium_cats WHERE cid = ".$cat_id));
		$body .= " ".__('in', 'wp-symposium')." ".$category;
		$body .= "...</p>";
						
		$body .= "<span style='font-size:24px'>".$new_topic_subject."</span><br /><br />";
		$body .= "<p>".$new_topic_text."</p>";
		$url = $forum_url.$q."cid=".$cat_id."&show=".$new_tid;
		$body .= "<p><a href='".$url."'>".$url."</a></p>";
		$body = str_replace(chr(13), "<br />", $body);
		$body = str_replace("\\r\\n", "<br />", $body);
		$body = str_replace("\\", "", $body);

		if ($topic_approved == "on") {
		
			if ($group_id == 0) {
			
				// Main forum (not a group forum)
				// Email people who want to know	
				
				$email_list = '0,';
				$query = $wpdb->get_results("
					SELECT user_email, ID
					FROM ".$wpdb->base_prefix."users u RIGHT JOIN ".$wpdb->prefix."symposium_subs s ON s.uid = u.ID 
					WHERE s.tid = 0 AND u.ID != ".$current_user->ID." AND s.cid = ".$cat_id);
			
				if ($query) {					
					foreach ($query as $user) {

						// Hook and Filter to allow further actions to take place
						apply_filters ('symposium_forum_newtopic_filter', $user->ID, $current_user->ID, $current_user->display_name, $url);

						// Add to list of those sent to
						$email_list .= $user->ID.',';
						
						// Send mail
						symposium_sendmail($user->user_email, __('New Forum Topic', 'wp-symposium'), $body);						
					}						
				}

				// Now send to everyone who wants to know about all new topics and replies
				$email_list .= '0';
				$sql = "SELECT ID,user_email FROM ".$wpdb->base_prefix."users u 
					INNER JOIN ".$wpdb->base_prefix."symposium_usermeta m ON u.ID = m.uid 
					WHERE m.forum_all = 'on' AND
					ID != %d AND 
					ID NOT IN (%s)";
				$query = $wpdb->get_results($wpdb->prepare($sql, $current_user->ID, $email_list));

					if ($query) {						
						foreach ($query as $user) {	

							// Filter to allow further actions to take place
							apply_filters ('symposium_forum_newtopic_filter', $user->ID, $current_user->ID, $current_user->display_name, $url);
					
							// Send mail
							symposium_sendmail($user->user_email, __('New Forum Topic', 'wp-symposium'), $body);							
						}
					}	
			
			} else {
				
				// Group Forum (tell all members automatically)
				$group_name = $wpdb->get_var($wpdb->prepare("SELECT name FROM ".$wpdb->base_prefix."symposium_groups WHERE gid = %d", $group_id));

				$sql = "SELECT ID FROM ".$wpdb->base_prefix."users u 
				LEFT JOIN ".$wpdb->prefix."symposium_group_members g ON u.ID = g.member_id 
				WHERE u.ID > 0 AND g.group_id = %d";

				$members = $wpdb->get_results($wpdb->prepare($sql, $group_id));

				if ($members) {
					foreach ($members as $member) {
						if (function_exists('symposium_news_add')) {
							if ($member->ID != $current_user->ID) {
								symposium_news_add($current_user->ID, $member->ID, "<a href='".$url."'>".$owner_name." ".__("started forum topic in", "wp-symposium")." ".$group_name."</a>");
							}
						}
					}
				}
				
				
			}

			// Hook to allow other actions
			$post = __('Started a new forum topic:', 'wp-symposium').' <a href="'.$url.'">'.$new_topic_subject.'</a>';
			do_action('symposium_forum_newtopic_hook', $current_user->ID, $current_user->display_name, $post, 'forum');
	

		} else {
			// Email admin if post needs approval
			$body = "<span style='font-size:24px font-style:italic;'>".__('Moderation Required', 'wp-symposium')."</span><br /><br />".$body;
			symposium_sendmail(get_bloginfo('admin_email'), __('Moderation Required', 'wp-symposium'), $body);
		}	
		
		
		echo $url;
		exit;
		
	} else {
	
		echo 'NOT LOGGED IN';
		exit;
		
	}

}

// Get Topic ****************************************************************
if ($_POST['action'] == 'getTopic') {
		
	$topic_id = $_POST['topic_id'];
	$group_id = $_POST['group_id'];

	$plugin = WP_CONTENT_URL.'/plugins/wp-symposium/';
	
	$previous_login = get_symposium_meta($current_user->ID, 'previous_login');

	$level = symposium_get_current_userlevel();

	// Confirm if can view or not
	if ( (WPS_VIEWER == "Guest")
	 || (WPS_VIEWER == "Subscriber" && $level >= 1)
	 || (WPS_VIEWER == "Contributor" && $level >= 2)
	 || (WPS_VIEWER == "Author" && $level >= 3)
	 || (WPS_VIEWER == "Editor" && $level >= 4)
	 || (WPS_VIEWER == "Administrator" && $level == 5) ) {
		$can_view = true;
	 } else {
		$can_view = false;
	 }

	// Confirm if can post/reply or not
	if ( (WPS_FORUM_EDITOR == "Guest")
	 || (WPS_FORUM_EDITOR == "Subscriber" && $level >= 1)
	 || (WPS_FORUM_EDITOR == "Contributor" && $level >= 2)
	 || (WPS_FORUM_EDITOR == "Author" && $level >= 3)
	 || (WPS_FORUM_EDITOR == "Editor" && $level >= 4)
	 || (WPS_FORUM_EDITOR == "Administrator" && $level == 5) ) {
		$can_edit = true;
		
		// Check if a in a group, that is a member
		if ($group_id > 0) {
			$sql = "SELECT COUNT(*) FROM ".$wpdb->prefix."symposium_group_members WHERE group_id=%d AND valid='on' AND member_id=%d";
			$member_count = $wpdb->get_var($wpdb->prepare($sql, $group_id, $current_user->ID));
			if ($member_count == 0) { $can_edit = false; }
		}
	 } else {
		$can_edit = false;
	 }

	if ($can_view) {

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

			$html = '';
			// Store removal limit for votes
			$html .= '<div id="symposium_forum_vote_remove" style="display:none">'.WPS_USE_VOTES_REMOVE.'</div>';
			$html .= '<div id="symposium_forum_vote_remove_msg" style="display:none">'.__('This post has been voted off the forum', 'wp-symposium').'</div>';
		

			// Breadcrumbs
			$cat_id = $post->topic_category;
			
			$html .= '<div id="topic_breadcrumbs" class="breadcrumbs label">';

				if (!WPS_LITE) {
					$this_level = $wpdb->get_row($wpdb->prepare("SELECT cid, title, cat_parent FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d", $cat_id));
					if ($this_level) {
	
						if ($this_level->cat_parent == 0) {
							if (WPS_FORUM_AJAX == 'on') {
								$html .= '<a href="#cid=0" class="category_title" title="0">'.__('Forum Home', 'wp-symposium')."</a> &rarr; ";
								$html .= '<a href="#cid='.$this_level->cid.'" class="category_title" title="'.$this_level->cid.'">'.trim($this_level->title).'</a>';
							} else {
								$html .= '<a href="'.$forum_url.$q.'cid=0" title="0">'.__('Forum Home', 'wp-symposium')."</a> &rarr; ";
								$html .= '<a href="'.$forum_url.$q."cid=".$this_level->cid.'" title="'.$this_level->cid.'">'.trim($this_level->title).'</a>';
							}
						} else {
	
							$parent_level = $wpdb->get_row($wpdb->prepare("SELECT cid, title, cat_parent FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d", $this_level->cat_parent));
	
							if ($parent_level->cat_parent == 0) {
								if (WPS_FORUM_AJAX == 'on') {
									$html .= '<a href="#cid=0" class="category_title" title="0">'.__('Forum Home', 'wp-symposium')."</a> &rarr; ";
								} else {
									$html .= '<a href="'.$forum_url.$q.'cid=0" title="0">'.__('Forum Home', 'wp-symposium')."</a> &rarr; ";
								}
							} else {
								$parent_level_2 = $wpdb->get_row($wpdb->prepare("SELECT cid, title, cat_parent FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d", $parent_level->cat_parent));
								if (WPS_FORUM_AJAX == 'on') {
									$html .= '<a href="#cid=0" class="category_title" title="0">'.__('Forum Home', 'wp-symposium')."</a> &rarr; " ;
									$html .= '<a href="#cid='.$parent_level_2->cid.'" class="category_title" title="'.$parent_level_2->cid.'">'.$parent_level_2->title."</a> &rarr; ";
								} else {
									$html .= '<a href="'.$forum_url.$q.'cid=0" title="0">'.__('Forum Home', 'wp-symposium')."</a> &rarr; " ;
									$html .= '<a href="'.$forum_url.$q."cid=".$parent_level_2->cid.'"  title="'.$parent_level_2->cid.'">'.$parent_level_2->title."</a> &rarr; ";
								}
							}
							if (WPS_FORUM_AJAX == 'on') {
								$html .= '<a href="#cid='.$parent_level->cid.'" class="category_title" title="'.$parent_level->cid.'">'.$parent_level->title."</a> &rarr; " ;
								$html .= '<a href="#cid='.$this_level->cid.'" class="category_title" title="'.$this_level->cid.'">'.$this_level->title."</a>" ;
							} else {
								$html .= '<a href="'.$forum_url.$q."cid=".$parent_level->cid.'" title="'.$parent_level->cid.'">'.$parent_level->title."</a> &rarr; " ;
								$html .= '<a href="'.$forum_url.$q."cid=".$this_level->cid.'" title="'.$this_level->cid.'">'.$this_level->title."</a>" ;
							}
						}
					} else {
						if (WPS_FORUM_AJAX == 'on') {
							$html .= '&larr; <a href="#cid=0" class="category_title" title="0">'.__('Forum Home', 'wp-symposium')."</a>";
						} else {
							$html .= '&larr; <a href="'.$forum_url.$q.'cid=0" title="0">'.__('Forum Home', 'wp-symposium')."</a>";
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
			if (WPS_SHARING != '') {
				$html .= show_sharing_icons($cat_id, $post->tid, WPS_SHARING, $group_id);
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

						if (WPS_FORUM_INFO) {
						
							$html .= "<div class='forum_info'>";
							
								$sql = "SELECT count(*) FROM ".$wpdb->prefix."symposium_topics WHERE topic_owner = %d";
								$count = $wpdb->get_var($wpdb->prepare($sql, $post->topic_owner));
								$html .= __('Posts:', 'wp-symposium').' ';
								$html .= '<span class="forum_info_numbers">'.$count.'</span>';

							$html .= "</div>";	
						
							if (WPS_USE_ANSWERS == 'on') {
								$html .= "<div class='forum_info'>";
									// Get widget settings (also used under Replies)
									$settings = get_option("widget_forumexperts-widget");
									if (isset($settings[3]['timescale'])) {
										$timescale = $settings[3]['timescale'];
										$w_cat_id = $settings[3]['cat_id'];
										$cat_id_exclude = $settings[3]['cat_id_exclude'];
										$groups = $settings[3]['groups'];
									} else {
										$timescale = 7;
										$w_cat_id = '';
										$cat_id_exclude = '';
										$groups = '';
									}
									// Now get value
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
								$html .= "</div>";
							}

							if ($post->topic_started > $previous_login && $post->topic_owner != $current_user->ID && is_user_logged_in()) {
								$html .= "<img src='".WPS_IMAGES_URL."/new.gif' alt='New!' /> ";
							}
						}
						
					$html .= "</div>";
			
					$html .= "<div class='topic-post-header-with-fav'>";
			
						$html .= "<div class='topic-post-header'>";

							if (WPS_ALLOW_REPORTS == 'on') {
								$html .= "<a href='javascript:void(0)' title='forum_".$post->tid."' class='report label symposium_report' style='display:none; cursor:pointer'><div class='topic-edit-icon'><img src='".WPS_IMAGES_URL."/warning.png' /></a></div>";
							}
							if ( ($post->topic_owner == $current_user->ID) || (current_user_can('level_10')) ) {
								$html .= "<a href='javascript:void(0)' title='".$post->tid."' id='edit-this-topic' class='edit_topic edit label' style='cursor:pointer'><div class='topic-edit-icon'><img src='".WPS_IMAGES_URL."/edit.png' /></a></div>";
							}

							$post_text = stripslashes(symposium_bbcode_replace(stripslashes($post->topic_subject)));
							$html .= $post_text;
							$topic_subject = $post_text;
			
							if ($post->topic_approved != 'on') { $html .= " <em>[".__("pending approval", "wp-symposium")."]</em>"; }

							// Favourites
							if (is_user_logged_in()) {
								if (strpos(get_symposium_meta($current_user->ID, 'forum_favs'), "[".$post->tid."]") === FALSE) { 
									$html .= "<img title='".$post->tid."' id='fav_link' src='".WPS_IMAGES_URL."/fav-off.png' style='height:22px; width:22px; cursor:pointer;' alt='".__("Click to add to favorites", "wp-symposium")."' />";						
								} else {
									$html .= "<img title='".$post->tid."' id='fav_link' src='".WPS_IMAGES_URL."/fav-on.png' style='height:22px; width:22px; cursor:pointer;' alt='".__("Click to remove to favorites", "wp-symposium")."' />";						
								}
							}


						$html .= "</div><div style='clear:both'></div>";
										
						$html .= "<div class='started-by' style='margin-top:10px'>";
						$html .= __("Started by", "wp-symposium");
						if ( substr(WPS_FORUM_RANKS, 0, 2) == 'on' ) {
							$html .= " <span class='forum_rank'>".forum_rank($post->topic_owner)."</span>";
						}
						$html .= " ".symposium_profile_link($post->topic_owner);
						$html .= " ".symposium_time_ago($post->topic_started);
						$html .= "</div>";

						$post_text = symposium_make_url(stripslashes($post->topic_post));
						$post_text = symposium_bbcode_replace($post_text);
						$html .= "<div class='topic-post-post'>".str_replace(chr(13), "<br />", $post_text)."</div><br />";
						
						// Allow owner or site admin to mark topic for information only
						if (WPS_USE_ANSWERS == 'on') {
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
						if (WPS_IMG_DB == 'on') {

							// get list of uploaded files from database
							$sql = "SELECT tmpid, filename FROM ".$wpdb->prefix."symposium_topics_images WHERE tid = ".$post->tid." ORDER BY tmpid";
							$images = $wpdb->get_results($sql);
							foreach ($images as $file) {
								$html .= '<div>';
								$ext = explode('.', $file->filename);
								if ($ext[sizeof($ext)-1]=='gif' || $ext[sizeof($ext)-1]=='jpg' || $ext[sizeof($ext)-1]=='png' || $ext[sizeof($ext)-1]=='jpeg') {
									// Image
									$url = WP_CONTENT_URL."/plugins/wp-symposium/get_attachment.php?tid=".$post->tid."&filename=".$file->filename;
									$html .= "<a target='_blank' href='".$url."' rel='symposium_forum_images-".$post->tid."'";
									$html .= " title='".$file->filename."'>";
									if (WPS_FORUM_THUMBS == 'on') {
										list($width, $height, $type, $attr) = getimagesize($url);
										$max_width = WPS_FORUM_THUMBS_SIZE;
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
									}
								}
								if ($post->topic_owner == $current_user->ID || symposium_get_current_userlevel($current_user->ID) == 5) {
									$html .= '<img id="'.$post->tid.'" title="'.$file->filename.'" class="remove_forum_image link_cursor" src="'.WPS_IMAGES_URL.'/delete.png" /> ';
								}
								$html .= '</div>';	
							}

						} else {

							if ($blog_id > 1) {
								$targetPath = WPS_IMG_PATH."/".$blog_id."/forum/".$post->tid;
							} else {
								$targetPath = WPS_IMG_PATH."/forum/".$post->tid;
							}
							if (file_exists($targetPath)) {
								$handler = opendir($targetPath);
								$cnt = 0;
								while ($file = readdir($handler)) {
									$cnt++;
									if ( ($file != "." && $file != ".." && $file != ".DS_Store") && (!is_dir($targetPath.'/'.$file)) ) {
										$html .= '<div>';
										if ($blog_id > 1) {
											$url = WPS_IMG_URL.'/'.$blog_id.'/forum/'.$post->tid.'/'.$file;
										} else {
											$url = WPS_IMG_URL.'/forum/'.$post->tid.'/'.$file;
										}
										$ext = explode('.', $file);
										if (strpos(WPS_IMAGE_EXT, $ext[sizeof($ext)-1]) > 0) {
											// Image
											$html .= "<a target='_blank' href='".$url."' rel='symposium_forum_images-".$post->tid."'";
											$html .= ' title="'.$file.'">';
											if (WPS_FORUM_THUMBS == 'on') {
												list($width, $height, $type, $attr) = getimagesize($url);
												$max_width = WPS_FORUM_THUMBS_SIZE;
												if ($width > $max_width) {
													$height = $height / ($width / $max_width);
													$width = $max_width;
												}
												$html .= '<img src="'.$url.'" style="width:'.$width.'px; height:'.$height.'px" />';
											} else {
												$html .= $file;
											}
											$html .= '</a> ';
										} else {
											// Video
											if (strpos(WPS_VIDEO_EXT, $ext[sizeof($ext)-1]) > 0) {

												if (WPS_FORUM_THUMBS == 'on') {
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
												$html .= '		width: "'.WPS_FORUM_THUMBS_SIZE.'px",';
												$html .= '		height: "250px"';
												$html .= '	});';
												$html .= '</script>';	

											} else {
												// Document
												$html .= "<a href='".$url."' title='".$file."' rel='mediaplayer'>".$file."</a>";															
											}
										}
										if ($post->topic_owner == $current_user->ID || symposium_get_current_userlevel($current_user->ID) == 5) {
											$html .= '<img id="'.$post->tid.'" title="'.$file.'" class="remove_forum_image link_cursor" src="'.WPS_IMAGES_URL.'/delete.png" /> ';
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
					if (WPS_INCLUDE_ADMIN == "on") { 
						$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix."symposium_topics SET topic_views = topic_views + 1 WHERE tid = %d", $post->tid) );
					}
				} else {
					$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix."symposium_topics SET topic_views = topic_views + 1 WHERE tid = %d", $post->tid) );
				}
					
			$html .= "</div>";		

			// Replies
			$sql = "SELECT t.tid, (SELECT SUM(score) FROM ".$wpdb->prefix."symposium_topics_scores s WHERE s.tid = t.tid) AS score, topic_subject, topic_approved, topic_post, t.topic_date, topic_owner, display_name, topic_answer, ID
				FROM ".$wpdb->prefix."symposium_topics t INNER JOIN ".$wpdb->base_prefix."users u ON t.topic_owner = u.ID 
				WHERE (t.topic_approved = 'on' OR t.topic_owner = %d) AND t.topic_parent = %d ORDER BY t.tid";
				
		
			if (WPS_OLDEST_FIRST != "on") { $sql .= " DESC"; }
	
			$child_query = $wpdb->get_results($wpdb->prepare($sql, $current_user->ID, $post->tid));

			$html .= "<div id='child-posts'>";

				if ($child_query) {
										
					// Get current number of votes by this member to see if can vote
					$sql = "SELECT count(*) FROM ".$wpdb->prefix."symposium_topics WHERE topic_owner = %d";
					$post_count = $wpdb->get_var($wpdb->prepare($sql, $current_user->ID));

					// Div to show if can't vote yet
					$html .= '<div id="symposium_novote_dialog" style="display:none">';
					$html .= sprintf(__("Spam Protection", "wp-symposium"), WPS_USE_VOTES_MIN);
					$html .= '</div>';
					$html .= '<div id="symposium_novote" style="display:none">';
					$html .= sprintf(__("Sorry, you can't vote until you have made %d posts.", "wp-symposium"), WPS_USE_VOTES_MIN);
					$html .= '</div>';
					
					foreach ($child_query as $child) {
						
						$score = $child->score;
						if ($score == NULL) { $score = 0; }

						$html .= "<div id='reply".$child->tid."' class='child-reply";
							$trusted = get_symposium_meta($child->topic_owner, 'trusted');
							if ($trusted == 'on') { $html .= " trusted"; }
							$html .= "'>";

							$html .= "<div class='avatar'>";
								$html .= get_avatar($child->ID, 64);
								
								if (WPS_FORUM_INFO) {
								
									$html .= "<div class='forum_info'>";
									
										$sql = "SELECT count(*) FROM ".$wpdb->prefix."symposium_topics WHERE topic_owner = %d";
										$count = $wpdb->get_var($wpdb->prepare($sql, $child->topic_owner));
										$html .= __('Posts:', 'wp-symposium').' ';								
										$html .= '<span class="forum_info_numbers">'.$count.'</span>';
										
									$html .= "</div>";	

									if (WPS_USE_ANSWERS == 'on') {
										$html .= "<div class='forum_info'>";
											$sql = "SELECT COUNT(*) FROM ".$wpdb->prefix."symposium_topics WHERE topic_owner = %d AND topic_answer = 'on' ";
											$sql .= "AND topic_date >= ( CURDATE() - INTERVAL ".$timescale." DAY )";
											if ($cat_id != '' && $cat_id > 0) {
												$sql .= "AND topic_category IN (".$cat_id.") ";
											}
											if ($cat_id_exclude != '' && $cat_id_exclude > 0) {
												$sql .= "AND topic_category NOT IN (".$cat_id_exclude.") ";
											}
											if ($groups != 'on') {
												$sql .= "AND topic_group = 0 ";
											}								
											$count = $wpdb->get_var($wpdb->prepare($sql, $child->topic_owner));
											if ($count > 0) {
												$html .= __('Rating:', 'wp-symposium').' ';
												$html .= '<span class="forum_info_numbers">'.$count.'</span>';
											}
										$html .= "</div>";	
									}	
									
								}

								if ($child->topic_date > $previous_login && $child->topic_owner != $current_user->ID && is_user_logged_in()) {
									$html .= "<img src='".WPS_IMAGES_URL."/new.gif' alt='New!' /> ";
								}

								
							$html .= "</div>";	
													
							$html .= "<div style='padding-left: 85px;'>";
							
								if ( (WPS_USE_VOTES_REMOVE == $score) && (symposium_get_current_userlevel() < 5) && ($score != 0) ) {
									$html .= '<p>'.__('This post has been voted off the forum', 'wp-symposium').'</p>';
								} else {
								
									if ( (WPS_USE_VOTES_REMOVE == $score) && ($score != 0) ) {
										$html .= '<p>'.__('This post has been voted off the forum (only visible to site admins) with a score of', 'wp-symposium').' '.$score.'.</p>';
									}
									// Votes (if being used)
									if (WPS_USE_VOTES == 'on' && ($child->topic_owner != $current_user->ID || symposium_get_current_userlevel() == 5)) {
										$html .= "<div class='floatright forum_post_score' style='width: 24px; text-align:center;'>";
											$html .= "<div style='line-height:16px;'>";
												if ($post_count >= WPS_USE_VOTES_MIN) {
													$html .= "<img id='".$child->tid."' class='forum_post_score_change' title='plus' src='".WPS_IMAGES_URL."/smilies/good.png' style='cursor:pointer;width:24px; height:24px;' />";
												} else {
													$html .= "<img id='".$child->tid."' class='forum_post_score_change' title='novote' src='".WPS_IMAGES_URL."/smilies/good.png' style='cursor:pointer;width:24px; height:24px;' />";
												}
											$html .= "</div>";
											$html .= "<div id='forum_score_".$child->tid."' style='margin-bottom:3px'>";
												if ($child->score > 0) { $html .= '+'; }
												$html .= $score;
											$html .= "</div>";
											$html .= "<div>";
												if ($post_count >= WPS_USE_VOTES_MIN) {
													$html .= "<img id='".$child->tid."' class='forum_post_score_change' title='minus' src='".WPS_IMAGES_URL."/smilies/bad.png' style='cursor:pointer;width:24px; height:24px;' />";
												} else {
													$html .= "<img id='".$child->tid."' class='forum_post_score_change' title='novote' src='".WPS_IMAGES_URL."/smilies/bad.png' style='cursor:pointer;width:24px; height:24px;' />";
												}
											$html .= "</div>";
										$html .= "</div>";
									}
									// Answer feature (if being used)
									if (WPS_USE_ANSWERS == 'on') {
										$html .= "<div class='floatright'>";
											if ($child->topic_answer == 'on') {
												$html .= "<img id='symposium_accepted_answer' src='".WPS_IMAGES_URL."/tick.png' style='cursor:pointer;margin-top:3px;width:20px; height:20px;' />";
											} else {
												if ($post->topic_owner == $current_user->ID || symposium_get_current_userlevel() == 5) {
													$html .= "<a id=".$child->tid." class='forum_post_answer' href='javascript:void(0);' style='margin-right:10px;";
													if ($post->for_info == 'on') {
														$html .= "display:none;";
													}
													$html .= "'>".__('Accept answer', 'wp-symposium')."</a>";
												}
											}
										$html .= "</div>";
									}
									
									$html .= "<div class='topic-edit-delete-icon'>";
										// Report warning (if being used)
										if (WPS_ALLOW_REPORTS == 'on') {
											$html .= "<a href='javascript:void(0)' class='floatright link_cursor symposium_report' style='display:none' title='reply_".$child->tid."'><img src='".WPS_IMAGES_URL."/warning.png' /></a>";
										}
										if ( ($child->topic_owner == $current_user->ID) || (current_user_can('level_10')) ) {
											$html .= "<a href='javascript:void(0)' class='floatright link_cursor delete_forum_reply' style='display:none' id='".$child->tid."'><img src='".WPS_IMAGES_URL."/delete.png' /></a>";
											$html .= "<a href='javascript:void(0)' class='floatright link_cursor edit_forum_reply' style='display:none; margin-right: 5px' id='".$child->tid."'><img src='".WPS_IMAGES_URL."/edit.png' /></a>";
										}
									$html .= "</div>";
									$html .= "<div class='started-by'>";
									if ( substr(WPS_FORUM_RANKS, 0, 2) == 'on' ) {
										$html .= "<span class='forum_rank'>".forum_rank($child->topic_owner)."</span> ";
									}
									$html .= symposium_profile_link($child->topic_owner);
									$html .= " ".__("replied", "wp-symposium")." ".symposium_time_ago($child->topic_date)."...";
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
																	
								}
	
								// show any uploaded files
								if (WPS_IMG_DB == 'on') {
	
									// get list of uploaded files from database
									$sql = "SELECT tmpid, filename FROM ".$wpdb->prefix."symposium_topics_images WHERE tid = ".$child->tid." ORDER BY tmpid";
									$images = $wpdb->get_results($sql);
									foreach ($images as $file) {

										$html .= '<div>';
										$url = WP_CONTENT_URL."/plugins/wp-symposium/get_attachment.php?tid=".$child->tid."&filename=".$file->filename;
										$html .= "<a target='_blank' href='".$url."'";
										$ext = explode('.', $file->filename);
										if ($ext[sizeof($ext)-1]=='gif' || $ext[sizeof($ext)-1]=='jpg' || $ext[sizeof($ext)-1]=='png' || $ext[sizeof($ext)-1]=='jpeg') {
											$html .= " rel='symposium_forum_images-".$post->tid."'";
										}
										$html .= ' title="'.$file->filename.'">';
										if (WPS_FORUM_THUMBS == 'on') {
											list($width, $height, $type, $attr) = getimagesize($url);
											$max_width = WPS_FORUM_THUMBS_SIZE;
											if ($width > $max_width) {
												$height = $height / ($width / $max_width);
												$width = $max_width;
											}
											$html .= '<img src="'.$url.'" style="width:'.$width.'px; height:'.$height.'px" />';
										} else {
											$html .= $file->filename;
										}
										$html .= '</a> ';
										$html .= '<img id="'.$child->tid.'" title="'.$file->filename.'" class="remove_forum_image link_cursor" src="'.WPS_IMAGES_URL.'/delete.png" /> ';
										$html .= '</div>';	
									}
	
								} else {
									
									if ($blog_id > 1) {
										$targetPath = WPS_IMG_PATH."/".$blog_id."/forum/".$post->tid.'/'.$child->tid;
									} else {
										$targetPath = WPS_IMG_PATH."/forum/".$post->tid.'/'.$child->tid;
									}
									if (file_exists($targetPath)) {
										$cnt = 0;
										$handler = opendir($targetPath);
										while ($file = readdir($handler)) {
											
											if ($file != "." && $file != ".." && $file != ".DS_Store") {
												
												$cnt++;

												$html .= '<div style="overflow:auto;">';
												if ($blog_id > 1) {
													$url = get_bloginfo('url').WPS_IMG_URL.'/'.$blog_id.'/forum/'.$post->tid.'/'.$child->tid.'/'.$file;
												} else {
													$url = get_bloginfo('url').WPS_IMG_URL.'/forum/'.$post->tid.'/'.$child->tid.'/'.$file;
												}
												$ext = explode('.', $file);
												if (strpos(WPS_IMAGE_EXT, $ext[sizeof($ext)-1]) > 0) {
													// Image
													$html .= "<a target='_blank' href='".$url."' rel='symposium_forum_images-".$post->tid."'";
													$html .= ' title="'.$file.'">';
													if (WPS_FORUM_THUMBS == 'on') {
														list($width, $height, $type, $attr) = getimagesize($url);
														$max_width = WPS_FORUM_THUMBS_SIZE;
														if ($width > $max_width) {
															$height = $height / ($width / $max_width);
															$width = $max_width;
														}
														$html .= '<img src="'.$url.'" style="width:'.$width.'px; height:'.$height.'px" />';
													} else {
														$html .= $file;
													}
													$html .= '</a> ';
												} else {
													// Video
													if (strpos(WPS_VIDEO_EXT, $ext[sizeof($ext)-1]) > 0) {
														
														$video_id = $child->tid.'_'.$cnt;
														
														if (WPS_FORUM_THUMBS == 'on') {
															$html .= '<div id="mediaplayer'.$video_id.'">JW Player goes here</div> ';
														} else {
															$html .= '<div style="display:none">';
															$html .= '<div id="mediaplayer'.$video_id.'">JW Player goes here</div> ';
															$html .= '</div>';
															$html .= "<a href='#' class='jwplayer' title='".$file."' rel='mediaplayer".$video_id."'>".$file."</a> ";															
														}
														$html .= '<script type="text/javascript"> ';
														$html .= '	jwplayer("mediaplayer'.$video_id.'").setup({';
														$html .= '		flashplayer: "'.WP_PLUGIN_URL.'/wp-symposium/jwplayer/player.swf",';
														$html .= '		image: "'.WP_PLUGIN_URL.'/wp-symposium/jwplayer/preview.gif",';
														$html .= '		file: "'.$url.'",';
														$html .= '		width: "'.WPS_FORUM_THUMBS_SIZE.'px",';
														$html .= '		height: "250px"';
														$html .= '	});';
														$html .= '</script>';																																												
													} else {
														// Document
														$html .= "<a target='_blank' href='".$url."' title='".$file."'>".$file."</a> ";															
													}
												}
												if ($child->topic_owner == $current_user->ID || symposium_get_current_userlevel($current_user->ID) == 5) {
													$html .= '<img id="'.$post->tid.'/'.$child->tid.'" title="'.$file.'" class="remove_forum_image link_cursor" src="'.WPS_IMAGES_URL.'/delete.png" /> ';
												}
												$html .= '</div>';
												
											}
										}			
										closedir($handler);
									}
								}

							// Add Signature
							$signature = get_symposium_meta($child->topic_owner, 'signature');
							if ($signature != '') {
								$html .= '<div class="sep_top"><em>'.symposium_make_url(stripslashes($signature)).'</em></div>';
							}
							
							$html .= "</div>";
							
					$html .= "</div>";

					}
			
			} else {
		
				$html .= "<div class='child-reply'>";
				$html .= __("No replies posted yet.", "wp-symposium");
				$html .= "</div>";
				$html .= "<div class='sep'></div>";						
		
			}			

			$html .= "</div>";
	
			// Quick Reply
			if ($can_edit) {
				$html .= '<div id="reply-topic-bottom" name="reply-topic-bottom">';
				if ($wpdb->get_var($wpdb->prepare("SELECT allow_replies FROM ".$wpdb->prefix."symposium_topics WHERE tid = %d", $post->tid)) == "on")
				{
					$html .= '<input type="hidden" id="symposium_reply_tid" value="'.$post->tid.'">';
					$html .= '<input type="hidden" id="symposium_reply_cid" value="'.$cat_id.'">';
										
					$html .= '<div class="reply-topic-subject label">'.__("Reply to this Topic", "wp-symposium").'</div>';

					if (WPS_ELASTIC == 'on') { $elastic = ' elastic'; } else { $elastic = ''; }
					$html .= '<textarea class="textarea_Editor reply-topic-text'.$elastic.'" id="symposium_reply_text"></textarea>';

					// For admin's only set this as the answer
					if (WPS_USE_ANSWERS == 'on' && symposium_get_current_userlevel() == 5) {
						$html .= '<input type="checkbox" id="quick-reply-answer" /> '.__('Set this as the answer', 'wp-symposium').'<br />';
					}

					$html .= '<input type="submit" id="quick-reply-warning" class="symposium-button" style="float: left" value="'.__("Reply", "wp-symposium").'" />';

					// Upload
					if (WPS_FORUM_UPLOADS) {
						$html .= "<div id='symposium_user_login' style='display:none'>".strtolower($current_user->user_login)."</div>";
						$html .= "<div id='symposium_user_email' style='display:none'>".strtolower($current_user->user_email)."</div>";
						$html .= '<input id="forum_file_upload" name="file_upload" type="file" />';					
						$html .= '<div id="forum_file_list" style="clear:both">';
						
						if (WPS_IMG_DB == 'on') {
							
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
								$html .= '<img id="0" title="'.$file->filename.'" class="remove_forum_image link_cursor" src="'.WPS_IMAGES_URL.'/delete.png" /> ';
								$html .= '</div>';	
							}
							
						} else {
							
							// get list of uploaded files from file system
							$targetPath = WPS_IMG_PATH."/forum/".$post->tid.'_'.$current_user->ID.'_tmp/';
							if (file_exists($targetPath)) {
								$handler = opendir($targetPath);
								while ($file = readdir($handler)) {
									if ($file != "." && $file != ".." && $file != ".DS_Store") {
										$html .= '<div>';
										$html .= '<a href="'.WPS_IMG_URL.'/forum/'.$post->tid.'_'.$current_user->ID.'_tmp/'.$file.'"';
										$ext = explode('.', $file);
										if ($ext[sizeof($ext)-1]=='gif' || $ext[sizeof($ext)-1]=='jpg' || $ext[sizeof($ext)-1]=='png' || $ext[sizeof($ext)-1]=='jpeg') {
											$html .= ' target="_blank" rel="symposium_forum_images-'.$post->tid.'"';
										} else {
											$html .= ' target="_blank"';
										}
										$html .= ' title="'.$file.'">'.$file.'</a> ';
										$html .= '<img id="'.$post->tid.'_'.$current_user->ID.'_tmp" title="'.$file.'" class="remove_forum_image link_cursor" src="'.WPS_IMAGES_URL.'/delete.png" /> ';
										$html .= '</div>';
									}
								}			
								closedir($handler);
							$html .= '</div>';
							}	
													
						}

					}

				}				

				$html .= '</div>';
				
				// Add page title at the start
				$html = $topic_subject.' | '.get_bloginfo('name').'[|]'.$html;

			} else {
				if ($group_id == 0) {
					$html .= "<p>".__("The minimum level to reply on this forum is", "wp-symposium")." ".WPS_FORUM_EDITOR."</p>";				
				}
			}
		
		} else {
			$html = __('Sorry, this topic is no longer available.', 'wp-symposium');
		}
		
		echo symposium_smilies($html);
		
	} else {
		echo 'You do not have permission to view this topic, sorry.';
	}
	
	exit;
	
}

// Get Forum ****************************************************************
if ($_POST['action'] == 'getForum') {

	$cat_id = $_POST['cat_id'];
	
	if (isset($_POST['limit_from'])) { $limit_from = $_POST['limit_from']; } else { $limit_from = 0; }
	$group_id = $_POST['group_id'];
	
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

	
	// If in a group forum check that they are a member!
	if ( $continue ) {
		
		// Post preview
		$snippet_length = WPS_PREVIEW1;
		if ($snippet_length == '') { $snippet_length = '45'; }
		$snippet_length_long = WPS_PREVIEW2;
		if ($snippet_length_long == '') { $snippet_length_long = '45'; }
		
		if ($limit_from == 0) {
		
			$template = WPS_TEMPLATE_FORUM_HEADER;
			$template = str_replace("[]", "", stripslashes($template));
	
			// Breadcrumbs	
			$breadcrumbs = '<div id="forum_breadcrumbs" class="breadcrumbs label">';
			$allow_new = 'on';

			if ($cat_id > 0) {
		
				if (!WPS_LITE) {
		
					$this_level = $wpdb->get_row($wpdb->prepare("SELECT cid, title, allow_new, cat_parent FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d", $cat_id));
					$allow_new = $this_level->allow_new;
					if ($this_level->cat_parent == 0) {
						if (WPS_FORUM_AJAX == 'on') {
							$breadcrumbs .= '<a href="#cid=0" class="category_title" title="0">'.__('Forum Home', 'wp-symposium')."</a> &rarr; ";
							$breadcrumbs .= '<a href="#cid='.$this_level->cid.'" class="category_title" title="'.$this_level->cid.'">'.trim($this_level->title).'</a>';
						} else {
							$breadcrumbs .= '<a href="'.$forum_url.$q.'cid=0" title="0">'.__('Forum Home', 'wp-symposium')."</a> &rarr; ";
							$breadcrumbs .= '<a href="'.$forum_url.$q."cid=".$this_level->cid.'" title="'.$this_level->cid.'">'.trim($this_level->title).'</a>';
						}
					} else {
		
						$parent_level = $wpdb->get_row($wpdb->prepare("SELECT cid, title, cat_parent FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d", $this_level->cat_parent));
		
						if ($parent_level->cat_parent == 0) {
							if (WPS_FORUM_AJAX == 'on') {
								$breadcrumbs .= '<a href="#cid=0" class="category_title" title="0">'.__('Forum Home', 'wp-symposium')."</a> &rarr; ";
							} else {
								$breadcrumbs .= '<a href="'.$forum_url.$q.'cid=0" title="0">'.__('Forum Home', 'wp-symposium')."</a> &rarr; ";
							}
						} else {
							$parent_level_2 = $wpdb->get_row($wpdb->prepare("SELECT cid, title, cat_parent FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d", $parent_level->cat_parent));
							if (WPS_FORUM_AJAX == 'on') {
								$breadcrumbs .= '<a href="#cid=0" class="category_title" title="0">'.__('Forum Home', 'wp-symposium')."</a> &rarr; " ;
								$breadcrumbs .= '<a href="#cid='.$parent_level_2->cid.'" class="category_title" title="'.$parent_level_2->cid.'">'.$parent_level_2->title."</a> &rarr; ";
							} else {
								$breadcrumbs .= '<a href="'.$forum_url.$q.'cid=0" title="0">'.__('Forum Home', 'wp-symposium')."</span></a> &rarr; " ;
								$breadcrumbs .= '<a href="'.$forum_url.$q."cid=".$parent_level_2->cid.'"  title="'.$parent_level_2->cid.'">'.$parent_level_2->title."</a> &rarr; ";
							}
						}
						if (WPS_FORUM_AJAX == 'on') {
							$breadcrumbs .= '<a href="#cid='.$parent_level->cid.'" class="category_title" title="'.$parent_level->cid.'">'.$parent_level->title."</a> &rarr; " ;
							$breadcrumbs .= '<a href="#cid='.$this_level->cid.'" class="category_title" title="'.$this_level->cid.'">'.$this_level->title."</a>" ;
						} else {
							$breadcrumbs .= '<a href="'.$forum_url.$q."cid=".$parent_level->cid.'" title="'.$parent_level->cid.'">'.$parent_level->title."</a> &rarr; " ;
							$breadcrumbs .= '<a href="'.$forum_url.$q."cid=".$this_level->cid.'" title="'.$this_level->cid.'">'.$this_level->title."</a>" ;
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
	
			// Confirm if can post/reply or not
			$level = symposium_get_current_userlevel();
	
			if ( (WPS_FORUM_EDITOR == "Guest")
			 || (WPS_FORUM_EDITOR == "Subscriber" && $level >= 1)
			 || (WPS_FORUM_EDITOR == "Contributor" && $level >= 2)
			 || (WPS_FORUM_EDITOR == "Author" && $level >= 3)
			 || (WPS_FORUM_EDITOR == "Editor" && $level >= 4)
			 || (WPS_FORUM_EDITOR == "Administrator" && $level == 5) ) {
				$can_edit = true;
			 } else {
				$can_edit = false;
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
						if (WPS_ELASTIC == 'on') { $elastic = ' elastic'; } else { $elastic = ''; }
						$new_topic_form .= '<textarea class="new-topic-subject-text'.$elastic.'" id="new_topic_text">';
						$new_topic_form .= '</textarea>';
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
									$new_topic_form .= '<option value='.$category->cid;
									if ($cat_id > 0) {
										if ($category->cid == $cat_id) { $new_topic_form .= " SELECTED"; }
									} else {
										if ($category->cid == $defaultcat) { $new_topic_form .= " SELECTED"; }
									}
									$new_topic_form .= '>'.stripslashes($category->title).'</option>';
								}
					
								$new_topic_form .= '</select>';
							}
					
						} else {
							// No categories for groups
							$new_topic_form .= '<input name="new_topic_category" type="hidden" value="0">';
						}
	
						// Upload
						if (WPS_FORUM_UPLOADS) {
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
						$new_topic_form .= '<input style="margin: 0 0 10px 0;" type="checkbox" id="info_only"> '.__('This topic is for information only, no answer will be selected.', 'wp-symposium');
						$new_topic_form .= '</div>';
	
						$new_topic_form .= '<input id="symposium_new_post" type="submit" class="symposium-button" style="float: left" value="'.__("Post", "wp-symposium").'" />';
						$new_topic_form .= '<input id="cancel_post" type="submit" class="symposium-button clear" onClick="javascript:void(0)" value="'.__("Cancel", "wp-symposium").'" />';
	
	
						$new_topic_form .= '</div>';
	
					$new_topic_form .= '</div>';
	
				} else {
	
					if ($group_id == 0 && $allow_new == 'on') {
						$new_topic_form = "<p>".__("The minimum level to start a topic is", "wp-symposium")." ".WPS_FORUM_EDITOR."</p>";	
					}
					if ($group_id > 0 && $allow_new != 'on' && $member_count > 0) {
						$new_topic_form = "<p>".__("New topics are disabled on this forum.", "wp-symposium")."</p>";							
					}
					$new_topic_button = '';
					
				}
				
	
			} else {
	
				$new_topic_button = '';
	
				if (WPS_FORUM_LOGIN == "on") {
					$new_topic_form .= "<div>".__("Until you login, you can only view the forum.", "wp-symposium");
					$new_topic_form .= " <a href=".wp_login_url( get_permalink() )." class='simplemodal-login' title='".__("Login", "wp-symposium")."'>".__("Login", "wp-symposium").".</a></div><br />";
				}
	
			}
	
			// Options
			$digest = "";
			$subscribe = "";
			if (is_user_logged_in()) {
		
				$send_summary = WPS_SEND_SUMMARY;
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
			if (WPS_SHARING != '' && $cat_id > 0) {
				$sharing = show_sharing_icons($cat_id, 0, WPS_SHARING, $group_id);
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
					$template = WPS_TEMPLATE_GROUP_FORUM_CATEGORY;
				} else {
					$template = WPS_TEMPLATE_FORUM_CATEGORY;
				}
				$template = str_replace("[]", "", stripslashes($template));
				
				if ($categories) {
	
					// Start of table
					$html .= '<div id="symposium_table">';
	
						$num_cats = $wpdb->num_rows;
						$cnt = 0;
						foreach($categories as $category) {
					
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
									SELECT tid, topic_subject, topic_approved, topic_post, topic_date, topic_owner, topic_sticky, topic_parent, display_name, topic_category 
									FROM ".$wpdb->prefix."symposium_topics t 
									INNER JOIN ".$wpdb->base_prefix."users u ON u.ID = t.topic_owner
									WHERE (topic_approved = 'on' OR topic_owner = %d) AND topic_parent = 0 AND topic_category = %d ORDER BY topic_date DESC LIMIT 0,1", $current_user->ID, $category->cid)); 
	
								if ($last_topic) {
									
										if (!WPS_LITE) {
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
											if (WPS_FORUM_AJAX == 'on') {
												$subject = '<a title="'.$last_topic->tid.'" class="topic_subject backto row_link_topic" href="#cid='.$category->cid.',tid='.$last_topic->tid.'">'.stripslashes($subject).'</a> ';
											} else {
												$subject = '<a class="backto row_link_topic" href="'.$forum_url.$q."cid=".$last_topic->topic_category."&show=".$last_topic->tid.'">'.stripslashes($subject).'</a> ';
											}
											if ($reply->topic_approved != 'on') { $subject .= "<em>[".__("pending approval", "wp-symposium")."]</em> "; }
											$row_template = str_replace("[subject]", $subject, $row_template);	
											$row_template = str_replace("[ago]", symposium_time_ago($reply->topic_date).".", $row_template);	
										} else {
											$row_template = str_replace("[replied]", symposium_profile_link($last_topic->topic_owner)." ".__("started", "wp-symposium")." ", $row_template);	
											$subject = symposium_bbcode_remove($last_topic->topic_subject);
											if (WPS_FORUM_AJAX == 'on') {
												$subject = '<a title="'.$last_topic->tid.'" class="topic_subject backto row_link_topic" href="#cid='.$category->cid.',tid='.$last_topic->tid.'">'.stripslashes($subject).'</a> ';
											} else {
												$subject = '<a class="backto row_link_topic" href="'.$forum_url.$q."cid=".$last_topic->topic_category."&show=".$last_topic->tid.'">'.stripslashes($subject).'</a> ';
											}
											$row_template = str_replace("[subject]", $subject, $row_template);	
											$row_template = str_replace("[ago]", symposium_time_ago($last_topic->topic_date).".", $row_template);	
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
								if (WPS_USE_STYLES) {
									$text_color = WPS_TEXT_COLOR;
								} else {
									$text_color = '';
								}
								if (!WPS_LITE) {
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
								if (!WPS_LITE) {
									$topic_count = get_topic_count($category->cid);
		
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
	
									if ($recursive_new) {
										$category_title_html .= "<img src='".WPS_IMAGES_URL."/new.gif' alt='New!' /> ";
									}
								}
								
								// Category title
								if (WPS_FORUM_AJAX == 'on') {
									$category_title_html .= '<a class="category_title backto row_link" href="#cid='.$category->cid.'" title='.$category->cid.'>'.stripslashes($category->title).'</a>';
								} else {
									$category_title_html .= '<a class="backto row_link" href="'.$forum_url.$q."cid=".$category->cid.'">'.stripslashes($category->title).'</a> ';
								}
								$subscribed = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix."symposium_subs WHERE cid = %d AND uid = %d", $category->cid, $current_user->ID));
								if ($subscribed > 0 && $forum_all != 'on') { $category_title_html .= ' <img src="'.WPS_IMAGES_URL.'/orange-tick.gif" alt="'.__('Subscribed', 'wp-symposium').'" />'; } 
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
	
					$html .= '</div>';
			
				}
			}
		}
	
		// Show topics in this category ++++++++++++++++++++++++++++++++++++++++++++++++++
		if (!WPS_LITE) {
			$sql = "SELECT tid, topic_subject, topic_approved, topic_post, topic_owner, topic_category, topic_date, display_name, topic_sticky, allow_replies, topic_started, 
				(SELECT COUNT(tid) FROM ".$wpdb->prefix."symposium_topics s WHERE s.topic_parent = t.tid AND s.topic_answer = 'on') AS answers 
				FROM ".$wpdb->prefix."symposium_topics t INNER JOIN ".$wpdb->base_prefix."users u ON t.topic_owner = u.ID 
				WHERE (topic_approved = 'on' OR topic_owner = %d) AND topic_category = %d AND topic_parent = 0 AND topic_group = %d ORDER BY topic_sticky DESC, topic_date DESC 
				LIMIT %d, %d";
			$query = $wpdb->get_results($wpdb->prepare($sql, $current_user->ID, $cat_id, $group_id, $limit_from, $limit_count)); 
		} else {
			$sql = "SELECT tid, topic_subject, topic_approved, topic_post, topic_owner, topic_category, topic_date, display_name, topic_sticky, allow_replies, topic_started,
				0 AS answers
				FROM ".$wpdb->prefix."symposium_topics t INNER JOIN ".$wpdb->base_prefix."users u ON t.topic_owner = u.ID 
				WHERE (topic_approved = 'on' OR topic_owner = %d) AND topic_category = %d AND topic_parent = 0 AND topic_group = %d ORDER BY topic_sticky DESC, topic_date DESC 
				LIMIT %d, %d";
			$query = $wpdb->get_results($wpdb->prepare($sql, $current_user->ID, $cat_id, $group_id, $limit_from, $limit_count)); 
		}
	
		$num_topics = $wpdb->num_rows;
	
		// Row template		
		if ( $group_id > 0 ) {
			$template = WPS_TEMPLATE_GROUP_FORUM_TOPIC;
		} else {
			$template = WPS_TEMPLATE_FORUM_TOPIC;
		}
		$template = str_replace("[]", "", stripslashes($template));
			
		// Favourites
		$favs = get_symposium_meta($current_user->ID, 'forum_favs');
	
		$cnt = 0;
					
		if ($query) {
		
			if ($limit_from == 0) {
				$html .= '<div id="symposium_table">';		
			}
	
				// For every topic in this category 
				foreach ($query as $topic) {
				
					$cnt++;

					if (!WPS_LITE) {
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
					$closed_word = strtolower(WPS_CLOSED_WORD);
					if ( strpos(strtolower($topic->topic_subject), "[".$closed_word."]") > 0) {
						$color_check = ' transparent';
					} else {
						$color_check = '';
					}
					$html .= $color_check.'">';
	
						// Reset template
						$topic_template = $template;
					
						// Started by/Last Reply
						if (!WPS_LITE) {
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
									$topic_template = str_replace("[ago]", " ".symposium_time_ago($each_last_post->topic_date).".", $topic_template);	
									$post = stripslashes($each_last_post->topic_post);
									if ( strlen($post) > $snippet_length_long ) { $post = substr($post, 0, $snippet_length_long)."..."; }
									$post = symposium_bbcode_remove($post);
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
							$topic_template = str_replace("[ago]", " ".symposium_time_ago($topic->topic_date).".", $topic_template);	
							$topic_template = str_replace("[topic]", "", $topic_template);	
						}
				
						// Views
						if (!WPS_LITE) {
							$views_html = "<div class='post_count' style='color:".WPS_TEXT_COLOR.";'>".$reply_views."</div>";
							if ($reply_views != 1) { 
								$views_html .= "<div style='color:".WPS_TEXT_COLOR.";' class='post_count_label'>".__("VIEWS", "wp-symposium")."</div>";
							} else {
								$views_html .= "<div style='color:".WPS_TEXT_COLOR.";' class='post_count_label'>".__("VIEW", "wp-symposium")."</div>";						
							}
							$topic_template = str_replace("[views]", $views_html, $topic_template);	
						} else {
							$topic_template = str_replace("[views]", "", $topic_template);	
						}
				
						// Replies
						if (!WPS_LITE) {
							$replies_html = "<div class='post_count' style='color:".WPS_TEXT_COLOR.";'>".$replies."</div>";
							$replies_html .= "<div style='color:".WPS_TEXT_COLOR.";' class='post_count_label'>";
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
						if (current_user_can('level_10')) {
							$topic_title_html .= "<div class='topic-delete-icon'>";
							$topic_title_html .= "<a class='floatright delete_topic link_cursor' id='".$topic->tid."'style='width:16px;'><img src='".WPS_IMAGES_URL."/delete.png' style='width:16px; height:16px;' /></a>";
							$topic_title_html .= "</div>";
						}
			
						if (strpos($favs, "[".$topic->tid."]") === FALSE ) { } else {
							$topic_title_html .= "<img src='".WPS_IMAGES_URL."/fav-on.png' class='floatleft' style='height:18px; width:18px; margin-right:4px; margin-top:4px' />";						
						}								
			
						$subject = symposium_bbcode_remove($topic->topic_subject);
						$topic_title_html .= '<div class="row_link_div">';
	
							if (is_user_logged_in()) {		
								if ( ($topic->topic_started > $previous_login && $topic->topic_owner != $current_user->ID) || ($child_is_new) ) {
									$topic_title_html .= "<img src='".WPS_IMAGES_URL."/new.gif' alt='New!' /> ";
								}	
							}
										
							if ($topic->answers > 0) { $topic_title_html .= '<img src="'.WPS_IMAGES_URL.'/tick.png" alt="'.__('Answer accepted', 'wp-symposium').'" /> '; }
							if (WPS_FORUM_AJAX == 'on') {
								$topic_title_html .= '<a title="'.$topic->tid.'" href="#cid='.$topic->topic_category.',tid='.$topic->tid.'" class="topic_subject backto row_link">'.stripslashes($subject).'</a>';
							} else {
								$topic_title_html .= '<a class="backto row_link" href="'.$forum_url.$q."cid=".$topic->topic_category."&show=".$topic->tid.'">'.stripslashes($subject).'</a> ';							
							}
							if ($topic->topic_approved != 'on') { $topic_title_html .= " <em>[".__("pending approval", "wp-symposium")."]</em>"; }
							if (is_user_logged_in()) {
								$is_subscribed = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix."symposium_subs WHERE cid = 0 AND tid = ".$topic->tid." AND uid = ".$current_user->ID));
								if ($is_subscribed > 0 && $forum_all != 'on') { $topic_title_html .= ' <img src="'.WPS_IMAGES_URL.'/orange-tick.gif" alt="Subscribed" />'; } 
							}
							if ($topic->allow_replies != 'on') { $topic_title_html .= ' <img src="'.WPS_IMAGES_URL.'/padlock.gif" alt="Replies locked" />'; } 
							if ($topic->topic_sticky) { $topic_title_html .= ' <img src="'.WPS_IMAGES_URL.'/pin.gif" alt="Sticky Topic" />'; } 
			
						$topic_title_html .= "</div>";
						$post = stripslashes($topic->topic_post);
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

		$cat_title = $wpdb->get_var($wpdb->prepare("SELECT title FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d", $cat_id));
		if ($cat_title) {
			$html = $cat_title.' | '.get_bloginfo('name').'[|]'.$html;
		} else {
			$html = __('Forum', 'wp-symposium').' | '.get_bloginfo('name').'[|]'.$html;
		}
		
	} else {
		
		$html = "DONTSHOW";
		
	}

	echo $html;

}

function get_topic_count($cat) {
	
	global $wpdb, $current_user;

	$topic_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix."symposium_topics WHERE (topic_approved = 'on' OR topic_owner = %d) AND topic_parent = 0 AND topic_category = %d", $current_user->ID, $cat));

	$category_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix."symposium_cats WHERE cat_parent = %d", $cat));

	return $topic_count+$category_count;	
	exit;
}


// Reply to Topic ****************************************************************
if ($_POST['action'] == 'reply') {
	
	if (is_user_logged_in()) {

		$tid = $_POST['tid'];
		$cat_id = $_POST['cid'];
		$answered = $_POST['answered'];

		$reply_text = $_POST['reply_text'];
		
		$striptags = WPS_STRIPTAGS;
		if ($striptags == 'on') {
			$reply_text = strip_tags($reply_text);
		}
		
		$group_id = $_POST['group_id'];
	
		$wpdb->show_errors;
	
		if ($reply_text != '') {
	
			if (is_user_logged_in()) {

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
			
				// Check for moderation
				$moderation = WPS_MODERATION;
				if ($moderation == "on") {
					$topic_approved = "";
				} else {
					$topic_approved = "on";
				}
			
				// Invalidate HTML
				$reply_text = str_replace("<", "&lt;", $reply_text);
				$reply_text = str_replace(">", "&gt;", $reply_text);

				// Check for banned words
				$chatroom_banned = WPS_CHATROOM_BANNED;
				if ($chatroom_banned != '') {
					$badwords = $pieces = explode(",", $chatroom_banned);

					 for($i=0;$i < sizeof($badwords);$i++){
					 	if (strpos(' '.$reply_text.' ', $badwords[$i])) {
						 	$reply_text=eregi_replace($badwords[$i], "***", $reply_text);
					 	}
					 }
				}
							
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
					topic_approved,
					topic_group,
					topic_answer
				)
				VALUES ( %s, %d, %s, %s, %s, %d, %d, %d, %s, %d, %s )", 
		        array(
		        	'', 
		        	$cat_id,
		        	$reply_text, 
		        	date("Y-m-d H:i:s"), 
					date("Y-m-d H:i:s"), 
					$current_user->ID, 
					$tid,
					0,
					$topic_approved,
					$group_id,
					$answered
		        	) 
		        ) );
		
				// get new topic id (or response)
				$new_id = $wpdb->insert_id;

				// check for any attachments
				if (WPS_IMG_DB == "on") {
					
					$wpdb->query( $wpdb->prepare( "UPDATE ".$wpdb->base_prefix."symposium_topics_images SET tid = %d WHERE uid = %d AND tid = 0", $new_id, $current_user->ID ));
					
				} else {
					
					// File system
					if ($blog_id > 1) {
						$to_path = WP_CONTENT_DIR.'/wps-content/'.$blog_id.'/forum/'.$tid.'/'.$new_id;
						$tmp_path = WP_CONTENT_DIR.'/wps-content/'.$blog_id.'/forum/'.$tid.'_'.$current_user->ID.'_tmp';
					} else {
						$to_path = WP_CONTENT_DIR.'/wps-content/forum/'.$tid.'/'.$new_id;
						$tmp_path = WP_CONTENT_DIR.'/wps-content/forum/'.$tid.'_'.$current_user->ID.'_tmp';
					}
					if (file_exists($tmp_path)) {
						mkdir($to_path, 0777, true);
						// copy tmp files to new location
						$handler = opendir($tmp_path);
						while ($file = readdir($handler)) {
							if ($file != "." && $file != ".." && $file != ".DS_Store") {
								copy($tmp_path.'/'.$file, $to_path.'/'.$file);
								unlink($tmp_path.'/'.$file);
							}
						}
						rmdir($tmp_path);
						closedir($handler);
					}
					
				}

				// Update last activity (if posting to a group)
				if ($group_id > 0) {
					$wpdb->query( $wpdb->prepare( "UPDATE ".$wpdb->base_prefix."symposium_groups SET last_activity = %s WHERE gid = %d", array( date("Y-m-d H:i:s"), $group_id ) ));
				}
	        
				// Update main topic date for freshness
				$bump_topics = WPS_BUMP_TOPICS;
				if ($bump_topics == 'on') {
					$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix."symposium_topics SET topic_date = '".date("Y-m-d H:i:s")."' WHERE tid = ".$tid) );					
				}
			
				// Email people who want to know and prepare body
				$owner_name = $wpdb->get_var($wpdb->prepare("SELECT display_name FROM ".$wpdb->base_prefix."users WHERE ID = ".$current_user->ID));
				$parent = $wpdb->get_var($wpdb->prepare("SELECT topic_subject FROM ".$wpdb->prefix."symposium_topics WHERE tid = ".$tid));
			
				$body = "<span style='font-size:24px'>".$parent."</span><br /><br />";
				$body .= "<p>".$owner_name." ".__('replied', 'wp-symposium')."...</p>";
				$body .= "<p>".$reply_text."</p>";
				$url = $forum_url.$q."cid=".$cat_id."&show=".$tid;
				$body .= "<p>".$url."</p>";
				$body = str_replace(chr(13), "<br />", $body);
				$body = str_replace("\\r\\n", "<br />", $body);
				$body = str_replace("\\", "", $body);
			
				$email_list = '0,';
				if ($topic_approved == "on") {
					$query = $wpdb->get_results("
						SELECT user_email, ID
						FROM ".$wpdb->base_prefix."users u 
						RIGHT JOIN ".$wpdb->prefix."symposium_subs ON ".$wpdb->prefix."symposium_subs.uid = u.ID 
						WHERE u.ID != ".$current_user->ID." AND tid = ".$tid);
						
					if ($query) {						
						foreach ($query as $user) {	

							// Filter to allow further actions to take place
							apply_filters ('symposium_forum_newreply_filter', $user->ID, $current_user->ID, $current_user->display_name, $url);
					
							// Keep track of who sent to so far
							$email_list .= $user->ID.',';
					
							// Send mail
							symposium_sendmail($user->user_email, __('New Forum Reply', 'wp-symposium'), $body);							
						}
					}						

					// Now send to everyone who wants to know about all new topics and replies
					$email_list .= '0';
					$sql = "SELECT ID,user_email FROM ".$wpdb->base_prefix."users u 
						INNER JOIN ".$wpdb->base_prefix."symposium_usermeta m ON u.ID = m.uid 
						WHERE m.forum_all = 'on' AND
						ID != %d AND 
						ID NOT IN (%s)";
					$query = $wpdb->get_results($wpdb->prepare($sql, $current_user->ID, $email_list));
	
					if ($query) {						
						foreach ($query as $user) {	
							
							// If a group and a member of the group, or not a group forum...
							if ($group_id == 0 || symposium_member_of($group_id) == "yes") {
	
								// Filter to allow further actions to take place
								apply_filters ('symposium_forum_newreply_filter', $user->ID, $current_user->ID, $current_user->display_name, $url);
					
								// Send mail
								symposium_sendmail($user->user_email, __('New Forum Reply', 'wp-symposium'), $body);							
								
							}
						}
					}	
					
				} else {
					// Email admin if post needs approval
					$body = "<span style='font-size:24px; font-style:italic;'>".__("Moderation required for a reply", "wp-symposium")."</span><br /><br />".$body;
					symposium_sendmail(get_bloginfo('admin_email'), __('Moderation required for a reply', 'wp-symposium'), $body);
				}									
						
				exit;
			
			}
		}	
		
	}
	exit;
}

	
// AJAX to fetch forum activity
if ($_POST['action'] == 'getActivity') {

	// Work out link to this page, dealing with permalinks or not
	$thispage = symposium_get_url('forum');
	$q = symposium_string_query($thispage);
	
	$snippet_length = WPS_PREVIEW1;
	if ($snippet_length == '') { $snippet_length = '45'; }
	
	$html = '<div id="forum_activity_div">';
	
		$html .= '<div id="forum_activity_all_new_topics">';
		
			$html .= '<div id="forum_activity_title">'.__('Recent Topics', 'wp-symposium').'</div>';
		
			// All topics started
			$sql = "SELECT t.*, u.display_name FROM ".$wpdb->prefix."symposium_topics t LEFT JOIN ".$wpdb->base_prefix."users u ON t.topic_owner = u.ID WHERE t.topic_approved = 'on' AND topic_parent = 0 ORDER BY topic_started DESC LIMIT 0,40";
	
			$topics = $wpdb->get_results($sql);
			if ($topics) {
				foreach ($topics as $topic) {		
					$html .= "<div class='forum_activity_new_topic_subject'><a href='".$thispage.$q.'cid='.$topic->topic_category.'&show='.$topic->tid."'>".symposium_bbcode_remove(stripslashes($topic->topic_subject))."</a></div>";
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
		
			$html .= '<div id="forum_activity_title">'.__('You recently started', 'wp-symposium').'</div>';
		
			// Topics Started
			$sql = "SELECT * FROM ".$wpdb->prefix."symposium_topics WHERE topic_approved = 'on' AND topic_owner = %d AND topic_parent = 0 ORDER BY topic_started DESC LIMIT 0,100";
	
			$topics = $wpdb->get_results($wpdb->prepare($sql, $current_user->ID));
			if ($topics) {
				foreach ($topics as $topic) {		
					$html .= "<div class='forum_activity_new_topic_subject'><a href='".$thispage.$q.'cid='.$topic->topic_category.'&show='.$topic->tid."'>".symposium_bbcode_remove(stripslashes($topic->topic_subject))."</a>, ".symposium_time_ago($topic->topic_date)."</div>";
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
		
			$html .= '<div id="forum_activity_title">'.__('You recent replied', 'wp-symposium').'</div>';
		
			// Topics Replied to
			
			$shown = '';
			$sql = "SELECT t.*, t2.topic_subject, p.tid as parent_tid, p.topic_owner as parent_owner, p.topic_date as parent_date FROM ".$wpdb->prefix."symposium_topics t LEFT JOIN ".$wpdb->prefix."symposium_topics t2 ON t.topic_parent = t2.tid LEFT JOIN ".$wpdb->prefix."symposium_topics p ON t.topic_parent = p.tid WHERE t.topic_approved = 'on' AND t.topic_owner = %d AND t.topic_parent > 0 ORDER BY t.topic_date DESC LIMIT 0,75";
			
			$topics = $wpdb->get_results($wpdb->prepare($sql, $current_user->ID));
			if ($topics) {
				foreach ($topics as $topic) {	
					
					if (strpos($shown, $topic->topic_parent.",") === FALSE) { 
						$html .= "<div class='forum_activity_new_topic_subject'><a href='".$thispage.$q.'cid='.$topic->topic_category.'&show='.$topic->topic_parent."'>".symposium_bbcode_remove(stripslashes($topic->topic_subject))."</a></div>";
						$text = symposium_bbcode_remove(stripslashes($topic->topic_post));
						if ( strlen($text) > $snippet_length ) { $text = substr($text, 0, $snippet_length)."..."; }
						$html .= $text;
						if (WPS_USE_ANSWERS == 'on' && $topic->topic_answer == 'on') {
							$html .= ' <img style="width:12px; height:12px" src="'.WPS_IMAGES_URL.'/tick.png" alt="'.__('Answer Accepted', 'wp-symposium').'" />';
						}
						$html .= "<br />";
						$html .= "<em>";
						$html .= __("You replied", "wp-symposium")." ".symposium_time_ago($topic->topic_date);
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

// AJAX to fetch group forum activity
if ($_POST['action'] == 'getAllActivity') {

	$previous_login = get_symposium_meta($current_user->ID, 'previous_login');

	$gid = $_POST['gid'];	

	// Work out link to this page, dealing with permalinks or not
	if ($gid == 0) {
		$thispage = symposium_get_url('forum');
		if ($thispage[strlen($thispage)-1] != '/') { $thispage .= '/'; }
		if (strpos($thispage, "?") === FALSE) { 
			$q = "?";
		} else {
			// No Permalink
			$q = "&";
		}
	} else {
		$thispage = symposium_get_url('group');
		if ($thispage[strlen($thispage)-1] != '/') { $thispage .= '/'; }
		if (strpos($thispage, "?") === FALSE) { 
			$q = "?";
		} else {
			// No Permalink
			$q = "&";
		}
		$q .= "gid=".$gid."&";
	}
	
	$preview = 50;	
	$postcount = 100; // Tries to retrieve last 7 days, but this will be a maximum
	
	$include = strtotime("now") - (86400 * 7); // 1 week
	$include = date("Y-m-d H:i:s", $include);
	
	$html = '<div id="forum_activity_div">';
	
		// All topics started
		$posts = $wpdb->get_results("
			SELECT tid, topic_subject, topic_owner, topic_post, topic_category, topic_date, display_name, topic_parent, topic_answer 
			FROM ".$wpdb->prefix.'symposium_topics'." t INNER JOIN ".$wpdb->base_prefix.'users'." u ON t.topic_owner = u.ID 
			WHERE topic_approved = 'on' AND topic_date > '".$include."' AND topic_group = ".$gid." ORDER BY tid DESC LIMIT 0,".$postcount); 

		if ($posts) {

			foreach ($posts as $post)
			{
				$html .= "<div style='clear:both'>";
					$html .= "<div class='symposium_latest_forum_row_avatar'>";
						$html .= get_avatar($post->topic_owner, 20);
					$html .= "</div>";
					$html .= "<div style='margin-bottom: 3px; float:left;'>";
						if ($post->topic_parent > 0) {
							$text = stripslashes($post->topic_post);
							if ( strlen($text) > $preview ) { $text = substr($text, 0, $preview)."..."; }
							$html .= symposium_profile_link($post->topic_owner)." ".__('replied', 'wp-symposium')." ";
							$html .= "<a href='".$thispage.$q."cid=".$post->topic_category."&show=".$post->topic_parent."'>";
							$html .= $text."</a> ".symposium_time_ago($post->topic_date).".";
							if (WPS_USE_ANSWERS == 'on' && $post->topic_answer == 'on') {
								$html .= ' <img style="width:12px; height:12px" src="'.WPS_IMAGES_URL.'/tick.png" alt="'.__('Answer Accepted', 'wp-symposium').'" />';
							}
							$html .= "<br>";
						} else {
							$text = stripslashes($post->topic_subject);
							if ( strlen($text) > $preview ) { $text = substr($text, 0, $preview)."..."; }
							$html .= symposium_profile_link($post->topic_owner)." ".__('started', 'wp-symposium')." <a href='".$thispage.$q."cid=".$post->topic_category."&show=".$post->tid."'>".$text."</a> ".symposium_time_ago($post->topic_date).".<br>";
						}
					$html .= "</div>";
					if ($post->topic_date > $previous_login && $post->topic_owner != $current_user->ID && is_user_logged_in()) {
						$html .= "<div style='float:left;'>";
							$html .= "<img src='".WPS_IMAGES_URL."/new.gif' alt='New!' /> ";
						$html .= "</div>";
					}		

				$html .= "</div>";
			}

		} else {
			$html .= "<p>".__("No topics started yet", "wp-symposium").".</p>";
		}
	
	$html .= '</div>';
	
	echo $html;
	exit;
}

// AJAX to fetch forum activity as threads
if ($_POST['action'] == 'getThreadsActivity') {

	$previous_login = get_symposium_meta($current_user->ID, 'previous_login');

	$gid = $_POST['gid'];
	
	$html = '<div id="forum_activity_div">';
	$html .= showThreadChildren(0, 0, $gid, $previous_login);	
	$html .= '</div>';
	
	echo $html;
	exit;
}

function showThreadChildren($parent, $level, $gid, $previous_login) {
	
	global $wpdb, $current_user;

	// Work out link to this page, dealing with permalinks or not
	if ($gid == 0) {
		$thispage = symposium_get_url('forum');
		if ($thispage[strlen($thispage)-1] != '/') { $thispage .= '/'; }
		if (strpos($thispage, "?") === FALSE) { 
			$q = "?";
		} else {
			// No Permalink
			$q = "&";
		}
	} else {
		$thispage = symposium_get_url('group');
		if ($thispage[strlen($thispage)-1] != '/') { $thispage .= '/'; }
		if (strpos($thispage, "?") === FALSE) { 
			$q = "?";
		} else {
			// No Permalink
			$q = "&";
		}
		$q .= "gid=".$gid."&";
	}
	
	$html = "";
	
	$preview = 50 - (10*$level);	
	if ($preview < 10) { $preview = 10; }
	$postcount = 20; // Tries to retrieve last 7 days, but this will be a maximum number of posts or replies
	
	if ($level == 0) {
		$avatar_size = 30;
		$margin_top = 10;
		$desc = "DESC";
	} else {
		$avatar_size = 20;
		$margin_top = 3;
		$desc = "";
	}

	$include = strtotime("now") - (86400 * 7); // 1 week
	$include = date("Y-m-d H:i:s", $include);

	// All topics started
	$posts = $wpdb->get_results("
		SELECT tid, topic_subject, topic_owner, topic_post, topic_category, topic_date, display_name, topic_parent, topic_answer, topic_started 
		FROM ".$wpdb->prefix.'symposium_topics'." t INNER JOIN ".$wpdb->base_prefix.'users'." u ON t.topic_owner = u.ID 
		WHERE topic_approved = 'on' AND topic_parent = ".$parent." AND topic_group = ".$gid." AND topic_date > '".$include."' ORDER BY tid ".$desc." LIMIT 0,".$postcount); 

	if ($posts) {

		foreach ($posts as $post)
		{
			$html .= "<div style='clear:both; padding-left: ".($level*40)."px; overflow: auto; margin-top:".$margin_top."px;'>";		
				$html .= "<div class='symposium_latest_forum_row_avatar'>";
					$html .= get_avatar($post->topic_owner, $avatar_size);
				$html .= "</div>";
				$html .= "<div style='margin-bottom: 3px; float:left;'>";
					if ($post->topic_parent > 0) {
						$text = stripslashes($post->topic_post);
						if ( strlen($text) > $preview ) { $text = substr($text, 0, $preview)."..."; }
						$html .= symposium_profile_link($post->topic_owner)." ".__('replied', 'wp-symposium')." ";
						$html .= "<a href='".$thispage.$q."cid=".$post->topic_category."&show=".$post->topic_parent."'>";
						$html .= $text."</a> ".symposium_time_ago($post->topic_date);
						if (WPS_USE_ANSWERS == 'on' && $post->topic_answer == 'on') {
							$html .= ' <img style="width:12px; height:12px" src="'.WPS_IMAGES_URL.'/tick.png" alt="'.__('Answer Accepted', 'wp-symposium').'" />';
						}
						$html .= "<br>";
					} else {
						$text = stripslashes($post->topic_subject);
						if ( strlen($text) > $preview ) { $text = substr($text, 0, $preview)."..."; }
						$html .= symposium_profile_link($post->topic_owner)." ".__('started', 'wp-symposium')." <a href='".$thispage.$q."cid=".$post->topic_category."&show=".$post->tid."'>".$text."</a> ".symposium_time_ago($post->topic_started).".<br>";
					}
				$html .= "</div>";
				if ($post->topic_date > $previous_login && $post->topic_owner != $current_user->ID && is_user_logged_in()) {
					$html .= "<img src='".WPS_IMAGES_URL."/new.gif' alt='New!' /> ";
				}
			$html .= "</div>";
			
			$html .= showThreadChildren($post->tid, $level+1, $gid, $previous_login);
			
		}
	}	
	
	return $html;
}

// AJAX to fetch favourites
if ($_POST['action'] == 'getFavs') {
	
	if (is_user_logged_in()) {

		$snippet_length_long = WPS_PREVIEW2;
		if ($snippet_length_long == '') { $snippet_length_long = '45'; }
	
		$html = '';
	
		$favs = get_symposium_meta($current_user->ID, 'forum_favs');
		$favs = explode('[', $favs);
		if ($favs) {
			foreach ($favs as $fav) {
				$fav = str_replace("]", "", $fav);
				if ($fav != '') {
				
					$post = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."symposium_topics WHERE tid = %d", $fav));

					// Work out link to this page, dealing with permalinks or not
					$gid = $post->topic_group;
					if ($gid == 0) {
						$thispage = symposium_get_url('forum');
						if ($thispage[strlen($thispage)-1] != '/') { $thispage .= '/'; }
						if (strpos($thispage, "?") === FALSE) { 
							$q = "?";
						} else {
							// No Permalink
							$q = "&";
						}
					} else {
						$thispage = symposium_get_url('group');
						if ($thispage[strlen($thispage)-1] != '/') { $thispage .= '/'; }
						if (strpos($thispage, "?") === FALSE) { 
							$q = "?";
						} else {
							// No Permalink
							$q = "&";
						}
						$q .= "gid=".$gid."&";
					}

					$html .= '<div id="fav_'.$fav.'" class="fav_row" style="padding:6px; margin-bottom:10px;">';

						$html .= " <a title='".$fav."' class='symposium-delete-fav' style='cursor:pointer'><img src='".WPS_IMAGES_URL."/delete.png' style='width:16px;height:16px' /></a>";
				
						$html .= '<div class="forum_activity_new_topic_subject"><a href="'.$thispage.$q.'cid='.$post->topic_category.'&show='.$post->tid.'">'.stripslashes($post->topic_subject).'</a></div>';

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
		
			$html .= __("You can add your favourite forum topics by clicking on the heart beside any forum topic title.", "wp-symposium");
		}
	
		echo $html;
		
	}
	exit;
}

// AJAX function to toggle post as a favourite
if ($_POST['action'] == 'toggleFav') {

	if (is_user_logged_in()) {

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
		
	}
	exit;

}
	
// AJAX function to get topic details for editing
if ($_POST['action'] == 'getEditDetails') {

	if (is_user_logged_in()) {

		$tid = $_POST['tid'];	
	
		$details = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix.'symposium_topics'." WHERE tid = ".$tid); 
		if ($details->topic_subject == '') {
			$parent = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix.'symposium_topics'." WHERE tid = ".$details->topic_parent); 
			$subject = $parent->topic_subject;
		} else {
			$subject = $details->topic_subject;
		}
		$subject = str_replace("&lt;", "<", $subject);	
		$subject = str_replace("&gt;", ">", $subject);	
		$topic_post = $details->topic_post;
		//$topic_post = str_replace("&lt;", "<", $topic_post);	
		//$topic_post = str_replace("&gt;", ">", $topic_post);	

		if ($details) {
			echo stripslashes($subject)."[split]".stripslashes($topic_post)."[split]".$details->topic_parent."[split]".$details->tid."[split]".$details->topic_category;
		} else {
			echo "Problem retrieving topic information[split]Passed Topic ID = ".$tid;
		}
		
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
	
	if (is_user_logged_in()) {

		$tid = $_POST['tid'];	

		$topic_subject = $_POST['topic_subject'];	
		$topic_post = $_POST['topic_post'];	
		
		if (WPS_STRIPTAGS == 'on') {
			$topic_subject = strip_tags($topic_subject);	
			$topic_post = strip_tags($topic_post);	
		}
		
		$topic_subject = addslashes($topic_subject);	
		$topic_post = addslashes($topic_post);	
		$topic_post = str_replace("\n", chr(13), $topic_post);	
		$topic_category = $_POST['topic_category'];
		
		// Ensure safe HTML
		$topic_subject = str_replace("<", "&lt;", $topic_subject);	
		$topic_subject = str_replace(">", "&gt;", $topic_subject);	
		$topic_post = str_replace("<", "&lt;", $topic_post);	
		$topic_post = str_replace(">", "&gt;", $topic_post);
		
		$topic_post = stripslashes($topic_post);
		
		if ($topic_category == "") {
			$topic_category = $wpdb->get_var($wpdb->prepare("SELECT topic_category FROM ".$wpdb->prefix.'symposium_topics'." WHERE tid = ".$tid));
		}

		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix."symposium_topics SET topic_category = ".$topic_category." WHERE topic_parent = ".$tid) );

		$sql = "UPDATE ".$wpdb->prefix."symposium_topics SET topic_subject = %s, topic_post = %s, topic_category = %d WHERE tid = %d";
		$wpdb->query( $wpdb->prepare($sql, $topic_subject, $topic_post, $topic_category, $tid) );
			
		$parent = $wpdb->get_var($wpdb->prepare("SELECT topic_parent FROM ".$wpdb->prefix.'symposium_topics'." WHERE tid = ".$tid));
		
	}
	
	exit;
}

// AJAX function to subscribe/unsubscribe to symposium topic
if ($_POST['action'] == 'updateForum') {
	
	if (is_user_logged_in()) {

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
		
	}
	exit;
}

// AJAX function to change sticky status
if ($_POST['action'] == 'updateForumSticky') {

	if (is_user_logged_in()) {

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
		
	}
	exit;
}

// AJAX function to change allow replies status
if ($_POST['action'] == 'updateTopicReplies') {

	if (is_user_logged_in()) {

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
		
	}
	exit;
}

// AJAX function to change allow replies status
if ($_POST['action'] == 'toggleForInfo') {

	if (is_user_logged_in()) {

		$topics = $wpdb->prefix . 'symposium_topics';

		$tid = $_POST['tid'];
		$value = $_POST['value'];

		// Store subscription if wanted
		$wpdb->query("UPDATE ".$topics." SET for_info = '".$value."' WHERE tid = ".$tid);
	
		if ($value=='on') {
			echo "Topic expected an answer";
		} else {
			echo "Topic is for info only";
		}
		
	}
	exit;
}

// AJAX function to subscribe/unsubscribe to new symposium topics
if ($_POST['action'] == 'updateForumSubscribe') {

	if (is_user_logged_in()) {

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
	}
	exit;

}

// Do search
if ($_POST['action'] == 'getSearch') {

	$term = $_POST['term'];
	$found_count=0;
	$max_return=20; // Helps with avoiding return huge amounts of HTML (and unresponsive page)

	$html = '<div id="forum_activity_div">';
 
	if (trim($term) != '') {
						
		$sql = "SELECT t.*, p.tid AS parent_tid, u2.display_name as parent_display_name, p.topic_subject AS parent_topic_subject, p.topic_started AS parent_topic_started, u.display_name 
			FROM ".$wpdb->prefix."symposium_topics t 
			LEFT JOIN ".$wpdb->base_prefix."users u ON t.topic_owner = u.ID 
			LEFT JOIN ".$wpdb->prefix."symposium_topics p ON t.topic_parent = p.tid 
			LEFT JOIN ".$wpdb->base_prefix."users u2 ON p.topic_owner = u2.ID 
			WHERE t.topic_approved = 'on' && (t.topic_subject LIKE '%".$term."%' OR t.topic_post LIKE '%".$term."%' OR u.display_name LIKE '%".$term."%') 
			ORDER BY t.topic_started LIMIT 0,40";

		$topics = $wpdb->get_results($sql);
		if ($topics) {

			foreach ($topics as $topic) {		

				$gid = $topic->topic_group;
				
				if ($gid == 0 || symposium_member_of($gid) == "yes") {

					if ($found_count > $max_return) { 
						$html .= '<p>'.sprintf(__('A maxium of %d search results will be displayed, please narrow your search.', 'wp-symposium'), $max_return).'</p>';
						break; 
					}

					$found_count++;

					// Work out link to this page, dealing with permalinks or not
					if ($gid == 0) {
						$thispage = symposium_get_url('forum');
						if ($thispage[strlen($thispage)-1] != '/') { $thispage .= '/'; }
						if (strpos($thispage, "?") === FALSE) { 
							$q = "?";
						} else {
							// No Permalink
							$q = "&";
						}
					} else {
						$thispage = symposium_get_url('group');
						if ($thispage[strlen($thispage)-1] != '/') { $thispage .= '/'; }
						if (strpos($thispage, "?") === FALSE) { 
							$q = "?";
						} else {
							// No Permalink
							$q = "&";
						}
						$q .= "gid=".$gid."&";
					}

					$html .= "<div class='symposium_search_subject_row_div'>";
					
						$html .= "<div class='symposium_search_subject_div'>";

						if ($topic->topic_parent != 0) {
							$html .= $topic->display_name.' ';
							$html .= __("in reply to", "wp-symposium")." ";
							$topic_subject = symposium_bbcode_remove(stripslashes($topic->parent_topic_subject));
							$topic_subject = preg_replace(
							  "/(>|^)([^<]+)(?=<|$)/esx",
							  "'\\1' . str_replace('" . $term . "', '<span class=\"symposium_search_highlight\">" . $term . "</span>', '\\2')",
							  $topic_subject
							);
							$html .= "<a class='symposium_search_subject' href='".$thispage.$q.'cid='.$topic->topic_category.'&show='.$topic->parent_tid."'>".stripslashes($topic_subject)."</a> ";
							$html .= __("by", "wp-symposium")." ".$topic->parent_display_name.", ".symposium_time_ago($topic->parent_topic_started).".";
						} else {
							$topic_subject = symposium_bbcode_remove(stripslashes($topic->topic_subject));
							$topic_subject = preg_replace(
							  "/(>|^)([^<]+)(?=<|$)/iesx",
							  "'\\1' . str_replace('" . $term . "', '<span class=\"symposium_search_highlight\">" . $term . "</span>', '\\2')",
							  $topic_subject
							);
							$html .= "<a class='symposium_search_subject' href='".$thispage.$q.'cid='.$topic->topic_category.'&show='.$topic->tid."'>".stripslashes($topic_subject)."</a> ";
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
				
			}
		
		}

		$html .= "<p><br />".__("Results found:", "wp-symposium")." ".$found_count."</p>";				

	}
	
	$html .= '</div>';
	
	//$html .= " ".$wpdb->last_query;
	echo $html;
	exit;
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
			$title = $wpdb->get_var($wpdb->prepare("SELECT topic_subject FROM ".$wpdb->prefix."symposium_topics WHERE tid = %d", $topic_id));
			$title = symposium_strip_smilies($title);
		} else {
			$title = '';
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
			$pageURL = str_replace("%26", "&", $pageURL);	
			$html .= "<img id='share_permalink' title='".$pageURL."' src='".WPS_IMAGES_URL."/link-icon.gif' style='cursor:pointer; margin-left:3px; height:22px; width:22px' alt='Permalink icon' />";
		}
		// Email
		if (!(strpos($sharing, "em") === FALSE)) {
			$html .= "<a title='".__('Share via email', 'wp-symposium')."' href='mailto:%20?subject=".str_replace(" ", "%20", $title)."&body=".$pageURL."'>";
			$html .= "<img src='".WPS_IMAGES_URL."/email-icon.gif' style='margin-left:3px; height:22px; width:22px;' alt='Email icon' /></a>";
		}
		// Facebook
		if (!(strpos($sharing, "fb") === FALSE)) {
			$html .= "<a target='_blank' title='".__('Share on Facebook', 'wp-symposium')."' href='http://www.facebook.com/share.php?u=".$pageURL."&t=".$title."'>";
			$html .= "<img src='".WPS_IMAGES_URL."/facebook-icon.gif' style=margin-left:3px; 'height:22px; width:22px' alt='Facebook icon' /></a>";
		}
		// Twitter
		if (!(strpos($sharing, "tw") === FALSE)) {
			$html .= "<a target='_blank' title='".__('Share on Twitter', 'wp-symposium')."' href='http://twitter.com/home?status=".$pageURL."'>";
			$html .= "<img src='".WPS_IMAGES_URL."/twitter-icon.gif' style='margin-left:3px; height:22px; width:22px' alt='Twitter icon' /></a>";
		}
		// Bebo
		if (!(strpos($sharing, "be") === FALSE)) {
			$html .= "<a target='_blank' title='".__('Share on Bebo', 'wp-symposium')."' href='http://www.bebo.com/c/share?Url=".$pageURL."&Title=".$title."'>";
			$html .= "<img src='".WPS_IMAGES_URL."/bebo-icon.gif' style=margin-left:3px; 'height:22px; width:22px' alt='Bebo icon' /></a>";
		}
		// LinkedIn
		if (!(strpos($sharing, "li") === FALSE)) {
			$html .= "<a target='_blank' title='".__('Share on LinkedIn', 'wp-symposium')."' href='http://www.linkedin.com/shareArticle?mini=true&url=".$pageURL."&title=".$title."'>";
			$html .= "<img src='".WPS_IMAGES_URL."/linkedin-icon.gif' style='margin-left:3px; height:22px; width:22px' alt='LinkedIn icon' /></a>";
		}
		// MySpace
		if (!(strpos($sharing, "ms") === FALSE)) {
			$html .= "<a target='_blank' title='".__('Share on MySpace', 'wp-symposium')."' href='http://www.myspace.com/Modules/PostTo/Pages/?u=".$pageURL."&t=".$title."'>";
			$html .= "<img src='".WPS_IMAGES_URL."/myspace-icon.gif' style='margin-left:3px; height:22px; width:22px' alt='MySpace icon' /></a>";
		}







	$html .= "</div>";	
	
	return $html;
}

?>
