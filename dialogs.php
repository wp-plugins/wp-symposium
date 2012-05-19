<?php
/*  Copyright 2010,2011,2012  Simon Goodchild  (info@wpsymposium.com)

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
echo "<div class='symposium_notice' style='display:none; z-index:999999;'><img src='".get_option('symposium_images')."/busy.gif' /> ".__('Saving...', 'wp-symposium')."</div>";
echo "<div class='symposium_pleasewait' style='display:none; z-index:999999;'><img src='".get_option('symposium_images')."/busy.gif' /> ".__('Please Wait...', 'wp-symposium')."</div>";	
echo "<div class='symposium_sending' style='display:none; z-index:999999;'><img src='".get_option('symposium_images')."/busy.gif' /> ".__('Sending...', 'wp-symposium')."</div>";	
	
// Translations for Javascript
echo "<div id='symposium_pleasewait' style='display:none'>".__("Please wait", "wp-symposium")."</div>";
echo "<div id='symposium_saving' style='display:none'>".__("Saving...", "wp-symposium")."</div>";
echo "<div id='symposium_more' style='display:none'>".__("more...", "wp-symposium")."</div>";
echo "<div id='symposium_areyousure' style='display:none'>".__("Are you sure?", "wp-symposium")."</div>";
echo "<div id='symposium_browseforfile' style='display:none'>".__("Browse for file", "wp-symposium")."</div>";
echo "<div id='symposium_attachfile' style='display:none'>".__("Attach file", "wp-symposium")."</div>";
echo "<div id='symposium_whatsup' style='display:none'>".stripslashes(get_option('symposium_status_label'))."</div>";
echo "<div id='symposium_whatsup_done' style='display:none'>".__("Post added to your activity.", "wp-symposium")."</div>";
echo "<div id='symposium_sendmail' style='display:none'>".__("Send a private mail...", "wp-symposium")."</div>";
echo "<div id='symposium_privatemail' style='display:none'>".__("Private Mail", "wp-symposium")."</div>";
echo "<div id='symposium_privatemailsent' style='display:none'>".__("Private mail sent!", "wp-symposium")."</div>";
echo "<div id='symposium_addasafriend' style='display:none'>".__("Add as a Friend...", "wp-symposium")."</div>";
echo "<div id='symposium_friendpending' style='display:none'>".__("Friendship requested", "wp-symposium")."</div>";
echo "<div id='symposium_attention' style='display:none'>".get_option('symposium_poke_label')."</div>";
echo "<div id='symposium_follow' style='display:none'>".__("Follow", "wp-symposium")."</div>";
echo "<div id='symposium_unfollow' style='display:none'>".__("Unfollow", "wp-symposium")."</div>";
echo "<div id='symposium_sent' style='display:none'>".__("Message sent!", "wp-symposium")."</div>";
echo "<div id='symposium_forumsearch' style='display:none'>".__("Search on forum", "wp-symposium")."</div>";
echo "<div id='symposium_gallerysearch' style='display:none'>".__("Search Gallery", "wp-symposium")."</div>";
echo "<div id='symposium_profile_info' style='display:none'>".__("Member Profile", "wp-symposium")."</div>";
echo "<div id='symposium_plus_mail' style='display:none'>".__("Mailbox", "wp-symposium")."</div>";
echo "<div id='symposium_plus_follow_who' style='display:none'>".__("Who am I following?", "wp-symposium")."</div>";
echo "<div id='symposium_plus_friends' style='display:none'>".__("Friends", "wp-symposium")."</div>";
echo "<div id='symposium_request_sent' style='display:none'>".__("Your friend request has been sent.", "wp-symposium")."</div>";
echo "<div id='symposium_add_a_comment' style='display:none'>".__("Add a comment:", "wp-symposium")."</div>";
echo "<div id='symposium_add' style='display:none'>".__("Add", "wp-symposium")."</div>";
echo "<div id='symposium_show_original' style='display:none'>".__("Show original", "wp-symposium")."</div>";
echo "<div id='symposium_write_a_comment' style='display:none'>".__("Write a comment...", "wp-symposium")."</div>";
echo "<div id='symposium-follow-box' class='widget-area corners' style='display:none'>Hi</div>";

?>
