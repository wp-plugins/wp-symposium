<?php

// ******************************** WP SYMPOSIUM WPS CURRENT USER CLASS ********************************

class wps_user {

	public function __construct($id='') {
		global $current_user;
		$id != '' ? $id = $id : $id = $current_user->ID;
		$this->id = $id;											// Set the ID of this member
		$user_info = get_userdata($id);
		$wps_current_user = get_symposium_meta_row($id);
		
		$this->display_name = $user_info->display_name;				// WordPress display name
		$this->first_name = $user_info->first_name;					// WordPress first name
		$this->last_name = $user_info->last_name;					// WordPress last name
		$this->user_login = $user_info->user_login;					// WordPress user login
		$this->user_email = $user_info->user_email;					// WordPress user email address
		$this->city = $wps_current_user->city;						// City
		$this->country = $wps_current_user->country;				// Country
		$this->avatar = '';											// Avatar (readonly)
		$this->latest_activity = '';								// Most recent activity post
		$this->activity_privacy = $user_info->wall_share;			// Privacy for sharing activity
		$this->dob_day = $wps_current_user->dob_day;				// Date of Birth (day)
		$this->dob_month = $wps_current_user->dob_month;			// Date of Birth (month)
		$this->dob_year = $wps_current_user->dob_year;				// Date of Birth (year)		
		$this->last_activity = $wps_current_user->last_activity;	// When last active
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
		wps_update_user("users", "user_login", $value, "%s", $this->id);
    }
    function get_user_login() {
		return $this->user_login;
    }	

	// Member email address
	function set_user_email($value) {
    	$this->user_email = $value;
		wps_update_user("users", "user_email", $value, "%s", $this->id);
    }
    function get_user_email() {
		return $this->user_email;
    }	

	// Member city
	function set_city($value) {
    	$this->city = $value;
		wps_update_user("symposium_usermeta", "city", $value, "%s", $this->id);
    }
    function get_city() {
		return $this->city;
    }

	// Member country
	function set_country($value) {
    	$this->country = $value;
		wps_update_user("symposium_usermeta", "country", $value, "%s", $this->id);
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
    function get_latest_activity() {
        global $wpdb;
		return $wpdb->get_var( $wpdb->prepare("SELECT comment FROM ".$wpdb->base_prefix."symposium_comments WHERE subject_uid = ".$this->id." AND author_uid = ".$this->id." AND comment_parent = 0 ORDER BY cid DESC LIMiT 0,1"));
    }        
    function get_latest_activity_age() {
        global $wpdb;
		$dateof = $wpdb->get_var( $wpdb->prepare("SELECT comment_timestamp FROM ".$wpdb->base_prefix."symposium_comments WHERE subject_uid = ".$this->id." AND author_uid = ".$this->id." AND comment_parent = 0 ORDER BY cid DESC LIMiT 0,1"));
		return $dateof;
    }        
	function set_activity_privacy($value) {
    	$this->activity_privacy = $value;
		wps_update_user("symposium_usermeta", "wall_share", $value, "%s", $this->id);
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
		wps_update_user("symposium_usermeta", "dob_day", $value, "%d", $this->id);
    }
    function get_dob_day() {
		return $this->dob_day;
    }
	function set_dob_month($value) {
    	$this->dob_month = $value;
		wps_update_user("symposium_usermeta", "dob_month", $value, "%d", $this->id);
    }
    function get_dob_month() {
		return $this->dob_month;
    }
	function set_dob_year($value) {
    	$this->dob_year = $value;
		wps_update_user("symposium_usermeta", "dob_year", $value, "%d", $this->id);
    }
    function get_dob_year() {
		return $this->dob_year;
    }
    
    // Member Extended Profile information
    function get_extended() {
        global $wpdb;
		$meta = get_symposium_meta_row($this->id);					
		$extended = $meta->extended;
		$fields = explode('[|]', $extended);
		$has_extended_fields = false;
		if ($fields) {
			$ext_rows = array();
			foreach ($fields as $field) {
				$split = explode('[]', $field);
				if ( ($split[0] != '') && ($split[1] != '') ) {
				
					$extension = $wpdb->get_row($wpdb->prepare("SELECT extended_name,extended_order,extended_type FROM ".$wpdb->prefix."symposium_extended WHERE eid = ".$split[0]));
					
					$ext = array (	'name'=>$extension->extended_name,
									'value'=>symposium_make_url($split[1]),
									'type'=>$extension->extended_type,
									'order'=>$extension->extended_order );
					array_push($ext_rows, $ext);
				}
			}
			$ext_rows = sub_val_sort($ext_rows,'order');
			return $ext_rows;
		} else {
			return '';
		}
    }
    
    // Member friends
    // Returns list of friend IDs, ordered by last activity
    function get_friends() {
	    global $wpdb;

	    $sql = "SELECT f.friend_to AS id, m.last_activity FROM ".$wpdb->base_prefix."symposium_friends f
	    		INNER JOIN ".$wpdb->base_prefix."symposium_usermeta m ON f.friend_to = m.uid
	    		WHERE friend_from = %d AND friend_accepted = 'on'
	    		ORDER BY m.last_activity DESC";
	 	$friends = $wpdb->get_results($wpdb->prepare($sql, $this->id));
	 	return $friends;
    }
    
    // Profile page URL
	function get_profile_url() {
		return symposium_profile_link($this->id);
	}
   
	/* Following methods check for various conditions and return boolean value ______________________________________ */
	
    function is_permitted($type='activity') {
		return user_has_permission($this->id, $type);    
    }

	function is_friend() {
	   	global $wpdb,$current_user;
		if ( $wpdb->get_var($wpdb->prepare("SELECT * FROM ".$wpdb->base_prefix."symposium_friends WHERE (friend_accepted = 'on') AND (friend_from = ".$this->id." AND friend_to = ".$current_user->ID.")")) ) {
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

function wps_update_user($table, $field, $value, $format, $id) {
		global $wpdb;
		if ($table == "users") { $uid = "ID"; }
		if ($table == "symposium_usermeta") { $uid = "UID"; }
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->base_prefix.$table." SET ".$field." = ".$format." WHERE ".$uid." = ".$id, $value));
}

function user_has_permission($id, $type) {
	global $wpdb,$current_user;
	if ($type == 'activity') $type = 'wall_share';
	if ($type == 'personal') $type = 'share';
	$privacy = $wpdb->get_var($wpdb->prepare("SELECT ".$type." FROM ".$wpdb->base_prefix."symposium_usermeta WHERE uid = %d", $id));
	if (is_user_logged_in() || $privacy == 'public') {	
		$is_friend = symposium_friend_of($id, $current_user->ID);
		if ((WPS_CURRENT_USER_PAGE == $current_user->ID) || (is_user_logged_in() && strtolower($privacy) == 'everyone') || (strtolower($privacy) == 'public') || (strtolower($privacy) == 'friends only' && $is_friend) || symposium_get_current_userlevel() == 5) {
			return true;
		}
	}
	return false;
}

?>