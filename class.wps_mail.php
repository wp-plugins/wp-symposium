<?php

// ******************************** WP SYMPOSIUM WPS MAIL CLASS ********************************

class wps_mail {

	public function __construct($id='') {
		global $current_user;
		$id != '' ? $id = $id : $id = $current_user->ID;
		$this->id = $id;											// Set the ID of this member's mail
	}

	/* Following methods provide get/set functionality ______________________________________ */
		
	function get_inbox_count() {
		global $wpdb;
		$sql = "SELECT COUNT(mail_mid) FROM ".$wpdb->base_prefix."symposium_mail WHERE mail_to = %d AND mail_in_deleted != 'on'";
		return $wpdb->get_var($wpdb->prepare($sql, $this->id));
	}
	
	function get_inbox($count=10, $start=0, $avatar_size=40, $term="", $order=true, $message_len=75) {
	    global $wpdb;
	    $mail_count = 1;
    	$return_arr = array();
    	$results_order = $order ? "DESC" : "";

	    $sql = "SELECT m.mail_mid, m.mail_from, m.mail_to, m.mail_read, m.mail_sent, m.mail_subject, m.mail_message, u.display_name
	    		FROM ".$wpdb->base_prefix."symposium_mail m
	    		INNER JOIN ".$wpdb->base_prefix."users u ON m.mail_from = u.ID
	    		WHERE m.mail_in_deleted != 'on'
	    		  AND m.mail_to = ".$this->id."
	    		ORDER BY m.mail_mid ".$results_order."
	    		LIMIT ".$start.",1000"; // Maximum 1,000 to reduce load on database
	 	$messages = $wpdb->get_results($wpdb->prepare($sql, $this->id));
	 	
	 	foreach ($messages AS $item) {
	 	    
			$row_array['mail_id'] = $item->mail_mid;
			$row_array['mail_from'] = $item->mail_from;
			$row_array['mail_to'] = $item->mail_to;
			$row_array['mail_read'] = $item->mail_read;
			$row_array['mail_sent'] = $item->mail_sent;
			$row_array['mail_subject'] = symposium_bbcode_remove(stripslashes($item->mail_subject));
			$row_array['mail_subject'] = preg_replace(
			  "/(>|^)([^<]+)(?=<|$)/iesx",
			  "'\\1' . str_replace('" . $term . "', '<span class=\"symposium_search_highlight\">" . $term . "</span>', '\\2')",
			  $row_array['mail_subject']
			);
			$row_array['mail_subject'] = stripslashes($row_array['mail_subject']);
			$row_array['mail_message'] = stripslashes($item->mail_message);
			if ( strlen($message) > $message_len ) { $message = substr($message, 0, $message_len)."..."; }
			$message = preg_replace(
			  "/(>|^)([^<]+)(?=<|$)/iesx",
			  "'\\1' . str_replace('" . $term . "', '<span class=\"symposium_search_highlight\">" . $term . "</span>', '\\2')",
			  $message
			);
			$row_array['display_name'] = $item->display_name;
			$row_array['display_name_link'] = stripslashes(symposium_profile_link($item->mail_from));
			$row_array['avatar'] = get_avatar($item->mail_from, $avatar_size);
			array_push($return_arr,$row_array);
			if ($mail_count++ == $count) break;
	 	}
	 	return $return_arr;
	}
	
	function get_message($mail_id) {
		if (is_numeric($mail_id)) {
			global $wpdb;
			$sql= "SELECT m.*, u.display_name FROM ".$wpdb->base_prefix."symposium_mail m
	    		INNER JOIN ".$wpdb->base_prefix."users u ON m.mail_from = u.ID
	    		WHERE m.mail_mid = %d";
		 	$message = $wpdb->get_row($wpdb->prepare($sql, $mail_id));		
		 	return $message;
		} else {
			return false;
		}
	}
	
	function set_as_read($mail_mid) {
		if (is_numeric($mail_mid)) {
			global $wpdb;
			$sql = "UPDATE ".$wpdb->base_prefix."symposium_mail SET mail_read = 'on' WHERE mail_mid = %d";
			$wpdb->query($wpdb->prepare($sql, $mail_mid));
			return true;
		} else {
			return false;
		}
	}
	
	function set_as_deleted($mail_mid) {
		if (is_numeric($mail_mid)) {
			global $wpdb, $current_user;
			if ( is_user_logged_in() ) {
				$sql = "SELECT mail_to, mail_from FROM ".$wpdb->base_prefix."symposium_mail WHERE mail_mid = %d";
				$tofrom = $wpdb->get_row($wpdb->prepare($sql, $mail_mid));
				if ($this->id == $tofrom->mail_to) {
					$sql = "UPDATE ".$wpdb->base_prefix."symposium_mail SET mail_in_deleted = 'on' WHERE mail_mid = %d";
					$wpdb->query($wpdb->prepare($sql, $mail_mid));
					return true;
				}
				if ($this->id == $tofrom->mail_from) {
					$sql = "UPDATE ".$wpdb->base_prefix."symposium_mail SET mail_sent_deleted = 'on' WHERE mail_mid = %d";
					$wpdb->query($wpdb->prepare($sql, $mail_mid));
				}
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	/* Following methods check for various conditions and return boolean value ______________________________________ */
	
}



?>