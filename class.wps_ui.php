<?php

// ******************************** WP SYMPOSIUM WPS UI CLASS ***********************************

class wps_ui {

	function whatsup($whatsup_text='', $box_class='input-field') {
		$whatsup = $whatsup_text ? $whatsup_text : WPS_STATUS_POST;		
		return '<input type="text" id="symposium_status" name="status" class="'.$box_class.'" onblur ="this.value=(this.value==\'\') ? \''.addslashes($whatsup_text).'\' : this.value;" onfocus="this.value=(this.value==\''.addslashes($whatsup_text).'\') ? \'\' : this.value;" value="'.stripslashes($whatsup_text).'" />';
	}

	function whatsup_button($button_text='Update', $button_class='symposium-button') {
		return '<input id="symposium_add_update" type="submit" class="'.$button_class.'" value="'.__($button_text, 'wp-symposium').'" /> ';
	}

	function friendship_add($id, $message='Add a message', $sent_message='Request sent.', $box_class='input-field') {
		$html = '';
		$html .= '<div id="addasfriend_done1_'.$id.'">';
		$html .= '<div id="add_as_friend_message">';
		$html .= '<input type="text" title="'.$id.'"id="addfriend" class="'.$box_class.'" onclick="this.value=\'\'" value="'.$message.'">';
		$html .= '</div></div>';
		$html .= '<div id="addasfriend_done2_'.$id.'" style="display:none">'.$sent_message.'</div>';
		return $html;
	}
	function friendship_add_button($id, $button_class='symposium-button') {
		return '<input type="submit" title="'.$id.'" id="addasfriend" class="'.$button_class.'" value="'.__('Add', 'wp-symposium').'" />';
	}
	function friendship_cancel($id, $cancel_text='Cancel', $done_cancel_text='Cancelled', $button_class='symposium-button') {
		$html = '<input type="submit" title="'.$id.'" id="cancelfriendrequest" class="'.$button_class.'" value="'.$cancel_text.'" />';
		$html .= '<div id="cancelfriendrequest_done" style="display:none">'.$done_cancel_text.'</div>';
		return $html;
	}
	
	function activity_post($post_text='', $box_class='input-field') {
		$post_text = $post_text ? $post_text : __('Write a comment...', 'wp-symposium');		
		return '<input id="symposium_comment"  type="text" name="post_comment" class="'.$box_class.'" onblur="this.value=(this.value==\'\') ? \''.$post_text.'\' : this.value;" onfocus="this.value=(this.value==\''.$post_text.'\') ? \'\' : this.value;" value="'.$post_text.'" />';		
	}

	function activity_post_button($button_text='Post', $button_class='symposium-button') {
		return '<input id="symposium_add_comment" type="submit" class="'.$button_class.'" value="'.__($button_text, 'wp-symposium').'" /> ';
	}
	
	function poke_button($text='Hey!', $button_class='symposium-button') {
		return '<input type="submit" value="'.$text.'" class="'.$button_class.' poke-button">';
	}
	
	function profile_placeholder($view='all', $class='') {
		$html = '<div id="force_profile_page" style="display:none; border:1px solid red;">'.$view.'</div>';
		$html .= '<div id="profile_body" style="padding:0; margin: 0;" class="'.$class.'"></div>';		
		return $html;
	}
	
}

?>