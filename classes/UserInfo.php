<?php


/**
 * @var String surname
 * @var String firstname
 * @var String lastname
 * @var ExtEmail confirm_email
 * @var ExtEmail org_email
 * @rules [surname.required, firstname.required, lastname.required, org_email.neq(.confirm_email)
 * 
 * <config>
 * UserInfo:
 *     properties:
 *         surname: 
 *             type: String
 *         firstname: 
 *             type: String
 *         lastname: 
 *             type: String
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
class UserInfo extends Object {
}
?>