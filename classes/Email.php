<?php


/**
 * <config>
 * Email:
 *     type: qxString
 *     rules: [valid_email]
 * </config>
 */
class Email extends qxString {
	
	public function valid_email() {
		return true;		
	}
	
}
?>