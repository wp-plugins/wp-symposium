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

<?php include_once('dialogs.php'); ?>

<div class="wrap">
<div id="icon-themes" class="icon32"><br /></div>
<h2>Groups</h2>

<div class="metabox-holder"><div id="toc" class="postbox"> 

<h3>Group Settings</h3>

<?php

	global $wpdb;

	// Re-run table create script, in case not done on WPMU
	symposium_groups_activate();
	
    // See if the user has posted profile settings
    if( isset($_POST[ 'symposium_update' ]) && $_POST[ 'symposium_update' ] == 'symposium-groups' ) {

		$group_all_create = (isset($_POST[ 'group_all_create' ])) ? $_POST[ 'group_all_create' ] : '';
		$group_invites = (isset($_POST[ 'group_invites' ])) ? $_POST[ 'group_invites' ] : '';
		$initial_groups = (isset($_POST[ 'initial_groups' ])) ? $_POST[ 'initial_groups' ] : '';
		$group_invites_max = $_POST[ 'group_invites_max' ];

		update_option('symposium_group_all_create', $group_all_create);
		update_option('symposium_group_invites', $group_invites);
		update_option('symposium_group_invites_max', $group_invites_max);
		update_option('symposium_initial_groups', $initial_groups);
		

        // Put an settings updated message on the screen
		echo "<div class='updated slideaway'><p>".__('Saved', 'wp-symposium').".</p></div>";
		
    }

    // Get values from database  
	$group_all_create = get_option('symposium_group_all_create');
	$group_invites = get_option('symposium_group_invites');
	$group_invites_max = get_option('symposium_group_invites_max');
	$initial_groups = get_option('symposium_initial_groups');

	?>

	<form method="post" action=""> 
	<input type="hidden" name="symposium_update" value="symposium-groups">

	<table class="form-table"> 

		<tr valign="top"> 
		<td scope="row"><label for="group_all_create"><?php _e('All users can create', 'wp-symposium'); ?></label></td>
		<td>
		<input type="checkbox" name="group_all_create" id="group_all_create" <?php if ($group_all_create == "on") { echo "CHECKED"; } ?>/>
		<span class="description"><?php echo __('All users or restricted to administrators only', 'wp-symposium'); ?></span></td> 
		</tr> 

		<tr valign="top"> 
		<td scope="row"><label for="initial_groups"><?php _e('Default Groups', 'wp-symposium'); ?></label></td> 
		<td><input name="initial_groups" type="text" id="initial_groups"  value="<?php echo $initial_groups; ?>" /> 
		<span class="description"><?php echo __('Comma separated list of group ID\'s that new members are assigned to (leave blank for no-one)', 'wp-symposium'); ?></td> 
		</tr> 
		
		<tr valign="top"> 
		<td scope="row"><label for="group_invites"><?php _e('Allow group invites', 'wp-symposium'); ?></label></td>
		<td>
		<input type="checkbox" name="group_invites" id="group_invites" <?php if ($group_invites == "on") { echo "CHECKED"; } ?>/>
		<span class="description"><?php echo __("Allow group admin's to invite people to join via email.", 'wp-symposium'); ?></span></td> 
		</tr> 

		<tr valign="top"> 
		<td scope="row"><label for="group_invites_max"><?php _e('Maximum invitations', 'wp-symposium'); ?></label></td>
		<td><input name="group_invites_max" style="width: 50px" type="text" id="group_invites_max" value="<?php echo $group_invites_max; ?>" class="regular-text" /> 
		<span class="description"><?php echo __('How many invitations to join the group can be sent out at one time (to avoid spamming from your server).', 'wp-symposium'); ?></span></td> 
		</tr> 

	<?php
	echo '</table>';

	echo '<p style="margin-left:10px">';
	echo __('Note: If people who are invited to join via email are not members they will be able to register first (if the option is set in WordPress).', 'wp-symposium');
	echo '</p>';
	
	echo '<p class="submit" style="text-align:right; margin-right:12px">';
	echo '<input type="submit" name="Submit" class="button-primary" value="'.__('Save Changes', 'wp-symposium').'" />';
	echo '</p>';
	echo '</form>';
	
	echo '<h3>'.__('Bulk add users to groups', 'wp-symposium').'</h3>';

	echo '<p style="margin-left:10px">';	
	echo __("Select a group to show current members. Then type part of a member's display name or username to search. Keep blank for all users.", "wp-symposium").'<br />';
	echo __("You cannot add or remove the group administrator. Group administrators are not displayed.", "wp-symposium").'<br />';
	echo '</p>';

	$sql = "SELECT * FROM ".$wpdb->prefix."symposium_groups ORDER BY name";
	$groups = $wpdb->get_results($wpdb->prepare($sql));
	
	if ($groups) {
	
		echo '<div style="margin-left:10px">';
		echo '<select id="group_list" style="margin-bottom:10px">';
		echo '<option value=0>'.__('-- Select a group --', 'wp-symposium-group').'</option>';
		foreach ($groups as $group) {
			echo '<option value='.$group->gid.'>'.$group->name.'</option>';
		}
		echo '</select> ';
		echo '<input type="text" style="margin-left:180px" id="user_list_search" /> '; 
		echo '<input type="submit" id="user_list_search_button" name="Submit" class="button-primary" value="'.__('Search', 'wp-symposium').'" />';
		echo '</div>';
		
		echo '<div style="clear:both; margin:10px; float:left;">';
		echo '<strong>'.__('Available users', 'wp-symposium').'</strong><br />';
		echo '<div id="user_list" style="width:300px; height:300px; overflow:auto; background-color:#fff; padding:4px; border:1px solid #aaa;"></div>';
		echo '</div>';
	
		echo '<div style="margin-top:10px; margin-bottom:10px;float:left;">';
		echo '<strong>'.__('Group members', 'wp-symposium').'</strong><br />';
		echo '<div id="selected_users" style="width:300px; height:300px; overflow:auto; background-color:#fff; padding:4px; border:1px solid #aaa;"></div>';
		echo '</div>';

		echo '<div style="clear:both; margin:10px;margin-left:330px">';
		echo '<input type="submit" id="users_add_button" name="Submit" class="button-primary" value="'.__('Update', 'wp-symposium').'" />';
		echo '</div>';

	} else {

		echo '<p style="margin-left:10px">';
		echo __('No groups created yet.', 'wp-sympsosium-groups');
		echo '</p>';

	}	
					  
?>

</div></div>

</div>
