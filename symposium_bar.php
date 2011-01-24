<?php
/*
Plugin Name: WP Symposium Notification Bar
Plugin URI: http://www.wpsymposium.com
Description: Bar along bottom of screen to display notifications on new messages, mail. Also controls live chat windows. Simply activate to add.
Version: 0.1.30
Author: WP Symposium
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

/* ====================================================== PHP FUNCTIONS ====================================================== */

// Adds notification bar
function add_notification_bar()  
{  
	if (!is_admin()) {

	   	global $wpdb, $current_user;
		wp_get_current_user();

		$plugin = WP_PLUGIN_URL.'/wp-symposium';

		$config = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix.'symposium_config'));
		$meta = get_symposium_meta_row($current_user->ID);
		
		if ( ($config->visitors == 'on') || (is_user_logged_in()) ) {
	
			$allow_personal_settings = $config->allow_personal_settings;
			if ($allow_personal_settings != "on") {
				$sound = $config->sound;
				$soundchat = $sound;
				$bar_position = $config->bar_position;
			} else {
				$sound = $meta->sound;
				$soundchat = $meta->soundchat;
				$bar_position = $meta->bar_position;			
			}
			$border_radius = $config->border_radius;
			$use_chat = $config->use_chat;
			$bar_polling = ($config->bar_polling*1000);
			$chat_polling = ($config->chat_polling*1000);
			$use_wp_profile = $config->use_wp_profile;
			$use_wp_login = $config->use_wp_login;
			$use_wp_register = $config->use_wp_register;
			$custom_login_url = $config->custom_login_url;
			$custom_register_url = $config->custom_register_url;
			$inactive = $config->online;
			$offline = $config->offline;
	
			include_once('symposium_functions.php');
	
			// maximum number of chat windows
			$maxChatWindows = 3;
	
			?>
				
			<style>
				#symposium-notification-bar {
					<?php echo $bar_position; ?>:0px;
				}
				
				#symposium-chatboxes {
					<?php echo $bar_position; ?>:28px;
				}
											
				.symposium-online-box {
					border-radius: <?php echo $border_radius; ?>px;
					-moz-border-radius: <?php echo $border_radius; ?>px;
					<?php if (!function_exists('symposium_profile')) {
						echo 'display: none';
					}?>
				}
				.symposium-online-box-none {
					border-radius: <?php echo $border_radius; ?>px;
					-moz-border-radius: <?php echo $border_radius; ?>px;
					<?php if (!function_exists('symposium_profile')) {
						echo 'display: none';
					}?>
				}
				
				.symposium-email-box {
					border-radius: <?php echo $border_radius; ?>px;
					-moz-border-radius: <?php echo $border_radius; ?>px;
					<?php if (!function_exists('symposium_mail')) {
						echo 'display: none';
					}?>
				}
				.symposium-email-box-read {
					background-image:url('<?php echo $plugin; ?>/images/email.gif');
				}
				.symposium-email-box-unread {
					background-image:url('<?php echo $plugin; ?>/images/emailunread.gif');
				}
	
				.symposium-friends-box {
					border-radius: <?php echo $border_radius; ?>px;
					-moz-border-radius: <?php echo $border_radius; ?>px;
					<?php if (!function_exists('symposium_profile')) {
						echo 'display: none';
					}?>
				}
				.symposium-friends-box-none {
					background-image:url('<?php echo $plugin; ?>/images/friends.gif');
				}
				.symposium-friends-box-new {
					background-image:url('<?php echo $plugin; ?>/images/friendsnew.gif');
				}
				.corners {
					border-radius: <?php echo $border_radius; ?>px;
					-moz-border-radius: <?php echo $border_radius; ?>px;
				}
			</style>
			
			<!-- NOTIFICATION BAR -->
			<div id="symposium-notification-bar">
				<div id="icons" style="float: left">
					<?php
			        echo '<a href="http://www.wpsymposium.com" target="_blank"><img src="http://www.wpsymposium.com/wp-content/plugins/wp-symposium/images/icon_logo.gif" alt="'.__('Powered by WP Symposium', 'wp-symposium').'" title="'.__('Powered by WP Symposium', 'wp-symposium').'" /></a> ';
			        if ($config->bar_label != '') {
				        echo $config->bar_label." - ";
			        }
			        echo __('Powered by WP Symposium', 'wp-symposium');
					?>
				</div>
	
				<?php if (is_user_logged_in()) {
					// Pending Friends
					if (function_exists('symposium_profile')) {
						echo "<div id='symposium-friends-box' title='".__("Go to Friends", "wp-symposium")."' class='symposium-friends-box symposium-friends-box-none'>";
					} else {
						echo "<div id='symposium-friends-box' style='display:none'>";
					}
					echo "</div>";
					
					// Unread Mail
					if (function_exists('symposium_mail')) {
						echo "<div id='symposium-email-box' title='".__("Go to Mail", "wp-symposium")."' class='symposium-email-box symposium-email-box-read'>";
					} else {
						echo "<div id='symposium-email-box' style='display:none'>";
					}
					echo "</div>";
	
					// Friends Status/Online
					echo "<div id='symposium-online-box' class='symposium-online-box-none'></div>";
				} ?>
	
				<!-- DIV for notification alerts -->
				<div id="alerts"></div>
				
				<!-- DIV for login/logout/etc links -->
				<div id="info">
					<?php
					if (is_user_logged_in()) {


						_e('Logged in as', 'wp-symposium');
					    
						if ($use_wp_profile == 'on') {
							echo ' <a href="/wp-admin/profile.php">'.$current_user->user_login.'</a>';
						} else {
							echo ' <a href="'.symposium_get_url("profile").'">'.$current_user->user_login.'</a>';
						}
						
						if (current_user_can('activate_plugins')) {
							echo wp_register('.&nbsp;', '');
						}
						
						echo '.&nbsp;';
						if ($use_wp_login == "on") {
							wp_loginout( '/index.php' );
						} else {
							echo '<a href="'.$custom_login_url.'">'.__('Log out', 'wp-symposium').'</a>';
						}
						echo '.&nbsp;';
													
					} else {
						
						if ($use_wp_login == "on") {
							echo "<a href=".wp_login_url( get_permalink() )." class='simplemodal-login' title='".__("Login", "wp-symposium")."'>".__("Login", "wp-symposium")."</a>";
						} else {
							echo '<a href="'.$custom_login_url.'?redirect_to='.get_permalink().'">'.__("Login", "wp-symposium").'</a>';
						}

						if ($use_wp_register == "on") {
							echo wp_register('&nbsp;', '');
						} else {
							echo '&nbsp;<a href="'.$custom_register_url.'">'.__("Register", "wp-symposium").'</a>';
						}


					}
	
					?>	
				</div>
				
			</div>
			
			<?php if (is_user_logged_in()) {
	
				echo "<div id='symposium-chatboxes'>";
		
					// DIV for who's online
					echo "<div id='symposium-who-online'>";
					
						echo "<div id='symposium-who-online_header' style='width:176px;height:18px;padding:2px;background-color:#000;color:#fff;'>";
							echo "<div id='symposium-who-online_close' style='float:right;cursor:pointer;width:18px; text-align:center'><img src='".$plugin."/images/delete.png' alt='".__("Close", "wp-symposium")."' /></div>";
						_e("Friends Status", "wp-symposium");
						echo "</div>";
						echo "<div id='symposium-friends-online-list'>";
						echo "</div>";
														
					echo "</div>";
					
					// set up chat windows (should match numChatWindows in function do_chat_check() in symposium_bar.js)
					if ($use_chat == 'on') {
						for ($w = 1; $w <= 3; $w++) {
							addChatWindow($w);
						}
					}
					
				echo "</div>";
			
			}
	
		}
	}

}  
add_action('wp_footer', 'add_notification_bar', 1);

/* ====================================================== PHP FUNCTIONS ====================================================== */

function addChatWindow($id) {
	
	$plugin = WP_PLUGIN_URL.'/wp-symposium';

	echo "<div id='chat".$id."' title='chat".$id."' class='chat_window' style='display:none'>";
		echo "<div id='chat".$id."_header' style='width:176px;height:18px;padding:2px;background-color:#000;color:#fff;'>";
		echo "<div id='chat".$id."_to' style='display:none'></div>";
		echo "<div id='chat".$id."_close' class='chat_close' style='float:right;cursor:pointer;width:18px; text-align:center'><img src='".$plugin."/images/delete.png' alt='Close' /></div>";
		echo "<div id='chat".$id."_display_name'></div>";
		echo "</div>";
		echo "<div id='chat".$id."_message' style='width:176px; height:170px;overflow:auto;padding:2px;padding-bottom:7px;'>";
		echo "</div>";
		echo "<div style='width:180px; height:40px;'>";
			echo "<textarea id='chat".$id."_textarea' class='chat_message' onclick='if (this.value == \"Type here...\") { this.value=\"\"; }' style='background-color:#efefef;border:0px;width:176px;height:34px;'>Type here...</textarea>";
		echo "</div>";
	echo "</div>";
	
}


?>
