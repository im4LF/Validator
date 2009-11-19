<?php

/**
 * <config>
 * UserAsPartner:
 *     fields:
 *         login:
 *             type: Login
 *             rules: [required, unique, >min(6)@required]
 *         company:
 *             type: qxString 
 *     rules:
 *         - -info.*name.req*
 *         - info.surname.required@first 
 *         - email.required@first
 *         - info.confirm_email.eq(.email) 
 *         #- email.eq(.info.confirm_email)
 * </config>
 */
class UserAsPartner extends User {
}
?>