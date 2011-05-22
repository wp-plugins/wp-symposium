<?php
/*
Plugin Name: WP Symposium Widgets
Plugin URI: http://www.wpsymposium.com
Description: Widgets for use with WP Symposium.
Version: 0.53.5
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
	register_widget( 'Symposium_members_Widget' );
	register_widget( 'Symposium_vote_Widget' );
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
		$posts = $wpdb->get_results("SELECT cid, author_uid, comment, comment_timestamp, display_name FROM ".$wpdb->prefix."symposium_comments c INNER JOIN ".$wpdb->base_prefix."users u ON c.author_uid = u.ID WHERE is_group != 'on' AND comment_parent = 0 AND author_uid = subject_uid ORDER BY cid DESC LIMIT 0,50");
			
		if ($posts) {

			echo "<div id='symposium_recent_activity'>";
				
				foreach ($posts as $post)
				{
					if ($shown_count < $postcount) {
						if (strpos($shown_uid, $post->author_uid.",") === FALSE) { 

							$share = $wpdb->get_var($wpdb->prepare("SELECT share FROM ".$wpdb->prefix."symposium_usermeta WHERE uid = ".$post->author_uid));
							$is_friend = symposium_friend_of($post->author_uid);

							if ( (strtolower($share) == 'everyone') || (strtolower($share) == 'friends only' && $is_friend) ) {

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
		
		// Start widget
		echo $before_widget;
		echo $before_title . $symposium_vote_question . $after_title;
		
		// Content of widget

		echo '<div id="symposium_chartcontainer">Chart of results</div>';
			
		if (is_user_logged_in()) {
			
			$voted = $wpdb->get_var($wpdb->prepare("SELECT widget_voted FROM ".$wpdb->base_prefix."symposium_usermeta WHERE uid = ".$current_user->ID));
			if ($voted == "on") {
				
				echo "<p>";
				echo __('Thank you for voting').".";
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
		update_option( "symposium_vote_yes", 0 );
		update_option( "symposium_vote_no", 0 );
		$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->base_prefix."symposium_usermeta SET widget_voted = ''") );
		
		/* Strip tags (if needed) and update the widget settings. */
		$instance['symposium_vote_question'] = strip_tags( $new_instance['symposium_vote_question'] );
		$instance['symposium_vote_forum'] = strip_tags( $new_instance['symposium_vote_forum'] );
		return $instance;
	}
	
	// This is the admin form for the widget
	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'symposium_vote_question' => __('A yes/no question...', 'wp-symposium'), 'symposium_vote_forum' => '' );
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
			<?php _e('(saving clears results)', 'wp-symposium'); ?>
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
		
		// Content of widget
		$sql = "SELECT tid, topic_subject, topic_owner, topic_post, topic_category, topic_date, display_name, topic_parent, topic_group FROM ".$wpdb->prefix.'symposium_topics'." t INNER JOIN ".$wpdb->base_prefix.'users'." u ON t.topic_owner = u.ID WHERE tid = tid ";
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
		if (strpos($forum_url, '?') !== FALSE) {
			$forum_q = "&";
		} else {
			$forum_q = "?";
		}
		
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
											echo " ".__('replied', 'wp-symposium')." <a href='".$forum_url.symposium_permalink($post->topic_parent, "topic")."?cid=".$post->topic_category."&show=".$post->topic_parent."'>".$text."</a>";
										} else {
											echo "<br />";
										}
										echo " ".symposium_time_ago($post->topic_date).".<br>";
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
										} else {
											echo "<br />";
										}
										echo " ".symposium_time_ago($post->topic_date).".<br>";
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


// JS Chart
wp_register_script('symposium_jsChart', WP_PLUGIN_URL . '/wp-symposium/js/jscharts.js');
wp_enqueue_script('symposium_jsChart');
?>