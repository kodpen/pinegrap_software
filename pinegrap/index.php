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

// the following constant is used by init.php in order to determine if a request for index.php
// is the index.php in the software root or the software directory, in order to determine path
define('NON_ROOT_INDEX', TRUE);

include('init.php');

$liveform = new liveform('login');

// If the request used the old u & p field names, then store those values in new field names.
// We do this for backwards compatibility reasons so if someone bookmarked a login url or has a
// customized form that posts to this script, then their old system will continue to work.
if (
    isset($_REQUEST['u'])
    and isset($_REQUEST['p'])
    and !isset($_REQUEST['email'])
    and !isset($_REQUEST['password'])
) {
    $_REQUEST['email'] = $_REQUEST['u'];
    $_REQUEST['password'] = $_REQUEST['p'];
}

// if the user has not submitted the form yet, then output form
if (!isset($_REQUEST['email'])) {
    // If there is not an error and
    // if the user is already logged in, then send user to login home.
    // For the registration entrance and membership entrance scripts, we allow the
    // form to be shown again for a user with edit access so the user can see what the screen looks like,
    // when the user is logged in, however we have decided not to do that for this script,
    // because many people might currently go to /[SOFTWARE_DIRECTORY]/ as a shortcut
    // to get into the backed of the system when they are already logged in.
    if (
        ($liveform->check_form_errors() == FALSE)
        && (isset($_SESSION['sessionusername']) == true)
        && (isset($_SESSION['sessionpassword']) == true)
        && (validate_login($_SESSION['sessionusername'], $_SESSION['sessionpassword']) == true)
    ) {
        send_user_to_login_home();
    }

    echo get_login_screen();
    $liveform->remove();
    
// else user has completed login form
} else {

    validate_token_field();
    
    require_cookies();

    check_banned_ip_addresses('login');
    
    $liveform->remove();
    $liveform->add_fields_to_session();
    
    $username = $liveform->get_field_value('email');
    $password = md5($liveform->get_field_value('password'));
    
    $liveform->validate_required_field('email', 'Email or username is required.');
    $liveform->validate_required_field('password', 'Password is required.');
    
    // if there is not already an error, validate login
    if ($liveform->check_form_errors() == false) {
        // if login is not valid, check which part of login is invalid
        if (validate_login($username, $password) == false) {
            // If email/username exists, password is incorrect, so tell visitor that
            if (validate_username($username) == true) {
                log_activity('access denied (password invalid) (email or username: ' . $username . ')', 'UNKNOWN');
                
                if (FORGOT_PASSWORD_LINK == true) {
                    $forgot_password_message = ' If you have forgotten your password, please click on the forgot password link below.';
                }
                
                $liveform->mark_error('password', 'The password you entered is incorrect. Please remember that passwords are case sensitive.' . $forgot_password_message);
                $liveform->assign_field_value('email', $username);
                $liveform->assign_field_value('password', '');
                
            // Otherwise email/username does not exist, so tell visitor that
            } else {
                
                log_activity('access denied (email or username invalid: ' . $username . ')', 'UNKNOWN');

                // If there is an "@" in the email/username, then the visitor probably attempted to
                // enter an email address, so customize message for that.
                if (mb_strpos($username, '@') !== false) {
                    $liveform->mark_error('email',
                        'The email address you entered could not be found.');

                // Otherwise visitor probably tried to enter a username, so customize message.
                } else {
                    $liveform->mark_error('email',
                        'The username you entered could not be found. You might try entering an email address instead.');
                }
            }
        }
    }
    
    // if there is an error with the form, then send user back to form
    if ($liveform->check_form_errors() == true) {
        // if this post came from a login_region, send user to send to
        if ((isset($_POST['login_region'])) && ($_POST['login_region'] == 'true')) {
            header('Location: ' . URL_SCHEME . HOSTNAME . $_POST['send_to']);
            exit();
        } else {
            // send user back to standard login page
            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/');
            exit();
        }
    }

    // Get the actual username for the user, because the user probably entered
    // an email address for the username field.  We need the actual username
    // because it is important that we store the actual username in the session and cookies.
    $username = db_value(
        "SELECT user_username
        FROM user
        WHERE
            (
                (user_username = '" . escape($username) . "')
                OR (user_email = '" . escape($username) . "')
            )
            AND (user_password = '" . escape($password) . "')");
    
    // if remember me feature is enabled then deal with it
    if (REMEMBER_ME == TRUE) {
        // if the user selected to be remembered, then add cookies for that
        if ($liveform->get_field_value('remember_me') == 1) {
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
                setcookie('software[password]', $password, time() + 315360000, '/', '', $secure, true);

            // Otherwise store login info in cookies without httponly cookie.
            } else {
                setcookie('software[username]', $username, time() + 315360000, '/', '', $secure);
                setcookie('software[password]', $password, time() + 315360000, '/', '', $secure);
            }

            // add cookie to remember that the user checked the remember me check box,
            // so that if the user logs out we can check the check box the next time by default for the user
            setcookie('software[remember_me]', 'true', time() + 315360000, '/');

        // else the user did not select to be remembered, so add a different cookie
        } else {
            // add cookie to remember the the user did not check the remember me check box,
            // so that the remember me check box will not be checked by default next time
            setcookie('software[remember_me]', 'false', time() + 315360000, '/');
        }
    }
    
    log_activity("user logged in", $username);
    
    $_SESSION['sessionusername'] = $username;
    $_SESSION['sessionpassword'] = $password;

    require_once(dirname(__FILE__) . '/connect_user_to_order.php');
    connect_user_to_order();
    
    $liveform->remove();
    
    send_user_to_login_home();
}