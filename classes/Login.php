<?php


/**
 * <config>
 * Login:
 *     type: qxString
 *     rules: [alpha_digit_underscore]	
 * </config>
 */
class Login extends qxString {
	function alpha_digit_underscore() {
		return true;
	}
	
	function unique() {
		return true;
	}
	
}
?>