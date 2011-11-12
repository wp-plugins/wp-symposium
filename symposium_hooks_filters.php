<?php

// *************************************** HOOKS AND FILTERS ***************************************

// Add meta box to posts for forum link
add_action( 'add_meta_boxes', 'wps_add_custom_post_box' );
add_action( 'save_post', 'wps_save_postdata' );

/* Adds a box to the main column on the Post and Page edit screens */
function wps_add_custom_post_box() {
	if (function_exists('symposium_forum')) {
	    add_meta_box( 
	        'myplugin_sectionid',
	        __( 'Link to Forum', 'myplugin_textdomain' ),
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
       _e("Select a topic", 'myplugin_textdomain' );
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
	

			if ( ( WPS_MENU_PROFILE == 'on') && ( ($uid1 == $uid2) || (is_user_logged_in() && strtolower($share) == 'everyone') || (strtolower($share) == 'public') || (strtolower($share) == 'friends only' && $is_friend) || symposium_get_current_userlevel() == 5) ) {
	
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

			if ( ($uid1 == $uid2) || (is_user_logged_in() && strtolower($share) == 'everyone') || (strtolower($share) == 'friends only' && $is_friend) || symposium_get_current_userlevel() == 5) {
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

	if (WPS_REDIRECT_WP_PROFILE == 'on' && symposium_get_current_userlevel() < 2) {
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

// Add activity comment for new forum topic posted 
function symposium_add_activity_comment($from_id, $from_name, $to_id, $url, $type = 'post') {
	
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
add_action('symposium_forum_newtopic_hook', 'symposium_add_activity_comment', 10, 4);



	
// **************************************************************************************************************



?>