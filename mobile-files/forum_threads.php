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
echo '<div class="subheading">'.__('Forum Threads', WPS_TEXT_DOMAIN).'</div>';

$viewer = get_option(WPS_OPTIONS_PREFIX.'_viewer');
$level = __wps__get_current_userlevel();

if ( ($viewer == "Guest")
 || ($viewer == "Subscriber" && $level >= 1)
 || ($viewer == "Contributor" && $level >= 2)
 || ($viewer == "Author" && $level >= 3)
 || ($viewer == "Editor" && $level >= 4)
 || ($viewer == "Administrator" && $level == 5) ) {

	echo showThreadChildren(0, 0, 0, $a);	
	
} else {

	echo '<div class="line">'.__('You need to login to view the forum.', WPS_TEXT_DOMAIN).'</div>';

}

if ( is_user_logged_in() ) {
	echo '<div class="line">'.__('Logged in as', WPS_TEXT_DOMAIN).' '.$current_user->display_name.' - <a href="login.php?action=logout">'.__('Logout', WPS_TEXT_DOMAIN).'</a></div>';
}

include_once(dirname(__FILE__).'/footer.php');	

// FUNCTIONS *******************************************************************

function showThreadChildren($parent, $level, $gid, $a) {
	
	global $wpdb;

	// Work out link to this page, dealing with permalinks or not
	if ($gid == 0) {
		$thispage = __wps__get_url('forum');
		if ($thispage[strlen($thispage)-1] != '/') { $thispage .= '/'; }
		if (strpos($thispage, "?") === FALSE) { 
			$q = "?";
		} else {
			// No Permalink
			$q = "&";
		}
	} else {
		$thispage = __wps__get_url('group');
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
	$postcount = get_option(WPS_OPTIONS_PREFIX.'_mobile_topics');
	if ($postcount < 1) { $postcount = 20; }
	
	if ($level == 0) {
		$desc = "DESC";
	} else {
		$desc = "";
	}

	// Tries to retrieve last 7 days unless postcount causes it to be less
	$include = strtotime("now") - (86400 * 7); // 1 week
	$include = date("Y-m-d H:i:s", $include);

	// All topics started
	$posts = $wpdb->get_results("
		SELECT tid, topic_subject, topic_owner, topic_post, topic_category, topic_date, display_name, topic_parent 
		FROM ".$wpdb->prefix.'symposium_topics'." t INNER JOIN ".$wpdb->base_prefix.'users'." u ON t.topic_owner = u.ID 
		WHERE topic_parent = ".$parent." AND topic_group = ".$gid." AND topic_date > '".$include."' ORDER BY tid ".$desc." LIMIT 0,".$postcount); 

	if ($posts) {

		$html .= '<ul>';
		foreach ($posts as $post)
		{
			$text = stripslashes($post->topic_post);
			if ( strlen($text) > $preview ) { $text = substr($text, 0, $preview)."..."; }
			if ($post->topic_parent > 0) {
				$html .= "<li><a href='forum.php?topic_id=".$post->topic_parent."&".$a."'>".$text."</a></li>";
			} else {
				$html .= "<li><a href='forum.php?topic_id=".$post->tid."&".$a."'>".$text."</a></li>";
			}
			$html .= showThreadChildren($post->tid, $level+1, $gid, $a);
			
		}
		$html .= '</ul>';
	}	
	
	return $html;

}


?>
</body>
</html>
