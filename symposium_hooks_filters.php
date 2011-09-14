<?php

// *************************************** HOOKS AND FILTERS ***************************************

// Profile Menu hook
function add_symposium_profile_menu($html,$uid1,$uid2,$privacy,$is_friend,$extended,$share)  
{  
	global $wpdb,$current_user;
	
	$menu = $wpdb->get_row($wpdb->prepare("SELECT menu_my_activity, menu_friends_activity, menu_all_activity, menu_profile, menu_friends FROM ".$wpdb->prefix . 'symposium_config'));

			if ( ( $menu->menu_profile == 'on') && ( ($uid1 == $uid2) || (strtolower($share) == 'everyone') || (strtolower($share) == 'public') || (strtolower($share) == 'friends only' && $is_friend) || symposium_get_current_userlevel() == 5) ) {
	
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
					if ($menu->menu_my_activity == 'on') {
						$html .= '<div id="menu_wall" class="symposium_profile_menu">'.__('My Activity', 'wp-symposium').'</div>';
					}
					if ($menu->menu_friends_activity == 'on') {
						if (strtolower($share) == 'public' && !(is_user_logged_in())) {
							// don't show friends activity to public
						} else {
							$html .= '<div id="menu_activity" class="symposium_profile_menu">'.__('My Friends Activity', 'wp-symposium').'</div>';
						}
					}
				} else {
					if ($menu->menu_my_activity == 'on') {
						$html .= '<div id="menu_wall" class="symposium_profile_menu">'.__('Activity', 'wp-symposium').'</div>';
					}
					if ($menu->menu_friends_activity == 'on') {
						if (strtolower($share) == 'public' && !(is_user_logged_in())) {
							// don't show friends activity to public
						} else {
							$html .= '<div id="menu_activity" class="symposium_profile_menu">'.__('Friends Activity', 'wp-symposium').'</div>';
						}
					}
				}
				if ($menu->menu_all_activity == 'on') {
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
				if ($menu->menu_friends == 'on') {
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
	
	$texthtml = $wpdb->get_var($wpdb->prepare("SELECT menu_texthtml FROM ".$wpdb->prefix . 'symposium_config'));
	
	return $texthtml;
}
add_action('symposium_profile_menu_end_filter', 'add_symposium_profile_menu_texthtml', 8, 7);


// Admin Header hook
function symposium_admin_header() {

	global $wpdb;
	if ( strpos($_SERVER['PHP_SELF'], "wp-admin/profile.php") !== FALSE ) {
		if (function_exists('symposium_profile')) {
			$redirect_wp_profile = $wpdb->get_var($wpdb->prepare("SELECT redirect_wp_profile FROM ".$wpdb->prefix . 'symposium_config'));
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

// Non-admin Header hook
function symposium_header() {
	include_once('symposium_styles.php');
}


?>