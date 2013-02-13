<?php
/*
WP Symposium Profile
Description: Member Profile component for the Symposium suite of plug-ins. Also enables Friends. Put [symposium-profile], [symposium-settings], [symposium-personal], [symposium-friends] or [symposium-extended] on any WordPress page to display relevant content. If Gallery in use, can also use [symposium-galleries].
*/


// Get constants
require_once(dirname(__FILE__).'/default-constants.php');


// [symposium-member-header] (just header)
function __wps__profile_member_header()  
{  
	return __wps__show_profile("header");
	exit;		
}

// [symposium-profile-menu] 
function __wps__profile_member_menu()  
{  
	global $wpdb, $current_user;

	$html = "";
	
	if (is_user_logged_in()) {
		
		if (isset($_GET['uid'])) {
			$uid = $_GET['uid'];
		} else {
			$uid = $current_user->ID;
		}	        			

		$html .= "<div class='__wps__wrapper'>";
		$html .= "<div id='profile_menu' style='margin-left: 0px;'>";
		$html .= __wps__show_profile_menu($uid, $current_user->ID);
		$html .= "</div>";
		$html .= "</div>";
		
	} else {
	
		$html = "&nbsp;";
		
	}
		
	return $html;
	exit;
		
}

// [symposium-stream] (aggregated wall)
function __wps__stream($view)  
{  
	global $current_user;
	
	$view = ($view == '') ? $view = 'activity' : $view = $view['view'];
	
	$html = "<div class='__wps__wrapper'>";
	$html .= __wps__buffer(__wps__profile_body(0, $current_user->ID, 0, "stream_".$view, 0, false));
	$html .= "</div>";
	        			
	return $html;
	exit;
		
}

// [symposium-profile] (wall)
function __wps__profile()  
{  
	        			
	return __wps__show_profile(get_option(WPS_OPTIONS_PREFIX.'_wps_profile_default'));
	exit;
		
}

// [symposium-activity] (friends activity)
function __wps__profile_activity()  
{  
										
	return __wps__show_profile("activity");
	exit;
		
}

// [symposium-all] (all activity)
function __wps__profile_all()  
{  
										
	return __wps__show_profile("all");
	exit;
		
}

// [symposium-friends]
function __wps__profile_friend()  
{  

	return __wps__show_profile("friends");
	exit;
		
}

// [symposium-personal]
function __wps__profile_personal()  
{  
										
	return __wps__show_profile("personal");
	exit;
		
}

// [symposium-settings]
function __wps__profile_settings()  
{  
										
	return __wps__show_profile("settings");
	exit;
		
}

// [symposium-extended]
function __wps__profile_extended()  
{  
										
	return __wps__show_profile("extended");
	exit;
		
}

// [symposium-avatar]
function __wps__profile_avatar()  
{  
										
	return __wps__show_profile("avatar");
	exit;
		
}


// [symposium-gallery]
function __wps__menu_gallery()  
{  
										
	return __wps__show_profile("gallery");
	exit;
		
}

// Adds profile page
function __wps__show_profile($page)  
{  

	global $wpdb, $current_user;

	$uid = '';
	
	if (isset($_POST['from']) && $_POST['from'] == 'small_search') {
		if ($_POST['uid'] == '') {
			$search = $_POST['member_small'];
			$uid = $wpdb->get_var($wpdb->prepare("SELECT u.ID FROM ".$wpdb->base_prefix."users u WHERE u.display_name LIKE '%s%%'", $search));
		}
	} 
	if ($uid == '') {

		if (isset($_GET['uid'])) {
			$uid = $_GET['uid'];
		} else {
			if (isset($_POST['uid'])) {
				$uid = $_POST['uid'];
			} else {
				$uid = $current_user->ID;
			}
		}

	}

	// resolve stubs if using permalinks
	if ( get_option(WPS_OPTIONS_PREFIX.'_permalink_structure') && get_query_var('stub')) {
		$stubs = explode('/', get_query_var('stub'));
		$stub0 = $stubs[0];
		if (WPS_DEBUG) echo $stub0.'<br />';
		
		if ($stub0) {
			$sql = "SELECT ID FROM ".$wpdb->base_prefix."users WHERE replace(display_name, ' ', '') = %s";
			$id = $wpdb->get_var($wpdb->prepare($sql, $stub0));
			if (WPS_DEBUG) echo $wpdb->last_query.'<br />';
			if ($id) {
				$uid = $id;
			}
		}
	}
	
	
	$share = __wps__get_meta($uid, 'share');
	if (WPS_DEBUG) echo 'UID:'.$uid.'<br />';
	$html = '<div id="__wps__current_user_page" style="display:none">'.$uid.'</div>';
	
	if (is_user_logged_in() || $share == 'public') {		
		
		$user = $wpdb->get_row($wpdb->prepare("SELECT display_name FROM ".$wpdb->base_prefix."users WHERE ID = %d", $uid));
		
		if ($user) {
			
			// Wrapper
			$html .= "<div class='__wps__wrapper'>";

				$html .= __wps__profile_header($uid, $current_user->ID, __wps__get_url('mail'), $user->display_name);

				if ($page != 'header') {
					
					if (isset($_GET['view']) && $_GET['view'] != '') {
						$page = $_GET['view'];
					}
					if (isset($_POST['view']) && $_POST['view'] != '') {
						$page = $_POST['view'];
					}
					if ($page == '') { $page = get_option(WPS_OPTIONS_PREFIX.'_wps_profile_default'); }
					
					$template = get_option(WPS_OPTIONS_PREFIX.'_template_profile_body');
					$template = str_replace("[]", "", stripslashes($template));
					
					// Put in forced profile page
					$template = str_replace("[default]", $page, stripslashes($template));

					// Put in busy image
					$template = str_replace("[page]", "<img src='".get_option(WPS_OPTIONS_PREFIX.'_images')."/busy.gif' />", stripslashes($template));

					// Put in menu
					$template = str_replace("[menu]", __wps__show_profile_menu($uid, $current_user->ID), stripslashes($template));
					$template = str_replace("[menu_tabs]", __wps__show_profile_menu_tabs($uid, $current_user->ID), stripslashes($template));

					$html .= $template;
  				
					$html .= "<br class='clear' />";
					
				}
					
			
			$html .= "</div>";
			$html .= "<div style='clear: both'></div>";
			
		} else {
			
			$html = __("Member not found, sorry", WPS_TEXT_DOMAIN);
		}
	
	} else {
		
		$html = __("Please login to view this member's profile.", WPS_TEXT_DOMAIN);
	}	

	// Finally, substitute other codes
	$html = str_replace("[menu_tabs]", __wps__show_profile_menu_tabs($uid, $current_user->ID), stripslashes($html));

				
	return $html;
	exit;

}  

function __wps__profile_header($uid1, $uid2, $url, $display_name) {
	
	global $wpdb, $current_user;
	$plugin = WPS_PLUGIN_URL;

	$template = get_option(WPS_OPTIONS_PREFIX.'_template_profile_header');
	$html = str_replace("[]", "", stripslashes($template));

	$privacy = __wps__get_meta($uid1, 'share');
	
	$html = str_replace("[display_name]", $display_name, $html);
	
	// Extended fields
	if (strpos($html, '[ext_') !== FALSE) {
		
		// Prepare array for use
		$sql = "SELECT * FROM ".$wpdb->base_prefix."symposium_extended";
		$extensions = $wpdb->get_results($sql);

		$ext_rows = array();		
		if ($extensions) {		
			foreach ($extensions as $extension) {
				$value = __wps__get_meta($uid1, 'extended_'.$extension->extended_slug);
				if ($extension->extended_type == 'Checkbox' || $value) {
					array_push ($ext_rows, array (	'slug'=>$extension->extended_slug,
													'name'=>$extension->extended_name,
													'value'=>$value,
													'type'=>$extension->extended_type,
													'order'=>$extension->extended_order ) );
				}
			}
		}
				
		$c = 0;
		while ($c < 100 && strpos($html, '[ext_') !== FALSE) {
			$ext = '';
			$c++;
			$s1 = strpos($html, '[ext_');
			$s2 = strpos($html, ']', $s1+1);
			$start = substr($html, 0, $s1-1);
			$code = substr($html, $s1+5, $s2-$s1-5);

			$end = substr($html, $s2+1, strlen($html)-$s1);
			if ( ($uid1 == $uid2) || (is_user_logged_in() && strtolower($privacy) == 'everyone') || (strtolower($privacy) == 'public') || (strtolower($privacy) == 'friends only' && __wps__friend_of($uid1, $current_user->ID)) ) {

				if ($ext_rows) {
					
					$ext_rows = __wps__sub_val_sort($ext_rows,'order');
					foreach ($ext_rows as $row) {
						if (strtolower($row['slug']) == strtolower($code)) {
							if ($row['type'] == 'Checkbox' && !$row['value'] && get_option(WPS_OPTIONS_PREFIX.'_profile_show_unchecked') != 'on') { 
								// Don't show if unchecked and chosen not to show (in Profile config)
							} else {

								if ($row['type'] == 'Text' && $row['value']) {
									$ext .= '<div class="__wps__profile_page_header_ext_label">'.stripslashes($row['name']).'</div>';
									$ext .= '<div class="__wps__profile_page_header_ext_value">'.stripslashes(__wps__make_url($row['value'])).'</div>';
								}
								if ($row['type'] == 'Textarea' && $row['value']) {
									$ext .= '<div class="__wps__profile_page_header_ext_label">'.stripslashes($row['name']).'</div>';
									$ext .= '<div class="__wps__profile_page_header_ext_value">'.str_replace(chr(10),'<br />',stripslashes(__wps__make_url($row['value']))).'</div>';
								}
								if ($row['type'] == 'List' && $row['value']) {
									$ext .= '<div class="__wps__profile_page_header_ext_label">'.stripslashes($row['name']).'</div>';
									$ext .= '<div class="__wps__profile_page_header_ext_value">'.str_replace(chr(10),'<br />',stripslashes(__wps__make_url($row['value']))).'</div>';
								}
								if ($row['type'] == 'Checkbox') {
									if (get_option(WPS_OPTIONS_PREFIX.'_profile_show_unchecked') == 'on' || $row['value']) {
										$ext .= '<div class="__wps__profile_page_header_ext_label">'.stripslashes($row['name'])."&nbsp;";
										if ($row['value']) { 
											$ext .= "<img src='".get_option(WPS_OPTIONS_PREFIX.'_images')."/tick.png' />"; 
										} else {
											$ext .= "<img src='".get_option(WPS_OPTIONS_PREFIX.'_images')."/cross.png' />"; 
										}
										$ext .= '</div>';
									}
								}
							}
						}
					}
				} 
				
				if ($c == 1) {
					$html = $start.'<div id=="__wps__profile_page_header_ext_fields">'.$ext;
				} else {
					$html = $start.$ext;
				}
				if (strpos($end, '[ext_') === FALSE)
					$html .= '</div>';
					
				$html .= $end;
			} else {
				$html = $start.$end;
			}
		}	
	}
		
	// Profile label
	if ($label = __wps__get_meta($uid1, 'profile_label')) {
		$html = str_replace("[profile_label]", $label, $html);
	} else {
		$html = str_replace("<div id='profile_label'>[profile_label]</div>", '', $html);
	}
	
	// Follow/Unfollow
	if (function_exists('__wps__profile_plus') && is_user_logged_in() && $uid1 != $uid2) {
		if (__wps__is_following($uid2, $uid1)) {
			$html = str_replace("[follow]", '<input type="submit" ref="unfollow" value="'.__('Unfollow', WPS_TEXT_DOMAIN).'" class="__wps__button follow-button">', $html);
		} else {
			$html = str_replace("[follow]", '<input type="submit" ref="follow" value="'.__('Follow', WPS_TEXT_DOMAIN).'" class="__wps__button follow-button">', $html);
		}
	} else {
		$html = str_replace("[follow]", '', $html);
	}

	// Poke
	if (get_option(WPS_OPTIONS_PREFIX.'_use_poke') == 'on' && is_user_logged_in() && $uid1 != $uid2) {
		$poke = "Poke";
		$html = str_replace("[poke]", '<input type="submit" value="'.get_option(WPS_OPTIONS_PREFIX.'_poke_label').'" class="__wps__button poke-button">', $html);
	} else {
		$html = str_replace("[poke]", '', $html);
	}

	

	$location = "";
	$born = "";
	
	if ( ($uid1 == $uid2) || (is_user_logged_in() && strtolower($privacy) == 'everyone') || (strtolower($privacy) == 'public') || (strtolower($privacy) == 'friends only' && __wps__friend_of($uid1, $current_user->ID)) ) {
			
		$city = __wps__get_meta($uid1, 'extended_city');
		$country = __wps__get_meta($uid1, 'extended_country');
		
		if ($city != '') { $location .= $city; }
		if ($city != '' && $country != '') { $location .= ", "; }
		if ($country != '') { $location .= $country; }

		$day = (int)__wps__get_meta($uid1, 'dob_day');
		$month = __wps__get_meta($uid1, 'dob_month');
		$year = (int)__wps__get_meta($uid1, 'dob_year');

		if ($year > 0 || $month > 0 || $day > 0) {
			$monthname = __wps__get_monthname($month);
			if ($day == 0) $day = '';
			if ($year == 0) $year = '';
			$born = get_option(WPS_OPTIONS_PREFIX.'_show_dob_format');
			$born = ( $born != '') ? $born : __('Born', WPS_TEXT_DOMAIN).' %monthname %day%th, %year';
			$day0 = str_pad($day, 2, '0', STR_PAD_LEFT);
			$month = ($month > 0) ? str_pad($month, 2, '0', STR_PAD_LEFT) : '';
			$month0 = ($month > 0) ? str_pad($month, 2, '0', STR_PAD_LEFT) : '';
			$year = ($year > 0) ? $year : '';
			$born = str_replace('%0day', $day0, $born);
			$born = str_replace('%day', $day, $born);
			$born = str_replace('%monthname', $monthname, $born);
			$born = str_replace('%0month', $month0, $born);
			$born = str_replace('%month', $month, $born);
			$born = str_replace('%year', $year, $born);
			$th = 'th';
			if ($day == 1 || $day == 21 || $day == 31) $th = 'st';
			if ($day == 2 || $day == 22) $th = 'nd';
			if ($day == 3 || $day == 23) $th = 'rd';
			if (strpos($born, '%th')) {
				if ($day) {
					$born = str_replace('%th', $th, $born);
				} else {
					$born = str_replace('%th', '', $born);
				}
			}
			$born = str_replace(' ,', ',', $born);
			if ($year == '') $born = str_replace(', ', '', $born);
			$born = apply_filters ( '__wps__profile_born', $born, $day, $month, $year );
		
		}
		
	} else {
	
		if (strtolower($privacy) == 'friends only') {
			$html = str_replace("[born]", sprintf(__("Personal information only for %s.", WPS_TEXT_DOMAIN), get_option(WPS_OPTIONS_PREFIX.'_alt_friends')), $html);						
		}

		if (strtolower($privacy) == 'nobody') {
			$html = str_replace("[born]", __("Personal information is private.", WPS_TEXT_DOMAIN), $html);						
		}
		
	}

	$html = str_replace("[location]", $location, $html);
	if (get_option(WPS_OPTIONS_PREFIX.'_show_dob') == 'on') {
		$html = str_replace("[born]", $born, $html);
	} else {
		$html = str_replace("[born]", "", $html);
	}
	
	if ( is_user_logged_in() ) {
		
		$actions = '';
		
		if ($uid1 == $uid2) {

			if (function_exists('__wps__facebook')) {
				$actions .= "<div id='facebook_div'>";
				if ( $facebook_id = __wps__get_meta($uid2, 'facebook_id') != '') {
					$actions .= "<input type='checkbox' CHECKED id='post_to_facebook' /> ";
					$actions .= __("Post to Facebook", WPS_TEXT_DOMAIN);
					$actions .= " (<a href='javascript:void(0)' id='cancel_facebook'>".__("Cancel", WPS_TEXT_DOMAIN)."</a>)";
				} else {
					$actions .= "<img src='".WPS_PLUGIN_URL."/images/logo_facebook.png' style='float:left; margin-right: 5px;' />";
					$actions .= "<a href='javascript:void(0)' id='setup_facebook'>".__("Connect to Facebook", WPS_TEXT_DOMAIN)."</a>";
				}
				$actions .= "</div>";
			}
			
		} else {

			// Buttons									
			if (__wps__friend_of($uid1, $current_user->ID)) {
	
				// A friend
				// Send mail
				if (function_exists('__wps__mail'))
					$actions .= '<input type="submit" class="__wps__button" id="profile_send_mail_button" value="'.__('Send a Mail...', WPS_TEXT_DOMAIN).'" />';
				
			} else {
				
				if (__wps__pending_friendship($uid1)) {
					// Pending
					$actions .= '<input type="submit" title="'.$uid1.'" id="cancelfriendrequest" class="__wps__button" value="'.sprintf(__('Cancel %s Request', WPS_TEXT_DOMAIN), get_option(WPS_OPTIONS_PREFIX.'_alt_friend')).'" /> ';
					$actions .= '<div id="cancelfriendrequest_done" class="hidden addasfriend_input">'.sprintf(__('%s Request Cancelled', WPS_TEXT_DOMAIN), get_option(WPS_OPTIONS_PREFIX.'_alt_friend')).'</div>';
				} else {							
					// Not a friend
					$actions .= '<div id="addasfriend_done1_'.$uid1.'" class="addasfriend_input">';
					if (function_exists('__wps__mail') && get_option(WPS_OPTIONS_PREFIX.'_mail_all') == 'on') {
						$actions .= ' (<a href="javascript:void(0);" id="profile_send_mail_button" onclick="document.location = \''.$url.__wps__string_query($url).'view=compose&to='.$uid1.'\';">'.__('or send a private mail', WPS_TEXT_DOMAIN).'</a>)';
					}
					$actions .= '<div id="add_as_friend_message">';
					$actions .= '<input type="text" title="'.$uid1.'"id="addfriend" class="input-field" onclick="this.value=\'\'" value="'.sprintf(__('Add as a %s', WPS_TEXT_DOMAIN), get_option(WPS_OPTIONS_PREFIX.'_alt_friend')).'...."';
					if (!get_option(WPS_OPTIONS_PREFIX.'_show_buttons')) {
						$actions .= ' style="width:210px"';
					}
					$actions .= '>';
					if (get_option(WPS_OPTIONS_PREFIX.'_show_buttons')) {
						$actions .= '<input type="submit" title="'.$uid1.'" id="addasfriend" class="__wps__button" value="'.__('Add', WPS_TEXT_DOMAIN).'" /> ';
					}
	
					$actions .= '</div></div>';
					$actions .= '<div id="addasfriend_done2_'.$uid1.'" class="hidden addasfriend_input">'.sprintf(__('%s Request Sent', WPS_TEXT_DOMAIN), get_option(WPS_OPTIONS_PREFIX.'_alt_friend')).'</div>';
					
				}
			}				
			
		}
				
		$html = str_replace("[actions]", $actions, $html);						
	} else {
		$html = str_replace("[actions]", "", $html);												
	}
	
	// Photo
	if (strpos($html, '[avatar') !== FALSE) {
		if (strpos($html, '[avatar]')) {
			$html = str_replace("[avatar]", get_avatar($uid1, 200), $html);						
		} else {
			$x = strpos($html, '[avatar');
			$y = strpos($html, ']', $x);
			$diff = $y-$x-8;
			$avatar = substr($html, 0, $x);
			$avatar2 = substr($html, $x+8, $diff);
			$avatar3 = substr($html, $x+$diff+9, strlen($html)-$x-($diff+9));

			$html = $avatar . get_avatar($uid1, $avatar2) . $avatar3;
			
			
		}
	}	

	
	// Filter for profile header
	$html = apply_filters ( '__wps__profile_header_filter', $html, $uid1 );

	
	return $html;


}

/* ====================================================== SET SHORTCODE ====================================================== */

if (!is_admin()) {

	add_shortcode(WPS_SHORTCODE_PREFIX.'-stream', '__wps__stream');  
	add_shortcode(WPS_SHORTCODE_PREFIX.'-profile', '__wps__profile');  
	add_shortcode(WPS_SHORTCODE_PREFIX.'-friends', '__wps__profile_friend');  
	add_shortcode(WPS_SHORTCODE_PREFIX.'-activity', '__wps__profile_activity');  
	add_shortcode(WPS_SHORTCODE_PREFIX.'-all', '__wps__profile_all');  
	add_shortcode(WPS_SHORTCODE_PREFIX.'-personal', '__wps__profile_personal');  
	add_shortcode(WPS_SHORTCODE_PREFIX.'-settings', '__wps__profile_settings');  
	add_shortcode(WPS_SHORTCODE_PREFIX.'-extended', '__wps__profile_extended');  
	add_shortcode(WPS_SHORTCODE_PREFIX.'-avatar', '__wps__profile_avatar');  
	add_shortcode(WPS_SHORTCODE_PREFIX.'-menu', '__wps__profile_member_menu');  
	add_shortcode(WPS_SHORTCODE_PREFIX.'-member-header', '__wps__profile_member_header');  
	add_shortcode(WPS_SHORTCODE_PREFIX.'-gallery', '__wps__menu_gallery');  

}
?>
