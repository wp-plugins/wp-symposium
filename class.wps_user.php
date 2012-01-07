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
		$this->user_login = $user_info->user_login;					// WordPress user login
		$this->user_email = $user_info->user_email;					// WordPress user email address
		$this->city = $wps_current_user->city;						// City
		$this->country = $wps_current_user->country;				// Country
		$this->avatar = '';											// Avatar (readonly)
		$this->latest_activity = '';								// Most recent activity post
	}
	
	// get/set functions ______________________________________________________________________________
	
	function set_display_name($value) {
    	$this->display_name = $value;
		wps_update_user("users", "display_name", $value, "%s", $this->id);
    }
    function get_display_name() {
		return $this->display_name;
    }	

	function set_user_login($value) {
    	$this->user_login = $value;
		wps_update_user("users", "user_login", $value, "%s", $this->id);
    }
    function get_user_login() {
		return $this->user_login;
    }	

	function set_user_email($value) {
    	$this->user_email = $value;
		wps_update_user("users", "user_email", $value, "%s", $this->id);
    }
    function get_user_email() {
		return $this->user_email;
    }	

	function set_city($value) {
    	$this->city = $value;
		wps_update_user("symposium_usermeta", "city", $value, "%s", $this->id);
    }
    function get_city() {
		return $this->city;
    }

	function set_country($value) {
    	$this->country = $value;
		wps_update_user("symposium_usermeta", "country", $value, "%s", $this->id);
    }
    function get_country() {
		return $this->country;
    }   
   
	function set_avatar($value) {
		// There is no set method
    }
    function get_avatar($size=64) {
		return get_avatar($this->id, $size);
    }        

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
		return $wpdb->get_var( $wpdb->prepare("SELECT comment FROM ".$wpdb->base_prefix."symposium_comments WHERE subject_uid = ".$this->id." AND author_uid = ".$this->id." ORDER BY cid DESC"));
    }        

}

// Single function to reduce duplication
function wps_update_user($table, $field, $value, $format, $id) {
		global $wpdb;
		if ($table == "users") { $uid = "ID"; }
		if ($table == "symposium_usermeta") { $uid = "UID"; }
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->base_prefix.$table." SET ".$field." = ".$format." WHERE ".$uid." = ".$id, $value));
}

?>