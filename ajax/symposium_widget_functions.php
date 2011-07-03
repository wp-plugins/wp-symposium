<?php

include_once('../../../../wp-config.php');

// Login
if ($_POST['action'] == 'doLogin') {

	global $wpdb, $current_user, $wp_error;
	
	if ($_POST['username'] != '') {

		$creds = array();
		$creds['user_login'] = $_POST['username'];
		$creds['user_password'] = $_POST['password'];
		$creds['remember'] = true;

		$user = wp_signon($creds, false);

		if(is_wp_error($user)) {
			echo 'FAIL';
		} else {

		  	if ($_POST['show_form'] == 'on') {
				if ($_POST['login_url'] != '') {
					$url = $_POST['login_url'];	
				} else {
					$url = symposium_get_url('profile');	
				}
				echo $url;	
  			} else {
				echo '/';	
			}
		}
	} else {
		echo 'FAIL';
	}

	exit;
}

// Vote Widget
if ($_POST['action'] == 'doVote') {

	global $wpdb, $current_user;
	wp_get_current_user();

	if (is_user_logged_in()) {
	
		$vote = $_POST['vote'];
		
		$sql = "UPDATE ".$wpdb->base_prefix."symposium_usermeta SET widget_voted = 'on' WHERE uid = %d";
		$wpdb->query( $wpdb->prepare($sql, $current_user->ID) );
	
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

	