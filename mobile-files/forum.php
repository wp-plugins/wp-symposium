<?php
include_once('../wp-config.php');
include_once(dirname(__FILE__).'/mobile_check.php');
	
global $wpdb, $current_user;

if (isset($_GET['topic_id'])) {
	$topic_id = $_GET['topic_id'];
} else {
	$topic_id = 0;
}

if (isset($_GET['cat_id'])) {
	$cat_id = $_GET['cat_id'];
} else {
	$cat_id = 0;
}

// Redirect if not on a mobile
if (!$mobile) {

	$forum_url = __wps__get_url('forum');
	if (strpos($forum_url, '?') !== FALSE) {
		$q = "&";
	} else {
		$q = "?";
	}

	header('Location: '.$forum_url.$q.'cid='.$cat_id.'&show='.$topic_id);
}

$maxlen = 35; // Max length of topic/category text

// Page Title
$page_title = get_bloginfo('name');

if ($cat_id > 0) {
	$sql = "SELECT title FROM ".$wpdb->prefix."symposium_cats WHERE cid = %d";
	$page_title = stripslashes($wpdb->get_var($wpdb->prepare($sql, $cat_id)));
}

if ($topic_id > 0) {
	$sql = "SELECT topic_subject FROM ".$wpdb->prefix."symposium_topics WHERE tid=%d";
	$page_title = stripslashes($wpdb->get_var($wpdb->prepare($sql, $topic_id)));
}
?>
<!DOCTYPE html>
<html>
<head>
<title><?php echo $page_title; ?></title>
<meta charset="UTF-8" />
<link rel="stylesheet" type="text/css" href="style.css" />
<?php if ($big_display) { ?>
	<link rel="stylesheet" type="text/css" href="bigdisplay.css" />
<?php } ?>
</head>
<body>

<?php
echo '<div id="header">'.get_bloginfo('name').'<div class="home_link"><a href="index.php?'.$a.'">'.__('Home', WPS_TEXT_DOMAIN).'</a></div></div>';

$viewer = get_option(WPS_OPTIONS_PREFIX.'_viewer');
$level = __wps__get_current_userlevel();

if ( ($viewer == "Guest")
 || ($viewer == "Subscriber" && $level >= 1)
 || ($viewer == "Contributor" && $level >= 2)
 || ($viewer == "Author" && $level >= 3)
 || ($viewer == "Editor" && $level >= 4)
 || ($viewer == "Administrator" && $level == 5) ) {

	// Get list of roles for this user (for use below)
    $user_roles = $current_user->roles;
    $user_role = strtolower(array_shift($user_roles));
    if ($user_role == '') $user_role = 'NONE';
	
	// Show Category if not viewing a topic, and topics in that category
	if ($topic_id == 0) {

		$postcount = get_option(WPS_OPTIONS_PREFIX.'_mobile_topics');
		if ($postcount < 1) { $postcount = 20; }
		
		// Show login link?
		if ( !is_user_logged_in() ) {
			echo '<ul><li><a href="login.php'.$a.'">'.__('Login', WPS_TEXT_DOMAIN).'</a></li></ul>';
		}

		// Get categories
		$sql = "SELECT * FROM ".$wpdb->prefix."symposium_cats WHERE cat_parent = %d ORDER BY listorder";
		$categories = $wpdb->get_results($wpdb->prepare($sql, $cat_id));
		
		// Get topics
		$sql = "SELECT * FROM ".$wpdb->prefix."symposium_topics WHERE topic_parent=0 AND topic_category=%d AND topic_group=0 ORDER BY tid DESC LIMIT 0,%d";
		$forum = $wpdb->get_results($wpdb->prepare($sql, $cat_id, $postcount));

		// Show categories if any to show					
		if ($categories) {

			if ($cat_id == 0) {
				echo '<div class="subheading">'.__('Categories', WPS_TEXT_DOMAIN).'</div>';
			}
			if ($cat_id > 0) {
				$cat_name = $wpdb->get_var($wpdb->prepare("SELECT title FROM ".$wpdb->prefix."symposium_cats WHERE cid = ".$cat_id));
				$cat_parent = $category->cat_parent;
				if ($cat_parent == '') { $cat_parent = 0; }
				echo '<div class="subheading">';
				echo $cat_name;
				echo '</div>';
			
				echo "<ul>";
				if ($cat_parent > 0) {
					echo "<li><a href='?cat_id=".$cat_parent."&".$a."'>".__('Up a level', WPS_TEXT_DOMAIN)."</a></li>";
				} else {
					echo "<li><a href='?cat_id=".$cat_parent."&".$a."'>".__('Top level', WPS_TEXT_DOMAIN)."</a></li>";
				}
				if ($forum) {
					echo "<li><a href='#topics?".$a."'>".__('Jump to topics', WPS_TEXT_DOMAIN)."</a></li>";
				}
				echo "</ul>";

			}

			echo '<ul>';
			foreach ($categories as $category) {

				// Check that permitted to category
				$cat_roles = unserialize($category->level);
				if (strpos(strtolower($cat_roles), 'everyone,') !== FALSE || strpos(strtolower($cat_roles), $user_role.',') !== FALSE) {		

					$title = stripslashes($category->title);
					if (strlen($title) > $maxlen) { $title = substr($title, 0, $maxlen)."..."; }
					echo "<li><a href='?cat_id=".$category->cid."&".$a."'>".$title."</a></li>";
					
				}
				
			}

			// Check that permitted to category
			$cat_roles = unserialize($cat_id);
			if (strpos(strtolower($cat_roles), 'everyone,') !== FALSE || strpos(strtolower($cat_roles), $user_role.',') !== FALSE) {		
				if ( is_user_logged_in() ) {
					echo '<li><a href="forum_new_topic.php?cat_id='.$cat_id.'&'.$a.'">'.__('Add new topic', WPS_TEXT_DOMAIN).'</a></li>';
				}
			}
			
			echo '</ul>';

		} else {

			if ($cat_id > 0) {
				$cat_name = $wpdb->get_var($wpdb->prepare("SELECT title FROM ".$wpdb->prefix."symposium_cats WHERE cid = ".$cat_id));
				$cat_parent = $wpdb->get_var($wpdb->prepare("SELECT cat_parent FROM ".$wpdb->prefix."symposium_cats WHERE cid = ".$cat_id));
				if ($cat_parent == '') { $cat_parent = 0; }
				echo '<div class="subheading">';
				echo $cat_name;
				echo '</div>';
				echo "<ul>";
				if ($cat_parent > 0) {
					echo "<li><a href='?cat_id=".$cat_parent."&".$a."'>".__('Up a level', WPS_TEXT_DOMAIN)."</a></li>";
				} else {
					echo "<li><a href='?cat_id=".$cat_parent."&".$a."'>".__('Top level', WPS_TEXT_DOMAIN)."</a></li>";
				}
				if ($forum) {
					echo "<li><a href='#topics?".$a."'>".__('Jump to topics', WPS_TEXT_DOMAIN)."</a></li>";
				}
				if ( is_user_logged_in() ) {
					echo '<li><a href="forum_new_topic.php?cat_id='.$cat_id.'&'.$a.'">'.__('Add new topic', WPS_TEXT_DOMAIN).'</a></li>';
				}
				echo "</ul>";
			}
		}
		
		// Show Topics in a Category
		if ($forum) {

			// Check that permitted to category
			$levels = $wpdb->get_var($wpdb->prepare("SELECT level FROM ".$wpdb->prefix."symposium_cats WHERE cid = ".$cat_id));
			$cat_roles = unserialize($levels);
			if (strpos(strtolower($cat_roles), 'everyone,') !== FALSE || strpos(strtolower($cat_roles), $user_role.',') !== FALSE) {					

				// Show topics
				echo '<a name="topics" />';
				echo '<div class="subheading">'.__('Topics', WPS_TEXT_DOMAIN).'</div>';
				echo '<ul>';
				foreach ($forum as $topic) {

					$subject = stripslashes($topic->topic_subject);
					if (strlen($subject) > $maxlen) { $subject = substr($subject, 0, $maxlen)."..."; }
					echo "<li><a href='?topic_id=".$topic->tid."&".$a."'>".$subject."</a></li>";
				
				}
				echo '</ul>';
				
			}
		}

	}

	// Show a topic
	if ($topic_id > 0) {

		$sql = "SELECT t.*, u.display_name FROM ".$wpdb->prefix."symposium_topics t LEFT JOIN ".$wpdb->base_prefix."users u ON t.topic_owner = u.ID WHERE tid=%d";
		$topic = $wpdb->get_row($wpdb->prepare($sql, $topic_id));
		if ($topic) {

			// Check that permitted to category
			$levels = $wpdb->get_var($wpdb->prepare("SELECT level FROM ".$wpdb->prefix."symposium_cats WHERE cid = ".$topic->topic_category));
			$cat_roles = unserialize($levels);
			if (strpos(strtolower($cat_roles), 'everyone,') !== FALSE || strpos(strtolower($cat_roles), $user_role.',') !== FALSE) {					

				$cat_name = $wpdb->get_var($wpdb->prepare("SELECT title FROM ".$wpdb->prefix."symposium_cats WHERE cid = ".$topic->topic_category));
				echo "<ul>";
				// Show login link?
				if ( !is_user_logged_in() ) {
					echo '<li><a href="login.php?".$a.">'.__('Login', WPS_TEXT_DOMAIN).'</a></li>';
				}
				echo "<li><a href='?cat_id=".$topic->topic_category."&".$a."'>Back to ".$cat_name."...</a></li>";
				if ( is_user_logged_in() ) {
					echo "<li><a href='forum_new_reply.php?cat_id=".$topic->topic_category."&topic_id=".$topic_id."&".$a."'>".__('Post a reply', WPS_TEXT_DOMAIN)."</a></li>";
				}
				echo "</ul>";
	
				echo "<div id='topic_post'>";
				echo stripslashes($topic->topic_subject);
				echo "</div>";
				echo "<div class='line'>";
				echo str_replace(chr(13), "<br />", stripslashes($topic->topic_post));
				echo "</div>";
				echo "<div class='line topic_started'>";
				echo __("Topic started:", WPS_TEXT_DOMAIN)." ".__wps__time_ago(stripslashes($topic->topic_date));
				echo " ".__("by", WPS_TEXT_DOMAIN)." ".$topic->display_name;
				echo "</div>";
	
				$sql = "SELECT t.*, u.display_name FROM ".$wpdb->prefix."symposium_topics t LEFT JOIN ".$wpdb->base_prefix."users u ON t.topic_owner = u.ID WHERE topic_parent=%d ORDER BY tid";
				$replies = $wpdb->get_results($wpdb->prepare($sql, $topic_id));
				if ($replies) {
					echo '<div class="subheading">'.__('Replies', WPS_TEXT_DOMAIN).'</div>';
					foreach ($replies as $reply) {
						echo "<div class='line'>";
						echo str_replace(chr(13), "<br />", stripslashes($reply->topic_post));
						echo "</div>";				
						echo "<div class='line reply'>";
						echo __wps__time_ago(stripslashes($reply->topic_date));
						echo " ".__("by", WPS_TEXT_DOMAIN)." ".$reply->display_name;
						echo "</div>";
					}
				}				
	
				if ( is_user_logged_in() ) {
					echo "<ul>";
					echo "<li><a href='forum_new_reply.php?topic_id=".$topic_id."&".$a."'>".__('Post a reply', WPS_TEXT_DOMAIN)."</a></li>";
					echo "</ul>";
				}
			}			
		}
	}

} else {

	echo '<div class="line">'.__('You need to login to view the forum.', WPS_TEXT_DOMAIN).'</div>';

}

if ( is_user_logged_in() ) {
	echo '<div class="line">'.__('Logged in as', WPS_TEXT_DOMAIN).' '.$current_user->display_name.' - <a href="login.php?action=logout">'.__('Logout', WPS_TEXT_DOMAIN).'</a></div>';
}

include_once(dirname(__FILE__).'/footer.php');	
?>
</body>
</html>
