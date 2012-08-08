<?php
/*
Plugin Name: WP Symposium Yes/No vote Widget
Plugin URI: http://www.wpsymposium.com
Description: Adds a WP Symposium Widget to display a Yes/No vote with chart (bar or pie). Requires a licence from http://www.jscharts.com to remove small JS Charts logo. Requires WP Symposium core plugin to be activated.
Version: 12.09
Author: WP Symposium
Author URI: http://www.wpsymposium.com
License: GPL3
*/

define('WPS_YESNO_VER', '12.09');
if(!defined('WPS_PLUS')) define('WPS_PLUS', '12.09');

/*  Copyright 2010,2011,2012  Simon Goodchild  (info@wpsymposium.com)

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

add_action( 'widgets_init', 'symposium_load_widget_yesno_vote' );

function symposium_load_widget_yesno_vote() {
	register_widget( 'Symposium_vote_Widget' );
}


/** Symposium: Vote ************************************************************************* **/
class Symposium_vote_Widget extends WP_Widget {

	function Symposium_vote_Widget() {
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'widget_symposium_vote', 'description' => 'Allows members to vote on a YES/NO question.' );
		
		/* Widget control settings. */
		$control_ops = array( 'id_base' => 'symposium_vote-widget' );
		
		/* Create the widget. */
		$this->WP_Widget( 'symposium_vote-widget', 'Symposium: '.__('Vote', 'wp-symposium'), $widget_ops, $control_ops );
	}
	
	// This is shown on the page
	function widget( $args, $instance ) {
		
		global $wpdb, $current_user;
		wp_get_current_user();
			
		extract( $args );

		// Get options
		$symposium_vote_question = apply_filters('widget_symposium_vote_question', $instance['symposium_vote_question'] );
		$symposium_vote_forum = apply_filters('widget_symposium_vote_forum', $instance['symposium_vote_forum'] );
		$symposium_vote_counts = apply_filters('widget_symposium_vote_counts', $instance['symposium_vote_counts'] );
		$symposium_vote_type = apply_filters('widget_symposium_vote_type', $instance['symposium_vote_type'] );
		$symposium_vote_key = apply_filters('widget_symposium_vote_key', $instance['symposium_vote_key'] );
		
		// Start widget
		echo $before_widget;
		echo $before_title . $symposium_vote_question . $after_title;
		
		// Content of widget

		echo '<div id="symposium_chartcontainer">Chart of results</div>';
		echo '<div id="symposium_chart_type" style="display:none">'.$symposium_vote_type.'</div>';
		echo '<div id="symposium_chart_counts" style="display:none">'.$symposium_vote_counts.'</div>';
		echo '<div id="symposium_chart_key" style="display:none">'.$symposium_vote_key.'</div>';

		// Store values
		$symposium_vote_yes = get_option("symposium_vote_yes");
		if ($symposium_vote_yes != false) {
			$symposium_vote_yes = (int) $symposium_vote_yes;
		} else {
		    update_option("symposium_vote_yes", 0);	    	   	
			$symposium_vote_yes = 0;
		}
		$symposium_vote_no = get_option("symposium_vote_no");
		if ($symposium_vote_no != false) {
			$symposium_vote_no = (int) $symposium_vote_no;
		} else {
		    update_option("symposium_vote_no", 0);	    	   	
			$symposium_vote_no = 0;
		}

		echo '<div id="symposium_chart_yes" style="display:none">'.$symposium_vote_yes.'</div>';
		echo '<div id="symposium_chart_no" style="display:none">'.$symposium_vote_no.'</div>';
			
		if (is_user_logged_in()) {
			
			$voted = get_symposium_meta($current_user->ID, 'widget_voted');
			if ($voted == "on") {
				
				echo "<p>";
				echo __('Thank you for voting', 'wp-symposium').".";
				if ($symposium_vote_forum != '') {
					echo "<br /><a href='".$symposium_vote_forum."'>".__('Discuss this on the forum', 'wp-symposium')."...</a>";
				}
				echo "</p>";

			} else {
			
			
				echo "<div id='symposium_vote_forum'>";
					echo "<p>".__('Your vote', 'wp-symposium').": ";
					echo "<a href='javascript:void(0)' title='yes' class='symposium_answer' value='".__("Yes", "wp-symposium")."'>".__("Yes", "wp-symposium")."</a> ".__('or', 'wp-symposium')." ";
					echo "<a href='javascript:void(0)' title='no' class='symposium_answer' value='".__("No", "wp-symposium")."'>".__("No", "wp-symposium")."</a>";
					if ($symposium_vote_forum != '') {
						echo "<br /><a href='".$symposium_vote_forum."'>".__('Discuss this on the forum', 'wp-symposium')."...</a>";
					}
					echo "</p>";
				echo "</div>";
				
				echo "<div id='symposium_vote_thankyou'>";
					echo "<p>".__("Thank you for voting, refresh the page for latest results", "wp-symposium");
					if ($symposium_vote_forum != '') {
						echo "<br /><a href='".$symposium_vote_forum."'>".__('Discuss this on the forum', 'wp-symposium')."...</a>";
					}
					echo "</p>";
				echo "</div>";
		
			}
			
		} else {
			
			echo "<p>".__("Log in to vote...", "wp-symposium")."</p>";
			
		}
				
		// End content
		
		echo $after_widget;
		// End widget
	}
	
	// This updates the stored values
	function update( $new_instance, $old_instance ) {

		global $wpdb;

		$instance = $old_instance;

		// Reset
		if (strip_tags( $new_instance['symposium_reset_votes'] ) == 'on' ) {
			update_option( "symposium_vote_yes", 0 );
			update_option( "symposium_vote_no", 0 );
			$users = $wpdb->get_results($wpdb->prepare("SELECT ID FROM ".$wpdb->base_prefix."users"));
			foreach ($users as $user) {
				update_symposium_meta($user->ID, 'widget_voted', '');
			}
		}
		
		/* Strip tags (if needed) and update the widget settings. */
		$instance['symposium_vote_question'] = strip_tags( $new_instance['symposium_vote_question'] );
		$instance['symposium_vote_forum'] = strip_tags( $new_instance['symposium_vote_forum'] );
		$instance['symposium_vote_counts'] = strip_tags( $new_instance['symposium_vote_counts'] );
		$instance['symposium_vote_type'] = strip_tags( $new_instance['symposium_vote_type'] );
		$instance['symposium_vote_key'] = strip_tags( $new_instance['symposium_vote_key'] );
		return $instance;
	}
	
	// This is the admin form for the widget
	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'symposium_vote_question' => __('A yes/no question...', 'wp-symposium'), 'symposium_vote_forum' => '', 'symposium_vote_counts' => '', 'symposium_vote_type' => 'bar', 'symposium_vote_key' => '' );
		$instance = wp_parse_args( (array) $instance, $defaults ); 

		$symposium_vote_yes = get_option("symposium_vote_yes");
		$symposium_vote_no = get_option("symposium_vote_no");

		echo "<p><span style='font-weight:bold'>".__('Results so far', 'wp-symposium')."</span><br />";
		echo __("Yes", "wp-symposium").": ".$symposium_vote_yes."<br />";
		echo __("No", "wp-symposium").": ".$symposium_vote_no."</p>";
		?>		
		<p>
			<label 	for="<?php echo $this->get_field_id( 'symposium_vote_question' ); ?>"><?php echo __('Question', 'wp-symposium'); ?>:<br /></label>
			<input 	id="<?php echo $this->get_field_id( 'symposium_vote_question' ); ?>" 
					name="<?php echo $this->get_field_name( 'symposium_vote_question' ); ?>" 
					value="<?php echo $instance['symposium_vote_question']; ?>" />
		<br /><br />
			<label 	for="<?php echo $this->get_field_id( 'symposium_vote_forum' ); ?>"><?php echo __('Forum Link', 'wp-symposium'); ?>:<br /></label>
			<input 	id="<?php echo $this->get_field_id( 'symposium_vote_forum' ); ?>" 
					name="<?php echo $this->get_field_name( 'symposium_vote_forum' ); ?>" 
					value="<?php echo $instance['symposium_vote_forum']; ?>" />
		<br /><br />
			<label for="<?php echo $this->get_field_id( 'symposium_vote_counts' ); ?>"><?php echo __('Show values', 'wp-symposium'); ?>:</label>
			<input type="checkbox" id="<?php echo $this->get_field_id( 'symposium_vote_counts' ); ?>" name="<?php echo $this->get_field_name( 'symposium_vote_counts' ); ?>"
			<?php if ($instance['symposium_vote_counts'] == 'on') { echo " CHECKED"; } ?>
			/>
			<br /><em>(if not, percentages shown)</em>
		<br /><br />
			<label for="<?php echo $this->get_field_id( 'symposium_vote_type' ); ?>"><?php echo __('Chart type', 'wp-symposium'); ?>:</label>
			<select type="checkbox" id="<?php echo $this->get_field_id( 'symposium_vote_type' ); ?>" name="<?php echo $this->get_field_name( 'symposium_vote_type' ); ?>">
				<option value="pie" <?php if ($instance['symposium_vote_type'] == 'pie') { echo " SELECTED"; } ?> >Pie</option>
				<option value="bar" <?php if ($instance['symposium_vote_type'] == 'bar') { echo " SELECTED"; } ?> >Bar</option>
			</select>
		<br /><br />
			<label for="<?php echo $this->get_field_id( 'symposium_reset_votes' ); ?>"><?php echo __('Reset votes?', 'wp-symposium'); ?>:</label>
			<input type="checkbox" id="<?php echo $this->get_field_id( 'symposium_reset_votes' ); ?>" name="<?php echo $this->get_field_name( 'symposium_reset_votes' ); ?>"
			 />
		<br /><br />
			<label 	for="<?php echo $this->get_field_id( 'symposium_vote_key' ); ?>"><?php echo __('Domain key', 'wp-symposium'); ?>:<br /></label>
			<input 	id="<?php echo $this->get_field_id( 'symposium_vote_key' ); ?>" 
					name="<?php echo $this->get_field_name( 'symposium_vote_key' ); ?>" 
					value="<?php echo $instance['symposium_vote_key']; ?>" />
			<br /><a href="http://www.wpswiki.com/index.php?title=Yes/No_Vote" target="_blank"><?php echo __('What is this?', 'wp-symposium'); ?></a>
		</p>
		<?php
	}

}

?>
