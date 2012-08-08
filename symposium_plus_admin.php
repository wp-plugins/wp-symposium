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
		$all_friends = (isset($_POST['all_friends']) ? $_POST['all_friends'] : '');
		$activity_images = (isset($_POST['activity_images']) ? $_POST['activity_images'] : '');
		$activity_images_old = (isset($_POST['activity_images_old']) ? $_POST['activity_images_old'] : '');
		$activity_likes = (isset($_POST['activity_likes']) ? $_POST['activity_likes'] : '');

		$profile_menu_scrolls = (isset($_POST['profile_menu_scrolls']) ? $_POST['profile_menu_scrolls'] : '');
	 	$profile_menu_delta = ($_POST[ 'profile_menu_delta' ] != '') ? $_POST[ 'profile_menu_delta' ] : '40';
	 	$profile_menu_adjust = ($_POST[ 'profile_menu_adjust' ] != '') ? $_POST[ 'profile_menu_adjust' ] : '0';

		update_option("symposium_plus_lat_long", $lat_long);
		update_option("symposium_plus_show_alt", $show_alt);
		update_option('symposium_wps_show_hoverbox', $wps_show_hoverbox);
		update_option('symposium_use_distance', $use_distance);
		update_option('symposium_unique_display_name', $unique_display_name);
		update_option('symposium_all_friends', $all_friends);
		update_option('symposium_activity_images', $activity_images);
		update_option('symposium_activity_images_old', $activity_images_old);
		update_option('symposium_activity_likes', $activity_likes);
		update_option("symposium_profile_menu_delta", $profile_menu_delta);
		update_option("symposium_profile_menu_scrolls", $profile_menu_scrolls);
		update_option("symposium_profile_menu_adjust", $profile_menu_adjust);
		
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
	$all_friends = ($value = get_option("symposium_all_friends")) ? $value : '';
	$activity_images = ($value = get_option("symposium_activity_images")) ? $value : '';
	$activity_likes = ($value = get_option("symposium_activity_likes")) ? $value : '';

	// Set defaults
	if (get_option("symposium_profile_menu_delta") == '') update_option("symposium_profile_menu_delta", '40');
	if (get_option("symposium_profile_menu_adjust") == '') update_option("symposium_profile_menu_adjust", '0');
	
	
	// Force friends retrospectively?
	if ($_POST['force_all_friends']) {
		echo "<div class='updated' style='padding-bottom:10px'><p style='font-weight:bold'>".__('Force friends to all', 'wp-symposium')."</p>";
		echo "<p>".__("Are you sure you want to make ALL users friends with each other? <strong>This cannot be reversed!</strong> Please take a backup of your database first!","wp-symposium")."</p>";
		echo "<p>".__("Depending on how many users you have, this may take a few minutes.","wp-symposium")."</p>";
		echo "<table border=0><tr><td>";
		echo "<form method='post' action=''><input type='hidden' name='force_all_friends_confirm' value='Y' /><input type='submit' class='button-primary' value='".__("Yes", "wp-symposium")."' /></form>";
		echo "</td><td>";
		echo "<form method='post' action=''><input type='hidden' name='force_all_friends_confirm' value='N' /><input type='submit' class='button-primary' value='".__("No", "wp-symposium")."' /></form>";
		echo "</td><tr></table>";
		echo "</div>";
	}
	if ($_POST['force_all_friends_confirm'] == 'Y') {
		echo "<div class='updated slideaway'><p style='font-weight:bold'>".__('Force friends to all', 'wp-symposium')."</p>";
		// Delete existing friendships
		$sql = "DELETE FROM ".$wpdb->base_prefix."symposium_friends";
		$wpdb->query($wpdb->prepare($sql));
		// Loop through each user, adding them as a friend to all other users
		$sql = "SELECT ID FROM ".$wpdb->base_prefix."users";
		$users = $wpdb->get_results($wpdb->prepare($sql));
		$users2 = $wpdb->get_results($wpdb->prepare($sql));
		foreach ($users as $user) {
			foreach ($users2 as $user2) {
				if ($user->ID != $user2->ID) {
					$wpdb->query( $wpdb->prepare( "
						INSERT INTO ".$wpdb->prefix."symposium_friends
						( 	friend_from, 
							friend_to,
							friend_accepted,
							friend_message,
							friend_timestamp
						)
						VALUES ( %d, %d, %s, %s, %s )", 
					    array(
					    	$user->ID,
					    	$user2->ID,
					    	'on', 
					    	'',
					    	date("Y-m-d H:i:s")
					    	) 
					    ) );
				}
			}
			
		}
		echo "<p>".__("All users are now friends with each other.","wp-symposium")."</p>";
		echo "</div>";
	}
	
?>

<div class="metabox-holder"><div id="toc" class="postbox"> 
	
	<form method="post" action=""> 
	<input type='hidden' name='symposium_profile_plus_updated' value='Y'>
	<table class="form-table"> 

	<tr valign="top"> 
	<td scope="row"><label for="profile_menu_scrolls"><?php echo __('Scrolling profile menu', 'wp-symposium'); ?></label></td>
	<td>
	<input type="checkbox" name="profile_menu_scrolls" id="profile_menu_scrolls" <?php if (get_option('symposium_profile_menu_scrolls') == "on") { echo "CHECKED"; } ?>/>
	<span class="description"><?php echo __('Profile menu scrolls down with page, remaining visible', 'wp-symposium'); ?></span></td> 
	</tr> 

	<tr valign="top"> 
	<td scope="row" style="text-align:right"><label for="profile_menu_delta"><?php echo __('Fixed position', 'wp-symposium'); ?></label></td> 
	<td><input name="profile_menu_delta" type="text" id="profile_menu_delta" style="width:50px" value="<?php echo get_option('symposium_profile_menu_delta'); ?>" /> 
	<span class="description"><?php echo __('The space above the fixed menu when the page is scrolling down (pixels)', 'wp-symposium'); ?></td> 
	</tr> 

	<tr valign="top"> 
	<td scope="row" style="text-align:right"><label for="profile_menu_adjust"><?php echo __('Adjustment', 'wp-symposium'); ?></label></td> 
	<td><input name="profile_menu_adjust" type="text" id="profile_menu_adjust" style="width:50px" value="<?php echo get_option('symposium_profile_menu_adjust'); ?>" /> 
	<span class="description"><?php echo __('Additional space at top of menu before and when moving (pixels)', 'wp-symposium'); ?></td> 
	</tr> 

	<tr valign="top"> 
	<td scope="row"><label for="activity_likes"><?php _e('Activity Like/Dislike', 'wp-symposium'); ?></label></td>
	<td>
	<input type="checkbox" name="activity_likes" id="activity_likes" <?php if ($activity_likes == "on") { echo "CHECKED"; } ?>/>
	<span class="description"><?php echo __('Adds a like and dislike icon to all activity posts', 'wp-symposium'); ?></span>
	</td> 
	</tr> 
		
	<tr valign="top"> 
	<td scope="row"><label for="activity_images"><?php _e('Allow activity/status images', 'wp-symposium'); ?></label></td>
	<td>
	<input type="checkbox" name="activity_images" id="activity_images" <?php if ($activity_images == "on") { echo "CHECKED"; } ?>/>
	<span class="description"><?php echo __('Allow users with modern browsers that support HTML5 (not IE, humph) to attach images to profile posts (<strong></strong>BETA</strong>)', 'wp-symposium'); ?></span><br />
	<input type="checkbox" name="activity_images_old" id="activity_images_old" <?php if ($activity_images_old == "on") { echo "CHECKED"; } ?>/>
	<span class="description"><?php echo __('Show prompt to upgrade browsers, for those who are not using modern browsers (and Microsoft IE)', 'wp-symposium'); ?></span>
	</td> 
	</tr> 
		
	<tr valign="top"> 
	<td scope="row"><label for="symposium_tags"><?php _e('Enable @user tags', 'wp-symposium'); ?></label></td>
	<td>
	<input type="checkbox" name="symposium_tags" id="symposium_tags" <?php if ($symposium_tags == "on") { echo "CHECKED"; } ?>/>
	<span class="description"><?php echo __('Replace @user with a link to profile page. Understands usernames and display names (with spaces removed)', 'wp-symposium'); ?></span></td> 
	</tr> 
	
	<tr valign="top"> 
	<td scope="row"><label for="unique_display_name"><?php _e('Unique display names', 'wp-symposium'); ?></label></td>
	<td>
	<input type="checkbox" name="unique_display_name" id="unique_display_name" <?php if ($unique_display_name == "on") { echo "CHECKED"; } ?>/>
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
	<td scope="row"><label for="all_friends"><?php _e('Friends to all?', 'wp-symposium'); ?></label></td>
	<td>
	<input type="checkbox" name="all_friends" id="all_friends" <?php if ($all_friends == "on") { echo "CHECKED"; } ?>/>
	<span class="description"><?php echo __('Automatically add new users as friends to all', 'wp-symposium'); ?>
	<br /><input type="checkbox" name="force_all_friends" /> <?php echo __('Set all users as friends to all', 'wp-symposium'); ?></span></td> 
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
		<?php echo __('Use [symposium-search] shortcode, or put &quot;echo symposium_search(150)&quot; in PHP, where 150 is the width in pixels.', 'wp-symposium'); ?><br />
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
