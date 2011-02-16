<?php

include_once('../../../../wp-config.php');
include_once('../../../../wp-includes/wp-db.php');
include_once('../symposium_functions.php');

global $wpdb, $current_user;
wp_get_current_user();

// Clear chatoom
if ($_POST['action'] == 'symposium_clear_chatroom') {

	global $wpdb;
	
   	$sql = "DELETE FROM ".$wpdb->base_prefix."symposium_chat WHERE chat_to = -1";
	$rows_affected = $wpdb->query( $wpdb->prepare($sql) );
	
	echo "OK";
	exit;

}

// Add to chatroom
if ($_POST['action'] == 'symposium_addchatroom') {

   	global $wpdb, $current_user;
   	
   	$chat_from = $_POST['chat_from'];
   	$chat_message = $_POST['chat_message'];
   	$r = '';

	if ( $rows_affected = $wpdb->insert( $wpdb->base_prefix . "symposium_chat", array( 
		'chat_to' => -1, 
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
   	exit;

}

// Get friends online
if ($_POST['action'] == 'symposium_getfriendsonline') {
	
	global $wpdb;
   	$inactive = $_POST['inactive'];
   	$offline = $_POST['offline'];
   	$me = $_POST['me'];
	$time_now = time();
	$use_chat = $_POST['use_chat'];
	$friends_online = 0;
	$plugin = WP_PLUGIN_URL.'/wp-symposium';
   	
   	$return = '';

	$sql = "SELECT f.*, m.last_activity, u.display_name, u.ID FROM ".$wpdb->base_prefix."symposium_friends f LEFT JOIN ".$wpdb->base_prefix."symposium_usermeta m ON m.uid = f.friend_to LEFT JOIN ".$wpdb->base_prefix."users u ON u.ID = f.friend_to WHERE f.friend_accepted = 'on' AND f.friend_from = ".$me." ORDER BY last_activity DESC";
	
	$friends = $wpdb->get_results($sql);
	
	foreach ($friends as $friend) {
		
		$time_now = time();
		if ($friend->last_activity) {
			$last_active_minutes = convert_datetime($friend->last_activity);
		} else {
			$last_active_minutes = 999999999;
		}
		$last_active_minutes = floor(($time_now-$last_active_minutes)/60);
		
		$return .= "<div style='clear:both; margin-top:4px; overflow: auto;'>";		
			$return .= "<div style='float: left; width:15px; padding-left:4px;'>";
				if ($last_active_minutes >= $offline) {
					$return .= "<img src='".$plugin."/images/loggedout.gif' alt='Logged Out'>";
				} else {
					$friends_online++;
					if ($last_active_minutes >= $inactive) {
						$return .= "<img src='".$plugin."/images/inactive.gif' alt='Inactive'>";
					} else {
						$return .= "<img src='".$plugin."/images/online.gif' alt='Online'>";
					}
				}
			$return .= "</div>";
			$return .= "<div>";
				if ( $use_chat != 'on' ) {
					if (function_exists('symposium_profile')) {	
						$return .= "<a class='symposium_offline_name' href='".symposium_get_url('profile')."?uid=".$friend->ID."'>";
						$return .= "<span title='".$friend->friend_to."'>".$friend->display_name."</span>";
						$return .= "</a>";
					}
				} else {
					$return .= "<span class='symposium_online_name' title='".$friend->friend_to."'>".$friend->display_name."</span>";
				}
			$return .= "</div>";
		$return .= "</div>";
	}

	echo $friends_online."[split]".$return;
	
	exit;
	
}

// Get friend requests
if ($_POST['action'] == 'symposium_friendrequests') {

   	global $wpdb;	
   	$me = $_POST['me'];
	$sql = "SELECT COUNT(*) FROM ".$wpdb->base_prefix."symposium_friends f WHERE f.friend_to = ".$me." AND f.friend_accepted != 'on'";
	$pending = $wpdb->get_var($sql);
	
	echo $pending;
	exit;

}

// Get count of unread mail
if ($_POST['action'] == 'symposium_getunreadmail') {

   	global $wpdb;	
   	$me = $_POST['me'];
   	$sql = "SELECT COUNT(*) FROM ".$wpdb->base_prefix.'symposium_mail'." WHERE mail_to = ".$me." AND mail_in_deleted != 'on' AND mail_read != 'on'";
	$unread_in = $wpdb->get_var($sql);
	
	echo $unread_in;
	exit;
}

// Get chat for updates
if ($_POST['action'] == 'symposium_getchat') {

   	global $wpdb;
	
   	$inactive = $_POST['inactive'];
   	$offline = $_POST['offline'];
   	$me = $_POST['me'];
	$time_now = time();
   	
   	$results = '';
   	
   	// clear rogue chats
   	$sql = "DELETE FROM ".$wpdb->base_prefix."symposium_chat WHERE chat_to = 0 OR chat_from = 0";
	$rows_affected = $wpdb->query( $wpdb->prepare($sql) );

	// get messages
	$sql = "SELECT c.*, u1.display_name AS fromname, u2.display_name AS toname ";
	$sql .= "FROM ".$wpdb->base_prefix."symposium_chat c ";
	$sql .= "LEFT JOIN ".$wpdb->base_prefix."users u1 ON c.chat_from = u1.ID ";
	$sql .= "LEFT JOIN ".$wpdb->base_prefix."users u2 ON c.chat_to = u2.ID ";
	$sql .= "WHERE (";
	$sql .= "chat_from = ".$me;
	$sql .= " OR chat_to = ".$me;
	$sql .= ")";
	$sql .= " AND chat_to > 0 ";
	$sql .= "ORDER BY chid DESC";
	
	$chats = $wpdb->get_results($sql);
	if ($chats) {
		foreach ($chats as $chat) {

			$results .= $chat->chid."[|]".$chat->chat_from.'[|]'.$chat->chat_to.'[|]';
			if ($chat->chat_from == $me) {
				$results .= '<div style="border-bottom:1px solid #aaa; color: #006">';
			} else {
				$results .= '<div style="border-bottom:1px solid #aaa; color: #600">';
			}
			$results .= stripslashes($chat->chat_message).'<br />';

			$results .= '<span style="float: right; color: #aaa; font-style:italic;">'.stripslashes($chat->fromname).'</span><br style="clear:both;" /></div>';
			$results .= '[|]';
			
			if ($chat->chat_to == $me) {
				$results .= $chat->fromname."[split]";
			} else {
				$results .= $chat->toname."[split]";
			}

		}

		$results = $results;


	}

   	echo $results;
   	exit;
	
}

// Get chatroom for updates
if ($_POST['action'] == 'symposium_getchatroom') {

   	global $wpdb, $current_user;

	$use_chat = $_POST['use_chat'];	
   	$inactive = $_POST['inactive'];
   	$offline = $_POST['offline'];
   	
   	// clear rogue chats
   	$sql = "DELETE FROM ".$wpdb->base_prefix."symposium_chat WHERE chat_to = 0 OR chat_from = 0";
	$rows_affected = $wpdb->query( $wpdb->prepare($sql) );

	// get messages
	$sql = "SELECT c.*, u1.display_name AS fromname ";
	$sql .= "FROM ".$wpdb->base_prefix."symposium_chat c ";
	$sql .= "LEFT JOIN ".$wpdb->base_prefix."users u1 ON c.chat_from = u1.ID ";
	$sql .= "WHERE chat_to = -1 ";
	$sql .= "ORDER BY chid DESC ";
	$sql .= "LIMIT 0,30";

	$c = 0;
	$time_now = time();
	$chatlist = $wpdb->get_results($sql);
	
	$results = $wpdb->last_query;
	
	$last_chat_chid = '';
	$last_chat_from = '';
	
	if ($chatlist) {

	   	$results = '';

		foreach ($chatlist as $chat) {
			
			$c++;

			if ($last_chat_from == '') {
				$last_chat_from = $chat->chat_from;
				$last_chat_chid = $chat->chid;
			}
									
			$results .= "<div style='border-bottom:1px solid #aaa;'>";
			if ($c&1) {
				$results .= "<div style='clear:both;color:#006; font-style:normal;float: left;'>";
			} else {
				$results .= "<div style='clear:both;color:#600; font-style:normal;float: left;'>";
			}
			$results .= symposium_make_url(stripslashes($chat->chat_message));
			$results .= "</div>";

			$results .= "<div style='clear:both; float:right; color:#aaa; font-style:italic;'>";
			$results .= $chat->fromname;
			$results .= '</div>';

			$results .= "<br style='clear:both;' /></div>";


		}

		// Check for banned words
		$chatroom_banned = $wpdb->get_var($wpdb->prepare("SELECT chatroom_banned FROM ".$wpdb->prefix."symposium_config"));
		if ($chatroom_banned != '') {
			$badwords = $pieces = explode(",", $chatroom_banned);
			
			 for($i=0;$i < sizeof($badwords);$i++){
			 	if (strpos($results, $badwords[$i])) {
				 	$results=eregi_replace($badwords[$i], "***", $results);
			 	}
			 }
		}

	}

   	echo $last_chat_from."[split]".$last_chat_chid."[split]".$results;
   	exit;
	
}


// Add to chat
if ($_POST['action'] == 'symposium_addchat') {

   	global $wpdb, $current_user;
   	
   	$chat_to = $_POST['chat_to'];
   	$chat_from = $_POST['chat_from'];
   	$chat_message = $_POST['chat_message'];
   	$r = '';
   	
   	$sql = "DELETE FROM ".$wpdb->base_prefix."symposium_chat WHERE chat_message = '[closed-".$chat_to."]' AND ( (chat_from = ".$chat_from." AND chat_to = ".$chat_to.") OR (chat_from = ".$chat_to." AND chat_to = ".$chat_from.") )";
	$rows_affected = $wpdb->query( $wpdb->prepare($sql) );
	
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
   	exit;

}

// Re-open chat
if ($_POST['action'] == 'symposium_reopenchat') {

   	global $wpdb;
   	$chat_to = $_POST['chat_to'];
   	$chat_from = $_POST['chat_from'];

	// clear the closed flag
   	$sql = "DELETE FROM ".$wpdb->base_prefix."symposium_chat WHERE chat_message = '[closed-".$chat_from."]' AND ( (chat_from = ".$chat_from." AND chat_to = ".$chat_to.") OR (chat_from = ".$chat_to." AND chat_to = ".$chat_from.") )";
	$wpdb->query( $wpdb->prepare($sql) );

	return $chat_to;
}

// Open chat
if ($_POST['action'] == 'symposium_openchat') {
	
   	global $wpdb;
   	$chat_to = $_POST['chat_to'];
   	$chat_from = $_POST['chat_from'];
   	$r = '';
   	
	// check to see if they are already chatting
	if ($wpdb->query( $wpdb->prepare("SELECT chid FROM ".$wpdb->base_prefix."symposium_chat WHERE (chat_from = ".$chat_from." AND chat_to = ".$chat_to.") OR (chat_from = ".$chat_to." AND chat_to = ".$chat_from.")"))) {

		// clear the closed flag
	   	$sql = "DELETE FROM ".$wpdb->base_prefix."symposium_chat WHERE chat_message = '[closed-".$chat_from."]' AND ( (chat_from = ".$chat_from." AND chat_to = ".$chat_to.") OR (chat_from = ".$chat_to." AND chat_to = ".$chat_from.") )";
		$wpdb->query( $wpdb->prepare($sql) );
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
	exit;

}

// Close chat
if ($_POST['action'] == 'symposium_closechat') {
	
   	global $wpdb;

	$chat_from = $_POST['chat_from'];
	$chat_to = $_POST['chat_to'];
	$r = '';

	// has other person closed the window?
	$sql = "SELECT COUNT(*) FROM ".$wpdb->base_prefix."symposium_chat WHERE (chat_from = ".$chat_to." AND chat_to = ".$chat_from.") AND INSTR(chat_message, '[closed-".$chat_to."]')";
	if ( $wpdb->get_var($wpdb->prepare($sql)) ) {

		$sql = "DELETE FROM ".$wpdb->base_prefix."symposium_chat WHERE (chat_from = ".$chat_from." AND chat_to = ".$chat_to.") OR (chat_from = ".$chat_to." AND chat_to = ".$chat_from.")";
		if ($wpdb->query( $wpdb->prepare($sql) ) ) {
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
	exit;

}

// Check for new mail, forum messages, etc
if ($_POST['action'] == 'checkForNotifications') {

   	global $wpdb, $current_user;
	wp_get_current_user();

	$return = '';
	
	$sql = "SELECT nid, notification_message FROM ".$wpdb->prefix."symposium_notifications WHERE notification_to = ".$current_user->ID." AND notification_shown != 'on'";
	$msgs = $wpdb->get_row($wpdb->prepare($sql));
	
	if ($msgs) {
		$return = $msgs->notification_message;
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix."symposium_notifications SET notification_shown = 'on' WHERE nid = ".$msgs->nid) );
	} else {
		$return = '';
	}
	
	echo $return;
	exit;
}


?>