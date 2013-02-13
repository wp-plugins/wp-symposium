<?php
	
	global $wpdb;

	// Store any new values
    if( isset($_POST[ 'symposium_update' ]) && $_POST[ 'symposium_update' ] == 'symposium_facebook_menu' ) {
    	    	        
        $facebook_api = $_POST[ 'facebook_api' ];
        $facebook_secret = $_POST[ 'facebook_secret' ];

		update_option(WPS_OPTIONS_PREFIX.'_facebook_api', $facebook_api);
		update_option(WPS_OPTIONS_PREFIX.'_facebook_secret', $facebook_secret);

        // Put an settings updated message on the screen
		echo "<div class='updated slideaway'><p>".__('Facebook options saved', WPS_TEXT_DOMAIN).".</p></div>";

    } else {
	    // Get values from database  
		$facebook_api = get_option(WPS_OPTIONS_PREFIX.'_facebook_api');
		$facebook_secret = get_option(WPS_OPTIONS_PREFIX.'_facebook_secret');
    }


  	echo '<div class="wrap">';

	  	echo '<div id="icon-themes" class="icon32"><br /></div>';
		echo '<h2>'.sprintf(__('%s Options', WPS_TEXT_DOMAIN), WPS_WL).'</h2><br />';
		
		__wps__show_tabs_header('facebook');

		?>

			<h3>Installation</h3>

			<div style="margin:10px">
			<p><?php _e("A Facebook application is used to post messages to Facebook Walls - you need to create a Facebook application for your website:", WPS_TEXT_DOMAIN) ?></p>

			<ol>
				<li>Log in to <a target='_blank' href="http://www.facebook.com">Facebook</a>.</li>
				<li>Go <a target='_blank' href='https://developers.facebook.com/apps'>here</a>.</li>
				<li>Click on <img src="<?php echo plugin_dir_url( __FILE__ ) ?>/library/create_app.gif" /> button</li>
				<li>Enter an <strong>App Display Name</strong> (that will appear under Facebook Wall posts), eg: Example Web Site</li>
				<li>You can leave App Namespace blank</li>				
				<li>Enter the security check words</li>
				<li>Click on <strong>Website with Facebook Login</strong> and enter your site URL, eg: <?php echo str_replace('http:/', 'http://', str_replace('//', '/', get_bloginfo('wpurl').'/')); ?> (including trailing slash).
				<li>Click on <strong>Save Changes</strong> on Facebook</li>
				<li>Copy and Paste the <strong>App ID</strong> and <strong>App Secret</strong> below, and click on the Save Changes button below</li>
			</ol>
			</div>
	
			<h3>Facebook Application values</h3>

			<form method="post" action=""> 
			<input type="hidden" name="symposium_update" value="symposium_facebook_menu">

			<table class="form-table __wps__admin_table"> 

				<tr valign="top"> 
				<td scope="row"><label for="facebook_api"><?php _e('Facebook Application ID', WPS_TEXT_DOMAIN); ?></label></td> 
				<td><input name="facebook_api" type="text" id="facebook_api"  value="<?php echo $facebook_api; ?>" style="width:250px" /> 
				<span class="description"><?php echo __('Also called your OAuth client_id', WPS_TEXT_DOMAIN); ?></td> 
				</tr> 

				<tr valign="top"> 
				<td scope="row"><label for="facebook_secret"><?php _e('Facebook Application Secret', WPS_TEXT_DOMAIN); ?></label></td> 
				<td><input name="facebook_secret" type="text" id="facebook_secret"  value="<?php echo $facebook_secret; ?>" style="width:250px" /> 
				<span class="description"><?php echo __('Also called your OAuth client_secret', WPS_TEXT_DOMAIN); ?></td> 
				</tr> 

			<?php
			echo '</table>';

			echo '<p class="submit" style="margin-left:6px;">';
			echo '<input type="submit" name="Submit" class="button-primary" value="'.__('Save Changes', WPS_TEXT_DOMAIN).'" />';
			echo '</p>';
			echo '</form>';
			
			echo '<h3>Example Facebook Application values</h3>';
			
			echo '<p>'.__('The following settings are used with the www.wpsymposium.com website, as an example.', WPS_TEXT_DOMAIN).'</p>';
			
			echo '<img src="'.plugin_dir_url( __FILE__ ).'/images/facebook_admin_screenshot.png" />';
			
		__wps__show_tabs_header_end();
			
	echo '</div>';
	
?>
