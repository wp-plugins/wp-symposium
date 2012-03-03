<?php
/*
Plugin Name: WP Symposium Widgets
Plugin URI: http://www.wpsymposium.com
Description: Widgets for use with WP Symposium.
Version: 12.03.03
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

		if (WPS_AJAX_WIDGETS == 'on') {
			// Parameters for AJAX
			echo '<div id="symposium_recent_Widget">';
			echo "<img src='".WPS_IMAGES_URL."/busy.gif' />";
			echo '<div id="symposium_recent_Widget_count" style="display:none">'.$symposium_recent_count.'</div>';
			echo '<div id="symposium_recent_Widget_desc" style="display:none">'.$symposium_recent_desc.'</div>';
			echo '<div id="symposium_recent_Widget_show_light" style="display:none">'.$symposium_recent_show_light.'</div>';
			echo '<div id="symposium_recent_Widget_show_mail" style="display:none">'.$symposium_recent_show_mail.'</div>';
			echo '</div>';	
			
		} else {
			do_recent_Widget($symposium_recent_count,$symposium_recent_desc,$symposium_recent_show_light,$symposium_recent_show_mail);
		}
		// End content
	
		echo $after_widget;
		// End widget

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

/** Profile: Recent Posts ************************************************************************* **/
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
				
		// Get options
		$wtitle = apply_filters('widget_title', $instance['wtitle'] );
		$postcount = apply_filters('widget_postcount', $instance['postcount'] );
		$preview = apply_filters('widget_preview', $instance['preview'] );
		$forum = apply_filters('widget_forum', $instance['forum'] );
		
		// Start widget
		echo $before_widget;
		echo $before_title . $wtitle . $after_title;

		if (WPS_AJAX_WIDGETS == 'on') {
			// Parameters for AJAX
			echo '<div id="symposium_Recentactivity_Widget">';
			echo "<img src='".WPS_IMAGES_URL."/busy.gif' />";
			echo '<div id="symposium_Recentactivity_Widget_postcount" style="display:none">'.$postcount.'</div>';
			echo '<div id="symposium_Recentactivity_Widget_preview" style="display:none">'.$preview.'</div>';
			echo '<div id="symposium_Recentactivity_Widget_forum" style="display:none">'.$forum.'</div>';
			echo '</div>';
		} else {
			do_Recentactivity_Widget($postcount,$preview,$forum);
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
		$instance['forum'] = strip_tags( $new_instance['forum'] );

		return $instance;
	}
	
	// This is the admin form for the widget
	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'wtitle' => 'What are members saying?', 'postcount' => '5', 'preview' => '60', 'forum' => 'on' );
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
		<br /><br />
			<label for="<?php echo $this->get_field_id( 'forum' ); ?>"><?php echo __('Include site activity', 'wp-symposium'); ?>:</label>
			<input type="checkbox" id="<?php echo $this->get_field_id( 'forum' ); ?>" name="<?php echo $this->get_field_name( 'forum' ); ?>"
			<?php if ($instance['forum'] == 'on') { echo " CHECKED"; } ?>
			/>
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

		if (WPS_AJAX_WIDGETS == 'on') {
			// Parameters for AJAX
			echo '<div id="symposium_members_Widget">';
			echo "<img src='".WPS_IMAGES_URL."/busy.gif' />";
			echo '<div id="symposium_members_Widget_count" style="display:none">'.$symposium_members_count.'</div>';
			echo '</div>';
		} else {
			do_members_Widget($symposium_members_count);
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

			if (WPS_AJAX_WIDGETS == 'on') {
				// Parameters for AJAX
				echo '<div id="symposium_friends_Widget">';
				echo "<img src='".WPS_IMAGES_URL."/busy.gif' />";
				echo '<div id="symposium_friends_count" style="display:none">'.$symposium_friends_count.'</div>';
				echo '<div id="symposium_friends_desc" style="display:none">'.$symposium_friends_desc.'</div>';
				echo '<div id="symposium_friends_mode" style="display:none">'.$symposium_friends_mode.'</div>';
				echo '<div id="symposium_friends_show_light" style="display:none">'.$symposium_friends_show_light.'</div>';
				echo '<div id="symposium_friends_show_mail" style="display:none">'.$symposium_friends_show_mail.'</div>';
				echo '</div>';	
			} else {
				do_symposium_friends_Widget($symposium_friends_count,$symposium_friends_desc,$symposium_friends_mode,$symposium_friends_show_light,$symposium_friends_show_mail);	
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
		
		// Start widget
		echo $before_widget;
		echo $before_title . $wtitle . $after_title;

		if (WPS_AJAX_WIDGETS == 'on') {
			// Parameters for AJAX
			echo '<div id="symposium_Forumrecentposts_Widget">';
			echo "<img src='".WPS_IMAGES_URL."/busy.gif' />";
			echo '<div id="symposium_Forumrecentposts_Widget_postcount" style="display:none">'.$postcount.'</div>';
			echo '<div id="symposium_Forumrecentposts_Widget_preview" style="display:none">'.$preview.'</div>';
			echo '<div id="symposium_Forumrecentposts_Widget_cat_id" style="display:none">'.$cat_id.'</div>';
			echo '<div id="symposium_Forumrecentposts_Widget_show_replies" style="display:none">'.$show_replies.'</div>';
			echo '</div>';
		} else {
			do_Forumrecentposts_Widget($postcount,$preview,$cat_id,$show_replies);			
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
		$login_url = apply_filters('widget_logi_url', $instance['login_url'] );
		$show_avatar = apply_filters('widget_show_avatar', $instance['show_avatar'] );
		
		// Start widget
		echo $before_widget;
		echo $before_title . $wtitle . $after_title;

		if (WPS_AJAX_WIDGETS == 'on') {
			// Parameters for AJAX
			echo '<div id="symposium_summary_Widget">';
			echo "<img src='".WPS_IMAGES_URL."/busy.gif' />";
			echo '<div id="symposium_summary_Widget_show_loggedout" style="display:none">'.$show_loggedout.'</div>';
			echo '<div id="symposium_summary_Widget_form" style="display:none">'.$show_form.'</div>';
			echo '<div id="symposium_summary_Widget_login_url" style="display:none">'.$login_url.'</div>';
			echo '<div id="symposium_summary_Widget_show_avatar" style="display:none">'.$show_avatar.'</div>';
			echo '</div>';
		} else {
			do_symposium_summary_Widget($show_loggedout,$show_form,$login_url,$show_avatar);			
		}

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
		$instance['show_avatar'] = strip_tags( $new_instance['show_avatar'] );
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
			<input type="checkbox" id="<?php echo $this->get_field_id( 'show_avatar' ); ?>" name="<?php echo $this->get_field_name( 'show_avatar' ); ?>"
			<?php if ($instance['show_avatar'] == 'on') { echo " CHECKED"; } ?>
			/>
			<label for="<?php echo $this->get_field_id( 'show_avatar' ); ?>"><?php echo __('Show avatar', 'wp-symposium'); ?></label>
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
		
		// Start widget
		echo $before_widget;
		echo $before_title . $wtitle . $after_title;

		if (WPS_AJAX_WIDGETS == 'on') {
			// Parameters for AJAX
			echo '<div id="symposium_Forumnoanswer_Widget">';
			echo "<img src='".WPS_IMAGES_URL."/busy.gif' />";
			echo '<div id="symposium_Forumnoanswer_Widget_preview" style="display:none">'.$preview.'</div>';
			echo '<div id="symposium_Forumnoanswer_Widget_cat_id" style="display:none">'.$cat_id.'</div>';
			echo '<div id="symposium_Forumnoanswer_Widget_cat_id_exclude" style="display:none">'.$cat_id_exclude.'</div>';
			echo '<div id="symposium_Forumnoanswer_Widget_timescale" style="display:none">'.$timescale.'</div>';
			echo '<div id="symposium_Forumnoanswer_Widget_postcount" style="display:none">'.$postcount.'</div>';
			echo '<div id="symposium_Forumnoanswer_Widget_groups" style="display:none">'.$groups.'</div>';
			echo '</div>';
		} else {
			do_Forumnoanswer_Widget($preview,$cat_id,$cat_id_exclude,$timescale,$postcount,$groups);			
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

		if (WPS_AJAX_WIDGETS == 'on') {
			// Parameters for AJAX
			echo '<div id="symposium_Forumexperts_Widget">';
			echo "<img src='".WPS_IMAGES_URL."/busy.gif' />";
			echo '<div id="symposium_Forumexperts_Widget_cat_id" style="display:none">'.$cat_id.'</div>';
			echo '<div id="symposium_Forumexperts_Widget_cat_id_exclude" style="display:none">'.$cat_id_exclude.'</div>';
			echo '<div id="symposium_Forumexperts_Widget_timescale" style="display:none">'.$timescale.'</div>';
			echo '<div id="symposium_Forumexperts_Widget_postcount" style="display:none">'.$postcount.'</div>';
			echo '<div id="symposium_Forumexperts_Widget_groups" style="display:none">'.$groups.'</div>';
			echo '</div>';
		} else {
		do_Forumexperts_Widget($cat_id,$cat_id_exclude,$timescale,$postcount,$groups);			
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
