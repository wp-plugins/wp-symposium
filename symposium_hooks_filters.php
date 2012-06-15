<?php
		
// *************************************** HOOKS AND FILTERS ***************************************

function symposium_query_var( $query_vars ){
	if ( get_option('wps_permalink_structure') ) {
		array_push($query_vars, 'stub');
	}
    return $query_vars;
}
add_filter( 'query_vars', 'symposium_query_var' );



/* Add meta box to posts for forum link */
add_action( 'add_meta_boxes', 'wps_add_custom_post_box' );
add_action( 'save_post', 'wps_save_postdata' );

/* Adds a box to the main column on the Post and Page edit screens */
function wps_add_custom_post_box() {
	if (function_exists('symposium_forum')) {
	    add_meta_box( 
	        'myplugin_sectionid',
	        __( 'Link to Forum', 'wp-symposium' ),
	        'wps_inner_custom_box',
	        'post' 
	    );
	}
}

/* Prints the box content */
function wps_inner_custom_box( $post ) {
	global $wpdb;
  // Use nonce for verification
  wp_nonce_field( plugin_basename( __FILE__ ), 'myplugin_noncename' );

  // The actual fields for data entry
  echo '<label for="myplugin_new_field">';
       _e("Select a topic", 'wp-symposium' );
  echo '</label> ';
  $value = get_post_meta($post->ID, 'WPS post link', true);
  echo '<select id="myplugin_new_field" name="myplugin_new_field">';
  $sql = "SELECT tid, topic_subject FROM ".$wpdb->prefix."symposium_topics WHERE topic_parent = 0 AND topic_group = 0 ORDER BY topic_subject";
  $topics = $wpdb->get_results($sql);
  echo '<option value=0';
  if ($value == 0 || $value == '') { echo " SELECTED"; }
  echo '>'.__('None', 'wp-symposium').'</option>';  
  if ($topics) {
	  foreach ($topics AS $topic) {
	      echo '<option value='.$topic->tid;
	      if ($topic->tid == $value) { echo " SELECTED"; }
	      echo '>'.stripslashes($topic->topic_subject).'</option>';
	  }
  }
  echo '</select>';
}

/* When the post is saved, saves our custom data */
function wps_save_postdata( $post_id ) {
  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
      return;

  if ( !isset($_POST['myplugin_noncename']) || !wp_verify_nonce( $_POST['myplugin_noncename'], plugin_basename( __FILE__ ) ) )
      return;

  // Check permissions
  if ( 'page' == $_POST['post_type'] ) 
  {
    if ( !current_user_can( 'edit_page', $post_id ) )
        return;
  }
  else
  {
    if ( !current_user_can( 'edit_post', $post_id ) )
        return;
  }

  // OK, we're authenticated: we need to find and save the data
  $mydata = $_POST['myplugin_new_field'];

  // Do something with $mydata 
  update_post_meta($post_id, 'WPS post link', $mydata);
}

add_filter( 'the_content', 'wps_post_content_filter', 10 );
function wps_post_content_filter( $content ) {

    if ( is_single() && function_exists('symposium_forum') ) {

    	$value = get_post_meta($GLOBALS['post']->ID, 'WPS post link', true);
    	
    	if ($value && $value != '') {
	    	$forum_url = symposium_get_url('forum');
			$q = symposium_string_query($forum_url);		
    		$content .= "<p><a href='".$forum_url.$q."show=".$value."'>".__('Discuss on the forum...', 'wp-symposium')."</a></p>";
    	}
    	
    }

    return $content;
}

// Profile Menu hook
function add_symposium_profile_menu($html,$uid1,$uid2,$privacy,$is_friend,$extended,$share)  
{  
	global $wpdb,$current_user;
	

			if ( ( get_option('symposium_menu_profile') == 'on') && ( ($uid1 == $uid2) || (is_user_logged_in() && strtolower($share) == 'everyone') || (strtolower($share) == 'public') || (strtolower($share) == 'friends only' && $is_friend) || symposium_get_current_userlevel() == 5) ) {
	
				if ($extended != '' || $uid1 == $uid2) {
					if ($uid1 == $uid2) {
						$html .= '<div id="menu_extended" class="symposium_profile_menu">'.__('My Profile', 'wp-symposium').'</div>';
					} else {
						$html .= '<div id="menu_extended" class="symposium_profile_menu">'.__('Profile', 'wp-symposium').'</div>';
					}
				}
			}

			if  ( ($uid1 == $uid2) || (is_user_logged_in() && strtolower($privacy) == 'everyone') || (strtolower($share) == 'public') || (strtolower($privacy) == 'friends only' && $is_friend) || symposium_get_current_userlevel() == 5) {

				if ($uid1 == $uid2) {
					if (get_option('symposium_menu_my_activity') == 'on') {
						$html .= '<div id="menu_wall" class="symposium_profile_menu">'.__('My Activity', 'wp-symposium').'</div>';
					}
					if (get_option('symposium_menu_friends_activity') == 'on') {
						if (strtolower($share) == 'public' && !(is_user_logged_in())) {
							// don't show friends activity to public
						} else {
							$html .= '<div id="menu_activity" class="symposium_profile_menu">'.__('My Friends Activity', 'wp-symposium').'</div>';
						}
					}
				} else {
					if (get_option('symposium_menu_my_activity') == 'on') {
						$html .= '<div id="menu_wall" class="symposium_profile_menu">'.__('Activity', 'wp-symposium').'</div>';
					}
					if (get_option('symposium_menu_friends_activity') == 'on') {
						if (strtolower($share) == 'public' && !(is_user_logged_in())) {
							// don't show friends activity to public
						} else {
							$html .= '<div id="menu_activity" class="symposium_profile_menu">'.__('Friends Activity', 'wp-symposium').'</div>';
						}
					}
				}
				if (get_option('symposium_menu_all_activity') == 'on') {
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

			if ( ($uid1 == $uid2) || (is_user_logged_in() && strtolower($share) == 'everyone') || (strtolower($share) == 'friends only' && $is_friend) || symposium_get_current_userlevel() == 5) {
				if (get_option('symposium_menu_friends') == 'on') {
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
	
	$texthtml = get_option('symposium_menu_texthtml');
	
	return $texthtml;
}
add_action('symposium_profile_menu_end_filter', 'add_symposium_profile_menu_texthtml', 8, 7);

// Non-admin Header hook
function symposium_header() {
	include_once('symposium_styles.php');	

	// Do check for Bronze plugins and activation code
	$has_bronze_plug_actived = false;
	if (function_exists('symposium_news_main') || 
		function_exists('symposium_facebook') || 
		function_exists('symposium_gallery') || 
		function_exists('symposium_profile_plus') || 
		function_exists('symposium_events_main') || 
		function_exists('symposium_group') || 
		function_exists('symposium_rss_main')
	) {
		$has_bronze_plug_actived = true;
    }
	
	if ($has_bronze_plug_actived && !symposium_is_plus()) {
		echo '<div style="border:1px solid #000; text-align:center; background-color:white; color: black; font-size:12px; padding:6px 12px 6px 12px; margin-left:auto; margin-right:auto; margin-top:10px; margin-bottom:10px; border-radius:5px;">';
		echo __('You have activated a <a href="'.get_admin_url().'plugins.php">Bronze WP Symposium plugin</a>, but you have not entered your <a href="'.get_admin_url().'admin.php?page=symposium_debug">Activation Code</a>.', 'wp-symposium').' ';
		echo __('You can get your Activation Code on the <a href="http://www.wpsymposium.com/membership" target="_blank">Membership page</a> on the WP Symposium website.', 'wp-symposium');
		echo '</div>';
	}		

	if (WPS_DEBUG) {
		echo '<div style="overflow:auto; border:1px solid #000; background-color:#ccc; color: black; font-size:12px; padding:6px 12px 6px 12px; margin-left:auto; margin-right:auto; margin-top:10px; margin-bottom:10px; border-radius:5px;">';
		echo '<input style="float:right" id="symposium_deactivate_debug" type="submit" value="'.__('De-activate', 'wp-symposium').'" />';
		echo '<strong>'.__('WP Symposium Debug Mode', 'wp-symposium').'</strong><br />';

		global $wp_rewrite;
		echo '<a href="javascript:void(0);" rel="rewrite_rules" class="symposium-dialog">Show rewrite rules</a><br />';
			echo '<div id="rewrite_rules" title="Rewrite rules" style="display:none;margin-top:10px;background-color:#fff;color:#000;padding:6px;border:1px solid #000; border-radius:3px;">';
			echo symposium_displayArrayContentFunction($wp_rewrite->rewrite_rules());
			echo '</div>';

		echo '</div>';
	}
	
}

// Admin Header hook
function symposium_admin_header() {

	if (get_option('symposium_redirect_wp_profile') == 'on' && symposium_get_current_userlevel() < 2) {
		if ( strpos($_SERVER['PHP_SELF'], "wp-admin/profile.php") !== FALSE ) {
			if (function_exists('symposium_profile')) {
				$profile_page = symposium_get_url('profile');
				if ( (isset($_GET['uid'])) && ($_GET['uid'] != '') ) {
					$uid = symposium_string_query($profile_page).'uid='.$_GET['uid'];
				} else {
					$uid = '';
				}
				wp_redirect( $profile_page.$uid );
			}
		}
	}
}
if ( is_admin() )
	add_action( 'admin_menu', 'symposium_admin_header' );
	
// ****** Hooks and Filters to add comments when certain things happen to activity ******************************

// Add activity comment 
function symposium_add_activity_comment($from_id, $from_name, $to_id, $url, $type, $var=0) {
	
	global $wpdb;
	
	$success = ($wpdb->query( $wpdb->prepare( "
		INSERT INTO ".$wpdb->base_prefix."symposium_comments
		( 	subject_uid, 
			author_uid,
			comment_parent,
			comment_timestamp,
			comment,
			is_group,
			type
		)
		VALUES ( %d, %d, %d, %s, %s, %s, %s )", 
        array(
        	$to_id, 
        	$from_id, 
        	0,
        	date("Y-m-d H:i:s"),
        	$url,
        	'',
        	$type
        	) 
        ) ) );	        
        
}
add_action('symposium_forum_newtopic_hook', 'symposium_add_activity_comment', 10, 6);

// **************************************************************************************************************

// Add WPS items to profile page and save them when admin or user saves profile

function show_symposium_metadata($user) {
global $wpdb;
	$uid = $user->ID;
	
	// get values
	$dob_day = get_symposium_meta($uid, 'dob_day');
	$dob_month = get_symposium_meta($uid, 'dob_month');
	$dob_year = get_symposium_meta($uid, 'dob_year');
	$city = get_symposium_meta($uid, 'extended_city');
	$country = get_symposium_meta($uid, 'extended_country');
	$share = get_symposium_meta($uid, 'share');
	$wall_share = get_symposium_meta($uid, 'wall_share');
	if (function_exists('symposium_rss_main')) {
		$rss_share = get_symposium_meta($uid, 'rss_share');
	} else {
		$rss_share = '';
	}
	$trusted = get_symposium_meta($uid, 'trusted');
	$notify_new_messages = get_symposium_meta($uid, 'notify_new_messages');
	$notify_new_wall = get_symposium_meta($uid, 'notify_new_wall');
	$forum_all = get_symposium_meta($uid, 'forum_all');
	$signature = get_symposium_meta($uid, 'signature');
	
	$html .= '<h3>' . __("Profile Details", "wp-symposium") . '</h3>';

	$html .= '<table class="form-table">';
	
	// Share personal information
	$html .= '<tr><th><label for="share">'.__('Who do you want to share personal information with?', 'wp-symposium').'</label></th>';
	$html .= '<td><select id="share" name="share">';
	$html .= "<option value='Nobody'";
		if ($share == 'Nobody') { $html .= ' SELECTED '; }
		$html .= '>'.__('Nobody', 'wp-symposium').'</option>';
	$html .= "<option value='Friends only'";
		if ($share == 'Friends only') { $html .= ' SELECTED '; }
		$html .= '>'.__('Friends Only', 'wp-symposium').'</option>';
	$html .= "<option value='Everyone'";
		if ($share == 'Everyone') { $html .= ' SELECTED '; }
		$html .= '>'.__('Everyone', 'wp-symposium').'</option>';
	$html .= "<option value='public'";
		if ($share == 'public') { $html .= ' SELECTED '; }
		$html .= '>'.__('Public', 'wp-symposium').'</option>';
	$html .= '</select></td></tr>';
	
	// Share Wall / Activity
	$html .= '<tr><th><label for="wall_share">'.__('Who do you want to share your activity with?', 'wp-symposium').'</label></th>';
	$html .= '<td><select id="wall_share" name="wall_share">';
	$html .= "<option value='Nobody'";
		if ($wall_share == 'Nobody') { $html .= ' SELECTED '; }
		$html .= '>'.__('Nobody', 'wp-symposium').'</option>';
	$html .= "<option value='Friends only'";
		if ($wall_share == 'Friends only') { $html .= ' SELECTED '; }
		$html .= '>'.__('Friends Only', 'wp-symposium').'</option>';
	$html .= "<option value='Everyone'";
		if ($wall_share == 'Everyone') { $html .= ' SELECTED '; }
		$html .= '>'.__('Everyone', 'wp-symposium').'</option>';
	$html .= "<option value='public'";
		if ($wall_share == 'public') { $html .= ' SELECTED '; }
		$html .= '>'.__('Public', 'wp-symposium').'</option>';
	$html .= '</select></td></tr>';
	
	// Publish RSS feed?
	if (function_exists('symposium_rss_main')) {
		$html .= '<tr><th><label for="rss_share">'.__('RSS feed', 'wp-symposium').'</label></th>';
		$html .= '<td><select id="rss_share" name="rss_share">';
			$html .= "<option value=''";
				if ($rss_share == '') { $html .= ' SELECTED '; }
				$html .= '>'.__('No', 'wp-symposium').'</option>';
			$html .= "<option value='on'";
				if ($rss_share == 'on') { $html .= ' SELECTED '; }
				$html .= '>'.__('Yes', 'wp-symposium').'</option>';
		$html .= '</select> ';
		$html .= '<span class="description">'.__('Publish your activity via RSS (only your initial posts)?', 'wp-symposium').'</span>';
		$html .= '</td></tr>';
	} else {
		$html .= '<input type="hidden" id="rss_share" value="">';
	}
	
	// Birthday
	if (get_option('symposium_show_dob') == 'on') {

		$html .= '<tr><th><label for="dob">'.__('Your date of birth', 'wp-symposium').'</label></th>';
		$html .= '<td><select id="dob_day" name="dob_day">';
			$html .= '<option value=0';
				if ($dob_day == 0) { $html .= ' SELECTED '; }
				$html .= '>---</option>';
			for ($i = 1; $i <= 31; $i++) {
				$html .= '<option value="'.$i.'"';
					if ($dob_day == $i) { $html .= ' SELECTED '; }
					$html .= '>'.$i.'</option>';
			}
		$html .= '</select> / ';									
		$html .= '<select id="dob_month" name="dob_month">';
			$html .= '<option value=0';
				if ($dob_month == 0) { $html .= ' SELECTED '; }
				$html .= '>---</option>';
			for ($i = 1; $i <= 12; $i++) {
				switch($i) {									
					case 1:$monthname = __("January", "wp-symposium");break;
					case 2:$monthname = __("February", "wp-symposium");break;
					case 3:$monthname = __("March", "wp-symposium");break;
					case 4:$monthname = __("April", "wp-symposium");break;
					case 5:$monthname = __("May", "wp-symposium");break;
					case 6:$monthname = __("June", "wp-symposium");break;
					case 7:$monthname = __("July", "wp-symposium");break;
					case 8:$monthname = __("August", "wp-symposium");break;
					case 9:$monthname = __("September", "wp-symposium");break;
					case 10:$monthname = __("October", "wp-symposium");break;
					case 11:$monthname = __("November", "wp-symposium");break;
					case 12:$monthname = __("December", "wp-symposium");break;
				}
				$html .= '<option value="'.$i.'"';
					if ($dob_month == $i) { $html .= ' SELECTED '; }
					$html .= '>'.$monthname.'</option>';
			}
		$html .= '</select> / ';									
		$html .= '<select id="dob_year" name="dob_year">';
			$html .= '<option value=0';
				if ($dob_year == 0) { $html .= ' SELECTED '; }
				$html .= '>---</option>';
			for ($i = date("Y"); $i >= 1900; $i--) {
				$html .= '<option value="'.$i.'"';
					if ($dob_year == $i) { $html .= ' SELECTED '; }
					$html .= '>'.$i.'</option>';
			}
			$html .= '</td></select>';									
	
	} else {
	
		$html .= '<input type="hidden" id="dob_day" value="'.$dob_day.'">';
		$html .= '<input type="hidden" id="dob_month" value="'.$dob_month.'">';
		$html .= '<input type="hidden" id="dob_year" value="'.$dob_year.'">';
	
	}
	
	// City
	$html .= '<tr><th><label for="extended_city">'.__('Which town/city are you in?', 'wp-symposium').'</label></th>';
	$html .= '<td><input type="text" class="input-field" id="extended_city" name="extended_city" style="width:300px" value="'.trim($city, "'").'">';
	$html .= '</td></tr>';
	
	// Country
	$html .= '<tr><th><label for="extended_country">'.__('Which country are you in?', 'wp-symposium').'</label></th>';
	$html .= '<td><input type="text" class="input-field" id="extended_country" name="extended_country" style="width:300px" value="'.trim($country, "'").'">';
	$html .= '</td></tr>';
	
	// Google map
	if ( ($city != '' || $country != '') && (get_option('symposium_profile_google_map') > 0) ){ 	
						
		$html .= '<tr><th></th><td>';
		$html .= '<a target="_blank" style="width:'.get_option('symposium_profile_google_map').'px; height:'.get_option('symposium_profile_google_map').'px;" href="http://maps.google.co.uk/maps?f=q&amp;source=embed&amp;hl=en&amp;geocode=&amp;q='.$city.',+'.$country.'&amp;ie=UTF8&amp;hq=&amp;hnear='.$city.',+'.$country.'&amp;output=embed&amp;z=5" alt="Click on map to enlarge" title="Click on map to enlarge">';
		$html .= '<img src="http://maps.google.com/maps/api/staticmap?center='.$city.',.+'.$country.'&zoom=5&size='.get_option('symposium_profile_google_map').'x'.get_option('symposium_profile_google_map').'&maptype=roadmap&markers=color:blue|label:&nbsp;|'.$city.',+'.$country.'&sensor=false" />';
		$html .= '</a><br /><span class="description"> '.__("The Google map that will be displayed on top of your WP Symposium profile page, resulting from your personal data above.").'</span></td></tr>';
	
	}
	
	// Extensions
	$extensions = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."symposium_extended ORDER BY extended_order, extended_name"));
	if ($extensions) {
	
		$sql = "SELECT * FROM ".$wpdb->base_prefix."usermeta WHERE user_id = ".$uid." AND meta_key like 'symposium_extended_%'";
		$fields = $wpdb->get_results($sql);
		
		foreach ($extensions as $extension) {
			
			// Don't display Extended Fields that are associated to WP usermeta data, they should be displayed somewhere else in the dashboard
			if ( $extension->wp_usermeta == '' ) {
			
				$value = $extension->extended_default;
				if ($extension->extended_type == "List") {
					$default_list = explode(',', $extension->extended_default);
					$value = $default_list[0];
				}
				foreach ($fields as $field) {
					$slug = str_replace('symposium_extended_', '', $field->meta_key);
					if ($slug == $extension->extended_slug) { $value = $field->meta_value; break; }
				}
				
				// Draw the object according to type
				switch ($extension->extended_type) :
				case 'Text' :
					$html .= '<tr><th><label for="extended_'.$slug.'">'.stripslashes($extension->extended_name).'</label></th>';
					$html .= '<td><input type="text" class="input-field" id="extended_'.$slug.'" name="extended_'.$slug.'" style="width:300px" value="'.$value.'"';
					if ( $extension->readonly ) { $html .= ' disabled="disabled"'; }
					$html .= ' /></td></tr>';
				break;
				case 'Textarea' :
					$html .= '<tr><th><label for="extended_'.$slug.'">'.stripslashes($extension->extended_name).'</label></th>';
					$html .= '<td><textarea rows="5" cols="30" id="extended_'.$slug.'" name="extended_'.$slug.'"';
					if ( $extension->readonly ) { $html .= ' disabled="disabled"'; }
					$html .= '>'.$value.'</textarea></td></tr>';
				break;
				case 'List' :
					$html .= '<tr><th><label for="extended_'.$slug.'">'.stripslashes($extension->extended_name).'</label></th>';
					$html .= '<td><select id="extended_'.$slug.'" name="extended_'.$slug.'"';
					if ( $extension->readonly ) { $html .= ' disabled="disabled"'; }
					$html .= '>';
					foreach ($default_list as $list_value) {
						$html .= '<option value="'.$list_value.'"';
						if ( $value == $list_value) { $html .= ' SELECTED '; }
						$html .= '>'.$list_value.'</option>';
					}
					$html .= '</select></td></tr>';
				break;
				case 'Checkbox' :
					$html .= '<tr><th><label for="extended_'.$slug.'">'.stripslashes($extension->extended_name).'</label></th>';
					$html .= '<td><input type="checkbox" id="extended_'.$slug.'" name="extended_'.$slug.'"';
					if ( $extension->readonly ) { $html .= ' disabled="disabled"'; }
					if ( $value == 'on') { $html .= ' CHECKED '; }
					$html .= '/></td>';
					$html .= '</tr>';
				break;
				endswitch;
			}
		}
	}
	
	$html .= '</table>';
	
	$html .= '<h3>' . __("Community Settings", "wp-symposium") . '</h3>';
	$html .= '<table class="form-table">';
	
	// Trusted member (for example, for support staff)
	if (symposium_get_current_userlevel() == 5) {
		$html .= '<tr><th><label for="trusted">'.__('Trusted Member?', 'wp-symposium').'</label></th>';
		$html .= '<td><input type="checkbox" name="trusted" id="trusted"';
		if ($trusted == 'on') { $html .= ' CHECKED '; }
		$html .= '/> ';
		$html .= '<span class="description">'.__('Is this member trusted?', 'wp-symposium').'</span>';
		$html .= '</td></tr>';
	} else {
		$html .= '<tr><td><input type="hidden" name="trusted_hidden" id="trusted_hidden" value="'.$trusted.'" /><td></tr>';
	}
	
	// profile_photo, avatar
	if ( get_option('show_avatars') ) {
		// AG - select your avatar here -->
	}
	
	// forum_digest
	
	// Email notifications for private messages
	$html .= '<tr><th><label for="notify_new_messages">'.__('Emails for private messages', 'wp-symposium').'</label></th>';
	$html .= '<td><input type="checkbox" name="notify_new_messages" id="notify_new_messages"';
	if ($notify_new_messages =='on') { $html .= ' CHECKED '; }
	$html .= '/> ';
	$html .= '<span class="description">'.__('Receive an email when you get new mail messages?', 'wp-symposium').'</span>';
	$html .= '</td></tr>';
	
	// Email notifications for wall posts
	$html .= '<tr><th><label for="notify_new_wall">'.__('Emails for posts on the Wall', 'wp-symposium').'</label></th>';
	$html .= '<td><input type="checkbox" name="notify_new_wall" id="notify_new_wall"';
	if ($notify_new_wall == 'on') { $html .= ' CHECKED '; }
	$html .= '/> ';
	$html .= '<span class="description">'.__('Receive an email when a friend adds a post?', 'wp-symposium').'</span>';
	$html .= '</td></tr>';
	
	if (function_exists('symposium_forum')) {
		
		// Email notifications for all forum activity
		$html .= '<tr><th><label for="forum_all">'.__('Emails for all new forum topics and replies', 'wp-symposium').'</label></th>';
		$html .= '<td><input type="checkbox" name="forum_all" id="forum_all"';
		if ($forum_all == 'on') { $html .= ' CHECKED '; }
		$html .= '/> ';
		$html .= '<span class="description">'.__('Receive an email for all new forum topics and replies?', 'wp-symposium').'</span><br />';
		$html .= '</td></tr>';
	
		// Signature in the forum
		$html .= '<tr><th><label for="signature">'.__('Forum signature', 'wp-symposium').'</label></th>';
		$html .= '<td><input type="text" class="input-field" id="signature" name="signature" style="width:300px" value="'.stripslashes(trim($signature, "'")).'"><br />';
		$html .= '<span class="description">'.__('If you want a signature to be appended automatically under your forum posts', 'wp-symposium').'</span></td></tr>';
	}
	
	// Facebook
	// AG - the return value needs to be dealt with...
	
	$html .= '</table>';
	
	echo $html;
}
// Runs near the end of the user profile editing screen when the page is displayed by the user. Action function argument: profileuser.
add_action("show_user_profile", "show_symposium_metadata", $user, 10, 1);
// Runs near the end of the user profile editing screen when the page is displayed in the admin menus. Action function argument: profileuser.
add_action("edit_user_profile", "show_symposium_metadata", $user, 10, 1);

function save_symposium_metadata($uid) {
	global $wpdb;
	
	if ( $_POST["action"] == 'update' ) {
		update_symposium_meta($uid, 'extended_city', isset($_POST["extended_city"]) ? addslashes($_POST["extended_city"]) : "");
		update_symposium_meta($uid, 'extended_country', isset($_POST["extended_country"]) ? addslashes($_POST["extended_country"]) : "");
		update_symposium_meta($uid, 'dob_day', isset($_POST["dob_day"]) ? $_POST["dob_day"] : "");
		update_symposium_meta($uid, 'dob_month', isset($_POST["dob_month"]) ? $_POST["dob_month"] : "");
		update_symposium_meta($uid, 'dob_year', isset($_POST["dob_year"]) ? $_POST["dob_year"] : "");
		update_symposium_meta($uid, 'share', isset($_POST["share"]) ? $_POST["share"] : "");
		update_symposium_meta($uid, 'wall_share', isset($_POST["wall_share"]) ? $_POST["wall_share"] : "");
		update_symposium_meta($uid, 'symposium_forum_digest', isset($_POST["forum_digest"]) ? $_POST["forum_digest"] : "");
		update_symposium_meta($uid, 'notify_new_messages', isset($_POST["notify_new_messages"]) ? $_POST["notify_new_messages"] : "");
		update_symposium_meta($uid, 'notify_new_wall', isset($_POST["notify_new_wall"]) ? $_POST["notify_new_wall"] : "");
		update_symposium_meta($uid, 'forum_all', isset($_POST["forum_all"]) ? $_POST["forum_all"] : "");
		update_symposium_meta($uid, 'signature', isset($_POST["signature"]) ? addslashes($_POST["signature"]) : "");
		update_symposium_meta($uid, 'trusted', isset($_POST["trusted"]) ? $_POST["trusted"] : "");
		update_symposium_meta($uid, 'facebook_id', isset($_POST["facebook_id"]) ? $_POST["facebook_id"] : "");
		update_symposium_meta($uid, 'rss_share', isset($_POST["rss_share"]) ? $_POST["rss_share"] : "");
		
		// loop over extensions' $_POSTs
		$extensions = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."symposium_extended ORDER BY extended_order, extended_name"));
		if ($extensions) {
			$sql = "SELECT * FROM ".$wpdb->base_prefix."usermeta WHERE user_id = ".$uid." AND meta_key like 'symposium_extended_%'";
			$fields = $wpdb->get_results($sql);
			
			foreach ($extensions as $extension) {
				
				// Don't update Extended Fields that are associated to WP usermeta data, they should be updated somewhere else in the dashboard
				if ( $extension->wp_usermeta == '' ) {
				
					$slug = 'extended_'.$extension->extended_slug;
					$value = ( isset($_POST[$slug])) ? $_POST[$slug] : "";
					if ($extended_type == 'Checkbox') {
						$value = ($value == 'on') ? true : false;
					}
					update_symposium_meta($uid, $slug, $value);
				}
			}
		}
	}
}
// Runs when the page data is edited by the user. Action function argument: user ID.
add_action("personal_options_update", "save_symposium_metadata", $uid, 10, 1);
// Runs when the page data is edited by an admin. Action function argument: user ID.
add_action("edit_user_profile_update", "save_symposium_metadata", $uid, 10, 1);

?>
