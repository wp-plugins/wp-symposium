<?php
/*
Plugin Name: WP Symposium Widgets
Plugin URI: http://www.wpsymposium.com
Description: Widgets for use with WP Symposium.
Version: 11.10.8
Author: WP Symposium
Author URI: http://www.wpsymposium.com
License: GPL3
*/

/*  Copyright 2010,2011  Simon Goodchild  (info@wpsymposium.com)

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
*/

/** Add our function to the widgets_init hook. **/

add_action( 'widgets_init', 'symposium_load_widgets' );

function symposium_load_widgets() {
	register_widget( 'Recentactivity_Widget' );
	register_widget( 'Forumrecentposts_Widget' );
	register_widget( 'Forumexperts_Widget' );
	register_widget( 'Forumnoanswer_Widget' );
	register_widget( 'Symposium_members_Widget' );
	register_widget( 'Symposium_summary_Widget' );
	register_widget( 'Symposium_friends_Widget' );
	register_widget( 'Symposium_recent_Widget' );
}

/** Symposium: Recently Online ************************************************************************* **/
class Symposium_recent_Widget extends WP_Widget {

	function Symposium_recent_Widget() {
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'widget_symposium_recent', 'description' => 'Shows members recently online.' );
		
		/* Widget control settings. */
		$control_ops = array( 'id_base' => 'symposium_recent-widget' );
		
		/* Create the widget. */
		$this->WP_Widget( 'symposium_recent-widget', 'Symposium: '.__('Recently Online', 'wp-symposium'), $widget_ops, $control_ops );
	}
	
	// This is shown on the page
	function widget( $args, $instance ) {
		global $wpdb, $current_user;
		wp_get_current_user();
		
		if (is_user_logged_in()) {
	
			extract( $args );
			
			// Get options
			$symposium_recent_title = apply_filters('widget_title', $instance['symposium_recent_title'] );
			$symposium_recent_count = apply_filters('widget_symposium_members_count', $instance['symposium_recent_count'] );
			$symposium_recent_desc = apply_filters('widget_symposium_recent_desc', $instance['symposium_recent_desc'] );
			$symposium_recent_show_light = apply_filters('widget_symposium_recent_show_light', $instance['symposium_recent_show_light'] );
			$symposium_recent_show_mail = apply_filters('widget_symposium_recent_show_mail', $instance['symposium_recent_show_mail'] );
			
			// Start widget
			echo $before_widget;
			echo $before_title . $symposium_recent_title . $after_title;
			
			// Content of widget
			$sql = "SELECT u.ID, u.display_name, m.last_activity 
				FROM ".$wpdb->base_prefix."users u 
				LEFT JOIN ".$wpdb->base_prefix."symposium_usermeta m ON u.ID = m.uid
				ORDER BY m.last_activity DESC LIMIT 0,".$symposium_recent_count;

			$members = $wpdb->get_results($wpdb->prepare($sql, $current_user->ID));
				
			if ($members) {
	
				$mail_url = symposium_get_url('mail');
				$profile_url = symposium_get_url('profile');
				$q = symposium_string_query($mail_url);
				$time_now = time();

				echo "<div id='symposium_new_members'>";
				
					$cnt = 0;
					foreach ($members as $member)
					{
						$last_active_minutes = strtotime($member->last_activity);
						$last_active_minutes = floor(($time_now-$last_active_minutes)/60);
						
						if ($symposium_recent_desc == 'on') {
							echo "<div class='symposium_new_members_row'>";		
								echo "<div class='symposium_new_members_row_avatar'>";
									echo "<a href='".$profile_url.$q."uid=".$member->ID."'>";
										echo get_avatar($member->ID, 32);
									echo "</a>";
								echo "</div>";
								echo "<div class='symposium_new_members_row_member'>";
									echo symposium_profile_link($member->ID)." ";
									if ($symposium_recent_show_light == 'on') {
										if ($last_active_minutes >= WPS_OFFLINE) {
											echo '<img src="'.WPS_IMAGES_URL.'/loggedout.gif"> ';
										} else {
											if ($last_active_minutes >= WPS_ONLINE) {
												echo '<img src="'.WPS_IMAGES_URL.'/inactive.gif"> ';
											} else {
												echo '<img src="'.WPS_IMAGES_URL.'/online.gif"> ';
											}
										}
									}
									echo __('last active', 'wp-symposium')." ";
									echo symposium_time_ago($member->last_activity).".";
									if ($symposium_recent_show_mail == 'on') {
										echo " <a title='".$member->display_name."' href='".$mail_url.$q."view=compose&to=".$member->ID."'>".__('Send Mail', 'wp-symposium')."</a>";
									}
								echo "</div>";
							echo "</div>";
						} else {
							echo "<a title='".$member->display_name."' style='padding-right:3px;padding-bottom:3px;float:left;cursor:pointer;' href='".$profile_url.$q."uid=".$member->ID."'>";
								echo get_avatar($member->ID, 32);
							echo "</a>";
						}
					}
					echo "</div>";				
			} else {
				echo "<div id='symposium_new_members'>";
				echo __("Nobody recently online.", "wp-symposium");
				echo "</div>";							
			}
			// End content
		
			echo $after_widget;
			// End widget
		}
	}
	
	// This updates the stored values
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		
		/* Strip tags (if needed) and update the widget settings. */
		$instance['symposium_recent_title'] = strip_tags( $new_instance['symposium_recent_title'] );
		$instance['symposium_recent_count'] = strip_tags( $new_instance['symposium_recent_count'] );
		$instance['symposium_recent_desc'] = strip_tags( $new_instance['symposium_recent_desc'] );
		$instance['symposium_recent_show_light'] = strip_tags( $new_instance['symposium_recent_show_light'] );
		$instance['symposium_recent_show_mail'] = strip_tags( $new_instance['symposium_recent_show_mail'] );
		return $instance;
	}
	
	// This is the admin form for the widget
	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'symposium_recent_title' => 'Recently Online', 'symposium_recent_count' => '5', 'symposium_recent_desc' => 'on', 'symposium_recent_show_light' => '', 'symposium_recent_show_mail' => 'on' );
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<p>
			<label for="<?php echo $this->get_field_id( 'symposium_recent_title' ); ?>"><?php echo __('Widget Title', 'wp-symposium'); ?>:</label>
			<input id="<?php echo $this->get_field_id( 'symposium_recent_title' ); ?>" name="<?php echo $this->get_field_name( 'symposium_recent_title' ); ?>" value="<?php echo $instance['symposium_recent_title']; ?>" />
		<br /><br />
			<label for="<?php echo $this->get_field_id( 'symposium_recent_count' ); ?>"><?php echo __('Max number shown', 'wp-symposium'); ?>:</label>
			<input id="<?php echo $this->get_field_id( 'symposium_recent_count' ); ?>" name="<?php echo $this->get_field_name( 'symposium_recent_count' ); ?>" value="<?php echo $instance['symposium_recent_count']; ?>" style="width: 30px" />
		<br /><br />
			<label for="<?php echo $this->get_field_id( 'symposium_recent_desc' ); ?>"><?php echo __('Show details as list', 'wp-symposium'); ?>:</label>
			<input type="checkbox" id="<?php echo $this->get_field_id( 'symposium_recent_desc' ); ?>" name="<?php echo $this->get_field_name( 'symposium_recent_desc' ); ?>"
			<?php if ($instance['symposium_recent_desc'] == 'on') { echo " CHECKED"; } ?>
			/>
		<?php if ($instance['symposium_recent_desc'] == 'on') { ?>
		<br /><br />
			<label for="<?php echo $this->get_field_id( 'symposium_recent_show_light' ); ?>"><?php echo __('Show online status indicator', 'wp-symposium'); ?>:</label>
			<input type="checkbox" id="<?php echo $this->get_field_id( 'symposium_recent_show_light' ); ?>" name="<?php echo $this->get_field_name( 'symposium_recent_show_light' ); ?>"
			<?php if ($instance['symposium_recent_show_light'] == 'on') { echo " CHECKED"; } ?>
			/>
		<br /><br />
			<label for="<?php echo $this->get_field_id( 'symposium_recent_show_mail' ); ?>"><?php echo __('Show mail link', 'wp-symposium'); ?>:</label>
			<input type="checkbox" id="<?php echo $this->get_field_id( 'symposium_recent_show_mail' ); ?>" name="<?php echo $this->get_field_name( 'symposium_recent_show_mail' ); ?>"
			<?php if ($instance['symposium_recent_show_mail'] == 'on') { echo " CHECKED"; } ?>
			/>
		<?php } else { ?>
			<input type="hidden" id="<?php echo $this->get_field_id( 'symposium_recent_show_light' ); ?>" name="<?php echo $this->get_field_name( 'symposium_recent_show_light' ); ?>" value="<?php echo $instance['symposium_recent_show_light']; ?>" style="width: 30px" />
			<input type="hidden" id="<?php echo $this->get_field_id( 'symposium_recent_show_mail' ); ?>" name="<?php echo $this->get_field_name( 'symposium_recent_show_mail' ); ?>" value="<?php echo $instance['symposium_recent_show_mail']; ?>" style="width: 30px" />
		<?php }  ?>
		</p>
		<?php
	}

}

/** Forum: Recent Posts ************************************************************************* **/
class Recentactivity_Widget extends WP_Widget {

	function Recentactivity_Widget() {
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'widget_recentactivity', 'description' => 'Shows recent member posts (not replies, ie. their status). Does not include Group posts. Observes privacy settings.' );
		
		/* Widget control settings. */
		$control_ops = array( 'id_base' => 'recentactivity-widget' );
		
		/* Create the widget. */
		$this->WP_Widget( 'recentactivity-widget', 'Symposium: '.__('Recent Activity', 'wp-symposium'), $widget_ops, $control_ops );
	}
	
	// This is shown on the page
	function widget( $args, $instance ) {
		global $wpdb, $current_user;
		wp_get_current_user();
	
		extract( $args );
		
		
		// Work out link to profile page, dealing with permalinks or not
		$profile_url = symposium_get_url('profile');
		if (strpos($profile_url, '?') !== FALSE) {
			$q = "&";
		} else {
			$q = "?";
		}
		
		// Get options
		$wtitle = apply_filters('widget_title', $instance['wtitle'] );
		$postcount = apply_filters('widget_postcount', $instance['postcount'] );
		$preview = apply_filters('widget_preview', $instance['preview'] );
		$shown_uid = "";
		$shown_count = 0;
		
		// Start widget
		echo $before_widget;
		echo $before_title . $wtitle . $after_title;
		
		// Content of widget
		$posts = $wpdb->get_results("SELECT cid, author_uid, comment, comment_timestamp, display_name 
		FROM ".$wpdb->base_prefix."symposium_comments c 
		INNER JOIN ".$wpdb->base_prefix."users u ON c.author_uid = u.ID 
		WHERE is_group != 'on' AND comment_parent = 0 AND author_uid = subject_uid ORDER BY cid DESC LIMIT 0,50");
			
		if ($posts) {

			echo "<div id='symposium_recent_activity'>";
				
				foreach ($posts as $post)
				{
					if ($shown_count < $postcount) {
						if (strpos($shown_uid, $post->author_uid.",") === FALSE) { 

							$share = $wpdb->get_var($wpdb->prepare("SELECT wall_share FROM ".$wpdb->prefix."symposium_usermeta WHERE uid = ".$post->author_uid));
							$is_friend = symposium_friend_of($post->author_uid, $current_user->ID);

							if ( (strtolower($share) == 'everyone') || (strtolower($share) == 'public') || (strtolower($share) == 'friends only' && $is_friend) ) {

								echo "<div class='symposium_recent_activity_row'>";		
									echo "<div class='symposium_recent_activity_row_avatar'>";
										echo get_avatar($post->author_uid, 32);
									echo "</div>";
									echo "<div class='symposium_recent_activity_row_post'>";
										$text = stripslashes($post->comment);
										if ( strlen($text) > $preview ) { $text = substr($text, 0, $preview)."..."; }
										echo "<a href='".$profile_url.$q."uid=".$post->author_uid."&post=".$post->cid."'>".$post->display_name."</a> ".$text." ".symposium_time_ago($post->comment_timestamp).".<br>";
									echo "</div>";
								echo "</div>";
							
								$shown_count++;
								$shown_uid .= $post->author_uid.",";							
							}
						}
					} else {
						break;
					}
				}

			echo "</div>";

		}
		// End content
		
		echo $after_widget;
		// End widget
	}
	
	// This updates the stored values
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		
		/* Strip tags (if needed) and update the widget settings. */
		$instance['wtitle'] = strip_tags( $new_instance['wtitle'] );
		$instance['postcount'] = strip_tags( $new_instance['postcount'] );
		$instance['preview'] = strip_tags( $new_instance['preview'] );
		return $instance;
	}
	
	// This is the admin form for the widget
	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'wtitle' => 'What are members saying?', 'postcount' => '5', 'preview' => '60' );
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<p>
			<label for="<?php echo $this->get_field_id( 'wtitle' ); ?>"><?php echo __('Widget Title', 'wp-symposium'); ?>:</label>
			<input id="<?php echo $this->get_field_id( 'wtitle' ); ?>" name="<?php echo $this->get_field_name( 'wtitle' ); ?>" value="<?php echo $instance['wtitle']; ?>" />
		<br /><br />
			<label for="<?php echo $this->get_field_id( 'postcount' ); ?>"><?php echo __('Max number of posts', 'wp-symposium'); ?>:</label>
			<input id="<?php echo $this->get_field_id( 'postcount' ); ?>" name="<?php echo $this->get_field_name( 'postcount' ); ?>" value="<?php echo $instance['postcount']; ?>" style="width: 30px" />
		<br /><br />
			<label for="<?php echo $this->get_field_id( 'preview' ); ?>"><?php echo __('Max length of preview', 'wp-symposium'); ?>:</label>
			<input id="<?php echo $this->get_field_id( 'preview' ); ?>" name="<?php echo $this->get_field_name( 'preview' ); ?>" value="<?php echo $instance['preview']; ?>" style="width: 30px" />
		</p>
		<?php
	}

}

/** Symposium: New Members ************************************************************************* **/
class Symposium_members_Widget extends WP_Widget {

	function Symposium_members_Widget() {
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'widget_symposium_members', 'description' => 'Shows recent new members.' );
		
		/* Widget control settings. */
		$control_ops = array( 'id_base' => 'symposium_members-widget' );
		
		/* Create the widget. */
		$this->WP_Widget( 'symposium_members-widget', 'Symposium: '.__('Latest New Members', 'wp-symposium'), $widget_ops, $control_ops );
	}
	
	// This is shown on the page
	function widget( $args, $instance ) {
		global $wpdb, $current_user;
		wp_get_current_user();
	
		extract( $args );
		
		// Get options
		$symposium_members_count_title = apply_filters('widget_title', $instance['symposium_members_count_title'] );
		$symposium_members_count = apply_filters('widget_symposium_members_count', $instance['symposium_members_count'] );
		
		// Start widget
		echo $before_widget;
		echo $before_title . $symposium_members_count_title . $after_title;
		
		// Content of widget

		$members = $wpdb->get_results("
			SELECT * FROM ".$wpdb->base_prefix."users
			ORDER BY user_registered DESC LIMIT 0,".$symposium_members_count); 
		
		if ($members) {

			echo "<div id='symposium_new_members'>";
	
				foreach ($members as $member)
				{
					echo "<div class='symposium_new_members_row'>";		
						echo "<div class='symposium_new_members_row_avatar'>";
							echo get_avatar($member->ID, 32);
						echo "</div>";
						echo "<div class='symposium_new_members_row_member'>";
							echo symposium_profile_link($member->ID)." ".__('joined', 'wp-symposium')." ";
							echo symposium_time_ago($member->user_registered).".";
						echo "</div>";
					echo "</div>";
				}
				
				echo "</div>";				
		}

		// End content
		
		echo $after_widget;
		// End widget
	}
	
	// This updates the stored values
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		
		/* Strip tags (if needed) and update the widget settings. */
		$instance['symposium_members_count_title'] = strip_tags( $new_instance['symposium_members_count_title'] );
		$instance['symposium_members_count'] = strip_tags( $new_instance['symposium_members_count'] );
		return $instance;
	}
	
	// This is the admin form for the widget
	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'symposium_members_count_title' => 'New Members', 'symposium_members_count' => '5' );
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<p>
			<label for="<?php echo $this->get_field_id( 'symposium_members_count_title' ); ?>"><?php echo __('Widget Title', 'wp-symposium'); ?>:</label>
			<input id="<?php echo $this->get_field_id( 'symposium_members_count_title' ); ?>" name="<?php echo $this->get_field_name( 'symposium_members_count_title' ); ?>" value="<?php echo $instance['symposium_members_count_title']; ?>" />
		<br /><br />
			<label for="<?php echo $this->get_field_id( 'symposium_members_count' ); ?>"><?php echo __('Max number shown', 'wp-symposium'); ?>:</label>
			<input id="<?php echo $this->get_field_id( 'symposium_members_count' ); ?>" name="<?php echo $this->get_field_name( 'symposium_members_count' ); ?>" value="<?php echo $instance['symposium_members_count']; ?>" style="width: 30px" />
		</p>
		<?php
	}

}

/** Symposium: Friends ************************************************************************* **/
class Symposium_friends_Widget extends WP_Widget {

	function Symposium_friends_Widget() {
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'widget_symposium_friends', 'description' => 'Shows a member friends, when logged in.' );
		
		/* Widget control settings. */
		$control_ops = array( 'id_base' => 'symposium_friends-widget' );
		
		/* Create the widget. */
		$this->WP_Widget( 'symposium_friends-widget', 'Symposium: '.__('Your Friends', 'wp-symposium'), $widget_ops, $control_ops );
	}
	
	// This is shown on the page
	function widget( $args, $instance ) {
		global $wpdb, $current_user;
		wp_get_current_user();
		
		if (is_user_logged_in()) {
	
			extract( $args );
			
			// Get options
			$symposium_friends_count_title = apply_filters('widget_title', $instance['symposium_friends_count_title'] );
			$symposium_friends_count = apply_filters('widget_symposium_friends_count', $instance['symposium_friends_count'] );
			$symposium_friends_desc = apply_filters('widget_symposium_friends_desc', $instance['symposium_friends_desc'] );
			$symposium_friends_mode = apply_filters('widget_symposium_friends_mode', $instance['symposium_friends_mode'] );
			$symposium_friends_show_light = apply_filters('widget_symposium_friends_show_light', $instance['symposium_friends_show_light'] );
			$symposium_friends_show_mail = apply_filters('widget_symposium_friends_show_mail', $instance['symposium_friends_show_mail'] );
			
			// Start widget
			echo $before_widget;
			echo $before_title . $symposium_friends_count_title . $after_title;
			
			// Content of widget
			$sql = "SELECT u.ID, u.display_name, m.last_activity 
				FROM ".$wpdb->base_prefix."symposium_friends f
				LEFT JOIN ".$wpdb->base_prefix."users u ON f.friend_to = u.ID
				LEFT JOIN ".$wpdb->base_prefix."symposium_usermeta m ON f.friend_to = m.uid
				WHERE f.friend_from = %d AND f.friend_accepted = 'on' 
				ORDER BY m.last_activity DESC LIMIT 0,".$symposium_friends_count;

			$members = $wpdb->get_results($wpdb->prepare($sql, $current_user->ID));
				
			if ($members) {
	
				$mail_url = symposium_get_url('mail');
				$profile_url = symposium_get_url('profile');
				$q = symposium_string_query($mail_url);
				$time_now = time();
	
				echo "<div id='symposium_new_members'>";
				
					if ($symposium_friends_mode == 'all' || $symposium_friends_mode == 'online') {
						$loop=1;
					} else {
						$loop=2;
					}
					for ($l=1; $l<=$loop; $l++) {
						
						if ($symposium_friends_mode == 'split') {
							if ($l==1) {
								echo '<div style="font-weight:bold">'.__('Online', 'wp-symposium').'</div>';
							} else {
								echo '<div style="clear:both;margin-top:6px;font-weight:bold">'.__('Offline', 'wp-symposium').'</div>';
							}
							
						}
		
						$cnt = 0;
						foreach ($members as $member)
						{
							$last_active_minutes = strtotime($member->last_activity);
							$last_active_minutes = floor(($time_now-$last_active_minutes)/60);
							
							$show = false;
							if ($symposium_friends_mode == 'online' && $last_active_minutes < WPS_OFFLINE) { $show = true; }
							if ( ($symposium_friends_mode == 'split') && ( ($last_active_minutes < WPS_OFFLINE && $l == 1) || ($last_active_minutes >= WPS_OFFLINE && $l == 2) ) ) { $show = true; }
							if ($symposium_friends_mode == 'all') { $show = true; }
							
							if ($show) {
								$cnt++;								
								if ($symposium_friends_desc == 'on') {
									echo "<div class='symposium_new_members_row'>";		
										echo "<div class='symposium_new_members_row_avatar'>";
											echo "<a href='".$profile_url.$q."uid=".$member->ID."'>";
												echo get_avatar($member->ID, 32);
											echo "</a>";
										echo "</div>";
										echo "<div class='symposium_new_members_row_member'>";
											echo symposium_profile_link($member->ID)." ";
											if ($symposium_friends_show_light == 'on') {
												if ($last_active_minutes >= WPS_OFFLINE) {
													echo '<img src="'.WPS_IMAGES_URL.'/loggedout.gif"> ';
												} else {
													if ($last_active_minutes >= WPS_ONLINE) {
														echo '<img src="'.WPS_IMAGES_URL.'/inactive.gif"> ';
													} else {
														echo '<img src="'.WPS_IMAGES_URL.'/online.gif"> ';
													}
												}
											}
											echo __('last active', 'wp-symposium')." ";
											echo symposium_time_ago($member->last_activity).".";
											if ($symposium_friends_show_mail == 'on') {
												echo " <a title='".$member->display_name."' href='".$mail_url.$q."view=compose&to=".$member->ID."'>".__('Send Mail', 'wp-symposium')."</a>";
											}
										echo "</div>";
									echo "</div>";
								} else {
									echo "<a title='".$member->display_name."' style='padding-right:3px;padding-bottom:3px;float:left;cursor:pointer;' href='".$profile_url.$q."uid=".$member->ID."'>";
										echo get_avatar($member->ID, 32);
									echo "</a>";
								}
							}
						}
						if ($cnt == 0) {
							echo __('Nobody', 'wp-symposium');
						}
					}
					
					echo "</div>";				
			} else {
				echo "<div id='symposium_new_members'>";
				echo __("No friends yet, add friends via their profile page.", "wp-symposium");
				echo "</div>";							
			}
			// End content
		
			echo $after_widget;
			// End widget
		}
	}
	
	// This updates the stored values
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		
		/* Strip tags (if needed) and update the widget settings. */
		$instance['symposium_friends_count_title'] = strip_tags( $new_instance['symposium_friends_count_title'] );
		$instance['symposium_friends_count'] = strip_tags( $new_instance['symposium_friends_count'] );
		$instance['symposium_friends_desc'] = strip_tags( $new_instance['symposium_friends_desc'] );
		$instance['symposium_friends_mode'] = strip_tags( $new_instance['symposium_friends_mode'] );
		$instance['symposium_friends_show_light'] = strip_tags( $new_instance['symposium_friends_show_light'] );
		$instance['symposium_friends_show_mail'] = strip_tags( $new_instance['symposium_friends_show_mail'] );
		return $instance;
	}
	
	// This is the admin form for the widget
	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'symposium_friends_count_title' => 'Your Friends', 'symposium_friends_count' => '5', 'symposium_friends_desc' => 'on', 'symposium_friends_mode' => 'all', 'symposium_friends_show_light' => '', 'symposium_friends_show_mail' => 'on' );
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<p>
			<label for="<?php echo $this->get_field_id( 'symposium_friends_count_title' ); ?>"><?php echo __('Widget Title', 'wp-symposium'); ?>:</label>
			<input id="<?php echo $this->get_field_id( 'symposium_friends_count_title' ); ?>" name="<?php echo $this->get_field_name( 'symposium_friends_count_title' ); ?>" value="<?php echo $instance['symposium_friends_count_title']; ?>" />
		<br /><br />
			<label for="<?php echo $this->get_field_id( 'symposium_friends_count' ); ?>"><?php echo __('Max number shown', 'wp-symposium'); ?>:</label>
			<input id="<?php echo $this->get_field_id( 'symposium_friends_count' ); ?>" name="<?php echo $this->get_field_name( 'symposium_friends_count' ); ?>" value="<?php echo $instance['symposium_friends_count']; ?>" style="width: 30px" />
		<br /><br />
			<label for="<?php echo $this->get_field_id( 'symposium_friends_desc' ); ?>"><?php echo __('Show details as list', 'wp-symposium'); ?>:</label>
			<input type="checkbox" id="<?php echo $this->get_field_id( 'symposium_friends_desc' ); ?>" name="<?php echo $this->get_field_name( 'symposium_friends_desc' ); ?>"
			<?php if ($instance['symposium_friends_desc'] == 'on') { echo " CHECKED"; } ?>
			/>
		<?php if ($instance['symposium_friends_desc'] == 'on') { ?>
		<br /><br />
			<label for="<?php echo $this->get_field_id( 'symposium_friends_show_light' ); ?>"><?php echo __('Show online status indicator', 'wp-symposium'); ?>:</label>
			<input type="checkbox" id="<?php echo $this->get_field_id( 'symposium_friends_show_light' ); ?>" name="<?php echo $this->get_field_name( 'symposium_friends_show_light' ); ?>"
			<?php if ($instance['symposium_friends_show_light'] == 'on') { echo " CHECKED"; } ?>
			/>
		<br /><br />
			<label for="<?php echo $this->get_field_id( 'symposium_friends_show_mail' ); ?>"><?php echo __('Show mail link', 'wp-symposium'); ?>:</label>
			<input type="checkbox" id="<?php echo $this->get_field_id( 'symposium_friends_show_mail' ); ?>" name="<?php echo $this->get_field_name( 'symposium_friends_show_mail' ); ?>"
			<?php if ($instance['symposium_friends_show_mail'] == 'on') { echo " CHECKED"; } ?>
			/>
		<?php } else { ?>
			<input type="hidden" id="<?php echo $this->get_field_id( 'symposium_friends_show_light' ); ?>" name="<?php echo $this->get_field_name( 'symposium_friends_show_light' ); ?>" value="<?php echo $instance['symposium_friends_show_light']; ?>" style="width: 30px" />
			<input type="hidden" id="<?php echo $this->get_field_id( 'symposium_friends_show_mail' ); ?>" name="<?php echo $this->get_field_name( 'symposium_friends_show_mail' ); ?>" value="<?php echo $instance['symposium_friends_show_mail']; ?>" style="width: 30px" />
		<?php }  ?>
		<br /><br />
			<label for="<?php echo $this->get_field_id( 'symposium_friends_mode' ); ?>"><?php echo __('Show', 'wp-symposium'); ?>:</label>
			<select id="<?php echo $this->get_field_id( 'symposium_friends_mode' ); ?>" name="<?php echo $this->get_field_name( 'symposium_friends_mode' ); ?>">
				<option value='all'
					<?php if ($instance['symposium_friends_mode'] == 'all') { echo " SELECTED"; } ?>
					><?php _e("All", "wp-symposium"); ?>
				<option value='split'
					<?php if ($instance['symposium_friends_mode'] == 'split') { echo " SELECTED"; } ?>
					><?php _e("Online/offline split", "wp-symposium"); ?>
				<option value='online'
					<?php if ($instance['symposium_friends_mode'] == 'online') { echo " SELECTED"; } ?>
					><?php _e("Online only", "wp-symposium"); ?>					
			</select>
		</p>
		<?php
	}

}

/** Forum: Recent Posts ************************************************************************* **/
class Forumrecentposts_Widget extends WP_Widget {

	function Forumrecentposts_Widget() {
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'widget_forumrecentposts', 'description' => 'Shows a number of recent posts.' );
		
		/* Widget control settings. */
		$control_ops = array( 'id_base' => 'forumrecentposts-widget' );
		
		/* Create the widget. */
		$this->WP_Widget( 'forumrecentposts-widget', 'Symposium: '.__('Latest Forum Posts', 'wp-symposium'), $widget_ops, $control_ops );
	}
	
	// This is shown on the page
	function widget( $args, $instance ) {
		global $wpdb, $current_user;
		wp_get_current_user();
	
		extract( $args );
		
		// Get options
		$wtitle = apply_filters('widget_title', $instance['wtitle'] );
		$postcount = apply_filters('widget_postcount', $instance['postcount'] );
		$preview = apply_filters('widget_preview', $instance['preview'] );
		$cat_id = apply_filters('widget_cat_id', $instance['cat_id'] );
		$show_replies = apply_filters('widget_show_replies', $instance['show_replies'] );

		// Previous login
		if (is_user_logged_in()) {
			$previous_login = get_symposium_meta($current_user->ID, 'previous_login');
		}
		
		// Start widget
		echo $before_widget;
		echo $before_title . $wtitle . $after_title;
		
		// Content of widget
		$sql = "SELECT tid, topic_subject, topic_owner, topic_post, topic_started, topic_category, topic_date, display_name, topic_parent, topic_group 
		FROM ".$wpdb->prefix.'symposium_topics'." t 
		INNER JOIN ".$wpdb->base_prefix.'users'." u ON t.topic_owner = u.ID 
		WHERE tid = tid AND topic_approved='on' ";
		if ($cat_id != '' && $cat_id > 0) {
			$sql .= "AND topic_category = ".$cat_id." ";
		}
		if ($show_replies != 'on') {
			$sql .= "AND topic_parent = 0 ";
		}
		$sql .= "ORDER BY tid DESC LIMIT 0,100";
		$posts = $wpdb->get_results($sql); 
		$count = 0;

		// Get forum URL worked out
		$forum_url = symposium_get_url('forum');
		$forum_q = symposium_string_query($forum_url);
		
		if ($posts) {

			echo "<div id='symposium_latest_forum'>";
				
				foreach ($posts as $post)
				{
						if ($post->topic_group == 0 || (symposium_member_of($post->topic_group) == "yes") || ($wpdb->get_var($wpdb->prepare("SELECT content_private FROM ".$wpdb->prefix."symposium_groups WHERE gid = ".$post->topic_group)) != "on") ) {

							echo "<div class='symposium_latest_forum_row'>";		
								echo "<div class='symposium_latest_forum_row_avatar'>";
									echo get_avatar($post->topic_owner, 32);
								echo "</div>";
								echo "<div class='symposium_latest_forum_row_post'>";
									if ($post->topic_parent > 0) {
										echo symposium_profile_link($post->topic_owner);
										if ($preview > 0) {
											$text = stripslashes($post->topic_post);
											if ( strlen($text) > $preview ) { $text = substr($text, 0, $preview)."..."; }
											echo " ".__('replied', 'wp-symposium')." <a href='".$forum_url."?cid=".$post->topic_category."&show=".$post->topic_parent."'>".$text."</a>";
										} else {
											echo "<br />";
										}
										echo " ".symposium_time_ago($post->topic_date).".";
									} else {
										echo symposium_profile_link($post->topic_owner);
										if ($preview > 0) {
											$text = stripslashes($post->topic_subject);
											if ( strlen($text) > $preview ) { $text = substr($text, 0, $preview)."..."; }
											if ($post->topic_group == 0) {
												$url = $forum_url;
												$q = $forum_q;
											} else {
												// Get group URL worked out
												$url = symposium_get_url('group');
												if (strpos($url, '?') !== FALSE) {
													$q = "&gid=".$post->topic_group."&";
												} else {
													$q = "?gid=".$post->topic_group."&";
												}
											}
											echo " ".__('started', 'wp-symposium')." <a href='".$url.$q."cid=".$post->topic_category."&show=".$post->tid."'>".$text."</a>";
										}
										echo " ".symposium_time_ago($post->topic_started).".";
									}
										if (is_user_logged_in()) {
											if ($post->topic_date > $previous_login && $post->topic_owner != $current_user->ID) {
												echo " <img src='".WPS_IMAGES_URL."/new.gif' alt='New!' />";
											}
										}
								echo "</div>";
							echo "</div>";
							
							$count++;
							if ($count >= $postcount) {
								break;
							}
							
						}
				}

			echo "</div>";

		}
		// End content
		
		echo $after_widget;
		// End widget
	}
	
	// This updates the stored values
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		
		/* Strip tags (if needed) and update the widget settings. */
		$instance['wtitle'] = strip_tags( $new_instance['wtitle'] );
		$instance['postcount'] = strip_tags( $new_instance['postcount'] );
		$instance['preview'] = strip_tags( $new_instance['preview'] );
		$instance['cat_id'] = strip_tags( $new_instance['cat_id'] );
		$instance['show_replies'] = strip_tags( $new_instance['show_replies'] );
		return $instance;
	}
	
	// This is the admin form for the widget
	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'wtitle' => 'Recent Forum Posts', 'show_replies' => 'on', 'postcount' => '3', 'cat_id' => '0', 'preview' => '30' );
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<p>
			<label for="<?php echo $this->get_field_id( 'wtitle' ); ?>"><?php echo __('Widget Title', 'wp-symposium'); ?>:</label>
			<input id="<?php echo $this->get_field_id( 'wtitle' ); ?>" name="<?php echo $this->get_field_name( 'wtitle' ); ?>" value="<?php echo $instance['wtitle']; ?>" />
		<br /><br />
			<label for="<?php echo $this->get_field_id( 'show_replies' ); ?>"><?php echo __('Show replies', 'wp-symposium'); ?>:</label>
			<input type="checkbox" id="<?php echo $this->get_field_id( 'show_replies' ); ?>" name="<?php echo $this->get_field_name( 'show_replies' ); ?>"
			<?php if ($instance['show_replies'] == 'on') { echo " CHECKED"; } ?>
			/>
		<br /><br />
			<label for="<?php echo $this->get_field_id( 'postcount' ); ?>"><?php echo __('Max number of posts', 'wp-symposium'); ?>:</label>
			<input id="<?php echo $this->get_field_id( 'postcount' ); ?>" name="<?php echo $this->get_field_name( 'postcount' ); ?>" value="<?php echo $instance['postcount']; ?>" style="width: 30px" />
		<br /><br />
			<label for="<?php echo $this->get_field_id( 'cat_id' ); ?>"><?php echo __('Category ID (0 for all)', 'wp-symposium'); ?>:</label>
			<input id="<?php echo $this->get_field_id( 'cat_id' ); ?>" name="<?php echo $this->get_field_name( 'cat_id' ); ?>" value="<?php echo $instance['cat_id']; ?>" style="width: 30px" />
		<br /><br />
			<label for="<?php echo $this->get_field_id( 'preview' ); ?>"><?php echo __('Max length of preview', 'wp-symposium'); ?>:</label>
			<input id="<?php echo $this->get_field_id( 'preview' ); ?>" name="<?php echo $this->get_field_name( 'preview' ); ?>" value="<?php echo $instance['preview']; ?>" style="width: 30px" />
		</p>
		<?php
	}

}

/** Login/Summary Widget ************************************************************************* **/
class symposium_summary_Widget extends WP_Widget {

	function symposium_summary_Widget() {
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'widget_symposium_summary', 'description' => 'When logged in, shows a summary of the WP Symposium user.' );
		
		/* Widget control settings. */
		$control_ops = array( 'id_base' => 'symposium_summary-widget' );
		
		/* Create the widget. */
		$this->WP_Widget( 'symposium_summary-widget', 'Symposium: '.__('Summary', 'wp-symposium'), $widget_ops, $control_ops );
	}
	
	// This is shown on the page
	function widget( $args, $instance ) {
		global $wpdb, $current_user;
		wp_get_current_user();

	
		extract( $args );
		
		// Get options
		$wtitle = apply_filters('widget_title', $instance['wtitle'] );
		$show_loggedout = apply_filters('widget_show_loggedout', $instance['show_loggedout'] );
		$show_form = apply_filters('widget_show_form', $instance['show_form'] );
		$login_url = apply_filters('widget_show_form', $instance['login_url'] );
		
		// Start widget
		echo $before_widget;
		echo $before_title . $wtitle . $after_title;

		// Content of widget

		echo "<div id='symposium_summary_widget'>";

			if (is_user_logged_in()) {

				// LOGGED IN

				echo "<ul>";
		
				// Mail
				if (function_exists('symposium_mail')) {

					// Get mail URL worked out
					$mail_url = symposium_get_url('mail');

					echo "<li>";
						$total_mail = $wpdb->get_var($wpdb->prepare("SELECT count(*) FROM ".$wpdb->base_prefix."symposium_mail WHERE mail_to = ".$current_user->ID." AND mail_in_deleted != 'on'"));
						echo "<a href='".$mail_url."'>".__("Messages:", "wp-symposium")."</a> ".$total_mail;
						$unread_mail = $wpdb->get_var($wpdb->prepare("SELECT count(*) FROM ".$wpdb->base_prefix."symposium_mail WHERE mail_to = ".$current_user->ID." AND mail_in_deleted != 'on' AND mail_read != 'on'"));
						if ($unread_mail > 0) {
							echo " (".$unread_mail." ".__("unread","wp-symposium").")";
						}
					echo "</li>";
				}

				// Friends
				if (function_exists('symposium_profile')) {

					// Get mail URL worked out
					$friends_url = symposium_get_url('profile');
					if (strpos($friends_url, '?') !== FALSE) {
						$q = "&view=friends";
					} else {
						$q = "?view=friends";
					}

					echo "<li>";
						$sql = "SELECT count(*) FROM ".$wpdb->base_prefix."symposium_friends WHERE friend_to = %d AND friend_accepted = 'on'";
						$current_friends = $wpdb->get_var($wpdb->prepare($sql, $current_user->ID));
						echo  "<a href='".$friends_url.$q."'>".__("Friends:", "wp-symposium")."</a> ".$current_friends;
						$sql = "SELECT count(*) FROM ".$wpdb->base_prefix."symposium_friends WHERE friend_to = %d AND friend_accepted != 'on'";
						$friend_requests = $wpdb->get_var($wpdb->prepare($sql, $current_user->ID));

						if ($friend_requests == 1) {	
							echo " (".$friend_requests." ".__("request","wp-symposium").")";
						}
						if ($friend_requests > 1) {	
							echo " (".$friend_requests." ".__("requests","wp-symposium").")";
						}
					echo "</li>";

					// Hook for more list items
					do_action('symposium_widget_summary_hook_loggedin');

					if ( current_user_can('manage_options') ) {
						echo wp_register( "<li>", "</li>", true);
					}
					if ($show_loggedout == 'on') {
						echo "<li>";
						echo wp_loginout( get_bloginfo('url'), true );
						echo "</li>";
					}

				}

				echo "</ul>";
						
			} else {
	
				// LOGGED OUT
	
				// Hook for more list items
				do_action('symposium_widget_summary_hook_loggedout');
	
				if ($show_loggedout == 'on' && $show_form == '') {
					echo wp_loginout( get_permalink(), true);
					echo ' (<a href="'.wp_lostpassword_url( get_bloginfo('url') ).'" title="'.__('Forgot Password?', 'wp-symposium').'">'.__('Forgot Password?', 'wp-symposium').'</a>)<br />';
					echo wp_register( "", "", true);
				}
	
				if ($show_loggedout == 'on' && $show_form == 'on') {
					if ($login_url != '') {
						wp_login_form(array('redirect' => $login_url ));
					} else {
						wp_login_form(get_permalink());
					}
					echo '<a href="'.wp_lostpassword_url( get_bloginfo('url') ).'" title="'.__('Forgot Password?', 'wp-symposium').'">'.__('Forgot Password?', 'wp-symposium').'</a><br />';
					echo wp_register("", "", true);
				}
			
			}
			
		echo "</div>";
		echo $after_widget;
		// End widget
		

	}
	
	// This updates the stored values
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		
		/* Strip tags (if needed) and update the widget settings. */
		$instance['wtitle'] = strip_tags( $new_instance['wtitle'] );
		$instance['show_loggedout'] = strip_tags( $new_instance['show_loggedout'] );
		$instance['show_form'] = strip_tags( $new_instance['show_form'] );
		$instance['login_url'] = strip_tags( $new_instance['login_url'] );
		return $instance;
	}
	
	// This is the admin form for the widget
	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'wtitle' => 'Welcome...', 'show_loggedout' => 'on', 'show_loggedout' => '' );
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<p>
			<label for="<?php echo $this->get_field_id( 'wtitle' ); ?>"><?php echo __('Widget Title', 'wp-symposium'); ?>:</label>
			<input id="<?php echo $this->get_field_id( 'wtitle' ); ?>" name="<?php echo $this->get_field_name( 'wtitle' ); ?>" value="<?php echo $instance['wtitle']; ?>" />
		<br /><br />
			<input type="checkbox" id="<?php echo $this->get_field_id( 'show_loggedout' ); ?>" name="<?php echo $this->get_field_name( 'show_loggedout' ); ?>"
			<?php if ($instance['show_loggedout'] == 'on') { echo " CHECKED"; } ?>
			/>
			<label for="<?php echo $this->get_field_id( 'show_loggedout' ); ?>"><?php echo __('Show Login/Logout links', 'wp-symposium'); ?></label>
		<br /><br />
			<input type="checkbox" id="<?php echo $this->get_field_id( 'show_form' ); ?>" name="<?php echo $this->get_field_name( 'show_form' ); ?>"
			<?php 
			$show_form = (isset($instance['show_form'])) ? $instance['show_form'] : '';
			if ($show_form == 'on') { echo " CHECKED"; } 
			?>
			/>
			<label for="<?php echo $this->get_field_id( 'show_form' ); ?>"><?php echo __('Show Login Form', 'wp-symposium'); ?></label>
		<br /><br />
			<label for="<?php echo $this->get_field_id( 'login_url' ); ?>"><?php echo __('Login URL (if using login form)', 'wp-symposium'); ?>:</label>
			<?php $login_url = (isset($instance['login_url'])) ? $instance['login_url'] : ''; ?>
			<input id="<?php echo $this->get_field_id( 'login_url' ); ?>" name="<?php echo $this->get_field_name( 'login_url' ); ?>" value="<?php echo $login_url; ?>" /><br />
			<?php echo __('Leave blank for current page (if the current page has values after # in the URL, they are not included as not passed to WordPress authentication).', 'wp-symposium'); ?>
		</p>
		<?php
	}

}

/** Forum: Needs answering ************************************************************************* **/
class Forumnoanswer_Widget extends WP_Widget {

	function Forumnoanswer_Widget() {
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'widget_forumrnoanswer', 'description' => 'Shows recent posts without an answer.' );
		
		/* Widget control settings. */
		$control_ops = array( 'id_base' => 'forumnoanswer-widget' );
		
		/* Create the widget. */
		$this->WP_Widget( 'forumnoanswer-widget', 'Symposium: '.__('Topics without an accepted answer', 'wp-symposium'), $widget_ops, $control_ops );
	}
	
	// This is shown on the page
	function widget( $args, $instance ) {
		global $wpdb, $current_user;
		wp_get_current_user();
	
		extract( $args );
		
		// Get options
		$wtitle = apply_filters('widget_title', $instance['wtitle'] );
		$preview = apply_filters('widget_preview', $instance['preview'] );
		$cat_id = apply_filters('widget_cat_id', $instance['cat_id'] );
		$cat_id_exclude = apply_filters('widget_cat_id_exclude', $instance['cat_id_exclude'] );
		$timescale = apply_filters('widget_timescale', $instance['timescale'] );
		$postcount = apply_filters('widget_postcount', $instance['postcount'] );
		$groups = apply_filters('widget_groups', $instance['groups'] );
		
		// Previous login
		if (is_user_logged_in()) {
			$previous_login = get_symposium_meta($current_user->ID, 'previous_login');
		}
		
		// Start widget
		echo $before_widget;
		echo $before_title . $wtitle . $after_title;
		
		// Content of widget
		$sql = "SELECT t.tid, t.topic_subject, t.topic_owner, t.topic_post, t.topic_category, t.topic_date, u.display_name, t.topic_parent, t.topic_group, t.topic_started, 
			(SELECT COUNT(*) FROM ".$wpdb->prefix."symposium_topics v WHERE v.topic_parent = t.tid) AS replies 
			FROM ".$wpdb->prefix."symposium_topics t 
			INNER JOIN ".$wpdb->base_prefix.'users'." u ON t.topic_owner = u.ID
			WHERE t.topic_parent = 0 
			  AND t.for_info != 'on' 
			  AND t.topic_approved = 'on' 
			  AND t.topic_started >= ( CURDATE() - INTERVAL ".$timescale." DAY ) 
			AND NOT EXISTS 
			  (SELECT tid from ".$wpdb->prefix."symposium_topics s 
			    WHERE s.topic_parent = t.tid AND s.topic_answer = 'on') ";
		if ($cat_id != '' && $cat_id > 0) {
			$sql .= "AND topic_category IN (".$cat_id.") ";
		}
		if ($cat_id_exclude != '' && $cat_id_exclude > 0) {
			$sql .= "AND topic_category NOT IN (".$cat_id_exclude.") ";
		}
		if ($groups != 'on') {
			$sql .= "AND topic_group = 0 ";
		}
		$sql .= "ORDER BY t.topic_started DESC LIMIT 0,".$postcount;
		$posts = $wpdb->get_results($sql); 
				
		// Get forum URL worked out
		$forum_url = symposium_get_url('forum');
		$forum_q = symposium_string_query($forum_url);
		
		if ($posts) {

			echo "<div id='symposium_latest_forum'>";
				
				foreach ($posts as $post)
				{
						if ($post->topic_group == 0 || (symposium_member_of($post->topic_group) == "yes") || ($wpdb->get_var($wpdb->prepare("SELECT content_private FROM ".$wpdb->prefix."symposium_groups WHERE gid = ".$post->topic_group)) != "on") ) {

							echo "<div class='symposium_latest_forum_row'>";		
								echo "<div class='symposium_latest_forum_row_avatar'>";
									echo get_avatar($post->topic_owner, 32);
								echo "</div>";
								echo "<div class='symposium_latest_forum_row_post'>";
									echo symposium_profile_link($post->topic_owner);
									if ($preview > 0) {
										$text = stripslashes($post->topic_subject);
										if ( strlen($text) > $preview ) { $text = substr($text, 0, $preview)."..."; } 
										if ($post->topic_group == 0) {
											$url = $forum_url;
											$q = $forum_q;
										} else {
											// Get group URL worked out
											$url = symposium_get_url('group');
											if (strpos($url, '?') !== FALSE) {
												$q = "&gid=".$post->topic_group."&";
											} else {
												$q = "?gid=".$post->topic_group."&";
											}
										}
										echo " ".__('started', 'wp-symposium')." <a href='".$url.$q."cid=".$post->topic_category."&show=".$post->tid."'>".$text."</a>";
									} else {
										echo "<br />";
									}
									echo " ".symposium_time_ago($post->topic_started).". ";
									if ($post->replies > 0) {
										echo $post->replies.' ';
										if ($post->replies != 1) {
											_e('replies', 'wp-symposium');
										} else {
											_e('reply', 'wp-symposium');
										}
										echo ".";
									}
									if (is_user_logged_in()) {
										if ($post->topic_started > $previous_login && $post->topic_owner != $current_user->ID) {
											echo " <img src='".WPS_IMAGES_URL."/new.gif' alt='New!' />";
										}
									}
									echo "<br />";
								echo "</div>";
							echo "</div>";
														
						}
				}

			echo "</div>";

		}
		// End content
		
		echo $after_widget;
		// End widget
	}
	
	// This updates the stored values
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		
		/* Strip tags (if needed) and update the widget settings. */
		$instance['wtitle'] = strip_tags( $new_instance['wtitle'] );
		$instance['preview'] = strip_tags( $new_instance['preview'] );
		$instance['cat_id'] = strip_tags( $new_instance['cat_id'] );
		$instance['cat_id_exclude'] = strip_tags( $new_instance['cat_id_exclude'] );
		$instance['timescale'] = strip_tags( $new_instance['timescale'] );
		$instance['postcount'] = strip_tags( $new_instance['postcount'] );
		$instance['groups'] = strip_tags( $new_instance['groups'] );
		return $instance;
	}
	
	// This is the admin form for the widget
	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'wtitle' => 'Topics without an accepted answer', 'cat_id' => '0', 'cat_id_exclude' => '0', 'preview' => '30', 'timescale' => 30, 'postcount' => 100, 'groups' => '' );
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'wtitle' ); ?>"><?php echo __('Widget Title', 'wp-symposium'); ?>:</label>
			<input id="<?php echo $this->get_field_id( 'wtitle' ); ?>" name="<?php echo $this->get_field_name( 'wtitle' ); ?>" value="<?php echo $instance['wtitle']; ?>" />
		<br /><br />
			<label for="<?php echo $this->get_field_id( 'cat_id' ); ?>"><?php echo __('<strong>Categories to include</strong><br />List IDs, comma separated. (0 for all)', 'wp-symposium'); ?>:</label>
			<input id="<?php echo $this->get_field_id( 'cat_id' ); ?>" name="<?php echo $this->get_field_name( 'cat_id' ); ?>" value="<?php echo $instance['cat_id']; ?>" />
		<br /><br />
			<label for="<?php echo $this->get_field_id( 'cat_id_exclude' ); ?>"><?php echo __('<strong>Categories to exclude</strong><br />List IDs, comma separated. (0 for none)', 'wp-symposium'); ?>:</label>
			<input id="<?php echo $this->get_field_id( 'cat_id_exclude' ); ?>" name="<?php echo $this->get_field_name( 'cat_id_exclude' ); ?>" value="<?php echo $instance['cat_id_exclude']; ?>" />
		<br /><br />
			<input type="checkbox" id="<?php echo $this->get_field_id( 'groups' ); ?>" name="<?php echo $this->get_field_name( 'groups' ); ?>"
			<?php
			$groups = (isset($instance['groups'])) ? $instance['groups'] : '';
			if ($groups == 'on') { echo " CHECKED"; } ?>
			/>
			<?php if (function_exists('symposium_groups')) { ?>
			<label for="<?php echo $this->get_field_id( 'groups' ); ?>"><?php echo __('Include groups', 'wp-symposium'); ?></label>
			<?php } ?>
		<br /><br />
			<label for="<?php echo $this->get_field_id( 'preview' ); ?>"><?php echo __('Max length of preview', 'wp-symposium'); ?>:</label>
			<input id="<?php echo $this->get_field_id( 'preview' ); ?>" name="<?php echo $this->get_field_name( 'preview' ); ?>" value="<?php echo $instance['preview']; ?>" style="width: 30px" />
		<br /><br />
			<label for="<?php echo $this->get_field_id( 'timescale' ); ?>"><?php echo __('Time period (days)', 'wp-symposium'); ?>:</label>
			<input id="<?php echo $this->get_field_id( 'timescale' ); ?>" name="<?php echo $this->get_field_name( 'timescale' ); ?>" value="<?php echo $instance['timescale']; ?>" style="width: 30px" />
		<br /><br />
			<label for="<?php echo $this->get_field_id( 'postcount' ); ?>"><?php echo __('Maximum number of posts', 'wp-symposium'); ?>:</label>
			<input id="<?php echo $this->get_field_id( 'postcount' ); ?>" name="<?php echo $this->get_field_name( 'postcount' ); ?>" value="<?php echo $instance['postcount']; ?>" style="width: 30px" />
		</p>
		<?php
	}

}

/** Forum: Top experts ************************************************************************* **/
class Forumexperts_Widget extends WP_Widget {

	function Forumexperts_Widget() {
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'widget_forumexperts', 'description' => 'Shows top members with answers accepted.' );
		
		/* Widget control settings. */
		$control_ops = array( 'id_base' => 'forumexperts-widget' );
		
		/* Create the widget. */
		$this->WP_Widget( 'forumexperts-widget', 'Symposium: '.__('Top Experts', 'wp-symposium'), $widget_ops, $control_ops );
	}
	
	// This is shown on the page
	function widget( $args, $instance ) {
		global $wpdb, $current_user;
		wp_get_current_user();
	
		extract( $args );
		
		// Get options
		$wtitle = apply_filters('widget_title', $instance['wtitle'] );
		$cat_id = apply_filters('widget_cat_id', $instance['cat_id'] );
		$cat_id_exclude = apply_filters('widget_cat_id_exclude', $instance['cat_id_exclude'] );
		$timescale = apply_filters('widget_timescale', $instance['timescale'] );
		$postcount = apply_filters('widget_postcount', $instance['postcount'] );
		$groups = apply_filters('widget_groups', $instance['groups'] );
		
		// Start widget
		echo $before_widget;
		echo $before_title . $wtitle . $after_title;
		
		// Content of widget
		$sql = "SELECT topic_owner, display_name, count(*) AS cnt FROM 
		 		(SELECT topic_owner FROM ".$wpdb->prefix."symposium_topics t 
				 WHERE t.topic_answer = 'on' AND t.topic_date >= ( CURDATE() - INTERVAL ".$timescale." DAY ) "; 
		if ($cat_id != '' && $cat_id > 0) {
			$sql .= "AND topic_category IN (".$cat_id.") ";
		}
		if ($cat_id_exclude != '' && $cat_id_exclude > 0) {
			$sql .= "AND topic_category NOT IN (".$cat_id_exclude.") ";
		}
		if ($groups != 'on') {
			$sql .= "AND topic_group = 0 ";
		}
		$sql .= "ORDER BY topic_owner) AS tmp ";
		$sql .= "LEFT JOIN ".$wpdb->prefix."users u ON topic_owner = u.ID ";
		$sql .= "GROUP BY topic_owner, display_name ";
		$sql .= "ORDER BY cnt DESC";
		$posts = $wpdb->get_results($sql); 
				
		$count = 1;
		
		if ($posts) {

			echo "<div id='symposium_latest_forum'>";
				
				foreach ($posts as $post)
				{
					echo '<div style="clear:both;">';
						echo '<div style="float:left;">';
							echo symposium_profile_link($post->topic_owner);
						echo '</div>';
						echo '<div style="float:right;">';
							echo $post->cnt.'<br />';
						echo '</div>';
					echo '</div>';
					
					if ($count++ == $postcount) {
						break;
					}
				}

			echo "</div>";

		}
		// End content
		
		echo $after_widget;
		// End widget
	}
	
	// This updates the stored values
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		
		/* Strip tags (if needed) and update the widget settings. */
		$instance['wtitle'] = strip_tags( $new_instance['wtitle'] );
		$instance['cat_id'] = strip_tags( $new_instance['cat_id'] );
		$instance['cat_id_exclude'] = strip_tags( $new_instance['cat_id_exclude'] );
		$instance['timescale'] = strip_tags( $new_instance['timescale'] );
		$instance['postcount'] = strip_tags( $new_instance['postcount'] );
		$instance['groups'] = strip_tags( $new_instance['groups'] );
		return $instance;
	}
	
	// This is the admin form for the widget
	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'wtitle' => 'Top Experts', 'cat_id' => '0', 'cat_id_exclude' => '0', 'timescale' => 30, 'postcount' => 10, 'groups' => '' );
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'wtitle' ); ?>"><?php echo __('Widget Title', 'wp-symposium'); ?>:</label>
			<input id="<?php echo $this->get_field_id( 'wtitle' ); ?>" name="<?php echo $this->get_field_name( 'wtitle' ); ?>" value="<?php echo $instance['wtitle']; ?>" />
		<br /><br />
			<label for="<?php echo $this->get_field_id( 'cat_id' ); ?>"><?php echo __('<strong>Categories to include</strong><br />List IDs, comma separated. (0 for all)', 'wp-symposium'); ?>:</label>
			<input id="<?php echo $this->get_field_id( 'cat_id' ); ?>" name="<?php echo $this->get_field_name( 'cat_id' ); ?>" value="<?php echo $instance['cat_id']; ?>" />
		<br /><br />
			<label for="<?php echo $this->get_field_id( 'cat_id_exclude' ); ?>"><?php echo __('<strong>Categories to exclude</strong><br />List IDs, comma separated. (0 for none)', 'wp-symposium'); ?>:</label>
			<input id="<?php echo $this->get_field_id( 'cat_id_exclude' ); ?>" name="<?php echo $this->get_field_name( 'cat_id_exclude' ); ?>" value="<?php echo $instance['cat_id_exclude']; ?>" />
		<br /><br />
			<input type="checkbox" id="<?php echo $this->get_field_id( 'groups' ); ?>" name="<?php echo $this->get_field_name( 'groups' ); ?>"
			<?php
			$groups = (isset($instance['groups'])) ? $instance['groups'] : '';
			if ($groups == 'on') { echo " CHECKED"; } ?>
			/>
			<?php if (function_exists('symposium_groups')) { ?>
			<label for="<?php echo $this->get_field_id( 'groups' ); ?>"><?php echo __('Include groups', 'wp-symposium'); ?></label>
			<?php } ?>
		<br /><br />
			<label for="<?php echo $this->get_field_id( 'timescale' ); ?>"><?php echo __('Time period (days)', 'wp-symposium'); ?>:</label>
			<input id="<?php echo $this->get_field_id( 'timescale' ); ?>" name="<?php echo $this->get_field_name( 'timescale' ); ?>" value="<?php echo $instance['timescale']; ?>" style="width: 30px" />
		<br /><br />
			<label for="<?php echo $this->get_field_id( 'postcount' ); ?>"><?php echo __('Maximum number of experts', 'wp-symposium'); ?>:</label>
			<input id="<?php echo $this->get_field_id( 'postcount' ); ?>" name="<?php echo $this->get_field_name( 'postcount' ); ?>" value="<?php echo $instance['postcount']; ?>" style="width: 30px" />
		</p>
		<?php
	}

}

?>
