<?php
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

		// Set dynamic styles
	
	global $wpdb;
	
	echo "<!-- WP Symposium styles -->";
	echo "<style>";
	
	echo '.mceStatusbar { display:none !important; }';

	$wp_width = get_option('symposium_wp_width');
	if ($wp_width == '') { $wp_width = '100pc'; }
	$wp_alignment = get_option('symposium_wp_alignment');

	echo ".symposium-wrapper {";
	if ($wp_alignment == 'Center') {
		echo "margin: 0 auto;";
	}
	if ($wp_alignment == 'Left' || $wp_alignment == 'Right') {
		echo "clear: both;";
		echo "margin: 0;";
		echo "float: ".strtolower($wp_alignment).";";
	}
	echo "  width: ".str_replace('pc', '%', $wp_width).";";
	echo "}";

	if (get_option('symposium_use_styles') == "on") {
	
		$border_radius = get_option('symposium_border_radius');
		$bigbutton_background = get_option('symposium_bigbutton_background');
		$bigbutton_color = get_option('symposium_bigbutton_color');
		$bigbutton_background_hover = get_option('symposium_bigbutton_background_hover');
		$bigbutton_color_hover = get_option('symposium_bigbutton_color_hover');
		$primary_color = get_option('symposium_bg_color_1');
		$row_color = get_option('symposium_bg_color_2');
		$row_color_alt = get_option('symposium_bg_color_3');
		$text_color = get_option('symposium_text_color');
		$text_color_2 = get_option('symposium_text_color_2');
		$link = get_option('symposium_link');
		$underline = get_option('symposium_underline');
		$link_hover = get_option('symposium_link_hover');
		$table_rollover = get_option('symposium_table_rollover');
		$table_border = get_option('symposium_table_border');
		$replies_border_size = get_option('symposium_replies_border_size');
		$row_border_style = get_option('symposium_row_border_style');
		$row_border_size = get_option('symposium_row_border_size');
		$label = get_option('symposium_label');
		$categories_background = get_option('symposium_categories_background');
		$categories_color = get_option('symposium_categories_color');
		$main_background = get_option('symposium_main_background');
		$closed_opacity = get_option('symposium_closed_opacity');
		$fontfamily = stripslashes(get_option('symposium_fontfamily'));
		$fontsize = get_option('symposium_fontsize');
		$headingsfamily = stripslashes(get_option('symposium_headingsfamily'));
		$headingssize = get_option('symposium_headingssize');
		
		$style = "";
		
		$style .= ".symposium-wrapper, 
					.symposium-wrapper .symposium-button, 
					.symposium-button, .symposium-wrapper p, 
					.symposium-wrapper li, 
					.symposium-wrapper td, 
					.symposium-wrapper div,
					.symposium-wrapper input[type=text], 
					.symposium-wrapper input[type=password], 
					.symposium-wrapper textarea, 
					.popup, 
					.ui-widget,
					.ui-dialog,
					 .mail_recipient_list_option
				    {".PHP_EOL;
		$style .= "	font-size: ".$fontsize."px;".PHP_EOL;
		$style .= "	color: ".$text_color.";".PHP_EOL;
		$style .= " text-shadow: none;".PHP_EOL;
		$style .= "}".PHP_EOL;
		
		$style .= ".symposium-wrapper div, .widget-area  {".PHP_EOL;
		$style .= "	font-family: ".$fontfamily.";".PHP_EOL;
		$style .= "	color: ".$text_color.";".PHP_EOL;
		$style .= "}".PHP_EOL;
		$style .= "#profile_menu div, #profile_header_panel div, #profile_body_wrapper div, .child-reply-post p, .topic-post-post p {".PHP_EOL;
		$style .= "	font-family: ".$fontfamily." !important;".PHP_EOL;
		$style .= "	color: ".$text_color."!important;".PHP_EOL;
		$style .= "}".PHP_EOL;
		
		$style .= ".symposium-heading {".PHP_EOL;
		$style .= "	font-family: ".$headingsfamily.";".PHP_EOL;
		$style .= "	font-size: ".$headingssize.";".PHP_EOL;
		$style .= "}".PHP_EOL;

		$style .= ".symposium-wrapper, #mail_recipient_list, .mail_recipient_list_option {".PHP_EOL;
		$style .= "	background-color: ".$main_background.";".PHP_EOL;
		$style .= "}".PHP_EOL;

		$style .= ".symposium-button {".PHP_EOL;
		$style .= "	color: ".$text_color." !important;".PHP_EOL;
		$style .= "}".PHP_EOL;

		$style .= ".symposium-wrapper a:link, .symposium-wrapper a:visited, .symposium-wrapper a:active,
					.widget-area a:link, .widget-area a:visited, .widget-area a:active
					{".PHP_EOL;
		$style .= "	color: ".$link." !important;".PHP_EOL;
		$style .= "	font-weight: normal !important;".PHP_EOL;
		if ($underline == "on") {
			$style .= "	text-decoration: underline !important;".PHP_EOL;
		} else {
			$style .= "	text-decoration: none !important;".PHP_EOL;
		}
		$style .= "}".PHP_EOL;
						
		
		$style .= ".symposium-wrapper a:hover {".PHP_EOL;
		$style .= "	color: ".$link_hover." !important;".PHP_EOL;
		$style .= "}".PHP_EOL;

		$style .= "body img, body input, .corners {".PHP_EOL;
		$style .= "	border-radius: ".$border_radius."px !important;".PHP_EOL;
		$style .= "	-moz-border-radius: ".$border_radius."px !important;".PHP_EOL;
		$style .= "}".PHP_EOL;

		$style .= ".symposium-wrapper .label {".PHP_EOL;
		$style .= "  color: ".$label." !important;".PHP_EOL;
		$style .= "}".PHP_EOL;

		// Profile 
		$style .= ".symposium-wrapper #profile_right_column, .popup {".PHP_EOL;
		$style .= "	background-color: ".$main_background." !important;".PHP_EOL;
		$style .= "	border: ".$replies_border_size."px solid ".$primary_color." !important;".PHP_EOL;	
		$style .= "}".PHP_EOL;
		
		$style .= ".symposium-wrapper #symposium_comment, .symposium-wrapper .symposium_reply {".PHP_EOL;
		$style .= "	border: 1px solid ".$primary_color." ;".PHP_EOL;	
		$style .= "	border-radius: ".$border_radius."px;".PHP_EOL;	
		$style .= "}".PHP_EOL;
		
		// Forum or Tables (layout)

		$style .= ".symposium-wrapper #symposium_table {".PHP_EOL;
		$style .= "	border: ".$table_border."px solid ".$primary_color.";".PHP_EOL;	
		$style .= "}".PHP_EOL;
	
		$style .= ".symposium-wrapper .table_header {".PHP_EOL;
		$style .= "	background-color: ".$categories_background.";".PHP_EOL;
		$style .= "  font-weight: bold;".PHP_EOL;
	 	$style .= "  border-radius:0px;".PHP_EOL;
		$style .= "  -moz-border-radius:0px;".PHP_EOL;
		$style .= "  border: 0px".PHP_EOL;
	 	$style .= "  border-top-left-radius:".($border_radius-5)."px;".PHP_EOL;
		$style .= "  -moz-border-radius-topleft:".($border_radius-5)."px;".PHP_EOL;
	 	$style .= "  border-top-right-radius:".($border_radius-5)."px;".PHP_EOL;
		$style .= "  -moz-border-radius-topright:".($border_radius-5)."px;".PHP_EOL;
		$style .= "}".PHP_EOL;

		$style .= ".symposium-wrapper .table_topic, .symposium-wrapper #profile_name, .symposium-wrapper .topic-post-header {".PHP_EOL;
		$style .= "	font-family: ".$headingsfamily." !important;".PHP_EOL;
		$style .= "	font-size: ".$headingssize." !important;".PHP_EOL;
		$style .= "}".PHP_EOL;

		$style .= ".symposium-wrapper .table_topic {".PHP_EOL;
		$style .= "	color: ".$categories_color.";".PHP_EOL;
		$style .= "}".PHP_EOL;

		$style .= ".symposium-wrapper .table_topic:hover {".PHP_EOL;
		$style .= "	background-color: ".$table_rollover." !important;".PHP_EOL;
		$style .= "}".PHP_EOL;
		
		$style .= ".symposium-wrapper .row a, .symposium-wrapper .row_odd a {".PHP_EOL;
		if ($underline == "on") {
			$style .= "	text-decoration: underline;".PHP_EOL;
		} else {
			$style .= "	text-decoration: none;".PHP_EOL;
		}
		$style .= "}".PHP_EOL;
	
		$style .= ".symposium-wrapper .new-topic-subject-input, .symposium-wrapper .input-field, .symposium-wrapper #mail_recipient_list {".PHP_EOL;
		$style .= "	font-family: ".$fontfamily.";".PHP_EOL;
		$style .= "	border: ".$replies_border_size."px solid ".$primary_color.";".PHP_EOL;	
		$style .= "}".PHP_EOL;

		$style .= ".symposium-wrapper .new-topic-subject-text, .symposium-wrapper .reply-topic-subject-text, .symposium-wrapper .reply-topic-text {".PHP_EOL;
		$style .= "	font-family: ".$fontfamily.";".PHP_EOL;
		$style .= "}".PHP_EOL;
	
		$style .= ".symposium-wrapper #reply-topic {".PHP_EOL;
		$style .= "	border: ".$replies_border_size."px solid ".$primary_color.";".PHP_EOL;	
		$style .= "}".PHP_EOL;

		$style .= ".symposium-wrapper #reply-topic-bottom textarea {".PHP_EOL;
		$style .= "	border: 1px solid ".$primary_color.";".PHP_EOL;			
		$style .= "}".PHP_EOL;
		
		$style .= ".symposium-wrapper #new-topic-link, .symposium-wrapper #reply-topic-link, .symposium-wrapper .symposium-button,  .symposium-button {".PHP_EOL;
		$style .= "	font-family: ".$fontfamily." !important;".PHP_EOL;
		$style .= "	font-size: ".$fontsize."px !important;".PHP_EOL;
		$style .= "	background-color: ".$bigbutton_background." !important;".PHP_EOL;
		$style .= "	color: ".$bigbutton_color." !important;".PHP_EOL;
		$style .= "}".PHP_EOL;
	
		$style .= ".symposium-wrapper #new-topic-link:hover, .symposium-wrapper #reply-topic-link:hover, .symposium-wrapper .symposium-button:hover,  .symposium-button:hover {".PHP_EOL;
		$style .= "	background-color: ".$bigbutton_background_hover." !important;".PHP_EOL;
		$style .= "}".PHP_EOL;
						
		$style .= ".symposium-wrapper .round_bottom_left {".PHP_EOL;
	 	$style .= "  border-bottom-left-radius:".($border_radius-5)."px;".PHP_EOL;
		$style .= "  -moz-border-radius-bottomleft:".($border_radius-5)."px;".PHP_EOL;
		$style .= "}".PHP_EOL;
		
		$style .= ".symposium-wrapper .round_bottom_right {".PHP_EOL;
	 	$style .= "  border-bottom-right-radius:".($border_radius-5)."px;".PHP_EOL;
		$style .= "  -moz-border-radius-bottomright:".($border_radius-5)."px;".PHP_EOL;
		$style .= "}".PHP_EOL;
		
		$style .= ".symposium-wrapper .categories_color {".PHP_EOL;
		$style .= "	color: ".$categories_color.";".PHP_EOL;
		$style .= "}";
		$style .= ".symposium-wrapper .categories_background {".PHP_EOL;
		$style .= "	background-color: ".$categories_background.";".PHP_EOL;
		$style .= "}".PHP_EOL;
		
		$style .= ".symposium-wrapper .row, .symposium-wrapper .reply_div {".PHP_EOL;
		$style .= "	background-color: ".$row_color.";".PHP_EOL;
		$style .= "}".PHP_EOL;

		$style .= ".symposium-wrapper .wall_reply, .symposium-wrapper .wall_reply_div, .symposium-wrapper .wall_reply_avatar, .symposium-wrapper a, ";
		$style .= ".symposium-wrapper .mailbox_message_subject, .symposium-wrapper .mailbox_message_from, .symposium-wrapper .mail_item_age, .symposium-wrapper .mailbox_message, ";
		$style .= ".symposium-wrapper .row_views ";
		$style .= " {".PHP_EOL;
		$style .= "	background-color: transparent;".PHP_EOL;
		$style .= "}".PHP_EOL;
			
			
		$style .= ".symposium-wrapper .row_odd {".PHP_EOL;
		$style .= "	background-color: ".$row_color_alt.";".PHP_EOL;
		$style .= "}".PHP_EOL;
	
		$style .= ".symposium-wrapper .row:hover, .symposium-wrapper .row_odd:hover {".PHP_EOL;
		$style .= "	background-color: ".$table_rollover." !important;".PHP_EOL;
		$style .= "}".PHP_EOL;
		
		$style .= ".symposium-wrapper .row_link, .symposium-wrapper .edit, .symposium-wrapper .delete {".PHP_EOL;
		$style .= "	font-size: ".$headingssize." !important;".PHP_EOL;
		$style .= "	color: ".$link." !important;".PHP_EOL;
		$style .= "}".PHP_EOL;
			
		$style .= ".symposium-wrapper .row_link:hover {".PHP_EOL;
		$style .= "	color: ".$link_hover." !important;".PHP_EOL;
		$style .= "}".PHP_EOL;
	
		$style .= ".symposium-wrapper #starting-post {".PHP_EOL;
		$style .= "	border: ".$replies_border_size."px solid ".$primary_color.";".PHP_EOL;
		$style .= "	background-color: ".$main_background.";".PHP_EOL;
		$style .= "}".PHP_EOL;
							
		$style .= ".symposium-wrapper #starting-post, .symposium-wrapper #child-posts {".PHP_EOL;
		$style .= "	border: ".$replies_border_size."px solid ".$primary_color.";".PHP_EOL;
		$style .= "	background-color: ".$row_color_alt.";".PHP_EOL;
		$style .= "}".PHP_EOL;
		$style .= ".symposium-wrapper .child-reply {".PHP_EOL;
		$style .= "	border-bottom: ".$replies_border_size."px dotted ".$text_color_2.";".PHP_EOL;
		$style .= "}".PHP_EOL;
		
		$style .= ".symposium-wrapper .sep, .symposium-wrapper .sep_top {".PHP_EOL;
		$style .= "	clear:both;".PHP_EOL;
		$style .= "	width:100%;".PHP_EOL;
		$style .= "	border-bottom: ".$replies_border_size."px ".$row_border_style." ".$text_color_2.";".PHP_EOL;
		$style .= "}".PHP_EOL;
		$style .= ".symposium-wrapper .sep_top {".PHP_EOL;
		$style .= "	border-bottom: 0px ;".PHP_EOL;
		$style .= "	border-top: ".$replies_border_size."px ".$row_border_style." ".$text_color_2.";".PHP_EOL;
		$style .= "}".PHP_EOL;
			
		// Alerts
		
		$style .= ".symposium-wrapper .alert {".PHP_EOL;
		$style .= "	clear:both;".PHP_EOL;
		$style .= "	padding:6px;".PHP_EOL;
		$style .= "	margin-bottom:15px;".PHP_EOL;
		$style .= "	border: 1px solid #666;".PHP_EOL;	
		$style .= "	background-color: #eee;".PHP_EOL;
		$style .= "	color: #000;".PHP_EOL;
		$style .= "}".PHP_EOL;

		$style .= ".symposium-wrapper .transparent {".PHP_EOL;
		$style .= '  -ms-filter: "progid: DXImageTransform.Microsoft.Alpha(Opacity='.($closed_opacity*100).')";'.PHP_EOL;
		$style .= "  filter: alpha(opacity=".($closed_opacity*100).");".PHP_EOL;
		$style .= "  -moz-opacity: ".$closed_opacity.";".PHP_EOL;
		$style .= "  -khtml-opacity: ".$closed_opacity.";".PHP_EOL;
		$style .= "  opacity: ".$closed_opacity.";".PHP_EOL;
		$style .= "}".PHP_EOL;
		
					
		echo $style;
				
	}

	// Apply advanced CSS (via WP Admin -> Symposium -> Styles -> CSS)	
	if (get_option('symposium_css') != '') {
		echo str_replace("[]", chr(13), stripslashes(get_option('symposium_css')));
	}

	echo "</style>";
	echo "<!-- End WP Symposium styles -->";

?>
