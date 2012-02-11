<?php

// ******************************** WP SYMPOSIUM WPS MAIL CLASS ********************************

class wps_forum {

	public function __construct($top_level='') {
		$root = $top_level != '' ? $top_level : 0;
		$this->root = $root;	// Set the top level of the forum
	}

	/* Following methods provide get/set functionality ______________________________________ */
	
	function get_categories($top_level='') {
		
		$root = $top_level != '' ? $root = $top_level : $this->root;
		
		global $wpdb;
		$sql = "SELECT *
			FROM ".$wpdb->prefix."symposium_cats
			WHERE cat_parent = %d
			ORDER BY listorder";
		$cats = $wpdb->get_results($wpdb->prepare($sql, $top_level));
		
		return $cats;
		
	}

	function get_topics($cid='', $start=0, $limit=9999, $order='DESC', $gid=0) {
		
		if ($cid != '') {
			
			global $wpdb;
			$sql = "SELECT t.*, u.display_name
				FROM ".$wpdb->prefix."symposium_topics t
				LEFT JOIN ".$wpdb->base_prefix."users u ON t.topic_owner = u.ID
				WHERE topic_category = %d
				AND topic_parent = 0
				AND topic_group = %d
				ORDER BY tid ".$order."
				LIMIT %d, %d";
				
			if ($limit-$start != 1) {
				$topics = $wpdb->get_results($wpdb->prepare($sql, $cid, $gid, $start, $limit));
			} else {
				$topics = $wpdb->get_row($wpdb->prepare($sql, $cid, $gid, $start, $limit));
			}
			return $topics;
			
		} else {
			echo 'No category ID passed';
			return false;
		}
		
	}
			

	/* Following methods provide functionality _____________________________________________________________________ */
	
	
	/* Following methods check for various conditions and return boolean value ______________________________________ */
	
}



?>