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


// Create like/dislike table
$table_name = $wpdb->base_prefix . "symposium_likes";
if($wpdb->get_var("show tables like '$table_name'") != $table_name) {

	$sql = "CREATE TABLE " . $table_name . " (
	vid int(11) NOT NULL AUTO_INCREMENT,
	type varchar(16) NOT NULL,
	cid int(11) NOT NULL,
	uid int(11) NOT NULL,
	liked_date datetime NOT NULL,
	PRIMARY KEY  vid (vid)
 	) CHARACTER SET utf8 COLLATE utf8_general_ci;";

	dbDelta($sql);

}
// Add index
symposium_add_index($table_name, 'cid');

// Create voting table
$table_name = $wpdb->prefix . "symposium_topics_scores";
if($wpdb->get_var("show tables like '$table_name'") != $table_name) {

	$sql = "CREATE TABLE " . $table_name . " (
	sid int(11) NOT NULL AUTO_INCREMENT,
	tid int(11) NOT NULL,
	uid int(11) NOT NULL,
	score int(11) NOT NULL,
	topic_date datetime NOT NULL,
	PRIMARY KEY  sid (sid)
 	) CHARACTER SET utf8 COLLATE utf8_general_ci;";

	dbDelta($sql);

}


// Create tmp image upload table (if storing images in database)
$table_name = $wpdb->prefix . "symposium_topics_images";
if($wpdb->get_var("show tables like '$table_name'") != $table_name) {

	$sql = "CREATE TABLE " . $table_name . " (
	tmpid int(11) NOT NULL AUTO_INCREMENT,
	uid int(11) NOT NULL DEFAULT '0',
	tid int(11) DEFAULT '0',
	filename varchar(256) NOT NULL,
	upload mediumblob,
	PRIMARY KEY  tmpid (tmpid)
 	) CHARACTER SET utf8 COLLATE utf8_general_ci;";

	dbDelta($sql);

}

// Create Categories
$table_name = $wpdb->prefix . "symposium_cats";
if($wpdb->get_var("show tables like '$table_name'") != $table_name) {

	$sql = "CREATE TABLE " . $table_name . " (
	cid int(11) NOT NULL AUTO_INCREMENT,
	title varchar(64) NOT NULL,
	listorder int(11) NOT NULL DEFAULT '0',
	allow_new varchar(2) NOT NULL DEFAULT 'on',
	defaultcat varchar(2) NOT NULL DEFAULT '',
	PRIMARY KEY  cid (cid)
 	) CHARACTER SET utf8 COLLATE utf8_general_ci;";

	dbDelta($sql);

	$rows_affected = $wpdb->insert( $table_name, array( 'title' => 'General Topics' ) );
	$new_category_id = $wpdb->insert_id;
	$rows_affected = $wpdb->insert( $table_name, array( 'title' => 'Support Issues' ) );
	$rows_affected = $wpdb->insert( $table_name, array( 'title' => 'Feedback' ) );
} 
// Add index
symposium_add_index($table_name, 'cat_parent');

 
// Create Subscriptions
$table_name = $wpdb->prefix . "symposium_subs";
if($wpdb->get_var("show tables like '$table_name'") != $table_name) {

	$sql = "CREATE TABLE " . $table_name . " (
	sid int(11) NOT NULL AUTO_INCREMENT,
	uid int(11) NOT NULL,
	tid int(11) NOT NULL,
	cid int(11) NOT NULL,
	PRIMARY KEY  sid (sid)
 	) CHARACTER SET utf8 COLLATE utf8_general_ci;";

	dbDelta($sql);

}
// Add index
symposium_add_index($table_name, 'uid');
symposium_add_index($table_name, 'tid');

// Create Comments (including status)
$table_name = $wpdb->base_prefix . "symposium_comments";
if($wpdb->get_var("show tables like '$table_name'") != $table_name) {

	$sql = "CREATE TABLE " . $table_name . " (
	cid int(11) NOT NULL AUTO_INCREMENT,
	subject_uid int(11) NOT NULL,
	author_uid int(11) NOT NULL,
	comment_parent int(11) NOT NULL,
	comment_timestamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	comment text,
	PRIMARY KEY  cid (cid)
 	) CHARACTER SET utf8 COLLATE utf8_general_ci;";

	dbDelta($sql);

}
// Add index
symposium_add_index($table_name, 'subject_uid');
symposium_add_index($table_name, 'author_uid');
symposium_add_index($table_name, 'comment_parent');

// Create Topics
$table_name = $wpdb->prefix . "symposium_topics";
if($wpdb->get_var("show tables like '$table_name'") != $table_name) {

	$sql = "CREATE TABLE " . $table_name . " (
	tid int(11) NOT NULL AUTO_INCREMENT,
	topic_group int(11) NOT NULL DEFAULT '0',
	topic_category int(11) NOT NULL DEFAULT '0',
	topic_subject varchar(256) NOT NULL,
	topic_post text NOT NULL,
	topic_owner int(11) NOT NULL,
	topic_date datetime NOT NULL,
	topic_parent int(11) NOT NULL,
	topic_views int(11) NOT NULL,
	topic_started datetime NOT NULL,
	topic_sticky int(11) NOT NULL DEFAULT '0',
	PRIMARY KEY  tid (tid)
	) CHARACTER SET utf8 COLLATE utf8_general_ci;";

	dbDelta($sql);
	
	if (!wps_is_wpmu()) {

		$rows_affected = $wpdb->insert( $table_name, array( 
		'topic_category' => $new_category_id, 
		'topic_subject' => 'Welcome to the Forum', 
		'topic_post' => 'Welcome to the forum - this is a demonstration post and can be deleted.',
		'topic_owner' => $current_user->ID,
		'topic_date' => date("Y-m-d H:i:s"),
		'topic_views' => 0,
		'topic_parent' => 0,
		'topic_started' => date("Y-m-d H:i:s")
		 ) );
		 
		$new_topic_id = $wpdb->insert_id;
		$rows_affected = $wpdb->insert( $table_name, array( 
		'topic_category' => $new_category_id, 
		'topic_subject' => '', 
		'topic_post' => 'This is a demonstration reply.',
		'topic_owner' => $current_user->ID,
		'topic_date' => date("Y-m-d H:i:s"),
		'topic_views' => 0,
		'topic_parent' => $new_topic_id,
		'topic_started' => date("Y-m-d H:i:s")
		 ) );
		$rows_affected = $wpdb->insert( $table_name, array( 
		'topic_category' => $new_category_id, 
		'topic_subject' => '', 
		'topic_post' => 'This is another demonstration reply.',
		'topic_owner' => $current_user->ID,
		'topic_date' => date("Y-m-d H:i:s"),
		'topic_views' => 0,
		'topic_parent' => $new_topic_id,
		'topic_started' => date("Y-m-d H:i:s")
		 ) );
	}

} 	
// Add index
symposium_add_index($table_name, 'topic_owner');
symposium_add_index($table_name, 'topic_parent');
symposium_add_index($table_name, 'topic_category');
symposium_add_index($table_name, 'topic_date');
symposium_add_index($table_name, 'topic_started');




// Create WPS users meta table
$table_name = $wpdb->prefix . "symposium_usermeta";
if($wpdb->get_var("show tables like '$table_name'") != $table_name) {

	$sql = "CREATE TABLE " . $table_name . " (
	mid int(11) NOT NULL AUTO_INCREMENT,
	uid int(11) NOT NULL,
	profile_avatar mediumblob,
	PRIMARY KEY  mid (mid)
 	) CHARACTER SET utf8 COLLATE utf8_general_ci;";

    dbDelta($sql);

} 	
// Add index
symposium_add_index($table_name, 'uid', "UNIQUE");


// Create extended usermeta table
$table_name = $wpdb->prefix . "symposium_extended";
if($wpdb->get_var("show tables like '$table_name'") != $table_name) {

	$sql = "CREATE TABLE " . $table_name . " (
	eid int(11) NOT NULL AUTO_INCREMENT,
	extended_name varchar(64) NOT NULL DEFAULT 'New field',
	extended_type varchar(16) NOT NULL DEFAULT 'Text',
	extended_default text,
	extended_order int(11) NOT NULL DEFAULT '0',
	PRIMARY KEY  eid (eid)
 	) CHARACTER SET utf8 COLLATE utf8_general_ci;";

    dbDelta($sql);

} 	

// Create friends table
$table_name = $wpdb->base_prefix . "symposium_friends";
if($wpdb->get_var("show tables like '$table_name'") != $table_name) {

	$sql = "CREATE TABLE " . $table_name . " (
	fid int(11) NOT NULL AUTO_INCREMENT,
	friend_from int(11) NOT NULL,
	friend_to int(11) NOT NULL,
	friend_accepted varchar(2) NOT NULL,
	friend_message text NOT NULL,
	friend_timestamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY  fid (fid)
 	) CHARACTER SET utf8 COLLATE utf8_general_ci;";

    dbDelta($sql);

} 	
// Add index
symposium_add_index($table_name, 'friend_from');
symposium_add_index($table_name, 'friend_to');

// Create chat table
$table_name = $wpdb->base_prefix . "symposium_chat";
if($wpdb->get_var("show tables like '$table_name'") != $table_name) {

	$sql = "CREATE TABLE " . $table_name . " (
	chid int(11) NOT NULL AUTO_INCREMENT,
	chat_from int(11) NOT NULL,
	chat_to int(11) NOT NULL,
	chat_message text NOT NULL,
	chat_timestamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY  chid (chid)
 	) CHARACTER SET utf8 COLLATE utf8_general_ci;";

    dbDelta($sql);

} 	
// Add index
symposium_add_index($table_name, 'chat_from');
symposium_add_index($table_name, 'chat_to');

// Create mail table
$table_name = $wpdb->base_prefix . "symposium_mail";
if($wpdb->get_var("show tables like '$table_name'") != $table_name) {

	$sql = "CREATE TABLE " . $table_name . " (
	mail_mid int(11) NOT NULL AUTO_INCREMENT,
	mail_from int(11),
	mail_to int(11),
	mail_read varchar(2) NOT NULL DEFAULT '',
	mail_sent timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	mail_subject varchar(256) NOT NULL,
	mail_in_deleted varchar(2) NOT NULL DEFAULT '',
	mail_sent_deleted varchar(2) NOT NULL DEFAULT '',
	mail_message TEXT,
	PRIMARY KEY  mail_mid (mail_mid)
 	) CHARACTER SET utf8 COLLATE utf8_general_ci;";

    dbDelta($sql);

	// Mail to administrator
	$rows_affected = $wpdb->insert( $wpdb->prefix . "symposium_mail", array( 
	'mail_from' => $current_user->ID, 
	'mail_to' => $current_user->ID, 
	'mail_subject' => 'Welcome to WP Symposium Mail.',
	'mail_message' => 'This is an example message, from me to myself...'
	 ) );

} 	
// Add index
symposium_add_index($table_name, 'mail_to');

// Library of Styles
$table_name = $wpdb->prefix . "symposium_styles";
if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
	
$sql = "CREATE TABLE " . $table_name . " (
    sid int(11) NOT NULL AUTO_INCREMENT,
	title varchar(32) NOT NULL,
	categories_background varchar(12) NOT NULL,
	categories_color varchar(12) NOT NULL,
	bigbutton_background varchar(12) NOT NULL,
	bigbutton_color varchar(12) NOT NULL,
	bigbutton_background_hover varchar(12) NOT NULL,
	bigbutton_color_hover varchar(12) NOT NULL,
	bg_color_1 varchar(12) NOT NULL,
	bg_color_2 varchar(12) NOT NULL,
	bg_color_3 varchar(12) NOT NULL,
	text_color varchar(12) NOT NULL,
	table_rollover varchar(12) NOT NULL,
	link varchar(12) NOT NULL,
	link_hover varchar(12) NOT NULL,
	table_border varchar(2) NOT NULL,
	replies_border_size varchar(2) NOT NULL,
	text_color_2 varchar(12) NOT NULL,
	row_border_style varchar(7) NOT NULL,
	row_border_size varchar(2) NOT NULL,
	border_radius varchar(2) NOT NULL,
	label varchar(12) NOT NULL,
	PRIMARY KEY  sid (sid)
	) CHARACTER SET utf8 COLLATE utf8_general_ci;";
	
	dbDelta($sql);

  	// Who Blue
	$rows_affected = $wpdb->insert( $table_name, array( 
	'title' => 'Who Blue', 
	'categories_background' => '#0072bc', 
	'categories_color' => '#fff', 
	'bigbutton_background' => '#0072bc', 
	'bigbutton_color' => '#fff', 
	'bigbutton_background_hover' => '#00aeef',
	'bigbutton_color_hover' => '#fff', 
	'bg_color_1' => '#0072bc', 
	'bg_color_2' => '#ebebeb',
	'bg_color_3' => '#fff', 
	'text_color' => '#000', 
	'table_rollover' => '#fbaf5a', 
	'link' => '#0054a5', 
	'link_hover' => '#000', 
	'table_border' => '0', 
	'replies_border_size' => '0', 
	'text_color_2' => '#0054a5', 
	'row_border_style' => 'dotted', 
	'row_border_size' => '0', 
	'border_radius' => '5',
	'label' => '#0054a5'
	) );

  	// Blue Azure
	$rows_affected = $wpdb->insert( $table_name, array( 
	'title' => 'Blue Azure', 
	'categories_background' => '#0072bc', 
	'categories_color' => '#fff', 
	'bigbutton_background' => '#0072bc', 
	'bigbutton_color' => '#fff', 
	'bigbutton_background_hover' => '#00aeef',
	'bigbutton_color_hover' => '#fff', 
	'bg_color_1' => '#0072bc', 
	'bg_color_2' => '#ebebeb',
	'bg_color_3' => '#e1e1e1', 
	'text_color' => '#000', 
	'table_rollover' => '#00aeef', 
	'link' => '#0054a5', 
	'link_hover' => '#000', 
	'table_border' => '2', 
	'replies_border_size' => '1', 
	'text_color_2' => '#0054a5', 
	'row_border_style' => 'dotted', 
	'row_border_size' => '1', 
	'border_radius' => '5',
	'label' => '#0054a5'
	) );

  	// Gothic
	$rows_affected = $wpdb->insert( $table_name, array( 
	'title' => 'Gothic', 
	'categories_background' => '#363636', 
	'categories_color' => '#fff', 
	'bigbutton_background' => '#fff', 
	'bigbutton_color' => '#000', 
	'bigbutton_background_hover' => '#c2c2c2',
	'bigbutton_color_hover' => '#000', 
	'bg_color_1' => '#000', 
	'bg_color_2' => '#363636',
	'bg_color_3' => '#464646', 
	'text_color' => '#959595', 
	'table_rollover' => '#626262', 
	'link' => '#fff', 
	'link_hover' => '#959595', 
	'table_border' => '2', 
	'replies_border_size' => '1', 
	'text_color_2' => '#c2c2c2', 
	'row_border_style' => 'dotted', 
	'row_border_size' => '1', 
	'border_radius' => '5',
	'label' => '#000'
	) );

  	// Metal
	$rows_affected = $wpdb->insert( $table_name, array( 
	'title' => 'Metal', 
	'border_radius' => '5',
	'bigbutton_background' => '#464646', 
	'bigbutton_background_hover' => '#555',
	'bigbutton_color' => '#fff', 
	'bigbutton_color_hover' => '#fff', 
	'bg_color_1' => '#7d7d7d', 
	'bg_color_2' => '#ebebeb',
	'bg_color_3' => '#e1e1e1', 
	'table_border' => '2', 
	'row_border_style' => 'dotted', 
	'row_border_size' => '1', 
	'replies_border_size' => '1', 
	'table_rollover' => '#7d7d7d', 
	'categories_background' => '#7d7d7d', 
	'categories_color' => '#fff', 
	'text_color' => '#000', 
	'text_color_2' => '#363636', 
	'link' => '#000', 
	'link_hover' => '#363636', 
	'label' => '#000'
	) );

  	// Neutral
	$rows_affected = $wpdb->insert( $table_name, array( 
	'title' => 'Neutral', 
	'border_radius' => '0',
	'bigbutton_background' => '#959595', 
	'bigbutton_background_hover' => '#c2c2c2',
	'bigbutton_color' => '#fff', 
	'bigbutton_color_hover' => '#000', 
	'bg_color_1' => '#363636', 
	'bg_color_2' => '#fff',
	'bg_color_3' => '#ebebeb', 
	'table_rollover' => '#e1e1e1', 
	'table_border' => '0', 
	'row_border_style' => 'dotted', 
	'row_border_size' => '1', 
	'replies_border_size' => '0', 
	'categories_background' => '#c2c2c2', 
	'categories_color' => '#000', 
	'text_color' => '#000', 
	'text_color_2' => '#898989', 
	'link' => '#000', 
	'link_hover' => '#363636', 
	'label' => '#000'
	) );

	// Aqua
   	$rows_affected = $wpdb->insert( $wpdb->prefix."symposium_styles", array( 
	'title' => 'Aqua', 
	'border_radius' => '5',
	'bigbutton_background' => '#B9D3EE', 
	'bigbutton_background_hover' => '#B9D3EE',
	'bigbutton_color' => '#505050', 
	'bigbutton_color_hover' => '#000', 
	'bg_color_1' => '#B9D3EE', 
	'bg_color_2' => '#fff',
	'bg_color_3' => '#fff', 
	'table_rollover' => '#F8F8F8', 
	'table_border' => '0', 
	'row_border_style' => 'dotted', 
	'row_border_size' => '1', 
	'replies_border_size' => '1', 
	'categories_background' => '#B9D3EE', 
	'categories_color' => '#505050', 
	'text_color' => '#505050', 
	'text_color_2' => '#505050', 
	'link' => '#505050', 
	'underline' => '', 
	'link_hover' => '#000', 
	'label' => '#505050'
	) );

} 

 	


?>
