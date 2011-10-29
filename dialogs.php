<?php
/*  Copyright 2010,2011  Simon Goodchild  (info@wpsymposium.com)

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

// Dialog
echo "<div id='dialog' style='display:none'></div>";	
	
// Translations for Javascript
echo "<div id='symposium_pleasewait' style='display:none'>".__("Please wait", "wp-symposium")."</div>";
echo "<div id='symposium_saving' style='display:none'>".__("Saving...", "wp-symposium")."</div>";
echo "<div id='symposium_more' style='display:none'>".__("more...", "wp-symposium")."</div>";
echo "<div id='symposium_areyousure' style='display:none'>".__("Are you sure?", "wp-symposium")."</div>";
echo "<div id='symposium_browseforfile' style='display:none'>".__("Browse for file", "wp-symposium")."</div>";
echo "<div id='symposium_attachfile' style='display:none'>".__("Attach file", "wp-symposium")."</div>";
echo "<div id='symposium_whatsup' style='display:none'>".WPS_STATUS_POST."</div>";

?>