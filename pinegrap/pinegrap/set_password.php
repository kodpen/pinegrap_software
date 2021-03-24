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

$form = new liveform('set_password');

validate_token_field();

$form->add_fields_to_session();

$token = $form->get('k');

if (!$token) {
    log_activity('Set Password: missing token');
    output_error('Sorry, the token is missing, so we can\'t allow you to set a password.');
}

// Create a hash of the token, because that is what we store in db.
$token_hash = hash('sha256', $token);

// Check database if token is still valid
$user = db_item(
    "SELECT
        user_id AS id,
        user_username AS username,
        token_timestamp
    FROM user
    WHERE token = '" . e($token_hash) . "'");

// If query to database return no results for token
if (!$user['id']) {

    log_activity('Set Password: invalid token');

    output_error('Sorry, the token is not valid, so we can\'t allow you to set a password. The token might be old, so please <a href="' . h(get_page_type_url('forgot password')) . '">request a new email</a>.');
}

$token_timestamp = $user['token_timestamp'];
$time_24hrs = (24*60*60);

// Check to see if token has expired after 24 hours
if (($token_timestamp + $time_24hrs) < time()) {

    log_activity('Set Password: expired token');

    output_error('Sorry, the token has expired. Please <a href="' . h(get_page_type_url('forgot password')) . '">request a new email</a>.');
}   

// validate required fields
$form->validate_required_field('new_password', 'Password is required.');

// if there is not already an error for the new password field,
// and strong password is enabled,
// and the new password is not strong,
// then mark error for new password fields
if (
    ($form->check_field_error('new_password') == false)
    && (STRONG_PASSWORD == true)
    && (validate_password_strength($form->get('new_password')) == false)
) {
    $form->mark_error('new_password', 'The password you entered does not meet the requirements. Please enter a different password.');
    $form->set('new_password', '');
}

// Check to see if the new password is in the password hint, and mark an error if so.
if (($form->get('password_hint') != '') && ($form->get('new_password') != '')) {
    if (
        ($form->get('password_hint') == $form->get('new_password'))
         || (mb_strpos(mb_strtolower($form->get('password_hint')), mb_strtolower($form->get('new_password'))) !== false) 
       ) {
            $form->mark_error('password_hint', 'Your password hint cannot contain your password.');
            $form->set('password_hint', '');
    }
}

// if an error exists, then send user back to previous screen
if ($form->check_form_errors()) {
    go(get_page_type_url('set password'));
}

$password_hash = md5($form->get('new_password'));

// Update password for user.
db(
    "UPDATE user SET 
        user_password = '" . e($password_hash) . "',
        user_password_hint = '" . e($form->get('password_hint')) . "',
        token = NULL,
        token_timestamp = '0'
    WHERE user_id = '" . e($user['id']) . "'");

// Auto-login user with new info so the user does not have to login manually.
$_SESSION['sessionusername'] = $user['username'];
$_SESSION['sessionpassword'] = $password_hash;

require_once(dirname(__FILE__) . '/connect_user_to_order.php');
connect_user_to_order();

log_activity('User set password and was automatically logged in.', $user['username']);

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
        setcookie('software[password]', $password_hash, time() + 315360000, '/', '', $secure, true);

    // Otherwise store login info in cookies without httponly cookie.
    } else {
        setcookie('software[password]', $password_hash, time() + 315360000, '/', '', $secure);
    }
}

// get user information
$user = validate_user();

$home_page_name = '';

if ($user['home']) {
    $home_page_name = get_page_name($user['home']);
}

$send_to = $form->get('send_to');

$form->remove();

$form->set('screen', 'confirm');

// if there is a send to, then forward user to send to
if ($send_to != '') {
    $continue_url = $send_to;
    
// else if user has a home page, then forward user to that page
} elseif ($home_page_name != '') {
    $continue_url = PATH . encode_url_path($home_page_name);

// else if user's role is administrator, designer, or manager
// or user has edit rights
// or user has access to control panel
// then forward user to control panel welcome screen
} elseif (
    ($user['role'] < 3)
    || (no_acl_check($user['id']) == true)
    || ($user['manage_calendars'] == true)
    || ($user['manage_forms'] == true)
    || ($user['manage_visitors'] == true)
    || ($user['manage_contacts'] == true)
    || ($user['manage_emails'] == true)
    || ($user['manage_ecommerce'] == true)
    || $user['manage_ecommerce_reports']
    || (count(get_items_user_can_edit('ad_regions', $user['id'])) > 0)
) {
    $continue_url = PATH . SOFTWARE_DIRECTORY . '/welcome.php';
    
// else we do not know where to send the user, so send user to home page.
} else {
    $continue_url = PATH;
}

$form->add_notice('We have set your password, and you are now logged in. <a href="' . h(escape_url($continue_url)) . '">Continue</a>');

go(get_page_type_url('set password'));