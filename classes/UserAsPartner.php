<?php

/**
 * @var Login login [required, unique, >min(6)@required]
 * @var String company
 * @rules [-info.*name.req*, info.surname.required@first, email.required@first, info.confirm_email.eq(.email)]
 * 
 * @action login:post/json	ajaxNewLogin [after, -before]
 * 
 * <config>
 * UserAsPartner:
 *     properties:
 *         login:
 *             type: Login
 *             rules: [required, unique, >min(6)@required]
 *         company:
 *             type: String 
 *     rules:
 *         - -info.*name.req*
 *         - info.surname.required@first 
 *         - email.required@first
 *         - info.confirm_email.eq(.email) 
 * </config>
 */
class UserAsPartner extends User {
}
?>