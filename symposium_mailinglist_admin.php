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

	<h2>Reply by Email</h2>
	
	<?php
	
	if (isset($_POST['check_pop3'])) {
		symposium_check_pop3(true);
		$_SESSION['symposium_mailinglist_lock'] = '';
	}
	
	
	if( isset($_POST[ 'symposium_update' ]) && $_POST[ 'symposium_update' ] == 'symposium_plugin_mailinglist' ) {
	
		update_option('symposium_mailinglist_server', isset($_POST[ 'symposium_mailinglist_server' ]) ? $_POST[ 'symposium_mailinglist_server' ] : '');
		update_option('symposium_mailinglist_port', isset($_POST[ 'symposium_mailinglist_port' ]) ? $_POST[ 'symposium_mailinglist_port' ] : '');
		update_option('symposium_mailinglist_username', isset($_POST[ 'symposium_mailinglist_username' ]) ? $_POST[ 'symposium_mailinglist_username' ] : '');
		update_option('symposium_mailinglist_password', isset($_POST[ 'symposium_mailinglist_password' ]) ? $_POST[ 'symposium_mailinglist_password' ] : '');
		update_option('symposium_mailinglist_prompt', isset($_POST[ 'symposium_mailinglist_prompt' ]) ? $_POST[ 'symposium_mailinglist_prompt' ] : '');
		update_option('symposium_mailinglist_divider', isset($_POST[ 'symposium_mailinglist_divider' ]) ? $_POST[ 'symposium_mailinglist_divider' ] : '');
		update_option('symposium_mailinglist_divider_bottom', isset($_POST[ 'symposium_mailinglist_divider_bottom' ]) ? $_POST[ 'symposium_mailinglist_divider_bottom' ] : '');
		update_option('symposium_mailinglist_cron', isset($_POST[ 'symposium_mailinglist_cron' ]) ? $_POST[ 'symposium_mailinglist_cron' ] : 900);
		update_option('symposium_mailinglist_from', isset($_POST[ 'symposium_mailinglist_from' ]) ? $_POST[ 'symposium_mailinglist_from' ] : '');
	
	    // Put an settings updated message on the screen
		echo "<div class='updated slideaway'><p>".__('Saved', 'wp-symposium').".</p></div>";
		
	}
	
	?>
	
	<h3>Description</h3>
	
	<p>
		Allows members to reply to forum topics by email (by replying to the notification received).
	</p>
	<p>
		You can set up automatic checking with the WordPress cron feature below (currently every <?php echo get_option('symposium_mailinglist_cron'); ?> seconds).
	</p>
	<p>
		Click on the button below to check for replies by email and add to the forum.
	</p>
		
	<form action="" method="POST">
		<input type='hidden' name='check_pop3' value='1' />
		<p style="margin-left:6px"> 
		<input type="submit" name="submit" class="button-primary" value="<?php echo __('Check for replies now', 'wp-symposium'); ?>" /> 
		</p> 
		
	</form> 
	
	<form action="" method="POST">
	
	
		<div class="metabox-holder"><div id="toc" class="postbox"> 
				
			<input type="hidden" name="symposium_update" value="symposium_plugin_mailinglist">
				
			<table class="form-table"> 
		
			<tr valign="top"> 
			<td scope="row"><label for="symposium_mailinglist_server"><?php echo __('Server', 'wp-symposium'); ?></label></td> 
			<td><input name="symposium_mailinglist_server" type="text" id="symposium_mailinglist_server"  value="<?php echo get_option('symposium_mailinglist_server'); ?>" /> 
			<span class="description"><?php echo __('Server URL or IP address, eg: mail.example.com', 'wp-symposium'); ?></td> 
			</tr> 
															
			<tr valign="top"> 
			<td scope="row"><label for="symposium_mailinglist_port"><?php echo __('Port', 'wp-symposium'); ?></label></td> 
			<td><input name="symposium_mailinglist_port" type="text" id="symposium_mailinglist_port"  value="<?php echo get_option('symposium_mailinglist_port'); ?>" /> 
			<span class="description"><?php echo __('Port used by mail server, eg: 110', 'wp-symposium'); ?></td> 
			</tr> 
	
			<tr valign="top"> 
			<td scope="row"><label for="symposium_mailinglist_username"><?php echo __('Username', 'wp-symposium'); ?></label></td> 
			<td><input name="symposium_mailinglist_username" type="text" id="symposium_mailinglist_username"  value="<?php echo get_option('symposium_mailinglist_username'); ?>" /> 
			<span class="description"><?php echo __('Username of mail account', 'wp-symposium'); ?></td> 
			</tr> 
	
			<tr valign="top"> 
			<td scope="row"><label for="symposium_mailinglist_password"><?php echo __('Password', 'wp-symposium'); ?></label></td> 
			<td><input name="symposium_mailinglist_password" type="password" id="symposium_mailinglist_password"  value="<?php echo get_option('symposium_mailinglist_password'); ?>" /> 
			<span class="description"><?php echo __('Password of mail account', 'wp-symposium'); ?></td> 
			</tr> 
	
			<tr valign="top"> 
			<td scope="row"><label for="symposium_mailinglist_from"><?php echo __('Email sent from', 'wp-symposium'); ?></label></td> 
			<td><input name="symposium_mailinglist_from" type="text" id="symposium_mailinglist_from"  value="<?php echo get_option('symposium_mailinglist_from'); ?>" /> 
			<span class="description"><?php echo __('Email address to reply to, eg: forum@example.com', 'wp-symposium'); ?></td> 
			</tr> 
	
			<tr valign="top"> 
			<td scope="row"><label for="symposium_mailinglist_prompt"><?php echo __('Prompt', 'wp-symposium'); ?></label></td> 
			<td><input style="width: 400px" name="symposium_mailinglist_prompt" type="text" id="symposium_mailinglist_prompt"  value="<?php echo get_option('symposium_mailinglist_prompt'); ?>" /> 
			<span class="description"><?php echo __('Line of text to appear as a prompt where to enter reply text', 'wp-symposium'); ?></td> 
			</tr> 
	
			<tr valign="top"> 
			<td scope="row"><label for="symposium_mailinglist_divider"><?php echo __('Top divider', 'wp-symposium'); ?></label></td> 
			<td><input style="width: 400px" name="symposium_mailinglist_divider" type="text" id="symposium_mailinglist_divider"  value="<?php echo get_option('symposium_mailinglist_divider'); ?>" /> 
			<span class="description"><?php echo __('The top boundary of where replies should be entered', 'wp-symposium'); ?></td> 
			</tr> 
	
			<tr valign="top"> 
			<td scope="row"><label for="symposium_mailinglist_divider_bottom"><?php echo __('Bottom divider', 'wp-symposium'); ?></label></td> 
			<td><input style="width: 400px" name="symposium_mailinglist_divider_bottom" type="text" id="symposium_mailinglist_divider_bottom"  value="<?php echo get_option('symposium_mailinglist_divider_bottom'); ?>" /> 
			<span class="description"><?php echo __('The bottom boundary of where replies should be entered', 'wp-symposium'); ?></td> 
			</tr> 
	
			<tr valign="top"> 
			<td scope="row"><label for="symposium_mailinglist_cron"><?php echo __('Check Frequency', 'wp-symposium'); ?></label></td> 
			<td><input name="symposium_mailinglist_cron" type="text" id="symposium_mailinglist_cron"  value="<?php echo get_option('symposium_mailinglist_cron'); ?>" /> 
			<span class="description"><?php echo __('Frequency (in seconds) to check, uses WordPress cron schedule, so requires your site to be visited. Not too low!!', 'wp-symposium'); ?></td> 
			</tr> 
	
			</table> 	
		 
			<p class="submit" style='margin-left:6px;'> 
			<input type="submit" name="Submit" class="button-primary" value="<?php echo __('Save Changes', 'wp-symposium'); ?>" /> 
			</p> 
		
		</div></div>
			
	</form>		
	
	
</div>
