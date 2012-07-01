<?php
/*
Plugin Name: WP Symposium Mobile
Plugin URI: http://www.wpsymposium.com
Description: <strong><a href="http://wpswiki.com/index.php?title=Bronze_membership">BRONZE PLUGIN</a></strong>. Mobile, SEO and Accessibility plugin compatible with WP Symposium. Activate and read instructions on Mobile tab on the <a href='admin.php?page=symposium_mobile_menu'>options page</a>.
Version: 12.07.01
Author: WP Symposium
Author URI: http://www.wpsymposium.com
License: Commercial
Requires at least: WordPress 3.0 and WP Symposium 11.8.21
*/

define('WPS_MOBILE_VER', '12.07.01');

	
/*  Copyright 2010,2011,2012  Simon Goodchild  (info@wpsymposium.com)

EULA stands for End User Licensing Agreement. This is the agreement through which the software is licensed to the software user. 

END-USER LICENSE AGREEMENT FOR WPS Groups 

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

/* ***************************************************** GROUP PAGE ***************************************************** */


global $wpdb;


// Function to WordPress knows this plugin is activated
function symposium_mobile()  
{  

	// Add to WP admin menu
	        			
	return 'wp-symposium';
	exit;
		
}

// Check for updates
if ( ( get_option("symposium_mobile_version") != WPS_MOBILE_VER && is_admin()) ) {

 	// Update Version *******************************************************************************
	update_option("symposium_mobile_version", WPS_MOBILE_VER);
	
}

// Add row to WPS installation page showing status of the Alerts plugin through hook provided
function add_mobile_installation_row()
{
	install_row('Mobile v'.get_option("symposium_mobile_version"), '', 'symposium_mobile', '-', 'wp-symposium/symposium-mobile.php', 'admin.php?page=symposium_mobile_menu', 
	__('The Mobile plugin must be installed in ', 'wp-symposium').WP_PLUGIN_DIR.'/wp-symposium.'.chr(10).chr(10).'Once activated, see further instructions on the WP Symposium -> Settings -> Mobile page.');
	
}
add_action('symposium_installation_hook', 'add_mobile_installation_row');

// Add plugin to WP Symposium admin menu via hook
function symposium_add_mobile_to_admin_menu()
{
	add_submenu_page('symposium_debug', __('Mobile', 'wp-symposium'), __('Mobile', 'wp-symposium'), 'manage_options', 'symposium_mobile_menu', 'symposium_mobile_menu');
}
add_action('symposium_admin_menu_hook', 'symposium_add_mobile_to_admin_menu');

function symposium_mobile_menu() {

		global $wpdb;
		
    	// See if the user has posted Mobile settings
		if( isset($_POST[ 'symposium_update' ]) && $_POST[ 'symposium_update' ] == 'symposium_mobile_menu' ) {
    	    	        
			update_option('symposium_mobile_topics', $_POST['mobile_topics']);
			echo "<div class='updated slideaway'><p>".__('Saved', 'wp-symposium').".</p></div>";

	    }
	 
	    // Get values from database  
		$mobile_topics = get_option('symposium_mobile_topics');

	  	echo '<div class="wrap">';

		  	echo '<div id="icon-themes" class="icon32"><br /></div>';
		  	echo '<h2>Mobile/SEO</h2>';

			?>

			<div class="metabox-holder"><div id="toc" class="postbox"> 

				<h3>Installation steps</h3>

				<div style="margin:10px">
				<p><?php _e("To install the Mobile/SEO/Accessibility plugin on your site:", "wp-symposium") ?></p>

				<ol>
					<li>In the directory where WordPress is installed, create a folder for your mobile version, for example '/m'.</li>
					<li>For example, for /m create <?php echo str_replace('//', '/', $_SERVER['DOCUMENT_ROOT'].'/'); ?>m to create <?php echo str_replace('//', '/', get_bloginfo('wpurl').'/'); ?>m</li>
					<li>Copy the <strong>contents</strong> of <?php echo WP_PLUGIN_DIR; ?>/wp-symposium/mobile-files (on your server) <strong>into</strong> this new folder.</li>
				</ol>
				</div>

				<h3>Mobile version of WP Symposium</h3>
		
				<div style="margin:10px">
				<p>On your mobile device/phone browse to (for example) <a target='_blank' href='<?php echo str_replace('//', '/', get_bloginfo('wpurl').'/'); ?>m'><?php echo str_replace('//', '/', get_bloginfo('wpurl').'/'); ?>m</a></p>
				</div>

				<h3>Accessible version of WP Symposium</h3>
		
				<div style="margin:10px">
				<p>To force the mobile version to show in a normal browser, add ?a=1. For example, <a target='_blank' href='<?php echo get_bloginfo('wpurl'); ?>/m?a=1'><?php echo get_bloginfo('wpurl'); ?>/m?a=1</a></p>
				</div>

				<h3>Search Engines</h3>
		
				<div style="margin:10px">
				<p>Submit the URL (for example) <?php echo get_bloginfo('wpurl'); ?>/m to search engines. When people visit the link indexed they will automatically be redirected to the full site (unless on a mobile device).</p>
				</div>

				<h3>Configuration options</h3>

				<form method="post" action=""> 
				<input type="hidden" name="symposium_update" value="symposium_mobile_menu">
	
				<table class="form-table"> 
	
					<tr valign="top"> 
					<td scope="row"><label for="mobile_topics"><?php _e('Maximum number of topics', 'wp-symposium'); ?></label></td> 
					<td><input name="mobile_topics" type="text" id="mobile_topics"  value="<?php echo $mobile_topics; ?>" style="width:50px" /> 
					<span class="description"><?php echo __('Threads view is also limited to last 7 days', 'wp-symposium'); ?></td> 
					</tr> 

				</table>
		 					
				<p class="submit" style="text-align:right; margin-right:12px;">
				<input type="submit" name="Submit" class="button-primary" value="<?php _e('Save Changes', 'wp-symposium'); ?>" />
				</p>
				</form>

				
			</div></div>
		</div>
	<?php
}


?>
