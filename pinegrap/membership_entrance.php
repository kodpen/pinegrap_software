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

$liveform = new liveform('membership_entrance');

// If the user is already logged in and has a user role, then determine if we should send user to login home.
// We don't want to send managers and above to the login home, because we want to allow them to
// view this screen in case they want to edit it.
if (
    (USER_LOGGED_IN == true)
    && (USER_ROLE == 3)
) {
    // Get the folder for the membership entrance page, so we can determine if
    // the user has edit access to the page.
    $query = "SELECT page_folder AS folder_id FROM page WHERE page_type = 'membership entrance'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    $row = mysqli_fetch_assoc($result);
    $folder_id = $row['folder_id'];

    // If there is not a membership entrance page or if the user does not have
    // edit access to the membership entrance page, then send user to login home.
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
    print get_membership_entrance_screen();
    $liveform->remove_form('membership_entrance');
    
// else user has completed membership form, so process form
} else {
    validate_token_field();
    
    require_cookies();
    
    $liveform->remove_form('membership_entrance');
    $liveform->add_fields_to_session();
    
    $query_string = '';
    
    // if there is a send to, then add send to to query string
    if ((isset($_POST['send_to']) == true) && ($_POST['send_to'] != '')) {
        $query_string = '?send_to=' . urlencode($_POST['send_to']);
    }
    
    // if the login form was submitted
    if ($_POST['login'] == 'true') {
        check_banned_ip_addresses('login');

        $username = $liveform->get_field_value('u');
        $password = md5($liveform->get_field_value('p'));
        
        $liveform->validate_required_field('u', 'Email or username is required.');
        $liveform->validate_required_field('p', 'Password is required.');
        
        // if there is not already an error, validate login
        if ($liveform->check_form_errors() == false) {
            // if login is not valid, check which part of login is invalid
            if (validate_login($username, $password) == false) {
                // if username exists, password is incorrect, so output error about password being incorrect
                if (validate_username($username) == true) {
                    log_activity('access denied (password invalid) (email or username: ' . $username . ')', 'UNKNOWN');
                    
                    if (FORGOT_PASSWORD_LINK == true) {
                        $forgot_password_message = ' If you have forgotten your password, please click on the forgot password link below.';
                    }
                    
                    $liveform->mark_error('p', 'The password is incorrect. Please remember that passwords are case sensitive.' . $forgot_password_message);
                    $liveform->assign_field_value('p', '');
                    
                // else username does not exist, so output error about username not existing
                } else {
                    log_activity('access denied (email or username invalid: ' . $username . ')', 'UNKNOWN');
                    $liveform->mark_error('u', 'The email address or username you entered could not be found. Please register if you have not already done so.');
                }
            }
        }
        
        // if an error does not exist, user has logged in successfully, so forward user to next screen
        if ($liveform->check_form_errors() == false) {
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
                if ($liveform->get_field_value('login_remember_me') == 1) {
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
            log_activity("user logged into membership area", $username);
            
            // remove liveform because we don't need it anymore
            $liveform->remove_form('membership_entrance');
            
            send_user_to_login_home();
            
        // else an error does exist
        } else {
            // send user back to previous form
            header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/membership_entrance.php' . $query_string);
        }
        
    // else if the register form was submitted
    } elseif ($_POST['register'] == 'true') {
        check_banned_ip_addresses('register as a member');
        
        $liveform->validate_required_field('first_name', 'First Name is required.');
        $liveform->validate_required_field('last_name', 'Last Name is required.');
        $liveform->validate_required_field('member_id', h(MEMBER_ID_LABEL) . ' is required.');
        $liveform->validate_required_field('username', 'Username is required.');
        $liveform->validate_required_field('email_address', 'Email is required.');
        $liveform->validate_required_field('email_address_verify', 'Please type email address again.');
        $liveform->validate_required_field('password', 'New Password is required.');
        $liveform->validate_required_field('password_verify', 'Please type new password again.');

        // try to find member id
        $query = "SELECT expiration_date FROM contacts WHERE member_id = '" . escape($liveform->get_field_value('member_id')) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed');
        if (mysqli_num_rows($result) == 0) {
            $liveform->mark_error('member_id', 'The ' . h(MEMBER_ID_LABEL) . ' you entered was not found.  Please enter a different ' . h(MEMBER_ID_LABEL) . '.');
        } else {
            $row = mysqli_fetch_assoc($result);
            $expiration_date = $row['expiration_date'];
        }
        
        // if there is not already an error for the username field, check to see if username is already in use
        if ($liveform->check_field_error('username') == false) {
            // check to see if username is already in use
            $query = "SELECT user_id FROM user WHERE (user_username = '" . escape($liveform->get_field_value('username')) . "') OR (user_email = '" . escape($liveform->get_field_value('username')) . "')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed');
            if (mysqli_num_rows($result) > 0) {
                $liveform->mark_error('username', 'The username you entered is already in use. Please enter a different username.');
            }
        }

        // if there is not already an error for the e-mail address field, check to see if e-mail address and verification e-mail address do not match
        if (($liveform->check_field_error('email_address') == false) && ($liveform->check_field_error('email_address_verify') == false)) {
            if ($liveform->get_field_value('email_address') != $liveform->get_field_value('email_address_verify')) {
                $liveform->mark_error('email_address', 'The two email addresses you entered did not match.');
                $liveform->mark_error('email_address_verify');
            }
        }

        // if there is not already an error for the e-mail address field, validate e-mail address
        if ($liveform->check_field_error('email_address') == false) {
            if ((validate_email_address($liveform->get_field_value('email_address')) == false)) {
                $liveform->mark_error('email_address', 'The email address you entered is invalid.');
                $liveform->mark_error('email_address_verify');
            }
        }

        // if there is not already an error for the e-mail address field, check to see if e-mail address is already in use
        if ($liveform->check_field_error('email_address') == false) {
            $query = "SELECT user_id FROM user WHERE (user_email = '" . escape($liveform->get_field_value('email_address')) . "') OR (user_username = '" . escape($liveform->get_field_value('email_address')) . "')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed');
            if (mysqli_num_rows($result) > 0) {
                $liveform->mark_error('email_address', 'The email address you entered is already in use. Please enter a different email address.');
                $liveform->mark_error('email_address_verify');
            }
        }
        
        // if there is not already an error for the password field, check to see if password and verification password do not match
        if (($liveform->check_field_error('password') == false) && ($liveform->check_field_error('password_verify') == false)) {
            if ($liveform->get_field_value('password') != $liveform->get_field_value('password_verify')) {
                $liveform->mark_error('password', 'The two passwords you entered did not match.');
                $liveform->mark_error('password_verify');
                $liveform->assign_field_value('password', '');
                $liveform->assign_field_value('password_verify', '');
            }
        }
        
        // if there is not already an error for the password field,
        // and there is not already an error for the password verify field,
        // and strong password is enabled,
        // and the password is not strong,
        // then mark error for password fields
        if (
            ($liveform->check_field_error('password') == false)
            && ($liveform->check_field_error('password_verify') == false)
            && (STRONG_PASSWORD == true)
            && (validate_password_strength($liveform->get_field_value('password')) == false)
        ) {
            $liveform->mark_error('password', 'The password you entered does not meet the requirements. Please enter a different password.');
            $liveform->mark_error('password_verify');
            $liveform->assign_field_value('password', '');
            $liveform->assign_field_value('password_verify', '');
        }
        
        // Check to see if the password is in the password hint, and mark an error if so.
        if (($liveform->get_field_value('password_hint') != '') && ($liveform->get_field_value('password') != '')) {
            if (
                ($liveform->get_field_value('password_hint') == $liveform->get_field_value('password'))
                || (mb_strpos(mb_strtolower($liveform->get_field_value('password_hint')), mb_strtolower($liveform->get_field_value('password'))) !== false) 
               ) {
                    $liveform->mark_error('password_hint', 'Your password hint cannot contain your password.');
                    $liveform->assign_field_value('password_hint', '');
            }
        }

        // if an error does not exist
        if ($liveform->check_form_errors() == false) {
            // check for an existing contact to use
            $query = "SELECT id FROM contacts WHERE member_id = '" . escape($liveform->get_field_value('member_id')) . "' AND last_name = '" . escape($liveform->get_field_value('last_name')) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed');

            // if a contact was found
            if (mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                $contact_id = $row['id'];

                // update contact
                $query = "UPDATE contacts
                         SET
                             first_name = '" . escape($liveform->get_field_value('first_name')) . "',
                             email_address = '" . escape($liveform->get_field_value('email_address')) . "',
                             opt_in = '" . e($liveform->get('opt_in')) . "'
                         WHERE id = '$contact_id'";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed');

            // else a contact was not found
            } else {

                // create contact
                $query = "INSERT INTO contacts (
                             first_name,
                             last_name,
                             email_address,
                             member_id,
                             expiration_date,
                             opt_in,
                             timestamp)
                         VALUES (
                             '" . escape($liveform->get_field_value('first_name')) . "',
                             '" . escape($liveform->get_field_value('last_name')) . "',
                             '" . escape($liveform->get_field_value('email_address')) . "',
                             '" . escape($liveform->get_field_value('member_id')) . "',
                             '" . escape($expiration_date) . "',
                             '" . e($liveform->get('opt_in')) . "',
                             UNIX_TIMESTAMP())";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed');

                // get contact id so we can connect user to contact
                $contact_id = mysqli_insert_id(db::$con);
            }

            // Update opt-in status for all contacts with this same email address, so the opt-in
            // status is the same for all.
            db(
                "UPDATE contacts SET opt_in = '" . e($liveform->get('opt_in')) . "'
                WHERE email_address = '" . e($liveform->get('email_address')) . "'");
            
            // check if membership contact group exists
            $query = "SELECT id FROM contact_groups WHERE id = '" . MEMBERSHIP_CONTACT_GROUP_ID  . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // if membership contact group exists
            if (mysqli_num_rows($result) > 0) {
                // check if contact is already in membership contact group
                $query =
                    "SELECT contact_id
                    FROM contacts_contact_groups_xref
                    WHERE
                        (contact_id = '$contact_id')
                        AND (contact_group_id = '" . MEMBERSHIP_CONTACT_GROUP_ID . "')";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                
                // if contact is not already in contact group, then add contact to membership contact group
                if (mysqli_num_rows($result) == 0) {
                    $query =
                        "INSERT INTO contacts_contact_groups_xref (
                            contact_id,
                            contact_group_id)
                        VALUES (
                            '$contact_id',
                            '" . MEMBERSHIP_CONTACT_GROUP_ID . "')";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                }
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
                         "'" . escape($liveform->get_field_value('username')) . "', " .
                         "'" . escape($liveform->get_field_value('email_address')) . "', " .
                         "md5('" . escape($liveform->get_field_value('password')) . "'), " .
                         "'3', " .
                         $sql_start_page_value .
                         "'" . escape($liveform->get_field_value('password_hint')) . "', " .
                         $contact_id . ", " .
                         "UNIX_TIMESTAMP())";

            $result = mysqli_query(db::$con, $query) or output_error('Query failed');

            // if remember me feature is enabled then deal with it
            if (REMEMBER_ME == TRUE) {
                // if the user selected to be remembered, then add cookies for that
                if ($liveform->get_field_value('register_remember_me') == 1) {
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
                        setcookie('software[username]', $liveform->get_field_value('username'), time() + 315360000, '/', '', $secure, true);
                        setcookie('software[password]', md5($liveform->get_field_value('password')), time() + 315360000, '/', '', $secure, true);

                    // Otherwise store login info in cookies without httponly cookie.
                    } else {
                        setcookie('software[username]', $liveform->get_field_value('username'), time() + 315360000, '/', '', $secure);
                        setcookie('software[password]', md5($liveform->get_field_value('password')), time() + 315360000, '/', '', $secure);
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
            $_SESSION['sessionusername'] = $liveform->get_field_value('username');
            $_SESSION['sessionpassword'] = md5($liveform->get_field_value('password'));

            require_once(dirname(__FILE__) . '/connect_user_to_order.php');
            connect_user_to_order();
            
            // if there is a membership e-mail address, then send membership confirmation via e-mail to e-mail address
            if (MEMBERSHIP_EMAIL_ADDRESS) {
                
                email(array(
                    'to' => MEMBERSHIP_EMAIL_ADDRESS,
                    'from_name' => ORGANIZATION_NAME,
                    'from_email_address' => EMAIL_ADDRESS,
                    'subject' => 'Registration Confirmation',
                    'format' => 'html',
                    'body' => get_membership_confirmation_screen()));

            }
            
            // remove liveform because we don't need it anymore
            $liveform->remove_form('membership_entrance');

            // send user to membership confirmation page
            header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/membership_confirmation.php' . $query_string);

        // else an error does exist
        } else {
            // send user back to previous form
            header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . PATH . SOFTWARE_DIRECTORY . '/membership_entrance.php' . $query_string);
        }
    }
}