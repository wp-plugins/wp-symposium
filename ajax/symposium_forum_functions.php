<?php

include_once('../../../../wp-config.php');
//include_once('../../../../wp-includes/wp-db.php');
//include_once('../symposium_functions.php');

global $wpdb, $current_user;
wp_get_current_user();

<<<<<<< .mine
// Delete Reply *************************************************************
if ($_POST['action'] == 'deleteReply') {

	if (current_user_can('level_10')) {
		$tid = $_POST['topic_id'];
		if (symposium_safe_param($tid)) {
			$wpdb->query("DELETE FROM ".$wpdb->prefix."symposium_topics WHERE tid = ".$tid);
		}
		
		echo $tid;
		
	} else {
		echo "NOT ADMIN";
	}
	
}

// Delete Topic and Replies *************************************************
if ($_POST['action'] == 'deleteTopic') {

	if (current_user_can('level_10')) {
		$tid = $_POST['topic_id'];
		if (symposium_safe_param($tid)) {
			$wpdb->query("DELETE FROM ".$wpdb->prefix."symposium_topics WHERE topic_parent = ".$tid);
			$wpdb->query("DELETE FROM ".$wpdb->prefix."symposium_topics WHERE tid = ".$tid);
			$wpdb->query("DELETE FROM ".$wpdb->prefix."symposium_subs WHERE tid = ".$tid);
		}
		
		echo $tid;
		
	} else {
		echo "NOT ADMIN";
	}
	
}

// New Topic ****************************************************************
if ($_POST['action'] == 'forumNewPost') {

	$new_topic_subject = $_POST['subject'];
	$new_topic_text = $_POST['text'];
	$new_topic_category = $_POST['category'];
	$new_topic_subscribe = $_POST['subscribed'];

	$config = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."symposium_config"));

	// Check for moderation
	if ($config->moderation == "on") {
		$topic_approved = "";
	} else {
		$topic_approved = "on";
	}

	if ($new_topic_subject == '') { $new_topic_subject = __('No subject', 'wp-symposium'); }
	if ($new_topic_text == '') { $new_topic_text = __('No message', 'wp-symposium');  }
	
	if ( is_user_logged_in() ) {
		
		// Get forum URL worked out
		$forum_url = symposium_get_url('forum');
		if ($forum_url[strlen($forum_url)-1] != '/') { $forum_url .= '/'; }
		if (isset($_GET[page_id]) && $_GET[page_id] != '') {
			// No Permalink
			$q = "&";
		} else {
			$q = "?";
		}
		
		// Check for duplicates
		$topic_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$topics." WHERE topic_subject = '".$new_topic_subject."' and topic_post = '".$new_topic_text."' AND topic_owner = ".$current_user->ID));

		if ($topic_count > 1) {
			// Don't double post (also helps reduce spam)
		} else {						
			
			// Store new topic in post
			
			// Replace carriage returns
			$new_topic_text = str_replace("\n", chr(13), $new_topic_text);	
			
			// Don't allow HTML
			$new_topic_text = str_replace("<", "&lt;", $new_topic_text);
			$new_topic_text = str_replace(">", "&gt;", $new_topic_text);

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
		        	$new_topic_subject, 
		        	$new_topic_category,
		        	$new_topic_text, 
		        	date("Y-m-d H:i:s"), 
					date("Y-m-d H:i:s"), 
					$current_user->ID, 
					0,
					0,
					$topic_approved
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
			
			// Set category to the category posted into
			$cat_id = $new_topic_category;
							
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
				// Email people who want to know	
				$query = $wpdb->get_results("
					SELECT user_email
					FROM ".$wpdb->base_prefix."users u RIGHT JOIN ".$wpdb->prefix."symposium_subs s ON s.uid = u.ID 
					WHERE s.tid = 0 AND u.ID != ".$current_user->ID." AND s.cid = ".$cat_id);
					
				if ($query) {					
					foreach ($query as $user) {
						symposium_sendmail($user->user_email, __('New Forum Topic', 'wp-symposium'), $body);						
					}						
				}
			} else {
				// Email admin if post needs approval
				$body = "<span style='font-size:24px font-style:italic;'>".__('Moderation Required', 'wp-symposium')."</span><br /><br />".$body;
				symposium_sendmail(get_bloginfo('admin_email'), __('Moderation Required', 'wp-symposium'), $body);
			}	
			
			echo $url;
			exit;
		}
	}
	
	echo 'NOT LOGGED IN';

}

// Get Topic ****************************************************************
if ($_POST['action'] == 'getTopic') {
	
	$topic_id = $_POST['topic_id'];
	
	$config = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."symposium_config"));
	$plugin = get_site_url().'/wp-content/plugins/wp-symposium/';
	
	// Get forum URL worked out
	$forum_url = symposium_get_url('forum');
	if ($forum_url[strlen($forum_url)-1] != '/') { $forum_url .= '/'; }
	if (isset($_GET['page_id']) && $_GET['page_id'] != '') {
		// No Permalink
		$q = "&";
	} else {
		$q = "?";
	}
	
	$post = $wpdb->get_row("
		SELECT tid, topic_subject, topic_approved, topic_category, topic_post, topic_started, display_name, topic_sticky, topic_owner 
		FROM ".$wpdb->prefix."symposium_topics t INNER JOIN ".$wpdb->base_prefix."users u ON t.topic_owner = u.ID 
		WHERE (t.topic_approved = 'on' OR t.topic_owner = ".$current_user->ID.") AND tid = ".$topic_id);
		
	if ($post) {

		// Breadcrumbs
		$cat_id = $post->topic_category;
		
		$html = '';
		$html .= '<div id="topic_breadcrumbs" class="breadcrumbs">';

			$this_level = $wpdb->get_row($wpdb->prepare("SELECT cid, title, cat_parent FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d", $cat_id));
			if ($this_level->cat_parent == 0) {
				if ($config->forum_ajax == 'on') {
					$html .= '<a href="javascript:void(0)" class="category_title" title="0">'.__('Forum Home', 'wp-symposium')."</a> > ";
					$html .= '<a href="javascript:void(0);" class="category_title" title="'.$this_level->cid.'">'.trim($this_level->title).'</a>';
				} else {
					$html .= '<a href="'.$forum_url.'" title="0">'.__('Forum Home', 'wp-symposium')."</a> > ";
					$html .= '<a href="'.$forum_url.$q."cid=".$this_level->cid.'" title="'.$this_level->cid.'">'.trim($this_level->title).'</a>';
				}
			} else {

				$parent_level = $wpdb->get_row($wpdb->prepare("SELECT cid, title, cat_parent FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d", $this_level->cat_parent));

				if ($parent_level->cat_parent == 0) {
					if ($config->forum_ajax == 'on') {
						$html .= '<a href="javascript:void(0)" class="category_title" title="0">'.__('Forum Home', 'wp-symposium')."</a> > ";
					} else {
						$html .= '<a href="'.$forum_url.'" title="0">'.__('Forum Home', 'wp-symposium')."</a> > ";
					}
				} else {
					$parent_level_2 = $wpdb->get_row($wpdb->prepare("SELECT cid, title, cat_parent FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d", $parent_level->cat_parent));
					if ($config->forum_ajax == 'on') {
						$html .= '<a href="javascript:void(0)" class="category_title" title="0">'.__('Forum Home', 'wp-symposium')."</a> > " ;
						$html .= '<a href="javascript:void(0)" class="category_title" title="'.$parent_level_2->cid.'">'.$parent_level_2->title."</a> > ";
					} else {
						$html .= '<a href="'.$forum_url.'" title="0">'.__('Forum Home', 'wp-symposium')."</a> > " ;
						$html .= '<a href="'.$forum_url.$q."cid=".$parent_level_2->cid.'"  title="'.$parent_level_2->cid.'">'.$parent_level_2->title."</a> > ";
					}
				}
				if ($config->forum_ajax == 'on') {
					$html .= '<a href="javascript:void(0)" class="category_title" title="'.$parent_level->cid.'">'.$parent_level->title."</a> > " ;
					$html .= '<a href="javascript:void(0)" class="category_title" title="'.$this_level->cid.'">'.$this_level->title."</a>" ;
				} else {
					$html .= '<a href="'.$forum_url.$q."cid=".$parent_level->cid.'" title="'.$parent_level->cid.'">'.$parent_level->title."</a> > " ;
					$html .= '<a href="'.$forum_url.$q."cid=".$this_level->cid.'" title="'.$this_level->cid.'">'.$this_level->title."</a>" ;
				}
			}

		$html .= '</div>';
		
		// Subscribe, Sticky and Allow Replies
		$html .= "<div class='floatleft label'>";
			$html .= "<input type='checkbox' title='".$post->tid."' id='subscribe' name='subscribe'";
			$subscribed_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix."symposium_subs WHERE tid = %d and uid = %d", $post->tid, $current_user->ID));
			if ($subscribed_count > 0) { $html .= ' checked'; } 
			$html .= "> ".__("Receive emails for new replies", "wp-symposium");
			if (current_user_can('level_10')) {
				$html .= "&nbsp;&nbsp;&nbsp;<input type='checkbox' title='".$post->tid."' id='sticky' name='sticky'";
				if ($post->topic_sticky > 0) { $html .= ' checked'; }
				$html .= "> ".__("Sticky", "wp-symposium");
				$html .= "&nbsp;&nbsp;&nbsp;<input type='checkbox' title='".$post->tid."' id='replies' name='replies'";
				$allow_replies = $wpdb->get_var($wpdb->prepare("SELECT allow_replies FROM ".$wpdb->prefix."symposium_topics WHERE tid = %d", $post->tid));
				if ($allow_replies == "on") { $html .= ' checked'; }
				$html .= "> ".__("Replies allowed", "wp-symposium");
			}
		$html .= "</div>";
		
		// Sharing icons
		if ($config->sharing != '') {
			$html .= show_sharing_icons($cat_id, $post->tid, $config->sharing);
		}

		// Forum options
		$html .= "<div id='forum_options'>";

			$html .= "<a id='show_search' class='label' href='javascript:void(0)'>".__("Search", "wp-symposium")."</a>";
			$html .= "&nbsp;&nbsp;&nbsp;&nbsp;<a id='show_all_activity' class='label' href='javascript:void(0)'>".__("All Activity", "wp-symposium")."</a>";
			$html .= "&nbsp;&nbsp;&nbsp;&nbsp;<a id='show_threads_activity' class='label' href='javascript:void(0)'>".__("Threads", "wp-symposium")."</a>";

			if (is_user_logged_in()) {
				$html .= "&nbsp;&nbsp;&nbsp;&nbsp;<a id='show_activity' class='label' href='javascript:void(0)'>".__("My Activity", "wp-symposium")."</a>";
				$html .= "&nbsp;&nbsp;&nbsp;&nbsp;<a id='show_favs' class='label' href='javascript:void(0)'>".__("My Favorites", "wp-symposium")."</a>";
			}

		$html .= "</div>";

		
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
			$html .= '<input type="submit" class="symposium-button edit_topic_submit" value="'.__("Update", "wp-symposium").'" />';
			$html .= '<input type="submit" class="symposium-button edit_topic_cancel" value="'.__("Cancel", "wp-symposium").'" />';
			$html .= '</div>';
		$html .= '</div>';
		
		// Topic starting post
		$html .= "<div id='starting-post'>";
		
			// Show topic header
			$html .= "<div id='top_of_first_post'>";
			
				$html .= "<div class='avatar' style='margin-bottom:0px; margin-top:6px;'>";
					$html .= get_avatar($post->topic_owner, 64);
				$html .= "</div>";
			
				$html .= "<div class='topic-post-header-with-fav'>";
			
					$html .= "<div class='topic-post-header'>";

						if ( ($post->topic_owner == $current_user->ID) || (current_user_can('level_10')) ) {
							$html .= "<a href='javascript:void(0)' title='".$post->tid."' id='edit-this-topic' class='edit_topic edit label' style='cursor:pointer'>".__("Edit", "wp-symposium")."</a>";
						}

					
						$post_text = symposium_bbcode_replace(stripslashes($post->topic_subject));
						$html .= stripslashes($post_text);
			
						if ($post->topic_approved != 'on') { $html .= " <em>[".__("pending approval", "wp-symposium")."]</em>"; }

						// Favourites
						if (is_user_logged_in()) {
							if (strpos(get_symposium_meta($current_user->ID, 'forum_favs'), "[".$post->tid."]") === FALSE) { 
								$html .= "<img title='".$post->tid."' id='fav_link' src='".$plugin."images/star-off.gif' style='height:22px; width:22px; cursor:pointer;' alt='".__("Click to add to favorites", "wp-symposium")."' />";						
							} else {
								$html .= "<img title='".$post->tid."' id='fav_link' src='".$plugin."images/star-on.gif' style='height:22px; width:22px; cursor:pointer;' alt='".__("Click to remove to favorites", "wp-symposium")."' />";						
							}
						}


					$html .= "</div><div style='clear:both'></div>";
										
					$html .= "<div class='started-by' style='margin-top:10px'>";
					$html .= __("Started by", "wp-symposium");
					if ( substr($config->forum_ranks, 0, 2) == 'on' ) {
						$html .= " <span class='forum_rank'>".forum_rank($post->topic_owner)."</span>";
					}
					$html .= " ".symposium_profile_link($post->topic_owner);
					$html .= " ".symposium_time_ago($post->topic_started);
					$html .= "</div>";

					$post_text = symposium_make_url(stripslashes($post->topic_post));
					$post_text = symposium_bbcode_replace($post_text);
					$html .= "<div class='topic-post-post'>".str_replace(chr(13), "<br />", $post_text)."</div>";
				
				$html .= "</div><div style='clear:both'></div>";				
												
			$html .= "</div>";

			// Update views
			if ($user_level == 5) {
				if ($config->include_admin == "on") { 
					$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix."symposium_topics SET topic_views = topic_views + 1 WHERE tid = %d", $post->tid) );
				}
			} else {
				$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix."symposium_topics SET topic_views = topic_views + 1 WHERE tid = %d", $post->tid) );
			}
					
		$html .= "</div>";		
	

		// Replies
		$sql = "SELECT tid, topic_subject, topic_approved, topic_post, topic_date, topic_owner, display_name, ID
			FROM ".$wpdb->prefix."symposium_topics t INNER JOIN ".$wpdb->base_prefix."users u ON t.topic_owner = u.ID 
			WHERE (t.topic_approved = 'on' OR t.topic_owner = %d) AND t.topic_parent = %d ORDER BY tid";
		
		if ($config->oldest_first != "on") { $sql .= " DESC"; }
	
		$child_query = $wpdb->get_results($wpdb->prepare($sql, $current_user->ID, $post->tid));

		$html .= "<div id='child-posts'>";

			if ($child_query) {

				foreach ($child_query as $child) {

					$html .= "<div id='reply".$child->tid."' class='child-reply";
						$trusted = get_symposium_meta($child->topic_owner, 'trusted');
						if ($trusted == 'on') { $html .= " trusted"; }
						$html .= "'>";
						if ( ($child->topic_owner == $current_user->ID) || (current_user_can('level_10')) ) {
							$html .= "<a href='javascript:void(0)' class='floatright link_cursor delete_forum_reply' style='display:none' id='".$child->tid."'>".__("Delete", "wp-symposium")."</a>";
							$html .= "<a href='javascript:void(0)' class='floatright link_cursor edit_forum_reply' style='display:none; margin-right: 10px' id='".$child->tid."'>".__("Edit", "wp-symposium")."</a>";
						}
						$html .= "<div class='avatar'>";
							$html .= get_avatar($child->ID, 64);
						$html .= "</div>";
						$html .= "<div class='started-by'>";
						if ( substr($config->forum_ranks, 0, 2) == 'on' ) {
							$html .= " <span class='forum_rank'>".forum_rank($child->topic_owner)."</span> ";
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

					// Separator
					$html .= "<div class='sep'></div>";						
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
		if (is_user_logged_in()) {
			$html .= '<div id="reply-topic-bottom" name="reply-topic-bottom">';
			if ($wpdb->get_var($wpdb->prepare("SELECT allow_replies FROM ".$wpdb->prefix."symposium_topics WHERE tid = %d", $post->tid)) == "on")
			{
				$html .= '<input type="hidden" id="symposium_reply_tid" value="'.$post->tid.'">';
				$html .= '<input type="hidden" id="symposium_reply_cid" value="'.$cat_id.'">';
				$html .= '<div class="reply-topic-subject label">'.__("Reply to this Topic", "wp-symposium").'</div>';
				$html .= '<textarea class="textarea_Editor reply-topic-text elastic" id="symposium_reply_text"></textarea>';
				$html .= '<input type="submit" id="quick-reply-warning" class="symposium-button" style="float: left" value="'.__("Reply", "wp-symposium").'" />';
			}				
			$html .= '</div>';
		}
		
	}
		
	echo symposium_smilies($html);
	
}

=======
<<<<<<< .mine
// Delete Reply *************************************************************
if ($_POST['action'] == 'deleteReply') {

	if (current_user_can('level_10')) {
		$tid = $_POST['topic_id'];
		if (symposium_safe_param($tid)) {
			$wpdb->query("DELETE FROM ".$wpdb->prefix."symposium_topics WHERE tid = ".$tid);
		}
		
		echo $tid;
		
	} else {
		echo "NOT ADMIN";
	}
	
}

// Delete Topic and Replies *************************************************
if ($_POST['action'] == 'deleteTopic') {

	if (current_user_can('level_10')) {
		$tid = $_POST['topic_id'];
		if (symposium_safe_param($tid)) {
			$wpdb->query("DELETE FROM ".$wpdb->prefix."symposium_topics WHERE topic_parent = ".$tid);
			$wpdb->query("DELETE FROM ".$wpdb->prefix."symposium_topics WHERE tid = ".$tid);
			$wpdb->query("DELETE FROM ".$wpdb->prefix."symposium_subs WHERE tid = ".$tid);
		}
		
		echo $tid;
		
	} else {
		echo "NOT ADMIN";
	}
	
}

// New Topic ****************************************************************
if ($_POST['action'] == 'forumNewPost') {

	$new_topic_subject = $_POST['subject'];
	$new_topic_text = $_POST['text'];
	$new_topic_category = $_POST['category'];
	$new_topic_subscribe = $_POST['subscribed'];

	$config = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."symposium_config"));

	// Check for moderation
	if ($config->moderation == "on") {
		$topic_approved = "";
	} else {
		$topic_approved = "on";
	}

	if ($new_topic_subject == '') { $new_topic_subject = __('No subject', 'wp-symposium'); }
	if ($new_topic_text == '') { $new_topic_text = __('No message', 'wp-symposium');  }
	
	if ( is_user_logged_in() ) {
		
		// Get forum URL worked out
		$forum_url = symposium_get_url('forum');
		if ($forum_url[strlen($forum_url)-1] != '/') { $forum_url .= '/'; }
		if (isset($_GET[page_id]) && $_GET[page_id] != '') {
			// No Permalink
			$q = "&";
		} else {
			$q = "?";
		}
		
		// Check for duplicates
		$topic_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$topics." WHERE topic_subject = '".$new_topic_subject."' and topic_post = '".$new_topic_text."' AND topic_owner = ".$current_user->ID));

		if ($topic_count > 1) {
			// Don't double post (also helps reduce spam)
		} else {						
			
			// Store new topic in post
			
			// Replace carriage returns
			$new_topic_text = str_replace("\n", chr(13), $new_topic_text);	
			
			// Don't allow HTML
			$new_topic_text = str_replace("<", "&lt;", $new_topic_text);
			$new_topic_text = str_replace(">", "&gt;", $new_topic_text);

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
		        	$new_topic_subject, 
		        	$new_topic_category,
		        	$new_topic_text, 
		        	date("Y-m-d H:i:s"), 
					date("Y-m-d H:i:s"), 
					$current_user->ID, 
					0,
					0,
					$topic_approved
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
			
			// Set category to the category posted into
			$cat_id = $new_topic_category;
							
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
				// Email people who want to know	
				$query = $wpdb->get_results("
					SELECT user_email
					FROM ".$wpdb->base_prefix."users u RIGHT JOIN ".$wpdb->prefix."symposium_subs s ON s.uid = u.ID 
					WHERE s.tid = 0 AND u.ID != ".$current_user->ID." AND s.cid = ".$cat_id);
					
				if ($query) {					
					foreach ($query as $user) {
						symposium_sendmail($user->user_email, __('New Forum Topic', 'wp-symposium'), $body);						
					}						
				}
			} else {
				// Email admin if post needs approval
				$body = "<span style='font-size:24px font-style:italic;'>".__('Moderation Required', 'wp-symposium')."</span><br /><br />".$body;
				symposium_sendmail(get_bloginfo('admin_email'), __('Moderation Required', 'wp-symposium'), $body);
			}	
			
			echo $url;
			exit;
		}
	}
	
	echo 'NOT LOGGED IN';

}

// Get Topic ****************************************************************
if ($_POST['action'] == 'getTopic') {
	
	$topic_id = $_POST['topic_id'];
	
	$config = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."symposium_config"));
	$plugin = get_site_url().'/wp-content/plugins/wp-symposium/';
	
	// Get forum URL worked out
	$forum_url = symposium_get_url('forum');
	if ($forum_url[strlen($forum_url)-1] != '/') { $forum_url .= '/'; }
	if (isset($_GET['page_id']) && $_GET['page_id'] != '') {
		// No Permalink
		$q = "&";
	} else {
		$q = "?";
	}
	
	$post = $wpdb->get_row("
		SELECT tid, topic_subject, topic_approved, topic_category, topic_post, topic_started, display_name, topic_sticky, topic_owner 
		FROM ".$wpdb->prefix."symposium_topics t INNER JOIN ".$wpdb->base_prefix."users u ON t.topic_owner = u.ID 
		WHERE (t.topic_approved = 'on' OR t.topic_owner = ".$current_user->ID.") AND tid = ".$topic_id);
		
	if ($post) {

		// Breadcrumbs
		$cat_id = $post->topic_category;
		
		$html = '';
		$html .= '<div id="topic_breadcrumbs" class="breadcrumbs">';

			$this_level = $wpdb->get_row($wpdb->prepare("SELECT cid, title, cat_parent FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d", $cat_id));
			if ($this_level->cat_parent == 0) {
				if ($config->forum_ajax == 'on') {
					$html .= '<a href="javascript:void(0)" class="category_title" title="0">'.__('Forum Home', 'wp-symposium')."</a> > ";
					$html .= '<a href="javascript:void(0);" class="category_title" title="'.$this_level->cid.'">'.trim($this_level->title).'</a>';
				} else {
					$html .= '<a href="'.$forum_url.'" title="0">'.__('Forum Home', 'wp-symposium')."</a> > ";
					$html .= '<a href="'.$forum_url.$q."cid=".$this_level->cid.'" title="'.$this_level->cid.'">'.trim($this_level->title).'</a>';
				}
			} else {

				$parent_level = $wpdb->get_row($wpdb->prepare("SELECT cid, title, cat_parent FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d", $this_level->cat_parent));

				if ($parent_level->cat_parent == 0) {
					if ($config->forum_ajax == 'on') {
						$html .= '<a href="javascript:void(0)" class="category_title" title="0">'.__('Forum Home', 'wp-symposium')."</a> > ";
					} else {
						$html .= '<a href="'.$forum_url.'" title="0">'.__('Forum Home', 'wp-symposium')."</a> > ";
					}
				} else {
					$parent_level_2 = $wpdb->get_row($wpdb->prepare("SELECT cid, title, cat_parent FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d", $parent_level->cat_parent));
					if ($config->forum_ajax == 'on') {
						$html .= '<a href="javascript:void(0)" class="category_title" title="0">'.__('Forum Home', 'wp-symposium')."</a> > " ;
						$html .= '<a href="javascript:void(0)" class="category_title" title="'.$parent_level_2->cid.'">'.$parent_level_2->title."</a> > ";
					} else {
						$html .= '<a href="'.$forum_url.'" title="0">'.__('Forum Home', 'wp-symposium')."</a> > " ;
						$html .= '<a href="'.$forum_url.$q."cid=".$parent_level_2->cid.'"  title="'.$parent_level_2->cid.'">'.$parent_level_2->title."</a> > ";
					}
				}
				if ($config->forum_ajax == 'on') {
					$html .= '<a href="javascript:void(0)" class="category_title" title="'.$parent_level->cid.'">'.$parent_level->title."</a> > " ;
					$html .= '<a href="javascript:void(0)" class="category_title" title="'.$this_level->cid.'">'.$this_level->title."</a>" ;
				} else {
					$html .= '<a href="'.$forum_url.$q."cid=".$parent_level->cid.'" title="'.$parent_level->cid.'">'.$parent_level->title."</a> > " ;
					$html .= '<a href="'.$forum_url.$q."cid=".$this_level->cid.'" title="'.$this_level->cid.'">'.$this_level->title."</a>" ;
				}
			}

		$html .= '</div>';
		
		// Subscribe, Sticky and Allow Replies
		$html .= "<div class='floatleft label'>";
			$html .= "<input type='checkbox' title='".$post->tid."' id='subscribe' name='subscribe'";
			$subscribed_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix."symposium_subs WHERE tid = %d and uid = %d", $post->tid, $current_user->ID));
			if ($subscribed_count > 0) { $html .= ' checked'; } 
			$html .= "> ".__("Receive emails for new replies", "wp-symposium");
			if (current_user_can('level_10')) {
				$html .= "&nbsp;&nbsp;&nbsp;<input type='checkbox' title='".$post->tid."' id='sticky' name='sticky'";
				if ($post->topic_sticky > 0) { $html .= ' checked'; }
				$html .= "> ".__("Sticky", "wp-symposium");
				$html .= "&nbsp;&nbsp;&nbsp;<input type='checkbox' title='".$post->tid."' id='replies' name='replies'";
				$allow_replies = $wpdb->get_var($wpdb->prepare("SELECT allow_replies FROM ".$wpdb->prefix."symposium_topics WHERE tid = %d", $post->tid));
				if ($allow_replies == "on") { $html .= ' checked'; }
				$html .= "> ".__("Replies allowed", "wp-symposium");
			}
		$html .= "</div>";
		
		// Sharing icons
		if ($config->sharing != '') {
			$html .= show_sharing_icons($cat_id, $post->tid, $config->sharing);
		}
		
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
			$html .= '<input type="submit" class="symposium-button edit_topic_submit" value="'.__("Update", "wp-symposium").'" />';
			$html .= '<input type="submit" class="symposium-button edit_topic_cancel" value="'.__("Cancel", "wp-symposium").'" />';
			$html .= '</div>';
		$html .= '</div>';
		
		// Topic starting post
		$html .= "<div id='starting-post'>";
		
			// Show topic header
			$html .= "<div id='top_of_first_post'>";
			
				$html .= "<div class='avatar' style='margin-bottom:0px; margin-top:6px;'>";
					$html .= get_avatar($post->topic_owner, 64);
				$html .= "</div>";
			
				$html .= "<div class='topic-post-header-with-fav'>";
			
					$html .= "<div class='topic-post-header'>";

						if ( ($post->topic_owner == $current_user->ID) || (current_user_can('level_10')) ) {
							$html .= "<a href='javascript:void(0)' title='".$post->tid."' id='edit-this-topic' class='edit_topic edit label' style='cursor:pointer'>".__("Edit", "wp-symposium")."</a>";
						}

					
						$post_text = symposium_bbcode_replace(stripslashes($post->topic_subject));
						$html .= stripslashes($post_text);
			
						if ($post->topic_approved != 'on') { $html .= " <em>[".__("pending approval", "wp-symposium")."]</em>"; }

						// Favourites
						if (is_user_logged_in()) {
							if (strpos(get_symposium_meta($current_user->ID, 'forum_favs'), "[".$post->tid."]") === FALSE) { 
								$html .= "<img title='".$post->tid."' id='fav_link' src='".$plugin."images/star-off.gif' style='height:22px; width:22px; cursor:pointer;' alt='".__("Click to add to favourites", "wp-symposium")."' />";						
							} else {
								$html .= "<img title='".$post->tid."' id='fav_link' src='".$plugin."images/star-on.gif' style='height:22px; width:22px; cursor:pointer;' alt='".__("Click to remove to favourites", "wp-symposium")."' />";						
							}
						}


					$html .= "</div><div style='clear:both'></div>";
										
					$html .= "<div class='started-by' style='margin-top:10px'>";
					$html .= __("Started by", "wp-symposium");
					if ( substr($config->forum_ranks, 0, 2) == 'on' ) {
						$html .= " <span class='forum_rank'>".forum_rank($post->topic_owner)."</span>";
					}
					$html .= " ".symposium_profile_link($post->topic_owner);
					$html .= " ".symposium_time_ago($post->topic_started);
					$html .= "</div>";

					$post_text = symposium_make_url(stripslashes($post->topic_post));
					$post_text = symposium_bbcode_replace($post_text);
					$html .= "<div class='topic-post-post'>".str_replace(chr(13), "<br />", $post_text)."</div>";
				
				$html .= "</div><div style='clear:both'></div>";				
												
			$html .= "</div>";

			// Update views
			if ($user_level == 5) {
				if ($config->include_admin == "on") { 
					$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix."symposium_topics SET topic_views = topic_views + 1 WHERE tid = %d", $post->tid) );
				}
			} else {
				$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix."symposium_topics SET topic_views = topic_views + 1 WHERE tid = %d", $post->tid) );
			}
					
		$html .= "</div>";		
	

		// Replies
		$sql = "SELECT tid, topic_subject, topic_approved, topic_post, topic_date, topic_owner, display_name, ID
			FROM ".$wpdb->prefix."symposium_topics t INNER JOIN ".$wpdb->base_prefix."users u ON t.topic_owner = u.ID 
			WHERE (t.topic_approved = 'on' OR t.topic_owner = %d) AND t.topic_parent = %d ORDER BY tid";
		
		if ($config->oldest_first != "on") { $sql .= " DESC"; }
	
		$child_query = $wpdb->get_results($wpdb->prepare($sql, $current_user->ID, $post->tid));

		$html .= "<div id='child-posts'>";

			if ($child_query) {

				foreach ($child_query as $child) {

					$html .= "<div id='reply".$child->tid."' class='child-reply";
						$trusted = get_symposium_meta($child->topic_owner, 'trusted');
						if ($trusted == 'on') { $html .= " trusted"; }
						$html .= "'>";
						if ( ($child->topic_owner == $current_user->ID) || (current_user_can('level_10')) ) {
							$html .= "<a href='javascript:void(0)' class='floatright link_cursor delete_forum_reply' style='display:none' id='".$child->tid."'>".__("Delete", "wp-symposium")."</a>";
							$html .= "<a href='javascript:void(0)' class='floatright link_cursor edit_forum_reply' style='display:none; margin-right: 10px' id='".$child->tid."'>".__("Edit", "wp-symposium")."</a>";
						}
						$html .= "<div class='avatar'>";
							$html .= get_avatar($child->ID, 64);
						$html .= "</div>";
						$html .= "<div class='started-by'>";
						if ( substr($config->forum_ranks, 0, 2) == 'on' ) {
							$html .= " <span class='forum_rank'>".forum_rank($child->topic_owner)."</span> ";
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

					// Separator
					$html .= "<div class='sep'></div>";						
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
		if (is_user_logged_in()) {
			$html .= '<div id="reply-topic-bottom" name="reply-topic-bottom">';
			if ($wpdb->get_var($wpdb->prepare("SELECT allow_replies FROM ".$wpdb->prefix."symposium_topics WHERE tid = %d", $post->tid)) == "on")
			{
				$html .= '<input type="hidden" id="symposium_reply_tid" value="'.$post->tid.'">';
				$html .= '<input type="hidden" id="symposium_reply_cid" value="'.$cat_id.'">';
				$html .= '<div class="reply-topic-subject label">'.__("Reply to this Topic", "wp-symposium").'</div>';
				$html .= '<textarea class="textarea_Editor reply-topic-text elastic" id="symposium_reply_text"></textarea>';
				$html .= '<input type="submit" id="quick-reply-warning" class="symposium-button" style="float: left" value="'.__("Reply", "wp-symposium").'" />';
			}				
			$html .= '</div>';
		}
		
	}
		
	echo symposium_smilies($html);
	
}

>>>>>>> .r358967
// Get Forum ****************************************************************
<<<<<<< .mine
=======
=======
<<<<<<< .mine
// Delete Reply *************************************************************
if ($_POST['action'] == 'deleteReply') {
>>>>>>> .r358238
	if (current_user_can('level_10')) {
		$tid = $_POST['topic_id'];
		if (symposium_safe_param($tid)) {
			$wpdb->query("DELETE FROM ".$wpdb->prefix."symposium_topics WHERE tid = ".$tid);
		}
		
		echo $tid;
		
	} else {
		echo "NOT ADMIN";
	}
	
}

>>>>>>> .r358967
// Delete Topic and Replies *************************************************
if ($_POST['action'] == 'deleteTopic') {

	if (current_user_can('level_10')) {
		$tid = $_POST['topic_id'];
		if (symposium_safe_param($tid)) {
			$wpdb->query("DELETE FROM ".$wpdb->prefix."symposium_topics WHERE topic_parent = ".$tid);
			$wpdb->query("DELETE FROM ".$wpdb->prefix."symposium_topics WHERE tid = ".$tid);
			$wpdb->query("DELETE FROM ".$wpdb->prefix."symposium_subs WHERE tid = ".$tid);
		}
		
		echo $tid;
		
	} else {
		echo "NOT ADMIN";
	}
	
}

// New Topic ****************************************************************
if ($_POST['action'] == 'forumNewPost') {

	$new_topic_subject = $_POST['subject'];
	$new_topic_text = $_POST['text'];
	$new_topic_category = $_POST['category'];
	$new_topic_subscribe = $_POST['subscribed'];



	$config = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."symposium_config"));

<<<<<<< .mine
	$plugin = get_site_url().'/wp-content/plugins/wp-symposium/';

	// Get forum URL worked out
	$forum_url = symposium_get_url('forum');
	if ($forum_url[strlen($forum_url)-1] != '/') { $forum_url .= '/'; }
	if (isset($_GET['page_id']) && $_GET['page_id'] != '') {
		// No Permalink
		$q = "&";
	} else {
		$q = "?";
	}
	
=======
<<<<<<< .mine
	$plugin = get_site_url().'/wp-content/plugins/wp-symposium/';

	// Get forum URL worked out
	$forum_url = symposium_get_url('forum');
	if ($forum_url[strlen($forum_url)-1] != '/') { $forum_url .= '/'; }
	if (isset($_GET['page_id']) && $_GET['page_id'] != '') {
		// No Permalink
		$q = "&";
	} else {
		$q = "?";
	}
	
>>>>>>> .r358967
	// Post preview
	$snippet_length = $config->preview1;
	if ($snippet_length == '') { $snippet_length = '45'; }
	$snippet_length_long = $config->preview2;
	if ($snippet_length_long == '') { $snippet_length_long = '45'; }
<<<<<<< .mine

	// Breadcrumbs
		
	$html = '';
=======
=======
	// Check for moderation
	if ($config->moderation == "on") {
		$topic_approved = "";
	} else {
		$topic_approved = "on";
	}

	if ($new_topic_subject == '') { $new_topic_subject = __('No subject', 'wp-symposium'); }
	if ($new_topic_text == '') { $new_topic_text = __('No message', 'wp-symposium');  }
>>>>>>> .r358238

	// Breadcrumbs
		
	$html = '';
>>>>>>> .r358967
	
<<<<<<< .mine
	$html .= '<div id="forum_breadcrumbs" class="breadcrumbs">';

		if ($cat_id > 0) {
	
			$this_level = $wpdb->get_row($wpdb->prepare("SELECT cid, title, cat_parent FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d", $cat_id));
			if ($this_level->cat_parent == 0) {
				if ($config->forum_ajax == 'on') {
					$html .= '<a href="javascript:void(0)" class="category_title" title="0">'.__('Forum Home', 'wp-symposium')."</a> > ";
					$html .= '<a href="javascript:void(0);" class="category_title" title="'.$this_level->cid.'">'.trim($this_level->title).'</a>';
				} else {
					$html .= '<a href="'.$forum_url.'" title="0">'.__('Forum Home', 'wp-symposium')."</a> > ";
					$html .= '<a href="'.$forum_url.$q."cid=".$this_level->cid.'" title="'.$this_level->cid.'">'.trim($this_level->title).'</a>';
				}
			} else {

				$parent_level = $wpdb->get_row($wpdb->prepare("SELECT cid, title, cat_parent FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d", $this_level->cat_parent));

				if ($parent_level->cat_parent == 0) {
					if ($config->forum_ajax == 'on') {
						$html .= '<a href="javascript:void(0)" class="category_title" title="0">'.__('Forum Home', 'wp-symposium')."</a> > ";
					} else {
						$html .= '<a href="'.$forum_url.'" title="0">'.__('Forum Home', 'wp-symposium')."</a> > ";
					}
				} else {
					$parent_level_2 = $wpdb->get_row($wpdb->prepare("SELECT cid, title, cat_parent FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d", $parent_level->cat_parent));
					if ($config->forum_ajax == 'on') {
						$html .= '<a href="javascript:void(0)" class="category_title" title="0">'.__('Forum Home', 'wp-symposium')."</a> > " ;
						$html .= '<a href="javascript:void(0)" class="category_title" title="'.$parent_level_2->cid.'">'.$parent_level_2->title."</a> > ";
					} else {
						$html .= '<a href="'.$forum_url.'" title="0">'.__('Forum Home', 'wp-symposium')."</a> > " ;
						$html .= '<a href="'.$forum_url.$q."cid=".$parent_level_2->cid.'"  title="'.$parent_level_2->cid.'">'.$parent_level_2->title."</a> > ";
					}
				}
				if ($config->forum_ajax == 'on') {
					$html .= '<a href="javascript:void(0)" class="category_title" title="'.$parent_level->cid.'">'.$parent_level->title."</a> > " ;
					$html .= '<a href="javascript:void(0)" class="category_title" title="'.$this_level->cid.'">'.$this_level->title."</a>" ;
				} else {
					$html .= '<a href="'.$forum_url.$q."cid=".$parent_level->cid.'" title="'.$parent_level->cid.'">'.$parent_level->title."</a> > " ;
					$html .= '<a href="'.$forum_url.$q."cid=".$this_level->cid.'" title="'.$this_level->cid.'">'.$this_level->title."</a>" ;
				}
			}
			
		}

		// New Topic Form	
		if (is_user_logged_in()) {

			$html .= '<input type="submit" class="symposium-button floatright" id="new-topic-button" value="'.__("New Topic", "wp-symposium").'" />';

			$html .= '<div name="new-topic" id="new-topic" style="display:none;">';
				$html .= '<input type="hidden" id="cid" value="'.$cat_id.'">';
				$html .= '<div id="new-topic-subject-label" class="new-topic-subject label">'.__("Topic Subject", "wp-symposium").'</div>';
				$html .= '<input class="new-topic-subject-input" type="text" id="new_topic_subject" value="">';
				$html .= '<div class="new-topic-subject label">'.__("First Post in Topic", "wp-symposium").'</div>';
				$html .= '<textarea class="new-topic-subject-text elastic" id="new_topic_text">';
				$html .= '</textarea>';
				$defaultcat = $wpdb->get_var($wpdb->prepare("SELECT cid FROM ".$wpdb->prefix."symposium_cats WHERE defaultcat = 'on'"));

				$html .= '<div class="new-topic-category label">'.__("Select a Category", "wp-symposium").': ';
				if (current_user_can('level_10')) {
					$categories = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix.'symposium_cats ORDER BY title');			
				} else {
					$categories = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix.'symposium_cats WHERE allow_new = "on" ORDER BY title');			
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

				$html .= '<div class="emailreplies label"><input type="checkbox" id="new_topic_subscribe"> '.__("Email me when I get any replies", "wp-symposium").'</div>';
				$html .= '<input id="new_post" type="submit" class="symposium-button" style="float: left" value="'.__("Post", "wp-symposium").'" />';
				$html .= '<input id="cancel_post" type="submit" class="symposium-button clear" onClick="javascript:void(0)" value="'.__("Cancel", "wp-symposium").'" />';

			$html .= '</div>';

		} else {

			$html .= __("Until you login, you can only view the forum.", "wp-symposium");
			$html .= " <a href=".wp_login_url( get_permalink() )." class='simplemodal-login' title='".__("Login", "wp-symposium")."'>".__("Login", "wp-symposium").".</a>";
			$html .= "<br />";

		}
						
	$html .= '</div>';
	
	// Options
=======
<<<<<<< .mine
	$html .= '<div id="forum_breadcrumbs" class="breadcrumbs">';

		if ($cat_id > 0) {
	
			$this_level = $wpdb->get_row($wpdb->prepare("SELECT cid, title, cat_parent FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d", $cat_id));
			if ($this_level->cat_parent == 0) {
				if ($config->forum_ajax == 'on') {
					$html .= '<a href="javascript:void(0)" class="category_title" title="0">'.__('Forum Home', 'wp-symposium')."</a> > ";
					$html .= '<a href="javascript:void(0);" class="category_title" title="'.$this_level->cid.'">'.trim($this_level->title).'</a>';
				} else {
					$html .= '<a href="'.$forum_url.'" title="0">'.__('Forum Home', 'wp-symposium')."</a> > ";
					$html .= '<a href="'.$forum_url.$q."cid=".$this_level->cid.'" title="'.$this_level->cid.'">'.trim($this_level->title).'</a>';
				}
			} else {

				$parent_level = $wpdb->get_row($wpdb->prepare("SELECT cid, title, cat_parent FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d", $this_level->cat_parent));

				if ($parent_level->cat_parent == 0) {
					if ($config->forum_ajax == 'on') {
						$html .= '<a href="javascript:void(0)" class="category_title" title="0">'.__('Forum Home', 'wp-symposium')."</a> > ";
					} else {
						$html .= '<a href="'.$forum_url.'" title="0">'.__('Forum Home', 'wp-symposium')."</a> > ";
					}
				} else {
					$parent_level_2 = $wpdb->get_row($wpdb->prepare("SELECT cid, title, cat_parent FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d", $parent_level->cat_parent));
					if ($config->forum_ajax == 'on') {
						$html .= '<a href="javascript:void(0)" class="category_title" title="0">'.__('Forum Home', 'wp-symposium')."</a> > " ;
						$html .= '<a href="javascript:void(0)" class="category_title" title="'.$parent_level_2->cid.'">'.$parent_level_2->title."</a> > ";
					} else {
						$html .= '<a href="'.$forum_url.'" title="0">'.__('Forum Home', 'wp-symposium')."</a> > " ;
						$html .= '<a href="'.$forum_url.$q."cid=".$parent_level_2->cid.'"  title="'.$parent_level_2->cid.'">'.$parent_level_2->title."</a> > ";
					}
				}
				if ($config->forum_ajax == 'on') {
					$html .= '<a href="javascript:void(0)" class="category_title" title="'.$parent_level->cid.'">'.$parent_level->title."</a> > " ;
					$html .= '<a href="javascript:void(0)" class="category_title" title="'.$this_level->cid.'">'.$this_level->title."</a>" ;
				} else {
					$html .= '<a href="'.$forum_url.$q."cid=".$parent_level->cid.'" title="'.$parent_level->cid.'">'.$parent_level->title."</a> > " ;
					$html .= '<a href="'.$forum_url.$q."cid=".$this_level->cid.'" title="'.$this_level->cid.'">'.$this_level->title."</a>" ;
				}
			}
			
		}

		// New Topic Form	
		if (is_user_logged_in()) {

			$html .= '<input type="submit" class="symposium-button floatright" id="new-topic-button" value="'.__("New Topic", "wp-symposium").'" />';

			$html .= '<div name="new-topic" id="new-topic" style="display:none;">';
				$html .= '<input type="hidden" id="cid" value="'.$cat_id.'">';
				$html .= '<div id="new-topic-subject-label" class="new-topic-subject label">'.__("Topic Subject", "wp-symposium").'</div>';
				$html .= '<input class="new-topic-subject-input" type="text" id="new_topic_subject" value="">';
				$html .= '<div class="new-topic-subject label">'.__("First Post in Topic", "wp-symposium").'</div>';
				$html .= '<textarea class="new-topic-subject-text elastic" id="new_topic_text">';
				$html .= '</textarea>';
				$defaultcat = $wpdb->get_var($wpdb->prepare("SELECT cid FROM ".$wpdb->prefix."symposium_cats WHERE defaultcat = 'on'"));

				$html .= '<div class="new-topic-category label">'.__("Select a Category", "wp-symposium").': ';
				if (current_user_can('level_10')) {
					$categories = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix.'symposium_cats ORDER BY title');			
				} else {
					$categories = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix.'symposium_cats WHERE allow_new = "on" ORDER BY title');			
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

				$html .= '<div class="emailreplies label"><input type="checkbox" id="new_topic_subscribe"> '.__("Email me when I get any replies", "wp-symposium").'</div>';
				$html .= '<input id="new_post" type="submit" class="symposium-button" style="float: left" value="'.__("Post", "wp-symposium").'" />';
				$html .= '<input id="cancel_post" type="submit" class="symposium-button clear" onClick="javascript:void(0)" value="'.__("Cancel", "wp-symposium").'" />';

			$html .= '</div>';

		} else {

			$html .= __("Until you login, you can only view the forum.", "wp-symposium");
			$html .= " <a href=".wp_login_url( get_permalink() )." class='simplemodal-login' title='".__("Login", "wp-symposium")."'>".__("Login", "wp-symposium").".</a>";
			$html .= "<br />";

		}
						
	$html .= '</div>';
	
>>>>>>> .r358967
	if (is_user_logged_in()) {
=======
	if ( is_user_logged_in() ) {
>>>>>>> .r358238
		
<<<<<<< .mine
		$send_summary = $config->send_summary;
		if ($send_summary == "on" && $cat_id == 0) {
			$forum_digest = get_symposium_meta($current_user->ID, 'forum_digest');
			$html .= "<div class='symposium_subscribe_option label'>";
			$html .= "<input type='checkbox' id='symposium_digest' name='symposium_digest'";
			if ($forum_digest == 'on') { $html .= ' checked'; } 
			$html .= "> ".__("Receive digests via email", "wp-symposium");
			$html .= "</div>";
=======
		// Get forum URL worked out
		$forum_url = symposium_get_url('forum');
		if ($forum_url[strlen($forum_url)-1] != '/') { $forum_url .= '/'; }
		if (isset($_GET[page_id]) && $_GET[page_id] != '') {
			// No Permalink
			$q = "&";
		} else {
			$q = "?";
>>>>>>> .r358238
		}
<<<<<<< .mine
		if ($cat_id > 0) {
			$subscribed_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix."symposium_subs WHERE tid = 0 AND cid = %d AND uid = %d", $cat_id, $current_user->ID));
			$html .= "<div class='symposium_subscribe_option label'>";
<<<<<<< .mine
			$html .= "<input type='checkbox' title='".$cat_id."' id='symposium_subscribe' name='symposium_subscribe'";
=======
			$html .= "<input type='checkbox' title='".$cat_id."' id='symposium_subscribe' name='symposium_subscribe'";
=======
		
		// Check for duplicates
		$topic_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$topics." WHERE topic_subject = '".$new_topic_subject."' and topic_post = '".$new_topic_text."' AND topic_owner = ".$current_user->ID));

		if ($topic_count > 1) {
			// Don't double post (also helps reduce spam)
		} else {						
			
			// Store new topic in post
			
			// Replace carriage returns
			$new_topic_text = str_replace("\n", chr(13), $new_topic_text);	
			
			// Don't allow HTML
			$new_topic_text = str_replace("<", "&lt;", $new_topic_text);
			$new_topic_text = str_replace(">", "&gt;", $new_topic_text);

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
		        	$new_topic_subject, 
		        	$new_topic_category,
		        	$new_topic_text, 
		        	date("Y-m-d H:i:s"), 
					date("Y-m-d H:i:s"), 
					$current_user->ID, 
					0,
					0,
					$topic_approved
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
			
			// Set category to the category posted into
			$cat_id = $new_topic_category;
							
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
				// Email people who want to know	
				$query = $wpdb->get_results("
					SELECT user_email
					FROM ".$wpdb->base_prefix."users u RIGHT JOIN ".$wpdb->prefix."symposium_subs s ON s.uid = u.ID 
					WHERE s.tid = 0 AND u.ID != ".$current_user->ID." AND s.cid = ".$cat_id);
					
				if ($query) {					
					foreach ($query as $user) {
						symposium_sendmail($user->user_email, __('New Forum Topic', 'wp-symposium'), $body);						
					}						
				}
			} else {
				// Email admin if post needs approval
				$body = "<span style='font-size:24px font-style:italic;'>".__('Moderation Required', 'wp-symposium')."</span><br /><br />".$body;
				symposium_sendmail(get_bloginfo('admin_email'), __('Moderation Required', 'wp-symposium'), $body);
			}	
			
			echo $url;
			exit;
		}
	}
	
	echo 'NOT LOGGED IN';

}

// Get Topic ****************************************************************
if ($_POST['action'] == 'getTopic') {
	
	$topic_id = $_POST['topic_id'];
	
	$config = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."symposium_config"));
	$plugin = get_site_url().'/wp-content/plugins/wp-symposium/';
	
	// Get forum URL worked out
	$forum_url = symposium_get_url('forum');
	if ($forum_url[strlen($forum_url)-1] != '/') { $forum_url .= '/'; }
	if (isset($_GET['page_id']) && $_GET['page_id'] != '') {
		// No Permalink
		$q = "&";
	} else {
		$q = "?";
	}
	
	$post = $wpdb->get_row("
		SELECT tid, topic_subject, topic_approved, topic_category, topic_post, topic_started, display_name, topic_sticky, topic_owner 
		FROM ".$wpdb->prefix."symposium_topics t INNER JOIN ".$wpdb->base_prefix."users u ON t.topic_owner = u.ID 
		WHERE (t.topic_approved = 'on' OR t.topic_owner = ".$current_user->ID.") AND tid = ".$topic_id);
		
	if ($post) {

		// Breadcrumbs
		$cat_id = $post->topic_category;
		
		$html = '';
		$html .= '<div id="topic_breadcrumbs" class="breadcrumbs">';

			$this_level = $wpdb->get_row($wpdb->prepare("SELECT cid, title, cat_parent FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d", $cat_id));
			if ($this_level->cat_parent == 0) {
				if ($config->forum_ajax == 'on') {
					$html .= '<a href="javascript:void(0)" class="category_title" title="0">'.__('Forum Home', 'wp-symposium')."</a> > ";
					$html .= '<a href="javascript:void(0);" class="category_title" title="'.$this_level->cid.'">'.trim($this_level->title).'</a>';
				} else {
					$html .= '<a href="'.$forum_url.'" title="0">'.__('Forum Home', 'wp-symposium')."</a> > ";
					$html .= '<a href="'.$forum_url.$q."cid=".$this_level->cid.'" title="'.$this_level->cid.'">'.trim($this_level->title).'</a>';
				}
			} else {

				$parent_level = $wpdb->get_row($wpdb->prepare("SELECT cid, title, cat_parent FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d", $this_level->cat_parent));

				if ($parent_level->cat_parent == 0) {
					if ($config->forum_ajax == 'on') {
						$html .= '<a href="javascript:void(0)" class="category_title" title="0">'.__('Forum Home', 'wp-symposium')."</a> > ";
					} else {
						$html .= '<a href="'.$forum_url.'" title="0">'.__('Forum Home', 'wp-symposium')."</a> > ";
					}
				} else {
					$parent_level_2 = $wpdb->get_row($wpdb->prepare("SELECT cid, title, cat_parent FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d", $parent_level->cat_parent));
					if ($config->forum_ajax == 'on') {
						$html .= '<a href="javascript:void(0)" class="category_title" title="0">'.__('Forum Home', 'wp-symposium')."</a> > " ;
						$html .= '<a href="javascript:void(0)" class="category_title" title="'.$parent_level_2->cid.'">'.$parent_level_2->title."</a> > ";
					} else {
						$html .= '<a href="'.$forum_url.'" title="0">'.__('Forum Home', 'wp-symposium')."</a> > " ;
						$html .= '<a href="'.$forum_url.$q."cid=".$parent_level_2->cid.'"  title="'.$parent_level_2->cid.'">'.$parent_level_2->title."</a> > ";
					}
				}
				if ($config->forum_ajax == 'on') {
					$html .= '<a href="javascript:void(0)" class="category_title" title="'.$parent_level->cid.'">'.$parent_level->title."</a> > " ;
					$html .= '<a href="javascript:void(0)" class="category_title" title="'.$this_level->cid.'">'.$this_level->title."</a>" ;
				} else {
					$html .= '<a href="'.$forum_url.$q."cid=".$parent_level->cid.'" title="'.$parent_level->cid.'">'.$parent_level->title."</a> > " ;
					$html .= '<a href="'.$forum_url.$q."cid=".$this_level->cid.'" title="'.$this_level->cid.'">'.$this_level->title."</a>" ;
				}
			}

		$html .= '</div>';
		
		// Subscribe, Sticky and Allow Replies
		$html .= "<div class='floatleft label'>";
			$html .= "<input type='checkbox' title='".$post->tid."' id='subscribe' name='subscribe'";
			$subscribed_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix."symposium_subs WHERE tid = %d and uid = %d", $post->tid, $current_user->ID));
>>>>>>> .r358238
>>>>>>> .r358967
			if ($subscribed_count > 0) { $html .= ' checked'; } 
			$html .= "> ".__("Receive emails for new replies", "wp-symposium");
			if (current_user_can('level_10')) {
				$html .= "&nbsp;&nbsp;&nbsp;<input type='checkbox' title='".$post->tid."' id='sticky' name='sticky'";
				if ($post->topic_sticky > 0) { $html .= ' checked'; }
				$html .= "> ".__("Sticky", "wp-symposium");
				$html .= "&nbsp;&nbsp;&nbsp;<input type='checkbox' title='".$post->tid."' id='replies' name='replies'";
				$allow_replies = $wpdb->get_var($wpdb->prepare("SELECT allow_replies FROM ".$wpdb->prefix."symposium_topics WHERE tid = %d", $post->tid));
				if ($allow_replies == "on") { $html .= ' checked'; }
				$html .= "> ".__("Replies allowed", "wp-symposium");
			}
		$html .= "</div>";
		
		// Sharing icons
		if ($config->sharing != '') {
			$html .= show_sharing_icons($cat_id, $post->tid, $config->sharing);
		}
		
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
			$html .= '<input type="submit" class="symposium-button edit_topic_submit" value="'.__("Update", "wp-symposium").'" />';
			$html .= '<input type="submit" class="symposium-button edit_topic_cancel" value="'.__("Cancel", "wp-symposium").'" />';
			$html .= '</div>';
		$html .= '</div>';
		
		// Topic starting post
		$html .= "<div id='starting-post'>";
		
			// Show topic header
			$html .= "<div id='top_of_first_post'>";
			
				$html .= "<div class='avatar' style='margin-bottom:0px; margin-top:6px;'>";
					$html .= get_avatar($post->topic_owner, 64);
				$html .= "</div>";
			
				$html .= "<div class='topic-post-header-with-fav'>";
			
					$html .= "<div class='topic-post-header'>";

						if ( ($post->topic_owner == $current_user->ID) || (current_user_can('level_10')) ) {
							$html .= "<a href='javascript:void(0)' title='".$post->tid."' id='edit-this-topic' class='edit_topic edit label' style='cursor:pointer'>".__("Edit", "wp-symposium")."</a>";
						}

<<<<<<< .mine
	$html .= "<div style='overflow:visible;'>";
	
		// Options above forum table
		$html .= "<div id='forum_options'>";
=======
<<<<<<< .mine
	$html .= "<div style='overflow:visible;'>";
	
		// Options above forum table
		$html .= "<div id='forum_options'>";
=======
					
						$post_text = symposium_bbcode_replace(stripslashes($post->topic_subject));
						$html .= stripslashes($post_text);
			
						if ($post->topic_approved != 'on') { $html .= " <em>[".__("pending approval", "wp-symposium")."]</em>"; }
>>>>>>> .r358238
>>>>>>> .r358967

<<<<<<< .mine
			$html .= "<a id='show_search' class='label' href='javascript:void(0)'>".__("Search", "wp-symposium")."</a>";
			$html .= "&nbsp;&nbsp;&nbsp;&nbsp;<a id='show_all_activity' class='label' href='javascript:void(0)'>".__("All Activity", "wp-symposium")."</a>";
			$html .= "&nbsp;&nbsp;&nbsp;&nbsp;<a id='show_threads_activity' class='label' href='javascript:void(0)'>".__("Latest Topics", "wp-symposium")."</a>";
=======
<<<<<<< .mine
			$html .= "<a id='show_search' class='label' href='javascript:void(0)'>".__("Search", "wp-symposium")."</a>";
=======
						// Favourites
						if (is_user_logged_in()) {
							if (strpos(get_symposium_meta($current_user->ID, 'forum_favs'), "[".$post->tid."]") === FALSE) { 
								$html .= "<img title='".$post->tid."' id='fav_link' src='".$plugin."images/star-off.gif' style='height:22px; width:22px; cursor:pointer;' alt='".__("Click to add to favourites", "wp-symposium")."' />";						
							} else {
								$html .= "<img title='".$post->tid."' id='fav_link' src='".$plugin."images/star-on.gif' style='height:22px; width:22px; cursor:pointer;' alt='".__("Click to remove to favourites", "wp-symposium")."' />";						
							}
						}
>>>>>>> .r358238
>>>>>>> .r358967

<<<<<<< .mine
			if (is_user_logged_in()) {
				$html .= "&nbsp;&nbsp;&nbsp;&nbsp;<a id='show_activity' class='label' href='javascript:void(0)'>".__("My Activity", "wp-symposium")."</a>";
				$html .= "&nbsp;&nbsp;&nbsp;&nbsp;<a id='show_favs' class='label' href='javascript:void(0)'>".__("My Favorites", "wp-symposium")."</a>";
=======
<<<<<<< .mine
			if (is_user_logged_in()) {
				$html .= "&nbsp;&nbsp;&nbsp;&nbsp;<a id='show_activity' class='label' href='javascript:void(0)'>".__("My Activity", "wp-symposium")."</a>";
				$html .= "&nbsp;&nbsp;&nbsp;&nbsp;<a id='show_favs' class='label' href='javascript:void(0)'>".__("My Favourites", "wp-symposium")."</a>";
=======

					$html .= "</div><div style='clear:both'></div>";
										
					$html .= "<div class='started-by' style='margin-top:10px'>";
					$html .= __("Started by", "wp-symposium");
					if ( substr($config->forum_ranks, 0, 2) == 'on' ) {
						$html .= " <span class='forum_rank'>".forum_rank($post->topic_owner)."</span>";
					}
					$html .= " ".symposium_profile_link($post->topic_owner);
					$html .= " ".symposium_time_ago($post->topic_started);
					$html .= "</div>";

					$post_text = symposium_make_url(stripslashes($post->topic_post));
					$post_text = symposium_bbcode_replace($post_text);
					$html .= "<div class='topic-post-post'>".str_replace(chr(13), "<br />", $post_text)."</div>";
				
				$html .= "</div><div style='clear:both'></div>";				
												
			$html .= "</div>";

			// Update views
			if ($user_level == 5) {
				if ($config->include_admin == "on") { 
					$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix."symposium_topics SET topic_views = topic_views + 1 WHERE tid = %d", $post->tid) );
				}
			} else {
				$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix."symposium_topics SET topic_views = topic_views + 1 WHERE tid = %d", $post->tid) );
			}
					
		$html .= "</div>";		
	

		// Replies
		$sql = "SELECT tid, topic_subject, topic_approved, topic_post, topic_date, topic_owner, display_name, ID
			FROM ".$wpdb->prefix."symposium_topics t INNER JOIN ".$wpdb->base_prefix."users u ON t.topic_owner = u.ID 
			WHERE (t.topic_approved = 'on' OR t.topic_owner = %d) AND t.topic_parent = %d ORDER BY tid";
		
		if ($config->oldest_first != "on") { $sql .= " DESC"; }
	
		$child_query = $wpdb->get_results($wpdb->prepare($sql, $current_user->ID, $post->tid));

		$html .= "<div id='child-posts'>";

			if ($child_query) {

				foreach ($child_query as $child) {

					$html .= "<div id='reply".$child->tid."' class='child-reply";
						$trusted = get_symposium_meta($child->topic_owner, 'trusted');
						if ($trusted == 'on') { $html .= " trusted"; }
						$html .= "'>";
						if ( ($child->topic_owner == $current_user->ID) || (current_user_can('level_10')) ) {
							$html .= "<a href='javascript:void(0)' class='floatright link_cursor delete_forum_reply' style='display:none' id='".$child->tid."'>".__("Delete", "wp-symposium")."</a>";
							$html .= "<a href='javascript:void(0)' class='floatright link_cursor edit_forum_reply' style='display:none; margin-right: 10px' id='".$child->tid."'>".__("Edit", "wp-symposium")."</a>";
						}
						$html .= "<div class='avatar'>";
							$html .= get_avatar($child->ID, 64);
						$html .= "</div>";
						$html .= "<div class='started-by'>";
						if ( substr($config->forum_ranks, 0, 2) == 'on' ) {
							$html .= " <span class='forum_rank'>".forum_rank($child->topic_owner)."</span> ";
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

					// Separator
					$html .= "<div class='sep'></div>";						
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
		if (is_user_logged_in()) {
			$html .= '<div id="reply-topic-bottom" name="reply-topic-bottom">';
			if ($wpdb->get_var($wpdb->prepare("SELECT allow_replies FROM ".$wpdb->prefix."symposium_topics WHERE tid = %d", $post->tid)) == "on")
			{
				$html .= '<input type="hidden" id="symposium_reply_tid" value="'.$post->tid.'">';
				$html .= '<input type="hidden" id="symposium_reply_cid" value="'.$cat_id.'">';
				$html .= '<div class="reply-topic-subject label">'.__("Reply to this Topic", "wp-symposium").'</div>';
				$html .= '<textarea class="textarea_Editor reply-topic-text elastic" id="symposium_reply_text"></textarea>';
				$html .= '<input type="submit" id="quick-reply-warning" class="symposium-button" style="float: left" value="'.__("Reply", "wp-symposium").'" />';
			}				
			$html .= '</div>';
		}
		
	}
		
	echo symposium_smilies($html);
	
}

=======
<<<<<<< .mine
// Delete Reply *************************************************************
if ($_POST['action'] == 'deleteReply') {

	if (current_user_can('level_10')) {
		$tid = $_POST['topic_id'];
		if (symposium_safe_param($tid)) {
			$wpdb->query("DELETE FROM ".$wpdb->prefix."symposium_topics WHERE tid = ".$tid);
		}
		
		echo $tid;
		
	} else {
		echo "NOT ADMIN";
	}
	
}

// Delete Topic and Replies *************************************************
if ($_POST['action'] == 'deleteTopic') {

	if (current_user_can('level_10')) {
		$tid = $_POST['topic_id'];
		if (symposium_safe_param($tid)) {
			$wpdb->query("DELETE FROM ".$wpdb->prefix."symposium_topics WHERE topic_parent = ".$tid);
			$wpdb->query("DELETE FROM ".$wpdb->prefix."symposium_topics WHERE tid = ".$tid);
			$wpdb->query("DELETE FROM ".$wpdb->prefix."symposium_subs WHERE tid = ".$tid);
		}
		
		echo $tid;
		
	} else {
		echo "NOT ADMIN";
	}
	
}

// New Topic ****************************************************************
if ($_POST['action'] == 'forumNewPost') {

	$new_topic_subject = $_POST['subject'];
	$new_topic_text = $_POST['text'];
	$new_topic_category = $_POST['category'];
	$new_topic_subscribe = $_POST['subscribed'];

	$config = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."symposium_config"));

	// Check for moderation
	if ($config->moderation == "on") {
		$topic_approved = "";
	} else {
		$topic_approved = "on";
	}

	if ($new_topic_subject == '') { $new_topic_subject = __('No subject', 'wp-symposium'); }
	if ($new_topic_text == '') { $new_topic_text = __('No message', 'wp-symposium');  }
	
	if ( is_user_logged_in() ) {
		
		// Get forum URL worked out
		$forum_url = symposium_get_url('forum');
		if ($forum_url[strlen($forum_url)-1] != '/') { $forum_url .= '/'; }
		if (isset($_GET[page_id]) && $_GET[page_id] != '') {
			// No Permalink
			$q = "&";
		} else {
			$q = "?";
		}
		
		// Check for duplicates
		$topic_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$topics." WHERE topic_subject = '".$new_topic_subject."' and topic_post = '".$new_topic_text."' AND topic_owner = ".$current_user->ID));

		if ($topic_count > 1) {
			// Don't double post (also helps reduce spam)
		} else {						
			
			// Store new topic in post
			
			// Don't allow HTML
			$new_topic_text = str_replace("<", "&lt;", $new_topic_text);
			$new_topic_text = str_replace(">", "&gt;", $new_topic_text);

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
		        	$new_topic_subject, 
		        	$new_topic_category,
		        	$new_topic_text, 
		        	date("Y-m-d H:i:s"), 
					date("Y-m-d H:i:s"), 
					$current_user->ID, 
					0,
					0,
					$topic_approved
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
>>>>>>> .r358238
>>>>>>> .r358967
			}
<<<<<<< .mine
=======
<<<<<<< .mine
=======
			
			// Set category to the category posted into
			$cat_id = $new_topic_category;
							
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
				// Email people who want to know	
				$query = $wpdb->get_results("
					SELECT user_email
					FROM ".$wpdb->base_prefix."users u RIGHT JOIN ".$wpdb->prefix."symposium_subs s ON s.uid = u.ID 
					WHERE s.tid = 0 AND u.ID != ".$current_user->ID." AND s.cid = ".$cat_id);
					
				if ($query) {					
					foreach ($query as $user) {
						symposium_sendmail($user->user_email, __('New Forum Topic', 'wp-symposium'), $body);						
					}						
				}
			} else {
				// Email admin if post needs approval
				$body = "<span style='font-size:24px font-style:italic;'>".__('Moderation Required', 'wp-symposium')."</span><br /><br />".$body;
				symposium_sendmail(get_bloginfo('admin_email'), __('Moderation Required', 'wp-symposium'), $body);
			}	
			
			echo $url;
			exit;
		}
	}
	
	echo 'NOT LOGGED IN';

}

// Get Topic ****************************************************************
if ($_POST['action'] == 'getTopic') {
	
	$topic_id = $_POST['topic_id'];
	
	$config = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."symposium_config"));
	$plugin = get_site_url().'/wp-content/plugins/wp-symposium/';
	
	// Get forum URL worked out
	$forum_url = symposium_get_url('forum');
	if ($forum_url[strlen($forum_url)-1] != '/') { $forum_url .= '/'; }
	if (isset($_GET[page_id]) && $_GET[page_id] != '') {
		// No Permalink
		$q = "&";
	} else {
		$q = "?";
	}
	
	$post = $wpdb->get_row("
		SELECT tid, topic_subject, topic_approved, topic_category, topic_post, topic_started, display_name, topic_sticky, topic_owner 
		FROM ".$wpdb->prefix."symposium_topics t INNER JOIN ".$wpdb->base_prefix."users u ON t.topic_owner = u.ID 
		WHERE (t.topic_approved = 'on' OR t.topic_owner = ".$current_user->ID.") AND tid = ".$topic_id);
		
	if ($post) {
>>>>>>> .r358238
>>>>>>> .r358967

<<<<<<< .mine
		$html .= "</div>";
=======
<<<<<<< .mine
		$html .= "</div>";
=======
		// Breadcrumbs
		$cat_id = $post->topic_category;
		
		$html .= '<div id="topic_breadcrumbs" class="breadcrumbs">';
>>>>>>> .r358238
>>>>>>> .r358967

<<<<<<< .mine
=======
<<<<<<< .mine
=======
			$this_level = $wpdb->get_row($wpdb->prepare("SELECT cid, title, cat_parent FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d", $cat_id));
			if ($this_level->cat_parent == 0) {
				if ($config->forum_ajax == 'on') {
					$html .= '<a href="javascript:void(0)" class="category_title" title="0">'.__('Forum Home', 'wp-symposium')."</a> > ";
					$html .= '<a href="javascript:void(0);" class="category_title" title="'.$this_level->cid.'">'.trim($this_level->title).'</a>';
				} else {
					$html .= '<a href="'.$forum_url.'" title="0">'.__('Forum Home', 'wp-symposium')."</a> > ";
					$html .= '<a href="'.$forum_url.$q."cid=".$this_level->cid.'" title="'.$this_level->cid.'">'.trim($this_level->title).'</a>';
				}
			} else {

>>>>>>> .r358967
>>>>>>> .r358238
				$parent_level = $wpdb->get_row($wpdb->prepare("SELECT cid, title, cat_parent FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d", $this_level->cat_parent));

				if ($parent_level->cat_parent == 0) {
					if ($config->forum_ajax == 'on') {
						$html .= '<a href="javascript:void(0)" class="category_title" title="0">'.__('Forum Home', 'wp-symposium')."</a> > ";
					} else {
						$html .= '<a href="'.$forum_url.'" title="0">'.__('Forum Home', 'wp-symposium')."</a> > ";
					}
				} else {
					$parent_level_2 = $wpdb->get_row($wpdb->prepare("SELECT cid, title, cat_parent FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d", $parent_level->cat_parent));
					if ($config->forum_ajax == 'on') {
						$html .= '<a href="javascript:void(0)" class="category_title" title="0">'.__('Forum Home', 'wp-symposium')."</a> > " ;
						$html .= '<a href="javascript:void(0)" class="category_title" title="'.$parent_level_2->cid.'">'.$parent_level_2->title."</a> > ";
					} else {
						$html .= '<a href="'.$forum_url.'" title="0">'.__('Forum Home', 'wp-symposium')."</a> > " ;
						$html .= '<a href="'.$forum_url.$q."cid=".$parent_level_2->cid.'"  title="'.$parent_level_2->cid.'">'.$parent_level_2->title."</a> > ";
					}
				}
				if ($config->forum_ajax == 'on') {
					$html .= '<a href="javascript:void(0)" class="category_title" title="'.$parent_level->cid.'">'.$parent_level->title."</a> > " ;
					$html .= '<a href="javascript:void(0)" class="category_title" title="'.$this_level->cid.'">'.$this_level->title."</a>" ;
				} else {
					$html .= '<a href="'.$forum_url.$q."cid=".$parent_level->cid.'" title="'.$parent_level->cid.'">'.$parent_level->title."</a> > " ;
					$html .= '<a href="'.$forum_url.$q."cid=".$this_level->cid.'" title="'.$this_level->cid.'">'.$this_level->title."</a>" ;
				}
			}

		$html .= '</div>';
		
		// Subscribe, Sticky and Allow Replies
		$html .= "<div class='floatleft label'>";
			$html .= "<input type='checkbox' title='".$post->tid."' id='subscribe' name='subscribe'";
			$subscribed_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix."symposium_subs WHERE tid = %d and uid = %d", $post->tid, $current_user->ID));
			if ($subscribed_count > 0) { $html .= ' checked'; } 
			$html .= "> ".__("Receive emails for new replies", "wp-symposium");
			if (current_user_can('level_10')) {
				$html .= "&nbsp;&nbsp;&nbsp;<input type='checkbox' title='".$post->tid."' id='sticky' name='sticky'";
				if ($post->topic_sticky > 0) { $html .= ' checked'; }
				$html .= "> ".__("Sticky", "wp-symposium");
				$html .= "&nbsp;&nbsp;&nbsp;<input type='checkbox' title='".$post->tid."' id='replies' name='replies'";
				$allow_replies = $wpdb->get_var($wpdb->prepare("SELECT allow_replies FROM ".$wpdb->prefix."symposium_topics WHERE tid = %d", $post->tid));
				if ($allow_replies == "on") { $html .= ' checked'; }
				$html .= "> ".__("Replies allowed", "wp-symposium");
			}
		$html .= "</div>";
		
		// Sharing icons
<<<<<<< .mine
		if ($config->sharing != '' && $cat_id > 0) {
			$html .= show_sharing_icons($cat_id, 0, $config->sharing);
		}
=======
<<<<<<< .mine
		if ($config->sharing != '' && $cat_id > 0) {
			$html .= show_sharing_icons($cat_id, 0, $config->sharing);
		}
=======
		if ($config->sharing != '') {
			$html .= show_sharing_icons($cat_id, $post->tid, $config->sharing);
		}
		
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
			$html .= '<input type="submit" class="symposium-button edit_topic_submit" value="'.__("Update", "wp-symposium").'" />';
			$html .= '<input type="submit" class="symposium-button edit_topic_cancel" value="'.__("Cancel", "wp-symposium").'" />';
			$html .= '</div>';
		$html .= '</div>';
		
		// Topic starting post
		$html .= "<div id='starting-post'>";
		
			// Show topic header
			$html .= "<div id='top_of_first_post'>";
			
				$html .= "<div class='avatar' style='margin-bottom:0px; margin-top:6px;'>";
					$html .= get_avatar($post->topic_owner, 64);
				$html .= "</div>";
			
				$html .= "<div class='topic-post-header-with-fav'>";
			
					$html .= "<div class='topic-post-header'>";
>>>>>>> .r358238
>>>>>>> .r358967

<<<<<<< .mine
	$html .= "</div>";
=======
<<<<<<< .mine
	$html .= "</div>";
	
	// Show child categories in this category ++++++++++++++++++++++++++++++++++++++++++++++++++
=======
						if ( ($post->topic_owner == $current_user->ID) || (current_user_can('level_10')) ) {
							$html .= "<a href='javascript:void(0)' title='".$post->tid."' id='edit-this-topic' class='edit_topic edit label' style='cursor:pointer'>".__("Edit", "wp-symposium")."</a>";
						}

					
						$post_text = symposium_bbcode_replace(stripslashes($post->topic_subject));
						$html .= stripslashes($post_text);
			
						if ($post->topic_approved != 'on') { $html .= " <em>[".__("pending approval", "wp-symposium")."]</em>"; }

						// Favourites
						if (is_user_logged_in()) {
							if (strpos(get_symposium_meta($current_user->ID, 'forum_favs'), "[".$post->tid."]") === FALSE) { 
								$html .= "<img title='".$post->tid."' id='fav_link' src='".$plugin."images/star-off.gif' style='height:22px; width:22px; cursor:pointer;' alt='".__("Click to add to favourites", "wp-symposium")."' />";						
							} else {
								$html .= "<img title='".$post->tid."' id='fav_link' src='".$plugin."images/star-on.gif' style='height:22px; width:22px; cursor:pointer;' alt='".__("Click to remove to favourites", "wp-symposium")."' />";						
							}
						}


					$html .= "</div><div style='clear:both'></div>";
										
					$html .= "<div class='started-by' style='margin-top:10px'>";
					$html .= __("Started by", "wp-symposium");
					if ( substr($config->forum_ranks, 0, 2) == 'on' ) {
						$html .= " <span class='forum_rank'>".forum_rank($post->topic_owner)."</span> ";
					}
					$html .= symposium_profile_link($post->topic_owner);
					$html .= ' '.symposium_time_ago($post->topic_started);
					$html .= "</div>";

					$post_text = symposium_make_url(stripslashes($post->topic_post));
					$post_text = symposium_bbcode_replace($post_text);
					$html .= "<div class='topic-post-post'>".str_replace(chr(13), "<br />", $post_text)."</div>";
				
				$html .= "</div><div style='clear:both'></div>";				
												
			$html .= "</div>";

			// Update views
			if ($user_level == 5) {
				if ($config->include_admin == "on") { 
					$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix."symposium_topics SET topic_views = topic_views + 1 WHERE tid = %d", $post->tid) );
				}
			} else {
				$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix."symposium_topics SET topic_views = topic_views + 1 WHERE tid = %d", $post->tid) );
			}
					
		$html .= "</div>";		
	

		// Replies
		$sql = "SELECT tid, topic_subject, topic_approved, topic_post, topic_date, topic_owner, display_name, ID
			FROM ".$wpdb->prefix."symposium_topics t INNER JOIN ".$wpdb->base_prefix."users u ON t.topic_owner = u.ID 
			WHERE (t.topic_approved = 'on' OR t.topic_owner = %d) AND t.topic_parent = %d ORDER BY tid";
		
		if ($config->oldest_first != "on") { $sql .= " DESC"; }
	
		$child_query = $wpdb->get_results($wpdb->prepare($sql, $current_user->ID, $post->tid));

		$html .= "<div id='child-posts'>";

			if ($child_query) {

				foreach ($child_query as $child) {

					$html .= "<div id='reply".$child->tid."' class='child-reply'>";
						if ( ($child->topic_owner == $current_user->ID) || (current_user_can('level_10')) ) {
							$html .= "<a href='javascript:void(0)' class='floatright link_cursor delete_forum_reply' style='display:none' id='".$child->tid."'>".__("Delete", "wp-symposium")."</a>";
							$html .= "<a href='javascript:void(0)' class='floatright link_cursor edit_forum_reply' style='display:none; margin-right: 10px' id='".$child->tid."'>".__("Edit", "wp-symposium")."</a>";
						}
						$html .= "<div class='avatar'>";
							$html .= get_avatar($child->ID, 64);
						$html .= "</div>";
						$html .= "<div class='started-by'>";
						if ( substr($config->forum_ranks, 0, 2) == 'on' ) {
							$html .= " <span class='forum_rank'>".forum_rank($child->topic_owner)."</span> ";
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

					// Separator
					$html .= "<div class='sep'></div>";						
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
		if (is_user_logged_in()) {
			$html .= '<div id="reply-topic-bottom" name="reply-topic-bottom">';
			if ($wpdb->get_var($wpdb->prepare("SELECT allow_replies FROM ".$wpdb->prefix."symposium_topics WHERE tid = %d", $post->tid)) == "on")
			{
				$html .= '<input type="hidden" id="symposium_reply_tid" value="'.$post->tid.'">';
				$html .= '<input type="hidden" id="symposium_reply_cid" value="'.$cat_id.'">';
				$html .= '<div class="reply-topic-subject label">'.__("Reply to this Topic", "wp-symposium").'</div>';
				$html .= '<textarea class="reply-topic-text elastic" id="symposium_reply_text"></textarea>';
				$html .= '<input type="submit" id="quick-reply-warning" class="symposium-button" style="float: left" value="'.__("Reply", "wp-symposium").'" />';
			}				
			$html .= '</div>';
		}
		
	}
		
	echo symposium_smilies($html);
	
}

=======
<<<<<<< .mine
// Delete Reply *************************************************************
if ($_POST['action'] == 'deleteReply') {

	if (current_user_can('level_10')) {
		$tid = $_POST['topic_id'];
		if (symposium_safe_param($tid)) {
			$wpdb->query("DELETE FROM ".$wpdb->prefix."symposium_topics WHERE tid = ".$tid);
		}
		
		echo $tid;
		
	} else {
		echo "NOT ADMIN";
	}
	
}

// Delete Topic and Replies *************************************************
if ($_POST['action'] == 'deleteTopic') {

	if (current_user_can('level_10')) {
		$tid = $_POST['topic_id'];
		if (symposium_safe_param($tid)) {
			$wpdb->query("DELETE FROM ".$wpdb->prefix."symposium_topics WHERE topic_parent = ".$tid);
			$wpdb->query("DELETE FROM ".$wpdb->prefix."symposium_topics WHERE tid = ".$tid);
			$wpdb->query("DELETE FROM ".$wpdb->prefix."symposium_subs WHERE tid = ".$tid);
		}
		
		echo $tid;
		
	} else {
		echo "NOT ADMIN";
	}
	
}

// New Topic ****************************************************************
if ($_POST['action'] == 'forumNewPost') {

	$new_topic_subject = $_POST['subject'];
	$new_topic_text = $_POST['text'];
	$new_topic_category = $_POST['category'];
	$new_topic_subscribe = $_POST['subscribed'];

	$config = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."symposium_config"));

	// Check for moderation
	if ($config->moderation == "on") {
		$topic_approved = "";
	} else {
		$topic_approved = "on";
	}

	if ($new_topic_subject == '') { $new_topic_subject = __('No subject', 'wp-symposium'); }
	if ($new_topic_text == '') { $new_topic_text = __('No message', 'wp-symposium');  }
	
	if ( is_user_logged_in() ) {
		
		// Get forum URL worked out
		$forum_url = symposium_get_url('forum');
		if ($forum_url[strlen($forum_url)-1] != '/') { $forum_url .= '/'; }
		if (isset($_GET[page_id]) && $_GET[page_id] != '') {
			// No Permalink
			$q = "&";
		} else {
			$q = "?";
		}
		
		// Check for duplicates
		$topic_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$topics." WHERE topic_subject = '".$new_topic_subject."' and topic_post = '".$new_topic_text."' AND topic_owner = ".$current_user->ID));

		if ($topic_count > 1) {
			// Don't double post (also helps reduce spam)
		} else {						
			
			// Store new topic in post
			
			// Don't allow HTML
			$new_topic_text = str_replace("<", "&lt;", $new_topic_text);
			$new_topic_text = str_replace(">", "&gt;", $new_topic_text);

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
		        	$new_topic_subject, 
		        	$new_topic_category,
		        	$new_topic_text, 
		        	date("Y-m-d H:i:s"), 
					date("Y-m-d H:i:s"), 
					$current_user->ID, 
					0,
					0,
					$topic_approved
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
			
			// Set category to the category posted into
			$cat_id = $new_topic_category;
							
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
				// Email people who want to know	
				$query = $wpdb->get_results("
					SELECT user_email
					FROM ".$wpdb->base_prefix."users u RIGHT JOIN ".$wpdb->prefix."symposium_subs s ON s.uid = u.ID 
					WHERE s.tid = 0 AND u.ID != ".$current_user->ID." AND s.cid = ".$cat_id);
					
				if ($query) {					
					foreach ($query as $user) {
						symposium_sendmail($user->user_email, __('New Forum Topic', 'wp-symposium'), $body);						
					}						
				}
			} else {
				// Email admin if post needs approval
				$body = "<span style='font-size:24px font-style:italic;'>".__('Moderation Required', 'wp-symposium')."</span><br /><br />".$body;
				symposium_sendmail(get_bloginfo('admin_email'), __('Moderation Required', 'wp-symposium'), $body);
			}	
			
			echo $url;
			exit;
		}
	}
	
	echo 'NOT LOGGED IN';

}

// Get Topic ****************************************************************
if ($_POST['action'] == 'getTopic') {
	
	$topic_id = $_POST['topic_id'];
	
	$config = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."symposium_config"));
	$plugin = get_site_url().'/wp-content/plugins/wp-symposium/';
	
	// Get forum URL worked out
	$forum_url = symposium_get_url('forum');
	if ($forum_url[strlen($forum_url)-1] != '/') { $forum_url .= '/'; }
	if (isset($_GET[page_id]) && $_GET[page_id] != '') {
		// No Permalink
		$q = "&";
	} else {
		$q = "?";
	}
	
	$post = $wpdb->get_row("
		SELECT tid, topic_subject, topic_approved, topic_category, topic_post, topic_started, display_name, topic_sticky, topic_owner 
		FROM ".$wpdb->prefix."symposium_topics t INNER JOIN ".$wpdb->base_prefix."users u ON t.topic_owner = u.ID 
		WHERE (t.topic_approved = 'on' OR t.topic_owner = ".$current_user->ID.") AND tid = ".$topic_id);
		
	if ($post) {

		// Breadcrumbs
		$cat_id = $post->topic_category;
		
		$html .= '<div id="topic_breadcrumbs" class="breadcrumbs">';

			$this_level = $wpdb->get_row($wpdb->prepare("SELECT cid, title, cat_parent FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d", $cat_id));
			if ($this_level->cat_parent == 0) {
				if ($config->forum_ajax == 'on') {
					$html .= '<a href="javascript:void(0)" class="category_title" title="0">'.__('Forum Home', 'wp-symposium')."</a> > ";
					$html .= '<a href="javascript:void(0);" class="category_title" title="'.$this_level->cid.'">'.trim($this_level->title).'</a>';
				} else {
					$html .= '<a href="'.$forum_url.'" title="0">'.__('Forum Home', 'wp-symposium')."</a> > ";
					$html .= '<a href="'.$forum_url.$q."cid=".$this_level->cid.'" title="'.$this_level->cid.'">'.trim($this_level->title).'</a>';
				}
			} else {

				$parent_level = $wpdb->get_row($wpdb->prepare("SELECT cid, title, cat_parent FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d", $this_level->cat_parent));

				if ($parent_level->cat_parent == 0) {
					if ($config->forum_ajax == 'on') {
						$html .= '<a href="javascript:void(0)" class="category_title" title="0">'.__('Forum Home', 'wp-symposium')."</a> > ";
					} else {
						$html .= '<a href="'.$forum_url.'" title="0">'.__('Forum Home', 'wp-symposium')."</a> > ";
					}
				} else {
					$parent_level_2 = $wpdb->get_row($wpdb->prepare("SELECT cid, title, cat_parent FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d", $parent_level->cat_parent));
					if ($config->forum_ajax == 'on') {
						$html .= '<a href="javascript:void(0)" class="category_title" title="0">'.__('Forum Home', 'wp-symposium')."</a> > " ;
						$html .= '<a href="javascript:void(0)" class="category_title" title="'.$parent_level_2->cid.'">'.$parent_level_2->title."</a> > ";
					} else {
						$html .= '<a href="'.$forum_url.'" title="0">'.__('Forum Home', 'wp-symposium')."</a> > " ;
						$html .= '<a href="'.$forum_url.$q."cid=".$parent_level_2->cid.'"  title="'.$parent_level_2->cid.'">'.$parent_level_2->title."</a> > ";
					}
				}
				if ($config->forum_ajax == 'on') {
					$html .= '<a href="javascript:void(0)" class="category_title" title="'.$parent_level->cid.'">'.$parent_level->title."</a> > " ;
					$html .= '<a href="javascript:void(0)" class="category_title" title="'.$this_level->cid.'">'.$this_level->title."</a>" ;
				} else {
					$html .= '<a href="'.$forum_url.$q."cid=".$parent_level->cid.'" title="'.$parent_level->cid.'">'.$parent_level->title."</a> > " ;
					$html .= '<a href="'.$forum_url.$q."cid=".$this_level->cid.'" title="'.$this_level->cid.'">'.$this_level->title."</a>" ;
				}
			}

		$html .= '</div>';
		
		// Subscribe, Sticky and Allow Replies
		$html .= "<div class='floatleft label'>";
			$html .= "<input type='checkbox' title='".$post->tid."' id='subscribe' name='subscribe'";
			$subscribed_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix."symposium_subs WHERE tid = %d and uid = %d", $post->tid, $current_user->ID));
			if ($subscribed_count > 0) { $html .= ' checked'; } 
			$html .= "> ".__("Receive emails for new replies", "wp-symposium");
			if (current_user_can('level_10')) {
				$html .= "&nbsp;&nbsp;&nbsp;<input type='checkbox' title='".$post->tid."' id='sticky' name='sticky'";
				if ($post->topic_sticky > 0) { $html .= ' checked'; }
				$html .= "> ".__("Sticky", "wp-symposium");
				$html .= "&nbsp;&nbsp;&nbsp;<input type='checkbox' title='".$post->tid."' id='replies' name='replies'";
				$allow_replies = $wpdb->get_var($wpdb->prepare("SELECT allow_replies FROM ".$wpdb->prefix."symposium_topics WHERE tid = %d", $post->tid));
				if ($allow_replies == "on") { $html .= ' checked'; }
				$html .= "> ".__("Replies allowed", "wp-symposium");
			}
		$html .= "</div>";
		
		// Sharing icons
		if ($config->sharing != '') {
			$html .= show_sharing_icons($cat_id, $post->tid, $config->sharing);
		}
		
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
			$html .= '<input type="submit" class="symposium-button edit_topic_submit" value="'.__("Update", "wp-symposium").'" />';
			$html .= '<input type="submit" class="symposium-button edit_topic_cancel" value="'.__("Cancel", "wp-symposium").'" />';
			$html .= '</div>';
		$html .= '</div>';
		
		// Topic starting post
		$html .= "<div id='starting-post'>";
		
			// Show topic header
			$html .= "<div id='top_of_first_post'>";
			
				$html .= "<div class='avatar' style='margin-bottom:0px; margin-top:6px;'>";
					$html .= get_avatar($post->topic_owner, 64);
				$html .= "</div>";
			
				$html .= "<div class='topic-post-header-with-fav'>";
			
					$html .= "<div class='topic-post-header'>";

						if ( ($post->topic_owner == $current_user->ID) || (current_user_can('level_10')) ) {
							$html .= "<a href='javascript:void(0)' title='".$post->tid."' id='edit-this-topic' class='edit_topic edit label' style='cursor:pointer'>".__("Edit", "wp-symposium")."</a>";
						}

					
						$post_text = symposium_bbcode_replace(stripslashes($post->topic_subject));
						$html .= stripslashes($post_text);
			
						if ($post->topic_approved != 'on') { $html .= " <em>[".__("pending approval", "wp-symposium")."]</em>"; }

						// Favourites
						if (is_user_logged_in()) {
							if (strpos(get_symposium_meta($current_user->ID, 'forum_favs'), "[".$post->tid."]") === FALSE) { 
								$html .= "<img title='".$post->tid."' id='fav_link' src='".$plugin."images/star-off.gif' style='height:22px; width:22px; cursor:pointer;' alt='".__("Click to add to favourites", "wp-symposium")."' />";						
							} else {
								$html .= "<img title='".$post->tid."' id='fav_link' src='".$plugin."images/star-on.gif' style='height:22px; width:22px; cursor:pointer;' alt='".__("Click to remove to favourites", "wp-symposium")."' />";						
							}
						}


					$html .= "</div><div style='clear:both'></div>";
										
					$html .= "<div class='started-by' style='margin-top:10px'>";
					$html .= __("Started by", "wp-symposium");
					if ( substr($config->forum_ranks, 0, 2) == 'on' ) {
						$html .= " <span class='forum_rank'>".forum_rank($post->topic_owner)."</span> ";
					}
					$html .= symposium_profile_link($post->topic_owner);
					$html .= ' '.symposium_time_ago($post->topic_started);
					$html .= "</div>";

					$post_text = symposium_make_url(stripslashes($post->topic_post));
					$post_text = symposium_bbcode_replace($post_text);
					$html .= "<div class='topic-post-post'>".str_replace(chr(13), "<br />", $post_text)."</div>";
				
				$html .= "</div><div style='clear:both'></div>";				
												
			$html .= "</div>";

			// Update views
			if ($user_level == 5) {
				if ($config->include_admin == "on") { 
					$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix."symposium_topics SET topic_views = topic_views + 1 WHERE tid = %d", $post->tid) );
				}
			} else {
				$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix."symposium_topics SET topic_views = topic_views + 1 WHERE tid = %d", $post->tid) );
			}
					
		$html .= "</div>";		
	

		// Replies
		$sql = "SELECT tid, topic_subject, topic_approved, topic_post, topic_date, topic_owner, display_name, ID
			FROM ".$wpdb->prefix."symposium_topics t INNER JOIN ".$wpdb->base_prefix."users u ON t.topic_owner = u.ID 
			WHERE (t.topic_approved = 'on' OR t.topic_owner = %d) AND t.topic_parent = %d ORDER BY tid";
		
		if ($config->oldest_first != "on") { $sql .= " DESC"; }
	
		$child_query = $wpdb->get_results($wpdb->prepare($sql, $current_user->ID, $post->tid));

		$html .= "<div id='child-posts'>";

			if ($child_query) {

				foreach ($child_query as $child) {

					$html .= "<div id='reply".$child->tid."' class='child-reply'>";
						if ( ($child->topic_owner == $current_user->ID) || (current_user_can('level_10')) ) {
							$html .= "<a href='javascript:void(0)' class='floatright link_cursor delete_forum_reply' style='display:none' id='".$child->tid."'>".__("Delete", "wp-symposium")."</a>";
							$html .= "<a href='javascript:void(0)' class='floatright link_cursor edit_forum_reply' style='display:none; margin-right: 10px' id='".$child->tid."'>".__("Edit", "wp-symposium")."</a>";
						}
						$html .= "<div class='avatar'>";
							$html .= get_avatar($child->ID, 64);
						$html .= "</div>";
						$html .= "<div class='started-by'>";
						if ( substr($config->forum_ranks, 0, 2) == 'on' ) {
							$html .= " <span class='forum_rank'>".forum_rank($child->topic_owner)."</span> ";
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

					// Separator
					$html .= "<div class='sep'></div>";						
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
		if (is_user_logged_in()) {
			$html .= '<div id="reply-topic-bottom" name="reply-topic-bottom">';
			if ($wpdb->get_var($wpdb->prepare("SELECT allow_replies FROM ".$wpdb->prefix."symposium_topics WHERE tid = %d", $post->tid)) == "on")
			{
				$html .= '<input type="hidden" id="symposium_reply_tid" value="'.$post->tid.'">';
				$html .= '<input type="hidden" id="symposium_reply_cid" value="'.$cat_id.'">';
				$html .= '<div class="reply-topic-subject label">'.__("Reply to this Topic", "wp-symposium").'</div>';
				$html .= '<textarea class="reply-topic-text elastic" id="symposium_reply_text"></textarea>';
				$html .= '<input type="submit" id="quick-reply-warning" class="symposium-button" style="float: left" value="'.__("Reply", "wp-symposium").'" />';
			}				
			$html .= '</div>';
		}
		
	}
		
	echo symposium_smilies($html);
	
}

=======
// Forum stats *************************************************************
if ($_POST['action'] == 'forum_info') {
	
	$html = '';
	
	$max_sql = "SELECT topic_owner, COUNT(*) AS cnt FROM ".$wpdb->prefix."symposium_topics GROUP BY topic_owner ORDER BY cnt DESC LIMIT 0,1";
	$max = $wpdb->get_row($max_sql);

	$min_sql = "SELECT topic_owner, COUNT(*) AS cnt FROM ".$wpdb->prefix."symposium_topics GROUP BY topic_owner ORDER BY cnt LIMIT 0,1";
	$min = $wpdb->get_row($min_sql);

	$my_sql = "SELECT COUNT(*) AS cnt FROM ".$wpdb->prefix."symposium_topics WHERE topic_owner = ".$current_user->ID;
	$my_count = $wpdb->get_var($my_sql);
	
	$range = $max->cnt - $min->cnt;
	$forum_ranks = $wpdb->get_var("SELECT forum_ranks FROM ".$wpdb->prefix."symposium_config");

	$html .= 'Most posts: '.$max->topic_owner.' ('.$max->cnt.')<br />';
	$html .= 'Least posts: '.$min->topic_owner.' ('.$min->cnt.')<br />';
	$html .= 'My post count: '.$my_count.'<br />';
	
	$ranks = explode(';', $forum_ranks);
	$num_ranks = 0;
	for ( $rank = 2; $rank <= 11; $rank ++) {
		if ($ranks[$rank] != '') { $num_ranks++; }
	}
	$html .= "Number of ranks: ".$num_ranks."<br />";
	
	$step = ($range / $num_ranks);
	$html .= "Range: ".$range."<br />";
	$html .= "Step value: ".$step."<br />";

	if ($my_count == $max->cnt) { 
		$html .= "My rank: TOP";
	} else {
		for ( $l = 1; $l <= $num_ranks; $l=$l+1) {
			
			$html .= $l." (".$num_ranks."):";
			
			$bottom = $max->cnt - ($l * $step);
			$top = $bottom + $step;
			
			$html .= $bottom." - ".$top."<br />";
			
			if ($my_count >= $bottom && $my_count <= $top) {
				$html .= "My rank: ".$l;
			}
		}
	}
	
	echo '<p class="clear" style="margin-top:15px">'.$html.'</p>';

}

// Delete Reply *************************************************************
if ($_POST['action'] == 'deleteReply') {

	if (current_user_can('level_10')) {
		$tid = $_POST['topic_id'];
		if (symposium_safe_param($tid)) {
			$wpdb->query("DELETE FROM ".$wpdb->prefix."symposium_topics WHERE tid = ".$tid);
		}
		
		echo $tid;
		
	} else {
		echo "NOT ADMIN";
	}
	
}

// Delete Topic and Replies *************************************************
if ($_POST['action'] == 'deleteTopic') {

	if (current_user_can('level_10')) {
		$tid = $_POST['topic_id'];
		if (symposium_safe_param($tid)) {
			$wpdb->query("DELETE FROM ".$wpdb->prefix."symposium_topics WHERE topic_parent = ".$tid);
			$wpdb->query("DELETE FROM ".$wpdb->prefix."symposium_topics WHERE tid = ".$tid);
			$wpdb->query("DELETE FROM ".$wpdb->prefix."symposium_subs WHERE tid = ".$tid);
		}
		
		echo $tid;
		
	} else {
		echo "NOT ADMIN";
	}
	
}

// New Topic ****************************************************************
if ($_POST['action'] == 'forumNewPost') {

	$new_topic_subject = $_POST['subject'];
	$new_topic_text = $_POST['text'];
	$new_topic_category = $_POST['category'];
	$new_topic_subscribe = $_POST['subscribed'];

	$config = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."symposium_config"));

	// Check for moderation
	if ($config->moderation == "on") {
		$topic_approved = "";
	} else {
		$topic_approved = "on";
	}

	if ($new_topic_subject == '') { $new_topic_subject = __('No subject', 'wp-symposium'); }
	if ($new_topic_text == '') { $new_topic_text = __('No message', 'wp-symposium');  }
	
	if ( is_user_logged_in() ) {
		
		// Get forum URL worked out
		$forum_url = symposium_get_url('forum');
		if ($forum_url[strlen($forum_url)-1] != '/') { $forum_url .= '/'; }
		if (isset($_GET[page_id]) && $_GET[page_id] != '') {
			// No Permalink
			$q = "&";
		} else {
			$q = "?";
		}
		
		// Check for duplicates
		$topic_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$topics." WHERE topic_subject = '".$new_topic_subject."' and topic_post = '".$new_topic_text."' AND topic_owner = ".$current_user->ID));

		if ($topic_count > 1) {
			// Don't double post (also helps reduce spam)
		} else {						
			
			// Store new topic in post
			
			// Don't allow HTML
			$new_topic_text = str_replace("<", "&lt;", $new_topic_text);
			$new_topic_text = str_replace(">", "&gt;", $new_topic_text);

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
		        	$new_topic_subject, 
		        	$new_topic_category,
		        	$new_topic_text, 
		        	date("Y-m-d H:i:s"), 
					date("Y-m-d H:i:s"), 
					$current_user->ID, 
					0,
					0,
					$topic_approved
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
			
			// Set category to the category posted into
			$cat_id = $new_topic_category;
							
			// Get post owner name and prepare email body
			$owner_name = $wpdb->get_var($wpdb->prepare("SELECT display_name FROM ".$wpdb->base_prefix."users WHERE ID = ".$current_user->ID));
			$body = "<p>".$owner_name." ".__('has started a new topic', 'wp-symposium');
			$category = $wpdb->get_var($wpdb->prepare("SELECT title FROM ".$wpdb->prefix."symposium_cats WHERE cid = ".$cat_id));
			$body .= " ".__('in', 'wp-symposium')." ".$category;
			$body .= "...</p>";
								
			$body .= "<span style='font-size:24px'>".$new_topic_subject."</span><br /><br />";
			$body .= "<p>".$new_topic_text."</p>";
			$url = $forum_url."?cid=".$cat_id."&show=".$new_tid;
			$body .= "<p><a href='".$url."'>".$url."</a></p>";
			$body = str_replace(chr(13), "<br />", $body);
			$body = str_replace("\\r\\n", "<br />", $body);
			$body = str_replace("\\", "", $body);
			
			if ($topic_approved == "on") {
				// Email people who want to know	
				$query = $wpdb->get_results("
					SELECT user_email
					FROM ".$wpdb->base_prefix."users u RIGHT JOIN ".$wpdb->prefix."symposium_subs s ON s.uid = u.ID 
					WHERE s.tid = 0 AND u.ID != ".$current_user->ID." AND s.cid = ".$cat_id);
					
				if ($query) {					
					foreach ($query as $user) {
						symposium_sendmail($user->user_email, __('New Forum Topic', 'wp-symposium'), $body);						
					}						
				}
			} else {
				// Email admin if post needs approval
				$body = "<span style='font-size:24px font-style:italic;'>".__('Moderation Required', 'wp-symposium')."</span><br /><br />".$body;
				symposium_sendmail(get_bloginfo('admin_email'), __('Moderation Required', 'wp-symposium'), $body);
			}	
			
			echo $url;
			exit;
		}
	}
	
	echo 'NOT LOGGED IN';

}

// Get Topic ****************************************************************
if ($_POST['action'] == 'getTopic') {
	
	$topic_id = $_POST['topic_id'];
	
	$config = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."symposium_config"));
	$plugin = get_site_url().'/wp-content/plugins/wp-symposium/';

	$post = $wpdb->get_row("
		SELECT tid, topic_subject, topic_approved, topic_category, topic_post, topic_started, display_name, topic_sticky, topic_owner 
		FROM ".$wpdb->prefix."symposium_topics t INNER JOIN ".$wpdb->base_prefix."users u ON t.topic_owner = u.ID 
		WHERE (t.topic_approved = 'on' OR t.topic_owner = ".$current_user->ID.") AND tid = ".$topic_id);
		
	if ($post) {

		// Breadcrumbs
		$cat_id = $post->topic_category;

		$html .= '<div id="topic_breadcrumbs" class="breadcrumbs">';

			$this_level = $wpdb->get_row($wpdb->prepare("SELECT cid, title, cat_parent FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d", $cat_id));
			if ($this_level->cat_parent == 0) {
				$html .= '<a href="javascript:void(0)" class="category_title" title="0">'.__('Forum Home', 'wp-symposium')."</a> > ";
				$html .= '<a href="javascript:void(0);" class="category_title" title="'.$this_level->cid.'">'.trim($this_level->title).'</a>';
			} else {

				$parent_level = $wpdb->get_row($wpdb->prepare("SELECT cid, title, cat_parent FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d", $this_level->cat_parent));

				if ($parent_level->cat_parent == 0) {
					$html .= '<a href="javascript:void(0)" class="category_title" title="0">'.__('Forum Home', 'wp-symposium')."</a> > ";
				} else {
					$parent_level_2 = $wpdb->get_row($wpdb->prepare("SELECT cid, title, cat_parent FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d", $parent_level->cat_parent));
					$html .= '<a href="javascript:void(0)" class="category_title" title="0">'.__('Forum Home', 'wp-symposium')."</a> > " ;
					$html .= '<a href="javascript:void(0)" class="category_title" title="'.$parent_level_2->cid.'">'.$parent_level_2->title."</a> > ";
				}
				$html .= '<a href="javascript:void(0)" class="category_title" title="'.$parent_level->cid.'">'.$parent_level->title."</a> > " ;
				$html .= '<a href="javascript:void(0)" class="category_title" title="'.$this_level->cid.'">'.$this_level->title."</a>" ;
			}

		$html .= '</div>';
		
		// Subscribe, Sticky and Allow Replies
		$html .= "<div class='floatleft label'>";
			$html .= "<input type='checkbox' title='".$post->tid."' id='subscribe' name='subscribe'";
			$subscribed_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix."symposium_subs WHERE tid = %d and uid = %d", $post->tid, $current_user->ID));
			if ($subscribed_count > 0) { $html .= ' checked'; } 
			$html .= "> ".__("Receive emails for new replies", "wp-symposium");
			if (current_user_can('level_10')) {
				$html .= "&nbsp;&nbsp;&nbsp;<input type='checkbox' title='".$post->tid."' id='sticky' name='sticky'";
				if ($post->topic_sticky > 0) { $html .= ' checked'; }
				$html .= "> ".__("Sticky", "wp-symposium");
				$html .= "&nbsp;&nbsp;&nbsp;<input type='checkbox' title='".$post->tid."' id='replies' name='replies'";
				$allow_replies = $wpdb->get_var($wpdb->prepare("SELECT allow_replies FROM ".$wpdb->prefix."symposium_topics WHERE tid = %d", $post->tid));
				if ($allow_replies == "on") { $html .= ' checked'; }
				$html .= "> ".__("Replies allowed", "wp-symposium");
			}
		$html .= "</div>";
		
		// Sharing icons
		if ($config->sharing != '') {
			$html .= show_sharing_icons($cat_id, $post->tid, $config->sharing);
		}
		
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
			$html .= '<input type="submit" class="symposium-button edit_topic_submit" value="'.__("Update", "wp-symposium").'" />';
			$html .= '<input type="submit" class="symposium-button edit_topic_cancel" value="'.__("Cancel", "wp-symposium").'" />';
			$html .= '</div>';
		$html .= '</div>';
		
		// Topic starting post
		$html .= "<div id='starting-post'>";
		
			// Show topic header
			$html .= "<div id='top_of_first_post'>";
			
				$html .= "<div class='avatar' style='margin-bottom:0px; margin-top:6px;'>";
					$html .= get_avatar($post->topic_owner, 64);
				$html .= "</div>";
			
				$html .= "<div class='topic-post-header-with-fav'>";
			
					$html .= "<div class='topic-post-header'>";

						if ( ($post->topic_owner == $current_user->ID) || (current_user_can('level_10')) ) {
							$html .= "<a href='javascript:void(0)' title='".$post->tid."' id='edit-this-topic' class='edit_topic edit label' style='cursor:pointer'>".__("Edit", "wp-symposium")."</a>";
						}

					
						$post_text = symposium_bbcode_replace(stripslashes($post->topic_subject));
						$html .= stripslashes($post_text);
			
						if ($post->topic_approved != 'on') { $html .= " <em>[".__("pending approval", "wp-symposium")."]</em>"; }

						// Favourites
						if (is_user_logged_in()) {
							if (strpos(get_symposium_meta($current_user->ID, 'forum_favs'), "[".$post->tid."]") === FALSE) { 
								$html .= "<img title='".$post->tid."' id='fav_link' src='".$plugin."images/star-off.gif' style='height:22px; width:22px; cursor:pointer;' alt='".__("Click to add to favourites", "wp-symposium")."' />";						
							} else {
								$html .= "<img title='".$post->tid."' id='fav_link' src='".$plugin."images/star-on.gif' style='height:22px; width:22px; cursor:pointer;' alt='".__("Click to remove to favourites", "wp-symposium")."' />";						
							}
						}


					$html .= "</div><div style='clear:both'></div>";
										
					$html .= "<div class='started-by' style='margin-top:10px'>";
					$html .= __("Started by", "wp-symposium")." ".symposium_profile_link($post->topic_owner);
					if ( substr($config->forum_ranks, 0, 2) == 'on' ) {
						$html .= " <span class='forum_rank'>(".forum_rank($post->topic_owner).")</span> ".symposium_time_ago($post->topic_started);
					}
					$html .= "</div>";

					$post_text = symposium_make_url(stripslashes($post->topic_post));
					$post_text = symposium_bbcode_replace($post_text);
					$html .= "<div class='topic-post-post'>".str_replace(chr(13), "<br />", $post_text)."</div>";
				
				$html .= "</div><div style='clear:both'></div>";				
												
			$html .= "</div>";

			// Update views
			if ($user_level == 5) {
				if ($config->include_admin == "on") { 
					$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix."symposium_topics SET topic_views = topic_views + 1 WHERE tid = %d", $post->tid) );
				}
			} else {
				$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix."symposium_topics SET topic_views = topic_views + 1 WHERE tid = %d", $post->tid) );
			}
					
		$html .= "</div>";		
	

		// Replies
		$sql = "SELECT tid, topic_subject, topic_approved, topic_post, topic_date, topic_owner, display_name, ID
			FROM ".$wpdb->prefix."symposium_topics t INNER JOIN ".$wpdb->base_prefix."users u ON t.topic_owner = u.ID 
			WHERE (t.topic_approved = 'on' OR t.topic_owner = %d) AND t.topic_parent = %d ORDER BY tid";
		
		if ($config->oldest_first != "on") { $sql .= " DESC"; }
	
		$child_query = $wpdb->get_results($wpdb->prepare($sql, $current_user->ID, $post->tid));

		$html .= "<div id='child-posts'>";

			if ($child_query) {

				foreach ($child_query as $child) {

					$html .= "<div id='reply".$child->tid."' class='child-reply'>";
						if ( ($child->topic_owner == $current_user->ID) || (current_user_can('level_10')) ) {
							$html .= "<a href='javascript:void(0)' class='floatright link_cursor delete_forum_reply' style='display:none' id='".$child->tid."'>".__("Delete", "wp-symposium")."</a>";
							$html .= "<a href='javascript:void(0)' class='floatright link_cursor edit_forum_reply' style='display:none; margin-right: 10px' id='".$child->tid."'>".__("Edit", "wp-symposium")."</a>";
						}
						$html .= "<div class='avatar'>";
							$html .= get_avatar($child->ID, 64);
						$html .= "</div>";
						$html .= "<div class='started-by'>".symposium_profile_link($child->topic_owner);
						if ( substr($config->forum_ranks, 0, 2) == 'on' ) {
							$html .= " <span class='forum_rank'>(".forum_rank($child->topic_owner).")</span>";
						}
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

					// Separator
					$html .= "<div class='sep'></div>";						
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
		if (is_user_logged_in()) {
			$html .= '<div id="reply-topic-bottom" name="reply-topic-bottom">';
			if ($wpdb->get_var($wpdb->prepare("SELECT allow_replies FROM ".$wpdb->prefix."symposium_topics WHERE tid = %d", $post->tid)) == "on")
			{
				$html .= '<input type="hidden" id="symposium_reply_tid" value="'.$post->tid.'">';
				$html .= '<input type="hidden" id="symposium_reply_cid" value="'.$cat_id.'">';
				$html .= '<div class="reply-topic-subject label">'.__("Reply to this Topic", "wp-symposium").'</div>';
				$html .= '<textarea class="reply-topic-text elastic" id="symposium_reply_text"></textarea>';
				$html .= '<input type="submit" id="quick-reply-warning" class="symposium-button" style="float: left" value="'.__("Reply", "wp-symposium").'" />';
			}				
			$html .= '</div>';
		}
		
	}
		
	echo symposium_smilies($html);
	
}

>>>>>>> .r357134
>>>>>>> .r357139
>>>>>>> .r358221
// Get Forum ****************************************************************
if ($_POST['action'] == 'getForum') {
	
	$cat_id = $_POST['cat_id'];

	$config = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."symposium_config"));

<<<<<<< .mine
	$plugin = get_site_url().'/wp-content/plugins/wp-symposium/';

	// Get forum URL worked out
	$forum_url = symposium_get_url('forum');
	if ($forum_url[strlen($forum_url)-1] != '/') { $forum_url .= '/'; }
	if (isset($_GET['page_id']) && $_GET['page_id'] != '') {
		// No Permalink
		$q = "&";
	} else {
		$q = "?";
	}
	
=======
<<<<<<< .mine
	$plugin = get_site_url().'/wp-content/plugins/wp-symposium/';

	// Get forum URL worked out
	$forum_url = symposium_get_url('forum');
	if ($forum_url[strlen($forum_url)-1] != '/') { $forum_url .= '/'; }
	if (isset($_GET[page_id]) && $_GET[page_id] != '') {
		// No Permalink
		$q = "&";
	} else {
		$q = "?";
	}
	
=======
<<<<<<< .mine
	$plugin = get_site_url().'/wp-content/plugins/wp-symposium/';

	// Get forum URL worked out
	$forum_url = symposium_get_url('forum');
	if ($forum_url[strlen($forum_url)-1] != '/') { $forum_url .= '/'; }
	if (isset($_GET[page_id]) && $_GET[page_id] != '') {
		// No Permalink
		$q = "&";
	} else {
		$q = "?";
	}
	
=======
	$plugin = get_site_url().'/wp-content/plugins/wp-symposium/';

>>>>>>> .r357134
>>>>>>> .r357139
>>>>>>> .r358221
	// Post preview
	$snippet_length = $config->preview1;
	if ($snippet_length == '') { $snippet_length = '45'; }
	$snippet_length_long = $config->preview2;
	if ($snippet_length_long == '') { $snippet_length_long = '45'; }

	// Breadcrumbs
		
	$html = '';
	
<<<<<<< .mine
	$html .= '<div id="forum_breadcrumbs" class="breadcrumbs">';

		if ($cat_id > 0) {
	
			$this_level = $wpdb->get_row($wpdb->prepare("SELECT cid, title, cat_parent FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d", $cat_id));
			if ($this_level->cat_parent == 0) {
				if ($config->forum_ajax == 'on') {
					$html .= '<a href="javascript:void(0)" class="category_title" title="0">'.__('Forum Home', 'wp-symposium')."</a> > ";
					$html .= '<a href="javascript:void(0);" class="category_title" title="'.$this_level->cid.'">'.trim($this_level->title).'</a>';
				} else {
					$html .= '<a href="'.$forum_url.'" title="0">'.__('Forum Home', 'wp-symposium')."</a> > ";
					$html .= '<a href="'.$forum_url.$q."cid=".$this_level->cid.'" title="'.$this_level->cid.'">'.trim($this_level->title).'</a>';
				}
			} else {

				$parent_level = $wpdb->get_row($wpdb->prepare("SELECT cid, title, cat_parent FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d", $this_level->cat_parent));

				if ($parent_level->cat_parent == 0) {
					if ($config->forum_ajax == 'on') {
						$html .= '<a href="javascript:void(0)" class="category_title" title="0">'.__('Forum Home', 'wp-symposium')."</a> > ";
					} else {
						$html .= '<a href="'.$forum_url.'" title="0">'.__('Forum Home', 'wp-symposium')."</a> > ";
					}
				} else {
					$parent_level_2 = $wpdb->get_row($wpdb->prepare("SELECT cid, title, cat_parent FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d", $parent_level->cat_parent));
					if ($config->forum_ajax == 'on') {
						$html .= '<a href="javascript:void(0)" class="category_title" title="0">'.__('Forum Home', 'wp-symposium')."</a> > " ;
						$html .= '<a href="javascript:void(0)" class="category_title" title="'.$parent_level_2->cid.'">'.$parent_level_2->title."</a> > ";
					} else {
						$html .= '<a href="'.$forum_url.'" title="0">'.__('Forum Home', 'wp-symposium')."</a> > " ;
						$html .= '<a href="'.$forum_url.$q."cid=".$parent_level_2->cid.'"  title="'.$parent_level_2->cid.'">'.$parent_level_2->title."</a> > ";
					}
				}
				if ($config->forum_ajax == 'on') {
					$html .= '<a href="javascript:void(0)" class="category_title" title="'.$parent_level->cid.'">'.$parent_level->title."</a> > " ;
					$html .= '<a href="javascript:void(0)" class="category_title" title="'.$this_level->cid.'">'.$this_level->title."</a>" ;
				} else {
					$html .= '<a href="'.$forum_url.$q."cid=".$parent_level->cid.'" title="'.$parent_level->cid.'">'.$parent_level->title."</a> > " ;
					$html .= '<a href="'.$forum_url.$q."cid=".$this_level->cid.'" title="'.$this_level->cid.'">'.$this_level->title."</a>" ;
				}
			}
			
		}

		// New Topic Form	
		if (is_user_logged_in()) {

			$html .= '<input type="submit" class="symposium-button floatright" id="new-topic-button" value="'.__("New Topic", "wp-symposium").'" />';

			$html .= '<div name="new-topic" id="new-topic" style="display:none;">';
				$html .= '<input type="hidden" id="cid" value="'.$cat_id.'">';
				$html .= '<div id="new-topic-subject-label" class="new-topic-subject label">'.__("Topic Subject", "wp-symposium").'</div>';
				$html .= '<input class="new-topic-subject-input" type="text" id="new_topic_subject" value="">';
				$html .= '<div class="new-topic-subject label">'.__("First Post in Topic", "wp-symposium").'</div>';
				$html .= '<textarea class="new-topic-subject-text elastic" id="new_topic_text">';
				$html .= '</textarea>';
				$defaultcat = $wpdb->get_var($wpdb->prepare("SELECT cid FROM ".$wpdb->prefix."symposium_cats WHERE defaultcat = 'on'"));

				$html .= '<div class="new-topic-category label">'.__("Select a Category", "wp-symposium").': ';
				if (current_user_can('level_10')) {
					$categories = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix.'symposium_cats ORDER BY title');			
				} else {
					$categories = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix.'symposium_cats WHERE allow_new = "on" ORDER BY title');			
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

				$html .= '<div class="emailreplies label"><input type="checkbox" id="new_topic_subscribe"> '.__("Email me when I get any replies", "wp-symposium").'</div>';
				$html .= '<input id="new_post" type="submit" class="symposium-button" style="float: left" value="'.__("Post", "wp-symposium").'" />';
				$html .= '<input id="cancel_post" type="submit" class="symposium-button clear" onClick="javascript:void(0)" value="'.__("Cancel", "wp-symposium").'" />';

			$html .= '</div>';

		} else {

			$html .= __("Until you login, you can only view the forum.", "wp-symposium");
			$html .= " <a href=".wp_login_url( get_permalink() )." class='simplemodal-login' title='".__("Login", "wp-symposium")."'>".__("Login", "wp-symposium").".</a>";
			$html .= "<br />";

		}
						
	$html .= '</div>';
	
=======
<<<<<<< .mine
	$html .= '<div id="forum_breadcrumbs" class="breadcrumbs">';

		if ($cat_id > 0) {
	
			$this_level = $wpdb->get_row($wpdb->prepare("SELECT cid, title, cat_parent FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d", $cat_id));
			if ($this_level->cat_parent == 0) {
				if ($config->forum_ajax == 'on') {
					$html .= '<a href="javascript:void(0)" class="category_title" title="0">'.__('Forum Home', 'wp-symposium')."</a> > ";
					$html .= '<a href="javascript:void(0);" class="category_title" title="'.$this_level->cid.'">'.trim($this_level->title).'</a>';
				} else {
					$html .= '<a href="'.$forum_url.'" title="0">'.__('Forum Home', 'wp-symposium')."</a> > ";
					$html .= '<a href="'.$forum_url.$q."cid=".$this_level->cid.'" title="'.$this_level->cid.'">'.trim($this_level->title).'</a>';
				}
			} else {

				$parent_level = $wpdb->get_row($wpdb->prepare("SELECT cid, title, cat_parent FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d", $this_level->cat_parent));

				if ($parent_level->cat_parent == 0) {
					if ($config->forum_ajax == 'on') {
						$html .= '<a href="javascript:void(0)" class="category_title" title="0">'.__('Forum Home', 'wp-symposium')."</a> > ";
					} else {
						$html .= '<a href="'.$forum_url.'" title="0">'.__('Forum Home', 'wp-symposium')."</a> > ";
					}
				} else {
					$parent_level_2 = $wpdb->get_row($wpdb->prepare("SELECT cid, title, cat_parent FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d", $parent_level->cat_parent));
					if ($config->forum_ajax == 'on') {
						$html .= '<a href="javascript:void(0)" class="category_title" title="0">'.__('Forum Home', 'wp-symposium')."</a> > " ;
						$html .= '<a href="javascript:void(0)" class="category_title" title="'.$parent_level_2->cid.'">'.$parent_level_2->title."</a> > ";
					} else {
						$html .= '<a href="'.$forum_url.'" title="0">'.__('Forum Home', 'wp-symposium')."</a> > " ;
						$html .= '<a href="'.$forum_url.$q."cid=".$parent_level_2->cid.'"  title="'.$parent_level_2->cid.'">'.$parent_level_2->title."</a> > ";
					}
				}
				if ($config->forum_ajax == 'on') {
					$html .= '<a href="javascript:void(0)" class="category_title" title="'.$parent_level->cid.'">'.$parent_level->title."</a> > " ;
					$html .= '<a href="javascript:void(0)" class="category_title" title="'.$this_level->cid.'">'.$this_level->title."</a>" ;
				} else {
					$html .= '<a href="'.$forum_url.$q."cid=".$parent_level->cid.'" title="'.$parent_level->cid.'">'.$parent_level->title."</a> > " ;
					$html .= '<a href="'.$forum_url.$q."cid=".$this_level->cid.'" title="'.$this_level->cid.'">'.$this_level->title."</a>" ;
				}
			}
>>>>>>> .r358967
>>>>>>> .r358238
			
		}

<<<<<<< .mine
=======
<<<<<<< .mine
	$sql = $wpdb->prepare("SELECT * FROM ".$wpdb->prefix."symposium_cats WHERE cat_parent = %d ORDER BY listorder", $cat_id);
	$categories = $wpdb->get_results($sql);
=======
		// New Topic Form	
		if (is_user_logged_in()) {

			// Sub Menu for Logged in User
			$html .= '<input type="submit" class="symposium-button floatright" id="new-topic-button" value="'.__("New Topic", "wp-symposium").'" />';

			$html .= '<div name="new-topic" id="new-topic" style="display:none;">';
				$html .= '<input type="hidden" id="cid" value="'.$cat_id.'">';
				$html .= '<div id="new-topic-subject-label" class="new-topic-subject label">'.__("Topic Subject", "wp-symposium").'</div>';
				$html .= '<input class="new-topic-subject-input" type="text" id="new_topic_subject" value="">';
				$html .= '<div class="new-topic-subject label">'.__("First Post in Topic", "wp-symposium").'</div>';
				$html .= '<textarea class="new-topic-subject-text elastic" id="new_topic_text">';
				$html .= '</textarea>';
				$defaultcat = $wpdb->get_var($wpdb->prepare("SELECT cid FROM ".$cats." WHERE defaultcat = 'on'"));

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

				$html .= '<div class="emailreplies label"><input type="checkbox" id="new_topic_subscribe"';
				if ($new_topic_subscribe != '') { $html .= 'checked'; } 
				$html .= '> '.__("Email me when I get any replies", "wp-symposium").'</div>';
				$html .= '<input id="new_post" type="submit" class="symposium-button" style="float: left" value="'.__("Post", "wp-symposium").'" />';
				$html .= '<input id="cancel_post" type="submit" class="symposium-button clear" onClick="javascript:void(0)" value="'.__("Cancel", "wp-symposium").'" />';

			$html .= '</div>';

		} else {

			$html .= __("Until you login, you can only view the forum.", "wp-symposium");
			$html .= " <a href=".wp_login_url( get_permalink() )." class='simplemodal-login' title='".__("Login", "wp-symposium")."'>".__("Login", "wp-symposium").".</a>";
			$html .= "<br />";

		}
						
	$html .= '</div>';
	
=======
<<<<<<< .mine
	$html .= '<div id="forum_breadcrumbs" class="breadcrumbs">';

		if ($cat_id > 0) {
	
			$this_level = $wpdb->get_row($wpdb->prepare("SELECT cid, title, cat_parent FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d", $cat_id));
			if ($this_level->cat_parent == 0) {
				if ($config->forum_ajax == 'on') {
					$html .= '<a href="javascript:void(0)" class="category_title" title="0">'.__('Forum Home', 'wp-symposium')."</a> > ";
					$html .= '<a href="javascript:void(0);" class="category_title" title="'.$this_level->cid.'">'.trim($this_level->title).'</a>';
				} else {
					$html .= '<a href="'.$forum_url.'" title="0">'.__('Forum Home', 'wp-symposium')."</a> > ";
					$html .= '<a href="'.$forum_url.$q."cid=".$this_level->cid.'" title="'.$this_level->cid.'">'.trim($this_level->title).'</a>';
				}
			} else {

				$parent_level = $wpdb->get_row($wpdb->prepare("SELECT cid, title, cat_parent FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d", $this_level->cat_parent));

				if ($parent_level->cat_parent == 0) {
					if ($config->forum_ajax == 'on') {
						$html .= '<a href="javascript:void(0)" class="category_title" title="0">'.__('Forum Home', 'wp-symposium')."</a> > ";
					} else {
						$html .= '<a href="'.$forum_url.'" title="0">'.__('Forum Home', 'wp-symposium')."</a> > ";
					}
				} else {
					$parent_level_2 = $wpdb->get_row($wpdb->prepare("SELECT cid, title, cat_parent FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d", $parent_level->cat_parent));
					if ($config->forum_ajax == 'on') {
						$html .= '<a href="javascript:void(0)" class="category_title" title="0">'.__('Forum Home', 'wp-symposium')."</a> > " ;
						$html .= '<a href="javascript:void(0)" class="category_title" title="'.$parent_level_2->cid.'">'.$parent_level_2->title."</a> > ";
					} else {
						$html .= '<a href="'.$forum_url.'" title="0">'.__('Forum Home', 'wp-symposium')."</a> > " ;
						$html .= '<a href="'.$forum_url.$q."cid=".$parent_level_2->cid.'"  title="'.$parent_level_2->cid.'">'.$parent_level_2->title."</a> > ";
					}
				}
				if ($config->forum_ajax == 'on') {
					$html .= '<a href="javascript:void(0)" class="category_title" title="'.$parent_level->cid.'">'.$parent_level->title."</a> > " ;
					$html .= '<a href="javascript:void(0)" class="category_title" title="'.$this_level->cid.'">'.$this_level->title."</a>" ;
				} else {
					$html .= '<a href="'.$forum_url.$q."cid=".$parent_level->cid.'" title="'.$parent_level->cid.'">'.$parent_level->title."</a> > " ;
					$html .= '<a href="'.$forum_url.$q."cid=".$this_level->cid.'" title="'.$this_level->cid.'">'.$this_level->title."</a>" ;
				}
			}
			
		}

		// New Topic Form	
		if (is_user_logged_in()) {

			// Sub Menu for Logged in User
			$html .= '<input type="submit" class="symposium-button floatright" id="new-topic-button" value="'.__("New Topic", "wp-symposium").'" />';

			$html .= '<div name="new-topic" id="new-topic" style="display:none;">';
				$html .= '<input type="hidden" id="cid" value="'.$cat_id.'">';
				$html .= '<div id="new-topic-subject-label" class="new-topic-subject label">'.__("Topic Subject", "wp-symposium").'</div>';
				$html .= '<input class="new-topic-subject-input" type="text" id="new_topic_subject" value="">';
				$html .= '<div class="new-topic-subject label">'.__("First Post in Topic", "wp-symposium").'</div>';
				$html .= '<textarea class="new-topic-subject-text elastic" id="new_topic_text">';
				$html .= '</textarea>';
				$defaultcat = $wpdb->get_var($wpdb->prepare("SELECT cid FROM ".$cats." WHERE defaultcat = 'on'"));

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

				$html .= '<div class="emailreplies label"><input type="checkbox" id="new_topic_subscribe"';
				if ($new_topic_subscribe != '') { $html .= 'checked'; } 
				$html .= '> '.__("Email me when I get any replies", "wp-symposium").'</div>';
				$html .= '<input id="new_post" type="submit" class="symposium-button" style="float: left" value="'.__("Post", "wp-symposium").'" />';
				$html .= '<input id="cancel_post" type="submit" class="symposium-button clear" onClick="javascript:void(0)" value="'.__("Cancel", "wp-symposium").'" />';

			$html .= '</div>';

		} else {

			$html .= __("Until you login, you can only view the forum.", "wp-symposium");
			$html .= " <a href=".wp_login_url( get_permalink() )." class='simplemodal-login' title='".__("Login", "wp-symposium")."'>".__("Login", "wp-symposium").".</a>";
			$html .= "<br />";

		}
						
	$html .= '</div>';
	
=======
	$html .= '<div id="forum_breadcrumbs" class="breadcrumbs">';

		if ($cat_id > 0) {
	
			$this_level = $wpdb->get_row($wpdb->prepare("SELECT cid, title, cat_parent FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d", $cat_id));
			if ($this_level->cat_parent == 0) {
				$html .= '<a href="javascript:void(0)" class="category_title" title="0">'.__('Forum Home', 'wp-symposium')."</a> > ";
				$html .= '<a href="javascript:void(0)" class="category_title" title="'.$this_level->cid.'">'.$this_level->title."</a>" ;
			} else {
		
				$parent_level = $wpdb->get_row($wpdb->prepare("SELECT cid, title, cat_parent FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d", $this_level->cat_parent));

				if ($parent_level->cat_parent == 0) {
					$html .= '<a href="javascript:void(0)" class="category_title" title="0">'.__('Forum Home', 'wp-symposium')."</a> > ";
				} else {
					$parent_level_2 = $wpdb->get_row($wpdb->prepare("SELECT cid, title, cat_parent FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d", $parent_level->cat_parent));
					$html .= '<a href="javascript:void(0)" class="category_title" title="0">'.__('Forum Home', 'wp-symposium')."</a> > " ;
					$html .= '<a href="javascript:void(0)" class="category_title" title="'.$parent_level_2->cid.'">'.$parent_level_2->title."</a> > ";
				}
				$html .= '<a href="javascript:void(0)" class="category_title" title="'.$parent_level->cid.'">'.$parent_level->title."</a> > " ;
				$html .= '<a href="javascript:void(0)" class="category_title" title="'.$this_level->cid.'">'.$this_level->title."</a>" ;
			}
			
		}

		// New Topic Form	
		if (is_user_logged_in()) {

			// Sub Menu for Logged in User
			$html .= '<input type="submit" class="symposium-button floatright" id="new-topic-button" value="'.__("New Topic", "wp-symposium").'" />';

			$html .= '<div name="new-topic" id="new-topic" style="display:none;">';
				$html .= '<input type="hidden" id="cid" value="'.$cat_id.'">';
				$html .= '<div id="new-topic-subject-label" class="new-topic-subject label">'.__("Topic Subject", "wp-symposium").'</div>';
				$html .= '<input class="new-topic-subject-input" type="text" id="new_topic_subject" value="">';
				$html .= '<div class="new-topic-subject label">'.__("First Post in Topic", "wp-symposium").'</div>';
				$html .= '<textarea class="new-topic-subject-text elastic" id="new_topic_text">';
				$html .= '</textarea>';
				$defaultcat = $wpdb->get_var($wpdb->prepare("SELECT cid FROM ".$cats." WHERE defaultcat = 'on'"));

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

				$html .= '<div class="emailreplies label"><input type="checkbox" id="new_topic_subscribe"';
				if ($new_topic_subscribe != '') { $html .= 'checked'; } 
				$html .= '> '.__("Email me when I get any replies", "wp-symposium").'</div>';
				$html .= '<input id="new_post" type="submit" class="symposium-button" style="float: left" value="'.__("Post", "wp-symposium").'" />';
				$html .= '<input id="cancel_post" type="submit" class="symposium-button clear" onClick="javascript:void(0)" value="'.__("Cancel", "wp-symposium").'" />';

			$html .= '</div>';

		} else {

			$html .= __("Until you login, you can only view the forum.", "wp-symposium");
			$html .= " <a href=".wp_login_url( get_permalink() )." class='simplemodal-login' title='".__("Login", "wp-symposium")."'>".__("Login", "wp-symposium").".</a>";
			$html .= "<br />";

		}
						
	$html .= '</div>';
	
>>>>>>> .r357134
>>>>>>> .r357139
>>>>>>> .r358221
	if (is_user_logged_in()) {
		
		$send_summary = $config->send_summary;
		if ($send_summary == "on" && $cat_id == 0) {
			$forum_digest = get_symposium_meta($current_user->ID, 'forum_digest');
			$html .= "<div class='symposium_subscribe_option label'>";
			$html .= "<input type='checkbox' id='symposium_digest' name='symposium_digest'";
			if ($forum_digest == 'on') { $html .= ' checked'; } 
			$html .= "> ".__("Receive digests via email", "wp-symposium");
			$html .= "</div>";
		}
		if ($cat_id > 0) {
			$subscribed_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix."symposium_subs WHERE tid = 0 AND cid = %d AND uid = %d", $cat_id, $current_user->ID));
			$html .= "<div class='symposium_subscribe_option label'>";
			$html .= "<input type='checkbox' title='".$cat_id."' id='symposium_subscribe' name='symposium_subscribe'";
			if ($subscribed_count > 0) { $html .= ' checked'; } 
			$html .= "> ".__("Receive emails when there are new topics posted", "wp-symposium");
			$html .= "</div>";
		}

	}	

	$html .= "<div style='overflow:visible;'>";
	
		// Options above forum table
		$html .= "<div id='forum_options'>";

			$html .= "<a id='show_search' class='label' href='javascript:void(0)'>".__("Search", "wp-symposium")."</a>";

			if (is_user_logged_in()) {
				$html .= "&nbsp;&nbsp;&nbsp;&nbsp;<a id='show_activity' class='label' href='javascript:void(0)'>".__("My Activity", "wp-symposium")."</a>";
				$html .= "&nbsp;&nbsp;&nbsp;&nbsp;<a id='show_favs' class='label' href='javascript:void(0)'>".__("My Favourites", "wp-symposium")."</a>";
			}

		$html .= "</div>";
>>>>>>> .r358238

<<<<<<< .mine
	if ($categories) {
=======
		// Sharing icons
		if ($config->sharing != '' && $cat_id > 0) {
			$html .= show_sharing_icons($cat_id, 0, $config->sharing);
		}

	$html .= "</div>";
>>>>>>> .r358967
	
<<<<<<< .mine
	// Show child categories in this category ++++++++++++++++++++++++++++++++++++++++++++++++++
=======
	// Show child categories in this category ++++++++++++++++++++++++++++++++++++++++++++++++++
>>>>>>> .r358238
>>>>>>> .r358967

<<<<<<< .mine
	$sql = $wpdb->prepare("SELECT * FROM ".$wpdb->prefix."symposium_cats WHERE cat_parent = %d ORDER BY listorder", $cat_id);
	$categories = $wpdb->get_results($sql);
=======
<<<<<<< .mine
		// Start of table
		$html .= '<div id="symposium_table">';
=======
	$sql = $wpdb->prepare("SELECT * FROM ".$wpdb->prefix."symposium_cats WHERE cat_parent = %d ORDER BY listorder", $cat_id);
	$categories = $wpdb->get_results($sql);
>>>>>>> .r358238
>>>>>>> .r358967

<<<<<<< .mine
	if ($categories) {
=======
<<<<<<< .mine
=======
	if ($categories) {
>>>>>>> .r358967

<<<<<<< .mine
		// Start of table
		$html .= '<div id="symposium_table">';

=======
		// Start of table
		$html .= '<div id="symposium_table">';

>>>>>>> .r358238
>>>>>>> .r358967
			
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
				
					// Last Topic/Reply
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
<<<<<<< .mine
								if ($config->forum_ajax == 'on') {
									$html .= '<a title="'.$last_topic->tid.'" class="topic_subject backto row_link_topic" href="javascript:void(0)">'.stripslashes($subject).'</a> ';
								} else {
									$html .= '<a class="backto row_link_topic" href="'.$forum_url.$q."cid=".$last_topic->topic_category."&show=".$last_topic->tid.'">'.stripslashes($subject).'</a> ';
								}
=======
<<<<<<< .mine
								if ($config->forum_ajax == 'on') {
									$html .= '<a title="'.$last_topic->tid.'" class="topic_subject backto row_link_topic" href="javascript:void(0)">'.stripslashes($subject).'</a> ';
								} else {
									$html .= '<a class="backto row_link_topic" href="'.$forum_url.$q."cid=".$last_topic->topic_category."&show=".$last_topic->tid.'">'.stripslashes($subject).'</a> ';
								}
=======
<<<<<<< .mine
								if ($config->forum_ajax == 'on') {
									$html .= '<a title="'.$last_topic->tid.'" class="topic_subject backto row_link_topic" href="javascript:void(0)">'.stripslashes($subject).'</a> ';
								} else {
									$html .= '<a class="backto row_link_topic" href="'.$forum_url.$q."cid=".$last_topic->topic_category."&show=".$last_topic->tid.'">'.stripslashes($subject).'</a> ';
								}
=======
<<<<<<< .mine
								if ($config->forum_ajax == 'on') {
									$html .= '<a title="'.$last_topic->tid.'" class="topic_subject backto row_link_topic" href="javascript:void(0)">'.stripslashes($subject).'</a> ';
								} else {
									$html .= '<a class="backto row_link_topic" href="'.$forum_url.$q."cid=".$last_topic->topic_category."&show=".$last_topic->tid.'">'.stripslashes($subject).'</a> ';
								}
=======
<<<<<<< .mine
								if ($config->forum_ajax == 'on') {
									$html .= '<a title="'.$last_topic->tid.'" class="topic_subject backto row_link_topic" href="javascript:void(0)">'.stripslashes($subject).'</a> ';
								} else {
									$html .= '<a class="backto row_link_topic" href="'.$forum_url.$q."cid=".$last_topic->topic_category."&show=".$last_topic->tid.'">'.stripslashes($subject).'</a> ';
								}
=======
								$html .= '<a title="'.$last_topic->tid.'" class="topic_subject backto row_link_topic" href="javascript:void(0)">'.stripslashes($subject).'</a> ';
>>>>>>> .r357134
>>>>>>> .r357139
>>>>>>> .r358221
>>>>>>> .r358238
>>>>>>> .r358967
								$html .= symposium_time_ago($reply->topic_date).".";
								if ($reply->topic_approved != 'on') { $html .= " <em>[".__("pending approval", "wp-symposium")."]</em>"; }
							} else {
								$html .= "<div class='avatar avatar_last_topic'>";
									$html .= get_avatar($last_topic->topic_owner, 32);
								$html .= "</div>";
								$html .= symposium_profile_link($last_topic->topic_owner)." ".__("started", "wp-symposium")." ";
								$subject = symposium_bbcode_remove($last_topic->topic_subject);
<<<<<<< .mine
								if ($config->forum_ajax == 'on') {
									$html .= '<a title="'.$last_topic->tid.'" class="topic_subject backto row_link_topic" href="javascript:void(0)">'.stripslashes($subject).'</a> ';
								} else {
									$html .= '<a class="backto row_link_topic" href="'.$forum_url.$q."cid=".$last_topic->topic_category."&show=".$last_topic->tid.'">'.stripslashes($subject).'</a> ';
								}
=======
<<<<<<< .mine
								if ($config->forum_ajax == 'on') {
									$html .= '<a title="'.$last_topic->tid.'" class="topic_subject backto row_link_topic" href="javascript:void(0)">'.stripslashes($subject).'</a> ';
								} else {
									$html .= '<a class="backto row_link_topic" href="'.$forum_url.$q."cid=".$last_topic->topic_category."&show=".$last_topic->tid.'">'.stripslashes($subject).'</a> ';
								}
=======
<<<<<<< .mine
								if ($config->forum_ajax == 'on') {
									$html .= '<a title="'.$last_topic->tid.'" class="topic_subject backto row_link_topic" href="javascript:void(0)">'.stripslashes($subject).'</a> ';
								} else {
									$html .= '<a class="backto row_link_topic" href="'.$forum_url.$q."cid=".$last_topic->topic_category."&show=".$last_topic->tid.'">'.stripslashes($subject).'</a> ';
								}
=======
<<<<<<< .mine
								if ($config->forum_ajax == 'on') {
									$html .= '<a title="'.$last_topic->tid.'" class="topic_subject backto row_link_topic" href="javascript:void(0)">'.stripslashes($subject).'</a> ';
								} else {
									$html .= '<a class="backto row_link_topic" href="'.$forum_url.$q."cid=".$last_topic->topic_category."&show=".$last_topic->tid.'">'.stripslashes($subject).'</a> ';
								}
=======
<<<<<<< .mine
								if ($config->forum_ajax == 'on') {
									$html .= '<a title="'.$last_topic->tid.'" class="topic_subject backto row_link_topic" href="javascript:void(0)">'.stripslashes($subject).'</a> ';
								} else {
									$html .= '<a class="backto row_link_topic" href="'.$forum_url.$q."cid=".$last_topic->topic_category."&show=".$last_topic->tid.'">'.stripslashes($subject).'</a> ';
								}
=======
								$html .= '<a title="'.$last_topic->tid.'" class="topic_subject backto row_link_topic" href="javascript:void(0)">'.stripslashes($subject).'</a> ';
>>>>>>> .r357134
>>>>>>> .r357139
>>>>>>> .r358221
>>>>>>> .r358238
>>>>>>> .r358967
								$html .= symposium_time_ago($last_topic->topic_date).".";
							}

					}
					$html .= "</div>";
				
					// Posts
					$html .= "<div class='row_views'>";
					$post_count = $wpdb->get_var($wpdb->prepare("SELECT count(*) FROM ".$wpdb->prefix."symposium_topics t INNER JOIN ".$wpdb->prefix."symposium_topics u ON u.topic_parent = t.tid WHERE t.topic_parent = 0 AND (t.topic_approved = 'on' OR t.topic_owner = %d) AND t.topic_category = %d", $current_user->ID, $category->cid));

					if ($post_count) { 
<<<<<<< .mine
						$html .= "<div class='post_count' style='color:".$config->text_color.";'>".$post_count."</div>";
							$html .= "<div style='color:".$config->text_color.";' class='post_count_label'>";
=======
<<<<<<< .mine
						$html .= "<div class='post_count' style='color:".$config->text_color.";'>".$post_count."</div>";
							$html .= "<div style='color:".$config->text_color.";' class='post_count_label'>";
=======
<<<<<<< .mine
						$html .= "<div class='post_count' style='color:".$config->text_color.";'>".$post_count."</div>";
							$html .= "<div style='color:".$config->text_color.";' class='post_count_label'>";
=======
						$html .= "<div class='post_count' style='color:".$text_color.";'>".$post_count."</div>";
							$html .= "<div style='color:".$text_color.";' class='post_count_label'>";
>>>>>>> .r358967
>>>>>>> .r358238
>>>>>>> .r358221
							if ($post_count > 1) {
								$html .= __("POSTS", "wp-symposium");
							} else {
								$html .= __("POST", "wp-symposium");
							}
							$html .= "</div>";
					}
					$html .= "</div>";

					// Topic Count
					$html .= "<div class='row_topic row_replies'>";
<<<<<<< .mine
					$topic_count = get_topic_count($category->cid);

					if ($topic_count > 0) {
						$html .= "<div class='post_count' style='color:".$config->text_color.";'>".$topic_count."</div>";
						$html .= "<div style='color:".$config->text_color.";' class='post_count_label'>";
=======
<<<<<<< .mine
					$topic_count = get_topic_count($category->cid);

					if ($topic_count > 0) {
						$html .= "<div class='post_count' style='color:".$config->text_color.";'>".$topic_count."</div>";
						$html .= "<div style='color:".$config->text_color.";' class='post_count_label'>";
=======
<<<<<<< .mine
					$topic_count = get_topic_count($category->cid);

					if ($topic_count > 0) {
						$html .= "<div class='post_count' style='color:".$config->text_color.";'>".$topic_count."</div>";
						$html .= "<div style='color:".$config->text_color.";' class='post_count_label'>";
=======
					$topic_count = get_topic_count($category->cid);

					if ($topic_count > 0) {
						$html .= "<div class='post_count' style='color:".$text_color.";'>".$topic_count."</div>";
						$html .= "<div style='color:".$text_color.";' class='post_count_label'>";
>>>>>>> .r358967
>>>>>>> .r358238
>>>>>>> .r358221
						if ($topic_count != 1) {
							$html .= __("TOPICS", "wp-symposium");
						} else {
							$html .= __("TOPIC", "wp-symposium");
						}
						$html .= "</div>";
					}
					$html .= "</div>";

					// Category title
					$html .= '<div class="row_topic">';
<<<<<<< .mine
					if ($config->forum_ajax == 'on') {
						$html .= '<a class="category_title backto row_link" href="javascript:void(0)" title='.$category->cid.'>'.stripslashes($category->title).'</a>';
					} else {
						$html .= '<a class="backto row_link" href="'.$forum_url.$q."cid=".$category->cid.'">'.stripslashes($category->title).'</a> ';
					}
=======
<<<<<<< .mine
					if ($config->forum_ajax == 'on') {
						$html .= '<a class="category_title backto row_link" href="javascript:void(0)" title='.$category->cid.'>'.stripslashes($category->title).'</a>';
					} else {
						$html .= '<a class="backto row_link" href="'.$forum_url.$q."cid=".$category->cid.'">'.stripslashes($category->title).'</a> ';
					}
=======
<<<<<<< .mine
					if ($config->forum_ajax == 'on') {
						$html .= '<a class="category_title backto row_link" href="javascript:void(0)" title='.$category->cid.'>'.stripslashes($category->title).'</a>';
					} else {
						$html .= '<a class="backto row_link" href="'.$forum_url.$q."cid=".$category->cid.'">'.stripslashes($category->title).'</a> ';
					}
=======
<<<<<<< .mine
					if ($config->forum_ajax == 'on') {
						$html .= '<a class="category_title backto row_link" href="javascript:void(0)" title='.$category->cid.'>'.stripslashes($category->title).'</a>';
					} else {
						$html .= '<a class="backto row_link" href="'.$forum_url.$q."cid=".$category->cid.'">'.stripslashes($category->title).'</a> ';
					}
=======
<<<<<<< .mine
					if ($config->forum_ajax == 'on') {
						$html .= '<a class="category_title backto row_link" href="javascript:void(0)" title='.$category->cid.'>'.stripslashes($category->title).'</a>';
					} else {
						$html .= '<a class="backto row_link" href="'.$forum_url.$q."cid=".$category->cid.'">'.stripslashes($category->title).'</a> ';
					}
=======
					$html .= '<a class="category_title backto row_link" href="javascript:void(0)" title='.$category->cid.'>'.stripslashes($category->title).'</a>';
>>>>>>> .r357134
>>>>>>> .r357139
>>>>>>> .r358221
>>>>>>> .r358238
>>>>>>> .r358967
					$subscribed = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix."symposium_subs WHERE cid = %d AND uid = %d", $category->cid, $current_user->ID));
					if ($subscribed > 0) { $html .= ' <img src="'.$plugin.'images/orange-tick.gif" alt="'.__('Subscribed', 'wp-symposium').'" />'; } 
					$html .= '</div>';

					// Separator
					$html .= "<div class='sep'></div>";											


				$html .= "</div>"; // Row in the table

			}

		$html .= '</div>';
		
	}
		
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
		
		$html .= '<div id="symposium_table">';		

	
			// For every topic in this category 
			foreach ($query as $topic) {
						
				$cnt++;
			
				$replies = $wpdb->get_var($wpdb->prepare("SELECT COUNT(tid) FROM ".$wpdb->prefix."symposium_topics WHERE (topic_approved = 'on' OR topic_owner = %d) AND topic_parent = %d", $current_user->ID, $topic->tid));
				$reply_views = $wpdb->get_var($wpdb->prepare("SELECT sum(topic_views) FROM ".$wpdb->prefix."symposium_topics WHERE (topic_approved = 'on' OR topic_owner = %d) AND tid = %d", $current_user->ID, $topic->tid));
					
				if ($cnt&1) {
					$html .= '<div id="row'.$topic->tid.'" style="border-radius:0px;-moz-border-radius:0px" class="row ';
					if ($cnt == $num_topics) { $html .= ' round_bottom_left round_bottom_right'; }
				} else {
					$html .= '<div id="row'.$topic->tid.'" style="border-radius:0px;-moz-border-radius:0px" class="row_odd ';
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
<<<<<<< .mine
						$html .= "<div class='post_count' style='color:".$config->text_color.";'>".$reply_views."</div>";
						if ($reply_views != 1) { 
							$html .= "<div style='color:".$config->text_color.";' class='post_count_label'>".__("VIEWS", "wp-symposium")."</div>";
						} else {
							$html .= "<div style='color:".$config->text_color.";' class='post_count_label'>".__("VIEW", "wp-symposium")."</div>";						
						}
=======
<<<<<<< .mine
						$html .= "<div class='post_count' style='color:".$config->text_color.";'>".$reply_views."</div>";
						if ($reply_views != 1) { 
							$html .= "<div style='color:".$config->text_color.";' class='post_count_label'>".__("VIEWS", "wp-symposium")."</div>";
						} else {
							$html .= "<div style='color:".$config->text_color.";' class='post_count_label'>".__("VIEW", "wp-symposium")."</div>";						
						}
=======
<<<<<<< .mine
						$html .= "<div class='post_count' style='color:".$config->text_color.";'>".$reply_views."</div>";
						if ($reply_views != 1) { 
							$html .= "<div style='color:".$config->text_color.";' class='post_count_label'>".__("VIEWS", "wp-symposium")."</div>";
						} else {
							$html .= "<div style='color:".$config->text_color.";' class='post_count_label'>".__("VIEW", "wp-symposium")."</div>";						
						}
=======
					$html .= "<div class='post_count' style='color:".$text_color.";'>".$reply_views."</div>";
					if ($reply_views != 1) { 
						$html .= "<div style='color:".$text_color.";' class='post_count_label'>".__("VIEWS", "wp-symposium")."</div>";
					} else {
						$html .= "<div style='color:".$text_color.";' class='post_count_label'>".__("VIEW", "wp-symposium")."</div>";						
					}
>>>>>>> .r358967
>>>>>>> .r358238
>>>>>>> .r358221
					$html .= "</div>";
				
					// Replies
					$html .= "<div class='row_replies'>";
<<<<<<< .mine
						$html .= "<div class='post_count' style='color:".$config->text_color.";'>".$replies."</div>";
						$html .= "<div style='color:".$config->text_color.";' class='post_count_label'>";
						if ($replies != 1) {
							$html .= __("REPLIES", "wp-symposium");
						} else {
							$html .= __("REPLY", "wp-symposium");
						}
						$html .= "</div>";
=======
<<<<<<< .mine
						$html .= "<div class='post_count' style='color:".$config->text_color.";'>".$replies."</div>";
						$html .= "<div style='color:".$config->text_color.";' class='post_count_label'>";
						if ($replies != 1) {
							$html .= __("REPLIES", "wp-symposium");
						} else {
							$html .= __("REPLY", "wp-symposium");
						}
						$html .= "</div>";
=======
<<<<<<< .mine
						$html .= "<div class='post_count' style='color:".$config->text_color.";'>".$replies."</div>";
						$html .= "<div style='color:".$config->text_color.";' class='post_count_label'>";
						if ($replies != 1) {
							$html .= __("REPLIES", "wp-symposium");
						} else {
							$html .= __("REPLY", "wp-symposium");
						}
						$html .= "</div>";
=======
					$html .= "<div class='post_count' style='color:".$text_color.";'>".$replies."</div>";
					$html .= "<div style='color:".$text_color.";' class='post_count_label'>";
					if ($replies != 1) {
						$html .= __("REPLIES", "wp-symposium");
					} else {
						$html .= __("REPLY", "wp-symposium");
					}
>>>>>>> .r358967
>>>>>>> .r358238
>>>>>>> .r358221
					$html .= "</div>";

					// Topic Title		
					$html .= "<div class='row_topic'>";

						// Delete link if applicable
						if (current_user_can('level_10')) {
							$html .= " <a class='floatright delete_topic link_cursor' id='".$topic->tid."'>".__("Delete", "wp-symposium")."</a>";
						}
				
						if (strpos($favs, "[".$topic->tid."]") === FALSE ) { } else {
							$html .= "<img src='".$plugin."images/star-on.gif' class='floatleft' style='height:12px; width:12px; margin-right:4px;' />";						
						}								
				
<<<<<<< .mine
						$subject = symposium_bbcode_remove($topic->topic_subject);
						$html .= '<div class="row_link_div">';
						
							if ($config->forum_ajax == 'on') {
								$html .= '<a title="'.$topic->tid.'" href="javascript:void(0)" class="topic_subject backto row_link">'.stripslashes($subject).'</a>';
							} else {
								$html .= '<a class="backto row_link" href="'.$forum_url.$q."cid=".$topic->topic_category."&show=".$topic->tid.'">'.stripslashes($subject).'</a> ';							
							}
							if ($topic->topic_approved != 'on') { $html .= " <em>[".__("pending approval", "wp-symposium")."]</em>"; }
							if (is_user_logged_in()) {
								$is_subscribed = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix."symposium_subs WHERE cid = 0 AND tid = ".$topic->tid." AND uid = ".$current_user->ID));
								if ($is_subscribed > 0) { $html .= ' <img src="'.$plugin.'images/orange-tick.gif" alt="Subscribed" />'; } 
							}
							if ($topic->allow_replies != 'on') { $html .= ' <img src="'.$plugin.'images/padlock.gif" alt="Replies locked" />'; } 
							if ($topic->topic_sticky) { $html .= ' <img src="'.$plugin.'images/pin.gif" alt="Sticky Topic" />'; } 
=======
<<<<<<< .mine
						$subject = symposium_bbcode_remove($topic->topic_subject);
						$html .= '<div class="row_link_div">';
						
							if ($config->forum_ajax == 'on') {
								$html .= '<a title="'.$topic->tid.'" href="javascript:void(0)" class="topic_subject backto row_link">'.stripslashes($subject).'</a>';
							} else {
								$html .= '<a class="backto row_link" href="'.$forum_url.$q."cid=".$topic->topic_category."&show=".$topic->tid.'">'.stripslashes($subject).'</a> ';							
							}
							if ($topic->topic_approved != 'on') { $html .= " <em>[".__("pending approval", "wp-symposium")."]</em>"; }
							if (is_user_logged_in()) {
								$is_subscribed = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix."symposium_subs WHERE cid = 0 AND tid = ".$topic->tid." AND uid = ".$current_user->ID));
								if ($is_subscribed > 0) { $html .= ' <img src="'.$plugin.'images/orange-tick.gif" alt="Subscribed" />'; } 
							}
							if ($topic->allow_replies != 'on') { $html .= ' <img src="'.$plugin.'images/padlock.gif" alt="Replies locked" />'; } 
							if ($topic->topic_sticky) { $html .= ' <img src="'.$plugin.'images/pin.gif" alt="Sticky Topic" />'; } 
=======
<<<<<<< .mine
						$subject = symposium_bbcode_remove($topic->topic_subject);
						$html .= '<div class="row_link_div">';
						
							if ($config->forum_ajax == 'on') {
								$html .= '<a title="'.$topic->tid.'" href="javascript:void(0)" class="topic_subject backto row_link">'.stripslashes($subject).'</a>';
							} else {
								$html .= '<a class="backto row_link" href="'.$forum_url.$q."cid=".$topic->topic_category."&show=".$topic->tid.'">'.stripslashes($subject).'</a> ';							
							}
							if ($topic->topic_approved != 'on') { $html .= " <em>[".__("pending approval", "wp-symposium")."]</em>"; }
							if (is_user_logged_in()) {
								$is_subscribed = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix."symposium_subs WHERE cid = 0 AND tid = ".$topic->tid." AND uid = ".$current_user->ID));
								if ($is_subscribed > 0) { $html .= ' <img src="'.$plugin.'images/orange-tick.gif" alt="Subscribed" />'; } 
							}
							if ($topic->allow_replies != 'on') { $html .= ' <img src="'.$plugin.'images/padlock.gif" alt="Replies locked" />'; } 
							if ($topic->topic_sticky) { $html .= ' <img src="'.$plugin.'images/pin.gif" alt="Sticky Topic" />'; } 
=======
<<<<<<< .mine
						$subject = symposium_bbcode_remove($topic->topic_subject);
						if ($config->forum_ajax == 'on') {
							$html .= '<div class="row_link_div"><a title="'.$topic->tid.'" href="javascript:void(0)" class="topic_subject backto row_link">'.stripslashes($subject).'</a>';
						} else {
							$html .= '<a class="backto row_link" href="'.$forum_url.$q."cid=".$topic->topic_category."&show=".$topic->tid.'">'.stripslashes($subject).'</a> ';							
						}
						if ($topic->topic_approved != 'on') { $html .= " <em>[".__("pending approval", "wp-symposium")."]</em>"; }
						if (is_user_logged_in()) {
							$is_subscribed = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix."symposium_subs WHERE cid = 0 AND tid = ".$topic->tid." AND uid = ".$current_user->ID));
							if ($is_subscribed > 0) { $html .= ' <img src="'.$plugin.'images/orange-tick.gif" alt="Subscribed" />'; } 
						}
						if ($topic->allow_replies != 'on') { $html .= ' <img src="'.$plugin.'images/padlock.gif" alt="Replies locked" />'; } 
						if ($topic->topic_sticky) { $html .= ' <img src="'.$plugin.'images/pin.gif" alt="Sticky Topic" />'; } 
=======
<<<<<<< .mine
						$subject = symposium_bbcode_remove($topic->topic_subject);
						if ($config->forum_ajax == 'on') {
							$html .= '<div class="row_link_div"><a title="'.$topic->tid.'" href="javascript:void(0)" class="topic_subject backto row_link">'.stripslashes($subject).'</a>';
						} else {
							$html .= '<a class="backto row_link" href="'.$forum_url.$q."cid=".$topic->topic_category."&show=".$topic->tid.'">'.stripslashes($subject).'</a> ';							
						}
						if ($topic->topic_approved != 'on') { $html .= " <em>[".__("pending approval", "wp-symposium")."]</em>"; }
						if (is_user_logged_in()) {
							$is_subscribed = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix."symposium_subs WHERE cid = 0 AND tid = ".$topic->tid." AND uid = ".$current_user->ID));
							if ($is_subscribed > 0) { $html .= ' <img src="'.$plugin.'images/orange-tick.gif" alt="Subscribed" />'; } 
						}
						if ($topic->allow_replies != 'on') { $html .= ' <img src="'.$plugin.'images/padlock.gif" alt="Replies locked" />'; } 
						if ($topic->topic_sticky) { $html .= ' <img src="'.$plugin.'images/pin.gif" alt="Sticky Topic" />'; } 
=======
						$subject = symposium_bbcode_remove($topic->topic_subject);
						$html .= '<div class="row_link_div"><a title="'.$topic->tid.'" href="javascript:void(0)" class="topic_subject backto row_link">'.stripslashes($subject).'</a>';
						if ($topic->topic_approved != 'on') { $html .= " <em>[".__("pending approval", "wp-symposium")."]</em>"; }
						if (is_user_logged_in()) {
							$is_subscribed = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix."symposium_subs WHERE cid = 0 AND tid = ".$topic->tid." AND uid = ".$current_user->ID));
							if ($is_subscribed > 0) { $html .= ' <img src="'.$plugin.'images/orange-tick.gif" alt="Subscribed" />'; } 
						}
						if ($topic->allow_replies != 'on') { $html .= ' <img src="'.$plugin.'images/padlock.gif" alt="Replies locked" />'; } 
						if ($topic->topic_sticky) { $html .= ' <img src="'.$plugin.'images/pin.gif" alt="Sticky Topic" />'; } 
>>>>>>> .r357134
>>>>>>> .r357139
>>>>>>> .r358221
>>>>>>> .r358238
>>>>>>> .r358967
				
<<<<<<< .mine
						$html .= "</div>";
						$post = stripslashes($topic->topic_post);
						$post = symposium_bbcode_remove($post);
						if ( strlen($post) > $snippet_length ) { $post = substr($post, 0, $snippet_length)."..."; }
						$html .= "<span class='row_topic_text'>".$post."</span>";
=======
<<<<<<< .mine
						$html .= "</div>";
						$post = stripslashes($topic->topic_post);
						$post = symposium_bbcode_remove($post);
						if ( strlen($post) > $snippet_length ) { $post = substr($post, 0, $snippet_length)."..."; }
						$html .= "<span class='row_topic_text'>".$post."</span>";
=======
<<<<<<< .mine
						$html .= "</div>";
						$post = stripslashes($topic->topic_post);
						$post = symposium_bbcode_remove($post);
						if ( strlen($post) > $snippet_length ) { $post = substr($post, 0, $snippet_length)."..."; }
						$html .= "<span class='row_topic_text'>".$post."</span>";
=======
>>>>>>> .r358221
>>>>>>> .r358238
>>>>>>> .r358967
					$html .= "</div>";
											
					// Separator
					$html .= "<div class='sep'></div>";		
				
				$html .= "</div>"; // End of Table Row
			
			}

		$html .= "</div>"; // End of table

	}
	
	echo $html;
	
}

function get_topic_count($cat) {
	
	global $wpdb, $current_user;

	$topic_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix."symposium_topics WHERE (topic_approved = 'on' OR topic_owner = %d) AND topic_parent = 0 AND topic_category = %d", $current_user->ID, $cat));
	
	return $topic_count;
	
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

			// Update main topic date for freshness - this is an old feature, now redundant
			//$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix."symposium_topics SET topic_date = NOW() WHERE tid = ".$tid) );					
			
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

// AJAX to fetch forum activity
if ($_POST['action'] == 'getAllActivity') {

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
	
	$preview = 50;	
	$postcount = 100; // Tries to retrieve last 7 days, but this will be a maximum
	
	$include = strtotime("now") - (86400 * 7); // 1 week
	$include = date("Y-m-d H:i:s", $include);
	
	$html = '<div id="forum_activity_div">';
	
		// All topics started
		$posts = $wpdb->get_results("
			SELECT tid, topic_subject, topic_owner, topic_post, topic_category, topic_date, display_name, topic_parent 
			FROM ".$wpdb->prefix.'symposium_topics'." t INNER JOIN ".$wpdb->base_prefix.'users'." u ON t.topic_owner = u.ID 
			WHERE topic_date > '".$include."' ORDER BY tid DESC LIMIT 0,".$postcount); 

		if ($posts) {

			foreach ($posts as $post)
			{
				$html .= "<div style='clear:both'>";		
					$html .= "<div class='symposium_latest_forum_row_avatar'>";
						$html .= get_avatar($post->topic_owner, 20);
					$html .= "</div>";
					$html .= "<div>";
						if ($post->topic_parent > 0) {
							$text = stripslashes($post->topic_post);
							if ( strlen($text) > $preview ) { $text = substr($text, 0, $preview)."..."; }
							$html .= symposium_profile_link($post->topic_owner)." ".__('replied', 'wp-symposium')." ";
							$html .= "<a href='".$thispage.symposium_permalink($post->topic_parent, "topic")."?cid=".$post->topic_category."&show=".$post->topic_parent."'>";
							$html .= $text."</a> ".symposium_time_ago($post->topic_date).".<br>";
						} else {
							$text = stripslashes($post->topic_subject);
							if ( strlen($text) > $preview ) { $text = substr($text, 0, $preview)."..."; }
							$html .= symposium_profile_link($post->topic_owner)." ".__('started', 'wp-symposium')." <a href='".$thispage.symposium_permalink($post->tid, "topic")."?cid=".$post->topic_category."&show=".$post->tid."'>".$text."</a> ".symposium_time_ago($post->topic_date).".<br>";
						}
					$html .= "</div>";
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
		
	$html = '<div id="forum_activity_div">';
	$html .= showThreadChildren(0, 0);	
	$html .= '</div>';
	
	echo $html;
	exit;
}

function showThreadChildren($parent, $level) {
	
	global $wpdb;

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
		$margin_top = 0;
		$desc = "";
	}

	$include = strtotime("now") - (86400 * 7); // 1 week
	$include = date("Y-m-d H:i:s", $include);

	// All topics started
	$posts = $wpdb->get_results("
		SELECT tid, topic_subject, topic_owner, topic_post, topic_category, topic_date, display_name, topic_parent 
		FROM ".$wpdb->prefix.'symposium_topics'." t INNER JOIN ".$wpdb->base_prefix.'users'." u ON t.topic_owner = u.ID 
		WHERE topic_parent = ".$parent." AND topic_date > '".$include."' ORDER BY tid ".$desc." LIMIT 0,".$postcount); 

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
						$html .= "<a href='".$thispage.symposium_permalink($post->topic_parent, "topic")."?cid=".$post->topic_category."&show=".$post->topic_parent."'>";
						$html .= $text."</a> ".symposium_time_ago($post->topic_date).".<br>";
					} else {
						$text = stripslashes($post->topic_subject);
						if ( strlen($text) > $preview ) { $text = substr($text, 0, $preview)."..."; }
						$html .= symposium_profile_link($post->topic_owner)." ".__('started', 'wp-symposium')." <a href='".$thispage.symposium_permalink($post->tid, "topic")."?cid=".$post->topic_category."&show=".$post->tid."'>".$text."</a> ".symposium_time_ago($post->topic_date).".<br>";
					}
				$html .= "</div>";
			$html .= "</div>";
			
			$html .= showThreadChildren($post->tid, $level+1);
			
		}
	}	
	
	return $html;
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
		echo __("Problem retrieving topic information[split]Passed Topic ID = ", "wp-symposium").$tid;
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

<<<<<<< .mine
function show_sharing_icons($cat_id, $topic_id, $sharing) {
	
	$html = "<div id='share_link'>";

		// Sharing icons
		// Work out link to this page, dealing with permalinks or not
		$thispage = symposium_get_url('forum');
		if ($thispage[strlen($thispage)-1] != '/') { $thispage .= '/'; }
		if (strpos($thispage, "?") === FALSE) { 
			$q = "?";
		} else {
			// No Permalink
			$q = "&";
		}
		$title = get_bloginfo();
		$pageURL = get_bloginfo('wpurl').$thispage.$q."cid=".$cat_id."%26show=".$topic_id;

		$plugin = get_site_url().'/wp-content/plugins/wp-symposium/';

		// MySpace
		if (!(strpos($sharing, "ms") === FALSE)) {
			$html .= "<div class='floatright'>";
			$html .= "<a target='_blank' title='".__('Share on MySpace', 'wp-symposium')."' href='http://www.myspace.com/Modules/PostTo/Pages/?u=".$pageURL."&t=".$title."'>";
			$html .= "<img src='".$plugin."images/myspace-icon.gif' style='height:22px; width:22px' alt='MySpace icon' /></a>";
			$html .= "</div>";
		}
		// LinkedIn
		if (!(strpos($sharing, "li") === FALSE)) {
			$html .= "<div class='floatright'>";
			$html .= "<a target='_blank' title='".__('Share on LinkedIn', 'wp-symposium')."' href='http://www.linkedin.com/shareArticle?mini=true&url=".$pageURL."&title=".$title."'>";
			$html .= "<img src='".$plugin."images/linkedin-icon.gif' style='height:22px; width:22px' alt='LinkedIn icon' /></a>";
			$html .= "</div>";
		}
		// Bebo
		if (!(strpos($sharing, "be") === FALSE)) {
			$html .= "<div class='floatright'>";
			$html .= "<a target='_blank' title='".__('Share on Bebo', 'wp-symposium')."' href='http://www.bebo.com/c/share?Url=".$pageURL."&Title=".$title."'>";
			$html .= "<img src='".$plugin."images/bebo-icon.gif' style='height:22px; width:22px' alt='Bebo icon' /></a>";
			$html .= "</div>";
		}
		// Twitter
		if (!(strpos($sharing, "tw") === FALSE)) {
			$html .= "<div class='floatright'>";
			$html .= "<a target='_blank' title='".__('Share on Twitter', 'wp-symposium')."' href='http://twitter.com/home?status=".$pageURL."'>";
			$html .= "<img src='".$plugin."images/twitter-icon.gif' style='height:22px; width:22px' alt='Twitter icon' /></a>";
			$html .= "</div>";
		}
		// Facebook
		if (!(strpos($sharing, "fb") === FALSE)) {
			$html .= "<div class='floatright'>";
			$html .= "<a target='_blank' title='".__('Share on Facebook', 'wp-symposium')."' href='http://www.facebook.com/share.php?u=".$pageURL."&t=".$title."'>";
			$html .= "<img src='".$plugin."images/facebook-icon.gif' style='height:22px; width:22px' alt='Facebook icon' /></a>";
			$html .= "</div>";
		}
		// Email
		if (!(strpos($sharing, "em") === FALSE)) {
			$html .= "<div class='floatright'>";
			$html .= "<a title='".__('Share via email', 'wp-symposium')."' href='mailto:%20?subject=".str_replace(" ", "%20", $title)."&body=".$pageURL."'>";
			$html .= "<img src='".$plugin."images/email-icon.gif' style='height:22px; width:22px' alt='Email icon' /></a>";
			$html .= "</div>";					
		}

	$html .= "</div>";	
	
	return $html;
}

=======
<<<<<<< .mine
function show_sharing_icons($cat_id, $topic_id, $sharing) {
	
	$html = "<div id='share_link'>";

		// Sharing icons
		// Work out link to this page, dealing with permalinks or not
		$thispage = symposium_get_url('forum');
		if ($thispage[strlen($thispage)-1] != '/') { $thispage .= '/'; }
		if (strpos($thispage, "?") === FALSE) { 
			$q = "?";
		} else {
			// No Permalink
			$q = "&";
		}
		$title = get_bloginfo();
		$pageURL = get_bloginfo('wpurl').$thispage.$q."cid=".$cat_id."%26show=".$topic_id;

		$plugin = get_site_url().'/wp-content/plugins/wp-symposium/';

		// MySpace
		if (!(strpos($sharing, "ms") === FALSE)) {
			$html .= "<div class='floatright'>";
			$html .= "<a target='_blank' title='".__('Share on MySpace', 'wp-symposium')."' href='http://www.myspace.com/Modules/PostTo/Pages/?u=".$pageURL."&t=".$title."'>";
			$html .= "<img src='".$plugin."images/myspace-icon.gif' style='height:22px; width:22px' alt='MySpace icon' /></a>";
			$html .= "</div>";
		}
		// LinkedIn
		if (!(strpos($sharing, "li") === FALSE)) {
			$html .= "<div class='floatright'>";
			$html .= "<a target='_blank' title='".__('Share on LinkedIn', 'wp-symposium')."' href='http://www.linkedin.com/shareArticle?mini=true&url=".$pageURL."&title=".$title."'>";
			$html .= "<img src='".$plugin."images/linkedin-icon.gif' style='height:22px; width:22px' alt='LinkedIn icon' /></a>";
			$html .= "</div>";
		}
		// Bebo
		if (!(strpos($sharing, "be") === FALSE)) {
			$html .= "<div class='floatright'>";
			$html .= "<a target='_blank' title='".__('Share on Bebo', 'wp-symposium')."' href='http://www.bebo.com/c/share?Url=".$pageURL."&Title=".$title."'>";
			$html .= "<img src='".$plugin."images/bebo-icon.gif' style='height:22px; width:22px' alt='Bebo icon' /></a>";
			$html .= "</div>";
		}
		// Twitter
		if (!(strpos($sharing, "tw") === FALSE)) {
			$html .= "<div class='floatright'>";
			$html .= "<a target='_blank' title='".__('Share on Twitter', 'wp-symposium')."' href='http://twitter.com/home?status=".$pageURL."'>";
			$html .= "<img src='".$plugin."images/twitter-icon.gif' style='height:22px; width:22px' alt='Twitter icon' /></a>";
			$html .= "</div>";
		}
		// Facebook
		if (!(strpos($sharing, "fb") === FALSE)) {
			$html .= "<div class='floatright'>";
			$html .= "<a target='_blank' title='".__('Share on Facebook', 'wp-symposium')."' href='http://www.facebook.com/share.php?u=".$pageURL."&t=".$title."'>";
			$html .= "<img src='".$plugin."images/facebook-icon.gif' style='height:22px; width:22px' alt='Facebook icon' /></a>";
			$html .= "</div>";
		}
		// Email
		if (!(strpos($sharing, "em") === FALSE)) {
			$html .= "<div class='floatright'>";
			$html .= "<a title='".__('Share via email', 'wp-symposium')."' href='mailto:%20?subject=".str_replace(" ", "%20", $title)."&body=".$pageURL."'>";
			$html .= "<img src='".$plugin."images/email-icon.gif' style='height:22px; width:22px' alt='Email icon' /></a>";
			$html .= "</div>";					
		}

	$html .= "</div>";	
	
	return $html;
}

=======
<<<<<<< .mine
function show_sharing_icons($cat_id, $topic_id, $sharing) {
	
	$html = "<div id='share_link'>";

		// Sharing icons
		// Work out link to this page, dealing with permalinks or not
		$thispage = symposium_get_url('forum');
		if ($thispage[strlen($thispage)-1] != '/') { $thispage .= '/'; }
		if (strpos($thispage, "?") === FALSE) { 
			$q = "?";
		} else {
			// No Permalink
			$q = "&";
		}
		$title = get_bloginfo();
		$pageURL = get_bloginfo('wpurl').$thispage.$q."cid=".$cat_id."%26show=".$topic_id;

		$plugin = get_site_url().'/wp-content/plugins/wp-symposium/';

		// MySpace
		if (!(strpos($sharing, "ms") === FALSE)) {
			$html .= "<div class='floatright'>";
			$html .= "<a target='_blank' title='".__('Share on MySpace', 'wp-symposium')."' href='http://www.myspace.com/Modules/PostTo/Pages/?u=".$pageURL."&t=".$title."'>";
			$html .= "<img src='".$plugin."images/myspace-icon.gif' style='height:22px; width:22px' alt='MySpace icon' /></a>";
			$html .= "</div>";
		}
		// LinkedIn
		if (!(strpos($sharing, "li") === FALSE)) {
			$html .= "<div class='floatright'>";
			$html .= "<a target='_blank' title='".__('Share on LinkedIn', 'wp-symposium')."' href='http://www.linkedin.com/shareArticle?mini=true&url=".$pageURL."&title=".$title."'>";
			$html .= "<img src='".$plugin."images/linkedin-icon.gif' style='height:22px; width:22px' alt='LinkedIn icon' /></a>";
			$html .= "</div>";
		}
		// Bebo
		if (!(strpos($sharing, "be") === FALSE)) {
			$html .= "<div class='floatright'>";
			$html .= "<a target='_blank' title='".__('Share on Bebo', 'wp-symposium')."' href='http://www.bebo.com/c/share?Url=".$pageURL."&Title=".$title."'>";
			$html .= "<img src='".$plugin."images/bebo-icon.gif' style='height:22px; width:22px' alt='Bebo icon' /></a>";
			$html .= "</div>";
		}
		// Twitter
		if (!(strpos($sharing, "tw") === FALSE)) {
			$html .= "<div class='floatright'>";
			$html .= "<a target='_blank' title='".__('Share on Twitter', 'wp-symposium')."' href='http://twitter.com/home?status=".$pageURL."'>";
			$html .= "<img src='".$plugin."images/twitter-icon.gif' style='height:22px; width:22px' alt='Twitter icon' /></a>";
			$html .= "</div>";
		}
		// Facebook
		if (!(strpos($sharing, "fb") === FALSE)) {
			$html .= "<div class='floatright'>";
			$html .= "<a target='_blank' title='".__('Share on Facebook', 'wp-symposium')."' href='http://www.facebook.com/share.php?u=".$pageURL."&t=".$title."'>";
			$html .= "<img src='".$plugin."images/facebook-icon.gif' style='height:22px; width:22px' alt='Facebook icon' /></a>";
			$html .= "</div>";
		}
		// Email
		if (!(strpos($sharing, "em") === FALSE)) {
			$html .= "<div class='floatright'>";
			$html .= "<a title='".__('Share via email', 'wp-symposium')."' href='mailto:%20?subject=".str_replace(" ", "%20", $title)."&body=".$pageURL."'>";
			$html .= "<img src='".$plugin."images/email-icon.gif' style='height:22px; width:22px' alt='Email icon' /></a>";
			$html .= "</div>";					
		}

	$html .= "</div>";	
	
	return $html;
}

=======
function show_sharing_icons($cat_id, $topic_id, $sharing) {
	
	$html = "<div id='share_link'>";

		// Sharing icons
		// Work out link to this page, dealing with permalinks or not
		$thispage = symposium_get_url('forum');
		if ($thispage[strlen($thispage)-1] != '/') { $thispage .= '/'; }
		if (strpos($thispage, "?") === FALSE) { 
			$q = "?";
		} else {
			// No Permalink
			$q = "&";
		}
		$title = get_bloginfo();
		$pageURL = get_bloginfo('wpurl').$thispage.$q."cid=".$cat_id."%26show=".$topic_id;

		$plugin = get_site_url().'/wp-content/plugins/wp-symposium/';

		// MySpace
		if (!(strpos($sharing, "ms") === FALSE)) {
			$html .= "<div class='floatright'>";
			$html .= "<a target='_blank' title='".__('Share on MySpace', 'wp-symposium')."' href='http://www.myspace.com/Modules/PostTo/Pages/?u=".$pageURL."&t=".$title."'>";
			$html .= "<img src='".$plugin."images/myspace-icon.gif' style='height:22px; width:22px' alt='MySpace icon' /></a>";
			$html .= "</div>";
		}
		// LinkedIn
		if (!(strpos($sharing, "li") === FALSE)) {
			$html .= "<div class='floatright'>";
			$html .= "<a target='_blank' title='".__('Share on LinkedIn', 'wp-symposium')."' href='http://www.linkedin.com/shareArticle?mini=true&url=".$pageURL."&title=".$title."'>";
			$html .= "<img src='".$plugin."images/linkedin-icon.gif' style='height:22px; width:22px' alt='LinkedIn icon' /></a>";
			$html .= "</div>";
		}
		// Bebo
		if (!(strpos($sharing, "be") === FALSE)) {
			$html .= "<div class='floatright'>";
			$html .= "<a target='_blank' title='".__('Share on Bebo', 'wp-symposium')."' href='http://www.bebo.com/c/share?Url=".$pageURL."&Title=".$title."'>";
			$html .= "<img src='".$plugin."images/bebo-icon.gif' style='height:22px; width:22px' alt='Bebo icon' /></a>";
			$html .= "</div>";
		}
		// Twitter
		if (!(strpos($sharing, "tw") === FALSE)) {
			$html .= "<div class='floatright'>";
			$html .= "<a target='_blank' title='".__('Share on Twitter', 'wp-symposium')."' href='http://twitter.com/home?status=".$pageURL."'>";
			$html .= "<img src='".$plugin."images/twitter-icon.gif' style='height:22px; width:22px' alt='Twitter icon' /></a>";
			$html .= "</div>";
		}
		// Facebook
		if (!(strpos($sharing, "fb") === FALSE)) {
			$html .= "<div class='floatright'>";
			$html .= "<a target='_blank' title='".__('Share on Facebook', 'wp-symposium')."' href='http://www.facebook.com/share.php?u=".$pageURL."&t=".$title."'>";
			$html .= "<img src='".$plugin."images/facebook-icon.gif' style='height:22px; width:22px' alt='Facebook icon' /></a>";
			$html .= "</div>";
		}
		// Email
		if (!(strpos($sharing, "em") === FALSE)) {
			$html .= "<div class='floatright'>";
			$html .= "<a title='".__('Share via email', 'wp-symposium')."' href='mailto:%20?subject=".str_replace(" ", "%20", $title)."&body=".$pageURL."'>";
			$html .= "<img src='".$plugin."images/email-icon.gif' style='height:22px; width:22px' alt='Email icon' /></a>";
			$html .= "</div>";					
		}

	$html .= "</div>";	
	
	return $html;
}
>>>>>>> .r358221
>>>>>>> .r358238
>>>>>>> .r358967
?>