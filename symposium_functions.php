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

function symposium_safe_param($param) {
	$return = true;
	
	if (is_numeric($param) == FALSE) { $return = false; }
	if (strpos($param, ' ') != FALSE) { $return = false; }
	if (strpos($param, '%20') != FALSE) { $return = false; }
	if (strpos($param, ';') != FALSE) { $return = false; }
	if (strpos($param, '<script>') != FALSE) { $return = false; }
	
	return $return;
}

function symposium_pagination($total, $current, $url) {
	
	$r = '';

	$r .= '<div class="tablenav"><div class="tablenav-pages">';
	for ($i = 0; $i < $total; $i++) {
		if ($i == $current) {
            $r .= "<b>".($i+1)."</b> ";
        } else {
        	if ( ($i == 0) || ($i == $total-1) || ($i+1 == $current) || ($i+1 == $current+2) ) {
	            $r .= " <a href='".$url.($i+1)."'>".($i+1)."</a> ";
        	} else {
        		$r .= "...";
        	}
        }
	}
	$r .= '</div></div>';
	
	while ( strpos($r, "....") > 0) {
		$r = str_replace("....", "...", $r);
	}
	
	
	return $r;
}

function get_message($mail_mid, $del) {

	global $wpdb, $current_user;
	wp_get_current_user();

	if ($del == "in") {
		$mail = $wpdb->get_row("SELECT m.*, u.display_name FROM ".$wpdb->prefix."symposium_mail m LEFT JOIN ".$wpdb->prefix."users u ON m.mail_from = u.ID WHERE mail_mid = ".$mail_mid);
	} else {
		$mail = $wpdb->get_row("SELECT m.*, u.display_name FROM ".$wpdb->prefix."symposium_mail m LEFT JOIN ".$wpdb->prefix."users u ON m.mail_to = u.ID WHERE mail_mid = ".$mail_mid);
	}
	
	$styles = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."symposium_config");
	
	$mail_url = $wpdb->get_var($wpdb->prepare("SELECT mail_url FROM ".$wpdb->prefix . 'symposium_config'));

	$msg = "<div style='padding-bottom:10px; overflow:auto;'>";
	
		$msg .= "<div style='width:44px; margin-right: 5px'>";
			$msg .= get_avatar($mail->mail_from, 44);
		$msg .= "</div>";

		// Delete
		$msg .= "<div style='float:right'>";
		$msg .= "<form action='' method='POST'>";
		$msg .= "<input type='hidden' name='del".$del."' value=".$mail_mid." />";
		$msg .= '<input type="submit" class="button" onclick="jQuery(\'.pleasewait\').inmiddle().show();" value="'.__('Delete', 'wp-symposium').'" />';
		$msg .= "</form>";
		$msg .= "</div>";
		
		// Reply
		if ($del == "in") {
			$msg .= "<div style='clear:both;margin-top:-16px;float:right'>";
			$msg .= "<form action='' method='POST'>";
			$msg .= "<input type='hidden' name='reply_recipient' value=".$mail->mail_from." />";
			$msg .= "<input type='hidden' name='reply_mid' value=".$mail_mid." />";
			$msg .= '<input type="submit" class="button" onclick="jQuery(\'.pleasewait\').inmiddle().show();" value="'.__('Reply', 'wp-symposium').'" />';
			$msg .= "</form>";
			$msg .= "</div>";
		}
		
		$msg .= "<span style='font-family:".$styles->headingsfamily."; font-size:".$styles->headingssize."px; font-weight:bold;'>".stripslashes($mail->mail_subject)."</span><br />";
		if ($del == "in") {
			$msg .= __('From', 'wp-symposium')." ";
		} else {
			$msg .= __('To', 'wp-symposium')." ";
		}
		$msg .= stripslashes($mail->display_name)." ".symposium_time_ago($mail->mail_sent).".<br />";
		
	$msg .= "</div>";
	
	$msg .= "<div style='padding-top:10px'>";
	$msg .= stripslashes(str_replace(chr(13), "<br />", $mail->mail_message));
	$msg .= "</div>";
	
	// Mark as read
	if ($del == "in") {
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix."symposium_mail SET mail_read = 'on' WHERE mail_mid = ".$mail_mid." AND mail_to = ".$current_user->ID) );
	}

	$msg = symposium_smilies($msg);
	
	return $msg;
	
}

function symposium_pending_friendship($uid) {
   	global $wpdb, $current_user;
	wp_get_current_user();
	
	$sql = "SELECT * FROM ".$wpdb->prefix."symposium_friends WHERE (friend_accepted != 'on') AND (friend_from = ".$uid." AND friend_to = ".$current_user->ID." OR friend_to = ".$uid." AND friend_from = ".$current_user->ID.")";
	
	if ( $wpdb->get_var($wpdb->prepare($sql)) ) {
		return true;
	} else {
		return false;
	}

}

function symposium_friend_of($uid) {
   	global $wpdb, $current_user;
	wp_get_current_user();
	
	if ( $wpdb->get_var($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."symposium_friends WHERE (friend_accepted = 'on') AND (friend_from = ".$uid." AND friend_to = ".$current_user->ID." OR friend_to = ".$uid." AND friend_from = ".$current_user->ID.")")) ) {
		return true;
	} else {
		return false;
	}

}

function symposium_get_current_userlevel() {

   	global $wpdb, $current_user;
	wp_get_current_user();

	// Work out user level
	$user_level = 0; // Guest
	if (is_user_logged_in()) { $user_level = 1; } // Subscriber
	if (current_user_can('edit_posts')) { $user_level = 2; } // Contributor
	if (current_user_can('edit_published_posts')) { $user_level = 3; } // Author
	if (current_user_can('moderate_comments')) { $user_level = 4; } // Editor
	if (current_user_can('activate_plugins')) { $user_level = 5; } // Administrator
	
	return $user_level;

}

function symposium_get_url($plugin) {
	global $wpdb;
	$urls = $wpdb->get_row($wpdb->prepare("SELECT forum_url, members_url, register_url, mail_url, profile_url FROM ".$wpdb->prefix . 'symposium_config'));
	$return = false;
	if ($plugin == 'mail') {
		$return = $urls->mail_url;
	}
	if ($plugin == 'forum') {
		$return = $urls->forum_url;
	}
	if ($plugin == 'profile') {
		$return = $urls->profile_url;
	}
	if ($plugin == 'register') {
		$return = $urls->register_url;
	}
	if ($plugin == 'members') {
		$return = $urls->members_url;
	}
	return $return;
}

function symposium_alter_table($table, $action, $field, $format, $null, $default) {
	
	if ($action == "MODIFY") { $action = "MODIFY COLUMN"; }
	if ($default != "") { $default = "DEFAULT ".$default; }

	global $wpdb;	
	
	$success = false;

	$ok = '';
	$check = $wpdb->get_var("SELECT count(".$field.") FROM ".$wpdb->prefix."symposium_".$table);
	if ($check != '') { 
		$ok = 'exists';
		if ($check > 0) { $ok = 'same'; }
	}
	
	if ($action == "ADD") {
		if ($ok == 'exists' || $ok == 'same') {
			// Do Nothing
		} else {
			if ($format != 'text') {
			  	$wpdb->query("ALTER TABLE ".$wpdb->prefix."symposium_".$table." ".$action." ".$field." ".$format." ".$null." ".$default);
			} else {
			  	$wpdb->query("ALTER TABLE ".$wpdb->prefix."symposium_".$table." ".$action." ".$field." ".$format);
			}
		}			
	}

	if ($action == "MODIFY COLUMN") {
		if ($format != 'text') {
			$sql = "ALTER TABLE ".$wpdb->prefix."symposium_".$table." ".$action." ".$field." ".$format." ".$null." ".$default;
		} else {
			$sql = "ALTER TABLE ".$wpdb->prefix."symposium_".$table." ".$action." ".$field." ".$format;
		}
	  	$wpdb->query($sql);
	}
	
	return $success;

}

// Checks is user meta exists, and if not creates it
function update_symposium_meta($uid, $meta, $value) {
   	global $wpdb;
	
	if ($value == '') { $value = "''"; }
	
	// check if exists, and create record if not
	if ($wpdb->get_var($wpdb->prepare("SELECT * FROM ".$wpdb->prefix.'symposium_usermeta'." WHERE uid = ".$uid))) {
	} else {
		$wpdb->insert( $wpdb->prefix . "symposium_usermeta", array( 
			'uid' => $uid, 
			'sound' => 'chime.mp3',
			'soundchat' => 'tap.mp3',
			'bar_position' => 'bottom',
			'notify_new_messages' => 'on',
			'timezone' => 0,
			'share' => 'Friends only',
			'visible' => 'on',
			'wall_share' => 'Friends only'
			 ) );
	}

	// now update value
 	$r = false;
  	if ($wpdb->query("UPDATE ".$wpdb->prefix."symposium_usermeta SET ".$meta." = ".$value." WHERE uid = ".$uid)) {
  		$r = true;
  	}
  	
  	return $r;
}

// Get user meta data
function get_symposium_meta($uid, $meta) {
   	global $wpdb;

	// check if exists, and create record if not
	if ($wpdb->get_var($wpdb->prepare("SELECT * FROM ".$wpdb->prefix.'symposium_usermeta'." WHERE uid = ".$uid))) {
	} else {
		$wpdb->insert( $wpdb->prefix . "symposium_usermeta", array( 
			'uid' => $uid, 
			'sound' => 'chime.mp3',
			'soundchat' => 'tap.mp3',
			'bar_position' => 'bottom',
			'notify_new_messages' => 'on',
			'timezone' => 0,
			'share' => 'Friends only',
			'visible' => 'on',
			'wall_share' => 'Friends only'
			 ) );
			
	}

	if ($value = $wpdb->get_var($wpdb->prepare("SELECT ".$meta." FROM ".$wpdb->prefix.'symposium_usermeta'." WHERE uid = ".$uid)) ) {
		return $value;
	} else {
		return false; 	
	}
}

// Get user meta data row
function get_symposium_meta_row($uid) {
   	global $wpdb;

	$row = '';
	
	// check if exists, and create record if not
	if ($row = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix.'symposium_usermeta'." WHERE uid = ".$uid))) {
	} else {
		$wpdb->insert( $wpdb->prefix . "symposium_usermeta", array( 
			'uid' => $uid, 
			'sound' => 'chime.mp3',
			'soundchat' => 'tap.mp3',
			'bar_position' => 'bottom',
			'notify_new_messages' => 'on',
			'timezone' => 0,
			'share' => 'Friends only',
			'visible' => 'on',
			'wall_share' => 'Friends only'
			 ) );
			
	}
	
	if ($row == '') {
		if ($row = $wpdb->get_row($wpdb->prepare("SELECT ".$meta." FROM ".$wpdb->prefix.'symposium_usermeta'." WHERE uid = ".$uid)) ) {
			return $row;
		} else {
			return false; 	
		}
	} else {
		return $row;
	}
	
}

// Display array contents (for de-bugging only)
function symposium_displayArrayContentFunction($arrayname,$tab="&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp",$indent=0) {
 $curtab ="";
 $returnvalues = "";
 while(list($key, $value) = each($arrayname)) {
  for($i=0; $i<$indent; $i++) {
   $curtab .= $tab;
   }
  if (is_array($value)) {
   $returnvalues .= "$curtab$key : Array: <br />$curtab{<br />\n";
   $returnvalues .= symposium_displayArrayContentFunction($value,$tab,$indent+1)."$curtab}<br />\n";
   }
  else $returnvalues .= "$curtab$key => $value<br />\n";
  $curtab = NULL;
  }
 return $returnvalues;
}

// Add notification
function symposium_add_notification($msg, $recipient) {
	global $wpdb;
	if ( $wpdb->insert( $wpdb->prefix . "symposium_notifications", array( 
		'notification_to' => $recipient, 
		'notification_message' => $msg
	 	) ) ) {
	 }
}

// Link to profile if pluing activated
function symposium_profile_link($uid) {
	global $wpdb;

	$display_name = $wpdb->get_var($wpdb->prepare("SELECT display_name FROM ".$wpdb->prefix."users WHERE ID = ".$uid));
	if (function_exists('symposium_profile')) {
		$profile_url = $wpdb->get_var($wpdb->prepare("SELECT profile_url FROM ".$wpdb->prefix."symposium_config"));
		$html = '<a href="'.$profile_url.'?uid='.$uid.'">'.$display_name.'</a>';
	} else {
		$html = $display_name;
	}
	return $html;
}

// Create Permalink for Forum
function symposium_permalink($id, $type) {

	global $wpdb;
	$seo = $wpdb->get_var($wpdb->prepare("SELECT seo FROM ".$wpdb->prefix.'symposium_config'));
	
	if ($seo != "on") {
		// Not set on options page
		return "";
	} else {
	
		if ($_GET['page_id'] != '') {
			
			// Not using Permalinks
			return "";
			
		} else {
		
			if ($wpdb->get_var($wpdb->prepare("SELECT show_categories FROM ".$wpdb->prefix.'symposium_config')) == "on")
			
			if ($type == "category") {
				$info = $wpdb->get_row("
					SELECT title FROM ".$wpdb->prefix.'symposium_cats'." WHERE cid = ".$id); 
				$string = stripslashes($info->title);
				$string = str_replace('\\', '-', $string);
				$string = str_replace('/', '-', $string);
			} else {
				$info = $wpdb->get_row("
					SELECT topic_subject, title FROM ".$wpdb->prefix.'symposium_topics'." INNER JOIN ".$wpdb->prefix.'symposium_cats'." ON ".$wpdb->prefix.'symposium_topics'.".topic_category = ".$wpdb->prefix.'symposium_cats'.".cid WHERE tid = ".$id); 
				$string = stripslashes($info->topic_subject);
				$string = str_replace('\\', '-', $string);
				$string = str_replace('/', '-', $string);
				if ($wpdb->get_var($wpdb->prepare("SELECT show_categories FROM ".$wpdb->prefix.'symposium_config')) == "on") {
					$title = stripslashes($info->title);
					$title = str_replace('\\', '-', $title);
					$title = str_replace('/', '-', $title);
					$string = $title."/".$string;
				}
			}
	
							
			$patterns = array();
			$patterns[0] = '/ /';
			$patterns[1] = '/\?/';
			$patterns[2] = '/\&/';
			$replacements = array();
			$replacements[0] = '-';
			$replacements[1] = '';
			$replacements[2] = '';
			$string = preg_replace($patterns, $replacements, $string);
	
			$string = $id."/".$string;
	
			
			return $string;
		}
	}
}

// How long ago as text
function symposium_time_ago($date,$granularity=1) {
	
    $date = strtotime($date);
    $difference = (time() - $date) + 1;
    $periods = array(__('decade') => 315360000,
        'year' => 31536000,
        'month' => 2628000,
        'week' => 604800, 
        'day' => 86400,
        'hour' => 3600,
        'minute' => 60,
        'second' => 1);
                                 
    foreach ($periods as $key => $value) {
        if ($difference >= $value) {
            $time = floor($difference/$value);
            $difference %= $value;
            $retval .= ($retval ? ' ' : '').$time.' ';
            $key = (($time > 1) ? $key.'s' : $key);
            if ($key == 'year') { $key = __('year', 'wp-symposium'); }
            if ($key == 'years') { $key = __('years', 'wp-symposium'); }
            if ($key == 'month') { $key = __('month', 'wp-symposium'); }
            if ($key == 'months') { $key = __('months', 'wp-symposium'); }
            if ($key == 'week') { $key = __('week', 'wp-symposium'); }
            if ($key == 'weeks') { $key = __('weeks', 'wp-symposium'); }
            if ($key == 'day') { $key = __('day', 'wp-symposium'); }
            if ($key == 'days') { $key = __('days', 'wp-symposium'); }
            if ($key == 'hour') { $key = __('hour', 'wp-symposium'); }
            if ($key == 'hours') { $key = __('hours', 'wp-symposium'); }
            if ($key == 'minute') { $key = __('minute', 'wp-symposium'); }
            if ($key == 'minutes') { $key = __('minutes', 'wp-symposium'); }
            if ($key == 'second') { $key = __('second', 'wp-symposium'); }
            if ($key == 'seconds') { $key = __('seconds', 'wp-symposium'); }
            $retval .= $key;
            $granularity--;
        }
        if ($granularity == '0') { break; }
    }
    
    $return = sprintf (__('%s ago', 'wp-symposium'), $retval);
    return $return;


}

// Send email
function symposium_sendmail($email, $subject, $msg)
{
	global $wpdb;
	
	// first get ID of recipient
	$uid = $wpdb->get_var($wpdb->prepare("SELECT ID FROM ".$wpdb->prefix."users WHERE lower(user_email) = '".strtolower($email)."'"));

	// get footer
	$footer = $wpdb->get_var($wpdb->prepare("SELECT footer FROM ".$wpdb->prefix.'symposium_config'));

	// build body text
	$body = "<style>";
	$body .= "body { background-color: #eee; }";
	$body .= "</style>";
	$body .= "<div style='margin: 20px; padding:20px; border-radius:10px; background-color: #fff;border:1px solid #000;'>";
	$body .= $msg."<br /><hr />";
	$body .= "<div style='width:430px;font-size:10px;border:0px solid #eee;text-align:left;float:left;'>".$footer."</div>";
	
	// If you are using the free version of Symposium Forum, the following link must be kept in place! Thank you.
	$body .= "<div style='width:370px;font-size:10px;border:0px solid #eee;text-align:right;float:right;'>Powered by <a href='http://www.wpsymposium.com'>WP Symposium</a> - Social Networking for WordPress</div>";
	$body .= "</div>";

	// To send HTML mail, the Content-type header must be set
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	$headers .= 'From: '.$wpdb->get_var($wpdb->prepare("SELECT from_email FROM ".$wpdb->prefix.'symposium_config'))."\r\n";
	
	// finally send mail
	if (mail($email, $subject, $body, $headers))
	{
		return true;
	} else {
		return false;
	}
}

// Function to turn a mysql datetime (YYYY-MM-DD HH:MM:SS) into a unix timestamp 

function convert_datetime($str) { 

    list($date, $time) = explode(' ', $str); 
    list($year, $month, $day) = explode('-', $date); 
    list($hour, $minute, $second) = explode(':', $time); 
     
    $timestamp = mktime($hour, $minute, $second, $month, $day, $year); 
     
    return $timestamp; 
} 
?>