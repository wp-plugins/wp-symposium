<?php
/*
Plugin Name: WP Symposium Reply-by-Email
Plugin URI: http://www.wpsymposium.com
Description: <strong><a href="http://wpswiki.com/index.php?title=Bronze_membership">BRONZE PLUGIN</a></strong>. Allows replies to forum notifications by email.
Version: 12.11
Author: Simon Goodchild
Author URI: http://www.wpsymposium.com
License: Commercial
Requires at least: WordPress 3.0 and WP Symposium 12.03.24
*/

define('WPS_LIST_VER', '12.11');
	
/*  Copyright 2010,2011,2012  Simon Goodchild  (info@wpsymposium.com)

EULA stands for End User Licensing Agreement. This is the agreement through which the software is licensed to the software user. 

END-USER LICENSE AGREEMENT FOR MAILING LIST PLUGIN

IMPORTANT PLEASE READ THE TERMS AND CONDITIONS OF THIS LICENSE AGREEMENT CAREFULLY BEFORE CONTINUING WITH THIS PROGRAM 

INSTALL: Web Technology Solutions Ltd End-User License Agreement ("EULA") is a legal agreement between you (either an individual or a single entity) and Web Technology Solutions Ltd, for the software product(s) identified above which may include associated software components, media, printed materials, and "online" or electronic documentation ("SOFTWARE PRODUCT"). 

By installing, copying, or otherwise using the SOFTWARE PRODUCT, you agree to be bound by the terms of this EULA. This license agreement represents the entire agreement concerning the program between you and Web Technology Solutions Ltd, (referred to as "licenser"), and it supersedes any prior proposal, representation, or understanding between the parties. If you do not agree to the terms of this EULA, do not install or use the SOFTWARE PRODUCT.

The SOFTWARE PRODUCT is protected by copyright laws and international copyright treaties, as well as other intellectual property laws and treaties. 

The SOFTWARE PRODUCT is licensed, not sold.

1. GRANT OF LICENSE. 
The SOFTWARE PRODUCT is licensed as follows: 
(a) Installation and Use.
Web Technology Solutions Ltd grants you the right to install and use copies of the SOFTWARE PRODUCT on your computer running a validly licensed copy of the operating system for which the SOFTWARE PRODUCT was designed.
(b) Backup Copies.
You may also make copies of the SOFTWARE PRODUCT as may be necessary for backup and archival purposes.

2. DESCRIPTION OF OTHER RIGHTS AND LIMITATIONS.
(a) Maintenance of Copyright Notices.
You must not remove or alter any copyright notices on any and all copies of the SOFTWARE PRODUCT.
(b) Distribution.
You may not distribute registered copies of the SOFTWARE PRODUCT to third parties. Evaluation versions available for download from Web Technology Solutions Ltd's websites may be freely distributed.
(c) Prohibition on Reverse Engineering, Decompilation, and Disassembly.
You may not reverse engineer, decompile, or disassemble the SOFTWARE PRODUCT, except and only to the extent that such activity is expressly permitted by applicable law notwithstanding this limitation. 
(d) Rental.
You may not rent, lease, or lend the SOFTWARE PRODUCT.
(e) Support Services.
Web Technology Solutions Ltd may provide you with support services related to the SOFTWARE PRODUCT ("Support Services"). Any supplemental software code provided to you as part of the Support Services shall be considered part of the SOFTWARE PRODUCT and subject to the terms and conditions of this EULA. 
(f) Compliance with Applicable Laws.
You must comply with all applicable laws regarding use of the SOFTWARE PRODUCT.

3. TERMINATION 
Without prejudice to any other rights, Web Technology Solutions Ltd may terminate this EULA if you fail to comply with the terms and conditions of this EULA. In such event, you must destroy all copies of the SOFTWARE PRODUCT in your possession.

4. COPYRIGHT
All title, including but not limited to copyrights, in and to the SOFTWARE PRODUCT and any copies thereof are owned by Web Technology Solutions Ltd or its suppliers. All title and intellectual property rights in and to the content which may be accessed through use of the SOFTWARE PRODUCT is the property of the respective content owner and may be protected by applicable copyright or other intellectual property laws and treaties. This EULA grants you no rights to use such content. All rights not expressly granted are reserved by Web Technology Solutions Ltd.

5. NO WARRANTIES
Web Technology Solutions Ltd expressly disclaims any warranty for the SOFTWARE PRODUCT. The SOFTWARE PRODUCT is provided 'As Is' without any express or implied warranty of any kind, including but not limited to any warranties of merchantability, noninfringement, or fitness of a particular purpose. Web Technology Solutions Ltd does not warrant or assume responsibility for the accuracy or completeness of any information, text, graphics, links or other items contained within the SOFTWARE PRODUCT. Web Technology Solutions Ltd makes no warranties respecting any harm that may be caused by the transmission of a computer virus, worm, time bomb, logic bomb, or other such computer program. Web Technology Solutions Ltd further expressly disclaims any warranty or representation to Authorized Users or to any third party.

6. LIMITATION OF LIABILITY
In no event shall Web Technology Solutions Ltd be liable for any damages (including, without limitation, lost profits, business interruption, or lost information) rising out of 'Authorized Users' use of or inability to use the SOFTWARE PRODUCT, even if Web Technology Solutions Ltd has been advised of the possibility of such damages. In no event will Web Technology Solutions Ltd be liable for loss of data or for indirect, special, incidental, consequential (including lost profit), or other damages based in contract, tort or otherwise. Web Technology Solutions Ltd shall have no liability with respect to the content of the SOFTWARE PRODUCT or any part thereof, including but not limited to errors or omissions contained therein, libel, infringements of rights of publicity, privacy, trademark rights, business interruption, personal injury, loss of privacy, moral rights or the disclosure of confidential information.

*/

// Get constants
require_once(dirname(__FILE__).'/default-constants.php');


/* ====================================================================== MAIN =========================================================================== */



// get any waiting emails and act upon them
function __wps__mailinglist() {

}


// add custom time to cron
function __wps__mailinglist_filter_cron_schedules( $schedules ) {
	$schedules['__wps__mailinglist_interval'] = array(
		'interval' => get_option(WPS_OPTIONS_PREFIX.'_mailinglist_cron'),
		'display' => sprintf(__('%s reply-by-email interval', WPS_TEXT_DOMAIN), WPS_WL)
	);
	return $schedules;
}
add_filter( 'cron_schedules', '__wps__mailinglist_filter_cron_schedules' );

// send automatic scheduled email
if ( !wp_next_scheduled('__wps__mailinglist_hook') ) {
	wp_schedule_event( time(), '__wps__mailinglist_interval', '__wps__mailinglist_hook' ); // Schedule event
}

// This is what is run
function __wps__mailinglist_hook_function() {
	__wps__check_pop3(false);
}
add_action('__wps__mailinglist_hook', '__wps__mailinglist_hook_function');
 

function __wps__check_pop3($output=false) {
	
	if (function_exists('__wps__mailinglist')) {
		
		if ($_SESSION['__wps__mailinglist_lock'] != 'locked') {
			
			$_SESSION['__wps__mailinglist_lock'] = 'locked';
			
			require_once(WPS_PLUGIN_DIR.'/class.wps_forum.php');
			$wps_forum = new wps_forum();
			
			global $wpdb;
			
			if ($output) echo '<h3>'.__('Checking for waiting email...', WPS_TEXT_DOMAIN).'</h3>';
		
			$server = get_option(WPS_OPTIONS_PREFIX.'_mailinglist_server');
			$port = get_option(WPS_OPTIONS_PREFIX.'_mailinglist_port');
			$username = get_option(WPS_OPTIONS_PREFIX.'_mailinglist_username');
			$password = get_option(WPS_OPTIONS_PREFIX.'_mailinglist_password');
			
			if ($mbox = imap_open ("{".$server.":".$port."/pop3}INBOX", $username, $password) ) {
				
				if ($output) echo __('Connected', WPS_TEXT_DOMAIN).', ';
				
				$num_msg = imap_num_msg($mbox);
				if ($output) echo __('number of messages found', WPS_TEXT_DOMAIN).': '.$num_msg.'<br /><br />';
		
				$carimap = array("=C3=A9", "=C3=A8", "=C3=AA", "=C3=AB", "=C3=A7", "=C3=A0", "=20", "=C3=80", "=C3=89", "\n", "> ");
				$carhtml = array("é", "è", "ê", "ë", "ç", "à", "&nbsp;", "À", "É", "<br>", "");
				
				if ($num_msg > 0) {
					
					if ($output) {
						echo '<table class="widefat">';
						echo '<thead>';
						echo '<tr>';
						echo '<th style="font-size:1.2em">'.__('From', WPS_TEXT_DOMAIN).'</th>';
						echo '<th style="font-size:1.2em">'.__('Date', WPS_TEXT_DOMAIN).'</th>';
						echo '<th style="font-size:1.2em">'.__('Topic ID', WPS_TEXT_DOMAIN).'</th>';
						echo '<th style="font-size:1.2em" width="50%">'.__('Snippet', WPS_TEXT_DOMAIN).'</th>';
						echo '</tr>';
						echo '</thead>';
						echo '<tbody>';
					}

					for ($i = 1; $i <= $num_msg; ++$i) {

						// Get email info
						$header = imap_header($mbox, $i);
		        		$prettydate = date("jS F Y H:i:s", $header->udate);
		        		$email = $header->from[0]->mailbox.'@'.$header->from[0]->host;
						$subject = $header->subject;
						
						// check email address is a registered email address
						$sql = "SELECT ID FROM ".$wpdb->base_prefix."users WHERE user_email = %s";
						$emailcheck = $wpdb->get_var($wpdb->prepare($sql, $email));
						
						if ($emailcheck) {						
		
							// Note user iD
							$uid = $emailcheck;
						
							$x = strpos($subject, '#TID=');
							if ($x !== FALSE) {
								
								// Get TID and continue
								$tid = substr($subject, $x+5, 1000);
								$x = strpos($tid, ' ');
								$tid = substr($tid, 0, $x);
								
								$sql = "SELECT tid FROM ".$wpdb->prefix."symposium_topics WHERE tid = %d";
								$tidcheck = $wpdb->get_var($wpdb->prepare($sql, $tid));
								
								if ($tidcheck) {
									
									// Get message to add as a reply					
									$body = imap_fetchbody($mbox, $i, "1.1");
									if ($body == "") {
									    $body = imap_fetchbody($mbox, $i, "1");
									}
									$body = quoted_printable_decode($body);
									$body = imap_utf8($body);
					  				$body = str_replace($carimap, $carhtml, $body);
					
									$divider = get_option(WPS_OPTIONS_PREFIX.'_mailinglist_divider');
									$divider_bottom = get_option(WPS_OPTIONS_PREFIX.'_mailinglist_divider_bottom');
									$x = strpos($body, $divider);
									$y = strpos($body, $divider_bottom);
									
									if ($x && $y) {
					
										$body = substr($body, $x+strlen($divider), strlen($body)-$x-strlen($divider)-1);
										$x = strpos($body, $divider_bottom);
										$body = trim(quoted_printable_decode(substr($body, 0, $x)));
										if (substr($body, 0, 4) == '<br>') { $body = substr($body, 4, strlen($body)-4); }
										
										// Replace <script> tags
										if (strpos($body, '<') !== FALSE) { str_replace('<', '&lt;', $body); }
										if (strpos($body, '>') !== FALSE) { str_replace('>', '&gt;', $body); }
										
										$snippet = trim(substr(quoted_printable_decode($body), 0, 100));
	
										// get category for topic
										$sql = "SELECT topic_category from ".$wpdb->prefix."symposium_topics WHERE tid = %d";
										$cid = $wpdb->get_var($wpdb->prepare($sql, $tid));
										
										// First check if it's already been inserted
										$sql = "SELECT lid FROM ".$wpdb->prefix."symposium_mailinglist_log WHERE tid = %d AND uid = %d and topic_post = %s";
										$checklog = $wpdb->get_var($wpdb->prepare($sql, $tid, $uid, $body));
										
										if (!$checklog) {
											
											// insert as a new reply
											if ($wps_forum->add_reply($tid, $body, $uid)) {
	
												$snippet .= '...';
	
											} else {
												
												$snippet = '<span style="color:red">'.__('Failed to add to forum', WPS_TEXT_DOMAIN).' '.$tid.'</span>';
												$snippet .= '<br>'.$subject;
												
											}	
																				        
										}

				        				// Delete from mailbox
										imap_delete($mbox, $i);
														
									} else {
										
										$snippet = '<span style="color:red">'.__('Empty reply. No boundaries found', WPS_TEXT_DOMAIN).'</span>';
										
									}
									
									
								} else {
		
									$tid = '<span style="color:red">'.__('Topic ID not found', WPS_TEXT_DOMAIN).': '.$tid.'</span>';
									$snippet = $subject;
									
								}
								
							} else {
								
								$tid = '<span style="color:red">'.__('No TID found in subject', WPS_TEXT_DOMAIN).'.</span>';
								$snippet = '';
								
							}
							
							
						} else {
							
							$email = '<span style="color:red">'.$email.' '.__('not found in users', WPS_TEXT_DOMAIN).'.</span>';
							$tid = '';
							$snippet = '';
							
						}
		
						if ($output) {
							echo '<tr>';
							echo '<td>'.$email.'</td>';
							echo '<td>'.$prettydate.'</td>';
							echo '<td>'.$tid.'</td>';
							echo '<td>'.$snippet.'</td>';
							echo '</tr>';
						}
		
					}
					    		
					if ($output) echo '</tbody></table>';
											
				} else {
					
					if ($output) echo __('No messages found', WPS_TEXT_DOMAIN).'.';
					
				}

				// purge deleted mail
				imap_expunge($mbox);
				// close the mailbox
				imap_close($mbox); 
				
			} else {
			
				if ($output) echo __('Problem connecting to mail server', WPS_TEXT_DOMAIN).': ' . imap_last_error().' '.__('(or no internet connection)', WPS_TEXT_DOMAIN).'.<br />';		
				if ($output) echo __('Check your mail server address and port number, username and password', WPS_TEXT_DOMAIN).'.';
				
			}
			
			$_SESSION['__wps__mailinglist_lock'] = '';
			
		} else {
			if ($output) echo __('Currently processing, please try again in a few minutes.', WPS_TEXT_DOMAIN).'.<br />';		
		}
	}
}
	

// ----------------------------------------------------------------------------------------------------------------------------------------------------------


if ( ( get_option(WPS_OPTIONS_PREFIX."_mailinglist_version") != WPS_LIST_VER && is_admin()) ) {

 	// Update Version *******************************************************************************
	update_option(WPS_OPTIONS_PREFIX."_mailinglist_version", WPS_LIST_VER);
	__wps__mailinglist_activate();	
}

// Add "Alerts" to admin menu via hook
function __wps__add_mailinglist_to_admin_menu()
{
	$hidden = get_option(WPS_OPTIONS_PREFIX.'_long_menu') == "on" ? '_hidden': '';
	add_submenu_page('symposium_debug'.$hidden, __('Reply by Email', WPS_TEXT_DOMAIN), __('Reply by Email', WPS_TEXT_DOMAIN), 'manage_options', WPS_DIR.'/mailinglist_admin.php');
}
add_action('__wps__admin_menu_hook', '__wps__add_mailinglist_to_admin_menu');

// Add row to installation page showing status of the Alerts plugin through hook provided
function __wps__add_mailinglist_installation_row()
{
	__wps__install_row(__('Reply_by_Email', WPS_TEXT_DOMAIN).' v'.get_option(WPS_OPTIONS_PREFIX."_mailinglist_version"), '', '__wps__news_main', '-', 'wp-symposium/mailinglist.php', 'admin.php?page='.WPS_DIR.'/symposium_mailinglist_admin.php', 
	__('The Reply-by-email plugin must be installed in ', WPS_TEXT_DOMAIN).WPS_PLUGIN_DIR);
}
add_action('__wps__installation_hook', '__wps__add_mailinglist_installation_row');

// ----------------------------------------------------------------------------------------------------------------------------------------------------------

function __wps__mailinglist_activate() {

	global $wpdb;

	if (get_option(WPS_OPTIONS_PREFIX.'_mailinglist_server') === false)
		update_option(WPS_OPTIONS_PREFIX.'_mailinglist_server', 'mail.example.com');
	if (get_option(WPS_OPTIONS_PREFIX.'_mailinglist_port') === false)
		update_option(WPS_OPTIONS_PREFIX.'_mailinglist_port', 110);
	if (get_option(WPS_OPTIONS_PREFIX.'_mailinglist_username') === false)
		update_option(WPS_OPTIONS_PREFIX.'_mailinglist_username', 'username');
	if (get_option(WPS_OPTIONS_PREFIX.'_mailinglist_password') === false)
		update_option(WPS_OPTIONS_PREFIX.'_mailinglist_password', '');
	if (get_option(WPS_OPTIONS_PREFIX.'_mailinglist_prompt') === false)
		update_option(WPS_OPTIONS_PREFIX.'_mailinglist_prompt', 'To reply, enter your reply text between the two lines of stars below, everything else will be ignored!');
	if (get_option(WPS_OPTIONS_PREFIX.'_mailinglist_divider') === false)
		update_option(WPS_OPTIONS_PREFIX.'_mailinglist_divider', 'ENTER TEXT BELOW HERE **********');
	if (get_option(WPS_OPTIONS_PREFIX.'_mailinglist_divider_bottom') === false)
		update_option(WPS_OPTIONS_PREFIX.'_mailinglist_divider_bottom', 'ENTER TEXT ABOVE HERE **********');
	if (get_option(WPS_OPTIONS_PREFIX.'_mailinglist_cron') === false)
		update_option(WPS_OPTIONS_PREFIX.'_mailinglist_cron', 900);
	if (get_option(WPS_OPTIONS_PREFIX.'_mailinglist_from') === false)
		update_option(WPS_OPTIONS_PREFIX.'_mailinglist_from', 'forum@example.com');
		
}
function __wps__mailinglist_deactivate() {
	$_SESSION['__wps__mailinglist_lock'] = 'locked';
	wp_clear_scheduled_hook('__wps__mailinglist_hook');
	$_SESSION['__wps__mailinglist_lock'] = '';
}

function __wps__mailinglist_uninstall() {}

register_activation_hook(__FILE__,'__wps__mailinglist_activate');
register_deactivation_hook(__FILE__, '__wps__mailinglist_deactivate');
register_uninstall_hook(__FILE__, '__wps__mailinglist_uninstall');

