<?php
/*
Plugin Name: WP Symposium Panel
Plugin URI: http://www.wpsymposium.com
Description: Panel bottom corner of screen to display new mail, friends online, etc. Also controls live chat windows, chatroom and online status. Simply activate to add.
Version: 12.06.23.1
Author: WP Symposium
Author URI: http://www.wpsymposium.com
License: GPL3
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

   	global $wpdb, $current_user;
	wp_get_current_user();

	$plugin = WP_PLUGIN_URL.'/wp-symposium';

	if ( is_user_logged_in() ) {

		$use_chat = get_option('symposium_use_chat');
		$use_chatroom = get_option('symposium_use_chatroom');
		if (get_option('symposium_wps_lite')) { 
			$use_chat = ''; 
			$use_chatroom = '';
		}
		$bar_polling = (get_option('symposium_bar_polling')*1000);
		$chat_polling = (get_option('symposium_chat_polling')*1000);
		$use_wp_profile = get_option('symposium_use_wp_profile');
		$inactive = get_option('symposium_online');
		$offline = get_option('symposium_offline');
		if (get_option('symposium_use_styles') == "on") {
			$border_radius = get_option('symposium_border_radius');
		}

		// maximum number of chat windows
		$maxChatWindows = 3;

		?>
			
		<style>

			<?php if (get_option('symposium_wps_lite')) { ?>
				#symposium-notification-bar {
					width: 107px;
				}
				#symposium-chatboxes {
					right:115px;
				}
			<?php } ?>

			#symposium-chatboxes {
				z-index: 99999999;
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
			
			#symposium-logout {
				background-image:url('<?php echo get_option('symposium_images'); ?>/logout.gif');
				border-radius: <?php echo $border_radius; ?>px;
				-moz-border-radius: <?php echo $border_radius; ?>px;
			}
							
			#symposium-chatroom-box {
				border-radius: <?php echo $border_radius; ?>px;
				-moz-border-radius: <?php echo $border_radius; ?>px;
			}
			.symposium-chatroom-new {
				background-image:url('<?php echo get_option('symposium_images'); ?>/chatroomnew.gif');
			}
			.symposium-chatroom-none {
				background-image:url('<?php echo get_option('symposium_images'); ?>/chatroom.gif');
			}
			
			.symposium-email-box {
				border-radius: <?php echo $border_radius; ?>px;
				-moz-border-radius: <?php echo $border_radius; ?>px;
				<?php if (!function_exists('symposium_mail')) {
					echo 'display: none';
				}?>
			}
			.symposium-email-box-read {
				background-image:url('<?php echo get_option('symposium_images'); ?>/email.gif');
			}
			.symposium-email-box-unread {
				background-image:url('<?php echo get_option('symposium_images'); ?>/emailunread.gif');
			}

			.symposium-friends-box {
				border-radius: <?php echo $border_radius; ?>px;
				-moz-border-radius: <?php echo $border_radius; ?>px;
				<?php if (!function_exists('symposium_profile')) {
					echo 'display: none';
				}?>
			}
			.symposium-friends-box-none {
				background-image:url('<?php echo get_option('symposium_images'); ?>/friends.gif');
			}
			.symposium-friends-box-new {
				background-image:url('<?php echo get_option('symposium_images'); ?>/friendsnew.gif');
			}
			.corners {
				border-radius: <?php echo $border_radius; ?>px;
				-moz-border-radius: <?php echo $border_radius; ?>px;
			}
		</style>

		
		<?php
		
		
		echo "<!-- NOTIFICATION BAR -->";

			if (is_user_logged_in()) {
				
				echo "<div id='symposium-chatboxes' style='width:100%;'>";
									
					// DIV for who's online
					echo "<div id='symposium-who-online'>";
					
						echo "<div id='symposium-who-online_header' style='width:172px;height:24px;padding:4px 4px 0px 4px;background-color:#000;color:#fff;'>";
							echo "<div id='symposium-who-online_close' style='float:right;cursor:pointer;width:18px; text-align:center'><img src='".get_option('symposium_images')."/close.png' alt='".__("Close", "wp-symposium")."' /></div>";
						_e("Friends Status", "wp-symposium");
						echo "</div>";
						echo "<div id='symposium-friends-online-list'>";
						echo "</div>";
														
					echo "</div>";
					
					// DIV for chat room
					if ($use_chatroom == 'on') {
						echo "<div id='symposium-chatroom'>";
						
							echo "<div id='symposium-chatroom_header' class='symposium_readChat'>";
								echo "<div id='symposium-chatroom_close' style='float:right;cursor:pointer;width:18px; text-align:center'><img src='".get_option('symposium_images')."/close.png' alt='".__("Close", "wp-symposium")."' /></div>";
								echo "<div id='symposium-chatroom_small' style='margin-right:5px;float:right;cursor:pointer;'><img src='".get_option('symposium_images')."/min.gif' title='Minimize' /></div>";
								echo "<div id='symposium-chatroom_big' style='display:none; margin-right:5px;float:right;cursor:pointer;'><img src='".get_option('symposium_images')."/max.gif' title='Maximize' /></div>";
								if (symposium_get_current_userlevel() == 5) {
									echo "<div id='symposium-chatroom_clear' style='margin-right:5px;float:right;cursor:pointer;'>".__("Clear all", "wp-symposium")."</div>";
								}
							_e("Chat Room (visible to all)", "wp-symposium");
							echo "</div>";
							echo "<div id='chatroom_messages'>";
							echo "</div>";
							echo "<div id='chatroom_typing_area'>";
								echo "<textarea id='chatroom_textarea' class='chatroom_message' onclick='if (this.value == \"".__("Type here...", "wp-symposium")."\") { this.value=\"\"; }'>".__("Type here...", "wp-symposium")."</textarea>";
							echo "</div>";
																					
						echo "</div>";
					}
					
					// set up chat windows (should match numChatWindows in function do_chat_check() in symposium_bar.js)
					if ($use_chat == 'on') {
						for ($w = 1; $w <= 3; $w++) {
							addChatWindow($w);
						}
					}
					
				echo "</div>";

				// Logout button DIV
				echo "<div id='symposium-logout-div'>";
					echo "<div id='symposium-online-status-div'>";
						echo "<input type='checkbox' id='symposium-online-status' ";
						if (get_symposium_meta($current_user->ID, 'status') == "offline") { echo " CHECKED"; }
						echo "> ".__("Appear offline?", "wp-symposium");
					echo "</div>";
					echo "<div id='symposium-online-status-div'>";
						echo "<img style='float: left; margin-left: 1px; margin-right: 5px;' src='".get_option('symposium_images')."/close.png' alt='".__("Logout", "wp-symposium")."' />";
						echo "<a id='symposium-logout-link' href='javascript:void(0);'>".__("Logout", "wp-symposium")."</a>";
					echo "</div>";
				echo "</div>";

				echo '<div id="symposium-notification-bar" >';

					// Log out
					echo "<div id='symposium-logout'></div>";

					// Chat room
					if ($use_chatroom == 'on') {
						echo "<div id='symposium-chatroom-box' class='symposium-chatroom-none'></div>";
					}

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
							
			echo "</div>";

		} 	

	}

}  

if (!is_admin()) {
	add_action('wp_footer', 'add_notification_bar', 1);
}

/* ====================================================== PHP FUNCTIONS ====================================================== */

function addChatWindow($id) {
	
	$plugin = WP_PLUGIN_URL.'/wp-symposium';

	echo "<div id='chat".$id."' title='chat".$id."' class='chat_window' style='clear:none; display:none;'>";
		echo "<div id='chat".$id."_header' class='chat_header symposium_readChat'>";
		echo "<div id='chat".$id."_to' style='display:none'></div>";
		echo "<div id='chat".$id."_close' class='chat_close' style='float:right;cursor:pointer;width:18px; text-align:center'><img src='".get_option('symposium_images')."/close.png' alt='Close' /></div>";
		echo "<div class='symposium-chat_small' style='margin-right:5px;float:right;cursor:pointer;'><img src='".get_option('symposium_images')."/min.gif' title='Minimize' /></div>";
		echo "<div class='symposium-chat_big' style='display:none; margin-right:5px;float:right;cursor:pointer;'><img src='".get_option('symposium_images')."/max.gif' title='Maximize' /></div>";
		echo "<div id='chat".$id."_display_name'></div>";
		echo "</div>";
		echo "<div id='chat".$id."_message' class='chat_messages'>";
		echo "</div>";
		echo "<div style='width:180px; height:40px;'>";
			echo "<textarea id='chat".$id."_textarea' class='chat_message' onclick='if (this.value == \"".__("Type here...", "wp-symposium")."\") { this.value=\"\"; }'>".__("Type here...", "wp-symposium")."</textarea>";
		echo "</div>";
	echo "</div>";
	
}


?>
