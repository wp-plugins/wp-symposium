<?php

include_once('../../../../wp-config.php');
//include_once('../../../../wp-includes/wp-db.php');
//include_once('../symposium_functions.php');

// Compose - recipients list (autocomplete)
if (isset($_GET['term']) && $_GET['term'] != '') {
	
	global $wpdb;	
	$return_arr = array();
	$term = $_GET['term'];

	$list = $wpdb->get_results("SELECT u.ID, u.display_name, m.city, m.country FROM ".$wpdb->base_prefix."users u LEFT JOIN ".$wpdb->base_prefix."symposium_usermeta m ON u.ID = m.uid WHERE (u.display_name LIKE '".$term."%') OR (m.city LIKE '".$term."%') OR (m.country LIKE '".$term."%') OR (u.display_name LIKE '% %".$term."%') ORDER BY u.display_name");

	if ($list) {
		foreach ($list as $item) {
			$row_array['id'] = $item->ID;
			$row_array['value'] = $item->display_name;
			$row_array['label'] = $item->display_name;
			if ($item->city != '') {
				$row_array['city'] = $item->city;
			} else {
				$row_array['city'] = '';
			}
			if ($item->country != '') {
				if ($item->city != '') {
					$row_array['country'] = ', '.$item->country;
				} else {
					$row_array['country'] = $item->country;
				}
			} else {
				$row_array['country'] = '';
			}
			
	        array_push($return_arr,$row_array);
		}
	}

	echo json_encode($return_arr);
	exit;

}


// Get mail messages
if ($_POST['action'] == 'getBox') {
	
	$tray = $_POST["tray"];
	$term = $_POST["term"];

	if ($tray == "in") {
		$mail = $wpdb->get_results("SELECT m.*, u.display_name FROM ".$wpdb->base_prefix."symposium_mail m LEFT JOIN ".$wpdb->base_prefix."users u ON m.mail_from = u.ID WHERE mail_in_deleted != 'on' AND mail_to = ".$current_user->ID." AND (u.display_name LIKE '%".$term."%' OR mail_subject LIKE '%".$term."%' OR mail_message LIKE '%".$term."%') ORDER BY mail_mid DESC LIMIT 0,30");
	}

	$return_arr = array();	

	if ($mail) {
		foreach ($mail as $item)
		{
			if ($item->mail_read != "on") {
				$row_array['mail_read'] = "row";
			} else {
				$row_array['mail_read'] = "row_odd";
			}
			$row_array['mail_mid'] = $item->mail_mid;
			$row_array['mail_sent'] = symposium_time_ago($item->mail_sent);
			$row_array['mail_from'] = stripslashes(symposium_profile_link($item->mail_from));
			$row_array['mail_subject'] = stripslashes(symposium_bbcode_remove($item->mail_subject));
			$row_array['mail_subject'] = preg_replace(
			  "/(>|^)([^<]+)(?=<|$)/iesx",
			  "'\\1' . str_replace('" . $term . "', '<span class=\"symposium_search_highlight\">" . $term . "</span>', '\\2')",
			  $row_array['mail_subject']
			);
			$message = stripslashes($item->mail_message);
			if ( strlen($message) > 75 ) { $message = substr($message, 0, 75)."..."; }
			$message = preg_replace(
			  "/(>|^)([^<]+)(?=<|$)/iesx",
			  "'\\1' . str_replace('" . $term . "', '<span class=\"symposium_search_highlight\">" . $term . "</span>', '\\2')",
			  $message
			);
			$row_array['message'] = symposium_bbcode_remove($message);

	        array_push($return_arr,$row_array);

		}
	}

	echo json_encode($return_arr);

}
				
// Get single mail message
if ($_POST['action'] == 'getMailMessage') {

	global $wpdb, $current_user;
	wp_get_current_user();
	
	$mail_mid = $_POST['mid'];	
	$tray = $_POST['tray'];	
	
	$message = get_message($mail_mid, $tray);
	
	// Fetch new unread count
	$unread = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix.'symposium_mail'." WHERE mail_to = ".$current_user->ID." AND mail_".$tray."_deleted != 'on' AND mail_read != 'on'");

	echo $mail_mid."[split]".$unread."[split]".$tray."[split]".$message;
	exit;
}


?>

	