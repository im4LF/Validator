<?php


/**
 * @var Email [+exists_email]
 * 
 * <config>
 * ExtEmail:
 *     type: Email
 *     rules: [+exists_email]
 * </config>
 */
class ExtEmail extends Email {
	
	public function exists_email() {
		return true;
	}
	
}
?>