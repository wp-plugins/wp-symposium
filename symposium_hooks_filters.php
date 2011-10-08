<?php

// *************************************** HOOKS AND FILTERS ***************************************

// Profile Menu hook
function add_symposium_profile_menu($html,$uid1,$uid2,$privacy,$is_friend,$extended,$share)  
{  
	global $wpdb,$current_user;
	

			if ( ( WPS_MENU_PROFILE == 'on') && ( ($uid1 == $uid2) || (strtolower($share) == 'everyone') || (strtolower($share) == 'public') || (strtolower($share) == 'friends only' && $is_friend) || symposium_get_current_userlevel() == 5) ) {
	
				if ($extended != '' || $uid1 == $uid2) {
					if ($uid1 == $uid2) {
						$html .= '<div id="menu_extended" class="symposium_profile_menu">'.__('My Profile', 'wp-symposium').'</div>';
					} else {
						$html .= '<div id="menu_extended" class="symposium_profile_menu">'.__('Profile', 'wp-symposium').'</div>';
					}
				}
			}

			if  ( ($uid1 == $uid2) || (strtolower($privacy) == 'everyone') || (strtolower($share) == 'public') || (strtolower($privacy) == 'friends only' && $is_friend) || symposium_get_current_userlevel() == 5) {

				if ($uid1 == $uid2) {
					if (WPS_MENU_MY_ACTIVITY == 'on') {
						$html .= '<div id="menu_wall" class="symposium_profile_menu">'.__('My Activity', 'wp-symposium').'</div>';
					}
					if (WPS_MENU_FRIENDS_ACTIVITY == 'on') {
						if (strtolower($share) == 'public' && !(is_user_logged_in())) {
							// don't show friends activity to public
						} else {
							$html .= '<div id="menu_activity" class="symposium_profile_menu">'.__('My Friends Activity', 'wp-symposium').'</div>';
						}
					}
				} else {
					if (WPS_MENU_MY_ACTIVITY == 'on') {
						$html .= '<div id="menu_wall" class="symposium_profile_menu">'.__('Activity', 'wp-symposium').'</div>';
					}
					if (WPS_MENU_FRIENDS_ACTIVITY == 'on') {
						if (strtolower($share) == 'public' && !(is_user_logged_in())) {
							// don't show friends activity to public
						} else {
							$html .= '<div id="menu_activity" class="symposium_profile_menu">'.__('Friends Activity', 'wp-symposium').'</div>';
						}
					}
				}
				if (WPS_MENU_ALL_ACTIVITY == 'on') {
					if (strtolower($share) == 'public' && !(is_user_logged_in())) {
						// don't show all activity to public
					} else {
						$html .= '<div id="menu_all" class="symposium_profile_menu">'.__('All Activity', 'wp-symposium').'</div>';
					}
				}
				if (function_exists('symposium_group')) {
					if ($uid1 == $uid2) {
						$html .= '<div id="menu_groups" class="symposium_profile_menu">'.__('My Groups', 'wp-symposium').'</div>';
					} else {
						$html .= '<div id="menu_groups" class="symposium_profile_menu">'.__('Groups', 'wp-symposium').'</div>';
					}
				}				
			}

			if ( ($uid1 == $uid2) || (strtolower($share) == 'everyone') || (strtolower($share) == 'friends only' && $is_friend) || symposium_get_current_userlevel() == 5) {
				if (WPS_MENU_FRIENDS == 'on') {
					if ($uid1 == $uid2) {
						$pending_friends = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->base_prefix."symposium_friends f WHERE f.friend_to = ".$uid1." AND f.friend_accepted != 'on'");
					
						if ( ($pending_friends > 0) && ($uid1 == $uid2) ) {
							$pending_friends = " (".$pending_friends.")";
						} else {
							$pending_friends = "";
						}
						$html .= '<div id="menu_friends" class="symposium_profile_menu">'.__('My Friends', 'wp-symposium').' '.$pending_friends.'</div>';
					} else {
						$html .= '<div id="menu_friends" class="symposium_profile_menu">'.__('Friends', 'wp-symposium').'</div>';
					}
				}
			}

	return $html;
}  
add_action('symposium_profile_menu_filter', 'add_symposium_profile_menu', 8, 7);

// Profile Menu hook (end of menu)
function add_symposium_profile_menu_texthtml($html,$uid1,$uid2,$privacy,$is_friend,$extended,$share)  
{  
	global $wpdb,$current_user;
	
	$texthtml = WPS_MENU_TEXTHTML;
	
	return $texthtml;
}
add_action('symposium_profile_menu_end_filter', 'add_symposium_profile_menu_texthtml', 8, 7);

// Non-admin Header hook
function symposium_header() {
	include_once('symposium_styles.php');
}

// Admin Header hook
function symposium_admin_header() {

	global $wpdb;
	if ( strpos($_SERVER['PHP_SELF'], "wp-admin/profile.php") !== FALSE ) {
		if (function_exists('symposium_profile')) {
			$redirect_wp_profile = WPS_REDIRECT_WP_PROFILE;
			if ($redirect_wp_profile == 'on') {
				$profile_page = symposium_get_url('profile');
				if ( (isset($_GET['uid'])) && ($_GET['uid'] != '') ) {
					$uid = symposium_string_query($profile_page).'uid='.$_GET['uid'];
				} else {
					$uid = '';
				}
				header("Location:".$profile_page.$uid);
			}
		}
	}
}

// ****** Hooks and Filters to add comments when certain things happen to activity ******************************

// Add activity comment for new forum topic posted 
function symposium_add_activity_comment($from_id, $from_name, $url) {
	
	global $wpdb;

	$success = ($wpdb->query( $wpdb->prepare( "
		INSERT INTO ".$wpdb->base_prefix."symposium_comments
		( 	subject_uid, 
			author_uid,
			comment_parent,
			comment_timestamp,
			comment,
			is_group
		)
		VALUES ( %d, %d, %d, %s, %s, %s )", 
        array(
        	$from_id, 
        	$from_id, 
        	0,
        	date("Y-m-d H:i:s"),
        	$url,
        	''
        	) 
        ) ) );	        
        
}
add_action('symposium_forum_newtopic_hook', 'symposium_add_activity_comment', 10, 3);


// **************************************************************************************************************



?>