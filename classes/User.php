<?php


/**
 * @var Login login
 * @var Password password [eq(.confirm_password)]
 * @var Password confirm_password
 * @var Email email [required@first]
 * @var UserInfo info
 * @rules [login.required@first, password.required, email.required]
 * 
 * <config>
 * User:
 *     properties:
 *         login: 
 *             type: Login
 *         password: 
 *             type: Password
 *             rules: [eq(.confirm_password)]
 *         confirm_password:
 *             type: Password
 *         email: 
 *             type: Email
 *             rules: [required@first]
 *         info: 
 *             type: UserInfo
 *     rules: [login.required@first, password.required, email.required]
 * </config>
 */
class User extends Object {
}

?>