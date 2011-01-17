<?php

include_once('../../../../wp-config.php');
include_once('../../../../wp-includes/wp-db.php');
include_once('../symposium_functions.php');

// AJAX function to send new password
if ($_POST['action'] == 'doForgot') {

	global $wpdb;

	$email= $_POST['email'];
	
	// Generate new password
	$pwd = "";
	$length = 7;
	$possible1 = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$possible2 = "!@$%*-+=_";
	$possible3 = "123456789";
	$i=0;
	while ($i < $length) {
		if ($i == 0 || $i == 1 || $i == 2) {
			$char = substr($possible1, mt_rand(0, strlen($possible1)-1), 1);
		}
		if ($i == 3) {
			$char = substr($possible2, mt_rand(0, strlen($possible2)-1), 1);
		}
		if ($i == 4 || $i == 5 || $i == 6) {
			$char = substr($possible3, mt_rand(0, strlen($possible3)-1), 1);
		}
		if (!substr($password, $char)) {
			$pwd .= $char;
			$i++;
		}
	}
	
	$sql = "UPDATE ".$wpdb->prefix."users SET user_pass = '".wp_hash_password($pwd)."' WHERE user_email = '".$email."'";
	$rows_affected = $wpdb->query( $wpdb->prepare($sql) );
	if ( $rows_affected > 0 ) {
		$body = "<p>";
		$body .= __('You (or somebody else) requested a new password');
		$body .= ".</p><p>";
		$body .= sprintf(__('Your password has been set to: %s', 'wp-symposium'), $pwd)."</p>";
		symposium_sendmail($email, __("Forgotten Password", "wp-symposium"), $body);
		echo "OK";
	} else {
		echo __("Email address not found, please use the email address you registered with.", "wp-symposium");
	}

	exit;
}

// AJAX function to login
if ($_POST['action'] == 'doLogin') {

	global $wpdb,$wp_error;

	$username = $_POST['username'];
	$password = $_POST['pwd'];
	$redirect_to = $_POST['redirect_to'];
	if ($redirect_to == '') { $redirect_to = '/'; }

	$user = wp_authenticate($username, $password);
    if(is_wp_error($user)) {
        echo "FAIL";
    } else {
    	
		wp_login($username, $password, true);
        wp_setcookie($username, $password, true);
        wp_set_current_user($user->ID, $username);

		$redirect = $wpdb->get_row($wpdb->prepare("SELECT enable_redirects, login_redirect, login_redirect_url FROM ".$wpdb->prefix . 'symposium_config'));
	
		if ( ($redirect->enable_redirects == 'on') ) {
			switch($redirect->login_redirect) {			
				case "Profile Wall":
					$url = symposium_get_url('profile');	
					break;
				case "Profile Settings":
					$url = symposium_get_url('profile')."?view=settings";	
					break;
				case "Profile Personal":
					$url = symposium_get_url('profile')."?view=personal";	
					break;
				case "Mail":
					$url = symposium_get_url('mail');	
					break;
				case "Forum":
					$url = symposium_get_url('forum');	
					break;
				case "Previous":
					$url = $redirect_to;	
					break;
				case "Custom":
					$url = $redirect->login_redirect_url;	
					break;
				default:
					$url = symposium_get_url('profile');	
					break;
			}
		} else {
			$url = $redirect_to;	
		}

        echo $url;
    }

	exit;
}


?>

	