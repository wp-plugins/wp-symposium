<?php

// ******************************** WP SYMPOSIUM WPS UI CLASS ***********************************

class wps_ui {

	function whatsup($box_class='input-field', $whatsup_text='') {
		$whatsup = $whatsup_text ? $whatsup_text : WPS_STATUS_POST;		
		echo  '<input type="text" id="symposium_status" name="status" class="'.$box_class.'" onblur ="this.value=(this.value==\'\') ? \''.addslashes($whatsup_text).'\' : this.value;" onfocus="this.value=(this.value==\''.addslashes($whatsup_text).'\') ? \'\' : this.value;" value="'.stripslashes($whatsup_text).'" />';
	}

	function whatsup_button($button_class='symposium-button', $button_text='Update') {
		echo '<input id="symposium_add_update" type="submit" class="'.$button_class.'" value="'.__($button_text, 'wp-symposium').'" /> ';
	}
    
}


?>