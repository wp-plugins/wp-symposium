<?php

include_once('../../../../wp-config.php');

global $wpdb, $current_user;


// symposium_summary_Widget
if ($_POST['action'] == 'symposium_summary_Widget') {
	
	$show_loggedout = $_POST['show_loggedout'];
	$show_form = $_POST['form'];
	$login_url = $_POST['login_url'];
	
	do_symposium_summary_Widget($show_loggedout,$show_form,$login_url);
		
}

// symposium_friends_Widget
if ($_POST['action'] == 'symposium_friends_Widget') {
	
	$symposium_friends_count = $_POST['count'];
	$symposium_friends_desc = $_POST['desc'];
	$symposium_friends_mode = $_POST['mode'];
	$symposium_friends_show_light = $_POST['show_light'];
	$symposium_friends_show_mail = $_POST['show_mail'];

	do_symposium_friends_Widget($symposium_friends_count,$symposium_friends_desc,$symposium_friends_mode,$symposium_friends_show_light,$symposium_friends_show_mail);

}

// Forumexperts_Widget
if ($_POST['action'] == 'Forumexperts_Widget') {
	
	$cat_id = $_POST['cat_id'];
	$cat_id_exclude = $_POST['cat_id_exclude'];
	$timescale = $_POST['timescale'];
	$postcount = $_POST['postcount'];
	$groups = $_POST['groups'];

	do_Forumexperts_Widget($cat_id,$cat_id_exclude,$timescale,$postcount,$groups);
}

// Forumnoanswer_Widget
if ($_POST['action'] == 'Forumnoanswer_Widget') {
	
	$preview = $_POST['preview'];
	$cat_id = $_POST['cat_id'];
	$cat_id_exclude = $_POST['cat_id_exclude'];
	$timescale = $_POST['timescale'];
	$postcount = $_POST['postcount'];
	$groups = $_POST['groups'];

	do_Forumnoanswer_Widget($preview,$cat_id,$cat_id_exclude,$timescale,$postcount,$groups);
	
}

// recent_Widget
if ($_POST['action'] == 'recent_Widget') {
	
	$symposium_recent_count = $_POST['count'];
	$symposium_recent_desc = $_POST['desc'];
	$symposium_recent_show_light = $_POST['show_light'];
	$symposium_recent_show_mail = $_POST['show_mail'];

	do_recent_Widget($symposium_recent_count,$symposium_recent_desc,$symposium_recent_show_light,$symposium_recent_show_mail);
}

// members_Widget
if ($_POST['action'] == 'members_Widget') {
	
	$symposium_members_count = $_POST['count'];
	do_members_Widget($symposium_members_count);
	
}

// Forumrecentposts_Widget
if ($_POST['action'] == 'Forumrecentposts_Widget') {
	
	$postcount = $_POST['postcount'];
	$preview = $_POST['preview'];
	$cat_id = $_POST['cat_id'];
	$show_replies = $_POST['show_replies'];
	
	do_Forumrecentposts_Widget($postcount,$preview,$cat_id,$show_replies);
}

// Recentactivity_Widget
if ($_POST['action'] == 'Recentactivity_Widget') {
	
	$postcount = $_POST['postcount'];
	$preview = $_POST['preview'];
	$forum = $_POST['forum'];
	
	do_Recentactivity_Widget($postcount,$preview,$forum);
	
}

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

	