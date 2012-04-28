<!--
    Copyright 2010,2011  Simon Goodchild  (info@wpsymposium.com)

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

<h2>Alerts</h2>

<h3>Description</h3>

<p>
	The Alerts plugin updates a DIV (which can be in a WordPress menu item or embedded in a theme) that notifies the member of news/notifications such as new 	mail/friends/activity/etc - notifications can be added by other plugins.
</p>
	
<?php
// React to POSTed information
if (isset($_POST['symposium_news_polling'])) {
	update_option('symposium_news_polling', $_POST['symposium_news_polling']);
}
if (isset($_POST['symposium_news_x_offset'])) {
	update_option('symposium_news_x_offset', $_POST['symposium_news_x_offset']);
}
if (isset($_POST['symposium_news_y_offset'])) {
	update_option('symposium_news_y_offset', $_POST['symposium_news_y_offset']);
}
?>

<h3><php _e('Configuration', 'wp-symposium'); ?></h3>

<p>
	<?php _e('Depending on the theme you are using, the position of the list of alerts may not be exactly as you require (the default values are for use in the Wordpress TwentyEleven theme).', 'wp-symposium'); ?>
</p>

<p>
	<?php _e('To move the list of alerts left/right or up/down, change the offset values below. Use negative values to move left/up and positive values to move right/down.', 'wp-symposium'); ?>
</p>


<div class="metabox-holder"><div id="toc" class="postbox"> 

<form action="" method="POST">

	<table class="form-table">
	
		<tr valign="top"> 
		<td scope="row"><label for="symposium_news_x_offset"><?php _e('Horizontal offset', 'wp-symposium'); ?></label></td>
		<td>
		<input type="text" name="symposium_news_x_offset" id="use_chat" value="<?php echo get_option("symposium_news_x_offset"); ?>"/>
		<span class="description"><?php echo __('Move the position of the list of alerts left/right', 'wp-symposium'); ?></span></td> 
		</tr> 	
		
		<tr valign="top"> 
		<td scope="row"><label for="symposium_news_y_offset"><?php _e('Vertical offset', 'wp-symposium'); ?></label></td>
		<td>
		<input type="text" name="symposium_news_y_offset" id="use_chat" value="<?php echo get_option("symposium_news_y_offset"); ?>"/>
		<span class="description"><?php echo __('Move the position of the list of alerts up/down', 'wp-symposium'); ?></span></td> 
		</tr> 	
		
		<tr valign="top"> 
		<td scope="row"><label for="symposium_news_polling"><?php _e('Polling interval (seconds)', 'wp-symposium'); ?></label></td>
		<td>
		<input type="text" name="symposium_news_polling" id="use_chat" value="<?php echo get_option("symposium_news_polling"); ?>"/>
		<span class="description"><?php echo __('Change the polling interval to reduce load on your server', 'wp-symposium'); ?></span></td> 
		</tr> 	
	
	</table> 
	
	<p style="margin-left:6px"> 
	<input type="submit" name="Submit" class="button-primary" value="<?php echo __('Save Changes', 'wp-symposium'); ?>" /> 
	</p> 
	
</form> 
					
</div></div>

<h3><?php _e('Implementing', 'wp-symposium') ?></h3>

<p>
	<strong><?php _e('To add as a menu item', 'wp-symposium'); ?></strong>
	<ol>
		<li><a href="post-new.php?post_type=page">Create a WordPress page</a> (to display the history when the menu item itself is clicked on), and make the page title "Alerts" (or what you want to call it). This is not what appears on the menu, but may appear as your page title when the page is viewed.</li>
		<li>Enter the shortcode [symposium-alerts] on to the page (note: hyphen, not an underscore)</li>
		<li>Visit the <a href="admin.php?page=symposium_debug">WPS Installation page</a> to complete the new page setup.</li>
		<li><a href="nav-menus.php">Edit your site menu</a>, and add the newly created page to the menu. Change the navigation label of your new menu item to that shown in the yellow box below.</li>
	</ol>
	
	<div style="border:1px dotted #333; padding:6px; border-radius:3px; width: 400px; font-family: courier; text-align: center; margin: 20px auto 20px; background-color: #ff9">
	&lt;div id='symposium_alerts'&gt;Alerts&lt;/div&gt;
	</div>
	
	<strong><?php _e('To add to a theme template', 'wp-symposium'); ?></strong>
	<ol>
		<li>Edit your theme, sidebar, etc and add the code shown in the yellow box above to position where the alerts will appear.</li>
	</ol>

</p>

<h3>More information</h3>

<p>
	There is more information available on the <a href="http://www.wpswiki.com/index.php?title=Alerts" target="_blank">WPS Wiki</a>.
</p>



</div>
