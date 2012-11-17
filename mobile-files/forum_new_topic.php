<?php
include_once('../wp-config.php');
include_once(dirname(__FILE__).'/mobile_check.php');
	
global $wpdb, $current_user;

if (isset($_GET['cat_id'])) {
	$cat_id = $_GET['cat_id'];
} else {
	$cat_id = 0;
}

// Re-act to POSTed information *******************************************************************

if ($_POST['new_topic_subject'] != '' && $_POST['new_topic_post'] != '') {
	$new_topic_subject = $_POST['new_topic_subject'];
	$new_topic_text = $_POST['new_topic_post'];
	$new_topic_category = $_POST['new_topic_category'];
	$group_id = 0;

	// Get list of roles for this user (for use below)
    $user_roles = $current_user->roles;
    $user_role = strtolower(array_shift($user_roles));
    if ($user_role == '') $user_role = 'NONE';
    
	// Check that permitted to category
	$levels = $wpdb->get_var($wpdb->prepare("SELECT level FROM ".$wpdb->prefix."symposium_cats WHERE cid = ".$cat_id));
	$cat_roles = unserialize($levels);
	
	if (strpos(strtolower($cat_roles), 'everyone,') !== FALSE || strpos(strtolower($cat_roles), $user_role.',') !== FALSE) {					

		// Calculate forum URL
		$forum_url = __wps__get_url('forum');
		$q = __wps__string_query($forum_url);		
		
		// Check for moderation
		if (get_option(WPS_OPTIONS_PREFIX.'_moderation') == "on") {
			$topic_approved = "";
		} else {
			$topic_approved = "on";
		}
	
		if ($new_topic_subject == '') { $new_topic_subject = __('No subject', WPS_TEXT_DOMAIN); }
		if ($new_topic_text == '') { $new_topic_text = __('No message', WPS_TEXT_DOMAIN);  }
			
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
				topic_approved,
				topic_group
			)
			VALUES ( %s, %d, %s, %s, %s, %d, %d, %d, %s, %d )", 
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
				$group_id
				) 
			) );
	
		// New Topic ID
		$new_tid = $wpdb->insert_id;
			
		// Set category to the category posted into
		$cat_id = $new_topic_category;
						
		// Get post owner name and prepare email body
		$owner_name = $wpdb->get_var($wpdb->prepare("SELECT display_name FROM ".$wpdb->base_prefix."users WHERE ID = ".$current_user->ID));
		$body = "<p>".$owner_name." ".__('has started a new topic', WPS_TEXT_DOMAIN);
		$category = $wpdb->get_var($wpdb->prepare("SELECT title FROM ".$wpdb->prefix."symposium_cats WHERE cid = ".$cat_id));
		$body .= " ".__('in', WPS_TEXT_DOMAIN)." ".$category;
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
					__wps__sendmail($user->user_email, __('New Forum Topic', WPS_TEXT_DOMAIN), $body);						
				}						
			}
		} else {
			// Email admin if post needs approval
			$body = "<span style='font-size:24px font-style:italic;'>".__('Moderation Required', WPS_TEXT_DOMAIN)."</span><br /><br />".$body;
			__wps__sendmail(get_bloginfo('admin_email'), __('Moderation Required', WPS_TEXT_DOMAIN), $body);
		}	

		// Hook to allow other actions
		$post = __('Started a new forum topic:', WPS_TEXT_DOMAIN).' <a href="'.$url.'">'.$new_topic_subject.'</a>';
		do_action('__wps__forum_newtopic_hook', $current_user->ID, $current_user->display_name, $current_user->ID, $post, 'forum', $new_tid);
	
		header('Location: forum.php?cat_id='.$cat_id.'&topic_id='.$new_tid.'&a='.$_POST['a']);
	}		
}

// End of POSTed information **********************************************************************	

?>
<!DOCTYPE html>
<html>
<head>
<title><?php echo get_bloginfo('name');?></title>
<meta charset="UTF-8" />
<link rel="stylesheet" type="text/css" href="style.css" />
<?php if ($big_display) { ?>
	<link rel="stylesheet" type="text/css" href="bigdisplay.css" />
<?php } ?>
</head>
<body>

<?php
echo '<div id="header">'.get_bloginfo('name').'<div class="home_link"><a href="index.php?'.$a.'">'.__('Home', WPS_TEXT_DOMAIN).'</a></div></div>';
echo '<div class="subheading">'.__('Add a topic', WPS_TEXT_DOMAIN).'</div>';
	
$cat_name = $wpdb->get_var($wpdb->prepare("SELECT title FROM ".$wpdb->prefix."symposium_cats WHERE cid = ".$cat_id));
if ($cat_name == '') { $cat_name = 'Top level'; }
echo "<ul><li><a href='forum.php?cat_id=".$cat_id."&".$a."'>Back to ".$cat_name."...</a></li></ul>";

// Show login link?
if ( is_user_logged_in() ) {
		
	// Add new topic form
	echo '<div class="form">';
		echo '<form action="" method="POST">';
		echo '<input type="hidden" name="a" value="'.$_GET['a'].'" />';
		echo '<input type="hidden" name="new_topic_category" value="'.$cat_id.'" />';
		echo __('New topic subject', WPS_TEXT_DOMAIN).'<br />';
		echo '<input type="text" class="input" name="new_topic_subject" /><br />';
		echo __('New topic text', WPS_TEXT_DOMAIN).'<br />';
		echo '<textarea name="new_topic_post"></textarea><br />';
		echo '<input type="submit" class="submit" value="'.__('Start topic in', WPS_TEXT_DOMAIN).' '.$cat_name.'" />';
		echo '</form>';
	echo '</div>';

}

include_once(dirname(__FILE__).'/footer.php');	
?>
</body>
</html>
