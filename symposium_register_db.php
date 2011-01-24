<?php
/*  Copyright 2010,2011  Simon Goodchild  (info@wpsymposium.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

include_once('../../../wp-config.php');
include_once('../../../wp-includes/wp-db.php');
include_once('symposium_functions.php');
	
global $wpdb, $current_user;
wp_get_current_user();

if (!is_user_logged_in()) {
	
	$hdn = $_POST['hdn'];
	$html = "";
	
	// Bots tend to fill all fields in, hdn is hidden via CSS
	if ($hdn == '') {
		
		$username = $_POST['username'];
		$youremail = $_POST['youremail'];
		$display_name = $_POST['display_name'];
		$pwd = $_POST['pwd'];
		$sum1 = $_POST['sum1'];
		$sum2 = $_POST['sum2'];
		$result = $_POST['result'];
		
		$existing_username = $wpdb->get_var($wpdb->prepare("SELECT user_login FROM ".$wpdb->prefix."users WHERE user_login = '".$username."'"));
		if ($existing_username) {
			$html .= 'Sorry, that username is already taken.';
		} else {
			$existing_email = $wpdb->get_var($wpdb->prepare("SELECT user_email FROM ".$wpdb->prefix."users WHERE user_email = '".$youremail."'"));
			if ($existing_email) {
				$html .= __('That email is already in use by another member, please enter another one.', 'wp-symposium');
			} else {
				if ($result != $sum1 + $sum2) {
					$html .= __('Please enter the sum of the two numbers.', 'wp-symposium');
				} else {
					
					// Store wp_user
					$wpdb->query( $wpdb->prepare( "
						INSERT INTO ".$wpdb->prefix."users
						( 	user_login,
							user_nicename,
							user_email, 
							display_name,
							user_pass,
							user_registered
						)
						VALUES ( %s, %s, %s, %s, %s, %s )", 
				        array(
				        	$username, 
				        	$username, 
				        	$youremail,
				        	$display_name,
				        	wp_hash_password($pwd),
				        	date("Y-m-d H:i:s")
				        	) 
				        ) );	
					$new_id = $wpdb->insert_id;
					
					// Store wp_usermeta
				    list($firstname, $lastname) = explode(' ', $display_name); 
					$wpdb->query( $wpdb->prepare( "INSERT INTO ".$wpdb->prefix."usermeta ( user_id, meta_key, meta_value ) VALUES ( %d, %s, %d )", array($new_id, 'wp_user_level', 0) ) );	
					$wpdb->query( $wpdb->prepare( "INSERT INTO ".$wpdb->prefix."usermeta ( user_id, meta_key, meta_value ) VALUES ( %d, %s, %s )", array($new_id, 'wp_capabilities', 'a:1:{s:10:"subscriber";s:1:"1";}') ) );	
					$wpdb->query( $wpdb->prepare( "INSERT INTO ".$wpdb->prefix."usermeta ( user_id, meta_key, meta_value ) VALUES ( %d, %s, %s )", array($new_id, 'first_name', $firstname) ) );	
					$wpdb->query( $wpdb->prepare( "INSERT INTO ".$wpdb->prefix."usermeta ( user_id, meta_key, meta_value ) VALUES ( %d, %s, %s )", array($new_id, 'last_name', $lastname) ) );	
					$wpdb->query( $wpdb->prepare( "INSERT INTO ".$wpdb->prefix."usermeta ( user_id, meta_key, meta_value ) VALUES ( %d, %s, %s )", array($new_id, 'nickname', $username) ) );	
					
					wp_login($username, $pwd, true);
			        wp_setcookie($username, $pwd, true);
			        wp_set_current_user($new_id, $username);
			        
			        // Email admin
			        $body = $display_name." (".$youremail.") ".__('has joined', 'wp-symposium')." ".get_bloginfo('name');
					symposium_sendmail(get_bloginfo('admin_email'), __('New Member', 'wp-symposium'), $body);
					
					// Email new member (if there is a message to send)
					$message = $wpdb->get_var($wpdb->prepare("SELECT register_message FROM ".$wpdb->prefix.'symposium_config'));
					if ($message != '') {
						symposium_sendmail($youremail, sprintf(__('Welcome to %s', 'wp-symposium'), get_bloginfo('name')), str_replace("\n", "<br />", $message));
					}
	        
					header("Location: ".symposium_get_url('profile')."?view=personal");
					exit;
				}
			}
		}	
	}
	$params = "?msg=".$html."&username=".$username."&youremail=".str_replace("@", "!", $youremail)."&display_name=".$display_name;
	header("Location: ".symposium_get_url('register').$params);
	exit;

}
	
?>