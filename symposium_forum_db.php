<?php
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

include_once('../../../wp-config.php');
include_once('../../../wp-includes/wp-db.php');
include_once('symposium_functions.php');

global $wpdb, $current_user;
wp_get_current_user();
	
if (is_user_logged_in()) {

	$users = $wpdb->base_prefix . 'users';
	$config = $wpdb->prefix . 'symposium_config';
	$topics = $wpdb->prefix . 'symposium_topics';
	$subs = $wpdb->prefix . 'symposium_subs';
	$cats = $wpdb->prefix . 'symposium_cats';
	
	// Get forum_url
	$forum_url = $wpdb->get_var($wpdb->prepare("SELECT forum_url FROM ".$wpdb->prefix.'symposium_config'));
	if ($forum_url[strlen($forum_url)-1] != '/') { $forum_url .= '/'; }

	// Check for moderation
	$moderation = $wpdb->get_var($wpdb->prepare("SELECT moderation FROM ".$wpdb->prefix.'symposium_config'));
	if ($moderation == "on") {
		$topic_approved = "";
	} else {
		$topic_approved = "on";
	}

	// New Topic ****************************************************************
	if ($_POST['action'] == 'post') {
		
		$new_topic_subject = $_POST['new_topic_subject'];
		$new_topic_text = $_POST['new_topic_text'];
		$new_topic_subscribe = $_POST['new_topic_subscribe'];
		$new_topic_category = $_POST['new_topic_category'];

		$store = true;
		$edit_new_topic = false;
		if ($new_topic_subject == '') { $msg = __('Please enter a subject', 'wp-symposium'); $store = false; $edit_new_topic = true; }
		if ($new_topic_text == '') { $msg = __('Please enter a message', 'wp-symposium'); $store = false; $edit_new_topic = true; }
		
		if ( ($store) && is_user_logged_in() ) {
			// Check for duplicates
			
			$topic_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$topics." WHERE topic_subject = '".$new_topic_subject."' and topic_post = '".$new_topic_text."' AND topic_owner = ".$current_user->ID));
	
			if ($topic_count > 1) {
				// Don't double post
			} else {						
				// Store new topic in post
				
				// Don't allow HTML
				$new_topic_text = str_replace("<", "&lt;", $new_topic_text);
				$new_topic_text = str_replace(">", "&gt;", $new_topic_text);
	
				$wpdb->query( $wpdb->prepare( "
					INSERT INTO ".$topics."
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
						INSERT INTO ".$subs."
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
				$owner_name = $wpdb->get_var($wpdb->prepare("SELECT display_name FROM ".$users." WHERE ID = ".$current_user->ID));
				$body = "<p>".$owner_name." ".__('has started a new topic', 'wp-symposium');
				$show_categories = $wpdb->get_var($wpdb->prepare("SELECT show_categories FROM ".$config));
				if ($show_categories == "on") {
					$category = $wpdb->get_var($wpdb->prepare("SELECT title FROM ".$cats." WHERE cid = ".$cat_id));
					$body .= " ".__('in', 'wp-symposium')." ".$category;
				}
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
						FROM ".$users." u RIGHT JOIN ".$subs." ON ".$subs.".uid = u.ID 
						WHERE tid = 0 AND u.ID != ".$current_user->ID." AND cid = ".$cat_id);
						
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
			}
		}
	
		header ("Location: ".$_POST['url']."cid=".$cat_id);
		exit;
	
	}
	

}
	
?>