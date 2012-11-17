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
echo "<div class='__wps__notice' style='display:none; z-index:999999;'><img src='".get_option(WPS_OPTIONS_PREFIX.'_images')."/busy.gif' /> ".__('Saving...', WPS_TEXT_DOMAIN)."</div>";
echo "<div class='__wps__pleasewait' style='display:none; z-index:999999;'><img src='".get_option(WPS_OPTIONS_PREFIX.'_images')."/busy.gif' /> ".__('Please Wait...', WPS_TEXT_DOMAIN)."</div>";	
echo "<div class='__wps__sending' style='display:none; z-index:999999;'><img src='".get_option(WPS_OPTIONS_PREFIX.'_images')."/busy.gif' /> ".__('Sending...', WPS_TEXT_DOMAIN)."</div>";	
	
// Translations for Javascript
echo "<div id='__wps__clear' style='display:none'>".__("Clear", WPS_TEXT_DOMAIN)."</div>";
echo "<div id='__wps__update' style='display:none'>".__("Update", WPS_TEXT_DOMAIN)."</div>";
echo "<div id='__wps__cancel' style='display:none'>".__("Cancel", WPS_TEXT_DOMAIN)."</div>";
echo "<div id='__wps__pleasewait' style='display:none'>".__("Please wait", WPS_TEXT_DOMAIN)."</div>";
echo "<div id='__wps__saving' style='display:none'>".__("Saving...", WPS_TEXT_DOMAIN)."</div>";
echo "<div id='__wps__more' style='display:none'>".__("more...", WPS_TEXT_DOMAIN)."</div>";
echo "<div id='__wps__next' style='display:none'>".__("Next", WPS_TEXT_DOMAIN)."</div>";
echo "<div id='__wps__areyousure' style='display:none'>".__("Are you sure?", WPS_TEXT_DOMAIN)."</div>";
echo "<div id='__wps__browseforfile' style='display:none'>".__("Browse for file", WPS_TEXT_DOMAIN)."</div>";
echo "<div id='__wps__attachfile' style='display:none'>".__("Attach file", WPS_TEXT_DOMAIN)."</div>";
echo "<div id='__wps__whatsup' style='display:none'>".stripslashes(get_option(WPS_OPTIONS_PREFIX.'_status_label'))."</div>";
echo "<div id='__wps__whatsup_done' style='display:none'>".__("Post added to your activity.", WPS_TEXT_DOMAIN)."</div>";
echo "<div id='__wps__sendmail' style='display:none'>".__("Send a private mail...", WPS_TEXT_DOMAIN)."</div>";
echo "<div id='__wps__privatemail' style='display:none'>".__("Private Mail", WPS_TEXT_DOMAIN)."</div>";
echo "<div id='__wps__privatemailsent' style='display:none'>".__("Private mail sent!", WPS_TEXT_DOMAIN)."</div>";
echo "<div id='__wps__addasafriend' style='display:none'>".sprintf(__("Add as a %s...", WPS_TEXT_DOMAIN), get_option(WPS_OPTIONS_PREFIX.'_alt_friend'))."</div>";
echo "<div id='__wps__friendpending' style='display:none'>".sprintf(__("%s request sent", WPS_TEXT_DOMAIN), get_option(WPS_OPTIONS_PREFIX.'_alt_friend'))."</div>";
echo "<div id='__wps__attention' style='display:none'>".get_option(WPS_OPTIONS_PREFIX.'_poke_label')."</div>";
echo "<div id='__wps__follow' style='display:none'>".__("Follow", WPS_TEXT_DOMAIN)."</div>";
echo "<div id='__wps__unfollow' style='display:none'>".__("Unfollow", WPS_TEXT_DOMAIN)."</div>";
echo "<div id='__wps__sent' style='display:none'>".__("Message sent!", WPS_TEXT_DOMAIN)."</div>";
echo "<div id='__wps__likes' style='display:none'>".__("Likes", WPS_TEXT_DOMAIN)."</div>";
echo "<div id='__wps__dislikes' style='display:none'>".__("Dislikes", WPS_TEXT_DOMAIN)."</div>";
echo "<div id='__wps__forumsearch' style='display:none'>".__("Search on forum", WPS_TEXT_DOMAIN)."</div>";
echo "<div id='__wps__gallerysearch' style='display:none'>".__("Search Gallery", WPS_TEXT_DOMAIN)."</div>";
echo "<div id='__wps__profile_info' style='display:none'>".__("Member Profile", WPS_TEXT_DOMAIN)."</div>";
echo "<div id='__wps__plus_mail' style='display:none'>".__("Mailbox", WPS_TEXT_DOMAIN)."</div>";
echo "<div id='__wps__plus_follow_who' style='display:none'>".__("Who am I following?", WPS_TEXT_DOMAIN)."</div>";
echo "<div id='__wps__plus_friends' style='display:none'>".get_option(WPS_OPTIONS_PREFIX.'_alt_friends')."</div>";
echo "<div id='__wps__request_sent' style='display:none'>".sprintf(__("Your %s request has been sent.", WPS_TEXT_DOMAIN), get_option(WPS_OPTIONS_PREFIX.'_alt_friend'))."</div>";
echo "<div id='__wps__add_a_comment' style='display:none'>".__("Add a comment:", WPS_TEXT_DOMAIN)."</div>";
echo "<div id='__wps__add' style='display:none'>".__("Add", WPS_TEXT_DOMAIN)."</div>";
echo "<div id='__wps__show_original' style='display:none'>".__("Show original", WPS_TEXT_DOMAIN)."</div>";
echo "<div id='__wps__write_a_comment' style='display:none'>".__("Write a comment...", WPS_TEXT_DOMAIN)."</div>";
echo "<div id='__wps__follow_box' class='widget-area corners' style='display:none'>Hi</div>";
echo "<div id='__wps__events_enable_places' style='display:none'>".__("Enable booking places:", WPS_TEXT_DOMAIN)."</div>";
echo "<div id='__wps__events_max_places' style='display:none'>".__("Maximum places:", WPS_TEXT_DOMAIN)."</div>";
echo "<div id='__wps__events_show_max' style='display:none'>".__("Show availability:", WPS_TEXT_DOMAIN)."</div>";
echo "<div id='__wps__events_confirmation' style='display:none'>".__("Bookings require confirmation:", WPS_TEXT_DOMAIN)."</div>";
echo "<div id='__wps__events_tickets_per_booking' style='display:none'>".__("Max tickets per booking:", WPS_TEXT_DOMAIN)."</div>";
echo "<div id='__wps__events_tab_1' style='display:none'>".__("Summary", WPS_TEXT_DOMAIN)."</div>";
echo "<div id='__wps__events_tab_2' style='display:none'>".__("More Information", WPS_TEXT_DOMAIN)."</div>";
echo "<div id='__wps__events_tab_3' style='display:none'>".__("Confirmation Email", WPS_TEXT_DOMAIN)."</div>";
echo "<div id='__wps__events_tab_4' style='display:none'>".__("Attendees", WPS_TEXT_DOMAIN)."</div>";
echo "<div id='__wps__events_send_email' style='display:none'>".__("Send confirmation email:", WPS_TEXT_DOMAIN)."</div>";
echo "<div id='__wps__events_replacements' style='display:none'>".__("You can use the following:", WPS_TEXT_DOMAIN)."</div>";
echo "<div id='__wps__events_pay_link' style='display:none'>".__("HTML for payment:", WPS_TEXT_DOMAIN)."</div>";
echo "<div id='__wps__events_cost' style='display:none'>".__("Price per booking:", WPS_TEXT_DOMAIN)."</div>";
echo "<div id='__wps__events_howmany' style='display:none'>".__("How many tickets to you want?", WPS_TEXT_DOMAIN)."</div>";
echo "<div id='__wps__events_labels' style='display:none'>".__("Ref|User|Booked|Confirmation email sent|# Tickets|Payment Confirmed|Actions|Confirm attendee|Send Mail|Re-send confirmation email|Remove attendee|Confirm payment", WPS_TEXT_DOMAIN)."</div>";
echo "<div id='__wps__gallery_labels' style='display:none'>".__("Rename|Photo renamed.|Drag thumbnails to re-order, and then|save|Delete this photo|Set as album cover", WPS_TEXT_DOMAIN)."</div>";

?>
