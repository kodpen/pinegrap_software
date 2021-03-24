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

// if user has not yet completed form
if (!$_POST) {
    print get_email_preferences_screen();

// else user has completed form, so process form
} else {
    
    validate_token_field();
    
    include_once('liveform.class.php');
    $liveform = new liveform('email_preferences');

    $liveform->add_fields_to_session();

    $liveform->validate_required_field('email_address', 'Email is required.');
    
    // get email preferences page, if one exists
    $query = "SELECT page_id FROM page WHERE page_type = 'email preferences'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

    // if there is a email preferences page, prepare path with page name
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $email_preferences_path = PATH . encode_url_path(get_page_name($row['page_id']));
        
    // else there is not a email preferences, so prepare default path
    } else {
        $email_preferences_path = PATH . SOFTWARE_DIRECTORY . '/email_preferences.php';
    }
    
    // if an id was submitted, set id for query string in case we need to forward user back to e-mail preferences screen
    if ($_POST['id']) {
        $url_id = '?id=' . $_POST['id'];
    } else {
        $url_id = '';
    }
    
    // if there are validation errors, then send user back to e-mail preferences screen
    if ($liveform->check_form_errors() == true) {
        header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . $email_preferences_path . $url_id);
        exit();
    }

    // validate e-mail address
    if (validate_email_address($liveform->get_field_value('email_address')) == false) {
        $liveform->mark_error('email_address', 'Please enter a valid email address.');
        $liveform->assign_field_value('email_address', '');
        
        // send user back to e-mail preferences screen
        header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . $email_preferences_path . $url_id);
        exit();
    }
    
    $user_id = 0;
    
    // if user is logged in
    if ($_SESSION['sessionusername']) {
        // check to see if e-mail address is already in use
        $query =
            "SELECT user_id
            FROM user
            WHERE
                (
                    (user_username = '" . escape($liveform->get_field_value('email_address')) . "')
                    OR (user_email = '" . escape($liveform->get_field_value('email_address')) . "')
                )
                AND (user_username != '" . escape($_SESSION['sessionusername']) . "')";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // if e-mail address is already in use by another user
        if (mysqli_num_rows($result) > 0) {
            $liveform->mark_error('email_address', 'That email address is already in use.  Please enter a different email address.');
            $liveform->assign_field_value('email_address', '');
            
            // send user back to e-mail preferences screen
            header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . $email_preferences_path. $url_id);
            exit();
        }
        
        // get user information
        $query =
            "SELECT
                user_id,
                user_contact,
                user_email
            FROM user
            WHERE user_username = '" . escape($_SESSION['sessionusername']) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        $row = mysqli_fetch_assoc($result);
        
        $user_id = $row['user_id'];
        $contact_id = $row['user_contact'];
        $current_email_address = $row['user_email'];

        // look for contact
        $query = "SELECT id FROM contacts WHERE id = '" . $contact_id . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

        // if a contact was not found, create contact (we will update later)
        if (mysqli_num_rows($result) == 0) {
            $query = "INSERT INTO contacts (
                        email_address,
                        user,
                        timestamp)
                     VALUES (
                        '" . escape($current_email_address) . "',
                        '$user_id',
                        UNIX_TIMESTAMP())";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');

            // get contact id so we can connect user to contact
            $contact_id = mysqli_insert_id(db::$con);
            
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
        }
        
        // if e-mail address has changed and user's current e-mail address is the same as the user's current username,
        if (($liveform->get_field_value('email_address') != $current_email_address) && ($current_email_address == $_SESSION['sessionusername'])) {
            // prepare sql to update user's username
            $sql_update_username = "user_username = '" . escape($liveform->get_field_value('email_address')) . "', ";
        } else {
            $sql_update_username = '';
        }

        // update user
        $query = "UPDATE user SET user_email = '" . escape($liveform->get_field_value('email_address')) . "',$sql_update_username user_contact = '$contact_id' WHERE user_id = '" . $user_id . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // if username was changed
        if ($sql_update_username) {
            // If there is a cookie in use, and user is not logged in as a different user,
            // then update the cookies username.
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
                    setcookie('software[username]', $liveform->get_field_value('email_address'), time() + 315360000, '/', '', $secure, true);

                // Otherwise store login info in cookies without httponly cookie.
                } else {
                    setcookie('software[username]', $liveform->get_field_value('email_address'), time() + 315360000, '/', '', $secure);
                }
            }
            
            // update username in session
            $_SESSION['sessionusername'] = $liveform->get_field_value('email_address');
            
            $liveform->add_notice('Your username has been updated. Username: ' . $liveform->get_field_value('email_address'));
        }
    
    // else user is not logged in
    } else {
        $current_email_address = str_rot13(base64_decode($_POST['id']));
        
        // get contact information
        $query =
            "SELECT id
            FROM contacts
            WHERE email_address = '" . escape($current_email_address) . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // if a contact was not found for the e-mail address, then output error
        if (mysqli_num_rows($result) == 0) {
            output_error('A contact for that email address could not be found.');
            
        // else get contact id
        } else {
            $row = mysqli_fetch_assoc($result);
            $contact_id = $row['id'];
        }
        
        // if user has updated e-mail address, check that new e-mail address is not already in use by a contact
        if ($liveform->get_field_value('email_address') != $current_email_address) {
            // check to see if e-mail address is already in use by a contact
            $query =
                "SELECT id
                FROM contacts
                WHERE email_address = '" . escape($liveform->get_field_value('email_address')) . "'";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // if e-mail address is already in use by a contact
            if (mysqli_num_rows($result) > 0) {
                $liveform->mark_error('email_address', 'That email address is already in use.  Please enter a different email address.');
                $liveform->assign_field_value('email_address', '');
                
                // send user back to e-mail preferences screen
                header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . $email_preferences_path . $url_id);
                exit();
            }
            
            // set id for query string with new e-mail address
            $url_id = '?id=' . base64_encode(str_rot13($liveform->get_field_value('email_address')));;
        }
    }
    
    // get all subscription contact groups, so that contacts can be opted-in or opted-out to contact groups
    $query =
        "SELECT
            id,
            email_subscription_type
        FROM contact_groups
        WHERE email_subscription = 1";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    $contact_groups = array();
    
    while ($row = mysqli_fetch_assoc($result)) {
        $contact_groups[] = $row;
    }
    
    // loop through contact groups in order to determine which contact groups appeared on form
    foreach ($contact_groups as $key => $contact_group) {
        // assume that we should not include contact group, until we find out otherwise
        $include_contact_group = false;
        
        // if contact group's e-mail subscription type is open, then we should include contact group
        if ($contact_group['email_subscription_type'] == 'open') {
            $include_contact_group = true;
            
        // else contact group's e-mail subscription type is closed
        } else {
            // check if contact is in this contact group
            $query =
                "SELECT contact_id
                FROM contacts_contact_groups_xref
                WHERE
                    (contact_id = '" . $contact_id . "')
                    AND (contact_group_id = '" . $contact_group['id'] . "')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // if contact is in this contact group, then we should include contact group
            if (mysqli_num_rows($result) > 0) {
                $include_contact_group = true;
            }
        }
        
        // if contact group should not be included, remove contact group from array
        if ($include_contact_group == false) {
            unset($contact_groups[$key]);
        }
    }
    
    // get all contacts with e-mail address, so they can be updated
    $query = "SELECT id FROM contacts WHERE email_address = '" . escape($current_email_address) . "'";
    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
    
    $contacts = array();
    
    // assume that the main contact for this visitor is not in the contacts array until we find out otherwise
    $main_contact_in_array = false;
    
    while ($row = mysqli_fetch_assoc($result)) {
        $contacts[] = $row;
        
        // if this is the main contact for this visitor, then take note of that.
        if ($row['id'] == $contact_id) {
            $main_contact_in_array = true;
        }
    }
    
    // if the main contact for this visitor is not in the contacts array, then add it (this happens if a user's contact's e-mail address does not match the user's e-mail address)
    if ($main_contact_in_array == false) {
        $contacts[] = array('id' => $contact_id);
    }
    
    // loop through all contacts in order to update them
    foreach ($contacts as $contact) {
        // update contact
        $query =
            "UPDATE contacts
            SET
                email_address = '" . escape($liveform->get_field_value('email_address')) . "',
                opt_in = '" . escape($liveform->get_field_value('opt_in')) . "',
                user = '$user_id',
                timestamp = UNIX_TIMESTAMP()
            WHERE id = '" . $contact['id'] . "'";
        $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
        
        // loop through contact groups in order to opt-in or opt-out contact
        foreach ($contact_groups as $contact_group) {
            // if user opted-in to this group
            if ($liveform->get_field_value('contact_group_' . $contact_group['id'])) {
                // check to see if contact is already in contact group
                $query =
                    "SELECT contact_id
                    FROM contacts_contact_groups_xref
                    WHERE
                        (contact_id = '" . $contact['id'] . "')
                        AND (contact_group_id = '" . $contact_group['id'] . "')";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                
                // if contact is not already in contact group, then add contact to contact group
                if (mysqli_num_rows($result) == 0) {
                    $query =
                        "INSERT INTO contacts_contact_groups_xref (
                            contact_id,
                            contact_group_id)
                        VALUES (
                            '" . $contact['id'] . "',
                            '" . $contact_group['id'] . "')";
                    $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                }
            }
            
            // check to see if there is already an opt-in record
            $query =
                "SELECT contact_id
                FROM opt_in
                WHERE
                    (contact_id = '" . $contact['id'] . "')
                    AND (contact_group_id = '" . $contact_group['id'] . "')";
            $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            
            // if there is not already an opt-in record, then create record
            if (mysqli_num_rows($result) == 0) {
                $query =
                    "INSERT INTO opt_in (
                        contact_id,
                        contact_group_id,
                        opt_in)
                    VALUES (
                        '" . $contact['id'] . "',
                        '" . $contact_group['id'] . "',
                        '" . escape($liveform->get_field_value('contact_group_' . $contact_group['id'])) . "')";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
                
            // else an opt-in record already exists, so update record
            } else {
                $query =
                    "UPDATE opt_in
                    SET opt_in = '" . escape($liveform->get_field_value('contact_group_' . $contact_group['id'])) . "'
                    WHERE
                        (contact_id = '" . $contact['id'] . "')
                        AND (contact_group_id = '" . $contact_group['id'] . "')";
                $result = mysqli_query(db::$con, $query) or output_error('Query failed.');
            }
        }
    }
    
    $liveform->add_notice('Your email preferences have been updated.');
    
    // send user back to e-mail preferences screen
    header('Location: ' . URL_SCHEME . $_SERVER['HTTP_HOST'] . $email_preferences_path . $url_id);

}