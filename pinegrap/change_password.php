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

$liveform = new liveform('change_password');

validate_token_field();

$liveform->add_fields_to_session();

// validate required fields
$liveform->validate_required_field('email_address', 'Email or username is required.');
$liveform->validate_required_field('current_password', 'Current Password is required.');
$liveform->validate_required_field('new_password', 'New Password is required.');
$liveform->validate_required_field('new_password_verify', 'Please type new password again.');

// if there is not already an error for email_address and current password fields, validate login
if (($liveform->check_field_error('email_address') == false) && ($liveform->check_field_error('current_password') == false)) {
    // if login is not valid, check which part of login is invalid
    if (validate_login($liveform->get_field_value('email_address'), md5($liveform->get_field_value('current_password'))) == false) {
        // if email_address exists, password is incorrect, so output error about password being incorrect
        if (validate_username($liveform->get_field_value('email_address')) == true) {
            log_activity('access denied to change password (password invalid) (email or username: ' . $liveform->get_field_value('email_address') . ')', 'UNKNOWN');
            $liveform->mark_error('current_password', 'The current password you entered is incorrect. Please remember that passwords are case sensitive.');
            $liveform->assign_field_value('current_password', '');
            
        // else email_address does not exist, so output error about email_address not existing
        } else {
            log_activity('access denied to change password (email or username invalid: ' . $liveform->get_field_value('email_address') . ')', 'UNKNOWN');
            $liveform->mark_error('email_address', 'The email address or username you entered could not be found.');
        }
    }
}

// if there is not already an error for the new password fields, check to see if new password and new password verify do not match
if (($liveform->check_field_error('new_password') == false) && ($liveform->check_field_error('new_password_verify') == false)) {
    if ($liveform->get_field_value('new_password') != $liveform->get_field_value('new_password_verify')) {
        $liveform->mark_error('new_password', 'The two new passwords you entered did not match.');
        $liveform->mark_error('new_password_verify');
        $liveform->assign_field_value('new_password', '');
        $liveform->assign_field_value('new_password_verify', '');
    }
}

// if there is not already an error for the new password field,
// and there is not already an error for the new password verify field,
// and strong password is enabled,
// and the new password is not strong,
// then mark error for new password fields
if (
    ($liveform->check_field_error('new_password') == false)
    && ($liveform->check_field_error('new_password_verify') == false)
    && (STRONG_PASSWORD == true)
    && (validate_password_strength($liveform->get_field_value('new_password')) == false)
) {
    $liveform->mark_error('new_password', 'The new password you entered does not meet the requirements. Please enter a different new password.');
    $liveform->mark_error('new_password_verify');
    $liveform->assign_field_value('new_password', '');
    $liveform->assign_field_value('new_password_verify', '');
}

// Check to see if the new password is in the password hint, and mark an error if so.
if (($liveform->get_field_value('password_hint') != '') && ($liveform->get_field_value('new_password') != '')) {
    if (
        ($liveform->get_field_value('password_hint') == $liveform->get_field_value('new_password'))
         || (mb_strpos(mb_strtolower($liveform->get_field_value('password_hint')), mb_strtolower($liveform->get_field_value('new_password'))) !== false) 
       ) {
            $liveform->mark_error('password_hint', 'Your password hint cannot contain your password.');
            $liveform->assign_field_value('password_hint', '');
    }
}

// if an error exists, then send user back to previous screen
if ($liveform->check_form_errors()) {
    go(get_page_type_url('change password'));
}

// Get the actual username for the user, because the user probably entered
// an email address for the username field.  We need the actual username
// because it is important that we store the actual username in the session and cookies.
$username = db_value(
    "SELECT user_username
    FROM user
    WHERE
        (
            (user_username = '" . escape($liveform->get_field_value('email_address')) . "')
            OR (user_email = '" . escape($liveform->get_field_value('email_address')) . "')
        )
        AND (user_password = '" . escape(md5($liveform->get_field_value('current_password'))) . "')");

// update password for user
$query = 
    "UPDATE user 
    SET 
        user_password = '" . escape(md5($liveform->get_field_value('new_password'))) . "',
        user_password_hint = '" . escape($liveform->get_field_value('password_hint')) . "'
    WHERE user_username = '" . escape($username) . "'";
$result = mysqli_query(db::$con, $query) or output_error('Query failed');

// assign username and new password to session
$_SESSION['sessionusername'] = $username;
$_SESSION['sessionpassword'] = md5($liveform->get_field_value('new_password'));
log_activity("user changed password", $username);

// If remember me is on and the user has chosen to be remembered,
// and the user is not logged in as a different user,
// then update cookie with new login information.
if (
    (REMEMBER_ME == true)
    && (isset($_COOKIE['software']['username']) == true)
    && ($_SESSION['software']['logged_in_as_different_user'] == false)
) {
    $secure = false;

    // If secure mode is enabled, then prepare secure cookie values.
    if (URL_SCHEME == 'https://') {
        $secure = true;
    }

    // If PHP version is greater than or equal to 5.2.0 then add cookies
    // for login info so that user will be logged in automatically and also
    // use httponly cookie, in order to prevent hacking methods.  PHP before 5.2.0
    // does not support setting httponly cookies.
    if (version_compare(PHP_VERSION, '5.2.0', '>=') == TRUE) {
        setcookie('software[username]', $username, time() + 315360000, '/', '', $secure, true);
        setcookie('software[password]', md5($liveform->get_field_value('new_password')), time() + 315360000, '/', '', $secure, true);

    // Otherwise store login info in cookies without httponly cookie.
    } else {
        setcookie('software[username]', $username, time() + 315360000, '/', '', $secure);
        setcookie('software[password]', md5($liveform->get_field_value('new_password')), time() + 315360000, '/', '', $secure);
    }
}

$my_account = new liveform('my_account');

$my_account->add_notice('Your password has been changed.');

$liveform->remove();

go(get_page_type_url('my account'));