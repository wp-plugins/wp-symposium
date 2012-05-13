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
<h2>Profile Plus</h2>


<?php

	global $wpdb;
	// See if the user has posted profile settings
	
	if( isset($_POST[ 'symposium_profile_plus_updated' ]) ) {

	 	// Update Version *******************************************************************************
	 	$lat_long = (isset($_POST[ 'lat_long' ])) ? $_POST[ 'lat_long' ] : '';
		update_option("symposium_plus_lat_long", $lat_long);

	 	$show_alt = (isset($_POST[ 'show_alt' ])) ? $_POST[ 'show_alt' ] : '';
		update_option("symposium_plus_show_alt", $show_alt);

		$wps_show_hoverbox = (isset($_POST['wps_show_hoverbox']) ? $_POST['wps_show_hoverbox'] : '');
		update_option('symposium_wps_show_hoverbox', $wps_show_hoverbox);
		
		// Put an settings updated message on the screen
		echo "<div class='updated slideaway'><p>".__('Saved', 'wp-symposium').".</p></div>";
		
	}

	// Get options
	$lat_long = ($value = get_option("symposium_plus_lat_long")) ? $value : '';
	$show_alt = ($value = get_option("symposium_plus_show_alt")) ? $value : '';
	
?>

	<p><?php _e("1. A hover box to all avatars shown on the site with 'quick links'", 'wp-symposium'); ?></p>
	<p><?php _e("2. A new menu item ('Following') to the member Profile page to view members who they are following.", 'wp-symposium'); ?></p>
	<p><?php _e("3. Adds geocoding to members location (city and country) to allow searching by distance on directory.", 'wp-symposium'); ?></p>
	<p><?php _e("4. Enables advanced search on member directory.", 'wp-symposium'); ?></p>

<div class="metabox-holder"><div id="toc" class="postbox"> 
	
	<form method="post" action=""> 
	<input type='hidden' name='symposium_profile_plus_updated' value='Y'>
	<table class="form-table"> 
	
	<tr valign="top"> 
	<td scope="row"><label for="lat_long"><?php _e('Use miles for geocoding distance', 'wp-symposium'); ?></label></td>
	<td>
	<input type="checkbox" name="lat_long" id="lat_long" <?php if ($lat_long == "on") { echo "CHECKED"; } ?>/>
	<span class="description"><?php echo __('Set distance to miles, otherwise kilometers', 'wp-symposium'); ?></span></td> 
	</tr> 
	
	<tr valign="top"> 
	<td scope="row"><label for="show_alt"><?php _e('Show alternative', 'wp-symposium'); ?></label></td>
	<td>
	<input type="checkbox" name="show_alt" id="show_alt" <?php if ($show_alt == "on") { echo "CHECKED"; } ?>/>
	<span class="description"><?php echo __('eg. If above set to miles, also show kilometers', 'wp-symposium'); ?></span></td> 
	</tr> 
	
	<tr valign="top"> 
	<td scope="row"><label for="wps_show_hoverbox"><?php echo __('Enable hover box', 'wp-symposium'); ?></label></td>
	<td>
	<input type="checkbox" name="wps_show_hoverbox" id="wps_show_hoverbox" <?php if (get_option('symposium_wps_show_hoverbox') == "on") { echo "CHECKED"; } ?>/>
	<span class="description"><?php echo __('Enables the hover box when cursor moved over profile avatar', 'wp-symposium'); ?></span></td> 
	</tr> 

	</table>
		
	<?php
				
	echo '<p class="submit" style="text-align:right; margin-right:12px">';
	echo '<input type="submit" name="Submit" class="button-primary" value="'.__('Save Changes', 'wp-symposium').'" />';
	echo '</p>';
	
	echo '</form>';
  
?>

</div></div>

</div>
