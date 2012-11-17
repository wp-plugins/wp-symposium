<?php
/*
Plugin Name: WP Symposium Panel
Plugin URI: http://www.wpsymposium.com
Description: Panel bottom corner of screen to display new mail, friends online, etc. Also controls live chat windows, chatroom and online status. Simply activate to add.
Version: 12.11
Author: Simon Goodchild
Author URI: http://www.wpsymposium.com
License: Commercial
Requires at least: WordPress 3.0 and WP Symposium 11.8.21
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

// Get constants
require_once(dirname(__FILE__).'/default-constants.php');

// Adds notification bar
function __wps__add_notification_bar()  
{  

   	global $wpdb, $current_user;
	wp_get_current_user();

	$plugin = WPS_PLUGIN_URL;

	if ( is_user_logged_in() ) {

		$use_chat = get_option(WPS_OPTIONS_PREFIX.'_use_chat');
		$use_chatroom = get_option(WPS_OPTIONS_PREFIX.'_use_chatroom');
		if (get_option(WPS_OPTIONS_PREFIX.'_wps_lite')) { 
			$use_chat = ''; 
			$use_chatroom = '';
		}
		$bar_polling = (get_option(WPS_OPTIONS_PREFIX.'_bar_polling')*1000);
		$chat_polling = (get_option(WPS_OPTIONS_PREFIX.'_chat_polling')*1000);
		$use_wp_profile = get_option(WPS_OPTIONS_PREFIX.'_use_wp_profile');
		$inactive = get_option(WPS_OPTIONS_PREFIX.'_online');
		$offline = get_option(WPS_OPTIONS_PREFIX.'_offline');
		if (get_option(WPS_OPTIONS_PREFIX.'_use_styles') == "on") {
			$border_radius = get_option(WPS_OPTIONS_PREFIX.'_border_radius');
		}

		// maximum number of chat windows
		$maxChatWindows = 3;

		?>
			
		<style>

			<?php if (get_option(WPS_OPTIONS_PREFIX.'_wps_lite')) { ?>
				#__wps__notification_bar {
					width: 107px;
				}
				#__wps__chatboxes {
					right:115px;
				}
			<?php } ?>

			#__wps__chatboxes {
				z-index: 99999999;
			}
										
			.__wps__online_box {
				border-radius: <?php echo $border_radius; ?>px;
				-moz-border-radius: <?php echo $border_radius; ?>px;
				<?php if (!function_exists('__wps__profile')) {
					echo 'display: none';
				}?>
			}
			.__wps__online_box-none {
				border-radius: <?php echo $border_radius; ?>px;
				-moz-border-radius: <?php echo $border_radius; ?>px;
				<?php if (!function_exists('__wps__profile')) {
					echo 'display: none';
				}?>
			}
			
			#__wps__logout {
				background-image:url('<?php echo get_option(WPS_OPTIONS_PREFIX.'_images'); ?>/logout.gif');
				border-radius: <?php echo $border_radius; ?>px;
				-moz-border-radius: <?php echo $border_radius; ?>px;
			}
							
			#__wps__chatroom-box {
				border-radius: <?php echo $border_radius; ?>px;
				-moz-border-radius: <?php echo $border_radius; ?>px;
			}
			.__wps__chatroom-new {
				background-image:url('<?php echo get_option(WPS_OPTIONS_PREFIX.'_images'); ?>/chatroomnew.gif');
			}
			.__wps__chatroom-none {
				background-image:url('<?php echo get_option(WPS_OPTIONS_PREFIX.'_images'); ?>/chatroom.gif');
			}
			
			.__wps__email_box {
				border-radius: <?php echo $border_radius; ?>px;
				-moz-border-radius: <?php echo $border_radius; ?>px;
				<?php if (!function_exists('__wps__mail')) {
					echo 'display: none';
				}?>
			}
			.__wps__email_box-read {
				background-image:url('<?php echo get_option(WPS_OPTIONS_PREFIX.'_images'); ?>/email.gif');
			}
			.__wps__email_box-unread {
				background-image:url('<?php echo get_option(WPS_OPTIONS_PREFIX.'_images'); ?>/emailunread.gif');
			}

			.__wps__friends_box {
				border-radius: <?php echo $border_radius; ?>px;
				-moz-border-radius: <?php echo $border_radius; ?>px;
				<?php if (!function_exists('__wps__profile')) {
					echo 'display: none';
				}?>
			}
			.__wps__friends_box-none {
				background-image:url('<?php echo get_option(WPS_OPTIONS_PREFIX.'_images'); ?>/friends.gif');
			}
			.__wps__friends_box-new {
				background-image:url('<?php echo get_option(WPS_OPTIONS_PREFIX.'_images'); ?>/friendsnew.gif');
			}
			.corners {
				border-radius: <?php echo $border_radius; ?>px;
				-moz-border-radius: <?php echo $border_radius; ?>px;
			}
		</style>

		
		<?php
		
		
		echo "<!-- NOTIFICATION BAR -->";

			if (is_user_logged_in()) {
				
				echo "<div id='__wps__chatboxes' style='width:100%;'>";
									
					// DIV for who's online
					echo "<div id='__wps__who_online'>";
					
						echo "<div id='__wps__who_online_header' style='width:172px;height:24px;padding:4px 4px 0px 4px;background-color:#000;color:#fff;'>";
							echo "<div id='__wps__who_online_close' style='float:right;cursor:pointer;width:18px; text-align:center'><img src='".get_option(WPS_OPTIONS_PREFIX.'_images')."/close.png' alt='".__("Close", WPS_TEXT_DOMAIN)."' /></div>";
							echo sprintf(__("%s Status", WPS_TEXT_DOMAIN), get_option(WPS_OPTIONS_PREFIX.'_alt_friends'));
						echo "</div>";
						echo "<div id='__wps__friends_online_list'>";
						echo "</div>";
														
					echo "</div>";
					
					// DIV for chat room
					if ($use_chatroom == 'on') {
						echo "<div id='__wps__chatroom'>";
						
							echo "<div id='__wps__chatroom_header' class='__wps__readChat'>";
								echo "<div id='__wps__chatroom_close' style='float:right;cursor:pointer;width:18px; text-align:center'><img src='".get_option(WPS_OPTIONS_PREFIX.'_images')."/close.png' alt='".__("Close", WPS_TEXT_DOMAIN)."' /></div>";
								echo "<div id='__wps__chatroom_small' style='margin-right:5px;float:right;cursor:pointer;'><img src='".get_option(WPS_OPTIONS_PREFIX.'_images')."/min.gif' title='Minimize' /></div>";
								echo "<div id='__wps__chatroom_big' style='display:none; margin-right:5px;float:right;cursor:pointer;'><img src='".get_option(WPS_OPTIONS_PREFIX.'_images')."/max.gif' title='Maximize' /></div>";
								if (__wps__get_current_userlevel() == 5) {
									echo "<div id='__wps__chatroom_clear' style='margin-right:5px;float:right;cursor:pointer;'>".__("Clear all", WPS_TEXT_DOMAIN)."</div>";
								}
							_e("Chat Room (visible to all)", WPS_TEXT_DOMAIN);
							echo "</div>";
							echo "<div id='chatroom_messages'>";
							echo "</div>";
							echo "<div id='chatroom_typing_area'>";
								echo "<textarea id='__wps__chatroom_textarea' class='chatroom_message' onclick='if (this.value == \"".__("Type here...", WPS_TEXT_DOMAIN)."\") { this.value=\"\"; }'>".__("Type here...", WPS_TEXT_DOMAIN)."</textarea>";
							echo "</div>";
																					
						echo "</div>";
					}
					
					// set up chat windows (should match numChatWindows in function do_chat_check() in symposium_bar.js)
					if ($use_chat == 'on') {
						for ($w = 1; $w <= 3; $w++) {
							__wps__addChatWindow($w);
						}
					}
					
				echo "</div>";

				// Logout button DIV
				echo "<div id='__wps__logout_div'>";
					echo "<div id='__wps__online_status_div'>";
						echo "<input type='checkbox' id='__wps__online_status' ";
						if (__wps__get_meta($current_user->ID, 'status') == "offline") { echo " CHECKED"; }
						echo "> ".__("Appear offline?", WPS_TEXT_DOMAIN);
					echo "</div>";
					echo "<div id='__wps__online_status_div'>";
						echo "<img style='float: left; margin-left: 1px; margin-right: 5px;' src='".get_option(WPS_OPTIONS_PREFIX.'_images')."/close.png' alt='".__("Logout", WPS_TEXT_DOMAIN)."' />";
						echo "<a id='__wps__logout-link' href='javascript:void(0);'>".__("Logout", WPS_TEXT_DOMAIN)."</a>";
					echo "</div>";
				echo "</div>";

				echo '<div id="__wps__notification_bar" >';

					// Log out
					echo "<div id='__wps__logout'></div>";

					// Chat room
					if ($use_chatroom == 'on') {
						echo "<div id='__wps__chatroom-box' class='__wps__chatroom-none'></div>";
					}

					// Pending Friends
					if (function_exists('__wps__profile')) {
						echo "<div id='__wps__friends_box' title='".sprintf(__("Go to %s", WPS_TEXT_DOMAIN), get_option(WPS_OPTIONS_PREFIX.'_alt_friends'))."' class='__wps__friends_box __wps__friends_box-none'>";
					} else {
						echo "<div id='__wps__friends_box' style='display:none'>";
					}
					echo "</div>";
					
					// Unread Mail
					if (function_exists('__wps__mail')) {
						echo "<div id='__wps__email_box' title='".__("Go to Mail", WPS_TEXT_DOMAIN)."' class='__wps__email_box __wps__email_box-read'>";
					} else {
						echo "<div id='__wps__email_box' style='display:none'>";
					}
					echo "</div>";
	
					// Friends Status/Online
					echo "<div id='__wps__online_box' class='__wps__online_box-none'></div>";
							
			echo "</div>";

		} 	

	}

}  

if (!is_admin()) {
	add_action('wp_footer', '__wps__add_notification_bar', 1);
}

/* ====================================================== PHP FUNCTIONS ====================================================== */

function __wps__addChatWindow($id) {
	
	$plugin = WPS_PLUGIN_URL;

	echo "<div id='chat".$id."' title='chat".$id."' class='__wps__chat_window' style='clear:none; display:none;'>";
		echo "<div id='chat".$id."_header' class='chat_header __wps__readChat'>";
		echo "<div id='chat".$id."_to' style='display:none'></div>";
		echo "<div id='chat".$id."_close' class='chat_close' style='float:right;cursor:pointer;width:18px; text-align:center'><img src='".get_option(WPS_OPTIONS_PREFIX.'_images')."/close.png' alt='Close' /></div>";
		echo "<div class='__wps__chat_small' style='margin-right:5px;float:right;cursor:pointer;'><img src='".get_option(WPS_OPTIONS_PREFIX.'_images')."/min.gif' title='Minimize' /></div>";
		echo "<div class='__wps__chat_big' style='display:none; margin-right:5px;float:right;cursor:pointer;'><img src='".get_option(WPS_OPTIONS_PREFIX.'_images')."/max.gif' title='Maximize' /></div>";
		echo "<div id='chat".$id."_display_name'></div>";
		echo "</div>";
		echo "<div id='chat".$id."_message' class='__wps__chat_messages'>";
		echo "</div>";
		echo "<div style='width:180px; height:40px;'>";
			echo "<textarea id='chat".$id."_textarea' class='__wps__chat_message' onclick='if (this.value == \"".__("Type here...", WPS_TEXT_DOMAIN)."\") { this.value=\"\"; }'>".__("Type here...", WPS_TEXT_DOMAIN)."</textarea>";
		echo "</div>";
	echo "</div>";
	
}


?>
