<?php


/**
 * <config>
 * UserInfo:
 *     properties:
 *         surname: 
 *             type: qxString
 *         firstname: 
 *             type: qxString
 *         lastname: 
 *             type: qxString
 *         confirm_email:
 *             type: ExtEmail
 *         org_email:
 *             type: ExtEmail
 *     rules: 
 *         - surname.required
 *         - firstname.required
 *         - lastname.required
 *         - org_email.neq(.confirm_email)         
 * </config>
 */
class UserInfo extends qxObject {
	public $surname;
	public $firstname;
	public $lastname;
	public $confirm_email;
	public $org_email;
}
?>