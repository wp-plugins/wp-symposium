<?php

include_once('../../../../wp-config.php');
//include_once('../../../../wp-includes/wp-db.php');
//include_once('../symposium_functions.php');

// Vote Widget
if ($_POST['action'] == 'doVote') {

	global $wpdb, $current_user;
	wp_get_current_user();

	if (is_user_logged_in()) {
	
		$vote = $_POST['vote'];
		
		$sql = "UPDATE ".$wpdb->base_prefix."symposium_usermeta SET widget_voted = 'on' WHERE uid = ".$current_user->ID;
		$wpdb->query( $wpdb->prepare($sql) );
	
		if ($vote == "yes") {
			update_option( "symposium_vote_yes", get_option("symposium_vote_yes")+1 );
		} else {
			update_option( "symposium_vote_no", get_option("symposium_vote_no")+1 );
		}

		echo $vote;

	} else {
		echo "NOT LOGGED IN";		
	}
	
	exit;
}

?>

	