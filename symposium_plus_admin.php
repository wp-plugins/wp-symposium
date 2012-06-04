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
	 	$show_alt = (isset($_POST[ 'show_alt' ])) ? $_POST[ 'show_alt' ] : '';
		$wps_show_hoverbox = (isset($_POST['wps_show_hoverbox']) ? $_POST['wps_show_hoverbox'] : '');
		$use_distance = (isset($_POST['use_distance']) ? $_POST['use_distance'] : '');
		$unique_display_name = (isset($_POST['unique_display_name']) ? $_POST['unique_display_name'] : '');
	 	
		update_option("symposium_plus_lat_long", $lat_long);
		update_option("symposium_plus_show_alt", $show_alt);
		update_option('symposium_wps_show_hoverbox', $wps_show_hoverbox);
		update_option('symposium_use_distance', $use_distance);
		update_option('symposium_unique_display_name', $unique_display_name);
		
		update_option('wps_site_search_prompt', isset($_POST[ 'wps_site_search_prompt' ]) ? $_POST[ 'wps_site_search_prompt' ] : '');
		update_option('wps_site_search_groups', isset($_POST[ 'wps_site_search_groups' ]) ? $_POST[ 'wps_site_search_groups' ] : '');
		update_option('wps_site_search_gallery', isset($_POST[ 'wps_site_search_gallery' ]) ? $_POST[ 'wps_site_search_gallery' ] : '');
		update_option('wps_site_search_topics', isset($_POST[ 'wps_site_search_topics' ]) ? $_POST[ 'wps_site_search_topics' ] : '');
		update_option('wps_site_search_posts', isset($_POST[ 'wps_site_search_posts' ]) ? $_POST[ 'wps_site_search_posts' ] : '');
		update_option('wps_site_search_pages', isset($_POST[ 'wps_site_search_pages' ]) ? $_POST[ 'wps_site_search_pages' ] : '');
		update_option('symposium_tags', isset($_POST[ 'symposium_tags' ]) ? $_POST[ 'symposium_tags' ] : '');
		
		// Put an settings updated message on the screen
		echo "<div class='updated slideaway'><p>".__('Saved', 'wp-symposium').".</p></div>";
		
	}

	// Get options
	$lat_long = ($value = get_option("symposium_plus_lat_long")) ? $value : '';
	$show_alt = ($value = get_option("symposium_plus_show_alt")) ? $value : '';
	$symposium_tags = ($value = get_option("symposium_tags")) ? $value : '';
	$use_distance = ($value = get_option("symposium_use_distance")) ? $value : '';
	$unique_display_name = ($value = get_option("symposium_unique_display_name")) ? $value : '';

	
?>
	<p><?php _e("Profile Plus adds the following features to WP Symposium:", 'wp-symposium'); ?></p>
	<p>
	<?php _e("1. A hover box to all avatars shown on the site with 'quick links'", 'wp-symposium'); ?><br />
	<?php _e("2. A new menu item ('Following') to the member Profile page to view members who they are following.", 'wp-symposium'); ?><br />
	<?php _e("3. Adds geocoding to members location (city and country) to allow searching by distance on directory.", 'wp-symposium'); ?><br />
	<?php _e("4. Enables advanced search on member directory.", 'wp-symposium'); ?><br />
	<?php _e("4. A site-wide search including members, groups, forum topics and WordPress pages and posts.", 'wp-symposium'); ?><br />
	</p>

<div class="metabox-holder"><div id="toc" class="postbox"> 
	
	<form method="post" action=""> 
	<input type='hidden' name='symposium_profile_plus_updated' value='Y'>
	<table class="form-table"> 
	
	<tr valign="top"> 
	<td scope="row"><label for="symposium_tags"><?php _e('Enable @user tags', 'wp-symposium'); ?></label></td>
	<td>
	<input type="checkbox" name="symposium_tags" id="symposium_tags" <?php if ($symposium_tags == "on") { echo "CHECKED"; } ?>/>
	<span class="description"><?php echo __('Replace @user with a link to profile page. Understands usernames and display names (with spaces removed)', 'wp-symposium'); ?></span></td> 
	</tr> 
	
	<tr valign="top"> 
	<td scope="row"><label for="unique_display_name"><?php _e('Unique display names', 'wp-symposium'); ?></label></td>
	<td>
	<input type="checkbox" name="unique_display_name" id="syunique_display_namemposium_tags" <?php if ($unique_display_name == "on") { echo "CHECKED"; } ?>/>
	<span class="description"><?php echo __('Include check for unique display names on WPS profile community settings', 'wp-symposium'); ?></span></td> 
	</tr> 
	
	<tr valign="top"> 
	<td scope="row"><label for="use_distance"><?php _e('Enable distance', 'wp-symposium'); ?></label></td>
	<td>
	<input type="checkbox" name="use_distance" id="use_distance" <?php if ($use_distance == "on") { echo "CHECKED"; } ?>/>
	<span class="description"><?php echo __('Enable distance in the member directory', 'wp-symposium'); ?></span></td> 
	</tr> 
	
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

	<tr valign="top"> 
	<td scope="row"><label for="wps_site_search"><?php echo __('Autocomplete search box', 'wp-symposium'); ?></label></td>
	<td>
	<span class="description">
		<?php echo __('Use [symposium-search] shortcode, or put &quot;echo symposium_search()&quot; in PHP.', 'wp-symposium'); ?><br />
		<?php echo __('The more results that are included, the slower the search may be and greater the impact on your server/database.', 'wp-symposium'); ?>
	</span></td> 
	</tr> 
	
	<tr valign="top"> 
	<td scope="row" style="text-align:right"><label for="wps_site_search_prompt"><?php echo __('Text prompt', 'wp-symposium'); ?></label></td> 
	<td><input name="wps_site_search_prompt" type="text" id="wps_site_search_prompt"  value="<?php echo get_option('wps_site_search_prompt'); ?>" /> 
	<span class="description"><?php echo __('Search box text prompt', 'wp-symposium'); ?></td> 
	</tr> 

	<tr valign="top"> 
	<td scope="row" style="text-align:right"><label for="wps_site_search_gallery"><?php echo __('Gallery', 'wp-symposium'); ?></label></td>
	<td>
	<input type="checkbox" name="wps_site_search_gallery" id="wps_site_search_gallery" <?php if (get_option('wps_site_search_gallery') == "on") { echo "CHECKED"; } ?>/>
	<span class="description"><?php echo __('Include WP Symposium Photo albums in search results', 'wp-symposium'); ?></span></td> 
	</tr> 

	<tr valign="top"> 
	<td scope="row" style="text-align:right"><label for="wps_site_search_groups"><?php echo __('Groups', 'wp-symposium'); ?></label></td>
	<td>
	<input type="checkbox" name="wps_site_search_groups" id="wps_site_search_groups" <?php if (get_option('wps_site_search_groups') == "on") { echo "CHECKED"; } ?>/>
	<span class="description"><?php echo __('Include WP Symposium Groups in search results', 'wp-symposium'); ?></span></td> 
	</tr> 

	<tr valign="top"> 
	<td scope="row" style="text-align:right"><label for="wps_site_search_pages"><?php echo __('Pages', 'wp-symposium'); ?></label></td>
	<td>
	<input type="checkbox" name="wps_site_search_pages" id="wps_site_search_pages" <?php if (get_option('wps_site_search_pages') == "on") { echo "CHECKED"; } ?>/>
	<span class="description"><?php echo __('Include WordPress blog posts in search results', 'wp-symposium'); ?></span></td> 
	</tr> 

	<tr valign="top"> 
	<td scope="row" style="text-align:right"><label for="wps_site_search_posts"><?php echo __('Blog Posts', 'wp-symposium'); ?></label></td>
	<td>
	<input type="checkbox" name="wps_site_search_posts" id="wps_site_search_posts" <?php if (get_option('wps_site_search_posts') == "on") { echo "CHECKED"; } ?>/>
	<span class="description"><?php echo __('Include WordPress blog posts in search results', 'wp-symposium'); ?></span></td> 
	</tr> 

	<tr valign="top"> 
	<td scope="row" style="text-align:right"><label for="wps_site_search_topics"><?php echo __('Forum Topics', 'wp-symposium'); ?></label></td>
	<td>
	<input type="checkbox" name="wps_site_search_topics" id="wps_site_search_topics" <?php if (get_option('wps_site_search_topics') == "on") { echo "CHECKED"; } ?>/>
	<span class="description"><?php echo __('Include WP Symposium Forum topics in search results', 'wp-symposium'); ?></span></td> 
	</tr> 

	</table>
		
	<?php
				
	echo '<p class="submit" style="margin-left:12px">';
	echo '<input type="submit" name="Submit" class="button-primary" value="'.__('Save Changes', 'wp-symposium').'" />';
	echo '</p>';
	
	echo '</form>';
  
?>

</div></div>

</div>
