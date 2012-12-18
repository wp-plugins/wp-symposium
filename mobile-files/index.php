<?php
include_once('../wp-config.php');
include_once(dirname(__FILE__).'/mobile_check.php');

global $wpdb, $current_user;

// Redirect if not on a mobile
if (!$mobile) {
	header('Location: ./..');
}

// Re-act to POSTed information *******************************************************************

if ($_POST['update_status'] != '' && $current_user->ID > 0) {
	$new_status = $_POST['update_status'];

	// Don't allow HTML
	$new_status = str_replace("<", "&lt;", $new_status);
	$new_status = str_replace(">", "&gt;", $new_status);

	$wpdb->query( $wpdb->prepare( "
		INSERT INTO ".$wpdb->base_prefix."symposium_comments
		( 	subject_uid, 
			author_uid,
			comment_parent,
			comment_timestamp,
			comment,
			is_group
		)
		VALUES ( %d, %d, %d, %s, %s, %s )", 
			array(
			$current_user->ID, 
		       	$current_user->ID, 
		       	0,
		       	date("Y-m-d H:i:s"),
		       	$new_status,
		       	''
		       	) 
		 ) );

}

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

echo '<div id="header">'.get_bloginfo('name').'</div>';

// Show login link?
if ( !is_user_logged_in() ) {
	echo '<div class="line"><a href="login.php?'.$a.'">'.__('Login', WPS_TEXT_DOMAIN).'</a></div>';
} else {

	echo '<div class="line"><br />';
	// Get current status
	$sql = "SELECT comment FROM ".$wpdb->base_prefix."symposium_comments WHERE is_group != 'on' AND comment_parent = 0 AND author_uid = %d AND subject_uid = %d ORDER BY cid DESC LIMIT 0,1";
	$status = $wpdb->get_var($wpdb->prepare($sql, $current_user->ID, $current_user->ID));
	$status_label = $wpdb->get_var("SELECT status_label FROM ".$wpdb->prefix."syposium_config");
	echo stripslashes($status)."<br /><br />";
	echo $status_label."<br />";
	echo '<form action="" method="POST">';
	echo '<input type="text" name="update_status" /><br />';
	echo '<input type="submit" class="submit" value="'.__('Update', WPS_TEXT_DOMAIN).'" />';
	echo '</form>';
	echo '</div>';
}

echo '<ul>';
//echo '<li><a href="profile.php?'.$a.'">'.__('Profile', WPS_TEXT_DOMAIN).'</a></li>';
echo '<li><a href="forum.php?'.$a.'">'.__('Forum', WPS_TEXT_DOMAIN).'</a></li>';
echo '<li><a href="forum_threads.php?'.$a.'">'.__('Forum Threads', WPS_TEXT_DOMAIN).'</a></li>';
echo '</ul>';

if ( is_user_logged_in() ) {
	echo '<div class="line">'.__('Logged in as', WPS_TEXT_DOMAIN).' '.$current_user->display_name.' - <a href="login.php?'.$a.'">'.__('Logout', WPS_TEXT_DOMAIN).'</a></div>';
}

include_once(dirname(__FILE__).'/footer.php');	

?>
</body>
</html>
