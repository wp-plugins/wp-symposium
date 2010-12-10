<?php
/*
Plugin Name: WP Symposium
Plugin URI: http://www.wpsymposium.com
Description: Core code for Symposium, this plugin must be activated to have the admin menu, and admin functions.
Version: 0.1.7.1
Author: Simon Goodchild
Author URI: http://www.wpsymposium.com
License: GPL2
*/
	
/*  Copyright 2010  Simon Goodchild  (info@wpsymposium.com)

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

/* ====================================================== ADMIN ONLY CODE ====================================================== */

if (is_admin()) {
	include('symposium_menu.php');
}

/* ====================================================== ADMIN ====================================================== */

global $symposium_db_version;
$symposium_db_version = "1";
// Change log
// 1 = Initial version

// Dashboard Widget
add_action('wp_dashboard_setup', 'symposium_dashboard_widget');
function symposium_dashboard_widget(){
	wp_add_dashboard_widget('symposium_id', 'WP Symposium', 'symposium_widget');
}
function symposium_widget() {
	
	global $wpdb;
	
	echo '<img src="'.WP_PLUGIN_URL.'/wp-symposium/logo_small.gif" alt="WP Symposium logo" style="float:right; width:100px;height:120px;" />';

	echo '<table>';
	echo '<tr><td style="padding:8px"><a href="admin.php?page=symposium_categories">Categories</a></td>';
	echo '<td style="padding:8px">'.$wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix.'symposium_cats').'</td></tr>';
	echo '<tr><td style="padding:8px">Topics</td>';
	echo '<td style="padding:8px">'.$wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix.'symposium_topics'." WHERE topic_parent = 0").'</td></tr>';
	echo '<tr><td style="padding:8px">Replies</td>';
	echo '<td style="padding:8px">'.$wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix.'symposium_topics'." WHERE topic_parent > 0").'</td></tr>';
	echo '<tr><td style="padding:8px">Views</td>';
	echo '<td style="padding:8px">'.$wpdb->get_var("SELECT SUM(topic_views) FROM ".$wpdb->prefix.'symposium_topics'." WHERE topic_parent = 0").'</td></tr>';
	echo '</table>';

	echo '<p>';
	echo 'WP Symposium Version: <strong>'.get_option("symposium_version").'</strong><br />';
	echo 'Database version: <strong>'.get_option("symposium_db_version").'</strong><br />';
	$forum_url = $wpdb->get_var($wpdb->prepare("SELECT forum_url FROM ".$wpdb->prefix . 'symposium_config'));
	echo '<a href="'.$forum_url.'">View Forum</a>';
	echo '</p>';
}

function symposium_activate() {
	
   	global $wpdb, $current_user;
   	global $symposium_db_version;
	wp_get_current_user();

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

	// Version of WP Symposium
	$symposium_version = "0.1.7.1";
	if (get_option("symposium_version") == false) {
	    add_option("symposium_version", $symposium_version);
	} else {
		update_option("symposium_version", $symposium_version);
	}
	
	$db_ver = get_option("symposium_db_version");
	
	// Initial version *************************************************************************************
	if ($db_ver == false) {

	 	// Categories
	   	$table_name = $wpdb->prefix . "symposium_cats";
	   	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
	      
	      $sql = "CREATE TABLE " . $table_name . " (
			  cid int(11) NOT NULL AUTO_INCREMENT,
			  title varchar(256) NOT NULL,
			  listorder int(11) NOT NULL DEFAULT '0',
			  allow_new varchar(2) NOT NULL DEFAULT 'on',
			  defaultcat varchar(2) NOT NULL DEFAULT '',
			  PRIMARY KEY cid (cid)
	  		);";
	
	      dbDelta($sql);
	
	      $rows_affected = $wpdb->insert( $table_name, array( 'title' => 'General Topics' ) );
	      $new_category_id = $wpdb->insert_id;
	      $rows_affected = $wpdb->insert( $table_name, array( 'title' => 'Support Issues' ) );
	      $rows_affected = $wpdb->insert( $table_name, array( 'title' => 'Feedback' ) );
	
	   	} 
	   	   	
	 	// Subscriptions
	   	$table_name = $wpdb->prefix . "symposium_subs";
	   	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
	      
	      $sql = "CREATE TABLE " . $table_name . " (
			  sid int(11) NOT NULL AUTO_INCREMENT,
			  uid int(11) NOT NULL,
			  tid int(11) NOT NULL,
			  cid int(11) NOT NULL,
			  PRIMARY KEY sid (sid)
	  		);";
	
	      dbDelta($sql);
	
	   	}
	   	
		// Configuration
	   	$table_name = $wpdb->prefix . "symposium_config";
	   	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
	      
	      $sql = "CREATE TABLE " . $table_name . " (
	          oid int(11) NOT NULL AUTO_INCREMENT,
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
			  footer varchar(2048) NOT NULL,
			  show_categories varchar(2) NOT NULL,
			  send_summary varchar(2) NOT NULL,
			  forum_url varchar(128) NOT NULL,
			  from_email varchar(128) NOT NULL,
			  PRIMARY KEY oid (oid)
			);";
			
	      dbDelta($sql);
	
	      $rows_affected = $wpdb->insert( $table_name, array( 
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
	      	'table_border' => '2', 
	      	'replies_border_size' => '1', 
	      	'text_color_2' => '#0054a5', 
	      	'row_border_style' => 'dotted', 
	      	'row_border_size' => '1', 
			'border_radius' => '5',
			'label' => '#000',
	      	'footer' => 'Please don\'t reply to this email',
	      	'show_categories' => 'on',
	      	'send_summary' => 'on',
	      	'forum_url' => 'Important: Please update!',
	      	'from_email' => 'noreply@example.com'
	      	) );
	   	} 
		
	 	// Topics
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
			  PRIMARY KEY tid (tid)
	  		);";
	
	      dbDelta($sql);
	
	      $rows_affected = $wpdb->insert( $table_name, array( 
	      	'topic_category' => $new_category_id, 
	      	'topic_subject' => 'Welcome to the Forum', 
	      	'topic_post' => 'Welcome to the forum - this is a demonstration post and can be deleted.',
	      	'topic_owner' => $current_user->ID,
	      	'topic_date' => date("Y-m-d H:i:s"),
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
	      	'topic_parent' => $new_topic_id,
	      	'topic_started' => date("Y-m-d H:i:s")
	      	 ) );
	      $rows_affected = $wpdb->insert( $table_name, array( 
	      	'topic_category' => $new_category_id, 
	      	'topic_subject' => '', 
	      	'topic_post' => 'This is another demonstration reply.',
	      	'topic_owner' => $current_user->ID,
	      	'topic_date' => date("Y-m-d H:i:s"),
	      	'topic_parent' => $new_topic_id,
	      	'topic_started' => date("Y-m-d H:i:s")
	      	 ) );
	  		
	   	} 	
	
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
			  PRIMARY KEY sid (sid)
			);";
			
	      dbDelta($sql);
	
		  // Symposium
	      $rows_affected = $wpdb->insert( $table_name, array( 
	      	'title' => 'Symposium', 
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
	      	'table_border' => '2', 
	      	'replies_border_size' => '1', 
	      	'text_color_2' => '#0054a5', 
	      	'row_border_style' => 'dotted', 
	      	'row_border_size' => '1', 
			'border_radius' => '5',
			'label' => '#0054a5'
	      	) );
	
		  // Blue
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
	
		  // Black/Grey
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
	
		  // Grey
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
	
	   	} 

		// Set Database Version		
	    add_option("symposium_db_version", $symposium_db_version);
	   	
	}

	// Version 2 *************************************************************************************
	$db_ver = get_option("symposium_db_version");
	if ($db_ver == "1") {

		// Add Languages to Options
   		$wpdb->query("ALTER TABLE ".$wpdb->prefix."symposium_config"." ADD language varchar(3) NOT NULL DEFAULT 'ENG'");
   		
	 	// Add Languages Table
	   	$table_name = $wpdb->prefix . "symposium_lang";
	   	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
	      
	      $sql = "CREATE TABLE " . $table_name . " (
			  lid int(11) NOT NULL AUTO_INCREMENT,
			  language varchar(3) NOT NULL,
			  sant varchar(256) NOT NULL,
			  ts varchar(256) NOT NULL,
			  fpit varchar(256) NOT NULL,
			  sac varchar(256) NOT NULL,
			  emw varchar(256) NOT NULL,
			  p varchar(256) NOT NULL,
			  c varchar(256) NOT NULL,
			  cat varchar(256) NOT NULL,
			  lac varchar(256) NOT NULL,
			  top varchar(256) NOT NULL,
			  btf varchar(256) NOT NULL,
			  rew varchar(256) NOT NULL,
			  sbl varchar(256) NOT NULL,
			  f varchar(256) NOT NULL,
			  r varchar(256) NOT NULL,
			  v varchar(256) NOT NULL,
			  sb varchar(256) NOT NULL,
			  rer varchar(256) NOT NULL,
			  tis varchar(256) NOT NULL,
			  re varchar(256) NOT NULL,
			  e varchar(256) NOT NULL,
			  d varchar(256) NOT NULL,
			  aar varchar(256) NOT NULL,			  
			  rtt varchar(256) NOT NULL,			  
			  wir varchar(256) NOT NULL,			  
			  rep varchar(256) NOT NULL,			  
			  tt varchar(256) NOT NULL,			  
			  u varchar(256) NOT NULL,			  
			  bt varchar(256) NOT NULL,			  
			  t varchar(256) NOT NULL,			  
			  mc varchar(256) NOT NULL,			  
			  s varchar(256) NOT NULL,			  
			  pw varchar(256) NOT NULL,			  
			  sav varchar(256) NOT NULL,			  
			  hsa varchar(256) NOT NULL,			  
			  i varchar(256) NOT NULL,			  
			  nft varchar(256) NOT NULL,			  
			  nfr varchar(256) NOT NULL,			  
			  PRIMARY KEY lid (lid)
	  		);";
	  		
	      dbDelta($sql);
	
	   	} 

		// Update Database Version
		update_option("symposium_db_version", "2");
	}
	    
	// Version 3 *************************************************************************************
	$db_ver = get_option("symposium_db_version");
	if ($db_ver == "2") {

		// Add language labels
   		$wpdb->query("ALTER TABLE ".$wpdb->prefix."symposium_lang"." ADD prs varchar(256) NOT NULL");
   		$wpdb->query("ALTER TABLE ".$wpdb->prefix."symposium_lang"." ADD prm varchar(256) NOT NULL");
   		
   		// Update Database Version
		update_option("symposium_db_version", "3");
	}

	// Version 4 *************************************************************************************
	$db_ver = get_option("symposium_db_version");
	if ($db_ver == "3") {

		// Extend languages fields
		$wpdb->query("ALTER TABLE ".$wpdb->prefix."symposium_lang"." MODIFY COLUMN language varchar(64)");
	 	$wpdb->query("ALTER TABLE ".$wpdb->prefix."symposium_config"." MODIFY COLUMN language varchar(64)");

		// Set language to English
   		$wpdb->query("UPDATE ".$wpdb->prefix."symposium_config SET language = 'English'");
   		
   		// Update Database Version
		update_option("symposium_db_version", "4");
	}

	// Version 5 *************************************************************************************
	$db_ver = get_option("symposium_db_version");
	if ($db_ver == "4") {

		// Add underline style
   		$wpdb->query("ALTER TABLE ".$wpdb->prefix."symposium_config"." ADD underline varchar(2) NOT NULL DEFAULT 'on'");
   		$wpdb->query("ALTER TABLE ".$wpdb->prefix."symposium_styles"." ADD underline varchar(2) NOT NULL DEFAULT 'on'");

		// Add Aqua Style
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
   		
   		// Update Database Version
		update_option("symposium_db_version", "5");
	}

	// Version 6 *************************************************************************************
	$db_ver = get_option("symposium_db_version");
	if ($db_ver == "5") {

		// Add language labels
   		$wpdb->query("ALTER TABLE ".$wpdb->prefix."symposium_lang"." ADD tp varchar(256) NOT NULL");
   		$wpdb->query("ALTER TABLE ".$wpdb->prefix."symposium_lang"." ADD tps varchar(256) NOT NULL");
   		
   		// Update Database Version 
		update_option("symposium_db_version", "6");
	}

	// Version 7 *************************************************************************************
	$db_ver = get_option("symposium_db_version");
	if ($db_ver == "6") {

		// Add preview text lengths
   		$wpdb->query("ALTER TABLE ".$wpdb->prefix."symposium_options"." ADD preview1 int(11) NOT NULL DEFAULT '45'");
   		$wpdb->query("ALTER TABLE ".$wpdb->prefix."symposium_options"." ADD preview2 int(11) NOT NULL DEFAULT '90'");
   		
   		// Update Database Version 
		update_option("symposium_db_version", "7");
	}

	// ***********************************************************************************************
	// Re-load languages file for latest version *****************************************************
	$wpdb->query("DELETE FROM ".$wpdb->prefix . "symposium_lang");

	// Check XML languages file
	$url = WP_PLUGIN_URL . '/wp-symposium/languages.xml';
	$xml_dir = WP_PLUGIN_DIR . '/wp-symposium/languages.xml';
	if (file_exists($xml_dir)) {

		// Load XML file
		$curl = curl_init();
	    	curl_setopt($curl, CURLOPT_URL, $url);
	    	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	    	curl_setopt($curl, CURLOPT_REFERER, get_site_url());
	    	$str = curl_exec($curl);
	    	curl_close($curl);
	    	$xml = simplexml_load_string($str);
	  		
		if ($xml === false) {
			$wpdb->query( $wpdb->prepare("INSERT INTO ".$wpdb->prefix . "symposium_lang(language) VALUES ( %s )", 'XML file found, but failed to load') );			
		} else {	
			$languages = $xml->languages->language;
			
			for ($i = 0; $i < count($languages); $i++) {
				$ref = $languages[$i]->attributes()->ref;
		
				$wpdb->query( $wpdb->prepare("INSERT INTO ".$wpdb->prefix . "symposium_lang(language) VALUES ( %s )", $ref) );
		
				$translations = $languages[$i]->children();
				for ($j = 0; $j < count($translations); $j++) {
			   		$wpdb->query("UPDATE ".$wpdb->prefix."symposium_lang"." SET ".$translations[$j]->attributes()->code." = '".str_replace("#", "&", $translations[$j])."' WHERE language='".$ref."'");
				}					
			}
		}
	} else {
		$wpdb->query( $wpdb->prepare("INSERT INTO ".$wpdb->prefix . "symposium_lang(language) VALUES ( %s )", $xml_dir.' not found') );		
	}    
	
}
/* End of Activation */

function symposium_uninstall() {
   
   	global $wpdb;

   	$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix."symposium_config");
   	$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix."symposium_topics");
   	$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix."symposium_subs");
   	$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix."symposium_cats");
   	$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix."symposium_styles");
   	$wpdb->query("DROP TABLE IF EXISTS ".$wpdb->prefix."symposium_lang");

	// Delete Notification options
	delete_option("symposium_notification_inseconds");
	delete_option("symposium_notification_recc");
	delete_option("symposium_notification_triggercount");
	wp_clear_scheduled_hook('symposium_notification_hook');
	
	// Delete any options thats stored also
	delete_option('symposium_db_version');
	
}
/* End of Un-install */

function symposium_deactivate() {

	wp_clear_scheduled_hook('symposium_notification_hook');

}


// Display array contents (for de-bugging only)
function symposium_displayArrayContentFunction($arrayname,$tab="&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp",$indent=0) {
 $curtab ="";
 $returnvalues = "";
 while(list($key, $value) = each($arrayname)) {
  for($i=0; $i<$indent; $i++) {
   $curtab .= $tab;
   }
  if (is_array($value)) {
   $returnvalues .= "$curtab$key : Array: <br />$curtab{<br />\n";
   $returnvalues .= symposium_displayArrayContentFunction($value,$tab,$indent+1)."$curtab}<br />\n";
   }
  else $returnvalues .= "$curtab$key => $value<br />\n";
  $curtab = NULL;
  }
 return $returnvalues;
}

/* NOTIFICATIONS */

add_action('init', 'symposium_notification_setoptions');
function symposium_notification_setoptions() {
	update_option("symposium_notification_inseconds",86400);
	// 60 = 1 minute, 3600 = 1 hour, 10800 = 3 hours, 21600 = 6 hours, 43200 = 12 hours, 86400 = Daily, 604800 = Weekly
	/* This is where the actual recurring event is scheduled */
	if (!wp_next_scheduled('symposium_notification_hook')) {
		$dt=explode(':',date('d:m:Y',time()));
		$schedule=mktime(0,1,0,$dt[1],$dt[0],$dt[2])+86400;
		// set for 00:01 from tomorrow
		wp_schedule_event($schedule, "symposium_notification_recc", "symposium_notification_hook");
	}
}

/* a reccurence has to be added to the cron_schedules array */
add_filter('cron_schedules', 'symposium_notification_more_reccurences');
function symposium_notification_more_reccurences($recc) {
	$recc['symposium_notification_recc'] = array('interval' => get_option("symposium_notification_inseconds"), 'display' => 'Symposium Notification Schedule');
	return $recc;
}
	
/* This is the scheduling hook for our plugin that is triggered by cron */
add_action('symposium_notification_hook','symposium_notification_trigger_schedule');
function symposium_notification_trigger_schedule() {
	
	global $wpdb;
	
	// Check to see if we should be sending a digest
	$send_summary = $wpdb->get_var($wpdb->prepare("SELECT send_summary FROM ".$wpdb->prefix . 'symposium_config'));
	$send_summary = "on";
	if ($send_summary == "on") {
		// Calculate yesterday			
		$startTime = mktime(0, 0, 0, date('m'), date('d')-1, date('Y'));
		$endTime = mktime(23, 59, 59, date('m'), date('d')-1, date('Y'));
		
		// Get all new topics from previous period
		$topics_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix.'symposium_topics'." WHERE topic_parent = 0 AND UNIX_TIMESTAMP(topic_date) >= ".$startTime." AND UNIX_TIMESTAMP(topic_date) <= ".$endTime));

		if ($topics_count > 0) {

			// Get Forum URL
			$forum_url = $wpdb->get_var($wpdb->prepare("SELECT forum_url FROM ".$wpdb->prefix . 'symposium_config'));

			$body = "";
			
			$categories = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix.'symposium_cats'." ORDER BY listorder"); 
			if ($categories) {
				foreach ($categories as $category) {
					
					$shown_category = false;
					$topics = $wpdb->get_results("
						SELECT tid, topic_subject, topic_parent, topic_post, topic_date, display_name, topic_category 
						FROM ".$wpdb->prefix.'symposium_topics'." INNER JOIN ".$wpdb->prefix.'users'." ON ".$wpdb->prefix.'symposium_topics'.".topic_owner = ".$wpdb->prefix.'users'.".ID 
						WHERE topic_parent = 0 AND topic_category = ".$category->cid." AND UNIX_TIMESTAMP(topic_date) >= ".$startTime." AND UNIX_TIMESTAMP(topic_date) <= ".$endTime." 
						ORDER BY tid"); 
					if ($topics) {
						if (!$shown_category) {
							$shown_category = true;
							$body .= "<h1>".stripslashes($category->title)."</h1>";
						}
						$body .= "<h2>New Topics</h2>";
						$body .= "<ol>";
						foreach ($topics as $topic) {
							$body .= "<li><strong><a href='".$forum_url."?cid=".$category->cid."&show=".$topic->tid."'>".stripslashes($topic->topic_subject)."</a></strong>";
							$body .= " started by ".$topic->display_name.":<br />";																
							$body .= stripslashes($topic->topic_post);
							$body .= "</li>";
						}
						$body .= "</ol>";
					}

					$replies = $wpdb->get_results("
						SELECT tid, topic_subject, topic_parent, topic_post, topic_date, display_name, topic_category 
						FROM ".$wpdb->prefix.'symposium_topics'." INNER JOIN ".$wpdb->prefix.'users'." ON ".$wpdb->prefix.'symposium_topics'.".topic_owner = ".$wpdb->prefix.'users'.".ID 
						WHERE topic_parent > 0 AND topic_category = ".$category->cid." AND UNIX_TIMESTAMP(topic_date) >= ".$startTime." AND UNIX_TIMESTAMP(topic_date) <= ".$endTime."
						ORDER BY topic_parent, tid"); 
					if ($replies) {
						if (!$shown_category) {
							$shown_category = true;
							$body .= "<h1>".$category->title."</h1>";
						}
						$body .= "<h2>Replies in ".$category->title."</h2>";
						$current_parent = '';
						foreach ($replies as $reply) {
							$parent = $wpdb->get_var($wpdb->prepare("SELECT topic_subject FROM ".$wpdb->prefix.'symposium_topics'." WHERE tid = ".$reply->topic_parent));
							if ($parent != $current_parent) {
								$body .= "<h3>".$parent."</h3>";
								$current_parent = $parent;
							}
							$body .= "<em>".$reply->display_name." wrote:</em> ";
							$post = $reply->topic_post;
							if ( strlen($post) > 100 ) { $post = substr($post, 0, 100)."..."; }
							$body .= stripslashes($post);
							$body .= " <a href='".$forum_url."?cid=".$category->cid."&show=".$topic->tid."'>View topic...</a>";
							$body .= "<br />";
							$body .= "<br />";
						}						
					}	
				}
			}
			
			$users = $wpdb->get_results("SELECT DISTINCT user_email FROM ".$wpdb->prefix.'users'); 
			if ($users) {
				foreach ($users as $user) {
					if(sendmail($user->user_email, 'Daily Forum Digest', $body)) {
						update_option("symposium_notification_triggercount",get_option("symposium_notification_triggercount")+1);
					}			
				}
			}

			// Report back to monitor the service - you can delete the following 4 lines if you do not want this support
			// but in providing this anonymous information you can help us to help you
			if ($topics_count > 0) {
				$mail_to = 'info@wpsymposium.com';
				$forum_url = $wpdb->get_var($wpdb->prepare("SELECT forum_url FROM ".$config));				
				if(sendmail($mail_to, 'Forum Digest Report: '.get_site_url(), get_site_url().'<br />'.$forum_url.'<br /><br />'.$topics_count.' post(s)')) {
					update_option("symposium_notification_triggercount",get_option("symposium_notification_triggercount")+1);
				}
			}

		} else {
			// Report back to monitor the service - you can delete the following 4 lines if you do not want this support
			// but in providing this anonymous information you can help us to help you
			$mail_to = 'info@wpsymposium.com';
			if(sendmail($mail_to, 'Nil Forum Digest Report: '.get_site_url(), get_site_url().'<br />'.$forum_url.'<br /><br />No Posts')) {
				update_option("symposium_notification_triggercount",get_option("symposium_notification_triggercount")+1);
			}
		}
	}

}

// Hook to replace Smilies
function symposium_smilies($buffer){ // $buffer contains entire page
	$smileys = WP_PLUGIN_URL . '/wp-symposium/smilies/';
	$smileys_dir = WP_PLUGIN_DIR . '/wp-symposium/smilies/';
	// Smilies as classic text
	$buffer = str_replace(":)", "<img src='".$smileys."smile.png' alt='emoticon'/>", $buffer);
	$buffer = str_replace(":(", "<img src='".$smileys."sad.png' alt='emoticon'/>", $buffer);
	$buffer = str_replace(":'(", "<img src='".$smileys."crying.png' alt='emoticon'/>", $buffer);
	$buffer = str_replace(":x", "<img src='".$smileys."kiss.png' alt='emoticon'/>", $buffer);
	$buffer = str_replace(":X", "<img src='".$smileys."shutup.png' alt='emoticon'/>", $buffer);
	$buffer = str_replace(":D", "<img src='".$smileys."laugh.png' alt='emoticon'/>", $buffer);
	$buffer = str_replace(":$", "<img src='".$smileys."moneymouth.png' alt='emoticon'/>", $buffer);
	$buffer = str_replace(":|", "<img src='".$smileys."neutral.png' alt='emoticon'/>", $buffer);
	$buffer = str_replace(":?", "<img src='".$smileys."question.png' alt='emoticon'/>", $buffer);
	$buffer = str_replace(":z", "<img src='".$smileys."sleepy.png' alt='emoticon'/>", $buffer);
	$buffer = str_replace(":P", "<img src='".$smileys."tongue.png' alt='emoticon'/>", $buffer);
	$buffer = str_replace(";)", "<img src='".$smileys."wink.png' alt='emoticon'/>", $buffer);
	// Other images
	
	$i = 0;
	do {
		$i++;
		$start = strpos($buffer, "{{");
		if ($start === false) {
		} else {
			$end = strpos($buffer, "}}");
			if ($end === false) {
			} else {
				$first_bit = substr($buffer, 0, $start);
				$last_bit = substr($buffer, $end+2, strlen($buffer)-$end-2);
				$bit = substr($buffer, $start+2, $end-$start-2);
				if (file_exists($smileys_dir.$bit.".png")) {
					$buffer = $first_bit."<img src='".$smileys.$bit.".png' alt='emoticon'/>".$last_bit;
				} else {
					$buffer = $first_bit."&#123;&#123;".$bit."&#125;&#125;".$last_bit;
				}
			}
		}
	} while ($i < 100 && strpos($buffer, "{{")>0);
	
	return $buffer;
}

// Hook for URL redirect
function symposium_redirect($buffer){ 
	global $wpdb;
	$thispage = get_permalink();

	if (function_exists('symposium_forum')) {
			
		$forum_url = $wpdb->get_var($wpdb->prepare("SELECT forum_url FROM ".$wpdb->prefix."symposium_config"));
		if ($forum_url[strlen($forum_url)-1] != '/') { $forum_url .= '/'; }
		
		$parsed_url=parse_url($_SERVER['REQUEST_URI']);
		$path = $parsed_url['path'];
		if ($path[strlen($path)-1] != '/') { $path .= '/'; }
		$paths = explode('/',$path);
		$query = $parsed_url['query'];
		
		$max = count($paths);
		$id = $paths[$max-4];
		$category = $paths[$max-3];
		$topic = $paths[$max-2];
		if (is_numeric($category)) {
			// Categories not in use
			$id = $category;
			$category = "-";
		}
				
		// If an ID was passed	
		if ($id != '') {
			if (!(isset($_GET['show']))) {
				// Just show category
				header("Location: ".$forum_url."?cid=".$id);
				exit;					
			} else {				
				// Try getting category for id
				$cat_id = $wpdb->get_var($wpdb->prepare("SELECT topic_category FROM ".$wpdb->prefix."symposium_topics"." WHERE tid = ".$id));
				if ($cat_id != 0) {
					header("Location: ".$forum_url."?cid=".$cat_id."&show=".$id);
					exit;
				} else {
					header("Location: ".$forum_url."?cid=&show=".$id);
					exit;
				}
			}
		}
	}
	
    return $buffer;
}

function symposium_admin_check() {
	global $wpdb;
	$forum_url = $wpdb->get_var($wpdb->prepare("SELECT forum_url FROM ".$wpdb->prefix . 'symposium_config'));
	if ($forum_url == "Important! Please update!") {
		echo "<div class='updated'><p><strong>Important!</strong> Please set <a href='admin.php?page=symposium_options'>WP Symposium Options</a> immediately.</p></div>";
	}
}
add_action('admin_notices', 'symposium_admin_check');

function symposium_replace(){
	ob_start();
	ob_start('symposium_smilies');
	ob_start('symposium_redirect');
}
add_action('template_redirect', 'symposium_replace');

// Add jQuery and jQuery scripts
function admin_init() {
	if (is_admin()) {
		// Color Picker
		wp_register_script('symposium_iColorPicker', WP_PLUGIN_URL . '/wp-symposium/iColorPicker.js');
	    	wp_enqueue_script('symposium_iColorPicker');

	}

}
add_action('init', 'admin_init');

// Add jQuery and jQuery scripts
function forum_init() {
	if (!is_admin()) {
		wp_enqueue_script('jquery');
	}
}
add_action('init', 'forum_init');

// Add Stylesheet
function add_symposium_stylesheet() {
	if (!is_admin()) {
	    $myStyleUrl = WP_PLUGIN_URL . '/wp-symposium/symposium.css';
	    $myStyleFile = WP_PLUGIN_DIR . '/wp-symposium/symposium.css';
	    if ( file_exists($myStyleFile) ) {
	        wp_register_style('symposium_StyleSheet', $myStyleUrl);
	        wp_enqueue_style('symposium_StyleSheet');
	    } else {
		    wp_die( __('Stylesheet ('.$myStyleFile.' not found.') );
	    }
	}    
}
add_action('wp_print_styles', 'add_symposium_stylesheet');


register_activation_hook(__FILE__,'symposium_activate');
register_deactivation_hook(__FILE__, 'symposium_deactivate');
register_uninstall_hook(__FILE__, 'symposium_uninstall');

?>
