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

$login_form = new liveform('login');
$register_form = new liveform('register');

// If the user is already logged in and has a user role, then determine if we should send user to login home.
// We don't want to send managers and above to the login home, because we want to allow them to
// view this screen in case they want to edit it.
if (
    (USER_LOGGED_IN == true)
    && (USER_ROLE == 3)
) { 
    // Get the folder for the registration entrance page, so we can determine if
    // the user has edit access to the page.
    $query = "SELECT page_folder AS folder_id FROM page WHERE page_type = 'registration entrance'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);
    $folder_id = $row['folder_id'];

    // If there is not a registration entrance page or if the user does not have
    // edit access to the registration entrance page, then send user to login home.
    // We don't want to send users with edit access to the login home,
    // because we want to allow them to view this screen in case they want to edit it.
    if (
        ($folder_id == '')
        || (check_edit_access($folder_id) == false)
    ) {
        send_user_to_login_home();
    }
}

// if the user has not submitted the form yet, then output form
if (!$_POST) {
    echo get_registration_entrance_screen();
    $login_form->remove();
    $register_form->remove();
    
// else user has completed registration form, so process form
} else {
    validate_token_field();

    require_cookies();
    
    $query_string = '';
    
    // prepare to add guest value to query string
    if (($_POST['allow_guest'] == "true") || ($_GET['allow_guest'] == "true")) {
        $query_string = '?allow_guest=true';
    }
    
    // if there is a send to, then add send to to query string
    if ((isset($_POST['send_to']) == true) && ($_POST['send_to'] != '')) {
        // if query string is blank, then add question mark
        if ($query_string == '') {
            $query_string .= '?';
            
        // else the query string is not blank, so add ampersand
        } else {
            $query_string .= '&';
        }
        
        $query_string .= 'send_to=' . urlencode($_POST['send_to']);
    }
    
    // if the login form was submitted
    if ($_POST['login'] == 'true') {

        $login_form->remove();
        $login_form->add_fields_to_session();

        check_banned_ip_addresses('login');

        $username = $login_form->get_field_value('email');
        $password = md5($login_form->get_field_value('password'));
        
        $login_form->validate_required_field('email', 'Email or username is required.');
        $login_form->validate_required_field('password', 'Password is required.');
        
        // if there is not already an error, validate login
        if ($login_form->check_form_errors() == false) {
            // if login is not valid, check which part of login is invalid
            if (validate_login($username, $password) == false) {
                // If email/username exists, password is incorrect, so tell visitor that
                if (validate_username($username) == true) {
                    log_activity('access denied (password invalid) (email or username: ' . $username . ')', 'UNKNOWN');
                    
                    if (FORGOT_PASSWORD_LINK == true) {
                        $forgot_password_message = ' If you have forgotten your password, please click on the forgot password link below.';
                    }
                    
                    $login_form->mark_error('password', 'The password you entered is incorrect. Please remember that passwords are case sensitive.' . $forgot_password_message);
                    $login_form->assign_field_value('password', '');
                    
                // Otherwise email/username does not exist, so tell visitor that
                } else {

                    log_activity('access denied (email or username invalid: ' . $username . ')', 'UNKNOWN');

                    // If there is an "@" in the email/username, then the visitor probably attempted to
                    // enter an email address, so customize message for that.
                    if (mb_strpos($username, '@') !== false) {
                        $login_form->mark_error('email',
                            'The email address you entered could not be found. Please register if you have not already done so.');

                    // Otherwise visitor probably tried to enter a username, so customize message.
                    } else {
                        $login_form->mark_error('email',
                            'The username you entered could not be found. You might try entering an email address instead, or register if you have not already done so.');
                    }
                }
            }
        }
        
        // if an error does not exist, user has logged in successfully, so forward user to next screen
        if ($login_form->check_form_errors() == false) {
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
                if ($login_form->get_field_value('login_remember_me') == 1) {
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
            
            $_SESSION['sessionusername'] = $username;
            $_SESSION['sessionpassword'] = $password;

            require_once(dirname(__FILE__) . '/connect_user_to_order.php');
            connect_user_to_order();
            
            // validation complete - we may now continue
            log_activity("user logged into registration area", $username);
            
            $login_form->remove();
            
            send_user_to_login_home();
            
        // else an error does exist
        } else {
            // send user back to previous form
            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/registration_entrance.php' . $query_string);
        }
        
    // else if the continue as a guest form was submitted
    } elseif ($_POST['continue'] == 'true') {
        // Update their session that they are a guest.
        $_SESSION['software']['guest'] = true;
        
        // forward user to send to
        header('Location: ' . URL_SCHEME . HOSTNAME . $_POST['send_to']);
        
    // else if the register form was submitted
    } elseif ($_POST['register'] == 'true') {

        $register_form->remove();
        $register_form->add_fields_to_session();

        check_banned_ip_addresses('register');
        
        $register_form->validate_required_field('first_name', 'First Name is required.');
        $register_form->validate_required_field('last_name', 'Last Name is required.');
        $register_form->validate_required_field('username', 'Username is required.');
        $register_form->validate_required_field('email', 'Email is required.');
        $register_form->validate_required_field('email_verify', 'Please type email address again.');
        $register_form->validate_required_field('password', 'New Password is required.');
        $register_form->validate_required_field('password_verify', 'Please type new password again.');

        // if there is not already an error for the username field, check to see if username is already in use
        if ($register_form->check_field_error('username') == false) {
            // check to see if username is already in use
            $query = "SELECT user_id FROM user WHERE (user_username = '" . escape($register_form->get_field_value('username')) . "') OR (user_email = '" . escape($register_form->get_field_value('username')) . "')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed');
            if (mysqli_num_rows($result) > 0) {
                $register_form->mark_error('username', 'The username you entered is already in use. Please enter a different username.');
            }
        }

        // if there is not already an error for the e-mail address field, check to see if e-mail address and verification e-mail address do not match
        if (($register_form->check_field_error('email') == false) && ($register_form->check_field_error('email_verify') == false)) {
            if ($register_form->get_field_value('email') != $register_form->get_field_value('email_verify')) {
                $register_form->mark_error('email', 'The two email addresses you entered did not match.');
                $register_form->mark_error('email_verify');
            }
        }

        // if there is not already an error for the e-mail address field, validate e-mail address
        if ($register_form->check_field_error('email') == false) {
            if ((validate_email_address($register_form->get_field_value('email')) == false)) {
                $register_form->mark_error('email', 'The email address you entered is invalid.');
                $register_form->mark_error('email_verify');
            }
        }

        // if there is not already an error for the e-mail address field, check to see if e-mail address is already in use
        if ($register_form->check_field_error('email') == false) {
            $query = "SELECT user_id FROM user WHERE (user_email = '" . escape($register_form->get_field_value('email')) . "') OR (user_username = '" . escape($register_form->get_field_value('email')) . "')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed');
            if (mysqli_num_rows($result) > 0) {
                $register_form->mark_error('email', 'The email address you entered is already in use. Please enter a different email address.');
                $register_form->mark_error('email_verify');
            }
        }
        
        // if there is not already an error for the password field, check to see if password and verification password do not match
        if (($register_form->check_field_error('password') == false) && ($register_form->check_field_error('password_verify') == false)) {
            if ($register_form->get_field_value('password') != $register_form->get_field_value('password_verify')) {
                $register_form->mark_error('password', 'The two passwords you entered did not match.');
                $register_form->mark_error('password_verify');
                $register_form->assign_field_value('password', '');
                $register_form->assign_field_value('password_verify', '');
            }
        }
        
        // if there is not already an error for the password field,
        // and there is not already an error for the password verify field,
        // and strong password is enabled,
        // and the password is not strong,
        // then mark error for password fields
        if (
            ($register_form->check_field_error('password') == false)
            && ($register_form->check_field_error('password_verify') == false)
            && (STRONG_PASSWORD == true)
            && (validate_password_strength($register_form->get_field_value('password')) == false)
        ) {
            $register_form->mark_error('password', 'The password you entered does not meet the requirements. Please enter a different password.');
            $register_form->mark_error('password_verify');
            $register_form->assign_field_value('password', '');
            $register_form->assign_field_value('password_verify', '');
        }

        // Check to see if the password is in the password hint, and mark an error if so.
        if (($register_form->get_field_value('password_hint') != '') && ($register_form->get_field_value('password') != '')) {
            if (
                ($register_form->get_field_value('password_hint') == $register_form->get_field_value('password'))
                 || (mb_strpos(mb_strtolower($register_form->get_field_value('password_hint')), mb_strtolower($register_form->get_field_value('password'))) !== false) 
               ) {
                    $register_form->mark_error('password_hint', 'Your password hint cannot contain your password.');
                    $register_form->assign_field_value('password_hint', '');
            }
        }
        
        // if CAPTCHA is enabled then validate CAPTCHA
        if (CAPTCHA == TRUE) {
            validate_captcha_answer($register_form);
        }

        // if an error does not exist
        if ($register_form->check_form_errors() == false) {

            // create contact
            $query = "INSERT INTO contacts (
                         first_name,
                         last_name,
                         email_address,
                         opt_in,
                         timestamp)
                     VALUES (" .
                         "'" . escape($register_form->get_field_value('first_name')) . "', " .
                         "'" . escape($register_form->get_field_value('last_name')) . "', " .
                         "'" . escape($register_form->get_field_value('email')) . "', " .
                         "'" . e($register_form->get('opt_in')) . "', " .
                         "UNIX_TIMESTAMP())";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed');

            // get contact id so we can connect user to contact
            $contact_id = mysqli_insert_id(db::$con);

            // Update opt-in status for all contacts with this same email address, so the opt-in
            // status is the same for all.
            db(
                "UPDATE contacts SET opt_in = '" . e($register_form->get('opt_in')) . "'
                WHERE email_address = '" . e($register_form->get('email')) . "'");
            
            // check if registration contact group exists
            $query = "SELECT id FROM contact_groups WHERE id = '" . REGISTRATION_CONTACT_GROUP_ID  . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // if registration contact group exists, assign contact to contact group
            if (mysqli_num_rows($result) > 0) {
                $query =
                    "INSERT INTO contacts_contact_groups_xref (
                        contact_id,
                        contact_group_id)
                    VALUES (
                        '" . $contact_id . "',
                        '" . REGISTRATION_CONTACT_GROUP_ID . "')";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            }

            $sql_start_page_column = "";
            $sql_start_page_value = "";

            // If there is a start page set in the session, then set start page for user.
            if ($_SESSION['software']['start_page_id']) {
                $sql_start_page_column = "user_home,";
                $sql_start_page_value = "'" . escape($_SESSION['software']['start_page_id']) . "',";
            }

            // create user
            $query = "INSERT INTO user (
                         user_username,
                         user_email,
                         user_password,
                         user_role,
                         $sql_start_page_column
                         user_password_hint,
                         user_contact,
                         user_timestamp)
                     VALUES (" .
                         "'" . escape($register_form->get_field_value('username')) . "', " .
                         "'" . escape($register_form->get_field_value('email')) . "', " .
                         "md5('" . escape($register_form->get_field_value('password')) . "'), " .
                         "'3', " .
                         $sql_start_page_value .
                         "'" . escape($register_form->get_field_value('password_hint')) . "', " .
                         $contact_id . ", " .
                         "UNIX_TIMESTAMP())";

            $result = mysqli_query(db::$con, $query) or output_error('Query failed');

            // if remember me feature is enabled then deal with it
            if (REMEMBER_ME == TRUE) {
                // if the user selected to be remembered, then add cookies for that
                if ($register_form->get_field_value('register_remember_me') == 1) {
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
                        setcookie('software[username]', $register_form->get_field_value('username'), time() + 315360000, '/', '', $secure, true);
                        setcookie('software[password]', md5($register_form->get_field_value('password')), time() + 315360000, '/', '', $secure, true);

                    // Otherwise store login info in cookies without httponly cookie.
                    } else {
                        setcookie('software[username]', $register_form->get_field_value('username'), time() + 315360000, '/', '', $secure);
                        setcookie('software[password]', md5($register_form->get_field_value('password')), time() + 315360000, '/', '', $secure);
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
            
            // add login information to session
            $_SESSION['sessionusername'] = $register_form->get_field_value('username');
            $_SESSION['sessionpassword'] = md5($register_form->get_field_value('password'));

            require_once(dirname(__FILE__) . '/connect_user_to_order.php');
            connect_user_to_order();
            
            // if there is a registration e-mail address, then send registration confirmation via e-mail to e-mail address
            if (REGISTRATION_EMAIL_ADDRESS) {
                
                email(array(
                    'to' => REGISTRATION_EMAIL_ADDRESS,
                    'from_name' => ORGANIZATION_NAME,
                    'from_email_address' => EMAIL_ADDRESS,
                    'subject' => 'Registration Confirmation',
                    'format' => 'html',
                    'body' => get_registration_confirmation_screen()));

            }
            
            $register_form->remove();
            
            $query_string = '';
            
            // if there is a send to, then add send to to query string
            if ((isset($_POST['send_to']) == true) && ($_POST['send_to'] != '')) {
                $query_string = '?send_to=' . urlencode($_POST['send_to']);
            }
            
            // send user to registration confirmation page
            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/registration_confirmation.php' . $query_string);

        // else an error does exist
        } else {
            // send user back to previous form
            header('Location: ' . URL_SCHEME . HOSTNAME . PATH . SOFTWARE_DIRECTORY . '/registration_entrance.php' . $query_string);
        }
    }
}