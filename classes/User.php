<?php


/**
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
class User extends qxObject {
}

?>