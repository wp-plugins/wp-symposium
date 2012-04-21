<?php

include_once('../../../../wp-config.php');

global $wpdb, $current_user;
wp_get_current_user();

// Change online status
if ($_POST['action'] == 'symposium_status') {

	global $wpdb, $current_user;
   	$status = $_POST['status'];
   	
   	if ($status == 'true') {

		update_symposium_meta($current_user->ID, 'status', 'offline');

   	} else {

		update_symposium_meta($current_user->ID, 'status', '');

   	}
   	
   	echo "OK";
   	exit;
	
}

// Clear chatoom
if ($_POST['action'] == 'symposium_clear_chatroom') {

	global $wpdb;

	if (is_user_logged_in()) {
	
   		$sql = "DELETE FROM ".$wpdb->base_prefix."symposium_chat WHERE chat_to = -1";
		$rows_affected = $wpdb->query( $wpdb->prepare($sql) );
	
		echo "OK";
		
	}
	
	exit;

}

// Add to chatroom
if ($_POST['action'] == 'symposium_addchatroom') {

   	global $wpdb, $current_user;

	if (is_user_logged_in()) {
   	
	   	$chat_from = $current_user->ID;
	   	$chat_message = $_POST['chat_message'];
		$chat_message = str_replace('<', '&lt;', $chat_message);
		$chat_message = str_replace('>', '&gt;', $chat_message);
	   	$r = '';

		if ( $rows_affected = $wpdb->insert( $wpdb->base_prefix . "symposium_chat", array( 
			'chat_to' => -1, 
			'chat_from' => $chat_from, 
			'chat_message' => $chat_message,
			'chat_timestamp' => date("Y-m-d H:i:s") 
		) ) ) {
			$r .= stripslashes($chat_message).' '.$wpdb->last_query;
		} else {
			$r .= $wpdb->last_query;
		}

		// Update as activity
		update_symposium_meta($current_user->ID, 'last_activity', "'".date("Y-m-d H:i:s")."'");
	   	
	   	echo $r;
	
	}
	
   	exit;

}

// Get friends online
if ($_POST['action'] == 'symposium_getfriendsonline') {
	
	global $wpdb, $current_user;

	if (is_user_logged_in()) {

	   	$inactive = $_POST['inactive'];
	   	$offline = $_POST['offline'];
	   	$me = $current_user->ID;
		$time_now = time();
		$use_chat = $_POST['use_chat'];
		$friends_online = 0;
		$plugin = WP_PLUGIN_URL.'/wp-symposium';
   	
	   	$return = '';

		if (!get_option('symposium_wps_panel_all')) {
			$sql = "SELECT u.ID, u.display_name
				FROM ".$wpdb->base_prefix."users u
				LEFT JOIN ".$wpdb->base_prefix."symposium_friends f ON u.ID = f.friend_to
				WHERE u.ID != ".$me."
				  AND f.friend_accepted = 'on' AND f.friend_from = ".$me;
		} else {
			$sql = "SELECT u.ID, u.display_name
				FROM ".$wpdb->base_prefix."users u
				WHERE u.ID != ".$me;
		}	
		$friends_list = $wpdb->get_results($sql);

		if ($friends_list) {
			$friends_array = array();
			foreach ($friends_list as $friend) {

				$add = array (	
					'ID' => $friend->ID,
					'display_name' => $friend->display_name,
					'last_activity' => get_symposium_meta($friend->ID, 'last_activity'),
					'status' => get_symposium_meta($friend->ID, 'status')
				);
				
				array_push($friends_array, $add);
			}
			$friends = sub_val_sort($friends_array, 'last_activity', false);
			
		} else {
			
			$friends = false;
		}

		if ($friends) {			
			foreach ($friends as $friend) {
			
				$time_now = time();
				if ($friend['last_activity'] && $friend['status'] != 'offline') {
					$last_active_minutes = convert_datetime($friend['last_activity']);
					$last_active_minutes = floor(($time_now-$last_active_minutes)/60);
				} else {
					$last_active_minutes = 999999999;
				}
	
				$return .= "<div style='clear:both; margin-top:4px; overflow: auto;'>";		
					$return .= "<div style='float: left; width:15px; padding-left:4px;'>";
						if ($last_active_minutes >= $offline) {
							$return .= "<img src='".get_option('symposium_images')."/loggedout.gif' alt='Logged Out'>";
						} else {
							$friends_online++;
							if ($last_active_minutes >= $inactive) {
								$return .= "<img src='".get_option('symposium_images')."/inactive.gif' alt='Inactive'>";
							} else {
								$return .= "<img src='".get_option('symposium_images')."/online.gif' alt='Online'>";
							}
						}
					$return .= "</div>";
					$return .= "<div>";
						if ( $use_chat != 'on' || get_option('symposium_wps_lite') ) {
							if (function_exists('symposium_profile')) {	
								$return .= "<a class='symposium_offline_name' href='".symposium_get_url('profile')."?uid=".$friend['ID']."'>";
								$return .= "<span title='".$friend['ID']."'>".$friend['display_name']."</span>";
								$return .= "</a>";
							}
						} else {
							$return .= "<span class='symposium_online_name' title='".$friend['ID']."'>".$friend['display_name']."</span>";
						}
					$return .= "</div>";
				$return .= "</div>";
			}
		}

		echo $friends_online."[split]".$return;
	
	}
	
	exit;
	
}

// Get friend requests
if ($_POST['action'] == 'symposium_friendrequests') {

   	global $wpdb, $current_user;	
   	$me = $current_user->ID;

	if (is_user_logged_in()) {

		$sql = "SELECT COUNT(*) FROM ".$wpdb->base_prefix."symposium_friends f WHERE f.friend_to = %d AND f.friend_accepted != 'on'";
		$pending = $wpdb->get_var($wpdb->prepare($sql, $me));
	
		echo $pending;
		
	}
	
	exit;

}

// Get count of unread mail
if ($_POST['action'] == 'symposium_getunreadmail') {

   	global $wpdb, $current_user;	

	if (is_user_logged_in()) {

	   	$me = $current_user->ID;
	   	$sql = "SELECT COUNT(*) FROM ".$wpdb->base_prefix.'symposium_mail'." WHERE mail_to = %d AND mail_in_deleted != 'on' AND mail_read != 'on'";
		$unread_in = $wpdb->get_var($wpdb->prepare($sql, $me));
	
		echo $unread_in;
		
	}
	
	exit;
}

// Get chat for updates
if ($_POST['action'] == 'symposium_getchat') {

   	global $wpdb, $current_user;

	if (is_user_logged_in()) {
	
	   	$inactive = $_POST['inactive'];
	   	$offline = $_POST['offline'];
	   	$me = $current_user->ID;
		$time_now = time();
   	
	   	$results = '';
   	
	   	// clear rogue chats
	   	$sql = "DELETE FROM ".$wpdb->base_prefix."symposium_chat WHERE chat_to = 0 OR chat_from = 0";
		$rows_affected = $wpdb->query( $wpdb->prepare($sql) );

		// get messages
		$sql = "SELECT c.chid, c.chat_from, c.chat_to, c.chat_message, c.chat_timestamp, u1.display_name AS fromname, u2.display_name AS toname ";
		$sql .= "FROM ".$wpdb->base_prefix."symposium_chat c ";
		$sql .= "LEFT JOIN ".$wpdb->base_prefix."users u1 ON c.chat_from = u1.ID ";
		$sql .= "LEFT JOIN ".$wpdb->base_prefix."users u2 ON c.chat_to = u2.ID ";
		$sql .= "WHERE (";
		$sql .= "chat_from = %d";
		$sql .= " OR chat_to = %d";
		$sql .= ")";
		$sql .= " AND chat_to > 0 ";
		$sql .= "ORDER BY chid";
		
		$chats_list = $wpdb->get_results($wpdb->prepare($sql, $me, $me));
		
		if ($chats_list) {
			$chat_array = array();
			foreach ($chats_list as $chat) {

				$add = array (	
					'chid' => $chat->chid,
					'chat_from' => $chat->chat_from,
					'chat_to' => $chat->chat_to,
					'chat_message' => $chat->chat_message,
					'chat_timestamp' => $chat->chat_timestamp,
					'fromname' => $chat->fromname,
					'toname' => $chat->toname,
					'fromlast' => get_symposium_meta($chat->chat_from, 'last_activity'),
					'status' => get_symposium_meta($chat->chat_from, 'status'),
					'tolast' => get_symposium_meta($chat->chat_to, 'last_activity')
				);
				
				array_push($chat_array, $add);
			}
			$chats = sub_val_sort($chat_array, 'chid');
			
		} else {
			
			$chats = false;
		}		
		
		
		if ($chats) {
			
			foreach ($chats as $chat) {

				$results .= $chat['chid']."[|]".$chat['chat_from'].'[|]'.$chat['chat_to'].'[|]';
				$last_message = $chat['chid']."[|]".$chat['chat_from'].'[|]'.$chat['chat_to'].'[|]';


					if ($chat['chat_from'] == $me) {
						$results .= $chat['toname']."[|]";
						$last_message .= $chat['toname']."[|]";
						$results .= "<div>";
						$results .= "<div style='clear:both;color:#006; font-style:normal;float: left;'>";
					} else {
						$results .= $chat['fromname']."[|]";
						$last_message .= $chat['fromname']."[|]";
						$results .= "<div>";
						$results .= "<div style='clear:both;color:#600; font-style:normal;float: left;'>";
					}
	
					$results .= stripslashes($chat['chat_message']);
					$results .= '</div>';
					$results .= "<div style='clear:both; float:right; color:#aaa; font-style:italic;'>";
					if ($chat['chat_from'] == $current_user->ID) {
						$results .= __("You", "wp-symposium");
					} else {
						$results .= $chat['fromname'];
					}
					$results .= '</div>';
			
				$results .= "<br style='clear:both;' /></div>";
				$results .= '[|]';
				
				$last_message .= '<div style="color:#aaa">'.__('Last message', 'wp-symposium').' '.symposium_time_ago($chat['chat_timestamp']).'.</div>[|]';
				

				if ($chat['chat_from'] == $me) {
					$last_active_minutes = convert_datetime($chat['tolast']);
				} else {

					if ($chat['fromlast'] && $chat['status'] != 'offline') {
						$last_active_minutes = convert_datetime($chat['fromlast']);
					} else {
						$last_active_minutes = 999999999;
					}
				}
				$last_active_minutes = floor(($time_now-$last_active_minutes)/60);			
				if ($last_active_minutes >= $offline) {
					$results .= "loggedout[split]";
					$last_message .= "loggedout[split]";
				} else {
					if ($last_active_minutes >= $inactive) {
						$results .= "inactive[split]";
						$last_message .= "inactive[split]";
					} else {
						$results .= "online[split]";
						$last_message .= "online[split]";
					}
				}
			
				// Only show 'last message' if over 60 seconds
				if ((time() - strtotime($chat['chat_timestamp'])) < 60) {
					$last_message = "";
				}
			
			}

			$results = $results.$last_message;


		}

	   	echo $results;
	
	}
	
   	exit;
	
}

// Get chatroom for updates
if ($_POST['action'] == 'symposium_getchatroom') {

   	global $wpdb, $current_user;

	if (is_user_logged_in()) {

		$use_chat = $_POST['use_chat'];	
	   	$inactive = $_POST['inactive'];
	   	$offline = $_POST['offline'];
   	
	   	// clear rogue chats
	   	$sql = "DELETE FROM ".$wpdb->base_prefix."symposium_chat WHERE chat_to = 0 OR chat_from = 0";
		$rows_affected = $wpdb->query( $wpdb->prepare($sql) );

		// get messages
		$sql = "SELECT c.chid, c.chat_from, c.chat_to, c.chat_message, c.chat_timestamp, u1.display_name AS fromname, u2.display_name AS toname ";
		$sql .= "FROM ".$wpdb->base_prefix."symposium_chat c ";
		$sql .= "LEFT JOIN ".$wpdb->base_prefix."users u1 ON c.chat_from = u1.ID ";
		$sql .= "LEFT JOIN ".$wpdb->base_prefix."users u2 ON c.chat_to = u2.ID ";
		$sql .= "WHERE chat_to = -1 ";
		$sql .= "ORDER BY chid DESC ";
		$sql .= "LIMIT 0,30";

		$c = 0;
		$time_now = time();
		$chats_list = $wpdb->get_results($sql);
		
		if ($chats_list) {
			$chat_array = array();
			foreach ($chats_list as $chat) {

				$add = array (	
					'chid' => $chat->chid,
					'chat_from' => $chat->chat_from,
					'chat_to' => $chat->chat_to,
					'chat_message' => $chat->chat_message,
					'chat_timestamp' => $chat->chat_timestamp,
					'fromname' => $chat->fromname,
					'toname' => $chat->toname,
					'fromlast' => get_symposium_meta($chat->chat_from, 'last_activity'),
					'status' => get_symposium_meta($chat->chat_from, 'status'),
					'tolast' => get_symposium_meta($chat->chat_to, 'last_activity')
				);
				
				array_push($chat_array, $add);
			}
			$chatlist = sub_val_sort($chat_array, 'chid');
			
		} else {
			
			$chatlist = false;
		}		
	
		$last_chat_chid = '';
		$last_chat_from = '';
	
		if ($chatlist) {

		   	$results = '';

			foreach ($chatlist as $chat) {
			
				$c++;

				if ($chat['fromlast'] && $chat['status'] != 'offline') {
					$last_active_minutes = convert_datetime($chat['from']);
				} else {
					$last_active_minutes = 999999999;
				}

				$last_active_minutes = floor(($time_now-$last_active_minutes)/60);			
				if ($last_active_minutes >= $offline) {
					$status_img = get_option('symposium_images').'/loggedout.gif';
				} else {
					if ($last_active_minutes >= $inactive) {
						$status_img = get_option('symposium_images').'/inactive.gif';
					} else {
						$status_img = get_option('symposium_images').'/online.gif';
					}
				}
			
				$last_chat_from = $chat['chat_from'];
				$last_chat_chid = $chat['chid'];
									
				$results .= "<div>";
				if ($c&1) {
					$results .= "<div style='clear:both;color:#006; font-style:normal;float: left;'>";
				} else {
					$results .= "<div style='clear:both;color:#600; font-style:normal;float: left;'>";
				}
				$results .= symposium_make_url(stripslashes($chat['chat_message']));
				$results .= "</div>";

				$results .= "<div style='clear:both; float:right; color:#aaa; font-style:italic;'>";
				$results .= "<img src='".$status_img."' title='".$status_img."' />&nbsp;";
				$results .= $chat['fromname'];
				$results .= ", ".symposium_time_ago($chat['chat_timestamp']);
				$results .= '</div>';

				$results .= "<br style='clear:both;' /></div>";

			}

			// Check for banned words
			$chatroom_banned = get_option('symposium_chatroom_banned');
			if ($chatroom_banned != '') {
				$badwords = $pieces = explode(",", $chatroom_banned);
			
				 for($i=0;$i < sizeof($badwords);$i++){
				 	if (strpos(' '.$results.' ', $badwords[$i])) {
					 	$results=eregi_replace($badwords[$i], "***", $results);
				 	}
				 }
			}

		}

	   	echo $last_chat_from."[split]".$last_chat_chid."[split]".$results;
	
	}
	
   	exit;
	
}


// Add to chat
if ($_POST['action'] == 'symposium_addchat') {

   	global $wpdb, $current_user;
   	
	if (is_user_logged_in()) {

	   	$chat_to = $_POST['chat_to'];
	   	$chat_from = $current_user->ID;
	   	$chat_message = $_POST['chat_message'];
	   	$r = '';
   	
	   	$sql = "DELETE FROM ".$wpdb->base_prefix."symposium_chat WHERE chat_message = '[closed-%d]' AND ( (chat_from = %d AND chat_to = %d) OR (chat_from = %d AND chat_to = %d) )";
		$rows_affected = $wpdb->query( $wpdb->prepare($sql, $chat_to, $chat_from, $chat_to, $chat_to, $chat_from) );
	
		if ($rows_affected === false) {
			$r .= $wpdb->last_query;
		}

		if ( $rows_affected = $wpdb->insert( $wpdb->base_prefix . "symposium_chat", array( 
			'chat_to' => $chat_to, 
			'chat_from' => $chat_from, 
			'chat_message' => $chat_message,
			'chat_timestamp' => date("Y-m-d H:i:s") 
		) ) ) {
			$r.= stripslashes($chat_message);
		} else {
			$r .= $wpdb->last_query;
		}

		// Update as activity
		update_symposium_meta($current_user->ID, 'last_activity', "'".date("Y-m-d H:i:s")."'");
   	
	   	echo $r;
	
	}
	
   	exit;

}

// Re-open chat
if ($_POST['action'] == 'symposium_reopenchat') {

   	global $wpdb, $current_user;

	if (is_user_logged_in()) {

	   	$chat_to = $_POST['chat_to'];
	   	$chat_from = $current_user->ID;

		// clear the closed flag
	   	$sql = "DELETE FROM ".$wpdb->base_prefix."symposium_chat WHERE chat_message = '[closed-%d]' AND ( (chat_from = %d AND chat_to = %d) OR (chat_from = %d AND chat_to = %d) )";
		$wpdb->query( $wpdb->prepare($sql, $chat_from, $chat_from, $chat_to, $chat_to, $chat_from) );

		return $chat_to;
		
	}
	
	exit;
}

// Open chat
if ($_POST['action'] == 'symposium_openchat') {
	
   	global $wpdb, $current_user;

	if (is_user_logged_in()) {

	   	$chat_to = $_POST['chat_to'];
	   	$chat_from = $current_user->ID;
	   	$r = '';
   	
		// check to see if they are already chatting
		if ($wpdb->query( $wpdb->prepare("SELECT chid FROM ".$wpdb->base_prefix."symposium_chat WHERE (chat_from = ".$chat_from." AND chat_to = ".$chat_to.") OR (chat_from = ".$chat_to." AND chat_to = ".$chat_from.")"))) {

			// clear the closed flag
		   	$sql = "DELETE FROM ".$wpdb->base_prefix."symposium_chat WHERE chat_message = '[closed-%d]' AND ( (chat_from = %d AND chat_to = %d) OR (chat_from = %d AND chat_to = %d) )";
			$wpdb->query( $wpdb->prepare($sql, $chat_from, $chat_from, $chat_to, $chat_to, $chat_from) );
			$r .= $chat_to;

		} else {

			if ( $rows_affected = $wpdb->insert( $wpdb->base_prefix . "symposium_chat", array( 
				'chat_to' => $chat_to, 
				'chat_from' => $chat_from, 
				'chat_message' => '[start]',
				'chat_timestamp' => date("Y-m-d H:i:s") 
			) ) ) {
			
				$display_name = $wpdb->get_var($wpdb->prepare("SELECT display_name FROM ".$wpdb->base_prefix."users WHERE ID = ".$chat_to));
				$r .= "OK[split]".$chat_to."[split]".$display_name;
			
			}
		}
	
		echo $r;
		
	}
	
	exit;

}

// Close chat
if ($_POST['action'] == 'symposium_closechat') {
	
   	global $wpdb, $current_user;

	if (is_user_logged_in()) {

		$chat_from = $current_user->ID;
		$chat_to = $_POST['chat_to'];
		$r = '';

		// has other person closed the window?
		$sql = "SELECT COUNT(*) FROM ".$wpdb->base_prefix."symposium_chat WHERE (chat_from = %d AND chat_to = %d) AND INSTR(chat_message, '[closed-%d]')";
		if ( $wpdb->get_var($wpdb->prepare($sql, $chat_to, $chat_from, $chat_to)) ) {

			$sql = "DELETE FROM ".$wpdb->base_prefix."symposium_chat WHERE (chat_from = %d AND chat_to = %d) OR (chat_from = %d AND chat_to = %d)";
			if ($wpdb->query( $wpdb->prepare($sql, $chat_from, $chat_to, $chat_to, $chat_from) ) ) {
				$r .= 'Cleared chat '.$wpdb->last_query;
			} else {
				$r .= $wpdb->last_query." ".$sql;
			}

		} else {

			if ( $rows_affected = $wpdb->insert( $wpdb->base_prefix . "symposium_chat", array( 
				'chat_to' => $chat_to, 
				'chat_from' => $chat_from, 
				'chat_message' => '[closed-'.$chat_from.']',
				'chat_timestamp' => date("Y-m-d H:i:s") 
			) ) ) {
				$r .= 'Closed chat '.$wpdb->last_query." ".$sql;
			} else {
				$r.= $wpdb->last_query;
			}

		}
	
		echo $r;
		
	}
	
	exit;

}



?>