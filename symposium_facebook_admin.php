<!--
    Copyright 2010,2011,2012  Simon Goodchild  (info@wpsymposium.com)

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
-->

<?php
	
	global $wpdb;

	// Store any new values
    if( isset($_POST[ 'symposium_update' ]) && $_POST[ 'symposium_update' ] == 'symposium_facebook_menu' ) {
    	    	        
        $facebook_api = $_POST[ 'facebook_api' ];
        $facebook_secret = $_POST[ 'facebook_secret' ];

		update_option('symposium_facebook_api', $facebook_api);
		update_option('symposium_facebook_secret', $facebook_secret);

        // Put an settings updated message on the screen
		echo "<div class='updated slideaway'><p>".__('Facebook options saved', 'wp-symposium').".</p></div>";

    } else {
	    // Get values from database  
		$facebook_api = get_option('symposium_facebook_api');
		$facebook_secret = get_option('symposium_facebook_secret');
    }


  	echo '<div class="wrap">';

	  	echo '<div id="icon-themes" class="icon32"><br /></div>';
	  	echo '<h2>Facebook Shared Status</h2>';

		?>

		<div class="metabox-holder"><div id="toc" class="postbox"> 

			<h3>Installation</h3>

			<div style="margin:10px">
			<p><?php _e("A Facebook application is used to post messages to Facebook Walls - you need to create a Facebook application for your website:", "wp-symposium") ?></p>

			<ol>
				<li>Log in to Facebook</li>
				<li>Go <a target='_blank' href='http://www.facebook.com/developers/apps.php'>here</a>.</li>
				<li>Click on Create New App button</li>
				<li>Enter an App Display Name (that will appear under Facebook Wall posts)</li>
				<li>You can leave App Namespace blank</li>				
				<li>Enter the security check words</li>
				<li>Now click on "Website: I want to allow people to log in to my website using Facebook."</li>
				<li>For the Site URL, enter your website address, for example http://www.example.com</li>
				<li>Click on Save Changes on Facebook</li>
				<li>Copy and Paste the <strong>App ID</strong> and <strong>App Secret</strong> below, and click on the Save Changes button below</li>
			</ol>
			</div>
	
			<h3>Facebook Application values</h3>
		
			<form method="post" action=""> 
			<input type="hidden" name="symposium_update" value="symposium_facebook_menu">

			<table class="form-table"> 

				<tr valign="top"> 
				<td scope="row"><label for="facebook_api"><?php _e('Facebook Application ID', 'wp-symposium'); ?></label></td> 
				<td><input name="facebook_api" type="text" id="facebook_api"  value="<?php echo $facebook_api; ?>" style="width:250px" /> 
				<span class="description"><?php echo __('Also called your OAuth client_id', 'wp-symposium'); ?></td> 
				</tr> 

				<tr valign="top"> 
				<td scope="row"><label for="facebook_secret"><?php _e('Facebook Application Secret', 'wp-symposium'); ?></label></td> 
				<td><input name="facebook_secret" type="text" id="facebook_secret"  value="<?php echo $facebook_secret; ?>" style="width:250px" /> 
				<span class="description"><?php echo __('Also called your OAuth client_secret', 'wp-symposium'); ?></td> 
				</tr> 

			<?php
			echo '</table>';
	 					
			echo '<p class="submit" style="margin-left:6px;">';
			echo '<input type="submit" name="Submit" class="button-primary" value="'.__('Save Changes', 'wp-symposium').'" />';
			echo '</p>';
			echo '</form>';
			
	echo '</div></div></div>';
	
?>
