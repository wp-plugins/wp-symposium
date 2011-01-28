<?php

include_once('../../../../wp-config.php');
include_once('../../../../wp-includes/wp-db.php');
include_once('../symposium_functions.php');

global $wpdb, $current_user;
wp_get_current_user();

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

					$html .= " <a title='".$fav."' class='delete_fav' style='cursor:pointer'>".__("Remove", "wp-symposium")."</a>";
				
					$html .= '<a class="backto row_link_topic" href="'.$thispage.symposium_permalink($post->tid, "topic").$q.'cid='.$post->topic_category.'&show='.$post->tid.'">'.stripslashes($post->topic_subject).'</a>';

					$replies = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$wpdb->prefix.'symposium_topics'." WHERE topic_parent = ".$post->tid." ORDER BY topic_date DESC"));
					if ($replies) {
						$cnt = 0;
						$dt = '';
						foreach ($replies as $reply) {
							$cnt++;
							if ($dt == '') { $dt = $reply->topic_date; }
						}
						
						if ($cnt > 0) {
							$html .= "<br /><em>".$cnt." ";
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

?>