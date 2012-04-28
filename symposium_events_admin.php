<!--
    Copyright 2010,2011  Simon Goodchild  (info@wpsymposium.com)

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
<h2>Events</h2>

<div class="metabox-holder"><div id="toc" class="postbox"> 

<h3>Events Settings</h3>

<?php

	global $wpdb;
    // See if the user has posted profile settings
    if( isset($_POST[ 'symposium_events_updated' ]) ) {

	 	// Update Version *******************************************************************************
		update_option("symposium_events_global_list", $_POST[ 'symposium_events_global_list' ]);

        // Put an settings updated message on the screen
		echo "<div class='updated slideaway'><p>".__('Saved', 'wp-symposium').".</p></div>";
		
    }

	// Get option value
	if ( get_option("symposium_events_global_list") ) {
		$symposium_events_global_list = get_option("symposium_events_global_list");
	} else {
		$symposium_events_global_list = '';
	}

	?>

	<form method="post" action=""> 
	<input type='hidden' name='symposium_events_updated' value='Y'>
	<table class="form-table"> 

	<tr valign="top"> 
	<td scope="row"><label for="symposium_events_global_list"><?php _e('Global events list', 'wp-symposium'); ?></label></td>
	<td><input name="symposium_events_global_list" type="text" style="width:50px" id="symposium_events_global_list" value="<?php echo $symposium_events_global_list; ?>" class="regular-text" /> 
	<span class="description"><?php echo __('Limits the members included when using [symposium-events]. Enter User IDs (comma separated) or leave blank for all.', 'wp-symposium'); ?></span></td> 
	</tr> 

	<?php
	echo '</table>';
	 					
	echo '<p class="submit" style="text-align:right; margin-right:12px">';
	echo '<input type="submit" name="Submit" class="button-primary" value="'.__('Save Changes', 'wp-symposium').'" />';
	echo '</p>';
	echo '</form>';
					  
?>

</div></div>

</div>
