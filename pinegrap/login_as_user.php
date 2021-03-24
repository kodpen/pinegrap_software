<?php

/**
 *
 * liveSite - Enterprise Website Platform
 *
 * @author      Camelback Web Architects
 * @link        https://livesite.com
 * @copyright   2001-2019 Camelback Consulting, Inc.
 * @license     https://opensource.org/licenses/mit-license.html MIT License
 *
 */

include('init.php');

$user = validate_user();

validate_area_access($user, 'manager');

validate_token_field();

// Get info for the user that the editor is trying to login as.
$user = db_item(
    "SELECT
        user_role AS role,
        user_username AS username,
        user_password AS password
    FROM user
    WHERE user_id = '" . escape($_GET['id']) . "'");

// If editor is less than an administrator role and the editor's role
// is less than or equal to the user that the editor is trying log in as,
// then output error because editor does not have access to do this.
if ((USER_ROLE > 0) && (USER_ROLE >= $user['role'])) {
    log_activity('access denied for user to login as different user because user\'s role is not high enough (' . USER_USERNAME . ' -> ' . $user['username'] . ')', USER_USERNAME);
    output_error('Access denied. <a href="javascript:history.go(-1)">Go back</a>.');
}

// If the editor is trying to login into his/her same user account, then output error.
// There is not an important reason why we don't allow a user to login as him/herself,
// however we just decided to prevent this because there is really no reason to allow it and
// it might cause confusing log messages eventually (e.g. "example_username -> example_username").
// It might also cause confusion for the user about what happens if they do that.
if ($_GET['id'] == USER_ID) {
    output_error('Access denied. You may not login as yourself. <a href="javascript:history.go(-1)">Go back</a>.');
}

// Remove session data so that user will have a fresh new session
// as different user.
session_unset();
session_destroy();

// Remove tracking code and affiliate code cookies so that
// they don't show up under different user session.
setcookie('software[tracking_code]', '', time() - 1000, '/');
setcookie('software[affiliate_code]', '', time() - 1000, '/');

// Start a new session for the different user.
session_start();

// Update session so that editor will be logged in as different user.
$_SESSION['sessionusername'] = $user['username'];
$_SESSION['sessionpassword'] = $user['password'];

// Remember that user is logged in as a different user, so we don't remove remember me login cookies
// when the user logs out from this different user account.
$_SESSION['software']['logged_in_as_different_user'] = true;

log_activity('user logged in as different user (' . USER_USERNAME . ' -> ' . $user['username'] . ')', USER_USERNAME);

send_user_to_login_home();