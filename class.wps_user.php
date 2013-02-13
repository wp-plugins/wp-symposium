<?php

// ******************************** CURRENT USER CLASS ********************************

class wps_user {

	public function __construct($id='') {
		global $current_user;
		$id != '' ? $id = $id : $id = $current_user->ID;
		$this->id = $id;													// Set the ID of this member
		$user_info = get_userdata($id);

		$this->display_name = $user_info->display_name;						// WordPress display name
		$this->first_name = $user_info->first_name;							// WordPress first name
		$this->last_name = $user_info->last_name;							// WordPress last name
		$this->user_login = $user_info->user_login;							// WordPress user login
		$this->user_email = $user_info->user_email;							// WordPress user email address
		$this->city = __wps__get_meta($id, 'extended_city');				// City
		$this->country = __wps__get_meta($id, 'extended_country');		// Country
		$this->avatar = '';													// Avatar (readonly)
		$this->latest_activity = '';										// Most recent activity post
		$this->activity_privacy = __wps__get_meta($id, 'wall_share');	// Privacy for sharing activity
		$this->dob_day = __wps__get_meta($id, 'dob_day');				// Date of Birth (day)
		$this->dob_month = __wps__get_meta($id, 'dob_month');			// Date of Birth (month)
		$this->dob_year = __wps__get_meta($id, 'dob_year');				// Date of Birth (year)		
		$this->last_activity = __wps__get_meta($id, 'last_activity');	// When last active		
		
	}
	
	
	/* Following methods provide get/set functionality ______________________________________ */
	
	// Member ID
    function get_id() {
		return $this->id;
    }	
	
	// First name
	function set_first_name($value) {
    	$this->first_name = $value;
		wp_update_user( array ('ID' => $this->id, 'first_name' => $value) );
    }
    function get_first_name() {
		return $this->first_name;
    }	

	// Last name
	function set_last_name($value) {
    	$this->last_name = $value;
		wp_update_user( array ('ID' => $this->id, 'last_name' => $value) );
    }
    function get_last_name() {
		return $this->last_name;
    }	

	// Display name
	function set_display_name($value) {
    	$this->display_name = $value;
		wp_update_user( array ('ID' => $this->id, 'display_name' => $value) );
    }
    function get_display_name() {
		return $this->display_name;
    }	

	// Member login
	function set_user_login($value) {
    	$this->user_login = $value;
		wp_update_user( array ('ID' => $this->id, 'user_login' => $value) );
    }
    function get_user_login() {
		return $this->user_login;
    }	

	// Member email address
	function set_user_email($value) {
    	$this->user_email = $value;
		wp_update_user( array ('ID' => $this->id, 'user_email' => $value) );
    }
    function get_user_email() {
		return $this->user_email;
    }	

	// Member city
	function set_city($value) {
    	$this->city = $value;
		__wps__update_meta($this->id, 'city', $value);
    }
    function get_city() {
		return $this->city;
    }

	// Member country
	function set_country($value) {
    	$this->country = $value;
		__wps__update_meta($this->id, 'country', $value);
    }
    function get_country() {
		return $this->country;
    }   
   
   	// Member avatar
    function get_avatar($size=64) {
		return get_avatar($this->id, $size);
    }        

	// Member latest activity
	function set_latest_activity($value) {
		global $wpdb;
		$wpdb->query( $wpdb->prepare( "
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
        	$this->id, 
        	$this->id, 
        	0,
        	date("Y-m-d H:i:s"),
        	$value,
        	''
        	) 
        ) );
    }
    function get_latest_activity($as_row=false) {
        global $wpdb;
        if (!$as_row) {
	        $comment = stripslashes($wpdb->get_var( $wpdb->prepare("SELECT comment FROM ".$wpdb->base_prefix."symposium_comments WHERE subject_uid = %d AND author_uid = %d AND comment_parent = 0 ORDER BY cid DESC LIMIT 0,1", $this->id, $this->id)));
        } else {
            $comment = $wpdb->get_row( $wpdb->prepare("SELECT comment FROM ".$wpdb->base_prefix."symposium_comments WHERE subject_uid = %d AND author_uid = %d AND comment_parent = 0 ORDER BY cid DESC LIMIT 0,1", $this->id, $this->id));
        }
		return $comment;
    }        
    function get_latest_activity_age() {
        global $wpdb;
		$dateof = $wpdb->get_var( $wpdb->prepare("SELECT comment_timestamp FROM ".$wpdb->base_prefix."symposium_comments WHERE subject_uid = %d AND author_uid = %d AND comment_parent = 0 ORDER BY cid DESC LIMIT 0,1", $this->id, $this->id));
		return $dateof;
    }        
	function set_activity_privacy($value) {
    	$this->activity_privacy = $value;
		__wps__update_meta($this->id, 'wall_share', $value);
    }
    function get_activity_privacy() {
		return $this->activity_privacy;
    }   
    
    // Last active
    function get_last_activity() {
		return $this->last_activity;
    }	    
    
    // Member Date of birth
	function set_dob_day($value) {
    	$this->dob_day = $value;
		__wps__update_meta($this->id, 'dob_day', $value);
    }
    function get_dob_day() {
		return $this->dob_day;
    }
	function set_dob_month($value) {
    	$this->dob_month = $value;
		__wps__update_meta($this->id, 'dob_month', $value);
    }
    function get_dob_month() {
		return $this->dob_month;
    }
	function set_dob_year($value) {
    	$this->dob_year = $value;
		__wps__update_meta($this->id, 'dob_year', $value);
    }
    function get_dob_year() {
		return $this->dob_year;
    }
    
    // Get single extended field
    function get_user_meta($uid, $slug) {
		return __wps__get_meta($uid, $slug);
    }
    
    // Member Extended Profile information
    function get_extended() {
        global $wpdb;
        
		$extended = __wps__get_meta($this->id, 'extended');					
		$fields = explode('[|]', $extended);
		$has_extended_fields = false;
		if ($fields) {
			$ext_rows = array();
			foreach ($fields as $field) {
				$split = explode('[]', $field);
				if ( ($split[0] != '') && ($split[1] != '') ) {
				
					$extension = $wpdb->get_row($wpdb->prepare("SELECT extended_slug,extended_name,extended_order,extended_type FROM ".$wpdb->base_prefix."symposium_extended WHERE eid = %d", $split[0]));

					$ext = array (	'name'=>$extension->extended_name,
									'value'=>__wps__make_url($split[1]),
									'type'=>$extension->extended_type,
									'order'=>$extension->extended_order );
					array_push($ext_rows, $ext);
				}
			}
			$ext_rows = __wps__sub_val_sort($ext_rows,'order');
			return $ext_rows;
		} else {
			return '';
		}
    }
    
    // Member friends
    // Returns array of friend IDs, ordered by last activity
    function get_friends($max=10) {
	    global $wpdb;

	    $sql = "SELECT f.friend_to AS id FROM ".$wpdb->base_prefix."symposium_friends f
	    		WHERE friend_from = %d AND friend_accepted = 'on' LIMIT 0,%d";
	 	$friends_list = $wpdb->get_results($wpdb->prepare($sql, $this->id, $max));
	 	
	 	if ($friends_list) {
			$friends_array = array();
			foreach ($friends_list as $friend) {
	
				$add = array (	
					'id' => $friend->id,
					'last_activity' => __wps__get_meta($friend->id, 'last_activity')
				);
				array_push($friends_array, $add);
			}
			$friends = __wps__sub_val_sort($friends_array, 'last_activity', false);
		} else {
			$friends = false;
		}
				 	
	 	return $friends;
    }
    
    // Profile page URL
	function get_profile_url() {
		return __wps__profile_link($this->id);
	}
   
	/* Following methods check for various conditions and return boolean value ______________________________________ */
	
    function is_permitted($type='activity') {
		return user_has_permission($this->id, $type);    
    }

	function is_friend() {
	   	global $wpdb,$current_user;
		if ( $wpdb->get_var($wpdb->prepare("SELECT * FROM ".$wpdb->base_prefix."symposium_friends WHERE (friend_accepted = 'on') AND (friend_from = %d AND friend_to = %d)", $this->id, $current_user->ID)) ) {
			return true;
		} else {
			return false;
		}
	}

	function is_pending_friend() {
	   	global $wpdb,$current_user;		
		$sql = "SELECT * FROM ".$wpdb->base_prefix."symposium_friends WHERE (friend_accepted != 'on') AND ((friend_from = %d AND friend_to = %d) OR (friend_to = %d AND friend_from = %d))";
		if ( $wpdb->get_var($wpdb->prepare($sql, $this->id, $current_user->ID, $this->id, $current_user->ID)) ) {
			return true;
		} else {
			return false;
		}
	} 


}

/* Single functions to reduce duplication above ____________________________________________________________________________ */

function user_has_permission($id, $type) {
	global $wpdb,$current_user;
	if ($type == 'activity') $type = 'wall_share';
	if ($type == 'personal') $type = 'share';
	$prviacy = __wps__get_meta($id, $type);
	if (is_user_logged_in() || $privacy == 'public') {	
		$is_friend = __wps__friend_of($id, $current_user->ID);
		if ((WPS_CURRENT_USER_PAGE == $current_user->ID) || (is_user_logged_in() && strtolower($privacy) == 'everyone') || (strtolower($privacy) == 'public') || (strtolower($privacy) == 'friends only' && $is_friend) || __wps__get_current_userlevel() == 5) {
			return true;
		}
	}
	return false;
}

?>
