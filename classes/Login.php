<?php


/**
 * @var String [alpha_digit_underscore]
 * 
 * <config>
 * Login:
 *     type: String
 *     rules: [alpha_digit_underscore]	
 * </config>
 */
class Login extends String {
	function alpha_digit_underscore() {
		return true;
	}
	
	function unique() {
		return true;
	}
	
}
?>