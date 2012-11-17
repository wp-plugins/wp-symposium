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


<div class="wrap">
<div id="icon-themes" class="icon32"><br /></div>

<?php
echo '<h2>'.sprintf(__('%s Options', WPS_TEXT_DOMAIN), WPS_WL).'</h2><br />';

__wps__show_tabs_header('replybyemail');

	
	if (isset($_POST['check_pop3'])) {
		__wps__check_pop3(true);
		$_SESSION['__wps__mailinglist_lock'] = '';
	}
	
	
	if( isset($_POST[ 'symposium_update' ]) && $_POST[ 'symposium_update' ] == 'symposium_plugin_mailinglist' ) {
	
		update_option(WPS_OPTIONS_PREFIX.'_mailinglist_server', isset($_POST[ 'symposium_mailinglist_server' ]) ? $_POST[ 'symposium_mailinglist_server' ] : '');
		update_option(WPS_OPTIONS_PREFIX.'_mailinglist_port', isset($_POST[ 'symposium_mailinglist_port' ]) ? $_POST[ 'symposium_mailinglist_port' ] : '');
		update_option(WPS_OPTIONS_PREFIX.'_mailinglist_username', isset($_POST[ 'symposium_mailinglist_username' ]) ? $_POST[ 'symposium_mailinglist_username' ] : '');
		update_option(WPS_OPTIONS_PREFIX.'_mailinglist_password', isset($_POST[ 'symposium_mailinglist_password' ]) ? $_POST[ 'symposium_mailinglist_password' ] : '');
		update_option(WPS_OPTIONS_PREFIX.'_mailinglist_prompt', isset($_POST[ 'symposium_mailinglist_prompt' ]) ? $_POST[ 'symposium_mailinglist_prompt' ] : '');
		update_option(WPS_OPTIONS_PREFIX.'_mailinglist_divider', isset($_POST[ 'symposium_mailinglist_divider' ]) ? $_POST[ 'symposium_mailinglist_divider' ] : '');
		update_option(WPS_OPTIONS_PREFIX.'_mailinglist_divider_bottom', isset($_POST[ 'symposium_mailinglist_divider_bottom' ]) ? $_POST[ 'symposium_mailinglist_divider_bottom' ] : '');
		update_option(WPS_OPTIONS_PREFIX.'_mailinglist_cron', isset($_POST[ 'symposium_mailinglist_cron' ]) ? $_POST[ 'symposium_mailinglist_cron' ] : 900);
		update_option(WPS_OPTIONS_PREFIX.'_mailinglist_from', isset($_POST[ 'symposium_mailinglist_from' ]) ? $_POST[ 'symposium_mailinglist_from' ] : '');
	
	    // Put an settings updated message on the screen
		echo "<div class='updated slideaway'><p>".__('Saved', WPS_TEXT_DOMAIN).".</p></div>";
		
	}
	
	?>
		
	<form action="" method="POST">
	
			<input type="hidden" name="symposium_update" value="symposium_plugin_mailinglist">
				
			<table class="form-table __wps__admin_table"> 

			<tr><td colspan="2"><h2><?php echo __('Options', WPS_TEXT_DOMAIN); ?></h2></td></tr>
		
			<tr><td colspan="2">
				<?php echo __('Allows members to reply to forum topics by email (by replying to the notification received).', WPS_TEXT_DOMAIN); ?><br />
				<?php echo sprintf(__('You can set up automatic checking with the WordPress cron feature below (currently every %d seconds).', WPS_TEXT_DOMAIN), get_option(WPS_OPTIONS_PREFIX.'_mailinglist_cron')); ?><br />
				<?php echo __('Click on the button below to check for replies by email and add to the forum.', WPS_TEXT_DOMAIN); ?>
			
			</td></tr>
			
			<tr><td colspan="2">
			
			</td></tr>
			
			<tr valign="top"> 
			<td scope="row"><label for="symposium_mailinglist_server"><?php echo __('Server', WPS_TEXT_DOMAIN); ?></label></td> 
			<td><input name="symposium_mailinglist_server" type="text" id="symposium_mailinglist_server"  value="<?php echo get_option(WPS_OPTIONS_PREFIX.'_mailinglist_server'); ?>" /> 
			<span class="description"><?php echo __('Server URL or IP address, eg: mail.example.com', WPS_TEXT_DOMAIN); ?></td> 
			</tr> 
															
			<tr valign="top"> 
			<td scope="row"><label for="symposium_mailinglist_port"><?php echo __('Port', WPS_TEXT_DOMAIN); ?></label></td> 
			<td><input name="symposium_mailinglist_port" type="text" id="symposium_mailinglist_port"  value="<?php echo get_option(WPS_OPTIONS_PREFIX.'_mailinglist_port'); ?>" /> 
			<span class="description"><?php echo __('Port used by mail server, eg: 110', WPS_TEXT_DOMAIN); ?></td> 
			</tr> 
	
			<tr valign="top"> 
			<td scope="row"><label for="symposium_mailinglist_username"><?php echo __('Username', WPS_TEXT_DOMAIN); ?></label></td> 
			<td><input name="symposium_mailinglist_username" type="text" id="symposium_mailinglist_username"  value="<?php echo get_option(WPS_OPTIONS_PREFIX.'_mailinglist_username'); ?>" /> 
			<span class="description"><?php echo __('Username of mail account', WPS_TEXT_DOMAIN); ?></td> 
			</tr> 
	
			<tr valign="top"> 
			<td scope="row"><label for="symposium_mailinglist_password"><?php echo __('Password', WPS_TEXT_DOMAIN); ?></label></td> 
			<td><input name="symposium_mailinglist_password" type="password" id="symposium_mailinglist_password"  value="<?php echo get_option(WPS_OPTIONS_PREFIX.'_mailinglist_password'); ?>" /> 
			<span class="description"><?php echo __('Password of mail account', WPS_TEXT_DOMAIN); ?></td> 
			</tr> 
	
			<tr valign="top"> 
			<td scope="row"><label for="symposium_mailinglist_from"><?php echo __('Email sent from', WPS_TEXT_DOMAIN); ?></label></td> 
			<td><input name="symposium_mailinglist_from" type="text" id="symposium_mailinglist_from"  value="<?php echo get_option(WPS_OPTIONS_PREFIX.'_mailinglist_from'); ?>" /> 
			<span class="description"><?php echo __('Email address to reply to, eg: forum@example.com', WPS_TEXT_DOMAIN); ?></td> 
			</tr> 
	
			<tr valign="top"> 
			<td scope="row"><label for="symposium_mailinglist_prompt"><?php echo __('Prompt', WPS_TEXT_DOMAIN); ?></label></td> 
			<td><input style="width: 400px" name="symposium_mailinglist_prompt" type="text" id="symposium_mailinglist_prompt"  value="<?php echo get_option(WPS_OPTIONS_PREFIX.'_mailinglist_prompt'); ?>" /> 
			<span class="description"><?php echo __('Line of text to appear as a prompt where to enter reply text', WPS_TEXT_DOMAIN); ?></td> 
			</tr> 
	
			<tr valign="top"> 
			<td scope="row"><label for="symposium_mailinglist_divider"><?php echo __('Top divider', WPS_TEXT_DOMAIN); ?></label></td> 
			<td><input style="width: 400px" name="symposium_mailinglist_divider" type="text" id="symposium_mailinglist_divider"  value="<?php echo get_option(WPS_OPTIONS_PREFIX.'_mailinglist_divider'); ?>" /> 
			<span class="description"><?php echo __('The top boundary of where replies should be entered', WPS_TEXT_DOMAIN); ?></td> 
			</tr> 
	
			<tr valign="top"> 
			<td scope="row"><label for="symposium_mailinglist_divider_bottom"><?php echo __('Bottom divider', WPS_TEXT_DOMAIN); ?></label></td> 
			<td><input style="width: 400px" name="symposium_mailinglist_divider_bottom" type="text" id="symposium_mailinglist_divider_bottom"  value="<?php echo get_option(WPS_OPTIONS_PREFIX.'_mailinglist_divider_bottom'); ?>" /> 
			<span class="description"><?php echo __('The bottom boundary of where replies should be entered', WPS_TEXT_DOMAIN); ?></td> 
			</tr> 
	
			<tr valign="top"> 
			<td scope="row"><label for="symposium_mailinglist_cron"><?php echo __('Check Frequency', WPS_TEXT_DOMAIN); ?></label></td> 
			<td><input name="symposium_mailinglist_cron" type="text" id="symposium_mailinglist_cron"  value="<?php echo get_option(WPS_OPTIONS_PREFIX.'_mailinglist_cron'); ?>" /> 
			<span class="description"><?php echo __('Frequency (in seconds) to check, uses WordPress cron schedule, so requires your site to be visited. Not too low!!', WPS_TEXT_DOMAIN); ?></td> 
			</tr> 
	
			</table> 	
		 
			<div style='margin-top:25px; margin-left:6px; float:left;'> 
			<input type="submit" name="Submit" class="button-primary" value="<?php echo __('Save Changes', WPS_TEXT_DOMAIN); ?>" /> 
			</div> 
			
	</form>		
	<form action="" method="POST">
		<input type='hidden' name='check_pop3' value='1' />
		<input type="submit" name="submit" class="button-primary" style="margin-top:25px; margin-left:10px;" value="<?php echo __('Check for replies now', WPS_TEXT_DOMAIN); ?>" /> 
	</form>
		
<?php __wps__show_tabs_header_end(); ?>
	
</div>
