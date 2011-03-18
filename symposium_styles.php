<?php
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

		// Set dynamic styles
		
		global $wpdb;
		
		$styles = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix . 'symposium_config'));
		
		if ($styles) {

			$wp_width = $styles->wp_width;
			if ($wp_width == '') { $wp_width = '100pc'; }
			$wp_alignment = $styles->wp_alignment;

			echo "<!-- WP Symposium styles -->";
			
			echo "<style>";

				echo ".symposium-wrapper {";
				if ($wp_alignment == 'Center') {
					echo "margin: 0 auto;";
				}
				if ($wp_alignment == 'Left' || $wp_alignment == 'Right') {
					echo "clear: both";
					echo "margin: 0;";
					echo "float: ".strtolower($wp_alignment).";";
				}
				echo "  width: ".str_replace('pc', '%', $wp_width).";";
				echo "}";

			
				if ($styles->use_styles == "on") {
				
					$border_radius = $styles->border_radius;
					$bigbutton_background = $styles->bigbutton_background;
					$bigbutton_color = $styles->bigbutton_color;
					$bigbutton_background_hover = $styles->bigbutton_background_hover;
					$bigbutton_color_hover = $styles->bigbutton_color_hover;
					$primary_color = $styles->bg_color_1;
					$row_color = $styles->bg_color_2;
					$row_color_alt = $styles->bg_color_3;
					$text_color = $styles->text_color;
					$text_color_2 = $styles->text_color_2;
					$link = $styles->link;
					$underline = $styles->underline;
					$link_hover = $styles->link_hover;
					$table_rollover = $styles->table_rollover;
					$table_border = $styles->table_border;
					$replies_border_size = $styles->replies_border_size;
					$row_border_style = $styles->row_border_style;
					$row_border_size = $styles->row_border_size;
					$label = $styles->label;
					$categories_background = $styles->categories_background;
					$categories_color = $styles->categories_color;
					$main_background = $styles->main_background;
					$closed_opacity = $styles->closed_opacity;
					$fontfamily = $styles->fontfamily;
					$fontsize = $styles->fontsize;
					$headingsfamily = $styles->headingsfamily;
					$headingssize = $styles->headingssize;
									
					
					echo ".symposium-wrapper,
					.symposium-wrapper .symposium-button, .symposium-button, 
					.symposium-wrapper input, .symposium-wrapper textarea,
					.symposium-wrapper p, .symposium-wrapper li, .symposium-wrapper td, .symposium-wrapper div,
					#symposium-search, #symposium-activity-list, #symposium-fav-list {
					";
					echo "	font-family: ".$fontfamily.";";
					echo "	font-size: ".$fontsize."px;";
					echo "	color: ".$text_color.";";
					echo "  text-shadow: none; ";
					echo "}";
					
					echo ".symposium-wrapper a:link, .symposium-wrapper a:visited, .symposium-wrapper a:active {";
					echo "	color: ".$link.";";
					if ($underline == "on") {
						echo "	text-decoration: underline;";
					} else {
						echo "	text-decoration: none;";
					}
					echo "}";

					echo ".symposium-wrapper a:hover {";
					echo "	color: ".$link_hover.";";
					echo "}";
	
					echo "body img, body input, .corners {";
					echo "	border-radius: ".$border_radius."px !important;";
					echo "	-moz-border-radius: ".$border_radius."px !important;";
					echo "}";
			
					echo ".symposium-wrapper .label {";
					echo "  color: ".$label.";";
					echo "}";
	
					echo ".symposium-wrapper #profile_right_column {";
					echo "	background-color: ".$main_background.";";
					echo "	border: ".$replies_border_size."px solid ".$primary_color.";";	
					echo "}";
									
					// Forum
	
					echo ".symposium-wrapper #symposium_table {";
					echo "	border: ".$table_border."px solid ".$primary_color.";";	
					echo "}";
				
					echo ".symposium-wrapper .table_header {";
					echo "	background-color: ".$categories_background.";";
					echo "  font-weight: bold;";
				 	echo "  border-radius:0px;";
					echo "  -moz-border-radius:0px;";
					echo "  border: 0px";
				 	echo "  border-top-left-radius:".($border_radius-5)."px;";
					echo "  -moz-border-radius-topleft:".($border_radius-5)."px;";
				 	echo "  border-top-right-radius:".($border_radius-5)."px;";
					echo "  -moz-border-radius-topright:".($border_radius-5)."px;";
					echo "}";
	
					echo ".symposium-wrapper .table_topic, .symposium-wrapper #profile_name {";
					echo "	font-family: ".$headingsfamily.";";
					echo "	font-size: ".$headingssize.";";
					echo "}";
	
					echo ".symposium-wrapper .table_topic {";
					echo "	color: ".$categories_color.";";
					echo "}";
					
					echo ".symposium-wrapper .row a, .symposium-wrapper .row_odd a {";
					if ($underline == "on") {
						echo "	text-decoration: underline;";
					} else {
						echo "	text-decoration: none;";
					}
					echo "}";
				
					echo ".symposium-wrapper .new-topic-subject-input, .symposium-wrapper .input-field {";
					echo "	font-family: ".$fontfamily.";";
					echo "	border: ".$replies_border_size."px solid ".$primary_color.";";	
					echo "}";
			
					echo ".symposium-wrapper .new-topic-subject-text, .symposium-wrapper .reply-topic-subject-text, .symposium-wrapper .reply-topic-text {";
					echo "	font-family: ".$fontfamily.";";
					echo "}";
				
					echo ".symposium-wrapper #new-topic, .symposium-wrapper #reply-topic, .symposium-wrapper #edit-topic-div, .symposium-wrapper #fav-list {";
					echo "	background-color: ".$main_background.";";
					echo "	border: ".$replies_border_size."px solid ".$primary_color.";";	
					echo "}";
			
					echo ".symposium-wrapper #reply-topic-bottom textarea {";
					echo "	border: 1px solid ".$primary_color.";";			
					echo "}";
					
					echo ".symposium-wrapper #new-topic-link, .symposium-wrapper #reply-topic-link, .symposium-wrapper .symposium-button,  .symposium-button {";
					echo "	font-family: ".$fontfamily." !important;";
					echo "	font-size: ".$fontsize."px !important;";
					echo "	background-color: ".$bigbutton_background." !important;";
					echo "	color: ".$bigbutton_color." !important;";
					echo "}";
				
					echo ".symposium-wrapper #new-topic-link:hover, .symposium-wrapper #reply-topic-link:hover, .symposium-wrapper .symposium-button:hover,  .symposium-button:hover {";
					echo "	background-color: ".$bigbutton_background_hover." !important;";
					echo "	color: ".$bigbutton_color_hover." !important;";
					echo "}";
									
					echo ".symposium-wrapper .round_bottom_left {";
				 	echo "  border-bottom-left-radius:".($border_radius-5)."px;";
					echo "  -moz-border-radius-bottomleft:".($border_radius-5)."px;";
					echo "}";
					
					echo ".symposium-wrapper .round_bottom_right {";
				 	echo "  border-bottom-right-radius:".($border_radius-5)."px;";
					echo "  -moz-border-radius-bottomright:".($border_radius-5)."px;";
					echo "}";
					
					echo ".symposium-wrapper .categories_color {";
					echo "	color: ".$categories_color.";";
					echo "}";
					echo ".symposium-wrapper .categories_background {";
					echo "	background-color: ".$categories_background.";";
					echo "}";
					
					echo ".symposium-wrapper .row {";
					echo "	background-color: ".$row_color.";";
					echo "}";
						
					echo ".symposium-wrapper .row_odd {";
					echo "	background-color: ".$row_color_alt.";";
					echo "}";
				
					echo ".symposium-wrapper .row:hover, .symposium-wrapper .row_odd:hover {";
					echo "	background-color: ".$table_rollover.";";
					echo "}";
					
					echo ".symposium-wrapper .row_link, .symposium-wrapper .edit, .symposium-wrapper .delete {";
					echo "	font-size: ".$headingssize.";";
					echo "	color: ".$link.";";
					echo "}";
						
					echo ".symposium-wrapper .row_link:hover {";
					echo "	color: ".$link_hover.";";
					echo "}";
				
					echo ".symposium-wrapper #starting-post {";
					echo "	border: ".$replies_border_size."px solid ".$primary_color.";";
					echo "	background-color: ".$main_background.";";
					echo "}";
					
					echo ".symposium-wrapper .started-by {";
					echo "	color: ".$text_color_2.";";
					echo "}";
							
					echo ".symposium-wrapper #child-posts {";
					echo "	border: ".$replies_border_size."px solid ".$primary_color.";";
					echo "	background-color: ".$row_color_alt.";";
					echo "}";
				
					echo ".symposium-wrapper .sep {";
					echo "	clear:both;";
					echo "	width:100%;";
					echo "	border-bottom: ".$row_border_size."px ".$row_border_style." ".$text_color_2.";";
					echo "}";
			
					echo ".symposium-wrapper .alert {";
					echo "	clear:both;";
					echo "	padding:6px;";
					echo "	margin-bottom:15px;";
					echo "	border: 1px solid #666;";	
					echo "	background-color: #eee;";
					echo "	color: #000;";
					echo "}";
			
					echo ".symposium-wrapper .transparent {";
					echo '  -ms-filter: "progid: DXImageTransform.Microsoft.Alpha(Opacity='.($closed_opacity*100).')";';
					echo "  filter: alpha(opacity=".($closed_opacity*100).");";
					echo "  -moz-opacity: ".$closed_opacity.";";
					echo "  -khtml-opacity: ".$closed_opacity.";";
					echo "  opacity: ".$closed_opacity.";";
					echo "}";
					
					// Mail
					
					echo ".symposium-wrapper #mail-main, .symposium-wrapper #mail_tabs .nav-tab-active, .symposium-wrapper #mail_tabs .nav-tab-inactive {";
					echo "	border: ".$table_border."px solid ".$primary_color.";";	
					echo "	background-color: ".$main_background.";";
					echo "}";
			
					echo ".symposium-wrapper #mail_tabs {";
					echo "	top: ".$table_border."px;";	
					echo "}";
			
					echo ".symposium-wrapper .mail_tab {";
				 	echo "  border-top-left-radius:".$border_radius."px;";
					echo "  -moz-border-radius-topleft:".$border_radius."px;";
				 	echo "  border-top-right-radius:".$border_radius."px;";
					echo "  -moz-border-radius-topright:".$border_radius."px;";
					echo "}";
			
					echo ".symposium-wrapper #mail_tabs .nav-tab-active {";
					echo "	background-color: ".$main_background.";";
					echo "	border-bottom: ".$table_border."px solid ".$main_background.";";	
					echo "}";
					echo ".symposium-wrapper #mail_tabs .nav-tab-active a {";
					echo "	color: ".$text_color.";";
					echo "}";
			
					echo ".symposium-wrapper #mail_tabs .nav-tab-inactive {";
					echo "	background-color: ".$categories_background.";";
					echo "	border-bottom: ".$table_border."px solid ".$primary_color.";";	
					echo "}";
					echo ".symposium-wrapper #mail_tabs .nav-tab-inactive a {";
					echo "	color: ".$categories_color.";";
					echo "}";
			
					echo ".symposium-wrapper .mail_item:hover {";
					echo "	background-color: ".$table_rollover.";";
					echo "}";
					
				}
							
			echo "</style>";
			
		} else {
			
			echo "<p><strong>Failed to get styles</strong></p>";
			
		}
?>
