<?php

include_once('../../../../wp-config.php');

global $wpdb, $current_user;

// Group list search (autocomplete)
if (isset($_GET['term'])) {
		
	global $wpdb;	
	$return_arr = array();
	$term = $_GET['term'];

	$sql = "SELECT * FROM ".$wpdb->prefix."symposium_groups WHERE  
	( name LIKE '%".$term."%') OR 
	(description LIKE '%".$term."%')
	ORDER BY last_activity DESC LIMIT 0,25";
	
	$list = $wpdb->get_results($sql);
	
	if ($list) {
		foreach ($list as $item) {
			$row_array['id'] = $item->gid;
			$row_array['value'] = $item->gid;
			$row_array['name'] = $item->name;
			$row_array['avatar'] = get_group_avatar($item->gid, 40);
			
	        array_push($return_arr,$row_array);
		}
	}

	echo json_encode($return_arr);
	exit;

}


// AJAX function to create group
if (isset($_POST['action']) && $_POST['action'] == 'createGroup') {
	
	if (is_user_logged_in()) {

		$html = '';
		$name_of_group = $_POST['name_of_group'];
		$description_of_group = $_POST['description_of_group'];
		$me = $_POST['me'];

		// Add Group
		$wpdb->query( $wpdb->prepare( "
			INSERT INTO ".$wpdb->prefix."symposium_groups
			( 	name, 
				description,
				last_activity,
				private,
				created,
				forum,
				photos,
				wall,
				content_private,
				group_avatar,
				profile_photo,
				group_forum
			)
			VALUES ( %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s )", 
	        array(
	        	$name_of_group, 
	        	$description_of_group, 
	        	date("Y-m-d H:i:s"),
	        	'',
	        	date("Y-m-d H:i:s"),
				'',
				'',
				'on',
				'',
				'',
				'',
				'on'
				) 
	        ) );
	        
		$insert_query = $wpdb->last_query;
	    $new_group_id = $wpdb->insert_id;
	        	
	    // Add Group Member Admin
		$wpdb->query( $wpdb->prepare( "
			INSERT INTO ".$wpdb->prefix."symposium_group_members
			( 	group_id, 
				member_id,
				admin,
				valid,
				joined
			)
			VALUES ( %d, %d, %s, %s, %s )", 
	        array(
	        	$new_group_id, 
	        	$me, 
	        	'on',
	        	'on',
	        	date("Y-m-d H:i:s")
	        	) 
	        ) );
	        
	    echo $new_group_id;
				
	}
	
	exit;
}

					
// Check that form hasn't been submited
if (isset($_POST['group_id']) && $_POST['group_id'] != '') {
	header("Location: ".symposium_get_url('group')."?gid=".$_POST['group_id']);
	exit;
}
if (isset($_POST['group']) && $_POST['group'] != '') {
	header("Location: ".symposium_get_url('groups')."?term=".$_POST['group']);
	exit;
}

// AJAX function to get groups
if ($_POST['action'] == 'getGroups') {

	$html = '';
	$page = $_POST['page'];
	$page_length = 25;
	$me = $_POST['me'];

	$plugin_dir = WP_PLUGIN_URL.'/wp-symposium-pro/';
	
	$term = '';
	
	$sql = "SELECT g.*, (SELECT COUNT(*) FROM ".$wpdb->prefix."symposium_group_members WHERE group_id = g.gid) AS member_count
	FROM ".$wpdb->prefix."symposium_groups g WHERE  
	( g.name LIKE '%".$term."%') OR 
	( g.description LIKE '%".$term."%' )
	ORDER BY last_activity DESC LIMIT 0,25";
	
	$groups = $wpdb->get_results($sql);
	$url = get_option('symposium_group_url');
		
	if ($groups) {
		
		foreach ($groups as $group) {

			if (symposium_member_of($group->gid) == 'yes') { 
				$html .= "<div class='groups_row row_odd corners'>";
			} else {
				$html .= "<div class='groups_row row corners'>";
			}
				
				$html .= "<div class='groups_info'>";

					$html .= "<div class='groups_avatar'>";
						$html .= get_group_avatar($group->gid, 64);
					$html .= "</div>";

					$html .= "<div class='group_name'>";
					$html .= "<a class='row_link' href='".symposium_get_url('group').symposium_string_query($url)."gid=".$group->gid."'>".stripslashes($group->name)."</a>";
					$html .= "</div>";
					
					$html .= "<div class='group_member_count'>";
					$html .= __("Member Count:", "wp-symposium")." ".$group->member_count;
					if ($group->last_activity) {
						$html .= '<br /><em>'.__('last active', 'wp-symposium').' '.symposium_time_ago($group->last_activity)."</em>";
					}
					$html .= "</div>";
				
					$html .= "<div class='group_description'>";
					$html .= stripslashes($group->description);
					$html .= "</div>";
					
				$html .= "</div>";
				
			$html .= "</div>";
			
		}
	} else {
		$html = __("No groups created yet....", "wp-symposium");
	}
	
	echo $html;
	exit;
}


// ADMIN PAGE FUNCTIONS /////////////////////////////////////////////////////////////////////////////////////

// AJAX function to get groups
if ($_POST['action'] == 'get_user_list') {

	$term = $_POST['term'];
	$gid = $_POST['gid'];
	
	$sql = "SELECT DISTINCT u.*, m.admin FROM ".$wpdb->base_prefix."users u
			LEFT JOIN ".$wpdb->prefix."symposium_group_members m ON u.ID = m.member_id
			WHERE (display_name LIKE '".$term."%' OR user_login LIKE '".$term."%')
			AND ID NOT IN (SELECT member_id FROM ".$wpdb->prefix."symposium_group_members WHERE group_id = ".$gid.")
			ORDER BY display_name";
	$users = $wpdb->get_results($sql);
	
	$html = '';
	if ($users) {
		foreach ($users as $user) {
			if ($user->admin != 'on') {
				$html .= '<div class="user_list_item" id="'.$user->ID.'" style="clear:both; cursor:pointer; float:left; border:1px solid #aaa; background-color:#ccc;margin-bottom:2px;padding:2px;margin-right:2px;">';
				$html .= "<img src='".get_option('symposium_images')."/add.png' style='width:12px; height:12px; float:right; margin-left:3px;' alt='add' /> ";
				$html .= $user->display_name.' ('.$user->ID.')';
				$html .= '</div>';
			}
		}	
	}

	echo $html;
	exit;
}

// Get group members
if ($_POST['action'] == 'get_group_members') {

	$gid = $_POST['gid'];
	
	// First clean up the table, making sure there are no user ID = 0 (do across all groups)
	$sql = "DELETE FROM ".$wpdb->prefix."symposium_group_members WHERE member_id = 0";
	$wpdb->query($sql);

	// Now get the members
	$sql = "SELECT u.ID, u.display_name, u.user_login FROM ".$wpdb->prefix."symposium_group_members m
			LEFT JOIN ".$wpdb->base_prefix."users u ON m.member_id = u.ID
			WHERE group_id = ".$gid." AND m.admin != 'on'
			ORDER BY u.display_name";
	$users = $wpdb->get_results($sql);
	
	$html = '';
	if ($users) {
		foreach ($users as $user) {
			$html .= '<div class="user_list_item" id="'.$user->ID.'" style="clear:both; cursor:pointer; float:left; border:1px solid #aaa; background-color:#ccc;margin-bottom:2px;padding:2px;margin-right:2px;">';
			$html .= "<img src='".get_option('symposium_images')."/cross.png' style='width:12px; height:12px; float:right; margin-left:3px;' alt='add' /> ";
			$html .= $user->display_name.' ('.$user->ID.')';
			$html .= '</div>';
		}	
	}
	
	echo $html;
	exit;
}

// Update group membership
if ($_POST['action'] == 'add_group_members') {
	
	$gid = $_POST['gid'];
	$ids = split(',', $_POST['ids']);

	// Delete all existing members
	$sql = "DELETE FROM ".$wpdb->prefix."symposium_group_members WHERE group_id = %d AND admin != 'on'";
	$wpdb->query($wpdb->prepare($sql, $gid));
	
	// Now re-add all those in the list
	$html = '';
	foreach ($ids as $id) {
		
		if ($id > 0) {
		
			$insert = $wpdb->insert( 
				$wpdb->prefix."symposium_group_members", 
				array( 
					'group_id' => $gid, 
					'member_id' => $id,
					'admin' => '',
					'valid' => 'on',
					'joined' => date("Y-m-d H:i:s"),
					'notify' => ''
				), 
				array( 
					'%d', 
					'%d', 
					'%s', 
					'%s', 
					'%s', 
					'%s' 
				) 
			);	
				
			if ( $insert == false) {
				$html .= 'Failed to add user '.$id;
			}
			
		}
	}
	
	echo $html;
	exit;

}

?>

	
