<?php

include_once('../../../../wp-config.php');

// Add Event
if ($_POST['action'] == 'updateEvent') {

	global $wpdb;
	
	$eid = $_POST['eid'];
	$name = $_POST['name'];
	$location = $_POST['location'];
	$google_map = $_POST['google_map'];
	$desc = $_POST['desc'];
	$start_date = $_POST['start_date'];
	$start_hours = $_POST['start_hours'];
	$start_minutes = $_POST['start_minutes'];
	$end_date = $_POST['end_date'];
	$end_hours = $_POST['end_hours'];
	$end_minutes = $_POST['end_minutes'];
	$event_live	 = $_POST['event_live'];

	// Sort out dates to correct format
	if ($start_date != '') {
		$dt=explode('/',$start_date);
		$year1 = $dt[2];
		$month1 = $dt[0];
		$day1 = $dt[1];	
	} else {
		$year1 = '0000';
		$month1 = '00';
		$day1 = '00';
	}

	if ($end_date != '') {
		$dt=explode('/',$end_date);
		$year2 = $dt[2];
		$month2 = $dt[0];
		$day2 = $dt[1];	
	} else {
		$year2 = '0000';
		$month2 = '00';
		$day2 = '00';
	}

    $start = $year1."-".$month1."-".$day1." 00:00:00";
	$end = $year2."-".$month2."-".$day2." 00:00:00";
		
	if (is_user_logged_in()) {

		$wpdb->query( $wpdb->prepare( "UPDATE ".$wpdb->base_prefix."symposium_events SET 
			event_name = %s,
			event_description = %s, 
			event_location = %s, 
			event_google_map = %s,
			event_start = %s, 
			event_start_hours = %d, 
			event_start_minutes = %d, 
			event_end = %s, 
			event_end_hours = %d, 
			event_end_minutes = %d,
			event_live = %s 
			WHERE eid = %d", 
		array( 
			$name, 
			$desc,
			$location,
			$google_map,
			$start,
			$start_hours,
			$start_minutes,
			$end,
			$end_hours,
			$end_minutes,
			$event_live,
			$eid
		 ) ));
			
	}
	
	echo 'OK';
	exit;
	
}

// Edit Event (get details for dialog)
if ($_POST['action'] == 'editEvent') {

	global $current_user, $wpdb;
	
	$eid = $_POST['eid'];

	$sql = "SELECT * FROM ".$wpdb->base_prefix."symposium_events WHERE eid = %d AND event_owner = %d";
	$event = $wpdb->get_row($wpdb->prepare($sql, $eid, $current_user->ID));
	
	// Prepare to return comments in JSON format
	$return_arr = array();

	$row_array['event_name'] = stripslashes($event->event_name);
	$row_array['event_description'] = stripslashes($event->event_description);
	$row_array['event_location'] = stripslashes($event->event_location);
	$row_array['event_google_map'] = stripslashes($event->event_google_map);
	$row_array['start_date'] = date("m/d/Y", strtotime($event->event_start));	
	$row_array['start_hours'] = $event->event_start_hours;
	$row_array['start_minutes'] = $event->event_start_minutes;
	$row_array['end_date'] = date("m/d/Y", strtotime($event->event_end));
	$row_array['end_hours'] = $event->event_end_hours;
	$row_array['end_minutes'] = $event->event_end_minutes;
	$row_array['event_live'] = $event->event_live;
	array_push($return_arr, $row_array);
	
	echo json_encode($return_arr);
	exit;
	
}

// Delete Event
if ($_POST['action'] == 'deleteEvent') {

	global $current_user, $wpdb;
	
	$eid = $_POST['eid'];

	$sql = "DELETE FROM ".$wpdb->base_prefix."symposium_events WHERE eid = %d AND event_owner = %d";
	$rows_affected = $wpdb->query( $wpdb->prepare($sql, $eid, $current_user->ID) );
	
	echo 'OK';
	exit;
	
}

// Add Event
if ($_POST['action'] == 'addEvent') {

	global $current_user, $wpdb;
	
	$name = $_POST['name'];
	$desc = $_POST['desc'];
	$location = $_POST['location'];
	$start_date = $_POST['start_date'];
	$start_hours = $_POST['start_hours'];
	$start_minutes = $_POST['start_minutes'];
	$end_date = $_POST['end_date'];
	$end_hours = $_POST['end_hours'];
	$end_minutes = $_POST['end_minutes'];
	
	// Sort out dates to correct format
	if ($start_date != '') {
		$dt=explode('/',$start_date);
		$year1 = $dt[2];
		$month1 = $dt[0];
		$day1 = $dt[1];	
	} else {
		$year1 = '0000';
		$month1 = '00';
		$day1 = '00';
	}

	if ($end_date != '') {
		$dt=explode('/',$end_date);
		$year2 = $dt[2];
		$month2 = $dt[0];
		$day2 = $dt[1];	
	} else {
		$year2 = '0000';
		$month2 = '00';
		$day2 = '00';
	}

    $start_date = $year1."-".$month1."-".$day1." 00:00:00";
	$end_date = $year2."-".$month2."-".$day2." 00:00:00";
		
	if (is_user_logged_in()) {

		$wpdb->query( $wpdb->prepare( "
			INSERT INTO ".$wpdb->prefix."symposium_events 
			( 	event_name,
				event_description, 
				event_location, 
				event_google_map, 
				event_created, 
				event_start, 
				event_start_hours, 
				event_start_minutes, 
				event_end, 
				event_end_hours, 
				event_end_minutes,
				event_owner,
				event_group
			)
			VALUES ( %s, %s, %s, %s, %s, %s, %d, %d, %s, %d, %d, %d, %d )", 
	        array(
	        	$name, 
	        	$desc,
	        	$location, 
	        	'', 
	        	date("Y-m-d H:i:s"), 
				$start_date,
				$start_hours,
				$start_minutes,
				$end_date,
				$end_hours,
				$end_minutes, 
				$current_user->ID, 
				0
	        	) 
	        ) );
			
	}
	
	echo 'OK';
	exit;
	
}

// Start events content
if ($_POST['action'] == 'menu_events') {

	$html = "";

	global $current_user; 
	$uid1 = $current_user->ID; // Current user
	$uid2 = $_POST['uid1']; // Which member's page is this?
	
	$privacy = get_symposium_meta($uid2, 'wall_share');		
	
	$is_friend = symposium_friend_of($uid2, $current_user->ID);
	
	if ( ($uid1 == $uid2) || (is_user_logged_in() && strtolower($privacy) == 'everyone') || (strtolower($privacy) == 'public') || (strtolower($privacy) == 'friends only' && $is_friend) || symposium_get_current_userlevel() == 5) {

		// Create events form
		if ($uid1 == $uid2) {

			$html .= '<input type="submit" id="symposium_create_event_button" class="symposium-button" value="'.__('Create Event', 'wp-symposium').'">';
		
			$html .= '<div id="symposium_create_event_form" style="display:none">';

				$html .= '<div class="new-topic-subject label">'.__("Event Name", "wp-symposium").'</div>';
				$html .= '<input id="symposium_create_event_name" class="new-topic-subject-input" type="text" value="">';

				$html .= '<div class="new-topic-subject label">'.__("Location", "wp-symposium").'</div>';
				$html .= '<input id="symposium_create_event_location" class="new-topic-subject-input" type="text" value="">';

				$html .= '<div class="new-topic-subject label">'.__("Description", "wp-symposium").'</div>';
				$html .= '<textarea id="symposium_create_event_desc" class="new-topic-subject-text elastic"></textarea>';

				$html .= '<div>';
					$html .= '<div style="float:left; margin-right:15px;">';
						$html .= '<div class="new-topic-subject label">'.__("Start Date", "wp-symposium").'</div>';
						$html .= '<input type="text" id="event_start" class="datepicker" />';
						$html .= '<div class="new-topic-subject label">'.__("Start Time", "wp-symposium").'</div>';
						$html .= '<select id="event_start_time_hours">';
						$html .= '<option value=99>-</option>';
					 	for($i=0;$i<=23;$i++){
							$html .= '<option value='.$i.'>'.$i.'</option>';
						}
						$html .= '</select> : ';
						$html .= '<select id="event_start_time_minutes">';
						$html .= '<option value=99>-</option>';
					 	for($i=0;$i<=3;$i++){
							$html .= '<option value='.($i*15).'>'.($i*15).'</option>';
						}
						$html .= '</select>';
					$html .= '</div>';
					$html .= '<div style="float:left">';
						$html .= '<div class="new-topic-subject label">'.__("End Date", "wp-symposium").'</div>';
						$html .= '<input type="text" id="event_end" class="datepicker" />';
						$html .= '<div class="new-topic-subject label">'.__("End Time", "wp-symposium").'</div>';
						$html .= '<select id="event_end_time_hours">';
						$html .= '<option value=99>-</option>';
					 	for($i=0;$i<=23;$i++){
							$html .= '<option value='.$i.'>'.$i.'</option>';
						}
						$html .= '</select> : ';
						$html .= '<select id="event_end_time_minutes">';
						$html .= '<option value=99>-</option>';
					 	for($i=0;$i<=3;$i++){
							$html .= '<option value='.($i*15).'>'.($i*15).'</option>';
						}
						$html .= '</select>';
					$html .= '</div>';
				$html .= '</div>';

				$html .= '<div style="clear:both">';
					$html .= '<input type="submit" id="symposium_add_event_button" class="symposium-button" style="margin-top:15px" value="'.__('Create Event', 'wp-symposium').'">';
					$html .= '<input type="submit" id="symposium_cancel_event_button" class="symposium-button" style="margin-top:15px" value="'.__('Cancel', 'wp-symposium').'">';
				$html .= '</div>';
		
			$html .= '</div>';

		}
		
		$html .= '<div id="symposium_events_list" style="width:95%;">';
		
			if (symposium_get_current_userlevel() == 5) {
				$sql = "SELECT * FROM ".$wpdb->prefix."symposium_events WHERE event_owner = %d && (event_live = 'on' || event_owner = %d) ORDER BY event_start";
			} else {
				$sql = "SELECT * FROM ".$wpdb->prefix."symposium_events WHERE event_owner = %d ORDER BY event_start";
			}
			$events = $wpdb->get_results($wpdb->prepare($sql, $uid2, $uid1));
			if ($events) {
				foreach ($events as $event) {
					$html .= '<div class="symposium_event_list_item row">';
					
						if ( ($event->event_owner == $uid1) || (symposium_get_current_userlevel() == 5) ) {
							$html .= "<div class='symposium_event_list_item_icons'>";
							if ($event->event_live != 'on') {
								$html .= '<div style="font-style:italic;float:right;">'.__('Edit to publish', 'wp-symposium').'</div>';
							}
							$html .= "<a href='javascript:void(0)' class='symposium_delete_event floatright link_cursor' style='display:none;margin-right: 5px' id='".$event->eid."'><img src='".get_option('symposium_images')."/delete.png' /></a>";
							$html .= "<a href='javascript:void(0)' class='symposium_edit_event floatright link_cursor' style='display:none;margin-right: 5px' id='".$event->eid."'><img src='".get_option('symposium_images')."/edit.png' /></a>";
							$html .= "</div>";
						}
					
						$html .= '<div class="symposium_event_list_name">'.stripslashes($event->event_name).'</div>';
						$html .= '<div class="symposium_event_list_location">'.stripslashes($event->event_location).'</div>';
						$html .= '<div class="symposium_event_list_description">';
							if ($event->event_google_map == 'on') {
								$html .= "<div id='event_google_profile_map' style='float:right; margin-left:5px; width:128px; height:128px'>";
								$html .= '<a target="_blank" href="http://maps.google.co.uk/maps?f=q&amp;source=embed&amp;hl=en&amp;geocode=&amp;q='.$event->event_location.'&amp;ie=UTF8&amp;hq=&amp;hnear='.$event->event_location.'&amp;output=embed&amp;z=5" alt="Click on map to enlarge" title="Click on map to enlarge">';
								$html .= '<img src="http://maps.google.com/maps/api/staticmap?center='.$event->event_location.'&zoom=5&size=128x128&maptype=roadmap&markers=color:blue|label:&nbsp;|'.$event->event_location.'&sensor=false" />';
								$html .= "</a></div>";
							}
							$html .= stripslashes($event->event_description);
						$html .= '</div>';
						$html .= '<div class="symposium_event_list_dates">';
							if ($event->event_start != '0000-00-00 00:00:00') {
								$html .= date("D, d M Y", convert_datetime($event->event_start));
							}
							if ($event->event_start != $event->event_end) {
								if ($event->event_end != '0000-00-00 00:00:00') {
									$html .= ' &rarr; ';
									$html .= date("D, d M Y", convert_datetime($event->event_end));
								}
							}
						$html .= '</div>';
						$html .= '<div class="symposium_event_list_times">';
							if ($event->event_start_hours != 99) {
								$html .= __('Start: ', 'wp-symposium').$event->event_start_hours.":".sprintf('%1$02d', $event->event_start_minutes);
							}
							if ($event->event_end_hours != 99) {
								$html .= ' '.__('End: ', 'wp-symposium').$event->event_end_hours.":".sprintf('%1$02d', $event->event_end_minutes);
							}
						$html .= '</div>';
					$html .= '</div>';
				}
			}
		
		$html .= '</div>';


	}

	// This filter allows others to filter output
	$html = apply_filters ( 'symposium_my_events_page_filter', $html);
	
	echo $html;
	exit;	
}

// Get events calendar
if ($_POST['action'] == 'getEvents') {
	
	$html = "EVENTS";
	echo $html;
	exit;
	
}

		
?>

	
