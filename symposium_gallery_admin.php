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
<h2>Gallery</h2>

<div class="metabox-holder"><div id="toc" class="postbox"> 

<h3>Gallery Settings</h3>

<?php

	global $wpdb;
    // See if the user has posted profile settings
    if( isset($_POST[ 'symposium_gallery_updated' ]) ) {

	 	// Update Version *******************************************************************************
	 	$show_resized = (isset($_POST[ 'show_resized' ])) ? $_POST[ 'show_resized' ] : '';
	 	$thumbnail_size = (isset($_POST[ 'thumbnail_size' ])) ? $_POST[ 'thumbnail_size' ] : '75';
	 	$gallery_page_length = (isset($_POST[ 'gallery_page_length' ])) ? $_POST[ 'gallery_page_length' ] : '10';
	 	$gallery_preview = (isset($_POST[ 'gallery_preview' ])) ? $_POST[ 'gallery_preview' ] : '5';

		update_option("symposium_gallery_show_resized", $show_resized);
		update_option("symposium_gallery_thumbnail_size", $thumbnail_size);
		update_option("symposium_gallery_page_length", $gallery_page_length);
		update_option("symposium_gallery_preview", $gallery_preview);

        // Put an settings updated message on the screen
		echo "<div class='updated slideaway'><p>".__('Saved', 'wp-symposium').".</p></div>";
		
    }

	// Get options
	$show_resized = ($value = get_option("symposium_gallery_show_resized")) ? $value : '';
	$thumbnail_size = ($value = get_option("symposium_gallery_thumbnail_size")) ? $value : '75';
	$gallery_page_length = ($value = get_option("symposium_gallery_page_length")) ? $value : '10';
	$gallery_preview = ($value = get_option("symposium_gallery_preview")) ? $value : '5';

	?>

	<form method="post" action=""> 
	<input type='hidden' name='symposium_gallery_updated' value='Y'>
	<table class="form-table"> 

	<tr valign="top"> 
	<td scope="row"><label for="show_resized"><?php _e('Re-size photos in slideshow', 'wp-symposium'); ?></label></td>
	<td>
	<input type="checkbox" name="show_resized" id="show_resized" <?php if ($show_resized == "on") { echo "CHECKED"; } ?>/>
	<span class="description"><?php echo __('Re-sizing photos will ensure that are displayed at nice size, but will stretch small images', 'wp-symposium'); ?></span></td> 
	</tr> 

	<tr valign="top"> 
	<td scope="row"><label for="thumbnail_size"><?php _e('Thumbnail size', 'wp-symposium'); ?></label></td> 
	<td><input name="thumbnail_size" type="text" id="thumbnail_size" style="width:50px" value="<?php echo $thumbnail_size; ?>" /> 
	<span class="description"><?php echo __('Size of gallery thumbnails', 'wp-symposium'); ?></td> 
	</tr> 
	
	<tr valign="top"> 
	<td scope="row"><label for="gallery_page_length"><?php _e('Page size', 'wp-symposium'); ?></label></td> 
	<td><input name="gallery_page_length" type="text" id="gallery_page_length" style="width:50px" value="<?php echo $gallery_page_length; ?>" /> 
	<span class="description"><?php echo __('Number of albums to show on the gallery page (shortcode)', 'wp-symposium'); ?></td> 
	</tr> 
	
	<tr valign="top"> 
	<td scope="row"><label for="gallery_preview"><?php _e('Preview photos', 'wp-symposium'); ?></label></td> 
	<td><input name="gallery_preview" type="text" id="gallery_preview" style="width:50px" value="<?php echo $gallery_preview; ?>" /> 
	<span class="description"><?php echo __('Number of photos to show on one row as an album preview on the gallery page (shortcode)', 'wp-symposium'); ?></td> 
	</tr> 
	
	<?php
	echo '</table>';
	 					
	echo '<p class="submit" style="text-align:right; margin-right:12px">';
	echo '<input type="submit" name="Submit" class="button-primary" value="'.__('Save Changes', 'wp-symposium').'" />';
	echo '</p>';
	echo '</form>';
					  
?>

</div></div>

</div>
