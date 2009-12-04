<?php


/**
 * @var String [valid_email]
 * 
 * <config>
 * Email:
 *     type: String
 *     rules: [valid_email]
 * </config>
 */
class Email extends String {
	
	public function valid_email() {
		return true;		
	}
	
}
?>