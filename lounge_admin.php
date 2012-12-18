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
// Update any POSTed updates
if (isset($_POST['symposium_lounge_max_rows'])) {
	update_option(WPS_OPTIONS_PREFIX.'_lounge_max_rows', $_POST['symposium_lounge_max_rows']);
}

?>


<div class="wrap">
<div id="icon-themes" class="icon32"><br /></div>
<?php
echo '<h2>'.sprintf(__('%s Options', WPS_TEXT_DOMAIN), WPS_WL).'</h2><br />';

__wps__show_tabs_header('lounge');
?>

<table class="form-table"><tr><td colspan="2">
<h2><?php echo __('Options', WPS_TEXT_DOMAIN); ?></h2>


<div>
<form action="" method="POST">
	
	<p>
	<?php _e('The Lounge plugin provides a site wide chat room ("shoutbox").', WPS_TEXT_DOMAIN); ?>
	</p>
	<?php _e('Maximum number of returned rows', WPS_TEXT_DOMAIN); ?>: <input type="text" name="symposium_lounge_max_rows" style="width:40px" value="<?php echo get_option(WPS_OPTIONS_PREFIX."_lounge_max_rows")+0; ?>" />
	<br /><br />
	<input type="submit" class="button-primary" value="<?php _e('Save', WPS_TEXT_DOMAIN); ?>">
	
</form>
</div>
</td></tr></table>

<table class="form-table __wps__admin_table"><tr><td colspan="2">
<h2>Development Demonstrator</h2>

<div>
<p>
The primary purpose of the plugin is to act as a demonstrator and/or template for the development of plugins that are compatible with <?php echo WPS_WL; ?>.
</p>

It includes many features such as using:
<ul style="list-style-type: circle; margin: 10px 0 10px 30px;">
<li><?php echo WPS_WL; ?> Hooks and Filters</li>
<li><?php echo WPS_WL; ?> functions</li>
<li><?php echo WPS_WL; ?> Javascript variables</li>
</ul>

<p>
It also demonstrates how to:
</p>

<ul style="list-style-type: circle; margin: 10px 0 10px 30px;">
<li>add to the Profile page menu and display content within the profile page without reloading whole page</li>
<li>use AJAX to present information within WordPress and reload partial content without a page refresh</li>
<li>plug in to the <?php echo WPS_WL; ?> installation page</li>
<li>add a short-code to include content as part of a WordPress page</li>
</ul>
</div>
</td></tr></table>

<table class="form-table __wps__admin_table"><tr><td colspan="2">
<h2>Documentation</h2>

<div>
<p>
For latest information please see the <a href="http://www.wpswiki.com/index.php?title=Developers" target="_blank">Developers Codex</a> on <a href="http://www.wpswiki.com" target="_blank">http://www.wpswiki.com</a>.
</p>
</div>
</td></tr></table>

<table class="form-table __wps__admin_table">
	<tr><td colspan="2"><h2>Shortcodes</h2></td></tr>
	<tr><td width="165px">[<?php echo WPS_SHORTCODE_PREFIX; ?>-lounge]</td>
		<td><?php echo __('Display the site wide chat form.', WPS_TEXT_DOMAIN); ?></td></tr>
</table>



<?php __wps__show_tabs_header_end(); ?>

</div>
