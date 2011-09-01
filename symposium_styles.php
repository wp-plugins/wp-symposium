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

	echo "<!-- WP Symposium styles -->";
	echo "<style>";
	
	if ($styles) {

		$wp_width = $styles->wp_width;
		if ($wp_width == '') { $wp_width = '100pc'; }
		$wp_alignment = $styles->wp_alignment;

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
			
			echo ".symposium-wrapper, .symposium-wrapper input[type=text], .symposium-wrapper input[type=password], .symposium-wrapper textarea {".PHP_EOL;
			echo "	font-family: ".$fontfamily.";".PHP_EOL;
			echo "	font-size: ".$fontsize."px;".PHP_EOL;
			echo "	color: ".$text_color.";".PHP_EOL;
			echo "  text-shadow: none;".PHP_EOL;
			echo "}".PHP_EOL;
			
			echo ".symposium-wrapper * {".PHP_EOL;
			echo "	color: ".$text_color.";".PHP_EOL;
			echo "}".PHP_EOL;
			
			echo ".symposium-heading {".PHP_EOL;
			echo "	font-family: ".$headingsfamily.";".PHP_EOL;
			echo "	font-size: ".$headingssize.";".PHP_EOL;
			echo "}".PHP_EOL;

			echo ".symposium-wrapper {".PHP_EOL;
			echo "	background-color: ".$main_background.";".PHP_EOL;
			echo "}".PHP_EOL;

			echo ".symposium-button {".PHP_EOL;
			echo "	color: ".$text_color." !important;".PHP_EOL;
			echo "}".PHP_EOL;

			echo ".symposium-wrapper a:link, .symposium-wrapper a:visited, .symposium-wrapper a:active {".PHP_EOL;
			echo "	color: ".$link." !important;".PHP_EOL;
			if ($underline == "on") {
				echo "	text-decoration: underline !important;".PHP_EOL;
			} else {
				echo "	text-decoration: none !important;".PHP_EOL;
			}
			echo "}".PHP_EOL;
							
			
			echo ".symposium-wrapper a:hover {".PHP_EOL;
			echo "	color: ".$link_hover." !important;".PHP_EOL;
			echo "}".PHP_EOL;

			echo "body img, body input, .corners {".PHP_EOL;
			echo "	border-radius: ".$border_radius."px !important;".PHP_EOL;
			echo "	-moz-border-radius: ".$border_radius."px !important;".PHP_EOL;
			echo "}".PHP_EOL;
	
			echo ".symposium-wrapper .label {".PHP_EOL;
			echo "  color: ".$label." !important;".PHP_EOL;
			echo "}".PHP_EOL;

			echo ".symposium-wrapper #profile_right_column {".PHP_EOL;
			echo "	background-color: ".$main_background." !important;".PHP_EOL;
			echo "	border: ".$replies_border_size."px solid ".$primary_color." !important;".PHP_EOL;	
			echo "}".PHP_EOL;
			
			// Forum or Tables (layout)

			echo ".symposium-wrapper #symposium_table {".PHP_EOL;
			echo "	border: ".$table_border."px solid ".$primary_color.";".PHP_EOL;	
			echo "}".PHP_EOL;
		
			echo ".symposium-wrapper .table_header {".PHP_EOL;
			echo "	background-color: ".$categories_background.";".PHP_EOL;
			echo "  font-weight: bold;".PHP_EOL;
		 	echo "  border-radius:0px;".PHP_EOL;
			echo "  -moz-border-radius:0px;".PHP_EOL;
			echo "  border: 0px".PHP_EOL;
		 	echo "  border-top-left-radius:".($border_radius-5)."px;".PHP_EOL;
			echo "  -moz-border-radius-topleft:".($border_radius-5)."px;".PHP_EOL;
		 	echo "  border-top-right-radius:".($border_radius-5)."px;".PHP_EOL;
			echo "  -moz-border-radius-topright:".($border_radius-5)."px;".PHP_EOL;
			echo "}".PHP_EOL;

			echo ".symposium-wrapper .table_topic, .symposium-wrapper #profile_name, .symposium-wrapper .topic-post-header {".PHP_EOL;
			echo "	font-family: ".$headingsfamily." !important;".PHP_EOL;
			echo "	font-size: ".$headingssize." !important;".PHP_EOL;
			echo "}".PHP_EOL;

			echo ".symposium-wrapper .table_topic {".PHP_EOL;
			echo "	color: ".$categories_color.";".PHP_EOL;
			echo "}".PHP_EOL;

			echo ".symposium-wrapper .table_topic:hover {".PHP_EOL;
			echo "	background-color: ".$table_rollover." !important;;".PHP_EOL;
			echo "}".PHP_EOL;
			
			echo ".symposium-wrapper .row a, .symposium-wrapper .row_odd a {".PHP_EOL;
			if ($underline == "on") {
				echo "	text-decoration: underline;".PHP_EOL;
			} else {
				echo "	text-decoration: none;".PHP_EOL;
			}
			echo "}".PHP_EOL;
		
			echo ".symposium-wrapper .new-topic-subject-input, .symposium-wrapper .input-field {".PHP_EOL;
			echo "	font-family: ".$fontfamily.";".PHP_EOL;
			echo "	border: ".$replies_border_size."px solid ".$primary_color.";".PHP_EOL;	
			echo "}".PHP_EOL;
	
			echo ".symposium-wrapper .new-topic-subject-text, .symposium-wrapper .reply-topic-subject-text, .symposium-wrapper .reply-topic-text {".PHP_EOL;
			echo "	font-family: ".$fontfamily.";".PHP_EOL;
			echo "}".PHP_EOL;
		
			echo ".symposium-wrapper #reply-topic {".PHP_EOL;
			echo "	border: ".$replies_border_size."px solid ".$primary_color.";".PHP_EOL;	
			echo "}".PHP_EOL;
	
			echo ".symposium-wrapper #reply-topic-bottom textarea {".PHP_EOL;
			echo "	border: 1px solid ".$primary_color.";".PHP_EOL;			
			echo "}".PHP_EOL;
			
			echo ".symposium-wrapper #new-topic-link, .symposium-wrapper #reply-topic-link, .symposium-wrapper .symposium-button,  .symposium-button {".PHP_EOL;
			echo "	font-family: ".$fontfamily." !important;".PHP_EOL;
			echo "	font-size: ".$fontsize."px !important;".PHP_EOL;
			echo "	background-color: ".$bigbutton_background." !important;".PHP_EOL;
			echo "	color: ".$bigbutton_color." !important;".PHP_EOL;
			echo "}".PHP_EOL;
		
			echo ".symposium-wrapper #new-topic-link:hover, .symposium-wrapper #reply-topic-link:hover, .symposium-wrapper .symposium-button:hover,  .symposium-button:hover {".PHP_EOL;
			echo "	background-color: ".$table_rollover." !important;;".PHP_EOL;
			echo "}".PHP_EOL;
							
			echo ".symposium-wrapper .round_bottom_left {".PHP_EOL;
		 	echo "  border-bottom-left-radius:".($border_radius-5)."px;".PHP_EOL;
			echo "  -moz-border-radius-bottomleft:".($border_radius-5)."px;".PHP_EOL;
			echo "}".PHP_EOL;
			
			echo ".symposium-wrapper .round_bottom_right {".PHP_EOL;
		 	echo "  border-bottom-right-radius:".($border_radius-5)."px;".PHP_EOL;
			echo "  -moz-border-radius-bottomright:".($border_radius-5)."px;".PHP_EOL;
			echo "}".PHP_EOL;
			
			echo ".symposium-wrapper .categories_color {".PHP_EOL;
			echo "	color: ".$categories_color.";".PHP_EOL;
			echo "}";
			echo ".symposium-wrapper .categories_background {".PHP_EOL;
			echo "	background-color: ".$categories_background.";".PHP_EOL;
			echo "}".PHP_EOL;
			
			echo ".symposium-wrapper .row, .symposium-wrapper .reply_div {".PHP_EOL;
			echo "	background-color: ".$row_color." !important;".PHP_EOL;
			echo "}".PHP_EOL;

			echo ".symposium-wrapper .wall_reply, .symposium-wrapper .wall_reply_div, .symposium-wrapper .wall_reply_avatar, .symposium-wrapper a, ";
			echo ".symposium-wrapper .mailbox_message_subject, .symposium-wrapper .mailbox_message_from, .symposium-wrapper .mail_item_age, .symposium-wrapper .mailbox_message, ";
			echo ".symposium-wrapper .row_views ";
			echo " {".PHP_EOL;
			echo "	background-color: transparent !important;".PHP_EOL;
			echo "}".PHP_EOL;
				
				
			echo ".symposium-wrapper .row_odd {".PHP_EOL;
			echo "	background-color: ".$row_color_alt." !important;".PHP_EOL;
			echo "}".PHP_EOL;
		
			echo ".symposium-wrapper .row:hover, .symposium-wrapper .row_odd:hover {".PHP_EOL;
			echo "	background-color: ".$table_rollover." !important;".PHP_EOL;
			echo "}".PHP_EOL;
			
			echo ".symposium-wrapper .row_link, .symposium-wrapper .edit, .symposium-wrapper .delete {".PHP_EOL;
			echo "	font-size: ".$headingssize." !important;".PHP_EOL;
			echo "	color: ".$link." !important;".PHP_EOL;
			echo "}".PHP_EOL;
				
			echo ".symposium-wrapper .row_link:hover {".PHP_EOL;
			echo "	color: ".$link_hover." !important;".PHP_EOL;
			echo "}".PHP_EOL;
		
			echo ".symposium-wrapper #starting-post {".PHP_EOL;
			echo "	border: ".$replies_border_size."px solid ".$primary_color.";".PHP_EOL;
			echo "	background-color: ".$main_background.";".PHP_EOL;
			echo "}".PHP_EOL;
								
			echo ".symposium-wrapper #starting-post, .symposium-wrapper #child-posts {".PHP_EOL;
			echo "	border: ".$replies_border_size."px solid ".$primary_color.";".PHP_EOL;
			echo "	background-color: ".$row_color_alt.";".PHP_EOL;
			echo "}".PHP_EOL;
		
			echo ".symposium-wrapper .sep {".PHP_EOL;
			echo "	clear:both;".PHP_EOL;
			echo "	width:100%;".PHP_EOL;
			echo "	border-bottom: ".$row_border_size."px ".$row_border_style." ".$text_color_2.";".PHP_EOL;
			echo "}".PHP_EOL;
	
			// Alerts
			
			echo ".symposium-wrapper .alert {".PHP_EOL;
			echo "	clear:both;".PHP_EOL;
			echo "	padding:6px;".PHP_EOL;
			echo "	margin-bottom:15px;".PHP_EOL;
			echo "	border: 1px solid #666;".PHP_EOL;	
			echo "	background-color: #eee;".PHP_EOL;
			echo "	color: #000;".PHP_EOL;
			echo "}".PHP_EOL;
	
			echo ".symposium-wrapper .transparent {".PHP_EOL;
			echo '  -ms-filter: "progid: DXImageTransform.Microsoft.Alpha(Opacity='.($closed_opacity*100).')";'.PHP_EOL;
			echo "  filter: alpha(opacity=".($closed_opacity*100).");".PHP_EOL;
			echo "  -moz-opacity: ".$closed_opacity.";".PHP_EOL;
			echo "  -khtml-opacity: ".$closed_opacity.";".PHP_EOL;
			echo "  opacity: ".$closed_opacity.";".PHP_EOL;
			echo "}".PHP_EOL;
			
		}
					
		
	} else {
		
		echo "<p><strong>Failed to get styles</strong></p>";
		
	}

	// Apply advanced CSS (via WP Admin -> Symposium -> Styles -> CSS)
	
	if ($styles->css != '') {
		echo str_replace("[]", chr(13), stripslashes($styles->css));
	}

	echo "</style>";
	echo "<!-- End WP Symposium styles -->";

?>
