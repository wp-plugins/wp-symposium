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
			$sql = "SELECT topic_parent, topic_owner FROM ".$wpdb->prefix."symposium_topics WHERE tid = %d";
			$topic_info = $wpdb->get_row($wpdb->prepare($sql, $tid));
			$topic_parent = $topic_info->topic_parent;
			$topic_owner = $topic_info->topic_owner;

			// Get owner of original topic post
			$sql = "SELECT topic_owner FROM ".$wpdb->prefix."symposium_topics WHERE tid = %d";
			$original_post_owner = $wpdb->get_var($wpdb->prepare($sql, $topic_parent));
			
			// Check to see if any answers already exits
			$sql = "SELECT COUNT(topic_answer) FROM ".$wpdb->prefix."symposium_topics WHERE topic_parent = %d AND topic_answer = 'on'";
			$answers = $wpdb->get_var($wpdb->prepare($sql, $topic_parent));			
			
			// Now clear all accepted answers for this topic (in case selected a different answer from previously chosen answer)			
			$sql = "UPDATE ".$wpdb->prefix."symposium_topics SET topic_answer = '' WHERE topic_parent = %d";
			$wpdb->get_var($wpdb->prepare($sql, $topic_parent));
			
			// Finally update new accepted answer
			$sql = "UPDATE ".$wpdb->prefix."symposium_topics SET topic_answer = 'on' WHERE tid = %d";
			$wpdb->get_var($wpdb->prepare($sql, $tid));
	
			// Prepare to return comments in JSON format
			$return_arr = array();		        
			
			// Hook for answer removed (previously there was an topic reply with topic_answer set to 'on'
			if ($answers) {
				do_action('symposium_forum_answer_removed_hook', $tid, $topic_owner, $topic_parent, $original_post_owner, $current_user->ID);
				$row_array['message'] = __('You have change the answer. If a better answer is posted, you can change your selection again.', 'wp-symposium');
				$row_array['title'] = __('Answer accepted', 'wp-symposium');
			} else {
				$row_array['message'] = __('You have accepted an answer. If a better answer is posted, you can change your selection.', 'wp-symposium');
				$row_array['title'] = __('Answer accepted', 'wp-symposium');
			}
			array_push($return_arr, $row_array);

			// Hook for answer accepted
			do_action('symposium_forum_answer_accepted_hook', $tid, $topic_owner, $topic_parent, $original_post_owner, $current_user->ID);

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
	
	if (get_option('symposium_img_db') == "on") {

		if ($wpdb->query( $wpdb->prepare( "DELETE FROM ".$wpdb->prefix."symposium_topics_images WHERE filename = %s AND tid = %d AND uid = %d", $file, $folder, $current_user->ID  ) ) ) {
			$html .= 'OK';
		} else {
			$html .= __('Failed to remove image from database', 'wp-symposium');
		}

	} else {
		
		if ($blog_id > 1) {			
			$src = get_option('symposium_img_path').'/'.$blog_id.'/forum/'.$folder.'/'.$file;
		} else {
			$src = get_option('symposium_img_path').'/forum/'.$folder.'/'.$file;
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
			
			// Hook for more actions
			do_action('symposium_forum_delete_reply_hook', $owner, $tid);			
		
		} else {
			echo "NOT ADMIN OR OWNER";
		}
	
	}
}

// Delete Topic and Replies *************************************************
if ($_POST['action'] == 'deleteTopic') {

	if (is_user_logged_in()) {

		$tid = $_POST['topic_id'];
		$topic_owner = $wpdb->get_var($wpdb->prepare("SELECT topic_owner FROM ".$wpdb->prefix."symposium_topics WHERE tid = %d", $tid));

		if (current_user_can('level_10') || $current_user->ID == $topic_owner) {
			if (symposium_safe_param($tid)) {
				$wpdb->query("DELETE FROM ".$wpdb->prefix."symposium_topics WHERE topic_parent = ".$tid);
				$wpdb->query("DELETE FROM ".$wpdb->prefix."symposium_topics_scores WHERE tid = ".$tid);
				$wpdb->query("DELETE FROM ".$wpdb->prefix."symposium_topics WHERE tid = ".$tid);
				$wpdb->query("DELETE FROM ".$wpdb->prefix."symposium_subs WHERE tid = ".$tid);
				
				// Delete comment
				$sql = "DELETE FROM ".$wpdb->base_prefix."symposium_comments WHERE subject_uid = ".$current_user->ID." 
					AND author_uid = ".$current_user->ID." 
					AND comment LIKE '%".__('Started a new forum topic:', 'wp-symposium')."%' 
					AND comment LIKE '%show=".$tid."%' 
					AND type = 'forum'";					
				$wpdb->query($sql);	
							
			}

			// Hook for more actions
			do_action('symposium_forum_delete_topic_hook', $topic_owner, $tid);			
		
			echo $tid;
		
		} else {
			echo "NOT ADMIN OR OWNER";
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

		if (get_option('symposium_striptags') == 'on') {
			$new_topic_subject = strip_tags($new_topic_subject);
			$new_topic_text = strip_tags($new_topic_text);
		}
		
		// Check for moderation
		if (get_option('symposium_moderation') == "on" && symposium_get_current_userlevel() < 5) {
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
	
		// Don't allow HTML in subject
		$new_topic_subject = str_replace("<", "&lt;", $new_topic_subject);
		$new_topic_subject = str_replace(">", "&gt;", $new_topic_subject);
		// Don't allow HTML in subject if not using WYSIWYG editor
		if (get_option('symposium_use_wysiwyg') != 'on') {
			$new_topic_text = str_replace("<", "&lt;", $new_topic_text);
			$new_topic_text = str_replace(">", "&gt;", $new_topic_text);
		}

		// Check for banned words
		$chatroom_banned = get_option('symposium_chatroom_banned');
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
		
		$stub = symposium_create_stub($new_topic_subject);

		$wpdb->query( $wpdb->prepare( "
			INSERT INTO ".$wpdb->prefix."symposium_topics 
			( 	topic_subject,
				stub,
				topic_category, 
				topic_post, 
				topic_date, 
				topic_started, 
				topic_owner, 
				topic_parent, 
				topic_views,
				topic_approved,
				for_info,
				topic_group,
				remote_addr,
				http_x_forwarded_for
			)
			VALUES ( %s, %s, %d, %s, %s, %s, %d, %d, %d, %s, %s, %d, %s, %s )", 
	        array(
	        	$new_topic_subject,
	        	$stub,
	        	$new_topic_category,
	        	$new_topic_text, 
	        	date("Y-m-d H:i:s"), 
				date("Y-m-d H:i:s"), 
				$current_user->ID, 
				0,
				0,
				$topic_approved,
				$info_only,
				$group_id,
				$_SERVER['REMOTE_ADDR'],
				$_SERVER['HTTP_X_FORWARDED_FOR']
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
		if (get_option('symposium_img_db') == "on") {
			
			$wpdb->query( $wpdb->prepare( "UPDATE ".$wpdb->base_prefix."symposium_topics_images SET tid = %d WHERE tid = 0 AND uid = %d", $new_tid, $current_user->ID ));
			
		} else {

			if ($blog_id > 1) {
				$tmp_path = get_option('symposium_img_path').'/'.$blog_id.'/forum/0_'.$current_user->ID.'_tmp';
				$to_path = get_option('symposium_img_path').'/'.$blog_id.'/forum/'.$new_tid;
			} else {
				$tmp_path = get_option('symposium_img_path').'/forum/0_'.$current_user->ID.'_tmp';
				$to_path = get_option('symposium_img_path').'/forum/'.$new_tid;
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
		
		// Email admin if post needs approval
		if (get_option('symposium_permalink_structure') && $group_id == 0) {
			$perma_cat = symposium_get_forum_category_part_url($cat_id);
			$url = symposium_get_url('forum').'/'.$perma_cat.$stub;
		} else {
			$url = $forum_url.$q."cid=".$cat_id."&show=".$new_tid;
		}
		if ($topic_approved != 'on') {
			$owner_name = $wpdb->get_var($wpdb->prepare("SELECT display_name FROM ".$wpdb->base_prefix."users WHERE ID = ".$current_user->ID));
			$body = "<p>".$owner_name." ".__('has started a new topic', 'wp-symposium');
			$category = $wpdb->get_var($wpdb->prepare("SELECT title FROM ".$wpdb->prefix."symposium_cats WHERE cid = ".$cat_id));
			$body .= " ".__('in', 'wp-symposium')." ".$category;
			$body .= "...</p>";
							
			$body .= "<span style='font-size:24px'>".$new_topic_subject."</span><br /><br />";
			$body .= "<p>".$new_topic_text."</p>";
			$body .= "<p><a href='".$url."'>".$url."</a></p>";
			$body = str_replace(chr(13), "<br />", $body);
			$body = str_replace("\\r\\n", "<br />", $body);
			$body = str_replace("\\", "", $body);
			$body = "<span style='font-size:24px font-style:italic;'>".__('Moderation Required', 'wp-symposium')."</span><br /><br />".$body;
			symposium_sendmail(get_bloginfo('admin_email'), __('Moderation Required', 'wp-symposium'), $body);
		}

		// Hook to allow other actions
		$post = __('Started a new forum topic:', 'wp-symposium').' <a href="'.$url.'">'.$new_topic_subject.'</a>';
		do_action('symposium_forum_newtopic_hook', $current_user->ID, $current_user->display_name, $current_user->ID, $post, 'forum', $new_tid);			

		// Return new Topic ID
		echo $new_tid.'[|]'.$url;
		exit;	
				
	} else {
	
		echo 'NOT LOGGED IN';
		exit;
		
	}

}

// New Topic (send notification emails) ****************************************************************
if ($_POST['action'] == 'forumNewPostEmails') {

	if (is_user_logged_in()) {

		$new_tid = $_POST['new_tid'];
		$cat_id = $_POST['cat_id'];
		if ($cat_id == '') $cat_id = 0;
		$group_id = $_POST['group_id'];
				
		// Get topic information
		$sql = "SELECT * FROM ".$wpdb->prefix."symposium_topics WHERE tid = %d";
		$topic = $wpdb->get_row($wpdb->prepare($sql, $new_tid));
		$new_topic_subject = stripslashes($topic->topic_subject);
		$new_topic_text = stripslashes($topic->topic_post);
		$stub = stripslashes($topic->stub);
		
		if ($topic->topic_approved == 'on') {
		
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
	
			// Get post owner name and prepare email body
			$owner_name = $wpdb->get_var($wpdb->prepare("SELECT display_name FROM ".$wpdb->base_prefix."users WHERE ID = ".$current_user->ID));
			$body = "<p>".$owner_name." ".__('has started a new topic', 'wp-symposium');
			$category = $wpdb->get_var($wpdb->prepare("SELECT title FROM ".$wpdb->prefix."symposium_cats WHERE cid = ".$cat_id));
							
			$body .= " ".__('in', 'wp-symposium')." ".$category;
			$body .= "...</p>";

			$body .= "<span style='font-size:24px'>".$new_topic_subject."</span><br /><br />";
			$body .= "<p>".$new_topic_text."</p>";
			if (get_option('symposium_permalink_structure') && $group_id == 0) {
				$perma_cat = symposium_get_forum_category_part_url($cat_id);
				$url = $forum_url.'/'.$perma_cat.$stub;
			} else {
				$url = $forum_url.$q."cid=".$cat_id."&show=".$new_tid;
			}
			$body .= "<p><a href='".$url."'>".$url."</a></p>";
			$body = str_replace(chr(13), "<br />", $body);
			$body = str_replace("\\r\\n", "<br />", $body);
			$body = str_replace("\\", "", $body);

			if (function_exists('symposium_mailinglist')) { 
				$subject_add = ' #TID='.$new_tid.' ['.__('do not edit', 'wp-symposium').']'; 
				$body_prefix = get_option('symposium_mailinglist_prompt').'<br />'.get_option('symposium_mailinglist_divider').'<br /><br />'.get_option('symposium_mailinglist_divider_bottom').'<br /><br />';
			} else {
				$subject_add = '';
				$body_prefix = '';
			}
														
		
			if ($group_id == 0) {
			
				// Main forum (not a group forum)
				// Email people who want to know	
				
				$email_list = '0,';
				$query = $wpdb->get_results("
					SELECT user_email, ID
					FROM ".$wpdb->base_prefix."users u RIGHT JOIN ".$wpdb->prefix."symposium_subs s ON s.uid = u.ID 
					WHERE s.tid = 0 AND u.ID != ".$current_user->ID." AND s.cid = ".$cat_id);

				// Work out mail subject
				if (strpos(get_option('symposium_subject_forum_new'), '[topic]') !== FALSE) {
					$topic = $wpdb->get_var($wpdb->prepare("SELECT topic_subject FROM ".$wpdb->prefix."symposium_topics WHERE tid = ".$new_tid));
					$subject = str_replace("[topic]", $topic, get_option('symposium_subject_forum_new'));
				} else {
					$subject = get_option('symposium_subject_forum_new');
				}	
																		
				if ($query) {					

					global $current_user;

					foreach ($query as $user) {
						
						// Hook and Filter to allow further actions to take place
						apply_filters ('symposium_forum_newtopic_filter', $user->ID, $current_user->ID, $current_user->display_name, $url);

						// Add to list of those sent to
						$email_list .= $user->ID.',';
						
						// Send mail
						symposium_sendmail($user->user_email, $subject.$subject_add, $body_prefix.$body);						
					}						
				}

				// Now send to everyone who wants to know about all new topics and replies
				$email_list .= '0';
				$sql = "SELECT ID,user_email FROM ".$wpdb->base_prefix."users u 
				    LEFT JOIN ".$wpdb->base_prefix."usermeta m ON u.ID = m.user_id
					WHERE u.ID != %d AND
					m.meta_key = 'symposium_forum_all' AND
					m.meta_value = 'on' AND
					u.ID NOT IN (".$email_list.")";
				$query = $wpdb->get_results($wpdb->prepare($sql, $current_user->ID));	
				
				// Get list of permitted roles for this topic category
				$sql = "SELECT level FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d";
				$level = $wpdb->get_var($wpdb->prepare($sql, $cat_id));
				$cat_roles = unserialize($level);					
								
					if ($query) {						
						foreach ($query as $user) {	

							// Get role of recipient user
							$the_user = get_userdata( $user->ID );
							$user_email = $the_user->user_email;
							$capabilities = $the_user->{$wpdb->prefix . 'capabilities'};
	
							if ( !isset( $wp_roles ) )
								$wp_roles = new WP_Roles();
								
							$user_role = 'NONE';
							foreach ( $wp_roles->role_names as $role => $name ) {
							
								if ( array_key_exists( $role, $capabilities ) )
									$user_role = $role;
							}

							// Check in this topics category level
							if (strpos(strtolower($cat_roles), 'everyone,') !== FALSE || strpos(strtolower($cat_roles), $user_role.',') !== FALSE) {	 

								// Filter to allow further actions to take place
								apply_filters ('symposium_forum_newtopic_filter', $user->ID, $current_user->ID, $current_user->display_name, $url);
					
								// Send mail
								symposium_sendmail($user_email, $subject.$subject_add, $body_prefix.$body);							
							}
							
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
			
			
			echo '';
			exit;
	
		} // endif topic_approved == 'on'	
				
	} else {
	
		echo 'NOT LOGGED IN';
		exit;
		
	}

}

// Get Topic ****************************************************************
if ($_POST['action'] == 'getTopic') {
		
	$topic_id = $_POST['topic_id'];
	$group_id = $_POST['group_id'];

	echo symposium_getTopic($topic_id, $group_id);
	exit;
}

// Get Forum ****************************************************************
if ($_POST['action'] == 'getForum') {

	$cat_id = $_POST['cat_id'];
	
	if (isset($_POST['limit_from'])) { $limit_from = $_POST['limit_from']; } else { $limit_from = 0; }
	$group_id = $_POST['group_id'];
	
	echo symposium_getForum($cat_id, $limit_from, $group_id);

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
		
		$striptags = get_option('symposium_striptags');
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
				$moderation = get_option('symposium_moderation');
				if ($moderation == "on") {
					$topic_approved = "";
				} else {
					$topic_approved = "on";
				}
			
				// Don't allow HTML in subject if not using WYSIWYG editor
				if (get_option('symposium_use_wysiwyg') != 'on') {
					$reply_text = str_replace("<", "&lt;", $reply_text);
					$reply_text = str_replace(">", "&gt;", $reply_text);
				}

				// Check for banned words
				$chatroom_banned = get_option('symposium_chatroom_banned');
				if ($chatroom_banned != '') {
					$badwords = $pieces = explode(",", $chatroom_banned);

					 for($i=0;$i < sizeof($badwords);$i++){
					 	if (strpos(' '.$reply_text.' ', $badwords[$i])) {
						 	$reply_text=eregi_replace($badwords[$i], "***", $reply_text);
					 	}
					 }
				}
				
				// Store new reply in post					
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
					topic_answer,
					remote_addr,
					http_x_forwarded_for
				)
				VALUES ( %s, %d, %s, %s, %s, %d, %d, %d, %s, %d, %s, %s, %s )", 
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
					$answered,
					$_SERVER['REMOTE_ADDR'],
					$_SERVER['HTTP_X_FORWARDED_FOR']
					
		        	) 
		        ) );
		
				if (WPS_DEBUG) echo $wpdb->last_query.'<br />';

				// get new topic id (or response)
				$new_id = $wpdb->insert_id;

				// check for any attachments
				if (get_option('symposium_img_db') == "on") {
					
					$wpdb->query( $wpdb->prepare( "UPDATE ".$wpdb->base_prefix."symposium_topics_images SET tid = %d WHERE uid = %d AND tid = 0", $new_id, $current_user->ID ));
					
				} else {
					
					// File system
					if ($blog_id > 1) {
						$to_path = get_option('symposium_img_path').'/'.$blog_id.'/forum/'.$tid.'/'.$new_id;
						$tmp_path = get_option('symposium_img_path').'/'.$blog_id.'/forum/'.$tid.'_'.$current_user->ID.'_tmp';
					} else {
						$to_path = get_option('symposium_img_path').'/forum/'.$tid.'/'.$new_id;
						$tmp_path = get_option('symposium_img_path').'/forum/'.$tid.'_'.$current_user->ID.'_tmp';
					}
					if (WPS_DEBUG) echo 'Looking for images in '.get_option('symposium_img_path').'/forum<br />';
					if (file_exists($tmp_path)) {
						if (WPS_DEBUG) echo 'FILE EXISTS: '.$tmp_path.'<br />';
						mkdir($to_path, 0777, true);
						// copy tmp files to new location
						$handler = opendir($tmp_path);
						while ($file = readdir($handler)) {
							if ($file != "." && $file != ".." && $file != ".DS_Store") {
								if (WPS_DEBUG) echo 'Copy '.$tmp_path.'/'.$file.' to '.$to_path.'/'.$file.'<br />';
								copy($tmp_path.'/'.$file, $to_path.'/'.$file);
								unlink($tmp_path.'/'.$file);
							}
						}
						rmdir($tmp_path);
						closedir($handler);
					} else {
						if (WPS_DEBUG) echo 'FILE DOES NOT EXIST: '.$tmp_path.'<br />';
					}
					
				}

				// Update last activity (if posting to a group)
				if ($group_id > 0) {
					$wpdb->query( $wpdb->prepare( "UPDATE ".$wpdb->base_prefix."symposium_groups SET last_activity = %s WHERE gid = %d", array( date("Y-m-d H:i:s"), $group_id ) ));
				}
	        
				// Update main topic date for freshness
				$bump_topics = get_option('symposium_bump_topics');
				if ($bump_topics == 'on') {
					$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix."symposium_topics SET topic_date = '".date("Y-m-d H:i:s")."' WHERE tid = ".$tid) );					
				}
				
				// Hook for more actions
				do_action('symposium_forum_reply_hook', $current_user->ID, $current_user->display_name, $new_id);			
			
				// Send out emails for new reply
				forumReplyEmails($tid, $cat_id, $reply_text, $forum_url, $q, $topic_approved);
			
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
	$grouppage = symposium_get_url('group');
	
	$snippet_length = get_option('symposium_preview1');
	if ($snippet_length == '') { $snippet_length = '45'; }
	
	$html = '<div id="forum_activity_div">';
	
		$html .= '<div id="forum_activity_all_new_topics">';
		
			$html .= '<div id="forum_activity_title">'.__('Recent Topics', 'wp-symposium').'</div>';
		
			// All topics started
			$sql = "SELECT t.*, u.display_name FROM ".$wpdb->prefix."symposium_topics t LEFT JOIN ".$wpdb->base_prefix."users u ON t.topic_owner = u.ID WHERE t.topic_approved = 'on' AND topic_parent = 0 ORDER BY topic_started DESC LIMIT 0,40";
	
			$topics = $wpdb->get_results($sql);
			if ($topics) {
				foreach ($topics as $topic) {
					
					if ($topic->topic_group == 0 || symposium_member_of($topic->topic_group) == 'yes') {
						
						$html .= "<div class='forum_activity_new_topic_subject'>";
						if ($topic->topic_group == 0) {
							$html .= "<a href='".$thispage.$q.'cid='.$topic->topic_category.'&show='.$topic->tid."'>".symposium_bbcode_remove(stripslashes($topic->topic_subject))."</a>";
						} else {
							$html .= "<a href='".$grouppage.$q.'gid='.$topic->topic_group.'&cid='.$topic->topic_category.'&show='.$topic->tid."'>".symposium_bbcode_remove(stripslashes($topic->topic_subject))."</a>";
						}
						$html .= "</div>";
						
						$text = symposium_bbcode_remove(strip_tags(stripslashes($topic->topic_post)));
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

					if ($topic->topic_group == 0 || symposium_member_of($topic->topic_group) == 'yes') {
						
						$html .= "<div class='forum_activity_new_topic_subject'>";
						if ($topic->topic_group == 0) {
							$html .= "<a href='".$thispage.$q.'cid='.$topic->topic_category.'&show='.$topic->tid."'>".symposium_bbcode_remove(stripslashes($topic->topic_subject))."</a>, ".symposium_time_ago($topic->topic_date);
						} else {
							$html .= "<a href='".$grouppage.$q.'gid='.$topic->topic_group.'&cid='.$topic->topic_category.'&show='.$topic->tid."'>".symposium_bbcode_remove(stripslashes($topic->topic_subject))."</a>, ".symposium_time_ago($topic->topic_date);
						}
						$html .= "</div>";
					
						$text = symposium_bbcode_remove(strip_tags(stripslashes($topic->topic_post)));
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

					if ($topic->topic_group == 0 || symposium_member_of($topic->topic_group) == 'yes') {
											
						if (strpos($shown, $topic->topic_parent.",") === FALSE) { 

							$html .= "<div class='forum_activity_new_topic_subject'>";
							if ($topic->topic_group == 0) {
								$html .= "<a href='".$thispage.$q.'cid='.$topic->topic_category.'&show='.$topic->topic_parent."'>".symposium_bbcode_remove(stripslashes($topic->topic_subject))."</a>";
							} else {
								$html .= "<a href='".$grouppage.$q.'gid='.$topic->topic_group.'&cid='.$topic->topic_category.'&show='.$topic->topic_parent."'>".symposium_bbcode_remove(stripslashes($topic->topic_subject))."</a>";
							}
							$html .= "</div>";	
																				
							$text = symposium_bbcode_remove(strip_tags(stripslashes($topic->topic_post)));
							if ( strlen($text) > $snippet_length ) { $text = substr($text, 0, $snippet_length)."..."; }
							$html .= $text;
							if (get_option('symposium_use_answers') == 'on' && $topic->topic_answer == 'on') {
								$html .= ' <img style="width:12px; height:12px" src="'.get_option('symposium_images').'/tick.png" alt="'.__('Answer Accepted', 'wp-symposium').'" />';
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
	
	// Get list of roles for this user
    $user_roles = $current_user->roles;
    $user_role = strtolower(array_shift($user_roles));	
    if ($user_role == '') $user_role = 'NONE';
	
	$html = '<div id="forum_activity_div">';
	
		// All topics started
		$posts = $wpdb->get_results("
			SELECT tid, topic_subject, topic_owner, topic_post, topic_category, topic_date, display_name, topic_parent, topic_answer 
			FROM ".$wpdb->prefix.'symposium_topics'." t INNER JOIN ".$wpdb->base_prefix.'users'." u ON t.topic_owner = u.ID 
			WHERE topic_approved = 'on' AND topic_date > '".$include."' AND topic_group = ".$gid." ORDER BY tid DESC LIMIT 0,".$postcount); 

		if ($posts) {

			foreach ($posts as $post)
			{
				
				// Check permitted to see forum category
				$sql = "SELECT level FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d";
				$cat_levels = $wpdb->get_var($wpdb->prepare($sql, $post->topic_category));
				$cat_roles = unserialize($cat_levels);
				if ($gid > 0 || strpos(strtolower($cat_roles), 'everyone,') !== FALSE || strpos(strtolower($cat_roles), $user_role.',') !== FALSE) {				
					
					$html .= "<div style='clear:both'>";
						$html .= "<div class='symposium_latest_forum_row_avatar'>";
							$html .= get_avatar($post->topic_owner, 20);
						$html .= "</div>";
						$html .= "<div style='margin-bottom: 3px; float:left;'>";
							if ($post->topic_parent > 0) {
								$text = strip_tags(stripslashes($post->topic_post));
								if ( strlen($text) > $preview ) { $text = substr($text, 0, $preview)."..."; }
								$html .= symposium_profile_link($post->topic_owner)." ".__('replied', 'wp-symposium')." ";
								$html .= "<a href='".$thispage.$q."cid=".$post->topic_category."&show=".$post->topic_parent."'>";
								$html .= $text."</a> ".symposium_time_ago($post->topic_date).".";
								if (get_option('symposium_use_answers') == 'on' && $post->topic_answer == 'on') {
									$html .= ' <img style="width:12px; height:12px" src="'.get_option('symposium_images').'/tick.png" alt="'.__('Answer Accepted', 'wp-symposium').'" />';
								}
								$html .= "<br>";
							} else {
								$text = strip_tags(stripslashes($post->topic_subject));
								if ( strlen($text) > $preview ) { $text = substr($text, 0, $preview)."..."; }
								$html .= symposium_profile_link($post->topic_owner)." ".__('started', 'wp-symposium')." <a href='".$thispage.$q."cid=".$post->topic_category."&show=".$post->tid."'>".$text."</a> ".symposium_time_ago($post->topic_date).".<br>";
							}
						$html .= "</div>";
						if ($post->topic_date > $previous_login && $post->topic_owner != $current_user->ID && is_user_logged_in() && get_option('symposium_forum_stars')) {
							$html .= "<div style='float:left;'>";
								$html .= "<img src='".get_option('symposium_images')."/new.gif' alt='New!' /> ";
							$html .= "</div>";
						}		
	
					$html .= "</div>";
					
				}
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

	// Get list of roles for this user
	global $current_user;
    $user_roles = $current_user->roles;
    $user_role = strtolower(array_shift($user_roles));
    if ($user_role == '') $user_role = 'NONE';

	if ($posts) {

		foreach ($posts as $post)
		{

			$sql = "SELECT level FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d";
			$cat_level = $wpdb->get_var($wpdb->prepare($sql, $post->topic_category));
			$cat_roles = unserialize($cat_level);
			if ($gid > 0 || strpos(strtolower($cat_roles), 'everyone,') !== FALSE || strpos(strtolower($cat_roles), $user_role.',') !== FALSE) {		

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
							if (get_option('symposium_use_answers') == 'on' && $post->topic_answer == 'on') {
								$html .= ' <img style="width:12px; height:12px" src="'.get_option('symposium_images').'/tick.png" alt="'.__('Answer Accepted', 'wp-symposium').'" />';
							}
							$html .= "<br>";
						} else {
							$text = stripslashes($post->topic_subject);
							if ( strlen($text) > $preview ) { $text = substr($text, 0, $preview)."..."; }
							$html .= symposium_profile_link($post->topic_owner)." ".__('started', 'wp-symposium')." <a href='".$thispage.$q."cid=".$post->topic_category."&show=".$post->tid."'>".$text."</a> ".symposium_time_ago($post->topic_started).".<br>";
						}
					$html .= "</div>";
					if ($post->topic_date > $previous_login && $post->topic_owner != $current_user->ID && is_user_logged_in() && get_option('symposium_forum_stars')) {
						$html .= "<img src='".get_option('symposium_images')."/new.gif' alt='New!' /> ";
					}
				$html .= "</div>";
				
				$html .= showThreadChildren($post->tid, $level+1, $gid, $previous_login);
			}			
							
		}
	}	
	
	return $html;
}

// AJAX to fetch favourites
if ($_POST['action'] == 'getFavs') {
	
	if (is_user_logged_in()) {

		$snippet_length_long = get_option('symposium_preview2');
		if ($snippet_length_long == '') { $snippet_length_long = '45'; }
	
		$html = '';
	
		$favs = get_symposium_meta($current_user->ID, 'forum_favs');
		$favs = explode('[', $favs);
		if ($favs) {
			foreach ($favs as $fav) {
				$fav = str_replace("]", "", $fav);
				if ($fav != '') {
				
					$post = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."symposium_topics WHERE tid = %d", $fav));

					if ($post) {
						
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
	
							$html .= " <a title='".$fav."' class='symposium-delete-fav' style='cursor:pointer'><img src='".get_option('symposium_images')."/delete.png' style='width:16px;height:16px' /></a>";
					
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
	
							$text = strip_tags(stripslashes($post->topic_post));
							if ( strlen($text) > $snippet_length_long ) { $text = substr($text, 0, $snippet_length_long)."..."; }
						
							$html .= "<br />".$text;
						
						$html .= '</div>';
					}
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
	
		$details = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix.'symposium_topics'." WHERE tid = %d", $tid)); 
		if ($details->topic_subject == '') {
			$parent = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix.'symposium_topics'." WHERE tid = %d", $details->topic_parent)); 
			$subject = $parent->topic_subject;
		} else {
			$subject = $details->topic_subject;
		}
		$subject = str_replace("&lt;", "<", $subject);	
		$subject = str_replace("&gt;", ">", $subject);	
		$topic_post = $details->topic_post;
		if (get_option('symposium_use_wysiwyg') == 'on') {
			$topic_post = htmlspecialchars($topic_post);
		}
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
		
		if (get_option('symposium_striptags') == 'on') {
			$topic_subject = strip_tags($topic_subject);	
			$topic_post = strip_tags($topic_post);	
		}
		
		$topic_post = str_replace("\n", chr(13), $topic_post);	
		$topic_category = $_POST['topic_category'];
		
		// Ensure safe HTML
		$topic_subject = str_replace("<", "&lt;", $topic_subject);	
		$topic_subject = str_replace(">", "&gt;", $topic_subject);	
		if (get_option('symposium_use_wysiwyg') != 'on') {
			$topic_post = str_replace("<", "&lt;", $topic_post);	
			$topic_post = str_replace(">", "&gt;", $topic_post);
		}
		
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

// Social media icon share
if ($_POST['action'] == 'socialShare') {
	if (is_user_logged_in()) {
		do_action('symposium_forum_socialmedia_hook', $current_user->ID, isset($_POST['destination']) ? $_POST['destination'] : 'error');			
	}
	exit;
}

// Do search
if ($_POST['action'] == 'getSearch') {

	$term = $_POST['term'];
	$found_count=0;
	$max_return=20; // Helps with avoiding return huge amounts of HTML (and unresponsive page)

	// Get list of roles for this user
    $user_roles = $current_user->roles;
    $user_role = strtolower(array_shift($user_roles));
    if ($user_role == '') $user_role = 'NONE';
    							
	$html = '<div id="forum_activity_div">';
 
	if (trim($term) != '') {
						
		$sql = "SELECT t.*, p.tid AS parent_tid, u2.display_name as parent_display_name, p.topic_subject AS parent_topic_subject, p.topic_started AS parent_topic_started, u.display_name 
			FROM ".$wpdb->prefix."symposium_topics t 
			LEFT JOIN ".$wpdb->base_prefix."users u ON t.topic_owner = u.ID 
			LEFT JOIN ".$wpdb->prefix."symposium_topics p ON t.topic_parent = p.tid 
			LEFT JOIN ".$wpdb->base_prefix."users u2 ON p.topic_owner = u2.ID 
			WHERE t.topic_approved = 'on' && (t.topic_subject LIKE '%".$term."%' OR t.topic_post LIKE '%".$term."%' OR u.display_name LIKE '%".$term."%') 
			ORDER BY t.tid DESC LIMIT 0,40";

		$topics = $wpdb->get_results($sql);
		if ($topics) {

			foreach ($topics as $topic) {	
				
				// Check permitted to see forum category
				$sql = "SELECT level FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d";
				$levels = $wpdb->get_var($wpdb->prepare($sql, $topic->topic_category));
				$cat_roles = unserialize($levels);
				if ($topic->topic_group > 0 || strpos(strtolower($cat_roles), 'everyone,') !== FALSE || strpos(strtolower($cat_roles), $user_role.',') !== FALSE) {					

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

								if (get_option('symposium_permalink_structure') && $group_id == 0) {
									$stub = $wpdb->get_var($wpdb->prepare("SELECT stub FROM ".$wpdb->prefix."symposium_topics WHERE tid = ".$topic->parent_tid));
									$perma_cat = symposium_get_forum_category_part_url($topic->topic_category);
									$url = $thispage.$perma_cat.$stub;							
									$html .= "<a class='symposium_search_subject' href='".$url."'>".stripslashes($topic_subject)."</a> ";
								} else {
									$html .= "<a class='symposium_search_subject' href='".$thispage.$q.'cid='.$topic->topic_category.'&show='.$topic->parent_tid."'>".stripslashes($topic_subject)."</a> ";
								}
								$html .= __("by", "wp-symposium")." ".$topic->parent_display_name.", ".symposium_time_ago($topic->parent_topic_started).".";
							} else {
								$topic_subject = symposium_bbcode_remove(stripslashes($topic->topic_subject));
								$topic_subject = preg_replace(
								  "/(>|^)([^<]+)(?=<|$)/iesx",
								  "'\\1' . str_replace('" . $term . "', '<span class=\"symposium_search_highlight\">" . $term . "</span>', '\\2')",
								  $topic_subject
								);
								if (get_option('symposium_permalink_structure') && $group_id == 0) {
									$stub = $wpdb->get_var($wpdb->prepare("SELECT stub FROM ".$wpdb->prefix."symposium_topics WHERE tid = ".$topic->tid));
									$perma_cat = symposium_get_forum_category_part_url($topic->topic_category);
									$url = $thispage.$perma_cat.$stub;							
									$html .= "<a class='symposium_search_subject' href='".$url."'>".stripslashes($topic_subject)."</a> ";
								} else {
									$html .= "<a class='symposium_search_subject' href='".$thispage.$q.'cid='.$topic->topic_category.'&show='.$topic->tid."'>".stripslashes($topic_subject)."</a> ";
								}
								$html .= __("by", "wp-symposium")." ".$topic->display_name.", ".symposium_time_ago($topic->topic_started).".";
							}
	
							$html .= "</div>";
	
							$text = symposium_bbcode_remove(strip_tags(stripslashes($topic->topic_post)));
							
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
		
		}

		$html .= "<p><br />".__("Results found:", "wp-symposium")." ".$found_count."</p>";				

	}
	
	$html .= '</div>';
	
	//$html .= " ".$wpdb->last_query;
	echo $html;
	exit;
}



// New Reply (send notification emails) ****************************************************************
function forumReplyEmails($tid, $cat_id, $reply_text, $forum_url, $q, $topic_approved) {

	global $wpdb,$current_user;

	if (WPS_DEBUG) echo 'Sending out forumReplyEmails<br />';
	
	// Email people who want to know and prepare body
	$owner_name = $wpdb->get_var($wpdb->prepare("SELECT display_name FROM ".$wpdb->base_prefix."users WHERE ID = ".$current_user->ID));
	$parent = $wpdb->get_var($wpdb->prepare("SELECT topic_subject FROM ".$wpdb->prefix."symposium_topics WHERE tid = ".$tid));
	$stub = $wpdb->get_var($wpdb->prepare("SELECT stub FROM ".$wpdb->prefix."symposium_topics WHERE tid = ".$tid));
	$group_id = $wpdb->get_var($wpdb->prepare("SELECT topic_group FROM ".$wpdb->prefix."symposium_topics WHERE tid = ".$tid));
	if (WPS_DEBUG) echo $owner_name.','.$parent.','.$stub.','.$group_id.'<br />';
	
	$body = "<span style='font-size:24px'>".$parent."</span><br /><br />";
	$body .= "<p>".$owner_name." ".__('replied', 'wp-symposium')."...</p>";
	$body .= "<p>".$reply_text."</p>";
	if (get_option('symposium_permalink_structure') && $group_id == 0) {
		$perma_cat = symposium_get_forum_category_part_url($cat_id);
		$url = $forum_url.'/'.$perma_cat.$stub;
	} else {
		$url = $forum_url.$q."cid=".$cat_id."&show=".$tid;
	}
	$body .= "<p><a href='".$url."'>".$url."</a></p>";
	$body = str_replace(chr(13), "<br />", $body);
	$body = str_replace("\\r\\n", "<br />", $body);
	$body = str_replace("\\", "", $body);

	// add section for reply-by-email
	if (function_exists('symposium_mailinglist')) { 
		$subject_add = ' #TID='.$tid.' ['.__('do not edit', 'wp-symposium').']'; 
		$body = get_option('symposium_mailinglist_prompt').'<br />'.get_option('symposium_mailinglist_divider').'<br /><br />'.get_option('symposium_mailinglist_divider_bottom').'<br /><br />'.'<br /><br />'.$body;
	} else {
		$subject_add = '';
	}

	$email_list = '0,';
	if ($topic_approved == "on") {

		$query = $wpdb->get_results("
			SELECT user_email, ID
			FROM ".$wpdb->base_prefix."users u 
			RIGHT JOIN ".$wpdb->prefix."symposium_subs ON ".$wpdb->prefix."symposium_subs.uid = u.ID 
			WHERE u.ID != ".$current_user->ID." AND tid = ".$tid);

		if (WPS_DEBUG) echo 'Checking subscription: '.$wpdb->last_query.'<br />';
			
		if ($query) {						
			foreach ($query as $user) {	

				// Filter to allow further actions to take place
				if (WPS_DEBUG) echo 'Applying symposium_forum_newreply_filter: '.$user->ID.','.$current_user->ID.','.$current_user->display_name.','.$url.'<br />';
				apply_filters ('symposium_forum_newreply_filter', $user->ID, $current_user->ID, $current_user->display_name, $url);
		
				// Keep track of who sent to so far
				$email_list .= $user->ID.',';
		
				// Send mail
				if (strpos(get_option('symposium_subject_forum_reply'), '[topic]') !== FALSE) {
					$subject = str_replace("[topic]", $parent, get_option('symposium_subject_forum_reply'));
				} else {
					$subject = get_option('symposium_subject_forum_reply');
				}
				symposium_sendmail($user->user_email, $subject.$subject_add, $body);							
			}
		}						

		// Now send to everyone who wants to know about all new topics and replies
		$email_list .= '0';
		$sql = "SELECT ID,user_email FROM ".$wpdb->base_prefix."users u 
		    LEFT JOIN ".$wpdb->base_prefix."usermeta m ON u.ID = m.user_id
			WHERE u.ID != %d AND
			m.meta_key = 'symposium_forum_all' AND
			m.meta_value = 'on' AND
			u.ID NOT IN (%s)";
		$query = $wpdb->get_results($wpdb->prepare($sql, $current_user->ID, $email_list));

		if (WPS_DEBUG) echo 'Checking subscribe-to-all: '.$wpdb->last_query.'<br />';
		
		// Get list of permitted roles for this topic category
		$sql = "SELECT level FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d";
		$level = $wpdb->get_var($wpdb->prepare($sql, $cat_id));
		$cat_roles = unserialize($level);					

		if ($query) {						
			foreach ($query as $user) {	
				
				// If a group and a member of the group, or not a group forum...
				if ($group_id == 0 || symposium_member_of($group_id) == "yes") {
					
					// Get role of recipient user
					$the_user = get_userdata( $user->ID );
					$user_email = $the_user->user_email;
					$capabilities = $the_user->{$wpdb->prefix . 'capabilities'};

					if ( !isset( $wp_roles ) )
						$wp_roles = new WP_Roles();
						
					$user_role = 'NONE';
					foreach ( $wp_roles->role_names as $role => $name ) {
					
						if ( array_key_exists( $role, $capabilities ) )
							$user_role = $role;
					}
					// Check in this topics category level
					if (WPS_DEBUG) echo 'Role check: '.$group_id.','.symposium_member_of($group_id).','.strtolower($cat_roles).','.$user_role.'<br />';
					if ((symposium_member_of($group_id) == "yes") || strpos(strtolower($cat_roles), 'everyone,') !== FALSE || strpos(strtolower($cat_roles), $user_role.',') !== FALSE) {	 

						// Filter to allow further actions to take place
						if (WPS_DEBUG) echo 'Applying symposium_forum_newreply_filter: '.$user->ID.','.$current_user->ID.','.$current_user->display_name.','.$url.'<br />';
						apply_filters ('symposium_forum_newreply_filter', $user->ID, $current_user->ID, $current_user->display_name, $url);

						// Send mail
						if (strpos(get_option('symposium_subject_forum_reply'), '[topic]') !== FALSE) {
							$subject = str_replace("[topic]", $parent, get_option('symposium_subject_forum_reply'));
						} else {
							$subject = get_option('symposium_subject_forum_reply');
						}
						symposium_sendmail($user_email, $subject.$subject_add, $body);							
						
					}
					
				}
			}
		}	
		
	} else {
		// Email admin if post needs approval
		$body = "<span style='font-size:24px; font-style:italic;'>".__("Moderation required for a reply", "wp-symposium")."</span><br /><br />".$body;
		symposium_sendmail(get_bloginfo('admin_email'), __('Moderation required for a reply', 'wp-symposium'), $body);
	}		
			
}


?>
