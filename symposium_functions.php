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

function get_user_avatar($uid, $size) {
	
	global $wpdb;
	$profile_photo = get_symposium_meta($uid, 'profile_photo');
	
	if ($profile_photo == '' || profile_photo == 'upload_failed') {
		return get_avatar($uid, $size);
	} else {
		return "<img src='/wp-content/wp-symposium-members/".$uid."/media/photos/profile_pictures/".$profile_photo."' style='width:".$size."px; height:".$size."' />";
	}
	
	exit;
}

function show_profile_menu($uid1, $uid2) {
	
	global $wpdb;

	$html .= "<div style='width:130px; padding-right: 15px; margin-right:0px; float: left;'>";
	
		$html .= '<div id="menu_wall" class="symposium_profile_menu">'.__('Wall', 'wp-symposium').'</div>';
		$html .= '<div id="menu_activity" class="symposium_profile_menu">'.__('Friends Activity', 'wp-symposium').'</div>';
		$html .= '<div id="menu_all" class="symposium_profile_menu">'.__('All Activity', 'wp-symposium').'</div>';
		
		if ($uid1 == $uid2) {

			// Check for pending friends
			$pending_friends = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."symposium_friends f WHERE f.friend_to = ".$uid1." AND f.friend_accepted != 'on'");
		
			if ($pending_friends > 0) {
				$pending_friends = " (".$pending_friends.")";
			} else {
				$pending_friends = "";
			}
		
			if (function_exists('symposium_avatar')) {
				$html .= '<div id="menu_photo" class="symposium_profile_menu">'.__('Profile Photo', 'wp-symposium').'</div>';
			}
			$html .= '<div id="menu_settings" class="symposium_profile_menu">'.__('Preferences', 'wp-symposium').'</div>';
			$html .= '<div id="menu_personal" class="symposium_profile_menu">'.__('Personal', 'wp-symposium').'</div>';
			$html .= '<div id="menu_friends" class="symposium_profile_menu">'.__('Friends', 'wp-symposium').' '.$pending_friends.'</div>';

		}
		
	$html .= "</div>";
	
	return $html;

}

function symposium_make_url($text) {

	$text = preg_replace("#(^|[\n ])(([\w]+?://[\w\#$%&~.\-;:=,?@\[\]+]*)(/[\w\#$%&~/.\-;:=,?@\[\]+]*)?)#is", "\\1<a href=\"\\2\" target=\"_blank\">\\2</a>", $text);
    $text = preg_replace("#(^|[\n ])(((www|ftp)\.[\w\#$%&~.\-;:=,?@\[\]+]*)(/[\w\#$%&~/.\-;:=,?@\[\]+]*)?)#is", "\\1<a href=\"http://\\2\" target=\"_blank\">\\2</a>", $text);
    
    return $text;

}

function symposium_profile_friends($uid) {

	global $wpdb, $current_user;
	wp_get_current_user();

	$plugin = WP_PLUGIN_URL.'/wp-symposium';
	$dbpage = $plugin.'/symposium_profile_db.php';
	$meta = get_symposium_meta_row($uid);					
	$config = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix . 'symposium_config'));

	$html .= "<div id='profile_left_column'>";

		$sql = "SELECT u1.display_name, u1.ID, f.friend_timestamp, f.friend_message, f.friend_from FROM ".$wpdb->prefix."symposium_friends f LEFT JOIN ".$wpdb->prefix."users u1 ON f.friend_from = u1.ID WHERE f.friend_to = ".$current_user->ID." AND f.friend_accepted != 'on' ORDER BY f.friend_timestamp DESC";

		$requests = $wpdb->get_results($sql);
		if ($requests) {
			
			$html .= '<h2>'.__('Friend Requests', 'wp-symposium').'...</h2>';
			
			foreach ($requests as $request) {
				$html .= "<div style='clear:both; margin-top:8px; overflow: auto; margin-bottom: 15px; '>";		
					$html .= "<div style='float: left; width:64px; margin-right: 15px'>";
						$html .= get_user_avatar($request->ID, 64);
					$html .= "</div>";
					$html .= "<div style='float: left; width:50%'>";
						$html .= symposium_profile_link($request->ID)."<br />";
						$html .= symposium_time_ago($request->friend_timestamp)."<br />";
						$html .= "<em>".stripslashes($request->friend_message)."</em>";
					$html .= "</div>";
					$html .= "<div style='float:right'>";
						$html .= '<form method="post" action="'.$dbpage.'">';
						$html .= '<input type="hidden" name="symposium_update" value="R">';
						$html .= '<input type="hidden" name="uid" value="'.$uid.'">';
						$html .= '<input type="hidden" name="friend_from" value="'.$request->friend_from.'">';
						$html .= '<input type="submit" name="friendreject" class="button" value="'.__('Reject', 'wp-symposium').'" /> ';
						$html .= '</form>';
					$html .= "</div>";
					$html .= "<div style='float:right'>";
						$html .= '<form method="post" action="'.$dbpage.'">';
						$html .= '<input type="hidden" name="symposium_update" value="A">';
						$html .= '<input type="hidden" name="uid" value="'.$uid.'">';
						$html .= '<input type="hidden" name="friend_from" value="'.$request->friend_from.'">';
						$html .= '<input type="submit" name="friendaccept" class="button" value="'.__('Accept', 'wp-symposium').'" /> ';
						$html .= '</form>';
					$html .= "</div>";
				$html .= "</div>";
			}
		}

		$sql = "SELECT f.*, m.last_activity FROM ".$wpdb->prefix."symposium_friends f LEFT JOIN ".$wpdb->prefix."symposium_usermeta m ON m.uid = f.friend_to WHERE f.friend_from = ".$current_user->ID." ORDER BY last_activity DESC";
		$friends = $wpdb->get_results($sql);

		if ($friends) {
			
			$inactive = $config->online;
			$offline = $config->offline;
			
			$html .= '<h2>'.__('Friends', 'wp-symposium').'...</h2>';
			
			foreach ($friends as $friend) {
				
				$time_now = time();
				$last_active_minutes = strtotime($friend->last_activity);
				$last_active_minutes = floor(($time_now-$last_active_minutes)/60);
												
				$html .= "<div style='clear:both; margin-top:8px; overflow: auto; margin-bottom: 15px; '>";		
					$html .= "<div style='float: left; width:64px; margin-right: 15px'>";
						$html .= get_user_avatar($friend->friend_to, 64);
					$html .= "</div>";
					$html .= "<div style='float: left; width:50%'>";
						$html .= symposium_profile_link($friend->friend_to)."<br />";
						if ($last_active_minutes >= $offline) {
							$html .= __('Logged out', 'wp-symposium').'. '.__('Last active', 'wp-symposium').' '.symposium_time_ago($friend->last_activity).".";
						} else {
							if ($last_active_minutes >= $inactive) {
								$html .= __('Offline', 'wp-symposium').'. '.__('Last active', 'wp-symposium').' '.symposium_time_ago($friend->last_activity).".";
							} else {
								$html .= __('Last active', 'wp-symposium').' '.symposium_time_ago($friend->last_activity).".";
							}
						}
					$html .= "</div>";

					$html .= "<div style='float:right'>";
						$html .= '<form method="post" action="'.$dbpage.'">';
						$html .= '<input type="hidden" name="symposium_update" value="D">';
						$html .= '<input type="hidden" name="uid" value="'.$uid.'">';
						$html .= '<input type="hidden" name="friend" value="'.$friend->friend_to.'">';
						$html .= '<input type="submit" name="frienddelete" class="button" value="'.__('Remove', 'wp-symposium').'" /> ';
						$html .= '</form>';
					$html .= "</div>";

					$html .= "<div style='float:right'>";
						$html .='<input type="button" value="'.__('Send Mail', 'wp-symposium').'" class="button" onclick="document.location = \''.symposium_get_url('mail').'?view=compose&to='.$friend->friend_to.'\';">';
					$html .= "</div>";

				$html .= "</div>";
			}
		}						

	$html .= '</div>';
	
	return $html;
	
}

function symposium_profile_header($uid1, $uid2, $url, $display_name) {
	
	global $wpdb;
	$plugin = WP_PLUGIN_URL.'/wp-symposium';
	$dbpage = $plugin.'/symposium_profile_db.php';
	$meta = get_symposium_meta_row($uid1);					

	if ($uid1 > 0) {
		
		$config = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix . 'symposium_config'));

		$html = "<div style='padding:0px;overflow:auto;'>";
	
			$html .= "<div style='float: left; width: 100%; overflow:auto; padding:0px;'>";
	
				$privacy = $meta->share;
	
				$html .= "<div id='profile_details' style='margin-left: 215px;overflow:auto;'>";

					if ( ($uid1 == $uid2) || (strtolower($privacy) == 'everyone') || (strtolower($privacy) == 'friends only' && symposium_friend_of($uid1)) ) {
	
						$city = $meta->city;
						$country = $meta->country;
		
						if ($city != '' || $country != '') { 	
												
							$html .= "<div style='float:right; width: 150px; margin-left:15px;'>";
							$html .= '<a target="_blank" href="http://maps.google.co.uk/maps?f=q&amp;source=embed&amp;hl=en&amp;geocode=&amp;q='.$city.',+'.$country.'&amp;ie=UTF8&amp;hq=&amp;hnear='.$city.',+'.$country.'&amp;output=embed&amp;z=5" alt="Click on map to enlarge" title="Click on map to englarge">';
							$html .= '<img src="http://maps.google.com/maps/api/staticmap?center='.$city.',.+'.$country.'&zoom=5&size=150x150&maptype=roadmap&markers=color:blue|label:&nbsp;|'.$city.',+'.$country.'&sensor=false" />';
							$html .= "</a></div>";
							
						}
						
					}
						
					$html .= "<h1 style='clear:none'>".$display_name."</h1>";

					if ( ($uid1 == $uid2) || (strtolower($privacy) == 'everyone') || (strtolower($privacy) == 'friends only' && symposium_friend_of($uid1)) ) {

						$html .= "<p>";
						if ($city != '') { $html .= $city; }
						if ($city != '' && $country != '') { $html .= ", "; }
						if ($country != '') { $html .= $country; }
						
						$day = $meta->dob_day;
						$month = $meta->dob_month;
						$year = $meta->dob_year;
						if (($day == 1) && ($month == 1) && ($year >= 2010)) {
						} else {
							if ($year != '' && $month != '' && $day != '') {
								if ($city != '' || $country != '') { $html .= ".<br />"; }
								switch($month) {									
									case 1:$monthname = __("January", "wp_symposim");break;
									case 2:$monthname = __("February", "wp_symposim");break;
									case 3:$monthname = __("March", "wp_symposim");break;
									case 4:$monthname = __("April", "wp_symposim");break;
									case 5:$monthname = __("May", "wp_symposim");break;
									case 6:$monthname = __("June", "wp_symposim");break;
									case 7:$monthname = __("July", "wp_symposim");break;
									case 8:$monthname = __("August", "wp_symposim");break;
									case 9:$monthname = __("September", "wp_symposim");break;
									case 10:$monthname = __("October", "wp_symposim");break;
									case 11:$monthname = __("November", "wp_symposim");break;
									case 12:$monthname = __("December", "wp_symposim");break;
								}
								$html .= __("Born", "wp-symposium")." ".$day." ".$monthname." ".$year.".";
							}
						}
						$html .= "</p>";
						
					}
					
					if ( is_user_logged_in() ) {

						if ($uid1 == $uid2) {

							// Status Input
							$html .= '<input type="text" id="symposium_status" name="status" class="input-field" value="'.__("What's on your mind?", "wp-symposium").'" onfocus="this.value = \'\';" style="width:300px" />';
							$html .= '&nbsp;<input id="symposium_add_update" type="submit" style="width:75px" class="button" value="'.__('Update', 'wp-symposium').'" /> ';
							
						} else {
													
							// Buttons									
							if (symposium_friend_of($uid1)) {
		
								// A friend
		
								// Send mail
								$html .='<input type="button" value="Send Mail" class="button" onclick="document.location = \''.$url.'?view=compose&to='.$uid1.'\';">';
								
							} else {
								
								if (symposium_pending_friendship($uid1)) {
									// Pending
									$html .= __('Friend Request Sent', 'wp-symposium').'...<br />';
									$html .= '<form method="post" action="'.$dbpage.'">';
									$html .= '<input type="hidden" name="symposium_update" value="C">';
									$html .= '<input type="hidden" name="uid" value="'.$uid1.'">';
									$html .= '<input type="hidden" name="friend_to" value="'.$_GET['uid'].'">';
									$html .= '<input type="submit" name="cancelfriend" class="button" value="Cancel" /> ';
									$html .= '</form>';
								} else {							
									// Not a friend
									$html .= '<strong>'.__('Add as a Friend', 'wp-symposium').'...</strong><br />';
									$html .= '<form method="post" action="'.$dbpage.'">';
									$html .= '<input type="hidden" name="symposium_update" value="F">';
									$html .= '<input type="hidden" name="uid" value="'.$uid1.'">';
									$html .= '<input type="hidden" name="friend_to" value="'.$_GET['uid'].'">';
									$html .= '<input type="text" name="friendmessage" class="input-field" style="width:200px" onclick="this.value=\'\'" value="'.__('Add a personal message...', 'wp-symposium').'">';
									$html .= '&nbsp;&nbsp;<input type="submit" name="addasfriend" class="button" value="'.__('Add as a Friend', 'wp-symposium').'" /> ';
									$html .= '</form>';
								}
							}
						}						
					}
	
				$html .= "</div>";
					
			$html .= "</div>";
		
			// Photo
			$html .= "<div id='profile_photo' style='float:left;width:215px;margin-left:-100%; margin-bottom:20px;'>";
			$html .= get_user_avatar($uid1, 200);
			$html .= "</div>";

		$html .= "</div>";
		
		return $html;
		
	} else {
		
		return '';
		
	}

}

function symposium_profile_body($uid1, $uid2, $post, $version) {
	
	global $wpdb;
	$plugin = WP_PLUGIN_URL.'/wp-symposium';
	$dbpage = $plugin.'/symposium_profile_db.php';
	$meta = get_symposium_meta_row($uid1);					

	if ($uid1 > 0) {
		
		$config = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix . 'symposium_config'));
		$styles = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix . 'symposium_styles'));

		$privacy = $meta->wall_share;		
		$is_friend = symposium_friend_of($uid1);

		$html = "";
		
		if ( ($uid1 == $uid2) || (strtolower($privacy) == 'everyone') || (strtolower($privacy) == 'friends only' && $is_friend) ) {
		
			$html .= "<div id='profile_left_column'>";

				$html .= "<div id='profile_right_column'>";
	
					// Extended Information
					$html .= "<div style='width:100%;padding:0px;overflow:auto;'>";
		
						$extended = $meta->extended;
						$fields = explode('[|]', $extended);
						if ($fields) {
							foreach ($fields as $field) {
								$split = explode('[]', $field);
								if ( ($split[0] != '') && ($split[1] != '') ) {
									$html .= "<p><strong>".$split[0]."</strong><br />";
									$html .= $split[1]."</p>";
								}
							}
						}
						
					$html .= "</div>";
	
					// Friends
					$html .= "<div style='width:100%;padding:0px;overflow:auto;'>";
		
						$sql = "SELECT f.*, m.last_activity FROM ".$wpdb->prefix."symposium_friends f LEFT JOIN ".$wpdb->prefix."symposium_usermeta m ON m.uid = f.friend_to WHERE f.friend_from = ".$uid1." AND friend_accepted = 'on' ORDER BY last_activity DESC LIMIT 0,6";
						$friends = $wpdb->get_results($sql);
	
						if ($friends) {
							
							$inactive = $config->online;
							$offline = $config->offline;
							
							$html .= '<strong>'.__('Recently Active Friends', 'wp-symposium').'</strong><br />';
							foreach ($friends as $friend) {
								
								$time_now = time();
								$last_active_minutes = strtotime($friend->last_activity);
								$last_active_minutes = floor(($time_now-$last_active_minutes)/60);
																
								$html .= "<div style='clear:both; width: 99%; margin-bottom: 10px; overflow: auto;'>";		
									$html .= "<div style='float: left; width:42px; margin-right: 5px'>";
										$html .= get_user_avatar($friend->friend_to, 42);
									$html .= "</div>";
									$html .= "<div>";
										$html .= symposium_profile_link($friend->friend_to)."<br />";
										$html .= __('Last active', 'wp-symposium').' '.symposium_time_ago($friend->last_activity).".";
									$html .= "</div>";
	
								$html .= "</div>";
							}
						}
												
					$html .= "</div>";
						
				$html .= "</div>";
					
				// Wall
				$html .= "<div id='symposium_wall' style='overflow: auto; padding:0px; margin:0px;'>";
				
					if ( ($uid1 != $uid2) || (is_user_logged_in() && $is_friend)) {
						// Post Comment Input
						$html .= '<input id="symposium_comment" type="text" name="post_comment" class="input-field" value="'.__('Write a comment', 'wp-symposium').'..." onfocus="this.value = \'\';" style="width:300px" />';
						$html .= '&nbsp;<input id="symposium_add_comment" type="submit" style="width:75px" class="button" value="'.__('Post', 'wp-symposium').'" /> ';
					}

					if ($post != '' && symposium_safe_param($post)) {

						$sql = "SELECT c.*, u.display_name, u2.display_name AS subject_name FROM ".$wpdb->prefix."symposium_comments c LEFT JOIN ".$wpdb->prefix."users u ON c.author_uid = u.ID LEFT JOIN ".$wpdb->prefix."users u2 ON c.subject_uid = u2.ID WHERE ( (c.subject_uid = ".$uid1.") OR (c.author_uid = ".$uid1.") OR ( c.author_uid IN (SELECT friend_to FROM ".$wpdb->prefix."symposium_friends WHERE friend_from = ".$uid1.")) ) AND c.cid = ".$post." AND c.comment_parent = 0 ORDER BY c.comment_timestamp DESC LIMIT 0,20";
						
					} else {

						if ($version == "all_activity") {
							$sql = "SELECT c.*, u.display_name, u2.display_name AS subject_name FROM ".$wpdb->prefix."symposium_comments c LEFT JOIN ".$wpdb->prefix."users u ON c.author_uid = u.ID LEFT JOIN ".$wpdb->prefix."users u2 ON c.subject_uid = u2.ID WHERE c.comment_parent = 0 ORDER BY c.comment_timestamp DESC LIMIT 0,20";							
						}

						if ($version == "friends_activity") {
							$sql = "SELECT c.*, u.display_name, u2.display_name AS subject_name FROM ".$wpdb->prefix."symposium_comments c LEFT JOIN ".$wpdb->prefix."users u ON c.author_uid = u.ID LEFT JOIN ".$wpdb->prefix."users u2 ON c.subject_uid = u2.ID WHERE ( (c.subject_uid = ".$uid1.") OR (c.author_uid = ".$uid1.") OR ( c.author_uid IN (SELECT friend_to FROM ".$wpdb->prefix."symposium_friends WHERE friend_from = ".$uid1.")) OR ( c.subject_uid IN (SELECT friend_to FROM ".$wpdb->prefix."symposium_friends WHERE friend_from = ".$uid1.")) ) AND c.comment_parent = 0 ORDER BY c.comment_timestamp DESC LIMIT 0,20";							
						}

						if ($version == "wall") {
							$sql = "SELECT c.*, u.display_name, u2.display_name AS subject_name FROM ".$wpdb->prefix."symposium_comments c LEFT JOIN ".$wpdb->prefix."users u ON c.author_uid = u.ID LEFT JOIN ".$wpdb->prefix."users u2 ON c.subject_uid = u2.ID WHERE (c.subject_uid = ".$uid1.") AND c.comment_parent = 0 ORDER BY c.comment_timestamp DESC LIMIT 0,20";							
						}

					}
					
					$comments = $wpdb->get_results($sql);	
					if ($comments) {
						foreach ($comments as $comment) {
	
							$html .= "<div id='".$comment->cid."' style='overflow: auto; padding-top: 10px;padding-left: 5px;margin-right: 15px;margin-bottom:0px; border-radius: 0px; -moz-border-radius: 0px'>";
								$html .= "<div style='float: left; overflow:auto; width:100%;padding:0px;'>";
									$html .= "<div class='wall_post' style='margin-left: 74px;overflow:auto;'>";
									
										if (symposium_get_current_userlevel($uid2) == 5 || $comment->subject_uid == $uid2 || $comment->author_uid == $uid2) {
											$html .= "<a title='".$comment->cid."' href='javascript:void(0);' class='delete_post delete_post_top'>".__("Delete", "wp-symposium")."</a>";
										}
										$html .= '<a href="'.symposium_get_url('profile').'?uid='.$comment->author_uid.'">'.stripslashes($comment->display_name).'</a> ';
										if ($comment->author_uid != $comment->subject_uid) {
											$html .= ' &rarr; <a href="'.symposium_get_url('profile').'?uid='.$comment->subject_uid.'">'.stripslashes($comment->subject_name).'</a> ';
										}
										$html .= symposium_time_ago($comment->comment_timestamp).".<br />";
										$html .= symposium_make_url(stripslashes($comment->comment));
	
										// Replies
										$sql = "SELECT c.*, u.display_name FROM ".$wpdb->prefix."symposium_comments c 
											LEFT JOIN ".$wpdb->prefix."users u ON c.author_uid = u.ID 
											LEFT JOIN ".$wpdb->prefix."symposium_comments p ON c.comment_parent = p.cid 
											WHERE ( 
													(c.subject_uid = ".$uid1.") 
											   	OR 	(c.author_uid = ".$uid1.") 
											   	OR 	(p.subject_uid = ".$uid1.") 
											   	OR 	(p.author_uid = ".$uid1.") 
											   	OR 	(p.author_uid = ".$uid2.") 
											   	OR 	(p.subject_uid IN (SELECT friend_to FROM ".$wpdb->prefix."symposium_friends WHERE friend_from = ".$uid2.")) 
											   	OR 	(p.author_uid IN (SELECT friend_to FROM ".$wpdb->prefix."symposium_friends WHERE friend_from = ".$uid2.")) 
											   ) 
											   AND c.comment_parent = ".$comment->cid." ORDER BY c.cid";
										
										$replies = $wpdb->get_results($sql);	
										$count = 0;
										if ($replies) {
											if (count($replies) > 4) {
												$html .= "<div style='padding:4px; padding-bottom:0px; clear: both; overflow: auto; margin-top:10px;'>";
												$html .= "<a title='".$comment->cid."' class='view_all_comments' href='javascript:void(0);'>".__(sprintf("View all %d comments", count($replies)), "wp-symposium")."</a>";
												$html .= "</div>";
											}
											foreach ($replies as $reply) {
												$count++;
												if ($count > count($replies)-4) {
													$reply_style = "";
												} else {
													$reply_style = "display:none; ";
												}
												$html .= "<div id='".$reply->cid."' class='reply_div' style='background-color: ".$styles->bg_color_2."; padding:4px; padding-bottom:0px; clear: both; overflow: auto; margin-top:10px;".$reply_style."'>";
													$html .= "<div style='float: left; overflow:auto; width:100%;padding:0px;'>";
														$html .= "<div class='wall_reply' style='margin-left: 45px;overflow:auto;'>";
															if (symposium_get_current_userlevel($uid2) == 5 || $reply->subject_uid == $uid2 || $reply->author_uid == $uid2) {
																$html .= "<a title='".$reply->cid."' href='javascript:void(0);' class='delete_post delete_reply'>".__("Delete", "wp-symposium")."</a>";
															}
															$html .= '<a href="'.symposium_get_url('profile').'?uid='.$reply->author_uid.'">'.stripslashes($reply->display_name).'</a> ';
															$html .= symposium_time_ago($reply->comment_timestamp).".<br />";
															$html .= symposium_make_url(stripslashes($reply->comment));
														$html .= "</div>";
													$html .= "</div>";
													
													$html .= "<div style='float:left;width:45px;margin-left:-100%;'>";
														$html .= get_user_avatar($reply->author_uid, 40);
													$html .= "</div>";		
												$html .= "</div>";
											}
										}
										$html .= "<div id='symposium_comment_".$comment->cid."'></div>";
	
										// Reply field
										if ( $uid1 == $uid2 || (is_user_logged_in() && $is_friend)) {
											$html .= '<p><input id="symposium_reply_'.$comment->cid.'" type="text" name="wall_comment" class="input-field" style="margin-top:10px; width:230px;" value="'.__('Write a comment', 'wp-symposium').'..." onfocus="this.value = \'\';" />';
											$html .= '<input id="symposium_author_'.$comment->cid.'" type="hidden" value="'.$comment->author_uid.'" />';
											$html .= '&nbsp;<input title="'.$comment->cid.'" type="submit" style="width:75px" class="button symposium_add_reply" value="'.__('Add', 'wp-symposium').'" /></p>';
										}
										
									$html .= "</div>";
								$html .= "</div>";
								$html .= "<div style='float:left;width:74px;margin-left:-100%;'>";
									$html .= get_user_avatar($comment->author_uid, 64);
								$html .= "</div>";
							$html .= "</div>";
							
	
							
						}
					}
				
				$html .= "</div>";
					
			$html .= "</div>";
		}
		
		return $html;
		
	} else {
		
		return '';
		
	}

}



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
			$msg .= get_user_avatar($mail->mail_from, 44);
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
	if ($plugin == 'avatar') {
		$return = $urls->avatar_url;
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