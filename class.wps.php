<?php

// *************************************** WP SYMPOSIUM WPS CLASS **************************************

class wps {

	public function __construct() {
		$this->poke_label = WPS_POKE_LABEL;			// appears on "Poke" (tm) button
		$this->mail_url = WPS_MAIL_URL;				// URL of mail page, ending with a slash
		$this->profile_url = WPS_PROFILE_URL;		// URL of profile page, ending with a slash
		$this->members_url = WPS_MEMBERS_URL;		// URL of members page, ending with a slash
		$this->groups_url = WPS_GROUPS_URL;			// URL of groups page, ending with a slash
		$this->group_url = WPS_GROUP_URL;			// URL of group page, ending with a slash
		$this->gallery_url = WPS_GALLERY_URL;		// URL of gallery page, ending with a slash
		$this->forum_url = WPS_FORUM_URL;			// URL of forum page, ending with a slash
		$this->img_url = WPS_IMG_URL;				// URL of images stored on filesystem, e.g. /wp-content/wps-content
	}
	

	// get/set functions ______________________________________________________________________________
	
	function set_poke_label($value) {
		wps_update_table("symposium_config", "poke_label", $value, "%s");
    	$this->poke_label = $value;
    }
    function get_poke_label() {
		return $this->poke_label;
    }	

	function set_mail_url($value) {
		wps_update_table("symposium_config", "mail_url", $value, "%s");
    	$this->mail_url = $value;
    }
    function get_mail_url() {
		return $this->mail_url;
    }	
    
	function set_profile_url($value) {
		wps_update_table("symposium_config", "profile_url", $value, "%s");
    	$this->profile_url = $value;
    }
    function get_profile_url() {
		return $this->profile_url;
    }	
	
	function set_members_url($value) {
		wps_update_table("symposium_config", "members_url", $value, "%s");
    	$this->members_url = $value;
    }
    function get_members_url() {
		return $this->members_url;
    }	
      
	function set_groups_url($value) {
		wps_update_table("symposium_config", "groups_url", $value, "%s");
    	$this->groups_url = $value;
    }
    function get_groups_url() {
		return $this->groups_url;
    }	
      
	function set_group_url($value) {
		wps_update_table("symposium_config", "group_url", $value, "%s");
    	$this->group_url = $value;
    }
    function get_group_url() {
		return $this->group_url;
    }	
      
	function set_gallery_url($value) {
		wps_update_table("symposium_config", "gallery_url", $value, "%s");
    	$this->gallery_url = $value;
    }
    function get_gallery_url() {
		return $this->gallery_url;
    }	
      
	function set_forum_url($value) {
		wps_update_table("symposium_config", "forum_url", $value, "%s");
    	$this->forum_url = $value;
    }
    function get_forum_url() {
		return $this->forum_url;
    }	
      
	function set_img_url($value) {
		wps_update_table("symposium_config", "img_url", $value, "%s");
    	$this->img_url = $value;
    }
    function get_img_url() {
		return $this->img_url;
    }	
    
    function get_current_user_page() {
        return WPS_CURRENT_USER_PAGE;
    }
         
}

// Single function to reduce duplication
function wps_update_table($table, $field, $value, $format) {
		global $wpdb;
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->base_prefix.$table." SET ".$field." = ".$format, $value));
}


?>