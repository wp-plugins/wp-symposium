<!--
    Copyright 2010,2011,2012  Simon Goodchild  (info@wpsymposium.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
-->

<div class="wrap">
<div id="icon-themes" class="icon32"><br /></div>
<?php
echo '<h2>'.sprintf(__('%s Options', 'wp-symposium'), WPS_WL).'</h2><br />';

symposium_show_tabs_header('events');

	global $wpdb;
    // See if the user has posted profile settings
    if( isset($_POST[ 'symposium_events_updated' ]) ) {

	 	// Update *******************************************************************************
		update_option("symposium_events_global_list", $_POST[ 'symposium_events_global_list' ]);
		update_option('symposium_events_user_places', isset($_POST[ 'symposium_events_user_places' ]) ? $_POST[ 'symposium_events_user_places' ] : '');
		update_option('symposium_events_use_wysiwyg', isset($_POST[ 'symposium_events_use_wysiwyg' ]) ? $_POST[ 'symposium_events_use_wysiwyg' ] : '');
		update_option('symposium_events_hide_expired', isset($_POST[ 'symposium_events_hide_expired' ]) ? $_POST[ 'symposium_events_hide_expired' ] : '');
		update_option('symposium_events_sort_order', isset($_POST[ 'symposium_events_sort_order' ]) ? $_POST[ 'symposium_events_sort_order' ] : '');

		// Included roles
		if (isset($_POST['dir_level'])) {
	   		$range = array_keys($_POST['dir_level']);
	   		$level = '';
   			foreach ($range as $key) {
				$level .= $_POST['dir_level'][$key].',';
	   		}
		} else {
			$level = '';
		}

		update_option('symposium_events_profile_include', serialize($level));

        // Put an settings updated message on the screen
		echo "<div class='updated slideaway'><p>".__('Saved', 'wp-symposium').".</p></div>";
		
    }

	// Get option value
	$symposium_events_global_list = get_option("symposium_events_global_list") ? get_option("symposium_events_global_list") : '';

	?>

	<form method="post" action=""> 
	<input type='hidden' name='symposium_events_updated' value='Y'>
	<table class="form-table"> 

	<tr valign="top"> 
	<td scope="row"><label for="symposium_events_global_list"><?php _e('Global events list', 'wp-symposium'); ?></label></td>
	<td><input name="symposium_events_global_list" type="text" style="width:150px" id="symposium_events_global_list" value="<?php echo $symposium_events_global_list; ?>" class="regular-text" /> 
	<span class="description"><?php echo __('Limits the members included when using [symposium-events]. Enter User IDs (comma separated) or leave blank for all.', 'wp-symposium'); ?></span></td> 
	</tr> 

	<tr valign="top"> 
	<td scope="row"><label for="symposium_events_sort_order"><?php echo __('Reverse list order', 'wp-symposium'); ?></label></td> 
	<td><input type="checkbox" name="symposium_events_sort_order" id="symposium_events_sort_order" <?php if (get_option('symposium_events_sort_order') == "on") { echo "CHECKED"; } ?>/>
	<span class="description"><?php echo __('Select to reverse list order (by start date)', 'wp-symposium'); ?></td> 
	</tr> 
			
	<tr valign="top"> 
	<td scope="row"><label for="symposium_events_hide_expired"><?php echo __('Hide expired events', 'wp-symposium'); ?></label></td> 
	<td><input type="checkbox" name="symposium_events_hide_expired" id="symposium_events_hide_expired" <?php if (get_option('symposium_events_hide_expired') == "on") { echo "CHECKED"; } ?>/>
	<span class="description"><?php echo __('Do not display events that have finished (by end date)', 'wp-symposium'); ?></td> 
	</tr> 
			
	<tr valign="top"> 
	<td scope="row"><label for="symposium_events_user_places"><?php echo __('Non-admin event manager', 'wp-symposium'); ?></label></td> 
	<td><input type="checkbox" name="symposium_events_user_places" id="symposium_events_user_places" <?php if (get_option('symposium_events_user_places') == "on") { echo "CHECKED"; } ?>/>
	<span class="description"><?php echo __('Can non-administrators set up event bookings (or just list basic information)?', 'wp-symposium'); ?></td> 
	</tr> 
			
	<tr valign="top"> 
	<td scope="row"><label for="symposium_events_use_wysiwyg"><?php echo __('Use WYSIWYG editor', 'wp-symposium'); ?></label></td> 
	<td><input type="checkbox" name="symposium_events_use_wysiwyg" id="symposium_events_use_wysiwyg" <?php if (get_option('symposium_events_use_wysiwyg') == "on") { echo "CHECKED"; } ?>/>
	<span class="description"><?php echo __('Use WYSIWYG editor for more information and confirmation email (not summary)?', 'wp-symposium'); ?></td> 
	</tr> 
			
	<tr valign="top"> 
	<td scope="row"><label for="dir_level"><?php echo __('Roles who get "My Events" on profile page', 'wp-symposium') ?></label></td> 
	<td>
	<?php

		// Get list of roles
		global $wp_roles;
		$all_roles = $wp_roles->roles;

		$dir_roles = get_option('symposium_events_profile_include');

		foreach ($all_roles as $role) {
			echo '<input type="checkbox" name="dir_level[]" value="'.$role['name'].'"';
			if (strpos(strtolower($dir_roles), strtolower($role['name']).',') !== FALSE) {
				echo ' CHECKED';
			}
			echo '> '.$role['name'].'<br />';
		}	

	?>
	</td></tr>
	
	<?php
	echo '</table>';
	 					
	echo '<p class="submit" style="margin-left:12px">';
	echo '<input type="submit" name="Submit" class="button-primary" value="'.__('Save Changes', 'wp-symposium').'" />';
	echo '</p>';
	echo '</form>';
					  
?>

<?php symposium_show_tabs_header_end(); ?>
</div>
