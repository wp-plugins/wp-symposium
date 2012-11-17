<?php
include_once('../wp-config.php');
include_once(dirname(__FILE__).'/mobile_check.php');

global $wpdb, $current_user, $wp_error;

// Re-act to GET/POST information *****************************************************************

if ( is_user_logged_in() ) {

	session_destroy();
	wp_logout();
	wp_redirect('index.php?a=1');

} else {

	if ($_POST['username'] != '') {
		$username = $_POST['username'];
		$password = $_POST['password'];

		$user = wp_authenticate($username, $password);

		if(is_wp_error($user)) {
			echo "<div class='line'>Login failed, please try again.</div>";
		} else {
			wp_login($username, $password, true);
			wp_setcookie($username, $password, true);
			wp_set_current_user($user->ID, $username);
			wp_redirect('index.php?a=1');
		}
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

// Show login form
if ( !is_user_logged_in() ) {
	echo '<div class="form">';
	echo '<form action="" method="POST">';
	echo 'Username';
	echo '<input type="text" class="input" name="username" /><br />';
	echo 'Password';
	echo '<input type="password" class="input" name="password" /><br />';
	echo '<input type="submit" class="submit" value="Login" />';
	echo '</form></div>';
}

include_once(dirname(__FILE__).'/footer.php');	
?>
</body>
</html>
